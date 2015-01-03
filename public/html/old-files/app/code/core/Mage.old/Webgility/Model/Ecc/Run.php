<?php
//require_once("lib/nusoap.php");
class Mage_Webgility_Model_Ecc_Run extends Mage_Webgility_Model_Ecc_Abstract
  {
    /**
     * Available options
     *
     * @var array
     */
    protected $__ENCODE_RESPONSE = true;
    protected $_options;
    protected $STORE_NAME= 'NOT_FOUND_STORE_NAME';
    protected $STORE_ID =null;
    protected $RequestOrders = array();

    const STATE_NEW        = 'new';
    const STATE_PROCESSING = 'processing';
    const STATE_COMPLETE   = 'complete';
    const STATE_CLOSED     = 'closed';
    const STATE_CANCELED   = 'canceled';
    const STATE_HOLDED     = 'holded';
    const STATE_UNHOLDED   = 'unholded';
    protected   $types = array('AE'=>'Amex', 'VI'=>'Visa', 'MC'=>'MasterCard', 'DI'=>'Discover','OT'=>'Other',''=>'');
    protected   $carriers = array('dhl'=>'DHL',
                                  'fedex'=>'FedEx',
                                  'ups'=>'UPS',
                                  'usps'=>'USPS',
                                  'freeshipping'=>"Free Shipping" ,
                                  'flatrate'=>"Flat Rate",
                                  'tablerate'=>"Best Way");
    protected   $carriers_ =array('DHL'=>'dhl',
                                  'FEDEX'=>'fedex',
                                  'UPS'=>'ups',
                                  'USPS'=>'usps',
                                  "FREE SHIPPING" =>'freeshipping',
                                  'FLAT RATE'=>"flatrate",
                                  "BEST WAY" =>'tablerate');
    protected   $PayMethodsCC = array( 'paypal_express'      ,
                                       'paypal_standard'     ,
                                       'paypal_direct'       ,
                                       'paypaluk_express'    ,
                                       'paypaluk_direct'     ,
                                       'ccsave'              ,
                                       'authorizenet'        ,
                                       'payflow_pro'         );
    protected   $PayMethods = array(   'paypal_express'      => 'PayPal Express',
                                       'paypal_standard'     => 'PayPal Standard',
                                       'paypal_direct'       => 'Paypal Direct',

                                       'paypaluk_express'    => 'PaypalUk Express',
                                       'paypaluk_direct'     => 'PaypalUk Direct',
                                       'ccsave'              => 'Credit Card (saved)',
                                       'checkmo'             => 'Check / Money order',
                                       'free'                => 'No Payment Information Required',
                                       'purchaseorder'       => 'Purchase Order' ,
                                       'authorizenet'        => 'Credit Card (Authorize.net)',
                                       'payflow_pro'         => 'Credit Card (Payflow Pro)'
                                      );

    protected $status_list = array(
                      self::STATE_PROCESSING,
                      self::STATE_COMPLETE,
                      self::STATE_CLOSED,
                      self::STATE_CANCELED,
                      self::STATE_HOLDED,
                      self::STATE_UNHOLDED
                      );

    /**
     * Script arguments
     *
     * @var array
     */
    protected $_orders = null;
    protected $xmlRequest = null;
    protected $xmlResponse = null;
    protected $root = null;
    protected $envelope = null;
    protected $_current_order = null;
    protected $send_email = false;
    protected $Msg = array();
    protected $result = '';
    protected $RequestParams = array();
    protected $filters = array();
    protected $QB_NUMBER_OF_DAYS,$QB_ORDER_START_NUMBER,$QB_PROVIDER;
    protected $_tagName = array(),  $_tagAttributes= array(),$_tagContents=array(),$_tagTags= array();
    /**
     * Installer data model to store data between installations steps
     *
     * @var Mage_Install_Model_Installer_Data|Mage_Install_Model_Session
     */
  //  protected $_dataModel;

    /**
     * Current application
     *
     * @var Mage_Core_Model_App
     */
   // protected $_app;

    public function Get__ENCODE_RESPONSE (){
    	return $this->__ENCODE_RESPONSE;
    }
    public function Set__ENCODE_RESPONSE ($fl = false){
    	$this->__ENCODE_RESPONSE= $fl;
    }

    /**
     * Run
     *
     * @return boolean
     */
    public function run()
    {}

    public function LoadXml($string){
          $this->xmlRequest = Mage::getModel('webgility/ecc_eccxml');
          $this->xmlRequest->loadString($string);
          $this->xmlRequest->parse();
          return true;
    }

    public  function CheckUser($username,$password)
	{
       try
	   {
	   
		   // return true;
		   $user = Mage::getSingleton('admin/user');
		   $userRole= Mage::getSingleton('admin/mysql4_acl_role');
		   //$userRole = Mage::getSingleton('admin/role');
		   if ($user->authenticate($username, $password)) 
		   {
			  $loadRole=$userRole->load($user->getRoles($user));
			  return 0;
		   }
		   else
		   {
		   	   $details =  $user->loadByUsername($username);
			   if($details->user_id >0)
			   return 2;
			   else
			   return 1;
			   exit;
		   }
       } catch (Exception $e) 
	   {
	   		  return 1;				
              exit;
       }


    }

   public  function SetParametersFilter(){
      if (isset($this->RequestParams['STATUS'])) {
         $this->filters['QB_STATUS']= $this->RequestParams['STATUS'];
      } else{
         $this->filters['QB_STATUS']= "ALL";
       }
      if (isset($this->RequestParams['PROVIDER'])) {
         $this->QB_PROVIDER= $this->RequestParams['PROVIDER'];
      } else{
         $this->QB_PROVIDER= "";
       }

      if (isset($this->RequestParams['SECURITYKEY'])&&$this->RequestParams['SECURITYKEY']!=""){
      $this->filters['PROVIDER']=" and o.vendor_id=v.vendor_id AND v.vendor_name='".$this->			RequestParams['SECURITYKEY']."' ";
      }else{
         $this->filters['PROVIDER']=" and o.vendor_id=v.vendor_id ";
      }
	   if (isset($this->RequestParams['LIMITORDERCOUNT'])&&(int)$this->RequestParams['LIMITORDERCOUNT']!=0){
         $this->filters['QB_ORDERS_PER_RESPONSE']=(string)$this->RequestParams['LIMITORDERCOUNT'];
      }else{
         $this->filters['QB_ORDERS_PER_RESPONSE']='25';
      }
       $filter_order_start_number="";
      if (isset($this->RequestParams['ORDERSTARTNUMBER'])){
      	$this->QB_ORDER_START_NUMBER = (int)$this->RequestParams['ORDERSTARTNUMBER'];
        if($this->QB_ORDER_START_NUMBER>0){
            $this->filters['QB_ORDER_START_NUMBER']=array("gteq"=>(int)$this->QB_ORDER_START_NUMBER);
            $this->filters['QB_ORDER_CREATE_DATE']=array("nin"=>date("Y-m-d",mktime(0,0,0,0,0,0)));
        }else{
         	$this->QB_ORDER_START_NUMBER = 0;
        }
      }else{
         	$this->QB_ORDER_START_NUMBER = 0;
      }
      if((int)$this->QB_ORDER_START_NUMBER == 0){
       	if (isset($this->RequestParams['NUMBEROFDAYS'])){
            $this->QB_NUMBER_OF_DAYS =(int) $this->RequestParams['NUMBEROFDAYS'];
            $this->filters['QB_ORDER_CREATE_DATE']=array("from"=>date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$this->QB_NUMBER_OF_DAYS+1,date("Y"))));
            $this->filters['QB_ORDER_START_NUMBER']=array("nin"=>"0");
         }else{
            $this->filters['QB_ORDER_CREATE_DATE']=array("nin"=>date("Y-m-d",mktime(0,0,0,0,0,0)));
            $this->filters['QB_ORDER_START_NUMBER']=array("gteq"=>(int)$this->QB_ORDER_START_NUMBER);
         }
      }
      return true;
   }


      public  function  CreateHeaderXml(){
         $this->xmlResponse = Mage::getModel('webgility/ecc_eccxml');
         $this->xmlResponse->version='1.0';
         $this->xmlResponse->encoding='ISO-8859-1';

      	$this->root = $this->xmlResponse->createTag("RESPONSE", array('Version'=>'4.0'));
      	$this->envelope = $this->xmlResponse->createTag("Envelope", array(), '', $this->root);
      	$this->xmlResponse->createTag("Command", array(), $this->RequestParams['COMMAND'], $this->envelope);
      }

      public  function  SetDefaultStoreName(){
         $storeCollection = Mage::getModel('core/store_group')
            ->getCollection()
            ->addFieldToFilter('default_store_id', 1)
            ->load();
          foreach ($storeCollection->toArray() as $store){
             if (isset($store[0]['name'])){
                $this->STORE_NAME=$store[0]['name'];
             }
             if (isset($store[0]['group_id'])){
                $this->STORE_ID=$store[0]['group_id'];
             }

          }
      }

      public  function  GetStoreId(){
         return $this->STORE_ID;
      }

      public  function  UpdateOrdersPaymentStatus(){
         try{
            $ordersTag = $this->xmlRequest->getChildByName(0, "ORDERS");
            if ((count($ordersTag) <1)||$ordersTag==null){
                 print($this->xmlErrorResponse($this->RequestParams['COMMAND'], '9999',
                         'Error XML request! Not found required tag Orders', $this->STORE_NAME, ''));
                 exit;
            }


            $this->xmlRequest->getTag($ordersTag, $_tagName, $_tagAttributes, $_tagContents, $_tagTags);
            if (count($_tagTags) == 0) $no_orders = true; else $no_orders = false;

            $this->xmlResponse->createTag("StatusCode", array(), ($no_orders?"1000":"0"), $this->envelope);
            $this->xmlResponse->createTag("StatusMessage", array(), $no_orders?"No Orders returned":"All Ok", $this->envelope);

            if ($no_orders){
              print($this->xmlResponse->generate()); exit;
            }

            $ordersNode = $this->xmlResponse->createTag("Orders", array(), '', $this->root);
            foreach($_tagTags as $k=>$v){
              $this->Msg = array();
              $this->xmlRequest->getTag($v, $_tagName, $_tagAttributes, $_tagContents, $_orderTags);
              $orderNode = $this->xmlResponse->createTag("Order",  array(), '',     $ordersNode);
              unset($TAGNAME);
              $issetTag['HOSTORDERID']=true;
              $issetTag['LOCALORDERID']=true;
              $issetTag['PAYMENTSTATUS']=true;
              foreach($_orderTags as $k1=>$v1){
                $this->xmlRequest->getTag($v1, $_tagName, $_tagAttributes, $_tagContents, $_tempTags);
                $TAGNAME=strtoupper($_tagName);
                $this->RequestOrders[$TAGNAME] = $_tagContents;

                switch ($TAGNAME){
                   case 'HOSTORDERID':
                      $issetTag['HOSTORDERID']=false;
                   break;
                   case 'LOCALORDERID':
                      $issetTag['LOCALORDERID']=false;
                   break;
                   case 'PAYMENTSTATUS':
                      $issetTag['PAYMENTSTATUS']=false;
                   break;
                }
              }

            	$errorMsg = '';
            	foreach($issetTag as $key => $fl){
            		if ($fl===true){
            			$errorMsg .= $key." ";
            		}
            	}
               if ($errorMsg!=''){
                  print($this->xmlErrorResponse($this->RequestParams['COMMAND'], '9999',
                         'Error XML request! Not found required tags ('.$errorMsg.') in XML request', $this->STORE_NAME, ''));
                  exit;
               }

               if (isset($this->RequestOrders['CLEAREDON'])){
               }


               $this->xmlResponse->createTag('HostOrderID',  array(),$this->RequestOrders['HOSTORDERID'], $orderNode);
               $this->xmlResponse->createTag('LocalOrderID',  array(), $this->RequestOrders['LOCALORDERID'], $orderNode);


               $this->xmlResponse->createTag('LocalOrderID',  array(), $this->RequestOrders['LOCALORDERID'], $orderNode);

               $orders = Mage::getResourceModel('sales/order_collection')
                  ->addAttributeToSelect('*')
                  ->addFieldToFilter('increment_id', $this->RequestOrders['HOSTORDERID'])
                  ->addAttributeToFilter('store_id', $this->GetStoreId())
                  ->load();
               $orders_array=$orders->toArray();           


               if (count($orders_array)==0){
                  $this->Msg[] = 'Order not found';
                  $this->result = 'Failed';
               }else{
                  foreach($orders_array as $orders_el){
                  	$this->_current_order = Mage::getModel('sales/order')
                           ->load($orders_el['entity_id']);
                     if ($this->SetPaymentStatus()){
                           $this->result = 'Success';
                     }else{
                           $this->result = 'Failed';
                     }

                     break;
                  }
               }

               $this->xmlResponse->createTag('HostStatus',  array(), $this->result, $orderNode);
               if (count($this->Msg)>0){
               	$ind=1;
               	foreach($this->Msg as $Msg){
                     $this->xmlResponse->createTag('StatusMessage'.$ind++,  array(), $Msg, $orderNode);
                  }
               }

            }

              print($this->xmlResponse->generate()); exit;


         } catch (Exception $e) {
             Mage::printException($e);
         }
           return true;

      }
    //***************************************************
    //
    //      Update Orders Shipping Status Service
    //
    //***************************************************

      public  function  UpdateOrdersShippingStatus($orderId,$storeId=1){
	  $orders = Mage::getResourceModel('sales/order_collection')
                  ->addAttributeToSelect('*')
                  ->addFieldToFilter('increment_id', $orderId)
                  ->addAttributeToFilter('store_id', $storeId)
                  ->load();
				return $orders;

      }


    public function _initShipment($current_order,$RequestOrders){
	
         try {
         	$shipment = false;
/*            $orders = Mage::getModel('sales/order')
                  ->load($orderID);
*/          if (!$current_order->getId()) {
                  $this->Msg[] = 'Error. Order not longer exist.';
                  $this->result = 'Failed';
                  return false;
            }

            if (!$current_order->canShip()) {
            //  Ship �沲�裡!
            // Added Track and Content

                  return false;
            }

             // Not Ship

            $convertor  = Mage::getModel('sales/convert_order');
            $_shipment    = $convertor->toShipment($current_order);

//            $savedQtys = $this->_getItemQtys();
            $savedQtys = array();
            // 寡ᣫ殨㡪 櫲񞟓hipment 塭 Item  饠Order
            // Item - 衯鲨 믲ﱻ㡢�柡 Ӯ沲�衯鲨.

            //$this->_getItemQtys();
			
            foreach ($current_order->getAllItems() as $orderItem) {
                if (!$orderItem->getQtyToShip()) {
                    continue;
                }
                $_item = $convertor->itemToShipmentItem($orderItem);
                if (isset($savedQtys[$orderItem->getId()])) {
                    $qty = $savedQtys[$orderItem->getId()];
                }
                else {
                    $qty = $orderItem->getQtyToShip();
                }
                $_item->setQty($qty);
            	$_shipment->addItem($_item);
            }
			
			
			
//print_r($RequestOrders);
			 		

	       //  foreach($RequestOrders['TRACKINGNUMBER'] as $trackNumber){
$trackNumber = $RequestOrders['TRACKINGNUMBER'];
	            if (!empty($trackNumber)){
                   if (!$CarrierCode =$this->getShippingCode($RequestOrders['SHIPPEDVIA'])){
                   	  $CarrierCode="custom";
      	              $Title = $RequestOrders['SHIPPEDVIA'];
                   }elseif (isset($RequestOrders['SERVICEUSED'])){
     	                 $Title = $RequestOrders['SERVICEUSED'];
                   }else{
      	              $Title = $RequestOrders['SHIPPEDVIA'];
                   }

                   $_track = Mage::getModel('sales/order_shipment_track')
                       ->setNumber($trackNumber)
                       ->setCarrierCode($CarrierCode)
                       ->setTitle($Title);
                   $_shipment->addTrack($_track);
               }
          //  }
//            }*/

//print_r($_shipment);
//exit;
         return $_shipment;
         }catch (Exception $e) {
        	      $this->Msg[] = "Critical Error _initShipment (Exception e)" ;
        }
    }
	

	

    public function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);

        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        return $this;
    }

    public function ChangeShipment(){
        try {

            if (!$this->_current_order->getId()) {
                  $this->Msg[] = 'Error. Order not longer exist.';
                  $this->result = 'Failed';
                  return false;
            }
            if (!$this->_current_order->canUnhold()) {

            //  Ship �沲�裡!
            // Added Track and Content


                  $_shipments=$this->_current_order->getShipmentsCollection();
                  $shipments_array=$_shipments->toarray();

                  if (count($_shipments)==0){
                     $this->Msg[] = 'Error. Not object shipment.';
                     $this->result = 'Failed';
                     return false;
                  }
/*                  echo("-------------");
                   echo("<pre>"); var_dump($shipments_array); echo("</pre>");*/

                 foreach ($_shipments as $_shipment){
            	         foreach($this->RequestOrders['TRACKINGNUMBER'] as $trackNumber){
            	            if (!empty($trackNumber)){
              	                if (!$CarrierCode =$this->getShippingCode($this->RequestOrders['SHIPPEDVIA'])){
              	                	  $CarrierCode="custom";
                  	              $Title = $this->RequestOrders['SHIPPEDVIA'];
              	                }elseif (isset($this->RequestOrders['SERVICEUSED'])){
                 	                 $Title = $this->RequestOrders['SERVICEUSED'];
              	                }else{
                  	              $Title = $this->RequestOrders['SHIPPEDVIA'];
              	                }

                               $_track = Mage::getModel('sales/order_shipment_track')
                                   ->setNumber($trackNumber)
                                   ->setCarrierCode($CarrierCode)
                                   ->setTitle($Title);
                               $_shipment->addTrack($_track);
                           }
                        }
                        break;
                 }
                  $this->Msg[] = 'Add Track Information.';

                   $comment = "\nOrder shipped on ".$this->RequestOrders['SHIPPEDON'].
                                  " via ".$this->RequestOrders['SHIPPEDVIA'].
                                  " track number(s) ".implode(",",$this->RequestOrders['TRACKINGNUMBER']).
                      (isset($this->RequestOrders['SERVICEUSED'])?
                            " using ".$this->RequestOrders['SERVICEUSED']." service.\n" : "."
                      );

                   $_shipment->addComment($comment,true );

                   if ($this->send_email) {
                       $_shipment->setEmailSent(true);
                   }
                            // $this->RequestOrders['send_email']
                   $_shipment->Save();
                   $this->Msg[] = 'Add Content Information.';
                   $_shipment->sendUpdateEmail($this->send_email, $comment);
                   if ((strtoupper($this->RequestOrders['NOTIFYCUSTOMER'])=="YES")){
                      $this->Msg[] = 'Send Mail.';
                   }
                   return true;
                  break;


            }else {
                return false;
            }

        }catch (Exception $e) {
        	      $this->Msg[] = "Critical Error ChangeShipment (Exception e)" ;
        }
    }


    public function AddShipment(){
//        $data = $this->getRequest()->getPost('shipment');
        try {
            if ($shipment = $this->_initShipment()) {
                $shipment->register();
                $this->Msg[] = 'Create Shipment .';

                $comment = "\nOrder shipped on ".$this->RequestOrders['SHIPPEDON'].
                               " via ".$this->RequestOrders['SHIPPEDVIA'].
                               " track number ".implode(",",$this->RequestOrders['TRACKINGNUMBER']).
                   (isset($this->RequestOrders['SERVICEUSED'])?
                         " using ".$this->RequestOrders['SERVICEUSED']." service.\n" : "."
                   );

                $shipment->addComment($comment,true );
                $this->Msg[] = 'Add Content Information.';

                if ($this->send_email) {
                    $shipment->setEmailSent(true);
                }

                $this->_saveShipment($shipment);
                $this->Msg[] = 'Save Shipment .';
                $shipment->sendUpdateEmail($this->send_email, $comment);
                if ($this->send_email){
                   $this->Msg[] = 'Send Mail.';
                }

                return true;
            }else {
                return false;
            }
        }catch (Mage_Core_Exception $e) {
        	      $this->Msg[] = "Critical Error AddShipment (Mage_Core_Exception e)";
      //      $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
        	      $this->Msg[] = "Critical Error AddShipment (Exception e)" ;
         //   $this->_getSession()->addError($this->__('Can not save shipment.'));
        }
//        $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));

    }


     public function SetPaymentStatus (){
         try {
            if (!$this->_current_order->getId()) {
                  $this->Msg[] = 'Error. Order not longer exist.';
                  $this->result = 'Failed';
                  return false;
            }
            switch(strtolower($this->RequestOrders['HOSTSTATUS'])){

/*    self::STATE_COMPLETE,
                      self::STATE_CLOSED,
                      self::STATE_CANCELED,
                      self::STATE_HOLDED);*/

            	case self::STATE_CANCELED:
                  if ($this->_current_order->getState() === self::STATE_CANCELED )  {
                  	return false;
                  }elseif (!$this->_current_order->canCancel()){
                       return false;
            	   } else{
            	   	$this->_current_order->cancel()->Save();
            	   }
            	break;
            	case self::STATE_CLOSED:
                  if ($this->_current_order->getState() === self::STATE_CLOSED )  {
                  	return false;
                  }

            	break;
            	case self::STATE_HOLDED:
                  if ($this->_current_order->getState() === self::STATE_HOLDED )  {
                        return false;
                  }elseif (!$this->_current_order->canHold()){
                        return false;
            	   } else{
            	   	$this->_current_order->hold()->Save();

            	   }
            	break;
            	case self::STATE_UNHOLDED:
                  if (!$this->_current_order->canUnhold()){
                        return false;
            	   } else{
            	   	$this->_current_order->unhold()->Save();
            	   }
            	break;
            	case self::STATE_COMPLETE:
                  if ($this->_current_order->getState() === self::STATE_HOLDED )  {
                  	return false;
            	   }
            	break;
            	case self::STATE_PROCESSING:
                  if ($this->_current_order->getState() === self::STATE_PROCESSING )  {
                  	return false;
            	   }
            	break;
            	default:
            	   return false;

            }

         }catch (Exception $e) {
        	      $this->Msg[] = "Critical Error SetPaymentStatus (Exception e)" ;
        	      return false;
         }
         return true;
     }


     public function sendOrderUpdateEmail($notifyCustomer=true, $comment='')
    {
        $bcc = $this->_getEmails(self::XML_PATH_UPDATE_EMAIL_COPY_TO);
        if (!$notifyCustomer && !$bcc) {
            return $this;
        }

        $mailTemplate = Mage::getModel('core/email_template');
        if ($notifyCustomer) {
            $customerEmail = $this->getCustomerEmail();
            $mailTemplate->addBcc($bcc);
        } else {
            $customerEmail = $bcc;
        }

        if ($this->getCustomerIsGuest()) {
            $template = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $this->getStoreId());
            $customerName = $this->getBillingAddress()->getName();
        } else {
            $template = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_TEMPLATE, $this->getStoreId());
            $customerName = $this->getCustomerName();
        }

        $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store' => $this->getStoreId()))
            ->sendTransactional(
                $template,
                Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_IDENTITY, $this->getStoreId()),
                $customerEmail,
                $customerName,
                array(
                    'order'     => $this,
                    'billing'   => $this->getBillingAddress(),
                    'comment'   => $comment
                )
            );
        return $this;
    }




