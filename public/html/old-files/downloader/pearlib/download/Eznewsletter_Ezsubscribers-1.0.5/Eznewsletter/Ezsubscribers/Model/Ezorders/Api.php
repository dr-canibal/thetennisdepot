<?php
class Eznewsletter_Ezsubscribers_Model_Ezorders_Api extends Mage_Api_Model_Resource_Abstract
{
    public function getAllOrders($dateLastRun, $status, $storeIds, $dateLastRunType)
    {
        // adjust the date to match the timezone of the clients installation
        $dateLastRun = date("Y-m-d H:i:s", strtotime($dateLastRun) + Mage::getModel('core/date')->getGmtOffset());
        //Mage::log($dateLastRun);

        // get the entity id for sales order
        $entityIdSalesOrder = $this->getEntityIdByEntityModel('sales/order');

        // get the entity id for sales customer
        $entityIdSalesCustomer = $this->getEntityIdByEntityModel('customer/customer');

        // get the entity id for sales customer address
        $entityIdSalesCustomerAddress = $this->getEntityIdByEntityModel('customer/customer_address');

        // temp var to hold the queue xml
        $xmlQueue = "";

        // temp var to hold the orders xml
        $xmlOrders = "";

        // temp var to thold the customer xml
        $xmlCustomer = "";

        // temp var to hold the order xml
        $xmlOrder = "";

        // temp var to hold sku xml
        $xmlSkus = "";

        // mage resource
        $resource = Mage::getSingleton('core/resource');

        // db read connection
        $read = $resource->getConnection('core_read');

        // db table names
        $newsletterTableName = $resource->getTableName('newsletter/subscriber');

        // build the order collection
        $orderCollection = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter(array(array('attribute'=>'status', strtolower($status))));

        // dateLastRunTypes
        // 0 = created_at, 1 = updated_at
        // build the order collection filter date
        if ($dateLastRunType == 0)
        {
            // new orders only
            $orderCollection->addAttributeToFilter(array(array('attribute'=>'created_at', 'datetime'=>true, 'gteq'=>$dateLastRun)))
            ->addAttributeToFilter(array(array('attribute'=>'created_at', 'is' => new Zend_Db_Expr('not null AND e.created_at = e.updated_at'))),'','left');
        }
        if ($dateLastRunType == 1)
        {
            // existing orders that have been updated
            $orderCollection->addAttributeToFilter(array(array('attribute'=>'updated_at', 'datetime'=>true, 'gteq'=>$dateLastRun)))
            ->addAttributeToFilter(array(array('attribute'=>'created_at', 'is' => new Zend_Db_Expr('not null AND e.created_at != e.updated_at'))),'','left');
        }

        // build the order collection filter on the store ids
        if (!empty($storeIds))
        {
            // split the string into array of numbers
            $storeIdsArr = explode(",", $storeIds);
            $storeIdsArrFilter = array();
            $cnt = 0;
            foreach ($storeIdsArr as $storeId)
            {
                $storeIdsArrFilter[$cnt] = array('attribute'=>'store_id', trim($storeId));
                $cnt++;
            }
            $orderCollection->addAttributeToFilter($storeIdsArrFilter);
        }
        //Mage::log($orderCollection->getSelect()->__toString());

        // load the data
        $orderCollection->load();

        // loop through all orders
        foreach ($orderCollection as $order)
        {
            // configure the order details
            $orderArr[] = $order->getData();
            $dateArr = explode(" ", $order->getData('created_at'));
            $dated = $dateArr[0] . "T" . $dateArr[1];
            $payType = "";
            $orderNumber = $order->getData('entity_id');
            $comments = "";
            $shippingMethod = $order->getData('shipping_method');
            $orderProcessingInfo = "";
            $totalWeight = $order->getData('weight');
            $totalGrand = $order->getData('grand_total');
            $totalShipping = $order->getData('shipping_amount');
            $totalTax = $order->getData('tax_amount');
            $totalDiscount = $order->getData('discount_amount');

            // build order xml
            $xmlOrder = $this->doOrder($dated, $payType, $orderNumber, $comments, $totalWeight, $totalGrand, $totalShipping, $totalTax, $shippingMethod, $totalDiscount, $orderProcessingInfo);

            // configure the customer details
            $customerBillingAddress = $order->getAddressById($order->getData('billing_address_id'));
            $fname = $customerBillingAddress->getData('firstname');
            $lname = $customerBillingAddress->getData('lastname');
            $email = $order->getData('customer_email');
            $address1 = $customerBillingAddress->getData('street');
            $address2 = "";
            $city = $customerBillingAddress->getData('city');
            $state = $customerBillingAddress->getData('region');
            $zip = $customerBillingAddress->getData('postcode');
            $country = $customerBillingAddress->getData('country_id');
            $company = $customerBillingAddress->getData('company');

            // check if customer is opted in
            $isOptedIn = "0";
            $subscribersResult = $read->query("SELECT * FROM $newsletterTableName WHERE subscriber_email = '$email' AND subscriber_status = 1 AND store_id in ($storeIds)");
            while ($row = $subscribersResult->fetch())
            {
                $isOptedIn = "1";
            }

            // build customer xml
            $xmlCustomer = $this->doCustomer($fname, $lname, $address1, $address2, $city, $state, $zip, $country, $company, $email, $isOptedIn);
            
            // foreach sku in the order
            $orderItems = $order->getItemsCollection();
            foreach($orderItems as $orderItem)
            {
                // build the sku
                $qty = $orderItem->getData('qty_ordered');                
                $subTotal = $orderItem->getData('row_total');
                $weight = $orderItem->getData('row_weight');
                $orderItemCollection = Mage::getModel('catalog/product')->load($orderItem->getData('product_id'));
                $plu =  $orderItemCollection->getData('sku');
                $name =  $orderItemCollection->getData('name');
                $price = $orderItemCollection->getData('price');
                $basePrice = $orderItemCollection->getData('minimal_price');
                $recordNumber = $orderItemCollection->getData('product_id');
                $recType = $orderItemCollection->getData('manufacturer');
                $productType = $orderItemCollection->getData('model');
                $handlingCharge = $orderItemCollection->getData('cost');
                $qtyPricing = $orderItemCollection->getData('special_price');
        
                // build the sku xml
                $xmlSkus .= trim($this->doSku($plu, $name, $price, $qty, $subTotal, $basePrice, $recType, $recordNumber, $productType, $weight, $handlingCharge, $qtyPricing, $dated));
            }
            
            // build the order xml
            $xmlOrders .= trim($this->doOrders($xmlCustomer, $xmlOrder, $xmlSkus));
        }

        // build the queue xml
        $xmlQueue = trim($this->doQueue($xmlOrders));
        return base64_encode($xmlQueue);

    }

