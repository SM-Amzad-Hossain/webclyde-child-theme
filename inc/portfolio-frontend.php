<?php
/**
 * Portfolio case-study front end.
 *
 * Renders the meta fields registered in portfolio-meta.php as a complete
 * case-study layout:
 *
 *   1. Header       – logo, title, description, "Visit live site" button
 *   2. Project info – organization, services, timeline, industry, platform, location
 *   3. Carousel     – swipeable image gallery
 *   4. Challenge    – title, description, list of main problems
 *   5. Approach     – image, title, numbered steps
 *   6. Results      – title, description, stat cards (load time / PageSpeed / checkouts)
 *   7. Review       – review image, quote, client avatar, name, designation
 *
 * Output is available three ways:
 *   - Automatically appended to single Portfolio pages (filterable).
 *   - Shortcode: [portfolio_case_study id="123" sections="challenge,results"]
 *   - Elementor widget: "Portfolio Case Study" (for Theme Builder templates).
 *   - Template tag: <?php echo wpc_render_case_study( get_the_ID() ); ?>
 *
 * @package webclyde
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asset version helper (filemtime cache-busting).
 *
 * @param string $relative Path relative to the child theme root.
 * @return string
 */
function wpc_asset_version( $relative ) {
	$file = get_stylesheet_directory() . '/' . $relative;
	return file_exists( $file ) ? (string) filemtime( $file ) : '1.0.0';
}

/**
 * Register (and conditionally enqueue) front-end assets.
 */
function wpc_register_assets() {
	$base = get_stylesheet_directory_uri() . '/assets';

	wp_register_style( 'wpc-portfolio', $base . '/css/portfolio.css', [], wpc_asset_version( 'assets/css/portfolio.css' ) );
	wp_register_script( 'wpc-portfolio', $base . '/js/portfolio.js', [], wpc_asset_version( 'assets/js/portfolio.js' ), true );

	if ( is_singular( 'portfolio' ) ) {
		wp_enqueue_style( 'wpc-portfolio' );
		wp_enqueue_script( 'wpc-portfolio' );
	}
}
add_action( 'wp_enqueue_scripts', 'wpc_register_assets' );

/**
 * Attachment image HTML with graceful fallback.
 *
 * @param int    $id      Attachment ID.
 * @param string $size    Image size.
 * @param string $class   Extra class attribute.
 * @param bool   $loading_lazy Whether to lazy-load.
 * @return string
 */
function wpc_image( $id, $size = 'large', $class = '', $loading_lazy = true ) {
	$id = (int) $id;
	if ( ! $id ) {
		return '';
	}

	$mime = get_post_mime_type( $id );
	if ( ! $mime || 0 !== strpos( $mime, 'image/' ) ) {
		return '';
	}

	$loading = $loading_lazy ? 'loading="lazy"' : 'fetchpriority="high"';

	// SVG (e.g. a vector logo): render a plain <img>; no srcset/sizes apply.
	if ( 'image/svg+xml' === $mime ) {
		$url = wp_get_attachment_image_url( $id, 'full' );
		if ( ! $url ) {
			return '';
		}
		$alt = trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );
		return sprintf(
			'<img src="%s" class="%s" alt="%s" decoding="async" %s />',
			esc_url( $url ),
			esc_attr( $class ),
			esc_attr( $alt ),
			$loading
		);
	}

	if ( ! wp_attachment_is_image( $id ) ) {
		return '';
	}

	$attr = [ 'class' => $class, 'decoding' => 'async' ];
	if ( $loading_lazy ) {
		$attr['loading'] = 'lazy';
	} else {
		$attr['fetchpriority'] = 'high';
	}

	return wp_get_attachment_image( $id, $size, false, array_filter( $attr ) );
}

/**
 * Section: header + project info grid.
 *
 * @param int   $post_id Post ID.
 * @param array $d       Meta data.
 */
