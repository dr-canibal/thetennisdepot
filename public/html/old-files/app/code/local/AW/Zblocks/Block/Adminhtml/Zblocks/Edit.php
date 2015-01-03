<?php

class AW_Zblocks_Block_Adminhtml_Zblocks_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'zblocks';
        $this->_controller = 'adminhtml_zblocks';
        
        $this->_updateButton('save', 'label', Mage::helper('zblocks')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('zblocks')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('zblocks_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'zblocks_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'zblocks_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('zblocks_data') && Mage::registry('zblocks_data')->getId() ) {
            return Mage::helper('zblocks')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('zblocks_data')->getTitle()));
        } else {
            return Mage::helper('zblocks')->__('Add Item');
        }
    }
}