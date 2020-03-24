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
class Creativestyle_AmazonPayments_CheckoutController extends Creativestyle_AmazonPayments_Controller_Checkout
{
    const CHECKOUT_JS_APP_PAGE_ID = 'checkout';
    const INVALID_PAYMENT_METHOD_JS_APP_PAGE_ID = 'invalid_payment';

    /**
     * @return string
     * @throws Mage_Core_Exception
     */
    protected function _getShippingMethodsHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('amazonpayments_checkout_shippingmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    /**
     * @return string
     * @throws Mage_Core_Exception
     */
    protected function _getReviewHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('amazonpayments_checkout_review');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    protected function _isSubmitAllowed()
    {
        if (!$this->_getQuote()->isVirtual()) {
            $address = $this->_getQuote()->getShippingAddress();
            $method = $address->getShippingMethod();
            $rate = $address->getShippingRateByCode($method);
            if (!$this->_getQuote()->isVirtual() && (!$method || !$rate)) {
                return false;
            }
        }

        return true;
    }

    public function indexAction()
    {
        try {
            if (!$this->_getQuote()->hasItems() || $this->_getQuote()->getHasError()) {
                $this->_redirect('checkout/cart');
                return;
            }

            if (!$this->_getQuote()->validateMinimumAmount()) {
                $error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
                    Mage::getStoreConfig('sales/minimum_order/error_message') :
                    Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');
                $this->_getCheckoutSession()->addError($error);
                $this->_redirect('checkout/cart');
                return;
            }

            if (null === $this->_getOrderReferenceId() && null === $this->_getAccessToken()) {
                $this->_redirect('checkout/cart');
                return;
            }

            $this->_getCheckoutSession()->setCartWasUpdated(false);
            $this->_getCheckout()->savePayment(null);

            if ($this->_getConfig()->getCheckoutType() ==
                Creativestyle_AmazonPayments_Model_Lookup_CheckoutType::CHECKOUT_TYPE_ONEPAGE) {
                $this->_redirect(
                    'checkout/onepage',
                    array(
                        'lpa' => true,
                        'orderReferenceId' => $this->_getOrderReferenceId(),
                        'accessToken' => $this->_getAccessToken()
                    )
                );
                return;
            }

            $this->loadLayout();
            $this->_setHeadTitle('Amazon Pay')
                ->_setJsParams(
                    array(
                        'order_reference_id' => $this->_getOrderReferenceId(),
                        'access_token' => $this->_getAccessToken(),
                        'js_app_page' => self::CHECKOUT_JS_APP_PAGE_ID
                    )
                );
            $this->renderLayout();
        } catch (Exception $e) {
            Creativestyle_AmazonPayments_Model_Logger::logException($e);
            $this->_getCheckoutSession()->addError(
                $this->__('There was an error processing your order. Please contact us or try again later.')
            );
            $this->_redirect('checkout/cart');
            return;
        }
    }

    public function saveShippingAction()
    {
        if ($this->getRequest()->isPost()) {
            try {
                if ($this->_expireAjax()) {
                    return;
                }

                $result = $this->_saveShipping();
            } catch (Exception $e) {
                Creativestyle_AmazonPayments_Model_Logger::logException($e);
                $result = array(
                    'error' => -1,
                    'error_messages' => $e->getMessage(),
                    'allow_submit' => false
                );
            }

            if (!isset($result['error'])) {
                $result = array(
                    'render_widget' => array(
                        'shipping-method' => $this->_getLayoutHandleHtml('amazonpayments_checkout_shippingmethod')
                    ),
                    'allow_submit' => false,
                    'goto_section' => 'shipping_method',
                    'update_sections' => array(
                        array(
                            'name' => 'shipping-method',
                            'html' => $this->_getLayoutHandleHtml('checkout_onepage_shippingmethod')
                        ),
                    )
                );
            };
        } else {
            $this->_forward('noRoute');
            return;
        }

        $this->_setJsonResponse($result);
    }

    /**
     * @throws Mage_Core_Exception
     */
    public function saveShippingMethodAction()
    {
        if ($this->getRequest()->isPost()) {
            try {
                if ($this->_expireAjax()) {
                    return;
                }

                $data = $this->getRequest()->getPost('shipping_method', '');
                $this->_getCheckout()->saveShippingMethod($data);
            } catch (Exception $e) {
                Creativestyle_AmazonPayments_Model_Logger::logException($e);
                $result = array(
                    'error' => true,
                    'error_messages' => $e->getMessage(),
                    'allow_submit' => false
                );
                $this->_setJsonResponse($result);
                return;
            }

            Mage::dispatchEvent(
                'checkout_controller_onepage_save_shipping_method',
                array('request' => $this->getRequest(), 'quote' => $this->_getQuote())
            );
            $this->_getQuote()->collectTotals()->save();
            $result = array(
                'render_widget' => array('review' => $this->_getReviewHtml()),
                'allow_submit' => $this->_isSubmitAllowed()
            );
            $this->_setJsonResponse($result);
        } else {
            $this->_forward('noRoute');
        }
    }

    /**
     * @param array $orderReference
     * @return array|null
     */
    protected function _validateOrderReference($orderReference)
    {
        if (isset($orderReference['Constraints']) && is_array($orderReference['Constraints'])) {
            foreach ($orderReference['Constraints'] as $constraint) {
                switch ($constraint['ConstraintID']) {
                    case 'ShippingAddressNotSet':
                        return array(
                            'success' => false,
                            'error' => true,
                            'error_messages' => $this->__(
                                'There has been a problem with the selected payment method from your Amazon account, '
                                . 'please update the payment method or choose another one.'
                            ),
                            'allow_submit' => false
                        );
                    case 'PaymentMethodNotAllowed':
                    case 'PaymentPlanNotSet':
                        return array(
                            'success' => false,
                            'error' => true,
                            'error_messages' => $this->__(
                                'There has been a problem with the selected payment method from your Amazon account, '
                                . 'please update the payment method or choose another one.'
                            ),
                            'allow_submit' => $this->_isSubmitAllowed(),
                            'deselect_payment' => true,
                            'render_widget' => array(
                                'wallet' => true
                            )
                        );
                }
            }
        }

        return null;
    }

    /**
     * @param array $postedAgreements
     * @param array $requiredAgreements
     * @return array|null
     */
    protected function _validateCheckoutAgreements($postedAgreements, $requiredAgreements)
    {
        if ($requiredAgreements) {
            $diff = array_diff($requiredAgreements, $postedAgreements);
            if ($diff) {
                return array(
                    'success' => false,
                    'error' => true,
                    'error_messages' => $this->__(
                        'Please agree to all the terms and conditions before placing the order.'
                    ),
                    'allow_submit' => $this->_isSubmitAllowed()
                );
            }
        }

        return null;
    }

    /**
     * @param Creativestyle_AmazonPayments_Exception_InvalidTransaction $e
     * @param Mage_Sales_Model_Order|null $order
     * @return array|null
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Exception
     */
    protected function _handleInvalidTransactionException(
        Creativestyle_AmazonPayments_Exception_InvalidTransaction $e,
        Mage_Sales_Model_Order $order = null
    ) {
        if ($e->isAuth()) {
            if ($e->isDeclined()) {
                switch ($e->getReasonCode()) {
                    case Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_REASON_INVALID_PAYMENT:
                        $this->_redirect('*/*/invalidPayment');
                        return null;

                    case Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_REASON_TIMEOUT:
                        if (!$e->isSync() || $this->_getConfig()->getAuthorizationMode()
                            === Creativestyle_AmazonPayments_Model_Lookup_AuthorizationMode::SYNCHRONOUS) {
                            try {
                                if ($order && $order->getId()) {
                                    $orderReferenceId = $order->getExtOrderId();
                                    if ($orderReferenceId) {
                                        $this->_getApi()->cancelOrderReference(null, $orderReferenceId);
                                    }

                                    if ($order->canUnhold()) {
                                        $order->unhold();
                                    }

                                    $order->cancel()->save();
                                } else {
                                    $this->_cancelOrderReference();
                                }
                            } catch (Creativestyle_AmazonPayments_Exception $e) {
                            }
                        }
                        break;

                    case Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_REASON_AMAZON_REJECTED:
                    case Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_REASON_PROCESSING_FAILURE:
                        if ($e->isSync()) {
                            if ($order && $order->getId()) {
                                $orderReferenceId = $order->getExtOrderId();
                                if ($orderReferenceId) {
                                    try {
                                        $this->_getApi()->cancelOrderReference(null, $orderReferenceId);
                                    } catch (Creativestyle_AmazonPayments_Exception $e) {
                                    }
                                }

                                if ($order->canUnhold()) {
                                    $order->unhold();
                                }

                                $order->cancel()->save();
                            } else {
                                $this->_cancelOrderReference();
                            }
                        }
                        break;
                }
            }
        }

        Creativestyle_AmazonPayments_Model_Logger::logException($e);

        $this->_getCheckoutSession()->addError(
            $this->__('There was an error processing your order. Please contact us or try again later.')
        );

        $this->_redirect('checkout/cart');

        return array(
            'success' => false,
            'error' => true,
            'redirect' => Mage::getUrl('checkout/cart')
        );
    }

    /**
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Exception
     * @throws Zend_Json_Exception
     */
    public function saveOrderAction()
    {
        try {
            // validate checkout agreements
            $result = $this->_validateCheckoutAgreements(
                array_keys($this->getRequest()->getPost('agreement', array())),
                Mage::helper('checkout')->getRequiredAgreementIds()
            );
            if ($result) {
                $this->_setJsonResponse($result);
                return;
            }

            // validate order reference
            $orderReferenceDetails = $this->_getApi()->getOrderReferenceDetails(
                null,
                $this->_getOrderReferenceId(),
                $this->_getAccessToken()
            );
            $result = $this->_validateOrderReference($orderReferenceDetails);
            if ($result) {
                $this->_setJsonResponse($result);
                return;
            }

            $skipOrderReferenceProcessing = false;
            if (isset($orderReferenceDetails['OrderReferenceStatus'])) {
                $orderReferenceStatus = $orderReferenceDetails['OrderReferenceStatus'];
                if (isset($orderReferenceStatus['State'])) {
                    $skipOrderReferenceProcessing = $orderReferenceStatus['State']
                        != Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_DRAFT;
                }
            }

            $this->_getQuote()->getPayment()
                ->setTransactionId($this->_getOrderReferenceId())
                ->setSkipOrderReferenceProcessing($skipOrderReferenceProcessing);

            $customFields = $this->getRequest()->getPost('custom_fields', array());
            $allowedFields = $this->_getConfig()->getCheckoutCustomFields();

            foreach ($customFields as $customFieldName => $customFieldValue) {
                if (isset($allowedFields[$customFieldName])) {
                    if (is_array($customFieldValue)) {
                        foreach ($customFieldValue as $key => $value) {
                            if (isset($allowedFields[$customFieldName][$key])) {
                                switch ($customFieldName) {
                                    case 'customer':
                                        $this->_getQuote()->getCustomer()->setData($key, $value);
                                        break;
                                    case 'billing_address':
                                        $this->_getQuote()->getBillingAddress()->setData($key, $value);
                                        break;
                                    case 'shipping_address':
                                        $this->_getQuote()->getShippingAddress()->setData($key, $value);
                                        break;
                                    default:
                                        break;
                                }
                            }
                        }
                    } else {
                        $this->_getQuote()->setData($customFieldName, $customFieldValue);
                    }
                }
            }

            $simulation = $this->getRequest()->getPost('simulation', array());
            if (!empty($simulation) && isset($simulation['object'])) {
                $simulationData = array(
                    'object' => isset($simulation['object']) ? $simulation['object'] : null,
                    'state' => isset($simulation['state']) ? $simulation['state'] : null,
                    'reason_code' => isset($simulation['reason']) ? $simulation['reason'] : null
                );
                $simulationData['options'] = Creativestyle_AmazonPayments_Model_Simulator::getSimulationOptions(
                    $simulationData['object'],
                    $simulationData['state'],
                    $simulationData['reason_code']
                );
                $this->_getQuote()->getPayment()->setSimulationData($simulationData);
            }

            $this->_getCheckout()->saveOrder();
            $this->_getQuote()->save();

            $this->_getApi()->setOrderAttributes(
                null,
                $this->_getOrderReferenceId(),
                $this->_getCheckoutSession()->getLastRealOrderId()
            );

            $result = array(
                'success' => true,
                'error' => false
            );

            if ($this->_getConfig()->getRegion() == 'us' || $this->_getConfig()->getRegion() == 'jp') {
                $result['redirect'] = Mage::getUrl('*/*/mfa', array(
                    'AuthenticationStatus' => Creativestyle_AmazonPayments_Model_Amazon_Mfa_AuthenticationStatus::SUCCESS
                ));
            }

            $this->_setJsonResponse($result);
        } catch (Creativestyle_AmazonPayments_Exception_InvalidTransaction $e) {
            $result = $this->_handleInvalidTransactionException($e);
            if (is_array($result)) {
                $this->_setJsonResponse($result);
                return;
            }
        } catch (Exception $e) {
            Creativestyle_AmazonPayments_Model_Logger::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->_getQuote(), $e->getMessage());
            $this->_getCheckoutSession()->addError(
                $this->__('There was an error processing your order. Please contact us or try again later.')
            );
            $result = array(
                'success' => false,
                'error' => true,
                'redirect' => Mage::getUrl('checkout/cart')
            );
            $this->_setJsonResponse($result);
        }
    }

