<?php
class Magentothem_Catslider_Model_System_Config_Category
{

    public function toOptionArray()
    {
        // return array(
        //     array('value'=>'name', 'label'=>Mage::helper('adminhtml')->__('Name')),
        //     array('value'=>'price', 'label'=>Mage::helper('adminhtml')->__('Price'))
        // );
		$cats = $this->getCategories();
		$options[0] = 'None';
		foreach ($cats as $cat) {
			$options[$cat->getId()] = $cat->getName();
		}
        return $options;
    }

	public function getCategories()
	{
		$_categories = Mage::getModel('catalog/category')->getCollection()
		->addAttributeToSelect('name')
		->addAttributeToFilter('is_active',array('eq'=>true))
		->addAttributeToFilter('catslider',array('eq'=>true))
		->load();
		return $_categories;
	}


}
