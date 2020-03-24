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


class Creativestyle_AmazonPayments_Model_Api_Alexa
{
    /**
     * @var AmazonPay_V2_ClientInterface[]
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
     * @return AmazonPay_V2_ClientInterface
     * @throws Exception
     */
    protected function _getApi($store = null)
    {
        if (!isset($this->_api[$store])) {
            $this->_api[$store] = new AmazonPay_V2_Client(
                array(
                    'public_key_id' => $this->_getConfig()->getDeliveryTrackingPublicKeyId($store),
                    'private_key'   => $this->_getConfig()->getDeliveryTrackingPrivateKey($store),
                    'sandbox'       => false,
                    'region'        => $this->_getConfig()->getRegion($store)
                )
            );
        }

        return $this->_api[$store];
    }

    /**
     * @param mixed|null $store
     * @param string $orderReferenceId
     * @param string $trackingNumber
     * @param string $carrierCode
     * @throws Exception
     */
    public function addDeliveryTracking($store, $orderReferenceId, $trackingNumber, $carrierCode)
    {
        $payload = array(
            'amazonOrderReferenceId' => $orderReferenceId,
            'deliveryDetails' => array(array(
                'trackingNumber' => $trackingNumber,
                'carrierCode' => $carrierCode
            ))
        );

        return $this->_getApi($store)->deliveryTrackers($payload);
    }
}
