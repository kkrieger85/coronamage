<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2017 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2017 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
abstract class Creativestyle_AmazonPayments_Controller_Adminhtml_Log_Action extends Mage_Adminhtml_Controller_Action
{
    const TITLE_AMAZON_PAY = 'Amazon Pay';
    const TITLE_LOGS       = 'Log preview';
    const TITLE_PREVIEW    = 'Preview';

    /**
     * @var array
     */
    protected $_titlePrefix = array(
        self::TITLE_AMAZON_PAY,
        self::TITLE_LOGS
    );

    /**
     * Returns Amazon Pay config model instance
     *
     * @return Creativestyle_AmazonPayments_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('amazonpayments/config');
    }

    /**
     * Returns collection of the logs of the given type
     *
     * @return Creativestyle_AmazonPayments_Model_Log_Collection
     */
    protected function _getLogCollection()
    {
        /** @var Creativestyle_AmazonPayments_Model_Log_Collection $collection */
        $collection = Mage::getModel('amazonpayments/log_collection');
        return $collection->setLogType($this->getLogType());
    }

    /**
     * Returns log of given ID
     *
     * @param $logId
     * @return Varien_Object|null
     */
    protected function _getLogById($logId)
    {
        return $this->_getLogCollection()->getItemById($logId);
    }

    /**
     * Returns array of titles for log list page
     *
     * @return array
     */
    protected function _getListPageTitleArray()
    {
        return array_merge($this->_titlePrefix, array($this->getTitle()));
    }

    /**
     * Returns array of titles for log preview page
     *
     * @return array
     */
    protected function _getViewPageTitleArray()
    {
        return array_merge($this->_getListPageTitleArray(), array(self::TITLE_PREVIEW));
    }

    /**
     * Returns log preview block instance
     *
     * @return Creativestyle_AmazonPayments_Block_Adminhtml_Log_View_Abstract
     */
    protected function _getLogPreviewBlock()
    {
        return $this->getLayout()->createBlock('amazonpayments/adminhtml_log_' . $this->getLogType() . '_view');
    }

    /**
     * Adds given log preview block to the current layout
     *
     * @param Varien_Object $log
     * @return $this
     */
    protected function _addLogPreviewContent(Varien_Object $log)
    {
        $this->_addContent($this->_getLogPreviewBlock()->setLog($log));
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('admin/creativestyle/amazonpayments/log/' . $this->getLogType());
    }

    /**
     * @param array $titles
     * @return $this
     */
    protected function _initAction(array $titles)
    {
        $this->loadLayout()->_setActiveMenu('creativestyle/amazonpayments/log/' . $this->getLogType());
        foreach ($titles as $title) {
            $localizedTitle = $this->__($title);
            $this->_addBreadcrumb($localizedTitle, $localizedTitle)
                ->_title($localizedTitle);
        }

        return $this;
    }

    /**
     * Logs listing action
     */
    public function indexAction()
    {
        $this->_initAction($this->_getListPageTitleArray())
            ->renderLayout();
    }

    /**
     * Log preview action
     */
    public function viewAction()
    {
        $logId = $this->getRequest()->getParam('id');
        $log = $this->_getLogById($logId);
        if ($log && $log->getId()) {
            $this->_initAction($this->_getViewPageTitleArray())
                ->_addLogPreviewContent($log)
                ->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')
                ->addError(Mage::helper('amazonpayments')->__('Log does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Logs download action
     */
    public function downloadAction()
    {
        $logFilePath = Creativestyle_AmazonPayments_Model_Logger::getAbsoluteLogFilePath($this->getLogType());
        // @codingStandardsIgnoreStart
        if (file_exists($logFilePath)) {
            $output = implode(
                $this->_getConfig()->getLogDelimiter(),
                Creativestyle_AmazonPayments_Model_Logger::getColumnMapping($this->getLogType())
            ) . "\n";
            $output .= file_get_contents($logFilePath);
            Mage::app()->getResponse()->setHeader('Content-type', 'text/csv');
            Mage::app()->getResponse()->setHeader(
                'Content-disposition',
                'attachment;filename=' . basename($logFilePath)
            );
            Mage::app()->getResponse()->setHeader('Content-Length', filesize($logFilePath));
            Mage::app()->getResponse()->setBody($output);
        } else {
            $this->_redirect('*/*/');
        }
        // @codingStandardsIgnoreEnd
    }

    /**
     * Returns the type of handled log
     *
     * @return string
     */
    abstract public function getLogType();

    /**
     * Returns page title for the handled log type
     *
     * @return string
     */
    abstract public function getTitle();
}
