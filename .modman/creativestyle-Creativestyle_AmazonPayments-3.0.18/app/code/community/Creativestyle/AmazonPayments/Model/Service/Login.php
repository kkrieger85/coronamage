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
class Creativestyle_AmazonPayments_Model_Service_Login
{
    const AMAZON_USER_ID_ATTRIBUTE = 'amazon_user_id';

    /**
     * @var string
     */
    private $_accessToken;

    /**
     * @var mixed
     */
    private $_store;

    /**
     * @var Creativestyle_AmazonPayments_Model_Login_UserProfile|null
     */
    private $_userProfile;

    /**
     * @var Mage_Customer_Model_Customer|null
     */
    private $_customer = null;

    /**
     * @return Creativestyle_AmazonPayments_Model_Api_Login
     */
    protected function _getApi()
    {
        /** @var Creativestyle_AmazonPayments_Model_Api_Login $api */
        $api = Mage::getSingleton('amazonpayments/api_login');
        return $api;
    }

    /**
     * @return Creativestyle_AmazonPayments_Model_Config
     */
    protected function _getConfig()
    {
        /** @var Creativestyle_AmazonPayments_Model_Config $config */
        $config = Mage::getSingleton('amazonpayments/config');
        return $config;
    }

    /**
     * @return Creativestyle_AmazonPayments_Helper_Data
     */
    protected function _getHelper()
    {
        /** @var Creativestyle_AmazonPayments_Helper_Data $helper */
        $helper = Mage::helper('amazonpayments');
        return $helper;
    }

