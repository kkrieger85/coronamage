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
class Creativestyle_AmazonPayments_Block_Adminhtml_Log_Ipn extends
 Creativestyle_AmazonPayments_Block_Adminhtml_Log_Abstract
{
    const LOG_TYPE = 'ipn';
    const LOG_TITLE = 'IPN Notifications';

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
