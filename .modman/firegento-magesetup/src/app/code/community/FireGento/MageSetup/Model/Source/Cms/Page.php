<?php
/**
 * This file is part of a FireGento e.V. module.
 *
 * This FireGento e.V. module is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This script is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * PHP version 5
 *
 * @category  FireGento
 * @package   FireGento_MageSetup
 * @author    FireGento Team <team@firegento.com>
 * @copyright 2013-2015 FireGento Team (http://www.firegento.com)
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 */

/**
 * CMS Source model for configuration dropdown of CMS pages
 *
 * @category FireGento
 * @package  FireGento_MageSetup
 * @author   FireGento Team <team@firegento.com>
 */
class FireGento_MageSetup_Model_Source_Cms_Page extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * @var array $_options cached options
     */
    protected $_options;

    /**
     * Return option array
     *
     * @return array Pages as option array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            /** @var $pages Mage_Cms_Model_Resource_Page_Collection */
            $pages = Mage::getModel('cms/page')->getCollection()
                ->addFieldToFilter('is_active', 1)
                ->setOrder('identifier', 'ASC');

            $options = array();

            foreach ($pages as $page) {
                /** @var $page Mage_Cms_Model_Page */
                $options[$page->getIdentifier()] = $page->getIdentifier();
            }

            foreach ($options as $identifier) {
                $this->_options[] = array(
                    'value' => $identifier,
                    'label' => $identifier,
                );
            }
        }

        array_unshift($this->_options, array('value' => '', 'label' => Mage::helper('magesetup')->__('No Page')));

        return $this->_options;
    }

    /**
     * Get all options as array
     *
     * @return array Pages as option array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }
}
