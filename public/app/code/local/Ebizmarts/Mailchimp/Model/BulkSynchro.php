<?php

class Ebizmarts_Mailchimp_Model_BulkSynchro extends Ebizmarts_Mailchimp_Model_MCAPI {

	const WAY_IMPORT    = 'import';
	const WAY_EXPORT    = 'export';

	const BULK_EXTENSION  = 'gz';
	const FILE_EXTENSION  = 'csv';
    const COMPRESS_RATE   = 9;

    const FLDR = 'mailchimp';

    const TD  = '\'';
    const TS  = '~';

    protected $_handler = null;
    protected $_customCol = array();
    protected $_return = array();
    protected $_alreadySubscripted = array();
    protected $_alreadyUnSubscripted = array();
	protected $_batchSubscribe = array();
    protected $_batchUnsubscribe = array();

	public function create($params){

		$this->open(true);
		$helper = Mage::helper('mailchimp');

		if($this->getType() == self::WAY_IMPORT){

			$apikey = $helper->getApiKey();
			if(!$apikey){
				return false;
			}
			$this->MCAPI($apikey);

			$header = 	self::TD . $helper->__('Subscriber Email') . self::TD . self::TS .
						self::TD . $helper->__('Subscriber Status') . self::TD . self::TS .
						self::TD . $helper->__('TimeStamp') . self::TD;

			$this->write($header."\n");

			$statusGroup = array('subscribed', 'unsubscribed', 'cleaned');
			foreach($statusGroup as $status){
				try {
					$subscribers = $this->listMembers($params['list'], $status, null, $params['start'], $params['limit']);
				}catch (Exception $e) {
		        	$helper->addException($e);
		        }
				foreach($subscribers['data'] as $subscriber){
					$data = self::TD . $subscriber['email'] . self::TD . self::TS .
							self::TD . $status . self::TD . self::TS .
							self::TD . $subscriber['timestamp'] . self::TD ;
					$this->write($data."\n");
				}
			}

			$this->close();
			return true;

		}elseif($this->getType() == self::WAY_EXPORT){

			$header = 	self::TD . $helper->__('Magento Subscriber Id') . self::TD . self::TS .
						self::TD . $helper->__('Store Id') . self::TD . self::TS .
						self::TD . $helper->__('Customer Id') . self::TD . self::TS .
						self::TD . $helper->__('Subscriber Email') . self::TD . self::TS .
						self::TD . $helper->__('Subscriber Status') . self::TD;

			$this->write($header."\n");

			$subscribers = Mage::getSingleton('newsletter/subscriber')->getCollection();
			foreach($subscribers as $subscriber){
				$data = self::TD . $subscriber->getSubscriberId() . self::TD . self::TS .
						self::TD . $subscriber->getStoreId() . self::TD . self::TS .
						self::TD . $subscriber->getCustomerId() . self::TD . self::TS .
						self::TD . $subscriber->getSubscriberEmail() . self::TD . self::TS .
						self::TD . $subscriber->getSubscriberStatus() . self::TD;
				$this->write($data."\n");
			}

			$this->close();
			return true;
		}
		return false;
	}

    public function getFileName(){

        return $this->getTime() . "_" . $this->getType() . "_" . $this->getList() . "." . self::FILE_EXTENSION . "." . self::BULK_EXTENSION;
    }

    public function getPath(){

        return Mage::getBaseDir("var") . DS . self::FLDR;
    }

    protected function open($write = false){

        $ioAdapter = new Varien_Io_File();
        try {
            $path = $ioAdapter->getCleanPath($this->getPath());
            $ioAdapter->checkAndCreateFolder($path);
            $filePath = $path . DS . $this->getFileName();
		}catch (Exception $e) {
        	Mage::helper('mailchimp')->addException($e);
        }

        if ($write && $ioAdapter->fileExists($filePath)) {
            $ioAdapter->rm($filePath);
        }
        if (!$write && !$ioAdapter->fileExists($filePath)) {
			$message = Mage::helper('mailchimp')->__('File "%s" does not exist.', $this->getFileName());
			Mage::getSingleton('adminhtml/session')->addError($this->__('Mailchimp General Error: ').$message);
        }

        $mode = $write ? 'wb' . self::COMPRESS_RATE : 'rb';

        try {
            $this->_handler = gzopen($filePath, $mode);
		}catch (Exception $e) {
        	Mage::helper('mailchimp')->addException($e);
        }

        return $this;
    }

