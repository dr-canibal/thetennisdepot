<?php
class Velite_Teaserbox_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()
        	->getBlock('content')->append(
        	    $this->getLayout()->createBlock('teaserbox/index', 'teaserbox_box', array('template' => 'teaserbox/box.phtml'))
        );
        $this->renderLayout();
    }
}