    // build the queue xml
    public function doQueue($xmlOrders)
    {
        $xml = "<?xml version=\"1.0\"?><Queue xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\"><Orders>$xmlOrders</Orders></Queue>";
        return $xml;
    }

    // build the orders xml
    public function doOrders($xmlCustomer, $xmlOrder, $xmlSkus)
    {
        $xml = "<Orders>$xmlCustomer$xmlOrder<Skus>$xmlSkus</Skus></Orders>";
        return $xml;
    }

    // build the customer xml
    public function doCustomer($fname, $lname, $address1, $address2, $city, $state, $zip, $country, $company, $email, $isOptedIn)
    {
        // make sure isOptedIn is filled out (default = false)
        if (empty($isOptedIn))
        {
            $isOptedIn = 0;
        }
        $xml = "<Customer><EznewsletterCoId>0</EznewsletterCoId><NewsModCustmanCustId>0</NewsModCustmanCustId><Fname>".$this->doEscapexml($fname)."</Fname><Lname>".$this->doEscapexml($lname)."</Lname><Address1>".$this->doEscapexml($address1)."</Address1><Address2>".$this->doEscapexml($address2)."</Address2><City>".$this->doEscapexml($city)."</City><State>".$this->doEscapexml($state)."</State><Zip>".$this->doEscapexml($zip)."</Zip><Country>".$this->doEscapexml($country)."</Country><Company>".$this->doEscapexml($company)."</Company><Email>".$this->doEscapexml($email)."</Email><IsOptedIn>".$this->doEscapexml($isOptedIn)."</IsOptedIn><NewsEmailId>0</NewsEmailId></Customer>";
        return $xml;
    }

