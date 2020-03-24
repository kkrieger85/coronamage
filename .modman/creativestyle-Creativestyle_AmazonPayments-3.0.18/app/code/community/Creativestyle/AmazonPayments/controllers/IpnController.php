<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2017 - 2018 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2017 - 2018 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_IpnController extends Creativestyle_AmazonPayments_Controller_Action
{
    /**
     * @var array
     */
    protected $_headers = array(
        'x-amz-sns-message-type',
        'x-amz-sns-message-id',
        'x-amz-sns-topic-arn',
        'x-amz-sns-subscription-arn'
    );

    /**
     * @var array
     */
    protected $_responseCodes = array(
        200 => '200 OK',
        400 => '400 Bad Request',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        500 => '500 Internal Server Error',
        503 => '503 Service Unavailable'
    );

    /**
     * Returns Amazon Pay IPN endpoint instance
     *
     * @return Creativestyle_AmazonPayments_Model_Api_Ipn
     */
    protected function _getApi()
    {
        /** @var Creativestyle_AmazonPayments_Model_Api_Ipn $api */
        $api = Mage::getSingleton('amazonpayments/api_ipn');
        return $api;
    }

    /**
     * Returns value of request header with given ID
     *
     * @param string $headerId
     * @return false|string
     * @throws Zend_Controller_Request_Exception
     */
    protected function _getRequestHeaderById($headerId)
    {
        return $this->getRequest()->getHeader($headerId);
    }

    /**
     * Returns values of the defined request headers in array
     *
     * @return array
     */
    protected function _getRequestHeaders()
    {
        return array_filter(
            array_combine($this->_headers, array_map(array($this, '_getRequestHeaderById'), $this->_headers)),
            function ($header) {
                return (bool)$header;
            }
        );
    }

    /**
     * Returns request headers formatted for log
     *
     * @return string
     */
    protected function _getFormattedRequestHeaders()
    {
        $formattedHeaders = array();
        foreach ($this->_getRequestHeaders() as $headerId => $header) {
            $formattedHeaders[] = sprintf('%s: %s', $headerId, $header);
        }

        return implode("\n", $formattedHeaders);
    }

    /**
     * Prepare response for sending
     *
     * @param int $code
     * @param null $message
     * @return int
     */
    protected function _prepareIpnResponse($code, $message = null)
    {
        if (!array_key_exists($code, $this->_responseCodes)) {
            $code = 500;
        }

        $this->getResponse()->setHeader('HTTP/1.1', $this->_responseCodes[$code]);

        if ($message) {
            $this->getResponse()->setBody($message);
        }

        return $code;
    }

    /**
     * @inheritdoc
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!$this->_getConfig()->isPaymentProcessingAllowed()
            || !$this->_getConfig()->isIpnActive()
            || !$this->getRequest()->isPost()) {
            if ($this->getRequest()->getActionName() != 'noRoute') {
                $this->_forward('noRoute');
            }
        }
    }

    /**
     * IPN entry point action
     */
    public function indexAction()
    {
        $notification = null;

        try {
            $transactionId = null;

            $notification = $this->_getApi()->parseNotification(
                $this->_getRequestHeaders(),
                $this->getRequest()->getRawBody()
            );

            $transactionId = $this->_getApi()->processNotification($notification);
            $responseError = null;
            $responseCode = $this->_prepareIpnResponse(200);
        } catch (Creativestyle_AmazonPayments_Exception_TransactionNotFound $e) {
            $transactionId = $e->getTxnId();
            $responseError = $e->getMessage();
            $responseCode = $this->_prepareIpnResponse($e->getCode(), $responseError);
        } catch (Exception $e) {
            Creativestyle_AmazonPayments_Model_Logger::logException($e);
            $responseError = $e->getMessage();
            $responseCode = $this->_prepareIpnResponse(400, $responseError);
        }

        if ($notification) {
            Mage::dispatchEvent(
                'amazonpayments_ipn_request',
                array('call_data' => array(
                    'notification_type' => $notification['NotificationType'],
                    'transaction_id'    => $transactionId,
                    'response_code'     => $responseCode,
                    'response_error'    => $responseError,
                    'request_headers'   => $this->_getFormattedRequestHeaders(),
                    'request_body'      => $this->getRequest()->getRawBody()
                ))
            );
        }
    }
}
