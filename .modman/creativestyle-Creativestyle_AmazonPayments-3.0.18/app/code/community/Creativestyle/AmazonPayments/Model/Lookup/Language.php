<?php
/**
 * This file is part of the official Amazon Pay and Login with Amazon extension
 * for Magento 1.x
 *
 * (c) 2014 - 2017 creativestyle GmbH. All Rights reserved
 *
 * Distribution of the derivatives reusing, transforming or being built upon
 * this software, is not allowed without explicit written permission granted
 * by creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_AmazonPayments
 * @copyright  2014 - 2017 creativestyle GmbH
 * @author     Marek Zabrowarny <ticket@creativestyle.de>
 */
class Creativestyle_AmazonPayments_Model_Lookup_Language extends Creativestyle_AmazonPayments_Model_Lookup_Abstract
{
    const LANGUAGE_EN_GB = 'en-GB';
    const LANGUAGE_DE_DE = 'de-DE';
    const LANGUAGE_FR_FR = 'fr-FR';
    const LANGUAGE_IT_IT = 'it-IT';
    const LANGUAGE_ES_ES = 'es-ES';

    /**
     * Array of allowed display languages for Amazon widgets
     *
     * @var array
     */
    protected $_allowedLanguages = array(
        self::LANGUAGE_EN_GB => 'English',
        self::LANGUAGE_DE_DE => 'German',
        self::LANGUAGE_FR_FR => 'French',
        self::LANGUAGE_IT_IT => 'Italian',
        self::LANGUAGE_ES_ES => 'Spanish'
    );

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (null === $this->_options) {
            $this->_options = array(array(
                'value' => '',
                'label' => Mage::helper('amazonpayments')->__('Auto')
            ));
            foreach ($this->_allowedLanguages as $languageCode => $languageName) {
                $this->_options[] = array(
                    'value' => $languageCode,
                    'label' => Mage::helper('amazonpayments')->__($languageName)
                );
            }
        }

        return $this->_options;
    }

    /**
     * @param string|null $locale
     * @param bool $isoFormatReturn
     * @return string|null
     */
    public function getLanguageByLocale($locale = null, $isoFormatReturn = false)
    {
        if (null === $locale) {
            $locale = Mage::app()->getLocale()->getLocaleCode();
        }

        $amazonLocale = str_replace('_', '-', $locale);

        if (in_array($amazonLocale, array_keys($this->_allowedLanguages))) {
            return $isoFormatReturn ? $locale : $amazonLocale;
        }

        $localeLanguagePart = substr($locale, 0, 2);

        foreach ($this->_allowedLanguages as $languageCode => $languageName) {
            if (false !== strpos($languageCode, $localeLanguagePart)) {
                return $isoFormatReturn ? str_replace('-', '_', $languageCode) : $languageCode;
            }
        }

        return null;
    }
}
