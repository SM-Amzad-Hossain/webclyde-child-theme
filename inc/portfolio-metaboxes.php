<?php

/**
 * Portfolio Custom Fields & Admin Meta Box
 *
 * @package Webclyde
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Register all Portfolio Meta Fields for REST API & Block Editor compatibility
 */
function webclyde_register_portfolio_post_meta()
{
	$fields = [
		'_portfolio_logo'                  => 'integer',
		'portfolio_logo'                   => 'integer',
		'_portfolio_site_url'              => 'string',
		'portfolio_site_url'               => 'string',
		'_portfolio_description'           => 'string',
		'portfolio_description'            => 'string',
		'_portfolio_organization'          => 'string',
		'portfolio_organization'           => 'string',
		'_portfolio_services'              => 'string',
		'portfolio_services'               => 'string',
		'_portfolio_timeline'              => 'string',
		'portfolio_timeline'               => 'string',
		'_portfolio_industry'              => 'string',
		'portfolio_industry'               => 'string',
		'_portfolio_active_tab'            => 'string',
		'portfolio_active_tab'             => 'string',
		'_portfolio_platform'              => 'string',
		'portfolio_platform'               => 'string',
		'_portfolio_location'              => 'string',
		'portfolio_location'               => 'string',
		'_portfolio_gallery'               => 'string',
		'portfolio_gallery'                => 'string',
		'_portfolio_challenge_title'       => 'string',
		'portfolio_challenge_title'        => 'string',
		'_portfolio_challenge_description' => 'string',
		'portfolio_challenge_description'  => 'string',
		'_portfolio_main_problems'         => 'array',
		'portfolio_main_problems'          => 'array',
		'_portfolio_approach_image'        => 'integer',
		'portfolio_approach_image'         => 'integer',
		'_portfolio_approach_badge_image'  => 'integer',
		'portfolio_approach_badge_image'   => 'integer',
		'_portfolio_approach_has_badge'    => 'string',
		'portfolio_approach_has_badge'     => 'string',
		'_portfolio_approach_badge_link'   => 'string',
		'portfolio_approach_badge_link'    => 'string',
		'_portfolio_approach_title'        => 'string',
		'portfolio_approach_title'         => 'string',
		'_portfolio_approach_steps'        => 'array',
		'portfolio_approach_steps'         => 'array',
		'_portfolio_result_title'          => 'string',
		'portfolio_result_title'           => 'string',
		'_portfolio_result_description'    => 'string',
		'portfolio_result_description'     => 'string',
		'_portfolio_mobile_load_time'      => 'string',
		'portfolio_mobile_load_time'       => 'string',
		'_portfolio_pagespeed_score'       => 'string',
		'portfolio_pagespeed_score'        => 'string',
		'_portfolio_completed_checkouts'   => 'string',
		'portfolio_completed_checkouts'    => 'string',
		'_portfolio_review_image'          => 'integer',
		'portfolio_review_image'           => 'integer',
		'_portfolio_client_said'           => 'string',
		'portfolio_client_said'            => 'string',
		'_portfolio_client_avatar'         => 'integer',
		'portfolio_client_avatar'          => 'integer',
		'_portfolio_client_name'           => 'string',
		'portfolio_client_name'            => 'string',
		'_portfolio_client_designation'    => 'string',
		'portfolio_client_designation'     => 'string',
	];

	foreach ($fields as $meta_key => $type) {
		register_post_meta('portfolio', $meta_key, [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => $type,
			'auth_callback' => function () {
				return current_user_can('edit_posts');
			},
		]);
	}
}
add_action('init', 'webclyde_register_portfolio_post_meta', 20);

/**
 * Enqueue Admin Scripts and Styles on Portfolio Edit Screen
 */
function webclyde_portfolio_admin_scripts($hook)
{
	global $post;

	if (! in_array($hook, ['post-new.php', 'post.php'], true)) {
		return;
	}

	if (! $post || 'portfolio' !== $post->post_type) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_style(
		'webclyde-portfolio-admin-css',
		get_stylesheet_directory_uri() . '/assets/portfolio-admin.css',
		[],
		filemtime(get_stylesheet_directory() . '/assets/portfolio-admin.css')
	);

	wp_enqueue_script(
		'webclyde-portfolio-admin-js',
		get_stylesheet_directory_uri() . '/assets/portfolio-admin.js',
		['jquery'],
		filemtime(get_stylesheet_directory() . '/assets/portfolio-admin.js'),
		true
	);
}
add_action('admin_enqueue_scripts', 'webclyde_portfolio_admin_scripts');

/**
 * Render Portfolio Case Study Details & Elements directly under the Title field (not as a meta box)
 *
 * @param WP_Post $post Current post object.
 */
function webclyde_render_portfolio_under_title($post)
{
	if (!$post || 'portfolio' !== $post->post_type) {
		return;
	}

?>
	<div class="wc-portfolio-details-under-title">
		<div class="wc-under-title-header">
			<h3><?php esc_html_e('Portfolio Case Study Details & Elements', 'webclyde'); ?></h3>
		</div>
		<?php webclyde_render_portfolio_metabox($post); ?>
	</div>
<?php
}
add_action('edit_form_after_title', 'webclyde_render_portfolio_under_title');

