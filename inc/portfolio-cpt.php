<?php

/**
 * Portfolio Custom Post Type & Elementor Support
 *
 * @package Webclyde
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Register Portfolio Post Type and Taxonomies
 */
function webclyde_register_portfolio_cpt()
{

	// Categories
	register_taxonomy('portfolio_category', 'portfolio', [
		'labels' => [
			'name'              => __('Portfolio Categories', 'webclyde'),
			'singular_name'     => __('Portfolio Category', 'webclyde'),
			'search_items'      => __('Search Categories', 'webclyde'),
			'all_items'         => __('All Categories', 'webclyde'),
			'parent_item'       => __('Parent Category', 'webclyde'),
			'parent_item_colon' => __('Parent Category:', 'webclyde'),
			'edit_item'         => __('Edit Category', 'webclyde'),
			'update_item'       => __('Update Category', 'webclyde'),
			'add_new_item'      => __('Add New Category', 'webclyde'),
			'new_item_name'     => __('New Category Name', 'webclyde'),
			'menu_name'         => __('Categories', 'webclyde'),
		],
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_in_rest'      => true,
		'rewrite'           => ['slug' => 'portfolio-category'],
	]);

	// Tags
	register_taxonomy('portfolio_tag', 'portfolio', [
		'labels' => [
			'name'          => __('Portfolio Tags', 'webclyde'),
			'singular_name' => __('Portfolio Tag', 'webclyde'),
			'search_items'  => __('Search Tags', 'webclyde'),
			'all_items'     => __('All Tags', 'webclyde'),
			'edit_item'     => __('Edit Tag', 'webclyde'),
			'update_item'   => __('Update Tag', 'webclyde'),
			'add_new_item'  => __('Add New Tag', 'webclyde'),
			'new_item_name' => __('New Tag Name', 'webclyde'),
			'menu_name'     => __('Tags', 'webclyde'),
		],
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_in_rest'      => true,
		'rewrite'           => ['slug' => 'portfolio-tag'],
	]);

	// Portfolio CPT
	register_post_type('portfolio', [
		'labels' => [
			'name'               => __('Portfolios', 'webclyde'),
			'singular_name'      => __('Portfolio', 'webclyde'),
			'menu_name'          => __('Portfolio', 'webclyde'),
			'add_new'            => __('Add New Project', 'webclyde'),
			'add_new_item'       => __('Add Portfolio', 'webclyde'),
			'edit_item'          => __('Edit Portfolio Project', 'webclyde'),
			'new_item'           => __('New Portfolio Project', 'webclyde'),
			'view_item'          => __('View Project', 'webclyde'),
			'search_items'       => __('Search Portfolio', 'webclyde'),
			'not_found'          => __('No portfolio items found', 'webclyde'),
			'not_found_in_trash' => __('No portfolio items in Trash', 'webclyde'),
		],
		'public'              => true,
		'has_archive'         => true,
		'rewrite'             => ['slug' => 'portfolio'],
		'menu_icon'           => 'dashicons-portfolio',
		'supports'            => [
			'title',
			'editor',
			'thumbnail',
			'excerpt',
			'custom-fields',
			'revisions',
			'author',
			'page-attributes',
			'elementor', // Native Elementor support
		],
		'taxonomies'          => ['portfolio_category', 'portfolio_tag'],
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'show_in_rest'        => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
	]);
}
add_action('init', 'webclyde_register_portfolio_cpt', 5);

/**
 * Enable Elementor editing support for Portfolio CPT
 */
add_action('init', function () {
	add_post_type_support('portfolio', 'elementor');
}, 11);

/**
 * Automatically include 'portfolio' in Elementor supported post types
 */
add_filter('option_elementor_cpt_support', function ($value) {
	if (! is_array($value)) {
		$value = ['post', 'page'];
	}
	if (! in_array('portfolio', $value, true)) {
		$value[] = 'portfolio';
	}
	return $value;
});

/**
 * Use standard editor screen for Portfolio so fields render directly under the Title
 */
add_filter('use_block_editor_for_post_type', function ($use_block_editor, $post_type) {
	if ('portfolio' === $post_type) {
		return false;
	}
	return $use_block_editor;
}, 10, 2);
