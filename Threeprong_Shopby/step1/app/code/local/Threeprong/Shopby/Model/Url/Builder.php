<?php
/**
 * @package    Threeprong_Shopby
 * @author     Andy Hudock <ahudock@pm.me>
 *
 * Adds PHP 8.2 compatibility to Amasty's Shopby module
 */
class Threeprong_Shopby_Model_Url_Builder extends Amasty_Shopby_Model_Url_Builder
{
    protected static function initialize()
    {
        Amasty_Shopby_Model_Url_Builder::$mode = Mage::getStoreConfig('amshopby/seo/urls');
        Amasty_Shopby_Model_Url_Builder::$brandAttributeCode = trim(Mage::getStoreConfig('amshopby/brands/attr'));
        Amasty_Shopby_Model_Url_Builder::$filterUrlSortMode = Mage::getStoreConfig('amshopby/seo/sort_attributes_in_url');
        Amasty_Shopby_Model_Url_Builder::$brandUrlKey = trim(Mage::getStoreConfig('amshopby/brands/url_key'));
        Amasty_Shopby_Model_Url_Builder::$specialChar = Mage::getStoreConfig('amshopby/seo/special_char');
        Amasty_Shopby_Model_Url_Builder::$multiselectQueryParam = trim(Mage::getStoreConfig('amshopby/seo/query_param'));
        /** @var Amasty_Shopby_Helper_Data $helper */
        $helper = Mage::helper('amshopby');
        Amasty_Shopby_Model_Url_Builder::$isCategoryMultiselect = $helper->getCategoriesMultiselectMode();
        Amasty_Shopby_Model_Url_Builder::$attributesPositions = Mage::helper('amshopby/attributes')->getPositionsAttributes();

        Amasty_Shopby_Model_Url_Builder::$rootCategoryId = (int) Mage::app()->getStore()->getRootCategoryId();
        Amasty_Shopby_Model_Url_Builder::$reservedKey = trim(Mage::getStoreConfig('amshopby/seo/key'));
        Amasty_Shopby_Model_Url_Builder::$magentoBaseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, Mage::app()->getStore()->isCurrentlySecure());

        $excludeParamsStr = trim(Mage::getStoreConfig('amshopby/seo/query_param_exclude') ?? '');
        Amasty_Shopby_Model_Url_Builder::$excludeParams = $excludeParamsStr == '' ? array() : explode(',', $excludeParamsStr);

        Amasty_Shopby_Model_Url_Builder::$currentLandingKey = Mage::app()->getRequest()->getParam('am_landing');
        Amasty_Shopby_Model_Url_Builder::$currentShopByBrandId = Mage::app()->getRequest()->getParam('ambrand_id', null);
        Amasty_Shopby_Model_Url_Builder::$filterGlue = (Amasty_Shopby_Model_Url_Builder::$mode == Amasty_Shopby_Model_Source_Url_Mode::MODE_SHORT) ? Mage::getStoreConfig('amshopby/seo/option_char') : '/';
        Amasty_Shopby_Model_Url_Builder::$urlHelper = $helper = Mage::helper('amshopby/url');
        Amasty_Shopby_Model_Url_Builder::$optionsHash = Amasty_Shopby_Model_Url_Builder::$urlHelper->getAllFilterableOptionsAsHash();

        Amasty_Shopby_Model_Url_Builder::$initialized = true;
    }

    public function reset()
    {
        if (!Amasty_Shopby_Model_Url_Builder::$initialized) {
            self::initialize();
        }

        /** @var Amasty_Shopby_Helper_Data $helper */
        $helper = Mage::helper('amshopby');
        $this->category = $helper->getCurrentCategory();

        // Destination parameters
        $this->moduleName = Mage::app()->getRequest()->getModuleName();
        if ($this->moduleName == 'cms') {
            $this->clearModule();
        }
        $this->query = Mage::app()->getRequest()->getQuery();
    }
}