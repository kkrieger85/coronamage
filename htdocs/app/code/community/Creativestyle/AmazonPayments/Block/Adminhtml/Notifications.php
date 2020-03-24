<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2016 - 2017 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2016 - 2017 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{
    /**
     * Store views collection
     */
    protected $_storeCollection = null;

    /**
     * Returns store views collection
     *
     * @return Mage_Core_Model_Resource_Store_Collection
     */
    protected function _getStoreCollection()
    {
        if (null === $this->_storeCollection) {
            $this->_storeCollection = Mage::getModel('core/store')->getCollection()->load();
        }

        return $this->_storeCollection;
    }

    /**
     * Returns Amazon Pay config model instance
     *
     * @return Creativestyle_AmazonPayments_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('amazonpayments/config');
    }

    public function isLegacyAccount()
    {
        foreach ($this->_getStoreCollection() as $store) {
            $active = $this->_getConfig()->isPayActive($store);
            $merchantId = $this->_getConfig()->getMerchantId($store);
            $clientId = $this->_getConfig()->getClientId($store);
            if ($active && $merchantId && !$clientId) {
                return true;
            }
        }

        return false;
    }
}
