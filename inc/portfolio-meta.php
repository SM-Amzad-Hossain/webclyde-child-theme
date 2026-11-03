<?php
/**
 * Portfolio case-study meta fields.
 *
 * Adds the full set of case-study fields to the Portfolio editor:
 *
 *   - Project Details : logo, site URL, description, organization, services,
 *                       timeline, industry, platform, location
 *   - Image Carousel  : ordered multi-image gallery
 *   - The Challenge   : title, description, repeatable "main problems" list
 *   - The Approach    : image, title, repeatable "step by step" list
 *   - The Results     : title, description, mobile load time,
 *                       Google PageSpeed score (mobile), completed checkouts
 *   - Client Review   : review image, quote, avatar, name, designation
 *
 * Everything is stored as post meta on the `portfolio` post type with the
 * `_wpc_` prefix and exposed through the REST API. No plugins required.
 *
 * @package webclyde
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta key prefix.
 */
if ( ! defined( 'WPC_META_PREFIX' ) ) {
	define( 'WPC_META_PREFIX', '_wpc_' );
}

/**
 * Meta box groups (in display order).
 *
 * @return array<string,string> group slug => title.
 */
function wpc_meta_groups() {
	return [
		'details'   => __( 'Project Details', 'webclyde' ),
		'carousel'  => __( 'Image Carousel', 'webclyde' ),
		'challenge' => __( 'The Challenge', 'webclyde' ),
		'approach'  => __( 'The Approach', 'webclyde' ),
		'results'   => __( 'The Results', 'webclyde' ),
		'review'    => __( 'Client Review', 'webclyde' ),
	];
}

/**
 * Central field registry.
 *
 * Types: text, url, textarea, number, image (attachment ID),
 *        gallery (ordered attachment IDs), repeatable (list of strings).
 *
 * @return array<string,array> field key => config.
 */
