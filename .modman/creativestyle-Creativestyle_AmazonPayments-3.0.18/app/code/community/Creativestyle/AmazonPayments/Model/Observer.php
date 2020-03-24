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
class Creativestyle_AmazonPayments_Model_Observer
{

    const DATA_POLL_TRANSACTION_LIMIT  = 36;
    const DATA_POLL_SLEEP_BETWEEN_TIME = 300000;

    /**
     * Return Amazon Pay config model instance
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
     * @return Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection
     */
    protected function _getPaymentTransactionCollection()
    {
        /** @var Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection $collection */
        $collection = Mage::getModel('sales/order_payment_transaction')->getCollection();
        $collection->addPaymentInformation(array('method'))
            ->addFieldToFilter('method', array('in' => $this->_getHelper()->getAvailablePaymentMethods()))
            ->addFieldToFilter('is_closed', 0)
            ->setOrder('transaction_id', 'asc');

        if ($recentPolledTransaction = $this->_getConfig()->getRecentPolledTransaction()) {
            $collection->addFieldToFilter('transaction_id', array('gt' => (int)$recentPolledTransaction));
        }

        return $collection;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment_Transaction $transaction
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _shouldPollDataForTransaction(Mage_Sales_Model_Order_Payment_Transaction $transaction)
    {
        return $this->_getTransactionProcessor($transaction)
            ->shouldPollData();
    }

    /**
     * @param string $method
     * @return bool
     */
    protected function _isAmazonPaymentsMethod($method)
    {
        return in_array($method, $this->_getHelper()->getAvailablePaymentMethods());
    }

    /**
     * @param Mage_Sales_Model_Order_Payment_Transaction $transaction
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _shouldCloseTransaction(Mage_Sales_Model_Order_Payment_Transaction $transaction)
    {
        return $this->_isAmazonPaymentsMethod($transaction->getOrder()->getPayment()->getMethod())
            && $this->_getTransactionProcessor($transaction)->shouldCloseTransaction();
    }

    protected function _pollPaymentTransactionData()
    {
        $recentTransactionId = null;
        $count = 0;

        /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
        foreach ($this->_getPaymentTransactionCollection() as $transaction) {
            try {
                if ($this->_shouldPollDataForTransaction($transaction)) {
                    $transaction->getOrderPaymentObject()
                        ->setOrder($transaction->getOrder())
                        ->importTransactionInfo($transaction);
                    // @codingStandardsIgnoreStart
                    $transaction->save();
                    // @codingStandardsIgnoreEnd
                    $recentTransactionId = $transaction->getId();
                    if ($count++ >= self::DATA_POLL_TRANSACTION_LIMIT) {
                        break;
                    }

                    usleep(self::DATA_POLL_SLEEP_BETWEEN_TIME);
                }
            } catch (Exception $e) {
                Creativestyle_AmazonPayments_Model_Logger::logException($e);
            }
        }

        if ($count < self::DATA_POLL_TRANSACTION_LIMIT) {
            $recentTransactionId = null;
        }

        $this->_getConfig()->setRecentPolledTransaction($recentTransactionId);

        return $this;
    }

    /**
     * Inject Authorize button to the admin order view page
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function injectAuthorizeButton($observer)
    {
        $observer->getEvent();
        try {
            $order = Mage::registry('sales_order');
            // check if object instance exists and whether manual authorization is enabled
            if ($order && $order->getId() && $this->_getConfig()->isManualAuthorizationAllowed()
                && Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/amazonpayments_authorize')
            ) {
                $payment = $order->getPayment();
                if ($this->_isAmazonPaymentsMethod($payment->getMethod())) {
                    // check if payment wasn't authorized already
                    $orderTransaction =
                        $payment->lookupTransaction(false, Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER);
                    $authTransaction =
                        $payment->lookupTransaction(false, Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);

                    // invoke injectAuthorizeButton helper if authorization transaction does not exist or is closed
                    if ($orderTransaction && !$orderTransaction->getIsClosed()
                        && (!$authTransaction || $authTransaction->getIsClosed())) {
                        $block = Mage::getSingleton('core/layout')->getBlock('sales_order_edit');
                        if ($block) {
                            $url = Mage::getModel('adminhtml/url')->getUrl(
                                'adminhtml/amazonpayments_order/authorize',
                                array('order_id' => $order->getId())
                            );
                            $message = $this->_getHelper()
                                ->__('Are you sure you want to authorize payment for this order?');
                            $block->addButton(
                                'payment_authorize',
                                array(
                                    'label'     => $this->_getHelper()->__('Authorize payment'),
                                    'onclick'   => "confirmSetLocation('{$message}', '{$url}')",
                                    'class'     => 'go'
                                )
                            );
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Creativestyle_AmazonPayments_Model_Logger::logException($e);
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveTransactionBefore($observer)
    {
        try {
            /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
            $transaction = $observer->getEvent()->getOrderPaymentTransaction();
            if ($this->_shouldCloseTransaction($transaction)) {
                $transaction->setIsClosed(true);
            }
        } catch (Exception $e) {
            Creativestyle_AmazonPayments_Model_Logger::logException($e);
        }

        return $this;
    }

    /**
     * Capture and log Amazon Payments API call
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function logApiCall($observer)
    {
        $callData = $observer->getEvent()->getCallData();
        if (is_array($callData)) {
            Creativestyle_AmazonPayments_Model_Logger::logApiCall($callData);
        }

        return $this;
    }

    /**
     * Capture and log incoming IPN notification
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function logIpnCall($observer)
    {
        $callData = $observer->getEvent()->getCallData();
        if (is_array($callData)) {
            Creativestyle_AmazonPayments_Model_Logger::logIpnCall($callData);
        }

        return $this;
    }

    /**
     * Sets secure URLs in Magento configuration
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function setSecureUrls($observer)
    {
        $observer->getEvent();
        try {
            $secureUrlsConfigNode = Mage::getConfig()->getNode('frontend/secure_url');
            if ($this->_getConfig()->isLoginActive() && !$this->_getConfig()->isRedirectAuthenticationExperience()) {
                $secureUrlsConfigNode->addChild('amazonpayments_cart', '/checkout/cart');
            }

            if ($this->_getConfig()->isSandboxActive()) {
                // @codingStandardsIgnoreStart
                unset($secureUrlsConfigNode->amazonpayments_ipn);
                unset($secureUrlsConfigNode->amazonpayments_ipn_legacy);
                // @codingStandardsIgnoreEnd
            }
        } catch (Exception $e) {
            Creativestyle_AmazonPayments_Model_Logger::logException($e);
        }

        return $this;
    }

    /**
     * Invokes Amazon Payments log files rotating
     *
     * @return $this
     * @throws Exception
     */
    public function rotateLogfiles()
    {
        try {
            Creativestyle_AmazonPayments_Model_Logger::rotateLogfiles();
        } catch (Exception $e) {
            Creativestyle_AmazonPayments_Model_Logger::logException($e);
            throw $e;
        }

        return $this;
    }

    /**
     * Invokes data polling from Amazon Payments gateway
     *
     * @return $this
     * @throws Exception
     */
    public function pollPaymentTransactionData()
    {
        try {
            if (!$this->_getConfig()->isIpnActive()) {
                $this->_pollPaymentTransactionData();
            }
        } catch (Exception $e) {
            Creativestyle_AmazonPayments_Model_Logger::logException($e);
            throw $e;
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function sendDeliveryNotification($observer)
    {
        try {
            /** @var Mage_Sales_Model_Order_Shipment $shipment */
            $shipment = $observer->getEvent()->getShipment();

            if ($shipment && $this->_isAmazonPaymentsMethod($shipment->getOrder()->getPayment()->getMethod())
                && $this->_getConfig()->isDeliveryTrackingActive($shipment->getStoreId())) {

                /** @var Mage_Sales_Model_Resource_Order_Shipment_Track_Collection $tracks */
                $tracks = $shipment->getTracksCollection();

                if ($tracks->count()) {
                    /** @var Mage_Sales_Model_Order_Shipment_Track $track */
                    $track  = $tracks->getLastItem();

                    $orderReferenceId = $shipment->getOrder()->getExtOrderId();
                    $trackingNumber = $track->getTrackNumber();
                    $carrierCode = $this->_mapCarrierCode(
                        array(
                            $track->getCarrierCode(),
                            $shipment->getOrder()->getShippingMethod(true)->getCarrierCode()
                        ),
                        $shipment->getStoreId()
                    );

                    if ($carrierCode) {
                        /** @var Creativestyle_AmazonPayments_Model_Api_Alexa $trackingApi */
                        $trackingApi = Mage::getModel('amazonpayments/api_alexa');

                        $trackingApi->addDeliveryTracking(
                            $shipment->getStoreId(),
                            $orderReferenceId,
                            $trackingNumber,
                            $carrierCode
                        );
                    }
                }
            }
        } catch (Exception $e) {
            Creativestyle_AmazonPayments_Model_Logger::logException($e);
        }

        return $this;
    }

    /**
     * @param string|array $carrierCodes
     * @param mixed|null $store
     * @return string|null
     */
    protected function _mapCarrierCode($carrierCodes, $store = null)
    {
        $carrierCodesMap = $this->_getConfig()->getDeliveryTrackingCarriers($store);

        if (!is_array($carrierCodes)) {
            $carrierCodes = array($carrierCodes);
        }

        foreach ($carrierCodes as $carrierCode) {
            if (isset($carrierCodesMap[strtolower($carrierCode)])) {
                return $carrierCodesMap[strtolower($carrierCode)];
            }
        }

        return null;
    }
}
