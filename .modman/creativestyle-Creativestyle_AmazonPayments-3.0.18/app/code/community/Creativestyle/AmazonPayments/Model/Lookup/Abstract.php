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
abstract class Creativestyle_AmazonPayments_Model_Lookup_Abstract extends Varien_Object
{
    /**
     * @var array|null
     */
    protected $_options = null;

    /**
     * @return array
     */
    public function getOptions()
    {
        $result = array();
        $_options = $this->toOptionArray();
        foreach ($_options as $_option) {
            if (isset($_option['label']) && isset($_option['value'])) {
                $result[$_option['value']] = $_option['label'];
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    abstract public function toOptionArray();
}
