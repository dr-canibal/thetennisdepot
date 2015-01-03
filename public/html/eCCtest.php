<?php
/*
© Copyright Webgility LLC 2007
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 File last updated: 5/06/2008 (5/May/2008)
 ## for zen cart service file 
*/
error_reporting(E_ALL && ~E_NOTICE && '~E_STRICT');
//require 'app/Mage.php';
//$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'local';
//$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'community';
//$paths = BP . DS . 'app' . DS . 'code' . DS . 'core';
//$paths[] = BP . DS . 'lib';

//$app_path = implode(PS, $paths);

//set_include_path($app_path . PS . Mage::registry('original_include_path'));
//echo $app_path . PS . Mage::registry('original_include_path');
//exit;


require "app/code/core/Mage/Webgility/Model/Ecc/lib/nusoap.php";
require "app/code/core/Mage/Webgility/Model/Ecc/eCCxml.php";
//$s= new soapclient_wg($wsdl,false, false, false, false, false, 120, 120);
//exit;
$response_result =0;
$geterr="";
$status_code="";

//include_once "Varien/Profiler.php";
$functions = array(	"Get Payment Methods"=>'getPaymentMethods',
					"Get Shipping Methods"=>'getShippingMethods',
					"Get Taxes"=>'getTaxes',
					"Get Categories"=>'getCategory',
					"Get Manufacturers"=>"getManufacturers");
					

if (isset($_GET['action']))
{
	switch ($_GET['action'])
	{
		case "phpinfo":
		 phpinfo();
		 break;
	}
die();
}


