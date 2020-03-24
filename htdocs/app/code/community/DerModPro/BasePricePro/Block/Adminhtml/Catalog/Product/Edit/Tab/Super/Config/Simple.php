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
 * Adminhtml Block Quick Simple Product Creator Form
 *
 * @category   DerModPro
 * @package    DerModPro_BasePricePro
 * @author     Vinai Kopp <vinai@der-modulprogrammierer.de>
 */
class DerModPro_BasePricePro_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Config_Simple
	extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Simple
{

	/**
	 * Add the base_price_amount text field
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();
		$form = $this->getForm();
		$fieldset = $form->getElement('simple_product');
		$attribute = $this->_getProduct()->getResource()->getAttribute('base_price_amount');
		$attributeCode = $attribute->getAttributeCode();
		$element = $fieldset->addField(
			'simple_product_' . $attributeCode,
			$attribute->getFrontend()->getInputType(),
			array(
				'label'    => $attribute->getFrontend()->getLabel(),
				'name'     => $attributeCode,
				'required' => $attribute->getIsRequired(),
				//'class'    => 'validate-number',
				//'values'   => $attribute->getSource()->getAllOptions(true, true),
				'value' => $this->_getProduct()->getData($attributeCode)
			),
			'simple_product_inventory_is_in_stock' // add after which element
		);

		// add the other base_price attributes as hidden fields using the values of the configurable product
		foreach (array('base_price_unit', 'base_price_base_unit', 'base_price_base_amount') as $attributeCode)
		{
			$fieldset->addField(
				'simple_product_' . $attributeCode, 'hidden',
				array(
					'name' => $attributeCode,
					'value' => $this->_getProduct()->getData($attributeCode),
				)
			);
		}
	}
}