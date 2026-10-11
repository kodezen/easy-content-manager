<?php
/**
 * ECM dummy-data generator — internal payload for scripts/generate-dummy-data.sh.
 *
 * Creates 3 ECM custom post types (100 posts each by default), 6 custom
 * taxonomies (3 hierarchical + 3 flat, 20 terms each) linked to those post
 * types plus ALMS/StoreEngine post types when those plugins are active, and
 * one field group covering every ECM field type, linked the same way.
 *
 * Runs in two phases, each a *separate* `wp eval-file` process (see the .sh
 * wrapper): "define" creates the post-type/taxonomy/field-group definitions
 * (as ECM's own config posts) and clears ECM's registration caches; "seed"
 * then runs in a fresh WP bootstrap where ECM's own `init` hooks have
 * already turned those definitions into real registered post types/
 * taxonomies, so posts/terms/meta can be created normally. Do not call this
 * file directly — use the .sh wrapper.
 *
 * Idempotent: every run starts by deleting only what a *previous* run of
 * this script created (matched by fixed `ecm_demo_*` slugs and a
 * `_ecm_demo_asset` marker on attachments), never touching other content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "This must be run via WP-CLI (see scripts/generate-dummy-data.sh).\n" );
	exit( 1 );
}

use EasyContentManager\Utils\Helper;
use EasyContentManager\Classes\Factory\FieldFactory;

$phase              = $args[0] ?? '';
$posts_per_type     = isset( $args[1] ) && '' !== $args[1] ? max( 1, (int) $args[1] ) : 100;
$terms_per_taxonomy = isset( $args[2] ) && '' !== $args[2] ? max( 1, (int) $args[2] ) : 20;

if ( ! in_array( $phase, [ 'define', 'seed' ], true ) ) {
	WP_CLI::error( 'Usage: wp eval-file scripts/generate-dummy-data.php <define|seed> [posts_per_type] [terms_per_taxonomy]' );
}

$field_group_slug    = 'ecm-demo-field-group';
$demo_asset_meta_key = '_ecm_demo_asset';

$demo_post_types = [
	'ecm_demo_recipe'    => [
		'singular_name' => 'Demo Recipe',
		'plural_name'   => 'Demo Recipes',
		'title_bank_a'  => [ 'Spicy', 'Creamy', 'Zesty', 'Smoky', 'Roasted', 'Grilled', 'Chilled', 'Herbed', 'Golden', 'Rustic', 'Tangy', 'Savory' ],
		'title_bank_b'  => [ 'Chicken Curry', 'Tomato Soup', 'Beef Stew', 'Veggie Stir Fry', 'Salmon Salad', 'Lentil Dal', 'Mushroom Risotto', 'Paneer Tikka', 'Shrimp Pasta', 'Pumpkin Pie', 'Chocolate Cake', 'Garlic Naan' ],
	],
	'ecm_demo_event'     => [
		'singular_name' => 'Demo Event',
		'plural_name'   => 'Demo Events',
		'title_bank_a'  => [ 'Workshop', 'Conference', 'Meetup', 'Webinar', 'Bootcamp', 'Seminar', 'Hackathon', 'Summit', 'Expo', 'Retreat' ],
		'title_bank_b'  => [ 'WordPress Development', 'Digital Marketing', 'UX Design', 'Cloud Security', 'AI & Automation', 'Startup Growth', 'Content Strategy', 'E-commerce', 'Remote Work', 'Open Source' ],
	],
	'ecm_demo_portfolio' => [
		'singular_name' => 'Demo Portfolio Item',
		'plural_name'   => 'Demo Portfolio Items',
		'title_bank_a'  => [ 'Website Redesign', 'Mobile App', 'Brand Identity', 'E-commerce Store', 'Landing Page', 'Dashboard UI', 'Marketing Campaign', 'Product Launch', 'API Integration', 'Content Hub' ],
		'title_bank_b'  => [ 'Acme Corp', 'Globex Inc', 'Initech', 'Umbrella Co', 'Stark Industries', 'Wayne Enterprises', 'Hooli', 'Soylent Corp', 'Wonka Industries', 'Cyberdyne Systems' ],
	],
];

$demo_taxonomies = [
	'ecm_demo_category'   => [ 'singular' => 'Demo Category', 'plural' => 'Demo Categories', 'hierarchical' => true ],
	'ecm_demo_department' => [ 'singular' => 'Demo Department', 'plural' => 'Demo Departments', 'hierarchical' => true ],
	'ecm_demo_region'     => [ 'singular' => 'Demo Region', 'plural' => 'Demo Regions', 'hierarchical' => true ],
	'ecm_demo_tag'        => [ 'singular' => 'Demo Tag', 'plural' => 'Demo Tags', 'hierarchical' => false ],
	'ecm_demo_label'      => [ 'singular' => 'Demo Label', 'plural' => 'Demo Labels', 'hierarchical' => false ],
	'ecm_demo_feature'    => [ 'singular' => 'Demo Feature', 'plural' => 'Demo Features', 'hierarchical' => false ],
];

// Post types contributed by other Kodezen plugins. Only linked when the
// plugin is actually active on this install — "if available" per the brief.
$external_candidates = [
	'academy_courses'      => 'ALMS',
	'academy_lessons'      => 'ALMS',
	'academy_announcement' => 'ALMS',
	'academy_certificate'  => 'ALMS',
	'storeengine_product'  => 'StoreEngine',
];

$demo_post_type_slugs = array_keys( $demo_post_types );
$demo_taxonomy_slugs  = array_keys( $demo_taxonomies );

$available_external = [];
foreach ( $external_candidates as $slug => $plugin ) {
	if ( post_type_exists( $slug ) ) {
		$available_external[ $slug ] = $plugin;
	}
}

$all_linked_post_types = array_merge( $demo_post_type_slugs, array_keys( $available_external ) );

WP_CLI::log( 'Post types available for linking: ' . implode( ', ', $all_linked_post_types ) );
if ( $available_external ) {
	$labelled = [];
	foreach ( $available_external as $slug => $plugin ) {
		$labelled[] = "{$slug} ({$plugin})";
	}
	WP_CLI::log( 'Detected active integrations: ' . implode( ', ', $labelled ) );
} else {
	WP_CLI::log( 'No ALMS/StoreEngine post types detected — linking demo taxonomies/fields to the 3 demo post types only.' );
}

// -----------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------

function ecm_demo_find_post_by_slug( string $post_type, string $slug ) {
	$posts = get_posts( [
		'post_type'      => $post_type,
		'name'           => $slug,
		'posts_per_page' => 1,
		'post_status'    => 'any',
	] );
	return $posts[0] ?? null;
}

function ecm_demo_pick_n( array $pool, int $n ): array {
	$pool = array_values( $pool );
	if ( ! $pool ) {
		return [];
	}
	$n = max( 1, min( $n, count( $pool ) ) );
	if ( $n >= count( $pool ) ) {
		return $pool;
	}
	$keys   = (array) array_rand( $pool, $n );
	$picked = [];
	foreach ( $keys as $key ) {
		$picked[] = $pool[ $key ];
	}
	return $picked;
}

function ecm_demo_sentence(): string {
	static $words    = [ 'quality', 'modern', 'reliable', 'flexible', 'creative', 'robust', 'elegant', 'intuitive', 'scalable', 'efficient', 'friendly', 'powerful', 'simple', 'engaging', 'thoughtful', 'practical', 'versatile', 'polished', 'streamlined', 'accessible' ];
	static $subjects = [ 'design', 'workflow', 'experience', 'content', 'structure', 'integration', 'performance', 'layout', 'presentation', 'approach' ];

	$picked = [];
	for ( $i = 0; $i < wp_rand( 4, 8 ); $i++ ) {
		$picked[] = $words[ array_rand( $words ) ];
	}
	return ucfirst( implode( ' ', $picked ) ) . ' ' . $subjects[ array_rand( $subjects ) ] . '.';
}

function ecm_demo_paragraph(): string {
	$sentences = [];
	for ( $i = 0; $i < wp_rand( 3, 5 ); $i++ ) {
		$sentences[] = ecm_demo_sentence();
	}
	return implode( ' ', $sentences );
}

function ecm_demo_post_title( array $def, int $index ): string {
	$bank_a = $def['title_bank_a'];
	$bank_b = $def['title_bank_b'];
	$a      = $bank_a[ $index % count( $bank_a ) ];
	$b      = $bank_b[ intdiv( $index, count( $bank_a ) ) % count( $bank_b ) ];
	return sprintf( '%s %s #%03d', $a, $b, $index + 1 );
}

function ecm_demo_term_name( string $taxonomy, int $index ): string {
	static $banks = [
		'ecm_demo_category'   => [ 'Italian', 'Mexican', 'Japanese', 'Indian', 'Mediterranean', 'Thai', 'French', 'Korean', 'Vietnamese', 'American', 'Middle Eastern', 'Caribbean', 'Ethiopian', 'Greek', 'Spanish' ],
		'ecm_demo_department' => [ 'Marketing', 'Engineering', 'Sales', 'Design', 'Support', 'Finance', 'Operations', 'HR', 'Legal', 'Product' ],
		'ecm_demo_region'     => [ 'North America', 'South America', 'Europe', 'Asia Pacific', 'Middle East', 'Africa', 'Oceania' ],
		'ecm_demo_tag'        => [ 'featured', 'trending', 'new', 'popular', 'editors-pick', 'seasonal', 'limited', 'staff-pick', 'bestseller', 'community' ],
		'ecm_demo_label'      => [ 'Draft', 'In Review', 'Approved', 'Archived', 'Needs Update', 'Verified', 'Flagged', 'Pending', 'Published', 'On Hold' ],
		'ecm_demo_feature'    => [ 'Eco-Friendly', 'Premium', 'Limited Edition', 'Handmade', 'Award-Winning', 'Best Value', 'Customer Favorite', 'New Arrival', 'Exclusive', 'Sustainable' ],
	];

	$bank  = $banks[ $taxonomy ] ?? [ 'Item' ];
	$name  = $bank[ $index % count( $bank ) ];
	$cycle = intdiv( $index, count( $bank ) );

	return $cycle > 0 ? "{$name} {$cycle}" : $name;
}

function ecm_demo_taxonomy_labels( string $singular, string $plural ): array {
	return [
		'name'                       => $plural,
		'singular_name'              => $singular,
		'menu_name'                  => $plural,
		'all_items'                  => "All {$plural}",
		'edit_item'                  => "Edit {$singular}",
		'view_item'                  => "View {$singular}",
		'update_item'                => "Update {$singular}",
		'add_new_item'               => "Add New {$singular}",
		'new_item_name'              => "New {$singular} Name",
		'parent_item'                => "Parent {$singular}",
		'parent_item_colon'          => "Parent {$singular}:",
		'search_items'               => "Search {$plural}",
		'popular_items'              => "Popular {$plural}",
		'separate_items_with_commas' => "Separate {$plural} with commas",
		'add_or_remove_items'        => "Add or remove {$plural}",
		'choose_from_most_used'      => "Choose from the most used {$plural}",
		'not_found'                  => "No {$plural} found.",
	];
}

function ecm_demo_choice_pairs(): array {
	return [
		'bronze'   => 'Bronze',
		'silver'   => 'Silver',
		'gold'     => 'Gold',
		'platinum' => 'Platinum',
	];
}

/**
 * Builds one custom_fields[] entry per ECM field type via the plugin's own
 * FieldFactory, so every field's post_meta_args/validation defaults come
 * straight from the field's own class instead of being hand-duplicated here.
 */
