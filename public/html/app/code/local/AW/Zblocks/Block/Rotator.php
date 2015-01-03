<?php
class AW_Zblocks_Block_Rotator extends Mage_Core_Block_Template
{
	public function prepareBlocks(){
		if(($this->ids)){

			$this->_blockid = preg_split("/[, 	]+/",$this->ids,-1,PREG_SPLIT_NO_EMPTY);
		}else{
			$this->_blockid = array();
		}
		
		
		
	}
	
	
	protected function _toHtml(){
		
		$this->prepareBlocks();
		$html = "";
		
		
		if(@$this->mode == 'all'){
			foreach($this->_blockid as $id){
				$html .=  $this->getLayout()->createBlock('cms/block')->setBlockId($id)->toHtml();
			}
		}elseif(@$this->mode == 'random'){
			$id = rand(0, count($this->_blockid)-1);
			$html .= $this->getLayout()->createBlock('cms/block')->setBlockId($this->_blockid[$id])->toHtml();
			
		}else{
			$session = Mage::getSingleton('customer/session');
			$session->start();
			
			$i = $session->getZblocksRotator() ? $session->getZblocksRotator() : 0;
			
			$html .= $this->getLayout()->createBlock('cms/block')->setBlockId($this->_blockid[$i])->toHtml();
			
			if($i>=(count($this->_blockid) - 1 )){
				$i = 0;
			}else{
				$i+=1;
			}
			$session->setZblocksRotator($i);
		}
		return $html ;
	}
}
