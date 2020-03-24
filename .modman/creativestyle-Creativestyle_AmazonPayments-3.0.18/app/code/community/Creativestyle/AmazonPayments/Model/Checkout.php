<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2014 - 2017 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2014 - 2017 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_Model_Checkout extends Mage_Checkout_Model_Type_Onepage
{
    /**
     * Returns instance of Amazon Payments config object
     *
     * @return Creativestyle_AmazonPayments_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('amazonpayments/config');
    }

    protected function _getPaymentMethod()
    {
        if ($this->_getConfig()->isSandboxActive()) {
            return array('method' => 'amazonpayments_advanced_sandbox');
        }

        return array('method' => 'amazonpayments_advanced');
    }

    protected function _sanitizeShippingAddress($addressData)
    {
        $allowedCountries = explode(',', (string)Mage::getStoreConfig('general/country/allow'));
        if (!(isset($addressData['country_id']) && in_array($addressData['country_id'], $allowedCountries))) {
            $addressData['country_id'] = null;
        }

        return $addressData;
    }

    /**
     * Sets cart coupon code from checkout to quote
     *
     * @return $this
     */
    protected function _setCartCouponCode()
    {
        if ($couponCode = $this->getCheckout()->getCartCouponCode()) {
            $this->getQuote()->setCouponCode($couponCode);
        }

        return $this;
    }

    public function savePayment($data)
    {
        $data = $this->_getPaymentMethod();
        if ($this->getQuote()->isVirtual()) {
            $this->getQuote()->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        } else {
            $this->getQuote()->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        }

        // shipping totals may be affected by payment method
        if (!$this->getQuote()->isVirtual() && $this->getQuote()->getShippingAddress()) {
            $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        }

        $data['checks'] = Creativestyle_AmazonPayments_Model_Payment_Abstract::CHECK_USE_FOR_COUNTRY
            | Creativestyle_AmazonPayments_Model_Payment_Abstract::CHECK_USE_FOR_CURRENCY
            | Creativestyle_AmazonPayments_Model_Payment_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
            | Creativestyle_AmazonPayments_Model_Payment_Abstract::CHECK_ZERO_TOTAL;


        $payment = $this->getQuote()->getPayment();
        $payment->importData($data);

        $this->getQuote()->save();

        $this->getCheckout()
            ->setStepData('payment', 'complete', true)
            ->setStepData('review', 'allow', true);

        return array();
    }

    public function saveShipping($data, $customerAddressId)
    {
        if (empty($data)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid data.'));
        }

        $data = $this->_sanitizeShippingAddress($data);

        unset($data['address_id']);
        $address = $this->getQuote()->getShippingAddress();
        $address->setCustomerAddressId(null);

        $address->addData($data)->setSaveInAddressBook(0);
        $address->implodeStreetAddress();

        $this->_setCartCouponCode();

        // shipping totals may be affected by payment method
        if (!$this->getQuote()->isVirtual() && $this->getQuote()->getShippingAddress()) {
            $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        }

        $this->getQuote()->collectTotals();
        $this->getQuote()->save();

        $this->getCheckout()
            ->setStepData('shipping', 'complete', true)
            ->setStepData('shipping_method', 'allow', true);

        return array();
    }

    public function saveShippingMethod($shippingMethod)
    {
        if (empty($shippingMethod)) {
            $this->getQuote()->getShippingAddress()->unsetShippingMethod();
            return array();
        }

        $rate = $this->getQuote()->getShippingAddress()->getShippingRateByCode($shippingMethod);
        if (!$rate) {
            throw new Creativestyle_AmazonPayments_Exception(Mage::helper('checkout')->__('Invalid shipping method.'));
        }

        $this->getQuote()->getShippingAddress()
            ->setShippingMethod($shippingMethod)
            ->collectTotals()
            ->save();

        $this->getCheckout()
            ->setStepData('shipping_method', 'complete', true)
            ->setStepData('payment', 'allow', true);

        return array();
    }

    public function saveOrder()
    {
        $this->getQuote()->collectTotals();
        $this->validate();
        $isNewCustomer = false;
        switch ($this->getCheckoutMethod()) {
            case self::METHOD_GUEST:
                $this->_prepareGuestQuote();
                break;
            case self::METHOD_REGISTER:
                $this->_prepareNewCustomerQuote();
                $isNewCustomer = true;
                break;
            default:
                $this->_prepareCustomerQuote();
                break;
        }

        /** @var Creativestyle_AmazonPayments_Model_Service_Quote $service */
        $service = Mage::getModel('amazonpayments/service_quote', $this->getQuote());
        $service->submitOrder();

        if ($isNewCustomer) {
            try {
                $this->_involveNewCustomer();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        $this->_checkoutSession->setLastQuoteId($this->getQuote()->getId())
            ->setLastSuccessQuoteId($this->getQuote()->getId())
            ->clearHelperData();

        $order = $service->getOrder();
        if ($order) {
            Mage::dispatchEvent(
                'checkout_type_onepage_save_order_after',
                array('order' => $order, 'quote' => $this->getQuote())
            );

            // add order information to the session
            $this->_checkoutSession->setLastOrderId($order->getId())
                ->setLastRealOrderId($order->getIncrementId())
                ->setAmazonOrderReferenceId(null);
        }

        Mage::dispatchEvent(
            'checkout_submit_all_after',
            array('order' => $order, 'quote' => $this->getQuote(), 'recurring_profiles' => null)
        );

        return $this;
    }

}
