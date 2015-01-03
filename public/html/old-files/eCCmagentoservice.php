<?php
/*
 © Copyright Webgility LLC 2007
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 File last updated: 1/08/2008 (1/Augest/2008)  
 
*/

require 'app/Mage.php';
try 
{
   Mage::setIsDeveloperMode(true);
   $app = Mage::app('default');
   $webgility = Mage::getSingleton('webgility/ecc_run');
   $webgility->genrateWSDL();
   
}
 catch (Exception $e) 
{
    Mage::printException($e);
}

?>