public function getorderitems($Id,$incrementID)
{
		
	   $collection =Mage::getModel('sales/order_item')->getCollection()
			->setOrderFilter($Id)
            ->setRandomOrder();
			 
	   $products = array();
        foreach ($collection as $item) {
            $products[] = $item->getProductId();
			
		    //$products[] = $item->toArray();
				
        }
		
		
		$productsCollection = Mage::getModel('catalog/product')
            ->getCollection()
			->addAttributeToSelect('*')
            ->addIdFilter($products)
            ->load();
	/*		
      Mage::getSingleton('catalog/product_visibility')
            ->addVisibleInSiteFilterToCollection($productsCollection);
	*/
			
        foreach ($collection as $item) {
            $item->setProduct($productsCollection->getItemById($item->getProductId()));
        }
		$collection = $collection->toArray();
		$productsCollection = $productsCollection->toArray();
		
        return $collection;
			
		//return $products;
		
		
}



public function addproduct($storeId=1)
{
		$Product  = Mage::getModel('catalog/product')->setStoreId($storeId);
		$Product->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
		$Product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
		//Mage::register('product', $Product);
		//Mage::register('current_product', $Product);
			
	   return $Product;
}

public function editproduct($storeId=1,$productId)
{
		$Product    = Mage::getModel('catalog/product')
		->setStoreId($storeId);
		$Product->load($productId);
		//Mage::register('product', $Product);
		//Mage::register('current_product', $Product);
		
		return $Product;
}



public function getProduct($storeId=1,$start_item_no=0,$limit=20)
{

if($start_item_no > 0)
{
if($start_item_no>$limit)
{
$start_no=intval($start_item_no/$limit)+1;
}
else
{
$start_no=intval($limit/$start_item_no)+1;
}
}
else
{
$start_no = 0;
}
			$productsCollection = Mage::getResourceModel('catalog/product_collection')
			->addAttributeToSelect('*')
			->addStoreFilter($storeId)
            ->addAttributeToSort('entity_id', 'asc')
			->setPageSize($limit)
            ->setCurPage($start_no);			
			return $productsCollection;
}

public function CountProduct($storeId=1)
{
			$productsCollection = Mage::getModel('catalog/product')
            ->getCollection()
			->addAttributeToSelect('*')
			->addStoreFilter($storeId)
            ->addAttributeToSort('entity_id', 'asc')
            ->load();			
			return $productsCollection;
}



public function getduplicaterecord($productname,$productcode)
{
		$productsCollection = Mage::getModel('catalog/product')
		->getCollection()
		->addAttributeToSelect('*')
		->addAttributeToFilter('sku', $productcode)
		->load();
		$productsCollection = $productsCollection->toArray();
		if(count($productsCollection)>0)
		{
			return "1";
		}
		else
		{
			$productsCollection = Mage::getModel('catalog/product')
			->getCollection()
			->addAttributeToSelect('*')
			->addAttributeToFilter('name', $productname)
			->load();
			$productsCollection = $productsCollection->toArray();
			if(count($productsCollection)>0)
			{
				return "1";
			}
			else
			{
				return "0";
			}
		}
}


public function getoptions($product)
{
	$collection = $product->getOptionInstance()
	->getProductOptionCollection($product);
	$lastvalues = array();
	$j=0;
	$collection = $collection->toArray();
	if(count($collection['items'])>0)
	{
		foreach($collection['items'] as $items){
			$values = Mage::getModel('catalog/product_option_value')
			->getCollection()
			->addTitleToResult(1)
			->addPriceToResult(1)
			->addOptionToFilter(array($items['option_id']))
			->setOrder('sort_order', 'asc')
			->setOrder('title', 'asc');
			$values = $values->toArray();
			for($i=0;$i<(count($values['items']));$i++)
			{
				$values['items'][$i]['option_title']= $items['default_title'];
				$lastvalues[$j] = $values['items'][$i];
				$j++;
			}
		}
		return $lastvalues;
	}
	
}

public function Getshipment($Id)
{
	$shipment = Mage::getModel('sales/order_shipment')->load($Id);
	$order = $shipment->getOrder();
	$order = $order->toArray();
	$shipment = $shipment->toArray();
	return $shipment;
}



public  function  GetOrdersRemained($datefrom,$start_order_no=0,$order_status_list='',$storeId=1)
{
	   $order_status_list = str_replace("'","",$order_status_list);
	   $order_status_list = explode(",",$order_status_list);
	   $carrier_names = array (
		"Processing" => "processing",
		"Pending" => "pending",
		"Pending PayPal" => "pending_paypal",
		"On Hold" => "holded",
		"Complete" => "complete",
		"Closed" => "closed",
		"Canceled" => "canceled"
		);	
		$order_status = array();	
		for ($i=0;$i<count($order_status_list);$i++)
		{
			if(array_key_exists($order_status_list[$i],$carrier_names))
			{
				$order_status[] =$carrier_names[$order_status_list[$i]];
			}
		}
	  
       try{

			$datetime1 = explode("-",$datefrom);			
			$datefrom = $datetime1[2]."-".$datetime1[0]."-".$datetime1[1];			
			$datefrom .=" 00:00:00"; 
			
         $this->_orders = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToSelect('*')
            ->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_street', 'order_address/street', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_company', 'order_address/company', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_city', 'order_address/city', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_region', 'order_address/region', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_country', 'order_address/country_id', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_postcode', 'order_address/postcode', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_telephone', 'order_address/telephone', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_fax', 'order_address/fax', 'billing_address_id', null, 'left')
            ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_street', 'order_address/street', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_company', 'order_address/company', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_city', 'order_address/city', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_region', 'order_address/region', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_country', 'order_address/country_id', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_postcode', 'order_address/postcode', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_telephone', 'order_address/telephone', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_fax', 'order_address/fax', 'shipping_address_id', null, 'left')
            ->addAttributeToFilter('created_at', array('from' => $datefrom,'datetime' => true))			
//            ->addAttributeToFilter('created_at', array('from' => $datefrom, 'to' => '02-12-2009', 'datetime' => true))
 //          ->addFieldToFilter('increment_id', "$start_order_no)

//			->addAttributeToFilter('increment_id', ">" . $start_order_no)

            ->addAttributeToFilter('store_id', $storeId)
			 ->addAttributeToFilter('increment_id', array('gt' => $start_order_no))
			 ->addAttributeToFilter('status', array('in' => $order_status))
			 
			 
            ->addAttributeToSort('increment_id', 'asc')
            ->load();
			return $this->_orders;			
         if (count($this->_orders)==0){ // 衯鲥狊  
		// print($this->xmlErrorResponse($this->RequestParams['COMMAND'], '1000',                   'No Orders returned', $this->STORE_NAME, ''));
           exit;
         }


        } catch (Exception $e) {
             Mage::printException($e);
        }
}

    //***************************************************
    //
    //      update  Orders Service
    //
    //***************************************************
public  function  GetOrders($datefrom,$start_order_no=0,$order_status_list='',$storeId=1,$no_of_orders=20)
{
	   
	   $order_status_list = str_replace("'","",$order_status_list);
	   $order_status_list = explode(",",$order_status_list);
	   $carrier_names = array (
		"Processing" => "processing",
		"Pending" => "pending",
		"Pending PayPal" => "pending_paypal",
		"On Hold" => "holded",
		"Complete" => "complete",
		"Closed" => "closed",
		"Canceled" => "canceled"
		);	
		$order_status = array();	
		for ($i=0;$i<count($order_status_list);$i++)
		{
			if(array_key_exists($order_status_list[$i],$carrier_names))
			{
				$order_status[] =$carrier_names[$order_status_list[$i]];
			}
		}
	  
       try{
			
			$datetime1 = explode("-",$datefrom);			
			$datefrom = $datetime1[2]."-".$datetime1[0]."-".$datetime1[1];			
			$datefrom .=" 00:00:00"; 
         $this->_orders = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToSelect('*')
            ->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_street', 'order_address/street', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_company', 'order_address/company', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_city', 'order_address/city', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_region', 'order_address/region', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_country', 'order_address/country_id', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_postcode', 'order_address/postcode', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_telephone', 'order_address/telephone', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_fax', 'order_address/fax', 'billing_address_id', null, 'left')
            ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_street', 'order_address/street', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_company', 'order_address/company', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_city', 'order_address/city', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_region', 'order_address/region', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_country', 'order_address/country_id', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_postcode', 'order_address/postcode', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_telephone', 'order_address/telephone', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_fax', 'order_address/fax', 'shipping_address_id', null, 'left')
            ->addAttributeToFilter('created_at', array('from' => $datefrom,'datetime' => true))			
//            ->addAttributeToFilter('created_at', array('from' => $datefrom, 'to' => '02-12-2009', 'datetime' => true))
 //          ->addFieldToFilter('increment_id', "$start_order_no)

//			->addAttributeToFilter('increment_id', ">" . $start_order_no)

            ->addAttributeToFilter('store_id', $storeId)
			 ->addAttributeToFilter('increment_id', array('gt' => $start_order_no))
			 ->addAttributeToFilter('status', array('in' => $order_status))
			 
			 
            ->addAttributeToSort('increment_id', 'asc')
            ->setPageSize($no_of_orders)
            ->load();
			return $this->_orders;			
         	if (count($this->_orders)==0){ // 衯鲥狊  
		// print($this->xmlErrorResponse($this->RequestParams['COMMAND'], '1000',                   'No Orders returned', $this->STORE_NAME, ''));
           exit;
         }

        } catch (Exception $e) {
             Mage::printException($e);
        }
}
	  
public function getorderstatus($incrementId,$storeId,$entityId)
{
	$orders = Mage::getResourceModel('sales/order_collection')
	->addAttributeToSelect('status')
	->addFieldToFilter('increment_id',$incrementId)
	->addAttributeToFilter('store_id', $storeId)
	->load();
	$orders_array=$orders->toArray();
	return $orders_array[$entityId]['status'];
}

