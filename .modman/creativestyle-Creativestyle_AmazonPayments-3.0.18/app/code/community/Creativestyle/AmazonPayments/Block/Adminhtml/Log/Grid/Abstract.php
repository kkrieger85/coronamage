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
abstract class Creativestyle_AmazonPayments_Block_Adminhtml_Log_Grid_Abstract extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('amazonpayments_log_' . $this->getLogType() . '_grid');
        $this->setFilterVisibility(false);
        $this->setSaveParametersInSession(true);
    }

    /**
     * Returns collection of the logs of the given type
     *
     * @return Creativestyle_AmazonPayments_Model_Log_Collection
     */
    protected function _getLogCollection()
    {
        /** @var Creativestyle_AmazonPayments_Model_Log_Collection $collection */
        $collection = Mage::getModel('amazonpayments/log_collection');
        return $collection->setLogType($this->getLogType());
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->_getLogCollection());
        return parent::_prepareCollection();
    }

    /**
     * Prepares columns for the log grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'preview_action',
            array(
                'header'    => $this->__('Preview'),
                'type'      => 'action',
                'align'     => 'center',
                'width'     => '50px',
                'getter'    => 'getId',
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
                'actions'   => array(
                    array(
                        'caption'   => $this->__('Preview'),
                        'url'       => array('base' => '*/*/view'),
                        'field'     => 'id'
                    )
                )
            )
        );
        return parent::_prepareColumns();
    }

    /**
     * Returns row url for js event handlers
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

    /**
     * Returns the type of handled log
     *
     * @return string
     */
    abstract public function getLogType();
}
