<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Facebooklink
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_FBIntegrator_Block_Adminhtml_System_Config_Form_Fieldset extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {

    /**
     * Render fieldset html
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {
	$html = $this->_getHeaderHtml($element);

	foreach ($element->getElements() as $field) {
	    $html.= $field->toHtml();
	}
	$html .= "<tr>
			<td class=\"label\"></td>
			<td class=\"value\">
			<button onclick=\"testApp()\" type=\"button\"><span>".$this->__('Test Applicaction')."</span></button>
                <div id='fb-test-app-result'></div>
			</td>
		 </tr>
         <script type=\"text/javascript\">
         function testApp(){

            if($('fbintegrator_app_api_key').getValue() == '' || $('fbintegrator_app_secret').getValue() == ''){
                $('fb-test-app-result').update();
                return alert('".$this->__('Please fill App ID and App Secret fields!')."');
            }
                
            new Ajax.Request('".Mage::getSingleton('core/url')->getUrl('fbintegrator/facebook/checkapp')."', {
                method:'get',
                parameters: {
                    app_id: $('fbintegrator_app_api_key').getValue(),
                    app_secret: $('fbintegrator_app_secret').getValue()
                },
                onSuccess: function(transport){
                      var response = transport.responseText;
                      var class = 'error';
                      var mess  = '".$this->__('Test Failed! Wrong App ID or App Secret.')."';
                      if(response == 1){
                        class = 'success';
                        mess = '".$this->__('Test Complete!')."';
                      }
                      $('fb-test-app-result').setAttribute('class', class);
                      $('fb-test-app-result').update(mess);
                    }
              });
         }
         </script>
		 ";
	$html .= $this->_getFooterHtml($element);

	return $html;
    }
}