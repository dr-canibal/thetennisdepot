<?php
/**
 * Referral Model
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Model_Referral extends Mage_Core_Model_Abstract
{
    const XML_PATH_SUBSCRIPTION_EMAIL_TEMPLATE       = 'rewards/referral/subscription_email_template';
    const XML_PATH_SUBSCRIPTION_EMAIL_IDENTITY       = 'rewards/referral/subscription_email_identity';
    const XML_PATH_CONFIRMATION_EMAIL_TEMPLATE       = 'rewards/referral/confirmation_email_template';
    const XML_PATH_CONFIRMATION_EMAIL_IDENTITY       = 'rewards/referral/confirmation_email_identity';
    
    const STATUS_REFERRAL_SENT = 0;
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('rewardsref/referral');
    }    

    public function getInvites($id){
        return $this->getCollection()->addClientFilter($id);
    }

    public function loadByEmail($customerEmail)
    {
        $this->addData($this->getResource()->loadByEmail($customerEmail));
        return $this;
    }
    


    public function subscribe(Mage_Customer_Model_Customer $parent, $email, $name, $msg="")
    {
        $this->setReferralParentId($parent->getId())
             ->setReferralEmail($email)
             ->setReferralName($name);
        return $this->save() && $this->sendSubscription($parent, $email, $name, $msg);
    }

    public function isSubscribed($email)
    {
        $collection = $this->getCollection()->addEmailFilter($email);
        return $collection->count() ? true : false;
    }

    public function sendSubscription(Mage_Customer_Model_Customer $parent, $destination, $destination_name, $msg="")
    {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $store_id = $this->getStoreId();
        $store_id = empty($store_id) ? Mage::app()->getStore()->getId() : $store_id;

        $email = Mage::getModel('core/email_template');
        $email->setDesignConfig(array('area'=>'frontend', 'store'=>$store_id));
        /* @var $email Mage_Core_Model_Email_Template */


        $template = Mage::getStoreConfig(self::XML_PATH_SUBSCRIPTION_EMAIL_TEMPLATE, $store_id);
        Mage::log($template);
        $recipient = array(
            'email' => $destination,
            'name'  => $destination_name,
        );

        $sender  = array(
            'name' => strip_tags($parent->getFirstname().' '.$parent->getLastname()),
            'email' => strip_tags($parent->getEmail())
        );
        $store_name = Mage::getModel('core/store')->load(Mage::app()->getStore()->getCode())->getName();
        $email->sendTransactional(
            $template,
            $sender,
            $recipient['email'],
            $recipient['name'],
            array(
                'parent'        => $parent,
                'referral'      => $this,
                'store_name'    => $store_name,
                'msg'           => $msg,
            )
        );

        $translate->setTranslateInline(true);

        return $email->getSentSuccess();
    }

    /**
     * @param $parent TBT_Rewards_Model_Customer the person who's making the referral and will earn the points
     * @param $child TBT_Rewards_Model_Customer  the person being referred to the site
     * @param $destination
     * 
     * @return send result          
     */           
    public function sendConfirmation($parent, $child, $destination)
    {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $email = Mage::getModel('core/email_template');
        /* @var $email Mage_Core_Model_Email_Template */        

        $template = Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_EMAIL_TEMPLATE, $this->getStoreId());
        $recipient = array(
            'email' => $destination,
            'name'  => $destination
        );

        $sender  = Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_EMAIL_IDENTITY);

        $email->setDesignConfig(array('area'=>'frontend', 'store'=>$this->getStoreId()))
                ->sendTransactional(
                    $template,
                    $sender,
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'parent'   => $parent,
                        'child'   => $child,
                        'referral' => $this,
                        'store_name' => Mage::getModel('core/store')->load(Mage::app()->getStore()->getCode())->getName()
                    )
                );

        $translate->setTranslateInline(true);
        return $email->getSentSuccess();
    }


}
