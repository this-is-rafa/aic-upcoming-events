<?php
/**
 * Plugin Name: AIC Upcoming Events Block Plugin
 * Description: Display the next 5 events from the AIC in a block.
 * Author: Rafa
 * Requires at least: 6.6
 * Requires PHP:      7.2
 * Version:           0.1.0
 * Author:            Rafa
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       aic-upcoming-events
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Register the custom post type
 */
function aic_upcoming_events_register_post_type() {
	$args = array(
		'public'       => true,
		'label'        => 'PAMM Posts',
		'supports'     => array( 'title', 'editor', 'thumbnail' ),
		'show_in_rest' => true,
	);
	register_post_type( 'pamm_post', $args );
}
add_action( 'init', 'aic_upcoming_events_register_post_type' );


/**
 * Register our block
 */
function aic_upcoming_events_register_block() {
	wp_register_script(
		'aic-upcoming-events-block',
		plugins_url( 'aic-upcoming-events-block.js', __FILE__ ),
		array( 'wp-blocks', 'wp-element', 'wp-editor' )
	);

	register_block_type(
		'aic-upcoming-events/events-block',
		array(
			'editor_script'   => 'aic-upcomings-events-block',
			'render_callback' => 'aic_upcoming_events_block',
		)
	);
}
add_action( 'init', 'aic_upcoming_events_register_block' );


/**
 * Enqueue our stylesheet
 */
function aic_upcoming_events_frontend_styles() {
	wp_enqueue_style(
			'aic-upcoming-events-styles',
			plugins_url('style.css', __FILE__),
			array(),
			filemtime(plugin_dir_path(__FILE__) . 'style.css')
	);
}
add_action('wp_enqueue_scripts', 'aic_upcoming_events_frontend_styles');


/**
 * Enqueue the block editor assets
 */
function aic_upcoming_events_enqueue_block_editor_assets() {
	wp_enqueue_script(
		'aic-upcoming-events-block-editor',
		plugins_url( 'aic-upcoming-events-block.js', __FILE__ ),
		array( 'wp-blocks', 'wp-element', 'wp-editor' ),
		filemtime( plugin_dir_path( __FILE__ ) . 'aic-upcoming-events-block.js' )
	);
}
add_action( 'enqueue_block_editor_assets', 'aic_upcoming_events_enqueue_block_editor_assets' );


/**
 * The block render function
 */
function aic_upcoming_events_block( $attributes ) {
	$api_url = 'https://api.artic.edu/api/v1/event-occurrences';
	$response = wp_remote_get($api_url . '?limit=5&fields=id,title,short_description,start_at,event_id');

	if ( is_wp_error( $response ) ) {
			return 'Error fetching events.';
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode($body, true);

	if (empty($data['data'])) {
			return 'No upcoming events found.';
	}

	$output = '<div class="aic-events">';
	$output .= '<h3 class="title">AIC Upcoming Events</h3>';
	foreach ( $data['data'] as $event ) {
			$output .= '<div class="aic-events-card">';
			$output .= '<h3 class="title">' . esc_html($event['title']) . '</h3>';
			$output .= '<p class="date">' . esc_html(date('M d, Y', strtotime($event['start_at']))) . '</p>';
			$output .= '<p class="desc">' . esc_html($event['short_description']) . '</p>';
			$output .= '<p class="link-wrap"><a class="link" href="https://www.artic.edu/events/' . $event['event_id'] . '" rel="nofollow" target="_blank">More Info</a></p>';
			$output .= '</div>';
	}
	$output .= '</div>';

	return $output;
}

