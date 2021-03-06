<?php
/**
 * Plugin Name: WP Plugin Info Card
 * Plugin URI: https://mediaron.com/wp-plugin-info-card/
 * Description: WP Plugin Info Card displays plugins & themes identity cards in a beautiful box with a smooth rotation effect using WordPress.org Plugin API & WordPress.org Theme API. Dashboard widget included.
 * Author: Brice CAPOBIANCO, Ronald Huereca
 * Author URI: http://b-website.com/
 * Version: 3.3.0
 * Domain Path: /langs
 * Text Domain: wp-plugin-info-card
 */


/***************************************************************
 * SECURITY : Exit if accessed directly
***************************************************************/
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct acces not allowed!' );
}


/***************************************************************
 * Define constants
 ***************************************************************/
if ( ! defined( 'WPPIC_VERSION' ) ) {
	define( 'WPPIC_VERSION', '3.3.0' );
}
if ( ! defined( 'WPPIC_PATH' ) ) {
	define( 'WPPIC_PATH', plugin_dir_path( __FILE__ ) . '/src/' );
}
if ( ! defined( 'WPPIC_URL' ) ) {
	define( 'WPPIC_URL', plugin_dir_url( __FILE__ ) . '/src/' );
}
if ( ! defined( 'WPPIC_BASE' ) ) {
	define( 'WPPIC_BASE', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'WPPIC_NAME' ) ) {
	define( 'WPPIC_NAME', 'WP Plugin Info Card' );
}
if ( ! defined( 'WPPIC_NAME_FULL' ) ) {
	define( 'WPPIC_NAME_FULL', 'WP Plugin Info Card' );
}
if ( ! defined( 'WPPIC_ID' ) ) {
	define( 'WPPIC_ID', 'wp-plugin-info-card' );
}
if ( ! defined( 'WPPIC_FILE' ) ) {
	define( 'WPPIC_FILE', __FILE__ );
}


/***************************************************************
 * Get options
 ***************************************************************/
global 	$wppicSettings;
$wppicSettings = get_option( 'wppic_settings' );

global 	$wppicDateFormat;
$wppicDateFormat = get_option( 'date_format' );


/***************************************************************
 * Load plugin files
 ***************************************************************/
$wppicFiles = array( 'api','shortcode','admin','widget','ui', 'add-plugin', 'add-theme', 'query' );
foreach( $wppicFiles as $wppicFile ){
	require_once( WPPIC_PATH . 'wp-plugin-info-card-' . $wppicFile . '.php' );
}


/***************************************************************
 * Load plugin textdomain
 ***************************************************************/
function wppic_load_textdomain() {
	$path = dirname( plugin_basename( __FILE__ ) ) . '/langs/';
	load_plugin_textdomain( 'wp-plugin-info-card', FALSE, $path );
}
add_action( 'init', 'wppic_load_textdomain' );


/***************************************************************
 * Add settings link on plugin list page
 ***************************************************************/
function wppic_settings_link( $links ) {
  $links[] = '<a href="' . admin_url( 'options-general.php?page=' . WPPIC_ID ) . '" title="'. __( 'WP Plugin Info Card Settings', 'wp-plugin-info-card' ) .'">' . __( 'Settings', 'wp-plugin-info-card' ) . '</a>';
  return $links;
}
add_filter( 'plugin_action_links_' . WPPIC_BASE, 'wppic_settings_link' );


/***************************************************************
 * Add custom meta link on plugin list page
 ***************************************************************/
function wppic_meta_links( $links, $file ) {
	if ( $file === 'wp-plugin-info-card/wp-plugin-info-card.php' ) {
		$links[] = '<a href="https://mediaron.com/wp-plugin-info-card/" target="_blank" title="'. __( 'Documentation and examples', 'wp-plugin-info-card' ) .'"><strong>'. __( 'Documentation and examples', 'wp-plugin-info-card' ) .'</strong></a>';
		$links[] = '<a href="http://b-website.com/category/plugins" target="_blank" title="'. __( 'More plugins by b*web', 'wp-plugin-info-card' ) .'">'. __( 'More plugins by b*web', 'wp-plugin-info-card' ) .'</a>';
		$links[] = '<a href="https://mediaron.com/project-type/wordpress-plugins/" target="_blank" title="'. __( 'More plugins by MediaRon', 'wp-plugin-info-card' ) .'">'. __( 'More plugins by MediaRon', 'wp-plugin-info-card' ) .'</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'wppic_meta_links', 10, 2 );


/***************************************************************
 * Admin Panel Favico
 ***************************************************************/
function wppic_add_favicon() {
	$screen = get_current_screen();
	if ( $screen->id != 'toplevel_page_' . WPPIC_ID )
		return;

	$favicon_url = WPPIC_URL . 'img/wppic.svg';
	echo '<link rel="shortcut icon" href="' . $favicon_url . '" />';
}
add_action( 'admin_head', 'wppic_add_favicon' );


/***************************************************************
 * Purge all plugin transients function
 ***************************************************************/
function wppic_delete_transients(){
	global $wpdb;
	/*if( extension_loaded( 'Memcache' ) )
		return;*/
	$wppic_transients = $wpdb->get_results(
		"SELECT option_name AS name,
		option_value AS value FROM $wpdb->options
		WHERE option_name LIKE '_transient_wppic_%'"
	);
	foreach( ( array ) $wppic_transients as $singleTransient ){
		delete_transient( str_replace( '_transient_', '', $singleTransient->name ) );
	}
}


/***************************************************************
 * Cron to purge all plugin transients every weeks
 ***************************************************************/
function wppic_add_weekly( $schedules ) {
	$schedules[ 'wppic-weekly' ] = array(
		'interval' => 604800,
		'display' => __( 'Once Weekly' )
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'wppic_add_weekly' );

function wppic_cron_activation() {
	wp_schedule_event( current_time( 'timestamp' ), 'wppic-weekly', 'wppic_daily_cron' );
}
add_action( 'wppic_daily_cron', 'wppic_delete_transients' );


/***************************************************************
 * Remove plugin settings from DB on plugin deletion
 ***************************************************************/
function wppic_uninstall() {
	// Remove option from DB
	delete_option( 'wppic_settings' );
	//deactivate cron
	wp_clear_scheduled_hook( 'wppic_daily_cron' );
	//Purge transients
	wppic_delete_transients();
}


/***************************************************************
 * Hooks for install & uninstall
 ***************************************************************/
function wppic_activation() {
	register_uninstall_hook( __FILE__,  'wppic_uninstall' );
}
register_activation_hook( __FILE__, 'wppic_activation' );
register_activation_hook( __FILE__, 'wppic_cron_activation' );
register_activation_hook( __FILE__, 'wppic_delete_transients' );

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block Initializer.
 */
if( function_exists( 'register_block_type' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'src/init.php';
}
