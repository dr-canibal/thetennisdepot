<?php

//ini_set('display_errors', 1);
//ini_set('xmlrpc_errors', 1);
//ini_set("memory_limit","256M");

class Eznewsletter_Ezsubscribers_Model_Ezsignups_Api extends Mage_Api_Model_Resource_Abstract
{
    public function getAllSignups($dateMin, $dateMax, $status, $storeIds)
    {
        // temp var to hold the queue xml
        $xmlQueue = "";

        // temp var to hold the orders xml
        $xmlOrders = "";
		
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('newsletter_read');
        $newsletterTableName = $resource->getTableName('newsletter/subscriber');
		$readresult = $read->query("SELECT * FROM $newsletterTableName WHERE subscriber_status = $status AND change_status_at >= '$dateMin' and change_status_at <= '$dateMax' AND store_id in ($storeIds)");
        Mage::log("SELECT * FROM $newsletterTableName WHERE subscriber_status = $status AND change_status_at >= '$dateLastRun' AND store_id in ($storeIds)");

        while ($row = $readresult->fetch())
        {
            $customer = Mage::getModel('customer/customer');
            $addresses = Mage::getModel("Customer/Entity_Address_Collection");
            $customer->
            $fname = "";
            $lname = "";
            $email = "";
            $address1 = "";
            $address2 = "";
            $city = "";
            $state = "";
            $zip = "";
            $country = "";
            $company = "";

            $customer->load($row['customer_id']);

            $addresses->setCustomerFilter($customer);
            $addresses->load();

            $fname = $customer->getData('firstname');
            $lname = $customer->getData('lastname');
            $email = $row['subscriber_email'];
            $isOptedIn = 1;
            
            foreach($addresses as $address)
            {
                $address->load($address->entity_id);
                if ($address->getData('is_active') == 1)
                {
                    $address1 = $address->getData('street');
                    $address2 = "";
                    $city = $address->getData('city');
                    $state = $address->getData('region');
                    $zip = $address->getData('postcode');
                    $country = $address->getData('country_id');
                    $company = $address->getData('company');
                }
            }

            // set the date for use in sku and order
            $dateArr = explode(" ", $row["change_status_at"]);
            $dated = $dateArr[0] + "T" + $dateArr[1];

            // build order xml (blank)
            $orderNumber = 0;
            $totalWeight = 0;
            $totalGrand = 0;
            $totalShipping = 0;
            $totalTax = 0;
            $totalDiscount = 0;
            $xmlOrder = $this->doOrder($dated, $payType, $orderNumber, $comments, $totalWeight, $totalGrand, $totalShipping, $totalTax, $shippingMethod, $totalDiscount, $orderProcessingInfo);

            // build the sku xml (blank)
            $price = 0;
            $qty = 0;
            $subTotal = 0;
            $basePrice = 0;
            $xmlSkus = trim($this->doSku($plu, $name, $price, $qty, $subTotal, $basePrice, $recType, $recordNumber, $productType, $weight, $handlingCharge, $qtyPricing, $dated));

            // build customer xml
            $xmlCustomer = $this->doCustomer($fname, $lname, $address1, $address2, $city, $state, $zip, $country, $company, $email, $isOptedIn);

            // build the order xml
            $xmlOrders .= trim($this->doOrders($xmlCustomer, $xmlOrder, $xmlSkus));

            $customer = null;
            $address = null;
        }

        // build the queue xml
        $xmlQueue = trim($this->doQueue($xmlOrders));
        return array(base64_encode($xmlQueue));
    }

    public function unsubscribe($email, $storeIds)
    {
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('newsletter_write');
        $newsletterTableName = $resource->getTableName('newsletter/subscriber');
        $writeResult = $write->query("UPDATE $newsletterTableName SET subscriber_status = 3 WHERE subscriber_email = '$email' AND store_id in ($storeIds)");
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
}
?>