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
class Creativestyle_AmazonPayments_Block_Adminhtml_Log_Api_View extends
 Creativestyle_AmazonPayments_Block_Adminhtml_Log_View_Abstract
{
    const LOG_TYPE = 'api';
    const LOG_TITLE = 'Amazon Pay API Call';

    const DATA_TYPE_XML = 'markup';
    const DATA_TYPE_HTTP = 'http';
    const DATA_TYPE_JSON = 'json';

    /**
     * @var string
     */
    protected $_headerTextPattern = '%s API Call | %s';

    /**
     * @inheritdoc
     */
    protected function _generateHeaderText()
    {
        if ($this->_getLog() && $this->_getLog()->getId()) {
            return $this->__($this->_headerTextPattern, $this->_getLog()->getCallAction(), $this->getTimestamp());
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

    /**
     * Returns API query string
     *
     * @return null|string
     */
    public function getQuery()
    {
        if ($this->_getLog()) {
            if (!$this->_getLog()->getCallAction()) {
                return $this->_getLog()->getQuery();
            }

            $queryStr = $this->_getLog()->getQuery();
            $queryArray = array();
            // @codingStandardsIgnoreStart
            parse_str($queryStr, $queryArray);
            // @codingStandardsIgnoreEnd
            if (!empty($queryArray)) {
                $output = '';
                foreach ($queryArray as $key => $value) {
                    $output .= $key .': ' . $value . "\n";
                }

                return $output;
            }

            return $queryStr;
        }

        return null;
    }

    public function getResponseType()
    {
        if (!$this->_getLog()->getCallAction()) {
            return self::DATA_TYPE_JSON;
        }

        return self::DATA_TYPE_XML;
    }

    public function getQueryType()
    {
        if (!$this->_getLog()->getCallAction()) {
            return self::DATA_TYPE_JSON;
        }

        return self::DATA_TYPE_HTTP;
    }
}