function wpc_meta_fields() {
	return [
		// ---- Project Details -------------------------------------------.
		'logo'                  => [
			'group'       => 'details',
			'type'        => 'image',
			'label'       => __( 'Logo', 'webclyde' ),
			'description' => __( 'Client / project logo shown in the case-study header. A transparent PNG or SVG works best.', 'webclyde' ),
		],
		'site_url'              => [
			'group'       => 'details',
			'type'        => 'url',
			'label'       => __( 'Site URL', 'webclyde' ),
			'description' => __( 'Live project URL, e.g. https://example.com. Shown as a “Visit live site” button.', 'webclyde' ),
			'placeholder' => 'https://',
		],
		'description'           => [
			'group'       => 'details',
			'type'        => 'textarea',
			'label'       => __( 'Description', 'webclyde' ),
			'description' => __( 'Short project summary shown under the title.', 'webclyde' ),
			'rows'        => 3,
		],
		'organization'          => [
			'group'       => 'details',
			'type'        => 'text',
			'label'       => __( 'Organization', 'webclyde' ),
			'placeholder' => __( 'e.g. Aurelia Skin Ltd.', 'webclyde' ),
		],
		'services'              => [
			'group'       => 'details',
			'type'        => 'text',
			'label'       => __( 'Services', 'webclyde' ),
			'description' => __( 'Comma-separated. Each service is shown as a badge.', 'webclyde' ),
			'placeholder' => __( 'e.g. UX/UI Design, WooCommerce Development, Speed Optimization', 'webclyde' ),
		],
		'timeline'              => [
			'group'       => 'details',
			'type'        => 'text',
			'label'       => __( 'Timeline', 'webclyde' ),
			'placeholder' => __( 'e.g. 6 weeks', 'webclyde' ),
		],
		'industry'              => [
			'group'       => 'details',
			'type'        => 'text',
			'label'       => __( 'Industry', 'webclyde' ),
			'placeholder' => __( 'e.g. Beauty & Skincare', 'webclyde' ),
		],
		'platform'              => [
			'group'       => 'details',
			'type'        => 'text',
			'label'       => __( 'Platform', 'webclyde' ),
			'placeholder' => __( 'e.g. WordPress + WooCommerce', 'webclyde' ),
		],
		'location'              => [
			'group'       => 'details',
			'type'        => 'text',
			'label'       => __( 'Location', 'webclyde' ),
			'placeholder' => __( 'e.g. London, United Kingdom', 'webclyde' ),
		],

		// ---- Image Carousel --------------------------------------------.
		'gallery'               => [
			'group'       => 'carousel',
			'type'        => 'gallery',
			'label'       => __( 'Carousel images', 'webclyde' ),
			'description' => __( 'Shown as a swipeable image carousel on the case study. Drag thumbnails to reorder.', 'webclyde' ),
		],

		// ---- The Challenge ---------------------------------------------.
		'challenge_title'       => [
			'group'       => 'challenge',
			'type'        => 'text',
			'label'       => __( 'Challenge title', 'webclyde' ),
			'placeholder' => __( 'e.g. A slow store was losing sales at checkout', 'webclyde' ),
		],
		'challenge_description' => [
			'group'       => 'challenge',
			'type'        => 'textarea',
			'label'       => __( 'Challenge description', 'webclyde' ),
			'rows'        => 4,
		],
		'problems'              => [
			'group'       => 'challenge',
			'type'        => 'repeatable',
			'label'       => __( 'The main problems', 'webclyde' ),
			'description' => __( 'Add as many problems as you need. Drag to reorder.', 'webclyde' ),
			'add_label'   => __( '+ Add problem', 'webclyde' ),
			'placeholder' => __( 'Describe one problem…', 'webclyde' ),
		],

		// ---- The Approach ----------------------------------------------.
		'approach_image'        => [
			'group'       => 'approach',
			'type'        => 'image',
			'label'       => __( 'Approach image', 'webclyde' ),
			'description' => __( 'Shown next to the approach steps (wireframes, design system, team at work…).', 'webclyde' ),
		],
		'approach_title'        => [
			'group'       => 'approach',
			'type'        => 'text',
			'label'       => __( 'Approach title', 'webclyde' ),
			'placeholder' => __( 'e.g. Rebuild around speed and a frictionless checkout', 'webclyde' ),
		],
		'approach_steps'        => [
			'group'       => 'approach',
			'type'        => 'repeatable',
			'label'       => __( 'Step-by-step approach', 'webclyde' ),
			'description' => __( 'Each item becomes a numbered step. Drag to reorder.', 'webclyde' ),
			'add_label'   => __( '+ Add step', 'webclyde' ),
			'placeholder' => __( 'Describe one step…', 'webclyde' ),
		],

		// ---- The Results -----------------------------------------------.
		'results_title'         => [
			'group'       => 'results',
			'type'        => 'text',
			'label'       => __( 'Result title', 'webclyde' ),
			'placeholder' => __( 'e.g. Faster pages, happier customers, more sales', 'webclyde' ),
		],
		'results_description'   => [
			'group'       => 'results',
			'type'        => 'textarea',
			'label'       => __( 'Result description', 'webclyde' ),
			'rows'        => 4,
		],
		'mobile_load_time'      => [
			'group'       => 'results',
			'type'        => 'text',
			'label'       => __( 'Mobile load time', 'webclyde' ),
			'placeholder' => __( 'e.g. 0.9s', 'webclyde' ),
		],
		'pagespeed_mobile'      => [
			'group'       => 'results',
			'type'        => 'number',
			'label'       => __( 'Google PageSpeed score (mobile)', 'webclyde' ),
			'description' => __( '0–100. Rendered as a score ring.', 'webclyde' ),
			'min'         => 0,
			'max'         => 100,
			'step'        => 1,
			'placeholder' => '98',
		],
		'checkouts'             => [
			'group'       => 'results',
			'type'        => 'text',
			'label'       => __( 'Completed checkouts after launch', 'webclyde' ),
			'placeholder' => __( 'e.g. 1,240+', 'webclyde' ),
		],

		// ---- Client Review ----------------------------------------------.
		'review_image'          => [
			'group'       => 'review',
			'type'        => 'image',
			'label'       => __( 'Review image', 'webclyde' ),
			'description' => __( 'Optional screenshot of the review (Trustpilot, Google, email…).', 'webclyde' ),
		],
		'review_quote'          => [
			'group'       => 'review',
			'type'        => 'textarea',
			'label'       => __( 'What the client said', 'webclyde' ),
			'rows'        => 4,
			'placeholder' => __( 'Paste the client’s testimonial…', 'webclyde' ),
		],
		'client_avatar'         => [
			'group'       => 'review',
			'type'        => 'image',
			'label'       => __( 'Client avatar', 'webclyde' ),
			'description' => __( 'Square photo, shown as a circle.', 'webclyde' ),
		],
		'client_name'           => [
			'group'       => 'review',
			'type'        => 'text',
			'label'       => __( 'Client name', 'webclyde' ),
			'placeholder' => __( 'e.g. Sarah Mitchell', 'webclyde' ),
		],
		'client_designation'    => [
			'group'       => 'review',
			'type'        => 'text',
			'label'       => __( 'Client designation', 'webclyde' ),
			'placeholder' => __( 'e.g. Founder & CEO, Aurelia Skin', 'webclyde' ),
		],
	];
}

