<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Featuredproducts
 * @copyright  Copyright (c) 2008-2009 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */

class AW_Featuredproducts_Model_System_Config_Source_Switchertype
{

    public function toOptionArray()
    {
        return array(
			array('value'=>'bubbles', 'label'=>"Thumbnails"),
            array('value'=>'digits', 'label'=>"Digits"),
            array('value'=>'none', 'label'=>"None")
        );
    }

}
