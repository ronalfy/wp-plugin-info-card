<?php
/**
 * Set up option saving/retrieval.
 *
 * @package WPPIC
 */

namespace MediaRon\WPPIC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class for for plugin's options.
 */
class Options {

	/**
	 * Array holding the options.
	 *
	 * @var [type]
	 */
	protected static $options = false;

	/**
	 * Static var to hold date format.
	 *
	 * @var string $date_format Date format.
	 */
	private static $date_format = '';

	/**
	 * Retrieve date format.
	 *
	 * @return string Date format.
	 */
	public static function get_date_format() {
		if ( empty( self::$date_format ) ) {
			self::$date_format = get_option( 'date_format' );
		}

		return self::$date_format;
	}

	/**
	 * Update options via sanitization
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $options array of options to save.
	 * @param bool  $merge_options Whether to merge options with old options or not.
	 * @return array $options.
	 */
	public static function update_options( $options, $merge_options = true ) {
		$force           = true;
		$current_options = $merge_options ? self::get_options( $force, true, true ) : array();
		foreach ( $options as $key => &$option ) {
			switch ( $key ) {
				case 'widget':
				case 'ajax':
				case 'enqueue':
				case 'credit':
				case 'enable_edd':
				case 'enable_rest_api':
				case 'enable_custom_plugins':
				case 'enable_github_info_cards':
				case 'github_info_cards_unauthenticated':
					$option = filter_var( $options[ $key ], FILTER_VALIDATE_BOOLEAN );
					break;
				default:
					if ( is_array( $option ) ) {
						$option = Functions::sanitize_array_recursive( $option );
					} else {
						$option = sanitize_text_field( $options[ $key ] );
					}
					break;
			}
		}
		$options = wp_parse_args( $options, $current_options );
		if ( Functions::is_multisite() ) {
			update_site_option( 'wppic_settings', $options );
		} else {
			update_option( 'wppic_settings', $options );
		}
		self::$options = $options;
		return $options;
	}

	/**
	 * Return a list of options.
	 *
	 * @param bool $force Whether to get options from cache or not.
	 * @param bool $include_sensitive Whether to include sensitive options or not.
	 *
	 * @return array Array of options.
	 */
	public static function get_options( $force = false, $include_sensitive = false ) {
		if ( is_array( self::$options ) && ! $force ) {
			return self::$options;
		}
		if ( Functions::is_multisite() ) {
			$options = get_site_option( 'wppic_settings', array() );
		} else {
			$options = get_option( 'wppic_settings', array() );
		}

		$defaults      = self::get_defaults();
		$options       = wp_parse_args( $options, $defaults );
		self::$options = $options;

		/**
		 * Filter the options before they are returned.
		 *
		 * @param array  $options The options to be output.
		 */
		$options = apply_filters(
			'wppic_options_home',
			$options,
		);
		if ( ! $include_sensitive ) {
			unset( $options['github_info_card_token'] );
		}
		self::$options = $options;
		return $options;
	}

	/**
	 * Get defaults for SCE options
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array default options
	 */
	public static function get_defaults() {
		$defaults = array(
			'default_layout'                            => 'card',
			'colorscheme'                               => 'default',
			'widget'                                    => false,
			'ajax'                                      => false,
			'enqueue'                                   => false,
			'credit'                                    => false,
			'cache_expiration'                          => 3600,
			'ping_rest_api_interval'                    => 14, /* days */
			'enable_screenshots'                        => true,
			'enable_local_screenshots'                  => false,
			'skip_animated_gifs'                        => true,
			'enable_local_screenshots_download_missing' => false,
			'enable_local_screenshots_keep_current'     => false,
			'enable_local_screenshots_cli_command'      => false,
			'screenshots_table_version'                 => '0.0.0',
			'enable_edd'                                => false,
			'edd_default_icon_id'                       => 0,
			'edd_default_banner_id'                     => 0,
			'edd_default_icon_url'                      => '',
			'edd_default_banner_url'                    => '',
			'enable_rest_api'                           => false,
			'enable_custom_plugins'                     => true,
			'enable_github_info_cards'                  => false,
			'github_info_cards_token'                   => '',
			'github_info_cards_unauthenticated'         => false,
			'github_info_cards_rate_limit'              => 0,
			'github_info_cards_rate_remaining'          => 0,
			'github_info_cards_rate_reset'              => 0,
		);
		return $defaults;
	}

	/**
	 * Check if the GitHub Info Cards are enabled.
	 *
	 * @return bool True if the GitHub Info Cards are enabled, false otherwise.
	 */
	public static function is_github_info_cards_enabled() {
		$options           = self::get_options();
		$github_enabled    = (bool) $options['enable_github_info_cards'];
		$github_token      = sanitize_text_field( $options['github_info_cards_token'] );
		$github_rate_limit = (int) $options['github_info_cards_rate_limit'];
		$unauthenticated   = ! empty( $options['github_info_cards_unauthenticated'] );
		if ( ! $github_enabled ) {
			return false;
		}
		// Public repositories can be requested without a token.
		if ( $unauthenticated && empty( $github_token ) ) {
			return true;
		}
		if ( ! empty( $github_token ) && $github_rate_limit > 0 ) {
			return true;
		}
		return false;
	}
}
