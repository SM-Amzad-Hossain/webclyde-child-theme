<?php
/**
 * Frame - Elementor Widget
 *
 * Registers a "Frame" Elementor widget.
 * Any image - most usefully the post's Featured Image (via Dynamic Tags) -
 * is placed inside a mockup with correct perspective. Pure CSS + sync script.
 *
 * @package Webclyde
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frame image URL.
 * Leave empty to load it from the theme folder: /frame/assets/frame.webp
 */
if ( ! defined( 'TFW_FRAME_URL' ) ) {
	define( 'TFW_FRAME_URL', '' );
}

if ( ! defined( 'TFW_VER' ) ) {
	define( 'TFW_VER', '1.0.0' );
}

/**
 * Enqueue Frame Dedicated Styles
 */
if ( ! function_exists( 'tfw_enqueue_assets' ) ) {
	function tfw_enqueue_assets() {
		$css_path = get_stylesheet_directory() . '/frame/assets/frame.css';
		$css_uri  = get_stylesheet_directory_uri() . '/frame/assets/frame.css';
		$ver      = file_exists( $css_path ) ? filemtime( $css_path ) : TFW_VER;

		wp_register_style( 'frame-style', $css_uri, [], $ver );
		wp_register_style( 'tablet-frame-style', $css_uri, [], $ver );
		wp_enqueue_style( 'frame-style' );
	}
	add_action( 'wp_enqueue_scripts', 'tfw_enqueue_assets' );
}

/**
 * Resolve the frame image source (constant first, theme folder as fallback).
 */
if ( ! function_exists( 'tfw_frame_src' ) ) {
	function tfw_frame_src() {

		// 1. URL set in TFW_FRAME_URL (Media Library or CDN).
		if ( TFW_FRAME_URL && false === strpos( TFW_FRAME_URL, 'YOUR-SITE.com' ) ) {
			return TFW_FRAME_URL;
		}

		// 2. File shipped inside the frame/assets folder.
		$relative = 'frame/assets/frame.webp';
		if ( file_exists( get_stylesheet_directory() . '/' . $relative ) ) {
			return trailingslashit( get_stylesheet_directory_uri() ) . $relative;
		}

		$relative_fallback = 'frame/assets/tablet-frame.webp';
		if ( file_exists( get_stylesheet_directory() . '/' . $relative_fallback ) ) {
			return trailingslashit( get_stylesheet_directory_uri() ) . $relative_fallback;
		}

		// 3. Fallback to uploads folder if present.
		if ( function_exists( 'wp_upload_dir' ) ) {
			$upload_dir = wp_upload_dir();
			if ( ! empty( $upload_dir['basedir'] ) && file_exists( $upload_dir['basedir'] . '/2026/09/tablet-mockup-1.webp' ) ) {
				return $upload_dir['baseurl'] . '/2026/09/tablet-mockup-1.webp';
			}
		}

		// 4. Nothing found - the widget falls back to a plain image.
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

		echo '<div class="notice notice-warning"><p><strong>Frame:</strong> '
			. 'this widget requires Elementor to be installed and activated.</p></div>';
	}
	add_action( 'admin_notices', 'tfw_no_elementor_notice' );
}

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

		if ( ! class_exists( 'TFW_Frame_Widget' ) ) {

			/**
			 * Frame widget class.
			 */
			class TFW_Frame_Widget extends \Elementor\Widget_Base {

				public function get_name() {
					return 'tfw_frame';
				}

				public function get_title() {
					return __( 'Frame', 'webclyde' );
				}

				public function get_icon() {
					return 'eicon-image-rollover';
				}

				public function get_categories() {
					return [ 'basic', 'webclyde-portfolio' ];
				}

				public function get_keywords() {
					return [ 'frame', 'tablet', 'mockup', 'device', 'featured', 'image' ];
				}

				public function get_style_depends() {
					return [ 'frame-style' ];
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
							'label'        => __( 'Show frame', 'webclyde' ),
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

		if ( ! class_exists( 'TFW_Tablet_Frame_Widget' ) ) {
			/**
			 * Backward compatibility alias: hidden from panel so only Frame is visible.
			 */
			class TFW_Tablet_Frame_Widget extends TFW_Frame_Widget {
				public function get_name() {
					return 'tfw_tablet_frame';
				}

				public function show_in_panel() {
					return false;
				}
			}
		}

		$widgets_manager->register( new \TFW_Frame_Widget() );
		$widgets_manager->register( new \TFW_Tablet_Frame_Widget() );
	}
	add_action( 'elementor/widgets/register', 'tfw_register_widget' );
}

/**
 * Keep the perspective distance in sync with the widget width.
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
	add_action( 'wp_footer', 'tfw_perspective_script', 99 );
}
