<?php
/**
 * Set up GitHub functionality.
 *
 * @package WPPIC
 */

namespace MediaRon\WPPIC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class for for GitHub functionality.
 */
class GitHub {

	/**
	 * Class runner.
	 */
	public function run() {
		$self = new self();

		add_filter( 'wppic_add_api_parser', array( $self, 'api_parser' ), 9, 5 );

		return $self;
	}

	/**
	 * Parse the API for WordPress Plugins.
	 *
	 * @param object $wppic_data       The data object.
	 * @param string $type             plugin or theme.
	 * @param string $slug             Slug of the plugin.
	 * @param bool   $load_attachments Load attachments.
	 * @param bool   $force            Force refresh.
	 *
	 * @return array|false $wppic_data The data object. False if the GitHub Info Cards are not enabled or on failure.
	 */
	public function api_parser( $wppic_data, $type, $slug, $load_attachments = false, $force = false ) {
		if ( 'github' === $type ) {

			// $slug is in username/repo format.
			$api_url = sprintf(
				'https://api.github.com/repos/%s',
				$slug
			);

			if ( ! Options::is_github_info_cards_enabled() ) {
				return false;
			}

			// Get options.
			$options = Options::get_options( false, true );
			$token   = $options['github_info_cards_token'];

			$headers = array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			);

			$response = wp_remote_get( esc_url( $api_url ), array( 'headers' => $headers ) );
			if ( is_wp_error( $response ) ) {
				return false;
			}

			// Check error code.
			$response_code = wp_remote_retrieve_response_code( $response );
			if ( 200 !== $response_code ) {
				return false;
			}

			$github_data = json_decode( wp_remote_retrieve_body( $response ) );

			$keys_to_extract = array(
				'name'              => 'name',
				'full_name'         => 'full_name',
				'login'             => 'organization/login',
				'description'       => 'description',
				'org_url'           => 'organization/html_url',
				'github_url'        => 'html_url',
				'is_private'        => 'private',
				'license'           => 'license/spdx_id',
				'avatar'            => 'organization/avatar_url',
				'stargazers_count'  => 'stargazers_count',
				'watchers_count'    => 'watchers_count', /* this would be the same as stargazers_count */
				'subscribers_count' => 'subscribers_count',
				'forks_count'       => 'forks_count',
				'updated_at'        => 'updated_at',
				'created_at'        => 'created_at',
				'pushed_at'         => 'pushed_at',
				'homepage'          => 'homepage',
				'language'          => 'language',
			);

			$alt_keys_to_extract = array(
				'login'   => 'owner/login',
				'avatar'  => 'owner/avatar_url',
				'org_url' => 'owner/html_url',
			);

			$wppic_data = array();
			foreach ( $keys_to_extract as $target_key => $github_path ) {
				$value = $this->get_nested_value( $github_data, $github_path );
				if ( null !== $value ) {
					$wppic_data[ $target_key ] = $value;
				} else {
					$wppic_data[ $target_key ] = '';
				}
			}

			// Extract alt keys.
			foreach ( $alt_keys_to_extract as $target_key => $github_path ) {
				$value = $this->get_nested_value( $github_data, $github_path );
				if ( null !== $value && ! empty( $value ) ) {
					if ( ! isset( $wppic_data[ $target_key ] ) || empty( $wppic_data[ $target_key ] ) ) {
						$wppic_data[ $target_key ] = $value;
					}
				}
			}

			/**
			 * Get sponsor URL.
			 */
			$wppic_data['sponsors_url'] = '';
			$sponsors_url               = sprintf(
				'https://api.github.com/repos/%s/contents/.github/FUNDING.yml',
				$slug
			);
			$sponsors_response          = wp_remote_get( esc_url( $sponsors_url ), array( 'headers' => $headers ) );
			if ( ! is_wp_error( $sponsors_response ) && 200 === wp_remote_retrieve_response_code( $sponsors_response ) ) {
				$sponsors_data = json_decode( wp_remote_retrieve_body( $sponsors_response ) );
				if ( ! empty( $sponsors_data ) ) {
					$yml_content = base64_decode( $sponsors_data->content );
					$yml_data    = $this->parse_simple_yaml( $yml_content );
					if ( ! empty( $yml_data ) ) {
						$wppic_data['sponsors_url'] = esc_url(
							sprintf(
								'https://github.com/sponsors/%s',
								preg_replace( '/[\[\]]/', '', $yml_data['github'] )
							)
						);
					}
				}
			}

			// Repo `has_downloads` is the old Downloads feature (removed 2012) and is always false now.
			// Releases live at /releases/latest. A 404 means the repo has no published release.
			$wppic_data['latest_release_tag_name']     = '';
			$wppic_data['latest_release_download_url'] = '';
			$wppic_data['latest_release_url']          = '';
			$releases_url                              = sprintf(
				'https://api.github.com/repos/%s/releases/latest',
				$slug
			);
			$releases_response                         = wp_remote_get( esc_url( $releases_url ), array( 'headers' => $headers ) );
			if ( ! is_wp_error( $releases_response ) && 200 === wp_remote_retrieve_response_code( $releases_response ) ) {
				$latest_release = json_decode( wp_remote_retrieve_body( $releases_response ) );
				if ( ! empty( $latest_release ) && ! empty( $latest_release->tag_name ) ) {
					$wppic_data['latest_release_tag_name'] = $latest_release->tag_name;
					if ( ! empty( $latest_release->assets ) ) {
						$current_asset                             = current( $latest_release->assets );
						$wppic_data['latest_release_download_url'] = $current_asset->browser_download_url;
					}
					$wppic_data['latest_release_url'] = $latest_release->html_url;
				}
			}

			// Get default full_name if not set.
			if ( empty( $wppic_data['name'] ) ) {
				$wppic_data['name'] = $wppic_data['login'];
			}

			// Sanitize the data.
			$wppic_data = Functions::sanitize_array_recursive( $wppic_data );

			// Return in object format.
			return json_decode( wp_json_encode( $wppic_data ) );
		}