/**
 * Read a single case-study value with a sensible default.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Field key (without prefix).
 * @return mixed
 */
function wpc_get_meta( $post_id, $key ) {
	$fields = wpc_meta_fields();
	if ( ! isset( $fields[ $key ] ) ) {
		return '';
	}

	$raw = get_post_meta( $post_id, WPC_META_PREFIX . $key, true );

	switch ( $fields[ $key ]['type'] ) {
		case 'image':
			return (int) $raw;
		case 'number':
			return ( '' === $raw || null === $raw ) ? '' : (float) $raw;
		case 'gallery':
			return array_values( array_filter( array_map( 'absint', (array) $raw ) ) );
		case 'repeatable':
			return array_values( array_filter( array_map( 'strval', (array) $raw ), 'strlen' ) );
		default:
			return is_string( $raw ) ? $raw : '';
	}
}

/**
 * Sanitize a value according to its field type.
 *
 * @param string $type  Field type.
 * @param mixed  $value Raw value.
 * @return mixed Empty string / empty array when the value is empty.
 */
function wpc_sanitize_value( $type, $value ) {
	switch ( $type ) {
		case 'image':
			$id = absint( $value );
			return ( $id && 'attachment' === get_post_type( $id ) ) ? $id : '';

		case 'number':
			if ( '' === $value || null === $value ) {
				return '';
			}
			$num = (float) $value;
			return (string) max( 0, min( 100, $num ) );

		case 'url':
			return esc_url_raw( trim( (string) $value ) );

		case 'textarea':
			return sanitize_textarea_field( (string) $value );

		case 'gallery':
			$ids = array_map( 'absint', (array) $value );
			$ids = array_values( array_unique( array_filter( $ids ) ) );
			return $ids;

		case 'repeatable':
			$items = array_map( 'sanitize_text_field', (array) $value );
			$items = array_map( 'trim', $items );
			return array_values( array_filter( $items, 'strlen' ) );

		case 'text':
		default:
			return sanitize_text_field( (string) $value );
	}
}

/**
 * REST schema for a field type (so the fields work with the block editor,
 * Elementor dynamic tags and headless setups).
 *
 * @param string $type Field type.
 * @return array|bool
 */
function wpc_rest_schema( $type ) {
	switch ( $type ) {
		case 'gallery':
			return [ 'type' => 'array', 'items' => [ 'type' => 'integer' ] ];
		case 'repeatable':
			return [ 'type' => 'array', 'items' => [ 'type' => 'string' ] ];
		case 'image':
			return [ 'type' => 'integer' ];
		case 'number':
			return [ 'type' => 'number' ];
		default:
			return [ 'type' => 'string' ];
	}
}

/**
 * Register every field as post meta (REST-visible, protected).
 */
function wpc_register_meta() {
	foreach ( wpc_meta_fields() as $key => $field ) {
		register_post_meta(
			'portfolio',
			WPC_META_PREFIX . $key,
			[
				'type'              => in_array( $field['type'], [ 'gallery', 'repeatable' ], true ) ? 'array' : ( 'image' === $field['type'] ? 'integer' : ( 'number' === $field['type'] ? 'number' : 'string' ) ),
				'single'            => true,
				'show_in_rest'      => [ 'schema' => wpc_rest_schema( $field['type'] ) ],
				'sanitize_callback' => function ( $value ) use ( $field ) {
					return wpc_sanitize_value( $field['type'], $value );
				},
				'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id );
				},
			]
		);
	}
}
add_action( 'init', 'wpc_register_meta', 20 );

/**
 * Use the classic editor for Portfolios.
 *
 * The case study is built from the structured meta boxes below, which are
 * front-and-center in the classic editor. This also keeps the media pickers,
 * drag-to-reorder galleries and repeaters working without Gutenberg's
 * meta-box save quirks. The normal content editor is still available.
 *
 * @param bool   $use_block_editor Whether to use the block editor.
 * @param string $post_type        Post type slug.
 * @return bool
 */
function wpc_use_classic_editor( $use_block_editor, $post_type ) {
	return 'portfolio' === $post_type ? false : $use_block_editor;
}
add_filter( 'use_block_editor_for_post_type', 'wpc_use_classic_editor', 10, 2 );

/**
 * Register the meta boxes on the Portfolio editor.
 */
