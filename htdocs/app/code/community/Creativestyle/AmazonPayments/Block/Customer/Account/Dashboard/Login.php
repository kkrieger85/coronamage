<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2017 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2017 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_Block_Customer_Account_Dashboard_Login
    extends Creativestyle_AmazonPayments_Block_Abstract
{
    /**
     * @inheritdoc
     */
    protected function _isActive() 
    {
        return $this->_getConfig()->isLoginActive();
    }

    /**
     * Checks whether Magento customer account is connected
     * with Amazon customer account
     *
     * @return bool
     */
    protected function isCustomerConnectedWithAmazon() 
    {
        return (bool)$this->_getCustomerSession()->getCustomer()->getAmazonUserId();
    }
}
