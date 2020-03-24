<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2019 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2019 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */

class Creativestyle_AmazonPayments_Block_Checkout_CustomFields extends
 Creativestyle_AmazonPayments_Block_Checkout_Abstract
{
    protected function _isActive()
    {
        $customFields = $this->_getConfig()->getCheckoutCustomFields();

        if (empty($customFields)) {
            return false;
        }

        return parent::_isActive();
    }
}