function wpc_add_meta_boxes() {
	foreach ( wpc_meta_groups() as $slug => $title ) {
		add_meta_box(
			'wpc-' . $slug,
			$title,
			'wpc_render_meta_box',
			'portfolio',
			'normal',
			'high',
			[ 'group' => $slug ]
		);
	}
}
add_action( 'add_meta_boxes', 'wpc_add_meta_boxes' );

/**
 * Enqueue admin assets only on the Portfolio editor.
 *
 * @param string $hook Current admin page hook.
 */
function wpc_admin_assets( $hook ) {
	$screen = get_current_screen();
	if ( ! $screen || 'portfolio' !== $screen->post_type || ! in_array( $screen->base, [ 'post', 'post-new' ], true ) ) {
		return;
	}

	wp_enqueue_media();

	$base = get_stylesheet_directory_uri() . '/assets';
	$dir  = get_stylesheet_directory();

	wp_enqueue_style(
		'wpc-portfolio-admin',
		$base . '/css/portfolio-admin.css',
		[],
		file_exists( $dir . '/assets/css/portfolio-admin.css' ) ? filemtime( $dir . '/assets/css/portfolio-admin.css' ) : '1.0.0'
	);

	wp_enqueue_script(
		'wpc-portfolio-admin',
		$base . '/js/portfolio-admin.js',
		[ 'jquery', 'jquery-ui-sortable' ],
		file_exists( $dir . '/assets/js/portfolio-admin.js' ) ? filemtime( $dir . '/assets/js/portfolio-admin.js' ) : '1.0.0',
		true
	);
}
add_action( 'admin_enqueue_scripts', 'wpc_admin_assets' );

/**
 * Render one meta box.
 *
 * @param WP_Post $post     Current post.
 * @param array   $meta_box Meta box args (contains our group slug).
 */
function wpc_render_meta_box( $post, $meta_box ) {
	$group  = $meta_box['args']['group'];
	$fields = wpc_meta_fields();

	// One nonce for the whole screen (printed in the first box only).
	static $nonce_printed = false;
	if ( ! $nonce_printed ) {
		wp_nonce_field( 'wpc_save', 'wpc_nonce' );
		$nonce_printed = true;
	}

	echo '<div class="wpc-metabox wpc-metabox-' . esc_attr( $group ) . '">';

	if ( 'details' === $group ) {
		echo '<div class="wpc-grid">';
	}

	foreach ( $fields as $key => $field ) {
		if ( $field['group'] !== $group ) {
			continue;
		}
		wpc_render_field( $post->ID, $key, $field );
	}

	if ( 'details' === $group ) {
		echo '</div>';
	}

	echo '</div>';
}

/**
 * Render a single field row.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Field key.
 * @param array  $field   Field config.
 */
function wpc_render_field( $post_id, $key, $field ) {
	$value = wpc_get_meta( $post_id, $key );
	$name  = 'wpc[' . $key . ']';
	$type  = $field['type'];
	$wide  = in_array( $type, [ 'textarea', 'gallery', 'repeatable' ], true ) || 'description' === $key;

	echo '<div class="wpc-field wpc-field-' . esc_attr( $type ) . ( $wide ? ' wpc-field-wide' : '' ) . '" data-key="' . esc_attr( $key ) . '">';
	echo '<span class="wpc-label">' . esc_html( $field['label'] ) . '</span>';

	switch ( $type ) {
		case 'text':
		case 'url':
			printf(
				'<input type="%s" class="wpc-input" name="%s" value="%s" placeholder="%s" />',
				'url' === $type ? 'url' : 'text',
				esc_attr( $name ),
				esc_attr( $value ),
				esc_attr( $field['placeholder'] ?? '' )
			);
			break;

		case 'number':
			printf(
				'<input type="number" class="wpc-input wpc-input-number" name="%s" value="%s" min="%s" max="%s" step="%s" placeholder="%s" />',
				esc_attr( $name ),
				esc_attr( $value ),
				esc_attr( $field['min'] ?? 0 ),
				esc_attr( $field['max'] ?? 100 ),
				esc_attr( $field['step'] ?? 1 ),
				esc_attr( $field['placeholder'] ?? '' )
			);
			break;

		case 'textarea':
			printf(
				'<textarea class="wpc-input" name="%s" rows="%d" placeholder="%s">%s</textarea>',
				esc_attr( $name ),
				(int) ( $field['rows'] ?? 4 ),
				esc_attr( $field['placeholder'] ?? '' ),
				esc_textarea( $value )
			);
			break;

		case 'image':
			wpc_render_image_control( $name, (int) $value );
			break;

		case 'gallery':
			wpc_render_gallery_control( $name, (array) $value );
			break;

		case 'repeatable':
			wpc_render_repeatable_control( $name, (array) $value, $field );
			break;
	}

	if ( ! empty( $field['description'] ) ) {
		echo '<span class="wpc-desc">' . esc_html( $field['description'] ) . '</span>';
	}

	echo '</div>';
}

