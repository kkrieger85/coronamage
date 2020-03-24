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
class Creativestyle_AmazonPayments_Model_Lookup_Design_Button_Color
    extends Creativestyle_AmazonPayments_Model_Lookup_Abstract
{
    const COLOR_ORANGE  = 'orange';
    const COLOR_TAN     = 'tan';

    public function toOptionArray() 
    {
        if (null === $this->_options) {
            $this->_options = array(
                array(
                    'value' => self::COLOR_ORANGE,
                    'label' => Mage::helper('amazonpayments')->__('Orange (recommended)')
                ),
                array('value' => self::COLOR_TAN, 'label' => Mage::helper('amazonpayments')->__('Tan')),
            );
        }

        return $this->_options;
    }
}
