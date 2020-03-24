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
 * Amazon Payments data helper
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 */
class Creativestyle_AmazonPayments_Helper_Data extends Mage_Core_Helper_Abstract
{
    const PAYMENT_OVERVIEW_URL_DE = 'https://payments.amazon.de/jr/your-account/orders/%s';
    const PAYMENT_OVERVIEW_URL_UK = 'https://payments.amazon.co.uk/jr/your-account/orders/%s';
    const PAYMENT_OVERVIEW_URL_FR = 'https://payments.amazon.fr/jr/your-account/orders/%s';
    const PAYMENT_OVERVIEW_URL_IT = 'https://payments.amazon.it/jr/your-account/orders/%s';
    const PAYMENT_OVERVIEW_URL_ES = 'https://payments.amazon.es/jr/your-account/orders/%s';
    const PAYMENT_OVERVIEW_URL_US = 'https://payments.amazon.com/jr/your-account/orders/%s';

    /**
     * Returns instance of Amazon Payments config object
     *
     * @return Creativestyle_AmazonPayments_Model_Config
     */
    protected function _getConfig()
    {
        /** @var Creativestyle_AmazonPayments_Model_Config $config */
        $config = Mage::getSingleton('amazonpayments/config');
        return $config;
    }

    /**
     * Return URL of the Amazon Pay overview page, where the buyer can review his payment that was declined
     *
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    protected function _getDeclinedPaymentOverviewUrl($order)
    {
        switch ($this->_getConfig()->getAccountRegion($order->getStore())) {
            case 'GBP':
                return sprintf(self::PAYMENT_OVERVIEW_URL_UK, $order->getExtOrderId());
            case 'EUR_FR':
                return sprintf(self::PAYMENT_OVERVIEW_URL_FR, $order->getExtOrderId());
            case 'EUR_IT':
                return sprintf(self::PAYMENT_OVERVIEW_URL_IT, $order->getExtOrderId());
            case 'EUR_ES':
                return sprintf(self::PAYMENT_OVERVIEW_URL_ES, $order->getExtOrderId());
            case 'USD':
                return sprintf(self::PAYMENT_OVERVIEW_URL_US, $order->getExtOrderId());
            default:
                return sprintf(self::PAYMENT_OVERVIEW_URL_DE, $order->getExtOrderId());
        }
    }

    /**
     * Sends an email to the customer if authorization has been declined
     *
     * @param Mage_Sales_Model_Order $order
     * @return $this
     */
    public function sendAuthorizationDeclinedEmail($order)
    {
        /** @var Mage_Core_Model_Translate $translate */
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        /** @var Mage_Core_Model_Email_Template $mailTemplate */
        $mailTemplate = Mage::getModel('core/email_template');
        $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $order->getStore()->getId()))
            ->sendTransactional(
                $this->_getConfig()->getAuthorizationDeclinedEmailTemplate($order->getStore()->getId()),
                $this->_getConfig()->getAuthorizationDeclinedEmailIdentity($order->getStore()->getId()),
                $order->getCustomerEmail(),
                null,
                array(
                    'orderId' => $order->getIncrementId(),
                    'storeName' => $order->getStore()->getFrontendName(),
                    'customer' => $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
                    'paymentUrl' => $this->_getDeclinedPaymentOverviewUrl($order)
                )
            );
        $translate->setTranslateInline(true);
        return $this;
    }

    /**
     * Return array of all available Amazon payment methods
     *
     * @return array
     */
    public function getAvailablePaymentMethods() 
    {
        return array(
            'amazonpayments_advanced',
            'amazonpayments_advanced_sandbox'
        );
    }

    /**
     * Check if the current User Agent is specific for any mobile device
     *
     * @return bool
     */
    public function isMobileDevice() 
    {
        $userAgent = Mage::app()->getRequest()->getServer('HTTP_USER_AGENT');
        if (empty($userAgent)) {
            return false;
        }

        return preg_match(
            '/iPhone|iPod|BlackBerry|Palm|Googlebot-Mobile|Mobile'
                .'|mobile|mobi|Windows Mobile|Safari Mobile|Android|Opera Mini/',
            $userAgent
        );
    }

    public function isOnePageCheckout()
    {
        $request = Mage::app()->getRequest();
        $module = strtolower($request->getModuleName());
        $controller = strtolower($request->getControllerName());
        $action = strtolower($request->getActionName());
        $lpa = $request->getParam('lpa', null);
        if ($module == 'checkout' && $controller == 'onepage'
            && ($lpa || $action == 'savepayment')) {
            return true;
        }

        return false;
    }

    /**
     * Splits customer name into first and last name
     * and returns it as an object
     *
     * @param string $customerName
     * @param string $emptyValuePlaceholder
     * @return Varien_Object
     */
    public function explodeCustomerName($customerName, $emptyValuePlaceholder = 'n/a')
    {
        $explodedName = explode(' ', trim($customerName));
        $result = array();
        if (count($explodedName) > 1) {
            $result['firstname'] = array_shift($explodedName);
            $result['lastname'] = implode(' ', $explodedName);
        } else {
            $result['firstname'] = $emptyValuePlaceholder
                ? Mage::helper('amazonpayments')->__($emptyValuePlaceholder) : null;
            $result['lastname'] = reset($explodedName);
        }

        return new Varien_Object($result);
    }

    /**
     * Returns extension common CSS
     *
     * @return string|null
     */
    public function getHeadCss() 
    {
        if ($this->_getConfig()->isPayActive() || $this->_getConfig()->isLoginActive()) {
            return 'creativestyle/css/amazonpayments.css';
        }

        return null;
    }

    /**
     * Returns Amazon Pay widgets CSS
     *
     * @return string|null
     */
    public function getWidgetsCss() 
    {
        if ($this->_getConfig()->isPayActive()) {
            if ($this->_getConfig()->isResponsive()) {
                return 'creativestyle/css/amazonpayments-responsive-widgets.css';
            } else {
                return 'creativestyle/css/amazonpayments-widgets.css';
            }
        }

        return null;
    }

    /**
     * Returns Prototype Tooltip library JS
     *
     * @return string|null
     */
    public function getTooltipJs()
    {
        if ($this->_getConfig()->isPayActive()) {
            return 'prototype/tooltip.js';
        }

        return null;
    }

    /**
     * Returns Amazon Pay button HTML markup
     *
     * @param string|null $buttonType
     * @param string|null $buttonSize
     * @param string|null $buttonColor
     * @param string|null $idSuffix
     * @return string
     */
    public function getPayWithAmazonButton(
        $buttonType = null,
        $buttonSize = null,
        $buttonColor = null,
        $idSuffix = null
    ) {
        /** @var Mage_Core_Model_Layout $layout */
        $layout = Mage::getSingleton('core/layout');
        /** @var Creativestyle_AmazonPayments_Block_Pay_Button $block */
        $block = $layout->createBlock('amazonpayments/pay_button');

        return $block->setButtonType($buttonType)
            ->setButtonSize($buttonSize)
            ->setButtonColor($buttonColor)
            ->setIdSuffix($idSuffix)
            ->toHtml();
    }

    /**
     * Returns Login with Amazon button HTML markup
     *
     * @param string|null $buttonType
     * @param string|null $buttonSize
     * @param string|null $buttonColor
     * @param string|null $idSuffix
     * @return string
     */
    public function getLoginWithAmazonButton(
        $buttonType = null,
        $buttonSize = null,
        $buttonColor = null,
        $idSuffix = null
    ) {
        /** @var Mage_Core_Model_Layout $layout */
        $layout = Mage::getSingleton('core/layout');
        /** @var Creativestyle_AmazonPayments_Block_Login_Button $block */
        $block = $layout->createBlock('amazonpayments/login_button');

        return $block->setButtonType($buttonType)
            ->setButtonSize($buttonSize)
            ->setButtonColor($buttonColor)
            ->setIdSuffix($idSuffix)
            ->toHtml();
    }
}
