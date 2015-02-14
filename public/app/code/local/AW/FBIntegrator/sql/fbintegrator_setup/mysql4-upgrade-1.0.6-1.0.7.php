<?php
function getStoreConfig($path){

    $coll = Mage::getModel('core/config_data')->getCollection();
    $coll->getSelect()->where('path=?',$path);

    foreach($coll as $i){
       return $i->getValue();
    }
}
    $text = getStoreConfig('fbintegrator/facebook/post_link_text');
    $link = getStoreConfig('fbintegrator/facebook/post_link');
    $template = $text.$link;

    $installer = $this;
	$installer->startSetup();

    $template = $text.$link;

    if($template != ''){
        $upData = array(
            'scope'     => 'default',
            'scope_id'  => '0',
            'path'      => 'fbintegrator/wall/post_link_template',
            'value'     => $template,
        );

        $configData = Mage::getModel('core/config_data')->load('fbintegrator/wall/post_link_template','path');
        if($configData->getConfigId())
            $configData->setValue($template);
        else
            $configData->setData($upData);
        $configData->save();
    }

	$installer->endSetup();