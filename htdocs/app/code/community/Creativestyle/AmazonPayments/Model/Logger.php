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
final class Creativestyle_AmazonPayments_Model_Logger
{
    const AMAZON_LOG_DIR            = 'amazonpayments';

    const AMAZON_API_LOG_FILE       = 'api_log.csv';
    const AMAZON_EXCEPTION_LOG_FILE = 'exception_log.csv';
    const AMAZON_IPN_LOG_FILE       = 'ipn_log.csv';

    const LOGFILE_ROTATION_SIZE     = 8;

    /**
     * Returns config model instance
     *
     * @return Creativestyle_AmazonPayments_Model_Config
     */
    protected static function _getConfig()
    {
        return Mage::getSingleton('amazonpayments/config');
    }

    protected static function _sanitizeResponseData($input)
    {
        $patterns = array();
        $patterns[0] = '/(<Buyer>)(.+)(<\/Buyer>)/ms';
        $patterns[1] = '/(<PhysicalDestination>)(.+)(<\/PhysicalDestination>)/ms';
        $patterns[2] = '/(<BillingAddress>)(.+)(<\/BillingAddress>)/ms';
        $patterns[3] = '/(<SellerNote>)(.+)(<\/SellerNote>)/ms';
        $patterns[4] = '/(<AuthorizationBillingAddress>)(.+)(<\/AuthorizationBillingAddress>)/ms';
        $patterns[5] = '/(<SellerAuthorizationNote>)(.+)(<\/SellerAuthorizationNote>)/ms';
        $patterns[6] = '/(<SellerCaptureNote>)(.+)(<\/SellerCaptureNote>)/ms';
        $patterns[7] = '/(<SellerRefundNote>)(.+)(<\/SellerRefundNote>)/ms';

        $replacements = array();
        $replacements[0] = '$1 REMOVED $3';
        $replacements[1] = '$1 REMOVED $3';
        $replacements[2] = '$1 REMOVED $3';
        $replacements[3] = '$1 REMOVED $3';
        $replacements[4] = '$1 REMOVED $3';
        $replacements[5] = '$1 REMOVED $3';
        $replacements[6] = '$1 REMOVED $3';
        $replacements[7] = '$1 REMOVED $3';

        return preg_replace($patterns, $replacements, $input);
    }

