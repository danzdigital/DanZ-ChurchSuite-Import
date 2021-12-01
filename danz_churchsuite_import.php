<?php

/**
 * @wordpress-plugin
 * Plugin Name:       DanZ - ChurchSuite Import
 * Plugin URI:        https://danzdigitaldesigns.co.uk/danz_churchsuite_import
 * Description:       This plugin imports ChurchSuite Events into the Events Post Type.
 * Version:           1.3.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            DanZ Digital Designs
 * Author URI:        https://danzdigitaldesigns.co.uk
 * Text Domain:       danz-churchsuite-events
 * GitHub Plugin URI: https://github.com/danzdigital/DanZ-ChurchSuite-Import
 * Primary Branch: main
 */


if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Register the "churchsuite_events" custom post type
 */
function danz_churchsuite_events_setup() {

	$post_labels = array(
		'name'                  => __('ChurchSuite Events', 'Post Type General Name', 'churchsuite_events'),
		'singular_name'         => __('ChurchSuite Event', 'Post Type Singular Name', 'churchsuite_events'),
		'menu_name'             => __('ChurchSuite Events', 'churchsuite_events'),
		'name_admin_bar'        => __('ChurchSuite Events', 'churchsuite_events'),
		'archives'              => __('ChurchSuite Event Archives', 'churchsuite_events'),
		'attributes'            => __('ChurchSuite Event Attributes', 'churchsuite_events'),
		'parent_item_colon'     => __('ChurchSuite Event:', 'churchsuite_events'),
		'all_items'             => __('All ChurchSuite Events', 'churchsuite_events'),
		'add_new_item'          => __('Add New ChurchSuite Event', 'churchsuite_events'),
		'add_new'               => __('Add New', 'churchsuite_events'),
		'new_item'              => __('New ChurchSuite Event', 'churchsuite_events'),
		'edit_item'             => __('Edit ChurchSuite Event', 'churchsuite_events'),
		'update_item'           => __('Update ChurchSuite Event', 'churchsuite_events'),
		'view_item'             => __('View ChurchSuite Event', 'churchsuite_events'),
		'view_items'            => __('View ChurchSuite Events', 'churchsuite_events'),
		'search_items'          => __('Search ChurchSuite Event', 'churchsuite_events'),
		'not_found'             => __('Not found', 'churchsuite_events'),
		'not_found_in_trash'    => __('Not found in Trash', 'churchsuite_events'),
		'featured_image'        => __('Featured Image', 'churchsuite_events'),
		'set_featured_image'    => __('Set featured image', 'churchsuite_events'),
		'remove_featured_image' => __('Remove featured image', 'churchsuite_events'),
		'use_featured_image'    => __('Use as featured image', 'churchsuite_events'),
		'insert_into_item'      => __('Insert into ChurchSuite Event', 'churchsuite_events'),
		'uploaded_to_this_item' => __('Uploaded to this ChurchSuite Event', 'churchsuite_events'),
		'items_list'            => __('ChurchSuite Events list', 'churchsuite_events'),
		'items_list_navigation' => __('Items list navigation', 'churchsuite_events'),
		'filter_items_list'     => __('Filter ChurchSuite Events', 'churchsuite_events'),
	);
	$post_args = array(
		'label'                 => __('ChurchSuite Event', 'churchsuite_events'),
		'description'           => __('All ChurchSuite Events imported from API', 'churchsuite_events'),
		'labels'                => $post_labels,
		'supports'              => array('title', 'editor', 'thumbnail', 'comments', 'custom-fields'),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-calendar-alt',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'show_in_rest'          => true,
		'rest_base'             => 'event',
	);
	register_post_type('churchsuite_events', $post_args);

} 
add_action( 'init', 'danz_churchsuite_events_setup' );
 
 
/**
 * Activate the plugin.
 */
function danz_churchsuite_events_activate() { 
    // Trigger our function that registers the custom post type plugin.
    danz_churchsuite_events_setup(); 
    // Clear the permalinks after the post type has been registered.
    flush_rewrite_rules(); 
}
register_activation_hook( __FILE__, 'danz_churchsuite_events_activate' );

/**
 * Deactivation hook.
 */
function danz_churchsuite_events_deactivate() {
    // Unregister the post type, so the rules are no longer in memory.
    unregister_post_type( 'churchsuite_events' );
    // Clear the permalinks to remove our post type's rules from the database.
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'danz_churchsuite_events_deactivate' );



