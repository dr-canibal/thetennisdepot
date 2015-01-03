<?php

$installer = $this;

$installer->startSetup();

$installer->run("CREATE TABLE IF NOT EXISTS `{$this->getTable('livechat_messages')}` (
  `id` int(10) NOT NULL auto_increment,
  `livechat_session_id` int(10) NOT NULL,
  `autor_name` varchar(32) NOT NULL,
  `message` text NOT NULL,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `livechat_session_id` (`livechat_session_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$this->getTable('livechat_operators')}` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
  `is_online` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `{$this->getTable('livechat_sessions')}` (
  `id` int(10) NOT NULL auto_increment,
  `customer_name` varchar(32) NOT NULL,
  `date_started` datetime NOT NULL,
  `customer_session_id` varchar(255) NOT NULL,
  `dispatched` int(10) NOT NULL default '0',
  `store_id` int(10) NOT NULL,
  `order_placed` varchar(25) NOT NULL,
  `close` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `customer_session_id` (`customer_session_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
			");
$installer->endSetup();