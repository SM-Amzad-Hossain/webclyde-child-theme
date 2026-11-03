/**
 * Portfolio case-study front-end behaviour.
 *
 * - Image carousel: scroll-snap based, arrow buttons, dots, counter,
 *   keyboard navigation and touch swipe (native scrolling).
 * - Safe to run multiple times (Elementor editor re-renders).
 */
(function () {
	'use strict';

	function initCarousel(root) {
		if (root.getAttribute('data-wcp-ready')) {
			return;
		}
		root.setAttribute('data-wcp-ready', '1');

		var viewport = root.querySelector('.wcp-carousel-viewport');
		var track = root.querySelector('.wcp-carousel-track');
		var slides = Array.prototype.slice.call(root.querySelectorAll('.wcp-carousel-slide'));
		var prev = root.querySelector('.wcp-prev');
		var next = root.querySelector('.wcp-next');
		var dots = Array.prototype.slice.call(root.querySelectorAll('.wcp-dot'));
		var counter = root.querySelector('.wcp-carousel-counter');

		if (!viewport || !track || slides.length === 0) {
			return;
		}

		var current = 0;
		var ticking = false;

		function slideWidth() {
			return viewport.clientWidth;
		}

		function goTo(index, smooth) {
			var max = slides.length - 1;
			if (index < 0) { index = max; }
			if (index > max) { index = 0; }
			viewport.scrollTo({
				left: index * slideWidth(),
				behavior: smooth === false ? 'auto' : 'smooth'
			});
		}

		function setActive(index) {
			if (index === current && root.getAttribute('data-wcp-active')) {
				return;
			}
			current = index;
			root.setAttribute('data-wcp-active', String(index));

			dots.forEach(function (dot, i) {
				dot.classList.toggle('is-active', i === index);
				dot.setAttribute('aria-selected', i === index ? 'true' : 'false');
			});

			if (counter) {
				counter.textContent = (index + 1) + ' / ' + slides.length;
			}
		}

		function onScroll() {
			if (ticking) {
				return;
			}
			ticking = true;
			window.requestAnimationFrame(function () {
				ticking = false;
				var width = slideWidth();
				if (width > 0) {
					setActive(Math.max(0, Math.min(slides.length - 1, Math.round(viewport.scrollLeft / width))));
				}
			});
		}

		viewport.addEventListener('scroll', onScroll, { passive: true });

		if (prev) {
			prev.addEventListener('click', function () { goTo(current - 1); });
		}
		if (next) {
			next.addEventListener('click', function () { goTo(current + 1); });
		}

		dots.forEach(function (dot, i) {
			dot.addEventListener('click', function () { goTo(i); });
		});

		// Keyboard support when the carousel (or its viewport) has focus.
		root.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft') {
				e.preventDefault();
				goTo(current - 1);
			} else if (e.key === 'ArrowRight') {
				e.preventDefault();
				goTo(current + 1);
			}
		});

		// Keep alignment on resize.
		window.addEventListener('resize', function () {
			viewport.scrollTo({ left: current * slideWidth(), behavior: 'auto' });
		});

		setActive(0);
	}

	function initAll(scope) {
		var root = scope || document;
		var carousels = root.querySelectorAll('[data-wcp-carousel]');
		Array.prototype.forEach.call(carousels, initCarousel);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () { initAll(); });
	} else {
		initAll();
	}

	// Elementor frontend: re-init after the editor re-renders a widget.
	window.addEventListener('elementor/frontend/init', function () {
		if (window.elementorFrontend && window.elementorFrontend.hooks) {
			window.elementorFrontend.hooks.addAction('frontend/element_ready/wpc_case_study.default', function ($scope) {
				initAll($scope && $scope[0] ? $scope[0] : document);
			});
		}
	});

	// Expose for manual re-initialisation if needed.
	window.wcpInitCarousels = initAll;
})();
