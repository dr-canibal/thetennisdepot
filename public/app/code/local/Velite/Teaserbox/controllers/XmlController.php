<?php
class Velite_Teaserbox_XmlController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
        $this->loadLayout();
		$this->getLayout()->setBlock(
		    'root',
            $this->getLayout()->createBlock('teaserbox/xml')
        );
        $this->renderLayout();
    }
}