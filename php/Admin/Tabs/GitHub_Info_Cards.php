<?php
/**
 * Output Custom Plugin wppic tab.
 *
 * @package wppic
 */

namespace MediaRon\WPPIC\Admin\Tabs;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access.' );
}

use MediaRon\WPPIC\Functions;
use MediaRon\WPPIC\Options;


/**
 * Output the custom plugin tab and content.
 */
class GitHub_Info_Cards {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'wppic_admin_tabs', array( $this, 'add_github_info_cards_tab' ), 1, 1 );
		add_filter( 'wppic_admin_sub_tabs', array( $this, 'add_github_info_cards_sub_tab' ), 1, 3 );
		add_action( 'wppic_output_github-info-cards', array( $this, 'output_github_info_cards_content' ), 1, 2 );
		add_action( 'wppic_admin_enqueue_scripts_github-info-cards', array( $this, 'admin_scripts' ) );
		add_action( 'wp_ajax_wppic_get_github_info_cards_options', array( $this, 'ajax_get_options' ) );
		add_action( 'wp_ajax_wppic_authenticate_with_github', array( $this, 'ajax_authenticate_with_github' ) );
		add_action( 'wp_ajax_wppic_revoke_github_token', array( $this, 'ajax_revoke_github_token' ) );
	}

	/**
	 * Include admin scripts for the home screen.
	 */
	public function admin_scripts() {
		$deps = require Functions::get_plugin_dir( 'dist/wppic-admin-github-info-cards.asset.php' );
		// Rename wp-escapeHtml to wp-escape-html.
		foreach ( $deps['dependencies'] as $key => $value ) {
			if ( 'wp-escapeHtml' === $value ) {
				$deps['dependencies'][ $key ] = 'wp-escape-html';
			}
		}
		wp_enqueue_script(
			'wppic-admin-github-info-cards',
			Functions::get_plugin_url( 'dist/wppic-admin-github-info-cards.js' ),
			$deps['dependencies'],
			$deps['version'],
			true
		);
		wp_localize_script(
			'wppic-admin-github-info-cards',
			'wppicAdminGitHubInfoCards',
			array(
				'getNonce'    => wp_create_nonce( 'wppic-admin-github-info-cards-retrieve-options' ),
				'saveNonce'   => wp_create_nonce( 'wppic-admin-github-info-cards-authenticate-with-github' ),
				'revokeNonce' => wp_create_nonce( 'wppic-admin-github-info-cards-revoke-token' ),
			)
		);

		// Enqueue media library.
		wp_enqueue_media();
	}

	/**
	 * Retrieve options via Ajax for the home options.
	 */
	public function ajax_get_options() {
		$nonce = sanitize_text_field( filter_input( INPUT_POST, 'nonce', FILTER_DEFAULT ) );
		// Security.
		if ( ! wp_verify_nonce( $nonce, 'wppic-admin-github-info-cards-retrieve-options' ) || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Could not verify nonce.', 'wp-plugin-info-card' ),
				)
			);
		}

		$options = $this->get_sanitized_options();
		wp_send_json_success( $options );
	}

	/**
	 * Authenticate with GitHub via Ajax.
	 */
	public function ajax_authenticate_with_github() {
		$nonce = sanitize_text_field( filter_input( INPUT_POST, 'nonce', FILTER_DEFAULT ) );
		// Security.
		if ( ! wp_verify_nonce( $nonce, 'wppic-admin-github-info-cards-authenticate-with-github' ) || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Could not verify nonce.', 'wp-plugin-info-card' ),
				)
			);
		}

		$enable_github_info_cards = (bool) filter_input( INPUT_POST, 'enable_github_info_cards', FILTER_VALIDATE_BOOLEAN );
		$github_info_cards_token  = sanitize_text_field( filter_input( INPUT_POST, 'github_info_cards_token', FILTER_DEFAULT ) );
		$unauthenticated          = (bool) filter_input( INPUT_POST, 'github_info_cards_unauthenticated', FILTER_VALIDATE_BOOLEAN );

		// A stored token always uses authenticated requests.
		if ( ! empty( $github_info_cards_token ) ) {
			$unauthenticated = false;
		}

		if ( $unauthenticated ) {
			$github_info_cards_token = '';
		}

		$limits = $this->get_github_rate_limits( $github_info_cards_token );

		// SAve options.
		$options                                      = Options::get_options( false, true );
		$options['enable_github_info_cards']          = $enable_github_info_cards;
		$options['github_info_cards_token']           = $github_info_cards_token;
		$options['github_info_cards_unauthenticated'] = $unauthenticated;
		$options['github_info_cards_rate_limit']      = $limits['rate_limit'];
		$options['github_info_cards_rate_remaining']  = $limits['rate_remaining'];
		$options['github_info_cards_rate_reset']      = $limits['rate_reset'];
		Options::update_options( $options );

		// Get options for return.
		$options = $this->get_sanitized_options();
		wp_send_json_success( $options );
	}

	/**
	 * Revoke the GitHub token via Ajax.
	/**
	 * Revoke the GitHub token via Ajax.
	 */
	public function ajax_revoke_github_token() {
		$nonce = sanitize_text_field( filter_input( INPUT_POST, 'nonce', FILTER_DEFAULT ) );
		// Security.
		if ( ! wp_verify_nonce( $nonce, 'wppic-admin-github-info-cards-revoke-token' ) || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Could not verify nonce.', 'wp-plugin-info-card' ),
				)
			);
		}

		$options                                      = Options::get_options( false, true );
		$options['enable_github_info_cards']          = false;
		$options['github_info_cards_token']           = '';
		$options['github_info_cards_unauthenticated'] = false;
		$options['github_info_cards_rate_limit']      = 0;
		$options['github_info_cards_rate_remaining']  = 0;
		$options['github_info_cards_rate_reset']      = 0;
		Options::update_options( $options );

		wp_send_json_success(
			array(
				'message' => __( 'GitHub token revoked.', 'wp-plugin-info-card' ),
			)
		);
	}

	/**
	 * Get the sanitized options.
	 *
	 * @return array The sanitized options.
	 */
	private function get_sanitized_options() {
		$options = Options::get_options( false, true );

		// Mask the token.
		$maybe_token = $options['github_info_cards_token'];
		if ( ! empty( $maybe_token ) ) {
			$maybe_token = substr( $maybe_token, 0, 6 ) . str_repeat( '*', strlen( $maybe_token ) - 6 );
		} else {
			$maybe_token = '';
		}

		$unauthenticated = ! empty( $options['github_info_cards_unauthenticated'] ) && empty( $options['github_info_cards_token'] );

		// Refresh the rate limit for a token, or for anonymous public requests.
		if ( $options['enable_github_info_cards'] && ! empty( $options['github_info_cards_token'] ) ) {
			$limits                                      = $this->get_github_rate_limits( $options['github_info_cards_token'] );
			$options['github_info_cards_rate_limit']     = $limits['rate_limit'];
			$options['github_info_cards_rate_remaining'] = $limits['rate_remaining'];
			$options['github_info_cards_rate_reset']     = $limits['rate_reset'];
			Options::update_options( $options );
		} elseif ( $options['enable_github_info_cards'] && $unauthenticated ) {
			$limits                                      = $this->get_github_rate_limits( '' );
			$options['github_info_cards_rate_limit']     = $limits['rate_limit'];
			$options['github_info_cards_rate_remaining'] = $limits['rate_remaining'];
			$options['github_info_cards_rate_reset']     = $limits['rate_reset'];
			Options::update_options( $options );
		} else {
			$options['github_info_cards_rate_limit']     = 0;
			$options['github_info_cards_rate_remaining'] = 0;
			$options['github_info_cards_rate_reset']     = 0;
			Options::update_options( $options );
		}

		$options['github_info_cards_token'] = $maybe_token;

		// Format reset date into a readable format.
		if ( ! empty( $options['github_info_cards_rate_reset'] ) ) {
			$options['github_info_cards_rate_reset'] = gmdate( 'Y-m-d H:i:s', $options['github_info_cards_rate_reset'] );
		}
		$options = Functions::sanitize_array_recursive( $options );
		return $options;
	}
	/**
	 * Get the GitHub rate limits.
	 *
	 * @param string $github_info_cards_token The GitHub API token.
	 *
	 * @return array The GitHub rate limits.
	 */
	public function get_github_rate_limits( $github_info_cards_token ) {
		$headers = array();
		// Omit Authorization when no token is stored. An empty credential is a 401.
		if ( ! empty( $github_info_cards_token ) ) {
			$headers['Authorization'] = 'token ' . $github_info_cards_token;
		}

		// Let's try to authenticate with GitHub.
		$response = wp_safe_remote_get(
			'https://api.github.com/rate_limit',
			array(
				'headers' => $headers,
			)
		);
		if ( is_wp_error( $response ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Could not authenticate with GitHub.', 'wp-plugin-info-card' ),
				)
			);
		}

		$body       = json_decode( wp_remote_retrieve_body( $response ), true );
		$error_code = wp_remote_retrieve_response_code( $response );
		if ( 401 === $error_code || 404 === $error_code ) {
			wp_send_json_error(
				array(
					'message' => ! empty( $github_info_cards_token )
						? __( 'Invalid GitHub API token.', 'wp-plugin-info-card' )
						: __( 'Could not authenticate with GitHub.', 'wp-plugin-info-card' ),
				)
			);
		}

		// Try to get rate limit.
		$rate = isset( $body['rate'] ) ? $body['rate'] : null;
		if ( ! $rate ) {
			wp_send_json_error(
				array(
					'message' => __( 'Could not get rate limit.', 'wp-plugin-info-card' ),
				)
			);
		}
		$rate_limit     = absint( $rate['limit'] );
		$rate_remaining = absint( $rate['remaining'] );
		$rate_reset     = absint( $rate['reset'] );

		return array(
			'rate_limit'     => $rate_limit,
			'rate_remaining' => $rate_remaining,
			'rate_reset'     => $rate_reset,
		);
	}
	/**
	 * Add the home tab and callback actions.
	 *
	 * @param array $tabs Array of tabs.
	 *
	 * @return array of tabs.
	 */
	public function add_github_info_cards_tab( $tabs ) {
		$tabs[] = array(
			'get'    => 'github-info-cards',
			'action' => 'wppic_output_github-info-cards',
			'url'    => Functions::get_settings_url( 'github-info-cards' ),
			'label'  => _x( 'GitHub Info Cards', 'Tab label as GitHub Info Cards', 'wp-plugin-info-card' ),
			'icon'   => 'wppic-github-icon',
		);
		return $tabs;
	}

	/**
	 * Add the home home tab and callback actions.
	 *
	 * @param array  $tabs        Array of tabs.
	 * @param string $current_tab The current tab selected.
	 * @param string $sub_tab     The current sub-tab selected.
	 *
	 * @return array of tabs.
	 */
	public function add_github_info_cards_sub_tab( $tabs, $current_tab, $sub_tab ) {
		if ( ( ! empty( $current_tab ) || ! empty( $sub_tab ) ) && 'github-info-cards' !== $current_tab ) {
			return $tabs;
		}
		return $tabs;
	}

	/**
	 * Begin Main routing for the various outputs.
	 *
	 * @param string $tab     Main tab.
	 * @param string $sub_tab Sub tab.
	 */
	public function output_github_info_cards_content( $tab, $sub_tab = '' ) {
		if ( 'github-info-cards' === $tab ) {
			?>
			<div id="wppic-tab-github-info-cards"></div>
			<?php
		}
	}
}
