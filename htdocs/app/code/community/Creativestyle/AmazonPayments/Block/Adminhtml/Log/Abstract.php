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
abstract class Creativestyle_AmazonPayments_Block_Adminhtml_Log_Abstract extends
 Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'amazonpayments';
        $this->_controller = 'adminhtml_log_' . $this->getLogType();
        $this->_headerText = $this->__($this->getTitle());
        $this->_removeButton('add');
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $logFilePath = Creativestyle_AmazonPayments_Model_Logger::getAbsoluteLogFilePath($this->getLogType());
        $this->_addButton(
            'download',
            array(
                'label'     => $this->_getDownloadButtonLabel(),
                'onclick'   => 'setLocation(\'' . $this->_getDownloadUrl() .'\')',
                'class'     => 'scalable',
                // @codingStandardsIgnoreStart
                'disabled'  => !file_exists($logFilePath)
                // @codingStandardsIgnoreEnd
            ),
            -1
        );
        return parent::_prepareLayout();
    }

    /**
     * Returns log CSV download button label
     *
     * @return string
     */
    protected function _getDownloadButtonLabel()
    {
        return $this->__('Download as CSV');
    }

    /**
     * Returns log CSV download URL
     *
     * @return string
     */
    protected function _getDownloadUrl()
    {
        return $this->getUrl('*/*/download');
    }

    /**
     * Returns CSS header class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-amazonpayments-log ' . parent::getHeaderCssClass();
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
