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
class Creativestyle_AmazonPayments_Block_Adminhtml_Log_Ipn_View extends
 Creativestyle_AmazonPayments_Block_Adminhtml_Log_View_Abstract
{
    const LOG_TYPE = 'ipn';
    const LOG_TITLE = 'IPN Notification';

    /**
     * @var string
     */
    protected $_headerTextPattern = 'IPN %s | %s';

    /**
     * @inheritdoc
     */
    protected function _generateHeaderText()
    {
        if ($this->_getLog() && $this->_getLog()->getId()) {
            return $this->__(
                $this->_headerTextPattern,
                $this->_getLog()->getNotificationType()
                    ? $this->_getLog()->getNotificationType()
                    : 'UnknownNotification',
                $this->getTimestamp()
            );
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
     * Returns IPN request body
     *
     * @return null|string
     */
    public function getRequestBody()
    {
        if ($this->_getLog() && $this->_getLog()->getRequestBody()) {
            try {
                // @codingStandardsIgnoreStart
                return Zend_Json::prettyPrint(
                    stripslashes($this->_getLog()->getRequestBody()),
                    array('indent' => '    ')
                );
                // @codingStandardsIgnoreEnd
            } catch (Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Returns admin link to the transaction page
     *
     * @return null|string
     */
    public function getTransactionLink()
    {
        /** @var Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection $transactionCollection */
        $transactionCollection = Mage::getModel('sales/order_payment_transaction')
            ->getCollection()
            ->addFieldToFilter('txn_id', $this->_getLog()->getTransactionId())
            ->setPageSize(1)
            ->setCurPage(1);

        foreach ($transactionCollection as $transaction) {
            return $this->getUrl(
                'adminhtml/sales_transactions/view',
                array('txn_id' => $transaction->getId())
            );
        }

        return null;
    }
}
