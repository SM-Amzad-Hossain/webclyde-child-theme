<?php
// Load parent + child styles
// The child style.css is where the tablet frame CSS lives, so it has to load too.
add_action('wp_enqueue_scripts', function () {

    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

    $child_css = get_stylesheet_directory() . '/style.css';

    wp_enqueue_style(
        'child-style',
        get_stylesheet_uri(),
        ['parent-style'],
        file_exists($child_css) ? filemtime($child_css) : wp_get_theme()->get('Version')
    );
});

// Portfolio case-study fields (admin) + front-end renderer.
require_once get_stylesheet_directory() . '/inc/portfolio-meta.php';
require_once get_stylesheet_directory() . '/inc/portfolio-frontend.php';

// Portfolio CPT + Taxonomies
add_action('init', function () {

    register_taxonomy('portfolio_category', 'portfolio', [
        'label'        => 'Categories',
        'hierarchical' => true,
        'public'       => true,
        'show_in_rest' => true,
    ]);

    register_taxonomy('portfolio_tag', 'portfolio', [
        'label'        => 'Tags',
        'hierarchical' => false,
        'public'       => true,
        'show_in_rest' => true,
    ]);

    register_post_type('portfolio', [
        'labels' => [
            'name'          => 'Portfolios',
            'singular_name' => 'Portfolio',
        ],
        'public'          => true,
        'show_in_rest'    => true,
        'has_archive'     => true,
        'rewrite'         => ['slug' => 'portfolio'],
        'menu_icon'       => 'dashicons-portfolio',
        'supports'        => [
            'title',
            'editor',
            'thumbnail',
            'excerpt',
            'custom-fields',
            'revisions',
            'author',
            'page-attributes'
        ],
        'taxonomies'      => ['portfolio_category', 'portfolio_tag'],
        'show_in_menu'    => true,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'capability_type' => 'post', // Full access like Posts
        'map_meta_cap'    => true,
    ]);

});


/* =========================================================================
 *  TABLET FRAME - ELEMENTOR WIDGET
 * ====================================================================== */
/**
 * Tablet Frame - Elementor widget (child theme version)
 * ------------------------------------------------------
 * Registers a "Tablet Frame" Elementor widget. Any image - most usefully the
 * post's Featured Image (via Dynamic Tags) - is placed inside a tablet mockup
 * with the correct perspective. Pure CSS, no JavaScript.
 *
 * Setup:
 *   1. Upload tablet-frame/assets/tablet-frame.webp to the child theme folder,
 *      or to the Media Library and paste its URL into TFW_FRAME_URL below.
 *   2. Append this file's code to the end of functions.php (drop the opening
 *      "<?php" tag), or keep the file and add to functions.php:
 *
 *          require_once get_stylesheet_directory() . '/tablet-frame-snippet.php';
 *
 *   3. Edit any Elementor template -> widget panel -> search "Tablet Frame".
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frame image URL.
 * Leave as-is to load it from the child theme: /tablet-frame/assets/tablet-frame.webp
 */
if ( ! defined( 'TFW_FRAME_URL' ) ) {
	define( 'TFW_FRAME_URL', 'https://YOUR-SITE.com/wp-content/uploads/tablet-frame.webp' );
}

if ( ! defined( 'TFW_VER' ) ) {
	define( 'TFW_VER', '1.0.0' );
}

/**
 * Resolve the frame image source (constant first, child theme folder as fallback).
 */
if ( ! function_exists( 'tfw_frame_src' ) ) {
	function tfw_frame_src() {

		// 1. URL set in TFW_FRAME_URL (Media Library or CDN).
		if ( TFW_FRAME_URL && false === strpos( TFW_FRAME_URL, 'YOUR-SITE.com' ) ) {
			return TFW_FRAME_URL;
		}

		// 2. File shipped inside the child theme.
		$relative = 'tablet-frame/assets/tablet-frame.webp';

		if ( file_exists( get_stylesheet_directory() . '/' . $relative ) ) {
			return trailingslashit( get_stylesheet_directory_uri() ) . $relative;
		}

		// 3. Nothing found - the widget falls back to a plain image.
		return '';
	}
}

/**
 * Warn admins when Elementor is not active (the site itself keeps working).
 */
