<?php
/**
 * Shared Render Functions for Portfolio Case Study
 * Used by Widgets, Shortcodes, and single-portfolio.php
 *
 * @package Webclyde
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Frontend Assets when needed
 */
function webclyde_portfolio_enqueue_frontend_assets() {
	wp_register_style(
		'webclyde-portfolio-frontend-css',
		get_stylesheet_directory_uri() . '/assets/portfolio-frontend.css',
		[],
		filemtime( get_stylesheet_directory() . '/assets/portfolio-frontend.css' )
	);
	wp_enqueue_style( 'webclyde-portfolio-frontend-css' );

	wp_register_script(
		'webclyde-portfolio-frontend-js',
		get_stylesheet_directory_uri() . '/assets/portfolio-frontend.js',
		[ 'jquery' ],
		filemtime( get_stylesheet_directory() . '/assets/portfolio-frontend.js' ),
		true
	);
	wp_enqueue_script( 'webclyde-portfolio-frontend-js' );
}
add_action( 'wp_enqueue_scripts', 'webclyde_portfolio_enqueue_frontend_assets' );

/**
 * Helper: Resolve the active portfolio post ID reliably
 */
function webclyde_get_portfolio_post_id( $post_id = 0 ) {
	if ( $post_id && 'portfolio' === get_post_type( $post_id ) ) {
		return (int) $post_id;
	}

	$id = $post_id ? (int) $post_id : get_the_ID();
	if ( $id && 'portfolio' === get_post_type( $id ) ) {
		return $id;
	}

	$queried = get_queried_object_id();
	if ( $queried && 'portfolio' === get_post_type( $queried ) ) {
		return $queried;
	}

	global $post;
	if ( ! empty( $post->ID ) && 'portfolio' === get_post_type( $post->ID ) ) {
		return (int) $post->ID;
	}

	// Elementor Theme Builder preview
	if ( ! empty( $_GET['elementor-preview'] ) ) {
		$preview_id = (int) $_GET['elementor-preview'];
		if ( 'portfolio' === get_post_type( $preview_id ) ) {
			return $preview_id;
		}
	}

	if ( class_exists( '\Elementor\Plugin' ) ) {
		$doc = \Elementor\Plugin::$instance->documents->get_current();
		if ( $doc ) {
			$preview_id = (int) $doc->get_settings( 'preview_id' );
			if ( $preview_id && 'portfolio' === get_post_type( $preview_id ) ) {
				return $preview_id;
			}
			$main_id = $doc->get_main_id();
			if ( 'portfolio' === get_post_type( $main_id ) ) {
				return $main_id;
			}
		}

		// When editing in Elementor, fallback to latest portfolio item for preview
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			$sample = get_posts( [
				'post_type'      => 'portfolio',
				'posts_per_page' => 1,
				'post_status'    => 'any',
				'fields'         => 'ids',
			] );
			if ( ! empty( $sample ) ) {
				return (int) $sample[0];
			}
		}
	}

	return $id ?: 0;
}

/**
 * 1. Render Meta & Overview Section
 */
