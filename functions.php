<?php
/**
 * Global Functions and Variables.
 *
 * @package WPPIC
 */

/**
 * Exit and prevent direct access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct acces not allowed!' );
}

/***************************************************************
 * Fetching plugins and themes data through WordPress Plugin API
 ***************************************************************/
function wppic_api_parser( $type, $slug, $expiration = 720, $extra = '', $load_attachments = false, $force = false ) {

	if ( ! empty( $extra ) ) {
		$extra = $extra . '_';
	}

	// Get option key name.
	$option_key = sanitize_key( 'wppic_' . $extra . $type . '_' . preg_replace( '/\-/', '_', $slug ) );

	$wppic_data = false;

	// Determine if we're getting data from cache.
	if ( ! $force ) {
		$maybe_wppic_data = get_transient( $option_key );
		if ( false !== $maybe_wppic_data ) {
			// We have data, but need to check for any errors.
			if ( is_wp_error( $maybe_wppic_data ) ) {
				// Try to get the data from option.
				$maybe_wppic_data = get_option( $option_key, false );
				if ( false !== $maybe_wppic_data && ! is_wp_error( $maybe_wppic_data ) ) {
					$wppic_data = $maybe_wppic_data;
				}
			} else {
				$wppic_data = $maybe_wppic_data;
			}
		}
	}

	// check if $expiration is numeric, only digit char.
	if ( empty( $expiration ) || ! is_numeric( $expiration ) ) {
		$expiration = 720;
	}

	if ( false === $wppic_data || empty( $wppic_data ) || $force ) {

		$wppic_data = false;
		$wppic_data = apply_filters( 'wppic_add_api_parser', $wppic_data, $type, $slug, $load_attachments, $force );
	}

	// If we have data, store it as an option and a transient.
	if ( false !== $wppic_data && ! empty( $wppic_data ) ) {
		// Remove old option.
		delete_option( $option_key );

		// Re-add option.
		add_option( $option_key, $wppic_data, '', 'no' );
		set_transient( $option_key, $wppic_data, $expiration * 60 );
	}

	return $wppic_data;
}


/***************************************************************
 * Add settings link on plugin list page
 ***************************************************************/
function wppic_settings_link( $links ) {
	$links[] = '<a href="' . esc_url( admin_url( 'options-general.php?page=' . WPPIC_ID ) ) . '" title="' . esc_html__( 'WP Plugin Info Card Settings', 'wp-plugin-info-card' ) . '">' . __( 'Settings', 'wp-plugin-info-card' ) . '</a>';
	return $links;
}
add_filter( 'plugin_action_links_' . WPPIC_BASE, 'wppic_settings_link' );


/***************************************************************
 * Add custom meta link on plugin list page
 ***************************************************************/
function wppic_meta_links( $links, $file ) {
	if ( $file === 'wp-plugin-info-card/wp-plugin-info-card.php' ) {
		$links[] = '<a href="https://docs.mediaron.com/product/wp-plugin-info-card/" target="_blank" title="' . __( 'Documentation and examples', 'wp-plugin-info-card' ) . '"><strong>' . __( 'Documentation and examples', 'wp-plugin-info-card' ) . '</strong></a>';
		$links[] = '<a href="http://b-website.com/category/plugins" target="_blank" title="' . __( 'More plugins by b*web', 'wp-plugin-info-card' ) . '">' . __( 'More plugins by b*web', 'wp-plugin-info-card' ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'wppic_meta_links', 10, 2 );


/***************************************************************
 * Purge all plugin transients function
 ***************************************************************/
function wppic_delete_transients() {
	global $wpdb;
	/*
	if( extension_loaded( 'Memcache' ) )
		return;*/
	$wppic_transients = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Lists plugin transients for immediate deletion, so the result is not cacheable.
		"SELECT option_name AS name,
		option_value AS value FROM $wpdb->options
		WHERE option_name LIKE '_transient_wppic_%'"
	);
	foreach ( (array) $wppic_transients as $singleTransient ) {
		delete_transient( str_replace( '_transient_', '', $singleTransient->name ) );
	}
}

/***************************************************************
 * Purge all plugin transients cache function
 ***************************************************************/
function wppic_delete_options_cache() {
	global $wpdb;
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'wppic_plugin%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Deletes cached plugin option rows with no cacheable result.
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'wppic_theme%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Deletes cached theme option rows with no cacheable result.
}

/***************************************************************
 * Purge all author/profile badge cache function
 ***************************************************************/
function wppic_clear_author_cache() {
	global $wpdb;

	// Delete all badge profile transients.
	$wppic_profile_transients = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Lists author badge transients for immediate deletion, so the result is not cacheable.
		"SELECT option_name AS name,
		option_value AS value FROM $wpdb->options
		WHERE option_name LIKE '_transient_wppic_profile_%'"
	);
	foreach ( (array) $wppic_profile_transients as $single_transient ) {
		delete_transient( str_replace( '_transient_', '', $single_transient->name ) );
	}

	// Also delete transient timeouts.
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_wppic_profile_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Deletes leftover author badge transient timeouts with no cacheable result.

	// Update _wppic_last_updated to 0 for all wppic_profiles posts to force refetch.
	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Resets profile last-updated meta in bulk because WordPress has no API for this update and it has no cacheable result.
		$wpdb->prepare(
			"UPDATE {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			SET pm.meta_value = %s
			WHERE pm.meta_key = %s
			AND p.post_type = %s",
			'0',
			'_wppic_last_updated',
			'wppic_profiles'
		)
	);
}


/***************************************************************
 * Cron to purge all plugin transients every weeks
 ***************************************************************/
function wppic_add_weekly( $schedules ) {
	$schedules['wppic-weekly'] = array(
		'interval' => 604800,
		'display'  => __( 'Once Weekly', 'wp-plugin-info-card' ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'wppic_add_weekly' );

function wppic_cron_activation() {
	wp_schedule_event( current_time( 'timestamp' ), 'wppic-weekly', 'wppic_daily_cron' );
}
add_action( 'wppic_daily_cron', 'wppic_delete_transients' );

register_activation_hook( __FILE__, 'wppic_cron_activation' );
register_activation_hook( __FILE__, 'wppic_delete_transients' );
