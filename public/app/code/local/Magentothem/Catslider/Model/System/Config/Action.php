<?php
class Magentothem_Catslider_Model_System_Config_Action
{

    // public function toOptionArray()
    // {
    //     return array(
    //     	array('value'=>'7', 'label'=>Mage::helper('adminhtml')->__('Display all')),
    //     	array('value'=>'6', 'label'=>Mage::helper('adminhtml')->__('Cart with Wishlist')),
    //         array('value'=>'5', 'label'=>Mage::helper('adminhtml')->__('Cart with Compare')),
    //         array('value'=>'4', 'label'=>Mage::helper('adminhtml')->__('Add to Cart')),
    //         array('value'=>'3', 'label'=>Mage::helper('adminhtml')->__('Compare with Wishlist')),
    //         array('value'=>'2', 'label'=>Mage::helper('adminhtml')->__('Wishlist')),
    //         array('value'=>'1', 'label'=>Mage::helper('adminhtml')->__('Add to Compare')),
    //         array('value'=>'0', 'label'=>Mage::helper('adminhtml')->__('Display none'))
    //     );
    // }

    public function toOptionArray()
    {
        return array(
            array('value'=>'0', 'label'=>Mage::helper('adminhtml')->__('None')),
            array('value'=>'cart', 'label'=>Mage::helper('adminhtml')->__('Add to Cart')),
            array('value'=>'compare', 'label'=>Mage::helper('adminhtml')->__('Add to Compare')),
            array('value'=>'wishlist', 'label'=>Mage::helper('adminhtml')->__('Wishlist')),
            array('value'=>'cart,compare', 'label'=>Mage::helper('adminhtml')->__('Cart with Compare')),
            array('value'=>'cart,wishlist', 'label'=>Mage::helper('adminhtml')->__('Cart with Wishlist')),
            array('value'=>'compare,wishlist', 'label'=>Mage::helper('adminhtml')->__('Compare with Wishlist')),
            array('value'=>'compare,cart,wishlist', 'label'=>Mage::helper('adminhtml')->__('Display all')),
        );
    }

}