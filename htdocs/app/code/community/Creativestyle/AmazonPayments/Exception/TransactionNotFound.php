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
class Creativestyle_AmazonPayments_Exception_TransactionNotFound extends Creativestyle_AmazonPayments_Exception
{
    /**
     * @var string
     */
    protected $_transactionId;

    /**
     * @var string
     */
    protected $_messagePattern = 'Payment transaction (%s) not found';

    /**
     * @param string $txnId
     */
    public function __construct($txnId)
    {
        parent::__construct(sprintf($this->_messagePattern, $txnId), 404);
        $this->_transactionId = $txnId;
    }

    /**
     * @return string
     */
    public function getTxnId()
    {
        return $this->_transactionId;
    }
}
