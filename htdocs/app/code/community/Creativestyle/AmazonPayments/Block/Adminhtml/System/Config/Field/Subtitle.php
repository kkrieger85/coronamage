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


class Creativestyle_AmazonPayments_Block_Adminhtml_System_Config_Field_Subtitle extends
 Mage_Adminhtml_Block_System_Config_Form_Field
{

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $colspan = $element->getCanUseWebsiteValue() || $element->getCanUseDefaultValue() ? '5' : '4';
        return <<<EOF
<tr id="row_{$element->getHtmlId()}" class="amazon-payments-config-subtitle">
    <td class="label" colspan="{$colspan}">
        <label for="{$element->getHtmlId()}">{$element->getLabel()}</label>
        <input type="hidden" id="{$element->getHtmlId()}"/>
    </td>
</tr>
EOF;
    }
}
