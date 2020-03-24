<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2015 - 2017 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2015 - 2017 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_Model_Lookup_Authentication
    extends Creativestyle_AmazonPayments_Model_Lookup_Abstract
{
    const AUTO_EXPERIENCE       = 'auto';
    const POPUP_EXPERIENCE      = 'popup';
    const REDIRECT_EXPERIENCE   = 'redirect';

    public function toOptionArray() 
    {
        if (null === $this->_options) {
            $this->_options = array(
                array('value' => self::AUTO_EXPERIENCE, 'label' => Mage::helper('amazonpayments')->__('Auto')),
                array('value' => self::POPUP_EXPERIENCE, 'label' => Mage::helper('amazonpayments')->__('Pop-up')),
                array('value' => self::REDIRECT_EXPERIENCE, 'label' => Mage::helper('amazonpayments')->__('Redirect')),
            );
        }

        return $this->_options;
    }
}
