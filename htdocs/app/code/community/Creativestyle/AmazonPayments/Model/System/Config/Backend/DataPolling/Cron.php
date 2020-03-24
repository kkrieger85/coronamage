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
class Creativestyle_AmazonPayments_Model_System_Config_Backend_DataPolling_Cron extends Mage_Core_Model_Config_Data
{

    const XML_PATH_DATA_POLLING_CRON_EXPR = 'crontab/jobs/amazonpayments_advanced_data_poll/schedule/cron_expr';

    protected function _afterSave() 
    {
        $cronExprModel = Mage::getModel('core/config_data')->load(self::XML_PATH_DATA_POLLING_CRON_EXPR, 'path');
        if (!$this->getData('groups/general/fields/ipn_active/value')) {
            $frequency = $this->getData('groups/general/fields/polling_frequency/value');
            $months = floor($frequency / (30 * 24 * 60 * 60));
            $days = floor(($frequency - $months * 30 * 24 * 60 * 60) / (24 * 60 * 60));
            $hours = floor(($frequency - $months * 30 * 24 * 60 * 60 - $days * 24 * 60 * 60) / (60 * 60));
            $minutes = floor(($frequency - $months * 30 * 24 * 60 * 60 - $days * 24 * 60 * 60 - $hours * 60 * 60) / 60);

            $cronExpr = '*/5 * * * *';

            if ($months) {
                $cronExpr = sprintf('%s %s %s * *', rand(0, 59), rand(0, 23), rand(0, 28));
            } else if ($days) {
                $cronExpr = sprintf('%s %s *%s * *', rand(0, 59), rand(0, 23), ($days > 1 ? '/' . $days : ''));
            } else if ($hours) {
                $cronExpr = sprintf('%s *%s * * *', rand(0, 59), ($hours > 1 ? '/' . $hours : ''));
            } else if ($minutes) {
                $cronExpr = sprintf('*/%s * * * *', $minutes);
            }

            try {
                $cronExprModel->setValue($cronExpr)
                    ->setPath(self::XML_PATH_DATA_POLLING_CRON_EXPR)
                    ->save();
            } catch (Exception $e) {
                Mage::throwException('Unable to save cron expression.');
            }
        } elseif ($cronExprModel->getId()) {
            try {
                $cronExprModel->delete();
            } catch (Exception $e) {
                Mage::throwException('Unable to delete cron expression.');
            }
        }
    }
}