function ecm_demo_build_field_defs( array $demo_post_type_slugs, string $taxonomy_field_target ): array {
	// The builder UI stores choices as a raw "value : Label" newline-delimited
	// string (parsed client-side with String.split()), not a PHP array — see
	// SelectFieldGeneral/index.js and FieldRenderer.getSerializedChoices().
	// ecm_demo_choice_pairs() below is the array form used to generate values.
	$choices = implode(
		"\n",
		array_map(
			static fn( $value, $label ) => "{$value} : {$label}",
			array_keys( ecm_demo_choice_pairs() ),
			ecm_demo_choice_pairs()
		)
	);

	$sub_field_defs = [
		[ 'type' => 'text-field', 'key' => 'demo_sub_item_name', 'label' => 'Item Name' ],
		[ 'type' => 'number-field', 'key' => 'demo_sub_item_qty', 'label' => 'Item Quantity' ],
	];
	$sub_fields = [];
	foreach ( $sub_field_defs as $def ) {
		$field = FieldFactory::make( $def['type'], '', $def['key'], 'post' );
		if ( ! $field ) {
			continue;
		}
		$sub_data = $field->get( false, [ 'general' => [ 'field_label' => $def['label'], 'field_name' => $def['key'] ] ] );
		$sub_data['post_meta_args']['sanitize_callback'] = null;
		$sub_data['post_meta_args']['auth_callback']      = null;
		$sub_fields[] = $sub_data;
	}

	$definitions = [
		[ 'type' => 'text-field', 'key' => 'demo_headline', 'label' => 'Headline' ],
		[ 'type' => 'textarea-field', 'key' => 'demo_summary', 'label' => 'Summary' ],
		[ 'type' => 'rich-text-field', 'key' => 'demo_rich_text', 'label' => 'Rich Text' ],
		[ 'type' => 'number-field', 'key' => 'demo_number', 'label' => 'Number' ],
		[ 'type' => 'range-field', 'key' => 'demo_range', 'label' => 'Range' ],
		[ 'type' => 'email-field', 'key' => 'demo_email', 'label' => 'Email' ],
		[ 'type' => 'url-field', 'key' => 'demo_url', 'label' => 'URL' ],
		[ 'type' => 'password-field', 'key' => 'demo_password', 'label' => 'Password' ],
		[ 'type' => 'color-field', 'key' => 'demo_color', 'label' => 'Color' ],
		[ 'type' => 'icon-field', 'key' => 'demo_icon', 'label' => 'Icon' ],
		[ 'type' => 'true-false-field', 'key' => 'demo_true_false', 'label' => 'True/False' ],
		[ 'type' => 'switch-field', 'key' => 'demo_switch', 'label' => 'Switch' ],
		[ 'type' => 'date-field', 'key' => 'demo_date', 'label' => 'Date' ],
		[ 'type' => 'date-time-field', 'key' => 'demo_date_time', 'label' => 'Date Time' ],
		[ 'type' => 'time-field', 'key' => 'demo_time', 'label' => 'Time' ],
		[ 'type' => 'select-field', 'key' => 'demo_select', 'label' => 'Select', 'general' => [ 'choices' => $choices ] ],
		[ 'type' => 'checkbox-field', 'key' => 'demo_checkbox', 'label' => 'Checkbox', 'general' => [ 'choices' => $choices ] ],
		[ 'type' => 'radio-field', 'key' => 'demo_radio', 'label' => 'Radio', 'general' => [ 'choices' => $choices ] ],
		[ 'type' => 'button-group-field', 'key' => 'demo_button_group', 'label' => 'Button Group', 'general' => [ 'choices' => $choices ] ],
		[ 'type' => 'image-field', 'key' => 'demo_image', 'label' => 'Image' ],
		[ 'type' => 'gallery-field', 'key' => 'demo_gallery', 'label' => 'Gallery' ],
		[ 'type' => 'file-field', 'key' => 'demo_file', 'label' => 'File' ],
		[ 'type' => 'video-field', 'key' => 'demo_video', 'label' => 'Video' ],
		[ 'type' => 'oembed-field', 'key' => 'demo_oembed', 'label' => 'oEmbed' ],
		[ 'type' => 'link-field', 'key' => 'demo_link', 'label' => 'Link' ],
		[ 'type' => 'map-field', 'key' => 'demo_map', 'label' => 'Map' ],
		[ 'type' => 'relationship-field', 'key' => 'demo_relationship', 'label' => 'Relationship', 'general' => [ 'post_type' => $demo_post_type_slugs ] ],
		[ 'type' => 'post-object-field', 'key' => 'demo_post_object', 'label' => 'Post Object', 'general' => [ 'added_post_types' => $demo_post_type_slugs, 'multiple' => true, 'return_format' => 'id' ] ],
		// Only element [0] of `post_type` is ever read (RenderPageLinkFiled.js) —
		// it fetches every post of that one post type, so the field is scoped to
		// the first demo post type and its value must be a matching post's ID.
		[ 'type' => 'page-link-field', 'key' => 'demo_page_link', 'label' => 'Page Link', 'general' => [ 'post_type' => [ $demo_post_type_slugs[0] ] ] ],
		[ 'type' => 'taxonomy-field', 'key' => 'demo_taxonomy', 'label' => 'Taxonomy', 'general' => [ 'taxonomy' => $taxonomy_field_target ] ],
		[ 'type' => 'user-field', 'key' => 'demo_user', 'label' => 'User', 'general' => [ 'return_format' => 'id' ] ],
		[ 'type' => 'group-field', 'key' => 'demo_group', 'label' => 'Group', 'general' => [ 'sub_fields' => $sub_fields ] ],
		[ 'type' => 'repeater-field', 'key' => 'demo_repeater', 'label' => 'Repeater', 'general' => [ 'sub_fields' => $sub_fields ] ],
		[ 'type' => 'message-field', 'key' => 'demo_message', 'label' => 'Message', 'general' => [ 'field_default_value' => 'This is a read-only message field used to spot-check informational-field rendering.' ] ],
		[ 'type' => 'accordion-field', 'key' => 'demo_accordion', 'label' => 'Accordion' ],
		[ 'type' => 'tab-field', 'key' => 'demo_tab', 'label' => 'Tab' ],
	];

	$custom_fields = [];
	foreach ( $definitions as $def ) {
		$field = FieldFactory::make( $def['type'], '', $def['key'], 'post' );
		if ( ! $field ) {
			WP_CLI::log( "    (skipped {$def['type']} — unavailable, e.g. requires the Pro license)" );
			continue;
		}
		$overrides = [ 'general' => array_merge( [ 'field_label' => $def['label'], 'field_name' => $def['key'] ], $def['general'] ?? [] ) ];
		if ( ! empty( $def['validation'] ) ) {
			$overrides['validation'] = $def['validation'];
		}
		$data = $field->get( false, $overrides );

		// A few field classes (checkbox/radio) set a real Closure here, which is
		// fine at registration time but fatals maybe_serialize(). The JS-authored
		// data this normally comes from can never contain one either way, and
		// RegisterFields::register_fields()/register_field_group() always rebuild
		// the actual callback from field_type at registration time regardless of
		// what's stored — see includes/features/register-fields.php.
		$data['post_meta_args']['sanitize_callback'] = null;
		$data['post_meta_args']['auth_callback']      = null;

		$custom_fields[] = $data;
	}

	return $custom_fields;
}

