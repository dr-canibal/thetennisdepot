<?php
/**
 * Referral Model
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
abstract class TBT_RewardsReferral_Model_Referral_Abstract extends TBT_RewardsReferral_Model_Referral
{
    
    public abstract function getReferralStatusId();
    public abstract function getTransferReasonId();
    public abstract function getTotalReferralPoints();
    public abstract function getReferralTransferMessage($newCustomer);
    
    
    //@nelkaake Added on Wednesday May 5, 2010: If $always_save is true the system will always save the referral model
    public function trigger($newCustomer, $always_save=false) {
        $this->loadByEmail($newCustomer->getEmail());
        $points = $this->getTotalReferralPoints();
        try {
            if(!$points->isEmpty() || $always_save) {
                $this->setReferralStatus($this->getReferralStatusId());
                $this->setReferralChildId($newCustomer->getId());
                $this->save();
            }
            if(!$points->isEmpty()) {
                foreach($points->getPoints() as $cid => $points_amount) {
                    $t = Mage::getModel('rewardsref/transfer')->create(
                        $points_amount,
                        $cid,
                        $this->getReferralParentId(),
                        $newCustomer->getId(),
                        $this->getReferralTransferMessage($newCustomer),
                        $this->getTransferReasonId()
                    );
                }
            }
            
        } catch(Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }
    
    public function getAccumulatedPoints($referralobj) {
        $col = Mage::getModel('rewardsref/transfer')
            ->getCollection()
            ->addFieldToFilter('reference_id', $referralobj->getReferralChildId())
            ->addFieldToFilter('reference_type', TBT_RewardsReferral_Model_Transfer::REFERENCE_REFERRAL)
            ->addFieldToFilter('customer_id', $referralobj->getReferralParentId())
			->selectOnlyPosTransfers()
			->sumPoints();
		$points_earned = Mage::getModel('rewards/points');		
		foreach($col as $points){
			$points_earned->add($points->getCurrencyId(), (int)$points->getPointsCount());
		}	
		return $points_earned;
    }

    public function hasReferralPoints() {
        return !$this->getTotalReferralPoints()->isEmpty();
    }



}