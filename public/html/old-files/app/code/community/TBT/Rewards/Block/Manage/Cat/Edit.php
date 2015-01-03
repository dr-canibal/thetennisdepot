<?php

class TBT_Rewards_Block_Manage_Cat_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'blog';
        $this->_controller = 'manage_cat';
        
        $this->_updateButton('save', 'label', Mage::helper('rewards')->__('Save Category'));
        $this->_updateButton('delete', 'label', Mage::helper('rewards')->__('Delete Category'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);
		
		$this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('blog_data') && Mage::registry('blog_data')->getId() ) {
            return Mage::helper('rewards')->__("Edit Category  '%s'", $this->htmlEscape(Mage::registry('blog_data')->getTitle()));
        } else {
            return Mage::helper('rewards')->__('Add Category');
        }
    }
}