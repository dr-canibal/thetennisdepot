<?php

class Interapptive_ShipWorks_Model_ItemAttributes
{
    public function toOptionArray()
    {
        $attributes = Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getAttributeCollection();
        $attributeArray = array();

        $attributeArray['None'] = 'None'; 
        
        foreach($attributes as $attribute)
        {
            if($attribute->getFrontendLabel() != '')
            {
                $attributeArray[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            }
        }
        return $attributeArray; 
    }   
     
    
}
