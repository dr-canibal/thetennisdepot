<?php

class Velite_Teaserbox_Model_Resource_Eav_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup
{
	/**
	 * @return array
	 */
    public function getDefaultEntities()
    {

        return array(
            'catalog_product' => array(
                'entity_model'      => 'catalog/product',
                'attribute_model'   => 'catalog/resource_eav_attribute',
                'table'             => 'catalog/product',
                'attributes'        => array(
                    'teaserboxadd'      => array(
                        'group'             => 'Teaserbox',
                        'label'             => 'Show in Teaserbox',
                        'type'              => 'int',
                        'input'             => 'select',
                        'default'           => '0',
                        'class'             => '',
                        'backend'           => '',
                        'frontend'          => '',
                        'source'            => 'velite_teaserbox_model_product_attribute_source_images',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           => true,
                        'required'          => false,
                        'user_defined'      => false,
                        'searchable'        => false,
                        'filterable'        => false,
                        'comparable'        => false,
                        'visible_on_front'  => false,
                        'visible_in_advanced_search' => false,
                        'unique'            => false
                    ),
                    'teaserboxprio' => array(
                        'group'             => 'Teaserbox',
                        'label'             => 'Order by priority',
                        'type'              => 'int',
                        'input'             => 'text',
                        'default'           => 5,
                        'class'             => '',
                        'backend'           => '',
                        'frontend'          => '',
                        'source'            => '',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           => true,
                        'required'          => false,
                        'user_defined'      => false,
                        'searchable'        => false,
                        'filterable'        => false,
                        'comparable'        => false,
                        'visible_on_front'  => false,
                        'visible_in_advanced_search' => false,
                        'unique'            => false,
                    ),
                    'teaserboxalttext' => array(
                        'group'             => 'Teaserbox',
                        'label'             => 'Alternative Productname',
                        'type'              => 'text',
                        'input'             => 'text',
                        'default'           => '',
                        'class'             => '',
                        'backend'           => '',
                        'frontend'          => '',
                        'source'            => '',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           => true,
                        'required'          => false,
                        'user_defined'      => false,
                        'searchable'        => false,
                        'filterable'        => false,
                        'comparable'        => false,
                        'visible_on_front'  => false,
                        'visible_in_advanced_search' => false,
                        'unique'            => false,
                    )
                )
            )
		
        );
    }
}