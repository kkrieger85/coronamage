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
class Creativestyle_AmazonPayments_Block_Adminhtml_SellerCentral extends Mage_Adminhtml_Block_Template
{
    protected $_configOptions = null;

    protected $_ipnUrls = null;

    protected $_jsOrigins = null;

    protected $_returnUrls = null;

    /**
     * Store views collection
     */
    protected $_storeCollection = null;

    /**
     * Store groups array
     */
    protected $_storeGroups = array();

    protected $_merchantIds = null;
    protected $_clientIds = null;

    protected function _construct()
    {
        $this->setTemplate('creativestyle/amazonpayments/seller_central.phtml');
        return parent::_construct();
    }

    protected function _getConfig()
    {
        return Mage::getSingleton('amazonpayments/config');
    }

    /**
     * Returns store views collection
     *
     * @return Mage_Core_Model_Resource_Store_Collection
     */
    protected function _getStoreCollection()
    {
        if (null === $this->_storeCollection) {
            $this->_storeCollection = Mage::getModel('core/store')->getCollection()->load();
        }

        return $this->_storeCollection;
    }

    protected function _getStoreGroup($groupId)
    {
        if (!isset($this->_storeGroups[$groupId])) {
            $this->_storeGroups[$groupId] = Mage::getModel('core/store_group')->load($groupId);
        }

        return $this->_storeGroups[$groupId];
    }

    protected function _getMerchantId($storeId)
    {
        if (null === $this->_merchantIds) {
            $this->_merchantIds = array();
            foreach ($this->_getStoreCollection() as $store) {
                $this->_merchantIds[$store->getId()] = $this->_getConfig()->getMerchantId($store->getId());
            }
        }

        if (isset($this->_merchantIds[$storeId])) {
            return $this->_merchantIds[$storeId];
        }

        return null;
    }

    protected function _getClientId($storeId)
    {
        if (null === $this->_clientIds) {
            $this->_clientIds = array();
            foreach ($this->_getStoreCollection() as $store) {
                $this->_clientIds[$store->getId()] = $this->_getConfig()->getClientId($store->getId());
            }
        }

        if (isset($this->_clientIds[$storeId])) {
            return $this->_clientIds[$storeId];
        }

        return null;
    }

    protected function _getDefaultStoreViewId($groupId)
    {
        return $this->_getStoreGroup($groupId)->getDefaultStoreId();
    }

    protected function _getIpnUrls()
    {
        if (null === $this->_ipnUrls) {
            $this->_ipnUrls = array();
            foreach ($this->_getStoreCollection() as $store) {
                $urlParams = array(
                    '_current' => false,
                    '_nosid' => true,
                    '_store' => $store->getId(),
                    '_secure' => !$this->_getConfig()->isSandboxActive($store->getId())
                );
                $url = Mage::getModel('core/url')
                    ->setStore($store->getId())->getUrl('amazonpayments/ipn/', $urlParams);
                // @codingStandardsIgnoreStart
                $scheme = parse_url($url, PHP_URL_SCHEME);
                // @codingStandardsIgnoreEnd
                if ($scheme == 'https' || $this->_getConfig()->isSandboxActive($store->getId())) {
                    $merchantId = $this->_getMerchantId($store->getId());
                    $defaultStoreId = $this->_getDefaultStoreViewId($store->getGroupId());
                    if (array_search($url, $this->_ipnUrls) != $merchantId || $defaultStoreId == $store->getId()) {
                        $this->_ipnUrls[$merchantId] = $url;
                    }
                }
            }
        }

        return $this->_ipnUrls;
    }

