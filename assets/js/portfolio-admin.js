/**
 * Portfolio case-study admin controls.
 *
 * - Single image pickers (logo, approach image, review image, avatar)
 * - Multi-image gallery picker with drag-to-reorder (carousel)
 * - Repeatable text lists (main problems, approach steps)
 *
 * Requires: jQuery, jQuery UI Sortable, wp.media (all bundled with WordPress).
 */
/* global jQuery, wp */
(function ($) {
	'use strict';

	if (typeof wp === 'undefined' || !wp.media) {
		return;
	}

	/* ------------------------------------------------------------------
	 * Single image picker
	 * ---------------------------------------------------------------- */
	$(document).on('click', '.wpc-image-select', function (e) {
		e.preventDefault();

		var $control = $(this).closest('.wpc-image-control');
		var frame = $control.data('wpcFrame');

		if (!frame) {
			frame = wp.media({
				title: wp.media.view.l10n.chooseImage || 'Select image',
				button: { text: wp.media.view.l10n.useImage || 'Use image' },
				library: { type: 'image' },
				multiple: false
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				var url = (attachment.sizes && attachment.sizes.medium)
					? attachment.sizes.medium.url
					: attachment.url;

				$control.find('.wpc-image-id').val(attachment.id).trigger('change');
				$control.find('.wpc-image-preview').html($('<img>', { src: url, alt: '' }));
				$control.addClass('has-image');
			});

			$control.data('wpcFrame', frame);
		}

		frame.open();
	});

	$(document).on('click', '.wpc-image-remove', function (e) {
		e.preventDefault();
		var $control = $(this).closest('.wpc-image-control');
		$control.find('.wpc-image-id').val('').trigger('change');
		$control.find('.wpc-image-preview').empty();
		$control.removeClass('has-image');
	});

	/* ------------------------------------------------------------------
	 * Gallery (carousel) picker
	 * ---------------------------------------------------------------- */
	function syncGallery($control) {
		var ids = [];
		$control.find('.wpc-gallery-item').each(function () {
			ids.push($(this).data('id'));
		});
		$control.find('.wpc-gallery-ids').val(ids.join(',')).trigger('change');
	}

	function addGalleryItems($control, attachments) {
		var $grid = $control.find('.wpc-gallery-grid');
		var existing = {};

		$grid.find('.wpc-gallery-item').each(function () {
			existing[$(this).data('id')] = true;
		});

		attachments.forEach(function (attachment) {
			if (existing[attachment.id]) {
				return;
			}
			var url = (attachment.sizes && attachment.sizes.thumbnail)
				? attachment.sizes.thumbnail.url
				: attachment.url;

			var $item = $('<div>', { 'class': 'wpc-gallery-item', 'data-id': attachment.id });
			$item.append($('<img>', { src: url, alt: '' }));
			$item.append($('<button>', {
				type: 'button',
				'class': 'wpc-gallery-remove',
				'aria-label': 'Remove image',
				html: '&times;'
			}));
			$grid.append($item);
		});

		syncGallery($control);
	}

	$(document).on('click', '.wpc-gallery-add', function (e) {
		e.preventDefault();

		var $control = $(this).closest('.wpc-gallery-control');
		var frame = $control.data('wpcFrame');

		if (!frame) {
			frame = wp.media({
				title: 'Select carousel images',
				button: { text: 'Add to carousel' },
				library: { type: 'image' },
				multiple: true
			});

			frame.on('select', function () {
				addGalleryItems($control, frame.state().get('selection').toJSON());
			});

			$control.data('wpcFrame', frame);
		}

		frame.open();
	});

	$(document).on('click', '.wpc-gallery-remove', function (e) {
		e.preventDefault();
		e.stopPropagation();
		var $control = $(this).closest('.wpc-gallery-control');
		$(this).closest('.wpc-gallery-item').remove();
		syncGallery($control);
	});

	/* ------------------------------------------------------------------
	 * Repeatable lists (problems / steps)
	 * ---------------------------------------------------------------- */
	function buildRow(name, placeholder, value) {
		var $row = $('<div>', { 'class': 'wpc-repeater-row' });
		$row.append($('<span>', { 'class': 'wpc-repeater-handle', 'aria-hidden': 'true', html: '&#8942;&#8942;' }));
		$row.append($('<input>', {
			type: 'text',
			'class': 'wpc-input',
			name: name + '[]',
			value: value || '',
			placeholder: placeholder || ''
		}));
		$row.append($('<button>', {
			type: 'button',
			'class': 'wpc-repeater-remove button-link',
			'aria-label': 'Remove item',
			html: '&times;'
		}));
		return $row;
	}

	$(document).on('click', '.wpc-repeater-add', function (e) {
		e.preventDefault();
		var $repeater = $(this).closest('.wpc-repeater');
		var $row = buildRow($repeater.data('name'), $repeater.data('placeholder'), '');
		$repeater.find('.wpc-repeater-rows').append($row);
		$row.find('input').trigger('focus');
	});

	$(document).on('click', '.wpc-repeater-remove', function (e) {
		e.preventDefault();
		$(this).closest('.wpc-repeater-row').remove();
	});

	// Pressing Enter inside a repeater input adds the next row.
	$(document).on('keydown', '.wpc-repeater-row input', function (e) {
		if (e.key === 'Enter') {
			e.preventDefault();
			$(this).closest('.wpc-repeater').find('.wpc-repeater-add').trigger('click');
		}
	});

	/* ------------------------------------------------------------------
	 * Drag-to-reorder
	 * ---------------------------------------------------------------- */
	function initSortables() {
		$('.wpc-gallery-grid').each(function () {
			var $grid = $(this);
			if ($grid.data('ui-sortable') || $grid.hasClass('ui-sortable')) {
				return;
			}
			$grid.sortable({
				items: '.wpc-gallery-item',
				cursor: 'grabbing',
				opacity: 0.7,
				update: function () {
					syncGallery($grid.closest('.wpc-gallery-control'));
				}
			});
		});

		$('.wpc-repeater-rows').each(function () {
			var $rows = $(this);
			if ($rows.data('ui-sortable') || $rows.hasClass('ui-sortable')) {
				return;
			}
			$rows.sortable({
				items: '.wpc-repeater-row',
				handle: '.wpc-repeater-handle',
				cursor: 'grabbing',
				opacity: 0.7,
				axis: 'y'
			});
		});
	}

	$(initSortables);

	// Gutenberg moves meta boxes around after load; re-init when that happens.
	if (window.MutationObserver) {
		var observer = new MutationObserver(function (mutations) {
			var relevant = mutations.some(function (m) {
				return m.addedNodes.length > 0;
			});
			if (relevant) {
				initSortables();
			}
		});
		$(function () {
			var $wrap = $('#poststuff');
			if ($wrap.length) {
				observer.observe($wrap[0], { childList: true, subtree: true });
			}
		});
	}
})(jQuery);
