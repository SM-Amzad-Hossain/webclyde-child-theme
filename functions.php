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

// Portfolio System: CPT, Custom Fields, Elementor Dynamic Tags & Widgets, Shortcodes
require_once get_stylesheet_directory() . '/inc/portfolio-cpt.php';
require_once get_stylesheet_directory() . '/inc/portfolio-metaboxes.php';
require_once get_stylesheet_directory() . '/inc/portfolio-render.php';
require_once get_stylesheet_directory() . '/inc/portfolio-widgets.php';
require_once get_stylesheet_directory() . '/inc/portfolio-dynamic-tags.php';


// Frame Elementor Widget
require_once get_stylesheet_directory() . '/frame/frame.php';