		return $wppic_data;
	}

	/**
	 * Get a nested value from an object/array using dot notation.
	 *
	 * @param object|array $data The data object or array.
	 * @param string       $path The path to the value (e.g., 'organization/login').
	 *
	 * @return mixed|null The value if found, null otherwise.
	 */
	private function get_nested_value( $data, $path ) {
		$keys    = explode( '/', $path );
		$current = $data;

		foreach ( $keys as $key ) {
			if ( is_object( $current ) && isset( $current->$key ) ) {
				$current = $current->$key;
			} elseif ( is_array( $current ) && isset( $current[ $key ] ) ) {
				$current = $current[ $key ];
			} else {
				return null;
			}
		}

		return $current;
	}

	/**
	 * Parse a simple YAML string.
	 *
	 * @param string $yaml The YAML string to parse.
	 *
	 * @return array The parsed YAML data.
	 */
	private function parse_simple_yaml( $yaml ) {
		$result = array();
		$lines  = preg_split( '/\r\n|\r|\n/', $yaml );

		$current_key = null;
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' === $line || strpos( $line, '#' ) === 0 ) {
				continue; // skip blank lines & comments.
			}

			// Key: value format.
			if ( preg_match( '/^([A-Za-z0-9_]+):\s*(.+)?$/', $line, $matches ) ) {
				$key = $matches[1];
				$val = $matches[2] ?? '';
				if ( '' === $val ) {
					$result[ $key ] = array();
					$current_key    = $key;
				} else {
					$result[ $key ] = $val;
					$current_key    = null;
				}
			} elseif ( $current_key && preg_match( '/^- (.+)$/', $line, $matches ) ) {
				$result[ $current_key ][] = trim( $matches[1] );
			}
		}
		return $result;
	}
}
