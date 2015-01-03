<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE 	{$this->getTable('shipping_productmatrix')} ADD COLUMN
   `algorithm` varchar (255) NOT NULL default '';

ALTER TABLE 	{$this->getTable('shipping_productmatrix')} ADD COLUMN
  `notes` varchar (255) NULL;

    	
    ");

$installer->endSetup(); 