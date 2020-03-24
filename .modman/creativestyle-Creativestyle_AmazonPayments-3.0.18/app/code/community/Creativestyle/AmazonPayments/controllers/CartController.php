<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2017 - 2019 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2017 - 2019 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_CartController extends Creativestyle_AmazonPayments_Controller_Action
{
    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        /** @var Mage_Checkout_Model_Cart $cart */
        $cart = Mage::getSingleton('checkout/cart');
        if ($cart->getQuote()->getIsMultiShipping()) {
            $cart->getQuote()->setIsMultiShipping(false);
        }

        return $cart;
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product|false
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }

        return false;
    }

    public function addAction()
    {
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $result = array(
                    'success' => false,
                    'error' => true,
                    'message' => $this->__('Some of the requested products are unavailable')
                );
                $this->_setJsonResponse($result);
                return;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent(
                'checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            $result = array(
                'success' => true,
                'message' => $this->__(
                    '%s was added to your shopping cart.',
                    $this->_getCoreHelper()->escapeHtml($product->getName())
                )
            );
            $this->_setJsonResponse($result);
        } catch (Mage_Core_Exception $e) {
            $result = array(
                'success' => false,
                'error' => true,
                'error_messages' => array_unique(explode("\n", $e->getMessage()))
            );
            $this->_setJsonResponse($result);
        } catch (Exception $e) {
            Mage::logException($e);
            $result = array(
                'success' => false,
                'error' => true,
                'error_messages' => $e->getMessage()
            );
            $this->_setJsonResponse($result);
        }
    }
}
