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
 * Amazon Pay button block
 *
 * @method int getEnableOr()
 * @method $this setEnableOr(int $value)
 * @method int getIsProductButton()
 * @method $this setIsProductButton(int $value)
 * @method string getButtonType()
 * @method $this setButtonType(string $value)
 * @method string getButtonSize()
 * @method $this setButtonSize(string $value)
 * @method string getButtonColor()
 * @method $this setButtonColor(string $value)
 */
class Creativestyle_AmazonPayments_Block_Pay_Button extends Creativestyle_AmazonPayments_Block_Pay_Abstract
{
    const WIDGET_CONTAINER_ID_PREFIX = 'payButtonWidget';
    const WIDGET_CONTAINER_CLASS = 'payButtonWidget';

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
            $this->setTemplate('creativestyle/amazonpayments/pay/button.phtml');
        }
    }

    /**
     * @inheritdoc
     */
    protected function _isActive()
    {
        if ($this->getIsProductButton()) {
            if (!$this->isPayActiveOnProductPage()
                || !$this->_isCurrentIpAllowed()
                || !$this->_isCurrentLocaleAllowed()) {
                return false;
            }

            // hide for Login and popup enabled, when request wasn't secure
            if ($this->isLoginActive()
                && $this->isPopupAuthenticationExperience()
                && !$this->_isConnectionSecure()) {
                return false;
            }

            return true;
        }

        return parent::_isActive();
    }

    /**
     * @return null|string
     */
    public function getButtonWidgetUrl()
    {
        return $this->_getConfig()->getPayButtonUrl();
    }

    /**
     * @return bool
     */
    public function isCustomDesignSet()
    {
        return $this->hasData('button_type') || $this->hasData('button_size') || $this->hasData('button_color');
    }

    /**
     * @return array
     */
    public function getDataAttributes()
    {
        $dataAttributes = array();

        if ($this->getIsProductButton()) {
            $dataAttributes['is-product-button'] = 1;
            $dataAttributes['button-type'] =
                Creativestyle_AmazonPayments_Model_Lookup_Design_Button_Type_Pay::TYPE_FULL;
            $dataAttributes['button-size'] =
                Creativestyle_AmazonPayments_Model_Lookup_Design_Button_Size_LoginPay::SIZE_SMALL;
        }

        if ($buttonType = $this->getButtonType()) {
            $dataAttributes['button-type'] = $buttonType;
        }

        if ($buttonSize = $this->getButtonSize()) {
            $dataAttributes['button-size'] = $buttonSize;
        }

        if ($buttonColor = $this->getButtonColor()) {
            $dataAttributes['button-color'] = $buttonColor;
        }

        return $dataAttributes;
    }
}
