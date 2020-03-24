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


class Creativestyle_AmazonPayments_Block_Adminhtml_System_Config_Field_ExpertMode extends
 Mage_Adminhtml_Block_System_Config_Form_Field
{

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $inheritCheckbox = $element->getCanUseWebsiteValue() || $element->getCanUseDefaultValue();

        $this->setTemplate('creativestyle/amazonpayments/config/field/expert_mode.phtml')
            ->setData(
                array(
                    'id' => $element->getId(),
                    'name' => $element->getName(),
                    'html_id' => $element->getHtmlId(),
                    'value' => $element->getEscapedValue()
                )
            );
        return '<tr id="row_' . $element->getHtmlId() . '"><td class="label" colspan="' . ($inheritCheckbox ? '5' : '4') . '">' . $this->toHtml() . '</td></tr>';
    }
}
