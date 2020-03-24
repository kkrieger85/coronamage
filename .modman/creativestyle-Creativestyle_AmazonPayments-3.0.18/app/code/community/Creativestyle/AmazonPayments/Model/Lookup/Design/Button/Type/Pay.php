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
class Creativestyle_AmazonPayments_Model_Lookup_Design_Button_Type_Pay extends
 Creativestyle_AmazonPayments_Model_Lookup_Abstract
{
    const TYPE_FULL     = 'PwA';
    const TYPE_SHORT    = 'Pay';
    const TYPE_LOGO     = 'A';

    public function toOptionArray()
    {
        if (null === $this->_options) {
            $this->_options = array(
                array('value' => self::TYPE_FULL, 'label' => Mage::helper('amazonpayments')->__('Amazon Pay')),
                array('value' => self::TYPE_SHORT, 'label' => Mage::helper('amazonpayments')->__('Pay')),
                array('value' => self::TYPE_LOGO, 'label' => Mage::helper('amazonpayments')->__('Amazon logo'))
            );
        }

        return $this->_options;
    }
}
