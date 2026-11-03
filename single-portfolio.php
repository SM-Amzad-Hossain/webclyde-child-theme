<?php
/**
 * Single Portfolio template.
 *
 * Renders the case study (project details, carousel, challenge, approach,
 * results, client review) without the parent theme's default page title,
 * because the case-study header already contains the H1.
 *
 * The case study is appended to the content automatically by
 * inc/portfolio-frontend.php. If the content was built with Elementor,
 * use the "Portfolio Case Study" widget or the [portfolio_case_study]
 * shortcode inside the Elementor layout instead — the renderer detects
 * that and will not print the case study twice.
 *
 * @package webclyde
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<main id="content" <?php post_class( 'site-main wcp-portfolio-main' ); ?>>
		<div class="page-content">
			<?php
			the_content(); // Case study auto-appends here (see wpc_auto_append).
			wp_link_pages();
			?>
		</div>
	</main>

	<?php
endwhile;

get_footer();