    /**
     * Returns path for selected logs and creates missing folder if needed
     *
     * @param string $logType
     * @return string|null
     */
    public static function getAbsoluteLogFilePath($logType)
    {
        try {
            $logDir = Mage::getBaseDir('log') . DS . self::AMAZON_LOG_DIR;
            // @codingStandardsIgnoreStart
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }
            // @codingStandardsIgnoreEnd

            switch ($logType) {
                case 'api':
                    return $logDir . DS . self::AMAZON_API_LOG_FILE;
                case 'exception':
                    return $logDir . DS . self::AMAZON_EXCEPTION_LOG_FILE;
                case 'ipn':
                    return $logDir . DS . self::AMAZON_IPN_LOG_FILE;
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return null;
    }

    /**
     * Logs a request made to Amazon Payments Advanced APIs
     *
     * @param array $callData
     */
    public static function logApiCall($callData)
    {
        if (self::_getConfig()->isLoggingActive()) {
            array_unshift($callData, Mage::getModel('core/date')->gmtTimestamp());
            // @codingStandardsIgnoreStart
            if (isset($callData['response_body'])) {
                $callData['response_body'] = self::_sanitizeResponseData($callData['response_body']);
            }

            if (($fileHandle = fopen(self::getAbsoluteLogFilePath('api'), 'a')) !== false) {
                fputcsv(
                    $fileHandle,
                    $callData,
                    self::_getConfig()->getLogDelimiter(),
                    self::_getConfig()->getLogEnclosure()
                );
                fclose($fileHandle);
            } else {
                Mage::log('AMAZON PAY: unable to open ' . self::getAbsoluteLogFilePath('api') . ' for writing.');
            }
            // @codingStandardsIgnoreEnd
        }
    }

    /**
     * Logs incoming IPN request
     *
     * @param array $callData
     */
    public static function logIpnCall($callData)
    {
        if (self::_getConfig()->isLoggingActive()) {
            array_unshift($callData, Mage::getModel('core/date')->gmtTimestamp());
            $callData['message_xml'] = '';
            if (isset($callData['request_body']) && $callData['request_body']) {
                try {
                    $requestBody = Mage::helper('core')->jsonDecode($callData['request_body']);
                    if (isset($requestBody['Message'])) {
                        $requestBody['Message'] = Mage::helper('core')->jsonDecode($requestBody['Message']);
                        if (isset($requestBody['Message']['NotificationData'])) {
                            $callData['message_xml'] = $requestBody['Message']['NotificationData'];
                            $requestBody['Message']['NotificationData'] = 'see XML message';
                        }

                        $callData['request_body'] = Mage::helper('core')->jsonEncode(
                            self::_sanitizeResponseData($requestBody)
                        );
                    }
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

            // @codingStandardsIgnoreStart
            if (($fileHandle = fopen(self::getAbsoluteLogFilePath('ipn'), 'a')) !== false) {
                fputcsv(
                    $fileHandle,
                    $callData,
                    self::_getConfig()->getLogDelimiter(),
                    self::_getConfig()->getLogEnclosure()
                );
                fclose($fileHandle);
            } else {
                Mage::log('AMAZON PAY: unable to open ' . self::getAbsoluteLogFilePath('ipn') . ' for writing.');
            }
            // @codingStandardsIgnoreEnd
        }
    }

    /**
     * Logs an exception thrown during Amazon Payments processing or post-processing
     *
     * @param Exception $e
     */
    public static function logException(Exception $e)
    {
        if (self::_getConfig()->isLoggingActive()) {
            // @codingStandardsIgnoreStart
            if (($fileHandle = fopen(self::getAbsoluteLogFilePath('exception'), 'a')) !== false) {
                $exceptionData = array(
                    'timestamp' => Mage::getModel('core/date')->gmtTimestamp(),
                    'exception_code' => $e->getCode(),
                    'exception_message' => $e->getMessage(),
                    'exception_trace' => $e->getTraceAsString()
                );
                fputcsv(
                    $fileHandle,
                    $exceptionData,
                    self::_getConfig()->getLogDelimiter(),
                    self::_getConfig()->getLogEnclosure()
                );
                fclose($fileHandle);
            } else {
                Mage::log('AMAZON PAY: unable to open ' . self::getAbsoluteLogFilePath('exception') . ' for writing.');
            }
            // @codingStandardsIgnoreEnd
        }
    }

    public static function getColumnMapping($logType)
    {
        switch ($logType) {
            case 'api':
                return array(
                    'timestamp',
                    'call_url',
                    'call_action',
                    'query',
                    'response_code',
                    'response_error',
                    'response_headers',
                    'response_body',
                    'request_headers'
                );
            case 'exception':
                return array(
                    'timestamp',
                    'exception_code',
                    'exception_message',
                    'exception_trace'
                );
            case 'ipn':
                return array(
                    'timestamp',
                    'notification_type',
                    'transaction_id',
                    'response_code',
                    'response_error',
                    'request_headers',
                    'request_body',
                    'message_xml'
                );
        }

        return null;
    }

    public static function rotateLogfiles()
    {
        $logTypes = array('api', 'exception', 'ipn');
        $maxFilesize = self::LOGFILE_ROTATION_SIZE * 1048576;
        foreach ($logTypes as $logType) {
            $filepath = self::getAbsoluteLogFilePath($logType);
            // @codingStandardsIgnoreStart
            if (file_exists($filepath) && filesize($filepath) > $maxFilesize) {
                rename($filepath, $filepath . '.' . Mage::getModel('core/date')->date("Ymdhis"));
            }
            // @codingStandardsIgnoreEnd
        }
    }
}
