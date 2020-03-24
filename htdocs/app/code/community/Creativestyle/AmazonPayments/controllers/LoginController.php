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

class Creativestyle_AmazonPayments_LoginController extends Creativestyle_AmazonPayments_Controller_Action implements
    Creativestyle_AmazonPayments_Controller_LoginInterface
{
    const ACCESS_TOKEN_PARAM_NAME = 'access_token';
    const STATE_PARAM_NAME = 'state';
    const ERROR_PARAM_NAME = 'error';
    const PASSWORD_PARAM_NAME = 'password';

    const LOGIN_TARGET = 'login';
    const CHECKOUT_TARGET = 'checkout';

    /**
     * @return string
     */
    protected function _extractAccessTokenFromUrl()
    {
        $accessToken = $this->getRequest()->getParam(self::ACCESS_TOKEN_PARAM_NAME, null);
        $accessToken = str_replace('|', '%7C', $accessToken);
        return $accessToken;
    }

    /**
     * @return string
     */
    protected function _extractStateFromUrl()
    {
        return strtolower($this->getRequest()->getParam(self::STATE_PARAM_NAME, null));
    }

    /**
     * @return string
     */
    protected function _extractTargetFromUrl()
    {
        return $this->_extractStateFromUrl();
    }

    /**
     * @return Creativestyle_AmazonPayments_Model_Service_Login
     */
    protected function _getLoginService()
    {
        /** @var Creativestyle_AmazonPayments_Model_Service_Login $loginService */
        $loginService = Mage::getSingleton('amazonpayments/service_login');
        return $loginService;
    }

    /**
     * Returns checkout session instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');
        return $session;
    }

    /**
     * Returns customer session instance
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        /** @var Mage_Customer_Model_Session $session */
        $session = Mage::getSingleton('customer/session');
        return $session;
    }

    /**
     * @return Mage_Checkout_Model_Session|Mage_Customer_Model_Session
     */
    protected function _getTargetSession()
    {
        switch ($this->_extractTargetFromUrl()) {
            case self::CHECKOUT_TARGET:
                return $this->_getCheckoutSession();
            default:
                return $this->_getCustomerSession();
        }
    }

    /**
     * @return string
     */
    protected function _getSuccessUrl()
    {
        switch ($this->_extractTargetFromUrl()) {
            case self::CHECKOUT_TARGET:
                return $this->_getUrl()->getPaySuccessUrl($this->_extractAccessTokenFromUrl());
            default:
                return $this->_getUrl()->getLoginSuccessUrl();
        }
    }

    /**
     * @return string
     */
    protected function _getFailureUrl()
    {
        switch ($this->_extractTargetFromUrl()) {
            case self::CHECKOUT_TARGET:
                return $this->_getUrl()->getPayFailureUrl();
            default:
                return $this->_getUrl()->getLoginFailureUrl();
        }
    }

    public function preDispatch()
    {
        parent::preDispatch();
        if (!$this->_getConfig()->isLoginActive()) {
            $this->_forward('noRoute');
        }
    }

    /**
     * @inheritdoc
     */
    public function success()
    {
        $this->_redirectUrl($this->_getSuccessUrl());
    }

    /**
     * @inheritdoc
     */
    public function accountConfirm($email)
    {
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();
        $update->addHandle('amazonpayments_account_confirm');
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_initLayoutMessages('customer/session');

        /** @var Creativestyle_AmazonPayments_Block_Login_Account_Confirm $formBlock */
        $formBlock = $this->getLayout()->getBlock('amazonpayments_login_account_confirm');
        if ($formBlock) {
            $formBlock->setData('back_url', $this->_getRefererUrl());
            $formBlock->setData('username', $email);
        }

        $this->renderLayout();
    }

    /**
     * @inheritdoc
     */
    public function missingAttributes($userProfile)
    {
        $accountPost = $this->getRequest()->getPost('account', array());

        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();
        $update->addHandle('amazonpayments_account_update');
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_initLayoutMessages('customer/session');

        $customerName = $this->_getHelper()->explodeCustomerName($userProfile->getName());
        $formData = new Varien_Object($accountPost);
        if (!$formData->getData('firstname')) {
            $formData->setData('firstname', $customerName->getData('firstname'));
        }

        if (!$formData->getData('lastname')) {
            $formData->setData('lastname', $customerName->getData('lastname'));
        }

        /** @var Creativestyle_AmazonPayments_Block_Login_Account_Update $formBlock */
        $formBlock = $this->getLayout()->getBlock('amazonpayments_login_account_update');
        if ($formBlock) {
            $formBlock->setData('back_url', $this->_getRefererUrl());
            $formBlock->setData('field_name_format', 'account[%s]');
            $formBlock->setData('form_data', $formData);
        }

        $this->renderLayout();
    }

    public function indexAction()
    {
        $accessToken = $this->_extractAccessTokenFromUrl();
        if (null !== $accessToken) {
            $accessToken = urldecode($accessToken);
            try {
                $loginPost = $this->getRequest()->getPost('login', array());
                $accountPost = $this->getRequest()->getPost('account', array());

                $this->_getLoginService()
                    ->authenticate($accessToken)
                    ->connect(
                        $this,
                        isset($loginPost[self::PASSWORD_PARAM_NAME]) ? $loginPost[self::PASSWORD_PARAM_NAME] : null,
                        $accountPost
                    );

                return;
            } catch (Exception $e) {
                Creativestyle_AmazonPayments_Model_Logger::logException($e);
                $this->_getTargetSession()->addError(
                    $this->__(
                        'There was an error connecting your Amazon account. Please contact us or try again later.'
                    )
                );
                $this->_redirectReferer();
                return;
            }
        } elseif ($error = $this->getRequest()->getParam(self::ERROR_PARAM_NAME, false)) {
            $this->_getTargetSession()->addError(
                $this->__('You have aborted the login with Amazon. Please contact us or try again.')
            );
            $this->_redirectUrl($this->_getFailureUrl());
            return;
        }

        $this->_forward('noRoute');
    }

    public function redirectAction()
    {
        $this->loadLayout();
        /** @var Mage_Page_Block_Html_Head $headBlock */
        $headBlock = $this->getLayout()->getBlock('head');
        $headBlock->setTitle($this->__('Login with Amazon'));
        $this->renderLayout();
    }

    public function disconnectAction()
    {
        if ($customer = $this->_getCustomerSession()->getCustomer()) {
            if ($customer->getAmazonUserId()) {
                $customer->setAmazonUserId(null)->save();
            }
        }

        $this->_redirect('customer/account');
    }
}
