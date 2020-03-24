<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2017 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @package    Creativestyle\AmazonPayments\Block
 * @copyright  2017 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */

/**
 * Amazon Pay button tooltip block
 */
class Creativestyle_AmazonPayments_Block_Pay_Tooltip extends Creativestyle_AmazonPayments_Block_Pay_Abstract
{
    /**
     * Text for the default Amazon Pay button tooltip
     */
    const DEFAULT_TOOLTIP_TEXT = '<strong>Amazon Pay</strong> helps you shop quickly, safely and securely. You can pay on our website without re-entering your payment and address details. All Amazon transactions are protected by Amazon&apos;s A-to-z Guarantee.'; // @codingStandardsIgnoreLine

    /**
     * Text for Amazon Pay button tooltip for virtual products in the cart
     */
    const VIRTUAL_PRODUCTS_TOOLTIP_TEXT = '<strong>Amazon Pay</strong> helps you shop quickly, safely and securely. You can pay on our website without re-entering your payment and address details.'; // @codingStandardsIgnoreLine

    /**
     * Returns text for Amazon Pay button tooltip
     *
     * @return string
     */
    public function getTooltipText()
    {
        if ($this->_quoteHasVirtualItems()) {
            return self::VIRTUAL_PRODUCTS_TOOLTIP_TEXT;
        }

        return self::DEFAULT_TOOLTIP_TEXT;
    }
}
