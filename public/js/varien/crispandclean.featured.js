/*!
 * CrispAndClean Featured Slider
 *
 * @category    WorryFreeLabs
 * @package     js
 * @copyright   Copyright (c) 2010 Worry Free Labs, LLC. (http://worryfreelabs.com/)
 * @author      Oleksandr Bernatskyi
 */
;(function($)
{
	$.fn.extend
	({
		crispAndCleanFeatured: function(options)
		{
			var defaults =
			{
				animationDuration: 1200,
				itemsPerSlide: 2,
				reverseDirection: false,
				slideWidth: 480,
				slideHeight: 259,
				sliderControl: null
			};
			
			var options = $.extend(defaults, options);
			
			return this.each
			(
				function()
				{
					var o = options;
					var $this = $(this);
					var $slider = $('ul', $this);
					
					// Number of slides
					var slidesNumber = Math.ceil($('> li', $slider).length / o.itemsPerSlide);
					
					// CSS adaptation
					$slider.css
					({
						left: o.reverseDirection ? -o.slideWidth * (slidesNumber - 1) : 0,
						top: 0,
						width: o.slideWidth * slidesNumber,
						height: o.slideHeight,
						position: 'relative'
					});
					
					// Animation effect
					var $control = $(o.sliderControl);
					
					var animationCallback = function()
					{
						var $leftOffset = Math.abs(parseInt($slider.css('left')));
						
						if ($leftOffset == 0 || !$control.is('.prev') && $leftOffset < o.slideWidth * slidesNumber - o.slideWidth)
						{
							$control.attr('class', 'next');
						}
						else
						{
							$control.attr('class', 'prev');
						}
					};
					
					$control.click
					(
						function()
						{
							if ($slider.is(':animated'))
							{
								return false;
							}
							
							if ($control.is('.prev'))
							{
								$slider.animate({left: '+=' + o.slideWidth}, o.animationDuration, animationCallback);
							}
							else
							{
								$slider.animate({left: '-=' + o.slideWidth}, o.animationDuration, animationCallback);
							}
							
							return false;
						}
					);
				}
			);
		}
	});
})(jQuery);