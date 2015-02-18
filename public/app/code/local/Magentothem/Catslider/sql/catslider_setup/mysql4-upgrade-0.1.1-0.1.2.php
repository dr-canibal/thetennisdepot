<?php
$this->startSetup();
$this->updateAttribute('catalog_category', 'catslider', 'global', Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE);
$this->endSetup();