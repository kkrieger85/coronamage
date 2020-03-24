<?php

class DerModPro_BasePricePro_Block_Catalog_Product_View_Type_Grouped
    extends Mage_Catalog_Block_Product_View_Type_Grouped
{
	/**
	 * Set the module translation namespace
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
        // the associated simple product needs to be set as current product 
        $this->setProduct($product);
        
        $html = parent::getPriceHtml($product, $displayMinimalPrice, $idSuffix);
        $container = new Varien_Object();
        $container->setHtml($html);
        Mage::dispatchEvent('block_catalog_product_get_price_html', array('block' => $this, 'container' => $container));
        return $container->getHtml();
    }
}