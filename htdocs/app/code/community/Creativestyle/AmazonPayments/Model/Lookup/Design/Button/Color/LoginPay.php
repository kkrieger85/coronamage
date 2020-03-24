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
class Creativestyle_AmazonPayments_Model_Lookup_Design_Button_Color_LoginPay
    extends Creativestyle_AmazonPayments_Model_Lookup_Abstract
{
    const COLOR_GOLD      = 'Gold';
    const COLOR_DARK_GRAY = 'DarkGray';
    const COLOR_LIGHT_GRAY= 'LightGray';

    public function toOptionArray() 
    {
        if (null === $this->_options) {
            $this->_options = array(
                array('value' => self::COLOR_GOLD, 'label' => Mage::helper('amazonpayments')->__('Gold')),
                array('value' => self::COLOR_DARK_GRAY, 'label' => Mage::helper('amazonpayments')->__('Dark gray')),
                array('value' => self::COLOR_LIGHT_GRAY, 'label' => Mage::helper('amazonpayments')->__('Light gray'))
            );
        }

        return $this->_options;
    }
}