    // build the order xml
    public function doOrder($dated, $payType, $orderNumber, $comments, $totalWeight, $totalGrand, $totalShipping, $totalTax, $shippingMethod, $totalDiscount, $orderProcessingInfo)
    {
        $xml = "<Order><EznewsletterCoId>0</EznewsletterCoId><NewsModCustmanCustId>0</NewsModCustmanCustId><NewsModCustmanOrderId>0</NewsModCustmanOrderId><Dated>".$this->doEscapexml($dated)."</Dated><PayType>".$this->doEscapexml($payType)."</PayType><OrderNumber>".$this->doEscapexml($orderNumber)."</OrderNumber><Comments>".$this->doEscapexml($comments)."</Comments><TotalWeight>".$this->doEscapexml($totalWeight)."</TotalWeight><TotalGrand>".$this->doEscapexml($totalGrand)."</TotalGrand><TotalShipping>".$this->doEscapexml($totalShipping)."</TotalShipping><TotalTax>".$this->doEscapexml($totalTax)."</TotalTax><ShippingMethod>".$this->doEscapexml($shippingMethod)."</ShippingMethod><TotalDiscount>".$this->doEscapexml($totalDiscount)."</TotalDiscount><OrderProcessingInfo>".$this->doEscapexml($orderProcessingInfo)."</OrderProcessingInfo></Order>";
        return $xml;
    }

    // build the sku xml
    public function doSku($plu, $name, $price, $qty, $subTotal, $basePrice, $recType, $recordNumber, $productType, $weight, $handlingCharge, $qtyPricing, $dated)
    {
        $xml = "<Sku><EznewsletterCoId>0</EznewsletterCoId><NewsModCustmanCustId>0</NewsModCustmanCustId><NewsModCustmanOrderId>0</NewsModCustmanOrderId><NewsModCustmanSkuId>0</NewsModCustmanSkuId><Plu>".$this->doEscapexml($plu)."</Plu><Name>".$this->doEscapexml($name)."</Name><Price>".$this->doEscapexml($price)."</Price><Qty>".$this->doEscapexml($qty)."</Qty><Subtotal>".$this->doEscapexml($subTotal)."</Subtotal><Baseprice>".$this->doEscapexml($basePrice)."</Baseprice><RecType>".$this->doEscapexml($recType)."</RecType><RecordNumber>".$this->doEscapexml($recordNumber)."</RecordNumber><ProductType>".$this->doEscapexml($productType)."</ProductType><Weight>".$this->doEscapexml($weight)."</Weight><HandlingCharge>".$this->doEscapexml($handlingCharge)."</HandlingCharge><QtyPricing>".$this->doEscapexml($qtyPricing)."</QtyPricing><Dated>".$this->doEscapexml($dated)."</Dated></Sku>";
        return $xml;
    }

    // escapes illegal xml chars
    public function doEscapexml($xml)
    {
        $xml = str_replace("'", "[apos]", $xml);
        $xml = str_replace("&", "[amp]", $xml);
        $xml = str_replace("<", "[lt]", $xml);
        $xml = str_replace(">", "[gt]", $xml);
        $xml = str_replace("\"", "[quot]", $xml);
        return $xml;
    }

    // gets the entity id for a model
    public function getEntityIdByEntityModel($entity_model)
    {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $eavEntityType = $resource->getTableName('eav/entity_type');
        $readresult = $read->query("SELECT entity_type_id FROM $eavEntityType WHERE entity_model = '$entity_model'");
        $entityId = "";

        while ($row = $readresult->fetch())
        {
            $entityId = $row['entity_type_id'] . ",";
        }
        $entityId = trim($entityId, ",");

        return $entityId;
    }
}
?>