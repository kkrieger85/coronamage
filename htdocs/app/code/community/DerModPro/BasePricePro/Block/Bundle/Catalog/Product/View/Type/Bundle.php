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
 * Catalog Bundle Product Info Block
 *
 * @category   DerModPro
 * @package    DerModPro_BasePricePro
 * @author     Vinai Kopp <vinai@der-modulprogrammierer.de>
 */
class DerModPro_BasePricePro_Block_Bundle_Catalog_Product_View_Type_Bundle
	extends Mage_Bundle_Block_Catalog_Product_View_Type_Bundle
{
	/**
	 * Set the module translaton namespace
	 */
	public function _construct()
	{
		$this->setData('module_name', 'Mage_Bundle');
	}
	
    public function getChildHtml($name='', $useCache=true, $sorted=false)
    {
    	$html = parent::getChildHtml($name, $useCache, $sorted);
    	if ($name === 'bundle_prices')
    	{
			$container = new Varien_Object();
			$container->setHtml($html);
			Mage::dispatchEvent('block_catalog_product_get_price_html', array('block' => $this, 'container' => $container));
			$html = $container->getHtml();
    	}
    	return $html;
    }
}