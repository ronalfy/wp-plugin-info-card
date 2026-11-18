<?php
/**
 * Output EDD wppic tab.
 *
 * @package wppic
 */

namespace MediaRon\WPPIC;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access.' );
}

/**
 * Output the EDD tab and content.
 */
class EDD {

	/**
	 * License totals stay cached for 12 hours. Callers cannot shorten this.
	 */
	const LICENSE_STATS_TTL = 720 * MINUTE_IN_SECONDS;

	/**
	 * Main class runner.
	 *
	 * @return Blocks.
	 */
	public function run() {
		$self = new self();

		$edd_enabled = false;
		$options     = Options::get_options();
		if ( isset( $options['enable_edd'] ) && (bool) $options['enable_edd'] ) {
			$edd_enabled = true;
		}

		// Only if EDD is enabled and software licensing is active.
		if ( $edd_enabled && Functions::is_activated( 'edd-software-licensing/edd-software-licenses.php' ) ) {
			add_filter( 'wppic_plugin_info', array( $this, 'add_edd_data' ), 10, 4 );
			add_filter( 'wppic_api_data', array( $this, 'apply_license_stats' ), 10, 3 );
			add_action( 'init', array( $this, 'init' ) );

			// Modify downloads post type to support custom fields.
			add_action( 'init', array( $this, 'add_custom_fields_to_downloads' ), 100 );

			// Enqueue the sidebar for EDD post type.
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );

			// For clearing plugin cache when a download is updated.
			add_action( 'save_post', array( $this, 'clear_plugin_cache' ), 10 );

			// For saving the classic editor meta data.
			add_action( 'save_post', array( $this, 'save_meta_data' ), 10, 1 );

			// Filter the plugin data before it is output.
			add_filter( 'wppic_data_pre_display', array( $this, 'modify_wppic_data' ), 10, 2 );
		}

