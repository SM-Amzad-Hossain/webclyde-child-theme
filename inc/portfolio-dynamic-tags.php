<?php
/**
 * Elementor Dynamic Tags for Portfolio Fields
 *
 * @package Webclyde
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Dynamic Tags with Elementor
 */
add_action( 'elementor/dynamic_tags/register', function( $dynamic_tags_manager ) {

	if ( ! class_exists( '\Elementor\Core\DynamicTags\Tag' ) || ! class_exists( '\Elementor\Core\DynamicTags\Data_Tag' ) ) {
		return;
	}

	// Register Group
	$dynamic_tags_manager->register_group( 'portfolio_case_study', [
		'title' => __( 'Portfolio Case Study', 'webclyde' ),
	] );

	// 1. Text Dynamic Tag
	class Webclyde_Portfolio_Text_Tag extends \Elementor\Core\DynamicTags\Tag {

		public function get_name() {
			return 'portfolio-text-field';
		}

		public function get_title() {
			return __( 'Portfolio Text Field', 'webclyde' );
		}

		public function get_group() {
			return 'portfolio_case_study';
		}

		public function get_categories() {
			return [
				\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			];
		}

		protected function register_controls() {
			$this->add_control(
				'field',
				[
					'label'   => __( 'Select Field', 'webclyde' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => 'organization',
					'options' => [
						'organization'        => __( 'Organization', 'webclyde' ),
						'services'            => __( 'Services', 'webclyde' ),
						'timeline'            => __( 'Timeline', 'webclyde' ),
						'industry'            => __( 'Industry', 'webclyde' ),
						'platform'            => __( 'Platform', 'webclyde' ),
						'location'            => __( 'Location', 'webclyde' ),
						'description'         => __( 'Project Overview / Description', 'webclyde' ),
						'challenge_title'     => __( 'Challenge Title', 'webclyde' ),
						'challenge_desc'      => __( 'Challenge Description', 'webclyde' ),
						'approach_title'      => __( 'Approach Title', 'webclyde' ),
						'result_title'        => __( 'Result Title', 'webclyde' ),
						'result_desc'         => __( 'Result Description', 'webclyde' ),
						'mobile_load_time'    => __( 'Mobile Load Time', 'webclyde' ),
						'pagespeed_score'     => __( 'Google PageSpeed Score', 'webclyde' ),
						'completed_checkouts' => __( 'Completed Checkouts', 'webclyde' ),
						'client_said'         => __( 'What the Client Said (Quote)', 'webclyde' ),
						'client_name'         => __( 'Client Name', 'webclyde' ),
						'client_designation'  => __( 'Client Designation', 'webclyde' ),
					],
				]
			);
		}

		public function render() {
			$field   = $this->get_settings( 'field' );
			$post_id = function_exists( 'webclyde_get_portfolio_post_id' ) ? webclyde_get_portfolio_post_id( get_the_ID() ) : get_the_ID();

			if ( ! $post_id ) {
				return;
			}

			$map = [
				'organization'        => '_portfolio_organization',
				'services'            => '_portfolio_services',
				'timeline'            => '_portfolio_timeline',
				'industry'            => '_portfolio_industry',
				'platform'            => '_portfolio_platform',
				'location'            => '_portfolio_location',
				'description'         => '_portfolio_description',
				'challenge_title'     => '_portfolio_challenge_title',
				'challenge_desc'      => '_portfolio_challenge_description',
				'approach_title'      => '_portfolio_approach_title',
				'result_title'        => '_portfolio_result_title',
				'result_desc'         => '_portfolio_result_description',
				'mobile_load_time'    => '_portfolio_mobile_load_time',
				'pagespeed_score'     => '_portfolio_pagespeed_score',
				'completed_checkouts' => '_portfolio_completed_checkouts',
				'client_said'         => '_portfolio_client_said',
				'client_name'         => '_portfolio_client_name',
				'client_designation'  => '_portfolio_client_designation',
			];

			$meta_key = $map[ $field ] ?? '_portfolio_' . $field;
			$value = webclyde_get_portfolio_field( $meta_key, $post_id );

			if ( in_array( $field, [ 'description', 'challenge_desc', 'result_desc', 'client_said' ], true ) ) {
				echo wp_kses_post( wpautop( (string) $value ) );
			} else {
				echo wp_kses_post( (string) $value );
			}
		}
	}

	// 2. Image Dynamic Tag
	class Webclyde_Portfolio_Image_Tag extends \Elementor\Core\DynamicTags\Data_Tag {

		public function get_name() {
			return 'portfolio-image-field';
		}

		public function get_title() {
			return __( 'Portfolio Image Field', 'webclyde' );
		}

		public function get_group() {
			return 'portfolio_case_study';
		}

		public function get_categories() {
			return [
				\Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
			];
		}

		protected function register_controls() {
			$this->add_control(
				'image_field',
				[
					'label'   => __( 'Select Image', 'webclyde' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => 'featured_image',
					'options' => [
						'featured_image'       => __( 'Default Featured Image', 'webclyde' ),
						'logo'                 => __( 'Portfolio Logo', 'webclyde' ),
						'approach_image'       => __( 'Approach Image (Diagram / Mockup)', 'webclyde' ),
						'approach_badge_image' => __( 'Approach Grid Badge Image', 'webclyde' ),
						'review_image'         => __( 'Review Proof Image', 'webclyde' ),
						'client_avatar'        => __( 'Client Avatar', 'webclyde' ),
					],
				]
			);
		}

		public function get_value( array $options = [] ) {
			$field   = $this->get_settings( 'image_field' );
			$post_id = function_exists( 'webclyde_get_portfolio_post_id' ) ? webclyde_get_portfolio_post_id( get_the_ID() ) : get_the_ID();

			if ( ! $post_id ) {
				return [];
			}

			if ( 'featured_image' === $field ) {
				$img_id = get_post_thumbnail_id( $post_id );
				if ( ! $img_id && class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
					// Fallback to latest portfolio featured image for preview
					$sample = get_posts( [
						'post_type'      => 'portfolio',
						'posts_per_page' => 1,
						'post_status'    => 'publish',
						'fields'         => 'ids',
					] );
					if ( ! empty( $sample ) ) {
						$img_id = get_post_thumbnail_id( $sample[0] );
					}
				}
				if ( $img_id ) {
					return [
						'id'  => (int) $img_id,
						'url' => wp_get_attachment_image_url( $img_id, 'full' ),
					];
				}
				return [];
			}

			$map = [
				'logo'                 => '_portfolio_logo',
				'approach_image'       => '_portfolio_approach_image',
				'approach_badge_image' => '_portfolio_approach_badge_image',
				'review_image'         => '_portfolio_review_image',
				'client_avatar'        => '_portfolio_client_avatar',
			];

			$meta_key = $map[ $field ] ?? '_portfolio_' . $field;
			$img_id   = absint( webclyde_get_portfolio_field( $meta_key, $post_id ) );

			if ( ! $img_id ) {
				return [];
			}

			return [
				'id'  => $img_id,
				'url' => wp_get_attachment_image_url( $img_id, 'full' ),
			];
		}
	}

	// 3. URL Dynamic Tag
	class Webclyde_Portfolio_URL_Tag extends \Elementor\Core\DynamicTags\Data_Tag {

		public function get_name() {
			return 'portfolio-url-field';
		}

		public function get_title() {
			return __( 'Portfolio: Site URL', 'webclyde' );
		}

		public function get_group() {
			return 'portfolio_case_study';
		}

		public function get_categories() {
			return [
				\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
			];
		}

		public function get_value( array $options = [] ) {
			$post_id = function_exists( 'webclyde_get_portfolio_post_id' ) ? webclyde_get_portfolio_post_id( get_the_ID() ) : get_the_ID();
			if ( ! $post_id ) {
				return '';
			}
			return esc_url( webclyde_get_portfolio_field( '_portfolio_site_url', $post_id ) );
		}
	}

	// 4. Gallery Dynamic Tag
	class Webclyde_Portfolio_Gallery_Tag extends \Elementor\Core\DynamicTags\Data_Tag {

		public function get_name() {
			return 'portfolio-gallery-field';
		}

		public function get_title() {
			return __( 'Portfolio Gallery Images', 'webclyde' );
		}

		public function get_group() {
			return 'portfolio_case_study';
		}

		public function get_categories() {
			return [
				\Elementor\Modules\DynamicTags\Module::GALLERY_CATEGORY,
			];
		}

		public function get_value( array $options = [] ) {
			$post_id = function_exists( 'webclyde_get_portfolio_post_id' ) ? webclyde_get_portfolio_post_id( get_the_ID() ) : get_the_ID();

			$ids = [];

			if ( $post_id ) {
				$raw_gallery = webclyde_get_portfolio_field( '_portfolio_gallery', $post_id );
				if ( empty( $raw_gallery ) ) {
					$raw_gallery = webclyde_get_portfolio_field( '_portfolio_gallery_array', $post_id );
				}

				if ( is_array( $raw_gallery ) ) {
					$ids = array_filter( array_map( 'absint', $raw_gallery ) );
				} elseif ( is_string( $raw_gallery ) && '' !== trim( $raw_gallery ) ) {
					$ids = array_filter( array_map( 'absint', explode( ',', $raw_gallery ) ) );
				}

				// If no gallery images defined, check if post has a featured image
				if ( empty( $ids ) && has_post_thumbnail( $post_id ) ) {
					$thumb_id = get_post_thumbnail_id( $post_id );
					if ( $thumb_id ) {
						$ids = [ (int) $thumb_id ];
					}
				}
			}

			// In Elementor Editor preview: provide fallback preview images so default carousel displays immediately
			if ( empty( $ids ) && class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				// 1. Try any other portfolio item with a gallery
				$sample_posts = get_posts( [
					'post_type'      => 'portfolio',
					'posts_per_page' => 5,
					'post_status'    => 'any',
					'fields'         => 'ids',
				] );
				foreach ( $sample_posts as $sp_id ) {
					$sp_gallery = webclyde_get_portfolio_field( '_portfolio_gallery', $sp_id );
					if ( empty( $sp_gallery ) ) {
						$sp_gallery = webclyde_get_portfolio_field( '_portfolio_gallery_array', $sp_id );
					}
					$sp_ids = is_array( $sp_gallery )
						? array_filter( array_map( 'absint', $sp_gallery ) )
						: array_filter( array_map( 'absint', explode( ',', (string) $sp_gallery ) ) );
					if ( ! empty( $sp_ids ) ) {
						$ids = $sp_ids;
						break;
					}
				}

				// 2. Try media library image attachments
				if ( empty( $ids ) ) {
					$media_sample = get_posts( [
						'post_type'      => 'attachment',
						'post_mime_type' => 'image',
						'posts_per_page' => 4,
						'fields'         => 'ids',
					] );
					if ( ! empty( $media_sample ) ) {
						$ids = $media_sample;
					}
				}
			}

			$images = [];
			foreach ( $ids as $id ) {
				$url = wp_get_attachment_image_url( $id, 'full' );
				if ( $url ) {
					$images[] = [
						'id'  => (int) $id,
						'url' => $url,
					];
				}
			}

			return $images;
		}
	}

	// Register all tags
	$dynamic_tags_manager->register( new Webclyde_Portfolio_Text_Tag() );
	$dynamic_tags_manager->register( new Webclyde_Portfolio_Image_Tag() );
	$dynamic_tags_manager->register( new Webclyde_Portfolio_URL_Tag() );
	$dynamic_tags_manager->register( new Webclyde_Portfolio_Gallery_Tag() );
} );

