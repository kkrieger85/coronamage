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
 * @package    Creativestyle\AmazonPayments\Model\Payment
 * @copyright  2014 - 2019 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
abstract class Creativestyle_AmazonPayments_Model_Payment_Abstract extends Mage_Payment_Model_Method_Abstract
{
    const ACTION_MANUAL                         = 'manual';
    const ACTION_AUTHORIZE                      = 'authorize';
    const ACTION_AUTHORIZE_CAPTURE              = 'authorize_capture';
    const ACTION_ERP                            = 'erp';

    const CHECK_USE_FOR_COUNTRY                 = 1;
    const CHECK_USE_FOR_CURRENCY                = 2;
    const CHECK_USE_CHECKOUT                    = 4;
    const CHECK_USE_FOR_MULTISHIPPING           = 8;
    const CHECK_USE_INTERNAL                    = 16;
    const CHECK_ORDER_TOTAL_MIN_MAX             = 32;
    const CHECK_RECURRING_PROFILES              = 64;
    const CHECK_ZERO_TOTAL                      = 128;

    protected $_code                            = 'amazonpayments_abstract';
    protected $_infoBlockType                   = 'amazonpayments/payment_info';

    /**
     * Amazon Pay method features
     *
     * @var bool
     */
    protected $_isGateway                   = false;
    protected $_canOrder                    = true;
    protected $_canAuthorize                = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canCaptureOnce              = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_canVoid                     = true;
    protected $_canUseInternal              = false;
    protected $_canUseCheckout              = true;
    protected $_canUseForMultishipping      = false;
    protected $_isInitializeNeeded          = true;
    protected $_canFetchTransactionInfo     = true;
    protected $_canReviewPayment            = false;
    protected $_canCreateBillingAgreement   = false;
    protected $_canManageRecurringProfiles  = true;

    /**
     * @var Creativestyle_AmazonPayments_Model_Processor_Transaction|null
     */
    protected $_lastTransactionProcessor    = null;

    /**
     * Returns Amazon Pay config model instance
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
     * Return Magento order processor instance
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Creativestyle_AmazonPayments_Model_Processor_Order
     */
    protected function _getOrderProcessor(Mage_Sales_Model_Order_Payment $payment)
    {
        return Mage::getModel('amazonpayments/processor_order')->setOrder($payment->getOrder());
    }

    /**
     * Returns payment processor instance
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Creativestyle_AmazonPayments_Model_Processor_Payment
     */
    protected function _getPaymentProcessor(Mage_Sales_Model_Order_Payment $payment)
    {
        return Mage::getModel('amazonpayments/processor_payment')
            ->setPayment($payment)
            ->setStoreId($payment->getOrder()->getStoreId());
    }

    /**
     * Returns transaction processor instance
     *
     * @param Mage_Sales_Model_Order_Payment_Transaction $transaction
     * @return Creativestyle_AmazonPayments_Model_Processor_Transaction
     */
    protected function _getTransactionProcessor(Mage_Sales_Model_Order_Payment_Transaction $transaction)
    {
        return Mage::getModel('amazonpayments/processor_transaction')->setTransaction($transaction);
    }

    /**
     * @param mixed|null $store
     * @return Varien_Object
     * @throws Varien_Exception
     */
    protected function _getCustomStatusList($store = null)
    {
        return new Varien_Object(
            array(
                'new_order_status' => $this->_getConfig()->getNewOrderStatus($store),
                'holded_order_status' => $this->_getConfig()->getHoldedOrderStatus($store),
                'authorized_order_status' => $this->_getConfig()->getAuthorizedOrderStatus($store),
                'invalid_payment_method_declined_authorization_order_status'
                    => $this->_getConfig()->getInvalidPaymentMethodOrderStatus($store),
                'transaction_timed_out_declined_authorization_order_status'
                    => $this->_getConfig()->getTransactionTimedOutOrderStatus($store),
                'amazon_rejected_declined_authorization_order_status'
                    => $this->_getConfig()->getAmazonRejectedOrderStatus($store),
                'processing_failure_declined_authorization_order_status'
                    => $this->_getConfig()->getProcessingFailureOrderStatus($store)
            )
        );
    }

