<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2017 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @package    Creativestyle\AmazonPayments\Model\Processor
 * @copyright  2017 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_Model_Processor_Transaction_DataMapper
{
    protected $_transactionTypeMap = array(
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_ORDER
            => Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER,
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_AUTH
            => Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_CAPTURE
            => Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_REFUND
            => Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND
    );

    protected $_magentoChildTransactionTypeMap = array(
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_ORDER
            => Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_AUTH
            => Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_CAPTURE
            => Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND
    );

    protected $_shouldUpdateOrderDataMap = array(
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_ORDER => array(
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_OPEN,
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_SUSPENDED,
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_CANCELED
        ),
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_AUTH => true
    );

    protected $_orderStateMap = array(
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_ORDER => array(
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_OPEN
                => Mage_Sales_Model_Order::STATE_NEW
        ),
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_AUTH => array(
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_PENDING
                => Mage_Sales_Model_Order::STATE_NEW,
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_OPEN
                => Mage_Sales_Model_Order::STATE_PROCESSING,
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_DECLINED
                => Mage_Sales_Model_Order::STATE_HOLDED,
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_CLOSED => array(
                Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_REASON_MAX_CAPTURES
                    => Mage_Sales_Model_Order::STATE_PROCESSING
            )
        ),
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_CAPTURE => array(
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_DECLINED
                => Mage_Sales_Model_Order::STATE_HOLDED,
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_COMPLETED
                => Mage_Sales_Model_Order::STATE_PROCESSING,
        ),
    );

    protected $_emailToSendMap = array(
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_AUTH => array(
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_OPEN
                => Creativestyle_AmazonPayments_Model_Processor_Transaction::EMAIL_TYPE_NEW_ORDER,
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_CLOSED
                => Creativestyle_AmazonPayments_Model_Processor_Transaction::EMAIL_TYPE_NEW_ORDER,
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_DECLINED => array(
                Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_REASON_INVALID_PAYMENT
                => Creativestyle_AmazonPayments_Model_Processor_Transaction::EMAIL_TYPE_AUTH_DECLINED
            )
        )
    );

    protected $_paymentFlagsMap = array(
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_CAPTURE => array(
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_PENDING
                => 'is_transaction_pending',
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_DECLINED => array(
                Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_REASON_AMAZON_REJECTED
                    => 'is_transaction_pending,is_transaction_denied',
                Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_REASON_PROCESSING_FAILURE
                    => 'is_transaction_pending'
            ),
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_COMPLETED
                => 'is_transaction_approved'
        )
    );

    protected $_invoiceStateMap = array(
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_CAPTURE => array(
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_PENDING
                => Mage_Sales_Model_Order_Invoice::STATE_OPEN,
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_DECLINED => array(
                Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_REASON_AMAZON_REJECTED
                    => Mage_Sales_Model_Order_Invoice::STATE_CANCELED,
                Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_REASON_PROCESSING_FAILURE
                    => Mage_Sales_Model_Order_Invoice::STATE_OPEN
            ),
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_COMPLETED
                => Mage_Sales_Model_Order_Invoice::STATE_PAID
        ),
    );

    protected $_creditmemoStateMap = array(
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_REFUND => array(
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_DECLINED
                => Mage_Sales_Model_Order_Creditmemo::STATE_CANCELED,
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_PENDING
                => Mage_Sales_Model_Order_Creditmemo::STATE_OPEN,
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_COMPLETED
                => Mage_Sales_Model_Order_Creditmemo::STATE_REFUNDED
        ),
    );

    protected $_shouldCloseTransactionMap = array(
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_DECLINED,
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_CANCELED,
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_CLOSED,
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_COMPLETED
    );

    protected $_shouldUpdateParentTransactionMap = array(
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_AUTH => array(
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_DECLINED => true
        )
    );

    protected $_shouldCloseOrderTransactionMap = array(
        Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_CAPTURE => array(
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_COMPLETED => true
        )
    );

    /**
     * Converts CamelCase string to snake_case string
     *
     * @param string $input
     * @return string
     */
    protected function _underscore($input)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * @param array $map
     * @param string $transactionType
     * @param string $transactionState
     * @param string|null $transactionReasonCode
     * @return null|mixed
     */
    protected function _getMapTarget(
        array $map,
        $transactionType,
        $transactionState,
        $transactionReasonCode = null
    ) {
        if (array_key_exists($transactionType, $map)) {
            $transactionTypeTargets = $map[$transactionType];
            if (array_key_exists($transactionState, $transactionTypeTargets)) {
                $transactionStateTargets = $transactionTypeTargets[$transactionState];
                if (is_array($transactionStateTargets)) {
                    if ($transactionReasonCode && array_key_exists($transactionReasonCode, $transactionStateTargets)) {
                        return $transactionStateTargets[$transactionReasonCode];
                    }

                    return null;
                }

                return $transactionStateTargets;
            }
        }

        return null;
    }

    /**
     * Check whether given address lines contain PO Box data
     *
     * @param string $firstAddressLine
     * @param string|null $secondAddressLine
     *
     * @return bool
     */
    protected function _isPoBox($firstAddressLine, $secondAddressLine = null)
    {
        if (is_numeric($firstAddressLine)) {
            return true;
        }

        if (strpos(strtolower($firstAddressLine), 'packstation') !== false) {
            return true;
        }

        if (strpos(strtolower($secondAddressLine), 'packstation') !== false) {
            return true;
        }

        return false;
    }

    /**
     * @param string $transactionType
     * @param string|null $transactionState
     * @param string|null $transactionReasonCode
     * @return string|null
     */
    protected function _getOrderStateByTransaction(
        $transactionType,
        $transactionState,
        $transactionReasonCode = null
    ) {
        return $this->_getMapTarget($this->_orderStateMap, $transactionType, $transactionState, $transactionReasonCode);
    }

    /**
     * @param string $state
     * @param Varien_Object|null $customStatus
     * @return string
     */
    protected function _getStatusByState($state, $customStatus = null)
    {
        $defaultStatus = Mage::getModel('sales/order_status')->loadDefaultByState($state)->getStatus();
        if ($customStatus) {
            switch ($state) {
                case Mage_Sales_Model_Order::STATE_NEW:
                    return $customStatus->getNewOrderStatus() ? $customStatus->getNewOrderStatus() : $defaultStatus;
                case Mage_Sales_Model_Order::STATE_PROCESSING:
                    return $customStatus->getAuthorizedOrderStatus()
                        ? $customStatus->getAuthorizedOrderStatus() : $defaultStatus;
                case Mage_Sales_Model_Order::STATE_HOLDED:
                    return $customStatus->getHoldedOrderStatus()
                        ? $customStatus->getHoldedOrderStatus() : $defaultStatus;
            }
        }

        return $defaultStatus;
    }

    /**
     * @param string $state
     * @param Varien_Object|null $customStatus
     * @param string|null $transactionType
     * @param string|null $transactionState
     * @param string|null $transactionReasonCode
     * @return string
     */
    protected function _getStatusByStateAndTransaction(
        $state,
        $customStatus = null,
        $transactionType = null,
        $transactionState = null,
        $transactionReasonCode = null
    ) {
        $stateStatus = $this->_getStatusByState($state, $customStatus);
        if ($customStatus) {
            $transactionData = array();
            if ($transactionReasonCode) {
                $transactionData[] = $this->_underscore($transactionReasonCode);
            }

            if ($transactionState) {
                $transactionData[] = $this->_underscore($transactionState);
            }

            if ($transactionType) {
                $transactionData[] = $this->_underscore($transactionType);
            }

            $orderStatusCode = join('_', $transactionData) . '_order_status';
            return $customStatus->getData($orderStatusCode) ? $customStatus->getData($orderStatusCode) : $stateStatus;
        }

        return $stateStatus;
    }

    /**
     * @param $message
     * @param Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor
     * @return string
     */
    protected function _formatTransactionUpdateMessage(
        $message,
        Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor
    ) {
        return Mage::helper('amazonpayments')->__(
            $message,
            $transactionProcessor->getFormattedTransactionAmount(),
            $transactionProcessor->getTransactionId(),
            sprintf(
                '<strong>%s</strong>%s',
                $transactionProcessor->getTransactionState(),
                $transactionProcessor->getTransactionReasonCode()
                    ? ' (' . $transactionProcessor->getTransactionReasonCode() . ')' : ''
            )
        );
    }

    // @codingStandardsIgnoreStart
    /**
     * @param array $addressLines
     * @param string|null $countryId
     * @return array
     */
    protected function _mapAmazonAddressLines(array $addressLines = array(), $countryId = null)
    {
        $address = array('street' => array());
        switch ($countryId) {
            case 'DE':
            case 'AT':
                if (isset($addressLines[2]) && $addressLines[2]) {
                    if ($this->_isPoBox($addressLines[0], $addressLines[1])) {
                        $address['street'][] = $addressLines[0];
                        $address['street'][] = $addressLines[1];
                    } else {
                        $address['company'] = trim($addressLines[0] . ' ' . $addressLines[1]);
                    }
                    $address['street'][] = $addressLines[2];
                } elseif (isset($addressLines[1]) && $addressLines[1]) {
                    if ($this->_isPoBox($addressLines[0])) {
                        $address['street'][] = $addressLines[0];
                    } else {
                        $address['company'] = $addressLines[0];
                    }
                    $address['street'][] = $addressLines[1];
                } elseif (isset($addressLines[0]) && $addressLines[0]) {
                    $address['street'][] = $addressLines[0];
                }
                break;
            default:
                if (isset($addressLines[0]) && $addressLines[0]) {
                    $address['street'][] = $addressLines[0];
                }
                if (isset($addressLines[1]) && $addressLines[1]) {
                    $address['street'][] = $addressLines[1];
                }
                if (isset($addressLines[2]) && $addressLines[2]) {
                    $address['street'][] = $addressLines[2];
                }
                break;
        }
        return $address;
    }
    // @codingStandardsIgnoreEnd

    /**
     * @param Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor
     * @param Varien_Object|null $stateObject
     * @param Varien_Object|null $customStatus
     * @return Varien_Object
     */
    public function generateMagentoOrderStateObject(
        Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor,
        $stateObject = null,
        $customStatus = null
    ) {
        if (null === $stateObject) {
            $stateObject = new Varien_Object();
        }

        $orderState = $this->_getOrderStateByTransaction(
            $transactionProcessor->getTransactionType(),
            $transactionProcessor->getTransactionState(),
            $transactionProcessor->getTransactionReasonCode()
        );

        if ($orderState) {
            $stateObjectData = array(
                'state' => $orderState,
                'status' => $this->_getStatusByStateAndTransaction(
                    $orderState,
                    $customStatus,
                    $transactionProcessor->getTransactionType(),
                    $transactionProcessor->getTransactionState(),
                    $transactionProcessor->getTransactionReasonCode()
                )
            );
            if ($orderState == Mage_Sales_Model_Order::STATE_HOLDED) {
                $stateObjectData['hold_before_state'] = $stateObject->getState();
                $stateObjectData['hold_before_status'] = $stateObject->getStatus();
            }

            $stateObject->setData($stateObjectData);
        }

        $message = null;
        switch ($transactionProcessor->getTransactionType()) {
            case Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_ORDER:
                $message = 'An order of %s has been processed by Amazon Payments (%s). The new status is %s.';
                break;
            case Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_AUTH:
                $message = 'An authorization of %s has been processed by Amazon Payments (%s). The new status is %s.';
                break;
            case Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_CAPTURE:
                $message = 'A capture of %s has been processed by Amazon Payments (%s). The new status is %s.';
                break;
            case Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_REFUND:
                $message = 'A refund of %s has been processed by Amazon Payments (%s). The new status is %s.';
                break;
        }

        if ($message) {
            $stateObject->setMessage($this->_formatTransactionUpdateMessage($message, $transactionProcessor));
        }

        return $stateObject;
    }

    /**
     * @param string $transactionType
     * @param string|null $transactionState
     * @param string|null $transactionReasonCode
     * @return string|null
     */
    public function getCreditmemoState(
        $transactionType,
        $transactionState,
        $transactionReasonCode = null
    ) {
        return $this->_getMapTarget(
            $this->_creditmemoStateMap,
            $transactionType,
            $transactionState,
            $transactionReasonCode
        );
    }

    /**
     * @param string $transactionType
     * @param string|null $transactionState
     * @param string|null $transactionReasonCode
     * @return string|null
     */
    public function getInvoiceState(
        $transactionType,
        $transactionState,
        $transactionReasonCode = null
    ) {
        return $this->_getMapTarget(
            $this->_invoiceStateMap,
            $transactionType,
            $transactionState,
            $transactionReasonCode
        );
    }

    /**
     * @param string $transactionType
     * @param string|null $transactionState
     * @param string|null $transactionReasonCode
     * @return array|null
     */
    public function getPaymentFlags(
        $transactionType,
        $transactionState,
        $transactionReasonCode = null
    ) {
        $flags = $this->_getMapTarget(
            $this->_paymentFlagsMap,
            $transactionType,
            $transactionState,
            $transactionReasonCode
        );
        return $flags ? explode(',', $flags) : $flags;
    }

    /**
     * Returns Amazon transaction type for corresponding Magento transaction type
     *
     * @param string $transactionType
     * @return string|null
     */
    public function getAmazonTransactionType($transactionType)
    {
        $transactionTypes = array_flip($this->_transactionTypeMap);
        if (array_key_exists($transactionType, $transactionTypes)) {
            return $transactionTypes[$transactionType];
        }

        return null;
    }

    /**
     * @param string $transactionType
     * @param string|null $transactionState
     * @param string|null $transactionReasonCode
     * @return string|null
     */
    public function getTransactionalEmailToSend(
        $transactionType,
        $transactionState,
        $transactionReasonCode = null
    ) {
        return $this->_getMapTarget(
            $this->_emailToSendMap,
            $transactionType,
            $transactionState,
            $transactionReasonCode
        );
    }

    /**
     * @param string $transactionType
     * @param string $transactionState
     * @return bool
     */
    public function shouldUpdateOrderData($transactionType, $transactionState)
    {
        if (array_key_exists($transactionType, $this->_shouldUpdateOrderDataMap)) {
            if (is_array($this->_shouldUpdateOrderDataMap[$transactionType])) {
                if (in_array($transactionState, $this->_shouldUpdateOrderDataMap[$transactionType])) {
                    return true;
                }

                return false;
            }

            return (bool)$this->_shouldUpdateOrderDataMap[$transactionType];
        }

        return false;
    }

    /**
     * @param string $transactionState
     * @return bool
     */
    public function shouldCloseTransaction($transactionState)
    {
        return in_array($transactionState, $this->_shouldCloseTransactionMap);
    }

    /**
     * @param string $transactionType
     * @param string|null $transactionState
     * @param string|null $transactionReasonCode
     * @return bool
     */
    public function shouldUpdateParentTransaction(
        $transactionType,
        $transactionState,
        $transactionReasonCode = null
    ) {
        return (bool)$this->_getMapTarget(
            $this->_shouldUpdateParentTransactionMap,
            $transactionType,
            $transactionState,
            $transactionReasonCode
        );
    }

    /**
     * @param string $transactionType
     * @param string|null $transactionState
     * @param string|null $transactionReasonCode
     * @return bool
     */
    public function shouldCloseOrderTransaction(
        $transactionType,
        $transactionState,
        $transactionReasonCode = null
    ) {
        return (bool)$this->_getMapTarget(
            $this->_shouldCloseOrderTransactionMap,
            $transactionType,
            $transactionState,
            $transactionReasonCode
        );
    }

    /**
     * Maps transaction details to Magento billing address array
     *
     * @param Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor
     * @return array|null
     */
    public function getBillingAddress(Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor)
    {
        $addressLines = $transactionProcessor->getBillingAddressLines();
        $countryCode = $transactionProcessor->getBillingAddressCountryCode();
        if ($addressLines && $countryCode) {
            return array_merge(
                array(
                    'firstname' => $transactionProcessor->getBillingAddressFirstname(),
                    'lastname' => $transactionProcessor->getBillingAddressLastname(),
                ),
                $this->_mapAmazonAddressLines($addressLines, $countryCode),
                array(
                    'country_id' => $countryCode,
                    'city' => $transactionProcessor->getBillingAddressCity(),
                    'postcode' => $transactionProcessor->getBillingAddressPostalCode(),
                    'region' => $transactionProcessor->getBillingAddressRegion(),
                    'telephone' => $transactionProcessor->getBillingAddressPhone()
                )
            );
        }

        return null;
    }

    /**
     * Maps transaction details to Magento shipping address array
     *
     * @param Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor
     * @return array|null
     */
    public function getShippingAddress(Creativestyle_AmazonPayments_Model_Processor_Transaction $transactionProcessor)
    {
        $addressLines = $transactionProcessor->getShippingAddressLines();
        $countryCode = $transactionProcessor->getShippingAddressCountryCode();
        if ($addressLines && $countryCode) {
            return array_merge(
                array(
                    'firstname' => $transactionProcessor->getShippingAddressFirstname(),
                    'lastname' => $transactionProcessor->getShippingAddressLastname(),
                ),
                $this->_mapAmazonAddressLines($addressLines, $countryCode),
                array(
                    'country_id' => $countryCode,
                    'city' => $transactionProcessor->getShippingAddressCity(),
                    'postcode' => $transactionProcessor->getShippingAddressPostalCode(),
                    'region' => $transactionProcessor->getShippingAddressRegion(),
                    'telephone' => $transactionProcessor->getShippingAddressPhone()
                )
            );
        }

        return null;
    }

    /**
     * @param string $transactionType
     * @return string|null
     */
    public function getMagentoChildTransactionType($transactionType)
    {
        if (array_key_exists($transactionType, $this->_magentoChildTransactionTypeMap)) {
            return $this->_magentoChildTransactionTypeMap[$transactionType];
        }

        return null;
    }
}