function ecm_demo_create_terms( string $taxonomy, bool $hierarchical, int $count ): array {
	$term_ids = [];

	if ( ! $hierarchical ) {
		for ( $i = 0; $i < $count; $i++ ) {
			$term = wp_insert_term( ecm_demo_term_name( $taxonomy, $i ), $taxonomy );
			if ( ! is_wp_error( $term ) ) {
				$term_ids[] = $term['term_id'];
			}
		}
		return $term_ids;
	}

	$parent_count = max( 1, (int) round( $count * 0.25 ) );
	$parent_ids   = [];
	for ( $p = 0; $p < $parent_count; $p++ ) {
		$term = wp_insert_term( ecm_demo_term_name( $taxonomy, $p ), $taxonomy );
		if ( ! is_wp_error( $term ) ) {
			$parent_ids[] = $term['term_id'];
			$term_ids[]   = $term['term_id'];
		}
	}

	if ( ! $parent_ids ) {
		return $term_ids;
	}

	$remaining = $count - count( $parent_ids );
	for ( $c = 0; $c < $remaining; $c++ ) {
		$parent_id = $parent_ids[ $c % count( $parent_ids ) ];
		$cycle     = intdiv( $c, count( $parent_ids ) ) + 1;
		$name      = ecm_demo_term_name( $taxonomy, $parent_count + $c ) . ' ' . $cycle;
		$term      = wp_insert_term( $name, $taxonomy, [ 'parent' => $parent_id ] );
		if ( ! is_wp_error( $term ) ) {
			$term_ids[] = $term['term_id'];
		}
	}

	return $term_ids;
}

