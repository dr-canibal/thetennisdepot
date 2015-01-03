/*!
 * WorryFreeLabs Three-way Slider
 *
 * @version     1.2.1
 * @category    WorryFreeLabs
 * @package     js
 * @copyright   Copyright (c) 2010 Worry Free Labs, LLC. (http://worryfreelabs.com/)
 * @author      Oleksandr Bernatskyi
 */
;(function($)
{
	$.fn.extend
	({
		threeWaySlider: function(options)
		{
			var defaults =
			{
				animationDuration: 1200,
				mainSlideWidth: 627,
				mainSlideHeight: 398,
				secondSlideWidth: 310,
				secondSlideHeight: 188,
				thirdSlideWidth: 310,
				thirdSlideHeight: 188,
				overlay: '.overlay',
				overlayControl: null,
				controls: false,
				controlsContainer: null,
				autoMode: false,
				pauseInterval: 3000
			};
			
			var options = $.extend(defaults, options);
			
			return this.each
			(
				function()
				{
					var o = options;
					var $this = $(this);
					var $slideList = $('ul', $this);
					
					// Number of slides
					var slidesNumber = $('> li', $slideList).length;
					
					// Slide wrapper
					var slideWrapper = '<div class="slide"></div>';
					
					// Sliders
					var $slider = $slideList.find('li:first');
					var $slidePattern = $('<div class="slider">' + slideWrapper + '</div>').css('position', 'relative');
					
					// Main slider
					$('div.main', $slider).wrapInner
					(
						$slidePattern.clone()
							.css({
								top: 0,
								left: 0,
								width: o.mainSlideWidth,
								height: o.mainSlideHeight * slidesNumber
							})
					).css('position', 'relative');
					
					var $mainSlider = $('div.main div.slider', $slider);
					
					// Second Slider
					$('div.second', $slider).wrapInner
					(
						$slidePattern.clone()
							.css({
								top: (-slidesNumber + 1) * o.secondSlideHeight,
								left: 0,
								width: o.secondSlideWidth,
								height: o.secondSlideHeight * slidesNumber
							})
					).css('position', 'relative');
					
					var $secondSlider = $('div.second div.slider', $slider);
					
					// Third Slider
					$('div.third', $slider).wrapInner
					(
						$slidePattern.clone()
							.css({
								top: 0,
								left: 0,
								width: o.thirdSlideWidth * slidesNumber,
								height: o.thirdSlideHeight
							})
					).css('position', 'relative');
					
					var $thirdSlider = $('div.third div.slider', $slider);
					
					
					// Move all images into the slider
					$('li:not(:first)', $slideList).each
					(
						function()
						{
							var $this = $(this);
							
							$('div.main', $this).wrapInner(slideWrapper).children().appendTo($mainSlider);
							$('div.second', $this).wrapInner(slideWrapper).children().prependTo($secondSlider);
							$('div.third', $this).wrapInner(slideWrapper).children().appendTo($thirdSlider);
							
							$this.remove();
						}
					);
					
					
					// Controls generator
					var $controls = null;
					
					if (o.controls)
					{
						$controls = $('<ul></ul>').appendTo($(o.controlsContainer));
						
						var controlClick = function(event)
						{
							event.preventDefault();
							disableAutoMode();
							
							var index = $(this).parent().index();
							
							animate(index);
						};
						
						var index;
						
						for (index = 0; index < slidesNumber; index++)
						{
							$('<a href="#"></a>')
								.bind('click', controlClick)
								.wrap('<li></li>')
								.parent()
									.appendTo($controls);
						}
					}
					
					
					// Current slide number, used in animation function
					var currentSlide = 0;
					
					// Set the current slide number and mark the control link as active
					function setCurrentSlide(slideIndex)
					{
						currentSlide = parseInt(slideIndex) < slidesNumber ? parseInt(slideIndex) : 0;
						
						if (o.controls)
						{
							$('a', $controls).removeClass('active');
							$('li:eq(' + currentSlide + ')', $controls).find('a').addClass('active');
						}
					}
					
					
					// Actual animation effect
					function animate(slideIndex)
					{
						setCurrentSlide(slideIndex);
						setOverlayClickable();
						
						$mainSlider.stop().animate({top: -currentSlide * o.mainSlideHeight}, o.rewindDuration);
						$secondSlider.stop().animate({top: (-slidesNumber + currentSlide + 1) * o.secondSlideHeight}, o.rewindDuration);
						$thirdSlider.stop().animate({left: -currentSlide * o.thirdSlideWidth}, o.rewindDuration);
					}
					
					
					// Overlay
					var $overlay = $(o.overlay, $this);
					
					function getOverlayLink()
					{
						return $('.slide:eq(' + currentSlide + ') a:first', $mainSlider);
					}
					
					function setOverlayClickable()
					{
						var $link = getOverlayLink();
						
						if ($link.length)
						{
							$overlay.addClass('clickable');
						}
						else
						{
							$overlay.removeClass('clickable');
						}
					}
					
					$overlay.click
					(
						function(event)
						{
							event.preventDefault();
							
							var $link = getOverlayLink();
							
							if ($link.length)
							{
								disableAutoMode();
								window.location = $link.attr('href');
							}
						}
					);
					
					
					// Overlay control
					var $overlayControl = $(o.overlayControl, $this);
					
					$overlayControl.click
					(
						function(event)
						{
							event.preventDefault();
							event.stopPropagation();
							
							$overlayControl.blur();
							disableAutoMode();
							
							animate(currentSlide + 1);
						}
					);
					
					
					// AutoScrolling
					var autoModeIntervalId = 0;
					
					if (o.autoMode)
					{
						var autoModeFunction = function()
						{
							animate(currentSlide + 1);
						};
						
						autoModeIntervalId = setInterval(autoModeFunction, o.pauseInterval);
					}
					
					function disableAutoMode()
					{
						if (o.autoMode)
						{
							clearInterval(autoModeIntervalId);
							o.autoMode = false;
						}
					}
					
					
					// Initialization
					animate(0);
				}
			);
		}
	});
})(jQuery);