    /**
     * Returns transaction sequence ID, comprised of order reference ID
     * and unique part, it can be next used as a transaction reference ID
     * for requesting new transactions in Amazon Pay API
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return string
     */
    protected function _getTransactionSequenceId(Mage_Sales_Model_Order_Payment $payment)
    {
        return substr(
            sprintf(
                '%s-%s',
                $payment->getOrder()->getExtOrderId(),
                hash('sha512', uniqid())
            ),
            0,
            27
        );
    }

    /**
     * @param string $transactionType
     * @param array|null $transactionInfo
     * @param array $states
     * @param bool $isSync
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception_InvalidTransaction
     */
    protected function _assertTransactionState(
        $transactionType,
        $transactionInfo = null,
        $states = array(),
        $isSync = true
    ) {
        if ($transactionInfo) {
            $transactionInfoObj = new Varien_Object($transactionInfo);
            $transactionState = $transactionInfoObj->getData(
                Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_KEY
            );
            if (!in_array($transactionState, $states)) {
                throw new Creativestyle_AmazonPayments_Exception_InvalidTransaction(
                    $transactionType,
                    $transactionInfo,
                    $isSync,
                    sprintf('Invalid Amazon Pay %s transaction status', $transactionType)
                );
            }
        }

        return true;
    }

    /**
     * Checks whether payment should be re-authorized asynchronously
     * after synchronous authorization failed
     *
     * @param bool $isSync
     * @param string $txnState
     * @param string|null $txnReasonCode
     * @return bool
     */
    protected function _shouldReauthorizeAsynchronously($isSync, $txnState, $txnReasonCode = null)
    {
        return $isSync && $this->_getConfig()->isAuthorizationOmnichronous()
            && $txnState == Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_DECLINED
            && $txnReasonCode == Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_REASON_TIMEOUT;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @param Varien_Object $stateObject
     * @return Mage_Sales_Model_Order_Payment_Transaction
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Creativestyle_AmazonPayments_Exception_InvalidTransaction
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     * @throws Varien_Exception
     */
    protected function _order(Mage_Sales_Model_Order_Payment $payment, $amount, Varien_Object $stateObject)
    {
        if (!$this->canOrder()) {
            throw new Creativestyle_AmazonPayments_Exception('Order action is not available');
        }

        $paymentProcessor = $this->_getPaymentProcessor($payment);

        if (!$payment->getSkipOrderReferenceProcessing()) {
            $paymentProcessor->setOrderDetails(
                $amount,
                $payment->getOrder()->getBaseCurrencyCode(),
                $payment->getTransactionId(),
                $this->_getConfig()->getStoreName($payment->getOrder()->getStoreId())
            );
        }

        $paymentProcessor->order($payment->getTransactionId());

        /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
        $transaction = $payment->setIsTransactionClosed(false)
            ->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER);
        $transactionInfo = $this->_fetchTransactionInfo($payment, $transaction, $stateObject);
        $this->_validateOrderReferenceState($transactionInfo);
        if ($transactionInfo) {
            $transaction->setAdditionalInformation(
                Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
                $transactionInfo
            );
        }

        return $transaction;
    }

    /**
     * @param array|null $transactionInfo
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception_InvalidTransaction
     */
    protected function _validateOrderReferenceState($transactionInfo = null)
    {
        return $this->_assertTransactionState(
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_ORDER,
            $transactionInfo,
            array(
                Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_OPEN
            )
        );
    }

