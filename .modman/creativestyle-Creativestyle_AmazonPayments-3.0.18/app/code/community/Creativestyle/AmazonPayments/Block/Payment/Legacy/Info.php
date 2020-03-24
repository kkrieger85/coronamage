<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2016 - 2017 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2016 - 2017 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_Block_Payment_Legacy_Info extends Mage_Payment_Block_Info
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('creativestyle/amazonpayments/payment/legacy/info.phtml');
    }

    /**
     * @inheritdoc
     */
    public function toPdf()
    {
        $this->setTemplate('creativestyle/amazonpayments/payment/legacy/pdf/info.phtml');
        return $this->toHtml();
    }
}
