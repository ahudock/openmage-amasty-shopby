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