if ( ! function_exists( 'tfw_no_elementor_notice' ) ) {
	function tfw_no_elementor_notice() {
		if ( ! current_user_can( 'manage_options' ) || did_action( 'elementor/loaded' ) ) {
			return;
		}

		echo '<div class="notice notice-warning"><p><strong>Tablet Frame:</strong> '
			. 'this widget requires Elementor to be installed and activated.</p></div>';
	}
}
add_action( 'admin_notices', 'tfw_no_elementor_notice' );

/**
 * Diagnostic: warns you if Elementor is active but the widget never registered.
 * (If you see this notice, the widget will not appear in the Elementor panel.)
 * Safe to delete once everything works.
 */
if ( ! function_exists( 'tfw_registration_check' ) ) {
	function tfw_registration_check() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! did_action( 'elementor/loaded' ) || class_exists( 'TFW_Tablet_Frame_Widget' ) ) {
			return;
		}

		echo '<div class="notice notice-error"><p><strong>Tablet Frame:</strong> '
			. 'the widget class was never registered. Check that this code sits inside functions.php '
			. '(not inside another function), then clear Elementor cache (Elementor &rarr; Tools &rarr; '
			. 'Regenerate CSS &amp; Data).</p></div>';
	}
}
add_action( 'admin_notices', 'tfw_registration_check' );

/**
 * Diagnostic: tells you when the frame image cannot be found, which is what
 * makes the mockup look broken (image without a tablet around it).
 */
if ( ! function_exists( 'tfw_frame_missing_notice' ) ) {
	function tfw_frame_missing_notice() {
		if ( ! current_user_can( 'manage_options' ) || '' !== tfw_frame_src() ) {
			return;
		}

		echo '<div class="notice notice-error"><p><strong>Tablet Frame:</strong> the frame image was not found. '
			. 'Upload <code>tablet-frame.webp</code> to <code>'
			. esc_html( get_stylesheet_directory() . '/tablet-frame/assets/' ) . '</code> '
			. 'or paste its URL into <code>TFW_FRAME_URL</code> at the top of functions.php.</p></div>';
	}
}
add_action( 'admin_notices', 'tfw_frame_missing_notice' );

/**
 * Load order note (this is what usually breaks Elementor widgets in themes):
 *
 * WordPress loads the active plugins first and fires "plugins_loaded" during
 * that step. Elementor fires its own "elementor/loaded" action from inside
 * "plugins_loaded" - i.e. BEFORE the child theme's functions.php is loaded.
 *
 * So code in functions.php must never wait for "elementor/loaded" (that action
 * has already fired and the callback would never run - the widget would simply
 * never appear in the panel). Instead we hook into "elementor/widgets/register",
 * which Elementor fires later, on "init".
 */
add_action( 'elementor/widgets/register', 'tfw_register_widget' );

/**
 * Register the widget with Elementor.
 *
 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widget manager.
 */
