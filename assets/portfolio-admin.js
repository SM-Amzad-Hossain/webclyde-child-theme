/**
 * Webclyde Portfolio Admin Meta Box JavaScript
 */
(function($) {
	'use strict';

	$(document).ready(function() {

		// Helper to mark inputs dirty and trigger change events for WordPress & Gutenberg
		function triggerChange($input) {
			if (!$input || !$input.length) return;
			$input.trigger('input').trigger('change');
			if ($input[0]) {
				try {
					$input[0].dispatchEvent(new Event('input', { bubbles: true }));
					$input[0].dispatchEvent(new Event('change', { bubbles: true }));
				} catch (e) {}
			}
			// Notify Gutenberg editor store if active
			if (window.wp && wp.data && wp.data.dispatch) {
				try {
					wp.data.dispatch('core/editor').editPost({ meta: {} });
				} catch (e) {}
			}
		}

		// Notify Gutenberg when any meta box input/textarea is modified
		$(document).on('input change', '.wc-meta-content input, .wc-meta-content textarea', function() {
			if (window.wp && wp.data && wp.data.dispatch && wp.data.select) {
				try {
					var isDirty = wp.data.select('core/editor').isEditedPostDirty();
					if (!isDirty) {
						wp.data.dispatch('core/editor').editPost({ meta: {} });
					}
				} catch (e) {}
			}
		});

		// 1. Tab Switching & State Persistence
		function setActiveTab(tabId, updateHash) {
			if (!tabId || !$('#' + tabId).length) return;

			$('.wc-meta-nav button').removeClass('active');
			$('.wc-meta-nav button[data-tab="' + tabId + '"]').addClass('active');

			$('.wc-meta-tab-panel').removeClass('active');
			$('#' + tabId).addClass('active');

			$('#wc_portfolio_active_tab').val(tabId);

			try {
				sessionStorage.setItem('wc_portfolio_active_tab', tabId);
			} catch (e) {}

			if (updateHash && window.history && window.history.replaceState) {
				window.history.replaceState(null, null, '#' + tabId);
			}
		}

		$('.wc-meta-nav button').on('click', function(e) {
			e.preventDefault();
			var tabId = $(this).data('tab');
			setActiveTab(tabId, true);
		});

		// Restore tab on page load (from hash, session, or saved post meta)
		var initialTab = '';
		if (window.location.hash && $(window.location.hash).length) {
			initialTab = window.location.hash.replace('#', '');
		} else {
			try {
				initialTab = sessionStorage.getItem('wc_portfolio_active_tab');
			} catch (e) {}
		}
		if (!initialTab || !$('#' + initialTab).length) {
			initialTab = $('#wc_portfolio_active_tab').val() || 'tab-overview';
		}
		if (initialTab && $('#' + initialTab).length) {
			setActiveTab(initialTab, false);
		}

		// When page reloads after updating post (message query param present), stay/scroll on the exact position
		if (window.location.search.indexOf('message=') !== -1) {
			setTimeout(function() {
				var $metabox = $('.wc-portfolio-details-under-title');
				if ($metabox.length) {
					$('html, body').animate({
						scrollTop: $metabox.offset().top - 32
					}, 350);
				}
			}, 150);
		}

		// Ensure TinyMCE editors sync their content to hidden textareas before save / submit
		$('form#post').on('submit', function() {
			if (typeof tinyMCE !== 'undefined') {
				tinyMCE.triggerSave();
			}
		});

		// For Gutenberg save & autosave lifecycle
		if (window.wp && wp.data && wp.data.subscribe) {
			var wasSaving = false;
			wp.data.subscribe(function() {
				try {
					var editorSelect = wp.data.select('core/editor');
					if (editorSelect) {
						var isSaving = editorSelect.isSavingPost() || editorSelect.isAutosavingPost();
						if (isSaving && !wasSaving) {
							if (typeof tinyMCE !== 'undefined') {
								tinyMCE.triggerSave();
							}
						}
						wasSaving = isSaving;
					}
				} catch (e) {}
			});
		}

		// Connect TinyMCE events for live changes & dirty state detection
		function hookTinyMCEChanges() {
			if (typeof tinymce === 'undefined') return;
			['wc_portfolio_description', 'wc_portfolio_challenge_description'].forEach(function(editorId) {
				var ed = tinymce.get(editorId);
				if (ed && !ed.__wcBound) {
					ed.__wcBound = true;
					ed.on('change keyup', function() {
						ed.save();
						var $ta = $('#' + editorId);
						triggerChange($ta);
					});
				}
			});
		}

		hookTinyMCEChanges();
		$(document).on('tinymce-editor-init', function() {
			hookTinyMCEChanges();
		});

		// 2. Single Image Uploaders (Logo, Approach Image, Review Image, Avatar)
		$(document).on('click', '.wc-upload-btn', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var targetKey = $btn.data('target');
			var $wrap = $btn.closest('.wc-image-uploader');
			var $input = $('#' + targetKey);
			var $preview = $wrap.find('.wc-image-preview');
			var $removeBtn = $wrap.find('.wc-remove-btn');

			var frame = wp.media({
				title: 'Select Image',
				button: { text: 'Use Image' },
				multiple: false
			});

			frame.on('select', function() {
				var attachment = frame.state().get('selection').first().toJSON();
				$input.val(attachment.id);

				var imgUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
				$preview.html('<img src="' + imgUrl + '" alt="">');
				$removeBtn.show();
				triggerChange($input);
			});

			frame.open();
		});

		// Remove Single Image
		$(document).on('click', '.wc-remove-btn', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var targetKey = $btn.data('target');
			var $wrap = $btn.closest('.wc-image-uploader');
			var $input = $('#' + targetKey);
			var $preview = $wrap.find('.wc-image-preview');

			$input.val('');
			$preview.html('<span class="dashicons dashicons-format-image placeholder"></span>');
			$btn.hide();
			triggerChange($input);
		});

		// 3. Multi-Image Carousel Gallery using Native WordPress Gallery Workflow
		function renderGalleryItems(models) {
			var $grid = $('#wc-gallery-grid');
			var $input = $('#wc_portfolio_gallery');
			var idArr = [];
			var html = '';

			models.forEach(function(item) {
				var att = (typeof item.toJSON === 'function') ? item.toJSON() : item;
				idArr.push(att.id);
				var imgUrl = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : (att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url);
				html += '<div class="wc-gallery-item" data-id="' + att.id + '">' +
					'<img src="' + imgUrl + '" alt="">' +
					'<button type="button" class="remove-gallery-img" title="Remove">&times;</button>' +
					'</div>';
			});

			$input.val(idArr.join(','));
			$grid.html(html);
			$('#wc-clear-gallery-btn').toggle(idArr.length > 0);
			triggerChange($input);
		}

		var galleryFrame;
		$('#wc-add-gallery-btn').on('click', function(e) {
			e.preventDefault();
			var $input = $('#wc_portfolio_gallery');
			var currentIdsStr = $.trim($input.val());
			var currentIds = currentIdsStr ? currentIdsStr.split(',').filter(Boolean) : [];

			// Native WordPress System:
			if (currentIds.length > 0 && wp.media && wp.media.gallery && typeof wp.media.gallery.edit === 'function') {
				galleryFrame = wp.media.gallery.edit('[gallery ids="' + currentIds.join(',') + '"]');

				galleryFrame.state('gallery-edit').on('update', function(selection) {
					renderGalleryItems(selection.models);
				});
			} else {
				// Open default WordPress Create Gallery state
				galleryFrame = wp.media({
					frame: 'post',
					state: 'gallery',
					title: (wp.media.view.l10n && wp.media.view.l10n.createGalleryTitle) || 'Create Gallery',
					multiple: true
				});

				galleryFrame.on('update', function(selection) {
					renderGalleryItems(selection.models);
				});

				galleryFrame.open();

				if (galleryFrame.state('gallery-edit')) {
					galleryFrame.state('gallery-edit').on('update', function(selection) {
						renderGalleryItems(selection.models);
					});
				}
			}
		});

		// Clear All Images
		$('#wc-clear-gallery-btn').on('click', function(e) {
			e.preventDefault();
			if (confirm('Remove all carousel images?')) {
				var $input = $('#wc_portfolio_gallery');
				$input.val('');
				$('#wc-gallery-grid').empty();
				$(this).hide();
				triggerChange($input);
			}
		});

		// Remove individual image from gallery
		$(document).on('click', '.remove-gallery-img', function(e) {
			e.preventDefault();
			var $item = $(this).closest('.wc-gallery-item');
			var idToRemove = String($item.data('id'));
			var $input = $('#wc_portfolio_gallery');
			var currentIds = $input.val() ? $input.val().split(',').filter(Boolean) : [];

			currentIds = currentIds.filter(function(id) {
				return String(id) !== idToRemove;
			});

			$input.val(currentIds.join(','));
			$item.fadeOut(200, function() {
				$(this).remove();
				$('#wc-clear-gallery-btn').toggle(currentIds.length > 0);
				triggerChange($input);
			});
		});

		// 4. Repeater: The Main Problems
		$('#wc-add-problem-btn').on('click', function(e) {
			e.preventDefault();
			var $list = $('#wc-problems-list');
			var rowHtml = '<div class="wc-repeater-row">' +
				'<span class="dashicons dashicons-menu drag-handle"></span>' +
				'<input type="text" name="_portfolio_main_problems[]" value="" placeholder="Enter a key problem faced by the client...">' +
				'<button type="button" class="remove-row-btn" title="Delete problem">&times;</button>' +
				'</div>';
			$list.append(rowHtml);
			var $newInput = $list.find('.wc-repeater-row:last-child input');
			$newInput.focus();
			triggerChange($newInput);
		});

		// 5. Repeater: Step-by-Step Approach
		function reindexSteps() {
			$('#wc-steps-list .wc-step-row').each(function(index) {
				$(this).find('.wc-step-badge').text('Step ' + (index + 1));
			});
		}

		$('#wc-add-step-btn').on('click', function(e) {
			e.preventDefault();
			var $list = $('#wc-steps-list');
			var count = $list.children('.wc-step-row').length + 1;
			var rowHtml = '<div class="wc-step-row">' +
				'<div class="wc-step-row-header">' +
					'<span class="wc-step-badge">Step ' + count + '</span>' +
					'<button type="button" class="remove-row-btn" title="Delete step">&times;</button>' +
				'</div>' +
				'<input type="text" name="_portfolio_approach_step_titles[]" value="" placeholder="Step title (e.g. Discovery & UX Audit)...">' +
				'<textarea name="_portfolio_approach_step_descs[]" placeholder="Describe the approach taken in this step..."></textarea>' +
				'</div>';
			$list.append(rowHtml);
			var $newInput = $list.find('.wc-step-row:last-child input[type="text"]');
			$newInput.focus();
			triggerChange($newInput);
		});

		// Toggle Approach Grid Extra Badge / Image Box
		$(document).on('change', '#wc_portfolio_approach_has_badge', function() {
			if ($(this).val() === 'yes') {
				$('#wc-approach-badge-box').slideDown(150);
			} else {
				$('#wc-approach-badge-box').slideUp(150);
			}
			triggerChange($(this));
		});

		// Generic row delete
		$(document).on('click', '.remove-row-btn', function(e) {
			e.preventDefault();
			var $row = $(this).closest('.wc-repeater-row, .wc-step-row');
			$row.fadeOut(200, function() {
				$(this).remove();
				reindexSteps();
				triggerChange($('#tab-challenge input').first());
			});
		});

	});
})(jQuery);
