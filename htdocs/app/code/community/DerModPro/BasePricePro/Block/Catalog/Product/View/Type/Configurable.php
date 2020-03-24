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
 * Configurable Product View block
 *
 * @category   DerModPro
 * @package    DerModPro_BasePricePro
 * @author     Vinai Kopp <vinai@der-modulprogrammierer.de>
 */
class DerModPro_BasePricePro_Block_Catalog_Product_View_Type_Configurable
	extends Mage_Catalog_Block_Product_View_Type_Configurable
{
	/**
	 * Set the module translaton namespace
	 */
	public function _construct()
	{
		$this->setData('module_name', 'Mage_Catalog');
		$this->setBcpNoCache(true);
	}
	
	/**
     * Returns product price block html
     *
     * @param Mage_Catalog_Model_Product $product
     * @param boolean $displayMinimalPrice
     */
    public function getPriceHtml($product, $displayMinimalPrice = false, $idSuffix='')
    {
    	$html = parent::getPriceHtml($product, $displayMinimalPrice, $idSuffix);
		$container = new Varien_Object();
		$container->setHtml($html);
		Mage::dispatchEvent('block_catalog_product_get_price_html', array('block' => $this, 'container' => $container));
		$html = $container->getHtml();
		return $html;
    }
}