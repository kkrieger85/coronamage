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

class Creativestyle_AmazonPayments_Model_Lookup_CarrierCode extends Creativestyle_AmazonPayments_Model_Lookup_Abstract
{
    public function toOptionArray()
    {
        if (null === $this->_options) {
            $skipNextRow = true;
            $csvPath = Mage::getModuleDir('data', 'Creativestyle_AmazonPayments') . DS . 'carriers.csv';
            if (($fileHandle = fopen($csvPath, 'r')) !== false) {
                while (($row = fgetcsv($fileHandle, 0, ',')) !== false) {
                    if ($skipNextRow) {
                        $skipNextRow = false;
                        continue;
                    }

                    $this->_options[] = array('value' => $row[1], 'label' => $row[0]);
                }
            }
        }

        return $this->_options;
    }
}
