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
abstract class Creativestyle_AmazonPayments_Block_Checkout_Abstract extends
 Creativestyle_AmazonPayments_Block_Pay_Abstract
{
    /**
     * @inheritdoc
     */
    protected function _isActive()
    {
        if (!$this->isPayActive() || !$this->_isCurrentIpAllowed() || !$this->_isCurrentLocaleAllowed()) {
            return false;
        }

        // hide for orders with virtual items when Login with Amazon is disabled
        if (!$this->isLoginActive() && $this->_quoteHasVirtualItems()) {
            return false;
        }

        /** @var Creativestyle_AmazonPayments_Model_Payment_Abstract $paymentMethodInstance */
        $paymentMethodInstance = $this->isSandboxActive()
            ? Mage::getModel('amazonpayments/payment_advanced_sandbox')
            : Mage::getModel('amazonpayments/payment_advanced');

        return $paymentMethodInstance->isAvailable($this->_getQuote());
    }
}