if ( ! function_exists( 'tfw_register_widget' ) ) {
	function tfw_register_widget( $widgets_manager ) {

		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}

		if ( ! class_exists( 'TFW_Tablet_Frame_Widget' ) ) {

			/**
			 * Tablet Frame widget.
			 */
			class TFW_Tablet_Frame_Widget extends \Elementor\Widget_Base {

				public function get_name() {
					return 'tfw_tablet_frame';
				}

				public function get_title() {
					return __( 'Frame', 'webclyde' );
				}

				public function get_icon() {
					return 'eicon-image-rollover';
				}

				public function get_categories() {
					return [ 'basic' ];
				}

				public function get_keywords() {
					return [ 'tablet', 'frame', 'mockup', 'device', 'featured', 'image' ];
				}

				public function get_style_depends() {
					return [ 'child-style' ];
				}

				protected function register_controls() {

					/* ---------------------------------------------- Content */
					$this->start_controls_section(
						'section_image',
						[ 'label' => __( 'Image', 'webclyde' ) ]
					);

					$this->add_control(
						'image',
						[
							'label'       => __( 'Screen image', 'webclyde' ),
							'type'        => \Elementor\Controls_Manager::MEDIA,
							'dynamic'     => [ 'active' => true ], // enables Dynamic Tags (Featured Image, ACF...)
							'default'     => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
							'description' => __( 'Click the Dynamic Tag icon on the right and pick "Featured Image" so every post shows its own image automatically.', 'webclyde' ),
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Image_Size::get_type(),
						[
							'name'    => 'image',
							'default' => 'full',
							'exclude' => [ 'custom' ],
						]
					);

					$this->add_control(
						'fit',
						[
							'label'   => __( 'Fit', 'webclyde' ),
							'type'    => \Elementor\Controls_Manager::SELECT,
							'default' => 'cover',
							'options' => [
								'cover'   => __( 'Cover - fill the screen (crop)', 'webclyde' ),
								'contain' => __( 'Contain - show the whole image', 'webclyde' ),
								'fill'    => __( 'Fill - stretch to fit', 'webclyde' ),
							],
						]
					);

					$this->add_control(
						'position',
						[
							'label'     => __( 'Image position', 'webclyde' ),
							'type'      => \Elementor\Controls_Manager::SELECT,
							'default'   => 'top center',
							'options'   => [
								'top left'      => __( 'Top Left', 'webclyde' ),
								'top center'    => __( 'Top Center', 'webclyde' ),
								'top right'     => __( 'Top Right', 'webclyde' ),
								'center left'   => __( 'Center Left', 'webclyde' ),
								'center center' => __( 'Center', 'webclyde' ),
								'center right'  => __( 'Center Right', 'webclyde' ),
								'bottom left'   => __( 'Bottom Left', 'webclyde' ),
								'bottom center' => __( 'Bottom Center', 'webclyde' ),
								'bottom right'  => __( 'Bottom Right', 'webclyde' ),
							],
							'condition' => [ 'fit!' => 'fill' ],
						]
					);

					$this->add_control(
						'show_frame',
						[
							'label'        => __( 'Show tablet frame', 'webclyde' ),
							'type'         => \Elementor\Controls_Manager::SWITCHER,
							'default'      => 'yes',
							'return_value' => 'yes',
						]
					);

					$this->end_controls_section();

					/* ---------------------------------------------- Frame */
					$this->start_controls_section(
						'section_frame',
						[
							'label'     => __( 'Frame', 'webclyde' ),
							'condition' => [ 'show_frame' => 'yes' ],
						]
					);

					$this->add_control(
						'frame_image',
						[
							'label'   => __( 'Frame image (transparent PNG / WebP)', 'webclyde' ),
							'type'    => \Elementor\Controls_Manager::MEDIA,
							'default' => [ 'url' => tfw_frame_src() ],
						]
					);

					$this->end_controls_section();

					/* ---------------------------------------------- Layout */
					$this->start_controls_section(
						'section_layout',
						[ 'label' => __( 'Layout', 'webclyde' ) ]
					);

					$this->add_responsive_control(
						'max_width',
						[
							'label'      => __( 'Max width', 'webclyde' ),
							'type'       => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px', '%' ],
							'range'      => [
								'px' => [ 'min' => 200, 'max' => 2000, 'step' => 10 ],
								'%'  => [ 'min' => 10, 'max' => 100 ],
							],
							'default'    => [ 'unit' => 'px', 'size' => 1100 ],
							'selectors'  => [ '{{WRAPPER}} .tfw' => 'max-width: {{SIZE}}{{UNIT}};' ],
						]
					);

					$this->add_responsive_control(
						'align',
						[
							'label'                => __( 'Alignment', 'webclyde' ),
							'type'                 => \Elementor\Controls_Manager::CHOOSE,
							'options'              => [
								'left'   => [ 'title' => __( 'Left', 'webclyde' ), 'icon' => 'eicon-text-align-left' ],
								'center' => [ 'title' => __( 'Center', 'webclyde' ), 'icon' => 'eicon-text-align-center' ],
								'right'  => [ 'title' => __( 'Right', 'webclyde' ), 'icon' => 'eicon-text-align-right' ],
							],
							'default'              => 'center',
							'selectors'            => [ '{{WRAPPER}} .tfw' => 'margin: {{VALUE}};' ],
							'selectors_dictionary' => [
								'left'   => '0 auto 0 0',
								'center' => '0 auto',
								'right'  => '0 0 0 auto',
							],
						]
					);

					$this->add_control(
						'link',
						[
							'label'   => __( 'Image link', 'webclyde' ),
							'type'    => \Elementor\Controls_Manager::URL,
							'dynamic' => [ 'active' => true ],
							'default' => [ 'url' => '' ],
							'options' => [ 'url', 'is_external', 'nofollow' ],
						]
					);

					$this->end_controls_section();
				}

				/**
				 * Resolve the image URL (works with Dynamic Tags as well).
				 *
				 * @return array [ url, attachment_id ]
				 */
				protected function get_image_url( $settings ) {
					$image = $settings['image'] ?? [];
					$id    = ! empty( $image['id'] ) ? (int) $image['id'] : 0;
					$url   = ! empty( $image['url'] ) ? $image['url'] : '';

					if ( $id ) {
						$size = ! empty( $settings['image_size'] ) ? $settings['image_size'] : 'full';
						$src  = wp_get_attachment_image_url( $id, $size );

						if ( $src ) {
							return [ $src, $id ];
						}
					}

					return [ $url, $id ];
				}

				/**
				 * Frontend output.
				 * The frame overlay is drawn above the screenshot, so the image can
				 * never spill outside the bezel.
				 */
				protected function render() {
					$settings = $this->get_settings_for_display();

					list( $src, $img_id ) = $this->get_image_url( $settings );

					if ( ! $src ) {
						return;
					}

					$fit   = ! empty( $settings['fit'] ) ? $settings['fit'] : 'cover';
					$pos   = ! empty( $settings['position'] ) ? $settings['position'] : 'top center';
					$alt   = $img_id ? trim( (string) get_post_meta( $img_id, '_wp_attachment_image_alt', true ) ) : '';
					$frame = ! empty( $settings['frame_image']['url'] ) ? $settings['frame_image']['url'] : tfw_frame_src();
					$show  = ! isset( $settings['show_frame'] ) || 'yes' === $settings['show_frame'];

					$shot_style = 'object-fit:' . esc_attr( $fit ) . ';';
					if ( 'fill' !== $fit ) {
						$shot_style .= 'object-position:' . esc_attr( $pos ) . ';';
					}

					$has_link = ! empty( $settings['link']['url'] );
					if ( $has_link ) {
						$this->add_link_attributes( 'tfw-link', $settings['link'] );
						$this->add_render_attribute( 'tfw-link', 'class', 'tfw-link' );
					}

					$this->add_render_attribute( 'tfw-wrapper', 'class', 'tfw' );

					// No frame image available: show a plain (undistorted) image
					// instead of a half-broken mockup.
					if ( ! $frame ) {
						$show = false;
						$this->add_render_attribute( 'tfw-wrapper', 'class', 'tfw-plain' );
					}

					?>
					<div <?php $this->print_render_attribute_string( 'tfw-wrapper' ); ?>>
						<img class="tfw-shot"
						     src="<?php echo esc_url( $src ); ?>"
						     alt="<?php echo esc_attr( $alt ); ?>"
						     style="<?php echo esc_attr( $shot_style ); ?>"
						     decoding="async" fetchpriority="high">
						<?php if ( $show ) : ?>
							<img class="tfw-frame"
							     src="<?php echo esc_url( $frame ); ?>"
							     alt="" aria-hidden="true" decoding="async">
						<?php endif; ?>
						<?php if ( $has_link ) : ?>
							<a <?php $this->print_render_attribute_string( 'tfw-link' ); ?>></a>
						<?php endif; ?>
					</div>
					<?php
				}
			}
		}

		$widgets_manager->register( new \TFW_Tablet_Frame_Widget() );
	}
}

