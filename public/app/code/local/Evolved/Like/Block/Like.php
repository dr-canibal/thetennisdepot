<?php

/**
 * Retail Evolved - Facebook Like Button
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA that is bundled with this
 * package in the file EVOLVED_EULA.txt.
 * It is also available through the world-wide-web at this URL:
 * http://retailevolved.com/eula-1-0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to service@retailevolved.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * You may edit this file, but only at your own risk, as long as it is within
 * the constraints of the license agreement. Before upgrading the module (not Magento), 
 * be sure to back up your original installation as the upgrade may override your
 * changes.
 *
 * @category   Evolved
 * @package    Evolved_Like
 * @copyright  Copyright (c) 2010 Kaelex Inc. DBA Retail Evolved (http://retailevolved.com)
 * @license    http://retailevolved.com/eula-1-0 (Retail Evolved EULA 1.0)
 */

class Evolved_Like_Block_Like extends Mage_Core_Block_Template
{
	public function getProduct()
    {
		$_product = $this->hasData('product') ? $this->getData('product') : Mage::registry('product');
		
		return $_product;
    }
	
	public function getCategory()
	{
		$_category = $this->getIsCategoryButton() ? Mage::registry('current_category') : $this->getData('category');
		
		return $_category;
	}
	
	protected function getProductUrl() {
		$_url = Mage::getBaseUrl('web');

		if(!Mage::getStoreConfig('web/seo/use_rewrites')) {
			$_url .= 'index.php/';
		}
		
		if (Mage::getStoreConfig('web/url/use_store') && $storeCode = Mage::app()->getStore()->getCode()) {
			$_url .= $storeCode . '/';
		}
		
		// Check if user wants to use the rewrite
		if(Mage::helper('evlike')->getLikeConfig('ev_use_rewrite')) {
			// Check if URL rewrite is available
			$rewrite = Mage::getModel('core/url_rewrite');
			
			if ($this->getProduct()->getStoreId()) {
                $rewrite->setStoreId($this->getProduct()->getStoreId());
            }
            else {
                $rewrite->setStoreId(Mage::app()->getStore()->getId());
            }

			$idPath = 'product/'.$this->getProduct()->getId();
			if($this->getProduct()->getCategoryId() && Mage::getStoreConfig('catalog/seo/product_use_categories') && Mage::helper('evlike')->getLikeConfig('ev_use_long')) {
				$idPath .= '/'.$this->getProduct()->getCategoryId();
			}
			
			$rewrite->loadByIdPath($idPath);
			
			if ($rewrite->getId()) {
				$_url .= $rewrite->getRequestPath();
				return $_url;
            }
		}
		
		$_url .= $this->getProduct()->getUrlKey() . Mage::helper('catalog/product')->getProductUrlSuffix();
		
		return $_url;
	}
	
	public function getCategoryUrl() {
		$_url = Mage::getBaseUrl('web');

		if(!Mage::getStoreConfig('web/seo/use_rewrites')) {
			$_url .= 'index.php/';
		}
		
		if (Mage::getStoreConfig('web/url/use_store') && $storeCode = Mage::app()->getStore()->getCode()) {
			$_url .= $storeCode . '/';
		}
		
		// Check if user wants to use the rewrite
		if(Mage::helper('evlike')->getLikeConfig('ev_use_rewrite')) {
			// Check if URL rewrite is available
			$rewrite = Mage::getModel('core/url_rewrite');
			
			if ($this->getCategory()->getStoreId()) {
                $rewrite->setStoreId($this->getCategory()->getStoreId());
            }
            else {
                $rewrite->setStoreId(Mage::app()->getStore()->getId());
            }

			$idPath = 'category/'.$this->getCategory()->getId();
			
			$rewrite->loadByIdPath($idPath);
			
			if ($rewrite->getId()) {
				$_url .= $rewrite->getRequestPath();
				return $_url;
            }
		}
		
		$_url .= $this->getCategory()->getUrlPath() . Mage::helper('catalog/category')->getCategoryUrlSuffix();
		
		return $_url;
	}
	