    protected function _getJavascriptOrigins()
    {
        if (null === $this->_jsOrigins) {
            $this->_jsOrigins = array();
            foreach ($this->_getStoreCollection() as $store) {
                $secureUrl = Mage::getModel('core/url')
                    ->setStore($store->getId())
                    ->getUrl(
                        '/',
                        array(
                            '_current' => false, '_secure' => true, '_nosid' => true, '_store' => $store->getId()
                        )
                    );
                // @codingStandardsIgnoreStart
                $scheme = parse_url($secureUrl, PHP_URL_SCHEME);
                if ($scheme == 'https') {
                    $host = parse_url($secureUrl, PHP_URL_HOST);
                    $origin = 'https://' . $host;
                    $port = parse_url($secureUrl, PHP_URL_PORT);
                    if ($port) {
                        $origin .= ':' . $port;
                    }

                    $merchantId = $this->_getClientId($store->getId());
                    if (!isset($this->_jsOrigins[$merchantId])) {
                        $this->_jsOrigins[$merchantId] = array();
                    }

                    if (array_search($origin, $this->_jsOrigins[$merchantId]) === false) {
                        $this->_jsOrigins[$merchantId][] = $origin;
                    }
                }
                // @codingStandardsIgnoreEnd
            }
        }

        return $this->_jsOrigins;
    }

    protected function _getReturnUrls()
    {
        if (null === $this->_returnUrls) {
            $this->_returnUrls = array();
            foreach ($this->_getStoreCollection() as $store) {
                $urlModel = Mage::getModel('core/url');
                $urls = array(
                    $urlModel->setStore($store->getId())->getUrl(
                        'amazonpayments/advanced_login/redirect',
                        array(
                            '_current' => false, '_secure' => true, '_nosid' => true, '_store' => $store->getId()
                        )
                    )
                );
                foreach ($urls as $url) {
                    // @codingStandardsIgnoreStart
                    $scheme = parse_url($url, PHP_URL_SCHEME);
                    // @codingStandardsIgnoreEnd
                    if ($scheme == 'https') {
                        $merchantId = $this->_getClientId($store->getId());
                        if (!isset($this->_returnUrls[$merchantId])) {
                            $this->_returnUrls[$merchantId] = array();
                        }

                        if (array_search($url, $this->_returnUrls[$merchantId]) === false) {
                            $this->_returnUrls[$merchantId][] = $url;
                        }
                    }
                }
            }
        }

        return $this->_returnUrls;
    }

    // @codingStandardsIgnoreStart
    protected function _getIpnUrlsHtml($htmlId) 
    {
        $placeholder = <<<EOH
<em>I couldn't find any IPN endpoint URL :( Please double check if following conditions set is met in any store view:
    <ul>
        <li><strong>General > Web > Secure > Base URL</strong> starts with <strong>https</strong> and <strong>General > Web > Secure > Use Secure URLs in Frontend</strong> is set to <strong>Yes</strong> (alternatively: <strong>Amazon Payments > General Settings > Sandbox Mode</strong> is set to <strong>Yes</strong>)</li>
    </ul>
</em>
EOH;

        $html = '';
        $ipnUrls = $this->_getIpnUrls();
        foreach ($ipnUrls as $merchantId => $ipnUrl) {
            $html .= '<div class="section-config">';
            $html .= sprintf(
                '<div class="entry-edit-head collapseable"><a id="%s-head" href="#" onclick="Fieldset.toggleCollapse(\'%s\'); return false;">%s</a></div>',
                $htmlId . '_' . $merchantId,
                $htmlId . '_' . $merchantId,
                $this->helper('amazonpayments')->__('<span class="close">Click to reveal config for </span>%s', $merchantId)
            );
            $html .= sprintf(
                '<input id="%s-state" name="config_state[%s]" type="hidden" value="1">',
                $htmlId . '_' . $merchantId,
                $htmlId . '_' . $merchantId
            );
            $html .= '<div class="fieldset config" id="' . $htmlId . '_' . $merchantId . '">';
            $html .= sprintf('<a class="nobr" href="%s">%s</a>', $ipnUrl, $ipnUrl);
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<script type="text/javascript">//<![CDATA[' . "\n";
            $html .= 'Fieldset.applyCollapse(\'' . $htmlId . '_' . $merchantId . '\');' . "\n";
            $html .= '//]]></script>' . "\n";
        }

        return $html ? $html : $placeholder;
    }

