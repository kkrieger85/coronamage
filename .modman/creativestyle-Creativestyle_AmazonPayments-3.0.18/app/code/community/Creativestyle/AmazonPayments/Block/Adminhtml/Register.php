<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2014 - 2017 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2014 - 2017 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_Block_Adminhtml_Register extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        $this->setTemplate('creativestyle/amazonpayments/register.phtml');
        return parent::_construct();
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

    public function getAccountRegionOptions()
    {
        return Mage::getSingleton('amazonpayments/lookup_accountRegion')->toOptionArray();
    }

    public function getLanguageOptions()
    {
        return Mage::getSingleton('amazonpayments/lookup_language')->toOptionArray();
    }

    public function getDefaultAccountRegion()
    {
        return Mage::getStoreConfig('currency/options/base');
    }

    public function getDefaultLanguage()
    {
        /** @var Creativestyle_AmazonPayments_Model_Lookup_Language $languageLookupModel */
        $languageLookupModel = Mage::getSingleton('amazonpayments/lookup_language');
        return $languageLookupModel->getLanguageByLocale(Mage::app()->getLocale()->getLocaleCode(), true);
    }

    public function getState()
    {
        if ($this->_getConfig()->getMerchantId()) {
            return 0;
        }

        return 1;
    }
}
