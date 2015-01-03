<?php
/**
 * Morningtime 
 * LatestReviews module
 *
 * @category   Morningtime
 * @package    Morningtime_LatestReviews
 * @author     Mathijs Koenraadt (info@morningtime.com)
 */

class Morningtime_LatestReviews_Model_Config_Source_TitleType
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'product', 'label'=>Mage::helper('adminhtml')->__('Product')),
            array('value'=>'review', 'label'=>Mage::helper('adminhtml')->__('Review')),
        );
    }
}