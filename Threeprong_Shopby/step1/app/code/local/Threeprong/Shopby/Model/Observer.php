<?php

/**
 * @package    Threeprong_Shopby
 * @author     Andy Hudock <ahudock@pm.me>
 *
 * Adds PHP 8.2 compatibility to Amasty's Shopby module
 */
class Threeprong_Shopby_Model_Observer extends Amasty_Shopby_Model_Observer
{
    public function onCoreBlockAbstractToHtmlBefore($observer)
    {
        $this->removeParsedQueryParams($observer);
        $this->disableCacheOnSubcategoriesBlock($observer);
        return $this;
    }

    /**
     * For the store switcher.
     * Fix redirect from http://example.com/default/male.html to http://example.com/french/male.html?gender=93
     * where "gender" GET param is excess.
     * @param Varien_Event_Observer $observer
     */
    private function removeParsedQueryParams(Varien_Event_Observer $observer)
    {
        if ($observer->getBlock() instanceof Mage_Page_Block_Switch) {
            $oldQuery = Mage::registry(Amasty_Shopby_Model_Observer::QUERY_BEFORE_SEO_UPDATE, array());
            $currQuery = Mage::app()->getRequest()->getQuery();
            Mage::register(Amasty_Shopby_Model_Observer::QUERY_AFTER_SEO_UPDATE, $currQuery, true);
            foreach ($currQuery as $key => $value) {
                if (!isset($oldQuery[$key])) {
                    unset(${'_GET'}[$key]);
                } else {
                    Mage::app()->getRequest()->setQuery($key, $oldQuery[$key]);
                }
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    private function disableCacheOnSubcategoriesBlock(Varien_Event_Observer $observer)
    {
        if ($observer->getBlock() instanceof Mage_Cms_Block_Block) {
            $blockModel = Mage::getModel('cms/block')->load($observer->getBlock()->getBlockId());
            if (strpos($blockModel->getContent() ?? '', 'amshopby/subcategories') !== false) {
                $observer->getBlock()->setCacheLifetime(null);
            }
        }
    }
}