    public function cancelOrderReferenceAction()
    {
        $this->_cancelOrderReference();
        $this->_redirect('checkout/cart');
    }

    /**
     * @throws Mage_Core_Exception
     */
    public function couponPostAction()
    {
        if ($this->getRequest()->isPost()) {
            try {
                if ($this->_expireAjax()) {
                    return;
                }

                $couponCode = (string) $this->getRequest()->getParam('coupon_code');
                if ($this->getRequest()->getParam('remove') == 1) {
                    $couponCode = '';
                }

                $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
                $result = $this->_getQuote()->setCouponCode($couponCode)
                    ->collectTotals()
                    ->save();
            } catch (Exception $e) {
                Creativestyle_AmazonPayments_Model_Logger::logException($e);
                $result = array(
                    'error' => -1,
                    'error_messages' => $e->getMessage()
                );
            }

            if (!isset($result['error'])) {
                $result = array(
                    'render_widget' => array(
                        'review' => $this->_getReviewHtml()
                    ),
                    'allow_submit' => $this->_isSubmitAllowed()
                );
            };
        } else {
            $this->_forward('noRoute');
            return;
        }

        $this->_setJsonResponse($result);
    }

    /**
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Exception
     */
    public function mfaAction()
    {
        $order = null;

        $session = $this->_getCheckoutSession();
        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();

        if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }

