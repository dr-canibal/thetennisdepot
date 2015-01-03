<?php
class Eznewsletter_Ezsubscribers_Model_Ezsignups_Api extends Mage_Api_Model_Resource_Abstract
{
    public function getAllSignups($dateLastRun, $status, $storeIds)
    {
        // adjust the date to match the timezone of the clients installation
        $dateLastRun = date("Y-m-d H:i:s", strtotime($dateLastRun) + Mage::getModel('core/date')->getGmtOffset());

        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('newsletter_read');
        $newsletterTableName = $resource->getTableName('newsletter/subscriber');
        $readresult = $read->query("SELECT * FROM $newsletterTableName WHERE subscriber_status = $status AND change_status_at >= '$dateLastRun' AND store_id in ($storeIds)");
        Mage::log("SELECT * FROM $newsletterTableName WHERE subscriber_status = $status AND change_status_at >= '$dateLastRun' AND store_id in ($storeIds)");
        
        while ($row = $readresult->fetch())
        {
            $subscribers[] = $row['subscriber_email'];
        }
        if (empty($subscribers))
        {
            return array();
        }
        else
        {
            return $subscribers;
        }
    }

    public function unsubscribe($email, $storeIds)
    {
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('newsletter_write');
        $newsletterTableName = $resource->getTableName('newsletter/subscriber');
        $writeResult = $write->query("UPDATE $newsletterTableName SET subscriber_status = 3 WHERE subscriber_email = '$email' AND store_id in ($storeIds)");
    }
}
?>