/**
 * Helper to get a portfolio field with fallback
 */
function webclyde_get_portfolio_field($key, $post_id = 0)
{
	if (! $post_id) {
		$post_id = function_exists('webclyde_get_portfolio_post_id') ? webclyde_get_portfolio_post_id() : get_the_ID();
	}
	if (! $post_id) {
		global $post;
		if (! empty($post->ID)) {
			$post_id = $post->ID;
		}
	}
	if (! $post_id) {
		return '';
	}

	$clean_key = ltrim($key, '_');
	$val       = get_post_meta($post_id, '_' . $clean_key, true);

	if ('' === $val || false === $val || null === $val || [] === $val) {
		$val = get_post_meta($post_id, $clean_key, true);
	}

	// Safely decode if stored as json or serialized string
	if (is_string($val)) {
		$trimmed = trim($val);
		if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
			$decoded = json_decode($trimmed, true);
			if (is_array($decoded)) {
				$val = $decoded;
			}
		} elseif (is_serialized($val)) {
			$val = maybe_unserialize($val);
		}
	}

	return $val;
}

/**
 * Render Meta Box HTML
 */
function webclyde_render_portfolio_metabox($post)
{
	wp_nonce_field('webclyde_save_portfolio_meta', 'webclyde_portfolio_nonce');

	$post_id = $post->ID;

	// Retrieve values
	$logo_id             = webclyde_get_portfolio_field('_portfolio_logo', $post_id);
	$site_url            = webclyde_get_portfolio_field('_portfolio_site_url', $post_id);
	$description         = webclyde_get_portfolio_field('_portfolio_description', $post_id);
	$organization        = webclyde_get_portfolio_field('_portfolio_organization', $post_id);
	$services            = webclyde_get_portfolio_field('_portfolio_services', $post_id);
	$timeline            = webclyde_get_portfolio_field('_portfolio_timeline', $post_id);
	$industry            = webclyde_get_portfolio_field('_portfolio_industry', $post_id);
	$platform            = webclyde_get_portfolio_field('_portfolio_platform', $post_id);
	$location            = webclyde_get_portfolio_field('_portfolio_location', $post_id);
	$gallery_ids_str     = webclyde_get_portfolio_field('_portfolio_gallery', $post_id);

	// Challenge
	$challenge_title     = webclyde_get_portfolio_field('_portfolio_challenge_title', $post_id);
	$challenge_desc      = webclyde_get_portfolio_field('_portfolio_challenge_description', $post_id);
	$main_problems       = webclyde_get_portfolio_field('_portfolio_main_problems', $post_id);
	if (! is_array($main_problems)) {
		$main_problems = [];
	}

	// Approach
	$approach_image_id       = webclyde_get_portfolio_field('_portfolio_approach_image', $post_id);
	$approach_title          = webclyde_get_portfolio_field('_portfolio_approach_title', $post_id);
	$approach_badge_image_id = webclyde_get_portfolio_field('_portfolio_approach_badge_image', $post_id);
	$approach_has_badge      = webclyde_get_portfolio_field('_portfolio_approach_has_badge', $post_id);
	if ('' === (string) $approach_has_badge) {
		$approach_has_badge = ! empty($approach_badge_image_id) ? 'yes' : 'no';
	}
	$approach_badge_link     = webclyde_get_portfolio_field('_portfolio_approach_badge_link', $post_id);
	$approach_steps          = webclyde_get_portfolio_field('_portfolio_approach_steps', $post_id);
	if (! is_array($approach_steps)) {
		$approach_steps = [];
	}

	// Results
	$result_title        = webclyde_get_portfolio_field('_portfolio_result_title', $post_id);
	$result_desc         = webclyde_get_portfolio_field('_portfolio_result_description', $post_id);
	$mobile_load_time    = webclyde_get_portfolio_field('_portfolio_mobile_load_time', $post_id);
	$pagespeed_score     = webclyde_get_portfolio_field('_portfolio_pagespeed_score', $post_id);
	$completed_checkouts = webclyde_get_portfolio_field('_portfolio_completed_checkouts', $post_id);

	// Client Review
	$review_image_id     = webclyde_get_portfolio_field('_portfolio_review_image', $post_id);
	$client_said         = webclyde_get_portfolio_field('_portfolio_client_said', $post_id);
	$client_avatar_id    = webclyde_get_portfolio_field('_portfolio_client_avatar', $post_id);
	$client_name         = webclyde_get_portfolio_field('_portfolio_client_name', $post_id);
	$client_designation  = webclyde_get_portfolio_field('_portfolio_client_designation', $post_id);

	// Helper for preview URLs
	$logo_url                = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
	$approach_img_url        = $approach_image_id ? wp_get_attachment_image_url($approach_image_id, 'medium') : '';
	$approach_badge_img_url  = $approach_badge_image_id ? wp_get_attachment_image_url($approach_badge_image_id, 'medium') : '';
	$review_img_url          = $review_image_id ? wp_get_attachment_image_url($review_image_id, 'medium') : '';
	$client_avatar_url       = $client_avatar_id ? wp_get_attachment_image_url($client_avatar_id, 'thumbnail') : '';

	// Remember active tab after update
	$active_tab = sanitize_key(webclyde_get_portfolio_field('_portfolio_active_tab', $post_id));
	if (! in_array($active_tab, ['tab-overview', 'tab-gallery', 'tab-challenge', 'tab-approach', 'tab-results', 'tab-review'], true)) {
		$active_tab = 'tab-overview';
	}
?>

	<div class="wc-meta-wrap">
		<input type="hidden" id="wc_portfolio_active_tab" name="_portfolio_active_tab" value="<?php echo esc_attr($active_tab); ?>">

		<!-- Sidebar Navigation Tabs -->
		<div class="wc-meta-nav">
			<button type="button" class="<?php echo 'tab-overview' === $active_tab ? 'active' : ''; ?>" data-tab="tab-overview">
				<span class="dashicons dashicons-admin-settings"></span>
				<?php esc_html_e('Overview & Meta', 'webclyde'); ?>
			</button>
			<button type="button" class="<?php echo 'tab-gallery' === $active_tab ? 'active' : ''; ?>" data-tab="tab-gallery">
				<span class="dashicons dashicons-images-alt2"></span>
				<?php esc_html_e('Image Carousel', 'webclyde'); ?>
			</button>
			<button type="button" class="<?php echo 'tab-challenge' === $active_tab ? 'active' : ''; ?>" data-tab="tab-challenge">
				<span class="dashicons dashicons-warning"></span>
				<?php esc_html_e('The Challenge', 'webclyde'); ?>
			</button>
			<button type="button" class="<?php echo 'tab-approach' === $active_tab ? 'active' : ''; ?>" data-tab="tab-approach">
				<span class="dashicons dashicons-hammer"></span>
				<?php esc_html_e('The Approach', 'webclyde'); ?>
			</button>
			<button type="button" class="<?php echo 'tab-results' === $active_tab ? 'active' : ''; ?>" data-tab="tab-results">
				<span class="dashicons dashicons-chart-bar"></span>
				<?php esc_html_e('Results & Metrics', 'webclyde'); ?>
			</button>
			<button type="button" class="<?php echo 'tab-review' === $active_tab ? 'active' : ''; ?>" data-tab="tab-review">
				<span class="dashicons dashicons-testimonial"></span>
				<?php esc_html_e('Client Review', 'webclyde'); ?>
			</button>
		</div>

		<!-- Content Panels -->
		<div class="wc-meta-content">

			<!-- 1. OVERVIEW & META -->
			<div id="tab-overview" class="wc-meta-tab-panel <?php echo 'tab-overview' === $active_tab ? 'active' : ''; ?>">
				<h3><?php esc_html_e('Project Overview & General Details', 'webclyde'); ?></h3>
				<p class="section-desc"><?php esc_html_e('General project metadata displayed on the single portfolio case study or via Elementor dynamic tags.', 'webclyde'); ?></p>

				<div class="wc-field-group">
					<label><?php esc_html_e('Project / Brand Logo', 'webclyde'); ?></label>
					<div class="wc-image-uploader">
						<div class="wc-image-preview">
							<?php if ($logo_url) : ?>
								<img src="<?php echo esc_url($logo_url); ?>" alt="">
							<?php else : ?>
								<span class="dashicons dashicons-format-image placeholder"></span>
							<?php endif; ?>
						</div>
						<div class="wc-image-actions">
							<input type="hidden" id="wc_portfolio_logo" name="_portfolio_logo" value="<?php echo esc_attr($logo_id); ?>">
							<button type="button" class="button button-secondary wc-upload-btn" data-target="wc_portfolio_logo">
								<?php esc_html_e('Upload / Select Logo', 'webclyde'); ?>
							</button>
							<button type="button" class="button button-link-delete wc-remove-btn" data-target="wc_portfolio_logo" style="<?php echo $logo_id ? '' : 'display:none;'; ?>">
								<?php esc_html_e('Remove', 'webclyde'); ?>
							</button>
						</div>
					</div>
				</div>

				<div class="wc-field-grid">
					<div class="wc-field-group">
						<label for="wc_portfolio_site_url"><?php esc_html_e('Site URL', 'webclyde'); ?></label>
						<input type="url" id="wc_portfolio_site_url" name="_portfolio_site_url" value="<?php echo esc_url($site_url); ?>" placeholder="https://example.com">
					</div>

					<div class="wc-field-group">
						<label for="wc_portfolio_organization"><?php esc_html_e('Organization / Client', 'webclyde'); ?></label>
						<input type="text" id="wc_portfolio_organization" name="_portfolio_organization" value="<?php echo esc_attr($organization); ?>" placeholder="e.g. Acme Corp">
					</div>

					<div class="wc-field-group">
						<label for="wc_portfolio_services"><?php esc_html_e('Services Provided', 'webclyde'); ?></label>
						<input type="text" id="wc_portfolio_services" name="_portfolio_services" value="<?php echo esc_attr($services); ?>" placeholder="e.g. Web Design, UI/UX, WooCommerce">
					</div>

					<div class="wc-field-group">
						<label for="wc_portfolio_timeline"><?php esc_html_e('Timeline / Duration', 'webclyde'); ?></label>
						<input type="text" id="wc_portfolio_timeline" name="_portfolio_timeline" value="<?php echo esc_attr($timeline); ?>" placeholder="e.g. 6 Weeks / Jan - Mar 2024">
					</div>

					<div class="wc-field-group">
						<label for="wc_portfolio_industry"><?php esc_html_e('Industry', 'webclyde'); ?></label>
						<input type="text" id="wc_portfolio_industry" name="_portfolio_industry" value="<?php echo esc_attr($industry); ?>" placeholder="e.g. E-Commerce, SaaS, Healthcare">
					</div>

					<div class="wc-field-group">
						<label for="wc_portfolio_platform"><?php esc_html_e('Platform / Tech Stack', 'webclyde'); ?></label>
						<input type="text" id="wc_portfolio_platform" name="_portfolio_platform" value="<?php echo esc_attr($platform); ?>" placeholder="e.g. WordPress, Shopify, Next.js">
					</div>

					<div class="wc-field-group wc-field-col-full">
						<label for="wc_portfolio_location"><?php esc_html_e('Location', 'webclyde'); ?></label>
						<input type="text" id="wc_portfolio_location" name="_portfolio_location" value="<?php echo esc_attr($location); ?>" placeholder="e.g. New York, USA">
					</div>

					<div class="wc-field-group wc-field-col-full">
						<label for="wc_portfolio_description"><?php esc_html_e('Project Overview / Description', 'webclyde'); ?></label>
						<?php
						wp_editor(
							$description,
							'wc_portfolio_description',
							[
								'textarea_name' => '_portfolio_description',
								'textarea_rows' => 7,
								'media_buttons' => true,
								'teeny'         => false,
								'quicktags'     => true,
							]
						);
						?>
					</div>
				</div>
			</div>

			<!-- 2. IMAGE CAROUSEL / GALLERY -->
			<div id="tab-gallery" class="wc-meta-tab-panel <?php echo 'tab-gallery' === $active_tab ? 'active' : ''; ?>">
				<h3><?php esc_html_e('Image Carousel / Showcase Gallery', 'webclyde'); ?></h3>
				<p class="section-desc"><?php esc_html_e('Add multiple screenshots or mockups. They will automatically populate the portfolio image carousel.', 'webclyde'); ?></p>

				<input type="hidden" id="wc_portfolio_gallery" name="_portfolio_gallery" value="<?php echo esc_attr($gallery_ids_str); ?>">

				<div class="wc-gallery-wrap">
					<div id="wc-gallery-grid" class="wc-gallery-grid">
						<?php
						$gallery_ids = array_filter(array_map('absint', explode(',', (string) $gallery_ids_str)));
						foreach ($gallery_ids as $img_id) :
							$img_src = wp_get_attachment_image_url($img_id, 'thumbnail');
							if (! $img_src) {
								continue;
							}
						?>
							<div class="wc-gallery-item" data-id="<?php echo esc_attr($img_id); ?>">
								<img src="<?php echo esc_url($img_src); ?>" alt="">
								<button type="button" class="remove-gallery-img" title="<?php esc_attr_e('Remove', 'webclyde'); ?>">&times;</button>
							</div>
						<?php endforeach; ?>
					</div>

					<div style="display: flex; gap: 12px; align-items: center; margin-top: 14px;">
						<button type="button" id="wc-add-gallery-btn" class="button button-primary">
							<?php esc_html_e('Add', 'webclyde'); ?>
						</button>
						<button type="button" id="wc-clear-gallery-btn" class="button button-link-delete" style="<?php echo empty($gallery_ids) ? 'display:none;' : ''; ?>">
							<?php esc_html_e('Clear All', 'webclyde'); ?>
						</button>
					</div>
					<p class="description" style="margin-top: 8px;">
						<?php esc_html_e('Opens the standard WordPress Gallery manager where you can upload, select from media library, reorder images by dragging, and update.', 'webclyde'); ?>
					</p>
				</div>
			</div>

			<!-- 3. THE CHALLENGE -->
			<div id="tab-challenge" class="wc-meta-tab-panel <?php echo 'tab-challenge' === $active_tab ? 'active' : ''; ?>">
				<h3><?php esc_html_e('The Challenge & Main Problems', 'webclyde'); ?></h3>
				<p class="section-desc"><?php esc_html_e('Explain the core obstacles and list the specific problems the client was facing.', 'webclyde'); ?></p>

				<div class="wc-field-group">
					<label for="wc_portfolio_challenge_title"><?php esc_html_e('Challenge Title', 'webclyde'); ?></label>
					<input type="text" id="wc_portfolio_challenge_title" name="_portfolio_challenge_title" value="<?php echo esc_attr($challenge_title ?: 'The Challenge'); ?>" placeholder="e.g. The Challenge: Outdated Infrastructure & High Bounce Rates">
				</div>

				<div class="wc-field-group">
					<label for="wc_portfolio_challenge_description"><?php esc_html_e('Challenge Description', 'webclyde'); ?></label>
					<?php
					wp_editor(
						$challenge_desc,
						'wc_portfolio_challenge_description',
						[
							'textarea_name' => '_portfolio_challenge_description',
							'textarea_rows' => 7,
							'media_buttons' => true,
							'teeny'         => false,
							'quicktags'     => true,
						]
					);
					?>
				</div>

				<div class="wc-field-group">
					<label><?php esc_html_e('The Main Problems (List with Multiple Add Option)', 'webclyde'); ?></label>
					<p class="description"><?php esc_html_e('Click "+ Add Problem" to add as many problem bullet points as needed.', 'webclyde'); ?></p>

					<div class="wc-repeater-wrap">
						<div id="wc-problems-list" class="wc-repeater-items">
							<?php
							if (! empty($main_problems)) :
								foreach ($main_problems as $problem) :
									if ('' === trim($problem)) {
										continue;
									}
							?>
									<div class="wc-repeater-row">
										<span class="dashicons dashicons-menu drag-handle"></span>
										<input type="text" name="_portfolio_main_problems[]" value="<?php echo esc_attr($problem); ?>" placeholder="<?php esc_attr_e('Enter a key problem faced by the client...', 'webclyde'); ?>">
										<button type="button" class="remove-row-btn" title="<?php esc_attr_e('Delete problem', 'webclyde'); ?>">&times;</button>
									</div>
							<?php
								endforeach;
							endif;
							?>
						</div>

						<button type="button" id="wc-add-problem-btn" class="button button-secondary">
							<span class="dashicons dashicons-plus-alt" style="vertical-align:middle; margin-top:-2px;"></span>
							<?php esc_html_e('Add Problem', 'webclyde'); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- 4. THE APPROACH -->
			<div id="tab-approach" class="wc-meta-tab-panel <?php echo 'tab-approach' === $active_tab ? 'active' : ''; ?>">
				<h3><?php esc_html_e('The Approach & Step-by-Step Execution', 'webclyde'); ?></h3>
				<p class="section-desc"><?php esc_html_e('Highlight your strategy, visual mockup / diagram, and your step-by-step roadmap.', 'webclyde'); ?></p>

				<div class="wc-field-group">
					<label for="wc_portfolio_approach_title"><?php esc_html_e('Approach Title', 'webclyde'); ?></label>
					<input type="text" id="wc_portfolio_approach_title" name="_portfolio_approach_title" value="<?php echo esc_attr($approach_title ?: 'Our Approach'); ?>" placeholder="e.g. Our Approach: User-First Strategy & Clean Architecture">
				</div>

				<div class="wc-field-group">
					<label><?php esc_html_e('Approach Image (Diagram / Wireframe / Architecture)', 'webclyde'); ?></label>
					<div class="wc-image-uploader">
						<div class="wc-image-preview">
							<?php if ($approach_img_url) : ?>
								<img src="<?php echo esc_url($approach_img_url); ?>" alt="">
							<?php else : ?>
								<span class="dashicons dashicons-format-image placeholder"></span>
							<?php endif; ?>
						</div>
						<div class="wc-image-actions">
							<input type="hidden" id="wc_portfolio_approach_image" name="_portfolio_approach_image" value="<?php echo esc_attr($approach_image_id); ?>">
							<button type="button" class="button button-secondary wc-upload-btn" data-target="wc_portfolio_approach_image">
								<?php esc_html_e('Upload / Select Image', 'webclyde'); ?>
							</button>
							<button type="button" class="button button-link-delete wc-remove-btn" data-target="wc_portfolio_approach_image" style="<?php echo $approach_image_id ? '' : 'display:none;'; ?>">
								<?php esc_html_e('Remove', 'webclyde'); ?>
							</button>
						</div>
					</div>
				</div>

				<div class="wc-field-group">
					<label><?php esc_html_e('Step-by-Step Approach Cards (Multiple Add Option)', 'webclyde'); ?></label>
					<p class="description"><?php esc_html_e('Add each milestone card with title and description (e.g. 5 steps).', 'webclyde'); ?></p>

					<div class="wc-repeater-wrap">
						<div id="wc-steps-list" class="wc-repeater-items">
							<?php
							if (! empty($approach_steps)) :
								foreach ($approach_steps as $idx => $step) :
									$st_title = $step['title'] ?? '';
									$st_desc  = $step['desc'] ?? '';
							?>
									<div class="wc-step-row">
										<div class="wc-step-row-header">
											<span class="wc-step-badge"><?php echo esc_html(sprintf(__('Step %d', 'webclyde'), $idx + 1)); ?></span>
											<button type="button" class="remove-row-btn" title="<?php esc_attr_e('Delete step', 'webclyde'); ?>">&times;</button>
										</div>
										<input type="text" name="_portfolio_approach_step_titles[]" value="<?php echo esc_attr($st_title); ?>" placeholder="<?php esc_attr_e('Step title (e.g. Discovery & UX Audit)...', 'webclyde'); ?>">
										<textarea name="_portfolio_approach_step_descs[]" placeholder="<?php esc_attr_e('Describe the approach taken in this step...', 'webclyde'); ?>"><?php echo esc_textarea($st_desc); ?></textarea>
									</div>
							<?php
								endforeach;
							endif;
							?>
						</div>

						<button type="button" id="wc-add-step-btn" class="button button-secondary">
							<span class="dashicons dashicons-plus-alt" style="vertical-align:middle; margin-top:-2px;"></span>
							<?php esc_html_e('Add Step', 'webclyde'); ?>
						</button>
					</div>
				</div>

				<div class="wc-field-group" style="margin-top: 24px; padding-top: 18px; border-top: 1px solid #dcdcde;">
					<label><?php esc_html_e('Approach Grid Extra Image / Badge (For Empty Slot)', 'webclyde'); ?></label>
					<p class="description"><?php esc_html_e('In a 3-column grid, if you have 5 steps, the 6th slot remains empty. You can place a badge or certificate image (e.g. WordPress Certified Developer) in that slot.', 'webclyde'); ?></p>

					<div style="display: flex; align-items: center; gap: 10px; margin: 12px 0;">
						<label for="wc_portfolio_approach_has_badge" style="font-weight: 600; font-size: 13px; margin: 0;"><?php esc_html_e('Show Badge / Image in Grid?', 'webclyde'); ?></label>
						<select id="wc_portfolio_approach_has_badge" name="_portfolio_approach_has_badge" style="max-width: 100px;">
							<option value="no" <?php selected($approach_has_badge, 'no'); ?>><?php esc_html_e('No', 'webclyde'); ?></option>
							<option value="yes" <?php selected($approach_has_badge, 'yes'); ?>><?php esc_html_e('Yes', 'webclyde'); ?></option>
						</select>
					</div>

					<div id="wc-approach-badge-box" style="<?php echo 'yes' === $approach_has_badge ? '' : 'display:none;'; ?>">
						<div class="wc-image-uploader">
							<div class="wc-image-preview">
								<?php if ($approach_badge_img_url) : ?>
									<img src="<?php echo esc_url($approach_badge_img_url); ?>" alt="">
								<?php else : ?>
									<span class="dashicons dashicons-format-image placeholder"></span>
								<?php endif; ?>
							</div>
							<div class="wc-image-actions">
								<input type="hidden" id="wc_portfolio_approach_badge_image" name="_portfolio_approach_badge_image" value="<?php echo esc_attr($approach_badge_image_id); ?>">
								<button type="button" class="button button-secondary wc-upload-btn" data-target="wc_portfolio_approach_badge_image">
									<?php esc_html_e('Upload / Select Badge Image', 'webclyde'); ?>
								</button>
								<button type="button" class="button button-link-delete wc-remove-btn" data-target="wc_portfolio_approach_badge_image" style="<?php echo $approach_badge_image_id ? '' : 'display:none;'; ?>">
									<?php esc_html_e('Remove', 'webclyde'); ?>
								</button>
							</div>
						</div>

						<div style="margin-top: 12px;">
							<label for="wc_portfolio_approach_badge_link" style="font-weight: 500; font-size: 12px;"><?php esc_html_e('Badge Link URL (Optional)', 'webclyde'); ?></label>
							<input type="url" id="wc_portfolio_approach_badge_link" name="_portfolio_approach_badge_link" value="<?php echo esc_url($approach_badge_link); ?>" placeholder="https://example.com/certification">
						</div>
					</div>
				</div>
			</div>

			<!-- 5. RESULTS -->
			<div id="tab-results" class="wc-meta-tab-panel <?php echo 'tab-results' === $active_tab ? 'active' : ''; ?>">
				<h3><?php esc_html_e('Results & Measurable Impact', 'webclyde'); ?></h3>
				<p class="section-desc"><?php esc_html_e('Document the outcomes, performance enhancements, and conversion benchmarks achieved.', 'webclyde'); ?></p>

				<div class="wc-field-group">
					<label for="wc_portfolio_result_title"><?php esc_html_e('Result Title', 'webclyde'); ?></label>
					<input type="text" id="wc_portfolio_result_title" name="_portfolio_result_title" value="<?php echo esc_attr($result_title ?: 'The Results'); ?>" placeholder="e.g. The Results: Faster Speed & 150% Checkout Growth">
				</div>

				<div class="wc-field-group">
					<label for="wc_portfolio_result_description"><?php esc_html_e('Result Description', 'webclyde'); ?></label>
					<textarea id="wc_portfolio_result_description" name="_portfolio_result_description" rows="4" placeholder="<?php esc_attr_e('Explain the transformation and business outcomes after project completion...', 'webclyde'); ?>"><?php echo esc_textarea($result_desc); ?></textarea>
				</div>

				<div class="wc-field-grid">
					<div class="wc-field-group">
						<label for="wc_portfolio_mobile_load_time"><?php esc_html_e('Mobile Load Time', 'webclyde'); ?></label>
						<input type="text" id="wc_portfolio_mobile_load_time" name="_portfolio_mobile_load_time" value="<?php echo esc_attr($mobile_load_time); ?>" placeholder="e.g. 0.9s (-62% faster)">
					</div>

					<div class="wc-field-group">
						<label for="wc_portfolio_pagespeed_score"><?php esc_html_e('Google PageSpeed Score (Mobile)', 'webclyde'); ?></label>
						<input type="text" id="wc_portfolio_pagespeed_score" name="_portfolio_pagespeed_score" value="<?php echo esc_attr($pagespeed_score); ?>" placeholder="e.g. 98/100">
					</div>

					<div class="wc-field-group wc-field-col-full">
						<label for="wc_portfolio_completed_checkouts"><?php esc_html_e('Completed Checkouts After Launch', 'webclyde'); ?></label>
						<input type="text" id="wc_portfolio_completed_checkouts" name="_portfolio_completed_checkouts" value="<?php echo esc_attr($completed_checkouts); ?>" placeholder="e.g. +185% Increase / 2,400+ checkouts">
					</div>
				</div>
			</div>

			<!-- 6. CLIENT REVIEW -->
			<div id="tab-review" class="wc-meta-tab-panel <?php echo 'tab-review' === $active_tab ? 'active' : ''; ?>">
				<h3><?php esc_html_e('Client Review & Testimonial', 'webclyde'); ?></h3>
				<p class="section-desc"><?php esc_html_e('Showcase what the client said about your work along with their profile and review proof.', 'webclyde'); ?></p>

				<div class="wc-field-group">
					<label for="wc_portfolio_client_said"><?php esc_html_e('What the Client Said (Testimonial Quote)', 'webclyde'); ?></label>
					<textarea id="wc_portfolio_client_said" name="_portfolio_client_said" rows="4" placeholder="<?php esc_attr_e('Paste the client quote or testimonial here...', 'webclyde'); ?>"><?php echo esc_textarea($client_said); ?></textarea>
				</div>

				<div class="wc-field-grid">
					<div class="wc-field-group">
						<label for="wc_portfolio_client_name"><?php esc_html_e('Client Name', 'webclyde'); ?></label>
						<input type="text" id="wc_portfolio_client_name" name="_portfolio_client_name" value="<?php echo esc_attr($client_name); ?>" placeholder="e.g. Sarah Jenkins">
					</div>

					<div class="wc-field-group">
						<label for="wc_portfolio_client_designation"><?php esc_html_e('Client Designation / Title / Company', 'webclyde'); ?></label>
						<input type="text" id="wc_portfolio_client_designation" name="_portfolio_client_designation" value="<?php echo esc_attr($client_designation); ?>" placeholder="e.g. Founder & CEO, BrandCo">
					</div>
				</div>

				<div class="wc-field-grid">
					<div class="wc-field-group">
						<label><?php esc_html_e('Client Avatar / Photo', 'webclyde'); ?></label>
						<div class="wc-image-uploader">
							<div class="wc-image-preview">
								<?php if ($client_avatar_url) : ?>
									<img src="<?php echo esc_url($client_avatar_url); ?>" alt="">
								<?php else : ?>
									<span class="dashicons dashicons-admin-users placeholder"></span>
								<?php endif; ?>
							</div>
							<div class="wc-image-actions">
								<input type="hidden" id="wc_portfolio_client_avatar" name="_portfolio_client_avatar" value="<?php echo esc_attr($client_avatar_id); ?>">
								<button type="button" class="button button-secondary wc-upload-btn" data-target="wc_portfolio_client_avatar">
									<?php esc_html_e('Upload Avatar', 'webclyde'); ?>
								</button>
								<button type="button" class="button button-link-delete wc-remove-btn" data-target="wc_portfolio_client_avatar" style="<?php echo $client_avatar_id ? '' : 'display:none;'; ?>">
									<?php esc_html_e('Remove', 'webclyde'); ?>
								</button>
							</div>
						</div>
					</div>

					<div class="wc-field-group">
						<label><?php esc_html_e('Review Proof Image (Badge / Clutch / Screenshot)', 'webclyde'); ?></label>
						<div class="wc-image-uploader">
							<div class="wc-image-preview">
								<?php if ($review_img_url) : ?>
									<img src="<?php echo esc_url($review_img_url); ?>" alt="">
								<?php else : ?>
									<span class="dashicons dashicons-format-image placeholder"></span>
								<?php endif; ?>
							</div>
							<div class="wc-image-actions">
								<input type="hidden" id="wc_portfolio_review_image" name="_portfolio_review_image" value="<?php echo esc_attr($review_image_id); ?>">
								<button type="button" class="button button-secondary wc-upload-btn" data-target="wc_portfolio_review_image">
									<?php esc_html_e('Upload Review Image', 'webclyde'); ?>
								</button>
								<button type="button" class="button button-link-delete wc-remove-btn" data-target="wc_portfolio_review_image" style="<?php echo $review_image_id ? '' : 'display:none;'; ?>">
									<?php esc_html_e('Remove', 'webclyde'); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>

<?php
}

