<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2015 - 2018 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2015 - 2018 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_Model_Processor_Payment
{

    /**
     * Payment info instance
     *
     * @var Mage_Sales_Model_Order_Payment|null
     */
    protected $_payment = null;

    /**
     * Payment store ID
     *
     * @var int|null
     */
    protected $_storeId = null;

    /**
     * Returns API adapter instance
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
     * Process transaction simulation request
     *
     * @param string $transactionType
     * @return string
     */
    protected function _processTransactionStateSimulation($transactionType)
    {
        return Creativestyle_AmazonPayments_Model_Simulator::simulate($this->getPayment(), $transactionType);
    }

    /**
     * Returns payment info instance
     *
     * @return Mage_Sales_Model_Order_Payment
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getPayment()
    {
        if (null === $this->_payment) {
            throw new Creativestyle_AmazonPayments_Exception('[proc::Payment] Payment info object is not set');
        }

        return $this->_payment;
    }

    /**
     * Sets payment info instance for the processing
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return $this
     */
    public function setPayment(Mage_Sales_Model_Order_Payment $payment)
    {
        $this->_payment = $payment;
        return $this;
    }

    /**
     * Returns order's store ID
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * Sets store ID for processing payment
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * @param float $amount
     * @param string $currency
     * @param string $transactionReferenceId
     * @param string|null $storeName
     * @return array|null
     * @throws Exception
     */
    public function setOrderDetails($amount, $currency, $transactionReferenceId, $storeName = null)
    {
        $transactionDetails = $this->_getApi()->setOrderReferenceDetails(
            $this->getStoreId(),
            $transactionReferenceId,
            $amount,
            $currency,
            $storeName
        );
        return $transactionDetails;
    }

    /**
     * @param string $transactionReferenceId
     * @throws Exception
     */
    public function order($transactionReferenceId)
    {
        $this->_getApi()->confirmOrderReference(
            $this->getStoreId(),
            $transactionReferenceId,
            Mage::getUrl('amazonpayments/checkout/mfa')
        );
        $this->_processTransactionStateSimulation('OrderReference');
    }

    /**
     * @param string $transactionId
     * @param string|null $closureReason
     * @throws Exception
     */
    public function closeOrderReference($transactionId, $closureReason = null)
    {
        $this->_getApi()->closeOrderReference($this->getStoreId(), $transactionId, $closureReason);
    }

    /**
     * Authorize order amount on Amazon Payments gateway
     *
     * @param float $amount
     * @param string $currency
     * @param string $transactionReferenceId
     * @param string $parentTransactionId
     * @param bool $isSync
     * @param bool $captureNow
     * @param string|null $softDescriptor
     * @return array|null
     * @throws Exception
     */
    public function authorize(
        $amount,
        $currency,
        $transactionReferenceId,
        $parentTransactionId,
        $isSync = true,
        $captureNow = false,
        $softDescriptor = null
    ) {
        return $this->_getApi()->authorize(
            $this->getStoreId(),
            $parentTransactionId,
            $amount,
            $currency,
            $transactionReferenceId,
            $isSync ? 0 : null,
            $captureNow,
            $this->_processTransactionStateSimulation('Authorization'),
            $captureNow ? $softDescriptor : null
        );
    }

    /**
     * Capture order amount on Amazon Payments gateway
     *
     * @param float $amount
     * @param string $currency
     * @param string $transactionReferenceId
     * @param string $parentTransactionId
     * @param string|null $softDescriptor
     * @return array|null
     * @throws Exception
     */
    public function capture($amount, $currency, $transactionReferenceId, $parentTransactionId, $softDescriptor = null)
    {
        return $this->_getApi()->capture(
            $this->getStoreId(),
            $parentTransactionId,
            $amount,
            $currency,
            $transactionReferenceId,
            $this->_processTransactionStateSimulation('Capture'),
            $softDescriptor
        );
    }

    /**
     * Refund amount on Amazon Payments gateway
     *
     * @param float $amount
     * @param string $currency
     * @param string $transactionReferenceId
     * @param $parentTransactionId
     * @param null $softDescriptor
     * @return array|null
     * @throws Exception
     */
    public function refund($amount, $currency, $transactionReferenceId, $parentTransactionId, $softDescriptor = null)
    {
        return $this->_getApi()->refund(
            $this->getStoreId(),
            $parentTransactionId,
            $amount,
            $currency,
            $transactionReferenceId,
            $this->_processTransactionStateSimulation('Refund'),
            $softDescriptor
        );
    }

    /**
     * Cancel order reference on Amazon Payments gateway
     *
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Exception
     */
    public function cancelOrderReference()
    {
        $orderTransaction = $this->getPayment()
            ->lookupTransaction(false, Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER);
        if ($orderTransaction && !$orderTransaction->getIsClosed()) {
            $this->_getApi()->cancelOrderReference($this->getStoreId(), $orderTransaction->getTxnId());
            $orderTransaction->setIsClosed(true)->save();
        }
    }
}