    protected function read(){

        if (is_null($this->_handler)) {
			$message = Mage::helper('mailchimp')->__('File handler was unspecified.');
			Mage::getSingleton('adminhtml/session')->addError($this->__('Mailchimp General Error: ').$message);
        }

        $length = '';

        return gzread($this->_handler, $length);
    }

	protected function write($string){

        if (is_null($this->_handler)) {
			$message = Mage::helper('mailchimp')->__('File handler was unspecified.');
			Mage::getSingleton('adminhtml/session')->addError($this->__('Mailchimp General Error: ').$message);
        }

        try {
            gzwrite($this->_handler, $string);
        }catch (Exception $e) {
            Mage::helper('mailchimp')->addException($e);
        }

        return $this;
    }

 	public function close(){

        @gzclose($this->_handler);
        $this->_handler = null;

        return $this;
    }

	public function loadFile($fileName, $filePath){

        list ($time, $type, $list) = explode("_", substr($fileName, 0, strrpos($fileName, ".")));
        $this->addData(array(
            'id'   => $filePath . DS . $fileName,
            'list'   => substr($list, 0, strrpos($list, ".")),
            'created_time' => (int)$time,
            'path' => $filePath,
            'created_object' => new Zend_Date((int)$time)
        ));
        $this->setType($type);
        return $this;
    }

	public function delete(){

        if (!$this->exists()) {
            Mage::throwException(Mage::helper('mailchimp')->__("File does not exist."));
        }

        try {
	        $ioProxy = new Varien_Io_File();
	        $ioProxy->open(array('path'=>$this->getPath()));
	        $ioProxy->rm($this->getFileName());
        }catch (Exception $e) {
            Mage::helper('mailchimp')->addException($e);
        }
        return $this;
	}

	public function exists(){
        return is_file($this->getPath() . DS . $this->getFileName());
    }

	public function getSize(){

        if (!is_null($this->getData('size'))) {
            return $this->getData('size');
        }

        if ($this->exists()) {
            $this->setData('size', filesize($this->getPath() . DS . $this->getFileName()));
            return $this->getData('size');
        }

        return 0;
    }

	public function output(){
        if (!$this->exists()) {
        	Mage::throwException(Mage::helper('mailchimp')->__("File does not exist."));
            return ;
        }

        $ioAdapter = new Varien_Io_File();
        $ioAdapter->open(array('path' => $this->getPath()));

        $ioAdapter->streamOpen($this->getFileName(), 'r');
        while ($buffer = $ioAdapter->streamRead()) {
            echo $buffer;
        }
        $ioAdapter->streamClose();
    }

	protected function getAllCustomers(){

		if(!$this->_customCol || !count($this->_customCol)){
	   		$customers = Mage::getSingleton('customer/customer')
	   					->getCollection()
	   					->addAttributeToSelect('*');
			$customerArray = array();
	    	foreach($customers as $customer){
				$customerArray[$customer->getEmail()] = $customer->getData();
	    	}
	    	if(count($customerArray)) $this->_customCol = $customerArray;
    	}

    	return $this;
	}

	protected function getListSubscribers(){

		$helper = Mage::helper('mailchimp');
		$apikey = $helper->getApiKey();
		if(!$apikey){
			return false;
		}
		$this->MCAPI($apikey);

		$subscribed = $this->listMembers($this->getList(), 'subscribed', null, (int)0, (int)15000);
		if(isset($subscribed['data']) && count($subscribed['data'])){
			foreach($subscribed['data'] as $item){
				$this->_alreadySubscripted[] = $item['email'];
	    	}
		}

		$statusGroup = array('unsubscribed', 'cleaned');
		foreach($statusGroup as $status){
			$unSubscribed = $this->listMembers($this->getList(), $status, null, (int)0, (int)15000);
			if(isset($unSubscribed['data']) && count($unSubscribed['data'])){
				foreach($unSubscribed['data'] as $item){
					$this->_alreadyUnSubscripted[] = $item['email'];
		    	}
			}
		}

    	return $this;
	}