function ecm_demo_create_image_attachment( int $index, string $demo_asset_meta_key ): int {
	if ( ! function_exists( 'imagecreatetruecolor' ) ) {
		return 0;
	}

	$upload_dir = wp_upload_dir();
	if ( ! empty( $upload_dir['error'] ) ) {
		return 0;
	}

	$colors = [ [ 255, 99, 71 ], [ 65, 105, 225 ], [ 60, 179, 113 ], [ 238, 130, 238 ], [ 255, 165, 0 ], [ 70, 130, 180 ], [ 219, 112, 147 ], [ 46, 139, 87 ] ];
	[ $r, $g, $b ] = $colors[ $index % count( $colors ) ];

	$width  = 640;
	$height = 480;
	$image  = imagecreatetruecolor( $width, $height );
	$bg     = imagecolorallocate( $image, $r, $g, $b );
	imagefill( $image, 0, 0, $bg );
	$white = imagecolorallocate( $image, 255, 255, 255 );
	$label = 'ECM Demo Image ' . ( $index + 1 );
	imagestring( $image, 5, 20, (int) ( $height / 2 ) - 8, $label, $white );

	$filename  = 'ecm-demo-image-' . ( $index + 1 ) . '.png';
	$file_path = trailingslashit( $upload_dir['path'] ) . $filename;
	imagepng( $image, $file_path );
	imagedestroy( $image );

	return ecm_demo_finish_attachment( $file_path, $filename, $label, $demo_asset_meta_key );
}