protected  function  OrderToXml($order_array = array() )
{
	$this->xmlResponse->createTag("StatusCode", array(), "0", $this->envelope);
	$this->xmlResponse->createTag("StatusMessage", array(), "All OK", $this->envelope);
	$this->xmlResponse->createTag("Provider", array(), $this->QB_PROVIDER, $this->envelope);
	
	$ordersNode = $this->xmlResponse->createTag("Orders", array(), '', $this->root);
	
	
	foreach ($this->_orders as $_order) {
		
		$orders=$_order->toArray();
		
		$_payment=$_order->getPayment();
		$payment=$_payment->toArray();
		/*               echo("<pre>");
		var_dump($payment);
		echo("</pre>");*/
		
		$datetime=explode(" ",$orders["created_at"] );
		
		$dateCreateOrder= $datetime[0];
		$timeCreateOrder= $datetime[1];
		$orderNode = $this->xmlResponse->createTag("Order", array(), '', $ordersNode );
		$this->xmlResponse->createTag("OrderID",          array(), $orders['increment_id'], $orderNode );
		$this->xmlResponse->createTag("ProviderOrderRef", array(),$orders['increment_id'],  $orderNode);
		$this->xmlResponse->createTag("Date",             array(), $dateCreateOrder   ,$orderNode );
		$this->xmlResponse->createTag("Time",             array(), $timeCreateOrder   ,$orderNode );
		$this->xmlResponse->createTag("TimeZone",         array(), 'not found',                        $orderNode,  $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("StoreId",          array(), $orders['store_id'], $orderNode);
		$this->xmlResponse->createTag("StoreName",        array(), $this->STORE_NAME,   $orderNode,  $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("CustomerID",       array(), $orders['customer_id'], $orderNode);
		//           $this->xmlResponse->createTag("SalesRep",         array(), "not found",            $orderNode,  $this->Get__ENCODE_RESPONSE());
		
		// ALEX: ޲졿 衪ﭬ殲驠𐯲ﭳ �㼢ᬨ㡫᲼ ﹨⫠ 𐯱죠󯤮 롪 寡ᣨ쯱� �妰
		if (isset($orders['customer_note'])&&$orders['customer_note']!=''){
		$this->xmlResponse->createTag("Comment",          array(), $orders['customer_note'],    $orderNode,  $this->Get__ENCODE_RESPONSE());
		}
		
		$this->xmlResponse->createTag("Currency",         array(), $orders['order_currency_code'],    $orderNode, $this->Get__ENCODE_RESPONSE());
		
		$BillNode = $this->xmlResponse->createTag("Bill", array(), "", $orderNode);
		$ShipNode  = $this->xmlResponse->createTag("Ship",    array(), '', $orderNode);
		$itemsNode  = $this->xmlResponse->createTag("Items",  array(), '', $orderNode);
		$chargesNode  = $this->xmlResponse->createTag("Charges", array(), '', $orderNode);
		
		/////////////////////////////////////
		//   billing info
		/////////////////////////////////////
		$PayStatus = "Cleared";
		
		if (isset($payment['amount_ordered'])&&isset($payment['amount_paid'])){
		if (($payment['amount_paid']==$payment['amount_ordered']))
		$PayStatus = "Pending";
		}
		$this->xmlResponse->createTag("PayStatus",array(), $PayStatus,   $BillNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("PayMethod", array(),$this->getPayMethodName($payment['method']),$BillNode, $this->Get__ENCODE_RESPONSE());
		//   $this->xmlResponse->createTag("PayStatus", array(), "", $BillNode);
		$this->xmlResponse->createTag("FirstName", array(), $this->_maxlen($orders["billing_firstname"],45), $BillNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("LastName", array(), $this->_maxlen($orders["billing_lastname"],45), $BillNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("MiddleName", array(),"", $BillNode, $this->Get__ENCODE_RESPONSE());
		if (!empty($orders["billing_company"]))
		$this->xmlResponse->createTag("CompanyName", array(), $this->_maxlen($orders["billing_company"],75), $BillNode, $this->Get__ENCODE_RESPONSE());
		
		$this->xmlResponse->createTag("Address1", array(), $this->_maxlen($orders["billing_street"],75), $BillNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("Address2", array(), $this->_maxlen("",75), $BillNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("City", array(), $this->_maxlen($orders["billing_city"],75), $BillNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("State", array(), $this->_maxlen($orders["billing_region"],75), $BillNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("Country", array(), $this->_maxlen($orders["billing_country"],75), $BillNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("Zip", array(), $this->_maxlen($orders["billing_postcode"],75), $BillNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("Email", array(), $this->_maxlen($orders["customer_email"],150), $BillNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("Phone", array(), $this->_maxlen($orders["billing_telephone"],15), $BillNode, $this->Get__ENCODE_RESPONSE());
		//    $this->xmlResponse->createTag("State", array(), $this->_maxlen($orders["billing_state"],75), $BillNode, $this->Get__ENCODE_RESPONSE());
		
		//            $this->xmlResponse->createTag("Email", array(), $orders["customer_email"], $BillNode, $this->Get__ENCODE_RESPONSE());
		/////////////////////////////////////
		//   CreditCard info
		/////////////////////////////////////
		
		
		
		
		if (in_array($payment['method'],$this->PayMethodsCC)){
		$BillCreditCardNode = $this->xmlResponse->createTag("CreditCard", array(), "", $BillNode);
		if (isset($payment['cc_type'])){
		if ($cc_type=$this->getCcTypeName($payment['cc_type'])){
		$this->xmlResponse->createTag("CreditCardType", array(), $cc_type, $BillCreditCardNode, $this->Get__ENCODE_RESPONSE());
		}
		}else
		$this->xmlResponse->createTag("CreditCardType", array(), "", $BillCreditCardNode, $this->Get__ENCODE_RESPONSE());
		
		if (isset($payment['amount_paid'])){
		$this->xmlResponse->createTag("CreditCardCharge", array(), $payment['amount_paid'], $BillCreditCardNode, $this->Get__ENCODE_RESPONSE());
		}else{
		$this->xmlResponse->createTag("CreditCardCharge", array(), "", $BillCreditCardNode, $this->Get__ENCODE_RESPONSE());
		}
		
		//  sprintf('%02d',$p->getCcExpMonth()).substr($p->getCcExpYear(),-2,2)
		if (isset($payment['cc_exp_month'])&&isset($payment['cc_exp_year'])){
		$this->xmlResponse->createTag("ExpirationDate", array(),sprintf('%02d',$payment['cc_exp_month']).substr($payment['cc_exp_year'],-2,2), $BillCreditCardNode, $this->Get__ENCODE_RESPONSE());
		
		}else{
		$this->xmlResponse->createTag("ExpirationDate", array(),"", $BillCreditCardNode, $this->Get__ENCODE_RESPONSE());
		}
		
		
		
		$CreditCardName = (isset($payment['cc_owner'])?($payment['cc_owner']):"");
		$this->xmlResponse->createTag("CreditCardName", array(), $CreditCardName, $BillCreditCardNode, $this->Get__ENCODE_RESPONSE());
		
		$CreditCardNumber = (isset($payment['cc_last4'])?$this->getCcNumberXXXX($payment['cc_last4']):"");
		$this->xmlResponse->createTag("CreditCardNumber", array(), $CreditCardNumber, $BillCreditCardNode, $this->Get__ENCODE_RESPONSE());
		
		//               $this->xmlResponse->createTag("CVV2", array(), "", $BillCreditCardNode);
		
		
		$AuthDetails = (isset($payment['cc_trans_id'])?"cc_trans_id=".$payment['cc_trans_id'].";":"");
		$AuthDetails .= (isset($payment['last_trans_id'])?"last_trans_id=".$payment['last_trans_id'].";":"");
		$this->xmlResponse->createTag("AuthDetails", array(), $AuthDetails, $BillCreditCardNode, $this->Get__ENCODE_RESPONSE());
		
		
		//               $this->xmlResponse->createTag("ReconciliationData", array(), "", $BillCreditCardNode, $this->Get__ENCODE_RESPONSE());
		
		}
		
		
		/////////////////////////////////////
		//   shipping info
		/////////////////////////////////////
		
		
		$this->xmlResponse->createTag("ShipCarrierName", array(), $this->_maxlen($orders["shipping_description"],45), $ShipNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("ShipMethod", array(), $this->_maxlen($orders["shipping_method"],45), $ShipNode, $this->Get__ENCODE_RESPONSE());
		
		$this->xmlResponse->createTag("FirstName", array(), $this->_maxlen($orders["shipping_firstname"],45), $ShipNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("LastName", array(), $this->_maxlen($orders["shipping_lastname"],45), $ShipNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("MiddleName", array(),"", $ShipNode, $this->Get__ENCODE_RESPONSE());
		if (!empty($orders["shipping_company"]))
		$this->xmlResponse->createTag("CompanyName", array(), $this->_maxlen($orders["shipping_company"],75), $ShipNode, $this->Get__ENCODE_RESPONSE());
		
		$this->xmlResponse->createTag("Address1", array(), $this->_maxlen($orders["shipping_street"],75), $ShipNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("Address2", array(), $this->_maxlen("",75), $ShipNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("City", array(), $this->_maxlen($orders["shipping_city"],75), $ShipNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("State", array(), $this->_maxlen($orders["shipping_region"],75), $ShipNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("Country", array(), $this->_maxlen($orders["shipping_country"],75), $ShipNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("Zip", array(), $this->_maxlen($orders["shipping_postcode"],75), $ShipNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("Email", array(), $this->_maxlen($orders["customer_email"],150), $ShipNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("Phone", array(), $this->_maxlen($orders["shipping_telephone"],15), $ShipNode, $this->Get__ENCODE_RESPONSE());
		
		
		$item = Mage::getModel('sales/order_item')->getCollection()
		->addAttributeToSelect('*')
		->setOrderFilter($orders["entity_id"])
		//   ->setOrder('000000001')
		->load();
		
		$item_array=$item->toArray();
		
		/////////////////////////////////////
		//   items info
		/////////////////////////////////////
		
		foreach($item_array as $itm) {
		
		$itemNode = $this->xmlResponse->createTag("Item",    array(), '',    $itemsNode);
		
		$this->xmlResponse->createTag("ItemCode",       array(), $this->_maxlen($itm["sku"],25), $itemNode,$this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("ItemDescription",array(), $this->_maxlen($itm["name"],75),      $itemNode, $this->Get__ENCODE_RESPONSE());
		//                $this->xmlResponse->createTag("ItemDescription",array(), $this->_maxlen($itm["description"],75),      $itemNode, $this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("Quantity",       array(), $itm["qty_ordered"],  $itemNode);
		$this->xmlResponse->createTag("UnitPrice",      array(), $itm["price"],        $itemNode);
		$this->xmlResponse->createTag("ItemTotal",      array(), $itm["row_total"],  $itemNode);
		$this->xmlResponse->createTag("ItemUnitWeight",      array(), $itm["weight"],  $itemNode);
		
		for ($i=1; $i<6; $i++) {
		$this->xmlResponse->createTag("CustomField".(string)$i,      array(), '',  $itemNode);
		}
		}
		
		/////////////////////////////////////
		//   Charges info
		/////////////////////////////////////
		
		$this->xmlResponse->createTag("Shipping",       array(), $orders["shipping_amount"], $chargesNode,$this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("Handling",       array(), '0.0000', $chargesNode,$this->Get__ENCODE_RESPONSE());
		$this->xmlResponse->createTag("Tax",       array(), $orders["tax_amount"], $chargesNode,$this->Get__ENCODE_RESPONSE());
		if (isset($orders["tax_refunded"])&&$orders["tax_refunded"]!=null&&$orders["tax_refunded"]!=0){
		$TaxOther = $orders["tax_refunded"];
		}
		
		if (isset($orders["tax_canceled"])&&$orders["tax_canceled"]!=null&&$orders["tax_canceled"]!=0){
		if (isset($TaxOther))
		$TaxOther += $orders["tax_canceled"];
		else
		$TaxOther = $orders["tax_canceled"];
		}
		if (isset($TaxOther))
		$this->xmlResponse->createTag("TaxOther",       array(), $TaxOther, $chargesNode);		
		$this->xmlResponse->createTag("Discount",       array(),  $orders["discount_amount"], $chargesNode);
		$this->xmlResponse->createTag("Total",       array(),  $orders["grand_total"], $chargesNode);
		
		if (isset($orders["coupon_code"])){
		
		
		
		if ($orders["coupon_code"]!=""){
		
		$discount = Mage::getModel('salesrule/rule')->getResourceCollection()
		->addFieldToFilter('coupon_code', $orders["coupon_code"])
		->load();
		
		$discount_=$discount->toArray();
		$discount_=$discount_['items'][0];
		//   var_dump($discount_);
		
		$CouponsNode=$this->xmlResponse->createTag("Coupons",       array(), '', $chargesNode);
		$this->xmlResponse->createTag("Coupon",       array(), $discount_['name'], $CouponsNode);
		$this->xmlResponse->createTag("CouponCode",       array(), $orders["coupon_code"], $CouponsNode);
		$this->xmlResponse->createTag("CouponID",       array(), $discount_['rule_id'], $CouponsNode);
		$this->xmlResponse->createTag("CouponDescription",       array(), $discount_['description'], $CouponsNode);
		$this->xmlResponse->createTag("CouponAction",       array(), $discount_['simple_action'], $CouponsNode);
		$this->xmlResponse->createTag("CouponValue",       array(), $discount_['discount_amount'], $CouponsNode);
		}
		
		}
		for ($i=1; $i<6; $i++) {
			$this->xmlResponse->createTag("CustomField".(string)$i,      array(), '',  $chargesNode);
		}
		
	}
	header("Content-type: application/xml");
	print($this->xmlResponse->generate());
	
}
   public  function CheckXmlRequst(){
       $this->xmlRequest->getTag(0, $this->_tagName, $this->_tagAttributes, $this->_tagContents, $this->_tagTags);
       if (strtoupper(trim($this->_tagName)) != 'REQUEST') {
           print($this->xmlErrorResponse('unknown', '9999',
                   'Unknown request', $this->STORE_NAME, ''));
           exit;
       }
       if (count($this->_tagTags) == 0) {
           print($this->xmlErrorResponse('unknown', '9999',
                   'REQUEST tag doesnt have necessry parameters', $this->STORE_NAME, ''));
           exit;
       }
       $this->RequestParams = Array();
       foreach ($this->_tagTags as $k=>$v){
           $this->xmlRequest->getTag($v, $tN, $tA, $tC, $tT);
           $this->RequestParams[strtoupper($tN)] = trim($tC);
       }

       if (!isset($this->RequestParams['COMMAND'])) {
           print($this->xmlErrorResponse('unknown', '9999',
                   'Command is not set', $this->STORE_NAME, ''));
           exit;
       }
       $this->RequestParams['COMMAND'] = strtoupper($this->RequestParams['COMMAND']);

  // print($this->RequestParams['COMMAND']);
       if(  ($this->RequestParams['COMMAND'] != ('GET'.'ORDERS'))
            && ($this->RequestParams['COMMAND'] != ('UPDATE'.'ORDERS'))
            && ($this->RequestParams['COMMAND'] != ('UPDATE'.'INVENTORY'))
            && ($this->RequestParams['COMMAND'] != ('UPDATE'.'ORDERS'.'SHIPPING'.'STATUS'))
            && ($this->RequestParams['COMMAND'] != ('UPDATE'.'ORDERS'.'PAYMENT'.'STATUS'))){
          print($this->xmlErrorResponse('unknown', '9999',
                  'Unknown Command '.$this->RequestParams['COMMAND'], $this->STORE_NAME, ''));
          exit;
       }
       return true;
   }


protected function _getDataModel()
{
	if (is_null($this->_dataModel)) {
		$this->_dataModel = Mage::getModel('thub/run_data');
	}
	return $this->_dataModel;
}


    /**
     * Init installation
     *
     * @param Mage_Core_Model_App $app
     * @return boolean
     */
    public function init(Mage_Core_Model_App $app)
    {
        return true;
    }
    public function xmlErrorResponse($command, $code, $message, $provider="", $request_id='') {
        header("Content-type: application/xml");
        $this->xmlResponse = Mage::getModel('webgility/ecc_eccxml');
        $this->xmlResponse->loadString('<?xml version="1.0" encoding="UTF-8"?>');
        //$xmlResponse = new xml_doc();
        //$xmlResponse->version='';
       // $xmlResponse->encoding='UTF-8';
        $root = $this->xmlResponse->createTag("RESPONSE", array('Version'=>'1.0'));
        $envelope = $this->xmlResponse->createTag("Envelope", array(), '', $root);
        $this->xmlResponse->createTag("Command", array(), $command, $envelope);
        $this->xmlResponse->createTag("StatusCode", array(), $code, $envelope);
        $this->xmlResponse->createTag("StatusMessage", array(), $message, $envelope);
        $this->xmlResponse->createTag("Provider", array(), $provider, $envelope);
        return $this->xmlResponse->generate();
    }


    protected function _maxlen($str,$len){

        if (strlen($str)>$len)
           return substr($str,0,$len);
        else
           return $str;
    }

    public function getCcTypeName($ccType)
    {
        return isset($this->types[$ccType]) ? $this->types[$ccType] : false;
    }

    public function getPayMethodName($method)
    {
        return isset($this->PayMethods[$method]) ? $this->PayMethods[$method] : "unknown method:".$method;
    }

    public function getCcNumberXXXX($ccNumber){
        return "XXXX-XXXX-XXXX-".$ccNumber;
    }

    public function getShippingCode($shipp){
       $shipp = strtoupper($shipp);
       if (array_key_exists($shipp, $this->carriers_)){
          return $this->carriers_[$shipp];
       }
       return false;
    }
	## By Vaibhav Parashar
	## start code 
	public function genrateWSDL()
	{
		require_once("lib/nusoap.php");
		
		#
		# create a new soap server
		#
		$namespace = "http://www.webgility.com/services";
		$server = new soap_server();
		
		#
		# configure our WSDL
		#
		$server->configureWSDL("eCCService",$namespace);
		
		#
		# set our namespace
		#
		$server->wsdl->schemaTargetNamespace = $namespace;
		
		#
		# register our WebMethod#

		#
		# Test for valid authorization.
		#
		$server->register('checkAccessInfo',array('username'=>'xsd:string','password'=>'xsd:string'),array('return'=>'xsd:string'),$namespace,false,'rpc','','Check for valid authorization information.');
		
		#
		# Get Company information.
		#
		$server->register('getCompanyInfo',array('username'=>'xsd:string','password'=>'xsd:string'),array('return'=>'xsd:string'),$namespace,false,'rpc','encoded','Returns company information.');
		
		#
		# Return all orders from X-cart to eCCclient.
		#
		$server->register('getOrders',array('username'=>'xsd:string','password'=>'xsd:string','datefrom'=>'xsd:string','start_order_no'=>'xsd:string','ecc_excl_list'=>'xsd:string','order_per_response'=>'xsd:string'),array('return'=>'xsd:string'),$namespace,false,'rpc','encoded','Return all orders from X-cart to eCCclient. Exclude orders in failed, declined and not finished states.Please ensure (m-d-Y) format for date filters.');
		
		#
		# Return all orders from X-cart to eCCclient.
		#
		$server->register('getItems',array('username'=>'xsd:string','password'=>'xsd:string','start'=>'xsd:string','limit'=>'xsd:string'),array('return'=>'xsd:string'),$namespace,false,'rpc','encoded','Rerurn all items from OSCommerce to eCCclient.');
		
		#
		# Return all payment methods from X-cart to eCCclient.
		#
		$server->register('getPaymentMethods',array('username'=>'xsd:string','password'=>'xsd:string'),array('return'=>'xsd:string'),$namespace,false,'rpc','encoded','Return all payment methods configured for X-cart to eCCclient.');
		
		#
		# Return all payment methods from X-cart to eCCclient.
		#
		$server->register('getCategory',array('username'=>'xsd:string','password'=>'xsd:string'),array('return'=>'xsd:string'),$namespace,false,'rpc','encoded','Return all Category configured for X-cart to eCCclient.');
		
		#
		# Rerurn all payment methods from livecart to eCCclient.
		#
				$server->register('getOrderStatus',array('username'=>'xsd:string','password'=>'xsd:string'),array('return'=>'xsd:string'),$namespace,false,'rpc','encoded','Rerurn all order status from X-cart to eCCclient.');



		#
		# Return all tax methods from X-cart to eCCclient.
		#
		$server->register('getTaxes',array('username'=>'xsd:string','password'=>'xsd:string'),array('return'=>'xsd:string'),$namespace,false,'rpc','encoded','Return all taxes configured for X-cart to eCCclient.');
		
		#
		# Return all Menufacturers methods from X-cart to eCCclient.
		#
		$server->register('getManufacturers',array('username'=>'xsd:string','password'=>'xsd:string'),array('return'=>'xsd:string'),$namespace,false,'rpc','encoded','Return all manufacturers configured for X-cart to eCCclient.');
		
		
		#
		# Return all shipping methods from X-cart to eCCclient.
		#
		$server->register('getShippingMethods',array('username'=>'xsd:string','password'=>'xsd:string'),array('return'=>'xsd:string'),$namespace,false,'rpc','encoded','Return all shipping methods available for X-cart to eCCclient.');
		$server->register('getStores',array('username'=>'xsd:string','password'=>'xsd:string'),array('return'=>'xsd:string'),$namespace,false,'rpc','encoded','Return all shipping methods available for X-cart to eCCclient.');
		
		
		#
		# Update X-cart order Notes and tracking number from eCCClient
		#
		$str=nl2br(htmlspecialchars("
		<REQUEST Version=\"1.0\">
		<Orders>
			<Order>
				 <OrderID>10</OrderID>
				 <ShippedOn>05/18/2007</ShippedOn>
				 <ShippedVia>UPS</ShippedVia>
				 <ServiceUsed>Ground</ServiceUsed>
				 <TrackingNumber>Z31212215475</TrackingNumber>
				 <ORDERSTATUS>P</ORDERSTATUS>
				 <ORDERNOTES> asdfasdfasd fasd fa sdf \"asd fa sdf asd a sdfa\' sdfsd</ORDERNOTES>
			</Order>
			<Order>
				 <<OrderID>11</OrderID>
				 <ShippedOn>05/18/2007</ShippedOn>
				 <ShippedVia>UPS</ShippedVia>
				 <ServiceUsed>Ground</ServiceUsed>
				 <TrackingNumber>Z31212215475</TrackingNumber>
				 <ORDERSTATUS>P</ORDERSTATUS>
				 <ORDERNOTES> asdfasdfasd fasd fa sdf \"asd fa sdf asd a sdfa\' sdfsd</ORDERNOTES>
			</Order>
		</Orders></REQUEST>",ENT_QUOTES));
		$str='Update X-cart order Notes and tracking number from eCCClient.The input parameter "xmlOrders" should be in the xml format as defined below.'.$str;
		$server->register('UpdateOrdersShippingStatus',array('username'=>'xsd:string','password'=>'xsd:string','xmlOrders'=>'xsd:string'),array('return'=>'xsd:string'),$namespace,false,'rpc','encoded',$str);
		
		$str=nl2br(htmlspecialchars("
		<REQUEST Version=\"1.0\">
		<Items>
			<Item>
				<ProductID>11</ProductID>
				<Sku/>
				<ProductName>New Printer</ProductName>
				<Qty/>
				<Price/>	
				<varients>		
				  <varient ID=\"21\" Qty=\"10\" Price=\"200\"/>	
				  <varient ID=\"21\" Qty=\"10\" Price=\"220\"/>	
				  <varient ID=\"21\" Qty=\"10\" Price=\"240\"/>	
				</varients>		
				<options/>
			</Item>
			<Item>
				<ProductID>12</ProductID>
				<Sku>sku1101</Sku>
				<ProductName>New CD</ProductName>
				<Qty>101</Qty>
				<Price>11</Price>	
				<varients/>		
				<options/>
			</Item>
		</Items>
		</REQUEST>",ENT_QUOTES));
		$str='Update X-cart Product\'s quantity and price from eCCClient.The input parameter "xmlOrders" should be in the xml format as defined below.'.$str;
		$server->register('synchronizeItems',array('username'=>'xsd:string','password'=>'xsd:string','xmlOrders'=>'xsd:string'),array('return'=>'xsd:string'),$namespace,false,'rpc','encoded',$str);
		
		#
		# Add Product to X-cart from eCCClient
		#
		
		$str=nl2br(htmlspecialchars("
			<REQUEST Version='1.0'>
			<Items>
			<Item>
				<CatID>22</CatID>
				<ItemCode>AbC111</ItemCode>
				<ItemName>AbC111</ItemName>
				<ItemDesc>AbC111</ItemDesc>
				<FreeShipping>N</FreeShipping>
				<TaxExempt>N</TaxExempt>
				<TaxID/>
				<ManufacturerID/>
				<ItemMatchBy>SKU#</ItemMatchBy>
				<Quantity>45</Quantity>
				<UnitPrice>560.25</UnitPrice>
				<Weight>12</Weight>
				<ItemVariants/>
				<ItemOptions/>
			</Item>
			</Items>
		</REQUEST>",ENT_QUOTES));
		
		$str='Update X-cart Product\'s quantity and price from eCCClient.The input parameter "xmlOrders" should be in the xml format as defined below.'.$str;
		$server->register('addProduct',array('username'=>'xsd:string','password'=>'xsd:string','xmlOrders'=>'xsd:string'),array('return'=>'xsd:string'),$namespace,false,'rpc','encoded',$str);
		
		
		
		
		
		#
		# Update X-cart items quantity in stock from eCCClient
		#
		$str=nl2br(htmlspecialchars("
		<REQUEST Version=\"1.0\">
		<UpdateInventory>1</UpdateInventory>
		   <Items>
			  <Item>
				 <ItemCode>SKU14</ItemCode>
				 <QuantityInStock>126</QuantityInStock>
			  </Item>
			  <Item>
				 <ItemCode>SKU143</ItemCode >
				 <QuantityInStock>25</QuantityInStock>
			  </Item>
		   </Items>
		</REQUEST>",ENT_QUOTES));
		$str='Update X-cart items quantity in stock from eCCClient. The input parameter "xmlItems" should be in the xml format as defined below.'.$str;
		$server->register('UpdateInventory',array('username'=>'xsd:string','password'=>'xsd:string','xmlItems'=>'xsd:string'),array('return'=>'xsd:string'),$namespace,false,'rpc','encoded',$str);
		
		
		#
		# Update X-cart orders for QB posted flag from eCCClient
		#
		$str=nl2br(htmlspecialchars("
		<REQUEST Version=\"1.0\">
		<Orders>
			<Order>
				 <HostOrderID>18</HostOrderID>
				 <LocalOrderRef>eCC118</LocalOrderRef>
			</Order>
			<Order>
				 <HostOrderID>20</HostOrderID>
				 <LocalOrderRef>eCC120</LocalOrderRef>
			</Order>
		</Orders></REQUEST>",ENT_QUOTES));
		$str='Update X-cart order porsted to QB flag along with date and local eCC order referenbce from eCCClient.The input parameter "xmlOrders" should be in the xml format as defined below.'.$str;
		
		$server->register('UpdateOrders',array('username'=>'xsd:string','password'=>'xsd:string','xmlOrders'=>'xsd:string'),array('return'=>'xsd:string'),$namespace,false,'rpc','encoded',$str);

		
		#
		# Get our posted data if the service is being consumed otherwise leave this data blank.
		#
		$POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA'])?$GLOBALS['HTTP_RAW_POST_DATA'] : '';

		$POST_DATA = ereg_replace("&lt;","&#60;",$POST_DATA); 
		$POST_DATA = str_replace("&gt;",">",$POST_DATA);
		$POST_DATA = ereg_replace("&quot;",'"',$POST_DATA);
		$POST_DATA = ereg_replace("&apos;","'",$POST_DATA); 
		
	
		# pass our posted data (or nothing) to the soap service
		$server->service($POST_DATA);
	
	}
	
	function getstores()
	{
	
	$stores = Mage::getModel('core/store')
                ->getResourceCollection()
                ->setLoadDefault(true)
                ->load();
				$stores = $stores->toArray();
				/*$store = Mage::app()->getStore();
				$store = $store->toArray();
				$name = Mage::app()->getDefaultStoreView();
				$name = $name->toArray();				
//				echo $name;
				//print_r($name);
				//;
//				 $websites = Mage::getModel('core/website')->getDefaultStore();
				  $storeViews = Mage::app()->getStores();*/
//                ->setLoadDefault(true)
  //              ->load()
    //            ->toOptionHash(true);
	
				
			
				return $stores;
	
	}
	
	public function getmanufacturers()
	{
	$optionCollection = array();
		 $attributesInfo = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setCodeFilter('manufacturer')
            ->addSetInfo()
            ->getData();
			 $attributes = array();
			// print_r($attributesInfo);
			// exit;
			 
			 if(count($attributesInfo)>0)
{
            $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($attributesInfo[0]['attribute_id'])
                ->setPositionOrder('desc', true)
                ->load();
				 $optionCollection = $optionCollection->toArray();
				//print_r($optionCollection);
//				exit;
				
				return $optionCollection;
				
				}
				else
				{
				return $optionCollection;
				}
 
	
	}
	
	function getStoreDetails()
	{
	//exit;
	

				//print_r($optionCollection);
				//exit;
		 $config = array();
		$store = Mage::getSingleton('Adminhtml/system_store');
  		$data =  $store->getStoreNameWithWebsite(1);		
		list($config['website'],$config['storeName'],$config['view']) = explode("/",$data);
		$configDataCollection = Mage::getModel('core/config_data')->getCollection();//->addScopeFilter('default', '0', 'currency');
	//	$configDataCollection = $configDataCollection->toArray();
//		print_r($configDataCollection);
	//	exit;
		foreach ($configDataCollection as $data) {
			//echo $data->getPath();
			list($base,$field,$value) = explode("/",$data->getPath());
			$config[$base][$field][$value] = $data->getValue();
			//echo $config[$base][$field][$value];
        }
		//print_r($config);
		//exit;
		
		return $config;
	
	}
	
	function getAttributeValues($tag)
	{
		$obj_cat = Mage::getSingleton('Catalog/category');
		echo "<pre>";
		print_r($obj_cat->getAttributes(false));
		echo "</pre>";
		die();	
	}
	   
	   
	    public function _getPaymentMethods($store=null)
	    {
//		 $config = Mage::getStoreConfig('payment', $store);
        return Mage::getSingleton('payment/config')->getAllMethods();
    }
	

    public function getPaymentArray($store=null)
    {
	
        $methods = array(array('value'=>'', 'label'=>''));
        foreach ($this->_getPaymentMethods() as $paymentCode=>$paymentModel) {
            $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
            $methods[$paymentCode] = array(
                'label'   => $paymentTitle,
                'value' => $paymentCode,
            );
        }
//print_r($methods);
        return $methods;
    }
	
	
	 public function getPaymentlabel($paymethod='')
    {
	
        $method = "";
        foreach ($this->_getPaymentMethods() as $paymentCode=>$paymentModel) {
            $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
			if($paymentCode==$paymethod)
			{
			return $paymentTitle;
			break;
			}
			
			
        }
		return $method;
//print_r($methods);
//        return $methods;
    }
	
	public function _getorderstatuses($storeId=1)
	{			
	            $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
				//print_r($statuses);
				//exit;
				return $statuses;				

	
	}
	
	public function _getcategory($storeId=1)
	{
		$collection = Mage::getModel('catalog/category')->getCollection()
            ->setStoreId($storeId)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active');
			        foreach ($collection as $category) {
            /* @var $category Mage_Catalog_Model_Category */
            $result[] = array(
                'category_id' => $category->getId(),
                'parent_id'   => $category->getParentId(),
                'name'        => $category->getName(),
                'is_active'   => $category->getIsActive(),
                'position'    => $category->getPosition(),
                'level'       => $category->getLevel()
            );
        }
        return $result;
	}
	
	
	public function _gettaxes($storeId=1)
	{
	 $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
				//print_r($statuses);
				//exit;
        //$rateRequest = Mage::getModel('tax/calculation_rate')->getCollection()->toOptionArray();
		$rateRequest = Mage::getModel('tax/class')->getCollection()->setClassTypeFilter('PRODUCT')->toOptionArray();		
	//		print_r($rateRequest);
	
		//	exit;
		return $rateRequest;
    	
	}
	
	  public function _getshippingMethods($storeid=1,$isActiveOnlyFlag=false)
    {
	       // $rateRequest = Mage::getModel('tax/calculation_rate')->getCollection()->toOptionArray();
			//print_r($rateRequest);
			//exit;
        $methods = array(array('value'=>'', 'label'=>''));
		$carriers = Mage::getSingleton('shipping/config')->getAllCarriers($storeid);
//      $carriers = Mage::getSingleton('shipping/config')->getAllCarriers();
	//	print_r($carriers);
		//exit;
        foreach ($carriers as $carrierCode=>$carrierModel) {
            if (!$carrierModel->isActive() && (bool)$isActiveOnlyFlag === true) {
                continue;
            }
            $carrierMethods = $carrierModel->getAllowedMethods();
            if (!$carrierMethods) {
                continue;
            }
            $carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');
            $methods[$carrierCode] = array(
                'label'   => $carrierTitle,
                'value' => array(),
            );
            foreach ($carrierMethods as $methodCode=>$methodTitle) {
                $methods[$carrierCode]['value'][] = array(
                    'value' => $carrierCode.'_'.$methodCode,
                    'label' => $methodTitle,
                );
            }
        }


//print_r($methods);
//exit;
        return  $methods;
    }



 }

#########################################################################################
#
# registered function defination
#

require_once('eCCxml.php');	
include_once("dBug.php");
define('__ENCODE_RESPONSE',true);	
//define("__STORE_ID","2");

function getDefaultStore()
{
if(!defined("__STORE_ID"))
{
$name = Mage::app()->getDefaultStoreView();
$name = $name->toArray();	
//print_r($name);

return $name['store_id'];
define("__STORE_ID",$name['store_id']);
}
else
{
return __STORE_ID;
}

}

function getVersion()
{

if(Mage::getVersion()!="")
{
	return Mage::getVersion();
}
else
{
return "0";
}

}

function checkAccessInfo($username,$password)
{	
	$objNew = new Mage_Webgility_Model_Ecc_Run();
	$xmlResponse = new xml_doc();
	$xmlResponse->version='1.0';
	$xmlResponse->encoding='UTF-8';
	$root = $xmlResponse->createTag("RESPONSE", array('Version'=>'1.0'));
	$status =  $objNew->CheckUser($username,$password);
	$Cartsupportversion = array("1.2.1","1.2.1.1","1.3.1.1","1.3.2.1","1.3.2.2","1.3.1","1.3.0");
/*	$eCCsupportversions= array("v1.8","v1.8.1","v1.9");
	if(!in_array($eCCversion,$eCCsupportversions))
			{
			$xmlResponse->createTag("StatusCode", array(), "6", $root, __ENCODE_RESPONSE);
			$xmlResponse->createTag("StatusMessage", array(), "eCC version " . $eCCversion ." is not supported by service module. Please contact webgility." , $root, __ENCODE_RESPONSE);
			$xml= $xmlResponse->generate();	
		return $xml;	
			}*/
			$version = getVersion();
			
	//echo $version;
	//exit;

		if($status!="0"){ //login name invalid
		if($status=="1"){
		$xmlResponse->createTag("StatusCode", array(), "1", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid login. Authorization failed", $root, __ENCODE_RESPONSE);
		$xml= $xmlResponse->generate();
		return $xml;
		}
		if($status=="2"){ //password invalid
		$xmlResponse->createTag("StatusCode", array(), "2", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid password. Authorization failed", $root, __ENCODE_RESPONSE);
		$xml= $xmlResponse->generate();
		return $xml;
		}
	}
	else {
	$code = "0";
		$message = "Successfully connected to your online store.";
		$xmlResponse->createTag("StatusCode", array(), $code, $root,__ENCODE_RESPONSE);
if($version!==0)
	{
	if(!in_array($version,$Cartsupportversion))
	{
	$xmlResponse->createTag("StatusMessage", array(), $message ." However, your store version is " . $version ." which hasn't been fully tested with eCC. If you'd still like to continue, click OK to continue or contact Webgility to confirm compatibility.", $root,__ENCODE_RESPONSE);
		$xml= $xmlResponse->generate();
		return $xml;
	}
	else
	{
	$xmlResponse->createTag("StatusMessage", array(), $message, $root,__ENCODE_RESPONSE);
		$xml= $xmlResponse->generate();
		return $xml;
	}
	
	}
	else
	{
			$xmlResponse->createTag("StatusMessage", array(), $message ." However, eCC is unable to detect your store version. If you'd still like to continue, click OK to continue or contact Webgility to confirm compatibility.", $root,__ENCODE_RESPONSE);
		$xml= $xmlResponse->generate();
		return $xml;

	}
		
	}

	//$xml= $xmlResponse->generate();
	//return $xml;
}

function isAuthorized($username,$password)
{
	$objNew = new Mage_Webgility_Model_Ecc_Run();
	$xmlResponse = new xml_doc();
	$xmlResponse->version='1.0';
	$xmlResponse->encoding='UTF-8';
	
	$root = $xmlResponse->createTag("RESPONSE", array('Version'=>'1.0'));
	
	$status =  $objNew->CheckUser($username,$password);

	if($status!="0")
	{ //login name invalid
		if($status=="1"){
		$xmlResponse->createTag("StatusCode", array(), "1", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid login. Authorization failed", $root, __ENCODE_RESPONSE);
		}
		if($status=="2"){ //password invalid
		$xmlResponse->createTag("StatusCode", array(), "2", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid password. Authorization failed", $root, __ENCODE_RESPONSE);
		}
		return $xmlResponse->generate();
		exit; 
	}

}

function getCompanyInfo($username,$password)
{
	$storeId=getDefaultStore();
	$xmlResponse = new xml_doc();
	$xmlResponse->version='1.0';
	$xmlResponse->encoding='UTF-8';	
	$root = $xmlResponse->createTag("RESPONSE", array('Version'=>'1.0'));	
	$objNew = new Mage_Webgility_Model_Ecc_Run();
	$status =  $objNew->CheckUser($username,$password);

	if($status!="0"){ //login name invalid
		if($status=="1"){
		$xmlResponse->createTag("StatusCode", array(), "1", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid login. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
		if($status=="2"){ //password invalid
		$xmlResponse->createTag("StatusCode", array(), "2", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid password. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
	}
	
	$config = $objNew->getStoreDetails();	
	
	//$config = $config->toArray();	
	//print_r($config);
	//exit;
//echo $regions = Mage::getResourceModel('directory/region_collection')->addRegionNameFilter($config['shipping']['origin']['region_id'])->load();
//	echo $region = Mage::getModel('directory/region')->load($config['shipping']['origin']['region_id']);

if(isset($config['shipping']['origin']['region_id']))
{

 $origRegionCode = Mage::getModel('directory/region')->load($config['shipping']['origin']['region_id'])->getCode();
 }
 else
 {
 $origRegionCode ="";
 }
 if(isset($config['shipping']['origin']['country_id']))
{
  $country = Mage::getModel('directory/country')->load($config['shipping']['origin']['country_id'])->getIso2Code();
  }
  else
  {
  $country ="";
  }
	//exit;

	$companyNode = $xmlResponse->createTag("Company", array(), '', $root);
	$xmlResponse->createTag("StatusCode", array(), "0", $companyNode, __ENCODE_RESPONSE);
	$xmlResponse->createTag("StatusMessage", array(), "All Ok", $companyNode, __ENCODE_RESPONSE);
	
	$xmlResponse->createTag("StoreId",  array(), 1, $companyNode, __ENCODE_RESPONSE);
	$xmlResponse->createTag("StoreName",array(), $config['storeName'], $companyNode, __ENCODE_RESPONSE);
	$xmlResponse->createTag("Address",array(), htmlspecialchars('', ENT_NOQUOTES),$companyNode, __ENCODE_RESPONSE);
	if(isset($config['shipping']['origin']['city']))
{
	$xmlResponse->createTag("City",array(), htmlspecialchars($config['shipping']['origin']['city'], ENT_NOQUOTES), $companyNode, __ENCODE_RESPONSE);
	}
	else
	{
	$xmlResponse->createTag("City",array(), '', $companyNode, __ENCODE_RESPONSE);
	
	}
	$xmlResponse->createTag("State",     array(), htmlspecialchars($origRegionCode, ENT_NOQUOTES), $companyNode, __ENCODE_RESPONSE); //date
	$xmlResponse->createTag("Country",     array(), $country, $companyNode, __ENCODE_RESPONSE); //time
	if(isset($config['shipping']['origin']['postcode']))
{
	$xmlResponse->createTag("Zipcode", array(), htmlspecialchars($config['shipping']['origin']['postcode'], ENT_NOQUOTES),$companyNode, __ENCODE_RESPONSE);
	}
	else
	{
	$xmlResponse->createTag("Zipcode", array(), '',$companyNode, __ENCODE_RESPONSE);
	}
	$xmlResponse->createTag("Phone", array(), htmlspecialchars('', ENT_NOQUOTES), $companyNode, __ENCODE_RESPONSE);
	$xmlResponse->createTag("Fax", array(), htmlspecialchars('', ENT_NOQUOTES), $companyNode, __ENCODE_RESPONSE);
	$xmlResponse->createTag("Website", array(), htmlspecialchars(Mage::getStoreConfig('web/unsecure/base_url'), ENT_NOQUOTES), $companyNode, __ENCODE_RESPONSE);
	
	return $xmlResponse->generate();
}

#
# function to return the store Manufacturer list so synch with QB inventory
#
function getManufacturers($username,$password,$storeId=1)
{
	$storeId = getDefaultStore();
	//echo $storeId;
	$xmlResponse = new xml_doc();
	$xmlResponse->version='1.0';
	$xmlResponse->encoding='UTF-8';	
	$root = $xmlResponse->createTag("RESPONSE", array('Version'=>'1.0'));	
	$objNew = new Mage_Webgility_Model_Ecc_Run();
	$status =  $objNew->CheckUser($username,$password);
	if($status!="0"){ //login name invalid
		if($status=="1"){
		$xmlResponse->createTag("StatusCode", array(), "1", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid login. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
		if($status=="2"){ //password invalid
		$xmlResponse->createTag("StatusCode", array(), "2", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid password. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
	}
	//else
	//{
	$xmlResponse->createTag("StatusCode", array(), "0", $root, __ENCODE_RESPONSE);
	$xmlResponse->createTag("StatusMessage", array(), "All Ok", $root, __ENCODE_RESPONSE);
	
	//}
	$pMethodNodes = $xmlResponse->createTag("Manufacturers", array(), '', $root);
	$man = $objNew->getmanufacturers();	
	if(count($man['items'])>0)
	{
			for($i=0;$i < count($man['items']);$i++)
		{
		//$iInfo = parseSpecCharsA($iInfo);
		$pMethodNode = $xmlResponse->createTag("Manufacturer",    array(),'', $pMethodNodes, __ENCODE_RESPONSE);
		$xmlResponse->createTag("ManufacturerId",array(),$man['items'][$i]['option_id'], $pMethodNode, __ENCODE_RESPONSE);
		$xmlResponse->createTag("Name",array(),htmlentities($man['items'][$i]['value']), $pMethodNode, __ENCODE_RESPONSE);
		}
	}
	//}
	return $xmlResponse->generate();

	
}


function getPaymentMethods($username,$password,$storeId=1)
{
		$xmlResponse = new xml_doc();
	$xmlResponse->version='1.0';
	$xmlResponse->encoding='UTF-8';	
	$root = $xmlResponse->createTag("RESPONSE", array('Version'=>'1.0'));	
	$objNew = new Mage_Webgility_Model_Ecc_Run();
	$status =  $objNew->CheckUser($username,$password);

	if($status!="0"){ //login name invalid
		if($status=="1"){
		$xmlResponse->createTag("StatusCode", array(), "1", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid login. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
		if($status=="2"){ //password invalid
		$xmlResponse->createTag("StatusCode", array(), "2", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid password. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
	}
	else
	{
	$xmlResponse->createTag("StatusCode", array(), "0", $root, __ENCODE_RESPONSE);
	$xmlResponse->createTag("StatusMessage", array(), "All Ok", $root, __ENCODE_RESPONSE);
	
	}
	$config = $objNew->getPaymentArray(1);		
	$pMethodNodes = $xmlResponse->createTag("PaymentMethods", array(), '', $root, __ENCODE_RESPONSE);
	
	$xmlResponse->createTag("StatusCode", array(), "0", $pMethodNodes, __ENCODE_RESPONSE);
	$xmlResponse->createTag("StatusMessage", array(), "All Ok", $pMethodNodes, __ENCODE_RESPONSE);
	$i=1;
	foreach($config as $k=>$v)
	{
	if($config[$k]['value']!='' && $config[$k]['label']!='')
	{
				$pMethodNode = $xmlResponse->createTag("PaymentMethod",    array(), '', $pMethodNodes, __ENCODE_RESPONSE);

			$xmlResponse->createTag("MethodId", array(), $i, $pMethodNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("Method", array(), $config[$k]['label'], $pMethodNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("Detail", array(), $config[$k]['value'], $pMethodNode, __ENCODE_RESPONSE);
			}
			$i++;
				
	}
	return $xmlResponse->generate();

}



function getShippingMethods($username,$password,$storeId=1)
{
	$xmlResponse = new xml_doc();
	$xmlResponse->version='1.0';
	$xmlResponse->encoding='UTF-8';	
	$root = $xmlResponse->createTag("RESPONSE", array('Version'=>'1.0'));	
	$objNew = new Mage_Webgility_Model_Ecc_Run();
	$status =  $objNew->CheckUser($username,$password);

	if($status!="0"){ //login name invalid
		if($status=="1"){
		$xmlResponse->createTag("StatusCode", array(), "1", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid login. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
		if($status=="2"){ //password invalid
		$xmlResponse->createTag("StatusCode", array(), "2", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid password. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
	}
	else
	{
	$xmlResponse->createTag("StatusCode", array(), "0", $root, __ENCODE_RESPONSE);
	$xmlResponse->createTag("StatusMessage", array(), "All Ok", $root, __ENCODE_RESPONSE);
	
	}
	$carriers = $objNew->_getshippingMethods($storeId);	
	$shippingNodes = $xmlResponse->createTag("ShippingMethods", array(), '', $root);
	//print_r($carriers);
	if(is_array($carriers)) 
	{
	//print_r($carriers);
		//$xmlResponse->createTag("StatusCode", array(), "0", $shippingNodes, __ENCODE_RESPONSE);
  		//$xmlResponse->createTag("StatusMessage", array(), "All Ok", $shippingNodes, __ENCODE_RESPONSE);
		foreach($carriers as $k=>$v)
		{
		if($carriers[$k]['value']!="")
		{
		$shippingNode = $xmlResponse->createTag("ShippingMethod", array(), '', $shippingNodes, __ENCODE_RESPONSE);
		$xmlResponse->createTag("Carrier", array(), $carriers[$k]['label'], $shippingNode, __ENCODE_RESPONSE);
		$methodsNode = $xmlResponse->createTag("Methods", array(), '', $shippingNode);
		for($i=0;$i < count($carriers[$k]['value']);$i++)
		{
//		$label = explode(" ",$carriers[$k]['value'][$i]['label']);
		$xmlResponse->createTag("Shipping", array('active' => 'Y'), $carriers[$k]['value'][$i]['label'], $methodsNode, __ENCODE_RESPONSE);
		}
		}
	
		}
	}
	return $xmlResponse->generate();
}


function getTaxes($username,$password)
{
	$storeId=getDefaultStore();
	$xmlResponse = new xml_doc();
	$xmlResponse->version='1.0';
	$xmlResponse->encoding='UTF-8';	
	$root = $xmlResponse->createTag("RESPONSE", array('Version'=>'1.0'));	
	$objNew = new Mage_Webgility_Model_Ecc_Run();
	$status =  $objNew->CheckUser($username,$password);

	if($status!="0"){ //login name invalid
		if($status=="1"){
		$xmlResponse->createTag("StatusCode", array(), "1", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid login. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
		if($status=="2"){ //password invalid
		$xmlResponse->createTag("StatusCode", array(), "2", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid password. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
	}
	else
	{
	$xmlResponse->createTag("StatusCode", array(), "0", $root, __ENCODE_RESPONSE);
	$xmlResponse->createTag("StatusMessage", array(), "All Ok", $root, __ENCODE_RESPONSE);
	
	}
	$taxes = $objNew->_gettaxes($storeId);	
	

	#check for authorisation

	$pMethodNodes = $xmlResponse->createTag("Taxes", array(), '', $root);

	if($taxes)
	{
		//$xmlResponse->createTag("StatusCode", array(), "0", $pMethodNodes, __ENCODE_RESPONSE);
  	//$xmlResponse->createTag("StatusMessage", array(), "All Ok", $pMethodNodes, __ENCODE_RESPONSE);
		
		for($i=0;$i< count($taxes);$i++) 
		{
		$pMethodNode = $xmlResponse->createTag("Tax",    array(),'', $pMethodNodes, __ENCODE_RESPONSE);
		$xmlResponse->createTag("TaxID",array(),$taxes[$i]['value'], $pMethodNode, __ENCODE_RESPONSE);
		$xmlResponse->createTag("TaxName",array(),htmlentities($taxes[$i]['label'], ENT_QUOTES), $pMethodNode, __ENCODE_RESPONSE);
		}
	}
	return $xmlResponse->generate();
}


function getOrderStatus($username,$password)
{
	$storeId=getDefaultStore();
	$xmlResponse = new xml_doc();
	$xmlResponse->version='1.0';
	$xmlResponse->encoding='UTF-8';	
	$root = $xmlResponse->createTag("RESPONSE", array('Version'=>'1.0'));	
	$objNew = new Mage_Webgility_Model_Ecc_Run();
	$status =  $objNew->CheckUser($username,$password);

	if($status!="0"){ //login name invalid
		if($status=="1"){
		$xmlResponse->createTag("StatusCode", array(), "1", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid login. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
		if($status=="2"){ //password invalid
		$xmlResponse->createTag("StatusCode", array(), "2", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid password. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
	}
	else
	{
	$xmlResponse->createTag("StatusCode", array(), "0", $root, __ENCODE_RESPONSE);
	$xmlResponse->createTag("StatusMessage", array(), "All Ok", $root, __ENCODE_RESPONSE);
	
	}
	$orderStatus = $objNew->_getorderstatuses($storeId);	
	$pMethodNodes = $xmlResponse->createTag("OrderStatus", array(), '', $root);

	//$xmlResponse->createTag("StatusCode", array(), "0", $pMethodNodes, __ENCODE_RESPONSE);
	//$xmlResponse->createTag("StatusMessage", array(), "All Ok", $pMethodNodes, __ENCODE_RESPONSE);
	
	foreach($orderStatus as $id=>$val)
	{		
		$pMethodNode = $xmlResponse->createTag("Status",    array(), '', $pMethodNodes);
		$xmlResponse->createTag("StatusId", array(), $id, $pMethodNode, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusName", array(), $val, $pMethodNode, __ENCODE_RESPONSE);
	}	
	
	return $xmlResponse->generate();				

}



function addProduct($username,$password,$xmlItems)
{
	global $sql_tbl,$config; 	
	$storeId=getDefaultStore();
	
	$xmlResponse = new xml_doc();
	$xmlResponse->version='1.0';
	$xmlResponse->encoding='UTF-8';	
	$root = $xmlResponse->createTag("RESPONSE", array('Version'=>'1.0'));	
	$objNew = new Mage_Webgility_Model_Ecc_Run();
	$status =  $objNew->CheckUser($username,$password);

	if($status!="0"){ //login name invalid
		if($status=="1"){
		$xmlResponse->createTag("StatusCode", array(), "1", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid login. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
		if($status=="2"){ //password invalid
		$xmlResponse->createTag("StatusCode", array(), "2", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid password. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
	}
	else
	{
	$xmlResponse->createTag("StatusCode", array(), "0", $root, __ENCODE_RESPONSE);
	$xmlResponse->createTag("StatusMessage", array(), "All Ok", $root, __ENCODE_RESPONSE);
	
	}

	
	$xmlRequest = new xml_doc($xmlItems);
	$xmlRequest->parse();
	
	$xmlRequest->getTag(0, $_tagName, $_tagAttributes, $_tagContents, $_tagTags, __ENCODE_RESPONSE);
	if (strtoupper(trim($_tagName)) != 'REQUEST') {
	$xmlResponse->createTag("StatusCode", array(), "9997", $root, __ENCODE_RESPONSE);
	$xmlResponse->createTag("StatusMessage", array(), "Unknown request or request not in proper format", $root, __ENCODE_RESPONSE);
	return $xmlResponse->generate();
	}
	if (count($_tagTags) == 0) {
	$xmlResponse->createTag("StatusCode", array(), "9996", $root, __ENCODE_RESPONSE);
	$xmlResponse->createTag("StatusMessage", array(), "REQUEST tag(s) doesnt have correct input format", $root, __ENCODE_RESPONSE);
	return $xmlResponse->generate();
	}

	 $itemsTag = $xmlRequest->getChildByName(0, "ITEMS");
	 $xmlRequest->getTag($itemsTag, $_tagName, $_tagAttributes, $_tagContents, $_tagTags, __ENCODE_RESPONSE);
	 
	 $items = $xmlResponse->createTag("Items", array(), '', $root);
	 $itemsCount = 0;
	 $itemsProcessed = 0;
	
	 // Go throught items

	 $itemsCount = 0;
	 $_err_message_arr = Array();
	
	 foreach($_tagTags as $k=>$itemTag)
	 {
	 
 			$itemsCount++;
			
			$productIdTag = $xmlRequest->getChildByName($itemTag, "CATID");
			$xmlRequest->getTag($productIdTag,  $_tagName, $_tagAttributes, $categoryid, $_pTags, __ENCODE_RESPONSE);
			
			$skuTag = $xmlRequest->getChildByName($itemTag, "ITEMCODE");
			$xmlRequest->getTag($skuTag, $_tagName, $_tagAttributes, $productcode, $_sTags, __ENCODE_RESPONSE);

			$productNameTag = $xmlRequest->getChildByName($itemTag, "ITEMNAME");
			$xmlRequest->getTag($productNameTag, $_tagName, $_tagAttributes, $product, $_pnTags, __ENCODE_RESPONSE);

			$qtyTag = $xmlRequest->getChildByName($itemTag, "ITEMDESC");
			$xmlRequest->getTag($qtyTag, $_tagName, $_tagAttributes, $descr, $_qTags, __ENCODE_RESPONSE);

			$priceTag = $xmlRequest->getChildByName($itemTag, "FREESHIPPING");
			$xmlRequest->getTag($priceTag, $_tagName, $_tagAttributes, $free_shipping, $_pTags, __ENCODE_RESPONSE);

			$priceTag = $xmlRequest->getChildByName($itemTag, "TAXEXEMPT");
			$xmlRequest->getTag($priceTag, $_tagName, $_tagAttributes, $free_tax, $_pTags, __ENCODE_RESPONSE);

			$priceTag = $xmlRequest->getChildByName($itemTag, "TAXID");
			$xmlRequest->getTag($priceTag, $_tagName, $_tagAttributes, $tax_id, $_pTags, __ENCODE_RESPONSE);

			$priceTag = $xmlRequest->getChildByName($itemTag, "MANUFACTURERID");
			$xmlRequest->getTag($priceTag, $_tagName, $_tagAttributes, $manufacturerid, $_pTags, __ENCODE_RESPONSE);			
			
			$priceTag = $xmlRequest->getChildByName($itemTag, "QUANTITY");
			$xmlRequest->getTag($priceTag, $_tagName, $_tagAttributes, $avail_qty, $_pTags, __ENCODE_RESPONSE);
			
			$priceTag = $xmlRequest->getChildByName($itemTag, "UNITPRICE");
			$xmlRequest->getTag($priceTag, $_tagName, $_tagAttributes, $price, $_pTags, __ENCODE_RESPONSE);
			
			$priceTag = $xmlRequest->getChildByName($itemTag, "WEIGHT");
			$xmlRequest->getTag($priceTag, $_tagName, $_tagAttributes, $weight, $_pTags, __ENCODE_RESPONSE);
			
			
			## static/fixed values for product
			$forsale = "Y";
			$provider = "master";
			$list_price = $price;
			$fulldescr =$descr;
			$min_amount = "1";
			//$manufacturerid = "";
			$variantid="0";

			/*$productcode = "sku101-test";
			$product = "Test101 product";
			$provider = "master";
			$list_price = "121";
			$descr = "test desc for test";
			$fulldescr = "test full desc";
			$avail_qty = "11";
			$forsale = "Y";
			$free_shipping = "Y";
			$discount_avail = "Y";
			$weight = "1";
			$min_amount = "1";
			$free_tax = "Y";
			$manufacturerid = "";
			$price="120";
			$variantid="0";
			$categoryid="22";*/
//$duplicaterecord = $objNew->getduplicaterecord($product,$productcode);
			//exit;			
			/*$Product = $objNew->addproduct($storeId);
			$Product->setCategoryIds($categoryid);
				$data = array();
				$data['name'] = $product;//'testp';
				$data['sku'] = $productcode;//'testp114512';
				$data['manufacturer'] = $manufacturerid;//'''122';
				$data['description'] = $descr;//'test';
				$data['stock_data']['qty'] = $avail_qty;//'58';
				$data['attribute_set_id']='1';
				$data['price'] = $price;//'100';
				$data['tax_class_id'] =$tax_id;// '1';
				$data['weight'] = $weight;//'1';
				$data['stock_data']['use_config_manage_stock'] = 1;
				$data['visibility'] = '4';
				$data['status'] = '1';


				$Product->addData($data);
				 $Product->save();
				    $productId = $Product->getId();*/

			
			//$query = "select count(*) as cnt from $sql_tbl[products] where productcode='".addslashes(html_entity_decode($productcode))."'";
			//$sku_result = db_query($query);
			//$result_row = db_fetch_array($sku_result);

			$entityTypeId = Mage::getModel('eav/entity')
			->setType('catalog_product')
			->getTypeId();
			
			$attributeSet = Mage::getResourceModel('eav/entity_attribute_set_collection')
			->setEntityTypeFilter($entityTypeId)
			->addFilter('attribute_set_name', 'Default')
			->getLastItem();
			$attributeSet = $attributeSet->toArray();

			
			if ($objNew->getduplicaterecord($product,$productcode)==0)
			{
			$Product = $objNew->addproduct($storeId);
			$Product->setCategoryIds($categoryid);
				$data = array();
				$data['name'] = $product;//'testp';
				$data['sku'] = $productcode;//'testp114512';
				$data['manufacturer'] = $manufacturerid;//'''122';
				$data['description'] = $descr;//'test';
				$data['qty'] = $avail_qty;//'test';
				//$data['stock_data']['qty'] = $avail_qty;//'58';
				
				
				
				$data['attribute_set_id']=$attributeSet['attribute_set_id'];
				
				$data['price'] = $price;//'100';
				$data['tax_class_id'] =$tax_id;// '1';
				$data['weight'] = $weight;//'1';
				$data['stock_data']['use_config_manage_stock'] = 1;
				$data['stock_data']['is_in_stock'] = 1;
				$data['visibility'] = '4';
				$data['status'] = '1';
				$Product->addData($data);
				 $Product->save();
				    $productId = $Product->getId();
					$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
    $stockItem->addQty($avail_qty);
	$stockItem->setIsInStock(true);
     $stockItem->save();
			#
			# Insert new product into the database and get its productid
			#
			$item= $xmlResponse->createTag("Item", array(),'', $items);
			$xmlResponse->createTag("Status", array(),'Success', $item, __ENCODE_RESPONSE);
			$xmlResponse->createTag("ProductID", array(),$productId, $item, __ENCODE_RESPONSE);
			$xmlResponse->createTag("Sku", array(),htmlentities($productcode), $item, __ENCODE_RESPONSE);
		$xmlResponse->createTag("ProductName", array(),htmlentities($product), $item, __ENCODE_RESPONSE);

			}
			else
			{
			$item= $xmlResponse->createTag("Item", array(),'', $items);
			$xmlResponse->createTag("Status", array(),'Duplicate product code exists', $item, __ENCODE_RESPONSE);
			$xmlResponse->createTag("ProductID", array(),'', $item, __ENCODE_RESPONSE);
			$xmlResponse->createTag("Sku", array(),htmlentities($productcode), $item, __ENCODE_RESPONSE);
			$xmlResponse->createTag("ProductName", array(),htmlentities($product), $item, __ENCODE_RESPONSE);

			
			}
			
			
		} //End of Items foreach loop

	return $xmlResponse->generate();

}


function synchronizeItems($username,$password,$xmlItems)
{
	$storeId=getDefaultStore();	
	$xmlResponse = new xml_doc();
	$xmlResponse->version='1.0';
	$xmlResponse->encoding='UTF-8';	
	$root = $xmlResponse->createTag("RESPONSE", array('Version'=>'1.0'));	
	$objNew = new Mage_Webgility_Model_Ecc_Run();
	$status =  $objNew->CheckUser($username,$password);

	if($status!="0"){ //login name invalid
		if($status=="1"){
		$xmlResponse->createTag("StatusCode", array(), "1", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid login. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
		if($status=="2"){ //password invalid
		$xmlResponse->createTag("StatusCode", array(), "2", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid password. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
	}
	else
	{
	$xmlResponse->createTag("StatusCode", array(), "0", $root, __ENCODE_RESPONSE);
	$xmlResponse->createTag("StatusMessage", array(), "All Ok", $root, __ENCODE_RESPONSE);
	
	}
	
	
	$xmlRequest = new xml_doc($xmlItems);
	$xmlRequest->parse();
	
	$xmlRequest->getTag(0, $_tagName, $_tagAttributes, $_tagContents, $_tagTags, __ENCODE_RESPONSE);
	if (strtoupper(trim($_tagName)) != 'REQUEST') {
	$xmlResponse->createTag("StatusCode", array(), "9997", $root, __ENCODE_RESPONSE);
	$xmlResponse->createTag("StatusMessage", array(), "Unknown request or request not in proper format", $root, __ENCODE_RESPONSE);
	return $xmlResponse->generate();
	}
	if (count($_tagTags) == 0) {
	$xmlResponse->createTag("StatusCode", array(), "9996", $root, __ENCODE_RESPONSE);
	$xmlResponse->createTag("StatusMessage", array(), "REQUEST tag(s) doesnt have correct input format", $root, __ENCODE_RESPONSE);
	return $xmlResponse->generate();
	}
	
	 $itemsTag = $xmlRequest->getChildByName(0, "ITEMS");
	 $xmlRequest->getTag($itemsTag, $_tagName, $_tagAttributes, $_tagContents, $_tagTags, __ENCODE_RESPONSE);
	 
	$items = $xmlResponse->createTag("Items", array(), '', $root);
	 $itemsCount = 0;
	 $itemsProcessed = 0;
	
	 // Go throught items
	 $itemsCount = 0;
	 $_err_message_arr = Array();
	
	 foreach($_tagTags as $k=>$itemTag)
	 {
 			$itemsCount++;
			$productIdTag = $xmlRequest->getChildByName($itemTag, "PRODUCTID");
			$xmlRequest->getTag($productIdTag,  $_tagName, $_tagAttributes, $productID, $_pTags, __ENCODE_RESPONSE);
			
			$skuTag = $xmlRequest->getChildByName($itemTag, "SKU");
			$xmlRequest->getTag($skuTag, $_tagName, $_tagAttributes, $sku, $_sTags, __ENCODE_RESPONSE);

			$productNameTag = $xmlRequest->getChildByName($itemTag, "PRODUCTNAME");
			$xmlRequest->getTag($productNameTag, $_tagName, $_tagAttributes, $productName, $_pnTags, __ENCODE_RESPONSE);

			$qtyTag = $xmlRequest->getChildByName($itemTag, "QTY");
			$xmlRequest->getTag($qtyTag, $_tagName, $_tagAttributes, $qty, $_qTags, __ENCODE_RESPONSE);

			$priceTag = $xmlRequest->getChildByName($itemTag, "PRICE");
			$xmlRequest->getTag($priceTag, $_tagName, $_tagAttributes, $price, $_pTags, __ENCODE_RESPONSE);
			
			$varientsTag = $xmlRequest->getChildByName($itemTag, "VARIENTS");
			$xmlRequest->getTag($varientsTag, $_tagName, $_tagAttributes, $vcnt, $_varientsTags, __ENCODE_RESPONSE);
			
			
			$item= $xmlResponse->createTag("Item", array(),'', $items);
			
			$xmlResponse->createTag("ProductID", array(),$productID, $item, __ENCODE_RESPONSE);
			$xmlResponse->createTag("Sku", array(),htmlentities($sku), $item, __ENCODE_RESPONSE);
			$xmlResponse->createTag("ProductName", array(), htmlentities($productName), $item, __ENCODE_RESPONSE);
			$updated_attrib=0;
			if ($updated_attrib ==0)
			{
			
			$status ="Success";	
			$Product = $objNew->editproduct($storeId,$productID);
			$Productattribute = $Product->toArray();						
			
			$data = array();
			$data = $Productattribute;
			//print_r($data);
			//die();			
			 	  		
			if ($qty!="")
				{	
				$version = getVersion();
				
				if($version=='1.2.1' || $version=='1.3.1.1' || $version=='1.3.1' || $version=='1.3.0' || $version=='1.3.2.2' )
				 {
					$stockItem = Mage::getModel('cataloginventory/stock_item');							 				$stockItem->load($productID);
					$stockItem->setQty($qty);
					$stockItem->save();
				}else if ($version=='1.3.2.1' || $version=='1.3.2')
				{
				$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productID);
				$stockItem->setQty($qty);
   				$stockItem->save();
				}else 
				{
				
					$stockItem = Mage::getModel('cataloginventory/stock_item');							 					$stockItem->load($productID);
					$stockItem->setQty($qty);
					$stockItem->save();
				
				}	 
			}
			if ($price!="")
				{	
				  	 $version = getVersion();
					 if($version=='1.2.1')
					 {
						 $Product->setPrice($price);
						 $Product->save();
					 }else if ($version=='1.3.1.1' || $version=='1.3.2.1'  || $version=='1.3.2.2' || $version=='1.3.2' || $version=='1.3.0' || $version=='1.3.1')
					 {
						Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID); 					
						$p = new Mage_Catalog_Model_Product();
						// 16 is some product id number
						$p->load($productID);
						$p->price = $price;
						$p->save();
					}else
					{
					
						Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID); 					
						$p = new Mage_Catalog_Model_Product();
						// 16 is some product id number
						$p->load($productID);
						$p->price = $price;
						$p->save();					
					
					}
				}
				  
				$itemsProcessed++; 	
				
				$xmlResponse->createTag("ItemUpdateStatus", array(),$status, $item, __ENCODE_RESPONSE);
				$xmlResponse->createTag("Varients", array(),'', $item, __ENCODE_RESPONSE);				
			}
			else if($updated_attrib == $k1+1)
			{
				$itemsProcessed++; 
			}
			
			$xmlResponse->createTag("Options", array(),'', $item);

	 }
	
	return $xmlResponse->generate();	
}

function getItems($username,$password,$start_item_no=0,$limit=500)
{

	$storeId=getDefaultStore();
	//echo $storeId;
	$xmlResponse = new xml_doc();
	$xmlResponse->version='1.0';
	$xmlResponse->encoding='UTF-8';	
	$root = $xmlResponse->createTag("RESPONSE", array('Version'=>'1.0'));	
	$objNew = new Mage_Webgility_Model_Ecc_Run();
	$status =  $objNew->CheckUser($username,$password);

	if($status!="0"){ //login name invalid
		if($status=="1"){
		$xmlResponse->createTag("StatusCode", array(), "1", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid login. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
		if($status=="2"){ //password invalid
		$xmlResponse->createTag("StatusCode", array(), "2", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid password. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
	}
	
	//$_orders = $objNew->getProduct($storeId,$start_item_no,$limit);	
	//	exit;
	$itemsNode = $xmlResponse->createTag("Items", array(), '', $root);

/*	$items_query_raw = "SELECT distinct p.productid,p.productcode,p.product,p.descr,p.avail,p.list_price,p.weight,
											p.low_avail_limit,p.free_shipping,p.discount_avail,p.shipping_freight,p.free_tax,xcart_pricing.price
											FROM xcart_pricing, xcart_products p
											WHERE xcart_pricing.productid = p.productid AND p.product_type <> 'C' AND p.forsale <> 'B'
											AND p.forsale = 'Y' Group BY p.productid";*/
											

	/*$items_query = db_query($items_query_raw);
	$total_record = db_num_rows($items_query);*/
	
	$items_query_product = $objNew->getProduct($storeId,$start_item_no,$limit);	
	//$items_query_product = $items_query_product->toArray();
	
 
		
	if(count($items_query_product)>0){
	
	$count_query_product = $objNew->CountProduct($storeId);	
	$xmlResponse->createTag("StatusCode", array(), "0", $itemsNode, __ENCODE_RESPONSE);
  	$xmlResponse->createTag("StatusMessage", array(), "All Ok", $itemsNode, __ENCODE_RESPONSE);
	$xmlResponse->createTag("RecordCount", array(), count($count_query_product), $itemsNode, __ENCODE_RESPONSE);

		foreach ($items_query_product->getItems() as $iInfo11) {
		
		
		
		$options = $objNew->getoptions($iInfo11);
	
		//exit;
		$iInfo = $iInfo11->toArray();
		if($iInfo['type_id']=='simple')
		{
		
	//	 print_r($iInfo);
		// exit;
		//echo $iInfo['weight'];
//		 exit;
		// $iInfo = $iInfo->toArray();
			$itemNode = $xmlResponse->createTag("Item",    array(), '',    $itemsNode);
			$desc=addslashes(htmlspecialchars(substr($iInfo['description'],0,4000),ENT_QUOTES));			
//			$iInfo['price'] = htmlspecialchars($iInfo['price'],ENT_QUOTES);
//			$iInfo = parseSpecCharsA($iInfo);
 //print_r($iInfo);
 /*if(in_array('price',$iInfo))
			{
			echo "YES";
			}*/
 //number_format($iInfo['price'],2);
//		$price_info =  $iInfo['price'];
//		 exit;
//	echo $iInfo->price;

	 //exit;
	 $stockItem =  Mage::getModel('cataloginventory/stock_item')->loadByProduct($iInfo['entity_id']);
	$stockItem=$stockItem->toArray();
			$xmlResponse->createTag("ItemID",       array(), $iInfo['entity_id'], $itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("ItemCode",       array(), htmlspecialchars($iInfo['sku'],ENT_QUOTES), $itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("ItemDescription",array(),htmlspecialchars($iInfo['name'],ENT_QUOTES) , $itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("ItemShortDescr",array(), addslashes($desc), $itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("Quantity",       array(), $stockItem['qty'],  $itemNode, __ENCODE_RESPONSE);
			/*if(in_array('price',$iInfo))
			{*/
			$xmlResponse->createTag("UnitPrice", array(),$iInfo11->getPrice(),  $itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("ListPrice", array(),$iInfo11->getPrice(), $itemNode, __ENCODE_RESPONSE);
			/*}
			else
			{
			$xmlResponse->createTag("UnitPrice",      array(), "0.00",  $itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("ListPrice",      array(), "0.00", $itemNode, __ENCODE_RESPONSE);
			
			}*/
						

			$xmlResponse->createTag("Weight",      		array(), $iInfo11->getWeight(), $itemNode, __ENCODE_RESPONSE);
			
			$xmlResponse->createTag("LowQtyLimit",    array(), $stockItem['min_qty'], $itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("FreeShipping",   array(), "N", $itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("Discounted",     array(), "", $itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("shippingFreight",array(), "", $itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("Weight_Symbol",  array(), "lbs",$itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("Weight_Symbol_Grams", array(), "453.6",$itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("TaxExempt", array(), "N",$itemNode, __ENCODE_RESPONSE);
$iVariants = $xmlResponse->createTag("ItemVariants", array(), '',$itemNode, __ENCODE_RESPONSE);
			#
			# get item variants if any
			#
//			$items_query_raw = "SELECT v.productcode,v.avail,v.weight,xcart_pricing.price, v.variantid
	//								FROM xcart_pricing, xcart_variants v 													WHERE v.variantid = xcart_pricing.variantid AND v.productid = '".$iInfo['productid']."'";
			/*$items_query_raw = "SELECT v.productcode,v.avail,v.weight,xcart_pricing.price, v.variantid
									FROM ".$sql_tbl['pricing'].", xcart_variants v 													WHERE v.variantid = ".$sql_tbl['pricing'].".variantid AND v.productid = '".$iInfo['productid']."'";

			$itemsv_query = db_query($items_query_raw);

			$iVariants = $xmlResponse->createTag("ItemVariants", array(), '',$itemNode);

			$c=1; //counter
			while ($ivInfo = db_fetch_array($itemsv_query)) 
			{
				$ivInfo = parseSpecCharsA($ivInfo);
				$iVariant = $xmlResponse->createTag("ItemVariant", array(), '',$iVariants, __ENCODE_RESPONSE); 								
				$xmlResponse->createTag("ItemCode", array(), $ivInfo['productcode'], $iVariant, __ENCODE_RESPONSE);
				$xmlResponse->createTag("VarientID", array(), $ivInfo['variantid'],  $iVariant, __ENCODE_RESPONSE);
				$xmlResponse->createTag("Quantity", array(), $ivInfo['avail'],  $iVariant, __ENCODE_RESPONSE);
				$xmlResponse->createTag("UnitPrice",array(), $ivInfo['price'],  $iVariant, __ENCODE_RESPONSE);
				$xmlResponse->createTag("Weight",   array(), $ivInfo['weight'], $iVariant, __ENCODE_RESPONSE);
				$c++;
			}
			#
			# get item options if any
			#

			$items_query_raw = "SELECT class,option_name,price_modifier,optionid 
								FROM xcart_classes , xcart_class_options,xcart_variant_items
								WHERE xcart_classes.classid = xcart_class_options.classid AND xcart_classes.productid = '".$iInfo['productid']."'";*/
			#Made the New Query								
								
							

//			$itemso_query = db_query($items_query_raw);
			$iOptions = $xmlResponse->createTag("ItemOptions", array(), '', $itemNode, __ENCODE_RESPONSE);
			if(is_array($options) && count($options)>0)
			{

			foreach($options as $ioInfo)
			{
				$ioInfo = parseSpecCharsA($ioInfo);
				$xmlResponse->createTag("ItemOption", array("ID"=>$ioInfo['option_type_id'],"Value"=>htmlspecialchars($ioInfo['title'],ENT_QUOTES),"Name"=>htmlspecialchars($ioInfo['option_title'],ENT_QUOTES)), "",        $iOptions, __ENCODE_RESPONSE);
			}

			}
			}
		} // end items
	}
	return $xmlResponse->generate();

}


function UpdateOrdersShippingStatus($username,$password,$xmlOrders,$emailAlert='N'){
$storeId=getDefaultStore();
 	$xmlResponse = new xml_doc();
	$xmlResponse->version='1.0';
	$xmlResponse->encoding='UTF-8';	
	$root = $xmlResponse->createTag("RESPONSE", array('Version'=>'1.0'));	
	$objNew = new Mage_Webgility_Model_Ecc_Run();
	$status =  $objNew->CheckUser($username,$password);
	


	if($status!="0"){ //login name invalid
		if($status=="1"){
		$xmlResponse->createTag("StatusCode", array(), "1", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid login. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
		if($status=="2"){ //password invalid
		$xmlResponse->createTag("StatusCode", array(), "2", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid password. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
	}

  $xmlRequest = new xml_doc($xmlOrders);

  $xmlRequest->parse();
  
  $xmlRequest->getTag(0, $_tagName, $_tagAttributes, $_tagContents, $_tagTags, __ENCODE_RESPONSE);
  if (strtoupper(trim($_tagName)) != 'REQUEST') {
		$xmlResponse->createTag("StatusCode", array(), "9997", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Unknown request or request not in proper format", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
  }
  if (count($_tagTags) == 0) {
		$xmlResponse->createTag("StatusCode", array(), "9996", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "REQUEST tag(s) doesnt have correct input format", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
  }


	$ordersTag = $xmlRequest->getChildByName(0, "ORDERS");
	$xmlRequest->getTag($ordersTag, $_tagName, $_tagAttributes, $_tagContents, $_tagTags, __ENCODE_RESPONSE);


	if (count($_tagTags) == 0) $no_orders = true; else $no_orders = false;
	$xmlResponse->createTag("StatusCode", array(), $no_orders?"1000":"0", $root, __ENCODE_RESPONSE);
	$xmlResponse->createTag("StatusMessage", array(), $no_orders?"No new orders.":"All Ok", $root, __ENCODE_RESPONSE);
	if ($no_orders){
		return $xmlResponse->generate();
	}

	$ordersNode = $xmlResponse->createTag("Orders", array(), '', $root);
	foreach($_tagTags as $k=>$v){
		$xmlRequest->getTag($v, $_tagName, $_tagAttributes, $_tagContents, $_orderTags, __ENCODE_RESPONSE);
		$orderNode = $xmlResponse->createTag("Order",  array(), '',     $ordersNode, __ENCODE_RESPONSE);

		foreach($_orderTags as $k1=>$v1){
			$xmlRequest->getTag($v1, $_tagName, $_tagAttributes, $_tagContents, $_tempTags, __ENCODE_RESPONSE);
			$order[strtoupper($_tagName)] = $_tagContents;
			if (strtoupper($_tagName)=='ORDERID')
				$xmlResponse->createTag('OrderID',  array(), $_tagContents,     $orderNode, __ENCODE_RESPONSE);
		}
		 $carrier_names = array (
			"Processing" => "processing",
			"Pending" => "pending",
			"Pending PayPal" => "pending_paypal",
			"On Hold" => "holded",
			"Complete" => "complete",
			"Closed" => "closed",
			"Canceled" => "canceled"
			
			
		);	
		
		$status = $order['ORDERSTATUS'];
		//echo $carrier_names[$status];
//		exit;
		$order['ORDERSTATUS'] = $carrier_names[$status];
		//echo $order['ORDERSTATUS'];
		
	
		
		$info = "\nOrder shipped ";
			
		    if ($order['SHIPPEDON']!="")
			$info .= " on ". substr($order['SHIPPEDON'],0,10);
			
			if ($order['SHIPPEDVIA']!="" || $order['SERVICEUSED']!="" )
			$info .= " via ".$order['SHIPPEDVIA']." ".$order['SERVICEUSED'];
			
			if ($order['TRACKINGNUMBER']!="")
			$info .= " with Tracking Number ".$order['TRACKINGNUMBER'].".";
			
			if ($order['ORDERNOTES']!="")			
			$info .=" \n".$order['ORDERNOTES'];
		//echo $order['ORDERID'];
	//	exit;
	
		$orders = $objNew->UpdateOrdersShippingStatus($order['ORDERID'],$storeId);
	//print_r($order);
	//exit;
	$orders_array=$orders->toArray();
	unset($orders);
	//print_r($orders_array);
	//exit;
	foreach($orders_array as $orders_el){
	 
		$current_order = Mage::getModel('sales/order')
                           ->load($orders_el['entity_id']);
				   $current_order->setState($order['ORDERSTATUS'],true);
				   $current_order->addStatusToHistory($order['ORDERSTATUS'], $info, true);

					if($emailAlert=='Y')
					{
				     $current_order->sendNewOrderEmail();
//				     $current_order->sendOrderUpdateEmail();
					 }

           			 $current_order->save();
					 //$current_order = $current_order->toArray();
				//		   print_r($current_order);
					///	   exit;
						  
//						   getState
	   			$_shipments=$current_order->getShipmentsCollection();
				  $shipments_array=$_shipments->toarray();
				  $shipmentcount = count($shipments_array);
				//  print_r($shipments_array);
				//  echo $shipmentcount;
				//  unset($shipments_array);
				  if($shipmentcount>0)
				  {
				  foreach ($_shipments as $_shipment){
				 // echo "YES";
				  $_track = Mage::getModel('sales/order_shipment_track')
                                   ->setNumber($order['TRACKINGNUMBER'])
                                   ->setCarrierCode($order['SHIPPEDVIA'])
                                   ->setTitle($order['SERVICEUSED']);
                               $_shipment->addTrack($_track);
							   $_shipment->Save();
				  }
				  
				  }
				  else
				  {
				  $RequestOrders = array("TRACKINGNUMBER"=>$order['TRACKINGNUMBER'],"SHIPPEDVIA"=>$order['SHIPPEDVIA'],"SERVICEUSED"=>$order['SERVICEUSED']);
				  if($shipment = $objNew->_initShipment($current_order,$RequestOrders))
				  {
				  
				  $shipment->register();
				  $shipment->addComment($info,false);
				  $shipment_arr = $objNew->_saveShipment($shipment);
				  }
				//  print_r($shipment_arr);
				//  exit;
				  }
				  }


		$result = 'Success';

		$xmlResponse->createTag('Status',  array(), $result, $orderNode, __ENCODE_RESPONSE);
	}
	return $xmlResponse->generate();
}


 


 
 
 
 
 


function getOrders($username,$password,$datefrom,$start_order_no,$ecc_excl_list,$order_per_response="25")
{


	$storeId=getDefaultStore();
	if(!isset($datefrom) or empty($datefrom)) $datefrom=date('m-d-Y');
	if(!isset($dateto) or empty($dateto)) $dateto=date('m-d-Y');

	$xmlResponse = new xml_doc();
	$xmlResponse->version='1.0';
	$xmlResponse->encoding='UTF-8';	
	$root = $xmlResponse->createTag("RESPONSE", array('Version'=>'1.0'));	
	$objNew = new Mage_Webgility_Model_Ecc_Run();
	$status =  $objNew->CheckUser($username,$password);

	if($status!="0"){ //login name invalid
		if($status=="1"){
		$xmlResponse->createTag("StatusCode", array(), "1", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid login. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
		if($status=="2"){ //password invalid
		$xmlResponse->createTag("StatusCode", array(), "2", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid password. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
	}
	
	$_orders = $objNew->GetOrders($datefrom,$start_order_no,$ecc_excl_list,$storeId,$order_per_response);	
	$_countorders = $objNew->GetOrdersRemained($datefrom,$start_order_no,$ecc_excl_list,$storeId);	
  	$countorders_array=$_countorders->toArray();
  if(count($countorders_array)>0)
  {
  $orders_remained = count($countorders_array);
  }
  else
  {
    $orders_remained = 0;
  }
	$xmlResponse = new xml_doc();
	$xmlResponse->version='1.0';
	$xmlResponse->encoding='UTF-8';	
	$root = $xmlResponse->createTag("RESPONSE", array('Version'=>'1.0'));	
     $orders_array=$_orders->toArray();
	 $no_orders = false;

if(count($orders_array)<=0)
{
$no_orders = true;
}

 $ordersNode = $xmlResponse->createTag("Orders", array(), '', $root);
 

 $xmlResponse->createTag("StatusCode", array(), $no_orders?"9999":"0", $ordersNode, __ENCODE_RESPONSE);
	$xmlResponse->createTag("StatusMessage", array(), $no_orders?"No Orders returned":"Total Orders:".$orders_remained,$ordersNode, __ENCODE_RESPONSE);
        
		if ($no_orders)
		{	
			return ($xmlResponse->generate()); flush; exit;
		}

        foreach ($_orders as $_order) {
		$shipments = $_order->getShipmentsCollection();
		$shipTrack = "";
            foreach ($shipments as $shipment){
                $increment_id = $shipment->getIncrementId();
                $tracks = $shipment->getTracksCollection();

                $trackingInfos=array();
                foreach ($tracks as $track){
				$track = $track->toArray();
//l				print_r($track);
//                    $trackingInfos[] = $track->getNumberDetail();
                }
                if(isset($track['number']))
				{
                $shipTrack = $track['number'];
				}

            }
				
		//print_r($shipTrack);
			

            $orders=$_order->toArray();
			
			
			//print_r($orders);
//			exit;

            $_payment=$_order->getPayment();
            $payment=$_payment->toArray();
				  
	//Mage::getModel('sales/order_shipment')->load($shipmentId);				  print_r($shipments_array);
			///print_r($payment);
			//exit;
/*               echo("<pre>");
               var_dump($payment);
               echo("</pre>");*/

            //echo $orders["created_at"];
			
			$datetime=explode(" ",$orders["created_at"] );
			
			$datetime1 = explode("-",$datetime[0]);			
			$dateCreateOrder= $datetime1[1]."-".$datetime1[2]."-".$datetime1[0];
            
			//$dateCreateOrder= $datetime[0];
			
            $timeCreateOrder= $datetime[1];
			
            $orderNode = $xmlResponse->createTag("Order", array(), '', $ordersNode,__ENCODE_RESPONSE );

			 $itemsNode  = $xmlResponse->createTag("Items",  array(), '', $orderNode,__ENCODE_RESPONSE);
			 
             $item_array = $objNew->getorderitems($orders["entity_id"],$orders["increment_id"]); 
			
			$item_array = $item_array['items'];
			
			//print_r($item_array);
		    //exit;
			/*Mage::getModel('sales/order_item')->getCollection()
                  ->addAttributeToSelect('*')
                  ->setOrderFilter($orders["entity_id"])
               //   ->setOrder('000000001')
                  ->load();*/

//            $item_array=$item->toArray();

                                       /////////////////////////////////////
                                       //   items info
                                       /////////////////////////////////////
									   

            foreach($item_array as $iInfo) {			
			
			//print_r($iInfo);			
			
			if(intval($iInfo["qty_ordered"])>0 && intval($iInfo["price"]))
			{
			
			$productoptions = array();
			$productoptions = unserialize($iInfo['product_options']);			
			
			//$product = $iInfo['product']->toArray();
			
			
			if(isset($iInfo['product']))
			{				
				$product = $iInfo['product']->toArray();
			}else
			{
				
				$product = $iInfo;
				$product['type_id'] = $iInfo['product_type'];
				$product['tax_class_id'] = 'no';	
				$productoptions['simple_sku'] = $iInfo['sku'];
							
			}
			
			
			//print_r($product);
			//die();
			//$product = $iInfo;
			//print_r($productoptions);
			//die();
			
			
          $itemNode = $xmlResponse->createTag("Item",    array(), '',    $itemsNode);
		  
		  if($product['type_id']!='configurable')
		  {
		  	$xmlResponse->createTag("ItemCode",       array(), htmlentities($product['sku'],ENT_QUOTES), $itemNode,__ENCODE_RESPONSE);
		  }else
		  {
          	$xmlResponse->createTag("ItemCode",       array(), htmlentities($productoptions['simple_sku'],ENT_QUOTES), $itemNode,__ENCODE_RESPONSE);
		  }
          $xmlResponse->createTag("ItemDescription",array(),  htmlentities(substr($product['name'],0,50),ENT_QUOTES),      $itemNode, __ENCODE_RESPONSE);
//          $xmlResponse->createTag("ItemShortDescr",array(), htmlentities(substr($product['short_description'],0,50),ENT_QUOTES),      $itemNode, __ENCODE_RESPONSE);
		  $xmlResponse->createTag("ItemShortDescr",array(), htmlentities(substr($product['description'],0,2000),ENT_QUOTES),      $itemNode, __ENCODE_RESPONSE);
				
            $xmlResponse->createTag("Quantity",array(),intval($iInfo["qty_ordered"]),$itemNode,__ENCODE_RESPONSE);
            $xmlResponse->createTag("UnitPrice",array(), $iInfo["price"],$itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("Weight",array(), $iInfo["weight"],  $itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("FreeShipping",array(),"N",  $itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("Discounted",   array(),"N",  $itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("shippingFreight",array(),"0.00",  $itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("Weight_Symbol",  array(),"lbs",  $itemNode, __ENCODE_RESPONSE);		            $xmlResponse->createTag("Weight_Symbol_Grams",array(),"453.6", $itemNode, __ENCODE_RESPONSE);
	
			if($product['tax_class_id']<=0 || $product['tax_class_id']="")
			{
				$xmlResponse->createTag("TaxExempt",array(),'Y',$itemNode, __ENCODE_RESPONSE);		
			}else
			{
				$xmlResponse->createTag("TaxExempt",array(),'N',$itemNode, __ENCODE_RESPONSE);		
			}
			
				$iInfo['onetime_charges']="0.00";
				$xmlResponse->createTag("OneTimeCharge",array(),number_format($iInfo['onetime_charges'],2,'.',''),$itemNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("ItemTaxAmount",      array(), '', $itemNode, __ENCODE_RESPONSE);
			$iOptions = $xmlResponse->createTag("ItemOptions",      array(), '',        $itemNode,__ENCODE_RESPONSE);
			
			if(array_key_exists("attributes_info",$productoptions))
			{
			for($i=0;$i<count($productoptions['attributes_info']);$i++)
			{
			 $xmlResponse->createTag("ItemOption", array("Name"=>htmlentities($productoptions['attributes_info'][$i]['label']),"Value"=> htmlentities($productoptions['attributes_info'][$i]['value'])), "", $iOptions, __ENCODE_RESPONSE);
			}
			}
			
            }
			
			}
			
			  /////////////////////////////////////
	          //   billing info
              /////////////////////////////////////
			
			$BillNode = $xmlResponse->createTag("Bill", array(),'', $orderNode);
            $PayStatus = "Cleared";
			$BillCreditCardNode = $xmlResponse->createTag("CreditCard", array(), "", $BillNode, __ENCODE_RESPONSE);
			
			
						 //  echo $payment['cc_type'] . "1111";
						 
               if ($payment['cc_type']!=""){
//			   echo "HELLO" .$payment['cc_type'];
			    $xmlResponse->createTag("CreditCardType",     array(), $objNew->getCcTypeName($payment['cc_type']),       $BillCreditCardNode, __ENCODE_RESPONSE);
                 if (isset($payment['amount_paid'])){
               $xmlResponse->createTag("CreditCardCharge", array(), $payment['amount_paid'], $BillCreditCardNode, __ENCODE_RESPONSE);
               }else{
                  $xmlResponse->createTag("CreditCardCharge", array(), "", $BillCreditCardNode, __ENCODE_RESPONSE);
               }

                         //  sprintf('%02d',$p->getCcExpMonth()).substr($p->getCcExpYear(),-2,2)
               if (isset($payment['cc_exp_month'])&&isset($payment['cc_exp_year'])){
                  $xmlResponse->createTag("ExpirationDate", array(),sprintf('%02d',$payment['cc_exp_month']).substr($payment['cc_exp_year'],-2,2), $BillCreditCardNode, __ENCODE_RESPONSE);

               }else{
                  $xmlResponse->createTag("ExpirationDate", array(),"", $BillCreditCardNode, __ENCODE_RESPONSE);
               }



               $CreditCardName = (isset($payment['cc_owner'])?($payment['cc_owner']):"");
               $xmlResponse->createTag("CreditCardName", array(), $CreditCardName, $BillCreditCardNode, __ENCODE_RESPONSE);
			   
               $CreditCardNumber = (isset($payment['cc_last4'])?$payment['cc_last4']:"");
               $xmlResponse->createTag("CreditCardNumber", array(), $CreditCardNumber, $BillCreditCardNode, __ENCODE_RESPONSE);

               $xmlResponse->createTag("CVV2", array(), "", $BillCreditCardNode, __ENCODE_RESPONSE);

               $xmlResponse->createTag("AdvanceInfo", array(), "", $BillCreditCardNode, __ENCODE_RESPONSE);
			   $transcationId ="";
$transcationId = (isset($payment['cc_trans_id'])?($payment['cc_trans_id']):"");
              // $AuthDetails = (isset($payment['cc_trans_id'])?"cc_trans_id=".$payment['cc_trans_id'].";":"");
//               $AuthDetails .= (isset($payment['last_trans_id'])?"last_trans_id=".$payment['last_trans_id'].";":"");
               $xmlResponse->createTag("TransactionId", array(), $transcationId, $BillCreditCardNode, __ENCODE_RESPONSE);


			   
               //   if ($cc_type=$getCcTypeName($payment['cc_type'])){
                 //    $xmlResponse->createTag("CreditCardType", array(), $cc_type, $BillCreditCardNode, __ENCODE_RESPONSE);
                  //}
               }else
                 // $xmlResponse->createTag("CreditCardType", array(), "", $BillCreditCardNode, __ENCODE_RESPONSE);
			


            if (isset($payment['amount_ordered'])&&isset($payment['amount_paid'])){
            	if (($payment['amount_paid']==$payment['amount_ordered']))
                  $PayStatus = "Pending";
            }
			
			

           $xmlResponse->createTag("PayMethod", array(),$objNew->getPaymentlabel($payment['method']),$BillNode, __ENCODE_RESPONSE);
          //  $xmlResponse->createTag("PayStatus",array(), $PayStatus,   $BillNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("Title",array(), "", $BillNode, __ENCODE_RESPONSE);		  
            $xmlResponse->createTag("FirstName", array(), $orders["billing_firstname"], $BillNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("LastName", array(), $orders["billing_lastname"], $BillNode, __ENCODE_RESPONSE);
//            $xmlResponse->createTag("MiddleName", array(),"", $BillNode, __ENCODE_RESPONSE);

            if (!empty($orders["billing_company"]))
			{
               $xmlResponse->createTag("CompanyName", array(), $orders["billing_company"], $BillNode, __ENCODE_RESPONSE);
			   }
			   else
			   {
               $xmlResponse->createTag("CompanyName", array(), "", $BillNode, __ENCODE_RESPONSE);
			   
			   }

            $xmlResponse->createTag("Address1", array(), $orders["billing_street"], $BillNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("Address2", array(), "", $BillNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("City", array(), $orders["billing_city"], $BillNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("State", array(), $orders["billing_region"], $BillNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("Zip", array(), $orders["billing_postcode"], $BillNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("Country", array(), $orders["billing_country"], $BillNode, __ENCODE_RESPONSE);
			
            $xmlResponse->createTag("Email", array(), $orders["customer_email"], $BillNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("Phone", array(), $orders["billing_telephone"], $BillNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("PONumber", array(), "",  $BillNode, __ENCODE_RESPONSE);
			
        //    $xmlResponse->createTag("State", array(), $_maxlen($orders["billing_state"],75), $BillNode, __ENCODE_RESPONSE);

//            $xmlResponse->createTag("Email", array(), $orders["customer_email"], $BillNode, __ENCODE_RESPONSE);
                                       /////////////////////////////////////
                                       //   CreditCard info
                                       /////////////////////////////////////




          //  if (in_array($payment['method'],$PayMethodsCC)){

//               $xmlResponse->createTag("ReconciliationData", array(), "", $BillCreditCardNode, __ENCODE_RESPONSE);

           // }
		   
		   $ShipNode  = $xmlResponse->createTag("Ship",    array(), '', $orderNode,__ENCODE_RESPONSE);

 //$xmlResponse->createTag("ShipCarrierName", array(), $orders["shipping_description"], $ShipNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("ShipMethod", array(), $orders["shipping_description"], $ShipNode, __ENCODE_RESPONSE);
						$xmlResponse->createTag("TrackingNumber",array(), $shipTrack,   $ShipNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("Title",array(), "", $ShipNode, __ENCODE_RESPONSE);			


            $xmlResponse->createTag("FirstName", array(), $orders["shipping_firstname"], $ShipNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("LastName", array(), $orders["shipping_lastname"], $ShipNode, __ENCODE_RESPONSE);
            //$xmlResponse->createTag("MiddleName", array(),"", $ShipNode, __ENCODE_RESPONSE);
            if (!empty($orders["shipping_company"]))
			{
               $xmlResponse->createTag("CompanyName", array(), $orders["shipping_company"], $ShipNode, __ENCODE_RESPONSE);
			   }
			   else
			   {
               $xmlResponse->createTag("CompanyName", array(), "", $ShipNode, __ENCODE_RESPONSE);
			   
			   }

            $xmlResponse->createTag("Address1", array(), $orders["shipping_street"], $ShipNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("Address2", array(), "", $ShipNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("City", array(), $orders["shipping_city"], $ShipNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("State", array(), $orders["shipping_region"], $ShipNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("Zip", array(), $orders["shipping_postcode"], $ShipNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("Country", array(), $orders["shipping_country"], $ShipNode, __ENCODE_RESPONSE);
			
            $xmlResponse->createTag("Email", array(), $orders["customer_email"], $ShipNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("Phone", array(), $orders["shipping_telephone"], $ShipNode, __ENCODE_RESPONSE);
			 $chargesNode  = $xmlResponse->createTag("Charges", array(), '', $orderNode,__ENCODE_RESPONSE);

                                     
                                       /////////////////////////////////////
                                       //   shipping info
                                       /////////////////////////////////////


           


                                       /////////////////////////////////////
                                       //   Charges info
                                       /////////////////////////////////////
           $xmlResponse->createTag("Discount",       array(), $orders["discount_amount"], $chargesNode,__ENCODE_RESPONSE);
           $xmlResponse->createTag("Tax",       array(), $orders["tax_amount"], $chargesNode,__ENCODE_RESPONSE);
          $xmlResponse->createTag("Shipping",       array(), $orders["shipping_amount"], $chargesNode,__ENCODE_RESPONSE);
           $xmlResponse->createTag("Total",       array(),  $orders["grand_total"], $chargesNode,__ENCODE_RESPONSE);
           /* if (isset($orders["coupon_code"])){
                if ($orders["coupon_code"]!=""){
            $discount = Mage::getModel('salesrule/rule')->getResourceCollection()
                  ->addFieldToFilter('coupon_code', $orders["coupon_code"])
                  ->load();
              $discount_=$discount->toArray();
                  $discount_=$discount_['items'][0];
              //   var_dump($discount_);
                $CouponsNode=$xmlResponse->createTag("Coupons",       array(), '', $chargesNode);
                $xmlResponse->createTag("Coupon",       array(), $discount_['name'], $CouponsNode);
                $xmlResponse->createTag("CouponCode",       array(), $orders["coupon_code"], $CouponsNode);
                $xmlResponse->createTag("CouponID",       array(), $discount_['rule_id'], $CouponsNode);
                $xmlResponse->createTag("CouponDescription",       array(), $discount_['description'], $CouponsNode);
                $xmlResponse->createTag("CouponAction",       array(), $discount_['simple_action'], $CouponsNode);
                $xmlResponse->createTag("CouponValue",       array(), $discount_['discount_amount'], $CouponsNode);
                }

             }
             for ($i=1; $i<6; $i++) {
                $xmlResponse->createTag("CustomField".(string)$i,      array(), '',  $chargesNode);
             }*/



            $xmlResponse->createTag("OrderID",          array(), $orders['increment_id'], $orderNode , __ENCODE_RESPONSE);
            $xmlResponse->createTag("Title",          array(), "", $orderNode, __ENCODE_RESPONSE );
			            $xmlResponse->createTag("FirstName", array(), $orders["billing_firstname"], $orderNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("LastName", array(), $orders["billing_lastname"], $orderNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("Date",             array(), $dateCreateOrder   ,$orderNode, __ENCODE_RESPONSE );
            $xmlResponse->createTag("Time",             array(), $timeCreateOrder   ,$orderNode, __ENCODE_RESPONSE );
            $xmlResponse->createTag("StoreId",          array(), $orders['store_id'], $orderNode, __ENCODE_RESPONSE);
            $xmlResponse->createTag("StoreName",        array(), "",   $orderNode,  __ENCODE_RESPONSE);
            $xmlResponse->createTag("Currency",         array(), $orders['order_currency_code'],    $orderNode, __ENCODE_RESPONSE);
			$xmlResponse->createTag("Weight_Symbol",  array(),"lbs",  $orderNode, __ENCODE_RESPONSE);		            $xmlResponse->createTag("Weight_Symbol_Grams",array(),"453.6", $orderNode, __ENCODE_RESPONSE);
			

//            $xmlResponse->createTag("ProviderOrderRef", array(),$orders['increment_id'],  $orderNode);
            $xmlResponse->createTag("Status", array(),$orders['status'], $orderNode,  __ENCODE_RESPONSE);	
			               $xmlResponse->createTag("Notes",          array(), "",    $orderNode,  __ENCODE_RESPONSE);
						   
		
            if (isset($orders['customer_note'])&&$orders['customer_note']!=''){
               $xmlResponse->createTag("Comment",          array(), $orders['customer_note'],    $orderNode,  __ENCODE_RESPONSE);
            }
			else
			{
               $xmlResponse->createTag("Comment",          array(), "",    $orderNode,  __ENCODE_RESPONSE);
			
			}
						    $xmlResponse->createTag("Fax",          array(), "",    $orderNode,  __ENCODE_RESPONSE);
			
            //$xmlResponse->createTag("TimeZone",         array(), 'not found',                        $orderNode,  __ENCODE_RESPONSE);
           // $xmlResponse->createTag("CustomerID",       array(), $orders['customer_id'], $orderNode);
 //           $xmlResponse->createTag("SalesRep",         array(), "not found",            $orderNode,  __ENCODE_RESPONSE);

            // ALEX: ޲졿 衪ﭬ殲驠𐯲ﭳ �㼢ᬨ㡫᲼ ﹨⫠ 𐯱죠󯤮 롪 寡ᣨ쯱� �妰


            
            

           
        
         }
      //   header("Content-type: application/xml");
    //     print($xmlResponse->generate());       //  header("Content-type: application/xml");
        
	return $xmlResponse->generate();				

}


function getStores($username,$password)
{
$xmlResponse = new xml_doc();
	$xmlResponse->version='1.0';
	$xmlResponse->encoding='UTF-8';	
	$root = $xmlResponse->createTag("RESPONSE", array('Version'=>'1.0'));	
	$objNew = new Mage_Webgility_Model_Ecc_Run();
	$status =  $objNew->CheckUser($username,$password);

	if($status!="0"){ //login name invalid
		if($status=="1"){
		$xmlResponse->createTag("StatusCode", array(), "1", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid login. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
		if($status=="2"){ //password invalid
		$xmlResponse->createTag("StatusCode", array(), "2", $root, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StatusMessage", array(), "Invalid password. Authorization failed", $root, __ENCODE_RESPONSE);
		return $xmlResponse->generate();
		}
	}
	$pMethodNodes = $xmlResponse->createTag("Stores", array(), '', $root);
	//exit;
	$stores = $objNew->getstores();
//print_r($stores);
	//exit;
	
	if(count($stores)>0)
	{
	for($i=0;$i<count($stores['items']);$i++)
	{
	
	 //echo $stores['items'][$i]['store_id'];
	if($stores['items'][$i]['store_id']>0)
	{
			$pMethodNode = $xmlResponse->createTag("Store",    array(),'', $pMethodNodes, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StoreID",array(),$stores['items'][$i]['store_id'], $pMethodNode, __ENCODE_RESPONSE);
		$xmlResponse->createTag("StoreName",array(),htmlentities($stores['items'][$i]['name'], ENT_QUOTES), $pMethodNode, __ENCODE_RESPONSE);

	
	}
	}
	
	}
	return $xmlResponse->generate();
//	print_r($stores);
	//exit;

}


function getCategory($username,$password)
{
	$storeId=getDefaultStore();
	$xmlResponse = new xml_doc();
	$xmlResponse->version='1.0';
	$xmlResponse->encoding='UTF-8';	
	$root = $xmlResponse->createTag("RESPONSE", array('Version'=>'1.0'));	
	$objNew = new Mage_Webgility_Model_Ecc_Run();
	isAuthorized($username,$password);
	$categories = $objNew->_getcategory($storeId);	
	//print_r($categories);

	$pMethodNodes = $xmlResponse->createTag("Categories", array(), '', $root);

	if($categories)
	{
		$xmlResponse->createTag("StatusCode", array(), "0", $pMethodNodes, __ENCODE_RESPONSE);
  	$xmlResponse->createTag("StatusMessage", array(), "All Ok", $pMethodNodes, __ENCODE_RESPONSE);

		for($i=0;$i <count ($categories);$i++) 
		{
	//	echo $categories[0]['categoryid'];
		
		if($categories[$i]['category_id']=='' || $categories[$i]['name']=='')
		{
		}
		else
		{
		$pMethodNode = $xmlResponse->createTag("Category",    array(),'', $pMethodNodes, __ENCODE_RESPONSE);
		$xmlResponse->createTag("CategoryID",array(),$categories[$i]['category_id'], $pMethodNode, __ENCODE_RESPONSE);
		$xmlResponse->createTag("CategoryName",array(),htmlentities($categories[$i]['name'], ENT_QUOTES), $pMethodNode, __ENCODE_RESPONSE);
		}
		}
	}
	return $xmlResponse->generate();
}









 
 
 function parseSpecCharsA($arr){
   foreach($arr as $k=>$v){
   if(is_array($k))
   {
   foreach($k as $l=>$m)
   {
   $arr[$l] = addslashes(htmlentities($m, ENT_QUOTES));
   }
   }
   else
   {
     //$arr[$k] = htmlspecialchars($v, ENT_NOQUOTES);
		 $arr[$k] = addslashes(htmlentities($v, ENT_QUOTES));
		 }
   }
   return $arr;
}

function checkVersion()
{
	global $config;
	$str="";
	if($config['version'])
	{
	return $config['version'];
	}
	else
	{
	return "0";
	}
}
?>