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
 * @package    Creativestyle\AmazonPayments\Model\Api
 * @copyright  2014 - 2018 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_Model_Api_Ipn
{
    /**
     * @param array $headers
     * @param string $body
     * @return AmazonPay_IpnHandlerInterface
     */
    protected function _getHandler($headers, $body)
    {
        return new AmazonPay_IpnHandler($headers, $body);
    }

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
     * Extracts transaction data from IPN notification
     *
     * @param array $notification
     * @return array
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _getTransactionDetailsFromNotification($notification)
    {
        if (isset($notification['OrderReference'])) {
            return array($notification['OrderReference'], $notification['OrderReference']['AmazonOrderReferenceId']);
        } elseif (isset($notification['AuthorizationDetails'])) {
            return array(
                $notification['AuthorizationDetails'],
                $notification['AuthorizationDetails']['AmazonAuthorizationId']
            );
        } elseif (isset($notification['CaptureDetails'])) {
            return array($notification['CaptureDetails'], $notification['CaptureDetails']['AmazonCaptureId']);
        } elseif (isset($notification['RefundDetails'])) {
            return array($notification['RefundDetails'], $notification['RefundDetails']['AmazonRefundId']);
        }

        throw new Creativestyle_AmazonPayments_Exception('[api::Ipn] Invalid IPN notification data', 500);
    }

    /**
     * Returns order instance for transaction of given ID
     *
     * @param string $transactionId
     * @return int
     * @throws Creativestyle_AmazonPayments_Exception_TransactionNotFound
     */
    protected function _lookupOrderIdByTransactionId($transactionId)
    {
        /** @var Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection $transactionCollection */
        $transactionCollection = Mage::getModel('sales/order_payment_transaction')
            ->getCollection()
            ->addFieldToFilter('txn_id', $transactionId)
            ->setPageSize(1)
            ->setCurPage(1);

        foreach ($transactionCollection as $transaction) {
            return (int)$transaction->getOrderId();
        }

        throw new Creativestyle_AmazonPayments_Exception_TransactionNotFound($transactionId);
    }

    /**
     * @param string $transactionId
     * @return Mage_Sales_Model_Order_Payment
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _lookupPaymentByTransactionId($transactionId)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($this->_lookupOrderIdByTransactionId($transactionId));
        if ($order->getId()) {
            return $order->getPayment();
        }

        throw new Creativestyle_AmazonPayments_Exception('[api::Ipn] Order for the payment transaction not found', 500);
    }

    /**
     * Converts a IPN request body and headers into a notification array
     *
     * @param array $headers
     * @param string $body
     * @return array
     */
    public function parseNotification($headers, $body)
    {
        return $this->_getHandler($headers, $body)->toArray();
    }

    /**
     * @param array $notification
     * @return string
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Creativestyle_AmazonPayments_Exception_TransactionNotFound
     * @throws Mage_Core_Exception
     * @throws Varien_Exception
     */
    public function processNotification(array $notification)
    {
        list($transactionDetails, $transactionId) = $this->_getTransactionDetailsFromNotification($notification);

        if (isset($transactionDetails['AuthorizationReferenceId'])
            && preg_match('/-sync$/', $transactionDetails['AuthorizationReferenceId'])) {
            return $transactionId;
        }

        $payment = $this->_lookupPaymentByTransactionId($transactionId);

        if (!$payment->getAuthorizationTransaction() && !$this->_getConfig()->isManualAuthorizationAllowed()) {
            throw new Creativestyle_AmazonPayments_Exception_TransactionNotFound($transactionId);
        }

        $transaction = $payment->lookupTransaction($transactionId);

        /** @var Creativestyle_AmazonPayments_Model_Payment_Abstract $methodInstance */
        $methodInstance = $payment->getMethodInstance();
        $methodInstance->importTransactionDetails($payment, $transaction, new Varien_Object());

        $payment->getOrder()
            ->addRelatedObject($transaction)
            ->save();

        return $transactionId;
    }

    /**
     * @param array $headers
     * @param string $body
     * @return string
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Exception
     */
    public function parseAndProcessNotification($headers, $body)
    {
        $notification = $this->parseNotification($headers, $body);
        return $this->processNotification($notification);
    }
}
