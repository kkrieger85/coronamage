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
 * Helper for the baseprice extension.
 *
 * @category   DerModPro
 * @package    DerModPro_BasePricePro
 * @author     Vinai Kopp <vinai@der-modulprogrammierer.de>
 */
class DerModPro_BasePricePro_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Check if the script is called from the adminhtml interface
	 *
	 * @return boolean
	 */
	public function inAdmin()
	{
		return Mage::app()->getStore()->isAdmin();
	}
	
	/**
	 * Dump a variable to the logfile (defaults to hideprices.log)
	 *
	 * @param mixed $var
	 * @param string $file
	 */
	public function log($var, $file = null)
	{
		$file = isset($file) ? $file : 'baseprice.log';
		
		$var = print_r($var, 1);
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $var = str_replace("\n", "\r\n", $var);
		Mage::log($var, null, $file);
	}

	/**
	 * Check if the extension has been disabled in the system configuration
	 */
	public function moduleActive()
	{
		return (! (bool) $this->getConfig('disable_ext')) && $this->isBasePriceInstalledAndActive();
	}
	
	/**
	 * Check if the BasePrice extension is installed and active
	 *
	 * @return boolean
	 */
    public function isBasePriceInstalledAndActive()
    {
    	if ($node = Mage::getConfig()->getNode('modules/DerModPro_BasePrice'))
    	{
    		return strval($node->active) == 'true';
    	}
    	return false;
    }
	
	/**
	 * Return the config value for the passed key (current store)
	 * Use the baseprice config scpe, since the bBasePricePro vrsion doesn't have it's own
	 * 
	 * @param string $key
	 * @return string
	 */
	public function getConfig($key)
	{
		$path = 'catalog/baseprice/' . $key;
		return Mage::getStoreConfig($path, Mage::app()->getStore());
	}
	
	public function getBasePriceConfigJson(Mage_Catalog_Model_Product $product)
	{
		$config = array();
		$referenceAmount = $product->getBasePriceBaseAmount();
		$referenceUnit = $product->getBasePriceBaseUnit();
		$productAmount = $product->getBasePriceAmount();
		$productUnit = $product->getBasePriceUnit();
		if ($productAmount) {
			// will throw Exception if no conversion rate is defined
			try {
				$rate = Mage::getSingleton('baseprice/baseprice')->getConversionRate($productUnit, $referenceUnit);
				$config['productAmount'] = $productAmount;
				$config['productUnit'] = $this->__(DerModPro_BasePrice_Model_Baseprice::UNIT_TRANSLATION_PREFIX.$productUnit);
				$config['referenceAmount'] = $referenceAmount;
				$config['referenceUnit'] = $this->__(DerModPro_BasePrice_Model_Baseprice::UNIT_TRANSLATION_PREFIX.$referenceUnit);
				$config['referenceUnitShort'] = $this->__(DerModPro_BasePrice_Model_Baseprice::UNIT_TRANSLATION_PREFIX_SHORT.$referenceUnit);
				$config['rate'] = $rate;
				$config['optionAmounts'] = $product->isConfigurable() ? $this->_getUsedProductsBasePriceConfig($product) : 0;
			}
			catch (Exception $e) {}
		}
		if (! isset($config['productAmount'])) $config['productAmount'] = 0;

		// provide a possibility to add new config values
		$additionalConfig = new Varien_Object();
		Mage::dispatchEvent('basepricepro_base_price_config_json_encode_before',
			array('product' => $product, 'config' => $config, 'additional_config' => $additionalConfig));
		$config = array_merge($config, $additionalConfig->toArray());

		return Zend_Json::encode($config);
	}
	
	protected function _getUsedProductsBasePriceConfig(Mage_Catalog_Model_Product $product)
	{
		$config = array();
		foreach ($product->getTypeInstance()->getUsedProducts() as $_product)
		{
			$config[$_product->getId()] = ($productBasePriceAmount = $_product->getBasePriceAmount()) ? $productBasePriceAmount : 0;
		}
		return $config;
	}
}

