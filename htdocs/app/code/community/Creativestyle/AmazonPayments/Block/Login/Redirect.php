<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2015 - 2019 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2015 - 2019 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */

class Creativestyle_AmazonPayments_Block_Login_Redirect extends Creativestyle_AmazonPayments_Block_Login_Abstract
{
    /**
     * Returns name of the access token param passed along with the request
     *
     * @return string
     */
    public function getAccessTokenParamName()
    {
        return Creativestyle_AmazonPayments_LoginController::ACCESS_TOKEN_PARAM_NAME;
    }

    /**
     * Returns name of the state param passed along with the request
     *
     * @return string
     */
    public function getStateParamName()
    {
        return Creativestyle_AmazonPayments_LoginController::STATE_PARAM_NAME;
    }

    /**
     * Returns redirect URL
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->_getUrl()->getLoginCallbackUrl(
            array(
                $this->getAccessTokenParamName() => '%access_token',
                $this->getStateParamName() => '%state'
            )
        );
    }

    /**
     * Returns failure URL
     *
     * @return string
     */
    public function getFailureUrl()
    {
        if ($this->hasData('failure_url')) {
            return $this->getData('failure_url');
        }

        return $this->getUrl('customer/account/login');
    }
}
