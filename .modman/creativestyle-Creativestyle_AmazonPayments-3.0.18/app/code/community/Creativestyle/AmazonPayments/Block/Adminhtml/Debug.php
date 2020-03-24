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
class Creativestyle_AmazonPayments_Block_Adminhtml_Debug extends Mage_Adminhtml_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('creativestyle/amazonpayments/debug.phtml');
    }

    /**
     * @return Creativestyle_AmazonPayments_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('amazonpayments');
    }

    protected function _prepareLayout()
    {
        $accordion = $this->getLayout()->createBlock('adminhtml/widget_accordion')->setId('amazonPaymentsDebug');

        $accordion->addItem(
            'general',
            array(
                'title' => $this->_getHelper()->__('General Info'),
                'content' => $this->getLayout()
                    ->createBlock('amazonpayments/adminhtml_debug_section')
                    ->setDebugArea('general')
                    ->toHtml()
            )
        );

        $accordion->addItem(
            'stores',
            array(
                'title' => $this->_getHelper()->__('Stores'),
                'content' => $this->getLayout()
                    ->createBlock('amazonpayments/adminhtml_debug_section_table')
                    ->setDebugArea('stores')
                    ->toHtml()
            )
        );

        $accordion->addItem(
            'amazon_account',
            array(
                'title' => $this->_getHelper()->__('Amazon Payments Account'),
                'content' => $this->getLayout()
                    ->createBlock('amazonpayments/adminhtml_debug_section_table')
                    ->setDebugArea('amazon_account')
                    ->toHtml()
            )
        );

        $accordion->addItem(
            'amazon_general',
            array(
                'title' => $this->_getHelper()->__('General Settings'),
                'content' => $this->getLayout()
                    ->createBlock('amazonpayments/adminhtml_debug_section_table')
                    ->setDebugArea('amazon_general')
                    ->toHtml()
            )
        );

        $accordion->addItem(
            'amazon_email',
            array(
                'title' => $this->_getHelper()->__('Email Options'),
                'content' => $this->getLayout()
                    ->createBlock('amazonpayments/adminhtml_debug_section_table')
                    ->setDebugArea('amazon_email')
                    ->toHtml()
            )
        );

        $accordion->addItem(
            'amazon_design',
            array(
                'title' => $this->_getHelper()->__('Appearance Settings'),
                'content' => $this->getLayout()
                    ->createBlock('amazonpayments/adminhtml_debug_section_table')
                    ->setDebugArea('amazon_design')
                    ->toHtml()
            )
        );

        $accordion->addItem(
            'amazon_developer',
            array(
                'title' => $this->_getHelper()->__('Developer Options'),
                'content' => $this->getLayout()
                    ->createBlock('amazonpayments/adminhtml_debug_section_table')
                    ->setDebugArea('amazon_developer')
                    ->toHtml()
            )
        );

        $accordion->addItem(
            'magento_general',
            array(
                'title' => $this->_getHelper()->__('Magento Settings'),
                'content' => $this->getLayout()
                    ->createBlock('amazonpayments/adminhtml_debug_section_table')
                    ->setDebugArea('magento_general')
                    ->toHtml()
            )
        );

        $accordion->addItem(
            'cronjobs',
            array(
                'title' => $this->_getHelper()->__('Amazon Cronjobs'),
                'content' => $this->getLayout()
                    ->createBlock('amazonpayments/adminhtml_debug_section_table')
                    ->setDebugArea('cronjobs')
                    ->setShowKeys(false)
                    ->toHtml()
            )
        );

        $accordion->addItem(
            'event_observers',
            array(
                'title' => $this->_getHelper()->__('Event Observers'),
                'content' => $this->getLayout()
                    ->createBlock('amazonpayments/adminhtml_debug_section')
                    ->setDebugArea('event_observers')
                    ->toHtml()
            )
        );

        $accordion->addItem(
            'magento_extensions',
            array(
                'title' => $this->_getHelper()->__('Installed Magento extensions'),
                'content' => $this->getLayout()
                    ->createBlock('amazonpayments/adminhtml_debug_section')
                    ->setDebugArea('magento_extensions')
                    ->toHtml()
            )
        );

        $accordion->addItem(
            'php_modules',
            array(
                'title' => $this->_getHelper()->__('Installed PHP modules'),
                'content' => $this->getLayout()
                    ->createBlock('amazonpayments/adminhtml_debug_section')
                    ->setDebugArea('php_modules')
                    ->toHtml()
            )
        );

        $this->setChild('debug_data', $accordion);
        return parent::_prepareLayout();
    }

    public function getDownloadUrl()
    {
        return $this->getUrl('*/*/download');
    }
}