        $authenticationStatus = $this->getRequest()->getParam('AuthenticationStatus', null);

        switch ($authenticationStatus) {
            case Creativestyle_AmazonPayments_Model_Amazon_Mfa_AuthenticationStatus::SUCCESS:
                try {
                    // do not authorize for ERP and manual authorization modes
                    if ($this->_getConfig()->isPaymentProcessingAllowed()
                        && !$this->_getConfig()->isManualAuthorizationAllowed()) {
                        /** @var Mage_Sales_Model_Quote $quote */
                        $quote = Mage::getModel('sales/quote')->load($lastQuoteId);

                        /** @var Mage_Sales_Model_Order $order */
                        $order = Mage::getModel('sales/order')->load($lastOrderId);

                        if ($order->getId()) {
                            /** @var Mage_Sales_Model_Order_Payment $payment */
                            $payment = $order->getPayment();
                            if (in_array($payment->getMethod(), $this->_getHelper()->getAvailablePaymentMethods())) {
                                /** @var Mage_Core_Model_Resource_Transaction $transaction */
                                $transaction = Mage::getModel('core/resource_transaction');

                                $order->setBaseTotalPaid(0);
                                $payment->getMethodInstance()->authorize($payment, $order->getBaseTotalDue());
                                $payment->setAmountAuthorized($order->getTotalDue())
                                    ->setBaseAmountAuthorized($order->getBaseTotalDue());

                                $transaction->addObject($order);

                                if ($quote->getId()) {
                                    $quote->setIsActive(false);
                                    $transaction->addObject($quote);
                                }

                                $transaction->save();
                            }
                        }
                    }

                    $this->_redirect('checkout/onepage/success');
                    return;
                } catch (Creativestyle_AmazonPayments_Exception_InvalidTransaction $e) {
                    $this->_handleInvalidTransactionException($e, $order);
                    return;
                } catch (Exception $e) {
                    Creativestyle_AmazonPayments_Model_Logger::logException($e);
                    $this->_redirect('checkout/cart');
                    return;
                }

            // no break
            case Creativestyle_AmazonPayments_Model_Amazon_Mfa_AuthenticationStatus::ABANDONED:
                $this->_redirect('*/*/invalidPayment');
                return;

            case Creativestyle_AmazonPayments_Model_Amazon_Mfa_AuthenticationStatus::FAILURE:
                /** @var Mage_Sales_Model_Order $order */
                $order = Mage::getModel('sales/order')->load($lastOrderId);
                if ($order->getId()) {
                    $orderReferenceId = $order->getExtOrderId();
                    if ($orderReferenceId) {
                        $this->_getApi()->cancelOrderReference(null, $orderReferenceId);
                    }

                    $order->cancel()->save();
                }

                $this->_getCheckoutSession()->addError(
                    $this->__('There was an error processing your order. Please contact us or try again later.')
                );
                $this->_redirect('checkout/cart');
                return;

            default:
                $this->_redirect('checkout/cart');
                return;
        }
    }

    public function invalidPaymentAction()
    {
        $session = $this->_getCheckoutSession();
        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($lastOrderId);

        if (!$order->getId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        if ($this->getRequest()->isPost()) {
            /** @var Mage_Sales_Model_Order_Payment $payment */
            $payment = $order->getPayment();

            if (in_array($payment->getMethod(), $this->_getHelper()->getAvailablePaymentMethods())) {
                $this->_getApi()->confirmOrderReference(
                    null,
                    $this->_getOrderReferenceId(),
                    $this->_getUrl()->getCheckoutMultiFactorAuthenticationUrl()
                );
            }

            $this->getResponse()->setBody(
                $this->_jsonEncode(
                    array(
                        'success' => true,
                        'error' => false
                    )
                )
            );
            return;
        }

        $this->loadLayout();
        $this->_setHeadTitle('Amazon Pay');
        $this->_setJsParams(array('order_reference_id' => $order->getExtOrderId()));
        $this->renderLayout();
    }
}
