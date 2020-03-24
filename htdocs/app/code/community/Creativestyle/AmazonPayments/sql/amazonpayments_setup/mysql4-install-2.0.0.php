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
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2017 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */

/** @var Creativestyle_AmazonPayments_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$installer->addAttribute(
    'customer',
    'amazon_user_id',
    array(
        'type'      => 'varchar',
        'label'     => 'Amazon UID',
        'visible'   => false,
        'required'  => false,
        'unique'    => true
    )
);

$installer->endSetup();