/**
 * Single-image picker control.
 *
 * @param string $name Input name.
 * @param int    $id   Attachment ID.
 */
function wpc_render_image_control( $name, $id ) {
	$src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	?>
	<div class="wpc-image-control<?php echo $src ? ' has-image' : ''; ?>">
		<input type="hidden" class="wpc-image-id" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $id ? $id : '' ); ?>" />
		<div class="wpc-image-preview">
			<?php if ( $src ) : ?>
				<img src="<?php echo esc_url( $src ); ?>" alt="" />
			<?php endif; ?>
		</div>
		<div class="wpc-image-actions">
			<button type="button" class="button wpc-image-select"><?php esc_html_e( 'Select image', 'webclyde' ); ?></button>
			<button type="button" class="button-link wpc-image-remove"><?php esc_html_e( 'Remove', 'webclyde' ); ?></button>
		</div>
	</div>
	<?php
}

/**
 * Multi-image gallery control with drag-to-reorder.
 *
 * @param string $name Input name.
 * @param int[]  $ids  Attachment IDs.
 */
function wpc_render_gallery_control( $name, $ids ) {
	?>
	<div class="wpc-gallery-control">
		<input type="hidden" class="wpc-gallery-ids" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>" />
		<div class="wpc-gallery-grid">
			<?php foreach ( $ids as $id ) :
				$src = wp_get_attachment_image_url( $id, 'thumbnail' );
				if ( ! $src ) {
					continue;
				}
				?>
				<div class="wpc-gallery-item" data-id="<?php echo esc_attr( $id ); ?>">
					<img src="<?php echo esc_url( $src ); ?>" alt="" />
					<button type="button" class="wpc-gallery-remove" aria-label="<?php esc_attr_e( 'Remove image', 'webclyde' ); ?>">&times;</button>
				</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button wpc-gallery-add"><?php esc_html_e( 'Add / edit images', 'webclyde' ); ?></button>
	</div>
	<?php
}

/**
 * Repeatable text-list control.
 *
 * @param string $name  Input name (without []).
 * @param array  $items Existing items.
 * @param array  $field Field config.
 */
function wpc_render_repeatable_control( $name, $items, $field ) {
	?>
	<div class="wpc-repeater"
		data-name="<?php echo esc_attr( $name ); ?>"
		data-placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>">
		<div class="wpc-repeater-rows">
			<?php foreach ( $items as $item ) : ?>
				<div class="wpc-repeater-row">
					<span class="wpc-repeater-handle" aria-hidden="true">&#8942;&#8942;</span>
					<input type="text" class="wpc-input" name="<?php echo esc_attr( $name ); ?>[]" value="<?php echo esc_attr( $item ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>" />
					<button type="button" class="wpc-repeater-remove button-link" aria-label="<?php esc_attr_e( 'Remove item', 'webclyde' ); ?>">&times;</button>
				</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button wpc-repeater-add"><?php echo esc_html( $field['add_label'] ?? __( '+ Add item', 'webclyde' ) ); ?></button>
	</div>
	<?php
}

/**
 * Save all case-study fields.
 *
 * @param int $post_id Post ID being saved.
 */
function wpc_save_meta( $post_id ) {
	if ( ! isset( $_POST['wpc_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['wpc_nonce'] ), 'wpc_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['wpc'] ) || ! is_array( $_POST['wpc'] ) ) {
		return;
	}

	$raw    = wp_unslash( $_POST['wpc'] );
	$fields = wpc_meta_fields();

	foreach ( $fields as $key => $field ) {
		// Gallery arrives as a comma-separated string from the hidden input.
		if ( 'gallery' === $field['type'] ) {
			$value = isset( $raw[ $key ] ) ? explode( ',', (string) $raw[ $key ] ) : [];
		} else {
			$value = $raw[ $key ] ?? ( 'repeatable' === $field['type'] ? [] : '' );
		}

		$value = wpc_sanitize_value( $field['type'], $value );

		if ( '' === $value || [] === $value ) {
			delete_post_meta( $post_id, WPC_META_PREFIX . $key );
		} else {
			update_post_meta( $post_id, WPC_META_PREFIX . $key, $value );
		}
	}
}
add_action( 'save_post_portfolio', 'wpc_save_meta' );
