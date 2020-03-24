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
abstract class Creativestyle_AmazonPayments_Block_Adminhtml_Log_View_Abstract extends
 Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Log model instance
     *
     * @var Varien_Object|null
     */
    protected $_model = null;

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->_controller = 'adminhtml_log_' . $this->getLogType();
        $this->setTemplate('creativestyle/amazonpayments/advanced/log/' . $this->getLogType() . '/view.phtml');
        $this->_addButton(
            'back',
            array(
                'label'     => Mage::helper('adminhtml')->__('Back'),
                'onclick'   => 'window.location.href=\'' . $this->getUrl('*/*/') . '\'',
                'class'     => 'back',
            )
        );
    }

    /**
     * @inheritdoc
     */
    protected function _beforeToHtml()
    {
        $this->_headerText = $this->_generateHeaderText();
        return parent::_beforeToHtml();
    }

    /**
     * @return string
     */
    protected function _generateHeaderText()
    {
        return $this->__($this->getTitle());
    }

    /**
     * Returns log model instance
     *
     * @return Varien_Object
     */
    protected function _getLog()
    {
        return $this->_model;
    }

    /**
     * @inheritdoc
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get':
                $key = $this->_underscore(substr($method, 3));
                if ($this->_getLog() && $this->_getLog()->hasData($key)) {
                    return $this->_getLog()->getData($key);
                }
                return parent::__call($method, $args);
            default:
                return parent::__call($method, $args);
        }
    }

    /**
     * @return null|Zend_Date
     */
    public function getTimestamp()
    {
        if (null !== $this->_getLog()) {
            // @codingStandardsIgnoreStart
            return Mage::app()->getLocale()->date($this->_getLog()->getTimestamp());
            // @codingStandardsIgnoreEnd
        }

        return null;
    }

    /**
     * Sets log model for the view block
     *
     * @param Varien_Object $model
     * @return $this
     */
    public function setLog($model)
    {
        $this->_model = $model;
        return $this;
    }

    /**
     * Returns CSS header class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'icon-head head-amazonpayments-log ' . parent::getHeaderCssClass();
    }

    /**
     * Returns the type of handled log
     *
     * @return string
     */
    abstract public function getLogType();

    /**
     * Returns page title for the log listing page
     *
     * @return string
     */
    abstract public function getTitle();
}