	public function getCurrentUrl() 
	{
		$_url = $this->getCategory() ? $this->getCategoryUrl() : $this->getProductUrl();
		return $_url;
	}
	
	public function getEncodedUrl() 
	{
		$_url = $this->getCategory() ? $this->getCategoryUrl() : $this->getProductUrl();
		$_encodedUrl = urlencode($_url);
		
		$_pos = strpos($_encodedUrl, "%3F___SID");
		
		// Take off the SID portion
		if($_pos !== false) {
			$_encodedUrl = substr($_encodedUrl, 0, $_pos);
		}
		
		return $_encodedUrl;
	}
	
	protected function getListMode() {
		if ($_mode = Mage::getSingleton('catalog/session')->getDisplayMode()) {
			return $_mode;
		}
			
		switch (Mage::getStoreConfig('catalog/frontend/list_mode')) {
            case 'grid':
			case 'grid-list':
                return 'grid';
                break;

		    case 'list':
			case 'list-grid':
	            return 'list';
				break;
        }

		return false;
	}
	
	public function getLayoutStyle() {
		$_layout = '';
		
		if(Mage::registry('product')) {
			$_layout = Mage::helper('evlike')->getLikeConfig('ev_facebook_layout');
		} else if ($this->getCategory()) {
			$_layout = Mage::helper('evlike')->getLikeCategoryConfig('ev_like_category_layout');
		} else if ($this->getLayout()->getBlock('likebutton')) {
			$_layout =  $this->getListMode() == 'grid' ? Mage::helper('evlike')->getLikeGridConfig('ev_like_grid_layout') : Mage::helper('evlike')->getLikeListConfig('ev_like_list_layout');
		} 
		
		return $_layout;
	}
	
	public function getShowFaces() {
		$_showFaces = false;
		
		if(Mage::registry('product')) {
			$_showFaces = Mage::helper('evlike')->getLikeConfig('ev_show_faces');
		} else if ($this->getCategory()) {
			$_showFaces = Mage::Helper('evlike')->getLikeCategoryConfig('ev_like_category_show_faces');
		} else if ($this->getLayout()->getBlock('likebutton')) {
			$_showFaces = $this->getListMode() == 'grid' ? Mage::helper('evlike')->getLikeGridConfig('ev_like_grid_show_faces') : Mage::helper('evlike')->getLikeListConfig('ev_like_list_show_faces');
		}
		
		$_showFaces = $_showFaces ? 'true' : 'false';
		
		return $_showFaces;
	}
	
	public function getLikeWidth() {
		$_width = '';
		
		if(Mage::registry('product')) {
			$_width = Mage::helper('evlike')->getLikeConfig('ev_like_width');
		} else if ($this->getCategory()) {
			$_width = Mage::helper('evlike')->getLikeCategoryConfig('ev_like_category_width');
		} else if ($this->getLayout()->getBlock('likebutton')) {
			$_width = $this->getListMode() == 'grid' ? Mage::helper('evlike')->getLikeGridConfig('ev_like_grid_width') : Mage::helper('evlike')->getLikeListConfig('ev_like_list_width');
		}
		
		return $_width;
	}
	
	public function getLikeHeight() {
		$_height = '';
		
		if(Mage::registry('product')) {
			$_height = Mage::helper('evlike')->getLikeConfig('ev_like_height');
		} else if ($this->getCategory()) {
			$_height = Mage::helper('evlike')->getLikeCategoryConfig('ev_like_category_height');
		} else if ($this->getLayout()->getBlock('likebutton')) {
			$_height = $this->getListMode() == 'grid' ? Mage::helper('evlike')->getLikeGridConfig('ev_like_grid_height') : Mage::helper('evlike')->getLikeListConfig('ev_like_list_height');
		}
		
		return $_height;
	}
}