function webclyde_render_portfolio_meta( $post_id = 0, $args = [] ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$logo_id      = webclyde_get_portfolio_field( '_portfolio_logo', $post_id );
	$logo_url     = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
	$site_url     = webclyde_get_portfolio_field( '_portfolio_site_url', $post_id );
	$description  = webclyde_get_portfolio_field( '_portfolio_description', $post_id );
	$organization = webclyde_get_portfolio_field( '_portfolio_organization', $post_id );
	$services     = webclyde_get_portfolio_field( '_portfolio_services', $post_id );
	$timeline     = webclyde_get_portfolio_field( '_portfolio_timeline', $post_id );
	$industry     = webclyde_get_portfolio_field( '_portfolio_industry', $post_id );
	$platform     = webclyde_get_portfolio_field( '_portfolio_platform', $post_id );
	$location     = webclyde_get_portfolio_field( '_portfolio_location', $post_id );

	$show_logo        = $args['show_logo'] ?? true;
	$show_button      = $args['show_button'] ?? true;
	$button_text      = $args['button_text'] ?? __( 'Visit Website', 'webclyde' );
	$show_description = $args['show_description'] ?? true;

	ob_start();
	?>
	<div class="wc-port-meta-card">
		<?php if ( ( $show_logo && $logo_url ) || ( $show_button && $site_url ) ) : ?>
			<div class="wc-port-meta-header">
				<?php if ( $show_logo && $logo_url ) : ?>
					<div class="wc-port-logo-wrap">
						<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $organization ); ?>">
					</div>
				<?php endif; ?>

				<?php if ( $show_button && $site_url ) : ?>
					<a href="<?php echo esc_url( $site_url ); ?>" target="_blank" rel="noopener noreferrer" class="wc-port-site-btn">
						<?php echo esc_html( $button_text ); ?> &rarr;
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $show_description && $description ) : ?>
			<div class="wc-port-description">
				<?php echo wp_kses_post( wpautop( $description ) ); ?>
			</div>
		<?php endif; ?>

		<div class="wc-port-meta-grid">
			<?php if ( $organization ) : ?>
				<div class="wc-port-meta-item">
					<span class="wc-port-meta-label"><?php esc_html_e( 'Organization', 'webclyde' ); ?></span>
					<span class="wc-port-meta-value"><?php echo esc_html( $organization ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( $services ) : ?>
				<div class="wc-port-meta-item">
					<span class="wc-port-meta-label"><?php esc_html_e( 'Services', 'webclyde' ); ?></span>
					<span class="wc-port-meta-value"><?php echo esc_html( $services ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( $timeline ) : ?>
				<div class="wc-port-meta-item">
					<span class="wc-port-meta-label"><?php esc_html_e( 'Timeline', 'webclyde' ); ?></span>
					<span class="wc-port-meta-value"><?php echo esc_html( $timeline ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( $industry ) : ?>
				<div class="wc-port-meta-item">
					<span class="wc-port-meta-label"><?php esc_html_e( 'Industry', 'webclyde' ); ?></span>
					<span class="wc-port-meta-value"><?php echo esc_html( $industry ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( $platform ) : ?>
				<div class="wc-port-meta-item">
					<span class="wc-port-meta-label"><?php esc_html_e( 'Platform', 'webclyde' ); ?></span>
					<span class="wc-port-meta-value"><?php echo esc_html( $platform ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( $location ) : ?>
				<div class="wc-port-meta-item">
					<span class="wc-port-meta-label"><?php esc_html_e( 'Location', 'webclyde' ); ?></span>
					<span class="wc-port-meta-value"><?php echo esc_html( $location ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * 2. Render Image Carousel / Gallery (Smooth Autoplay + Live Preview)
 */
function webclyde_render_portfolio_carousel( $post_id = 0, $args = [] ) {
	if ( is_string( $args ) ) {
		$args = [ 'layout' => $args ];
	}

	$post_id = function_exists( 'webclyde_get_portfolio_post_id' ) ? webclyde_get_portfolio_post_id( $post_id ) : ( $post_id ?: get_the_ID() );

	$image_items = []; // Array of [ 'id' => ..., 'url' => ..., 'alt' => ... ]

	// Custom images passed (e.g. from Elementor default WP gallery picker) or fallback to post meta
	if ( ! empty( $args['images'] ) && is_array( $args['images'] ) ) {
		$raw_ids = array_filter( array_map( 'absint', $args['images'] ) );
		foreach ( $raw_ids as $id ) {
			$src = wp_get_attachment_image_url( $id, 'large' );
			if ( $src ) {
				$image_items[] = [
					'id'  => $id,
					'url' => $src,
					'alt' => trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) ),
				];
			}
		}
	} else {
		$gallery_str = webclyde_get_portfolio_field( '_portfolio_gallery', $post_id );
		$raw_ids     = array_filter( array_map( 'absint', explode( ',', (string) $gallery_str ) ) );
		foreach ( $raw_ids as $id ) {
			$src = wp_get_attachment_image_url( $id, 'large' );
			if ( $src ) {
				$image_items[] = [
					'id'  => $id,
					'url' => $src,
					'alt' => trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) ),
				];
			}
		}
	}

	// LIVE PREVIEW FALLBACK FOR ELEMENTOR EDITOR
	if ( empty( $image_items ) && class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
		// 1. Try any other portfolio post with a gallery
		$sample_posts = get_posts( [
			'post_type'      => 'portfolio',
			'posts_per_page' => 5,
			'post_status'    => 'any',
			'fields'         => 'ids',
		] );
		foreach ( $sample_posts as $sp_id ) {
			$sp_gallery = webclyde_get_portfolio_field( '_portfolio_gallery', $sp_id );
			$sp_ids     = array_filter( array_map( 'absint', explode( ',', (string) $sp_gallery ) ) );
			if ( ! empty( $sp_ids ) ) {
				foreach ( $sp_ids as $sp_img_id ) {
					$sp_src = wp_get_attachment_image_url( $sp_img_id, 'large' );
					if ( $sp_src ) {
						$image_items[] = [
							'id'  => $sp_img_id,
							'url' => $sp_src,
							'alt' => 'Portfolio Gallery Preview',
						];
					}
				}
				break;
			}
		}

		// 2. Try media library image attachments
		if ( empty( $image_items ) ) {
			$media_sample = get_posts( [
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 4,
				'fields'         => 'ids',
			] );
			foreach ( $media_sample as $att_id ) {
				$att_src = wp_get_attachment_image_url( $att_id, 'large' );
				if ( $att_src ) {
					$image_items[] = [
						'id'  => $att_id,
						'url' => $att_src,
						'alt' => 'Media Library Sample',
					];
				}
			}
		}

		// 3. Fallback to Elementor placeholder image so editor always shows the working carousel
		if ( empty( $image_items ) ) {
			$placeholder_src = \Elementor\Utils::get_placeholder_image_src();
			for ( $i = 1; $i <= 3; $i++ ) {
				$image_items[] = [
					'id'  => 0,
					'url' => $placeholder_src,
					'alt' => 'Preview Slide ' . $i,
				];
			}
		}
	}

	if ( empty( $image_items ) ) {
		return '';
	}

	$layout           = $args['layout'] ?? 'slider';
	$autoplay         = $args['autoplay'] ?? 'yes';
	$mode             = $args['autoplay_mode'] ?? 'smooth_slides';
	$speed            = ! empty( $args['speed'] ) ? (int) $args['speed'] : 3500;
	$transition_speed = ! empty( $args['transition_speed'] ) ? (int) $args['transition_speed'] : 800;
	$pause            = $args['pause_on_hover'] ?? 'yes';
	$per_view         = ! empty( $args['slides_to_show'] ) ? (int) $args['slides_to_show'] : 2;
	$per_view_tab     = ! empty( $args['slides_to_show_tablet'] ) ? (int) $args['slides_to_show_tablet'] : min( 2, $per_view );
	$per_view_mob     = ! empty( $args['slides_to_show_mobile'] ) ? (int) $args['slides_to_show_mobile'] : 1;
	$gap              = isset( $args['slide_gap'] ) ? (int) $args['slide_gap'] : 20;
	$show_arrows      = ! isset( $args['show_arrows'] ) || 'yes' === $args['show_arrows'] || true === $args['show_arrows'];
	$show_dots        = ! isset( $args['show_dots'] ) || 'yes' === $args['show_dots'] || true === $args['show_dots'];
	$height           = ! empty( $args['image_height'] ) ? (int) $args['image_height'] : 380;
	$fit              = $args['image_fit'] ?? 'cover';
	$style_attr       = 'height:' . esc_attr( $height ) . 'px; object-fit:' . esc_attr( $fit ) . ';';

	ob_start();
	?>
	<?php if ( 'grid' === $layout ) : ?>
		<div class="wc-port-carousel-wrap wc-port-grid-wrap" data-gap="<?php echo esc_attr( $gap ); ?>">
			<div class="wc-port-gallery-grid" style="gap: <?php echo esc_attr( $gap ); ?>px;">
				<?php foreach ( $image_items as $item ) : ?>
					<div class="wc-port-gallery-item">
						<img src="<?php echo esc_url( $item['url'] ); ?>" alt="<?php echo esc_attr( $item['alt'] ); ?>" style="<?php echo esc_attr( $style_attr ); ?>" loading="lazy">
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php else : ?>
		<div class="wc-port-carousel-wrap"
		     data-mode="<?php echo esc_attr( $mode ); ?>"
		     data-autoplay="<?php echo esc_attr( $autoplay ); ?>"
		     data-speed="<?php echo esc_attr( $speed ); ?>"
		     data-transition="<?php echo esc_attr( $transition_speed ); ?>"
		     data-pause="<?php echo esc_attr( $pause ); ?>"
		     data-per-view="<?php echo esc_attr( $per_view ); ?>"
		     data-per-view-tablet="<?php echo esc_attr( $per_view_tab ); ?>"
		     data-per-view-mobile="<?php echo esc_attr( $per_view_mob ); ?>"
		     data-gap="<?php echo esc_attr( $gap ); ?>">

			<div class="wc-port-carousel-viewport">
				<div class="wc-port-carousel-track" style="gap: <?php echo esc_attr( $gap ); ?>px;">
					<?php foreach ( $image_items as $item ) : ?>
						<div class="wc-port-slide">
							<img src="<?php echo esc_url( $item['url'] ); ?>" alt="<?php echo esc_attr( $item['alt'] ); ?>" style="<?php echo esc_attr( $style_attr ); ?>" loading="lazy">
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( $show_arrows && count( $image_items ) > 1 && 'smooth_ticker' !== $mode ) : ?>
				<button type="button" class="wc-port-arrow wc-port-arrow-prev" aria-label="<?php esc_attr_e( 'Previous', 'webclyde' ); ?>">&lsaquo;</button>
				<button type="button" class="wc-port-arrow wc-port-arrow-next" aria-label="<?php esc_attr_e( 'Next', 'webclyde' ); ?>">&rsaquo;</button>
			<?php endif; ?>

			<?php if ( $show_dots && count( $image_items ) > 1 && 'smooth_ticker' !== $mode ) : ?>
				<div class="wc-port-dots"></div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<?php
	return ob_get_clean();
}

/**
 * 3. Render The Challenge & Problems
 */
function webclyde_render_portfolio_challenge( $post_id = 0, $args = [] ) {
	$post_id = webclyde_get_portfolio_post_id( $post_id );

	$defaults = [
		'show_title_desc' => false, // Default false as requested: only show the problems card!
		'problems_title'  => __( 'THE MAIN PROBLEMS', 'webclyde' ),
	];
	$args = wp_parse_args( $args, $defaults );

	if ( empty( $args['show_title_desc'] ) ) {
		return webclyde_render_portfolio_problems( $post_id, [
			'title' => $args['problems_title'],
		] );
	}

	$title    = webclyde_get_portfolio_field( '_portfolio_challenge_title', $post_id ) ?: __( 'The Challenge', 'webclyde' );
	$desc     = webclyde_get_portfolio_field( '_portfolio_challenge_description', $post_id );
	$problems = webclyde_get_portfolio_field( '_portfolio_main_problems', $post_id );

	if ( ! is_array( $problems ) ) {
		$problems = [];
	}

	if ( ! $desc && empty( $problems ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="wc-port-section wc-port-challenge-section">
		<h2 class="wc-port-section-title"><?php echo esc_html( $title ); ?></h2>

		<?php if ( $desc ) : ?>
			<div class="wc-port-section-desc">
				<?php echo wp_kses_post( wpautop( $desc ) ); ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $problems ) ) : ?>
			<?php echo webclyde_render_portfolio_problems( $post_id, [ 'title' => $args['problems_title'] ] ); ?>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * 3b. Render The Main Problems Card (Exact matching widget)
 */
function webclyde_render_portfolio_problems( $post_id = 0, $args = [] ) {
	$post_id = webclyde_get_portfolio_post_id( $post_id );

	$defaults = [
		'title'       => __( 'THE MAIN PROBLEMS', 'webclyde' ),
		'title_tag'   => 'h3',
		'problems'    => null,
		'show_title'  => true,
		'extra_class' => '',
	];
	$args = wp_parse_args( $args, $defaults );

	if ( is_array( $args['problems'] ) && ! empty( $args['problems'] ) ) {
		$problems = $args['problems'];
	} else {
		$problems = webclyde_get_portfolio_field( '_portfolio_main_problems', $post_id );
		if ( ! is_array( $problems ) ) {
			$problems = [];
		}
	}

	// Clean list
	$problems = array_values( array_filter( array_map( 'trim', (array) $problems ) ) );

	// Preview / Editor Fallback with exact demo copy if post has no problems entered yet
	if ( empty( $problems ) ) {
		if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			$problems = [
				__( 'Pages took too long to load on mobile', 'webclyde' ),
				__( 'Checkout had too many steps and fields', 'webclyde' ),
				__( 'The design looked dated next to the brand’s products', 'webclyde' ),
			];
		} else {
			return '';
		}
	}

	$title_tag = in_array( strtolower( $args['title_tag'] ), [ 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p' ], true ) ? strtolower( $args['title_tag'] ) : 'h3';

	ob_start();
	?>
	<div class="wc-port-problems-card <?php echo esc_attr( $args['extra_class'] ); ?>">
		<?php if ( ! empty( $args['show_title'] ) && ! empty( $args['title'] ) ) : ?>
			<<?php echo esc_attr( $title_tag ); ?> class="wc-port-problems-title">
				<?php echo esc_html( $args['title'] ); ?>
			</<?php echo esc_attr( $title_tag ); ?>>
		<?php endif; ?>

		<div class="wc-port-problems-list">
			<?php foreach ( $problems as $index => $problem ) : ?>
				<div class="wc-port-problem-row wc-port-problem-item">
					<span class="wc-port-problem-badge wc-port-problem-num"><?php echo (int) ( $index + 1 ); ?></span>
					<span class="wc-port-problem-text"><?php echo esc_html( $problem ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * 4. Render Approach & Steps (Modern Cards Grid)
 */
function webclyde_render_portfolio_approach( $post_id = 0, $args = [] ) {
	$post_id = function_exists( 'webclyde_get_portfolio_post_id' ) ? webclyde_get_portfolio_post_id( $post_id ) : ( $post_id ?: get_the_ID() );

	$steps = [];

	if ( ! empty( $args['steps'] ) && is_array( $args['steps'] ) ) {
		$steps = $args['steps'];
	} elseif ( $post_id ) {
		$raw_steps = webclyde_get_portfolio_field( '_portfolio_approach_steps', $post_id );
		if ( is_array( $raw_steps ) ) {
			$steps = $raw_steps;
		}
	}

	$valid_steps = [];
	foreach ( $steps as $s ) {
		$t = trim( (string) ( $s['title'] ?? '' ) );
		$d = trim( (string) ( $s['desc'] ?? '' ) );
		if ( '' !== $t || '' !== $d ) {
			$valid_steps[] = [
				'title' => $t,
				'desc'  => $d,
			];
		}
	}

	// Elementor editor preview fallback: if no steps yet, display the exact 5 steps from user's design
	if ( empty( $valid_steps ) && class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
		$valid_steps = [
			[
				'title' => __( 'A custom theme, built for speed', 'webclyde' ),
				'desc'  => __( 'Instead of a heavy template with extra plugins, we built a lightweight custom theme with only the features the store needed. That kept the code clean and the pages quick to load.', 'webclyde' ),
			],
			[
				'title' => __( 'A shorter, simpler checkout', 'webclyde' ),
				'desc'  => __( 'We removed unnecessary fields, added guest checkout, and made the buttons easy to tap on a small screen, so customers can finish an order in fewer steps.', 'webclyde' ),
			],
			[
				'title' => __( 'Payments customers already trust', 'webclyde' ),
				'desc'  => __( 'We removed unnecessary fields, added guest checkout, and made the buttons easy to tap on a small screen, so customers can finish an order in fewer steps.', 'webclyde' ),
			],
			[
				'title' => __( 'Clear product pages', 'webclyde' ),
				'desc'  => __( 'Instead of a heavy template with extra plugins, we built a lightweight custom theme with only the features the store needed. That kept the code clean and the pages quick to load.', 'webclyde' ),
			],
			[
				'title' => __( 'Speed work behind the scenes', 'webclyde' ),
				'desc'  => __( 'We removed unnecessary fields, added guest checkout, and made the buttons easy to tap on a small screen, so customers can finish an order in fewer steps.', 'webclyde' ),
			],
		];
	}

	// Approach Grid Extra Badge / Image (For empty 6th slot)
	$badge_img_url = '';
	$badge_link    = '';

	if ( isset( $args['show_badge'] ) && false === $args['show_badge'] ) {
		// Explicitly disabled via widget setting
		$badge_img_url = '';
	} elseif ( ! empty( $args['badge_image_url'] ) ) {
		$badge_img_url = $args['badge_image_url'];
		$badge_link    = $args['badge_link'] ?? '';
	} elseif ( ! empty( $args['badge_image_id'] ) ) {
		$badge_img_url = wp_get_attachment_image_url( absint( $args['badge_image_id'] ), 'full' );
		$badge_link    = $args['badge_link'] ?? '';
	} elseif ( $post_id ) {
		$has_badge = webclyde_get_portfolio_field( '_portfolio_approach_has_badge', $post_id );
		$badge_id  = webclyde_get_portfolio_field( '_portfolio_approach_badge_image', $post_id );
		if ( empty( $badge_id ) && 'yes' === $has_badge ) {
			$badge_id = webclyde_get_portfolio_field( '_portfolio_approach_image', $post_id );
		}
		if ( 'no' !== $has_badge && ! empty( $badge_id ) ) {
			$badge_img_url = wp_get_attachment_image_url( absint( $badge_id ), 'full' );
			$badge_link    = webclyde_get_portfolio_field( '_portfolio_approach_badge_link', $post_id );
		}
	}

	if ( empty( $valid_steps ) && empty( $badge_img_url ) ) {
		return '';
	}

	$title_tag = ! empty( $args['title_tag'] ) ? tag_escape( $args['title_tag'] ) : 'h3';

	ob_start();
	?>
	<div class="wc-port-approach-grid">
		<?php foreach ( $valid_steps as $i => $step ) :
			$num = $i + 1;
			?>
			<div class="wc-port-approach-card">
				<div class="wc-port-approach-badge"><?php echo esc_html( (string) $num ); ?></div>
				<?php if ( ! empty( $step['title'] ) ) : ?>
					<<?php echo $title_tag; ?> class="wc-port-approach-title">
						<?php echo esc_html( $step['title'] ); ?>
					</<?php echo $title_tag; ?>>
				<?php endif; ?>
				<?php if ( ! empty( $step['desc'] ) ) : ?>
					<p class="wc-port-approach-desc"><?php echo wp_kses_post( $step['desc'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>

		<?php if ( ! empty( $badge_img_url ) ) : ?>
			<div class="wc-port-approach-grid-badge">
				<?php if ( ! empty( $badge_link ) ) : ?>
					<a href="<?php echo esc_url( $badge_link ); ?>" target="_blank" rel="noopener noreferrer" class="wc-port-approach-badge-link">
				<?php endif; ?>

				<img src="<?php echo esc_url( $badge_img_url ); ?>" alt="<?php esc_attr_e( 'Approach Badge', 'webclyde' ); ?>" class="wc-port-approach-badge-img">

				<?php if ( ! empty( $badge_link ) ) : ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * 5. Render Results & Metrics
 */
function webclyde_render_portfolio_results( $post_id = 0 ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$title     = webclyde_get_portfolio_field( '_portfolio_result_title', $post_id ) ?: __( 'The Results', 'webclyde' );
	$desc      = webclyde_get_portfolio_field( '_portfolio_result_description', $post_id );
	$speed     = webclyde_get_portfolio_field( '_portfolio_mobile_load_time', $post_id );
	$pagespeed = webclyde_get_portfolio_field( '_portfolio_pagespeed_score', $post_id );
	$checkouts = webclyde_get_portfolio_field( '_portfolio_completed_checkouts', $post_id );

	if ( ! $desc && ! $speed && ! $pagespeed && ! $checkouts ) {
		return '';
	}

	ob_start();
	?>
	<div class="wc-port-results-card">
		<h2 class="wc-port-section-title"><?php echo esc_html( $title ); ?></h2>

		<?php if ( $desc ) : ?>
			<div class="wc-port-section-desc">
				<?php echo wp_kses_post( wpautop( $desc ) ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $speed || $pagespeed || $checkouts ) : ?>
			<div class="wc-port-metrics-grid">
				<?php if ( $speed ) : ?>
					<div class="wc-port-metric-box">
						<div class="wc-port-metric-icon">&#9889;</div>
						<div class="wc-port-metric-value"><?php echo esc_html( $speed ); ?></div>
						<div class="wc-port-metric-label"><?php esc_html_e( 'Mobile Load Time', 'webclyde' ); ?></div>
					</div>
				<?php endif; ?>

				<?php if ( $pagespeed ) : ?>
					<div class="wc-port-metric-box">
						<div class="wc-port-metric-icon">&#128640;</div>
						<div class="wc-port-metric-value"><?php echo esc_html( $pagespeed ); ?></div>
						<div class="wc-port-metric-label"><?php esc_html_e( 'PageSpeed Score (Mobile)', 'webclyde' ); ?></div>
					</div>
				<?php endif; ?>

				<?php if ( $checkouts ) : ?>
					<div class="wc-port-metric-box">
						<div class="wc-port-metric-icon">&#128200;</div>
						<div class="wc-port-metric-value"><?php echo esc_html( $checkouts ); ?></div>
						<div class="wc-port-metric-label"><?php esc_html_e( 'Completed Checkouts', 'webclyde' ); ?></div>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * 6. Render Client Review & Testimonial
 */
function webclyde_render_portfolio_review( $post_id = 0 ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$quote       = webclyde_get_portfolio_field( '_portfolio_client_said', $post_id );
	$client_name = webclyde_get_portfolio_field( '_portfolio_client_name', $post_id );
	$designation = webclyde_get_portfolio_field( '_portfolio_client_designation', $post_id );
	$avatar_id   = webclyde_get_portfolio_field( '_portfolio_client_avatar', $post_id );
	$avatar_url  = $avatar_id ? wp_get_attachment_image_url( $avatar_id, 'thumbnail' ) : '';
	$review_id   = webclyde_get_portfolio_field( '_portfolio_review_image', $post_id );
	$review_url  = $review_id ? wp_get_attachment_image_url( $review_id, 'medium' ) : '';

	if ( ! $quote && ! $client_name ) {
		return '';
	}

	ob_start();
	?>
	<div class="wc-port-review-card">
		<div class="wc-port-quote-icon">&ldquo;</div>

		<?php if ( $quote ) : ?>
			<blockquote class="wc-port-client-quote">
				<?php echo wp_kses_post( wpautop( $quote ) ); ?>
			</blockquote>
		<?php endif; ?>

		<div class="wc-port-review-footer">
			<div class="wc-port-client-profile">
				<?php if ( $avatar_url ) : ?>
					<img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $client_name ); ?>" class="wc-port-client-avatar">
				<?php endif; ?>

				<div class="wc-port-client-info">
					<?php if ( $client_name ) : ?>
						<h4><?php echo esc_html( $client_name ); ?></h4>
					<?php endif; ?>
					<?php if ( $designation ) : ?>
						<p><?php echo esc_html( $designation ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( $review_url ) : ?>
				<div class="wc-port-review-proof">
					<img src="<?php echo esc_url( $review_url ); ?>" alt="Verified Client Review">
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * 7. Render Complete Case Study (All sections)
 */
function webclyde_render_portfolio_case_study( $post_id = 0 ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$output  = '<div class="wc-port-case-study">';
	$output .= webclyde_render_portfolio_meta( $post_id );
	$output .= webclyde_render_portfolio_carousel( $post_id );
	$output .= webclyde_render_portfolio_challenge( $post_id, [ 'show_title_desc' => true ] );
	$output .= webclyde_render_portfolio_approach( $post_id );
	$output .= webclyde_render_portfolio_results( $post_id );
	$output .= webclyde_render_portfolio_review( $post_id );
	$output .= '</div>';

	return $output;
}

/**
 * 8. Shortcodes Registration
 * Allows embedding individual portfolio sections or the complete case study
 * into any page, post, or template using WordPress shortcodes.
 */
add_shortcode( 'portfolio_case_study', function( $atts ) {
	$atts = shortcode_atts( [ 'id' => 0 ], $atts, 'portfolio_case_study' );
	return webclyde_render_portfolio_case_study( (int) $atts['id'] );
} );
add_shortcode( 'webclyde_portfolio', function( $atts ) {
	$atts = shortcode_atts( [ 'id' => 0 ], $atts, 'webclyde_portfolio' );
	return webclyde_render_portfolio_case_study( (int) $atts['id'] );
} );

add_shortcode( 'portfolio_meta', function( $atts ) {
	$atts = shortcode_atts( [
		'id'               => 0,
		'show_logo'        => true,
		'show_button'      => true,
		'button_text'      => '',
		'show_description' => true,
	], $atts, 'portfolio_meta' );
	return webclyde_render_portfolio_meta( (int) $atts['id'], $atts );
} );

add_shortcode( 'portfolio_carousel', function( $atts ) {
	$atts = shortcode_atts( [
		'id'     => 0,
		'layout' => 'slider',
	], $atts, 'portfolio_carousel' );
	return webclyde_render_portfolio_carousel( (int) $atts['id'], $atts );
} );

add_shortcode( 'portfolio_challenge', function( $atts ) {
	$atts = shortcode_atts( [
		'id'              => 0,
		'show_title_desc' => true,
		'problems_title'  => '',
	], $atts, 'portfolio_challenge' );
	return webclyde_render_portfolio_challenge( (int) $atts['id'], $atts );
} );

add_shortcode( 'portfolio_problems', function( $atts ) {
	$atts = shortcode_atts( [
		'id'         => 0,
		'title'      => __( 'THE MAIN PROBLEMS', 'webclyde' ),
		'title_tag'  => 'h3',
		'show_title' => true,
	], $atts, 'portfolio_problems' );
	return webclyde_render_portfolio_problems( (int) $atts['id'], $atts );
} );

add_shortcode( 'portfolio_approach', function( $atts ) {
	$atts = shortcode_atts( [
		'id'        => 0,
		'title_tag' => 'h3',
	], $atts, 'portfolio_approach' );
	return webclyde_render_portfolio_approach( (int) $atts['id'], $atts );
} );

add_shortcode( 'portfolio_results', function( $atts ) {
	$atts = shortcode_atts( [ 'id' => 0 ], $atts, 'portfolio_results' );
	return webclyde_render_portfolio_results( (int) $atts['id'] );
} );

add_shortcode( 'portfolio_review', function( $atts ) {
	$atts = shortcode_atts( [ 'id' => 0 ], $atts, 'portfolio_review' );
	return webclyde_render_portfolio_review( (int) $atts['id'] );
} );

