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
class Creativestyle_AmazonPayments_Block_Adminhtml_Log_Exception_View extends
 Creativestyle_AmazonPayments_Block_Adminhtml_Log_View_Abstract
{
    const LOG_TYPE = 'exception';
    const LOG_TITLE = 'Amazon Pay Exception';

    /**
     * @var string
     */
    protected $_headerTextPattern = 'Amazon Pay Exception | %s';

    /**
     * @inheritdoc
     */
    protected function _generateHeaderText()
    {
        if ($this->_getLog() && $this->_getLog()->getId()) {
            return $this->__($this->_headerTextPattern, $this->getTimestamp());
        }

        return parent::_generateHeaderText();
    }

    /**
     * @inheritdoc
     */
    public function getLogType()
    {
        return self::LOG_TYPE;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return self::LOG_TITLE;
    }
}