	private function setCustomToHandle($email){

		$customer = Mage::getModel('customer/customer');
		$customer->setListId($this->getList());
		$customer->setEmail($email);
		$customer->setCustomerId('0');
		$customer->setStoreId(Mage::app()->getDefaultStoreView()->getStoreId());
    	$customer->setWebsiteId(Mage::app()->getDefaultStoreView()->getWebsiteId());
    	$customer->setFirstname(($this->getSubscribeConfig('guest_name',$customer->getStoreId()))? $this->getSubscribeConfig('guest_name',$customer->getStoreId()) : 'GUEST');
	    $customer->setLastname(($this->getSubscribeConfig('guest_lastname',$customer->getStoreId()))? $this->getSubscribeConfig('guest_lastname',$customer->getStoreId()) : 'GUEST');

		if(count($this->_customCol) && array_key_exists($email,$this->_customCol)){
			$customer->setCustomerId($this->_customCol[$email]['entity_id']);
			foreach($this->_customCol[$email] as $k=>$v){
				$customer->setData($k,$v);
			}
			if($address = $customer->getDefaultBillingAddress()){
				$addressArray = array();
				foreach($address->getData() as $k=>$v){
					$addressArray[$k] = $v;
				}
				$customer->setAddress($addressArray);
			}
		}

		return $customer;
	}

    public function run(){

    	$chimp   = 0;
    	$general = 0;
    	$this->getAllCustomers();
    	$helper = Mage::helper('mailchimp');
		$lines = gzfile($this->getPath() . DS . $this->getFileName());
		if($this->getType() == self::WAY_EXPORT) $this->getListSubscribers();

		foreach($lines as $k=>$line){

			if($k != 0){

				if($this->getType() == self::WAY_EXPORT){

					list($subscriberId, $storeId, $customerId, $email, $status) = explode(self::TS, str_replace("\n","",str_replace(self::TD,"",$line)));

					if($status == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED && !in_array($email, $this->_alreadySubscripted)){
						$this->_batchSubscribe[] = $helper->getMergeVars($this->setCustomToHandle($email),true);
					}elseif($status == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED && !in_array($email, $this->_alreadyUnSubscripted) && in_array($email, $this->_alreadySubscripted)){
						$this->_batchUnsubscribe[$this->getList()][] = $email;
					}

				}elseif($this->getType() == self::WAY_IMPORT){

					list($email, $action) = explode(self::TS, str_replace("\n","",str_replace(self::TD,"",$line)));

					$customer = $this->setCustomToHandle($email);

					$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);

					if($action == 'subscribed'){
						if((string)$this->getList() == (string)$helper->getGeneralConfig('general',$customer->getStoreId())){
							if(!$subscriber->getSubscriberId()){
								Mage::getModel('mailchimp/subscriber')->quickSubscribe($customer);
								$general++;
							}elseif($subscriber->getSubscriberId() && !$subscriber->isSubscribed()){
								$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
								$subscriber->setIsStatusChanged(true);
								$subscriber->save();
								$general++;
							}
						}
						$customer->setAction(Ebizmarts_Mailchimp_Model_Mailchimp::ACTION_SUBSCRIBE);
					}elseif($action == 'unsubscribed' || $action == 'cleaned'){
						if($subscriber->isSubscribed()){
							$subscriber->unsubscribe();
							$general++;
						}
						$customer->setAction(Ebizmarts_Mailchimp_Model_Mailchimp::ACTION_UNSUBSCRIBE);
					}
					$chimp += Mage::getModel('mailchimp/subscripter')->changeStatus($customer);

				}else{
					$this->_return['error'] = $helper->__('The way of bulk does not exists.');
				}
			}
		}

		if($this->getType() == self::WAY_EXPORT){

 			if(!count($this->_batchSubscribe) && !count($this->_batchUnsubscribe)){
				$this->_return['notice'] = $helper->__('This file is already synchronized.');
				return $this->_return;
			}

			$apikey = $helper->getApiKey();
			if(!$apikey){
				return false;
			}
			$this->MCAPI($apikey);
			$this->setStore(Mage::app()->getDefaultStoreView()->getStoreId());

			$this->batchSubscribe();
			$this->batchUnsubscribe();

		}elseif($this->getType() == self::WAY_IMPORT){
			$this->_return = array('chimp'=>$chimp,
								   'general'=>$general);
		}

