<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2019 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2019 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */

class Creativestyle_AmazonPayments_Block_Adminhtml_System_Config_Field_CarrierCodes extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * @var Creativestyle_AmazonPayments_Block_Adminhtml_System_Config_Field_CarrierCodes_Magento
     */
    protected $_magentoCarriersRenderer;

    /**
     * @var Creativestyle_AmazonPayments_Block_Adminhtml_System_Config_Field_CarrierCodes_Amazon
     */
    protected $_amazonCarriersRenderer;

    public function _prepareToRender()
    {
        $this->addColumn(
            'magento',
            array(
                'label' => Mage::helper('amazonpayments')->__('Magento carrier'),
                'renderer' => $this->_getMagentoCarriersRenderer(),
            )
        );

        $this->addColumn(
            'amazon',
            array(
                'label' => Mage::helper('amazonpayments')->__('Amazon carrier'),
                'renderer' => $this->_getAmazonCarriersRenderer(),
            )
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('amazonpayments')->__('Add');
    }

    /**
     * @return Creativestyle_AmazonPayments_Block_Adminhtml_System_Config_Field_CarrierCodes_Magento
     */
    protected function _getMagentoCarriersRenderer()
    {
        if (!$this->_magentoCarriersRenderer) {
            $this->_magentoCarriersRenderer = $this->getLayout()->createBlock(
                'amazonpayments/adminhtml_system_config_field_carrierCodes_magento',
                '',
                array('is_render_to_js_template' => true)
            );
        }

        return $this->_magentoCarriersRenderer;
    }

    /**
     * @return Creativestyle_AmazonPayments_Block_Adminhtml_System_Config_Field_CarrierCodes_Amazon
     */
    protected function _getAmazonCarriersRenderer()
    {
        if (!$this->_amazonCarriersRenderer) {
            $this->_amazonCarriersRenderer = $this->getLayout()->createBlock(
                'amazonpayments/adminhtml_system_config_field_carrierCodes_amazon',
                '',
                array('is_render_to_js_template' => true)
            );
        }

        return $this->_amazonCarriersRenderer;
    }

    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getMagentoCarriersRenderer()->calcOptionHash($row->getData('magento')),
            'selected="selected"'
        );

        $row->setData(
            'option_extra_attr_' . $this->_getAmazonCarriersRenderer()->calcOptionHash($row->getData('amazon')),
            'selected="selected"'
        );
    }

    protected function _toHtml()
    {
        return '<div id="' . $this->getElement()->getId(). '">' . parent::_toHtml() . '</div>';
    }
}