// ChurchSuite Events Admin Dashboard

class ChurchSuiteEvents {
	private $churchsuite_events_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'churchsuite_events_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'churchsuite_events_page_init' ) );
	}

	public function churchsuite_events_add_plugin_page() {
		add_options_page(
			'ChurchSuite Events', // page_title
			'ChurchSuite Events', // menu_title
			'manage_options', // capability
			'churchsuite-events', // menu_slug
			array( $this, 'churchsuite_events_create_admin_page' ) // function
		);
	}

	public function churchsuite_events_create_admin_page() {
		$this->churchsuite_events_options = get_option( 'churchsuite_events_option_name' ); ?>

		<div class="wrap">
			<h2>ChurchSuite Events</h2>
			<p>All ChurchSuite Settings</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'churchsuite_events_option_group' );
					do_settings_sections( 'churchsuite-events-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function churchsuite_events_page_init() {
		register_setting(
			'churchsuite_events_option_group', // option_group
			'churchsuite_events_option_name', // option_name
			array( $this, 'churchsuite_events_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'churchsuite_events_setting_section', // id
			'Settings', // title
			array( $this, 'churchsuite_events_section_info' ), // callback
			'churchsuite-events-admin' // page
		);

		add_settings_field(
			'churchsuite_account_id_0', // id
			'ChurchSuite Account ID', // title
			array( $this, 'churchsuite_account_id_0_callback' ), // callback
			'churchsuite-events-admin', // page
			'churchsuite_events_setting_section' // section
		);
	}

	public function churchsuite_events_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['churchsuite_account_id_0'] ) ) {
			$sanitary_values['churchsuite_account_id_0'] = sanitize_text_field( $input['churchsuite_account_id_0'] );
		}

		return $sanitary_values;
	}

	public function churchsuite_events_section_info() {
		
	}

	public function churchsuite_account_id_0_callback() {
		printf(
			'<input class="regular-text" type="text" name="churchsuite_events_option_name[churchsuite_account_id_0]" id="churchsuite_account_id_0" value="%s">',
			isset( $this->churchsuite_events_options['churchsuite_account_id_0'] ) ? esc_attr( $this->churchsuite_events_options['churchsuite_account_id_0']) : ''
		);
	}

}
if ( is_admin() )
	$churchsuite_events = new ChurchSuiteEvents();

/* 
 * Retrieve this value with:
 * $churchsuite_events_options = get_option( 'churchsuite_events_option_name' ); // Array of All Options
 * $churchsuite_account_id_0 = $churchsuite_events_options['churchsuite_account_id_0']; // ChurchSuite Account ID
 */

/*
 function churchsuite_handle_save()
{

	// Get the options that were sent
	$url = (!empty($_POST["cs_acc_id"])) ? $_POST["cs_acc_id"] : NULL;

	// Validation would go here

	// Update the values
	update_option("cs_acc_id", $url, TRUE);

	// Redirect back to settings page
	$redirect_url = get_bloginfo("url") . "/wp-admin/options-general.php?page=churchsuite&status=success";
	header("Location: " . $redirect_url);
	exit;
}
*/
// ChurchSuite Import Events API

