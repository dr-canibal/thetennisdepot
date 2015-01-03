<?php
/**
 * Morningtime 
 * LatestReviews module
 *
 * @category   Morningtime
 * @package    Morningtime_LatestReviews
 * @author     Mathijs Koenraadt (info@morningtime.com)
 */

class Morningtime_LatestReviews_Model_Config_Source_SortFields
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'rs.rating_summary', 'label'=>Mage::helper('adminhtml')->__('Best rated first')),
            array('value'=>'r.created_at', 'label'=>Mage::helper('adminhtml')->__('Most recent first')),
        );
    }
}