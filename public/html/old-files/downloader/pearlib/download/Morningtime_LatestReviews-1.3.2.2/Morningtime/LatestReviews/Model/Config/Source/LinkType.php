<?php
/**
 * Morningtime 
 * LatestReviews module
 *
 * @category   Morningtime
 * @package    Morningtime_LatestReviews
 * @author     Mathijs Koenraadt (info@morningtime.com)
 */

class Morningtime_LatestReviews_Model_Config_Source_LinkType
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'product', 'label'=>Mage::helper('adminhtml')->__('Product page')),
            array('value'=>'listing', 'label'=>Mage::helper('adminhtml')->__('Product page with ratings')),
            array('value'=>'review', 'label'=>Mage::helper('adminhtml')->__('Review detail page')),
            array('value'=>'section', 'label'=>Mage::helper('adminhtml')->__('Reviews section at product page')),
        );
    }
}