<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2014 - 2019 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2014 - 2019 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_Model_Api_Login
{
    /**
     * @var AmazonPay_ClientInterface[]
     */
    protected $_api = array();

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
     * Returns Amazon Pay API client instance
     *
     * @param mixed $store
     * @return AmazonPay_ClientInterface
     * @throws Exception
     */
    protected function _getApi($store = null)
    {
        if (!isset($this->_api[$store])) {
            $this->_api[$store] = new AmazonPay_Client(
                array(
                    'client_id'     => $this->_getConfig()->getClientId($store),
                    'region'        => $this->_getConfig()->getRegion($store),
                    'sandbox'       => $this->_getConfig()->isSandboxActive($store)
                )
            );
        }

        return $this->_api[$store];
    }

    /**
     * Returns user's profile
     *
     * @param mixed $store
     * @param string $accessToken
     * @return Creativestyle_AmazonPayments_Model_Login_UserProfile
     * @throws Exception
     */
    public function getUserProfile($store, $accessToken)
    {
        /** @var Creativestyle_AmazonPayments_Model_Login_UserProfile $userProfile */
        $userProfile = Mage::getModel(
            'amazonpayments/login_userProfile',
            $this->_getApi($store)->getUserInfo($accessToken)
        );
        return $userProfile;
    }
}