function ecm_demo_create_file_attachment( int $index, string $demo_asset_meta_key ): int {
	$upload_dir = wp_upload_dir();
	if ( ! empty( $upload_dir['error'] ) ) {
		return 0;
	}

	$filename  = 'ecm-demo-file-' . ( $index + 1 ) . '.txt';
	$file_path = trailingslashit( $upload_dir['path'] ) . $filename;
	file_put_contents( $file_path, "ECM demo file #" . ( $index + 1 ) . "\nGenerated by scripts/generate-dummy-data.sh for local testing.\n" );

	return ecm_demo_finish_attachment( $file_path, $filename, 'ECM Demo File ' . ( $index + 1 ), $demo_asset_meta_key );
}

function ecm_demo_finish_attachment( string $file_path, string $filename, string $title, string $demo_asset_meta_key ): int {
	$filetype      = wp_check_filetype( $filename, null );
	$attachment_id = wp_insert_attachment( [
		'post_mime_type' => $filetype['type'],
		'post_title'     => $title,
		'post_content'   => '',
		'post_status'    => 'inherit',
	], $file_path );

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file_path ) );
	update_post_meta( $attachment_id, $demo_asset_meta_key, 1 );

	return $attachment_id;
}

/**
 * Value shapes below are matched against the actual React renderer for each
 * field (dev_contentManager/components/RenderField/Render*), not just the
 * PHP field class's declared `type` — several fields store a shape that
 * differs from what their `type`/`rest_schema` alone would suggest (e.g.
 * `choices` is a raw string, checkbox/radio/select/user/post-object all use
 * different per-item shapes). relationship-field has no renderer in this
 * version of the post-edit screen (absent from FieldRenderer's field-type
 * map), so its value is never read there — any array is fine.
 */
function ecm_demo_field_value( string $field_type, array $field_entry, array $ctx ) {
	$general = $field_entry['general'] ?? [];

	switch ( $field_type ) {
		case 'text-field':
			return ecm_demo_sentence();
		case 'textarea-field':
			return ecm_demo_paragraph();
		case 'rich-text-field':
			return '<p>' . ecm_demo_paragraph() . '</p><p><strong>' . ecm_demo_sentence() . '</strong></p>';
		case 'number-field':
			return wp_rand( 1, 500 );
		case 'range-field':
			return wp_rand( 0, 100 );
		case 'email-field':
			return 'demo.user' . $ctx['index'] . '@example.com';
		case 'url-field':
			return 'https://example.com/demo/' . $ctx['index'];
		case 'password-field':
			return wp_generate_password( 12, false );
		case 'color-field':
			return sprintf( '#%06x', wp_rand( 0, 0xFFFFFF ) );
		case 'icon-field':
			// Bare Dashicon slug — DASHICONS in RenderIconField/helper.js has no
			// "dashicons-" prefix, and the value is compared against it directly.
			return [ 'star-filled', 'admin-post', 'cart', 'calendar-alt', 'camera' ][ $ctx['index'] % 5 ];
		case 'true-false-field':
		case 'switch-field':
			return (bool) ( $ctx['index'] % 2 );
		case 'date-field':
			return gmdate( 'Y-m-d', strtotime( '-' . wp_rand( 0, 180 ) . ' days' ) );
		case 'date-time-field':
			return gmdate( 'Y-m-d H:i:s', strtotime( '-' . wp_rand( 0, 180 ) . ' days' ) );
		case 'time-field':
			return sprintf( '%02d:%02d', wp_rand( 0, 23 ), wp_rand( 0, 59 ) );
		case 'select-field':
		case 'button-group-field':
			// Single choice, stored as the full {value,label} option object.
			$pairs = ecm_demo_choice_pairs();
			$value = array_rand( $pairs );
			return [ 'value' => $value, 'label' => $pairs[ $value ] ];
		case 'radio-field':
			// RenderRadioFiled stores only {value}, no label.
			return [ 'value' => array_rand( ecm_demo_choice_pairs() ) ];
		case 'checkbox-field':
			// RenderCheckboxFiled stores an array of {value, isChecked}.
			$picked = ecm_demo_pick_n( array_keys( ecm_demo_choice_pairs() ), 2 );
			return array_map( static fn( $value ) => [ 'value' => $value, 'isChecked' => true ], $picked );
		case 'image-field':
			return ecm_demo_pick_n( $ctx['image_pool'], 1 );
		case 'gallery-field':
			return ecm_demo_pick_n( $ctx['image_pool'], 3 );
		case 'file-field':
			$picked = ecm_demo_pick_n( $ctx['file_pool'], 1 );
			return $picked ? $picked[0] : 0;
		case 'video-field':
		case 'oembed-field':
			return 'https://www.youtube.com/watch?v=jNQXAC9IVRw';
		case 'link-field':
			return [ 'url' => 'https://example.com', 'title' => 'Example Link', 'target' => '_blank' ];
		case 'map-field':
			return [ 'lat' => '23.8103', 'lng' => '90.4125', 'zoomLevel' => 14 ];
		case 'relationship-field':
			return ecm_demo_pick_n( $ctx['related_pool'], 2 );
		case 'post-object-field':
			// return_format 'id' (forced in the field def) → array of {id}.
			$picked = ecm_demo_pick_n( $ctx['related_pool'], 2 );
			return array_map( static fn( $post_id ) => [ 'id' => $post_id ], $picked );
		case 'page-link-field':
			// Left unset: RenderPageLinkFiled.js stores/reads a bare numeric
			// post ID, but this field type is registered with esc_url_raw as
			// its sanitize_callback (get_sanitizer_for_field_type() in
			// register-fields.php), which mangles a bare ID into "http://<id>"
			// on every update_post_meta() write — a plugin-level mismatch
			// between the renderer and the registered sanitizer, not something
			// a stored value can route around. Left null so the field just
			// shows its normal empty "Select…" state instead of corrupt data.
			return null;
		case 'taxonomy-field':
			$taxonomy = $general['taxonomy'] ?? '';
			$pool     = $ctx['term_pool_by_taxonomy'][ $taxonomy ] ?? [];
			$picked   = ecm_demo_pick_n( $pool, 1 );
			if ( ! $picked ) {
				return [];
			}
			$term = get_term( $picked[0], $taxonomy );
			return ( $term && ! is_wp_error( $term ) ) ? [ [ 'id' => $term->term_id, 'name' => $term->name, 'slug' => $term->slug ] ] : [];
		case 'user-field':
			// return_format 'id' (forced in the field def) → array of {ID}.
			return [ [ 'ID' => $ctx['user_id'] ] ];
		case 'group-field':
			return [ 'demo_sub_item_name' => 'Item ' . ( $ctx['index'] + 1 ), 'demo_sub_item_qty' => wp_rand( 1, 20 ) ];
		case 'repeater-field':
			$rows = [];
			for ( $r = 0; $r < wp_rand( 2, 3 ); $r++ ) {
				$rows[] = [ 'demo_sub_item_name' => 'Row ' . ( $r + 1 ), 'demo_sub_item_qty' => wp_rand( 1, 20 ) ];
			}
			return $rows;
		case 'message-field':
		case 'accordion-field':
		case 'tab-field':
			return null;
		default:
			return null;
	}
}

