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
class Creativestyle_AmazonPayments_Model_Lookup_Design_Button_Size_LoginPay
    extends Creativestyle_AmazonPayments_Model_Lookup_Abstract
{
    const SIZE_SMALL    = 'small';
    const SIZE_MEDIUM   = 'medium';
    const SIZE_LARGE    = 'large';
    const SIZE_XLARGE   = 'x-large';

    public function toOptionArray() 
    {
        if (null === $this->_options) {
            $this->_options = array(
                array('value' => self::SIZE_SMALL, 'label' => Mage::helper('amazonpayments')->__('Small')),
                array('value' => self::SIZE_MEDIUM, 'label' => Mage::helper('amazonpayments')->__('Medium')),
                array('value' => self::SIZE_LARGE, 'label' => Mage::helper('amazonpayments')->__('Large')),
                array('value' => self::SIZE_XLARGE, 'label' => Mage::helper('amazonpayments')->__('X-Large'))
            );
        }

        return $this->_options;
    }
}
