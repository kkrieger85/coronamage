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
class Creativestyle_AmazonPayments_Block_Checkout_SandboxToolbox extends
 Creativestyle_AmazonPayments_Block_Checkout_Abstract
{
    public function getSimulationOptions()
    {
        return $this->_jsonEncode(Creativestyle_AmazonPayments_Model_Simulator::getAvailableSimulations());
    }
}
