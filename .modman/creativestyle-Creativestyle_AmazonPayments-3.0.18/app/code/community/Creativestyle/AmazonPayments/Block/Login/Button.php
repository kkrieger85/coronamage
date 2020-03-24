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

/**
 * Login with Amazon button block
 *
 * @method string|null getMoveBefore()
 * @method $this setMoveBefore(string $value)
 */
class Creativestyle_AmazonPayments_Block_Login_Button extends Creativestyle_AmazonPayments_Block_Login_Abstract
{
    const WIDGET_CONTAINER_ID_PREFIX = 'loginButtonWidget';
    const WIDGET_CONTAINER_CLASS = 'loginButtonWidget';

    /**
     * @inheritdoc
     */
    protected $_containerIdPrefix = self::WIDGET_CONTAINER_ID_PREFIX;

    /**
     * @inheritdoc
     */
    protected $_containerClass = self::WIDGET_CONTAINER_CLASS;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        if (!$this->hasData('template')) {
            $this->setTemplate('creativestyle/amazonpayments/login/button.phtml');
        }
    }

    /**
     * @inheritdoc
     */
    protected function _isActive()
    {
        if ($this->_getCustomerSession()->isLoggedIn()
            && $this->_getCustomerSession()->getCustomer()->getAmazonUserId()) {
            return false;
        }

        return parent::_isActive();
    }
}
