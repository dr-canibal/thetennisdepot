<?php
/*-----------------------------------------------------------------------------+
| MagneticOne                                                                  |
| Copyright (c) 2008 MagneticOne.com <contact@magneticone.com>                 |
| All rights reserved                                                          |
+------------------------------------------------------------------------------+
| PLEASE READ  THE FULL TEXT OF SOFTWARE LICENSE AGREEMENT IN THE "license.txt"|
| FILE PROVIDED WITH THIS DISTRIBUTION. THE AGREEMENT TEXT IS ALSO AVAILABLE   |
| AT THE FOLLOWING URL: http://www.magneticone.com/store/license.php           |
|                                                                              |
| THIS  AGREEMENT  EXPRESSES  THE  TERMS  AND CONDITIONS ON WHICH YOU MAY USE  |
| THIS SOFTWARE   PROGRAM   AND  ASSOCIATED  DOCUMENTATION   THAT  MAGNETICONE |
| (hereinafter  referred to as "THE AUTHOR") IS FURNISHING  OR MAKING          |
| AVAILABLE TO YOU WITH  THIS  AGREEMENT  (COLLECTIVELY,  THE  "SOFTWARE").    |
| PLEASE   REVIEW   THE  TERMS  AND   CONDITIONS  OF  THIS  LICENSE AGREEMENT  |
| CAREFULLY   BEFORE   INSTALLING   OR  USING  THE  SOFTWARE.  BY INSTALLING,  |
| COPYING   OR   OTHERWISE   USING   THE   SOFTWARE,  YOU  AND  YOUR  COMPANY  |
| (COLLECTIVELY,  "YOU")  ARE  ACCEPTING  AND AGREEING  TO  THE TERMS OF THIS  |
| LICENSE   AGREEMENT.   IF  YOU    ARE  NOT  WILLING   TO  BE  BOUND BY THIS  |
| AGREEMENT, DO  NOT INSTALL OR USE THE SOFTWARE.  VARIOUS   COPYRIGHTS   AND  |
| OTHER   INTELLECTUAL   PROPERTY   RIGHTS    PROTECT   THE   SOFTWARE.  THIS  |
| AGREEMENT IS A LICENSE AGREEMENT THAT GIVES  YOU  LIMITED  RIGHTS   TO  USE  |
| THE  SOFTWARE   AND  NOT  AN  AGREEMENT  FOR SALE OR FOR  TRANSFER OF TITLE. |
| THE AUTHOR RETAINS ALL RIGHTS NOT EXPRESSLY GRANTED BY THIS AGREEMENT.       |
|                                                                              |
| The Developer of the Code is MagneticOne,                                    |
| Copyright (C) 2006 - 2008 All Rights Reserved.                               |
+-----------------------------------------------------------------------------*/

#
# $Id: savefile_action.class.php 245 2009-05-28 17:23:40Z peter $
#

class M1_C2c_Bridge_Action_Savefile
{
	function perform($bridge) {
  	$source = urldecode($_GET['src']);
    $source = str_replace(" ", "%20", $source);
    $destination = urldecode($_GET['dst']);

    if(basename($destination) == ""  || basename($source) == "") {
      echo "[BRDIGE ERROR] Destination or source is empty!";
      return;
    }
    
    if( $this->_create_dir(dirname($destination)) !== false ) {
      $destination = M1_C2C_STORE_BASE_DIR . $destination;
      
      $ch = curl_init();    
	    curl_setopt($ch, CURLOPT_URL, $source);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
	    curl_setopt($ch, CURLOPT_NOBODY, true);
	    curl_exec($ch);
	    $httpResponseCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    
	    if($httpResponseCode != 200) {
	     	curl_close($ch);
        echo "[BRIDGE ERROR] Bad response received from source, HTTP code $httpResponseCode!";
	     	return;
	    }
	    
	    $dst = fopen($destination, "wb");
	    curl_setopt($ch, CURLOPT_FILE, $dst);
	    curl_setopt($ch, CURLOPT_HTTPGET, true);
      curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
      curl_exec($ch);
      if( ($error_no = curl_errno($ch)) != CURLE_OK ) {
        echo "[BRIDGE ERROR] $error_no: " . curl_error($ch);
      }
      curl_close($ch);

      @chmod($destination, 0777);
    } else {
      echo "[BRIDGE ERROR] Deirectory creation failed!";
    }
	}
		
	function _create_dir($dir) {
		$dir_parts = explode("/", $dir);
		$path = M1_C2C_STORE_BASE_DIR;
    foreach ($dir_parts as $item) {
    	$path .= $item . DIRECTORY_SEPARATOR;
    	if(!is_dir($path)) {
    		$res = @mkdir($path);
    		if(!$res) {
    			return false;
    		}
    	}
    	@chmod($path, 0777);
    }
		return true;
	}
}