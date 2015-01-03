<?php

/**
 * Fooman Order = Invoice Number
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Fooman
 * @package    SameOrderInvoiceNumber extending Magento Mage_Sales
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fooman_SameOrderInvoiceNumber_Model_Mysql4_Order_Shipment extends Mage_Sales_Model_Mysql4_Order_Shipment
{
    public function setNewIncrementId(Varien_Object $object)
    {

        if ($object->getIncrementId()) {
            return $this;
        }

        $prefix = Mage::getStoreConfig('sameorderinvoicenumber/settings/shipmentprefix',$object->getStore()->getId());
        $incrementId = Mage::getModel('sales/order')->load($object->getOrderId())->getIncrementId();

        if (false!==$incrementId) {
            $object->setIncrementId($prefix.$incrementId);
        }

        return $this;
    }
}