    protected function _getJavascriptOriginsHtml($htmlId)
    {
        $placeholder = <<<EOH
<em>I couldn't find any JS origin URL :( Please double check if following conditions set is met in any store view:
    <ul>
        <li><strong>General > Web > Secure > Base URL</strong> starts with <strong>https</strong> and <strong>General > Web > Secure > Use Secure URLs in Frontend</strong> is set to <strong>Yes</strong></li>
    </ul>
</em>
EOH;

        $html = '';
        $jsOrigins = $this->_getJavascriptOrigins();
        foreach ($jsOrigins as $merchantId => $origins) {
            $html .= '<div class="section-config">';
            $html .= sprintf(
                '<div class="entry-edit-head collapseable"><a id="%s-head" href="#" onclick="Fieldset.toggleCollapse(\'%s\'); return false;">%s</a></div>',
                $htmlId . '_' . $merchantId,
                $htmlId . '_' . $merchantId,
                $this->helper('amazonpayments')->__('<span class="close">Click to reveal config for </span>%s', $merchantId)
            );
            $html .= sprintf(
                '<input id="%s-state" name="config_state[%s]" type="hidden" value="1">',
                $htmlId . '_' . $merchantId,
                $htmlId . '_' . $merchantId
            );
            $html .= '<div class="fieldset config" id="' . $htmlId . '_' . $merchantId . '">';
            $jsOriginsHtml = array();
            foreach ($origins as $origin) {
                $jsOriginsHtml[] = sprintf('<a class="nobr" href="%s">%s</a>', $origin, $origin);
            }

            $html .= implode('<br/>', $jsOriginsHtml);
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<script type="text/javascript">//<![CDATA[' . "\n";
            $html .= 'Fieldset.applyCollapse(\'' . $htmlId . '_' . $merchantId . '\');' . "\n";
            $html .= '//]]></script>' . "\n";
        }

        return $html ? $html : $placeholder;
    }

    protected function _getReturnUrlsHtml($htmlId) 
    {
        $placeholder = <<<EOH
<em>I couldn't find any return URL :( Please double check if following conditions set is met in any store view:
    <ul>
        <li><strong>General > Web > Secure > Base URL</strong> starts with <strong>https</strong> and <strong>General > Web > Secure > Use Secure URLs in Frontend</strong> is set to <strong>Yes</strong></li>
    </ul>
</em>
EOH;

        $html = '';
        $returnUrls = $this->_getReturnUrls();
        foreach ($returnUrls as $merchantId => $urls) {
            $html .= '<div class="section-config">';
            $html .= sprintf(
                '<div class="entry-edit-head collapseable"><a id="%s-head" href="#" onclick="Fieldset.toggleCollapse(\'%s\'); return false;">%s</a></div>',
                $htmlId . '_' . $merchantId,
                $htmlId . '_' . $merchantId,
                $this->helper('amazonpayments')->__('<span class="close">Click to reveal config for </span>%s', $merchantId)
            );
            $html .= sprintf(
                '<input id="%s-state" name="config_state[%s]" type="hidden" value="1">',
                $htmlId . '_' . $merchantId,
                $htmlId . '_' . $merchantId
            );
            $html .= '<div class="fieldset config" id="' . $htmlId . '_' . $merchantId . '">';
            $returnUrlsHtml = array();
            foreach ($urls as $url) {
                $returnUrlsHtml[] = sprintf('<a class="nobr" href="%s">%s</a>', $url, $url);
            }

            $html .= implode('<br/>', $returnUrlsHtml);
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<script type="text/javascript">//<![CDATA[' . "\n";
            $html .= 'Fieldset.applyCollapse(\'' . $htmlId . '_' . $merchantId . '\');' . "\n";
            $html .= '//]]></script>' . "\n";
        }

        return $html ? $html : $placeholder;
    }