if (isset($_POST["btncheck"]) && ($_POST["btncheck"]=="Check" || $_POST["btncheck"]=="Mail To Webgility"))
{

	if (trim($_POST["uname"])!="" && trim($_POST["pwd"])!="")
	{
	$username=trim($_POST["uname"]);
		$password=trim($_POST["pwd"]); 	
	if(isset($_POST['sendmail']) && $_POST['sendmail']=='Y')
	{
	
	$subject="eCCtest Information(Magento)";
	$mailto = "support@webgility.com";
	$mailfrom = "bugs@webgility.com";
	$headers = "From:".$mailfrom."\r\nContent-Type: text/html; charset=iso-8859-1";
	$body = '<html>
<head>
 <title>eCC test Report</title>
</head>
<table width=644 border=0 cellspacing=2 cellpadding=0 align=center>
  <tr> 
    <td width=266 height=30  > <p >StoreType:</p></td>
    <td width=372 height=30 ><b>Magento</b></td>
  </tr>
  <tr> 
    <td  height=30 >eCC test URL:</td>
    <td height=30 ><b> '. $_SERVER['HTTP_REFERER'] .'</b></td>
  </tr>
   <tr bgcolor=#E2E6E9> 
    <td height=25 colspan=2 align=center bgcolor=#E2E6E9 class=Text8PTGreyColor> 
      <div align=center> 
        <p>Copyright &copy; 2008-2009 Webgility LLC</p>
      </div></td>
  </tr>
  <tr> 
    <td colspan=2> </td>
  </tr>
</table>

</body>
</html>
';

@mail($mailto, $subject, $body, $headers);

	}
		//require_once("lib/nusoap.php");
		//require_once('eCCxml.php');
		ini_set("max_execution_time",5000);
		$wsdl= str_replace("eCCtest.php","eCCmagentoservice.php",$_SERVER['HTTP_REFERER']); 
//		echo $wsdl;
		
		$s= new nusoap_client($wsdl,false);
		//echo $wsdl;
		$param=array($username,$password);
		//print_r($param);
		$result=$s->call('checkAccessInfo',$param);	
		//print_r($_POST['functionname']);

		# Check for a fault
		//header("Content-type: application/xml");
	//	header("Content-type: text/html");
		
		if ($s->fault) {
			echo '<h2>Fault</h2><pre>';
			print_r($result);
			echo '</pre>'; 
			die();
		}
		else
		{		
			if(!$err=$s->getError())
			{				
				$xmlResponse = new xml_doc();
				$xmlResponse->version='1.0';
				$xmlResponse->encoding='ISO-8859-1';
				$xmlRequest = new xml_doc($result);
				$xmlRequest->parse();	
				$xmlRequest->getTag(0, $_tagName, $_tagAttributes, $_tagContents, $_tagTags);
				if (strtoupper(trim($_tagName)) != 'RESPONSE' || count($_tagTags) == 0 ) 
				{
					echo "RESPONSE Tag not found";			
				}
				else
				{
				//echo "HELLO";
					$response_result=1;
					$xmlRequest->getTag(1, $_tagName, $_tagAttributes, $status_code, $_tempTags);
					$xmlRequest->getTag(2, $_tagName, $_tagAttributes, $msg, $_tempTags);
					//print_r($xmlRequest->xml);

					//echo $msg;
					$companyInfoResult=$s->call('getCompanyInfo',$param);	
					## Access company info
					if ($s->fault) 
					{
						echo '<h2>Fault</h2><pre>';
						print_r($result);
						echo '</pre>'; 
						die();
					}
					else
					{		
						if(!$err=$s->getError())
						{
						$xmlRequest = new xml_doc($companyInfoResult);	
						$xmlRequest->parse();	
					//	$xmlRequest->getTag(0, $_tagName, $_tagAttributes, $_tagContents, $_cmpTags);
						$companyinfoFlag=1;
						$ordersTag = $xmlRequest->getChildByName(0, "COMPANY");
						$xmlRequest->getTag($ordersTag, $_tagName, $_tagAttributes, $_tagContents, $_tagTags);
						$xmlRequest->getTag(4, $_tagName, $_tagAttributes, $storeid, $_tempTags);
						$xmlRequest->getTag(5, $_tagName, $_tagAttributes, $storename, $_tempTags);
						$xmlRequest->getTag(6, $_tagName, $_tagAttributes, $address, $_tempTags);
						$xmlRequest->getTag(7, $_tagName, $_tagAttributes, $city, $_tempTags);
						$xmlRequest->getTag(8, $_tagName, $_tagAttributes, $state, $_tempTags);
						$xmlRequest->getTag(9, $_tagName, $_tagAttributes, $country, $_tempTags);
						$xmlRequest->getTag(10, $_tagName, $_tagAttributes, $zip, $_tempTags);
						$xmlRequest->getTag(11, $_tagName, $_tagAttributes, $phone, $_tempTags);
						$xmlRequest->getTag(12, $_tagName, $_tagAttributes, $fax, $_tempTags);
						$xmlRequest->getTag(13, $_tagName, $_tagAttributes, $website, $_tempTags);
						
						}	
					}
					## Access company info
				}	
			}
			else
			{
			$error = $err;
			$esponseerror = htmlspecialchars($s->response, ENT_QUOTES);
			$error;
			$geterr =1;
//			header("Content-type: text/html");
			//	echo '<h2>Response</h2><pre>' . htmlspecialchars($s->response, ENT_QUOTES) . '</pre>';
		//		echo "Error :".$err;
			//	die();
			}
		}
	}
	else
	{
	
		echo "<br><center><strong>Please enter username and password!</strong> </center>";
	}	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Webgility Test Page</title>
<link href="http://www.webgility.com/stylesheet.css" type="text/css" rel="stylesheet" />
<link href="http://www.webgility.com/dropdown.css" type="text/css" rel="stylesheet" />
<script language="javascript1.2" type="text/javascript">
function checklogin()
{
if (document.frmlogin.uname.value=="" || document.frmlogin.pwd.value=="")
{
	alert("Please enter username and password");
	return false;
}
return true;
}
</script>
</head>
<body>
<div id="hasLayout">

  <div class="header-stripe">&nbsp;</div>
  <div class="header">
    <ul id="menu">
      <li class="homeLink"><a href="#"><img src="http://www.webgility.com/images/spacer.gif" height="38" border="0"  /></a></li>
    </ul>
    <div class="logo-align"><a href="http://www.webgility.com/index.php"><img src="http://www.webgility.com/images/webgility-logo-white-greybg.jpg" alt="webgility logo quickbooks integration" border="0" /></a></div>
  </div>
  <div class="header-top-corner">
    <div class="content">
      <div>&nbsp;</div>
      <!-- Sub Nav-->
      <div id="subNavRight">
        <div id="subNavRepeat">
          <ul id="subNav">
            <li class="firstCurrent"><a href="#" >eCC Test Page</a></li>
          </ul>
        </div>
      </div>
      
      <!--User Name & Password -->
      <p>Please enter online store's admin panel username and password</p>
	  <form action="eCCtest.php" method="post" name="frmlogin" onSubmit="return checklogin()">
      <table cellpadding="2" cellspacing="0" border="0" width="98%" align="center">
        <tr>
          <td width="7%">Username</td>
          <td width="15%"><input type="text" name="uname"/>
          </td>
          <td width="7%">Password</td>
          <td width="17%"><input type="password" name="pwd"/></td>
          <td width="54%"><input type="submit" value="Check" name="btncheck" /></td>
        </tr>
      </table>
	  </form>
	  
	  <?php   if($response_result==1) {
	
	  //echo "YES";
ob_start();
phpinfo();
$phpinfo = array('phpinfo' => array());
if(preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER))
    foreach($matches as $match)
        if(strlen($match[1]))
            $phpinfo[$match[1]] = array();
        elseif(isset($match[3]))
            $phpinfo[end(array_keys($phpinfo))][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
        else
            $phpinfo[end(array_keys($phpinfo))][] = $match[2];
			
			
		//	print_r($phpinfo['PHP Variables']);
//			exit;
?>
      
      <!-- Result Desk-->
      <h2>Result Desk:</h2>
      <table width="98%" align="center" cellpadding="2" cellspacing="0" border="0">
        <tr>
          <td width="357" valign="top" >Status Code</td>
          <td width="595"><?php echo $status_code; ?></td>
        </tr>
        <tr>
          <td valign="top">Message</td>
          <td><?php echo $msg; ?></td>
        </tr>
  <?php  if ($companyinfoFlag==1)  { ?>		
	
        <tr>
          <td valign="top">Store Name</td>
          <td><?php echo $storename; ?></td>
        </tr>
        <tr>
          <td valign="top" nowrap="nowrap">Store Address</td>
          <td><?php echo $address."<br>".$city. $state."<br>". $country."&nbsp;". $zip ; ?></td>
        </tr>
        <tr>
          <td valign="top">Phone</td>
          <td><?php echo $phone; ?></td>
        </tr>
        <tr>
          <td valign="top">website</td>
          <td><?php echo $website; ?></td>
        </tr>
		<?php 	if($status_code==0)	{?>
        <tr>
          <td valign="top">System</td>
          <td><?php echo "{$phpinfo['phpinfo']['System']}<br />\n"; ?></td>
        </tr>
        <tr>
          <td valign="top">Server Software</td>
          <td><?php echo $_SERVER['SERVER_SOFTWARE'];?></td>
        </tr>
        <tr>
          <td valign="top">PHP Version</td>
          <td><?php echo phpversion();?></td>
        </tr>
		 <?php if(extension_loaded('mysql'))
{
?>
        <tr>
          <td valign="top">mysql Version</td>
          <td><?php echo "{$phpinfo['mysql']['Client API version']}";?></td>
        </tr>
	<?php }
	else
{
?>
        <tr>
          <td valign="top">mysql Version</td>
          <td>Not Found</td>
        </tr>
        <?php
}
?>
	
        <tr>
          <td colspan="2" style="padding-top:10px;"><form action="eCCtest.php" method="post" name="frmlogin">
		<input type="hidden" name="pwd" value="<?php echo $password ;?>"/>
		<input type="hidden" name="uname" value="<?php echo $username;?>"/>
		<input type="hidden" name="sendmail" value="Y"/>
		<input type="submit" value="Mail To Webgility" name="btncheck" />
		</form></td>
        </tr>
      </table>
      
      <!-- Required Extension -->
      <h2>Required Extensions:</h2>
<!--      <div  <?php /* if(extension_loaded("mcrypt")){ ?> class="found" <?php } else {?>class="notfound" <?php }?>>PHP mcrypt</div>
      <div <?php if(extension_loaded("pcre")){ ?> class="found" <?php } else {?>class="notfound" <?php }?>>PHP PCRE</div>
      <div <?php if(extension_loaded("zlib")){ ?> class="found" <?php } else {?>class="notfound" <?php }?>>PHP Zlib</div>
      <div <?php if(extension_loaded("curl")){ ?> class="found" <?php } else {?>class="notfound" <?php }*/?>>PHP CURL</div>-->
      <div <?php if(extension_loaded("curl")){ ?> class="found" <?php } else {?>class="notfound" <?php }?>>PHP CURL</div>
	  <div <?php if(extension_loaded("gd")){ ?> class="found" <?php } else {?>class="notfound" <?php }?>>PHP GD</div>
     <!-- <div <?php if(extension_loaded("soap")){ ?> class="found" <?php } else {?>class="notfound" <?php }?>>PHP Soap</div> -->

      <h2>Required Settings:</h2>
    <?php /*?>  <div  <?php  if(ini_get('register_globals')){ ?> class="found" <?php } else {?>class="notfound" <?php }?>>register_globals</div><?php */?>
      <!--<div <?php /*if(extension_loaded("pcre")){ ?> class="found" <?php } else {?>class="notfound" <?php }?>>PHP PCRE</div>
      <div <?php if(extension_loaded("zlib")){ ?> class="found" <?php } else {?>class="notfound" <?php }?>>PHP Zlib</div>
      <div <?php if(extension_loaded("curl")){ ?> class="found" <?php } else {?>class="notfound" <?php }*/?>>PHP CURL</div>-->
  <?php /*?>    <div <?php if(ini_get('magic_quotes_gpc')){ ?> class="found" <?php } else {?>class="notfound" <?php }?>>magic_quotes</div><?php */?>
      <div <?php if(!ini_get('safe_mode')){ ?> class="found" <?php } else {?>class="notfound" <?php }?>>Safe Mode Off</div>
	  
	  
  <h2>Needed Information:</h2>
  <div class="information"> Memory Limit <?php echo "(" . ini_get("memory_limit") .")";?></div>	  
	 
      

      
      <!-- Functions check: -->
      <h2>Functions Check:</h2>
	            <?php  
		$i=0;
		foreach($functions as $key=>$val)
		{
		if($val=='getItems')
		{
		$param = array($username,$password,0,10);
		}
		else
		{
		$param = array($username,$password);
		}
		
		$result=$s->call($val,$param);	
		 getrecord($s,$result,$i,$key);	   ?>
        <!--<tr>
          <td valign="top"><?php // echo $key ?></td>
          <td></td>
        </tr>-->
        <?php
  $i++;
  }
  
  ?>

	  <!--<div class="found">Get Items </div>
      <div class="found">Get Payment Methods</div>
      <div class="found">Get Shipping Methods</div>
      <div class="found">Get Taxes</div>
      <div class="found">Get Categories</div>
      <div class="notfound">Get Manufacturers</div>
    </div>-->
	 <?php }
	 }}
	 elseif($geterr==1){ //echo "YES"; ?>
     <h2>Result Desk:</h2>
	<table width="98%" align="center" cellpadding="2" cellspacing="0" border="0">
        <tr>
          <td width="18%" valign="top" >Error:</td>
          <td width="82%"><?php echo $err;?></td>
        </tr>
        <tr>
          <td valign="top">Error Message</td>
          <td><textarea cols="50" rows="10" style="height:auto" ><?php echo htmlspecialchars($s->response, ENT_QUOTES);  ?></textarea></td>
        </tr>
      </table>
  <?php }?>
   <?php  if($status_code=="0") { ?>
  <table cellpadding="0" cellspacing="0" width="98%" align="center">
  
    <tr>
      <td align="left" width="33%">&nbsp;&nbsp;</td>
    </tr>
  
    <tr>
      <td align="left" width="33%"><a href="http://www.webgility.com" target="_blank">www.webgility.com</a></td>
      <td align="center" width="33%"><a href="mailto:support@webgility.com">email:support@webgility.com</a></td>
      <td align="right" width="33%"><a href="eCCtest.php?action=phpinfo">Php Info</a> </td>
    </tr>
  </table>
  <?php }?>

  </div>
  <div class="header-bottom-corner" style="background-position:bottom;">&nbsp;</div>
</div>
</body>
</html>
<?php
function getrecord($s,$result,$i,$name)
{
		if ($s->fault) {
			echo '<div><h2>Fault</h2><pre>';
			print_r($result);
			echo '</pre></div>'; 
			//die();
		}
		else
		{		
			if(!$err=$s->getError())
			{				
				$xmlResponse = new xml_doc();
				$xmlResponse->version='1.0';
				$xmlResponse->encoding='ISO-8859-1';
				$xmlRequest = new xml_doc($result);
				$xmlRequest->parse();	
				$xmlRequest->getTag(0, $_tagName, $_tagAttributes, $_tagContents, $_tagTags);
				if (strtoupper(trim($_tagName)) != 'RESPONSE' || count($_tagTags) == 0 ) 
				{
					echo "<div class='notfound'>RESPONSE Tag not found</div>";			
				}
				else
				{
					$response_result=1;
					$xmlRequest->getTag(1, $_tagName, $_tagAttributes, $status_code, $_tempTags);
//					$xmlRequest->getTag(2, $_tagName, $_tagAttributes, $msg, $_tempTags);
					$xmlRequest->getTag(3, $_tagName, $_tagAttributes, $msg, $_tempTags);					
					echo "<div class='found'>". $name . "</div>";
					
				}	
			}
			else
			{
				echo "<div class='notfound'>".$name." <font color='red'><b>Error</b></font> :".$err . "&nbsp;&nbsp;	<a href='javascript:void(0);' onclick=\"javascript:showdiv('div_$i');\">Show Error</a>&nbsp;&nbsp;<a href=\"javascript:hidediv('div_$i');\">Hide Error</a></div>";
				echo '<div id="div_'.$i.'" style="display:none;margin-left:20px"><textarea cols="50" rows="15"> <pre>' . htmlspecialchars($s->response, ENT_QUOTES) . '</pre></textarea></div>';				
//				die();
			}
}
}
?>
<script language="javascript">
function showdiv(div)
{
//alert(div);
document.getElementById(div).style.display = ''; 
}
function hidediv(div)
{
document.getElementById(div).style.display = 'none'; 
//document.getElementById('hideshow')
}

</script>

