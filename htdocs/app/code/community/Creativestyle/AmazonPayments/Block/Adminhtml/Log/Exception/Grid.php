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
class Creativestyle_AmazonPayments_Block_Adminhtml_Log_Exception_Grid extends
 Creativestyle_AmazonPayments_Block_Adminhtml_Log_Grid_Abstract
{
    const LOG_TYPE = 'exception';

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'timestamp',
            array(
                'header'    => $this->__('Date'),
                'index'     => 'timestamp',
                'type'      => 'datetime',
                'width'     => '150px',
                'renderer'  => 'Creativestyle_AmazonPayments_Block_Adminhtml_Renderer_Timestamp',
                'filter'    => false,
                'sortable'  => false
            )
        );

        $this->addColumn(
            'exception_message',
            array(
                'header'    => $this->__('Exception message'),
                'index'     => 'exception_message',
                'filter'    => false,
                'sortable'  => false
            )
        );

        $this->addColumn(
            'exception_code',
            array(
                'header'    => $this->__('Exception code'),
                'index'     => 'exception_code',
                'align'     => 'center',
                'width'     => '50px',
                'filter'    => false,
                'sortable'  => false
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * @inheritdoc
     */
    public function getLogType()
    {
        return self::LOG_TYPE;
    }
}
