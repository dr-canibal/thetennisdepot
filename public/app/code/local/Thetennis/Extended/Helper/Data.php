<?php

class Thetennis_Extended_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function cleanNum($num)
    {
        return trim(trim($num, '0'), '.');
    }
}