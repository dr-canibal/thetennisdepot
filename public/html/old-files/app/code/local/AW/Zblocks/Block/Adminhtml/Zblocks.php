<?php
class AW_Zblocks_Block_Adminhtml_Zblocks extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_zblocks';
    $this->_blockGroup = 'zblocks';
    $this->_headerText = Mage::helper('zblocks')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('zblocks')->__('Add Item');
    parent::__construct();
  }
}