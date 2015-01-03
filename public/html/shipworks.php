<?php 

    /*
    |
    | This file and the source codes contained herein are the property 
    | of Interapptive, Inc.  Use of this file is restricted to the specific 
    | terms and conditions in the License Agreement associated with this 
    | file.	Distribution of this file or portions of this file for uses
    | not covered by the License Agreement is not allowed without a written 
    | agreement signed by an officer of Interapptive, Inc.
    | 
    | The code contained herein may not be reproduced, copied or
    | redistributed in any form, as part of another product or otherwise.
    | Modified versions of this code may not be sold or redistributed.
    |
    | Copyright 2009 Interapptive, Inc.  All rights reserved.
    | http://www.interapptive.com/
    |
    |
     */

    /* Note: for Magento 1.3.0, the security feature of "Add Secret key to Urls" MUST be disabled */
	define('REQUIRE_SECURE', true);
	$moduleVersion = "2.9.37";

	// include the Mage engine
	require_once 'app/Mage.php';
	umask(0);

        $storeCode = '';
        if (isset($_REQUEST['store']))
        {
            $storeCode = $_REQUEST['store'];	
        }

	
	// using output buffering to get around headers that magento is setting after we've started output
	ob_start();

	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	
	// HTTP/1.1
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);

	// HTTP/1.0
	header("Pragma: no-cache");	
	
	// write xml documenta declaration
	function writeXmlDeclaration()
	{
		echo "<?xml version=\"1.0\" standalone=\"yes\" ?>";
	}
	
	// write the open xml tag 
	function writeStartTag($tag)
	{
		echo '<' . $tag . '>';
	}
	
	// write closing xml tag
	function writeCloseTag($tag)
	{
		echo '</' . $tag . '>';
	}

	// Output the given tag\value pair
	function writeElement($tag, $value)
	{
		writeStartTag($tag);
		echo htmlspecialchars($value);
		writeCloseTag($tag);
	}
	
	// Function used to output an error and quit.
	function outputError($code, $error)
	{	
		writeStartTag("Error");
		writeElement("Code", $code);
		writeElement("Description", $error);
		writeCloseTag("Error");
	}	
		
	// check for SSL
	$secure = false;
	try
	{
            if (isset($_SERVER['HTTPS']))
            {
                $secure = ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == '1');
            }
	}
	catch(Exception $e)
	{
	}

	// Open the XML output and root
	writeXmlDeclaration();
	writeStartTag("ShipWorks");
	writeElement("ModuleVersion", $moduleVersion);

	try
	{
            // start the mage engine
            Mage::app($storeCode);
	}
	catch (Mage_Core_Model_Store_Exception $e)
	{
            outputError(100, "Invalid Store Code.");
            writeCloseTag("ShipWorks");
            exit;
	}

	// Enforse SSL
	if (!$secure && REQUIRE_SECURE)
	{
	    outputError(10, 'A secure (https://) connection is required.');
	}
	else
	{
	    // If the admin module is installed, we make use of it
	    if (checkAdminLogin())
	    {
		$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
		switch (strtolower($action)) 
		{
		case 'getstore': Action_GetStore(); break;
		case 'getcount': Action_GetCount(); break;
		case 'getorders': Action_GetOrders(); break;
		case 'getstatuscodes': Action_GetStatusCodes(); break;
		case 'updateorder': Action_UpdateOrder(); break;
		default:
		    outputError(20, "'$action' is not supported.");
		}
	    }
	}
	
	// Close the output
	writeCloseTag("ShipWorks");
	
	// Check to see if admin functions exist.  And if so, determine if the user
	// has access.
	function checkAdminLogin()
	{
	    $loginOK = false;

	    if (isset($_REQUEST['username']) && isset($_REQUEST['password']))
	    {
		$username = $_REQUEST['username'];
		$password = $_REQUEST['password'];

		$user = Mage::getSingleton('admin/session')->login($username, $password);
		if ($user && $user->getId())
		{
		    $loginOK = true;	    
		}
	    }

	    if (!$loginOK)
	    {
		outputError(50, "The username or password is incorrect.");
	    }

	    writeStartTag("Admin");
		writeElement("Authenticated", $loginOK ? "true" : "false");
	    writecloseTag("Admin");

	    return $loginOK;
	}
	
	// Write store data
	function Action_GetStore()
	{
	    // get state name
	    $region_model = Mage::getModel('directory/region');
	    if (is_object($region_model))
	    {
		$state = $region_model->load(Mage::getStoreConfig('shipping/origin/region_id'))->getDefaultName();
	    }

	    $name = Mage::getStoreConfig('system/store/name');
	    $owner = Mage::getStoreConfig('trans_email/ident_general/name');
	    $email = Mage::getStoreConfig('trans_email/ident_general/email');
	    $country = Mage::getStoreConfig('shipping/origin/country_id');
	    $website = Mage::getURL();

	    writeStartTag("Store");
	    writeElement("Name", $name);
	    writeElement("Owner", $owner);
	    writeElement("Email", $email);
	    writeElement("State", $state);
	    writeElement("Country", $country);
	    writeElement("Website", $website);
	    writeCloseTag("Store");
	}

	// Convert timestamp to sql date format
	function toSqlDate($timestamp)
	{
	    return date("Y-m-d H:i:s", (Integer)$timestamp);
	}
	
	// Get the count of orders greater than the start ID
	function Action_GetCount()
	{	  
	    $start = 0;

	    if (isset($_REQUEST['start']))
	    {
		$start = $_REQUEST['start'];
	    }

	    // only get orders through 2 seconds ago
	    $end = date("Y-m-d H:i:s", time() - 2);

	    // Convert to local SQL time
	    $start = toSqlDate($start);

	    // Write the params for easier diagnostics
	    writeStartTag("Parameters");
	    writeElement("Start", $start);
	    writeCloseTag("Parameters");

	    $orders = Mage::getModel('sales/order')->getCollection();
	    $orders->addAttributeToSelect("updated_at")->getSelect()->where("(updated_at > '$start' AND updated_at <= '$end')");
	    $count = $orders->count();

	    writeElement("OrderCount", $count);
	}

	// Get all orders greater than the given start id, limited by max count
	function Action_GetOrders()
	{
	    $start = 0;
	    $maxcount = 50;

	    if (isset($_REQUEST['start']))
	    {
		$start = $_REQUEST['start'];
	    }

	    if (isset($_REQUEST['maxcount']))
	    {
		$maxcount = $_REQUEST['maxcount'];
	    }

	    // Only get orders through 2 seconds ago.
	    $end = date("Y-m-d H:i:s", time() - 2);

	    // Convert to local SQL time
	    $start = toSqlDate($start);

	    // Write the params for easier diagnostics
	    writeStartTag("Parameters");
	    writeElement("Start", $start);
	    writeElement("End", $end);
	    writeElement("MaxCount", $maxcount);
	    writeCloseTag("Parameters");				    

	    $orders = Mage::getModel('sales/order')->getCollection();
	    $orders->addAttributeToSelect("*")
		->getSelect()
		->where("(updated_at > '$start' AND updated_at <= '$end')")
		->order('updated_at', 'asc');
            $orders->setCurPage(1)
                ->setPageSize($maxcount)
                ->loadData();

	    writeElement("Total", $orders->count());

	    writeStartTag("Orders");

	    $lastModified = null;
	    $processedIds = "";

	    foreach ($orders as $order)
	    {
		// keep track of the ids we've downloaded
		$lastModified = $order->getUpdatedAt();

		if ($processedIds != "")
		{
		    $processedIds .= ", ";
		}
		$processedIds .= $order->getEntityId();

		WriteOrder($order);
	    }

	    // if we processed some orders we may have to get some more
	    if ($processedIds != "")
	    {
		$orders = Mage::getModel('sales/order')->getCollection();
		$orders->addAttributeToSelect("*")->getSelect()->where("updated_at = '$lastModified' AND entity_id not in ($processedIds) ");

		foreach ($orders as $order)
		{
		    WriteOrder($order);
		}
	    }

	    writeCloseTag("Orders");
	}
	
	// Output the order as xml
	function WriteOrder($order)
	{		  
	    writeStartTag("Order");

	    writeElement("OrderNumber", $order->getIncrementId());
	    writeElement("OrderID", $order->getEntityId());
	    writeElement("OrderDate", $order->getCreatedAt());
	    writeElement("LastModified", $order->getUpdatedAt());
	    writeElement("LastModifiedLocal", $order->getUpdatedAt());
	    writeElement("ShippingMethod", $order->getShippingDescription());
	    writeElement("StatusCode", $order->getStatus());
	    writeElement("CustomerComment", '');

            // safety measure
            if ($order->getShippingAddress() == null && $order->getBillingAddress() == null)
            {
              writeCloseTag("Order");
              return;
            }

	    writeStartTag("Customer");
	    writeElement("CustomerID", $order->getCustomerId());
	    writeElement("Phone", $order->getBillingAddress()->getTelephone());
	    writeElement("Email", $order->getCustomerEmail());
	    writeCloseTag("Customer");

	    $address = $order->getShippingAddress();
	    if (!$address)
	    {
		$address = $order->getBillingAddress();
	    }

	    writeStartTag("ShipAddress");
	    writeElement("Name", $address->getName());
	    writeElement("Company", $address->getCompany());
	    writeElement("Street1", $address->getStreet(-1));
	    writeElement("Street2", "");
	    writeElement("Street3", "");
	    writeElement("City", $address->getCity());
	    writeElement("PostalCode", $address->getPostcode());
	    writeElement("State", $address->getRegion());
	    writeElement("Country", $address->getCountryId());
	    writeCloseTag("ShipAddress");

	    $address = $order->getBillingAddress();
	    writeStartTag("BillAddress");
	    writeElement("Name", $address->getName());
	    writeElement("Company", $address->getCompany());
	    writeElement("Street1", $address->getStreet(-1));
	    writeElement("Street2", "");
	    writeElement("Street3", "");
	    writeElement("City", $address->getCity());
	    writeElement("PostalCode", $address->getPostcode());
	    writeElement("State", $address->getRegion());
	    writeElement("Country", $address->getCountryId());
	    writeCloseTag("BillAddress");

	    $payment = $order->getPayment();
	    $cc_num = $payment->getCcLast4();
	    $cc_year = sprintf('%02u%s', $payment->getCcExpMonth(), substr($payment->getCcExpYear(), 2)); 
	    if (!empty($cc_num)){
		$cc_num = '************'.$payment->getCcLast4();
	    }
	    else {
		$cc_year = '';
	    };

	    writeStartTag("Payment");
	    writeElement("Method", Mage::helper('payment')->getMethodInstance($payment->getMethod())->getTitle());

	    writeStartTag("CreditCard");
	    writeElement("Type", $payment->getCctype());
	    writeElement("Owner", $payment->getCcOwner());
	    writeElement("Number", $cc_num);
	    writeElement("Expires", $cc_year);
	    writeCloseTag("CreditCard");

	    writeCloseTag("Payment");

	    WriteOrderItems($order->getAllItems());

	    WriteOrderTotals($order);

	    writeCloseTag("Order");
	}

	// writes a single order total
	function WriteOrderTotal($name, $text, $value, $class)
	{
	    writeStartTag("Total");
	    writeElement("Name", $name);
	    writeElement("Text", $text);
	    writeElement("Value", $value);
	    writeElement("Class", $class);
	    writeCloseTag("Total");
	}

	
	// Write all totals lines for the order
	function WriteOrderTotals($order)
	{
	    writeStartTag("Totals");

	    WriteOrderTotal("Order Subtotal", 
		$order->formatPriceTxt($order->getSubtotal()),
		$order->getSubtotal(),
		"ot_subtotal");

	    WriteOrderTotal("Shipping and Handling",
		$order->formatPriceTxt($order->getShippingAmount()),
		$order->getShippingAmount(),
		"ot_shipping");

	    if ($order->getTaxAmount() > 0)
	    {
		WriteOrderTotal("Tax",
		    $order->formatPriceTxt($order->getTaxAmount()),
		    $order->getTaxAmount(),
		    "tax");
	    }

	    if ($order->getDiscountAmount() > 0)
	    {
		$couponcode = $order->getCouponCode();
		WriteOrderTotal("Discount ($couponcode)",
		    $order->formatPriceTxt($order->getDiscountAmount()),
		    -1 * $order->getDiscountAmount(),
		    "ot_discount");
	    }

	    if ($order->getGiftcertAmount() > 0)
	    {
		WriteOrderTotal("Gift Certificate",
		    $order->formatPriceTxt($order->getGiftcertAmount()),
		    -1 * $order->getGiftcertAmount(),
		    "giftcertificate");
	    }

	    if ($order->getAdjustmentPositive())
	    {
		WriteOrderTotal("Adjustment Refund",
		    $order->formatPriceTxt($order->getAdjustmentPositive()),
		    -1 * $order->getAdjustmentPositive(),
		    "refund");
	    }

	    if ($order->getAdjustmentNegative())
	    {
		WriteOrderTotal("Adjustment Fee", 
		    $order->formatPriceTxt($order->getAdjustmentNegative()),
		    $order->getAdjustmentPositive(),
		    "fee");
	    }

	    writeStartTag("Total");
	    writeElement("Name", "Grand Total");
	    writeElement("Text", $order->formatPriceTxt($order->getGrandTotal()));
	    writeElement("Value", $order->getGrandTotal());
	    writeElement("Class", "ot_total");
	    writeCloseTag("Total"); 

	    writeCloseTag("Totals");
	}

	// Gets the price of an order item
	function getCalculationPrice($item)
	{
	    if ($item instanceof Mage_Sales_Model_Order_Item)
	    { 
		if ($item->hasCustomPrice())
		{
		    return $item->getCustomPrice();
		}
		else if ($item->hasOriginalPrice())
		{
		    return $item->getOriginalPrice();
		}
	    }

	    return 0;
	}

	// Write XML for all products for the given order
	function WriteOrderItems($orderItems)
	{
	    writeStartTag("Items");

	    $parentMap = Array();

	    // go through each item in the collection
	    foreach ($orderItems as $item)
	    {
                // keep track of item Id and types
                $parentMap[$item->getItemId()] = $item->getProductType();

                if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
                {
                    $sku = $item->getProductOptionByCode('simple_sku');
                }
                else
                {
                    $sku = $item->getSku();
                }		      

                $weight = $item->getWeight();
                if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)
                {
                    $name = $item->getName(). " (bundle)";
                    $unitPrice = getCalculationPrice($item);
                }
                else
                {
                    $name = $item->getName();

                    // if it's part of a bundle
                    if (is_null($item->getParentItemId()))
                    {
                      $unitPrice = getCalculationPrice($item);
                    }
                    else
                    {
                      // need to see if the parent is a bundle or not
                      $isBundle = ($parentMap[$item->getParentItemId()] == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE);
                      if ($isBundle)
                      {
                          // it's a bundle member - price and weight come from the bundle definition itself
                          $unitPrice = 0;
                          $weight = 0;
                      }
                      else
                      { 
                          // don't even want to include if the parent item is anything but a bundle
                          continue;
                      }
                    }
                }

                writeStartTag("Item");
                writeElement("ItemID", $item->getItemId());
                writeElement("ProductID", $item->getProductId());
                writeElement("Code", $sku);
                writeElement("Name", $name);
                writeElement("Quantity", (int)$item->getQtyOrdered());
                writeElement("UnitPrice", $unitPrice);
                writeElement("Weight", $weight);


                writeStartTag("Attributes");
                $opt = $item->getProductOptions();
                echo "product type = ". $item->getProductType();
                if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
                {
                    if (is_array($opt) &&
                        isset($opt['attributes_info']) &&
                        is_array($opt['attributes_info']) &&
                        is_array($opt['info_buyRequest']) &&
                        is_array($opt['info_buyRequest']['super_attribute']))
                    {
                        $attr_id = $opt['info_buyRequest']['super_attribute'];
                        reset($attr_id);
                        foreach ($opt['attributes_info'] as $sub)
                        {
                            writeStartTag("Attribute");
                            writeElement("AttributeID", key($attr_id));
                            writeElement("Name", $sub['label']);
                            writeElement("Value", $sub['value']);
                            writeCloseTag("Attribute");

                            next($attr_id);
                        }
                    }

                }
                else
                {
                    if (is_array($opt) &&
                        isset($opt['options']) &&
                        is_array($opt['options']))
                    {
                        foreach ($opt['options'] as $sub)
                        {
                            writeStartTag("Attribute");
                            writeElement("Name", $sub['label']);
                            writeElement("Value", $sub['value']);
                            writeCloseTag("Attribute");
                        }
                    }
                }
                writeCloseTag("Attributes");

                writeCloseTag("Item");
            }

	    writeCloseTag("Items");
	}

	// Returns the status codes for the store
	function Action_GetStatusCodes()
	{
	    writeStartTag("StatusCodes");

	    $statuses_node		    = Mage::getConfig()->getNode('global/sales/order/statuses');

	    foreach ($statuses_node->children() as $status)
	    {
		writeStartTag("StatusCode");
		writeElement("Code", $status->getName());
		writeElement("Name", $status->label);
		writeCloseTag("StatusCode");
	    }

	    writeCloseTag("StatusCodes");
	}

	// Update the status of an order
	function Action_UpdateOrder()
	{
	    // gather paramtetes
	    if ((!isset($_REQUEST['order']) && !isset($_REQUEST['orderid'])) || 
	    	!isset($_REQUEST['command']) || !isset($_REQUEST['comments']))
	    {
		outputError(40, "Not all parameters supplied.");
		return;
	    }

	    if (isset($_REQUEST['order']))
	    {
	    	$orderNumber = (int) $_REQUEST['order'];
	    	$order = Mage::getModel('sales/order')->loadByIncrementId($orderNumber);
	    }
	    else
	    {
	    	// newer version of ShipWorks, pull the entity id
		$orderID = (int)$_REQUEST['orderid'];
		$order = Mage::getModel('sales/order')->load($orderID);
	    }

	    $command = (string) $_REQUEST['command'];
	    $comments = $_REQUEST['comments'];
	    $tracking = $_REQUEST['tracking'];
	    $carrierData = $_REQUEST['carrier'];

	    ExecuteOrderCommand($order, $command, $comments, $carrierData, $tracking);
	}

	// Takes the actions necessary to get an order to Complete
	function CompleteOrder($order, $comments, $carrierData, $tracking)
	{
	    // first create a shipment
	    $shipment = $order->prepareShipment();
	    if ($shipment)
	    {
		$shipment->register();
		$shipment->addComment($comments, false);
		$order->setIsInProcess(true);

		// add tracking info if it was supplied
		if (strlen($tracking) > 0)
		{
		    $tracking = $tracking;
		    $track = Mage::getModel('sales/order_shipment_track')->setNumber($tracking);
	
		    # carrier data is of the format code|title
		    $carrierData = split("[|]", $carrierData);
		    $track->setCarrierCode($carrierData[0]);
		    $track->setTitle($carrierData[1]);

		    $shipment->addTrack($track);
		}

		$transactionSave = Mage::getModel('core/resource_transaction')
		    ->addObject($shipment)
		    ->addObject($shipment->getOrder())
		    ->save();
	    } 

	    // invoice the order
	    $invoice = $order->prepareInvoice();
	    $invoice->addComment($comments);
	    $invoice->register();	    

	    // save the new invoice
	    $transactionSave = Mage::getModel('core/resource_transaction')
		->addObject($invoice)
		->addObject($invoice->getOrder());
	    $transactionSave->save();

	    // now change the order state and save
	    $order->setState('complete', true, $comments);
	    $order->save();
	}

	// Changes the status of an order 
	function ExecuteOrderCommand($order, $command, $comments, $carrierData, $tracking)
	{
	    try
	    {
		// to change statuses, we need to unhold if necessary
		if ($order->canUnhold())
		{
		    $order->unhold();
		    $order->save();
		}

		switch (strtolower($command))
		{
		case "complete":
		    CompleteOrder($order, $comments, $carrierData, $tracking);
		    break;
		case "cancel":
		    $order->cancel();
		    $order->addStatusToHistory($order->getStatus(), $comments);
		    $order->save();
		    break;
		case "hold":
		    $order->hold();
		    $order->addStatusToHistory($order->getStatus(), $comments);
		    $order->save();
		    break;
		default:
		    outputError(80, "Unknown order command '$command'.");
		    break;
		}

		writeElement("OrderStatus", $order->getStatus());
	    }
	    catch (Exception $ex)
	    {
		outputError(90, "Error Executing Command. ".  $ex->getMessage());
	    }
	}

	// Converts a sql data string to gmt
	function toGmt($dateSql)
	{
	    $pattern = "/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})$/i";

	    if (preg_match($pattern, $dateSql, $dt)) 
	    {
		$dateUnix = mktime($dt[4], $dt[5], $dt[6], $dt[2], $dt[3], $dt[1]);
		return gmdate("Y-m-d H:i:s", $dateUnix);
	    }

	    return $dateSql;
	}

	function toLocalSqlDate($dateUnix)
	{					       
	    return date("Y-m-d H:i:s", $dateUnix);
	}

	// end output
	ob_end_flush();
?>
