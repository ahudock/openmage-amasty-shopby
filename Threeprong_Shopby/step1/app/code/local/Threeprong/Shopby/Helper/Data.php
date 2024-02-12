<?php
/**
* @copyright Amasty.
*/ 
/**
 * @package    Threeprong_Shopby
 * @author     Andy Hudock <ahudock@pm.me>
 *
 * Adds PHP 8.2 compatibility to Amasty's Shopby module
 */
class Threeprong_Shopby_Helper_Data extends Amasty_Shopby_Helper_Data
{
    /**
     * Gets params (6,17,89) from the request as array and sanitize them
     *
     * @param string $key attribute code
     * @param string $backendType attribute type
     * @return array
     */
    public function getRequestValues($key, $backendType = '')
    {
        $v = Mage::app()->getRequest()->getParam($key);

        if (is_array($v) || $backendType == 'decimal') {//smth goes wrong
            return array();
        }

        $tmp = str_replace(Amasty_Shopby_Helper_Attributes::MAPPED_PREFIX, '', $v ?? '');
        if (preg_match('/^[0-9,]+$/', $tmp)) {
            $v = array_unique(explode(',', $v));
        } else {
            $v = array();
        }

        return $v;
    }
}