    /**
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        /** @var Mage_Customer_Model_Session $session */
        $session = Mage::getSingleton('customer/session');
        return $session;
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');
        return $session;
    }

    /**
     * @return int|string|null
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function _getWebsiteId()
    {
        return Mage::app()->getStore($this->_store)->getWebsiteId();
    }

    /**
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _assertAmazonUserIdAttributeExists()
    {
        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attributeModel */
        $attributeModel = Mage::getResourceModel('catalog/eav_attribute')
            ->loadByCode('customer', self::AMAZON_USER_ID_ATTRIBUTE);
        if (!$attributeModel->getId()) {
            throw new Creativestyle_AmazonPayments_Exception(
                sprintf('[service::Login] %s customer attribute does not exist', self::AMAZON_USER_ID_ATTRIBUTE)
            );
        }

        return true;
    }

    /**
     * @return bool
     * @throws Creativestyle_AmazonPayments_Exception
     */
    protected function _assertUserIsAuthenticated()
    {
        if (null === $this->_userProfile || !$this->_userProfile->validate()) {
            throw new Creativestyle_AmazonPayments_Exception('[service::Login] User is not authenticated');
        }

        return true;
    }

    /**
     * @return Mage_Customer_Model_Customer|null
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function _getCustomerByUserId()
    {
        $this->_assertAmazonUserIdAttributeExists();

        if (null === $this->_customer) {
            /** @var Mage_Customer_Model_Customer $customer */
            $customer = Mage::getModel('customer/customer');

            /** @var Mage_Customer_Model_Resource_Customer_Collection $collection */
            $collection = $customer->getCollection()
                ->addAttributeToFilter(self::AMAZON_USER_ID_ATTRIBUTE, $this->getUserProfile()->getUserId())
                ->setPageSize(1);

            if ($customer->getSharingConfig()->isWebsiteScope()) {
                $collection->addAttributeToFilter('website_id', $this->_getWebsiteId());
            }

            if ($collection->count()) {
                $this->_customer = $collection->getFirstItem();
            }
        }

        return $this->_customer;
    }

    /**
     * @return Mage_Customer_Model_Customer|null
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function _getCustomerByEmail()
    {
        if (null === $this->_customer) {
            /** @var Mage_Customer_Model_Customer $customer */
            $customer = Mage::getModel('customer/customer');

            if ($customer->getSharingConfig()->isWebsiteScope()) {
                $customer->setData('website_id', $this->_getWebsiteId());
            }

            $customer->loadByEmail($this->getUserProfile()->getEmail());

            if ($customer->getId()) {
                $this->_customer = $customer;
            }
        }

        return $this->_customer;
    }

    /**
     * @return array
     */
    protected function _getCustomerMandatoryAttributes()
    {
        $mandatoryAttributes = array();
        /** @var Mage_Eav_Model_Config $eavConfig */
        $eavConfig = Mage::getSingleton('eav/config');
        foreach ($this->_getConfig()->getGlobalConfigData('customer_attributes') as $attributeCode => $attributeData) {
            $attributeModel = $eavConfig->getAttribute('customer', $attributeCode);
            if ($attributeModel instanceof Varien_Object) {
                if ($attributeModel->getIsRequired()) {
                    $mandatoryAttributes[] = $attributeCode;
                }
            }
        }

        return $mandatoryAttributes;
    }

    /**
     * @param array $accountData
     * @return Mage_Customer_Model_Customer
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Model_Store_Exception
     * @throws Exception
     */
    protected function _createCustomer($accountData = array())
    {
        $userProfile = $this->getUserProfile();
        $customerName = $this->_getHelper()->explodeCustomerName($userProfile->getName());

        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer');
        $password = $customer->generatePassword(8);
        $customer->setId(null)
            ->setSkipConfirmationIfEmail($userProfile->getEmail())
            ->setFirstname($customerName->getFirstname())
            ->setLastname($customerName->getLastname())
            ->setEmail($userProfile->getEmail())
            ->setPassword($password)
            ->setPasswordConfirmation($password)
            ->setConfirmation($password)
            ->setAmazonUserId($userProfile->getUserId());

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $customer->setData('website_id', $this->_getWebsiteId());
        }

        foreach ($accountData as $attribute => $value) {
            $customer->setData($attribute, $value);
        }

        // validate customer
        $validation = $customer->validate();
        if ($validation !== true && !empty($validation)) {
            $validation = implode(", ", $validation);
            throw new Creativestyle_AmazonPayments_Exception(
                sprintf('[service::Login] error while creating customer account: %s', $validation)
            );
        }

        $customer->save();

        return $customer;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     */
    protected function _setCustomerAsLoggedIn(Mage_Customer_Model_Customer $customer)
    {
        /**
         * The following operations on the quote model circumvent guest cart
         * items removal, invoked after the successful authentication within
         * the Magento session. It was introduced as a security fix in:
         *   - CE 1.9.4.2
         *   - EE 1.14.4.2
         *   - SUPEE-11155 patch
         *
         * @var Mage_Sales_Model_Quote $quote
         */
        $quote = $this->_getCheckoutSession()->getQuote();
        if (!$quote->getCustomerId()) {
            $quote->setCustomerId($customer->getId())->save();
        }

        $this->_getSession()->setCustomerAsLoggedIn($customer);
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param string $amazonUserId
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Exception
     */
    protected function _setCustomerUserId(Mage_Customer_Model_Customer $customer, $amazonUserId)
    {
        $this->_assertAmazonUserIdAttributeExists();
        $customer->setData(self::AMAZON_USER_ID_ATTRIBUTE, $amazonUserId);
        $customer->save();
    }

    /**
     * @param string $accessToken
     * @param mixed|null $store
     * @return $this
     * @throws Exception
     */
    public function authenticate($accessToken, $store = null)
    {
        $this->_userProfile = $this->_getApi()->getUserProfile($store, $accessToken);
        $this->_accessToken = $accessToken;
        $this->_store = $store;
        return $this;
    }

    /**
     * @return Creativestyle_AmazonPayments_Model_Login_UserProfile
     * @throws Creativestyle_AmazonPayments_Exception
     */
    public function getUserProfile()
    {
        $this->_assertUserIsAuthenticated();
        return $this->_userProfile;
    }

    /**
     * @param Creativestyle_AmazonPayments_Controller_LoginInterface $loginController
     * @param null $password
     * @param array $accountData
     * @throws Creativestyle_AmazonPayments_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    public function connect(
        Creativestyle_AmazonPayments_Controller_LoginInterface $loginController,
        $password = null,
        $accountData = array()
    ) {
        $this->_assertUserIsAuthenticated();

        $customer = $this->_getCustomerByUserId();
        if (null !== $customer) {
            $this->_setCustomerAsLoggedIn($customer);
            $loginController->success();
            return;
        }

        $customer = $this->_getCustomerByEmail();
        if (null !== $customer) {
            if ($this->_getSession()->isLoggedIn()) {
                $this->_setCustomerUserId($customer, $this->getUserProfile()->getUserId());
                $this->_setCustomerAsLoggedIn($customer);
                $loginController->success();
                return;
            } else {
                if (null !== $password) {
                    if ($customer->validatePassword($password)) {
                        $this->_setCustomerUserId($customer, $this->getUserProfile()->getUserId());
                        $this->_setCustomerAsLoggedIn($customer);
                        $loginController->success();
                        return;
                    } else {
                        $this->_getSession()->addError($this->_getHelper()->__('Invalid password'));
                    }
                }

                $loginController->accountConfirm($customer->getEmail());
                return;
            }
        }

        $customerAttributes = $this->_getCustomerMandatoryAttributes();
        $postedAttributes = array_keys($accountData);
        $attributesDiff = array_diff($customerAttributes, $postedAttributes);

        if (!(empty($customerAttributes) || empty($attributesDiff))) {
            $loginController->missingAttributes($this->getUserProfile());
            return;
        } else {
            $customer = $this->_createCustomer($accountData);
            $this->_setCustomerAsLoggedIn($customer);
            $loginController->success();
            return;
        }
    }
}
