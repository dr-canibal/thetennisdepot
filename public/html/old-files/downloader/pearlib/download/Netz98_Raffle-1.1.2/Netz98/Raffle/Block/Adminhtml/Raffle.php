<?php
class Netz98_Raffle_Block_Adminhtml_Raffle extends Mage_Adminhtml_Block_Template
{
    /**
	 * Constructor
	 *
	 * Initializes block
	 */
    public function __construct()
    {
        $this->setTemplate('raffle/participants/list.phtml');
    }
  
	/**
	 * Prepares block to render
	 *
	 * @return Netz98_Raffle_Block_Adminhtml_Raffle_Grid
	 */
	protected function _beforeToHtml()
	{
		$this->setChild('grid', $this->getLayout()->createBlock('raffle/adminhtml_raffle_grid', 'grid'));
		return parent::_beforeToHtml();
	}
}