<?php
$this->startSetup();
// $this->addAttribute('catalog_category', 'cattop_label', array(
//     'group'         => 'General Information',
//     'input'         => 'select',
//     'type'          => 'varchar',
//     'label'         => 'Type label',
//     'backend'       => '',
//     'visible'       => 1,
//     'required'      => 0,
//     'user_defined'  => 1,
//     'source'   => 'cattop/entity_attribute_source_label',
//     'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
// ));

$this->addAttribute('catalog_category', 'hover_thumb', array(
    'group'                    => 'General Information',
    'label'                    => 'Hover thumbnail',
    'input'                    => 'image',
    'type'                     => 'varchar',
    'backend'                  => 'catalog/category_attribute_backend_image',
    'global'                   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'                  => true,
    'required'                 => false,
    'user_defined'             => true,
    'order'                    => 20
));

$this->endSetup();