<?php

/**
 * Helper Data
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function log($msg) {
        Mage::log($msg, null, "rewards_referral.log");
    }
}
