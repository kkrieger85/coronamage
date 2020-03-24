<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2014 - 2018 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2014 - 2018 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */

class Creativestyle_AmazonPayments_Model_Api_Pay
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
                    'merchant_id'   => $this->_getConfig()->getMerchantId($store),
                    'access_key'    => $this->_getConfig()->getAccessKey($store),
                    'secret_key'    => $this->_getConfig()->getSecretKey($store),
                    'client_id'     => $this->_getConfig()->getClientId($store),
                    'region'        => $this->_getConfig()->getRegion($store),
                    'sandbox'       => $this->_getConfig()->isSandboxActive($store)
                )
            );
        }

        return $this->_api[$store];
    }

    /**
     * @param array $response
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _assertNoErrorResponse($response)
    {
        if (isset($response['Error'])) {
            throw new Creativestyle_AmazonPayments_Exception(
                '[api::Pay] ' . (
                    isset($response['Error']['Message']) ? $response['Error']['Message'] : 'Amazon Pay API error'
                ),
                isset($response['ResponseStatus']) ? $response['ResponseStatus'] : 0
            );
        }

        return true;
    }

    /**
     * @param mixed|null $store
     * @param string $orderReferenceId
     * @param string|null $addressConsentToken
     * @return mixed
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getOrderReferenceDetails($store, $orderReferenceId, $addressConsentToken = null)
    {
        $response = $this->_getApi($store)->getOrderReferenceDetails(
            array(
                'amazon_order_reference_id' => $orderReferenceId,
                'address_consent_token'     => $addressConsentToken
            )
        )->toArray();

        $this->_assertNoErrorResponse($response);

        if (isset($response['GetOrderReferenceDetailsResult'])) {
            $result = $response['GetOrderReferenceDetailsResult'];
            if (isset($result['OrderReferenceDetails'])) {
                return $result['OrderReferenceDetails'];
            }
        }

        return null;
    }

    /**
     * @param mixed|null $store
     * @param string $orderReferenceId
     * @param float $amount
     * @param string $currency
     * @param string|null $storeName
     * @return array|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function setOrderReferenceDetails($store, $orderReferenceId, $amount, $currency, $storeName = null)
    {
        $response = $this->_getApi($store)->setOrderReferenceDetails(
            array(
                'amazon_order_reference_id'     => $orderReferenceId,
                'amount'                        => $amount,
                'currency_code'                 => $currency,
                'platform_id'                   => 'AIOVPYYF70KB5',
                'store_name'                    => $storeName,
                'custom_information'            => 'Created by creativestyle, Magento 1,'
                    . ' V' . (string)Mage::getConfig()->getNode('modules/Creativestyle_AmazonPayments/version')
            )
        )->toArray();

        $this->_assertNoErrorResponse($response);

        if (isset($response['SetOrderReferenceDetailsResult'])) {
            $result = $response['SetOrderReferenceDetailsResult'];
            if (isset($result['OrderReferenceDetails'])) {
                return $result['OrderReferenceDetails'];
            }
        }

        return null;
    }

    /**
     * @param mixed|null $store
     * @param string $orderReferenceId
     * @param string $successUrl
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function confirmOrderReference($store, $orderReferenceId, $successUrl)
    {
        $response = $this->_getApi($store)->confirmOrderReference(
            array(
                'amazon_order_reference_id' => $orderReferenceId,
                'success_url' => $successUrl
            )
        )->toArray();

        $this->_assertNoErrorResponse($response);

        return true;
    }

    /**
     * @param mixed|null $store
     * @param string $orderReferenceId
     * @param string|null $cancellationReason
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function cancelOrderReference($store, $orderReferenceId, $cancellationReason = null)
    {
        $response = $this->_getApi($store)->cancelOrderReference(
            array(
                'amazon_order_reference_id' => $orderReferenceId,
                'cancelation_reason'        => $cancellationReason
            )
        )->toArray();

        $this->_assertNoErrorResponse($response);

        return true;
    }

    /**
     * @param mixed|null $store
     * @param string $orderReferenceId
     * @param string|null $closureReason
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function closeOrderReference($store, $orderReferenceId, $closureReason = null)
    {
        $response = $this->_getApi($store)->closeOrderReference(
            array(
                'amazon_order_reference_id' => $orderReferenceId,
                'closure_reason'            => $closureReason,
            )
        )->toArray();

        $this->_assertNoErrorResponse($response);

        return true;
    }

    /**
     * @param mixed|null $store
     * @param string $orderReferenceId
     * @param string|null $sellerOrderId
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function setOrderAttributes($store, $orderReferenceId, $sellerOrderId = null)
    {
        $response = $this->_getApi($store)->setOrderAttributes(
            array(
                'amazon_order_reference_id'         => $orderReferenceId,
                'seller_order_id'                   => $sellerOrderId
            )
        )->toArray();

        $this->_assertNoErrorResponse($response);

        return true;
    }

    /**
     * @param mixed|null $store
     * @param string $orderReferenceId
     * @param float $amount
     * @param string $currency
     * @param string $authorizationReferenceId
     * @param int|null $transactionTimeout
     * @param bool $captureNow
     * @param string|null $sellerAuthorizationNote
     * @param string|null $softDescriptor
     * @return array|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function authorize(
        $store,
        $orderReferenceId,
        $amount,
        $currency,
        $authorizationReferenceId,
        $transactionTimeout = null,
        $captureNow = false,
        $sellerAuthorizationNote = null,
        $softDescriptor = null
    ) {
        $response = $this->_getApi($store)->authorize(
            array(
                'amazon_order_reference_id'  => $orderReferenceId,
                'authorization_amount'       => $amount,
                'currency_code'              => $currency,
                'authorization_reference_id' => $authorizationReferenceId,
                'capture_now'                => $captureNow,
                'seller_authorization_note'  => $sellerAuthorizationNote,
                'transaction_timeout'        => $transactionTimeout,
                'soft_descriptor'            => $softDescriptor
            )
        )->toArray();

        $this->_assertNoErrorResponse($response);

        if (isset($response['AuthorizeResult'])) {
            $result = $response['AuthorizeResult'];
            if (isset($result['AuthorizationDetails'])) {
                return $result['AuthorizationDetails'];
            }
        }

        return null;
    }

    /**
     * @param mixed|null $store
     * @param string $authorizationId
     * @return array|null
     * @throws Exception
     */
    public function getAuthorizationDetails($store, $authorizationId)
    {
        $response = $this->_getApi($store)->getAuthorizationDetails(
            array(
                'amazon_authorization_id' => $authorizationId
            )
        )->toArray();

        $this->_assertNoErrorResponse($response);

        if (isset($response['GetAuthorizationDetailsResult'])) {
            $result = $response['GetAuthorizationDetailsResult'];
            if (isset($result['AuthorizationDetails'])) {
                return $result['AuthorizationDetails'];
            }
        }

        return null;
    }

    /**
     * @param mixed|null $store
     * @param string $authorizationId
     * @param float $amount
     * @param string $currency
     * @param string $captureReferenceId
     * @param string|null $sellerCaptureNote
     * @param string|null $softDescriptor
     * @return array|null
     * @throws Exception
     */
    public function capture(
        $store,
        $authorizationId,
        $amount,
        $currency,
        $captureReferenceId,
        $sellerCaptureNote = null,
        $softDescriptor = null
    ) {
        $response = $this->_getApi($store)->capture(
            array(
                'amazon_authorization_id' => $authorizationId,
                'capture_amount'          => $amount,
                'currency_code'           => $currency,
                'capture_reference_id'    => $captureReferenceId,
                'seller_capture_note'     => $sellerCaptureNote,
                'soft_descriptor'         => $softDescriptor,
            )
        )->toArray();

        $this->_assertNoErrorResponse($response);

        if (isset($response['CaptureResult'])) {
            $result = $response['CaptureResult'];
            if (isset($result['CaptureDetails'])) {
                return $result['CaptureDetails'];
            }
        }

        return null;
    }

    /**
     * @param mixed|null $store
     * @param string $captureId
     * @return array|null
     * @throws Exception
     */
    public function getCaptureDetails($store, $captureId)
    {
        $response = $this->_getApi($store)->getCaptureDetails(
            array(
                'amazon_capture_id' => $captureId,
            )
        )->toArray();

        $this->_assertNoErrorResponse($response);

        if (isset($response['GetCaptureDetailsResult'])) {
            $result = $response['GetCaptureDetailsResult'];
            if (isset($result['CaptureDetails'])) {
                return $result['CaptureDetails'];
            }
        }

        return null;
    }

    /**
     * @param mixed|null $store
     * @param string $captureId
     * @param float $amount
     * @param string $currency
     * @param string $refundReferenceId
     * @param null $sellerRefundNote
     * @param null $softDescriptor
     * @return array|null
     * @throws Exception
     */
    public function refund(
        $store,
        $captureId,
        $amount,
        $currency,
        $refundReferenceId,
        $sellerRefundNote = null,
        $softDescriptor = null
    ) {
        $response = $this->_getApi($store)->refund(
            array(
                'amazon_capture_id'                => $captureId,
                'refund_reference_id'              => $refundReferenceId,
                'refund_amount'                    => $amount,
                'currency_code'                    => $currency,
                'seller_refund_note'               => $sellerRefundNote,
                'soft_descriptor'                  => $softDescriptor
            )
        )->toArray();

        $this->_assertNoErrorResponse($response);

        if (isset($response['RefundResult'])) {
            $result = $response['RefundResult'];
            if (isset($result['RefundDetails'])) {
                return $result['RefundDetails'];
            }
        }

        return null;
    }

    /**
     * @param mixed|null $store
     * @param string $refundId
     * @return array|null
     * @throws Exception
     */
    public function getRefundDetails($store, $refundId)
    {
        $response = $this->_getApi($store)->getRefundDetails(
            array(
                'amazon_refund_id'  => $refundId,
            )
        )->toArray();

        $this->_assertNoErrorResponse($response);

        if (isset($response['GetRefundDetailsResult'])) {
            $result = $response['GetRefundDetailsResult'];
            if (isset($result['RefundDetails'])) {
                return $result['RefundDetails'];
            }
        }

        return null;
    }
}
