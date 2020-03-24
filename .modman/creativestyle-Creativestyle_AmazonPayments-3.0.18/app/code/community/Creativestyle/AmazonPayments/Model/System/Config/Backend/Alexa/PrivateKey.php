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
class Creativestyle_AmazonPayments_Model_System_Config_Backend_Alexa_PrivateKey extends Mage_Core_Model_Config_Data
{
    /**
     * @return Mage_Core_Model_Encryption
     */
    protected function _getEncryptionModel()
    {
        /** @var Mage_Core_Model_Encryption $encryption */
        $encryption = Mage::getSingleton('core/encryption');
        return $encryption;
    }

    /**
     * @return Mage_Core_Model_Abstract
     * @throws Exception
     */
    public function save()
    {
        $value = $this->getValue();
        if ($value) {
            $this->setValue(trim($value) ? $this->_getEncryptionModel()->encrypt(trim($value)) : null);
            return parent::save();
        }

        return $this;
    }
}
