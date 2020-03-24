<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2018 - 2019 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2018 - 2019 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */

class Creativestyle_AmazonPayments_Model_Lookup_CheckoutType extends Creativestyle_AmazonPayments_Model_Lookup_Abstract
{
    const CHECKOUT_TYPE_AMAZON = 'amazon';
    const CHECKOUT_TYPE_ONEPAGE = 'onepage';

    public function toOptionArray()
    {
        if (null === $this->_options) {
            $this->_options = array(
                array(
                    'value' => self::CHECKOUT_TYPE_AMAZON,
                    'label' => Mage::helper('amazonpayments')->__('Amazon Checkout')
                ),
                array(
                    'value' => self::CHECKOUT_TYPE_ONEPAGE,
                    'label' => Mage::helper('amazonpayments')->__('Magento One Page Checkout')
                )
            );
        }

        return $this->_options;
    }
}