function ecm_demo_cleanup( array $demo_post_type_slugs, array $demo_taxonomy_slugs, string $field_group_slug, string $demo_asset_meta_key ): void {
	WP_CLI::log( 'Cleaning up any previous demo data created by this script...' );

	foreach ( $demo_taxonomy_slugs as $tax_slug ) {
		if ( taxonomy_exists( $tax_slug ) ) {
			$terms = get_terms( [ 'taxonomy' => $tax_slug, 'hide_empty' => false, 'fields' => 'ids' ] );
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term_id ) {
					wp_delete_term( $term_id, $tax_slug );
				}
			}
		}
		$tax_post = ecm_demo_find_post_by_slug( Helper::ECM_TAXONOMY_POST_TYPE_NAME, $tax_slug );
		if ( $tax_post ) {
			wp_delete_post( $tax_post->ID, true );
		}
	}

	foreach ( $demo_post_type_slugs as $pt_slug ) {
		if ( post_type_exists( $pt_slug ) ) {
			$existing = get_posts( [ 'post_type' => $pt_slug, 'posts_per_page' => -1, 'post_status' => 'any', 'fields' => 'ids' ] );
			foreach ( $existing as $post_id ) {
				wp_delete_post( $post_id, true );
			}
		}
		$pt_post = ecm_demo_find_post_by_slug( Helper::ECM_POST_TYPE_NAME, $pt_slug );
		if ( $pt_post ) {
			wp_delete_post( $pt_post->ID, true );
		}
	}

	$field_group_post = ecm_demo_find_post_by_slug( Helper::ECM_FIELD_GROUP_POST_TYPE_NAME, $field_group_slug );
	if ( $field_group_post ) {
		wp_delete_post( $field_group_post->ID, true );
	}

	$assets = get_posts( [
		'post_type'      => 'attachment',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => $demo_asset_meta_key,
	] );
	foreach ( $assets as $attachment_id ) {
		wp_delete_attachment( $attachment_id, true );
	}

	delete_transient( 'ecm_cached_prepared_post_types' );
	delete_transient( 'ecm_cached_prepared_custom_fields' );
	delete_transient( 'ecm_cached_prepared_field_groups' );
	delete_transient( 'ecm_priority_meta_slugs' );

	WP_CLI::log( '  done.' );
}

// -----------------------------------------------------------------------
// Phase: define
// -----------------------------------------------------------------------

