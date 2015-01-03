<?php
$installer = $this;

$installer->startSetup();
$installer->run("ALTER TABLE `{$this->getTable('livechat_operators')}` ADD `store_allowed` VARCHAR( 255 ) NOT NULL;");
$installer->endSetup();
