<?php
/**
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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Paypal
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * PayPal Standard Checkout Module
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class TBT_Rewards_Model_Paypal_Standard extends Mage_Paypal_Model_Standard
{

    //@nelkaake Sunday April 25, 2010 : Calculated in the base currency.
    public function getPaypalZeroCheckoutFee() {
        return 0.01;
    }

    public function getStandardCheckoutFormFields()
    {
        Mage::getSingleton('rewards/redeem')->refactorRedemptions($this->getQuote()->getAllItems());
        $scf = parent::getStandardCheckoutFormFields();
        if(isset($scf['discount_amount_cart'])) {
            $discountAmount = (float)$scf['discount_amount_cart'];
            if($discountAmount >= $this->getQuote()->getSubtotal()) {
                //@nelkaake Sunday April 25, 2010 : We're discounting the whole amount, so we need to add a premium in order for PayPal to see the output.
                $scf['discount_amount_cart'] = (float)$scf['discount_amount_cart'] - $this->getPaypalZeroCheckoutFee();
                $scf['discount_amount_cart'] = (string) $scf['discount_amount_cart'];
                
            }
            
        }
        //<input id="discount_amount_cart" name="discount_amount_cart" value="259.98" type="hidden"/>

        return $scf;
    }

}

/*
<html><body>You will be redirected to PayPal in a few seconds.<form action="https://www.sandbox.paypal.com/webscr" id="paypal_standard_checkout" name="paypal_standard_checkout" method="POST"><div><input name="form_key" type="hidden" value="hbIgig05he6K3sqr" /></div><input id="business" name="business" value="jay_1221085195_biz@webdevelopmentcanada.ca" type="hidden"/>
<input id="invoice" name="invoice" value="300000011" type="hidden"/>
<input id="currency_code" name="currency_code" value="USD" type="hidden"/>
<input id="paymentaction" name="paymentaction" value="authorization" type="hidden"/>
<input id="return" name="return" value="http://www.wdca.ca/dev/prog/mpg140/paypal/standard/success/" type="hidden"/>
<input id="cancel_return" name="cancel_return" value="http://www.wdca.ca/dev/prog/mpg140/paypal/standard/cancel/" type="hidden"/>
<input id="notify_url" name="notify_url" value="http://www.wdca.ca/dev/prog/mpg140/paypal/ipn/standard/" type="hidden"/>
<input id="bn" name="bn" value="Varien_Cart_WPS_US" type="hidden"/>
<input id="charset" name="charset" value="utf-8" type="hidden"/>
<input id="item_number_1" name="item_number_1" value="1112" type="hidden"/>
<input id="item_name_1" name="item_name_1" value="Chair" type="hidden"/>
<input id="quantity_1" name="quantity_1" value="2" type="hidden"/>
<input id="amount_1" name="amount_1" value="129.99" type="hidden"/>
<input id="tax_cart" name="tax_cart" value="21.45" type="hidden"/>
<input id="discount_amount_cart" name="discount_amount_cart" value="259.98" type="hidden"/>
<input id="cmd" name="cmd" value="_cart" type="hidden"/>

<input id="upload" name="upload" value="1" type="hidden"/>
<input id="city" name="city" value="Cambridge" type="hidden"/>
<input id="country" name="country" value="CA" type="hidden"/>
<input id="email" name="email" value="najibkaake@gmail.com" type="hidden"/>
<input id="first_name" name="first_name" value="John CANADA" type="hidden"/>
<input id="last_name" name="last_name" value="Doe" type="hidden"/>
<input id="zip" name="zip" value="N3C 4M2" type="hidden"/>
<input id="state" name="state" value="ON" type="hidden"/>
<input id="address1" name="address1" value="358 Kerwood Dr." type="hidden"/>
<input id="address_override" name="address_override" value="1" type="hidden"/>
</form></body></html>
*/