if ( 'define' === $phase ) {
	ecm_demo_cleanup( $demo_post_type_slugs, $demo_taxonomy_slugs, $field_group_slug, $demo_asset_meta_key );

	WP_CLI::log( 'Creating ' . count( $demo_post_types ) . ' demo post types...' );
	foreach ( $demo_post_types as $slug => $def ) {
		$post_content = Helper::get_prepared_post_type_data( [
			'singular_name' => $def['singular_name'],
			'post_slug'     => $slug,
			'plural_name'   => $def['plural_name'],
			'is_public'     => true,
			'hierarchical'  => false,
		] );

		$post_id = wp_insert_post( [
			'post_title'   => $def['singular_name'],
			'post_name'    => $slug,
			'post_content' => maybe_serialize( $post_content ),
			'post_status'  => 'publish',
			'post_type'    => Helper::ECM_POST_TYPE_NAME,
		], true );

		if ( is_wp_error( $post_id ) ) {
			WP_CLI::error( "Failed to create post type {$slug}: " . $post_id->get_error_message() );
		}
		WP_CLI::log( "  + {$def['singular_name']} ({$slug})" );
	}

	WP_CLI::log( 'Creating ' . count( $demo_taxonomies ) . ' demo taxonomies...' );
	$object_type = array_map( static fn( $slug ) => [ 'post_type' => $slug ], $all_linked_post_types );

	foreach ( $demo_taxonomies as $slug => $def ) {
		$post_content = [
			'taxonomy'              => $def['singular'],
			'taxonomy_slug'         => $slug,
			'active'                => true,
			'ui_type'               => 'modern',
			'advanced_configuration' => true,
			'object_type'           => $object_type,
			'labels'                => ecm_demo_taxonomy_labels( $def['singular'], $def['plural'] ),
			'description'           => '',
			'public'                => true,
			'publicly_queryable'    => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'show_in_nav_menus'     => true,
			'show_tagcloud'         => true,
			'show_in_quick_edit'    => true,
			'show_admin_column'     => true,
			'meta_box_cb'           => null,
			'meta_box_sanitize_cb'  => null,
			'hierarchical'          => $def['hierarchical'],
			'update_count_callback' => null,
			'sort'                  => false,
			'args'                  => [],
			'capabilities_enabled'  => false,
			'capabilities'          => [],
			'show_in_rest'          => true,
			'rest_base'             => $slug,
			'rest_namespace'        => 'wp/v2',
			'rest_controller_class' => 'WP_REST_Terms_Controller',
			'query_var'             => $slug,
			'query_var_name'        => '',
			'rewrite'               => true,
			'custom_fields'         => [],
		];

		$post_id = wp_insert_post( [
			'post_title'   => $slug,
			'post_name'    => $slug,
			'post_content' => maybe_serialize( $post_content ),
			'post_status'  => 'publish',
			'post_type'    => Helper::ECM_TAXONOMY_POST_TYPE_NAME,
		], true );

		if ( is_wp_error( $post_id ) ) {
			WP_CLI::error( "Failed to create taxonomy {$slug}: " . $post_id->get_error_message() );
		}
		WP_CLI::log( "  + {$def['plural']} ({$slug}, " . ( $def['hierarchical'] ? 'hierarchical' : 'flat' ) . ')' );
	}

	// Mirror what the taxonomy AJAX handler does: keep each demo post type's
	// own `taxonomies` list in sync so its edit screen also lists them.
	foreach ( $demo_post_type_slugs as $pt_slug ) {
		$pt_post = ecm_demo_find_post_by_slug( Helper::ECM_POST_TYPE_NAME, $pt_slug );
		if ( ! $pt_post ) {
			continue;
		}
		$content                 = maybe_unserialize( $pt_post->post_content );
		$content['taxonomies']   = $demo_taxonomy_slugs;
		wp_update_post( [ 'ID' => $pt_post->ID, 'post_content' => maybe_serialize( $content ) ] );
	}

	WP_CLI::log( 'Creating the demo field group (one field per ECM field type)...' );
	$custom_fields = ecm_demo_build_field_defs( $demo_post_type_slugs, 'ecm_demo_tag' );

	$location = [];
	foreach ( $all_linked_post_types as $pt_slug ) {
		$location[] = [ [
			'logic_type'    => 'and',
			'selected_page' => $pt_slug,
			'selected_type' => 'post_type',
			'variant'       => 'is-equal-to',
		] ];
	}

	$field_group_content = [
		'location'      => $location,
		'custom_fields' => $custom_fields,
		'post_slug'     => $field_group_slug,
	];

	$field_group_id = wp_insert_post( [
		'post_title'   => 'ECM Demo Field Group (All Field Types)',
		'post_name'    => $field_group_slug,
		'post_content' => maybe_serialize( $field_group_content ),
		'post_status'  => 'publish',
		'post_type'    => Helper::ECM_FIELD_GROUP_POST_TYPE_NAME,
	], true );

	if ( is_wp_error( $field_group_id ) ) {
		WP_CLI::error( 'Failed to create field group: ' . $field_group_id->get_error_message() );
	}

	update_post_meta( $field_group_id, 'ecm_field_group_location', maybe_serialize( Helper::get_post_types_from_location_array( $location ) ) );

	WP_CLI::log( '  + ' . count( $custom_fields ) . ' fields, linked to: ' . implode( ', ', $all_linked_post_types ) );

	delete_transient( 'ecm_cached_prepared_post_types' );
	delete_transient( 'ecm_cached_prepared_custom_fields' );
	delete_transient( 'ecm_cached_prepared_field_groups' );
	delete_transient( 'ecm_priority_meta_slugs' );
	update_option( 'ecm_flush_rewrite_rules', true );
	update_option( 'ecm_flush_taxonomy_rules', true );

	WP_CLI::success( 'Definitions created. Now run the "seed" phase (a fresh WP bootstrap is required for the new post types/taxonomies to register).' );
}

// -----------------------------------------------------------------------
// Phase: seed
// -----------------------------------------------------------------------

