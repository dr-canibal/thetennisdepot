<?php
abstract class TBT_Rewards_Model_Special_Action_Abstract extends Varien_Model {
    protected $needs_approval = false;
    
    public function _construct() {
        $this->setCaption("unnamed special points rule action");
        $this->setDescription("unnamed special points rule action...");
        return parent::_construct();
    }

    public function givePoints(&$customer);
    public function revokePoints(&$customer);
    public function holdPoints(&$customer);
    public function cancelPoints(&$customer);
    public function approvePoints(&$customer);
    

}