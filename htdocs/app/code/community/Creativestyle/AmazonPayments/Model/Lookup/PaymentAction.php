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
class Creativestyle_AmazonPayments_Model_Lookup_PaymentAction extends Creativestyle_AmazonPayments_Model_Lookup_Abstract
{
    public function toOptionArray()
    {
        if (null === $this->_options) {
            $this->_options = array(
                array(
                    'value' => Creativestyle_AmazonPayments_Model_Payment_Abstract::ACTION_MANUAL,
                    'label' => Mage::helper('amazonpayments')->__('Manual authorization')
                ),
                array(
                    'value' => Creativestyle_AmazonPayments_Model_Payment_Abstract::ACTION_AUTHORIZE,
                    'label' => Mage::helper('amazonpayments')->__('Authorize')
                ),
                array(
                    'value' => Creativestyle_AmazonPayments_Model_Payment_Abstract::ACTION_AUTHORIZE_CAPTURE,
                    'label' => Mage::helper('amazonpayments')->__('Authorize & capture')
                ),
                array(
                    'value' => Creativestyle_AmazonPayments_Model_Payment_Abstract::ACTION_ERP,
                    'label' => Mage::helper('amazonpayments')->__('ERP mode')
                )
            );
        }

        return $this->_options;
    }
}
