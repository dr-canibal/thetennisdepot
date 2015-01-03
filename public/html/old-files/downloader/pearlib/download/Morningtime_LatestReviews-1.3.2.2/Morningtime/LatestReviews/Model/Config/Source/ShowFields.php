<?php
/**
 * Morningtime 
 * LatestReviews module
 *
 * @category   Morningtime
 * @package    Morningtime_LatestReviews
 * @author     Mathijs Koenraadt (info@morningtime.com)
 */

class Morningtime_LatestReviews_Model_Config_Source_ShowFields
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'date', 'label'=>Mage::helper('adminhtml')->__('Date')),
            array('value'=>'title', 'label'=>Mage::helper('adminhtml')->__('Title')),
            array('value'=>'preview', 'label'=>Mage::helper('adminhtml')->__('Preview')),
            array('value'=>'rating', 'label'=>Mage::helper('adminhtml')->__('Rating')),
        );
    }
}