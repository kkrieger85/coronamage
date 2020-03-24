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
class Creativestyle_AmazonPayments_Adminhtml_Amazonpayments_OrderController extends Mage_Adminhtml_Controller_Action
{
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
     * Order manual authorization action
     */
    public function authorizeAction()
    {
        $orderId = $this->getRequest()->getParam('order_id', null);
        if (null !== $orderId) {
            try {
                /** @var Mage_Sales_Model_Order $order */
                $order = Mage::getModel('sales/order')->load($orderId);
                if ($order->getId()) {
                    /** @var Mage_Sales_Model_Order_Payment $payment */
                    $payment = $order->getPayment();
                    if (in_array($payment->getMethod(), $this->_getHelper()->getAvailablePaymentMethods())) {
                        try {
                            $payment->setAmountAuthorized($order->getTotalDue())
                                ->setBaseAmountAuthorized($order->getBaseTotalDue())
                                ->getMethodInstance()->authorize($payment, $order->getBaseTotalDue());
                        } catch (Creativestyle_AmazonPayments_Exception_InvalidTransaction $e) {
                            // do nothing
                        }

                        $order->save();
                    }
                }
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                Mage::logException($e);
            }

            $this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId));
            return;
        }

        $this->_redirect('adminhtml/sales_order');
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('sales/order/actions/amazonpayments_authorize');
    }
}
