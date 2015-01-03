/*!
 * CrispAndClean
 *
 * @category    CrispAndClean
 * @package     js
 * @copyright   Copyright (c) 2010 Worry Free Labs, LLC. (http://worryfreelabs.com/)
 * @author      Oleksandr Bernatskyi
 */

;function toggleMenu(el, over) {}

jQuery.noConflict();

jQuery(document).ready
(
	function()
	{
		/**
		 * Navigation
		 */
		jQuery('#nav')
			.find('li')
				.removeAttr('onmouseover')
				.removeAttr('onmouseout')
				.unbind('mouseover')
				.unbind('mouseout')
				.hover
				(
					function()
					{
						jQuery(this).toggleClass('over');
					}
				)
				.end()
			.find('ul.level2')
				.parent('li')
					.removeClass('parent')
					.end()
				.remove()
				.end()
			.find('li.level0 > a')
				.prepend('<span class="white"></span>')
				.end()
			.find('ul.level0')
				.wrap('<div class="dropdown"></div>')
				.end()
			.find('li.level0')
				.each
				(
					function()
					{
						var $this = jQuery(this);
						var $lists = jQuery('li.level1:has(ul)', $this);
						var $dropdown = jQuery('.dropdown', $this);
						
						var width = $dropdown.width();
						var itemsNumber = $lists.length;
						
						if (itemsNumber > 5)
						{
							itemsNumber = 5;
						}
						
						$dropdown.css('width', itemsNumber * width - 10);
						
						$lists
							.parent()
								.css({width: parseInt(itemsNumber * width - 36), float: 'left', 'padding-right': 0})
								.end()
							.css({width: Math.abs(parseInt((itemsNumber * width - 36) / itemsNumber - 10)), float: 'left', margin: '5px 10px 5px 0'});
					}
				);
		
		jQuery('#footer ul.level0').remove();
		
		
		/**
		 * Product page and Cart
		 */
		jQuery('.tabs-and-content .tabs').tabs();
		
		jQuery('.sidebar .block:last-child').addClass('last');
		jQuery('.top-cart').hover
		(
			function()
			{
				jQuery('#topLinks').toggleClass('open');
			}
		);
		
		jQuery('.fancybox').fancybox
		({
			titlePosition: 'inside'
		});
		
		
		/**
		 * Three-way slider
		 */
		jQuery('#slideshow').threeWaySlider
		({
			mainSlideWidth: 630,
			mainSlideHeight: 400,
			secondSlideWidth: 310,
			secondSlideHeight: 190,
			thirdSlideWidth: 310,
			thirdSlideHeight: 190,
			controls: true,
			controlsContainer: '#slideshow-controls',
			autoMode: true,
			pauseInterval: 5000
		});
		
		
		/**
		 * Featured slider
		 */
		jQuery('#featured div.left').crispAndCleanFeatured
		({
			reverseDirection: true,
			sliderControl: '#controls div.left a'
		});
		
		jQuery('#featured div.right').crispAndCleanFeatured
		({
			sliderControl: '#controls div.right a'
		});
	}
);
