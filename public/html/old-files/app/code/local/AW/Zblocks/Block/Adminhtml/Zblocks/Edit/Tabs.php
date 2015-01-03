<?php

class AW_Zblocks_Block_Adminhtml_Zblocks_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('zblocks_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('zblocks')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('zblocks')->__('Item Information'),
          'title'     => Mage::helper('zblocks')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('zblocks/adminhtml_zblocks_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}