    public function getSellerCentralConfigOptions() 
    {
        if (null === $this->_configOptions) {
            $ipnUrls = $this->_getIpnUrlsHtml($this->getHtmlId() . '_ipn_urls');
            $javascriptOrigins = $this->_getJavascriptOriginsHtml($this->getHtmlId() . '_allowed_javascript_origins');
            $returnUrls = $this->_getReturnUrlsHtml($this->getHtmlId() . '_allowed_return_urls');
            $this->_configOptions = array(
                new Varien_Object(
                    array(
                    'id'        => 'ipn_url',
                    'label'     => $this->helper('amazonpayments')->__('IPN endpoint URL'),
                    'value'     => $ipnUrls,
                    'comment'   => (count($this->_getIpnUrls()) ? (count($this->_getIpnUrls()) > 1
                        ? $this->helper('amazonpayments')->__('Please enter those URLs in the <strong>Merchant URL</strong> field of the <a target="_blank" href="https://sellercentral-europe.amazon.com/gp/pyop/seller/account/settings/user-settings-view.html">Integration Settings</a> in your Amazon Seller Central.')
                        : $this->helper('amazonpayments')->__('Please enter this URL in the <strong>Merchant URL</strong> field of the <a target="_blank" href="https://sellercentral-europe.amazon.com/gp/pyop/seller/account/settings/user-settings-view.html">Integration Settings</a> in your Amazon Seller Central.'))
                        : null
                    ),
                    'depends'   => array(
                        'amazonpayments_general_active' => 1,
                        'amazonpayments_general_ipn_active' => 1
                    )
                    )
                ),
                new Varien_Object(
                    array(
                    'id'        => 'allowed_javascript_origins',
                    'label'     => $this->helper('amazonpayments')->__('Allowed JavaScript Origins'),
                    'value'     => $javascriptOrigins,
                    'comment'   => (count($this->_getJavascriptOrigins()) ? (count($this->_getJavascriptOrigins()) > 1
                        ? $this->helper('amazonpayments')->__('Please enter those URLs in the <strong>Allowed JavaScript Origins</strong> field of the <a target="_blank" href="https://sellercentral-europe.amazon.com/gp/pyop/seller/account/settings/user-settings-view.html">Control Panel</a> in your Amazon Seller Central.')
                        : $this->helper('amazonpayments')->__('Please enter this URL in the <strong>Allowed JavaScript Origins</strong> field of the <a target="_blank" href="https://sellercentral-europe.amazon.com/gp/pyop/seller/account/settings/user-settings-view.html">Control Panel</a> in your Amazon Seller Central.'))
                        : null
                    ),
                    'depends'   => array(
                        'amazonpayments_login_active' => 1
                    )
                    )
                ),
                new Varien_Object(
                    array(
                    'id'        => 'allowed_return_urls',
                    'label'     => $this->helper('amazonpayments')->__('Allowed Return URLs'),
                    'value'     => $returnUrls,
                    'comment'   => (count($this->_getReturnUrls()) ? (count($this->_getReturnUrls()) > 1
                        ? $this->helper('amazonpayments')->__('Please enter those URLs in the <strong>Allowed Return URLs</strong> field of the <a target="_blank" href="https://sellercentral-europe.amazon.com/gp/pyop/seller/account/settings/user-settings-view.html">Control Panel</a> in your Amazon Seller Central.')
                        : $this->helper('amazonpayments')->__('Please enter this URL in the <strong>Allowed Return URLs</strong> field of the <a target="_blank" href="https://sellercentral-europe.amazon.com/gp/pyop/seller/account/settings/user-settings-view.html">Control Panel</a> in your Amazon Seller Central.'))
                        : null
                    ),
                    'depends'   => array(
                        'amazonpayments_login_active' => 1
                    )
                    )
                )
            );
        }

        return $this->_configOptions;
    }
    // @codingStandardsIgnoreEnd

    public function getDependencyJs()
    {
        return '<script type="text/javascript">'
            . 'new FormElementDependenceController(' . $this->_getDependsJson() . ');'
            . '</script>';
    }

    public function getState()
    {
        if ($this->_getConfig()->getMerchantId()) {
            return 1;
        }

        return 0;
    }
}
