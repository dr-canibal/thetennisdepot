<?php

class AW_Zblocks_Block_Adminhtml_Zblocks_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('zblocks_form', array('legend'=>Mage::helper('zblocks')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('zblocks')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('zblocks')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('zblocks')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('zblocks')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('zblocks')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('zblocks')->__('Content'),
          'title'     => Mage::helper('zblocks')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getZblocksData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getZblocksData());
          Mage::getSingleton('adminhtml/session')->setZblocksData(null);
      } elseif ( Mage::registry('zblocks_data') ) {
          $form->setValues(Mage::registry('zblocks_data')->getData());
      }
      return parent::_prepareForm();
  }
}