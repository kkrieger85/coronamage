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

class Creativestyle_AmazonPayments_Block_Adminhtml_System_Config_Field_CarrierCodes_Amazon extends Mage_Core_Block_Html_Select
{
    public function _toHtml()
    {
        /** @var Creativestyle_AmazonPayments_Model_Lookup_CarrierCode $carrierCodes */
        $carrierCodes = Mage::getSingleton('amazonpayments/lookup_carrierCode');

        foreach ($carrierCodes->getOptions() as $code => $title) {
            $this->addOption($code, $title);
        }

        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