    /**
     * Authorize payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @param Varien_Object $stateObject
     * @param string $transactionReferenceId
     * @param string $parentTransactionId
     * @param bool $isSync
     * @param bool $captureNow
     * @param string|null $softDescriptor
     * @return Mage_Sales_Model_Order_Payment_Transaction
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Creativestyle_AmazonPayments_Exception_InvalidTransaction
     * @throws Mage_Core_Exception
     * @throws Varien_Exception
     */
    protected function _authorize(
        Mage_Sales_Model_Order_Payment $payment,
        $amount,
        Varien_Object $stateObject,
        $transactionReferenceId,
        $parentTransactionId,
        $isSync = true,
        $captureNow = false,
        $softDescriptor = null
    ) {
        if (!$this->canAuthorize()) {
            throw new Creativestyle_AmazonPayments_Exception('Authorize action is not available');
        }

        $transactionDetails = $this->_getPaymentProcessor($payment)->authorize(
            $amount,
            $payment->getOrder()->getBaseCurrencyCode(),
            $transactionReferenceId. ($isSync ? '-sync' : ''),
            $parentTransactionId,
            $isSync,
            $captureNow,
            $softDescriptor
        );

        /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
        $transaction = $payment->setIsTransactionClosed(false)
            ->setTransactionId($transactionDetails['AmazonAuthorizationId'])
            ->setParentTransactionId($parentTransactionId)
            ->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);
        $transactionInfo = $this->_fetchTransactionInfo($payment, $transaction, $stateObject);
        if ($transactionInfo) {
            $transaction->setAdditionalInformation(
                Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
                $transactionInfo
            );
        }

        try {
            $this->_validateAuthorizationState($transactionInfo, $isSync);
        } catch (Creativestyle_AmazonPayments_Exception_InvalidTransaction $e) {
            if ($this->_shouldReauthorizeAsynchronously($isSync, $e->getState(), $e->getReasonCode())) {
                $transaction->setIsClosed(true);
                return $this->_authorize(
                    $payment,
                    $amount,
                    $stateObject,
                    $this->_getTransactionSequenceId($payment),
                    $parentTransactionId,
                    false,
                    $captureNow,
                    $softDescriptor
                );
            }

            throw $e;
        }