function ChurchSuite_Import_Events()
{

	$response = wp_remote_get('https://' . get_option('cs_acc_id') . '.churchsuite.co.uk/embed/calendar/json');
	$body     = wp_remote_retrieve_body($response);

	$events = json_decode($body);

	foreach ((array)$events as $event) {
		$event_title = $event->name;
		$event_id = $event->id;
		$event_featured_image = $event->images->original_1000;
		$event_identifier = $event->identifier;
		$event_desciption = $event->description;
		$event_start = $event->datetime_start;
		$event_end = $event->datetime_end;
		$event_cat_name = $event->category->name;
		$event_cat_id = $event->category->id;
		$event_featured = $event->signup_options->public->featured;
		$event_tickets = $event->signup_options->tickets->enabled;
		$event_tickets_url = $event->signup_options->tickets->url;
		$event_created = $event->ctime;
		$event_modified = $event->mtime;
		$clean_event_start = strtotime($event_start);
		$clean_event_end = strtotime($event_end);
		$event_date = date('j M', $clean_event_start) . ' - ' . date('j M y', $clean_event_end);

		// Create post object
		$event_post = array(
			'post_title'    => wp_strip_all_tags(ucwords($event_title)),
			'post_content'  => $event_desciption,
			'post_status'   => 'publish',
			'post_type' => 'churchsuite_events',
			'import_id' => $event_id,
			'post_date' => $event_created,
			'post_modified' => $event_modified,
			'post_author'   => 10,
			'meta_input'   => array(
				'event_start' => $event_start,
				'event_end' => $event_end,
				'event_date' => $event_date,
				'event_id' => $event_id,
				'event_identifier' => $event_identifier,
				'event_featured' => $event_featured,
				'event_cat_id' => $event_cat_id,
				'event_cat_name' => $event_cat_name,
				'event_tickets' => $event_tickets,
				'event_tickets_url' => $event_tickets_url,
				'event_featured_image_URL' => $event_featured_image,
			),
		);


		$content = get_post($event_id);

		if ($content) {
			return false;
		} else {
			wp_insert_post($event_post);
		}
	}
}
add_action('init', 'ChurchSuite_Import_Events');


// Add the custom columns to the ChurchSuite Event post type:
add_filter('manage_churchsuite_events_posts_columns', 'set_custom_edit_churchsuite_events_columns');
function set_custom_edit_churchsuite_events_columns($columns)
{
	unset($columns['title']);
	unset($columns['date']);
	unset($columns['comments']);
	$columns['event_id'] = __('Event ID', 'danz-churchsuite-events');
	$columns['event_identifier'] = __('Event Identifier', 'danz-churchsuite-events');
	$columns['title'] = __('Event Name', 'danz-churchsuite-events');
	$columns['event_date'] = __('Event Date', 'danz-churchsuite-events');
	$columns['event_start'] = __('Event Start', 'danz-churchsuite-events');
	$columns['event_end'] = __('Event End', 'danz-churchsuite-events');
	$columns['event_cat_name'] = __('Event Category', 'danz-churchsuite-events');
	$columns['event_cat_id'] = __('Event Category ID', 'danz-churchsuite-events');


	return $columns;
}

// Add the data to the custom columns for the ChurchSuite Event post type:
add_action('manage_churchsuite_events_posts_custom_column', 'custom_churchsuite_events_column', 10, 2);
function custom_churchsuite_events_column($column, $post_id)
{
	switch ($column) {

		case 'event_id':
			echo get_post_meta($post_id, 'event_id', true);
			break;
		case 'event_identifier':
			echo get_post_meta($post_id, 'event_identifier', true);
			break;
		case 'event_start':
			echo get_post_meta($post_id, 'event_start', true);
			break;
		case 'event_end':
			echo get_post_meta($post_id, 'event_end', true);
			break;
		case 'event_date':
			echo get_post_meta($post_id, 'event_date', true);
			break;
		case 'event_cat_name':
			echo get_post_meta($post_id, 'event_cat_name', true);
			break;
		case 'event_cat_id':
			echo get_post_meta($post_id, 'event_cat_id', true);
			break;
	}
}

add_filter('manage_edit-churchsuite_events_sortable_columns', function ($columns) {
	$columns['event_id'] = 'event_id';
	$columns['event_start'] = 'event_start';
	$columns['event_end'] = 'event_end';
	return $columns;
});


// Showing post with meta key filter in Portfolio Widget
add_action('elementor/query/featured_events', function ($query) {
	// Get current meta Query
	$meta_query = $query->get('meta_query');

	// If there is no meta query when this filter runs, it should be initialized as an empty array.
	if (!$meta_query) {
		$meta_query = [];
	}

	// Append our meta query
	$meta_query[] = [
		'key' => 'event_featured',
		'value' => '1',
		'compare' => '=',
	];
	$query->set('meta_query', $meta_query);

	$event_cat_id = $query->get('event_cat');

	// If there is no meta query when this filter runs, it should be initialized as an empty array.
	if (!$event_cat_id) {
		$event_cat_id = [];
	}

	// Append our meta query
	$event_cat_id[] = [
		'key' => 'event_cat_id',
		'value' => ['10', '8', '9', '7'],
		'compare' => 'in',
	];
	$query->set('event_cat', $event_cat_id);


	$query->set('orderby', $meta_query);
});

add_filter( 'auto_update_plugin', '__return_true' );