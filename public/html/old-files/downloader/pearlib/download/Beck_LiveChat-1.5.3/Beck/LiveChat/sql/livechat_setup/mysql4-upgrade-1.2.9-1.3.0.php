<?php
$installer = $this;

$installer->startSetup();
$installer->run("ALTER TABLE `{$this->getTable('livechat_sessions')}` ADD `customer_url` VARCHAR( 255 ) NOT NULL ;");
$installer->endSetup();

