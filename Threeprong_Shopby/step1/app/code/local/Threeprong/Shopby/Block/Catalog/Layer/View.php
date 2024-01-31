<?php
/**
 * @package    Threeprong_Shopby
 * @author     Andy Hudock <ahudock@pm.me>
 *
 * Adds PHP 8.2 compatibility to Amasty's Shopby module
 */
class Threeprong_Shopby_Block_Catalog_Layer_View extends Amasty_Shopby_Block_Catalog_Layer_View
{
    /**
     * @return array
     */
    public function getFilters()
    {
        if ($this->_filterBlocks !== null){
            return $this->_filterBlocks;
        }

        if ($this->_isCurrentUserAgentExcluded()) {
            return array();
        }

        $filters = Amasty_Shopby_Block_Catalog_Layer_View_Pure::getFilters();

        $filters = $this->getChildFilters($filters);

        $filters = $this->_excludeCurrentLandingFilters($filters);

        // append stock filter
        $filter = $this->getChild('stock_filter');
        if ($filter instanceof Amasty_Shopby_Block_Catalog_Layer_Filter_Stock
            && !$this->_notInBlock(Mage::getStoreConfig('amshopby/stock_filter/block_pos'))
        ) {
            $filters[] = $filter;
        }

        /** @var Amasty_Shopby_Block_Catalog_Layer_Filter_Rating $filter */
        $filter = $this->getChild('rating_filter');
        if ($filter && !Mage::helper('amshopby')->useSolr()
            && !$this->_notInBlock(Mage::getStoreConfig('amshopby/rating_filter/block_pos'))
        ) {
            $filters[] = $filter;
        }

        $filter = $this->getChild('new_filter');
        if ($filter instanceof Amasty_Shopby_Block_Catalog_Layer_Filter_New
            && !$this->_notInBlock(Mage::getStoreConfig('amshopby/new_filter/block_pos'))) {
            $filters[] = $filter;
        }

        $filter = $this->getChild('on_sale_filter');
        if ($filter instanceof Amasty_Shopby_Block_Catalog_Layer_Filter_OnSale
            && !$this->_notInBlock(Mage::getStoreConfig('amshopby/on_sale_filter/block_pos'))) {
            $filters[] = $filter;
        }

        // remove some filters from the home page
        $exclude = Mage::getStoreConfig('amshopby/general/exclude');
        if ('/' == Mage::app()->getRequest()->getRequestString() && $exclude) {
            $exclude = explode(',', preg_replace('/[^a-zA-Z0-9_\-,]+/', '', $exclude));
            $filters = $this->excludeFilters($filters, $exclude);
        } else {
            $exclude = array();
        }

        $filters = $this->excludeSplashProPageFilters($filters);

        $this->computeAttributeOptionsData($filters);

        $filtersPositions = Mage::helper('amshopby/attributes')->getPositionsAttributes();

        // update filters with new properties
        $allSelected = array();
        foreach ($filters as $filter) {
            $strategy = $this->_getFilterStrategy($filter);

            if (is_object($strategy)) {
                // initiate all filter-specific logic
                $strategy->prepare();
                $filter->setIsExcluded($strategy->getIsExcluded());

                // remember selected options for dependent excluding
                if ($strategy instanceof Amasty_Shopby_Helper_Layer_View_Strategy_Attribute) {
                    $selectedValues = $strategy->getSelectedValues();
                    if ($selectedValues){
                        $allSelected = array_merge($allSelected, $selectedValues);
                    }
                }
            }

            if (is_object($filter->getAttributeModel())
                && isset($filtersPositions[$filter->getAttributeModel()->getAttributeCode()])) {
                $filter->setPosition($filtersPositions[$filter->getAttributeModel()->getAttributeCode()]);
            }
            if ($filter instanceof Mage_Catalog_Block_Layer_Filter_Category
                || $filter instanceof Enterprise_Search_Block_Catalog_Layer_Filter_Category) {
                $filter->setPosition($filtersPositions['ama_category_filter']);
            }
            if ($filter instanceof Amasty_Shopby_Block_Catalog_Layer_Filter_Rating) {
                $filter->setPosition($filtersPositions['ama_rating_filter']);
            }
            if ($filter instanceof Amasty_Shopby_Block_Catalog_Layer_Filter_New) {
                $filter->setPosition($filtersPositions['ama_new_filter']);
            }
            if ($filter instanceof Amasty_Shopby_Block_Catalog_Layer_Filter_Stock) {
                $filter->setPosition($filtersPositions['ama_stock_filter']);
            }
            if ($filter instanceof Amasty_Shopby_Block_Catalog_Layer_Filter_OnSale) {
                $filter->setPosition($filtersPositions['ama_on_sale_filter']);
            }
        }

        //exclude dependant, since 1.4.7
        foreach ($filters as $filter) {
            $parentAttributes = trim(str_replace(' ', '', $filter->getDependOnAttribute() ?? ''));

            if (!$parentAttributes) {
                continue;
            }

            if (!empty($parentAttributes)) {
                $attributePresent = false;
                $parentAttributes = explode(',', $parentAttributes);
                foreach ($parentAttributes as $parentAttribute) {
                    if (Mage::app()->getRequest()->getParam($parentAttribute)) {
                        $attributePresent = true;
                        break;
                    }
                }
                if (!$attributePresent) {
                    $exclude[] = $filter->getAttributeModel()->getAttributeCode();
                }
            }
        }

        // 1.2.7 exclude some filters from the selected categories
        $filters = $this->excludeFilters($filters, $exclude);

        usort($filters, array(Mage::helper('amshopby/attributes'), 'sortFiltersByOrder'));

        $this->_filterBlocks = $filters;
        return $filters;
    }

    /**
     * @param $filter
     * @param $storeId
     * @return string
     */
    protected function getStoreComment($filter, $storeId)
    {
        if ($comment = $filter->getComment()) {
            if (preg_match('^([adObis]:|N;)^', $comment)) {
                try {
                    $comment = Mage::helper('amshopby')->unserialize($comment);
                } catch (Exception $e) {
                    Mage::log($e->getMessage());
                }

                if (is_array($comment)) {
                    $comment = isset($comment[$storeId])
                        ? $comment[$storeId]
                        : $comment[0];
                }
            }
            $comment = htmlspecialchars($comment ?? '');
        }

        return $comment;
    }

    /**
     * @return bool
     */
    protected function _isCurrentUserAgentExcluded()
    {
        /** @var Mage_Core_Helper_Http $helper */
        $helper = Mage::helper('core/http');
        $currentAgent = $helper->getHttpUserAgent();

        $excludeAgents = explode(',', Mage::getStoreConfig('amshopby/seo/exclude_user_agent'));

        // Remove empty strings from the array, otherwise stripos (below) will always find one at position zero and exclude our user agent
        $excludeAgents = array_filter($excludeAgents, 'strlen');

        foreach ($excludeAgents as $agent) {
            if (stripos($currentAgent, trim($agent)) !== false) {
                return true;
            }
        }

        return false;
    }
}
