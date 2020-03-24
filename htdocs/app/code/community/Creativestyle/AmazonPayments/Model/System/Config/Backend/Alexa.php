<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2019 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2019 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_Model_System_Config_Backend_Alexa extends Mage_Core_Model_Config_Data
{
    /**
     * Returns Magento admin session instance
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        /** @var Mage_Adminhtml_Model_Session $session */
        $session = Mage::getModel('adminhtml/session');
        return $session;
    }

    /**
     * @return Mage_Core_Model_Abstract
     * @throws Exception
     */
    public function save()
    {
        $enabled = $this->getValue();
        $carriers = $this->getFieldsetDataValue('delivery_tracking_carrier_codes');
        if ($enabled && !(is_array($carriers) && count($carriers) > 1)) {
            $this->_getSession()
                ->addError('You didn\'t map carrier codes. Without mapping, no delivery notification will be sent.');
        }

        return parent::save();
    }
}
