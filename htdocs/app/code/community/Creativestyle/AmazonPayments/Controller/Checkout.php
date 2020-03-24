<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2018 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2018 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */

class Creativestyle_AmazonPayments_Controller_Checkout extends Creativestyle_AmazonPayments_Controller_Action
{
    /**
     * Request params sent either in GET query or in POST params or in the request body
     *
     * @var array
     */
    private $_requestParams = array();

    /**
     * Returns Amazon checkout instance
     *
     * @return Creativestyle_AmazonPayments_Model_Checkout
     */
    protected function _getCheckout()
    {
        /** @var Creativestyle_AmazonPayments_Model_Checkout $checkout */
        $checkout = Mage::getSingleton('amazonpayments/checkout');
        return $checkout;
    }

    /**
     * Returns checkout session instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $checkoutSession = Mage::getSingleton('checkout/session');
        return $checkoutSession;
    }

    /**
     * Returns param of the given $key sent in the request body
     *
     * @param string $key
     * @return string|null
     */
    protected function _getRequestParam($key)
    {
        return isset($this->_requestParams[$key])
            ? $this->_requestParams[$key]
            : $this->getRequest()->getParam($key, null);
    }

    /**
     * Returns saved order reference ID
     *
     * @return string|null
     */
    protected function _getOrderReferenceId()
    {
        return $this->_getRequestParam('orderReferenceId');
    }

    /**
     * Returns saved access token
     *
     * @return string|null
     */
    protected function _getAccessToken()
    {
        return $this->_getRequestParam('accessToken');
    }

    /**
     * Returns Amazon Pay API adapter instance
     *
     * @return Creativestyle_AmazonPayments_Model_Api_Pay
     */
    protected function _getApi()
    {
        /** @var Creativestyle_AmazonPayments_Model_Api_Pay $api */
        $api = Mage::getSingleton('amazonpayments/api_pay');
        return $api;
    }

    /**
     * Returns current quote entity
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCheckout()->getQuote();
    }

    /**
     * @param string $handle
     * @return string
     * @throws Mage_Core_Exception
     */
    protected function _getLayoutHandleHtml($handle)
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load($handle);
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    /**
     * Cancels order reference at Amazon Payments gateway
     *
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _cancelOrderReference()
    {
        $orderReferenceId = $this->_getOrderReferenceId();
        if ($orderReferenceId) {
            $this->_getApi()->cancelOrderReference(null, $orderReferenceId);
        }
    }

    /**
     * Send Ajax redirect response
     *
     * @param string|null $debug
     * @return $this
     */
    protected function _ajaxRedirectResponse($debug = null)
    {
        /** @var Mage_Core_Controller_Response_Http $response */
        $response = $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true');

        if ($debug) {
            $response->setHeader('X-Amazon-Pay-Debug', $debug);
        }

        $response->sendResponse();
        return $this;
    }

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        if (!$this->_getQuote()->hasItems() || $this->_getQuote()->getHasError()) {
            $this->_ajaxRedirectResponse('Cart is invalid or has no items');
            return true;
        }

        if ($this->_getCheckoutSession()->getCartWasUpdated(true)) {
            $this->_ajaxRedirectResponse('Cart was updated');
            return true;
        }

        if (null === $this->_getOrderReferenceId()) {
            $this->_ajaxRedirectResponse('Order reference ID is missing');
            return true;
        }

        return false;
    }

    /**
     * @return array|Mage_Checkout_Model_Type_Onepage
     * @throws Exception
     */
    protected function _saveShipping()
    {
        // submit draft data of order reference to Amazon gateway
        $this->_getApi()->setOrderReferenceDetails(
            null,
            $this->_getOrderReferenceId(),
            $this->_getCheckout()->getQuote()->getBaseGrandTotal(),
            $this->_getCheckout()->getQuote()->getBaseCurrencyCode()
        );

        $orderReferenceDetails = $this->_getApi()->getOrderReferenceDetails(
            null,
            $this->_getOrderReferenceId(),
            $this->_getAccessToken()
        );

        /** @var Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor */
        $transactionProcessor = Mage::getModel('amazonpayments/processor_transaction');
        $transactionProcessor->setTransactionDetails($orderReferenceDetails);
        $shippingAddress = $transactionProcessor->getMagentoShippingAddress();
        $billingAddress = $transactionProcessor->getMagentoBillingAddress();
        if (empty($billingAddress)) {
            $billingAddress = $shippingAddress;
        }

        $this->_getCheckout()->saveBilling($billingAddress, false);

        $result = $this->_getCheckout()->saveShipping(
            array_merge($shippingAddress, array('use_for_shipping' => true)),
            false
        );

        return $result;
    }

    public function preDispatch()
    {
        parent::preDispatch();

        $rawBodyParams = array();
        if ($rawBody = $this->getRequest()->getRawBody()) {
            try {
                $rawBodyParams = $this->_jsonDecode($rawBody);
            } catch (Zend_Json_Exception $e) {
                $rawBodyParams = array();
            }
        }

        $this->_requestParams = array_merge($rawBodyParams, $this->getRequest()->getParams());
    }
}
