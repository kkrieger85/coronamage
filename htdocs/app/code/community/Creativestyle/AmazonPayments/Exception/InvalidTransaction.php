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
class Creativestyle_AmazonPayments_Exception_InvalidTransaction extends Creativestyle_AmazonPayments_Exception
{
    /**
     * @var array
     */
    protected $_state;

    /**
     * @var string
     */
    protected $_transactionType;

    /**
     * @var boolean
     */
    protected $_isSync;

    /**
     * @param string $txnType
     * @param array $state
     * @param boolean $isSync
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($txnType, array $state, $isSync = true, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->_transactionType = $txnType;
        $this->_state = $state;
        $this->_isSync = $isSync;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    protected function _getStateAttribute($key)
    {
        if (array_key_exists($key, $this->_state)) {
            return $this->_state[$key];
        }

        return null;
    }

    /**
     * @return mixed|null
     */
    public function getState()
    {
        return $this->_getStateAttribute(
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_KEY
        );
    }

    /**
     * @return mixed|null
     */
    public function getReasonCode()
    {
        return $this->_getStateAttribute(
            Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_REASON_CODE_KEY
        );
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_transactionType;
    }

    /**
     * @return boolean
     */
    public function isAuth()
    {
        return $this->getType() == Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_TYPE_AUTH;
    }

    /**
     * @return bool
     */
    public function isDeclined()
    {
        return $this->getState()
            == Creativestyle_AmazonPayments_Model_Processor_Transaction::TRANSACTION_STATE_DECLINED;
    }

    /**
     * @return boolean
     */
    public function isSync()
    {
        return $this->_isSync;
    }
}
