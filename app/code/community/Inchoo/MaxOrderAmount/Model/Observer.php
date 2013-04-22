<?php
/**
 * @category    Inchoo
 * @package     Inchoo_MaxOrderAmount
 * @author      Branko Ajzele <ajzele@gmail.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Inchoo_MaxOrderAmount_Model_Observer
{
    private $_helper;
    
    public function __construct() 
    {
        $this->_helper = Mage::helper('inchoo_maxorderamount');
    }

    /**
     * No single order can be placed over the amount of X
     */
    public function enforceSingleOrderLimit($observer)
    {
        if (!$this->_helper->isModuleEnabled()) {
            return;
        }

        $quote = $observer->getEvent()->getQuote();
        
        if ((float)$quote->getGrandTotal() > (float)$this->_helper->getSingleOrderTopAmount()) {
            
            $formattedPrice = Mage::helper('core')->currency($this->_helper->getSingleOrderTopAmount(), true, false);
            
            Mage::getSingleton('checkout/session')->addError(
                $this->_helper->__($this->_helper->getSingleOrderTopAmountMsg(), $formattedPrice));

            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
            Mage::app()->getResponse()->sendResponse();
            exit;
        }
    }
    
    /**
     * An email is sent to admins when an order over X total price is placed in order to investigate
     */
    public function pushOrderAmountNotification($observer)
    {
        $order = $observer->getEvent()->getOrder();
        
        if ((float)$order->getGrandTotal() < (float)$this->_helper->getOrderAmountNotification()) {
            return;
        }
        
        $storeId = $order->getStoreId();
        
        if (!$this->_helper->isModuleEnabled()) {
            return;
        }  
        
        try {
            $notifyToEmails = $this->_helper->getNotifyToEmails();

            if (empty($notifyToEmails)) {
                return;
            }
            
            $templateId = $this->_helper->getOrderAmountNotificationEmailTemplate($storeId);
            $mailer = Mage::getModel('core/email_template_mailer');            
            
            foreach ($notifyToEmails as $entry) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($entry['email'], $entry['name']);
                $mailer->addEmailInfo($emailInfo);
            }

            $mailer->setSender(array(
                'name' => $this->_helper->getStoreEmailAddressSenderOption('general', 'name'),
                'email' => $this->_helper->getStoreEmailAddressSenderOption('general', 'email'),
            ));

            $mailer->setStoreId($storeId);
            $mailer->setTemplateId($templateId);
            $mailer->setTemplateParams(array(
                'order' => $order,
            ));
            
            $mailer->send();
            
            
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
