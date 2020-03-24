<?php
/**
 * Der Modulprogrammierer - Vinai Kopp, Rico Neitzel GbR
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the
 * Der Modulprogrammierer - COMMERCIAL SOFTWARE LICENSE (v1.0) (DMCSL 1.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.der-modulprogrammierer.de/licenses/dmcsl-1.0.html
 *
 *
 * @category   DerModPro
 * @package    DerModPro_BasePricePro
 * @copyright  Copyright (c) 2011 Der Modulprogrammierer - Vinai Kopp, Rico Neitzel GbR
 * @copyright  Copyright (c) 2012 Netresearch GmbH 
 * @license    http://www.der-modulprogrammierer.de/licenses/dmcsl-1.0.html  (DMCSL 1.0)
 */

/**
 * @var $this Mage_Eav_Model_Entity_Setup
 */
if (Mage::helper('basepricepro')->isBasePriceInstalledAndActive())
{
	$this->startSetup();
	$this->updateAttribute('catalog_product', 'base_price_amount', 'apply_to', 'simple,bundle,configurable');
	$this->updateAttribute('catalog_product', 'base_price_unit', 'apply_to', 'simple,bundle,configurable');
	$this->updateAttribute('catalog_product', 'base_price_base_amount', 'apply_to', 'simple,bundle,configurable');
	$this->updateAttribute('catalog_product', 'base_price_base_unit', 'apply_to', 'simple,bundle,configurable');	
	$this->endSetup();
}


// EOF