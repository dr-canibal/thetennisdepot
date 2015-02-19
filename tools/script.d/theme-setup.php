<?php
require_once dirname(dirname(dirname(__FILE__))) . '/public/app/Mage.php';

Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

set_time_limit(0);
ini_set('memory_limit', '1024M');

Mage::init();

// enable error output
ini_set('display_errors', '1');
error_reporting(E_ALL);

