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

interface Creativestyle_AmazonPayments_Controller_LoginInterface
{
    /**
     * Action controller for the successful connection (either matching with or creation of the shop account)
     * with the Amazon account
     */
    public function success();

    /**
     * Action controller for the successfully matched Amazon account with an existing shop account
     * In this scenario, the buyer is required to authenticate with his shop account to confirm
     * he owns the shop account matched with the Amazon account
     *
     * @param string $email
     */
    public function accountConfirm($email);

    /**
     * Action controller for the successful connection with Amazon account, for the shops that require additional
     * customer attributes not provided in buyer data provided by Amazon
     *
     * @param Creativestyle_AmazonPayments_Model_Login_UserProfile $userProfile
     */
    public function missingAttributes($userProfile);
}
