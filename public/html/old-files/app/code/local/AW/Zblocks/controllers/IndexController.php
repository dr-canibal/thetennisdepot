<?php
class AW_Zblocks_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/zblocks?id=15 
    	 *  or
    	 * http://site.com/zblocks/id/15 	
    	 */
    	/* 
		$zblocks_id = $this->getRequest()->getParam('id');

  		if($zblocks_id != null && $zblocks_id != '')	{
			$zblocks = Mage::getModel('zblocks/zblocks')->load($zblocks_id)->getData();
		} else {
			$zblocks = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($zblocks == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$zblocksTable = $resource->getTableName('zblocks');
			
			$select = $read->select()
			   ->from($zblocksTable,array('zblocks_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$zblocks = $read->fetchRow($select);
		}
		Mage::register('zblocks', $zblocks);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}