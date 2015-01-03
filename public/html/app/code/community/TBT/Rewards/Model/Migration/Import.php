<?php
/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Sales Rule Rule
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Migration_Import extends Varien_Object
{
    const DATA_CATALOGRULE_RULE =   TBT_Rewards_Model_Migration_Export::DATA_CATALOGRULE_RULE;
    const DATA_SALESRULE_RULE =     TBT_Rewards_Model_Migration_Export::DATA_SALESRULE_RULE;
    const DATA_SPECIAL_RULE =       TBT_Rewards_Model_Migration_Export::DATA_SPECIAL_RULE;
    const DATA_CURRENCY =           TBT_Rewards_Model_Migration_Export::DATA_CURRENCY;
    const DATA_CONFIG =             TBT_Rewards_Model_Migration_Export::DATA_CONFIG;
    const EXT =                     TBT_Rewards_Model_Migration_Export::EXT;
    
    public function importFromFile($filename) {
        $sinput = file_get_contents($filename);
        return $this->importFromSerializedData($sinput);
    }
    
    public function importFromSerializedData($data) {
        $input = unserialize($data);
        return $this->importFromData($input);
    }
    public function importFromData($data) {
        if(isset($data[self::DATA_CATALOGRULE_RULE])) {
            $this->importAllCatalogruleRuleData($data[self::DATA_CATALOGRULE_RULE]);
        }
        if(isset($data[self::DATA_SALESRULE_RULE])) {
            $this->importAllSalesruleRuleData($data[self::DATA_SALESRULE_RULE]);
        }
        if(isset($data[self::DATA_SPECIAL_RULE])) {
            $this->importAllSpecialRuleData($data[self::DATA_SPECIAL_RULE]);
        }
        if(isset($data[self::DATA_CURRENCY])) {
            $this->importCurrencyData($data[self::DATA_CURRENCY]);
        }
        if(isset($data[self::DATA_CONFIG])) {
            $this->importConfigData($data[self::DATA_CONFIG]);
        }
        return $this;
        //saveConfig($path, $value,
    }
    
    public function importAllCatalogruleRuleData($rules_data) {
        return $this->importModelData($rules_data, 'rewards/catalogrule_rule');
    }
    
    public function importAllSalesruleRuleData($rules_data) {
        return $this->importModelData($rules_data, 'rewards/salesrule_rule');
    }
    public function importAllSpecialRuleData($rules_data) {
        return $this->importModelData($rules_data, 'rewards/special');
    }
    
    public function importCurrencyData($curencies_data) {
        return $this->importModelData($curencies_data, 'rewards/currency');
    }
    
    public function importModelData($models_data, $model_key) {
        $m = Mage::getModel($model_key);
        foreach($models_data as $md) {
            
            $m = Mage::getModel($model_key);
            if( $m->getWebsiteIds() ) {
                $m->setWebsiteIds(  implode(",", $m->getWebsiteIds())  );
            }
            $m = Mage::getModel($model_key)->setData($md);
            $m->saveWithId();
        }
        return $this;
    }
    
    public function importRewardsConfigData() { 
        return $this->getConfigData('rewards');
    }
    
    public function importConfigData($data) { 
		$config_table = Mage::getConfig()->getTablePrefix() ."core_config_data";
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		foreach($data as $data_row) {
            $select = $write->insert($config_table, $data_row);
        }
        return $this;
    }
    
    
    
    
}