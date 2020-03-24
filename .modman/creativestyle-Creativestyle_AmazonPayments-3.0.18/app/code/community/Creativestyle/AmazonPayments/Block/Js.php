<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2014 - 2019 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2014 - 2019 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */

/**
 * Amazon Pay JS block
 *
 * @method $this setOrderReferenceId(string $orderReferenceId)
 * @method string getOrderReferenceId()
 * @method $this setAccessToken(string $accessToken)
 * @method string getAccessToken()
 * @method $this setJsAppPage(string $appPage)
 * @method string|null getJsAppPage()
 * @method $this setIsCheckout(int $value)
 * @method int getIsCheckout()
 * @method $this setIsLogout(int $value)
 * @method int getIsLogout()
 */
class Creativestyle_AmazonPayments_Block_Js extends Creativestyle_AmazonPayments_Block_Abstract
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        if (!$this->hasData('template')) {
            $this->setTemplate('creativestyle/amazonpayments/js.phtml');
        }
    }

    /**
     * Returns Widgets JS library URL
     *
     * @return string
     */
    public function getWidgetJsUrl()
    {
        return $this->_getConfig()->getWidgetJsUrl();
    }

    /**
     * Returns JS app configuration
     *
     * @return string
     * @throws Mage_Core_Model_Store_Exception
     * @deprecated
     */
    public function getJsConfig()
    {
        $jsConfig = array(
            'merchantId' => $this->getMerchantId(),
            'clientId' => $this->getClientId(),
            'region' => $this->getRegion(),
            'live' => !$this->isSandboxActive(),
            'popup' => $this->isPopupAuthenticationExperience(),
            'virtual' => $this->isQuoteVirtual(),
            'language' => $this->getDisplayLanguage(),
            'pay' => array(
                'selector' => $this->getPayButtonSelector(),
                'callbackUrl' => $this->getButtonCallbackUrl(),
                'design' => $this->getPayButtonDesignParams()
            ),
            'login' => $this->isLoginActive() ? array(
                'selector' => $this->getLoginButtonSelector(),
                'callbackUrl' => $this->getButtonCallbackUrl(),
                'design' => $this->getLoginButtonDesignParams()
            ) : null,
            'checkoutUrl' => $this->_getUrl()->getCheckoutUrl(),
            'addToCartUrl' => $this->getAddToCartUrl(),
            'currency' => Mage::app()->getStore()->getBaseCurrencyCode()
        );

        return $this->_jsonEncode($jsConfig);
    }

    /**
     * @return null|string
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getAddToCartUrl()
    {
        $params = array();
        if ($this->_isConnectionSecure()) {
            $params['_secure'] = true;
        }

        return $this->getUrl('amazonpayments/cart/add', $params);
    }

    /**
     * Returns callback URL for Amazon Pay button
     *
     * @return string|null
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getButtonCallbackUrl()
    {
        // no callback URL for APA button
        if (!$this->isLoginActive()) {
            return null;
        }

        if ($this->isPopupAuthenticationExperience()) {
            return $this->_getUrl()->getLoginCallbackUrl();
        }

        return $this->_getUrl()->getLoginRedirectUrl();
    }

    /**
     * Returns Amazon Pay button design params
     *
     * @return array|null
     */
    public function getPayButtonDesignParams()
    {
        return $this->_getConfig()->getPayButtonDesign();
    }

    /**
     * Returns Amazon Pay buttons DOM selector
     *
     * @return string
     */
    public function getPayButtonSelector()
    {
        return sprintf('.%s', Creativestyle_AmazonPayments_Block_Pay_Button::WIDGET_CONTAINER_CLASS);
    }

    /**
     * Returns Login with Amazon button design params
     *
     * @return array
     */
    public function getLoginButtonDesignParams()
    {
        return $this->_getConfig()->getLoginButtonDesign();
    }

    /**
     * Returns Login with Amazon buttons DOM selector
     *
     * @return string
     */
    public function getLoginButtonSelector()
    {
        return sprintf('.%s', Creativestyle_AmazonPayments_Block_Login_Button::WIDGET_CONTAINER_CLASS);
    }

    /**
     * Returns JSON-formatted checkout URLs
     *
     * @return string
     */
    public function getCheckoutUrls()
    {
        $urls = array(
            'saveShipping' => $this->_getUrl()->getCheckoutSaveShippingUrl(),
            'saveShippingMethod' => $this->_getUrl()->getCheckoutSaveShippingMethodUrl(),
            'saveOrder' => $this->_getUrl()->getCheckoutSaveOrderUrl(),
            'saveCoupon' => $this->_getUrl()->getCheckoutSaveCouponUrl(),
            'invalidPayment' => $this->_getUrl()->getCheckoutInvalidPaymentUrl(),
            'cancelOrderReference' => $this->_getUrl()->getCheckoutCancelOrderReferenceUrl(),
            'failure' => $this->_getUrl()->getCheckoutFailureUrl()
        );
        return $this->_jsonEncode($urls);
    }

    /**
     * @return bool
     */
    public function isOnePageCheckout()
    {
        return $this->_getHelper()->isOnePageCheckout();
    }

    /**
     * @param array $arrayToFilter
     * @return array
     */
    protected function _removeNullElementsFromArray(array $arrayToFilter)
    {
        return array_filter(
            $arrayToFilter,
            function ($element) {
                return null !== $element;
            }
        );
    }

    /**
     * @return array
     */
    protected function _getButtonsConfig()
    {
        return $this->_removeNullElementsFromArray(
            array(
                'pay' => array(
                    'selector' => $this->getPayButtonSelector(),
                    'design' => $this->getPayButtonDesignParams()
                ),
                'login' => $this->isLoginActive() ? array(
                    'selector' => $this->getLoginButtonSelector(),
                    'design' => $this->getLoginButtonDesignParams()
                ) : null
            )
        );
    }

    /**
     * @return array
     */
    protected function _getUrlsConfig()
    {
        return $this->_removeNullElementsFromArray(
            array(
                'checkout' => $this->_removeNullElementsFromArray(
                    array(
                        'saveShipping' => $this->isQuoteVirtual()
                            ? null
                            : $this->_getUrl()->getCheckoutSaveShippingUrl(),
                    )
                ),
                'login' => $this->isLoginActive() ? array(
                    'callback' => $this->_getUrl()->getLoginCallbackUrl(),
                    'success' => $this->_getUrl()->getLoginSuccessUrl(),
                    'failure' => $this->_getUrl()->getLoginFailureUrl(),
                ) : null,
                'pay' => $this->isLoginActive() ? null : array(
                    'checkout' => $this->_getUrl()->getPayCallbackUrl(),
                ),
            )
        );
    }

    /**
     * Returns JS app configuration
     *
     * @return string
     */
    public function getJsAppConfig()
    {
        $appConfig = $this->_removeNullElementsFromArray(
            array(
                'merchantId' => $this->getMerchantId(),
                'clientId' => $this->isLoginActive() ? $this->getClientId() : null,
                'region' => $this->getRegion(),
                'live' => !$this->isSandboxActive(),
                'popup' => $this->isPopupAuthenticationExperience(),
                'virtual' => $this->isQuoteVirtual(),
                'language' => $this->getDisplayLanguage(),
                'buttons' => $this->_getButtonsConfig(),
                'urls' => $this->_getUrlsConfig(),
                'checkoutUrl' => $this->getCheckoutUrl(),
                'addToCartUrl' => $this->getAddToCartUrl(),
                'currency' => $this->getCurrencyCode()
            )
        );

        return $this->_jsonEncode($appConfig);
    }

    /**
     * Returns the URL of the frontend app JS file
     *
     * @return string
     */
    public function getAppJsUrl() {
        $jsUrl = 'creativestyle/amazonpayments.min.js';

        if (!$this->_getConfig()->isJsVersioningDisabled()) {
            $jsUrl .= sprintf(
                '?v=%s',
                (string)Mage::getConfig()->getNode('modules/Creativestyle_AmazonPayments/version')
            );
        }

        return $this->getJsUrl($jsUrl);
    }

    /**
     * @return string
     */
    public function getJsAppSession()
    {
        $appSession = $this->_removeNullElementsFromArray(
            array(
                'orderReferenceId' => $this->getOrderReferenceId(),
                'accessToken' => $this->getAccessToken()
            )
        );

        return !empty($appSession) ? $this->_jsonEncode($appSession) : 'null';
    }
}
