<?php
$installer = $this;

$installer->startSetup();
$installer->run("ALTER TABLE `{$this->getTable('livechat_messages')}` ADD `is_customer_message` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `autor_name`;");
$installer->endSetup();
