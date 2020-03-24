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
class Creativestyle_AmazonPayments_Block_Adminhtml_Debug_Section extends Mage_Adminhtml_Block_Template
{
    protected $_id = null;

    protected $_debugArea = 'general';

    protected $_showKeys = true;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('creativestyle/amazonpayments/debug/section.phtml');
    }

    public function getDebugData()
    {
        return Mage::helper('amazonpayments/debug')->getDebugData($this->_debugArea);
    }

    public function getSectionId()
    {
        if (null === $this->_id) {
            $this->_id = 'amazon-payments-debug-section-' . uniqid();
        }

        return $this->_id;
    }

    public function setDebugArea($debugArea)
    {
        $this->_debugArea = $debugArea;
        return $this;
    }

    public function setShowKeys($showKeys)
    {
        $this->_showKeys = (bool)$showKeys;
        return $this;
    }

    public function showKeys()
    {
        return $this->_showKeys;
    }

    public function formatOutput($value, $key = null)
    {
        if ($key == 'Amazon User ID attribute present' && !$value) {
            return sprintf('No (<a href="%s">Fix</a>)', $this->getUrl('*/*/fix'));
        }

        if (false === $value) {
            return 'No';
        }

        if (true === $value) {
            return 'Yes';
        }

        return $value;
    }
}
