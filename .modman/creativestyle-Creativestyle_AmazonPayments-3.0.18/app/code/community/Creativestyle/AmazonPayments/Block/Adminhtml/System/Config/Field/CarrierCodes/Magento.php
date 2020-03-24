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

class Creativestyle_AmazonPayments_Block_Adminhtml_System_Config_Field_CarrierCodes_Magento extends Mage_Core_Block_Html_Select
{
    public function _toHtml()
    {
        /** @var Mage_Shipping_Model_Config $shippingConfig */
        $shippingConfig = Mage::getSingleton('shipping/config');
        /** @var Mage_Shipping_Model_Carrier_Abstract[] $carrierInstances */
        $carrierInstances = $shippingConfig->getAllCarriers();
        foreach ($carrierInstances as $code => $carrier) {
            $this->addOption(strtolower($code), $carrier->getConfigData('title'));
        }

        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
