<?php
class Eznewsletter_Ezsubscribers_Model_Ezutilities_Api extends Mage_Api_Model_Resource_Abstract
{
    public function getVersion()
    {
        $arrVersion = array(1, 18);
        return $arrVersion;
    }

    public function getServerDateTime()
    {
        $dateShifted = date("Y-m-d H:i:s", time());
        $dateArr = explode(" ", $dateShifted);
        $dated = $dateArr[0] . "T" . $dateArr[1];
        return $dated;
    }

    public function getDateShifted($date)
    {
        $dateShifted = date("Y-m-d H:i:s", strtotime($date) + Mage::getModel('core/date')->getGmtOffset());
        $dateArr = explode(" ", $dateShifted);
        $dated = $dateArr[0] . "T" . $dateArr[1];
        return $dated;
    }

    public function getTimeZone()
    {
        $tz = Mage::app()->getStore()->getConfig('general/locale/timezone');
        return $tz;
    }
    
    public function getGMTOffset()
    {
        $dateTime = new DateTime("now", new DateTimeZone(Mage::app()->getStore()->getConfig('general/locale/timezone')));
        return $dateTime->getOffset();
    }
    
    public function getStoreIds()
    {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $storeTableName = $resource->getTableName('core/store');
        $readresult = $read->query("SELECT * FROM $storeTableName");

        $storeIds = "";
        while ($row = $readresult->fetch())
        {
            $storeIds .= $row['store_id'] . ",";
        }

        $storeIds = trim($storeIds, ",");

        return $storeIds;
    }

    public function getStoreIdsByWebsiteCode($websiteCode)
    {
        $storeIds = "";
        
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $websiteTableName = $resource->getTableName('core/website');
        $readresult = $read->query("SELECT * FROM $websiteTableName WHERE code = '$websiteCode'");
        //Mage::log("SELECT * FROM $websiteTableName WHERE code = '$websiteCode'");
        while ($row = $readresult->fetch())
        {
            $websiteId = $row['website_id'];
        }
        if (!empty($websiteId))
        {
            $storeTableName = $resource->getTableName('core/store');
            $readresult = $read->query("SELECT * FROM $storeTableName WHERE website_id IN ($websiteId)");
            //Mage::log("SELECT * FROM $storeTableName WHERE website_id IN ($websiteId)");
            while ($row = $readresult->fetch())
            {
                $storeIds .= $row['store_id'] . ",";
            }

            $storeIds = trim($storeIds, ",");
        }

        return $storeIds;
    }

    public function getStoreIdsByStoreName()
    {
        $storeIds = "";

        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $storeGroupTableName = $resource->getTableName('core/store/group');
        $readresult = $read->query("SELECT * FROM $storeGroupTableName");
        while ($row = $readresult->fetch())
        {
            $storeGroupId = $row['group_id'];
        }
        if (!empty($websiteId))
        {
            $storeTableName = $resource->getTableName('core/store');
            $readresult = $read->query("SELECT * FROM $storeTableName WHERE group_id IN ($storeGroupId)");
            while ($row = $readresult->fetch())
            {
                $storeIds .= $row['store_id'] . ",";
            }

            $storeIds = trim($storeIds, ",");
        }

        return $storeIds;
    }

    public function getStoreIdsByStoreViewCode($storeViewCode)
    {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $storeTableName = $resource->getTableName('core/store');
        $readresult = $read->query("SELECT * FROM $storeTableName WHERE code = '$storeViewCode'");
        $storeIds = "";
        while ($row = $readresult->fetch())
        {
            $storeIds .= $row['store_id'] . ",";
        }

        $storeIds = trim($storeIds, ",");

        return $storeIds;
    }

    public function getEntityIdByEntityModel($entity_model)
    {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $eavEntityType = $resource->getTableName('eav/entity_type');
        //Mage::log("SELECT entity_type_id FROM $eavEntityType WHERE entity_model = '$entity_model'");
        $readresult = $read->query("SELECT entity_type_id FROM $eavEntityType WHERE entity_model = '$entity_model'");
        $entityId = "";

        while ($row = $readresult->fetch())
        {
            $entityId = $row['entity_type_id'] . ",";
        }
        $entityId = trim($entityId, ",");

        return $entityId;
    }

    public function getOrderStatuses()
    {
        $resource = Mage::getConfig();
        $retval = $resource->getNode('global/sales/order/statuses')->asXML();
        return base64_encode($retval);
    }
}
?>