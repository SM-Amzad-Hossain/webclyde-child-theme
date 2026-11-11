<?php
/**
 * Elementor Widgets for Portfolio Case Study
 *
 * @package Webclyde
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Elementor Category
 */
add_action( 'elementor/elements/categories_registered', function( $elements_manager ) {
	$elements_manager->add_category(
		'webclyde-portfolio',
		[
			'title' => __( 'Portfolio Case Study', 'webclyde' ),
			'icon'  => 'eicon-folder',
		]
	);
} );

/**
 * Register Elementor Widgets
 */
add_action( 'elementor/widgets/register', function( $widgets_manager ) {

	if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
		return;
	}

	/* -------------------------------------------------------------------------
	 * 1. PORTFOLIO META & OVERVIEW WIDGET
	 * ---------------------------------------------------------------------- */
	class Webclyde_Portfolio_Meta_Widget extends \Elementor\Widget_Base {

		public function get_name() {
			return 'webclyde_portfolio_meta';
		}

		public function get_title() {
			return __( 'Portfolio: Overview & Meta', 'webclyde' );
		}

		public function get_icon() {
			return 'eicon-info-box';
		}

		public function get_categories() {
			return [ 'webclyde-portfolio' ];
		}

		public function show_in_panel() {
			return false; // Hidden from panel per user request
		}

		public function get_style_depends() {
			return [ 'webclyde-portfolio-frontend-css' ];
		}

		protected function register_controls() {
			$this->start_controls_section(
				'section_content',
				[ 'label' => __( 'Settings & Display', 'webclyde' ) ]
			);

			$this->add_control(
				'show_logo',
				[
					'label'        => __( 'Show Logo', 'webclyde' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'default'      => 'yes',
					'return_value' => 'yes',
				]
			);

			$this->add_control(
				'show_button',
				[
					'label'        => __( 'Show Site URL Button', 'webclyde' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'default'      => 'yes',
					'return_value' => 'yes',
				]
			);

			$this->add_control(
				'button_text',
				[
					'label'     => __( 'Button Text', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::TEXT,
					'default'   => __( 'Visit Website', 'webclyde' ),
					'condition' => [ 'show_button' => 'yes' ],
				]
			);

			$this->add_control(
				'show_description',
				[
					'label'        => __( 'Show Description', 'webclyde' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'default'      => 'yes',
					'return_value' => 'yes',
				]
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_style',
				[ 'label' => __( 'Style', 'webclyde' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
			);

			$this->add_control(
				'card_bg',
				[
					'label'     => __( 'Card Background', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => [ '{{WRAPPER}} .wc-port-meta-card' => 'background-color: {{VALUE}};' ],
				]
			);

			$this->add_control(
				'accent_color',
				[
					'label'     => __( 'Button & Accent Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => [ '{{WRAPPER}} .wc-port-site-btn' => 'background-color: {{VALUE}};' ],
				]
			);

			$this->end_controls_section();
		}

		protected function render() {
			$settings = $this->get_settings_for_display();
			echo webclyde_render_portfolio_meta( get_the_ID(), [
				'show_logo'        => 'yes' === $settings['show_logo'],
				'show_button'      => 'yes' === $settings['show_button'],
				'button_text'      => $settings['button_text'] ?: __( 'Visit Website', 'webclyde' ),
				'show_description' => 'yes' === $settings['show_description'],
			] );
		}
	}

	/* -------------------------------------------------------------------------
	 * 2. PORTFOLIO IMAGE CAROUSEL WIDGET (Smooth Autoplay + Live Preview & Styles)
	 * ---------------------------------------------------------------------- */
	class Webclyde_Portfolio_Carousel_Widget extends \Elementor\Widget_Base {

		public function get_name() {
			return 'webclyde_portfolio_carousel';
		}

		public function get_title() {
			return __( 'Portfolio: Image Carousel (Custom / Legacy)', 'webclyde' );
		}

		public function get_icon() {
			return 'eicon-slider-push';
		}

		public function get_categories() {
			return [ 'webclyde-portfolio' ];
		}

		public function show_in_panel() {
			return false; // Hidden from panel per user request
		}

		public function get_style_depends() {
			return [ 'webclyde-portfolio-frontend-css' ];
		}

		public function get_script_depends() {
			return [ 'webclyde-portfolio-frontend-js' ];
		}

		protected function register_controls() {

			// Content: Images
			$this->start_controls_section(
				'section_images',
				[ 'label' => __( 'Images (Default WordPress System)', 'webclyde' ) ]
			);

			$this->add_control(
				'gallery_source',
				[
					'label'   => __( 'Image Source', 'webclyde' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => 'cpt',
					'options' => [
						'cpt'    => __( 'Default: Portfolio Post (Case Study Details)', 'webclyde' ),
						'custom' => __( 'Custom: Add Images with WordPress Gallery', 'webclyde' ),
					],
				]
			);

			$this->add_control(
				'custom_gallery',
				[
					'label'       => __( 'Add Images (Default WordPress Gallery)', 'webclyde' ),
					'type'        => \Elementor\Controls_Manager::GALLERY,
					'default'     => [],
					'description' => __( 'Uses the native WordPress Media Library gallery system to add, upload, or reorder images.', 'webclyde' ),
					'condition'   => [ 'gallery_source' => 'custom' ],
				]
			);

			$this->end_controls_section();

			// Content: Autoplay & Carousel Settings
			$this->start_controls_section(
				'section_carousel_settings',
				[ 'label' => __( 'Smooth Autoplay & Navigation', 'webclyde' ) ]
			);

			$this->add_control(
				'autoplay',
				[
					'label'        => __( 'Auto Play', 'webclyde' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'default'      => 'yes',
					'return_value' => 'yes',
				]
			);

			$this->add_control(
				'autoplay_mode',
				[
					'label'   => __( 'Carousel Animation Style', 'webclyde' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => 'smooth_slides',
					'options' => [
						'smooth_slides' => __( 'Smooth Slide Carousel (Autoplay)', 'webclyde' ),
						'smooth_ticker' => __( 'Continuous Infinite Flow (Smooth Marquee)', 'webclyde' ),
						'grid'          => __( 'Static Multi-column Grid', 'webclyde' ),
					],
				]
			);

			$this->add_control(
				'slides_to_show',
				[
					'label'     => __( 'Slides Per View (Desktop)', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::SELECT,
					'default'   => '2',
					'options'   => [
						'1' => '1 Slide',
						'2' => '2 Slides',
						'3' => '3 Slides',
						'4' => '4 Slides',
						'5' => '5 Slides',
					],
					'condition' => [ 'autoplay_mode' => 'smooth_slides' ],
				]
			);

			$this->add_control(
				'slides_to_show_tablet',
				[
					'label'     => __( 'Slides Per View (Tablet)', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::SELECT,
					'default'   => '2',
					'options'   => [
						'1' => '1 Slide',
						'2' => '2 Slides',
						'3' => '3 Slides',
					],
					'condition' => [ 'autoplay_mode' => 'smooth_slides' ],
				]
			);

			$this->add_control(
				'slides_to_show_mobile',
				[
					'label'     => __( 'Slides Per View (Mobile)', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::SELECT,
					'default'   => '1',
					'options'   => [
						'1' => '1 Slide',
						'2' => '2 Slides',
					],
					'condition' => [ 'autoplay_mode' => 'smooth_slides' ],
				]
			);

			$this->add_control(
				'autoplay_speed',
				[
					'label'     => __( 'Autoplay Delay (ms)', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::NUMBER,
					'min'       => 1500,
					'max'       => 10000,
					'step'      => 250,
					'default'   => 3500,
					'condition' => [ 'autoplay' => 'yes', 'autoplay_mode' => 'smooth_slides' ],
				]
			);

			$this->add_control(
				'transition_speed',
				[
					'label'     => __( 'Transition Speed (ms)', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::NUMBER,
					'min'       => 300,
					'max'       => 3000,
					'step'      => 50,
					'default'   => 800,
					'condition' => [ 'autoplay_mode' => 'smooth_slides' ],
				]
			);

			$this->add_control(
				'pause_on_hover',
				[
					'label'        => __( 'Pause on Hover', 'webclyde' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'default'      => 'yes',
					'return_value' => 'yes',
					'condition'    => [ 'autoplay_mode!' => 'grid' ],
				]
			);

			$this->add_control(
				'show_arrows',
				[
					'label'        => __( 'Show Navigation Arrows', 'webclyde' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'default'      => 'yes',
					'return_value' => 'yes',
					'condition'    => [ 'autoplay_mode' => 'smooth_slides' ],
				]
			);

			$this->add_control(
				'show_dots',
				[
					'label'        => __( 'Show Pagination Dots', 'webclyde' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'default'      => 'yes',
					'return_value' => 'yes',
					'condition'    => [ 'autoplay_mode' => 'smooth_slides' ],
				]
			);

			$this->end_controls_section();

			// Style Section 1: Slides & Images
			$this->start_controls_section(
				'section_style_images',
				[ 'label' => __( 'Slides & Images', 'webclyde' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
			);

			$this->add_responsive_control(
				'image_height',
				[
					'label'      => __( 'Image Height', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'vh', '%' ],
					'range'      => [
						'px' => [ 'min' => 150, 'max' => 1000, 'step' => 10 ],
						'vh' => [ 'min' => 15, 'max' => 95 ],
					],
					'default'    => [ 'unit' => 'px', 'size' => 380 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-slide img, {{WRAPPER}} .wc-port-gallery-item img' => 'height: {{SIZE}}{{UNIT}} !important;',
					],
				]
			);

			$this->add_control(
				'image_fit',
				[
					'label'     => __( 'Image Fit', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::SELECT,
					'default'   => 'cover',
					'options'   => [
						'cover'   => __( 'Cover (Fill area, cropped)', 'webclyde' ),
						'contain' => __( 'Contain (Show entire image)', 'webclyde' ),
						'fill'    => __( 'Fill (Stretch to fit)', 'webclyde' ),
					],
					'selectors' => [
						'{{WRAPPER}} .wc-port-slide img, {{WRAPPER}} .wc-port-gallery-item img' => 'object-fit: {{VALUE}} !important;',
					],
				]
			);

			$this->add_responsive_control(
				'slide_gap',
				[
					'label'      => __( 'Slide Gap / Spacing', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
					'default'    => [ 'unit' => 'px', 'size' => 20 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-carousel-track, {{WRAPPER}} .wc-port-gallery-grid' => 'gap: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'slide_bg_color',
				[
					'label'     => __( 'Slide Background Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#ffffff',
					'selectors' => [
						'{{WRAPPER}} .wc-port-slide, {{WRAPPER}} .wc-port-gallery-item' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'slide_border_radius',
				[
					'label'      => __( 'Border Radius', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'default'    => [
						'top'      => '12',
						'right'    => '12',
						'bottom'   => '12',
						'left'     => '12',
						'unit'     => 'px',
						'isLinked' => true,
					],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-slide, {{WRAPPER}} .wc-port-gallery-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name'     => 'slide_border',
					'selector' => '{{WRAPPER}} .wc-port-slide, {{WRAPPER}} .wc-port-gallery-item',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name'     => 'slide_shadow',
					'selector' => '{{WRAPPER}} .wc-port-slide, {{WRAPPER}} .wc-port-gallery-item',
				]
			);

			$this->start_controls_tabs( 'tabs_slide_hover' );

			$this->start_controls_tab(
				'tab_slide_normal',
				[ 'label' => __( 'Normal', 'webclyde' ) ]
			);
			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_slide_hover',
				[ 'label' => __( 'Hover', 'webclyde' ) ]
			);

			$this->add_control(
				'slide_hover_lift',
				[
					'label'      => __( 'Hover Lift (px)', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 0, 'max' => 20 ] ],
					'default'    => [ 'unit' => 'px', 'size' => 4 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-slide:hover, {{WRAPPER}} .wc-port-gallery-item:hover' => 'transform: translateY(-{{SIZE}}px);',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name'     => 'slide_hover_shadow',
					'selector' => '{{WRAPPER}} .wc-port-slide:hover, {{WRAPPER}} .wc-port-gallery-item:hover',
				]
			);

			$this->end_controls_tab();
			$this->end_controls_tabs();

			$this->end_controls_section();

			// Style Section 2: Navigation Arrows
			$this->start_controls_section(
				'section_style_arrows',
				[
					'label'     => __( 'Navigation Arrows', 'webclyde' ),
					'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
					'condition' => [ 'show_arrows' => 'yes', 'autoplay_mode' => 'smooth_slides' ],
				]
			);

			$this->add_responsive_control(
				'arrow_size',
				[
					'label'      => __( 'Arrow Button Size', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 28, 'max' => 80 ] ],
					'default'    => [ 'unit' => 'px', 'size' => 44 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-arrow' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'arrow_icon_size',
				[
					'label'      => __( 'Icon Size', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 14, 'max' => 50 ] ],
					'default'    => [ 'unit' => 'px', 'size' => 26 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-arrow' => 'font-size: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'arrow_offset',
				[
					'label'      => __( 'Side Offset', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => -50, 'max' => 50 ] ],
					'default'    => [ 'unit' => 'px', 'size' => -18 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-arrow-prev' => 'left: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .wc-port-arrow-next' => 'right: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->start_controls_tabs( 'tabs_arrows_style' );

			$this->start_controls_tab(
				'tab_arrows_normal',
				[ 'label' => __( 'Normal', 'webclyde' ) ]
			);

			$this->add_control(
				'arrow_color',
				[
					'label'     => __( 'Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#0f172a',
					'selectors' => [
						'{{WRAPPER}} .wc-port-arrow' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'arrow_bg_color',
				[
					'label'     => __( 'Background Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => 'rgba(255, 255, 255, 0.95)',
					'selectors' => [
						'{{WRAPPER}} .wc-port-arrow' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'arrow_border_color',
				[
					'label'     => __( 'Border Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#e2e8f0',
					'selectors' => [
						'{{WRAPPER}} .wc-port-arrow' => 'border-color: {{VALUE}};',
					],
				]
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_arrows_hover',
				[ 'label' => __( 'Hover', 'webclyde' ) ]
			);

			$this->add_control(
				'arrow_hover_color',
				[
					'label'     => __( 'Hover Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#ffffff',
					'selectors' => [
						'{{WRAPPER}} .wc-port-arrow:hover' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'arrow_hover_bg_color',
				[
					'label'     => __( 'Hover Background Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#2563eb',
					'selectors' => [
						'{{WRAPPER}} .wc-port-arrow:hover' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'arrow_hover_border_color',
				[
					'label'     => __( 'Hover Border Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#2563eb',
					'selectors' => [
						'{{WRAPPER}} .wc-port-arrow:hover' => 'border-color: {{VALUE}};',
					],
				]
			);

			$this->end_controls_tab();
			$this->end_controls_tabs();

			$this->add_responsive_control(
				'arrow_border_radius',
				[
					'label'      => __( 'Border Radius', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'default'    => [
						'top'      => '50',
						'right'    => '50',
						'bottom'   => '50',
						'left'     => '50',
						'unit'     => '%',
						'isLinked' => true,
					],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-arrow' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name'     => 'arrow_shadow',
					'selector' => '{{WRAPPER}} .wc-port-arrow',
				]
			);

			$this->end_controls_section();

			// Style Section 3: Pagination Dots
			$this->start_controls_section(
				'section_style_dots',
				[
					'label'     => __( 'Pagination Dots', 'webclyde' ),
					'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
					'condition' => [ 'show_dots' => 'yes', 'autoplay_mode' => 'smooth_slides' ],
				]
			);

			$this->add_responsive_control(
				'dot_width',
				[
					'label'      => __( 'Dot Width', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 4, 'max' => 30 ] ],
					'default'    => [ 'unit' => 'px', 'size' => 10 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-dot' => 'width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'dot_height',
				[
					'label'      => __( 'Dot Height', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 4, 'max' => 30 ] ],
					'default'    => [ 'unit' => 'px', 'size' => 10 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-dot' => 'height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'active_dot_width',
				[
					'label'      => __( 'Active Dot Width', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 10, 'max' => 60 ] ],
					'default'    => [ 'unit' => 'px', 'size' => 28 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-dot.active' => 'width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'dot_color',
				[
					'label'     => __( 'Dot Inactive Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#cbd5e1',
					'selectors' => [
						'{{WRAPPER}} .wc-port-dot' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'active_dot_color',
				[
					'label'     => __( 'Active Dot Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#2563eb',
					'selectors' => [
						'{{WRAPPER}} .wc-port-dot.active' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'dots_top_spacing',
				[
					'label'      => __( 'Top Spacing', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
					'default'    => [ 'unit' => 'px', 'size' => 20 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-dots' => 'margin-top: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'dots_gap',
				[
					'label'      => __( 'Gap between Dots', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 4, 'max' => 30 ] ],
					'default'    => [ 'unit' => 'px', 'size' => 8 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-dots' => 'gap: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->end_controls_section();

			// Style Section 4: Wrapper Container Box
			$this->start_controls_section(
				'section_style_container',
				[ 'label' => __( 'Container Box', 'webclyde' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
			);

			$this->add_control(
				'container_bg',
				[
					'label'     => __( 'Background Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .wc-port-carousel-wrap' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'container_padding',
				[
					'label'      => __( 'Padding', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-carousel-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'container_margin',
				[
					'label'      => __( 'Margin', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-carousel-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name'     => 'container_border',
					'selector' => '{{WRAPPER}} .wc-port-carousel-wrap',
				]
			);

			$this->add_responsive_control(
				'container_radius',
				[
					'label'      => __( 'Border Radius', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-carousel-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->end_controls_section();
		}

		protected function render() {
			$settings = $this->get_settings_for_display();
			$post_id  = function_exists( 'webclyde_get_portfolio_post_id' ) ? webclyde_get_portfolio_post_id( get_the_ID() ) : get_the_ID();

			$args = [
				'layout'                => ( 'grid' === $settings['autoplay_mode'] ) ? 'grid' : 'slider',
				'autoplay'              => $settings['autoplay'] ?? 'yes',
				'autoplay_mode'         => $settings['autoplay_mode'] ?? 'smooth_slides',
				'speed'                 => ! empty( $settings['autoplay_speed'] ) ? (int) $settings['autoplay_speed'] : 3500,
				'transition_speed'      => ! empty( $settings['transition_speed'] ) ? (int) $settings['transition_speed'] : 800,
				'pause_on_hover'        => $settings['pause_on_hover'] ?? 'yes',
				'slides_to_show'        => ! empty( $settings['slides_to_show'] ) ? (int) $settings['slides_to_show'] : 2,
				'slides_to_show_tablet' => ! empty( $settings['slides_to_show_tablet'] ) ? (int) $settings['slides_to_show_tablet'] : 2,
				'slides_to_show_mobile' => ! empty( $settings['slides_to_show_mobile'] ) ? (int) $settings['slides_to_show_mobile'] : 1,
				'slide_gap'             => isset( $settings['slide_gap']['size'] ) ? (int) $settings['slide_gap']['size'] : 20,
				'show_arrows'           => 'yes' === $settings['show_arrows'],
				'show_dots'             => 'yes' === $settings['show_dots'],
				'image_fit'             => $settings['image_fit'] ?? 'cover',
				'image_height'          => ! empty( $settings['image_height']['size'] ) ? (int) $settings['image_height']['size'] : 380,
			];

			// Custom images from default WordPress gallery picker in Elementor
			if ( 'custom' === ( $settings['gallery_source'] ?? 'cpt' ) && ! empty( $settings['custom_gallery'] ) ) {
				$custom_ids = wp_list_pluck( $settings['custom_gallery'], 'id' );
				if ( ! empty( $custom_ids ) ) {
					$args['images'] = $custom_ids;
				}
			}

			echo webclyde_render_portfolio_carousel( $post_id, $args );
		}
	}

	/* -------------------------------------------------------------------------
	 * 3. PORTFOLIO CHALLENGE WIDGET
	 * ---------------------------------------------------------------------- */
	/* -------------------------------------------------------------------------
	 * 3. PORTFOLIO CHALLENGE & PROBLEMS WIDGET
	 * ---------------------------------------------------------------------- */
	class Webclyde_Portfolio_Challenge_Widget extends \Elementor\Widget_Base {

		public function get_name() {
			return 'webclyde_portfolio_challenge';
		}

		public function get_title() {
			return __( 'Portfolio: The Main Problems', 'webclyde' );
		}

		public function get_icon() {
			return 'eicon-editor-list-ol';
		}

		public function get_categories() {
			return [ 'webclyde-portfolio' ];
		}

		public function show_in_panel() {
			return false; // Hidden from panel to avoid duplicate with Problems widget
		}

		public function get_keywords() {
			return [ 'problem', 'problems', 'main problems', 'challenge', 'list', 'portfolio' ];
		}

		public function get_style_depends() {
			return [ 'webclyde-portfolio-frontend-css' ];
		}

		protected function register_controls() {

			/* --- Content Section --- */
			$this->start_controls_section(
				'section_content',
				[ 'label' => __( 'Problems Content', 'webclyde' ) ]
			);

			$this->add_control(
				'problems_title',
				[
					'label'   => __( 'Card Title', 'webclyde' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => __( 'THE MAIN PROBLEMS', 'webclyde' ),
					'dynamic' => [ 'active' => true ],
				]
			);

			$this->add_control(
				'show_challenge_text',
				[
					'label'        => __( 'Show Challenge Title & Description', 'webclyde' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => __( 'Show', 'webclyde' ),
					'label_off'    => __( 'Hide', 'webclyde' ),
					'return_value' => 'yes',
					'default'      => '', // Hidden by default: only show problems card!
					'description'  => __( 'Keep off to display only the dynamic problems card.', 'webclyde' ),
				]
			);

			$this->end_controls_section();

			/* --- Style Section: Card Box --- */
			$this->start_controls_section(
				'section_style_card',
				[ 'label' => __( 'Card Box', 'webclyde' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
			);

			$this->add_control(
				'card_bg',
				[
					'label'     => __( 'Background Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#ffffff',
					'selectors' => [
						'{{WRAPPER}} .wc-port-problems-card' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name'     => 'card_border',
					'selector' => '{{WRAPPER}} .wc-port-problems-card',
				]
			);

			$this->add_responsive_control(
				'card_radius',
				[
					'label'      => __( 'Border Radius', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'default'    => [
						'top'      => '16',
						'right'    => '16',
						'bottom'   => '16',
						'left'     => '16',
						'unit'     => 'px',
						'isLinked' => true,
					],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-problems-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name'     => 'card_shadow',
					'selector' => '{{WRAPPER}} .wc-port-problems-card',
				]
			);

			$this->add_responsive_control(
				'card_padding',
				[
					'label'      => __( 'Padding', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'default'    => [
						'top'      => '36',
						'right'    => '32',
						'bottom'   => '32',
						'left'     => '32',
						'unit'     => 'px',
						'isLinked' => false,
					],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-problems-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->end_controls_section();

			/* --- Style Section: Badges --- */
			$this->start_controls_section(
				'section_style_badge',
				[ 'label' => __( 'Number Badge', 'webclyde' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
			);

			$this->add_control(
				'badge_bg_color',
				[
					'label'     => __( 'Badge Background Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#ffe6e7',
					'selectors' => [
						'{{WRAPPER}} .wc-port-problem-badge' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'badge_number_color',
				[
					'label'     => __( 'Number Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#d72736',
					'selectors' => [
						'{{WRAPPER}} .wc-port-problem-badge' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'badge_size',
				[
					'label'      => __( 'Badge Size', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 20, 'max' => 60 ] ],
					'default'    => [ 'unit' => 'px', 'size' => 30 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-problem-badge' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}}; max-width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->end_controls_section();

			/* --- Style Section: Items Text --- */
			$this->start_controls_section(
				'section_style_text',
				[ 'label' => __( 'Problem Text', 'webclyde' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
			);

			$this->add_control(
				'item_text_color',
				[
					'label'     => __( 'Text Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#25252d',
					'selectors' => [
						'{{WRAPPER}} .wc-port-problem-text' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name'     => 'item_typography',
					'selector' => '{{WRAPPER}} .wc-port-problem-text',
				]
			);

			$this->add_responsive_control(
				'row_gap',
				[
					'label'      => __( 'Row Spacing (Gap)', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 8, 'max' => 60 ] ],
					'default'    => [ 'unit' => 'px', 'size' => 22 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-problems-list' => 'gap: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->end_controls_section();
		}

		protected function render() {
			$settings = $this->get_settings_for_display();
			$post_id  = function_exists( 'webclyde_get_portfolio_post_id' ) ? webclyde_get_portfolio_post_id( get_the_ID() ) : get_the_ID();

			$show_title_desc = ! empty( $settings['show_challenge_text'] ) && 'yes' === $settings['show_challenge_text'];
			$title           = ! empty( $settings['problems_title'] ) ? $settings['problems_title'] : __( 'THE MAIN PROBLEMS', 'webclyde' );

			if ( $show_title_desc ) {
				echo webclyde_render_portfolio_challenge( $post_id, [
					'show_title_desc' => true,
					'problems_title'  => $title,
				] );
			} else {
				// ONLY render the dynamic problems card
				$html = webclyde_render_portfolio_problems( $post_id, [
					'title' => $title,
				] );

				if ( ! $html && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
					// Preview placeholder in editor if no problems added yet
					echo webclyde_render_portfolio_problems( 0, [
						'title' => $title,
					] );
					return;
				}

				echo $html;
			}
		}
	}

	/* -------------------------------------------------------------------------
	 * 3b. PORTFOLIO PROBLEMS WIDGET (THE MAIN PROBLEMS)
	 * ---------------------------------------------------------------------- */
	class Webclyde_Portfolio_Problems_Widget extends \Elementor\Widget_Base {

		public function get_name() {
			return 'webclyde_portfolio_problems';
		}

		public function get_title() {
			return __( 'Portfolio: The Main Problems', 'webclyde' );
		}

		public function get_icon() {
			return 'eicon-editor-list-ol';
		}

		public function get_categories() {
			return [ 'webclyde-portfolio' ];
		}

		public function get_keywords() {
			return [ 'problem', 'problems', 'main problems', 'challenge', 'list', 'portfolio', 'webclyde' ];
		}

		public function get_style_depends() {
			return [ 'webclyde-portfolio-frontend-css' ];
		}

		protected function register_controls() {

			/* --- Content Section --- */
			$this->start_controls_section(
				'section_content',
				[ 'label' => __( 'Problems Content', 'webclyde' ) ]
			);

			$this->add_control(
				'title',
				[
					'label'   => __( 'Card Title', 'webclyde' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => __( 'THE MAIN PROBLEMS', 'webclyde' ),
					'dynamic' => [ 'active' => true ],
				]
			);

			$this->add_control(
				'title_tag',
				[
					'label'   => __( 'Title HTML Tag', 'webclyde' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => 'h3',
					'options' => [
						'h2'   => 'H2',
						'h3'   => 'H3',
						'h4'   => 'H4',
						'div'  => 'div',
						'span' => 'span',
						'p'    => 'p',
					],
				]
			);

			$this->add_control(
				'source',
				[
					'label'   => __( 'Data Source', 'webclyde' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => 'dynamic',
					'options' => [
						'dynamic' => __( 'Dynamic (From Post Meta)', 'webclyde' ),
						'custom'  => __( 'Custom Repeater List', 'webclyde' ),
					],
				]
			);

			$repeater = new \Elementor\Repeater();
			$repeater->add_control(
				'problem_text',
				[
					'label'       => __( 'Problem Text', 'webclyde' ),
					'type'        => \Elementor\Controls_Manager::TEXTAREA,
					'rows'        => 2,
					'default'     => __( 'Sample Problem Description', 'webclyde' ),
					'label_block' => true,
					'dynamic'     => [ 'active' => true ],
				]
			);

			$this->add_control(
				'custom_problems',
				[
					'label'       => __( 'Custom Problems List', 'webclyde' ),
					'type'        => \Elementor\Controls_Manager::REPEATER,
					'fields'      => $repeater->get_controls(),
					'default'     => [
						[ 'problem_text' => __( 'Pages took too long to load on mobile', 'webclyde' ) ],
						[ 'problem_text' => __( 'Checkout had too many steps and fields', 'webclyde' ) ],
						[ 'problem_text' => __( 'The design looked dated next to the brand’s products', 'webclyde' ) ],
					],
					'title_field' => '{{{ problem_text }}}',
					'condition'   => [ 'source' => 'custom' ],
				]
			);

			$this->end_controls_section();

			/* --- Style Section: Card --- */
			$this->start_controls_section(
				'section_style_card',
				[ 'label' => __( 'Card Box', 'webclyde' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
			);

			$this->add_control(
				'card_bg',
				[
					'label'     => __( 'Background Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#ffffff',
					'selectors' => [
						'{{WRAPPER}} .wc-port-problems-card' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name'     => 'card_border',
					'selector' => '{{WRAPPER}} .wc-port-problems-card',
				]
			);

			$this->add_responsive_control(
				'card_radius',
				[
					'label'      => __( 'Border Radius', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'default'    => [
						'top'      => '16',
						'right'    => '16',
						'bottom'   => '16',
						'left'     => '16',
						'unit'     => 'px',
						'isLinked' => true,
					],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-problems-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name'     => 'card_shadow',
					'selector' => '{{WRAPPER}} .wc-port-problems-card',
				]
			);

			$this->add_responsive_control(
				'card_padding',
				[
					'label'      => __( 'Padding', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'default'    => [
						'top'      => '36',
						'right'    => '32',
						'bottom'   => '32',
						'left'     => '32',
						'unit'     => 'px',
						'isLinked' => false,
					],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-problems-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->end_controls_section();

			/* --- Style Section: Title --- */
			$this->start_controls_section(
				'section_style_title',
				[ 'label' => __( 'Title', 'webclyde' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
			);

			$this->add_control(
				'title_color',
				[
					'label'     => __( 'Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#25252d',
					'selectors' => [
						'{{WRAPPER}} .wc-port-problems-title' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name'     => 'title_typography',
					'selector' => '{{WRAPPER}} .wc-port-problems-title',
				]
			);

			$this->add_responsive_control(
				'title_spacing',
				[
					'label'      => __( 'Bottom Spacing', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'em' ],
					'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
					'default'    => [ 'unit' => 'px', 'size' => 28 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-problems-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->end_controls_section();

			/* --- Style Section: Badges --- */
			$this->start_controls_section(
				'section_style_badge',
				[ 'label' => __( 'Number Badge', 'webclyde' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
			);

			$this->add_control(
				'badge_bg_color',
				[
					'label'     => __( 'Badge Background Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#ffe6e7',
					'selectors' => [
						'{{WRAPPER}} .wc-port-problem-badge' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'badge_text_color',
				[
					'label'     => __( 'Number Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#d72736',
					'selectors' => [
						'{{WRAPPER}} .wc-port-problem-badge' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'badge_size',
				[
					'label'      => __( 'Badge Size', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 20, 'max' => 60 ] ],
					'default'    => [ 'unit' => 'px', 'size' => 30 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-problem-badge' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}}; max-width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'badge_font_size',
				[
					'label'      => __( 'Number Font Size', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 10, 'max' => 30 ] ],
					'default'    => [ 'unit' => 'px', 'size' => 14 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-problem-badge' => 'font-size: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'badge_gap',
				[
					'label'      => __( 'Gap to Text', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 6, 'max' => 50 ] ],
					'default'    => [ 'unit' => 'px', 'size' => 16 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-problem-row' => 'gap: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->end_controls_section();

			/* --- Style Section: Items Text & Row Spacing --- */
			$this->start_controls_section(
				'section_style_text',
				[ 'label' => __( 'Problem Text & Rows', 'webclyde' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
			);

			$this->add_control(
				'item_text_color',
				[
					'label'     => __( 'Text Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#25252d',
					'selectors' => [
						'{{WRAPPER}} .wc-port-problem-text' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name'     => 'item_typography',
					'selector' => '{{WRAPPER}} .wc-port-problem-text',
				]
			);

			$this->add_responsive_control(
				'row_gap',
				[
					'label'      => __( 'Row Spacing (Gap)', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 8, 'max' => 60 ] ],
					'default'    => [ 'unit' => 'px', 'size' => 22 ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-problems-list' => 'gap: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->end_controls_section();
		}

		protected function render() {
			$settings = $this->get_settings_for_display();

			$custom_problems = [];
			if ( 'custom' === $settings['source'] && ! empty( $settings['custom_problems'] ) ) {
				foreach ( $settings['custom_problems'] as $item ) {
					if ( ! empty( $item['problem_text'] ) ) {
						$custom_problems[] = $item['problem_text'];
					}
				}
			}

			echo webclyde_render_portfolio_problems(
				get_the_ID(),
				[
					'title'     => ! empty( $settings['title'] ) ? $settings['title'] : __( 'THE MAIN PROBLEMS', 'webclyde' ),
					'title_tag' => ! empty( $settings['title_tag'] ) ? $settings['title_tag'] : 'h3',
					'problems'  => 'custom' === $settings['source'] ? $custom_problems : null,
				]
			);
		}
	}

	/* -------------------------------------------------------------------------
	 * 4. PORTFOLIO APPROACH WIDGET (MODERN CARDS GRID)
	 * ---------------------------------------------------------------------- */
	class Webclyde_Portfolio_Approach_Widget extends \Elementor\Widget_Base {

		public function get_name() {
			return 'webclyde_portfolio_approach';
		}

		public function get_title() {
			return __( 'Portfolio: Approach & Steps', 'webclyde' );
		}

		public function get_icon() {
			return 'eicon-flow';
		}

		public function get_categories() {
			return [ 'webclyde-portfolio' ];
		}

		public function get_keywords() {
			return [ 'approach', 'steps', 'process', 'cards', 'grid', 'portfolio', 'webclyde' ];
		}

		public function get_style_depends() {
			return [ 'webclyde-portfolio-frontend-css' ];
		}

		protected function register_controls() {

			/* --- Content Section --- */
			$this->start_controls_section(
				'section_content',
				[ 'label' => __( 'Approach Content', 'webclyde' ) ]
			);

			$this->add_control(
				'source',
				[
					'label'   => __( 'Data Source', 'webclyde' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => 'dynamic',
					'options' => [
						'dynamic' => __( 'Dynamic (From Post Meta)', 'webclyde' ),
						'custom'  => __( 'Custom Repeater Cards', 'webclyde' ),
					],
				]
			);

			$this->add_responsive_control(
				'columns',
				[
					'label'           => __( 'Columns', 'webclyde' ),
					'type'            => \Elementor\Controls_Manager::SELECT,
					'default'         => '3',
					'tablet_default'  => '2',
					'mobile_default'  => '1',
					'options'         => [
						'1' => '1 Column',
						'2' => '2 Columns',
						'3' => '3 Columns',
						'4' => '4 Columns',
					],
					'selectors'       => [
						'{{WRAPPER}} .wc-port-approach-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
					],
				]
			);

			$this->add_control(
				'title_tag',
				[
					'label'   => __( 'Title HTML Tag', 'webclyde' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => 'h3',
					'options' => [
						'h2'   => 'H2',
						'h3'   => 'H3',
						'h4'   => 'H4',
						'h5'   => 'H5',
						'div'  => 'div',
						'span' => 'span',
						'p'    => 'p',
					],
				]
			);

			$repeater = new \Elementor\Repeater();

			$repeater->add_control(
				'step_title',
				[
					'label'       => __( 'Step Title', 'webclyde' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'default'     => __( 'A custom theme, built for speed', 'webclyde' ),
					'label_block' => true,
					'dynamic'     => [ 'active' => true ],
				]
			);

			$repeater->add_control(
				'step_desc',
				[
					'label'       => __( 'Step Description', 'webclyde' ),
					'type'        => \Elementor\Controls_Manager::TEXTAREA,
					'rows'        => 3,
					'default'     => __( 'Instead of a heavy template with extra plugins, we built a lightweight custom theme with only the features the store needed.', 'webclyde' ),
					'label_block' => true,
					'dynamic'     => [ 'active' => true ],
				]
			);

			$this->add_control(
				'custom_steps',
				[
					'label'       => __( 'Custom Steps Cards', 'webclyde' ),
					'type'        => \Elementor\Controls_Manager::REPEATER,
					'fields'      => $repeater->get_controls(),
					'title_field' => '{{{ step_title }}}',
					'default'     => [
						[
							'step_title' => __( 'A custom theme, built for speed', 'webclyde' ),
							'step_desc'  => __( 'Instead of a heavy template with extra plugins, we built a lightweight custom theme with only the features the store needed. That kept the code clean and the pages quick to load.', 'webclyde' ),
						],
						[
							'step_title' => __( 'A shorter, simpler checkout', 'webclyde' ),
							'step_desc'  => __( 'We removed unnecessary fields, added guest checkout, and made the buttons easy to tap on a small screen, so customers can finish an order in fewer steps.', 'webclyde' ),
						],
						[
							'step_title' => __( 'Payments customers already trust', 'webclyde' ),
							'step_desc'  => __( 'We removed unnecessary fields, added guest checkout, and made the buttons easy to tap on a small screen, so customers can finish an order in fewer steps.', 'webclyde' ),
						],
						[
							'step_title' => __( 'Clear product pages', 'webclyde' ),
							'step_desc'  => __( 'Instead of a heavy template with extra plugins, we built a lightweight custom theme with only the features the store needed. That kept the code clean and the pages quick to load.', 'webclyde' ),
						],
						[
							'step_title' => __( 'Speed work behind the scenes', 'webclyde' ),
							'step_desc'  => __( 'We removed unnecessary fields, added guest checkout, and made the buttons easy to tap on a small screen, so customers can finish an order in fewer steps.', 'webclyde' ),
						],
					],
					'condition'   => [ 'source' => 'custom' ],
				]
			);

			$this->end_controls_section();

			/* --- Section: Grid Extra Badge / Image --- */
			$this->start_controls_section(
				'section_grid_badge',
				[ 'label' => __( 'Grid Empty Slot Badge / Image', 'webclyde' ) ]
			);

			$this->add_control(
				'show_grid_badge',
				[
					'label'        => __( 'Display Badge / Image in Grid', 'webclyde' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => __( 'Yes', 'webclyde' ),
					'label_off'    => __( 'No', 'webclyde' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				]
			);

			$this->add_control(
				'badge_source',
				[
					'label'     => __( 'Badge Source', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::SELECT,
					'default'   => 'dynamic',
					'options'   => [
						'dynamic' => __( 'Dynamic (From Portfolio Post Meta)', 'webclyde' ),
						'custom'  => __( 'Custom Image', 'webclyde' ),
					],
					'condition' => [ 'show_grid_badge' => 'yes' ],
				]
			);

			$this->add_control(
				'custom_badge_image',
				[
					'label'     => __( 'Upload Badge Image', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::MEDIA,
					'condition' => [
						'show_grid_badge' => 'yes',
						'badge_source'    => 'custom',
					],
				]
			);

			$this->add_control(
				'custom_badge_link',
				[
					'label'         => __( 'Badge Link URL (Optional)', 'webclyde' ),
					'type'          => \Elementor\Controls_Manager::URL,
					'placeholder'   => 'https://example.com/certification',
					'show_external' => true,
					'default'       => [ 'url' => '' ],
					'condition'     => [ 'show_grid_badge' => 'yes' ],
				]
			);

			$this->add_responsive_control(
				'badge_max_width',
				[
					'label'      => __( 'Badge Max Width', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range'      => [
						'px' => [ 'min' => 80, 'max' => 400 ],
						'%'  => [ 'min' => 10, 'max' => 100 ],
					],
					'default'    => [ 'size' => 220, 'unit' => 'px' ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-approach-badge-img' => 'max-width: {{SIZE}}{{UNIT}};',
					],
					'condition'  => [ 'show_grid_badge' => 'yes' ],
				]
			);

			$this->end_controls_section();

			/* --- Style Section: Card Box --- */
			$this->start_controls_section(
				'section_style_card',
				[ 'label' => __( 'Card Box', 'webclyde' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
			);

			$this->add_control(
				'card_bg',
				[
					'label'     => __( 'Background Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#ffffff',
					'selectors' => [
						'{{WRAPPER}} .wc-port-approach-card' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name'     => 'card_border',
					'selector' => '{{WRAPPER}} .wc-port-approach-card',
					'fields_options' => [
						'border' => [ 'default' => 'solid' ],
						'width'  => [ 'default' => [ 'top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'isLinked' => true ] ],
						'color'  => [ 'default' => '#e5e7eb' ],
					],
				]
			);

			$this->add_responsive_control(
				'card_radius',
				[
					'label'      => __( 'Border Radius', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'default'    => [ 'top' => '14', 'right' => '14', 'bottom' => '14', 'left' => '14', 'unit' => 'px', 'isLinked' => true ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-approach-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'card_padding',
				[
					'label'      => __( 'Padding', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'default'    => [ 'top' => '28', 'right' => '24', 'bottom' => '28', 'left' => '24', 'unit' => 'px', 'isLinked' => false ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-approach-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name'     => 'card_shadow',
					'selector' => '{{WRAPPER}} .wc-port-approach-card',
				]
			);

			$this->add_responsive_control(
				'grid_gap',
				[
					'label'      => __( 'Cards Gap', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'em' ],
					'range'      => [
						'px' => [ 'min' => 0, 'max' => 60 ],
					],
					'default'    => [ 'size' => 24, 'unit' => 'px' ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-approach-grid' => 'gap: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->end_controls_section();

			/* --- Style Section: Number Badge --- */
			$this->start_controls_section(
				'section_style_badge',
				[ 'label' => __( 'Number Badges', 'webclyde' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
			);

			$this->add_responsive_control(
				'badge_size',
				[
					'label'      => __( 'Badge Size', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 20, 'max' => 60 ] ],
					'default'    => [ 'size' => 32, 'unit' => 'px' ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-approach-badge' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'badge_bg',
				[
					'label'     => __( 'Badge Background', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#dcfce7',
					'selectors' => [
						'{{WRAPPER}} .wc-port-approach-badge' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'badge_color',
				[
					'label'     => __( 'Badge Text Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#059669',
					'selectors' => [
						'{{WRAPPER}} .wc-port-approach-badge' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name'     => 'badge_typography',
					'selector' => '{{WRAPPER}} .wc-port-approach-badge',
				]
			);

			$this->add_responsive_control(
				'badge_margin_bottom',
				[
					'label'      => __( 'Bottom Spacing', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
					'default'    => [ 'size' => 20, 'unit' => 'px' ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-approach-badge' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->end_controls_section();

			/* --- Style Section: Typography --- */
			$this->start_controls_section(
				'section_style_typography',
				[ 'label' => __( 'Title & Description', 'webclyde' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
			);

			$this->add_control(
				'heading_title_style',
				[
					'label'     => __( 'Step Title', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'title_color',
				[
					'label'     => __( 'Title Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#0f172a',
					'selectors' => [
						'{{WRAPPER}} .wc-port-approach-title' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name'     => 'title_typography',
					'selector' => '{{WRAPPER}} .wc-port-approach-title',
				]
			);

			$this->add_responsive_control(
				'title_bottom_space',
				[
					'label'      => __( 'Title Bottom Space', 'webclyde' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 0, 'max' => 30 ] ],
					'default'    => [ 'size' => 12, 'unit' => 'px' ],
					'selectors'  => [
						'{{WRAPPER}} .wc-port-approach-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'heading_desc_style',
				[
					'label'     => __( 'Step Description', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'desc_color',
				[
					'label'     => __( 'Description Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#475569',
					'selectors' => [
						'{{WRAPPER}} .wc-port-approach-desc' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name'     => 'desc_typography',
					'selector' => '{{WRAPPER}} .wc-port-approach-desc',
				]
			);

			$this->end_controls_section();
		}

		protected function render() {
			$settings = $this->get_settings_for_display();

			$custom_steps = [];
			if ( 'custom' === ( $settings['source'] ?? 'dynamic' ) && ! empty( $settings['custom_steps'] ) && is_array( $settings['custom_steps'] ) ) {
				foreach ( $settings['custom_steps'] as $item ) {
					if ( ! empty( $item['step_title'] ) || ! empty( $item['step_desc'] ) ) {
						$custom_steps[] = [
							'title' => $item['step_title'] ?? '',
							'desc'  => $item['step_desc'] ?? '',
						];
					}
				}
			}

			$show_badge = ( $settings['show_grid_badge'] ?? 'yes' ) === 'yes';
			$badge_url  = '';
			$badge_link = ! empty( $settings['custom_badge_link']['url'] ) ? $settings['custom_badge_link']['url'] : '';

			if ( $show_badge ) {
				if ( ( $settings['badge_source'] ?? 'dynamic' ) === 'custom' && ! empty( $settings['custom_badge_image']['url'] ) ) {
					$badge_url = $settings['custom_badge_image']['url'];
				}
			}

			echo webclyde_render_portfolio_approach(
				get_the_ID(),
				[
					'steps'           => 'custom' === ( $settings['source'] ?? 'dynamic' ) ? $custom_steps : null,
					'title_tag'       => ! empty( $settings['title_tag'] ) ? $settings['title_tag'] : 'h3',
					'show_badge'      => $show_badge,
					'badge_image_url' => $badge_url,
					'badge_link'      => $badge_link,
				]
			);
		}
	}

	/* -------------------------------------------------------------------------
	 * 5. PORTFOLIO RESULTS WIDGET
	 * ---------------------------------------------------------------------- */
	class Webclyde_Portfolio_Results_Widget extends \Elementor\Widget_Base {

		public function get_name() {
			return 'webclyde_portfolio_results';
		}

		public function get_title() {
			return __( 'Portfolio: Results & Metrics', 'webclyde' );
		}

		public function get_icon() {
			return 'eicon-counter-circle';
		}

		public function get_categories() {
			return [ 'webclyde-portfolio' ];
		}

		public function show_in_panel() {
			return false; // Hidden from panel per user request
		}

		public function get_style_depends() {
			return [ 'webclyde-portfolio-frontend-css' ];
		}

		protected function register_controls() {
			$this->start_controls_section(
				'section_style',
				[ 'label' => __( 'Style & Background', 'webclyde' ) ]
			);

			$this->add_control(
				'bg_color',
				[
					'label'     => __( 'Background Card Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => [ '{{WRAPPER}} .wc-port-results-card' => 'background: {{VALUE}};' ],
				]
			);

			$this->end_controls_section();
		}

		protected function render() {
			$html = webclyde_render_portfolio_results( get_the_ID() );

			if ( ! $html && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				?>
				<div class="wc-port-results-card">
					<h2 class="wc-port-section-title"><?php esc_html_e( 'The Results', 'webclyde' ); ?></h2>
					<div class="wc-port-metrics-grid">
						<div class="wc-port-metric-box">
							<div class="wc-port-metric-icon">&#9889;</div>
							<div class="wc-port-metric-value">0.8s</div>
							<div class="wc-port-metric-label"><?php esc_html_e( 'Mobile Load Time', 'webclyde' ); ?></div>
						</div>
						<div class="wc-port-metric-box">
							<div class="wc-port-metric-icon">&#128640;</div>
							<div class="wc-port-metric-value">98/100</div>
							<div class="wc-port-metric-label"><?php esc_html_e( 'PageSpeed Score (Mobile)', 'webclyde' ); ?></div>
						</div>
						<div class="wc-port-metric-box">
							<div class="wc-port-metric-icon">&#128200;</div>
							<div class="wc-port-metric-value">+185%</div>
							<div class="wc-port-metric-label"><?php esc_html_e( 'Completed Checkouts', 'webclyde' ); ?></div>
						</div>
					</div>
				</div>
				<?php
				return;
			}

			echo $html;
		}
	}

	/* -------------------------------------------------------------------------
	 * 6. PORTFOLIO CLIENT REVIEW WIDGET
	 * ---------------------------------------------------------------------- */
	class Webclyde_Portfolio_Review_Widget extends \Elementor\Widget_Base {

		public function get_name() {
			return 'webclyde_portfolio_review';
		}

		public function get_title() {
			return __( 'Portfolio: Client Review', 'webclyde' );
		}

		public function get_icon() {
			return 'eicon-testimonial';
		}

		public function get_categories() {
			return [ 'webclyde-portfolio' ];
		}

		public function show_in_panel() {
			return false; // Hidden from panel per user request
		}

		public function get_style_depends() {
			return [ 'webclyde-portfolio-frontend-css' ];
		}

		protected function register_controls() {
			$this->start_controls_section(
				'section_style',
				[ 'label' => __( 'Style & Card', 'webclyde' ) ]
			);

			$this->add_control(
				'card_bg',
				[
					'label'     => __( 'Background Color', 'webclyde' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => [ '{{WRAPPER}} .wc-port-review-card' => 'background-color: {{VALUE}};' ],
				]
			);

			$this->end_controls_section();
		}

		protected function render() {
			$html = webclyde_render_portfolio_review( get_the_ID() );

			if ( ! $html && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				?>
				<div class="wc-port-review-card">
					<div class="wc-port-quote-icon">&ldquo;</div>
					<blockquote class="wc-port-client-quote">
						<p><?php esc_html_e( 'The new platform completely exceeded our expectations. Our checkout conversion rate exploded after launch!', 'webclyde' ); ?></p>
					</blockquote>
					<div class="wc-port-review-footer">
						<div class="wc-port-client-profile">
							<div class="wc-port-client-info">
								<h4><?php esc_html_e( 'Sarah Jenkins', 'webclyde' ); ?></h4>
								<p><?php esc_html_e( 'CEO, BrandCo', 'webclyde' ); ?></p>
							</div>
						</div>
					</div>
				</div>
				<?php
				return;
			}

			echo $html;
		}
	}

	/* -------------------------------------------------------------------------
	 * 7. PORTFOLIO CASE STUDY (ALL-IN-ONE) WIDGET
	 * ---------------------------------------------------------------------- */
	class Webclyde_Portfolio_Case_Study_Widget extends \Elementor\Widget_Base {

		public function get_name() {
			return 'webclyde_portfolio_case_study';
		}

		public function get_title() {
			return __( 'Portfolio: Complete Case Study', 'webclyde' );
		}

		public function get_icon() {
			return 'eicon-document-file';
		}

		public function get_categories() {
			return [ 'webclyde-portfolio' ];
		}

		public function show_in_panel() {
			return false; // Hidden from panel per user request
		}

		public function get_style_depends() {
			return [ 'webclyde-portfolio-frontend-css' ];
		}

		protected function register_controls() {
			$this->start_controls_section(
				'section_sections',
				[ 'label' => __( 'Visible Sections', 'webclyde' ) ]
			);

			$this->add_control(
				'show_meta',
				[ 'label' => __( 'Overview & Meta Box', 'webclyde' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ]
			);
			$this->add_control(
				'show_carousel',
				[ 'label' => __( 'Image Carousel', 'webclyde' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ]
			);
			$this->add_control(
				'show_challenge',
				[ 'label' => __( 'The Challenge & Problems', 'webclyde' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ]
			);
			$this->add_control(
				'show_approach',
				[ 'label' => __( 'The Approach & Steps', 'webclyde' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ]
			);
			$this->add_control(
				'show_results',
				[ 'label' => __( 'Results & Metrics', 'webclyde' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ]
			);
			$this->add_control(
				'show_review',
				[ 'label' => __( 'Client Review', 'webclyde' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ]
			);

			$this->end_controls_section();
		}

		protected function render() {
			$settings = $this->get_settings_for_display();
			$post_id  = get_the_ID();

			echo '<div class="wc-port-case-study">';

			if ( 'yes' === $settings['show_meta'] ) {
				echo webclyde_render_portfolio_meta( $post_id );
			}

			if ( 'yes' === $settings['show_carousel'] ) {
				echo webclyde_render_portfolio_carousel( $post_id );
			}

			if ( 'yes' === $settings['show_challenge'] ) {
				echo webclyde_render_portfolio_challenge( $post_id );
			}

			if ( 'yes' === $settings['show_approach'] ) {
				echo webclyde_render_portfolio_approach( $post_id );
			}

			if ( 'yes' === $settings['show_results'] ) {
				echo webclyde_render_portfolio_results( $post_id );
			}

			if ( 'yes' === $settings['show_review'] ) {
				echo webclyde_render_portfolio_review( $post_id );
			}

			echo '</div>';
		}
	}

	// Active widgets:
	// 1. Portfolio: The Main Problems (Numbered circle badges card)
	$widgets_manager->register( new Webclyde_Portfolio_Problems_Widget() );

	// 2. Portfolio: Approach & Steps (Modern cards grid with mint badges)
	$widgets_manager->register( new Webclyde_Portfolio_Approach_Widget() );

	// Legacy widgets registered as hidden from panel (show_in_panel = false) for backward-compat
	$widgets_manager->register( new Webclyde_Portfolio_Challenge_Widget() );

	// The following widgets have been removed from the panel per user request:
	// - Webclyde_Portfolio_Meta_Widget (Overview & Meta)
	// - Webclyde_Portfolio_Carousel_Widget (Custom Image Carousel)
	// - Webclyde_Portfolio_Results_Widget (Results & Metrics)
	// - Webclyde_Portfolio_Review_Widget (Client Review)
	// - Webclyde_Portfolio_Case_Study_Widget (Complete Case Study)
} );
