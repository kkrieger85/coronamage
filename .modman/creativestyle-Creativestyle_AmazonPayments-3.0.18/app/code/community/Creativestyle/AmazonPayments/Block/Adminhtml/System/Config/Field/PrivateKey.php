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
class Creativestyle_AmazonPayments_Block_Adminhtml_System_Config_Field_PrivateKey extends
 Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_placeholder = '[encrypted]';

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setValue('');
        $generateKeysUrl = Mage::helper('adminhtml')->getUrl('adminhtml/amazonpayments_system/generateKeys');
        $saveKeysConfirmMsg = $this->helper('amazonpayments')->__('Do you want to save the newly generated keys?');
        $missingKeyDataMsg = $this->helper('amazonpayments')->__('Either Merchant ID or Public Key is missing or invalid');
        $publicKeyChangedMsg = $this->helper('amazonpayments')->__('Your Public Key changed. Please save your changes prior to submitting it to Amazon Pay.');

        $html = parent::_getElementHtml($element);
        $html .= <<<EOF
<script type="text/javascript">
    var privateKeyInput = '{$element->getHtmlId()}';
    var publicKeyInput = 'amazonpayments_configuration_delivery_tracking_public_key';
    var merchantIdInput = 'amazonpayments_basic_merchant_id';
    
    var publicKeyChanged = false;
    
    function handleKeyGenerationResponse(xhr) {
        var keys = xhr.responseText.evalJSON();
        $(privateKeyInput).value = keys.privateKey;
        $(publicKeyInput).value = keys.publicKey;
        publicKeyChanged = true;
        confirm('{$saveKeysConfirmMsg}') && configForm.submit();
    }

    function generateAlexaKeys() {
        new Ajax.Request('{$generateKeysUrl}', {method: 'post', onSuccess: handleKeyGenerationResponse});
    }

    function getAlexaPublicKeySubmitLink(merchantId, publicKey) {
        return "mailto:amazon-pay-delivery-notifications@amazon.com"
             + "?subject=" + escape('[EU] Request for Amazon Pay Public Key ID for ' + merchantId)
             + "&body=" + escape('Merchant ID: ' + merchantId + '\\n\\nPublic Key:\\n\\n' + publicKey);
    }
    
    document.observe('dom:loaded', function() {
        Event.observe($('lpa-alexa-public-key-submit'), 'click', function (evt) {
            var merchantId = \$F(merchantIdInput);
            var publicKey = \$F(publicKeyInput);
            
            if (publicKeyChanged) {
                evt.preventDefault();
                $('lpa-alexa-public-key-submit').setAttribute('href', '#');
                alert('{$publicKeyChangedMsg}');                
            } else if (merchantId && publicKey) {
                $('lpa-alexa-public-key-submit').setAttribute('href', getAlexaPublicKeySubmitLink(merchantId, publicKey));
            } else {
                evt.preventDefault();
                $('lpa-alexa-public-key-submit').setAttribute('href', '#');
                alert('{$missingKeyDataMsg}');
            }
        });
    });
</script>
EOF;

        $html .= '<div style="clear:both;">';
        if ($this->getConfigData(Creativestyle_AmazonPayments_Model_Config::XML_PATH_DELIVERY_TRACKING_PRIVATE_KEY)) {
            $title = $this->helper('amazonpayments')->__('Re-generate Private Key');
            $confirmMsg = $this->helper('amazonpayments')
                ->__('Are you sure you want to re-generate Private Key? It will clear the previous one!');
            $html .= <<<EOF
<button id="lpa-alexa-regenerate-keys" title="{$title}" type="button" class="save">
    <span><span><span>{$title}</span></span></span>
</button>
<script type="text/javascript">
    $('{$element->getId()}').setAttribute('placeholder', '{$this->_placeholder}');
    Event.observe($('lpa-alexa-regenerate-keys'), 'click', function () {
        confirm('{$confirmMsg}') && generateAlexaKeys();
    });
</script>
EOF;
        } else {
            $title = $this->helper('amazonpayments')->__('Generate Private Key');
            $html .= <<<EOF
<button id="lpa-alexa-generate-keys" title="{$title}" type="button" class="save">
    <span><span><span>{$title}</span></span></span>
</button>
<script type="text/javascript">
    Event.observe($('lpa-alexa-generate-keys'), 'click', generateAlexaKeys);
</script>
EOF;
        }

        $html .= '</div>';
        return $html;
    }
}
