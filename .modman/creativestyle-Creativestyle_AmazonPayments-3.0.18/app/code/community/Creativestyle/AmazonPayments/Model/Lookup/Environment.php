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

class Creativestyle_AmazonPayments_Model_Lookup_Environment extends Creativestyle_AmazonPayments_Model_Lookup_Abstract
{

    const ENVIRONMENT_LIVE = 'live';
    const ENVIRONMENT_SANDBOX = 'sandbox';

    public function toOptionArray()
    {
        if (null === $this->_options) {
            $this->_options = array(
                array(
                    'value' => self::ENVIRONMENT_SANDBOX,
                    'label' => Mage::helper('amazonpayments')->__('Sandbox (test)')
                ),
                array(
                    'value' => self::ENVIRONMENT_LIVE,
                    'label' => Mage::helper('adminhtml')->__('Production (live)')
                )
            );
        }

        return $this->_options;
    }
}
