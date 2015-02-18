<?php
$this->startSetup();

$this->addAttribute('catalog_category', 'catslider', array(
    'group'         => 'General Information',
    'input'         => 'select',
    'type'          => 'varchar',
    'label'         => 'Categories slider',
    'backend'       => '',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'source'   => 'eav/entity_attribute_source_boolean',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE, //Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$this->addAttribute('catalog_product', 'mage_featured_product', array(
        'group'             => 'General',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Featured product',
        'input'             => 'boolean',
        'class'             => '',
        'source'            => '',
        'is_global', Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '0',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'simple,configurable,virtual,bundle,downloadable',
        'is_configurable'   => false,
        'used_in_product_listing', '1'
    ));


$this->updateAttribute('catalog_product', 'mage_featured_product', 'used_in_product_listing', '1');

$this->endSetup();


// $installer = $this;
// $installer->startSetup();

// $setup = new Mage_Eav_Model_Entity_Setup('core_setup');

// $setup->addAttribute('catalog_category', 'custom_attribute', array(
//     'type' => 'int',        // "varchar" for input type text box
//     'group'     => 'General Information',
//     'backend' => '',
//     'frontend' => '',
//     'label' => 'Custom Attribute',
//     'input' => 'select',    // "text" for input type text box
//     'class' => '',
//     'source' => 'eav/entity_attribute_source_boolean',            // Leave blank for input type text box
//     'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
//     'visible' => true,
//     'required' => false,
//     'user_defined' => false,
//     'default' => '0',        //Leave blank for input type text box
//     'searchable' => false,
//     'filterable' => false,
//     'comparable' => false,
//     'visible_on_front' => false,
//     'unique' => false,
// ));

// $installer->endSetup();