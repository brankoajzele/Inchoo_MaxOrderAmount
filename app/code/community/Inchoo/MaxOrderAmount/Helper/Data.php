<?php
/**
 * @category    Inchoo
 * @package     Inchoo_MaxOrderAmount
 * @author      Branko Ajzele <ajzele@gmail.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Inchoo_MaxOrderAmount_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ACTIVE                                   = 'sales/inchoo_maxorderamount/active';
    const XML_PATH_SINGLE_ORDER_TOP_AMOUNT                  = 'sales/inchoo_maxorderamount/single_order_top_amount';
    const XML_PATH_SINGLE_ORDER_TOP_AMOUNT_MSG              = 'sales/inchoo_maxorderamount/single_order_top_amount_msg';
    const XML_PATH_ORDER_AMOUNT_NOTIFICATION                = 'sales/inchoo_maxorderamount/order_amount_notification';
    const XML_PATH_NOTIFY_TO_EMAILS                         = 'sales/inchoo_maxorderamount/notify_to_emails';
    const XML_PATH_ORDER_AMOUNT_NOTIFICATION_EMAIL_TEMPLATE = 'sales/inchoo_maxorderamount/order_amount_notification_email_template';
    
    public function isModuleEnabled($moduleName = null)
    {
        if ((int)Mage::getStoreConfig(self::XML_PATH_ACTIVE, Mage::app()->getStore()) != 1) {
            return false;
        }
        
        return parent::isModuleEnabled($moduleName);
    }   
    
    public function getSingleOrderTopAmount($store = null) 
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_SINGLE_ORDER_TOP_AMOUNT, $store);
    }
    
    public function getSingleOrderTopAmountMsg($store = null) 
    {
        return Mage::getStoreConfig(self::XML_PATH_SINGLE_ORDER_TOP_AMOUNT_MSG, $store);
    }        
    
    public function getOrderAmountNotification($store = null) 
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_ORDER_AMOUNT_NOTIFICATION, $store);
    }
    
    public function getNotifyToEmails($store = null) 
    {
        $entries = Mage::getStoreConfig(self::XML_PATH_NOTIFY_TO_EMAILS, $store);
        $emails = array();

        if (!empty($entries)) {
            $entries = explode(PHP_EOL, $entries);

            if (is_array($entries)) {
                foreach ($entries as $entry) {
                    $_entry = trim($entry);
                    $_name = trim(substr($_entry, 0, strpos($_entry, '<')));
                    $_email = trim(substr($_entry, strpos($_entry, '<') + 1, -1));

                    if (empty($_name) || empty($_email)) { continue; }
                    if (!filter_var($_email, FILTER_VALIDATE_EMAIL)) { continue; }
                    
                    $emails[] = array('name' => $_name, 'email' => $_email);
                }
            }
        }

        return $emails;
    }
    
    public function getOrderAmountNotificationEmailTemplate($store = null) 
    {
        return Mage::getStoreConfig(self::XML_PATH_ORDER_AMOUNT_NOTIFICATION_EMAIL_TEMPLATE, $store);
    }

    /**
     * @param $identType ('general' or 'sales' or 'support' or 'custom1' or 'custom2')
     * @param $option ('name' or 'email')
     * @return string
     */
    public function getStoreEmailAddressSenderOption($identType, $option)
    {
        if (!$generalContactName = Mage::getSingleton('core/config_data')->getCollection()->getItemByColumnValue('path', 'trans_email/ident_'.$identType.'/'.$option)) {
            $conf = Mage::getSingleton('core/config')->init()->getXpath('/config/default/trans_email/ident_'.$identType.'/'.$option);
            $generalContactName = array_shift($conf);
        } else {
            $generalContactName = $generalContactName->getValue();
        }

        return (string)$generalContactName;
    }
}
