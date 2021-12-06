<?php

/**
 * @since             2.3.1
 * @package           churchsuite_events_import
 *
 * @wordpress-plugin
 * Plugin Name:       ChurchSuite Events Import
 * Description:       This plugin imports ChurchSuite Events into the ChurchSuite Events Post Type.
 * Version:           2.3.1
 * Author:            DanZ Digital Designs
 * Author URI:        https://danzdigitaldesigns.co.uk
 * Text Domain:       churchsuite-events-import
 * GitHub Plugin URI: https://github.com/danzdigital/DanZ-ChurchSuite-Import
 * Primary Branch: main
 * 
 */

use function PHPSTORM_META\type;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}


if (!function_exists('churchsuite_events')) {

	class ChurchSuiteEvents
	{

		function __construct()
		{
			add_action('admin_menu', array($this, 'adminPage'));
			add_action( 'admin_init', array($this,'settings'));
		}

		function settings(){
			register_setting( 'churchsuiteeventsplugin', 'cep_accountid',array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Account ID') );
			add_settings_field( 'cep_accountid', 'Account ID', array($this, 'accountIdHTML'), 'churchsuite-events-settings', 'cep_section' );
			add_settings_section( 'cep_section', null, null, 'churchsuite-events-settings' );
		}

		function accountIdHTML(){ ?>
			<input type="text" name="cep_accountid" value="<?php echo esc_attr(get_option('cep_accountid')) ?>">

		<?php }

		function adminPage()
		{
			add_options_page('ChurchSuite Events Settings', 'ChurchSuite Settings', 'manage_options', 'churchsuite-events-settings', array($this, 'adminPageHTML'));
		}

		function adminPageHTML()
		{ ?>
			<div class="wrap">
				<h1>ChurchSuite Events Settings</h1>
				<form action="options.php" method="post">
				<?php
				settings_fields( 'churchsuiteeventsplugin' );
				do_settings_sections( 'churchsuite-events-settings' );
				submit_button( );
				?>
				<p>Current ChurchSuite Account URL: <a href="https://<?php echo get_option( 'cep_accountid')?>.churchsuite.co.uk">https://<?php echo get_option( 'cep_accountid')?>.churchsuite.co.uk</a></p>
				</form>
			</div>
<?php
		}
	}

	$ChurchsuiteEvents = new ChurchSuiteEvents();

	// 	// Register ChurchSuites Events Posts
	function churchsuite_events()	{

		$post_labels = array(
			'name'                  => __('ChurchSuite Events', 'Post Type General Name', 'churchsuite_events_import'),
			'singular_name'         => __('ChurchSuite Event', 'Post Type Singular Name', 'churchsuite_events_import'),
			'menu_name'             => __('ChurchSuite Events', 'churchsuite_events_import'),
			'name_admin_bar'        => __('ChurchSuite Events', 'churchsuite_events_import'),
			'archives'              => __('ChurchSuite Event Archives', 'churchsuite_events_import'),
			'attributes'            => __('ChurchSuite Event Attributes', 'churchsuite_events_import'),
			'parent_item_colon'     => __('ChurchSuite Event:', 'churchsuite_events_import'),
			'all_items'             => __('All ChurchSuite Events', 'churchsuite_events_import'),
			'add_new_item'          => __('Add New ChurchSuite Event', 'churchsuite_events_import'),
			'add_new'               => __('Add New', 'churchsuite_events_import'),
			'new_item'              => __('New ChurchSuite Event', 'churchsuite_events_import'),
			'edit_item'             => __('Edit ChurchSuite Event', 'churchsuite_events_import'),
			'update_item'           => __('Update ChurchSuite Event', 'churchsuite_events_import'),
			'view_item'             => __('View ChurchSuite Event', 'churchsuite_events_import'),
			'view_items'            => __('View ChurchSuite Events', 'churchsuite_events_import'),
			'search_items'          => __('Search ChurchSuite Event', 'churchsuite_events_import'),
			'not_found'             => __('Not found', 'churchsuite_events_import'),
			'not_found_in_trash'    => __('Not found in Trash', 'churchsuite_events_import'),
			'featured_image'        => __('Featured Image', 'churchsuite_events_import'),
			'set_featured_image'    => __('Set featured image', 'churchsuite_events_import'),
			'remove_featured_image' => __('Remove featured image', 'churchsuite_events_import'),
			'use_featured_image'    => __('Use as featured image', 'churchsuite_events_import'),
			'insert_into_item'      => __('Insert into ChurchSuite Event', 'churchsuite_events_import'),
			'uploaded_to_this_item' => __('Uploaded to this ChurchSuite Event', 'churchsuite_events_import'),
			'items_list'            => __('ChurchSuite Events list', 'churchsuite_events_import'),
			'items_list_navigation' => __('Items list navigation', 'churchsuite_events_import'),
			'filter_items_list'     => __('Filter ChurchSuite Events', 'churchsuite_events_import'),
		);
		$rewrite = array(
			'slug' => 'event',
			'with_front' => true,
			'pages' => true,
			'feeds' => true,
		);
		$post_args = array(
			'label'                 => __('ChurchSuite Event', 'churchsuite_events_import'),
			'description'           => __('All ChurchSuite Events imported from API', 'churchsuite_events_import'),
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
			'rest_base'             => 'churchsuite_event',
			'rewrite' => $rewrite
		);
		register_post_type('churchsuite_events', $post_args);
	};
	add_action('init', 'churchsuite_events', 0);
}

// ChurchSuite Import Events API

function ChurchSuite_Import_Events(){

	$response = wp_remote_get('https://'. get_option( 'cep_accountid') . '.churchsuite.co.uk/embed/calendar/json');
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
		$clean2_event_start = date('Y-m-d',$clean_event_start);
		$time_event_start = date('H:i',$clean_event_start);
		$clean_event_end = strtotime($event_end);
		$clean2_event_end = date('Y-m-d',$clean_event_end);
		$event_month = date('F y',$clean_event_start);
		$time_event_end = date('H:i',$clean_event_end);
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
				'event_start' => $clean2_event_start,
				'event_end' => $clean2_event_end,
				'event_date' => $event_date,
				'event_start_time' => $time_event_start,
				'event_end_time' => $time_event_end,
				'event_month' => $event_month,
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
function set_custom_edit_churchsuite_events_columns($columns){
	unset($columns['title']);
	unset($columns['date']);
	unset($columns['comments']);
	$columns['event_id'] = __('Event ID', 'churchsuite_events_import');
	$columns['event_identifier'] = __('Event Identifier', 'churchsuite_events_import');
	$columns['title'] = __('Event Name', 'churchsuite_events_import');
	$columns['event_date'] = __('Event Date', 'churchsuite_events_import');
	$columns['event_start'] = __('Event Start', 'churchsuite_events_import');
	$columns['event_end'] = __('Event End', 'churchsuite_events_import');
	$columns['event_month'] = __('Event Month', 'churchsuite_events_import');
	$columns['event_start_time'] = __('Event Start Time', 'churchsuite_events_import');
	$columns['event_end_time'] = __('Event End Start', 'churchsuite_events_import');
	$columns['event_cat_name'] = __('Event Category', 'churchsuite_events_import');
	$columns['event_cat_id'] = __('Event ID', 'churchsuite_events_import');


	return $columns;
}

// Add the data to the custom columns for the ChurchSuite Event post type:
add_action('manage_churchsuite_events_posts_custom_column', 'custom_churchsuite_events_column', 10, 2);
function custom_churchsuite_events_column($column, $post_id){
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
		case 'event_start_time':
			echo get_post_meta($post_id, 'event_start_time', true);
			break;
		case 'event_end_time':
			echo get_post_meta($post_id, 'event_end_time', true);
			break;
		case 'event_month':
			echo get_post_meta($post_id, 'event_month', true);
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

	$query->set('orderby', 'event_end');	
	$query->set('meta_key', 'event_end');	 
	$query->set('order', 'ASC');
});

// Showing post with meta key filter in Portfolio Widget
add_action('elementor/query/full_calendar', function ($query) {
	$today = date("Y-m-d");

	$meta_query = $query->get( 'meta_query' );

	// If there is no meta query when this filter runs, it should be initialized as an empty array.
	if ( ! $meta_query ) {
		$meta_query = [];
	}

	// Append our meta query
	$meta_query[] = [
		'key' => 'event_start',
		'value' => $today,
		'compare' => '=',
	];
	$query->set( 'meta_query', $meta_query );

	$query->set( 'meta_key', 'event_start_time' );
	$query->set( 'orderby', 'meta_value_num' );
	$query->set( 'order', 'ASC' );

});

function get_delete_old_events() {

    $past_query = date('Y-m-d', strtotime('-1 day'));

    // Set our query arguments
    $args = [
        'fields'         => 'ids', // Only get post ID's to improve performance
        'post_type'      => 'churchsuite_events', // Post type
        'posts_per_page' => -1,
        'meta_query'     => [
            [
                'key'     => 'event_end', // Replace this with the event end date meta key.
                'value'   => $past_query,
                'compare' => '<='
            ]
        ]
      ];
    $q = get_posts( $args );

    // Check if we have posts to delete, if not, return false
    if ( !$q )
        return false;

    // OK, we have posts to delete, lets delete them
    foreach ( $q as $id )
		wp_delete_post($id);
		delete_post_meta($id,'event_start' );
		delete_post_meta($id,'event_end' );
		delete_post_meta($id,'event_date' );
		delete_post_meta($id,'event_start_time' );
		delete_post_meta($id,'event_end_time' );
		delete_post_meta($id,'event_month' );
		delete_post_meta($id,'event_id' );
		delete_post_meta($id,'event_identifier' );
		delete_post_meta($id,'event_featured' );
		delete_post_meta($id,'event_cat_id' );
		delete_post_meta($id,'event_cat_name' );
		delete_post_meta($id,'event_tickets' );
		delete_post_meta($id,'event_tickets_url' );
		delete_post_meta($id,'event_featured_image_URL' );
}

// expired_post_delete hook fires when the Cron is executed
add_action( 'old_event_delete', 'get_delete_old_events' );

// Add function to register event to wp
add_action( 'wp', 'register_daily_events_delete_event');

function register_daily_events_delete_event() {
    // Make sure this event hasn't been scheduled
    if( !wp_next_scheduled( 'old_event_delete' ) ) {
        // Schedule the event
        wp_schedule_event( time(), 'daily', 'old_event_delete' );
    }
}

/**
 * Deactivation hook.
 */
function churchsuite_events_deactivate() {
    // Unregister the post type, so the rules are no longer in memory.
    unregister_post_type( 'churchsuite_events' );
	
    // Set our query arguments
    $args = [
        'fields'         => 'ids', // Only get post ID's to improve performance
        'post_type'      => 'churchsuite_events', // Post type
        'posts_per_page' => -1,
	];
      
    $q = get_posts( $args );

    // Check if we have posts to delete, if not, return false
    if ( !$q )
        return false;

    // OK, we have posts to delete, lets delete them
    foreach ( $q as $id )
		wp_delete_post($id);
		delete_post_meta($id,'event_start' );
		delete_post_meta($id,'event_end' );
		delete_post_meta($id,'event_date' );
		delete_post_meta($id,'event_start_time' );
		delete_post_meta($id,'event_end_time' );
		delete_post_meta($id,'event_month' );
		delete_post_meta($id,'event_id' );
		delete_post_meta($id,'event_identifier' );
		delete_post_meta($id,'event_featured' );
		delete_post_meta($id,'event_cat_id' );
		delete_post_meta($id,'event_cat_name' );
		delete_post_meta($id,'event_tickets' );
		delete_post_meta($id,'event_tickets_url' );
		delete_post_meta($id,'event_featured_image_URL' );

    // Clear the permalinks to remove our post type's rules from the database.
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'pluginprefix_deactivate' );