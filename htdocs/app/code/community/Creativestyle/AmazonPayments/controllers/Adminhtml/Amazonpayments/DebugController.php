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
class Creativestyle_AmazonPayments_Adminhtml_Amazonpayments_DebugController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction($actionMenuItem, $actionBreadcrumbs)
    {
        $this->_setActiveMenu('creativestyle/amazonpayments/' . $actionMenuItem);
        foreach ($actionBreadcrumbs as $breadcrumb) {
            $this->_addBreadcrumb($this->__($breadcrumb), $this->__($breadcrumb))
                ->_title($breadcrumb);
        }

        return $this;
    }

    public function indexAction()
    {
        $this->loadLayout()
            ->_initAction('debug', array('Amazon Pay and Login with Amazon', 'Debug data'))
            ->renderLayout();
    }

    public function downloadAction()
    {
        $debugData = Mage::helper('amazonpayments/debug')->getDebugData();
        $filename = str_replace(
            array('.', '/', '\\'),
            array('_'),
            // @codingStandardsIgnoreStart
            parse_url(
                Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL),
                PHP_URL_HOST
            )
            // @codingStandardsIgnoreEnd
        ) . '_apa_debug_' . Mage::getModel('core/date')->gmtTimestamp() . '.dmp';
        $debugData = base64_encode(call_user_func('serialize', $debugData));
        Mage::app()->getResponse()->setHeader('Content-type', 'application/base64');
        Mage::app()->getResponse()->setHeader('Content-disposition', 'attachment;filename=' . $filename);
        Mage::app()->getResponse()->setBody($debugData);
    }

    public function fixAction()
    {
        try {
            if (!Mage::getResourceModel('catalog/eav_attribute')
                ->loadByCode('customer', 'amazon_user_id')->getId()) {
                /** @var Mage_Eav_Model_Entity_Setup $setup */
                $setup = new Mage_Eav_Model_Entity_Setup('core/write');
                $setup->startSetup();
                $setup->addAttribute(
                    'customer',
                    'amazon_user_id',
                    array(
                        'type'      => 'varchar',
                        'label'     => 'Amazon UID',
                        'visible'   => false,
                        'required'  => false,
                        'unique'    => true
                    )
                );
                $setup->endSetup();
                $this->_getSession()->addSuccess('Missing Amazon User ID attribute fixed');
            }
        } catch (Exception $e) {
            $this->_getSession()->addSuccess('Error when adding missing Amazon User ID attribute: ' . $e->getMessage());
        }

        $this->_forward('index');
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/creativestyle/amazonpayments/debug');
    }
}
