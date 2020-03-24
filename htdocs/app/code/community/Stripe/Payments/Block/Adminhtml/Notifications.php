<?php

class Stripe_Payments_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{
    public function areWebhooksConfigured()
    {
        return !file_exists(Mage::getBaseDir('cache') . DS . 'stripe_payments_webhooks.log');
    }

    public function getStripeWebhooksConfigurationLink()
    {
        return "https://stripe.com/docs/plugins/magento/configuration#webhooks";
    }
}
