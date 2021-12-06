<?php // exit if uninstall constant is not defined
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

// remove plugin options
delete_option('cep_accountid');

// remove plugin transients

// remove plugin cron events
$timestamp = wp_next_scheduled('old_event_delete');
wp_unschedule_event($timestamp, 'old_event_delete');

// delete custom post type posts
$churchsuite_cpt_args = array('post_type' => 'churchsuite_events', 'posts_per_page' => -1);
$churchsuite_cpt_posts = get_posts($churchsuite_cpt_args);
foreach ($churchsuite_cpt_posts as $post) {
	wp_delete_post($post->ID, false);
    delete_post_meta($post->ID, 'event_start');
	delete_post_meta($post->ID, 'event_end');
	delete_post_meta($post->ID, 'event_date');
	delete_post_meta($post->ID, 'event_start_time');
	delete_post_meta($post->ID, 'event_end_time');
	delete_post_meta($post->ID, 'event_month');
	delete_post_meta($post->ID, 'event_id');
	delete_post_meta($post->ID, 'event_identifier');
	delete_post_meta($post->ID, 'event_featured');
	delete_post_meta($post->ID, 'event_cat_id');
	delete_post_meta($post->ID, 'event_cat_name');
	delete_post_meta($post->ID, 'event_tickets');
	delete_post_meta($post->ID, 'event_tickets_url');
	delete_post_meta($post->ID, 'event_featured_image_URL');
    unregister_post_type( 'churchsuite_events' );

}

if (!get_option('plugin_do_uninstall', false)) exit;