        return $transaction;
    }

    /**
     * @param array|null $transactionInfo
     * @param bool $isSync
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception_InvalidTransaction
     */
    protected function _validateAuthorizationState($transactionInfo = null, $isSync = true)
    {
        return $this->_assertTransactionState(
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_AUTH,
            $transactionInfo,
            array(
                Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_PENDING,
                Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_OPEN,
                Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_COMPLETED,
                Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_CLOSED
            ),
            $isSync
        );
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @param Varien_Object $stateObject
     * @param string $transactionReferenceId
     * @param string $parentTransactionId
     * @param string|null $softDescriptor
     * @return Mage_Sales_Model_Order_Payment_Transaction
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Creativestyle_AmazonPayments_Exception_InvalidTransaction
     * @throws Mage_Core_Exception
     * @throws Varien_Exception
     */
    protected function _capture(
        Mage_Sales_Model_Order_Payment $payment,
        $amount,
        Varien_Object $stateObject,
        $transactionReferenceId,
        $parentTransactionId,
        $softDescriptor = null
    ) {
        if (!$this->canCapture()) {
            throw new Creativestyle_AmazonPayments_Exception('Capture action is not available');
        }

        $transactionDetails = $this->_getPaymentProcessor($payment)->capture(
            $amount,
            $payment->getOrder()->getBaseCurrencyCode(),
            $transactionReferenceId,
            $parentTransactionId,
            $softDescriptor
        );
        /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
        $transaction = $payment->setIsTransactionClosed(false)
            ->setTransactionId($transactionDetails['AmazonCaptureId'])
            ->setParentTransactionId($parentTransactionId)
            ->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
        $transactionInfo = $this->_fetchTransactionInfo($payment, $transaction, $stateObject, $transactionDetails);
        $this->_validateCaptureState($transactionInfo);
        if ($transactionInfo) {
            $transaction->setAdditionalInformation(
                Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
                $transactionInfo
            );
        }

        return $transaction;
    }

    /**
     * @param array|null $transactionInfo
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception_InvalidTransaction
     */
    protected function _validateCaptureState(array $transactionInfo = null)
    {
        return $this->_assertTransactionState(
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_CAPTURE,
            $transactionInfo,
            array(
                Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_PENDING,
                Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_COMPLETED,
                Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_CLOSED
            )
        );
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param $amount
     * @param Varien_Object $stateObject
     * @param $transactionReferenceId
     * @param $parentTransactionId
     * @return Mage_Sales_Model_Order_Payment_Transaction
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Creativestyle_AmazonPayments_Exception_InvalidTransaction
     * @throws Mage_Core_Exception
     * @throws Varien_Exception
     */
    protected function _refund(
        Mage_Sales_Model_Order_Payment $payment,
        $amount,
        Varien_Object $stateObject,
        $transactionReferenceId,
        $parentTransactionId
    ) {
        if (!$this->canRefund()) {
            throw new Creativestyle_AmazonPayments_Exception('Refund action is not available');
        }

        $transactionDetails = $this->_getPaymentProcessor($payment)->refund(
            $amount,
            $payment->getOrder()->getBaseCurrencyCode(),
            $transactionReferenceId,
            $parentTransactionId
        );
        /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
        $transaction = $payment->setIsTransactionClosed(false)
            ->setTransactionId($transactionDetails['AmazonRefundId'])
            ->setParentTransactionId($parentTransactionId)
            ->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND);
        $transactionInfo = $this->_fetchTransactionInfo($payment, $transaction, $stateObject, $transactionDetails);
        $this->_validateRefundState($transactionInfo);
        if ($transactionInfo) {
            $transaction->setAdditionalInformation(
                Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
                $transactionInfo
            );
        }

        return $transaction;
    }

    /**
     * @param array|null $transactionInfo
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception_InvalidTransaction
     */
    protected function _validateRefundState(array $transactionInfo = null)
    {
        return $this->_assertTransactionState(
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_REFUND,
            $transactionInfo,
            array(
                Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_PENDING,
                Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_COMPLETED
            )
        );
    }

    /**
     * Fetch payment transaction info
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param Mage_Sales_Model_Order_Payment_Transaction $transaction
     * @param Varien_Object $stateObject
     * @param array|null $transactionDetails
     * @return array|null
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Exception
     * @throws Varien_Exception
     */
    public function _fetchTransactionInfo(
        Mage_Sales_Model_Order_Payment $payment,
        Mage_Sales_Model_Order_Payment_Transaction $transaction,
        Varien_Object $stateObject,
        $transactionDetails = null
    ) {
        $transactionProcessor = $this->_getTransactionProcessor($transaction)
            ->setTransactionDetails($transactionDetails);
        $this->_getOrderProcessor($payment)->importTransactionDetails(
            $transactionProcessor,
            $stateObject,
            $this->_getCustomStatusList($transaction->getOrder()->getStoreId())
        );
        $this->_lastTransactionProcessor = $transactionProcessor;
        return $transactionProcessor->getRawDetails();
    }

    /**
     * Check whether Pay with Amazon is available
     *
     * @param Mage_Sales_Model_Quote|null $quote
     * @return bool
     * @throws Mage_Core_Model_Store_Exception
     */
    public function isAvailable($quote = null)
    {
        $checkResult = new StdClass;
        $isActive = $this->_getConfig()->isPayActive($quote ? $quote->getStoreId() : null);

        $checkResult->isDeniedInConfig = !$isActive;

        if ($quote && !$quote->validateMinimumAmount()) {
            $isActive = false;
        }

        if ($quote && Mage::app()->getStore()->roundPrice($quote->getGrandTotal()) == 0) {
            $isActive = false;
        }

        $checkResult->isAvailable = $isActive;

        Mage::dispatchEvent(
            'payment_method_is_active',
            array(
                'result' => $checkResult,
                'method_instance' => $this,
                'quote' => $quote,
            )
        );
        return $checkResult->isAvailable;
    }

    /**
     * @inheritdoc
     */
    public function getConfigData($field, $storeId = null)
    {
        switch ($field) {
            case 'payment_action':
                return $this->_getConfig()->getPaymentAction($storeId);
            default:
                return parent::getConfigData($field, $storeId);
        }
    }

    /**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param Varien_Object $stateObject
     * @return $this
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Creativestyle_AmazonPayments_Exception_InvalidTransaction
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     * @throws Varien_Exception
     */
    public function initialize($paymentAction, $stateObject)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $this->getInfoInstance();

        $this->_order(
            $payment,
            $payment->getOrder()->getBaseTotalDue(),
            $stateObject
        );

        return $this;
    }

    /**
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    public function order(Varien_Object $payment, $amount)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $this->_order($payment, $amount, new Varien_Object());
        return $this;
    }

    /**
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Creativestyle_AmazonPayments_Exception_InvalidTransaction
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $parentTransaction = $payment->lookupTransaction(false, Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER);
        if ($parentTransaction) {
            $this->_authorize(
                $payment,
                $amount,
                new Varien_Object(),
                $this->_getTransactionSequenceId($payment),
                $parentTransaction->getTxnId(),
                $this->_getConfig()->isAuthorizationSynchronous($payment->getOrder()->getStoreId()),
                $this->_getConfig()->captureImmediately($payment->getOrder()->getStoreId()),
                $this->_getConfig()->getSoftDescriptor($payment->getOrder()->getStoreId())
            );
        }

        return $this;
    }

    /**
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    public function capture(Varien_Object $payment, $amount)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $parentTransaction = $payment->getAuthorizationTransaction();
        if ($parentTransaction) {
            $this->_capture(
                $payment,
                $amount,
                new Varien_Object(),
                $this->_getTransactionSequenceId($payment),
                $parentTransaction->getTxnId(),
                $this->_getConfig()->getSoftDescriptor($payment->getOrder()->getStoreId())
            );
            $payment->setSkipTransactionCreation(true);
        }

        return $this;
    }

    /**
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Exception
     */
    public function refund(Varien_Object $payment, $amount)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $parentTransaction = $payment
            ->lookupTransaction(null, Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
        if ($parentTransaction) {
            $this->_refund(
                $payment,
                $amount,
                new Varien_Object(),
                $this->_getTransactionSequenceId($payment),
                $parentTransaction->getTxnId()
            );
            $payment->setSkipTransactionCreation(true);
        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return $this
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function processCreditmemo($creditmemo, $payment)
    {
        parent::processCreditmemo($creditmemo, $payment);
        /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
        if ($this->_lastTransactionProcessor) {
            $creditmemo->setState($this->_lastTransactionProcessor->getCreditmemoState());
        }

        return $this;
    }

    /**
     * Import transaction details
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param Mage_Sales_Model_Order_Payment_Transaction $transaction
     * @param Varien_Object $stateObject
     * @param array $transactionDetails
     * @return $this
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Exception
     * @throws Varien_Exception
     */
    public function importTransactionDetails(
        Mage_Sales_Model_Order_Payment $payment,
        Mage_Sales_Model_Order_Payment_Transaction $transaction,
        Varien_Object $stateObject,
        $transactionDetails = null
    ) {
        $transactionInfo = $this->_fetchTransactionInfo($payment, $transaction, $stateObject, $transactionDetails);
        if ($transactionInfo) {
            $transaction->setAdditionalInformation(
                Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
                $transactionInfo
            );
        }

        return $this;
    }

    /**
     * Fetch details for transaction with given ID
     *
     * @param Mage_Payment_Model_Info $payment
     * @param string $transactionId
     * @return array|null
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Exception
     * @throws Varien_Exception
     */
    public function fetchTransactionInfo(Mage_Payment_Model_Info $payment, $transactionId)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        if ($transaction = $payment->lookupTransaction($transactionId)) {
            $transactionInfo = $this->_fetchTransactionInfo($payment, $transaction, new Varien_Object());
            $payment->getOrder()
                ->addRelatedObject($transaction)
                ->save();
            return $transactionInfo;
        }

        return array();
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return $this
     * @throws Exception
     */
    public function closeOrderReference(Mage_Sales_Model_Order_Payment $payment)
    {
        $this->_getPaymentProcessor($payment)->closeOrderReference($payment->getOrder()->getExtOrderId());
        return $this;
    }

    /**
     * Can be used in regular checkout
     *
     * @return bool
     */
    public function canUseCheckout()
    {
        return $this->_getConfig()->getCheckoutType()
            === Creativestyle_AmazonPayments_Model_Lookup_CheckoutType::CHECKOUT_TYPE_ONEPAGE
            && Mage::helper('amazonpayments')->isOnePageCheckout();
    }
}
