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
class Creativestyle_AmazonPayments_Block_Payment_Info extends Mage_Payment_Block_Info
{
    /**
     * @inheritdoc
     */
    protected function _construct() 
    {
        parent::_construct();
        $this->setTemplate('creativestyle/amazonpayments/payment/info.phtml');
    }

    /**
     * @inheritdoc
     */
    public function toPdf()
    {
        $this->setTemplate('creativestyle/amazonpayments/payment/pdf/info.phtml');
        return $this->toHtml();
    }
}
