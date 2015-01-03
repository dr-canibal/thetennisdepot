<?php
class TBT_Rewards_Model_Observer_Checkout_Onepage extends Varien_Object
{
	public function __construct() { }
	
    public function payForShippingWithPoints($observer)
    {
        $this->setRequest($observer->getControllerAction()->getRequest());
    	
    	$pay_for_shipping_with_points = $this->getRequest()->getParam('pay_for_shipping_with_points', '');
        
    	if($pay_for_shipping_with_points) {
        	$quote = Mage::getSingleton('rewards/session')->getQuote();
            $shipaddr = $quote->getShippingAddress();
            $total_shipping_value = $shipaddr->getShippingAmount();
            $current_points_spending = Mage::getSingleton('rewards/session')->getPointsSpending();
            Mage::log("Paying for shipping with points..." );
                
            $rule_ids = explode(',', $quote->getAppliedRedemptions());
            foreach($rule_ids as $rid) {
                $salesrule = Mage::helper('rewards/transfer')->getSalesRule($rid);
                if($salesrule->getPointsAction() != 'discount_by_points_spent') continue;
                if(!$salesrule->getDiscountAmount()) continue; // discount amount should not be empty (so we dont divide by zero)
            Mage::log("Points step according to quote is {$quote->getPointsStep()}" );
                if($salesrule->getPointsAmount() == $quote->getPointsStep()) {
                    $uses_to_zero_shipping = ceil($total_shipping_value / $salesrule->getDiscountAmount());
                    Mage::getSingleton('rewards/session')->setPointsSpending($uses_to_zero_shipping + $current_points_spending);
                    Mage::log("Added {$uses_to_zero_shipping} to existing points uage of {$current_points_spending}" );
                    break;
                }
            }
        
        }
    	
    	return $this;
    }
}