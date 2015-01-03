<?php
/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Manage Comment Edit Form
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 
class TBT_Rewards extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form(array(
                                      'id' => 'edit_form',
                                      'action' => $this->getUrl('//save', array('id' => $this->getRequest()->getParam('id'))),
                                      'method' => 'post',
                                   ));
	  $form->setUseContainer(true);
      $this->setForm($form);
	  
      $fieldset = $form->addFieldset('comment_form', array('legend'=>Mage::helper('rewards')->__('Comment Information')));
     
      $fieldset->addField('user', 'text', array(
          'label'     => Mage::helper('rewards')->__('User'),
          'name'      => 'user',
      ));
	  
	  $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('rewards')->__('Email Address'),
          'name'      => 'email',
      ));

      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('rewards')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('rewards')->__('Unapproved'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('rewards')->__('Approved'),
              ),
          ),
      ));
     
      $fieldset->addField('comment', 'editor', array(
          'name'      => 'comment',
          'label'     => Mage::helper('rewards')->__('Comment'),
          'title'     => Mage::helper('rewards')->__('Comment'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => false,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getBlogData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getBlogData());
          Mage::getSingleton('adminhtml/session')->setBlogData(null);
      } elseif ( Mage::registry('blog_data') ) {
          $form->setValues(Mage::registry('blog_data')->getData());
      }
      return parent::_prepareForm();
  }
}