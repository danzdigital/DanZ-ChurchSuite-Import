<?php

/**
 * @wordpress-plugin
 * Plugin Name:       DanZ - ChurchSuite Import
 * Plugin URI:        https://danzdigitaldesigns.co.uk/danz_churchsuite_import
 * Description:       This plugin imports ChurchSuite Events into the Events Post Type.
 * Version:           1.0.2
 * Author:            DanZ Digital Designs
 * Author URI:        https://danzdigitaldesigns.co.uk
 * Text Domain:       danz-churchsuite-events
 * GitHub Plugin URI: https://github.com/danzdigital/DanZ-ChurchSuite-Import
 */


if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}


require_once( 'updater.php' );
if ( is_admin() ) {
    new ChurchSuite_Events_PluginUpdater( __FILE__, 'danzdigital', "DanZ-ChurchSuite-Import" );
}


/**
 * Currently plugin version.
 */
define('danz_churchsuite_import_VERSION', '1.0.2');


if (!function_exists('churchsuite_events')) {

	// 	// Register ChurchSuites Events Posts
	function churchsuite_events()
	{

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
			'rest_base'             => 'churchsuite_event',
		);
		register_post_type('churchsuite_events', $post_args);
	};
	add_action('init', 'churchsuite_events', 0);
}
// ChurchSuite Events Admin Dashboard

add_action("admin_menu", "ChurchSuite_Events_Plugin_Menu");

function ChurchSuite_Events_Plugin_Menu()
{
	add_submenu_page(
		"options-general.php",  // Which menu parent
		"ChurchSuite Events",            // Page title
		"ChurchSuite Events",            // Menu title
		"manage_options",       // Minimum capability (manage_options is an easy way to target administrators)
		"churchsuite",            // Menu slug
		"ChurchSuite_Events_Plugin_Options"     // Callback that prints the markup
	);
}

function ChurchSuite_Events_Plugin_Options()
{
	if (!current_user_can("manage_options")) {
		wp_die(__("You do not have sufficient permissions to access this page."));
	}
?>
	<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">

		<input type="hidden" name="action" value="update_churchsuite_settings" />

		<h3><?php _e("ChurchSuite ChurchSuite Events Info", "churchsuite-api"); ?></h3>
		<p>
			<label><?php _e("ChurchSuite Account ID:", "churchsuite-api"); ?></label>
			<input class="" type="text" name="cs_acc_id" value="<?php echo get_option('cs_acc_id'); ?>" />
		</p>
		<input class="button button-primary" type="submit" value="<?php _e("Save", "churchsuite-api"); ?>" />

	</form>

	<div class="churchsuite_info" style="margin-top: 20px;">
		<p>
			<?php
			_e("Current ChurchSuite URL: https://", "churchsuite-api");
			echo get_option("cs_acc_id");
			_e(".churchsuite.co.uk/embed/calendar/json", "churchsuite-api");
			?>
		</p>
	</div>

<?php

	add_action('admin_post_update_churchsuite_settings', 'churchsuite_handle_save');
}

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



if (isset($_GET['status']) && $_GET['status'] == 'success') {
?>
	<div id="message" class="updated notice is-dismissible">
		<p><?php _e("Settings updated!", "churchsuite-api"); ?></p>
		<button type="button" class="notice-dismiss">
			<span class="screen-reader-text"><?php _e("Dismiss this notice.", "churchsuite-api"); ?></span>
		</button>
	</div>
<?php
}

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
