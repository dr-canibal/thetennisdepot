<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('fbintegrator/users')} (
      `id` bigint(15) unsigned NOT NULL auto_increment,
      `fb_id` bigint(15) unsigned NOT NULL,
      `fb_email` varchar(250) NOT NULL,
      `customer_id` bigint(15) unsigned NOT NULL,
      `website_id` smallint(5) unsigned NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `fb_id` (`fb_id`,`website_id`),
      UNIQUE KEY `fb_email` (`fb_email`,`website_id`),
      UNIQUE KEY `customer_id` (`customer_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
