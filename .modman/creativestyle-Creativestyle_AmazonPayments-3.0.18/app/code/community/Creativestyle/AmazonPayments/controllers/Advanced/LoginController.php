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
class Creativestyle_AmazonPayments_Advanced_LoginController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->_forward('index', 'login');
    }

    public function redirectAction()
    {
        $this->_forward('redirect', 'login');
    }

    public function disconnectAction()
    {
        $this->_forward('disconnect', 'login');
    }
}
