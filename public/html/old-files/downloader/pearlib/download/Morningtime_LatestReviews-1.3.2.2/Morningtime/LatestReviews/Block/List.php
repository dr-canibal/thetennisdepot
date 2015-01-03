<?php
/**
 * Morningtime 
 * LatestReviews module
 *
 * @category   Morningtime
 * @package    Morningtime_LatestReviews
 * @author     Mathijs Koenraadt (info@morningtime.com)
 */


/**
 * Reviews list
 *
 * @category   Morningtime
 * @package    Morningtime_LatestReviews
 */

class Morningtime_LatestReviews_Block_List extends Mage_Review_Block_View
{

    protected $_defaultToolbarBlock = 'latestreviews/list_toolbar';	
	
    public function getReviewsCollection()
    {
    	$listLimit  = intval(Mage::getStoreConfig('latestreviews/general/num_displayed_reviews'));
    	$sortBy		= Mage::getStoreConfig('latestreviews/general/sort_by');
		$reviewTable 	= Mage::getSingleton('core/resource')->getTableName('review');
		$rdetailTable	= Mage::getSingleton('core/resource')->getTableName('review_detail');
		$rsummTable		= Mage::getSingleton('core/resource')->getTableName('review_entity_summary');
		$storeId 		= Mage::app()->getStore()->getStoreId();
		$dir 		= "DESC";
		$write 		= Mage::getSingleton('core/resource')->getConnection('core_write');		
		$result 	= $write->query("select r.review_id, r.created_at, r.entity_pk_value, rd.title, rd.detail, rd.nickname, rs.rating_summary from ".$reviewTable." r, ".$rdetailTable." rd, ".$rsummTable." rs 
						where r.entity_pk_value = rs.entity_pk_value and r.review_id = rd.review_id and r.status_id=1 and rs.store_id=$storeId
						order by $sortBy $dir
						limit $listLimit");
								
        return $result;
    }

    public function dateFormat($date)
    {
        return $this->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    public function getReviewLink($id)
    {
        return Mage::getUrl('review/product/view', array('id' => $id));
    }
	
	public function getListFields()
	{
		return Mage::getStoreConfig('latestreviews/reviews/show_fields');
	}
	
	public function getListLimit()
	{
		return intval(Mage::getStoreConfig('latestreviews/general/num_displayed_reviews'));
	}

    public function getProductLink($p, $r)
    {
    	$linktype = Mage::getStoreConfig('latestreviews/reviews/link_to');
		switch ($linktype)
		{
		case 'product':
			$u = $p->getProductUrl();
		break;
		case 'review':
			$u = $this->getReviewLink($r['review_id']);
		break;
		case 'listing':
			$c = $p->getCategoryIds();
			$u = Mage::getUrl('review/product/list/id/', array('id' => $r['entity_pk_value']))."category/".$c[0]."/";
		break;
		case 'section':
			$c = $p->getCategoryIds();
			$u = Mage::getUrl('review/product/list/id/', array('id' => $r['entity_pk_value']))."category/".$c[0]."#customer-reviews";
		break;
		default:
			$u = $p->getProductUrl();
		}
        return $u;
    }
	
    public function getTitle($p, $r)
    {
    	$showtitle = Mage::getStoreConfig('latestreviews/reviews/show_title');
		switch ($showtitle)
		{
		case 'product':
			$t = $p->getName();
		break;
		case 'review':
			$t = $r['title'];
		break;
		default:
			$t = $p->getName();
		}
        return $this->htmlEscape($t);
    }

    /**
     * Translate block sentence
     *
     * @return string
     */
    public function __()
    {
        $args = func_get_args();
        $expr = new Mage_Core_Model_Translate_Expr(array_shift($args), 'Mage_Catalog');
        array_unshift($args, $expr);
        return Mage::app()->getTranslator()->translate($args);
    }

}