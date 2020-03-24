<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2018 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2018 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */

class Creativestyle_AmazonPayments_Block_Adminhtml_System_Config_Field_CheckoutType extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $element->getElementHtml();
        if (!$element->getValue()) {
            $onePageCheckout = Creativestyle_AmazonPayments_Model_Lookup_CheckoutType::CHECKOUT_TYPE_ONEPAGE;
            $amazonCheckout = Creativestyle_AmazonPayments_Model_Lookup_CheckoutType::CHECKOUT_TYPE_AMAZON;
            $html .= <<<EOF
<script>
    function updateCheckoutType() {
        switch ($('amazonpayments_basic_region').value) {
            case 'EUR_FR':
            case 'EUR_IT':
            case 'EUR_ES':
                $({$element->getHtmlId()}).value = '$onePageCheckout';
                break;
            default:
                $({$element->getHtmlId()}).value = '$amazonCheckout';
                break;
        }        
    }

    Event.observe('amazonpayments_basic_region', 'change', updateCheckoutType);
    Event.observe('amazonpayments_basic_region', 'region:change', updateCheckoutType);
    Event.observe(document, 'dom:loaded', updateCheckoutType);
</script>
EOF;
        }

        return $html;
    }
}
