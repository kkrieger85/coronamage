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
 * @package    Creativestyle\AmazonPayments\Model\Processor
 * @copyright  2015 - 2017 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_Model_Processor_Order
{
    const REGISTRY_KEY = 'amazon_pay_order_processor';

    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * @var array
     */
    protected $_orderAfterSaveCallbacks = array();

    /**
     * Returns Amazon Pay helper
     *
     * @return Creativestyle_AmazonPayments_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('amazonpayments');
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
     * Return Magento payment processor instance
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Creativestyle_AmazonPayments_Model_Processor_Payment
     */
    protected function _getPaymentProcessor(Mage_Sales_Model_Order_Payment $payment)
    {
        return Mage::getModel('amazonpayments/processor_payment')->setPayment($payment);
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
     * @return Mage_Sales_Model_Order_Invoice|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _getInvoice()
    {
        $invoiceCollection = $this->getOrder()->getInvoiceCollection();
        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        foreach ($invoiceCollection as $invoice) {
            return $invoice;
        }

        return null;
    }

    /**
     * @param string $transactionId
     * @return Mage_Sales_Model_Order_Creditmemo|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _getCreditmemoByTransactionId($transactionId)
    {
        $creditmemosCollection = $this->getOrder()->getCreditmemosCollection();
        /** @var Mage_Sales_Model_Order_Creditmemo $creditmemo */
        foreach ($creditmemosCollection as $creditmemo) {
            if ($creditmemo->getTransactionId() == $transactionId) {
                return $creditmemo;
            }
        }

        return null;
    }

    /**
     * Init order state object for further processing
     *
     * @param Varien_Object $stateObject
     * @return $this
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _initStateObject(Varien_Object $stateObject)
    {
        $stateObject->setData(
            array(
                'state' => $this->getOrder()->getState()
                    ? $this->getOrder()->getState()
                    : Mage_Sales_Model_Order::STATE_NEW,
                'status' => $this->getOrder()->getStatus()
                    ? $this->getOrder()->getStatus()
                    : Mage::getModel('sales/order_status')
                        ->loadDefaultByState(Mage_Sales_Model_Order::STATE_NEW)
                        ->getStatus(),
                'is_notified' => Mage_Sales_Model_Order_Status_History::CUSTOMER_NOTIFICATION_NOT_APPLICABLE
            )
        );
        return $this;
    }

    /**
     * @param Mage_Customer_Model_Address_Abstract $address
     * @param Varien_Object $newAddress
     */
    protected function _updateAddress($address, $newAddress)
    {
        if ($address) {
            if ($address->getFirstname() != $newAddress->getFirstname()) {
                $address->setFirstname($newAddress->getFirstname());
            }

            if ($address->getLastname() != $newAddress->getLastname()) {
                $address->setLastname($newAddress->getLastname());
            }

            if ($address->getCompany() != $newAddress->getCompany()) {
                $address->setCompany($newAddress->getCompany());
            }

            if ($address->getCity() != $newAddress->getCity()) {
                $address->setCity($newAddress->getCity());
            }

            if ($address->getPostcode() != $newAddress->getPostcode()) {
                $address->setPostcode($newAddress->getPostcode());
            }

            if ($address->getRegion() != $newAddress->getRegion()) {
                $address->setRegion($newAddress->getRegion());
            }

            if ($address->getCountryId() != $newAddress->getCountryId()) {
                $address->setCountryId($newAddress->getCountryId());
            }

            if ($address->getTelephone() != $newAddress->getTelephone()) {
                $address->setTelephone($newAddress->getTelephone());
            }

            $streetDiff = array_diff($address->getStreet(), $newAddress->getStreet());
            if (!empty($streetDiff)) {
                $address->setStreet($newAddress->getStreet());
            }
        }
    }

    /**
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _updateCustomerData($email, $firstname, $lastname)
    {
        if ($email && $this->getOrder()->getCustomerEmail() != $email) {
            $this->getOrder()->setCustomerEmail($email);
        }

        if ($firstname && $this->getOrder()->getCustomerFirstname() != $firstname) {
            $this->getOrder()->setCustomerFirstname($firstname);
        }

        if ($lastname && $this->getOrder()->getCustomerLastname() != $lastname) {
            $this->getOrder()->setCustomerLastname($lastname);
        }
    }

    /**
     * @param array $address
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _updateBillingAddress(array $address)
    {
        $this->_updateAddress($this->getOrder()->getBillingAddress(), new Varien_Object($address));
    }

    /**
     * @param array $address
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _updateShippingAddress(array $address)
    {
        $this->_updateAddress($this->getOrder()->getShippingAddress(), new Varien_Object($address));
    }

    /**
     * @param Varien_Object $stateObject
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _shouldUpdateOrderState(Varien_Object $stateObject)
    {
        return !$this->getOrder()->isCanceled()
            && $this->getOrder()->getState() != Mage_Sales_Model_Order::STATE_CLOSED
            && (
                $stateObject->getState() != $this->getOrder()->getState()
                || $stateObject->getStatus() != $this->getOrder()->getStatus()
            );
    }

    /**
     * @param Varien_Object $stateObject
     * @return $this
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _updateOrderState(Varien_Object $stateObject)
    {
        if ($this->_shouldUpdateOrderState($stateObject)) {
            $this->getOrder()
                ->setHoldBeforeState($stateObject->getHoldBeforeState() ? $stateObject->getHoldBeforeState() : null)
                ->setHoldBeforeStatus($stateObject->getHoldBeforeStatus() ? $stateObject->getHoldBeforeStatus() : null)
                ->setState(
                    $stateObject->getState(),
                    $stateObject->getStatus(),
                    $stateObject->getMessage(),
                    $stateObject->getIsNotified()
                );
        } elseif ($stateObject->getMessage()) {
            /** @var Mage_Sales_Model_Order_Status_History $history */
            $history = $this->getOrder()
                ->addStatusHistoryComment($stateObject->getMessage());
            $history->setIsCustomerNotified($stateObject->getIsNotified());
        }

        return $this;
    }

    /**
     * @param mixed|null $flags
     * @return $this
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _updatePaymentFlags($flags = null)
    {
        if (null !== $flags) {
            if (!is_array($flags)) {
                $flags = array($flags);
            }

            foreach ($flags as $flag) {
                $this->getPayment()->setData($flag, true);
            }
        }

        return $this;
    }

    /**
     * @param Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor
     * @return $this
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _updateTransactionDocuments(
        Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor
    ) {
        switch ($transactionProcessor->getTransactionType()) {
            case Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_CAPTURE:
                return $this->_updateInvoice($transactionProcessor);
            case Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_REFUND:
                return $this->_updateCreditmemo($transactionProcessor);
        }

        return $this;
    }

    /**
     * @param Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor
     * @return $this
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _updateInvoice(
        Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor
    ) {
        if ($this->_getInvoice()) {
            $this->getPayment()
                ->setSkipTransactionCreation(true)
                ->registerPaymentReviewAction(Mage_Sales_Model_Order_Payment::REVIEW_ACTION_UPDATE, false);
        } else {
            $this->getPayment()
                ->setTransactionId($transactionProcessor->getTransactionId())
                ->registerCaptureNotification($transactionProcessor->getTransactionAmount());
        }

        return $this;
    }

    /**
     * @param Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor
     * @return $this
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _updateCreditmemo(
        Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor
    ) {
        $creditmemo = $this->_getCreditmemoByTransactionId($transactionProcessor->getTransactionId());
        $state = $transactionProcessor->getCreditmemoState();
        if ($creditmemo && null !== $state) {
            $creditmemo->setState((int)$state);
            $this->getOrder()->addRelatedObject($creditmemo);
        }

        return $this;
    }

    /**
     * @param Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor
     * @param Varien_Object $stateObject
     * @return $this
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Exception
     */
    protected function _processChildTransactions(
        Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor,
        Varien_Object $stateObject
    ) {
        $childrenIds = $transactionProcessor->getChildrenIds();
        if ($childrenIds) {
            foreach ($childrenIds as $childTransactionId) {
                if (!$this->getPayment()->lookupTransaction($childTransactionId)
                    && $transactionProcessor->getMagentoChildTransactionType()) {
                    $this->addTransaction(
                        $transactionProcessor->getMagentoChildTransactionType(),
                        $childTransactionId,
                        $transactionProcessor->getTransactionId(),
                        $stateObject
                    );
                }
            }
        }

        return $this;
    }

    /**
     * @param Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor
     * @param Varien_Object $stateObject
     * @return $this
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Exception
     */
    protected function _processParentTransactions(
        Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor,
        Varien_Object $stateObject
    ) {
        if ($transactionProcessor->shouldUpdateParentTransaction()) {
            if ($parentTransaction = $transactionProcessor->getTransaction()->getParentTransaction()) {
                $this->getPaymentMethodInstance()
                    ->importTransactionDetails($this->getPayment(), $parentTransaction, $stateObject);
            }
        }

        if ($transactionProcessor->shouldCloseOrderTransaction()) {
            $this->getPaymentMethodInstance()->closeOrderReference($this->getPayment());
        }

        if ($transactionProcessor->getTransactionType()
            == Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_ORDER
            && $transactionProcessor->getTransactionState()
            == Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_OPEN) {
            $authTransaction = $this->getPayment()->getAuthorizationTransaction();
            if ($authTransaction && $authTransaction->getIsClosed() && ($this->getOrder()->getBaseTotalDue() > 0)) {
                $this->getPaymentMethodInstance()
                    ->setStore($this->getStoreId())
                    ->authorize($this->getPayment(), $this->getOrder()->getBaseTotalDue());
            }
        }

        if ($transactionProcessor->getTransactionType()
            == Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_AUTH
            && $transactionProcessor->getTransactionState()
            == Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_DECLINED
            && $transactionProcessor->getTransactionReasonCode()
            == Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_REASON_TIMEOUT
            && !$transactionProcessor->isSync()) {
            $this->_getPaymentProcessor($this->getPayment())->cancelOrderReference();
        }

        return $this;
    }

    /**
     * @param Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor
     * @param Varien_Object $stateObject
     * @return $this
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _sendTransactionalEmails($transactionProcessor, $stateObject)
    {
        switch ($transactionProcessor->getTransactionalEmailToSend()) {
            case Creativestyle_AmazonPayments_Model_Processor_Transaction::EMAIL_TYPE_NEW_ORDER:
                if (!$this->getOrder()->getEmailSent()) {
                    $this->getOrder()->sendNewOrderEmail();
                    $stateObject->setIsNotified(true);
                }
                break;
            case Creativestyle_AmazonPayments_Model_Processor_Transaction::EMAIL_TYPE_AUTH_DECLINED:
                $this->_getHelper()->sendAuthorizationDeclinedEmail($this->getOrder());
                $stateObject->setIsNotified(true);
                break;
        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return $this
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Returns order instance
     *
     * @return Mage_Sales_Model_Order
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getOrder()
    {
        if (null === $this->_order) {
            throw new Creativestyle_AmazonPayments_Exception('[proc::Order] Order object is not set');
        }

        return $this->_order;
    }

    /**
     * @return Mage_Sales_Model_Order_Payment
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getPayment()
    {
        return $this->getOrder()->getPayment();
    }

    /**
     * @return Creativestyle_AmazonPayments_Model_Payment_Abstract
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Exception
     */
    public function getPaymentMethodInstance()
    {
        return $this->getPayment()->getMethodInstance();
    }

    /**
     * Returns order's store ID
     *
     * @return int
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getStoreId()
    {
        return $this->getOrder()->getStoreId();
    }

    /**
     * @param string $transactionType
     * @param string $transactionId
     * @param string $parentTransactionId
     * @param Varien_Object $stateObject
     * @return $this
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Exception
     */
    public function addTransaction(
        $transactionType,
        $transactionId,
        $parentTransactionId,
        Varien_Object $stateObject
    ) {
        /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
        $transaction = $this->getPayment()->setIsTransactionClosed(false)
            ->setTransactionId($transactionId)
            ->setParentTransactionId($parentTransactionId)
            ->addTransaction($transactionType);

        $this->getPaymentMethodInstance()
            ->importTransactionDetails($this->getPayment(), $transaction, $stateObject);

        return $this;
    }

    /**
     * @param Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor
     * @param Varien_Object $stateObject
     * @param Varien_Object $customStatus
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Exception
     */
    public function importTransactionDetails(
        Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor,
        Varien_Object $stateObject,
        Varien_Object $customStatus
    ) {
        if ($transactionProcessor->shouldUpdateTransaction()) {
            $this->_initStateObject($stateObject);

            if ($transactionProcessor->shouldUpdateOrderData()) {
                $this->_updateCustomerData(
                    $transactionProcessor->getCustomerEmail(),
                    $transactionProcessor->getCustomerFirstname(),
                    $transactionProcessor->getCustomerLastname()
                );
                $shippingAddress = $transactionProcessor->getMagentoShippingAddress();
                $billingAddress = $transactionProcessor->getMagentoBillingAddress();

                if ($shippingAddress) {
                    $this->_updateShippingAddress($shippingAddress);
                    // use shipping address for billing, if billing address is empty
                    if (!$billingAddress) {
                        $billingAddress = $shippingAddress;
                    }
                }

                if ($billingAddress) {
                    $this->_updateBillingAddress($billingAddress);
                }
            }

            $transactionProcessor->generateMagentoOrderStateObject($stateObject, $customStatus);
            $this->_updatePaymentFlags($transactionProcessor->getPaymentFlags())
                ->_updateTransactionDocuments($transactionProcessor)
                ->_sendTransactionalEmails($transactionProcessor, $stateObject)
                ->_updateOrderState($stateObject)
                ->_processChildTransactions($transactionProcessor, $stateObject)
                ->_processParentTransactions($transactionProcessor, $stateObject);
        }
    }
}
