<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2015 - 2019 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2015 - 2019 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_Adminhtml_Amazonpayments_SystemController extends Mage_Adminhtml_Controller_Action
{
    protected function _mapRegion($region)
    {
        switch ($region) {
            case 'EUR':
            case 'EUR_DE':
            case 'EUR_FR':
            case 'EUR_IT':
            case 'EUR_ES':
                return 'de';
            case 'GBP':
                return 'uk';
            case 'USD':
                return 'us';
            case 'JPY':
                return 'jp';
            default:
                return null;
        }
    }

    protected function _getRegionLabel($code)
    {
        return Mage::getSingleton('amazonpayments/lookup_accountRegion')
            ->getRegionLabelByCode($code);
    }

    // @codingStandardsIgnoreStart
    protected function _checkCredentials($merchantId, $accessKey, $secretKey, $clientId, $region)
    {
        $result = array('valid' => true, 'messages' => array());

        if (!$merchantId) {
            $result['valid'] = false;
            $result['messages'][] = $this->__('Merchant ID is missing.');
        }

        if (!$accessKey) {
            $result['valid'] = false;
            $result['messages'][] = $this->__('Access Key ID is missing.');
        }

        if (!$secretKey) {
            $result['valid'] = false;
            $result['messages'][] = $this->__('Secret Key is missing.');
        }

        if (!$clientId) {
            $result['valid'] = false;
            $result['messages'][] = $this->__('Client ID is missing.');
        }

        if (!$result['valid']) {
            return $result;
        }

        try {
            /** @var AmazonPay_Client $api */
            $api = new AmazonPay_Client(
                array(
                    'merchant_id'   => trim($merchantId),
                    'access_key'    => trim($accessKey),
                    'secret_key'    => trim($secretKey),
                    'region'        => $this->_mapRegion($region),
                    'sandbox'       => true
                )
            );

            $response = $api->getOrderReferenceDetails(array(
                'merchant_id' => trim($merchantId),
                'amazon_order_reference_id' => 'P00-0000000-0000000'
            ))->toArray();

            if (isset($response['Error']['Code'])) {
                switch ($response['Error']['Code']) {
                    case 'InvalidOrderReferenceId':
                        $result['messages'][] = $this->__(
                            'Congratulations! Your Amazon Payments account settings seem to be OK.'
                        );
                        break;
                    case 'InvalidParameterValue':
                        $result['valid'] = false;
                        $result['messages'][] = $this->__('Whoops! Your Merchant ID seems to be invalid.');
                        break;
                    case 'InvalidAccessKeyId':
                        $result['valid'] = false;
                        $result['messages'][] = $this->__('Whoops! Your Access Key ID seems to be invalid.');
                        break;
                    case 'SignatureDoesNotMatch':
                        $result['valid'] = false;
                        $result['messages'][] = $this->__('Whoops! Your Secret Access Key seems to be invalid.');
                        break;
                    default:
                        $result['valid'] = false;
                        $result['messages'][] = $this->__('Whoops! Something went wrong while validating your account.');
                        break;
                }
            } else {
                $result['valid'] = false;
                $result['messages'][] = $this->__('Whoops! Something went wrong while validating your account.');
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $result['valid'] = false;
            $result['messages'][] = $this->__('Whoops! Something went wrong while validating your account.');
        }

        return $result;
    }
    // @codingStandardsIgnoreEnd

    public function validateAction()
    {
        $merchantId = $this->getRequest()->getPost('merchant_id', null);
        $accessKey = $this->getRequest()->getPost('access_key', null);
        $secretKey = $this->getRequest()->getPost('secret_key', null);
        $clientId = $this->getRequest()->getPost('client_id', null);
        $region = $this->getRequest()->getPost('region', 'EUR');

        $result = $this->_checkCredentials($merchantId, $accessKey, $secretKey, $clientId, $region);

        $result['data'] = array(
            $this->__('Merchant ID') => $merchantId,
            $this->__('Client ID') => $clientId,
            $this->__('Access Key ID') => $accessKey,
            $this->__('Secret Access Key') => $this->__('VALID'),
            $this->__('Payment Region') => $this->_getRegionLabel($region)
        );

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * @throws Zend_Crypt_Rsa_Exception
     */
    public function generateKeysAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->_forward('noRoute');
        }

        $rsa = new Zend_Crypt_Rsa;
        $keys = $rsa->generateKeys(
            array(
                'private_key_bits' => 2048,
                'privateKeyBits' => 2048,
                'hashAlgorithm' => 'sha1'
            )
        );

        $result = array_map(
            function ($key) {
                return (string)$key;
            },
            $keys->getArrayCopy()
        );

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/system/config/amazonpayments');
    }
}
