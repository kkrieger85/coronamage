<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2017 - 2018 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @package    Creativestyle\AmazonPayments\Model\Processor
 * @copyright  2017 - 2018 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_Model_Processor_Transaction
{
    const TRANSACTION_TYPE_ORDER                = 'OrderReference';
    const TRANSACTION_TYPE_AUTH                 = 'Authorization';
    const TRANSACTION_TYPE_CAPTURE              = 'Capture';
    const TRANSACTION_TYPE_REFUND               = 'Refund';

    const TRANSACTION_STATE_KEY                 = 'State';
    const TRANSACTION_REASON_CODE_KEY           = 'ReasonCode';
    const TRANSACTION_REASON_DESCRIPTION_KEY    = 'ReasonDescription';
    const TRANSACTION_ORDER_LANGUAGE_KEY        = 'Language';

    const TRANSACTION_STATE_DRAFT               = 'Draft';
    const TRANSACTION_STATE_PENDING             = 'Pending';
    const TRANSACTION_STATE_OPEN                = 'Open';
    const TRANSACTION_STATE_SUSPENDED           = 'Suspended';
    const TRANSACTION_STATE_DECLINED            = 'Declined';
    const TRANSACTION_STATE_COMPLETED           = 'Completed';
    const TRANSACTION_STATE_CANCELED            = 'Canceled';
    const TRANSACTION_STATE_CLOSED              = 'Closed';

    const TRANSACTION_REASON_INVALID_PAYMENT    = 'InvalidPaymentMethod';
    const TRANSACTION_REASON_TIMEOUT            = 'TransactionTimedOut';
    const TRANSACTION_REASON_AMAZON_REJECTED    = 'AmazonRejected';
    const TRANSACTION_REASON_AMAZON_CLOSED      = 'AmazonClosed';
    const TRANSACTION_REASON_EXPIRED_UNUSED     = 'ExpiredUnused';
    const TRANSACTION_REASON_PROCESSING_FAILURE = 'ProcessingFailure';
    const TRANSACTION_REASON_MAX_CAPTURES       = 'MaxCapturesProcessed';

    const EMAIL_TYPE_NEW_ORDER                  = 'new_order';
    const EMAIL_TYPE_AUTH_DECLINED              = 'auth_declined';

    /**
     * @var Mage_Sales_Model_Order_Payment_Transaction|null
     */
    protected $_transaction = null;

    /**
     * @var array|null
     */
    protected $_transactionDetails = null;

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
     * Returns Amazon Pay helper
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
     * @param string $amount
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _formatAmount($amount = null)
    {
        return $amount ? $this->getTransaction()->getOrder()->getBaseCurrency()->formatTxt($amount) : null;
    }

    /**
     * Returns order's store ID
     *
     * @return int
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _getStoreId()
    {
        return $this->getTransaction()->getOrder()->getStoreId();
    }

    /**
     * Returns Magento transaction type
     *
     * @return string
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _getMagentoTransactionType()
    {
        return $this->getTransaction()->getTxnType();
    }

    /**
     * @return int
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _getTransactionAgeInDays()
    {
        /** @var Mage_Core_Model_Date $dateModel */
        $dateModel = Mage::getModel('core/date');
        $txnAge = ($dateModel->timestamp() - $dateModel->timestamp($this->getTransaction()->getCreatedAt()))
            / (60 * 60 * 24);
        return (int)floor($txnAge);
    }

    /**
     * Returns Magento transaction additional information
     *
     * @param string|null $key
     * @return array|null|string
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _getMagentoTransactionAdditionalInformation($key = null)
    {
        $additionalInformation = $this->getTransaction()
            ->getAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS);
        if (null !== $key) {
            if (is_array($additionalInformation) && array_key_exists($key, $additionalInformation)) {
                return $additionalInformation[$key];
            }

            return null;
        }

        return $additionalInformation;
    }

    /**
     * Returns transaction details
     *
     * Returns details for the transaction, if not set then retrieves
     * the details from Amazon Payments API.
     *
     * @return array|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _getAmazonTransactionDetails()
    {
        if (null == $this->_transactionDetails) {
            $this->setTransactionDetails($this->_fetchTransactionDetails());
        }

        return $this->_transactionDetails;
    }

    /**
     * @return array|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _getAmazonTransactionStatus()
    {
        $transactionDetails = $this->_getAmazonTransactionDetails();

        if (isset($transactionDetails[$this->getTransactionType() . 'Status'])) {
            return $transactionDetails[$this->getTransactionType() . 'Status'];
        }

        return null;
    }

    /**
     * @return array|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _getAmazonBuyer()
    {
        $transactionDetails = $this->_getAmazonTransactionDetails();
        return isset($transactionDetails['Buyer']) ? $transactionDetails['Buyer'] : null;
    }

    /**
     * @return array|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _getAmazonBillingAddress()
    {
        $transactionDetails = $this->_getAmazonTransactionDetails();
        if (isset($transactionDetails['BillingAddress'])) {
            $billingAddress = $transactionDetails['BillingAddress'];
            if (isset($billingAddress['PhysicalAddress'])) {
                return $billingAddress['PhysicalAddress'];
            }
        } elseif (isset($transactionDetails['AuthorizationBillingAddress'])) {
            return $transactionDetails['AuthorizationBillingAddress'];
        }

        return null;
    }

    /**
     * @return array|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _getAmazonShippingAddress()
    {
        $transactionDetails = $this->_getAmazonTransactionDetails();
        if (isset($transactionDetails['Destination'])) {
            $destination = $transactionDetails['Destination'];
            if (isset($destination['PhysicalDestination'])) {
                return $destination['PhysicalDestination'];
            }
        }

        return null;
    }

    /**
     * @return string|array|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _getAmazonIdList()
    {
        $transactionDetails = $this->_getAmazonTransactionDetails();
        if (isset($transactionDetails['IdList'])) {
            $idList = $transactionDetails['IdList'];
            if (isset($idList['member'])) {
                return is_array($idList['member']) ? $idList['member'] : array($idList['member']);
            } elseif (isset($idList['Id'])) {
                return is_array($idList['Id']) ? $idList['Id'] : array($idList['Id']);
            }
        }

        return null;
    }

    /**
     * Extracts city name from given Amazon Pay address object
     *
     * @param array|null $address
     * @return string|null
     */
    protected function _extractCityFromAmazonAddress($address = null)
    {
        return $address && isset($address['City']) ? $address['City'] : null;
    }

    /**
     * Extracts region from given Amazon Pay address object
     *
     * @param array|null $address
     * @return string|null
     */
    protected function _extractRegionFromAmazonAddress($address = null)
    {
        return $address && isset($address['StateOrRegion']) ? $address['StateOrRegion'] : null;
    }

    /**
     * Extracts country code from given Amazon Pay address object
     *
     * @param array|null $address
     * @return string|null
     */
    protected function _extractCountryCodeFromAmazonAddress($address = null)
    {
        return $address && isset($address['CountryCode']) ? $address['CountryCode'] : null;
    }

    /**
     * Extracts customer name from given Amazon Pay address object
     *
     * @param array|null $address
     * @return Varien_Object|null
     */
    protected function _extractCustomerNameFromAmazonAddress($address = null)
    {
        return $address && isset($address['Name'])
            ? $this->_getHelper()->explodeCustomerName($address['Name'], null) : null;
    }

    /**
     * Extracts address lines from given Amazon Pay address object
     *
     * @param array|null $address
     * @return array|null
     */
    protected function _extractLinesFromAmazonAddress($address = null)
    {
        if ($address) {
            return array(
                isset($address['AddressLine1']) && !empty($address['AddressLine1']) ? $address['AddressLine1'] : null,
                isset($address['AddressLine2']) && !empty($address['AddressLine2']) ? $address['AddressLine2'] : null,
                isset($address['AddressLine3']) && !empty($address['AddressLine3']) ? $address['AddressLine3'] : null
            );
        }

        return null;
    }

    /**
     * Extracts phone from given Amazon Pay address object
     *
     * @param array|null $address
     * @return string|null
     */
    protected function _extractPhoneFromAmazonAddress($address = null)
    {
        return $address && isset($address['Phone']) ? $address['Phone'] : null;
    }

    /**
     * Extracts postal code from given Amazon Pay address object
     *
     * @param array|null $address
     * @return string|null
     */
    protected function _extractPostalCodeFromAmazonAddress($address = null)
    {
        return $address && isset($address['PostalCode']) ? $address['PostalCode'] : null;
    }

    /**
     * Retrieve transaction details from Amazon Payments API
     *
     * Retrieves details for provided Magento transaction object using
     * Amazon Payments API client. Before making a call, identifies the
     * type of provided transaction type by using appropriate function.
     *
     * @return array|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _fetchTransactionDetails()
    {
        // @codingStandardsIgnoreStart
        return call_user_func(
            array($this->_getApi(), 'get' . $this->getTransactionType() . 'Details'),
            $this->_getStoreId(), $this->getTransactionId()
        );
        // @codingStandardsIgnoreEnd
    }

    /**
     * Returns Amazon to Magento transaction data mapper
     *
     * @return Creativestyle_AmazonPayments_Model_Processor_Transaction_DataMapper
     */
    public function getDataMapper()
    {
        /** @var Creativestyle_AmazonPayments_Model_Processor_Transaction_DataMapper $dataMapper */
        $dataMapper = Mage::getSingleton('amazonpayments/processor_transaction_dataMapper');
        return $dataMapper;
    }

    /**
     * Sets previously fetched transaction details
     *
     * @param Mage_Sales_Model_Order_Payment_Transaction $transaction
     * @return $this
     */
    public function setTransaction($transaction)
    {
        $this->_transaction = $transaction;
        return $this;
    }

    /**
     * Returns transaction instance
     *
     * @return Mage_Sales_Model_Order_Payment_Transaction
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getTransaction()
    {
        if (null === $this->_transaction) {
            throw new Creativestyle_AmazonPayments_Exception('[proc::Transaction] Transaction object is not set');
        }

        return $this->_transaction;
    }

    /**
     * Sets previously fetched transaction details
     *
     * @param array $transactionDetails
     * @return $this
     */
    public function setTransactionDetails($transactionDetails)
    {
        $this->_transactionDetails = $transactionDetails;
        return $this;
    }

    /**
     * Returns transaction ID
     *
     * @return string
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getTransactionId()
    {
        return $this->getTransaction()->getTxnId();
    }

    /**
     * Returns Amazon Pay-specific name for Magento transaction type
     *
     * Checks the type of provided payment transaction object and
     * returns its corresponding Amazon transaction name. Returns
     * null if type of provided transaction object is neither
     * recognized nor has an Amazon Pay equivalent.
     *
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getTransactionType()
    {
        return $this->getDataMapper()->getAmazonTransactionType($this->_getMagentoTransactionType());
    }

    /**
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getTransactionState()
    {
        $transactionStatus = $this->_getAmazonTransactionStatus();
        return $transactionStatus && isset($transactionStatus['State']) ? $transactionStatus['State'] : null;
    }

    /**
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getTransactionReasonCode()
    {
        $transactionStatus = $this->_getAmazonTransactionStatus();
        return $transactionStatus && isset($transactionStatus['ReasonCode']) ? $transactionStatus['ReasonCode'] : null;
    }

    /**
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getTransactionReasonDescription()
    {
        $transactionStatus = $this->_getAmazonTransactionStatus();
        return $transactionStatus && isset($transactionStatus['ReasonDescription'])
            ? $transactionStatus['ReasonDescription'] : null;
    }

    /**
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getTransactionOrderLanguage()
    {
        $transactionDetails = $this->_getAmazonTransactionDetails();

        if (isset($transactionDetails['OrderLanguage'])) {
            return $transactionDetails['OrderLanguage'];
        }

        return null;
    }

    /**
     * Returns transaction amount
     *
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getTransactionAmount()
    {
        $transactionDetails = $this->_getAmazonTransactionDetails();
        if (isset($transactionDetails[$this->getTransactionType() . 'Amount'])) {
            $transactionAmount = $transactionDetails[$this->getTransactionType() . 'Amount'];
            if (isset($transactionAmount['Amount'])) {
                return $transactionAmount['Amount'];
            }
        }

        if (isset($transactionDetails['OrderTotal'])) {
            $transactionAmount = $transactionDetails['OrderTotal'];
            if (isset($transactionAmount['Amount'])) {
                return $transactionAmount['Amount'];
            }
        }

        return null;
    }

    /**
     * Returns transaction amount
     *
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getFormattedTransactionAmount()
    {
        return $this->_formatAmount($this->getTransactionAmount());
    }

    /**
     * @return string|array|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getChildrenIds()
    {
        return $this->_getAmazonIdList();
    }

    /**
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getCustomerEmail()
    {
        $buyer = $this->_getAmazonBuyer();
        return $buyer && isset($buyer['Email']) ? $buyer['Email'] : null;
    }

    /**
     * @return Varien_Object|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getCustomerName()
    {
        if ($customerName = $this->_extractCustomerNameFromAmazonAddress($this->_getAmazonBillingAddress())) {
            return $customerName;
        }

        $buyer = $this->_getAmazonBuyer();
        return $buyer && isset($buyer['Name'])
            ? $this->_getHelper()->explodeCustomerName($buyer['Name'], null) : null;
    }

    /**
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getCustomerFirstname()
    {
        $customerName = $this->getCustomerName();
        return $customerName  ? $customerName->getData('firstname') : null;
    }

    /**
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getCustomerLastname()
    {
        $customerName = $this->getCustomerName();
        return $customerName  ? $customerName->getData('lastname') : null;
    }

    /**
     * Returns customer first name for billing address
     *
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getBillingAddressFirstname()
    {
        $customerName = $this->_extractCustomerNameFromAmazonAddress($this->_getAmazonBillingAddress());
        return $customerName ? $customerName->getData('firstname') : null;
    }

    /**
     * Returns customer last name for billing address
     *
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getBillingAddressLastname()
    {
        $customerName = $this->_extractCustomerNameFromAmazonAddress($this->_getAmazonBillingAddress());
        return $customerName ? $customerName->getData('lastname') : null;
    }

    /**
     * Returns billing address street lines
     *
     * @return array|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getBillingAddressLines()
    {
        return $this->_extractLinesFromAmazonAddress($this->_getAmazonBillingAddress());
    }

    /**
     * Returns billing address city
     *
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getBillingAddressCity()
    {
        return $this->_extractCityFromAmazonAddress($this->_getAmazonBillingAddress());
    }

    /**
     * Returns billing address postal code
     *
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getBillingAddressPostalCode()
    {
        return $this->_extractPostalCodeFromAmazonAddress($this->_getAmazonBillingAddress());
    }

    /**
     * Returns billing address region
     *
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getBillingAddressRegion()
    {
        return $this->_extractRegionFromAmazonAddress($this->_getAmazonBillingAddress());
    }

    /**
     * Returns billing address country code
     *
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getBillingAddressCountryCode()
    {
        return $this->_extractCountryCodeFromAmazonAddress($this->_getAmazonBillingAddress());
    }

    /**
     * Returns customer phone for billing address
     *
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getBillingAddressPhone()
    {
        return $this->_extractPhoneFromAmazonAddress($this->_getAmazonBillingAddress());
    }

    /**
     * Returns customer first name for shipping address
     *
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getShippingAddressFirstname()
    {
        $customerName = $this->_extractCustomerNameFromAmazonAddress($this->_getAmazonShippingAddress());
        return $customerName ? $customerName->getData('firstname') : null;
    }

    /**
     * Returns customer last name for shipping address
     *
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getShippingAddressLastname()
    {
        $customerName = $this->_extractCustomerNameFromAmazonAddress($this->_getAmazonShippingAddress());
        return $customerName ? $customerName->getData('lastname') : null;
    }

    /**
     * Returns shipping address street lines
     *
     * @return array|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getShippingAddressLines()
    {
        return $this->_extractLinesFromAmazonAddress($this->_getAmazonShippingAddress());
    }

    /**
     * Returns shipping address city
     *
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getShippingAddressCity()
    {
        return $this->_extractCityFromAmazonAddress($this->_getAmazonShippingAddress());
    }

    /**
     * Returns shipping address postal code
     *
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getShippingAddressPostalCode()
    {
        return $this->_extractPostalCodeFromAmazonAddress($this->_getAmazonShippingAddress());
    }

    /**
     * Returns shipping address region
     *
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getShippingAddressRegion()
    {
        return $this->_extractRegionFromAmazonAddress($this->_getAmazonShippingAddress());
    }

    /**
     * Returns shipping address country code
     *
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getShippingAddressCountryCode()
    {
        return $this->_extractCountryCodeFromAmazonAddress($this->_getAmazonShippingAddress());
    }

    /**
     * Returns customer phone for shipping address
     *
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getShippingAddressPhone()
    {
        return $this->_extractPhoneFromAmazonAddress($this->_getAmazonShippingAddress());
    }

    /**
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getMagentoTransactionState()
    {
        return $this->_getMagentoTransactionAdditionalInformation(self::TRANSACTION_STATE_KEY);
    }

    /**
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getMagentoTransactionReasonCode()
    {
        return $this->_getMagentoTransactionAdditionalInformation(self::TRANSACTION_REASON_CODE_KEY);
    }

    /**
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getMagentoTransactionOrderLanguage()
    {
        return $this->_getMagentoTransactionAdditionalInformation(self::TRANSACTION_ORDER_LANGUAGE_KEY);
    }

    /**
     * @return null|string
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getMagentoChildTransactionType()
    {
        return $this->getDataMapper()->getMagentoChildTransactionType($this->getTransactionType());
    }

    /**
     * @return array|null
     */
    public function getMagentoBillingAddress()
    {
        return $this->getDataMapper()->getBillingAddress($this);
    }

    /**
     * @return array|null
     */
    public function getMagentoShippingAddress()
    {
        return $this->getDataMapper()->getShippingAddress($this);
    }

    /**
     * @param Varien_Object|null $stateObject
     * @param Varien_Object|null $customStatus
     * @return Varien_Object
     */
    public function generateMagentoOrderStateObject($stateObject = null, $customStatus = null)
    {
        return $this->getDataMapper()->generateMagentoOrderStateObject($this, $stateObject, $customStatus);
    }

    /**
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getCreditmemoState()
    {
        return $this->getDataMapper()->getCreditmemoState(
            $this->getTransactionType(),
            $this->getTransactionState(),
            $this->getTransactionReasonCode()
        );
    }

    /**
     * @return string|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getInvoiceState()
    {
        return $this->getDataMapper()->getInvoiceState(
            $this->getTransactionType(),
            $this->getTransactionState(),
            $this->getTransactionReasonCode()
        );
    }

    /**
     * @return array|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getPaymentFlags()
    {
        return $this->getDataMapper()->getPaymentFlags(
            $this->getTransactionType(),
            $this->getTransactionState(),
            $this->getTransactionReasonCode()
        );
    }

    /**
     * @return null|string
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getTransactionalEmailToSend()
    {
        $email = $this->getDataMapper()->getTransactionalEmailToSend(
            $this->getTransactionType(),
            $this->getTransactionState(),
            $this->getTransactionReasonCode()
        );
        if (null === $email && null !== $this->getMagentoBillingAddress()) {
            return self::EMAIL_TYPE_NEW_ORDER;
        }

        return $email;
    }

    /**
     * Checks whether Magento order data should be updated
     *
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function shouldUpdateOrderData()
    {
        return $this->getDataMapper()->shouldUpdateOrderData(
            $this->getTransactionType(),
            $this->getTransactionState()
        );
    }

    /**
     * Checks whether Magento payment transaction should be updated
     *
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function shouldUpdateTransaction()
    {
        return $this->getMagentoTransactionState() != $this->getTransactionState()
            || $this->getMagentoTransactionReasonCode() != $this->getTransactionReasonCode()
            || $this->getMagentoTransactionOrderLanguage() != $this->getTransactionOrderLanguage();
    }

    /**
     * Checks whether Magento payment transaction should be closed
     *
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function shouldCloseTransaction()
    {
        return $this->getDataMapper()->shouldCloseTransaction($this->getMagentoTransactionState());
    }

    /**
     * Checks whether parent transaction should be updated
     *
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function shouldUpdateParentTransaction()
    {
        return $this->getTransaction()->getId()
            && $this->getDataMapper()->shouldUpdateParentTransaction(
                $this->getTransactionType(),
                $this->getTransactionState(),
                $this->getTransactionReasonCode()
            );
    }

    /**
     * Checks whether parent transaction should be updated
     *
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function shouldCloseOrderTransaction()
    {
        return $this->getDataMapper()->shouldCloseOrderTransaction(
            $this->getTransactionType(),
            $this->getTransactionState(),
            $this->getTransactionReasonCode()
        ) && !($this->getTransaction()->getOrder()->getBaseTotalDue());
    }

    /**
     * Checks whether transaction is eligible for data polling
     *
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function shouldPollData()
    {
        switch ($this->getMagentoTransactionState()) {
            case self::TRANSACTION_STATE_PENDING:
            case self::TRANSACTION_STATE_SUSPENDED:
            case null:
                return true;
            case self::TRANSACTION_STATE_OPEN:
                $txnAge = $this->_getTransactionAgeInDays();
                if (($this->getTransactionType() == self::TRANSACTION_TYPE_ORDER && $txnAge > 180)
                    || ($this->getTransactionType() == self::TRANSACTION_TYPE_AUTH && $txnAge > 30)) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Returns transaction status and details to save in 'additional_data'
     * field of the payment transaction entity in Magento
     *
     * @return array|null
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getRawDetails()
    {
        $rawDetails = array();
        if ($state = $this->getTransactionState()) {
            $rawDetails[self::TRANSACTION_STATE_KEY] = $state;
        }

        if ($reasonCode = $this->getTransactionReasonCode()) {
            $rawDetails[self::TRANSACTION_REASON_CODE_KEY] = $reasonCode;
        }

        if ($reasonDescription = $this->getTransactionReasonDescription()) {
            $rawDetails[self::TRANSACTION_REASON_DESCRIPTION_KEY] = $reasonDescription;
        }

        if ($orderLanguage = $this->getTransactionOrderLanguage()) {
            $rawDetails[self::TRANSACTION_ORDER_LANGUAGE_KEY] = $orderLanguage;
        }

        return !empty($rawDetails) ? $rawDetails : null;
    }

    /**
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function isSync()
    {
        $transactionDetails = $this->_getAmazonTransactionDetails();
        if (isset($transactionDetails['AuthorizationReferenceId'])
            && preg_match('/-sync$/', $transactionDetails['AuthorizationReferenceId'])) {
            return true;
        }

        return false;
    }
}
