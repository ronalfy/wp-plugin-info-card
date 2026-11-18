<?php
/**
 * Set up add plugin functionality.
 *
 * @package WPPIC
 */

namespace MediaRon\WPPIC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class for for add plugin functionality.
 */
class Add_Plugin {

	/**
	 * Class runner.
	 */
	public function run() {
		$self = new self();

		add_filter( 'wppic_add_api_parser', array( $self, 'api_parser' ), 9, 5 );
		add_filter( 'wppic_add_template', array( $self, 'plugin_template' ), 9, 2 );
		add_filter( 'wppic_add_mce_type', array( $self, 'mce_type' ) );
		add_filter( 'wppic_add_list_form', array( $self, 'list_form' ) );
		add_filter( 'wppic_add_widget_type', array( $self, 'widget_type' ) );
		add_filter( 'wppic_add_list_valdiation', array( $self, 'list_validation' ) );
		add_filter( 'wppic_add_widget_item', array( $self, 'widget_item' ), 9, 3 );

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
	 * @return object $wppic_data The data object.
	 */
	public function api_parser( $wppic_data, $type, $slug, $load_attachments = false, $force = false ) {
		if ( 'plugin' === $type ) {

			// Format slug to strip forward and trailing slashes.
			$slug = trim( $slug, '/' );

			/**
			 * Allow for custom plugin info.
			 *
			 * @param bool   $maybe_plugin_info The plugin info.
			 * @param string $slug              The plugin slug.
			 * @param string $type              The plugin type.
			 * @param bool   $force             Force refresh.
			 *
			 * @return bool|array {
			 *      @type string $name                The plugin name.
			 *      @type string $slug                The plugin slug.
			 *      @type string $version             The plugin version.
			 *      @type string $author              The plugin author.
			 *      @type string $author_profile      The plugin author profile.
			 *      @type string $contributors        Comma-separated contributors.
			 *      @type string $requires            The plugin requires.
			 *      @type string $tested              The plugin tested.
			 *      @type string $requires            The plugin requires.
			 *      @type string $rating              The plugin rating in percentage.
			 *      @type string $num_ratings         The plugin number of ratings.
			 *      @type string $ratings             The plugin ratings.
			 *      @type string $downloaded          The plugin downloaded.
			 *      @type string $active_installs     The plugin active installs.
			 *      @type string $last_updated        The plugin last updated.
			 *      @type string $last_updated_mk     The plugin last updated.
			 *      @type string $added               The plugin added.
			 *      @type string $homepage            The plugin homepage.
			 *      @type string $short_description   The plugin short description.
			 *      @type string $download_link       The plugin download link.
			 *      @type string $donate_link         The plugin donate link.
			 *      @type array  $icons               The plugin icons.
			 *      @type array  $banners             The plugin banners.
			 *      @type array  $screenshots         The plugin screenshots.
			 * }
			 *
			 * @since 5.2.0
			 */
			$maybe_plugin_info = apply_filters( 'wppic_plugin_info', false, $slug, $type, $force );
			if ( false !== $maybe_plugin_info ) {
				$wppic_data = (object) $maybe_plugin_info;
				return $wppic_data;
			}

			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			$plugin_info = plugins_api(
				'plugin_information',
				array(
					'slug'   => $slug,
					'is_ssl' => is_ssl(),
					'fields' => array(
						'sections'          => false,
						'tags'              => false,
						'short_description' => true,
						'icons'             => true,
						'banners'           => true,
						'reviews'           => false,
						'active_installs'   => true,
						'screenshots'       => true,
					),
				)
			);

			if ( ! is_wp_error( $plugin_info ) ) {
				// Get contributors and format them.
				$contributors = array();
				if ( is_array( $plugin_info->contributors ) ) {
					foreach ( $plugin_info->contributors as $contributor => $contributor_info ) {
						$contributors[] = $contributor;
					}
				} else {
					$contributors[] = $plugin_info->contributors;
				}
				$wppic_data = (object) array(
					'url'                     => 'https://wordpress.org/plugins/' . $slug . '/',
					'name'                    => $plugin_info->name,
					'slug'                    => $slug,
					'version'                 => $plugin_info->version,
					'author'                  => wp_strip_all_tags( $plugin_info->author ),
					'author_profile'          => $plugin_info->author_profile,
					'contributors'            => implode( ', ', $contributors ),
					'tested'                  => $plugin_info->tested,
					'requires'                => $plugin_info->requires,
					'rating'                  => $plugin_info->rating,
					'num_ratings'             => $plugin_info->num_ratings,
					'ratings'                 => $plugin_info->ratings,
					'downloaded'              => $plugin_info->active_installs,
					'active_installs'         => $plugin_info->active_installs,
					'last_updated_human_time' => human_time_diff( strtotime( $plugin_info->last_updated ), current_datetime()->getTimestamp() ),
					'last_updated'            => $plugin_info->last_updated,
					'last_updated_mk'         => $plugin_info->last_updated,
					'added'                   => $plugin_info->added,
					'homepage'                => $plugin_info->homepage,
					'short_description'       => $plugin_info->short_description,
					'download_link'           => $plugin_info->download_link,
					'donate_link'             => $plugin_info->donate_link,
					'icons'                   => $plugin_info->icons,
					'banners'                 => $plugin_info->banners,
					'screenshots'             => $plugin_info->screenshots,
					'is_edd'                  => false,
					'review_url'              => '',
					'requires_php'            => $plugin_info->requires_php,
				);
			}
		}

		return $wppic_data;
	}

	/**
	 * Load a plugin template.
	 *
	 * @param string $content The content.
	 * @param array  $data    Plugin data.
	 */
	public function plugin_template( $content, $data ) {
		$type       = $data[0];
		$wppic_data = $data[1]; // $wppic_data is used in the included templates.
		$image      = $data[2]; // $image is used in the included templates.
		$layout     = '-' . sanitize_key( $data[3] );

		if ( 'plugin' === $type ) {

			// load custom user template if exists.
			$wppic_template_file = '/wppic-templates/wppic-template-plugin';

			ob_start();
			if ( file_exists( get_stylesheet_directory() . $wppic_template_file . $layout . '.php' ) ) {
				include get_stylesheet_directory() . $wppic_template_file . $layout . '.php';
			} elseif ( file_exists( Functions::get_plugin_dir( 'templates/wppic-template-plugin' . $layout . '.php' ) ) ) {
				include Functions::get_plugin_dir( 'templates/wppic-template-plugin' . $layout . '.php' );
			} else {
				include Functions::get_plugin_dir( 'templates/wppic-template-plugin.php' );
			}
			$content .= ob_get_clean();

		}

		return $content;
	}

	/**
	 * Add MCE types for the plugins.
	 *
	 * @param array $parameters Array of MCE types.
	 *
	 * @return array $parameters Array of MCE types.
	 */
	public function mce_type( $parameters ) {
		$parameters['types'][] = array(
			'text'  => __( 'Plugin', 'wp-plugin-info-card' ),
			'value' => 'plugin',
		);
		return $parameters;
	}

	/**
	 * Add MCE list form for the plugins.
	 *
	 * @param array $parameters Array of MCE types.
	 *
	 * @return array $parameters Array of MCE types.
	 */
	public function list_form( $parameters ) {
		$parameters[] = array(
			'list',
			__( 'Add a plugin', 'wp-plugin-info-card' ),
			__(
				'Please refer to the plugin URL on wordpress.org to determine its slug',
				'wp-plugin-info-card'
			),
			'https://wordpress.org/plugins/<strong>THE-SLUG</strong>/',
		);
		return $parameters;
	}

	/**
	 * Add MCE list validation for the plugins.
	 *
	 * @param array $parameters Array of MCE types.
	 *
	 * @return array $parameters Array of MCE types.
	 */
	public function list_validation( $parameters ) {
		$parameters[] = array(
			'list',
			__( 'is not a valid plugin name format. This key has been deleted.', 'wp-plugin-info-card' ),
			'/^[a-z0-9\-]+$/',
		);
		return $parameters;
	}

	/**
	 * Add MCE widget type for the plugins.
	 *
	 * @param array $parameters Array of MCE types.
	 *
	 * @return array $parameters Array of MCE types.
	 */
	public function widget_type( $parameters ) {
		$parameters[] = array( 'plugin', 'list', __( 'Plugins', 'wp-plugin-info-card' ) );
		return $parameters;
	}

	/**
	 * Output the plugin widget.
	 *
	 * @param string $content    The content output.
	 * @param object $wppic_data The plugin data.
	 * @param string $type       Plugin or Theme.
	 *
	 * @return string $content.
	 */
	public function widget_item( $content, $wppic_data, $type ) {
		if ( 'plugin' === $type ) {

			$date_format = Options::get_date_format();

			$wppic_data->last_updated = date_i18n( $date_format, strtotime( $wppic_data->last_updated ) );

			$content .= '<div class="wp-pic-item">';
			$content .= '<a class="wp-pic-widget-name" href="' . esc_url( $wppic_data->url ) . '" target="_blank" title="' . __( 'WordPress.org Plugin Page', 'wp-plugin-info-card' ) . '">' . esc_html( $wppic_data->name ) . '</a>';
			$content .= '<span class="wp-pic-widget-rating"><span>' . __( 'Ratings:', 'wp-plugin-info-card' ) . '</span> ' . esc_html( $wppic_data->rating ) . '%';
			if ( ! empty( $wppic_data->num_ratings ) ) {
				$content .= ' (' . esc_html( $wppic_data->num_ratings ) . esc_html__( ' votes', 'wp-plugin-info-card' ) . ')';
			}
			$content .= '</span>';
			if ( isset( $wppic_data->active_installs ) ) {
				$content .= '<span class="wp-pic-widget-downloaded"><span>' . __( 'Active Installs:', 'wp-plugin-info-card' ) . '</span> ' . esc_html( number_format_i18n( $wppic_data->active_installs ) ) . '+</span>';
			}
			$content .= '<p class="wp-pic-widget-updated"><span>' . __( 'Last Updated:', 'wp-plugin-info-card' ) . '</span> ' . esc_html( $wppic_data->last_updated );
			if ( ! empty( $wppic_data->version ) ) {
				$content .= ' (v.' . esc_html( $wppic_data->version ) . ' )';
			}
			$content .= '</p>';
			$content .= '</div>';

		}
		return $content;
	}
}