		return $self;
	}

	/**
	 * Save the meta box data for the classic editor.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_meta_data( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['wppic-plugin-author'] ) ) {
			return;
		}
		if ( ! isset( $_POST['wppic-reviews-url'] ) ) {
			return;
		}
		if ( ! isset( $_POST['wppic-downloads-url'] ) ) {
			return;
		}
		if ( ! isset( $_POST['wppic-override-ratings'] ) ) {
			return;
		}
		if ( ! isset( $_POST['wppic-num-ratings'] ) ) {
			return;
		}
		if ( ! isset( $_POST['wppic-rating-percentage'] ) ) {
			return;
		}

		// Verify nonce for saving post meta.
		check_admin_referer( 'update-post_' . $post_id );

		$author            = sanitize_text_field( wp_unslash( $_POST['wppic-plugin-author'] ) );
		$reviews_url       = sanitize_text_field( wp_unslash( $_POST['wppic-reviews-url'] ) );
		$downloads_url     = sanitize_text_field( wp_unslash( $_POST['wppic-downloads-url'] ) );
		$override_ratings  = filter_var( wp_unslash( $_POST['wppic-override-ratings'] ), FILTER_VALIDATE_BOOLEAN );
		$num_ratings       = absint( wp_unslash( $_POST['wppic-num-ratings'] ) );
		$rating_percentage = absint( wp_unslash( $_POST['wppic-rating-percentage'] ) );

		update_post_meta( $post_id, '_wppic_plugin_author', $author );
		update_post_meta( $post_id, '_wppic_reviews_url', $reviews_url );
		update_post_meta( $post_id, '_wppic_downloads_url', $downloads_url );
		update_post_meta( $post_id, '_wppic_override_ratings', $override_ratings );
		update_post_meta( $post_id, '_wppic_num_ratings', $num_ratings );
		update_post_meta( $post_id, '_wppic_rating_percentage', $rating_percentage );
	}

	/**
	 * Registers the EDD block.
	 */
	public function init_edd_block() {
		register_block_type(
			Functions::get_plugin_dir( 'build/blocks/EDDCardGrid/block.json' ),
			array(
				'render_callback' => array( $this, 'edd_card_grid_output' ),
			)
		);
	}

	/**
	 * Render the main info card block for EDD.
	 *
	 * @param array $attributes Array of block attributes.
	 *
	 * @return string Block rendered.
	 */
	public function edd_card_grid_output( $attributes ) {
		if ( is_admin() ) {
			return;
		}

		$shortcode_atts = array(
			'id'     => $attributes['uniqueId'],
			'cols'   => $attributes['cols'],
			'colGap' => $attributes['colGap'],
			'rowGap' => $attributes['rowGap'],
			'scheme' => $attributes['scheme'],
			'align'  => $attributes['align'],
			'layout' => $attributes['layout'],
		);
		return '';
	}

	/**
	 * Modify the plugin data before it is output.
	 *
	 * @param object $existing_data Existing plugin data.
	 * @param string $slug          Plugin slug.
	 *
	 * @return array
	 */
	public function modify_wppic_data( $existing_data, $slug ) {
		if ( ! function_exists( 'edd_get_download' ) ) {
			return $existing_data;
		}
		if ( isset( $existing_data->is_edd ) && $existing_data->is_edd ) {
			$maybe_download = edd_get_download( $slug );
			if ( ! $maybe_download ) {
				return $existing_data;
			}

			// Get review URL.
			$reviews_url = get_post_meta( $maybe_download->ID, '_wppic_reviews_url', true );
			if ( $reviews_url ) {
				$existing_data->reviews_url = esc_url_raw( $reviews_url );
			} else {
				$existing_data->reviews_url = get_permalink( $maybe_download->ID );
			}
		}

		return $existing_data;
	}

	/**
	 * Clear the plugin cache when a download is updated.
	 *
	 * @param int $post_id Post ID.
	 */
	public function clear_plugin_cache( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( 'download' !== get_post_type( $post_id ) ) {
			return;
		}
		// Do nonce check.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$slug       = get_post_field( 'post_name', $post_id );
		$option_key = sanitize_key( 'wppic_plugin_' . preg_replace( '/\-/', '_', $slug ) );

		// Clear the cache.
		delete_transient( $option_key );
		delete_option( $option_key );
		delete_transient( $this->license_stats_key( $post_id ) );
	}

	/**
	 * Add custom fields to the downloads post type.
	 */
	public function add_custom_fields_to_downloads() {
		// Add custom fields support to the downloads post type.
		add_post_type_support( 'download', 'custom-fields' );
	}

	/**
	 * Enqueue block editor assets.
	 */
	public function enqueue_block_editor_assets() {
		if ( 'download' !== get_post_type() ) {
			return;
		}
		$deps = require Functions::get_plugin_dir( 'build/edd-sidebar.asset.php' );
		wp_enqueue_script(
			'wppic-edd',
			Functions::get_plugin_url( 'build/edd-sidebar.js' ),
			$deps['dependencies'],
			$deps['version'],
			true
		);
	}

	/**
	 * Initialize the meta boxes.
	 */
	public function init() {
		register_post_meta(
			'download',
			'_wppic_plugin_author',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'type'              => 'string',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
				'single'            => true,
				'default'           => '',
			)
		);
		register_post_meta(
			'download',
			'_wppic_reviews_url',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'type'              => 'string',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
				'single'            => true,
				'default'           => '',
			)
		);
		register_post_meta(
			'download',
			'_wppic_downloads_url',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'type'              => 'string',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
				'single'            => true,
				'default'           => '',
			)
		);
		register_post_meta(
			'download',
			'_wppic_override_ratings',
			array(
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => true,
				'type'              => 'boolean',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
				'single'            => true,
				'default'           => false,
			)
		);
		register_post_meta(
			'download',
			'_wppic_num_ratings', /* number of ratings */
			array(
				'sanitize_callback' => 'absint',
				'show_in_rest'      => true,
				'type'              => 'integer',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
				'single'            => true,
				'default'           => 0,
			)
		);
		register_post_meta(
			'download',
			'_wppic_rating_percentage', /* e.g., 96 for 96 */
			array(
				'sanitize_callback' => 'absint',
				'show_in_rest'      => true,
				'type'              => 'integer',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
				'single'            => true,
				'default'           => 0,
			)
		);

		// Add the meta boxes for classic editor.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}

	/**
	 * Add the meta boxes for the classic editor.
	 */
	public function add_meta_boxes() {
		// Only show if the classic editor is enabled for the download.
		if ( use_block_editor_for_post_type( 'download' ) ) {
			return;
		}
		add_meta_box(
			'wppic-edd-meta-box',
			__( 'Plugin Info Card', 'wp-plugin-info-card' ),
			array( $this, 'classic_editor_meta_box_output' ),
			'download',
			'side',
			'high'
		);
	}

	/**
	 * Output the meta box for the classic editor.
	 *
	 * @param object $post Post object.
	 */
	public function classic_editor_meta_box_output( $post ) {
		$author            = get_post_meta( $post->ID, '_wppic_plugin_author', true );
		$reviews_url       = get_post_meta( $post->ID, '_wppic_reviews_url', true );
		$downloads_url     = get_post_meta( $post->ID, '_wppic_downloads_url', true );
		$override_ratings  = (bool) get_post_meta( $post->ID, '_wppic_override_ratings', true );
		$num_ratings       = get_post_meta( $post->ID, '_wppic_num_ratings', true );
		$rating_percentage = get_post_meta( $post->ID, '_wppic_rating_percentage', true );

		?>
		<div class="wppic-edd-meta-box">
			<p>
				<label for="wppic-plugin-author"><?php esc_html_e( 'Plugin Author', 'wp-plugin-info-card' ); ?></label>
				<input type="text" id="wppic-plugin-author" name="wppic-plugin-author" value="<?php echo esc_attr( $author ); ?>" class="widefat">
			</p>
			<p>
				<label for="wppic-reviews-url"><?php esc_html_e( 'Reviews URL', 'wp-plugin-info-card' ); ?></label>
				<input type="text" id="wppic-reviews-url" name="wppic-reviews-url" value="<?php echo esc_attr( $reviews_url ); ?>" class="widefat">
			</p>
			<p>
				<label for="wppic-downloads-url"><?php esc_html_e( 'Downloads URL', 'wp-plugin-info-card' ); ?></label>
				<input type="text" id="wppic-downloads-url" name="wppic-downloads-url" value="<?php echo esc_attr( $downloads_url ); ?>" class="widefat">
			</p>
			<p>
				<label for="wppic-override-ratings"><?php esc_html_e( 'Override Ratings', 'wp-plugin-info-card' ); ?></label>
				<input type="checkbox" id="wppic-override-ratings" name="wppic-override-ratings" value="1" <?php checked( $override_ratings, true ); ?>>
			</p>
			<p>
				<label for="wppic-num-ratings"><?php esc_html_e( 'Number of Ratings', 'wp-plugin-info-card' ); ?></label>
				<input type="number" id="wppic-num-ratings" name="wppic-num-ratings" value="<?php echo esc_attr( $num_ratings ); ?>" class="widefat">
			</p>
			<p>
				<label for="wppic-rating-percentage"><?php esc_html_e( 'Rating Percentage', 'wp-plugin-info-card' ); ?></label>
				<input type="number" id="wppic-rating-percentage" name="wppic-rating-percentage" value="<?php echo esc_attr( $rating_percentage ); ?>" class="widefat">
			</p>
		</div>
		<?php
	}

	/**
	 * Add EDD data to the plugin info card.
	 *
	 * @param array  $existing_data Existing plugin data.
	 * @param string $slug          Plugin slug.
	 * @param string $type          Plugin type.
	 * @param bool   $force         Force refresh.
	 *
	 * @return array
	 */
	public function add_edd_data( $existing_data, $slug, $type, $force ) {
		if ( ! function_exists( 'edd_get_download' ) ) {
			return $existing_data;
		}
		$maybe_download = edd_get_download( $slug );
		if ( ! $maybe_download ) {
			return $existing_data;
		}

		$options = Options::get_options();

		$existing_data = array();

		// Get plugin name.
		$existing_data['name'] = \get_the_title( $maybe_download->ID );

		// Get slug.
		$existing_data['slug'] = $slug;

		// Get plugin version.
		$existing_data['version'] = sanitize_text_field( get_post_meta( $maybe_download->ID, '_edd_sl_version', true ) );

		// Get the required versions.
		$required_versions = get_post_meta( $maybe_download->ID, '_edd_sl_required_versions', true ); // format is: php, wp.
		if ( $required_versions ) {
			if ( isset( $required_versions['wp'] ) ) {
				$existing_data['requires'] = $required_versions['wp'];
			}
			if ( isset( $required_versions['php'] ) ) {
				$existing_data['requires_php'] = $required_versions['php'];
			}
		}

		// Get custom EDD Purchase URL, or use the default.
		$maybe_download_url = get_post_meta( $maybe_download->ID, '_wppic_downloads_url', true );
		$download_url       = $maybe_download_url ? $maybe_download_url : get_permalink( $maybe_download->ID );

		// Get the short description from excerpt. Overwrite with readme later if needed.
		$existing_data['short_description'] = get_the_excerpt( $maybe_download->ID );

		// Parse the plugin's readme.
		$readme_url  = get_post_meta( $maybe_download->ID, '_edd_readme_location', true );
		$readme_data = false;

		// Get the author and link to the download page for the plugin.
		$existing_data['author'] = get_the_author_meta( 'display_name', $maybe_download->post_author );

		// Try to get author from meta and override if set.
		$maybe_author = get_post_meta( $maybe_download->ID, '_wppic_plugin_author', true );
		if ( $maybe_author ) {
			$existing_data['author'] = $maybe_author;
		}

		// Author/Download URL.
		$readme_homepage = get_post_meta( $maybe_download->ID, '_edd_readme_plugin_homepage', true );
		if ( $readme_homepage ) {
			$existing_data['homepage'] = $readme_homepage;
		} else {
			$existing_data['homepage'] = $download_url;
		}

		// Set the download link to the download page.
		$existing_data['download_link'] = $download_url;

		// Get the URL for the download.
		$existing_data['url'] = $download_url;

		// Set last updated and mk.
		$existing_data['last_updated']    = get_the_modified_date( 'Y-m-d', $maybe_download->ID );
		$existing_data['last_updated_mk'] = get_the_modified_date( 'Y-m-d', $maybe_download->ID );

		// Set the plugin added date.
		$existing_data['added'] = get_the_date( 'Y-m-d', $maybe_download->ID );

		// Parse the readme and override if necessary.
		if ( function_exists( '_edd_sl_get_readme_data' ) ) {
			$readme_data = _edd_sl_get_readme_data( $readme_url );

			// overwrite requires header with readme.
			if ( isset( $readme_data['requires'] ) ) {
				$existing_data['requires'] = $readme_data['requires'];
			}

			// Overwrite tested header with readme.
			if ( isset( $readme_data['tested'] ) ) {
				$existing_data['tested'] = $readme_data['tested'];
			}

			// Overwrite contributors header with readme.
			if ( isset( $readme_data['contributors'] ) ) {
				$existing_data['contributors'] = $readme_data['contributors'];
			}

			// If short description set in readme, use it.
			if ( isset( $readme_data['short_description'] ) ) {
				$existing_data['short_description'] = $readme_data['short_description'];
			}
		}

		// Get the featured image URL which will be used for the icons. Format in array is 1x, 2x.
		$post_thumbnail_id = get_post_thumbnail_id( $maybe_download->ID );
		if ( ! $post_thumbnail_id ) {
			// Try to get from options.
			$icon_id = (int) $options['edd_default_icon_id'];
			if ( 0 !== $icon_id ) {
				$post_thumbnail_id = $icon_id;
			}
		}
		$featured_image = wp_get_attachment_image_src( $post_thumbnail_id );
		if ( $featured_image ) {
			$existing_data['icons'] = array(
				'1x' => $featured_image[0],
				'2x' => $featured_image[0],
			);
		} else {
			$existing_data['icons'] = array();
		}

		// Get the plugin banners.
		$banner_high = get_post_meta( $maybe_download->ID, '_edd_readme_plugin_banner_high', true );
		$banner_low  = get_post_meta( $maybe_download->ID, '_edd_readme_plugin_banner_low', true );
		if ( $banner_high && $banner_low ) {
			$existing_data['banners'] = array(
				'high' => $banner_high,
				'low'  => $banner_low,
			);
		} else {
			// Try to get from options.
			$banner_id = (int) $options['edd_default_banner_id'];
			if ( 0 !== $banner_id ) {
				$banner = wp_get_attachment_image_src( $banner_id, 'full' );
				if ( $banner ) {
					$existing_data['banners'] = array(
						'high' => $banner[0],
						'low'  => $banner[0],
					);
				}
			}
			if ( ! isset( $existing_data['banners'] ) ) {
				$existing_data['banners'] = array();
			}
		}

		// Set screenshots.
		$existing_data['screenshots'] = array();

		// Check post meta to see if we're overriding a rating.
		$override_rating = (bool) get_post_meta( $maybe_download->ID, '_wppic_override_ratings', true );
		if ( $override_rating ) {
			$existing_data['rating']      = get_post_meta( $maybe_download->ID, '_wppic_rating_percentage', true );
			$existing_data['num_ratings'] = get_post_meta( $maybe_download->ID, '_wppic_num_ratings', true );
			$existing_data['ratings']     = array();
		} else {
			// Get rating from meta.
			$maybe_rating = get_post_meta( $maybe_download->ID, 'edd_reviews_average_rating', true );
			if ( $maybe_rating ) {
				$existing_data['rating'] = round( $maybe_rating * 20, 2 );

				// Reviews are comments, so get the number of comments.
				$existing_data['num_ratings'] = get_comments_number( $maybe_download->ID );
				$existing_data['ratings']     = array();
			}
		}

		// Set the review URL from meta, or download.
		$reviews_url = get_post_meta( $maybe_download->ID, '_wppic_reviews_url', true );
		if ( $reviews_url ) {
			$existing_data['reviews_url'] = esc_url_raw( $reviews_url );
		} else {
			$existing_data['reviews_url'] = get_permalink( $maybe_download->ID );
		}

		// Set EDD flag.
		$existing_data['is_edd'] = true;

		return $existing_data;
	}

	/**
	 * Attach or remove EDD license totals after the card cache is read.
	 *
	 * Anonymous REST responses never read or write the stats transient.
	 * An editor REST request recounts. Every other request uses the cache
	 * and fills it only when the transient is missing.
	 *
	 * @param mixed  $wppic_data Card data.
	 * @param string $type       Asset type.
	 * @param string $slug       Asset slug.
	 *
	 * @return mixed
	 */
	public function apply_license_stats( $wppic_data, $type, $slug ) {
		if ( 'plugin' !== $type || ! is_object( $wppic_data ) ) {
			return $wppic_data;
		}
		if ( empty( $wppic_data->is_edd ) || ! empty( $wppic_data->is_custom_plugin ) ) {
			return $wppic_data;
		}

		$wppic_data = clone $wppic_data;
		unset( $wppic_data->active_installs, $wppic_data->active_installs_raw, $wppic_data->downloaded );

		$mode = $this->license_stats_mode();
		if ( 'omit' === $mode || ! function_exists( 'edd_get_download' ) ) {
			return $wppic_data;
		}

		$download = edd_get_download( $slug );
		if ( ! $download || empty( $download->ID ) ) {
			return $wppic_data;
		}

		$stats = ( 'live' === $mode )
			? $this->refresh_license_stats( $download->ID )
			: $this->get_cached_license_stats( $download->ID );

		$wppic_data->active_installs = $stats['active_installs'];
		$wppic_data->downloaded      = $stats['downloaded'];

		return $wppic_data;
	}

	/**
	 * Decide whether this request may see EDD license totals.
	 *
	 * @return string omit, live, or cache.
	 */
	private function license_stats_mode() {
		$is_rest = defined( 'REST_REQUEST' ) && REST_REQUEST;
		if ( $is_rest && current_user_can( 'edit_posts' ) ) {
			return 'live';
		}
		if ( $is_rest ) {
			return 'omit';
		}
		return 'cache';
	}

	/**
	 * Transient key for one download's license totals.
	 *
	 * @param int $download_id Download ID.
	 *
	 * @return string
	 */
	private function license_stats_key( $download_id ) {
		return 'wppic_edd_stats_' . absint( $download_id );
	}

	/**
	 * Count active licenses and read the lifetime download total.
	 *
	 * @param int $download_id Download ID.
	 *
	 * @return array{active_installs:int,downloaded:int}
	 */
	private function collect_license_stats( $download_id ) {
		$stats = array(
			'active_installs' => 0,
			'downloaded'      => absint( get_post_meta( $download_id, 'edd_sl_download_lifetime', true ) ),
		);

		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return $stats;
		}

		$licenses_db = edd_software_licensing()->licenses_db;
		if ( ! is_object( $licenses_db ) ) {
			return $stats;
		}

		// COUNT the active rows. Do not load every license with number -1.
		$query_args = array(
			'download_id' => absint( $download_id ),
			'status'      => 'active',
		);
		if ( method_exists( $licenses_db, 'count' ) ) {
			$active_installs = $licenses_db->count( $query_args );
		} else {
			$active_installs = $licenses_db->get_licenses( $query_args );
		}
		if ( is_numeric( $active_installs ) ) {
			$stats['active_installs'] = absint( $active_installs );
		}

		return $stats;
	}

	/**
	 * Return cached license totals, counting once when the cache is empty.
	 *
	 * @param int $download_id Download ID.
	 *
	 * @return array{active_installs:int,downloaded:int}
	 */
	private function get_cached_license_stats( $download_id ) {
		$key    = $this->license_stats_key( $download_id );
		$cached = get_transient( $key );
		if ( is_array( $cached ) && isset( $cached['active_installs'], $cached['downloaded'] ) ) {
			return array(
				'active_installs' => absint( $cached['active_installs'] ),
				'downloaded'      => absint( $cached['downloaded'] ),
			);
		}

		$stats = $this->collect_license_stats( $download_id );
		set_transient( $key, $stats, self::LICENSE_STATS_TTL );

		return $stats;
	}

	/**
	 * Recount license totals and replace the stats cache.
	 *
	 * @param int $download_id Download ID.
	 *
	 * @return array{active_installs:int,downloaded:int}
	 */
	private function refresh_license_stats( $download_id ) {
		$stats = $this->collect_license_stats( $download_id );
		set_transient( $this->license_stats_key( $download_id ), $stats, self::LICENSE_STATS_TTL );

		return $stats;
	}
}
