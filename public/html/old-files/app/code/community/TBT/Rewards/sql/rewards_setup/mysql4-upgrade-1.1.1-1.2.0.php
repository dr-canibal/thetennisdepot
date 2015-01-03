<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('catalogrule')} ADD (
    `points_uses_per_product` INT(11) DEFAULT '1'
);

ALTER TABLE {$this->getTable('rewards_currency')} ADD (
  `text_offset_x` int(11),
  `text_offset_y` int(11)
);
");
$installer->endSetup(); 
?>