		return $this->_return;
    }

	private function batchSubscribe(){

		if(count($this->_batchSubscribe)) {

			$helper = Mage::helper('mailchimp');
			$apikey = $helper->getApiKey();

			if(!$apikey){
				return false;
			}
			$this->setStore(Mage::app()->getDefaultStoreView()->getStoreId());
			$this->MCAPI($apikey);

			$this->_return = $this->listBatchSubscribe($this->getList(),
													   $this->_batchSubscribe,
													   (bool)$helper->getSubscribeConfig('double_optin',$this->getStore()),
													   (bool)$helper->getSubscribeConfig('update_existing',$this->getStore()),
													   (bool)$helper->getSubscribeConfig('replace_interests',$this->getStore()));
			if(isset($this->_return['error_count']) && $this->_return['error_count']) {
				Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('%s error(s) found, please review the log file for further details..',$this->_return['error_count']));
				foreach($this->_return['errors'] as $err){
					if($err){
						Mage::log($err, Zend_Log::DEBUG, 'mailChimp_BulkErrors.log');
					}
				}
			}

		}
		return $this;
	}

	private function batchUnsubscribe(){

		if(count($this->_batchUnsubscribe)) {

			$helper = Mage::helper('mailchimp');
			$apikey = $helper->getApiKey();

			if(!$apikey){
				return false;
			}
			$this->setStore(Mage::app()->getDefaultStoreView()->getStoreId());
			$this->MCAPI($apikey);

			foreach($this->_batchUnsubscribe as $listId=>$batchUnsubscribe){
				$this->_return = $this->listBatchUnsubscribe($listId,
												 $batchUnsubscribe,
												 (bool)$helper->getUnSubscribeConfig('delete_member',$this->getStore()),
												 (bool)$helper->getUnSubscribeConfig('send_goodbye',$this->getStore()),
												 (bool)$helper->getUnSubscribeConfig('send_notify',$this->getStore()));
				if(isset($this->_return['error_count']) && $this->_return['error_count']) {
					Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('%s error(s) found, please review the log file for further details..',$this->_return['error_count']));
					foreach($this->_return['errors'] as $err){
						if($err){
							Mage::log($err, Zend_Log::DEBUG, 'mailChimp_BulkErrors.log');
						}
					}
				}
			}
		}
		return $this;
	}

    public function adminMassDelete($params){

		if(count($params['customer'])){
			$model = Mage::getModel('mailchimp/subscripter');
			foreach($params['customer'] as $id){
				$customer = Mage::getModel('customer/customer')->load($id);
				$customer->setCustomerId($id);
				$lists = $model->getListsByCustomer($customer);
				if(count($lists)){
					foreach($lists as $k => $v){
						$email = $customer->getEmail();
						$this->setList($k);
						$this->getListSubscribers();
						if(!in_array($email, $this->_alreadyUnSubscripted) && in_array($email, $this->_alreadySubscripted)){
							$this->_batchUnsubscribe[$this->getList()][] = $email;
							$customer->setListId($this->getList());
							$customer->setAction(Ebizmarts_Mailchimp_Model_Mailchimp::ACTION_UNSUBSCRIBE);
							$model->changeStatus($customer);
						}
					}
				}
			}
		}

		$this->batchUnsubscribe();

		return $this;
    }

    public function adminMassSubscribe($params){

		if(count($params['customer'])){
			$helper = Mage::helper('mailchimp');
			$model = Mage::getModel('mailchimp/subscripter');

			$this->setStore(Mage::app()->getDefaultStoreView()->getStoreId());
			$this->setList($helper->getGeneralConfig('general',$this->getStore()));
			$this->getAllCustomers();
			$this->getListSubscribers();
			foreach($params['customer'] as $id){
				$customer = Mage::getModel('customer/customer')->load($id);
				$customer->setCustomerId($id);
				$email = $customer->getEmail();
				if(!in_array($email, $this->_alreadySubscripted)){
					$this->_batchSubscribe[] = $helper->getMergeVars($this->setCustomToHandle($email),true);
					$customer->setListId($this->getList());
					$customer->setAction(Ebizmarts_Mailchimp_Model_Mailchimp::ACTION_SUBSCRIBE);
					$model->changeStatus($customer);
				}
			}
		}

		$this->batchSubscribe();

		return $this;
    }

    public function newsMassDelete($params){

		$model = Mage::getModel('mailchimp/subscripter');
		foreach($params['subscriber'] as $id){
			$customer = Mage::getModel('newsletter/subscriber')->load($id);
			$customer->setEmail($customer->getSubscriberEmail());
			$lists = $model->getListsByCustomer($customer);
			if(count($lists)){
				foreach($lists as $k => $v){
					$this->setList($k);
					$this->getListSubscribers();
					if(!in_array($customer->getEmail(), $this->_alreadyUnSubscripted) && in_array($customer->getEmail(), $this->_alreadySubscripted)){
						$this->_batchUnsubscribe[$this->getList()][] = $customer->getEmail();
						$customer->setListId($this->getList());
						$customer->setAction(Ebizmarts_Mailchimp_Model_Mailchimp::ACTION_UNSUBSCRIBE);
						$model->changeStatus($customer);
					}
				}
			}
		}

		$this->batchUnsubscribe();

		return $this;
    }
}
