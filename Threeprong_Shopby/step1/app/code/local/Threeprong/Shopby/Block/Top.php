<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Shopby
*/
/**
 * @author     Andy Hudock <ahudock@pm.me>
 * @package    Threeprong_Shopby
 *
 * Adds PHP 8.2 compatibility to Amasty's Shopby module
 */
class Threeprong_Shopby_Block_Top extends Amasty_Shopby_Block_Top
{
    /**
     * @param $head Mage_Page_Block_Html_Head
     */
    protected function removeCanonical($head)
    {
        foreach ($head->getData('items') as $item) {
            if (strpos($item['params'] ?? '', 'canonical') !== false) {
                $head->removeItem('link_rel', $item['name']);
            };
        }
    }
}