/**
 * Keep the perspective distance in sync with the widget width.
 *
 * "perspective" has to be a length, and a percentage is not allowed there.
 * Container query units (cqw) are the CSS-only way to make it scale, but they
 * resolve against the wrong element if a theme/plugin overrides container-type,
 * and the image then drifts outside the frame. This tiny script sets the exact
 * pixel value instead - 207.962% of the widget width - so the alignment is
 * always correct. It does nothing when the CSS value is already right.
 */
if ( ! function_exists( 'tfw_perspective_script' ) ) {
	function tfw_perspective_script() {
		?>
		<script>
		( function () {
			var FIT = 2.07962; // 207.962% of the widget width

			function apply( el ) {
				var w = el.clientWidth;

				if ( w > 0 ) {
					el.style.setProperty( '--tfw-p', ( w * FIT ).toFixed( 2 ) + 'px' );
				}
			}

			function init() {
				var nodes = document.querySelectorAll( '.tfw' );

				Array.prototype.forEach.call( nodes, function ( el ) {
					apply( el );

					if ( window.ResizeObserver ) {
						new ResizeObserver( function () {
							apply( el );
						} ).observe( el );
					} else {
						window.addEventListener( 'resize', function () {
							apply( el );
						} );
					}
				} );
			}

			if ( 'loading' === document.readyState ) {
				document.addEventListener( 'DOMContentLoaded', init );
			} else {
				init();
			}
		} )();
		</script>
		<?php
	}
}
add_action( 'wp_footer', 'tfw_perspective_script', 99 );