function wpc_section_header( $post_id, $d ) {
	$logo = wpc_image( $d['logo'], 'medium', 'wcp-logo', false );

	$info = [
		__( 'Organization', 'webclyde' ) => $d['organization'],
		__( 'Services', 'webclyde' )     => $d['services'],
		__( 'Timeline', 'webclyde' )     => $d['timeline'],
		__( 'Industry', 'webclyde' )     => $d['industry'],
		__( 'Platform', 'webclyde' )     => $d['platform'],
		__( 'Location', 'webclyde' )     => $d['location'],
	];
	$info = array_filter( $info, 'strlen' );
	?>
	<header class="wcp-header">
		<div class="wcp-header-top">
			<?php if ( $logo ) : ?>
				<div class="wcp-logo-wrap"><?php echo $logo; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php endif; ?>
			<div class="wcp-header-text">
				<p class="wcp-eyebrow"><?php esc_html_e( 'Case Study', 'webclyde' ); ?></p>
				<h1 class="wcp-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
				<?php if ( $d['description'] ) : ?>
					<p class="wcp-description"><?php echo esc_html( $d['description'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $d['site_url'] ) : ?>
				<a class="wcp-btn" href="<?php echo esc_url( $d['site_url'] ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Visit live site', 'webclyde' ); ?>
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17L17 7M17 7H8M17 7v9" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</a>
			<?php endif; ?>
		</div>

		<?php if ( $info ) : ?>
			<dl class="wcp-info-grid">
				<?php foreach ( $info as $label => $value ) : ?>
					<div class="wcp-info-item">
						<dt><?php echo esc_html( $label ); ?></dt>
						<?php if ( __( 'Services', 'webclyde' ) === $label ) : ?>
							<dd class="wcp-services">
								<?php foreach ( array_filter( array_map( 'trim', explode( ',', $value ) ) ) as $service ) : ?>
									<span class="wcp-chip"><?php echo esc_html( $service ); ?></span>
								<?php endforeach; ?>
							</dd>
						<?php else : ?>
							<dd><?php echo esc_html( $value ); ?></dd>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>
	</header>
	<?php
}

/**
 * Section: image carousel.
 *
 * @param int   $post_id Post ID.
 * @param array $d       Meta data.
 */
function wpc_section_carousel( $post_id, $d ) {
	$ids = $d['gallery'];
	if ( ! $ids ) {
		return;
	}

	$slides = [];
	foreach ( $ids as $id ) {
		$img = wpc_image( $id, 'large', 'wcp-slide-img', empty( $slides ) );
		if ( $img ) {
			$slides[] = $img;
		}
	}
	if ( ! $slides ) {
		return;
	}
	?>
	<div class="wcp-carousel" data-wcp-carousel aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Project images', 'webclyde' ); ?>">
		<div class="wcp-carousel-viewport" tabindex="0">
			<ul class="wcp-carousel-track">
				<?php foreach ( $slides as $i => $img ) : ?>
					<li class="wcp-carousel-slide" role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( __( '%1$d of %2$d', 'webclyde' ), $i + 1, count( $slides ) ) ); ?>">
						<?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php if ( count( $slides ) > 1 ) : ?>
			<button type="button" class="wcp-carousel-btn wcp-prev" aria-label="<?php esc_attr_e( 'Previous image', 'webclyde' ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
			<button type="button" class="wcp-carousel-btn wcp-next" aria-label="<?php esc_attr_e( 'Next image', 'webclyde' ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
			<div class="wcp-carousel-footer">
				<div class="wcp-carousel-dots" role="tablist" aria-label="<?php esc_attr_e( 'Choose image', 'webclyde' ); ?>">
					<?php foreach ( $slides as $i => $img ) : ?>
						<button type="button" class="wcp-dot<?php echo 0 === $i ? ' is-active' : ''; ?>" role="tab" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Go to image %d', 'webclyde' ), $i + 1 ) ); ?>"></button>
					<?php endforeach; ?>
				</div>
				<span class="wcp-carousel-counter" aria-live="polite">1 / <?php echo (int) count( $slides ); ?></span>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Section heading helper.
 *
 * @param string $eyebrow Small label.
 * @param string $title   Heading text (optional).
 */
function wpc_section_heading( $eyebrow, $title = '' ) {
	?>
	<div class="wcp-section-head">
		<span class="wcp-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
		<?php if ( $title ) : ?>
			<h2 class="wcp-section-title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Section: the challenge.
 *
 * @param int   $post_id Post ID.
 * @param array $d       Meta data.
 */
function wpc_section_challenge( $post_id, $d ) {
	if ( ! $d['challenge_title'] && ! $d['challenge_description'] && ! $d['problems'] ) {
		return;
	}
	?>
	<section class="wcp-section wcp-challenge">
		<?php wpc_section_heading( __( 'The Challenge', 'webclyde' ), $d['challenge_title'] ); ?>

		<?php if ( $d['challenge_description'] ) : ?>
			<div class="wcp-prose"><?php echo wpautop( esc_html( $d['challenge_description'] ) ); ?></div>
		<?php endif; ?>

		<?php if ( $d['problems'] ) : ?>
			<ul class="wcp-problems">
				<?php foreach ( $d['problems'] as $problem ) : ?>
					<li>
						<span class="wcp-problem-icon" aria-hidden="true">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 8v5m0 3.5v.5M10.3 3.9L2.6 17a2 2 0 001.7 3h15.4a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</span>
						<span><?php echo esc_html( $problem ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * Section: the approach.
 *
 * @param int   $post_id Post ID.
 * @param array $d       Meta data.
 */
function wpc_section_approach( $post_id, $d ) {
	if ( ! $d['approach_image'] && ! $d['approach_title'] && ! $d['approach_steps'] ) {
		return;
	}

	$image = wpc_image( $d['approach_image'], 'large', 'wcp-approach-img' );
	?>
	<section class="wcp-section wcp-approach">
		<?php wpc_section_heading( __( 'The Approach', 'webclyde' ) ); ?>
		<div class="wcp-approach-grid<?php echo $image ? ' has-image' : ''; ?>">
			<?php if ( $image ) : ?>
				<figure class="wcp-approach-media"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput ?></figure>
			<?php endif; ?>
			<div class="wcp-approach-body">
				<?php if ( $d['approach_title'] ) : ?>
					<h2 class="wcp-section-title"><?php echo esc_html( $d['approach_title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( $d['approach_steps'] ) : ?>
					<ol class="wcp-steps">
						<?php foreach ( $d['approach_steps'] as $i => $step ) : ?>
							<li>
								<span class="wcp-step-num" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
								<span class="wcp-step-text"><?php echo esc_html( $step ); ?></span>
							</li>
						<?php endforeach; ?>
					</ol>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Section: the results.
 *
 * @param int   $post_id Post ID.
 * @param array $d       Meta data.
 */
function wpc_section_results( $post_id, $d ) {
	$stats = array_filter(
		[
			[
				'value' => $d['mobile_load_time'],
				'label' => __( 'Mobile load time', 'webclyde' ),
				'icon'  => 'bolt',
			],
			[
				'value' => ( '' !== $d['pagespeed_mobile'] ) ? $d['pagespeed_mobile'] . '/100' : '',
				'label' => __( 'Google PageSpeed score (mobile)', 'webclyde' ),
				'icon'  => 'gauge',
			],
			[
				'value' => $d['checkouts'],
				'label' => __( 'Completed checkouts after launch', 'webclyde' ),
				'icon'  => 'cart',
			],
		],
		function ( $stat ) {
			return '' !== $stat['value'];
		}
	);

	if ( ! $d['results_title'] && ! $d['results_description'] && ! $stats ) {
		return;
	}

	$icons = [
		'bolt'  => '<path d="M13 2L4.5 13.5H11L10 22l8.5-11.5H12L13 2z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
		'gauge' => '<path d="M12 15l4-6M3.5 15a9 9 0 1117 0M12 15h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
		'cart'  => '<path d="M3 4h2l2.4 12.2a1.5 1.5 0 001.5 1.3h8.7a1.5 1.5 0 001.5-1.2L21 8H6M9 21h.01M17 21h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
	];
	?>
	<section class="wcp-section wcp-results">
		<?php wpc_section_heading( __( 'The Results', 'webclyde' ), $d['results_title'] ); ?>

		<?php if ( $d['results_description'] ) : ?>
			<div class="wcp-prose"><?php echo wpautop( esc_html( $d['results_description'] ) ); ?></div>
		<?php endif; ?>

		<?php if ( $stats ) : ?>
			<div class="wcp-stats">
				<?php foreach ( $stats as $stat ) : ?>
					<div class="wcp-stat">
						<span class="wcp-stat-icon" aria-hidden="true">
							<svg width="22" height="22" viewBox="0 0 24 24" fill="none"><?php echo $icons[ $stat['icon'] ]; // phpcs:ignore WordPress.Security.EscapeOutput ?></svg>
						</span>
						<span class="wcp-stat-value"><?php echo esc_html( $stat['value'] ); ?></span>
						<span class="wcp-stat-label"><?php echo esc_html( $stat['label'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * Section: client review.
 *
 * @param int   $post_id Post ID.
 * @param array $d       Meta data.
 */
function wpc_section_review( $post_id, $d ) {
	if ( ! $d['review_image'] && ! $d['review_quote'] && ! $d['client_name'] ) {
		return;
	}

	$review_image = wpc_image( $d['review_image'], 'large', 'wcp-review-img' );
	$avatar       = wpc_image( $d['client_avatar'], 'thumbnail', 'wcp-avatar' );
	?>
	<section class="wcp-section wcp-review">
		<?php wpc_section_heading( __( 'Client Review', 'webclyde' ) ); ?>
		<div class="wcp-review-grid<?php echo $review_image ? ' has-image' : ''; ?>">
			<?php if ( $review_image ) : ?>
				<figure class="wcp-review-media"><?php echo $review_image; // phpcs:ignore WordPress.Security.EscapeOutput ?></figure>
			<?php endif; ?>

			<?php if ( $d['review_quote'] || $d['client_name'] ) : ?>
				<figure class="wcp-quote-card">
					<svg class="wcp-quote-mark" width="36" height="36" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M10.7 5.6C7 7.3 4.8 10.4 4.8 14.1c0 2.9 1.8 4.9 4.2 4.9 2.2 0 3.8-1.6 3.8-3.7 0-2-1.4-3.4-3.3-3.4-.4 0-.8.1-1 .2.3-2 2-4.1 4-5.1l-1.8-1.4zm8.5 0c-3.7 1.7-5.9 4.8-5.9 8.5 0 2.9 1.8 4.9 4.2 4.9 2.1 0 3.8-1.6 3.8-3.7 0-2-1.4-3.4-3.3-3.4-.4 0-.8.1-1 .2.3-2 2-4.1 4-5.1l-1.8-1.4z"/></svg>
					<?php if ( $d['review_quote'] ) : ?>
						<blockquote class="wcp-quote"><?php echo wpautop( esc_html( $d['review_quote'] ) ); ?></blockquote>
					<?php endif; ?>
					<?php if ( $d['client_name'] || $avatar ) : ?>
						<figcaption class="wcp-client">
							<?php if ( $avatar ) : ?>
								<span class="wcp-avatar-wrap"><?php echo $avatar; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<?php endif; ?>
							<span class="wcp-client-text">
								<?php if ( $d['client_name'] ) : ?>
									<cite class="wcp-client-name"><?php echo esc_html( $d['client_name'] ); ?></cite>
								<?php endif; ?>
								<?php if ( $d['client_designation'] ) : ?>
									<span class="wcp-client-role"><?php echo esc_html( $d['client_designation'] ); ?></span>
								<?php endif; ?>
							</span>
						</figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Collect all case-study data for a post.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function wpc_get_data( $post_id ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$data    = [];

	foreach ( wpc_meta_fields() as $key => $field ) {
		$data[ $key ] = wpc_get_meta( $post_id, $key );
	}

	return $data;
}

/**
 * Render the full case study (or selected sections).
 *
 * @param int   $post_id Post ID (0 = current post).
 * @param array $args    { sections: array|string list of section slugs }.
 * @return string HTML.
 */
function wpc_render_case_study( $post_id = 0, $args = [] ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	if ( ! $post_id || 'portfolio' !== get_post_type( $post_id ) ) {
		return '';
	}

	// Track rendered posts so the auto-append does not duplicate a manual
	// shortcode / widget placement.
	global $wpc_rendered_posts;
	if ( ! is_array( $wpc_rendered_posts ) ) {
		$wpc_rendered_posts = [];
	}
	$wpc_rendered_posts[ $post_id ] = true;

	wp_enqueue_style( 'wpc-portfolio' );
	wp_enqueue_script( 'wcp-portfolio' );

	$all_sections = [ 'header', 'carousel', 'challenge', 'approach', 'results', 'review' ];
	$sections     = $all_sections;
	if ( ! empty( $args['sections'] ) ) {
		$requested = is_array( $args['sections'] ) ? $args['sections'] : explode( ',', $args['sections'] );
		$sections  = array_values( array_intersect( $all_sections, array_map( 'trim', $requested ) ) );
	}

	$d = wpc_get_data( $post_id );

	ob_start();
	echo '<div class="wcp-case-study">';
	foreach ( $sections as $section ) {
		$callback = 'wpc_section_' . $section;
		if ( function_exists( $callback ) ) {
			$callback( $post_id, $d );
		}
	}
	echo '</div>';

	return (string) ob_get_clean();
}

/**
 * Shortcode: [portfolio_case_study id="123" sections="header,carousel,challenge,approach,results,review"]
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function wpc_shortcode( $atts ) {
	$atts = shortcode_atts(
		[
			'id'       => 0,
			'sections' => '',
		],
		$atts,
		'portfolio_case_study'
	);

	return wpc_render_case_study( absint( $atts['id'] ), [ 'sections' => $atts['sections'] ] );
}
add_shortcode( 'portfolio_case_study', 'wpc_shortcode' );

/**
 * Auto-append the case study to single portfolio content.
 *
 * Disable with:
 *   add_filter( 'wpc_auto_append_case_study', '__return_false' );
 *
 * @param string $content Post content.
 * @return string
 */
function wpc_auto_append( $content ) {
	global $wpc_rendered_posts;

	if ( ! is_singular( 'portfolio' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	if ( post_password_required() ) {
		return $content;
	}

	$post_id = get_the_ID();
	if ( ! empty( $wpc_rendered_posts[ $post_id ] ) ) {
		return $content; // Already placed via shortcode / widget.
	}
	if ( ! apply_filters( 'wpc_auto_append_case_study', true, $post_id ) ) {
		return $content;
	}

	return $content . wpc_render_case_study( $post_id );
}
add_filter( 'the_content', 'wpc_auto_append', 20 );

/**
 * Register the Elementor widget (when Elementor is active).
 */
function wpc_register_elementor_widget( $widgets_manager ) {
	if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
		return;
	}

	if ( ! class_exists( 'WPC_Case_Study_Widget' ) ) {

		/**
		 * Elementor widget that renders the portfolio case study.
		 */
		class WPC_Case_Study_Widget extends \Elementor\Widget_Base {

			public function get_name() {
				return 'wpc_case_study';
			}

			public function get_title() {
				return __( 'Portfolio Case Study', 'webclyde' );
			}

			public function get_icon() {
				return 'eicon-posts-grid';
			}

			public function get_categories() {
				return [ 'basic' ];
			}

			public function get_keywords() {
				return [ 'portfolio', 'case', 'study', 'project', 'webclyde' ];
			}

			public function get_style_depends() {
				return [ 'wcp-portfolio' ];
			}

			public function get_script_depends() {
				return [ 'wcp-portfolio' ];
			}

			protected function register_controls() {
				$this->start_controls_section(
					'section_content',
					[ 'label' => __( 'Case Study', 'webclyde' ) ]
				);

				$this->add_control(
					'post_id',
					[
						'label'       => __( 'Portfolio ID', 'webclyde' ),
						'type'        => \Elementor\Controls_Manager::NUMBER,
						'description' => __( 'Leave empty to use the current portfolio (recommended in Single Portfolio templates).', 'webclyde' ),
					]
				);

				$sections = [
					'header'    => __( 'Header + project details', 'webclyde' ),
					'carousel'  => __( 'Image carousel', 'webclyde' ),
					'challenge' => __( 'The challenge', 'webclyde' ),
					'approach'  => __( 'The approach', 'webclyde' ),
					'results'   => __( 'The results', 'webclyde' ),
					'review'    => __( 'Client review', 'webclyde' ),
				];

				foreach ( $sections as $slug => $label ) {
					$this->add_control(
						'show_' . $slug,
						[
							'label'        => $label,
							'type'         => \Elementor\Controls_Manager::SWITCHER,
							'default'      => 'yes',
							'return_value' => 'yes',
						]
					);
				}

				$this->end_controls_section();
			}

			protected function render() {
				$settings = $this->get_settings_for_display();
				$post_id  = ! empty( $settings['post_id'] ) ? absint( $settings['post_id'] ) : get_the_ID();

				$sections = [];
				foreach ( [ 'header', 'carousel', 'challenge', 'approach', 'results', 'review' ] as $slug ) {
					if ( ! isset( $settings[ 'show_' . $slug ] ) || 'yes' === $settings[ 'show_' . $slug ] ) {
						$sections[] = $slug;
					}
				}

				echo wpc_render_case_study( $post_id, [ 'sections' => $sections ] ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
		}
	}

	$widgets_manager->register( new \WPC_Case_Study_Widget() );
}
add_action( 'elementor/widgets/register', 'wpc_register_elementor_widget' );