/**
 * Save Meta Box Data
 */
function webclyde_save_portfolio_meta($post_id, $post = null)
{
	// 1. Avoid autosaves
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	// 2. Resolve revisions and autosaves to the actual post ID
	if ($parent_id = wp_is_post_revision($post_id)) {
		$post_id = $parent_id;
	}
	if ($parent_id = wp_is_post_autosave($post_id)) {
		$post_id = $parent_id;
	}

	// 3. Check post type
	if (! $post) {
		$post = get_post($post_id);
	}
	if (! $post || 'portfolio' !== $post->post_type) {
		return;
	}

	// 4. Check permissions
	if (! current_user_can('edit_post', $post_id)) {
		return;
	}

	// 5. Nonce check with fallback
	$nonce_valid = false;
	if (isset($_POST['webclyde_portfolio_nonce']) && wp_verify_nonce($_POST['webclyde_portfolio_nonce'], 'webclyde_save_portfolio_meta')) {
		$nonce_valid = true;
	} elseif (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'update-post_' . $post_id)) {
		$nonce_valid = true;
	}

	if (! $nonce_valid) {
		return;
	}

	// 6. Prevent duplicate runs in same request
	static $saved_posts = [];
	if (isset($saved_posts[$post_id])) {
		return;
	}
	$saved_posts[$post_id] = true;

	// Helper to save both `_key` and `key` for maximum compatibility
	$save_meta = function ($key, $val) use ($post_id) {
		$clean_key = ltrim($key, '_');
		update_post_meta($post_id, '_' . $clean_key, $val);
		update_post_meta($post_id, $clean_key, $val);
	};

	// Text & URL fields
	$text_fields = [
		'_portfolio_organization'          => 'sanitize_text_field',
		'_portfolio_services'              => 'sanitize_text_field',
		'_portfolio_timeline'              => 'sanitize_text_field',
		'_portfolio_industry'              => 'sanitize_text_field',
		'_portfolio_active_tab'            => 'sanitize_key',
		'_portfolio_platform'              => 'sanitize_text_field',
		'_portfolio_location'              => 'sanitize_text_field',
		'_portfolio_challenge_title'       => 'sanitize_text_field',
		'_portfolio_approach_title'        => 'sanitize_text_field',
		'_portfolio_approach_has_badge'    => 'sanitize_text_field',
		'_portfolio_approach_badge_link'   => 'esc_url_raw',
		'_portfolio_result_title'          => 'sanitize_text_field',
		'_portfolio_mobile_load_time'      => 'sanitize_text_field',
		'_portfolio_pagespeed_score'       => 'sanitize_text_field',
		'_portfolio_completed_checkouts'   => 'sanitize_text_field',
		'_portfolio_client_name'           => 'sanitize_text_field',
		'_portfolio_client_designation'    => 'sanitize_text_field',
		'_portfolio_site_url'              => 'esc_url_raw',
		'_portfolio_description'           => 'wp_kses_post',
		'_portfolio_challenge_description' => 'wp_kses_post',
		'_portfolio_result_description'    => 'wp_kses_post',
		'_portfolio_client_said'           => 'wp_kses_post',
	];

	foreach ($text_fields as $field => $sanitizer) {
		if (array_key_exists($field, $_POST)) {
			$val = call_user_func($sanitizer, wp_unslash($_POST[$field]));
			$save_meta($field, $val);
		}
	}

	// Image IDs
	$image_fields = [
		'_portfolio_logo',
		'_portfolio_approach_image',
		'_portfolio_approach_badge_image',
		'_portfolio_review_image',
		'_portfolio_client_avatar',
	];

	foreach ($image_fields as $img_field) {
		if (array_key_exists($img_field, $_POST)) {
			$img_id = absint($_POST[$img_field]);
			$save_meta($img_field, $img_id);
		}
	}

	// Gallery Image IDs (stored as comma-separated IDs and array)
	if (array_key_exists('_portfolio_gallery', $_POST)) {
		$raw_ids   = explode(',', sanitize_text_field(wp_unslash($_POST['_portfolio_gallery'])));
		$clean_ids = array_filter(array_map('absint', $raw_ids));
		$save_meta('_portfolio_gallery', implode(',', $clean_ids));
		$save_meta('_portfolio_gallery_array', $clean_ids);
	}

	// Repeater: The Main Problems
	if (isset($_POST['_portfolio_main_problems']) && is_array($_POST['_portfolio_main_problems'])) {
		$problems = [];
		foreach ($_POST['_portfolio_main_problems'] as $prob) {
			$trimmed = sanitize_text_field(wp_unslash($prob));
			if ('' !== $trimmed) {
				$problems[] = $trimmed;
			}
		}
		$save_meta('_portfolio_main_problems', $problems);
	} elseif (isset($_POST['webclyde_portfolio_nonce'])) {
		$save_meta('_portfolio_main_problems', []);
	}

	// Repeater: Step-by-Step Approach
	if (isset($_POST['_portfolio_approach_step_titles']) || isset($_POST['_portfolio_approach_step_descs'])) {
		$step_titles = isset($_POST['_portfolio_approach_step_titles']) && is_array($_POST['_portfolio_approach_step_titles']) ? $_POST['_portfolio_approach_step_titles'] : [];
		$step_descs  = isset($_POST['_portfolio_approach_step_descs']) && is_array($_POST['_portfolio_approach_step_descs']) ? $_POST['_portfolio_approach_step_descs'] : [];

		$steps = [];
		$max   = max(count($step_titles), count($step_descs));
		for ($i = 0; $i < $max; $i++) {
			$title = sanitize_text_field(wp_unslash($step_titles[$i] ?? ''));
			$desc  = sanitize_textarea_field(wp_unslash($step_descs[$i] ?? ''));

			if ('' !== $title || '' !== $desc) {
				$steps[] = [
					'title' => $title,
					'desc'  => $desc,
				];
			}
		}
		$save_meta('_portfolio_approach_steps', $steps);
	} elseif (isset($_POST['webclyde_portfolio_nonce'])) {
		$save_meta('_portfolio_approach_steps', []);
	}
}
add_action('save_post_portfolio', 'webclyde_save_portfolio_meta', 10, 2);
add_action('save_post', 'webclyde_save_portfolio_meta', 10, 2);