if ( 'seed' === $phase ) {
	$missing = [];
	foreach ( $demo_post_type_slugs as $slug ) {
		if ( ! post_type_exists( $slug ) ) {
			$missing[] = "post type {$slug}";
		}
	}
	foreach ( $demo_taxonomy_slugs as $slug ) {
		if ( ! taxonomy_exists( $slug ) ) {
			$missing[] = "taxonomy {$slug}";
		}
	}
	if ( $missing ) {
		WP_CLI::error( 'Not registered after the define phase: ' . implode( ', ', $missing ) . '. Run the define phase again.' );
	}

	$field_group_post = ecm_demo_find_post_by_slug( Helper::ECM_FIELD_GROUP_POST_TYPE_NAME, $field_group_slug );
	if ( ! $field_group_post ) {
		WP_CLI::error( 'Demo field group not found — run the define phase first.' );
	}
	$custom_fields = maybe_unserialize( $field_group_post->post_content )['custom_fields'] ?? [];

	$users   = get_users( [ 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC', 'fields' => 'ID' ] );
	$user_id = $users ? (int) $users[0] : 1;

	WP_CLI::log( 'Creating demo media assets...' );
	$image_pool = [];
	$file_pool  = [];
	if ( function_exists( 'imagecreatetruecolor' ) ) {
		for ( $i = 0; $i < 10; $i++ ) {
			$attachment_id = ecm_demo_create_image_attachment( $i, $demo_asset_meta_key );
			if ( $attachment_id ) {
				$image_pool[] = $attachment_id;
			}
		}
	} else {
		WP_CLI::warning( 'GD extension not available — image/gallery fields will be left empty.' );
	}
	for ( $i = 0; $i < 4; $i++ ) {
		$attachment_id = ecm_demo_create_file_attachment( $i, $demo_asset_meta_key );
		if ( $attachment_id ) {
			$file_pool[] = $attachment_id;
		}
	}
	WP_CLI::log( '  + ' . count( $image_pool ) . ' images, ' . count( $file_pool ) . ' files' );

	WP_CLI::log( "Creating {$terms_per_taxonomy} terms per taxonomy..." );
	$term_pool_by_taxonomy = [];
	foreach ( $demo_taxonomies as $slug => $def ) {
		$term_pool_by_taxonomy[ $slug ] = ecm_demo_create_terms( $slug, $def['hierarchical'], $terms_per_taxonomy );
		WP_CLI::log( "  + {$slug}: " . count( $term_pool_by_taxonomy[ $slug ] ) . ' terms' );
	}

	WP_CLI::log( "Creating {$posts_per_type} posts per post type..." );
	$related_pool = [];
	$ctx_base     = [
		'image_pool'            => $image_pool,
		'file_pool'             => $file_pool,
		'user_id'               => $user_id,
		'term_pool_by_taxonomy' => $term_pool_by_taxonomy,
	];

	foreach ( $demo_post_types as $pt_slug => $def ) {
		for ( $i = 0; $i < $posts_per_type; $i++ ) {
			$post_id = wp_insert_post( [
				'post_title'     => ecm_demo_post_title( $def, $i ),
				'post_content'   => ecm_demo_paragraph() . "\n\n" . ecm_demo_paragraph(),
				'post_excerpt'   => ecm_demo_sentence(),
				'post_status'    => 'publish',
				'post_type'      => $pt_slug,
				'comment_status' => 'open',
			], true );

			if ( is_wp_error( $post_id ) ) {
				WP_CLI::warning( "Failed to create a {$pt_slug} post: " . $post_id->get_error_message() );
				continue;
			}

			if ( $image_pool ) {
				set_post_thumbnail( $post_id, $image_pool[ array_rand( $image_pool ) ] );
			}

			foreach ( $demo_taxonomy_slugs as $tax_slug ) {
				$pool = $term_pool_by_taxonomy[ $tax_slug ];
				if ( ! $pool ) {
					continue;
				}
				wp_set_object_terms( $post_id, ecm_demo_pick_n( $pool, wp_rand( 1, 3 ) ), $tax_slug, false );
			}

			$ctx = $ctx_base + [ 'index' => $i, 'related_pool' => $related_pool ];
			foreach ( $custom_fields as $field_entry ) {
				$field_type = $field_entry['general']['field_type'] ?? '';
				$meta_key   = $field_entry['meta_key'] ?? '';
				if ( ! $meta_key ) {
					continue;
				}
				$value = ecm_demo_field_value( $field_type, $field_entry, $ctx );
				if ( null === $value ) {
					continue;
				}
				update_post_meta( $post_id, $meta_key, $value );
			}

			$related_pool[] = $post_id;
		}
		WP_CLI::log( "  + {$def['plural_name']} ({$pt_slug}): {$posts_per_type} posts" );
	}

	$total_terms = array_sum( array_map( 'count', $term_pool_by_taxonomy ) );
	WP_CLI::success(
		sprintf(
			'Seeded %d posts across %d post types, %d terms across %d taxonomies, %d custom fields.',
			$posts_per_type * count( $demo_post_types ),
			count( $demo_post_types ),
			$total_terms,
			count( $demo_taxonomies ),
			count( $custom_fields )
		)
	);

	foreach ( $demo_post_type_slugs as $pt_slug ) {
		WP_CLI::log( '  ' . admin_url( "edit.php?post_type={$pt_slug}" ) );
	}
}
