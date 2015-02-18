<?php
class Magentothem_Catslider_Helper_Data extends Mage_Core_Helper_Abstract
{

    // const PATH_SLIDER  = 'catslider/catslider_config';
    // const PATH_PRODUCT = 'catslider/product_show';

    public function getSliderCfg($cfg) 
    {
        $config = Mage::getStoreConfig('catslider/catslider_config');
        if(isset($config[$cfg])) return $config[$cfg];
        //else throw new Exception($cfg.' value not set');
    }

    public function getProductCfg($cfg)
    {
        $config =  Mage::getStoreConfig('catslider/product_show');
        if(isset($config[$cfg])) return $config[$cfg];
        //else throw new Exception($cfg.' value not set');
    }

}