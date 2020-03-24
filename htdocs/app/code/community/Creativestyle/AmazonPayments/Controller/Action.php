<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2017 - 2019 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2017 - 2019 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */

class Creativestyle_AmazonPayments_Controller_Action extends Mage_Core_Controller_Front_Action
{
    /**
     * Returns instance of Amazon Payments config object
     *
     * @return Creativestyle_AmazonPayments_Model_Config
     */
    protected function _getConfig()
    {
        /** @var Creativestyle_AmazonPayments_Model_Config $config */
        $config = Mage::getSingleton('amazonpayments/config');
        return $config;
    }

    /**
     * Returns instance of Amazon Pay helper
     *
     * @return Creativestyle_AmazonPayments_Helper_Data
     */
    protected function _getHelper()
    {
        /** @var Creativestyle_AmazonPayments_Helper_Data $helper */
        $helper = Mage::helper('amazonpayments');
        return $helper;
    }

    /**
     * Returns instance of Magento core helper
     *
     * @return Mage_Core_Helper_Data
     */
    protected function _getCoreHelper()
    {
        /** @var Mage_Core_Helper_Data $helper */
        $helper = Mage::helper('core');
        return $helper;
    }

    /**
     * Returns instance of Amazon Payments URLs repository
     *
     * @return Creativestyle_AmazonPayments_Model_Url
     */
    protected function _getUrl()
    {
        /** @var Creativestyle_AmazonPayments_Model_Url $url */
        $url = Mage::getSingleton('amazonpayments/url');
        return $url;
    }

    /**
     * Decodes provided $encodedValue JSON string
     *
     * @param string $encodedValue
     * @return array
     * @throws Zend_Json_Exception
     */
    protected function _jsonDecode($encodedValue)
    {
        return $this->_getCoreHelper()->jsonDecode($encodedValue);
    }

    /**
     * Encode provided $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @return string
     * @throws Zend_Json_Exception
     */
    protected function _jsonEncode($valueToEncode)
    {
        return $this->_getCoreHelper()->jsonEncode($valueToEncode);
    }

    /**
     * Sets value of head's title tag
     *
     * @param string $title
     * @return $this
     */
    protected function _setHeadTitle($title)
    {
        /** @var Mage_Page_Block_Html_Head $jsBlock */
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle($this->__($title));
        }

        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    protected function _setJsParams(array $params)
    {
        /** @var Creativestyle_AmazonPayments_Block_Js $headBlock */
        $jsBlock = $this->getLayout()->getBlock('amazonpayments.js');
        if ($jsBlock) {
            foreach ($params as $key => $value) {
                $jsBlock->setData($key, $value);
            }
        }

        return $this;
    }

    /**
     * @param array $responseData
     * @return $this
     * @throws Zend_Json_Exception
     */
    protected function _setJsonResponse(array $responseData)
    {
        $this->getResponse()->setBody($this->_jsonEncode($responseData));
        return $this;
    }
}
