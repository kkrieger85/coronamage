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
 * Observer for the BasePricePro extension.
 *
 * @category   DerModPro
 * @package    DerModPro_BasePricePro
 * @author     Vinai Kopp <vinai@der-modulprogrammierer.de>
 */
class DerModPro_BasePricePro_Model_Observer extends Mage_Core_Model_Abstract
{
	
	/**
	 * Append the baseprice js if necessary (frontend) 
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function blockCatalogProductGetPriceHtml($observer)
	{
		if (
			! Mage::helper('basepricepro')->moduleActive() ||
			Mage::helper('basepricepro')->inAdmin() ||
			! Mage::helper('basepricepro')->getConfig('auto_append_base_price')
		) return;
		$block = $observer->getBlock();
		$product = $block->getProduct();
		if ($product->getBasePriceAmount())
		{
			$container = $observer->getContainer();
			$block->setTemplate('basepricepro/basepricepro.phtml');
			$html = $container->getHtml() . $block->toHtml();
			$container->setHtml($html);
		}
	}
	
	/**
	 * Set the default value on a product in the admin interface
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function controllerActionLayoutLoadBefore($observer)
	{
		if (! Mage::helper('basepricepro')->isBasePriceInstalledAndActive())
		{
			$warn = Mage::helper('basepricepro')->__('Please install the BasePrice extension in order to use the BasePricePro extension!');
			Mage::getSingleton('adminhtml/session')->addWarning($warn);
		}
	}
}

