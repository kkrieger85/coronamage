<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2014 - 2018 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2014 - 2018 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_Model_Config
{
    const XML_PATH_ACCOUNT_MERCHANT_ID          = 'amazonpayments/account/merchant_id';
    const XML_PATH_ACCOUNT_ACCESS_KEY           = 'amazonpayments/account/access_key';
    const XML_PATH_ACCOUNT_SECRET_KEY           = 'amazonpayments/account/secret_key';
    const XML_PATH_LOGIN_CLIENT_ID              = 'amazonpayments/account/client_id';
    const XML_PATH_ACCOUNT_REGION               = 'amazonpayments/account/region';

    const XML_PATH_GENERAL_ACTIVE               = 'amazonpayments/general/active';
    const XML_PATH_GENERAL_CHECKOUT_TYPE        = 'amazonpayments/general/checkout_type';
    const XML_PATH_GENERAL_ENVIRONMENT          = 'amazonpayments/general/environment';
    const XML_PATH_GENERAL_SANDBOX              = 'amazonpayments/general/sandbox';
    const XML_PATH_GENERAL_SANDBOX_TOOLBOX      = 'amazonpayments/general/sandbox_toolbox';
    const XML_PATH_GENERAL_PAYMENT_ACTION       = 'amazonpayments/general/payment_action';
    const XML_PATH_GENERAL_AUTHORIZATION_MODE   = 'amazonpayments/general/authorization_mode';
    const XML_PATH_GENERAL_IPN_ACTIVE           = 'amazonpayments/general/ipn_active';

    const XML_PATH_GENERAL_RECENT_POLLED_TXN    = 'amazonpayments/general/recent_polled_transaction';

    const XML_PATH_LOGIN_ACTIVE                 = 'amazonpayments/general/login_active';
    const XML_PATH_LOGIN_LANGUAGE               = 'amazonpayments/general/language';
    const XML_PATH_LOGIN_AUTHENTICATION         = 'amazonpayments/general/authentication';

    const XML_PATH_PRODUCT_PAGE_ACTIVE          = 'amazonpayments/general/product_page_active';

    const XML_PATH_STORE_NAME                   = 'amazonpayments/store/name';
    const XML_PATH_SOFT_DESCRIPTOR              = 'amazonpayments/store/soft_descriptor';

    const XML_PATH_EMAIL_ORDER_CONFIRMATION     = 'amazonpayments/email/order_confirmation';
    const XML_PATH_EMAIL_DECLINED_TEMPLATE      = 'amazonpayments/email/authorization_declined_template';
    const XML_PATH_EMAIL_DECLINED_IDENTITY      = 'amazonpayments/email/authorization_declined_identity';

    const XML_PATH_DESIGN_BUTTON_SIZE           = 'amazonpayments/design_pay/button_size';
    const XML_PATH_DESIGN_BUTTON_COLOR          = 'amazonpayments/design_pay/button_color';

    const XML_PATH_DESIGN_LOGIN_BUTTON_TYPE     = 'amazonpayments/design_login/login_button_type';
    const XML_PATH_DESIGN_LOGIN_BUTTON_SIZE     = 'amazonpayments/design_login/login_button_size';
    const XML_PATH_DESIGN_LOGIN_BUTTON_COLOR    = 'amazonpayments/design_login/login_button_color';
    const XML_PATH_DESIGN_PAY_BUTTON_TYPE       = 'amazonpayments/design_login/pay_button_type';
    const XML_PATH_DESIGN_PAY_BUTTON_SIZE       = 'amazonpayments/design_login/pay_button_size';
    const XML_PATH_DESIGN_PAY_BUTTON_COLOR      = 'amazonpayments/design_login/pay_button_color';

    const XML_PATH_DEVELOPER_ALLOWED_IPS        = 'amazonpayments/developer/allowed_ips';
    const XML_PATH_DEVELOPER_LOG_ACTIVE         = 'amazonpayments/developer/log_active';

    /**
     * Custom order statuses
     */
    const XML_PATH_GENERAL_NEW_ORDER_STATUS             = 'amazonpayments/general/new_order_status';
    const XML_PATH_GENERAL_ORDER_STATUS                 = 'amazonpayments/general/authorized_order_status';
    const XML_PATH_INVALID_PAYMENT_METHOD_ORDER_STATUS  = 'amazonpayments/general/invalid_payment_method_order_status';
    const XML_PATH_TRANSACTION_TIMED_OUT_ORDER_STATUS   = 'amazonpayments/general/transaction_timed_out_order_status';
    const XML_PATH_AMAZON_REJECTED_ORDER_STATUS         = 'amazonpayments/general/amazon_rejected_order_status';
    const XML_PATH_PROCESSING_FAILURE_ORDER_STATUS      = 'amazonpayments/general/processing_failure_order_status';

    /**
     * Alexa Delivery Tracking
     */
    const XML_PATH_DELIVERY_TRACKING_ACTIVE             = 'amazonpayments/delivery_tracking/active';
    const XML_PATH_DELIVERY_TRACKING_PRIVATE_KEY        = 'amazonpayments/delivery_tracking/private_key';
    const XML_PATH_DELIVERY_TRACKING_PUBLIC_KEY         = 'amazonpayments/delivery_tracking/public_key';
    const XML_PATH_DELIVERY_TRACKING_PUBLIC_KEY_ID      = 'amazonpayments/delivery_tracking/public_key_id';
    const XML_PATH_DELIVERY_TRACKING_CARRIER_CODES      = 'amazonpayments/delivery_tracking/carrier_codes';

    /**
     * @deprecated
     */
    const XML_PATH_DESIGN_RESPONSIVE            = 'amazonpayments/design/responsive';
    const XML_PATH_DESIGN_ADDRESS_WIDTH         = 'amazonpayments/design/address_width';
    const XML_PATH_DESIGN_ADDRESS_HEIGHT        = 'amazonpayments/design/address_height';
    const XML_PATH_DESIGN_PAYMENT_WIDTH         = 'amazonpayments/design/payment_width';
    const XML_PATH_DESIGN_PAYMENT_HEIGHT        = 'amazonpayments/design/payment_height';

    const WIDGET_FORMAT_STRING                  = '%s/OffAmazonPayments/%s%s/lpa/js/Widgets.js';

    /**
     * Global config data array
     *
     * @var array|null
     */
    protected $_globalConfigData = null;

    /**
     * Returns configured authentication experience
     *
     * @param mixed|null $store
     * @return string
     */
    protected function _getAuthenticationExperience($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_LOGIN_AUTHENTICATION, $store);
    }

    /**
     * @return Mage_Core_Model_Encryption
     */
    protected function _getEncryptionModel()
    {
        /** @var Mage_Core_Model_Encryption $encryption */
        $encryption = Mage::getSingleton('core/encryption');
        return $encryption;
    }

    /**
     * Checks whether Amazon Pay is enabled
     *
     * @param mixed|null $store
     * @return bool
     */
    public function isPayActive($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_GENERAL_ACTIVE, $store);
    }

    /**
     * Checks whether Login with Amazon is enabled
     *
     * @param mixed|null $store
     * @return bool
     */
    public function isLoginActive($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_LOGIN_ACTIVE, $store);
    }

    /**
     * Checks whether Amazon Pay button shall be
     * displayed on the product details page
     *
     * @param mixed|null $store
     * @return bool
     */
    public function isPayActiveOnProductPage($store = null)
    {
        return $this->isPayActive($store) && $this->isLoginActive($store)
            && Mage::getStoreConfigFlag(self::XML_PATH_PRODUCT_PAGE_ACTIVE, $store);
    }

    /**
     * @param mixed|null $store
     * @return string
     */
    public function getCheckoutType($store = null)
    {
        return $this->isLoginActive($store)
            ? Mage::getStoreConfig(self::XML_PATH_GENERAL_CHECKOUT_TYPE, $store)
            : Creativestyle_AmazonPayments_Model_Lookup_CheckoutType::CHECKOUT_TYPE_AMAZON;
    }

    /**
     * Checks whether IPN is enabled
     *
     * @param mixed|null $store
     * @return bool
     */
    public function isIpnActive($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_GENERAL_IPN_ACTIVE, $store);
    }

    /**
     * Checks whether Alexa Delivery Tracking is enabled
     *
     * @param mixed|null $store
     * @return bool
     */
    public function isDeliveryTrackingActive($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_DELIVERY_TRACKING_ACTIVE, $store)
            && (bool)$this->getDeliveryTrackingPublicKeyId($store);
    }

    /**
     * Returns current run mode (sandbox vs. live)
     *
     * @param mixed|null $store
     * @return bool
     */
    public function getEnvironment($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_GENERAL_ENVIRONMENT, $store);
    }

    /**
     * Checks whether extension runs in sandbox mode
     *
     * @param mixed|null $store
     * @return bool
     */
    public function isSandboxActive($store = null)
    {
        return $this->getEnvironment($store) ==
            Creativestyle_AmazonPayments_Model_Lookup_Environment::ENVIRONMENT_SANDBOX;
    }

    /**
     * Checks whether simulation toolbox shall be displayed in the checkout
     *
     * @param mixed|null $store
     * @return bool
     */
    public function isSandboxToolboxActive($store = null)
    {
        return $this->isSandboxActive($store)
            && Mage::getStoreConfigFlag(self::XML_PATH_GENERAL_SANDBOX_TOOLBOX, $store);
    }

    /**
     * Checks whether debug logging is enabled
     *
     * @param mixed|null $store
     * @return bool
     */
    public function isLoggingActive($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_DEVELOPER_LOG_ACTIVE, $store);
    }

    /**
     * Returns Merchant ID for the configured Amazon merchant account
     *
     * @param mixed|null $store
     * @return string
     */
    public function getMerchantId($store = null)
    {
        return trim(strtoupper(Mage::getStoreConfig(self::XML_PATH_ACCOUNT_MERCHANT_ID, $store)));
    }

    /**
     * Returns Access Key for the configured Amazon merchant account
     *
     * @param mixed|null $store
     * @return string
     */
    public function getAccessKey($store = null)
    {
        return trim(Mage::getStoreConfig(self::XML_PATH_ACCOUNT_ACCESS_KEY, $store));
    }

    /**
     * Returns Secret Key for the configured Amazon merchant account
     *
     * @param mixed|null $store
     * @return string
     */
    public function getSecretKey($store = null)
    {
        return trim(Mage::getStoreConfig(self::XML_PATH_ACCOUNT_SECRET_KEY, $store));
    }

    /**
     * Returns Amazon app client ID
     *
     * @param mixed|null $store
     * @return string
     */
    public function getClientId($store = null)
    {
        return trim(Mage::getStoreConfig(self::XML_PATH_LOGIN_CLIENT_ID, $store));
    }

    /**
     * @param mixed|null $store
     * @return string|null
     */
    public function getAccountRegion($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ACCOUNT_REGION, $store);
    }

    /**
     * Returns merchant's region
     *
     * @param mixed|null $store
     * @return string|null
     */
    public function getRegion($store = null)
    {
        switch ($this->getAccountRegion($store)) {
            case 'EUR':
            case 'EUR_DE':
            case 'EUR_FR':
            case 'EUR_IT':
            case 'EUR_ES':
                return 'de';
            case 'GBP':
                return 'uk';
            case 'USD':
                return 'us';
            case 'JPY':
                return 'jp';
            default:
                return null;
        }
    }

    /**
     * Returns display language for Amazon widgets
     *
     * @param mixed|null $store
     * @return string|null
     */
    public function getDisplayLanguage($store = null)
    {
        $displayLanguage = Mage::getStoreConfig(self::XML_PATH_LOGIN_LANGUAGE, $store);
        if (!$displayLanguage) {
            /** @var Creativestyle_AmazonPayments_Model_Lookup_Language $languageLookupModel */
            $languageLookupModel = Mage::getSingleton('amazonpayments/lookup_language');
            $displayLanguage = $languageLookupModel->getLanguageByLocale();
        }

        return $displayLanguage;
    }

    /**
     * Checks whether authentication experience is set to automatic mode
     *
     * @param mixed|null $store
     * @return bool
     */
    public function isAutoAuthenticationExperience($store = null)
    {
        return $this->_getAuthenticationExperience($store)
            == Creativestyle_AmazonPayments_Model_Lookup_Authentication::AUTO_EXPERIENCE;
    }

    /**
     * Checks whether authentication experience is set to popup
     *
     * @param mixed|null $store
     * @return bool
     */
    public function isPopupAuthenticationExperience($store = null)
    {
        return $this->_getAuthenticationExperience($store)
            == Creativestyle_AmazonPayments_Model_Lookup_Authentication::POPUP_EXPERIENCE;
    }

    /**
     * Checks whether authentication experience is set to redirect
     *
     * @param mixed|null $store
     * @return bool
     */
    public function isRedirectAuthenticationExperience($store = null)
    {
        return $this->_getAuthenticationExperience($store)
            == Creativestyle_AmazonPayments_Model_Lookup_Authentication::REDIRECT_EXPERIENCE;
    }

    /**
     * Returns configured payment action
     *
     * @param mixed|null $store
     * @return string
     */
    public function getPaymentAction($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_GENERAL_PAYMENT_ACTION, $store);
    }

    /**
     * Checks whether order amount shall be authorized immediately after the order is placed
     *
     * @param mixed|null $store
     * @return bool
     */
    public function authorizeImmediately($store = null)
    {
        return in_array(
            $this->getPaymentAction($store),
            array(
                Creativestyle_AmazonPayments_Model_Payment_Abstract::ACTION_AUTHORIZE,
                Creativestyle_AmazonPayments_Model_Payment_Abstract::ACTION_AUTHORIZE_CAPTURE
            )
        );
    }

    /**
     * Checks whether order amount shall be captured immediately after the order is placed
     *
     * @param mixed|null $store
     * @return bool
     */
    public function captureImmediately($store = null)
    {
        return $this->getPaymentAction($store)
            == Creativestyle_AmazonPayments_Model_Payment_Abstract::ACTION_AUTHORIZE_CAPTURE;
    }

    /**
     * Checks whether manual authorization is allowed
     *
     * @param mixed|null $store
     * @return bool
     */
    public function isManualAuthorizationAllowed($store = null)
    {
        return $this->getPaymentAction($store)
            == Creativestyle_AmazonPayments_Model_Payment_Abstract::ACTION_MANUAL;
    }

    /**
     * Checks whether shop is allowed to process the payment
     *
     * @param mixed|null $store
     * @return bool
     */
    public function isPaymentProcessingAllowed($store = null)
    {
        return $this->getPaymentAction($store)
            != Creativestyle_AmazonPayments_Model_Payment_Abstract::ACTION_ERP;
    }

    /**
     * Returns authorization request mode (sync, async)
     *
     * @param mixed|null $store
     * @return string
     */
    public function getAuthorizationMode($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_GENERAL_AUTHORIZATION_MODE, $store);
    }

    /**
     * Checks if authorization should be requested synchronously
     *
     * @param mixed|null $store
     * @return bool
     */
    public function isAuthorizationSynchronous($store = null)
    {
        return in_array(
            $this->getAuthorizationMode($store),
            array(
                Creativestyle_AmazonPayments_Model_Lookup_AuthorizationMode::AUTO,
                Creativestyle_AmazonPayments_Model_Lookup_AuthorizationMode::SYNCHRONOUS
            )
        );
    }

    /**
     * Checks if authorization should be re-requested asynchronously,
     * after synchronous authorization fails with TransactionTimedOut
     * declined state
     *
     * @param mixed|null $store
     * @return bool
     */
    public function isAuthorizationOmnichronous($store = null)
    {
        return in_array(
            $this->getAuthorizationMode($store),
            array(
                Creativestyle_AmazonPayments_Model_Lookup_AuthorizationMode::AUTO
            )
        );
    }

    /**
     * Checks whether checkout widgets are configured to be
     * displayed in responsive mode

     * @param mixed|null $store
     * @return bool
     * @deprecated
     */
    public function isResponsive($store = null)
    {
        return true;
    }

    /**
     * @param mixed|null $store
     * @return string
     */
    public function getPayButtonSize($store = null)
    {
        if ($this->isLoginActive()) {
            return Mage::getStoreConfig(self::XML_PATH_DESIGN_PAY_BUTTON_SIZE, $store);
        }

        return Mage::getStoreConfig(self::XML_PATH_DESIGN_BUTTON_SIZE, $store);
    }

    /**
     * @param mixed|null $store
     * @return string
     */
    public function getPayButtonColor($store = null)
    {
        if ($this->isLoginActive()) {
            return Mage::getStoreConfig(self::XML_PATH_DESIGN_PAY_BUTTON_COLOR, $store);
        }

        return Mage::getStoreConfig(self::XML_PATH_DESIGN_BUTTON_COLOR, $store);
    }

    /**
     * @param mixed|null $store
     * @return string|null
     */
    public function getPayButtonUrl($store = null)
    {
        if (!$this->isLoginActive()) {
            $buttonUrls = $this->getGlobalConfigData('button_urls');
            $env = $this->getEnvironment($store);
            if (isset($buttonUrls[$this->getRegion($store)][$env])) {
                return sprintf(
                    '%s?sellerId=%s&amp;size=%s&amp;color=%s',
                    $buttonUrls[$this->getRegion($store)][$env],
                    $this->getMerchantId($store),
                    $this->getPayButtonSize($store),
                    $this->getPayButtonColor($store)
                );
            }
        }

        return null;
    }

    /**
     * Returns Amazon Pay button design params
     *
     * @param mixed|null $store
     * @return array
     */
    public function getPayButtonDesign($store = null)
    {
        return array(
            'type' => Mage::getStoreConfig(self::XML_PATH_DESIGN_PAY_BUTTON_TYPE, $store),
            'size' => Mage::getStoreConfig(self::XML_PATH_DESIGN_PAY_BUTTON_SIZE, $store),
            'color' => Mage::getStoreConfig(self::XML_PATH_DESIGN_PAY_BUTTON_COLOR, $store)
        );
    }

    /**
     * Returns Login with Amazon button design params
     *
     * @param mixed|null $store
     * @return array
     */
    public function getLoginButtonDesign($store = null)
    {
        return array(
            'type' => Mage::getStoreConfig(self::XML_PATH_DESIGN_LOGIN_BUTTON_TYPE, $store),
            'size' => Mage::getStoreConfig(self::XML_PATH_DESIGN_LOGIN_BUTTON_SIZE, $store),
            'color' => Mage::getStoreConfig(self::XML_PATH_DESIGN_LOGIN_BUTTON_COLOR, $store)
        );
    }

    /**
     * Returns status for newly created order
     *
     * @param mixed|null $store
     * @return string
     */
    public function getNewOrderStatus($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_GENERAL_NEW_ORDER_STATUS, $store);
    }

    /**
     * Returns status for order with confirmed authorization
     *
     * @param mixed|null $store
     * @return string
     */
    public function getAuthorizedOrderStatus($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_GENERAL_ORDER_STATUS, $store);
    }

    /**
     * Returns status for the order on hold
     *
     * @param mixed|null $store
     * @return string
     * @throws Varien_Exception
     */
    public function getHoldedOrderStatus($store = null)
    {
        return Mage::getModel('sales/order_status')
            ->setStore($store)
            ->loadDefaultByState(Mage_Sales_Model_Order::STATE_HOLDED)
            ->getStatus();
    }

    /**
     * Returns order status for declined authorization
     * with InvalidPaymentMethod reason code
     *
     * @param mixed|null $store
     * @return string
     */
    public function getInvalidPaymentMethodOrderStatus($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_INVALID_PAYMENT_METHOD_ORDER_STATUS, $store);
    }

    /**
     * Returns order status for declined authorization
     * with TransactionTimedOut reason code
     *
     * @param mixed|null $store
     * @return string
     */
    public function getTransactionTimedOutOrderStatus($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_TRANSACTION_TIMED_OUT_ORDER_STATUS, $store);
    }

    /**
     * Returns order status for declined authorization
     * with AmazonRejected reason code
     *
     * @param mixed|null $store
     * @return string
     */
    public function getAmazonRejectedOrderStatus($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_AMAZON_REJECTED_ORDER_STATUS, $store);
    }

    /**
     * Returns order status for declined authorization
     * with ProcessingFailure reason code
     *
     * @param mixed|null $store
     * @return string
     */
    public function getProcessingFailureOrderStatus($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_PROCESSING_FAILURE_ORDER_STATUS, $store);
    }

    /**
     * Returns e-mail template for declined authorization
     *
     * @param mixed|null $store
     * @return string
     */
    public function getAuthorizationDeclinedEmailTemplate($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_EMAIL_DECLINED_TEMPLATE, $store);
    }

    /**
     * Returns e-mail sender identity for declined authorization
     *
     * @param mixed|null $store
     * @return string
     */
    public function getAuthorizationDeclinedEmailIdentity($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_EMAIL_DECLINED_IDENTITY, $store);
    }

    /**
     * Checks whether current requester IP is allowed to display Amazon widgets
     *
     * @param mixed|null $store
     * @return bool
     */
    public function isCurrentIpAllowed($store = null)
    {
        $allowedIps = trim(Mage::getStoreConfig(self::XML_PATH_DEVELOPER_ALLOWED_IPS, $store), ' ,');
        if ($allowedIps) {
            $allowedIps = explode(',', str_replace(' ', '', $allowedIps));
            if (is_array($allowedIps) && !empty($allowedIps)) {
                $currentIp = Mage::helper('core/http')->getRemoteAddr();
                if (Mage::app()->getRequest()->getServer('HTTP_X_FORWARDED_FOR')) {
                    $currentIp = Mage::app()->getRequest()->getServer('HTTP_X_FORWARDED_FOR');
                }

                return in_array($currentIp, $allowedIps);
            }
        }

        return true;
    }

    /**
     * Checks whether Amazon widgets are allowed to be shown
     * in the current shop locale
     *
     * @param mixed|null $store
     * @return bool
     */
    public function isCurrentLocaleAllowed($store = null)
    {
        // no locale restriction when Login is enabled
        if ($this->isLoginActive($store)) {
            return true;
        }

        $currentLocale = Mage::app()->getLocale()->getLocaleCode();
        $language = strtolower($currentLocale);
        if (strpos($language, '_') !== 0) {
            $language = substr($language, 0, strpos($language, '_'));
        }

        switch ($this->getRegion($store)) {
            case 'de':
                return ($language == 'de');
            case 'uk':
            case 'us':
                return ($language == 'en');
            case 'jp':
                return ($language == 'ja');
            default:
                return false;
        }
    }

    /**
     * Returns global config data
     *
     * @param string|null $key
     * @return string|array
     */
    public function getGlobalConfigData($key = null)
    {
        if (null === $this->_globalConfigData) {
            $this->_globalConfigData = Mage::getConfig()->getNode('global/creativestyle/amazonpayments')->asArray();
        }

        if (null !== $key) {
            if (array_key_exists($key, $this->_globalConfigData)) {
                return $this->_globalConfigData[$key];
            }

            return null;
        }

        return $this->_globalConfigData;
    }

    /**
     * Returns path to the custom CA bundle file
     *
     * @return string|null
     */
    public function getCaBundlePath()
    {
        return $this->getGlobalConfigData('ca_bundle');
    }

    /**
     * Returns Widgets JS library URL
     *
     * @param mixed|null $store
     * @return string
     */
    public function getWidgetJsUrl($store = null)
    {
        $region = $this->getRegion($store);
        $widgetHosts = $this->getGlobalConfigData('widget_hosts');
        $widgetUrl = sprintf(
            self::WIDGET_FORMAT_STRING,
            isset($widgetHosts[$region]) ? $widgetHosts[$region] : $widgetHosts['de'],
            $region,
            $this->isSandboxActive($store) ? '/sandbox' : ''
        );

        if (!$this->isLoginActive($store) || $region == 'us') {
            return str_replace('lpa/', '', $widgetUrl);
        }

        return $widgetUrl;
    }

    /**
     * Returns configured store name
     *
     * @param mixed|null $store
     * @return string
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getStoreName($store = null)
    {
        $storeName = Mage::getStoreConfig(self::XML_PATH_STORE_NAME, $store);
        $storeName = $storeName ? $storeName : Mage::app()->getStore($store)->getFrontendName();
        return $storeName;
    }

    /**
     * Returns configured soft descriptor
     *
     * @param mixed|null $store
     * @return string
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getSoftDescriptor($store = null)
    {
        $softDescriptor = Mage::getStoreConfig(self::XML_PATH_SOFT_DESCRIPTOR, $store);
        $softDescriptor = $softDescriptor ? $softDescriptor : $this->getStoreName($store);
        return substr($softDescriptor, 0, 16);
    }

    /**
     * Returns entity ID of the recently polled payment transaction
     *
     * @return string|null
     */
    public function getRecentPolledTransaction()
    {
        return Mage::getStoreConfig(self::XML_PATH_GENERAL_RECENT_POLLED_TXN);
    }

    /**
     * Sets recently polled payment transaction
     *
     * @param int $txnId
     */
    public function setRecentPolledTransaction($txnId)
    {
        Mage::getConfig()->saveConfig(self::XML_PATH_GENERAL_RECENT_POLLED_TXN, $txnId)->cleanCache();
    }

    /**
     * Returns CSV log fields delimiter character
     *
     * @return string
     */
    public function getLogDelimiter()
    {
        return ';';
    }

    /**
     * Returns CSV log fields enclosure character
     * @return string
     */
    public function getLogEnclosure()
    {
        return '"';
    }

    /**
     * @return bool
     */
    public function isMultiCurrencyEnabled()
    {
        return true;
    }

    public function getCheckoutCustomFields()
    {
        return $this->getGlobalConfigData('custom_fields');
    }

    /**
     * @return bool
     */
    public function isExtendedConfigEnabled()
    {
        return (bool)$this->getGlobalConfigData('extended_config');
    }

    /**
     * @return bool
     */
    public function isJsVersioningDisabled()
    {
        return (bool)$this->getGlobalConfigData('disable_js_versioning');
    }

    public function getDeliveryTrackingPublicKeyId($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_DELIVERY_TRACKING_PUBLIC_KEY_ID, $store);
    }

    /**
     * @param mixed|null $store
     * @return string|null
     */
    public function getDeliveryTrackingPrivateKey($store = null)
    {
        $encryptedKey = Mage::getStoreConfig(self::XML_PATH_DELIVERY_TRACKING_PRIVATE_KEY, $store);
        if ($encryptedKey) {
            return $this->_getEncryptionModel()->decrypt($encryptedKey);
        }

        return null;
    }

    /**
     * @param mixed|null $store
     * @return array
     */
    public function getDeliveryTrackingCarriers($store = null)
    {
        $carriersMap = array();
        $carrierCodes = Mage::getStoreConfig(self::XML_PATH_DELIVERY_TRACKING_CARRIER_CODES, $store);
        if ($carrierCodes) {
            $carrierCodes = call_user_func('unserialize', $carrierCodes);
            foreach ($carrierCodes as $carrier) {
                if (isset($carrier['magento']) && isset($carrier['amazon'])) {
                    $carriersMap[$carrier['magento']] = $carrier['amazon'];
                }
            }
        }

        return $carriersMap;
    }
}
