<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2015 - 2019 creativestyle GmbH. All Rights reserved
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
class Creativestyle_AmazonPayments_Model_Lookup_AuthorizationMode extends
 Creativestyle_AmazonPayments_Model_Lookup_Abstract
{
    const AUTO         = 'auto';
    const ASYNCHRONOUS = 'asynchronous';
    const SYNCHRONOUS  = 'synchronous';

    public function toOptionArray()
    {
        if (null === $this->_options) {
            $this->_options = array(
                array('value' => self::AUTO, 'label' => Mage::helper('amazonpayments')->__('Optimized')),
                array('value' => self::SYNCHRONOUS, 'label' => Mage::helper('amazonpayments')->__('Synchronous')),
                array('value' => self::ASYNCHRONOUS, 'label' => Mage::helper('amazonpayments')->__('Asynchronous'))
            );
        }

        return $this->_options;
    }
}
