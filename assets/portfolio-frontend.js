/**
 * Webclyde Portfolio Smooth Autoplay Carousel
 * Hardware-accelerated, touch-friendly, pause-on-hover, infinite loop.
 */
(function($) {
	'use strict';

	function initSmoothCarousel(el) {
		var $wrap = $(el);

		// Stop any previous timer on re-init
		var existingTimer = $wrap.data('wc-carousel-timer');
		if (existingTimer) {
			clearInterval(existingTimer);
			$wrap.removeData('wc-carousel-timer');
		}

		if ($wrap.data('wc-carousel-init')) {
			return;
		}
		$wrap.data('wc-carousel-init', true);

		var mode         = $wrap.data('mode') || 'smooth_slides';
		var autoplay     = $wrap.data('autoplay') !== 'no';
		var intervalTime = parseInt($wrap.data('speed'), 10) || 3500;
		var transTime    = parseInt($wrap.data('transition'), 10) || 800;
		var pauseOnHover = $wrap.data('pause') !== 'no';
		var perViewDesk  = parseInt($wrap.data('per-view'), 10) || 2;
		var perViewTab   = parseInt($wrap.data('per-view-tablet'), 10) || Math.min(2, perViewDesk);
		var perViewMob   = parseInt($wrap.data('per-view-mobile'), 10) || 1;
		var gap          = parseInt($wrap.data('gap'), 10);
		if (isNaN(gap)) gap = 20;

		var $viewport = $wrap.find('.wc-port-carousel-viewport');
		var $track    = $wrap.find('.wc-port-carousel-track');
		var $slides   = $track.children('.wc-port-slide');
		var count     = $slides.length;

		if (count <= 1) {
			$wrap.find('.wc-port-arrow, .wc-port-dots').hide();
			return;
		}

		/* -------------------------------------------------------------
		 * Mode A: Continuous Smooth Marquee / Ticker
		 * ---------------------------------------------------------- */
		if (mode === 'smooth_ticker') {
			// Clone slides to ensure gapless infinite scroll
			if (!$track.data('cloned')) {
				var clones = $track.html();
				$track.append(clones).data('cloned', true);
			}
			return;
		}

		/* -------------------------------------------------------------
		 * Mode B: Smooth Slide-by-Slide Autoplay Carousel
		 * ---------------------------------------------------------- */
		var currentIndex = 0;
		var timer = null;
		var isHovered = false;
		var isAnimating = false;

		function getPerView() {
			var w = $(window).width();
			if (w <= 640) return perViewMob;
			if (w <= 1024) return perViewTab;
			return perViewDesk;
		}

		function getMaxIndex() {
			var perView = getPerView();
			return Math.max(0, count - perView);
		}

		// Build Pagination Dots
		var $dotsWrap = $wrap.find('.wc-port-dots');
		if ($dotsWrap.length) {
			$dotsWrap.empty();
			var perView = getPerView();
			var dotsCount = Math.max(1, count - perView + 1);
			for (var d = 0; d < dotsCount; d++) {
				$dotsWrap.append('<button type="button" class="wc-port-dot' + (d === 0 ? ' active' : '') + '" data-index="' + d + '" aria-label="Go to slide ' + (d + 1) + '"></button>');
			}
		}

		function updateSlideWidths() {
			var perView = getPerView();
			var viewWidth = $viewport.width();
			var slideWidth = (viewWidth - (gap * (perView - 1))) / perView;

			$slides.css({
				'flex': '0 0 ' + slideWidth + 'px',
				'width': slideWidth + 'px',
				'max-width': slideWidth + 'px'
			});

			$track.css({
				'gap': gap + 'px'
			});

			goToSlide(currentIndex, false);
		}

		function goToSlide(index, animate) {
			var max = getMaxIndex();
			if (index > max) {
				index = 0; // loop back smoothly
			} else if (index < 0) {
				index = max;
			}
			currentIndex = index;

			var perView = getPerView();
			var viewWidth = $viewport.width();
			var slideWidth = (viewWidth - (gap * (perView - 1))) / perView;
			var offset = (slideWidth + gap) * currentIndex;

			if (animate !== false) {
				isAnimating = true;
				$track.css({
					'transition': 'transform ' + transTime + 'ms cubic-bezier(0.25, 1, 0.5, 1)',
					'transform': 'translate3d(-' + offset + 'px, 0, 0)'
				});
				setTimeout(function() {
					isAnimating = false;
				}, transTime);
			} else {
				$track.css({
					'transition': 'none',
					'transform': 'translate3d(-' + offset + 'px, 0, 0)'
				});
			}

			// Update Dots
			$dotsWrap.find('.wc-port-dot').removeClass('active');
			$dotsWrap.find('.wc-port-dot[data-index="' + currentIndex + '"]').addClass('active');
		}

		function nextSlide() {
			goToSlide(currentIndex + 1, true);
		}

		function prevSlide() {
			goToSlide(currentIndex - 1, true);
		}

		// Autoplay Timer
		function startAutoplay() {
			if (!autoplay) return;
			stopAutoplay();
			timer = setInterval(function() {
				if (!isHovered && !isAnimating) {
					nextSlide();
				}
			}, intervalTime);
			$wrap.data('wc-carousel-timer', timer);
		}

		function stopAutoplay() {
			var existing = $wrap.data('wc-carousel-timer');
			if (existing) {
				clearInterval(existing);
				$wrap.removeData('wc-carousel-timer');
			}
			if (timer) {
				clearInterval(timer);
				timer = null;
			}
		}

		// Navigation Arrows
		$wrap.find('.wc-port-arrow-next').off('click').on('click', function(e) {
			e.preventDefault();
			nextSlide();
			startAutoplay();
		});

		$wrap.find('.wc-port-arrow-prev').off('click').on('click', function(e) {
			e.preventDefault();
			prevSlide();
			startAutoplay();
		});

		// Dots Click
		$dotsWrap.on('click', '.wc-port-dot', function(e) {
			e.preventDefault();
			var idx = parseInt($(this).data('index'), 10);
			goToSlide(idx, true);
			startAutoplay();
		});

		// Pause on hover
		if (pauseOnHover) {
			$wrap.on('mouseenter', function() {
				isHovered = true;
				stopAutoplay();
			}).on('mouseleave', function() {
				isHovered = false;
				startAutoplay();
			});
		}

		// Touch swipe support (Mobile)
		var touchStartX = 0;
		var touchEndX = 0;

		$viewport.on('touchstart', function(e) {
			touchStartX = e.originalEvent.touches[0].clientX;
			stopAutoplay();
		});

		$viewport.on('touchmove', function(e) {
			touchEndX = e.originalEvent.touches[0].clientX;
		});

		$viewport.on('touchend', function() {
			var diff = touchStartX - touchEndX;
			if (Math.abs(diff) > 40) {
				if (diff > 0) {
					nextSlide();
				} else {
					prevSlide();
				}
			}
			startAutoplay();
		});

		// Resize handler
		var resizeTimer;
		$(window).on('resize', function() {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(function() {
				updateSlideWidths();
			}, 100);
		});

		// Initial setup
		updateSlideWidths();
		startAutoplay();
	}

	// Document Ready
	$(document).ready(function() {
		$('.wc-port-carousel-wrap').each(function() {
			initSmoothCarousel(this);
		});
	});

	// Elementor Live Preview Hook
	$(window).on('elementor/frontend/init', function() {
		if (window.elementorFrontend && window.elementorFrontend.hooks) {
			elementorFrontend.hooks.addAction('frontend/element_ready/webclyde_portfolio_carousel.default', function($scope) {
				$scope.find('.wc-port-carousel-wrap').each(function() {
					var $this = $(this);
					var oldTimer = $this.data('wc-carousel-timer');
					if (oldTimer) clearInterval(oldTimer);
					$this.removeData('wc-carousel-init');
					$this.removeData('wc-carousel-timer');
					initSmoothCarousel(this);
				});
			});
			elementorFrontend.hooks.addAction('frontend/element_ready/webclyde_portfolio_case_study.default', function($scope) {
				$scope.find('.wc-port-carousel-wrap').each(function() {
					var $this = $(this);
					var oldTimer = $this.data('wc-carousel-timer');
					if (oldTimer) clearInterval(oldTimer);
					$this.removeData('wc-carousel-init');
					$this.removeData('wc-carousel-timer');
					initSmoothCarousel(this);
				});
			});
		}
	});

})(jQuery);

