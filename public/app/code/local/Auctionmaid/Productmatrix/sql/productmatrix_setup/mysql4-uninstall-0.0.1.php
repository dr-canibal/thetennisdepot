<?php
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('shipping_productmatrix')};
DELETE FROM {$this->getTable('core/config_data')} WHERE path like 'carriers/productmatrix/%';
select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='package_id';
DELETE FROM {$this->getTable('eav_entity_attribute')} WHERE attribute_id =  @attribute_id;
DELETE FROM {$this->getTable('eav_attribute')} WHERE attribute_code =  'package_id';
DELETE FROM {$this->getTable('eav_attribute_group')} WHERE attribute_group_name =  'Shipping';



");

$installer->endSetup();
