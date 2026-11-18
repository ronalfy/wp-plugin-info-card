<?php
/**
 * Set up the shortcodes and its output.
 *
 * @package WPPIC
 */

namespace MediaRon\WPPIC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class for for shortcode functionality.
 */
class Shortcodes {

	/**
	 * Main class runner.
	 *
	 * @return Admin.
	 */
	public function run() {
		$self = new self();

		add_action( 'wp_enqueue_scripts', array( $self, 'print_scripts' ) );
		add_action( 'wppic_enqueue_scripts', array( $self, 'enqueue_scripts' ) );
		add_action( 'rest_api_init', array( $self, 'register_rest_routes' ) );
		add_shortcode( 'wp-pic', array( static::class, 'shortcode_function' ) );
		add_shortcode( 'wp-pic-query', array( static::class, 'shortcode_query_function' ) );
		add_shortcode( 'wp-pic-site-plugins', array( static::class, 'shortcode_active_site_plugins_function' ) );
		add_shortcode( 'wp-pic-plugin-screenshots', array( static::class, 'shortcode_plugin_screenshots_info_card' ) );
		add_shortcode( 'github-info-card', array( static::class, 'shortcode_github_info_card' ) );
		add_shortcode( 'wp-pic-badges', array( static::class, 'shortcode_profile_badges' ) );
		add_action( 'wp_ajax_async_wppic_shortcode_content', array( static::class, 'shortcode_content' ) );
		add_action( 'wp_ajax_nopriv_async_wppic_shortcode_content', array( static::class, 'shortcode_content' ) );
		add_action( 'init', array( static::class, 'register_screenshots_presets_post_type' ) );
		add_action( 'init', array( static::class, 'register_custom_plugin_post_type' ) );
		if ( self::is_foobox_lightbox_active() ) {
			add_filter( 'foobox_js_selectors', array( static::class, 'add_foobox_screenshot_selector' ) );
		}
		return $self;
	}

	/**
	 * Whether FooBox Premium should open plugin screenshot lightboxes.
	 *
	 * @return bool
	 */
	public static function is_foobox_lightbox_active() {
		return class_exists( 'fooboxV2' );
	}

	/**
	 * Register the plugin screenshots carousel as a FooBox container.
	 *
	 * @param array $selectors Existing FooBox container selectors.
	 * @return array
	 */
	public static function add_foobox_screenshot_selector( $selectors ) {
		if ( ! is_array( $selectors ) ) {
			$selectors = array();
		}

		$selectors[] = '.wppic-screenshot-fancyapps';

		return $selectors;
	}

	/**
	 * Register plugin screenshots post type.
	 */
	public static function register_screenshots_presets_post_type() {
		$labels = array(
			'name'               => __( 'Presets', 'wp-plugin-info-card' ),
			'singular_name'      => __( 'Presets', 'wp-plugin-info-card' ),
			'add_new'            => __( 'Add New', 'wp-plugin-info-card' ),
			'add_new_item'       => __( 'Add New Preset', 'wp-plugin-info-card' ),
			'edit_item'          => __( 'Edit Preset', 'wp-plugin-info-card' ),
			'new_item'           => __( 'New Preset', 'wp-plugin-info-card' ),
			'all_items'          => __( 'All Presets', 'wp-plugin-info-card' ),
			'view_item'          => __( 'View Preset', 'wp-plugin-info-card' ),
			'search_items'       => __( 'Search Presets', 'wp-plugin-info-card' ),
			'not_found'          => __( 'No Presets found', 'wp-plugin-info-card' ),
			'not_found_in_trash' => __( 'No Presets found in Trash', 'wp-plugin-info-card' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Presets', 'wp-plugin-info-card' ),
		);

		$args = array(
			'labels'                  => $labels,
			'public'                  => false,
			'publicly_queryable'      => false,
			'show_ui'                 => false,
			'show_in_menu'            => false,
			'query_var'               => false,
			'rewrite'                 => false,
			'dlx_photo_block_archive' => false,
			'hierarchical'            => false,
		);

		register_post_type( 'wppic_screen_presets', $args );

		// Post type for author profiles.
		$labels = array(
			'name'               => __( 'Profiles', 'wp-plugin-info-card' ),
			'singular_name'      => __( 'Profile', 'wp-plugin-info-card' ),
			'add_new'            => __( 'Add New', 'wp-plugin-info-card' ),
			'add_new_item'       => __( 'Add New Profile', 'wp-plugin-info-card' ),
			'edit_item'          => __( 'Edit Profile', 'wp-plugin-info-card' ),
			'new_item'           => __( 'New Profile', 'wp-plugin-info-card' ),
			'all_items'          => __( 'All Profiles', 'wp-plugin-info-card' ),
			'view_item'          => __( 'View Profile', 'wp-plugin-info-card' ),
			'search_items'       => __( 'Search Profiles', 'wp-plugin-info-card' ),
			'not_found'          => __( 'No Profiles found', 'wp-plugin-info-card' ),
			'not_found_in_trash' => __( 'No Profiles found in Trash', 'wp-plugin-info-card' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Profiles', 'wp-plugin-info-card' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'query_var'          => false,
			'rewrite'            => false,
			'hierarchical'       => false,
		);

		register_post_type( 'wppic_profiles', $args );
	}

	/**
	 * Register plugin screenshots post type.
	 */
	public static function register_custom_plugin_post_type() {
		$labels = array(
			'name'               => __( 'Custom Plugins', 'wp-plugin-info-card' ),
			'singular_name'      => __( 'Custom Plugin', 'wp-plugin-info-card' ),
			'add_new'            => __( 'Add New', 'wp-plugin-info-card' ),
			'add_new_item'       => __( 'Add New Custom Plugin', 'wp-plugin-info-card' ),
			'edit_item'          => __( 'Edit Custom Plugin', 'wp-plugin-info-card' ),
			'new_item'           => __( 'New Custom Plugin', 'wp-plugin-info-card' ),
			'all_items'          => __( 'All Custom Plugins', 'wp-plugin-info-card' ),
			'view_item'          => __( 'View Custom Plugin', 'wp-plugin-info-card' ),
			'search_items'       => __( 'Search Custom Plugins', 'wp-plugin-info-card' ),
			'not_found'          => __( 'No Custom Plugins found', 'wp-plugin-info-card' ),
			'not_found_in_trash' => __( 'No Custom Plugins found in Trash', 'wp-plugin-info-card' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Custom Plugins', 'wp-plugin-info-card' ),
		);

		$args = array(
			'labels'                  => $labels,
			'public'                  => false,
			'publicly_queryable'      => false,
			'show_ui'                 => false,
			'show_in_menu'            => false,
			'query_var'               => false,
			'rewrite'                 => false,
			'dlx_photo_block_archive' => false,
			'hierarchical'            => false,
		);

		register_post_type( 'wppic_custom_plugins', $args );

		register_meta(
			'wppic_custom_plugins',
			'enableRestApi',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'boolean',
			)
		);
		register_meta(
			'wppic_custom_plugins',
			'isFromRest',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'boolean',
			)
		);
		register_meta(
			'wppic_custom_plugins',
			'restApiPasscode',
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		register_meta(
			'wppic_custom_plugins',
			'restApiDataVersion',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'integer',
			)
		);
	}

	/**
	 * Enqueue scripts on the frontend.
	 */
	public function enqueue_scripts() {
		$min_or_not = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		// This enqueue works for block themes where styles/scripts can be run late.
		wp_enqueue_style(
			'wppic-style',
			Functions::get_plugin_url( 'dist/wppic-styles.css' ),
			array(),
			Functions::get_plugin_version(),
			'all'
		);
		wp_enqueue_script(
			'wppic-script',
			Functions::get_plugin_url( 'assets/js/wppic-script' . $min_or_not . '.js' ),
			array( 'jquery' ),
			Functions::get_plugin_version(),
			true
		);
		wp_localize_script(
			'wppic-script',
			'wppicAjax',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);

		// This next filter runs late, and if the scripts aren't output yet, manually print them for classic/hybrid themes.
		add_action(
			'wp_footer',
			function () {
				if ( ! wp_style_is( 'dashicons', 'registered' ) ) {
					wp_print_styles( 'dashicons' );
				}
				if ( ! wp_style_is( 'wppic-style', 'done' ) ) {
					wp_print_styles( 'wppic-style' );
				}
				if ( ! wp_script_is( 'wppic-script', 'done' ) ) {
					wp_print_scripts( 'wppic-script' );
				}
			},
			100
		);

		/**
		 * Add icons to footer for plugin card.
		 */
		add_action( 'wp_footer', array( __CLASS__, 'add_icons_to_footer' ) );
	}

	/**
	 * Add scripts/styles to the footer.
	 */
	public function print_scripts() {
		$options = Options::get_options();

		if ( isset( $options['enqueue'] ) && true === $options['enqueue'] ) {
			if ( 0 === did_action( 'wppic_enqueue_scripts' ) ) {
				do_action( 'wppic_enqueue_scripts' );
			}
		}
	}

	/**
	 * Register route for getting plugin shortcode
	 *
	 * @since 3.0.0
	 */
	public function register_rest_routes() {
		register_rest_route(
			'wppic/v1',
			'/get_html',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_base_shortcode' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			'wppic/v2',
			'/get_data',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_asset_data' ),
				'permission_callback' => '__return_true',
			)
		);
		$query_route_args = $this->get_query_route_args();
		register_rest_route(
			'wppic/v1',
			'/get_query',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_query_shortcode' ),
				'permission_callback' => array( $this, 'rest_check_permissions' ),
				'args'                => $query_route_args,
			)
		);
		register_rest_route(
			'wppic/v2',
			'/get_query',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_query_shortcode_v2' ),
				'permission_callback' => array( $this, 'rest_check_permissions' ),
				'args'                => $query_route_args,
			)
		);
		register_rest_route(
			'wppic/v2',
			'/get_site_plugins',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_site_plugin_data' ),
				'permission_callback' => array( $this, 'rest_check_permissions' ),
			)
		);

		register_rest_route(
			'wppic/v2',
			'/get_github_data',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_github_data' ),
				'permission_callback' => array( $this, 'rest_check_permissions' ),
				'args'                => array(
					'username' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_title',
					),
					'repo'     => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_title',
					),
					'force'    => array(
						'type'              => 'boolean',
						'sanitize_callback' => function ( $value ) {
							return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
						},
					),
				),
			)
		);

		/**
		 * Register REST API for getting GitHub card HTML.
		 */
		register_rest_route(
			'wppic/v2',
			'/get_github_card_html',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'get_github_card_html' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'repo'           => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_title',
					),
					'username'       => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_title',
					),
					'token'          => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'cardAttributes' => array(
						'type'       => 'object',
						'properties' => array(
							'align'                   => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'avatarimageurl'          => array(
								'type'              => 'string',
								'sanitize_callback' => 'esc_url_raw',
							),
							'backgroundcolor'         => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'bordercolor'             => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'borderradius'            => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'borderwidth'             => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'cardstyle'               => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'colgap'                  => array(
								'type'              => 'integer',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'cols'                    => array(
								'type'              => 'integer',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'homepageurloverride'     => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'horizontalalign'         => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'iconcolor'               => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'iconcolorhover'          => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'languagebgcolor'         => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'languagetextcolor'       => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'layout'                  => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'loginoverride'           => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'marginspacing'           => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'marginspacingtarget'     => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'nameoverride'            => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'numchildren'             => array(
								'type'              => 'integer',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'organizationurloverride' => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'overridebutton'          => array(
								'type'              => 'boolean',
								'sanitize_callback' => function ( $value ) {
									return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
								},
							),
							'overridebuttontext'      => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'overridebuttonurl'       => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'parentbuttontype'        => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'repo'                    => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_title',
							),
							'rowgap'                  => array(
								'type'              => 'integer',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'showauthorbar'           => array(
								'type'              => 'boolean',
								'sanitize_callback' => function ( $value ) {
									return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
								},
							),
							'showlastupdated'         => array(
								'type'              => 'boolean',
								'sanitize_callback' => function ( $value ) {
									return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
								},
							),
							'showstatsbar'            => array(
								'type'              => 'boolean',
								'sanitize_callback' => function ( $value ) {
									return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
								},
							),
							'showtopbar'              => array(
								'type'              => 'boolean',
								'sanitize_callback' => function ( $value ) {
									return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
								},
							),
							'sponsorscolor'           => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'sponsorsurloverride'     => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'style'                   => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'textcolor'               => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'uniqueid'                => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'username'                => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_title',
							),
							'versionoverride'         => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
						),
					),
				),
			)
		);

		register_rest_route(
			'wppic/v2',
			'/get_profile_data',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_profile_data' ),
				'permission_callback' => array( $this, 'rest_check_permissions' ),
			)
		);

		/**
		 * Register REST API for getting profile badges HTML.
		 */
		register_rest_route(
			'wppic/v2',
			'/get_profile_badges_html',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'get_profile_badges_html' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'authorSlug' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'required'          => true,
					),
					'token'      => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
						'required'          => true,
					),
				),
			)
		);
	}

	/**
	 * Get .org username profile data for return.
	 *
	 * @param array $request Request data.
	 */
	public function get_profile_data( $request ) {
		// Get username.
		$username = isset( $request['author'] ) ? sanitize_text_field( $request['author'] ) : '';
		$force    = isset( $request['force'] ) ? filter_var( $request['force'], FILTER_VALIDATE_BOOLEAN ) : false;
		// If no username, return error.
		if ( empty( $username ) ) {
			wp_send_json_error( array( 'message' => 'No username provided' ) );
		}

		// Get user profile data from .org.
		$user_data = Functions::get_org_profile_data( $username, $force );

		// If no error, return data.
		if ( ! is_wp_error( $user_data ) ) {
			wp_send_json_success( $user_data );
		}

		// Return error if no data found.
		wp_send_json_error( array( 'message' => 'No data found' ) );
	}

	/**
	 * Get plugin data for return.
	 *
	 * @param object $request Request data.
	 */
	public function get_github_data( $request ) {

		$username = sanitize_key( strtolower( (string) $request->get_param( 'username' ) ) );
		$repo     = sanitize_key( strtolower( (string) $request->get_param( 'repo' ) ) );
		$force    = false;
		// The route permission already requires edit_posts. Check again so a reused callback cannot honor force anonymously.
		if ( current_user_can( 'edit_posts' ) ) {
			$force = (bool) $request->get_param( 'force' );
		}

		if ( empty( $username ) || empty( $repo ) ) {
			wp_send_json_error( array( 'message' => 'Invalid username or repo.' ) );
		}

		$github_data = wppic_api_parser( 'github', $username . '/' . $repo, 720, '', false, $force );

		if ( empty( $github_data ) ) {
			wp_send_json_error( array( 'message' => 'No data found.' ) );
		}

		wp_send_json_success( $github_data );
	}

	/**
	 * Get plugin data for return.
	 *
	 * @param array $request Request data.
	 */
	public function get_site_plugin_data( $request ) {
		// Get plugin data for active plugins.
		$plugins_on_org = Functions::get_active_plugins_with_data();

		if ( empty( $plugins_on_org ) ) {
			wp_send_json_success(
				array(
					'page'                => 1,
					'more_results'        => false,
					'plugins'             => array(),
					'num_plugins'         => 0,
					'percentage_complete' => 100,
				)
			);
		}

		// Get pagination.
		$per_page = 5;
		$page     = isset( $request['page'] ) ? absint( $request['page'] ) : 1;

		// Get plugins for page.
		$more_results   = true;
		$return_plugins = array_slice( $plugins_on_org, ( $page - 1 ) * $per_page, $per_page );
		if ( empty( $return_plugins ) ) {
			$more_results = false;
		}

		// Query plugins and return.
		foreach ( $return_plugins as $file => $plugin_data ) {
			$return_plugins[ $file ] = wppic_api_parser( 'plugin', $plugin_data['slug'], HOUR_IN_SECONDS );
		}

		// Get next page.
		$next_page = $page + 1;

		// Get percentage processed with page and per_page calculation.
		$percentage = ( ( $page * $per_page ) / count( $plugins_on_org ) ) * 100;
		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		$return_plugins = json_decode( wp_json_encode( $return_plugins ) );

		// Get .org plugins.
		wp_send_json_success(
			array(
				'page'                => absint( $next_page ),
				'more_results'        => $more_results,
				'plugins'             => $return_plugins,
				'num_plugins'         => count( $plugins_on_org ),
				'percentage_complete' => $percentage,
			)
		);
	}

	/**
	 * Get EDD plugin data for return.
	 *
	 * @param array $request Request data.
	 */
	protected function get_edd_plugin_downloads( $request ) {
		// Get plugin data for active plugins.
		if ( ! Functions::is_edd_installed() || ! function_exists( 'edd_get_download' ) ) {
			wp_send_json_error( array( 'message' => 'No plugins found.' ) );
		}

		// Get options and check if EDD is enabled.
		$options = Options::get_options();

		// Check if EDD is enabled.
		if ( ! (bool) $options['enable_edd'] ) {
			wp_send_json_error( array( 'message' => 'No plugins found.' ) );
		}

		// Get pagination.
		$per_page = 5;
		$page     = isset( $request['page'] ) ? absint( $request['page'] ) : 1;
		$order_by = isset( $request['orderby'] ) ? sanitize_text_field( $request['orderby'] ) : 'title';
		$order    = isset( $request['order'] ) ? sanitize_text_field( $request['order'] ) : 'ASC';

		$edd_query_args = array(
			'post_type'      => 'download',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'meta_query'     => array(
				array(
					'key'     => '_edd_sl_enabled',
					'value'   => '1',
					'compare' => '=',
				),
			),
			'orderby'        => $order_by,
			'order'          => $order,
		);

		$edd_query = new \WP_Query( $edd_query_args );

		// Return early if no posts found.
		if ( ! $edd_query->have_posts() ) {
			\wp_send_json_success(
				array(
					'page'                => 1,
					'more_results'        => false,
					'plugins'             => array(),
					'num_plugins'         => 0,
					'percentage_complete' => 100,
				)
			);
		}

		// Get download IDs.
		$download_ids = wp_list_pluck( $edd_query->posts, 'ID' );

		// Gather downloads.
		$downloads = array();
		foreach ( $download_ids as $download_id ) {
			$download                          = \edd_get_download( $download_id );
			$downloads[ $download->post_name ] = wppic_api_parser( 'plugin', $download->post_name, HOUR_IN_SECONDS );
		}

		// Get next page.
		$next_page = $page + 1;

		// Get percentage processed with page and per_page calculation.
		$percentage = ( ( $page * $per_page ) / $edd_query->max_num_pages ) * 100;
		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		$return_plugins = json_decode( wp_json_encode( $downloads ) );

		// Get .org plugins.
		wp_send_json_success(
			array(
				'page'                => absint( $next_page ),
				'more_results'        => true,
				'plugins'             => $return_plugins,
				'num_plugins'         => $edd_query->max_num_pages,
				'percentage_complete' => $percentage,
			)
		);
	}

	/**
	 * Check if user has access to REST API.
	 */
	public function rest_check_permissions() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Check if user has access to REST API for retrieving and sideloading images.
	 */
	public function rest_image_sideload_permissions() {
		return current_user_can( 'publish_posts' );
	}

	/**
	 * Get the main (plugin info card) shortcode.
	 */
	public function get_base_shortcode() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reject GitHub before any remote fetch. This public route does not authorize that type.
		$requested_type = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : '';
		if ( 'github' === $requested_type ) {
			return new \WP_Error(
				'wppic_github_rest_forbidden',
				__( 'GitHub cards cannot be loaded from this route.', 'wp-plugin-info-card' ),
				array( 'status' => 403 )
			);
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Public read-only card render. No state change.
		$attrs = array(
			'type'        => isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : '',
			'slug'        => isset( $_GET['slug'] ) ? sanitize_text_field( wp_unslash( $_GET['slug'] ) ) : '',
			'image'       => isset( $_GET['image'] ) ? esc_url_raw( wp_unslash( $_GET['image'] ) ) : '',
			'align'       => isset( $_GET['align'] ) ? sanitize_key( wp_unslash( $_GET['align'] ) ) : '',
			'containerid' => isset( $_GET['containerid'] ) ? sanitize_text_field( wp_unslash( $_GET['containerid'] ) ) : '',
			'margin'      => isset( $_GET['margin'] ) ? sanitize_text_field( wp_unslash( $_GET['margin'] ) ) : '',
			'clear'       => isset( $_GET['clear'] ) ? sanitize_key( wp_unslash( $_GET['clear'] ) ) : '',
			// Public callers cannot choose the cache lifetime. The parser default is 720 minutes.
			'expiration'  => '',
			'ajax'        => isset( $_GET['ajax'] ) ? sanitize_key( wp_unslash( $_GET['ajax'] ) ) : '',
			'scheme'      => isset( $_GET['scheme'] ) ? sanitize_key( wp_unslash( $_GET['scheme'] ) ) : '',
			'layout'      => isset( $_GET['layout'] ) ? sanitize_key( wp_unslash( $_GET['layout'] ) ) : '',
			'multi'       => isset( $_GET['multi'] ) ? filter_var( wp_unslash( $_GET['multi'] ), FILTER_VALIDATE_BOOLEAN ) : false,
		);
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		die( self::shortcode_function( $attrs ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Card HTML is escaped in the shortcode templates.
	}

	/**
	 * Shared arguments for the directory query routes.
	 *
	 * @return array Route args.
	 */
	private function get_query_route_args() {
		$text = static function ( $value ) {
			$value = sanitize_text_field( (string) $value );
			if ( strlen( $value ) > 100 ) {
				$value = substr( $value, 0, 100 );
			}
			return $value;
		};
		$key  = static function ( $value ) {
			return sanitize_key( (string) $value );
		};

		return array(
			'type'        => array(
				'type'              => 'string',
				'sanitize_callback' => $key,
			),
			'per_page'    => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'search'      => array(
				'type'              => 'string',
				'sanitize_callback' => $text,
			),
			'tag'         => array(
				'type'              => 'string',
				'sanitize_callback' => $text,
			),
			'author'      => array(
				'type'              => 'string',
				'sanitize_callback' => $text,
			),
			'user'        => array(
				'type'              => 'string',
				'sanitize_callback' => $text,
			),
			'browse'      => array(
				'type'              => 'string',
				'sanitize_callback' => $text,
			),
			'sortby'      => array(
				'type'              => 'string',
				'sanitize_callback' => $key,
			),
			'sort'        => array(
				'type'              => 'string',
				'sanitize_callback' => $text,
			),
			'searchBy'    => array(
				'type'              => 'string',
				'sanitize_callback' => $key,
			),
			'cols'        => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'image'       => array(
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
			),
			'align'       => array(
				'type'              => 'string',
				'sanitize_callback' => $key,
			),
			'containerid' => array(
				'type'              => 'string',
				'sanitize_callback' => $text,
			),
			'margin'      => array(
				'type'              => 'string',
				'sanitize_callback' => $text,
			),
			'clear'       => array(
				'type'              => 'string',
				'sanitize_callback' => $key,
			),
			'expiration'  => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'ajax'        => array(
				'type'              => 'string',
				'sanitize_callback' => $key,
			),
			'scheme'      => array(
				'type'              => 'string',
				'sanitize_callback' => $key,
			),
			'layout'      => array(
				'type'              => 'string',
				'sanitize_callback' => $key,
			),
		);
	}

	/**
	 * Run one directory query for the block editor.
	 *
	 * v2 only applies the field selected by searchBy. v1 accepts each field that is present.
	 * The editor draws cards from api_response, so this does not render shortcode HTML.
	 *
	 * @param WP_REST_Request $request         REST request.
	 * @param bool            $apply_search_by Whether to gate fields with searchBy.
	 * @return WP_REST_Response|WP_Error
	 */
	private function query_directory_for_rest( $request, $apply_search_by ) {
		$type = sanitize_key( (string) $request->get_param( 'type' ) );
		if ( ! in_array( $type, array( 'plugin', 'theme' ), true ) ) {
			return new \WP_Error(
				'wppic_query_type_invalid',
				__( 'Query type must be plugin or theme.', 'wp-plugin-info-card' ),
				array( 'status' => 400 )
			);
		}

		$per_page = absint( $request->get_param( 'per_page' ) );
		if ( $per_page < 1 || $per_page > 24 ) {
			$per_page = 24;
		}

		$search_by = sanitize_key( (string) $request->get_param( 'searchBy' ) );
		$search    = '';
		$tag       = '';
		$author    = '';
		$user      = '';
		$browse    = '';

		if ( $apply_search_by ) {
			if ( 'general' === $search_by ) {
				$search = sanitize_text_field( (string) $request->get_param( 'search' ) );
			} elseif ( 'tag' === $search_by ) {
				$tag = sanitize_text_field( (string) $request->get_param( 'tag' ) );
			} elseif ( 'author' === $search_by ) {
				$author = sanitize_text_field( (string) $request->get_param( 'author' ) );
			} elseif ( 'favorites' === $search_by ) {
				$user = sanitize_text_field( (string) $request->get_param( 'user' ) );
			} elseif ( 'category' === $search_by ) {
				$browse = sanitize_text_field( (string) $request->get_param( 'browse' ) );
			}
		} else {
			$search = sanitize_text_field( (string) $request->get_param( 'search' ) );
			$tag    = sanitize_text_field( (string) $request->get_param( 'tag' ) );
			$author = sanitize_text_field( (string) $request->get_param( 'author' ) );
			$user   = sanitize_text_field( (string) $request->get_param( 'user' ) );
			$browse = sanitize_text_field( (string) $request->get_param( 'browse' ) );
		}

		$sortby = sanitize_key( (string) $request->get_param( 'sortby' ) );
		$sort   = strtoupper( sanitize_text_field( (string) $request->get_param( 'sort' ) ) );
		if ( 'DESC' !== $sort ) {
			$sort = 'ASC';
		}

		$query_args = array(
			'search'   => $search,
			'tag'      => $tag,
			'author'   => $author,
			'user'     => $user,
			'browse'   => $browse,
			'per_page' => $per_page,
			'fields'   => array(
				'name'              => true,
				'requires'          => true,
				'tested'            => true,
				'compatibility'     => true,
				'screenshot_url'    => true,
				'ratings'           => true,
				'rating'            => true,
				'num_ratings'       => true,
				'homepage'          => true,
				'sections'          => true,
				'description'       => true,
				'short_description' => true,
				'banners'           => true,
				'downloaded'        => true,
				'last_updated'      => true,
				'downloadlink'      => true,
			),
		);
		$query_args = apply_filters( 'wppic_api_query', $query_args, $type, $request->get_params() );

		if ( 'plugin' === $type ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			$api       = plugins_api( 'query_plugins', $query_args );
			$list_type = 'plugins';
		} else {
			require_once ABSPATH . 'wp-admin/includes/theme.php';
			$api       = themes_api( 'query_themes', $query_args );
			$list_type = 'themes';
		}

		if ( is_wp_error( $api ) ) {
			$data = $api->get_error_data();
			if ( ! is_array( $data ) || ! isset( $data['status'] ) ) {
				$api->add_data( array( 'status' => 500 ) );
			}
			return $api;
		}

		$sort_results = 'plugins' === $list_type ? ( $api->plugins ?? null ) : ( $api->themes ?? null );
		if ( ! empty( $sort_results ) && 'none' !== $sortby && is_array( $sort_results ) ) {
			array_multisort(
				array_column( $sort_results, $sortby ),
				'DESC' === $sort ? SORT_DESC : SORT_ASC,
				$sort_results
			);
		}

		/**
		 * Filter: wppic_query_results
		 *
		 * Sorted results ready for display.
		 *
		 * @param array  $sort_results The sorted results.
		 * @param string $list_type    The type of query (plugins, themes).
		 * @param string $sortby       The field to sort by.
		 * @param string $sort         The sort order (ASC, DESC).
		 */
		$sort_results = apply_filters( 'wppic_query_results', $sort_results, $list_type, $sortby, $sort );

		if ( is_wp_error( $sort_results ) || empty( $sort_results ) ) {
			return new \WP_Error(
				'wppic_query_empty',
				__( 'No data found.', 'wp-plugin-info-card' ),
				array( 'status' => 404 )
			);
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => array(
					'api_response' => json_decode( wp_json_encode( $sort_results ) ),
					'html'         => '',
				),
			)
		);
	}

	/**
	 * Retrieve the query shortcode.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_query_shortcode( $request ) {
		return $this->query_directory_for_rest( $request, false );
	}

	/**
	 * Retrieve the v2 query shortcode.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_query_shortcode_v2( $request ) {
		return $this->query_directory_for_rest( $request, true );
	}

	/**
	 * Normalize the card arguments shared by the shortcode and the AJAX card handler.
	 *
	 * Type and layout are restricted to key characters so they are safe in markup
	 * and in template include paths, while still allowing custom types and layouts.
	 *
	 * @param array $args Raw arguments (type, slug, image, expiration, layout).
	 *
	 * @return array Normalized arguments.
	 */
	private static function normalize_card_args( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'type'       => '',
				'slug'       => '',
				'image'      => '',
				'expiration' => '',
				'layout'     => '',
			)
		);

		$type = sanitize_key( $args['type'] );

		return array(
			'type'       => '' === $type ? 'plugin' : $type,
			'slug'       => is_scalar( $args['slug'] ) ? sanitize_text_field( (string) $args['slug'] ) : '',
			'image'      => is_scalar( $args['image'] ) ? esc_url_raw( trim( (string) $args['image'] ) ) : '',
			'expiration' => is_scalar( $args['expiration'] ) ? absint( $args['expiration'] ) : 0,
			'layout'     => sanitize_key( $args['layout'] ),
		);
	}

	/**
	 * Build the data attributes used by the AJAX card loader.
	 *
	 * @param string $type       Asset type.
	 * @param string $slug       Asset slug.
	 * @param string $image      Image override URL.
	 * @param int    $expiration Cache expiration in minutes.
	 * @param string $layout     Card layout.
	 * @param array  $item_slugs Slugs with overriding titles.
	 *
	 * @return string Escaped data attributes, prefixed with a space.
	 */
	private static function get_ajax_data_attributes( $type, $slug, $image, $expiration, $layout, $item_slugs ) {
		return sprintf(
			' data-type="%s" data-slug="%s" data-image="%s" data-expiration="%s" data-layout="%s" data-slugs="%s"',
			esc_attr( $type ),
			esc_attr( $slug ),
			esc_url( $image ),
			esc_attr( $expiration ),
			esc_attr( $layout ),
			esc_attr( wp_json_encode( $item_slugs ) )
		);
	}

	/**
	 * Main Shortcode function.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content The content of the shortcode.
	 */
	public static function shortcode_function( $atts, $content = '' ) {

		$attributes = wp_parse_args(
			$atts,
			array(
				'id'                  => '',  // custom Div ID (could be use for anchor).
				'type'                => '',  // plugin | theme.
				'slug'                => '',  // plugin slug name.
				'image'               => '',  // image url to replace WP logo (175px X 175px).
				'align'               => '',  // center|left|right.
				'containerid'         => '',  // custom Div ID (could be use for anchor).
				'margin'              => '',  // custom container margin - eg: "15px 0".
				'clear'               => '',  // clear float before or after the card: before|after.
				'expiration'          => '',  // transient duration in minutes - 0 for never expires.
				'ajax'                => '',  // load plugin data async whith ajax: yes|no (default: no).
				'scheme'              => '',  // color scheme : default|scheme1->scheme10 (default: empty).
				'layout'              => '',  // card|large|flex|wordpress.
				'custom'              => '',  // value to print : url|name|version|author|requires|rating|num_ratings|downloaded|last_updated|download_link.
				'multi'               => false,
				'cols'                => 2,
				'col_gap'             => 20,
				'row_gap'             => 20,
				'itemSlugs'           => array(),
				'marginSpacing'       => 'none',
				'marginSpacingTarget' => 'both',
			),
		);
		// Use "shortcode_atts_wppic_default" filter to edit shortcode parameters default values or add your owns.

		// Get admin settingsr.
		$options = Options::get_options();

		// Global var to enqueue scripts + ajax param if is set to yes.
		add_filter( 'wppic_allow_scripts', '__return_true' );

		$add_class = array();

		$card_args = self::normalize_card_args( $attributes );

		// Remove unnecessary spaces.
		$id          = sanitize_text_field( trim( $attributes['id'] ) );
		$type        = $card_args['type'];
		$slug        = $card_args['slug'];
		$image       = $card_args['image'];
		$containerid = esc_attr( trim( $attributes['containerid'] ) );
		$margin      = esc_attr( trim( $attributes['margin'] ) );
		$clear       = sanitize_text_field( trim( $attributes['clear'] ) );
		$expiration  = $card_args['expiration'];
		$ajax        = sanitize_text_field( trim( $attributes['ajax'] ) );
		$scheme      = sanitize_text_field( trim( $attributes['scheme'] ) );
		$layout      = $card_args['layout'];
		$custom      = sanitize_text_field( trim( $attributes['custom'] ) );
		$multi       = filter_var( $attributes['multi'], FILTER_VALIDATE_BOOLEAN );
		$align       = sanitize_text_field( trim( $attributes['align'] ) );
		$cols        = absint( $attributes['cols'] );
		$col_gap     = absint( $attributes['col_gap'] );
		$row_gap     = absint( $attributes['row_gap'] );

		if ( empty( $layout ) ) {
			$layout      = 'wp-pic-card';
			$add_class[] = $layout;
		} elseif ( 'flex' === $layout ) {
			$add_class[] = 'flex';
			$add_class[] = 'wp-pic-card';
		} elseif ( 'card' === $layout ) {
			$layout      = 'wp-pic-card';
			$add_class[] = 'wp-pic-card';
		} elseif ( 'ratings' === $layout ) {
			$add_class[] = 'wp-pic-card';
		} else {
			$add_class[] = $layout;
		}

		if ( 'none' !== $attributes['marginSpacing'] ) {
			$add_class[] = 'wppic-margin-spacing-' . esc_attr( $attributes['marginSpacing'] );
		}
		if ( 'both' !== $attributes['marginSpacingTarget'] ) {
			$add_class[] = 'wppic-margin-spacing-target-' . esc_attr( $attributes['marginSpacingTarget'] );
		}
		// Check to see if slug exists and if it is false, else we should skip this.
		if ( isset( $attributes[ $slug ] ) ) {
			// If false, that means don't show the plugin.
			// Slug can be string false, boolean false, and and a string for a custom title.
			if ( false === $attributes[ $slug ] || 'false' === $attributes[ $slug ] ) {
				return '';
			} else {
				$attributes['itemSlugs'][ $slug ] = $attributes[ $slug ];
			}
		}

		// Check to see if itemSlugs value is false too.
		if ( isset( $attributes['itemSlugs'][ $slug ] ) ) {
			// If false, that means don't show the plugin.
			if ( false === $attributes['itemSlugs'][ $slug ] || 'false' === $attributes['itemSlugs'][ $slug ] ) {
				return '';
			}
		}

		// Random slug: comma-separated list.
		if ( strpos( $slug, ',' ) !== false && ! $multi ) {
			$slug = explode( ',', $slug );
			$slug = $slug[ array_rand( $slug ) ];
		} elseif ( strpos( $slug, ',' ) !== false && $multi ) {
			$slug = explode( ',', $slug );
			foreach ( $slug as &$item_slug ) {
				$item_slug = trim( $item_slug );
			}
		}

		$block_alignment = 'align-center';
		switch ( $align ) {
			case 'left':
				$block_alignment = 'alignleft';
				break;
			case 'right':
				$block_alignment = 'alignright';
				break;
			case 'center':
				$block_alignment = 'aligncenter';
				break;
			case 'wide':
				$block_alignment = 'alignwide';
				break;
			case 'full':
				$block_alignment = 'alignfull';
				break;
		}

		if ( is_array( $slug ) && $multi ) {
			ob_start();
			?>
			<style>
			.wppic-plugin-site-grid,
			#<?php echo esc_attr( $attributes['id'] ); ?> {
				grid-column-gap: <?php echo esc_attr( $col_gap ); ?>px;
				grid-row-gap: <?php echo esc_attr( $row_gap ); ?>px;
			}
			</style>
			<?php
			$content .= ob_get_clean();
			$content .= sprintf(
				'<div id="%s" class="wp-block-plugin-info-card %s cols-%d has-grid">',
				esc_attr( $id ),
				esc_attr( $block_alignment ),
				esc_attr( $cols ),
			);
			foreach ( $slug as $asset_slug ) {
				// For old plugin versions.
				if ( empty( $type ) ) {
					$type = 'plugin';
				}
				$add_class[] = $type;
				$add_class[] = 'multi';

				// Add custom shortcode slugs to itemslugs if it exists.
				if ( isset( $attributes[ $asset_slug ] ) ) {
					// Add to itemSlugs.
					$attributes['itemSlugs'][ $asset_slug ] = $attributes[ $asset_slug ];
				}

				if ( ! empty( $custom ) ) {

					$wppic_data = wppic_api_parser( $type, $asset_slug, $expiration );

					if ( ! $wppic_data ) {
						return '<strong>' . esc_html__( 'Item not found:', 'wp-plugin-info-card' ) . ' "' . esc_html( $asset_slug ) . '" ' . esc_html__( 'does not exist.', 'wp-plugin-info-card' ) . '</strong>';
					}

					if ( ! empty( $wppic_data->$custom ) ) {
						$content .= $wppic_data->$custom;
					}
				} else {

					// Ajax required data.
					$ajax_data = '';
					if ( 'yes' === $ajax ) {
						$add_class[] = 'wp-pic-ajax';
						$ajax_data   = self::get_ajax_data_attributes( $type, $asset_slug, $image, $expiration, $layout, $attributes['itemSlugs'] );
					}

					// Align card.
					$align_center = false;
					$align_style  = '';

					// Custom style.
					$style = '';
					if ( ! empty( $margin ) || ! empty( $align_style ) ) {
						$style = 'style="' . esc_attr( $margin ) . ' ' . esc_attr( $align_style ) . '"';
					}

					// Extra container ID.
					if ( ! empty( $containerid ) ) {
						$containerid = ' id="' . esc_attr( $containerid ) . '"';
					} else {
						$containerid = ' id="wp-pic-' . esc_attr( $asset_slug ) . '"';
					}

					// Color scheme.
					if ( empty( $scheme ) ) {
						$scheme = $options['colorscheme'];
						if ( 'default' === $scheme ) {
							$scheme = '';
						}
					}
					$add_class[] = $scheme;

					// Output.
					if ( 'before' === $clear ) {
						$content .= '<div style="clear:both"></div>';
					}

					$content .= sprintf( '<div class="wp-pic-wrapper %s %s %s" %s>', esc_attr( $block_alignment ), esc_attr( $layout ), $multi ? 'multi' : '', $style );
					if ( $align_center ) {
						$content .= '<div class="wp-pic-center">';
					}

					// Data attribute for ajax call.
					$content .= '<div class="wp-pic ' . esc_attr( implode( ' ', $add_class ) ) . '" ' . $containerid . $ajax_data . ' >';
					if ( 'yes' !== $ajax ) {
						$content .= self::shortcode_content( $type, $asset_slug, $image, $expiration, $layout, $attributes['itemSlugs'] );
					} else {
						$content .= '<div class="wp-pic-body-loading"><div class="signal"></div></div>';
					}

					$content .= '</div>';
					// Align center.
					if ( $align_center ) {
						$content .= '</div>';
					}

					$content .= '</div><!-- .wp-pic-wrapper-->';
					if ( 'after' === $clear ) {
						$content .= '<div style="clear:both"></div>';
					}
				}
			}
			$content .= '</div>';
			ob_start();
			if ( 0 === did_action( 'wppic_enqueue_scripts' ) ) {
				do_action( 'wppic_enqueue_scripts' );
			}
			$content .= ob_get_clean();
			return $content;
		} else {
			// For old plugin versions.
			if ( empty( $type ) ) {
				$type = 'plugin';
			}
			$add_class[] = $type;

			// Check to see if slug exists in attributes.
			if ( isset( $attributes[ $slug ] ) ) {
				// Add to itemSlugs.
				$attributes['itemSlugs'][ $slug ] = $attributes[ $slug ];
			}

			if ( ! empty( $custom ) ) {
				$wppic_data = wppic_api_parser( $type, $slug, $expiration );

				if ( ! $wppic_data ) {
					return '<strong>' . esc_html__( 'Item not found:', 'wp-plugin-info-card' ) . ' "' . esc_html( $slug ) . '" ' . esc_html__( 'does not exist.', 'wp-plugin-info-card' ) . '</strong>';
				}

				if ( ! empty( $wppic_data->$custom ) ) {
					$content .= $wppic_data->$custom;
				}
			} else {

				// Ajax required data.
				$ajax_data = '';
				if ( 'yes' === $ajax ) {
					$add_class[] = 'wp-pic-ajax';
					$ajax_data   = self::get_ajax_data_attributes( $type, $slug, $image, $expiration, $layout, $attributes['itemSlugs'] );
				}

				// Align card.
				$align_center = false;
				$align_style  = '';

				// Extra container ID.
				if ( ! empty( $containerid ) ) {
					$containerid = ' id="' . esc_attr( $containerid ) . '"';
				} else {
					$containerid = ' id="wp-pic-' . esc_attr( $slug ) . '"';
				}

				// Custom container margin.
				if ( ! empty( $margin ) ) {
					$margin = 'margin:' . $margin . ';';
				}

				// Custom style.
				$style = '';
				if ( ! empty( $margin ) || ! empty( $align_style ) ) {
					$style = 'style="' . $margin . $align_style . '"';
				}

				// Color scheme.
				if ( empty( $scheme ) ) {
					$scheme = $options['colorscheme'] ?? '';
					if ( 'default' === $scheme ) {
						$scheme = '';
					}
				}
				$add_class[] = $scheme;
				// Output.
				if ( 'before' === $clear ) {
					$content .= '<div style="clear:both"></div>';
				}

				$content .= sprintf( '<div class="wp-pic-wrapper %s %s" %s>', esc_attr( $block_alignment ), esc_attr( $layout ), $style );
				if ( $align_center ) {
					$content .= '<div class="wp-pic-center">';
				}

				// Data attribute for ajax call.
				$content .= '<div class="wp-pic ' . esc_attr( implode( ' ', $add_class ) ) . '" ' . $containerid . $ajax_data . ' >';
				if ( 'yes' !== $ajax ) {
					$content .= self::shortcode_content( $type, $slug, $image, $expiration, $layout, $attributes['itemSlugs'] );
				} else {
					$content .= '<div class="wp-pic-body-loading"><div class="signal"></div></div>';
				}

				$content .= '</div>';

				// Align center.
				if ( $align_center ) {
					$content .= '</div>';
				}

				$content .= '</div><!-- .wp-pic-wrapper-->';
				if ( 'after' === $clear ) {
					$content .= '<div style="clear:both"></div>';
				}
			}
		}

		ob_start();
		if ( 0 === did_action( 'wppic_enqueue_scripts' ) ) {
			do_action( 'wppic_enqueue_scripts' );
		}
		$content .= ob_get_clean();
		return $content;
	}

	/**
	 * Process the query shortcode.
	 *
	 * @param array  $atts    Array of shortcode attributes.
	 * @param string $content Shortcode content.
	 */
	public static function shortcode_query_function( $atts, $content = '' ) {
		add_filter( 'wppic_allow_scripts', '__return_true' );
		// Retrieve & extract shorcode parameters.
		extract( // phpcs:ignore
			wp_parse_args(
				$atts,
				array(
					'cols'        => 2,
					'per_page'    => 24,
					'type'        => '',
					'image'       => '',
					'align'       => '',
					'containerid' => '',
					'margin'      => '',
					'clear'       => '',
					'expiration'  => '',
					'ajax'        => '',
					'scheme'      => '',
					'layout'      => '',
					'custom'      => '',
					'sortby'      => 'none',
					'sort'        => 'ASC',
					'search'      => '',
					'tag'         => '',
					'author'      => '',
					'user'        => '',
					'browse'      => '',
					'row_gap'     => 20,
					'col_gap'     => 20,
					'searchBy'    => 'general',
					'itemSlugs'   => array(),
				)
			)
		);

		// Prepare the row columns.
		$column = false;
		$cols   = absint( $cols );
		if ( is_numeric( $cols ) && $cols > 0 && $cols < 4 ) {
			$column = true;
		}

		$per_page = absint( $per_page );
		if ( $per_page < 1 || $per_page > 24 ) {
			$per_page = 24;
		}

		// Build the query.
		$query_args = array(
			'search'   => $search,
			'tag'      => $tag,
			'author'   => $author,
			'user'     => $user,
			'browse'   => $browse,
			'per_page' => $per_page,
			'fields'   => array(
				'name'              => false,
				'requires'          => false,
				'tested'            => false,
				'compatibility'     => false,
				'screenshot_url'    => false,
				'ratings'           => true,
				'rating'            => true,
				'num_ratings'       => true,
				'homepage'          => false,
				'sections'          => false,
				'description'       => false,
				'short_description' => false,
			),
		);
		$query_args = apply_filters( 'wppic_api_query', $query_args, $type, $atts );

		$api = '';

		// Plugins query.
		if ( 'plugin' === $type ) {
			$type = 'plugins';
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			$api = plugins_api( 'query_plugins', $query_args );
		}

		// Themes query.
		if ( 'theme' === $type ) {
			$type = 'themes';
			require_once ABSPATH . 'wp-admin/includes/theme.php';
			$api = themes_api( 'query_themes', $query_args );
		}

		// Begin sort.
		$sort_results = array();
		if ( 'plugins' === $type ) {
			$sort_results = $api->plugins ?? null;
		} elseif ( 'themes' === $type ) {
			$sort_results = $api->themes ?? null;
		}
		if ( 'plugins' === $type && ! is_wp_error( $api ) && ! empty( $api ) && 'none' !== $sortby ) {
			$plugins = $api->plugins;
			array_multisort(
				array_column( $plugins, $sortby ),
				'DESC' === $sort ? SORT_DESC : SORT_ASC,
				$plugins
			);
			$sort_results = $plugins;
		}
		if ( 'themes' === $type && ! is_wp_error( $api ) && ! empty( $api ) && 'none' !== $sortby ) {
			$themes = $api->themes;
			array_multisort(
				array_column( $themes, $sortby ),
				'DESC' === $sort ? SORT_DESC : SORT_ASC,
				$themes
			);
			$sort_results = $themes;
		}

		/**
		 * Filter: wppic_query_results
		 *
		 * Sorted results ready for display.
		 *
		 * @param array $sort_results The sorted results.
		 * @param string $type The type of query (plugins, themes).
		 * @param string $sortby The field to sort by.
		 * @param string $sort The sort order (ASC, DESC).
		 */
		$sort_results = apply_filters( 'wppic_query_results', $sort_results, $type, $sortby, $sort );

		// If container ID is blank, generate a random one.
		if ( empty( $containerid ) ) {
			$containerid = 'wppic-' . wp_rand( 0, 1000 ) . wp_generate_password( 6, false, false );
		}

		// Get the query result to build the content.
		$content = '';
		if ( ! is_wp_error( $sort_results ) && ! empty( $sort_results ) ) {
			ob_start();
			if ( 0 === did_action( 'wppic_enqueue_scripts' ) ) {
				do_action( 'wppic_enqueue_scripts' );
			}
			$content .= ob_get_clean();
			if ( is_array( $sort_results ) ) {
				ob_start();
				?>
				<style>
					#<?php echo esc_attr( $containerid ); ?> {
						grid-column-gap: <?php echo esc_attr( $col_gap ); ?>px;
						grid-row-gap: <?php echo esc_attr( $row_gap ); ?>px;
					}

				</style>
				<div id="<?php echo esc_attr( $containerid ); ?>" class="wp-query-plugin-info-card cols-<?php echo esc_attr( $cols ); ?> align<?php echo esc_attr( $align ); ?>">
					<?php
					$sort_results = \json_decode( wp_json_encode( $sort_results ), true );
					// Creat the loop wp-pic-1-.
					foreach ( $sort_results as $item ) {
						// Add custom shortcode slugs to itemslugs if it exists.
						if ( isset( $atts[ $item['slug'] ] ) ) {
							// Add to itemSlugs.
							$atts['itemSlugs'][ $item['slug'] ] = $atts[ $item['slug'] ];
						}
						$atts['slug'] = $item['slug'];
						// Set alignment to none as these are nested.
						$atts['align'] = 'none';
						// Use the WPPIC shorcode to generate cards.
						echo self::shortcode_function( $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Card HTML is escaped in the shortcode templates.
					}
					?>
				</div>
				<?php
				$content .= ob_get_clean();

				return apply_filters( 'wppic_query_content', $content, $type, $atts );
			}
		}
	} //end of wp-pic-query Shortcode

	/**
	 * Shortcode for retrieving plugin screenshots card.
	 *
	 * @param array  $attributes Shortcode attributes.
	 * @param string $content    The content of the shortcode.
	 */
	public static function shortcode_plugin_screenshots_info_card( $attributes, $content = '' ) {

		$attributes = shortcode_atts(
			array(
				'unique_id'                                => 'wppic-' . wp_rand( 0, 1000 ) . wp_generate_password( 6, false, false ),
				'slug'                                     => '',
				'icon_style'                               => 'none',
				'asset_data'                               => array(),
				'enable_rounded_icon'                      => false,
				'color_theme'                              => 'default',
				'custom_colors'                            => false,
				'plugin_title'                             => '',
				'enable_context_menu'                      => true,
				'enable_screenshots'                       => true,
				'align'                                    => 'center',
				'color_background'                         => '#FFFFFF',
				'color_text'                               => '#000000',
				'color_border'                             => '#000000',
				'color_menu_border'                        => '#000000',
				'color_menu'                               => '#000000',
				'color_menu_hover'                         => '#DDDDDD',
				'color_menu_text'                          => '#FFFFFF',
				'color_menu_text_hover'                    => '#000000',
				'color_screenshots_background'             => '#DDDDDD',
				'color_screenshots_border'                 => '#000000',
				'color_screenshots_arrow_background'       => '#333333',
				'color_screenshots_arrow_background_hover' => '#000000',
				'color_screenshots_arrow'                  => '#EEEEEE',
				'color_screenshots_arrow_hover'            => '#FFFFFF',
				'color_star'                               => '#FF9529',
				'color_meta_background'                    => '#000000',
				'color_meta_text'                          => '#FFFFFF',
				'skip_animated_gifs'                       => false,
			),
			$attributes,
			'wp-pic-plugin-screenshots'
		);

		if ( '' === $attributes['align'] ) {
			$attributes['align'] = 'none';
		}

		// Build wrapper classes.
		$wrapper_classes = array(
			'wp-pic-plugin-screenshots-wrapper',
			'align' . $attributes['align'],
			'wp-pic-card',
		);

		// Build CSS classes.
		$classes = array(
			'wp-pic-plugin-screenshots',
			'wp-pic-screenshots',
			'large',
			'plugin',
			'wp-pic-has-screenshots',
		);

		// Check for the color theme.
		if ( ! $attributes['custom_colors'] && strpos( $attributes['color_theme'], 'custom' ) === false ) {
			$classes[] = sprintf(
				'wppic-plugin-screenshot-theme-%s',
				esc_attr( $attributes['color_theme'] )
			);
		}

		// Get asset data.
		$asset_data = wppic_api_parser( 'plugin', $attributes['slug'] );
		if ( $asset_data ) {
			$asset_data = json_decode( json_encode( $asset_data ), true );
		} else {
			return ''; // Exit silently.
		}

		// Get requires.
		$requires       = $asset_data['requires'] ?? false;
		$requires_label = $asset_data['tested'] ?? 'Unknown';
		if ( $requires && is_numeric( $requires ) ) {
			/* Translators: %s is the WP version the plugin supports */
			$requires_label = sprintf( __( 'WP %s+', 'wp-plugin-info-card' ), $requires );
		}

		// Get icon.
		$icon = $asset_data['icons']['svg'] ?? $asset_data['icons']['2x'] ?? $asset_data['icons']['1x'] ?? Functions::get_plugin_url( 'assets/img/default-plugin-icon.png' );

		// Get plugin screenshots.
		$screenshots = Functions::get_plugin_screenshots( $asset_data['slug'], false, true );

		// If animated gif is not allowed, remove them from the screenshots.
		$attributes['skip_animated_gifs'] = filter_var( $attributes['skip_animated_gifs'], FILTER_VALIDATE_BOOLEAN );
		if ( (bool) $attributes['skip_animated_gifs'] ) {
			$new_screenshots = array();
			foreach ( $screenshots as $screenshot ) {
				$full_screenshot = $screenshot['full'] ?? '';
				// If it does not have a gif extension, add it.
				if ( ! preg_match( '/\.gif/', $full_screenshot ) ) {
					$new_screenshots[] = $screenshot;
				}
			}
			$screenshots = $new_screenshots;
		}

		// Active installs. Omitted when this response must not include license totals.
		$active_installs_text = '';
		if ( isset( $asset_data['active_installs'] ) ) {
			if ( $asset_data['active_installs'] >= 1000000 ) {
				// Get number of million.
				$count_in_million = round( $asset_data['active_installs'] / 1000000, 1 );

				$active_installs_text = sprintf(
					/* Translators: %s is the number of million active installs */
					_n( '%s+ Million', '%s+ Million', $count_in_million, 'wp-plugin-info-card' ),
					number_format_i18n( $count_in_million )
				);
			} else {
				$active_installs_text = number_format_i18n( $asset_data['active_installs'] ) . '+';
			}
		}

		// Build custom color styles.
		$block_styles = '';
		if ( $attributes['custom_colors'] || ( ! $attributes['custom_colors'] && strpos( $attributes['color_theme'], 'custom' ) !== false ) ) {
			$block_styles = sprintf(
				'#%s {
					--wppic-plugin-screenshots-card-background: %s;
					--wppic-plugin-screenshots-card-text-color: %s;
					--wppic-plugin-screenshots-card-border-color: %s;
					--wppic-plugin-screenshots-card-menu-border-color: %s;
					--wppic-plugin-screenshots-card-menu-color: %s;
					--wppic-plugin-screenshots-card-menu-color-hover: %s;
					--wppic-plugin-screenshots-card-menu-text-color: %s;
					--wppic-plugin-screenshots-card-menu-text-color-hover: %s;
					--wppic-plugin-screenshots-card-screenshots-background: %s;
					--wppic-plugin-screenshots-card-screenshots-border-color: %s;
					--wppic-plugin-screenshots-card-screenshots-star-color: %s;
					--wppic-plugin-screenshots-card-meta-background-color: %s;
					--wppic-plugin-screenshots-card-meta-text-color: %s;
					--wppic-plugin-screenshots-card-screenshots-arrow-background-color: %s;
					--wppic-plugin-screenshots-card-screenshots-arrow-background-color-hover: %s;
					--wppic-plugin-screenshots-card-screenshots-arrow-color: %s;
					--wppic-plugin-screenshots-card-screenshots-arrow-color-hover: %s;
				}',
				esc_attr( $attributes['unique_id'] ),
				esc_attr( $attributes['color_background'] ),
				esc_attr( $attributes['color_text'] ),
				esc_attr( $attributes['color_border'] ),
				esc_attr( $attributes['color_menu_border'] ),
				esc_attr( $attributes['color_menu'] ),
				esc_attr( $attributes['color_menu_hover'] ),
				esc_attr( $attributes['color_menu_text'] ),
				esc_attr( $attributes['color_menu_text_hover'] ),
				esc_attr( $attributes['color_screenshots_background'] ),
				esc_attr( $attributes['color_screenshots_border'] ),
				esc_attr( $attributes['color_star'] ),
				esc_attr( $attributes['color_meta_background'] ),
				esc_attr( $attributes['color_meta_text'] ),
				esc_attr( $attributes['color_screenshots_arrow_background'] ),
				esc_attr( $attributes['color_screenshots_arrow_background_hover'] ),
				esc_attr( $attributes['color_screenshots_arrow'] ),
				esc_attr( $attributes['color_screenshots_arrow_hover'] )
			);
		}

		// Use CSS \2605 (BLACK STAR) escapes so UTF-8 glyphs are not mangled by CSS minifiers.
		$block_styles .= sprintf(
			'
			#%1$s .wp-pic-plugin-screenshots-rating-count {
				position: relative;
				display: block;
				vertical-align: baseline;
				margin-left: 0 !important;
			}
			#%1$s .wp-pic-plugin-screenshots-rating-count::before {
				--percent: %2$s;
				content: \'\\2605\\2605\\2605\\2605\\2605\';
				display: inline-block;
				position: relative;
				top: 0;
				left: 0;
				color: rgba(0,0,0,0.2);
				background:
					linear-gradient(90deg, var( --wppic-plugin-screenshots-card-screenshots-star-color ) var(--percent), rgba(0,0,0,0.2) var(--percent));
				-webkit-background-clip: text;
				-webkit-text-fill-color: transparent;
			}',
			esc_attr( $attributes['unique_id'] ),
			round( $asset_data['rating'] ) . '%'
		);

		// Begin outputting styles.
		if ( ! empty( $block_styles ) ) {
			$block_styles = sprintf( '<style>%s</style>', $block_styles );
		}

		// Global var to enqueue scripts + ajax param if is set to yes.
		add_filter( 'wppic_allow_scripts', '__return_true' );

		// Begin the output.
		ob_start();
		?>
			<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
				<?php echo wp_kses( $block_styles, Functions::get_kses_allowed_html() ); ?>
				<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" id="<?php echo esc_attr( $attributes['unique_id'] ); ?>">
					<div class="wp-pic-plugin-screenshots-card">
						<div class="wp-pic-plugin-screenshots-avatar-wrapper">
							<a href="<?php echo esc_url( $asset_data['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="wp-pic-plugin-screenshots-avatar style-<?php echo esc_attr( $attributes['icon_style'] ); ?> <?php echo esc_attr( $attributes['enable_rounded_icon'] ? 'is-rounded' : '' ); ?>">
								<img src="<?php echo esc_url( $icon ); ?>" width="125" height="125" alt="<?php echo esc_attr( $asset_data['name'] ); ?>" />
							</a>
							<?php
							if ( $attributes['enable_context_menu'] ) :
								?>
									<div class="wppic-meatball-menu wppic-meatball-menu-theme-light" style="display: none;">
									<div class="wppic-meatball-content">
										<input
											type="checkbox"
											aria-label="<?php esc_attr_e( 'Toggle menu', 'wp-plugin-info-card' ); ?>"
										/>
										<ul>
											<li class="wppic-meatball-menu-item wppic-meatball-menu-item-edit-comment" data-comment-action="edit">
												<a href="<?php echo esc_url( $asset_data['url'] ); ?>" class="button-reset">
													<span class="wppic-meatball-menu-icon">
														<svg width="24" height="24"><use xlink:href="#wppic-icon-wordpress"></use></svg>
													</span>
													<span class="wppic-meatball-menu-label">
														<?php esc_html_e( 'View Plugin Page', 'wp-plugin-info-card' ); ?>
													</span>
												</a>
											</li>
											<li class="wppic-meatball-menu-item" data-comment-action="approve">
												<a href="<?php echo esc_url( sprintf( 'https://wordpress.org/support/plugin/%s/reviews/', $asset_data['slug'] ) ); ?>" class="button-reset">
													<span class="wppic-meatball-menu-icon">
														<svg width="24" height="24"><use xlink:href="#wppic-icon-star"></use></svg>
													</span>
													<span class="wppic-meatball-menu-label">
														<?php esc_html_e( 'View Ratings', 'wp-plugin-info-card' ); ?>
													</span>
												</a>
											</li>
											<li class="wppic-meatball-menu-item">
												<a href="<?php echo esc_url( sprintf( 'https://wordpress.org/plugins/%s/advanced/', $asset_data['slug'] ) ); ?>" class="button-reset">
													<span class="wppic-meatball-menu-icon">
														<svg width="24" height="24"><use xlink:href="#wppic-icon-line-chart"></use></svg>
													</span>
													<span class="wppic-meatball-menu-label">
														<?php esc_html_e( 'View Plugin Stats', 'wp-plugin-info-card' ); ?>
													</span>
												</a>
											</li>
											<li class="wppic-meatball-menu-item">
											<a href="<?php echo esc_url( $asset_data['download_link'] ); ?>" class="button-reset">
													<span class="wppic-meatball-menu-icon">
														<svg width="24" height="24"><use xlink:href="#wppic-icon-download"></use></svg>
													</span>
													<span class="wppic-meatball-menu-label">
														<?php esc_html_e( 'Download Plugin', 'wp-plugin-info-card' ); ?>
													</span>
												</a>
											</li>
										</ul>
										<div class="wppic-meatball-icon-wrapper" aria-hidden="true">
											<div class="wppic-meatball-icon">
												<span></span>
											</div>
										</div>
									</div>
								</div>
								<?php
							endif;
							?>
						</div><!-- .wp-pic-plugin-screenshots-avatar-wrapper -->
						<div class="wp-pic-plugin-screenshots-title">
							<h2><?php echo esc_html( '' === $attributes['plugin_title'] ? $asset_data['name'] : $attributes['plugin_title'] ); ?></h2>
						</div>
						<div class="wp-pic-plugin-screenshots-author">
							<?php
							echo esc_html(
								sprintf(
								/* Translators: %s is the plugin author */
									__( 'By: %s', 'wp-plugin-info-card' ),
									$asset_data['author']
								)
							);
							?>
						</div>
						<div class="wp-pic-plugin-screenshots-rating">
							<span class="wp-pic-plugin-screenshots-rating-count">
								<?php
								echo esc_html(
									sprintf(
									/* Translators: %s is the number of ratings */
										_n( '%s Rating', '%s Ratings', $asset_data['num_ratings'], 'wp-plugin-info-card' ),
										number_format_i18n( $asset_data['num_ratings'] )
									)
								);
								?>
							</span>
						</div>
						<div class="wp-pic-plugin-screenshots-last-updated">
							<?php
							echo esc_html(
								sprintf(
								/* Translators: %s is the plugin author */
									__( 'Last Updated: %s ago', 'wp-plugin-info-card' ),
									\human_time_diff( strtotime( $asset_data['last_updated'] ) )
								)
							);
							?>
						</div>
						<div class="wp-pic-plugin-screenshots-description">
							<?php echo wp_kses_post( $asset_data['short_description'] ); ?>
						</div>
					</div><!-- .wp-pic-plugin-screenshots-card -->
					<footer class="wp-pic-plugin-screenshots-footer">
						<?php
						$local_screenshots   = $asset_data['screenshots'] ?? array();
						$screenshots_enabled = $attributes['enable_screenshots'] && ! empty( $screenshots ) && ! empty( $local_screenshots );
						if ( $screenshots_enabled ) {
							// Enqueue the modal script.
							if ( ! wp_script_is( 'fancybox', 'enqueued' ) ) {
								wp_enqueue_script(
									'wppic-fancybox-js',
									Functions::get_plugin_url( '/dist/wppic-fancybox.js' ),
									array(),
									Functions::get_plugin_version(),
									true
								);

								wp_register_style(
									'wppic-fancybox-css',
									Functions::get_plugin_url( '/dist/wppic-fancybox-css.css' ),
									array(),
									Functions::get_plugin_version(),
									'all'
								);
								wp_print_scripts( 'wppic-fancybox-js' );
								wp_print_styles( 'wppic-fancybox-css' );
							}
							add_action( 'wp_footer', array( __CLASS__, 'add_carousel_to_footer' ) );
							?>
								<div
									class="wp-pic-plugin-screenshots-images"
									<?php if ( self::is_foobox_lightbox_active() ) : ?>
										data-lightbox="foobox"
									<?php endif; ?>
								>
									<div class="wppic-screenshots-lazy" style="display: none;">
										<?php
										foreach ( $screenshots as $screenshot ) {
											?>
											<div class="wppic-screenshot-lazy" data-src="<?php echo esc_url( $screenshot['full'] ); ?>" data-alt="<?php echo esc_attr( $screenshot['caption'] ); ?>"></div>
											<?php
										}
										?>
									</div>
									<ul class="wppic-screenshot-fancyapps f-carousel" data-slug="<?php echo esc_attr( $asset_data['slug'] ); ?>" data-unique-id="<?php echo esc_attr( $attributes['unique_id'] ); ?>" style="display: none;">
									</ul>
								</div>
							<?php
						}
						?>
						<div class="wp-pic-plugin-screenshots-meta">
							<div class="wp-pic-plugin-screenshots-meta-item">
								<div class="wp-pic-plugin-screenshots-meta-item-svg">
									<svg width="24" height="24"><use xlink:href="#wppic-icon-code"></use></svg>
								</div>
								<div class="wp-pic-plugin-screenshots-meta-item-label">
									<a href="<?php echo esc_url( $asset_data['download_link'] ); ?>">
									<?php
									echo esc_html(
										sprintf(
										/* Translators: %s is the version */
											__( 'v%s', 'wp-plugin-info-card' ),
											$asset_data['version']
										)
									);
									?>
									</a>
								</div>
							</div>
							<div class="wp-pic-plugin-screenshots-meta-item">
								<div class="wp-pic-plugin-screenshots-meta-item-svg">
									<svg width="24" height="24"><use xlink:href="#wppic-icon-wordpress"></use></svg>
								</div>
								<div class="wp-pic-plugin-screenshots-meta-item-label">
								<a href="<?php echo esc_url( $asset_data['url'] ); ?>"><?php echo esc_html( $requires_label ); ?></a>
								</div>
							</div>
							<?php if ( '' !== $active_installs_text ) : ?>
							<div class="wp-pic-plugin-screenshots-meta-item">
								<div class="wp-pic-plugin-screenshots-meta-item-svg">
									<svg width="24" height="24"><use xlink:href="#wppic-icon-download-cloud"></use></svg>
								</div>
								<div class="wp-pic-plugin-screenshots-meta-item-label">
								<a href="<?php echo esc_url( sprintf( 'https://wordpress.org/plugins/%s/advanced/', $asset_data['slug'] ) ); ?>"><?php echo esc_html( $active_installs_text ); ?></a>
								</div>
							</div>
							<?php endif; ?>
						</div>
					</footer>
				</div>
			</div>
		<?php
		wp_enqueue_script( 'wp-escape-html' );
		if ( 0 === did_action( 'wppic_enqueue_scripts' ) ) {
			do_action( 'wppic_enqueue_scripts' );
		}
		$return = ob_get_clean();
		return $return;
	}

	/**
	 * Main Shortcode function.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content The content of the shortcode.
	 */
	public static function shortcode_active_site_plugins_function( $atts, $content = '' ) {

		$options    = Options::get_options();
		$attributes = wp_parse_args(
			$atts,
			array(
				'id'        => 'wppic-plugin-site-grid',
				'cols'      => 2,
				'col_gap'   => 20,
				'row_gap'   => 20,
				'scheme'    => '',
				'layout'    => '',
				'itemSlugs' => array(),
			)
		);

		// Color scheme.
		if ( empty( $attributes['scheme'] ) ) {
			$attributes['scheme'] = $options['colorscheme'] ?? 'default';
		}

		// Layout.
		if ( empty( $attributes['layout'] ) ) {
			$attributes['layout'] = $options['layout'] ?? 'card';
		}

		$plugins_on_org = Functions::get_active_plugins_with_data();

		ob_start();

		?>
		<style>
			.wppic-plugin-site-grid,
			#<?php echo esc_attr( $attributes['id'] ); ?> {
				grid-column-gap: <?php echo esc_attr( $attributes['col_gap'] ); ?>px;
				grid-row-gap: <?php echo esc_attr( $attributes['row_gap'] ); ?>px;
			}

		</style>
		<?php
		if ( 0 === did_action( 'wppic_enqueue_scripts' ) ) {
			do_action( 'wppic_enqueue_scripts' );
		}
		?>
			
		<div id="<?php echo esc_attr( $attributes['id'] ); ?>" class="wp-site-plugin-info-card cols-<?php echo esc_attr( $attributes['cols'] ); ?>">
			<?php
			$content = ob_get_clean();
			foreach ( $plugins_on_org as $plugin ) {
				$atts = array(
					'slug'      => $plugin['slug'],
					'layout'    => $attributes['layout'],
					'scheme'    => $attributes['scheme'],
					'type'      => 'plugin',
					'itemSlugs' => $attributes['itemSlugs'],
				);
				if ( isset( $attributes[ $plugin['slug'] ] ) ) {
					// Add to itemSlugs.
					$atts['itemSlugs'][ $plugin['slug'] ] = $attributes[ $plugin['slug'] ];
				}
				// Strip safe CSS.
				add_filter( 'safe_style_css', array( static::class, 'safe_css' ) );
				add_filter( 'safecss_filter_attr_allow_css', '__return_true' );
				// Use the WPPIC shorcode to generate cards.
				$content .= self::shortcode_function( $atts );
				remove_filter( 'safecss_filter_attr_allow_css', '__return_true' );
				remove_filter( 'safe_style_css', array( static::class, 'safe_css' ) );
			}
			ob_start();
			?>
		</div>
		<?php
		$content .= ob_get_clean();
		return $content;
	}

	/**
	 * Allows some CSS properties.
	 *
	 * @param array $css Array of allowed CSS properties.
	 *
	 * @return array Array of allowed CSS properties.
	 */
	public static function safe_css( $css ) {
		$css[] = 'display';
		$css[] = 'background';
		$css[] = 'linear-gradient';
		$css[] = '-webkit-background-clip';
		$css[] = '-webkit-text-fill-color';
		$css[] = 'background-image';
		return $css;
	}

	/**
	 * Retrieve the shortcode content.
	 *
	 * @param string $type plugin or theme.
	 * @param string $slug Asset slug.
	 * @param string $image Image override.
	 * @param string $expiration Expiration in seconds.
	 * @param string $layout What layout is being used.
	 * @param object $item_slugs Key separated slugs with overriding titles.
	 */
	public static function shortcode_content( $type = null, $slug = null, $image = null, $expiration = null, $layout = null, $item_slugs = null ) {
		/*
		 * This also runs as a public (nopriv) AJAX handler without a nonce on purpose.
		 * It only renders public asset data, and nonces would break on cached pages.
		 * All input is normalized below and escaped on output.
		 */
		$is_ajax_request = false;
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ! empty( $_POST['type'] ) ) {
			$type = wp_unslash( $_POST['type'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Normalized below.
		}
		if ( ! empty( $_POST['slug'] ) ) {
			$slug            = wp_unslash( $_POST['slug'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Normalized below.
			$is_ajax_request = true;
		}
		if ( ! empty( $_POST['image'] ) ) {
			$image = wp_unslash( $_POST['image'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Normalized below.
		}
		if ( ! empty( $_POST['expiration'] ) ) {
			$expiration = wp_unslash( $_POST['expiration'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Normalized below.
		}
		if ( ! empty( $_POST['layout'] ) ) {
			$layout = wp_unslash( $_POST['layout'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Normalized below.
		}

		$card_args = self::normalize_card_args(
			array(
				'type'       => $type,
				'slug'       => $slug,
				'image'      => $image,
				'expiration' => $expiration,
				'layout'     => $layout,
			)
		);

		$type       = $card_args['type'];
		$slug       = $card_args['slug'];
		$image      = $card_args['image'];
		$expiration = $card_args['expiration'];
		$layout     = $card_args['layout'];

		// GitHub cards hydrate through the token-gated REST route, not this public ajax handler.
		if ( $is_ajax_request && 'github' === $type ) {
			wp_send_json_error(
				array(
					'message' => __( 'GitHub cards cannot be loaded from this route.', 'wp-plugin-info-card' ),
				)
			);
		}

		// Get item slugs.
		if ( isset( $_POST['itemSlugs'] ) && is_array( $_POST['itemSlugs'] ) ) {
			$item_slugs = Functions::sanitize_array_recursive( wp_unslash( $_POST['itemSlugs'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized recursively.
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$wppic_data = wppic_api_parser( $type, $slug, $expiration );

		// if plugin does not exists.
		if ( ! $wppic_data ) {

			$error      = '<div class="wp-pic-flip" style="display: none;">';
				$error .= '<div class="wp-pic-face wp-pic-front error">';

					$error .= '<span class="wp-pic-no-plugin">' . esc_html__( 'Item not found:', 'wp-plugin-info-card' ) . '</br><i>"' . esc_html( $slug ) . '"</i></br>' . esc_html__( 'does not exist.', 'wp-plugin-info-card' ) . '</span>';
					$error .= '<div class="monster-wrapper">
									<div class="eye-left"></div>
									<div class="eye-right"></div>
									<div class="mouth">
										<div class="tooth-left"></div>
										<div class="tooth-right"></div>
									</div>
									<div class="arm-left"></div>
									<div class="arm-right"></div>
									<div class="dots"></div>
								</div>';
				$error     .= '</div>';
			$error         .= '</div>';

			if ( $is_ajax_request ) {
				echo wp_kses( $error, Functions::get_kses_allowed_html() );
				die();
			} else {
				return $error;
			}
		}

		// Date format Internationalizion.
		$date_format              = Options::get_date_format();
		$wppic_data->last_updated = date_i18n( $date_format, strtotime( $wppic_data->last_updated ) );

		// Prepare the credit.
		$credit  = '';
		$options = Options::get_options();
		if ( isset( $options['credit'] ) && true === $options['credit'] ) {
			$credit .= '<a class="wp-pic-credit" href="https://wordpress.org/plugins/wp-plugin-info-card/" target="_blank" data-tooltip="';
			$credit .= esc_html__( 'This card has been generated with WP Plugin Info Card', 'wp-plugin-info-card' );
			$credit .= '"></a>';
		}
		$wppic_data->credit = $credit;

		/**
		 * Filter the plugin data before it is displayed.
		 *
		 * @param object $wppic_data The plugin data.
		 * @param string $type The type of asset (plugin, theme).
		 * @param string $slug The asset slug.
		 * @param string $layout The layout being used.
		 *
		 * @since 5.2.0
		 */
		$wppic_data = apply_filters( 'wppic_data_pre_display', $wppic_data, $type, $slug, $layout );

		// Override the title if applicable.
		if ( is_array( $item_slugs ) && isset( $item_slugs[ $slug ] ) && '' !== $item_slugs[ $slug ] ) {
			$wppic_data->name = $item_slugs[ $slug ];
		}

		// Load theme or plugin template.
		$content = '';
		$content = apply_filters( 'wppic_add_template', $content, array( $type, $wppic_data, $image, $layout ) );

		if ( $is_ajax_request ) {
			echo wp_kses( $content, Functions::get_kses_allowed_html() );
			die();
		} else {
			return $content;
		}
	}

	/**
	 * Return plugin data based on passed strings.
	 */
	public function get_asset_data() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Public read-only card render. No state change.
		$type = isset( $_GET['type'] ) ? sanitize_title( wp_unslash( $_GET['type'] ) ) : 'plugin';
		$slug = isset( $_GET['slug'] ) ? sanitize_text_field( wp_unslash( $_GET['slug'] ) ) : 'wp-plugin-info-card-not-found';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// Random slug: comma-separated list.
		$slugs = explode( ',', $slug );
		foreach ( $slugs as &$item_slug ) {
			$item_slug = sanitize_title( trim( $item_slug ) );
		}

		$data = array();
		foreach ( $slugs as $asset_slug ) {
			$slug_data = wppic_api_parser( $type, $asset_slug );
			if ( false === $slug_data ) {
				continue;
			}
			if ( isset( $slug_data->author ) ) {
				$slug_data->author = wp_strip_all_tags( $slug_data->author );
			}
			if ( isset( $slug_data->author ) ) {
				$slug_data->author = wp_strip_all_tags( $slug_data->author );
			}
			if ( isset( $slug_data->active_installs ) ) {
				$slug_data->active_installs_raw = $slug_data->active_installs;
				$slug_data->active_installs     = number_format_i18n( $slug_data->active_installs );
			}
			if ( isset( $slug_data->last_updated ) ) {
				$slug_data->last_updated = human_time_diff( strtotime( $slug_data->last_updated ), time() ) . ' ' . _x( 'ago', 'Last time updated', 'wp-plugin-info-card' );
			}
			$data[] = $slug_data;
		}

		// Return error if no data.
		if ( empty( $data ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'No plugin could be found with that slug.', 'wp-plugin-info-card' ),
				)
			);
		}

		wp_send_json_success( $data );
	}

	/**
	 * Add SVGs to footer.
	 */
	public static function add_icons_to_footer() {
		?>
		<div style="display: none; height: 0; width: 0;" aria-hidden="true">
			<svg width="0" height="0" class="hidden" style="display: none;">
				<symbol id="wppic-icon-star" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star">
					<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
				</symbol>
				<symbol id="wppic-icon-star-filled" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 576 512">
					<path fill="currentColor" d="M288.1 0l86.5 164 182.7 31.6L428 328.5 454.4 512 288.1 430.2 121.7 512l26.4-183.5L18.9 195.6 201.5 164 288.1 0z" />
				</symbol>
				<symbol id="wppic-icon-wordpress" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" height="24" width="24" viewBox="0 0 512 512">
					<path fill="currentColor" d="M256 8C119.3 8 8 119.2 8 256c0 136.7 111.3 248 248 248s248-111.3 248-248C504 119.2 392.7 8 256 8zM33 256c0-32.3 6.9-63 19.3-90.7l106.4 291.4C84.3 420.5 33 344.2 33 256zm223 223c-21.9 0-43-3.2-63-9.1l66.9-194.4 68.5 187.8c.5 1.1 1 2.1 1.6 3.1-23.1 8.1-48 12.6-74 12.6zm30.7-327.5c13.4-.7 25.5-2.1 25.5-2.1 12-1.4 10.6-19.1-1.4-18.4 0 0-36.1 2.8-59.4 2.8-21.9 0-58.7-2.8-58.7-2.8-12-.7-13.4 17.7-1.4 18.4 0 0 11.4 1.4 23.4 2.1l34.7 95.2L200.6 393l-81.2-241.5c13.4-.7 25.5-2.1 25.5-2.1 12-1.4 10.6-19.1-1.4-18.4 0 0-36.1 2.8-59.4 2.8-4.2 0-9.1-.1-14.4-.3C109.6 73 178.1 33 256 33c58 0 110.9 22.2 150.6 58.5-1-.1-1.9-.2-2.9-.2-21.9 0-37.4 19.1-37.4 39.6 0 18.4 10.6 33.9 21.9 52.3 8.5 14.8 18.4 33.9 18.4 61.5 0 19.1-7.3 41.2-17 72.1l-22.2 74.3-80.7-239.6zm81.4 297.2l68.1-196.9c12.7-31.8 17-57.2 17-79.9 0-8.2-.5-15.8-1.5-22.9 17.4 31.8 27.3 68.2 27.3 107 0 82.3-44.6 154.1-110.9 192.7z"/>
				</symbol>
				<symbol id="wppic-icon-line-chart" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-line-chart">
					<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>
				</symbol>
				<symbol id="wppic-icon-download" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download">
					<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>
				</symbol>
				<symbol id="wppic-icon-code" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-code">
					<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>
				</symbol>
				<symbol id="wppic-icon-download-cloud" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download-cloud">
					<path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M12 12v9"/><path d="m8 17 4 4 4-4"/>
				</symbol>
				<symbol id="fa-icon-home" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
					<path fill="currentColor" d="M341.8 72.6C329.5 61.2 310.5 61.2 298.3 72.6L74.3 280.6C64.7 289.6 61.5 303.5 66.3 315.7C71.1 327.9 82.8 336 96 336L112 336L112 512C112 547.3 140.7 576 176 576L464 576C499.3 576 528 547.3 528 512L528 336L544 336C557.2 336 569 327.9 573.8 315.7C578.6 303.5 575.4 289.5 565.8 280.6L341.8 72.6zM304 384L336 384C362.5 384 384 405.5 384 432L384 528L256 528L256 432C256 405.5 277.5 384 304 384z"/>
				</symbol>
				<symbol id="fa-icon-github" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
					<path fill="currentColor" d="M237.9 461.4C237.9 463.4 235.6 465 232.7 465C229.4 465.3 227.1 463.7 227.1 461.4C227.1 459.4 229.4 457.8 232.3 457.8C235.3 457.5 237.9 459.1 237.9 461.4zM206.8 456.9C206.1 458.9 208.1 461.2 211.1 461.8C213.7 462.8 216.7 461.8 217.3 459.8C217.9 457.8 216 455.5 213 454.6C210.4 453.9 207.5 454.9 206.8 456.9zM251 455.2C248.1 455.9 246.1 457.8 246.4 460.1C246.7 462.1 249.3 463.4 252.3 462.7C255.2 462 257.2 460.1 256.9 458.1C256.6 456.2 253.9 454.9 251 455.2zM316.8 72C178.1 72 72 177.3 72 316C72 426.9 141.8 521.8 241.5 555.2C254.3 557.5 258.8 549.6 258.8 543.1C258.8 536.9 258.5 502.7 258.5 481.7C258.5 481.7 188.5 496.7 173.8 451.9C173.8 451.9 162.4 422.8 146 415.3C146 415.3 123.1 399.6 147.6 399.9C147.6 399.9 172.5 401.9 186.2 425.7C208.1 464.3 244.8 453.2 259.1 446.6C261.4 430.6 267.9 419.5 275.1 412.9C219.2 406.7 162.8 398.6 162.8 302.4C162.8 274.9 170.4 261.1 186.4 243.5C183.8 237 175.3 210.2 189 175.6C209.9 169.1 258 202.6 258 202.6C278 197 299.5 194.1 320.8 194.1C342.1 194.1 363.6 197 383.6 202.6C383.6 202.6 431.7 169 452.6 175.6C466.3 210.3 457.8 237 455.2 243.5C471.2 261.2 481 275 481 302.4C481 398.9 422.1 406.6 366.2 412.9C375.4 420.8 383.2 435.8 383.2 459.3C383.2 493 382.9 534.7 382.9 542.9C382.9 549.4 387.5 557.3 400.2 555C500.2 521.8 568 426.9 568 316C568 177.3 455.5 72 316.8 72zM169.2 416.9C167.9 417.9 168.2 420.2 169.9 422.1C171.5 423.7 173.8 424.4 175.1 423.1C176.4 422.1 176.1 419.8 174.4 417.9C172.8 416.3 170.5 415.6 169.2 416.9zM158.4 408.8C157.7 410.1 158.7 411.7 160.7 412.7C162.3 413.7 164.3 413.4 165 412C165.7 410.7 164.7 409.1 162.7 408.1C160.7 407.5 159.1 407.8 158.4 408.8zM190.8 444.4C189.2 445.7 189.8 448.7 192.1 450.6C194.4 452.9 197.3 453.2 198.6 451.6C199.9 450.3 199.3 447.3 197.3 445.4C195.1 443.1 192.1 442.8 190.8 444.4zM179.4 429.7C177.8 430.7 177.8 433.3 179.4 435.6C181 437.9 183.7 438.9 185 437.9C186.6 436.6 186.6 434 185 431.7C183.6 429.4 181 428.4 179.4 429.7z"/>
				</symbol>
				<symbol id="fa-icon-heart" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
					<path fill="currentColor" d="M305 151.1L320 171.8L335 151.1C360 116.5 400.2 96 442.9 96C516.4 96 576 155.6 576 229.1L576 231.7C576 343.9 436.1 474.2 363.1 529.9C350.7 539.3 335.5 544 320 544C304.5 544 289.2 539.4 276.9 529.9C203.9 474.2 64 343.9 64 231.7L64 229.1C64 155.6 123.6 96 197.1 96C239.8 96 280 116.5 305 151.1z"/>
				</symbol>
				<symbol id="fa-icon-star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
					<path fill="currentColor" d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"/>
				</symbol>
				<symbol id="fa-icon-fork" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
					<path fill="currentColor" d="M176 168C189.3 168 200 157.3 200 144C200 130.7 189.3 120 176 120C162.7 120 152 130.7 152 144C152 157.3 162.7 168 176 168zM256 144C256 176.8 236.3 205 208 217.3L208 240C208 266.5 229.5 288 256 288L384 288C410.5 288 432 266.5 432 240L432 217.3C403.7 205 384 176.8 384 144C384 99.8 419.8 64 464 64C508.2 64 544 99.8 544 144C544 176.8 524.3 205 496 217.3L496 240C496 301.9 445.9 352 384 352L352 352L352 422.7C380.3 435 400 463.2 400 496C400 540.2 364.2 576 320 576C275.8 576 240 540.2 240 496C240 463.2 259.7 435 288 422.7L288 352L256 352C194.1 352 144 301.9 144 240L144 217.3C115.7 205 96 176.8 96 144C96 99.8 131.8 64 176 64C220.2 64 256 99.8 256 144zM464 168C477.3 168 488 157.3 488 144C488 130.7 477.3 120 464 120C450.7 120 440 130.7 440 144C440 157.3 450.7 168 464 168zM344 496C344 482.7 333.3 472 320 472C306.7 472 296 482.7 296 496C296 509.3 306.7 520 320 520C333.3 520 344 509.3 344 496z"/>
				</symbol>
				<symbol id="fa-icon-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
					<path fill="currentColor" d="M320 96C239.2 96 174.5 132.8 127.4 176.6C80.6 220.1 49.3 272 34.4 307.7C31.1 315.6 31.1 324.4 34.4 332.3C49.3 368 80.6 420 127.4 463.4C174.5 507.1 239.2 544 320 544C400.8 544 465.5 507.2 512.6 463.4C559.4 419.9 590.7 368 605.6 332.3C608.9 324.4 608.9 315.6 605.6 307.7C590.7 272 559.4 220 512.6 176.6C465.5 132.9 400.8 96 320 96zM176 320C176 240.5 240.5 176 320 176C399.5 176 464 240.5 464 320C464 399.5 399.5 464 320 464C240.5 464 176 399.5 176 320zM320 256C320 291.3 291.3 320 256 320C244.5 320 233.7 317 224.3 311.6C223.3 322.5 224.2 333.7 227.2 344.8C240.9 396 293.6 426.4 344.8 412.7C396 399 426.4 346.3 412.7 295.1C400.5 249.4 357.2 220.3 311.6 224.3C316.9 233.6 320 244.4 320 256z"/>
				</symbol>
				<symbol id="fa-icon-code" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
					<path fill="currentColor" d="M246.6 182.6C259.1 170.1 259.1 149.8 246.6 137.3C234.1 124.8 213.8 124.8 201.3 137.3L41.3 297.3C28.8 309.8 28.8 330.1 41.3 342.6L201.3 502.6C213.8 515.1 234.1 515.1 246.6 502.6C259.1 490.1 259.1 469.8 246.6 457.3L109.3 320L246.6 182.6zM393.3 182.6L530.7 320L393.4 457.4C380.9 469.9 380.9 490.2 393.4 502.7C405.9 515.2 426.2 515.2 438.7 502.7L598.7 342.7C611.2 330.2 611.2 309.9 598.7 297.4L438.7 137.4C426.2 124.9 405.9 124.9 393.4 137.4C380.9 149.9 380.9 170.2 393.4 182.7z"/>
				</symbol>
			</symbol>
		</div>
		<?php
	}

	/**
	 * Add Splide to footer.
	 */
	public static function add_carousel_to_footer() {
		// Enqueue / print fancybox styles.
		if ( wp_script_is( 'wppic-fancybox-js', 'registered' ) && ! wp_script_is( 'wppic-fancybox-js', 'done' ) ) {
			\wp_add_inline_style(
				'wppic-fancybox-css',
				'.fancybox__container{z-index:99999 !important}'
			);
			wp_print_styles( array( 'wppic-fancybox-css' ) );
		}
	}

	/**
	 * GitHub Info Card shortcode.
	 *
	 * @param array  $attributes Shortcode attributes.
	 * @param string $content The content of the shortcode.
	 * @param string $shortcode_name The name of the shortcode.
	 * @param array  $asset_data The asset data.
	 */
	public static function shortcode_github_info_card( $attributes, $content = '', $shortcode_name = '', $asset_data = null ) {
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && ! defined( 'WPPIC_REST_REQUEST' ) ) ) {
			return '';
		}

		// If GitHub cards aren't supported, return early.
		if ( ! (bool) Options::is_github_info_cards_enabled() ) {
			return '';
		}

		// Make attributes lowercase to avoid case-sensitive issues.
		$attributes = array_change_key_case( $attributes, CASE_LOWER );

		$defaults_attributes = array(
			'class'                   => '',
			'style'                   => 'light',
			'uniqueid'                => 'wp-pic-' . wp_generate_password( 10, false ),
			'numchildren'             => 0,
			'cols'                    => 1,
			'colgap'                  => 10,
			'rowgap'                  => 10,
			'marginspacing'           => 'none',
			'marginspacingtarget'     => 'both',
			'backgroundcolor'         => '#fff',
			'textcolor'               => '#24292f',
			'iconcolor'               => '#666',
			'iconcolorhover'          => '#111827',
			'bordercolor'             => '#d0d7de',
			'languagebgcolor'         => '#24292f',
			'languagetextcolor'       => '#fff',
			'sponsorscolor'           => '#bf3989',
			'buttonbgcolor'           => '#1a7f37',
			'buttonbgcolorhover'      => '#2c974b',
			'buttontextcolor'         => '#fff',
			'buttontextcolorhover'    => '#fff',
			'layout'                  => 'large',
			'align'                   => 'center',
			'horizontalalign'         => 'center',
			'showtopbar'              => true,
			'showauthorbar'           => true,
			'showstatsbar'            => true,
			'showlastupdated'         => true,
			'parentbuttontype'        => '',
			'username'                => '',
			'repo'                    => '',
			'nameoverride'            => '',
			'loginoverride'           => '',
			'sponsorsurloverride'     => '',
			'versionoverride'         => '',
			'organizationurloverride' => '',
			'homepageurloverride'     => '',
			'overridebutton'          => false,
			'overridebuttontext'      => __( 'View on GitHub', 'wp-plugin-info-card' ),
			'overridebuttonurl'       => '',
			'buttontype'              => 'github',
			'avatarimageurl'          => '',
		);

		$attributes = shortcode_atts( $defaults_attributes, $attributes, 'github-info-card' );

		// Let's sanitize these one-by-one.
		$attributes['class']                   = Functions::sanitize_attribute( $attributes, 'class', 'string' );
		$attributes['style']                   = Functions::sanitize_attribute( $attributes, 'style', 'string' );
		$attributes['numchildren']             = Functions::sanitize_attribute( $attributes, 'numchildren', 'integer' );
		$attributes['cols']                    = Functions::sanitize_attribute( $attributes, 'cols', 'integer' );
		$attributes['colgap']                  = Functions::sanitize_attribute( $attributes, 'colgap', 'integer' );
		$attributes['rowgap']                  = Functions::sanitize_attribute( $attributes, 'rowgap', 'integer' );
		$attributes['marginspacingtarget']     = Functions::sanitize_attribute( $attributes, 'marginspacingtarget', 'string' );
		$attributes['marginspacing']           = Functions::sanitize_attribute( $attributes, 'marginspacing', 'string' );
		$attributes['backgroundcolor']         = Functions::sanitize_attribute( $attributes, 'backgroundcolor', 'string' );
		$attributes['textcolor']               = Functions::sanitize_attribute( $attributes, 'textcolor', 'string' );
		$attributes['iconcolor']               = Functions::sanitize_attribute( $attributes, 'iconcolor', 'string' );
		$attributes['iconcolorhover']          = Functions::sanitize_attribute( $attributes, 'iconcolorhover', 'string' );
		$attributes['bordercolor']             = Functions::sanitize_attribute( $attributes, 'bordercolor', 'string' );
		$attributes['languagebgcolor']         = Functions::sanitize_attribute( $attributes, 'languagebgcolor', 'string' );
		$attributes['languagetextcolor']       = Functions::sanitize_attribute( $attributes, 'languagetextcolor', 'string' );
		$attributes['sponsorscolor']           = Functions::sanitize_attribute( $attributes, 'sponsorscolor', 'string' );
		$attributes['buttonbgcolor']           = Functions::sanitize_attribute( $attributes, 'buttonbgcolor', 'string' );
		$attributes['buttonbgcolorhover']      = Functions::sanitize_attribute( $attributes, 'buttonbgcolorhover', 'string' );
		$attributes['buttontextcolor']         = Functions::sanitize_attribute( $attributes, 'buttontextcolor', 'string' );
		$attributes['buttontextcolorhover']    = Functions::sanitize_attribute( $attributes, 'buttontextcolorhover', 'string' );
		$attributes['layout']                  = Functions::sanitize_attribute( $attributes, 'layout', 'string' );
		$attributes['align']                   = Functions::sanitize_attribute( $attributes, 'align', 'string' );
		$attributes['horizontalalign']         = Functions::sanitize_attribute( $attributes, 'horizontalalign', 'string' );
		$attributes['uniqueid']                = Functions::sanitize_attribute( $attributes, 'uniqueid', 'string' );
		$attributes['showtopbar']              = Functions::sanitize_attribute( $attributes, 'showtopbar', 'boolean' );
		$attributes['showauthorbar']           = Functions::sanitize_attribute( $attributes, 'showauthorbar', 'boolean' );
		$attributes['showstatsbar']            = Functions::sanitize_attribute( $attributes, 'showstatsbar', 'boolean' );
		$attributes['showlastupdated']         = Functions::sanitize_attribute( $attributes, 'showlastupdated', 'boolean' );
		$attributes['parentbuttontype']        = Functions::sanitize_attribute( $attributes, 'parentbuttontype', 'string' );
		$attributes['username']                = Functions::sanitize_attribute( $attributes, 'username', 'string' );
		$attributes['repo']                    = Functions::sanitize_attribute( $attributes, 'repo', 'string' );
		$attributes['nameoverride']            = Functions::sanitize_attribute( $attributes, 'nameoverride', 'string' );
		$attributes['loginoverride']           = Functions::sanitize_attribute( $attributes, 'loginoverride', 'string' );
		$attributes['sponsorsurloverride']     = Functions::sanitize_attribute( $attributes, 'sponsorsurloverride', 'string' );
		$attributes['organizationurloverride'] = Functions::sanitize_attribute( $attributes, 'organizationurloverride', 'string' );
		$attributes['versionoverride']         = Functions::sanitize_attribute( $attributes, 'versionoverride', 'string' );
		$attributes['overridebutton']          = Functions::sanitize_attribute( $attributes, 'overridebutton', 'boolean' );
		$attributes['overridebuttontext']      = Functions::sanitize_attribute( $attributes, 'overridebuttontext', 'string' );
		$attributes['overridebuttonurl']       = Functions::sanitize_attribute( $attributes, 'overridebuttonurl', 'url' );
		$attributes['buttontype']              = Functions::sanitize_attribute( $attributes, 'buttontype', 'string' );
		$attributes['avatarimageurl']          = Functions::sanitize_attribute( $attributes, 'avatarimageurl', 'url' );

		// Skip enqueueing the lazy load script if we're in the REST API.
		if ( ! defined( 'WPPIC_REST_REQUEST' ) ) {
			// Enqueue the lazy load script.
			$deps = require Functions::get_plugin_dir( 'dist/github-info-card-lazy-load.asset.php' );
			wp_enqueue_script(
				'wppic-github-info-card-lazy-load',
				Functions::get_plugin_url( 'dist/github-info-card-lazy-load.js' ),
				$deps['dependencies'],
				$deps['version'],
				true
			);

			wp_localize_script(
				'wppic-github-info-card-lazy-load',
				'wppicGithubInfoCardLazyLoad',
				array(
					'restUrl'             => Functions::get_rest_url( 'wppic/v2/get_github_card_html' ),
					'restNonce'           => wp_create_nonce( 'wp_rest' ),
					'defaultGithubAvatar' => Functions::get_default_github_avatar_url(),
					'cardAttributes'      => array(),
				)
			);
			/**
			 * Add icons to footer for plugin card.
			 */
			add_action( 'wp_footer', array( __CLASS__, 'add_icons_to_footer' ) );

			if ( wp_style_is( 'wppic-github-info-card', 'registered' ) ) {
				wp_enqueue_style( 'wppic-github-info-card' );
			} else {
				wp_enqueue_style(
					'wppic-github-info-card',
					Functions::get_plugin_url( 'dist/github-info-card.css' ),
					array(),
					Functions::get_plugin_version(),
					'all'
				);
			}

			add_action(
				'wp_footer',
				function () {
					if ( ! wp_style_is( 'wppic-github-info-card', 'done' ) ) {
						wp_print_styles( 'wppic-github-info-card' );
					}
				},
				100
			);
		}

		// Now let's build the shortcode.
		if ( 0 === $attributes['numchildren'] ) {
			// todo - build wrapper around the shortcode.
		}

		// Check if option exists for GitHub local option cache.
		if ( null !== $asset_data ) {
			$maybe_local_cache = $asset_data;
		} else {
			$maybe_local_cache = get_transient( 'wppic_github_' . sanitize_key( preg_replace( '/\-/', '_', $attributes['username'] . '/' . $attributes['repo'] ) ) );
		}
		if ( ! $maybe_local_cache && ! defined( 'WPPIC_REST_REQUEST' ) ) { // Start lazy load.
			// Get wrapper classes for loading card.
			$wrapper_classes = array(
				'wppic-github-info-card',
				'layout-' . $attributes['layout'],
				'cols-' . ( $attributes['numchildren'] > 1 ? $attributes['cols'] : 1 ),
			);
			if ( 0 === $attributes['numchildren'] ) {
				$wrapper_classes[] = 'wppic-margin-spacing-' . $attributes['marginspacing'];
				$wrapper_classes[] = 'wppic-margin-spacing-target-' . $attributes['marginspacingtarget'];
				$wrapper_classes[] = $attributes['class'];
				$wrapper_classes[] = 'is-style-wppic-github-' . $attributes['style'];
				$wrapper_classes[] = 'align' . ( ! is_wp_error( $attributes['align'] ) && ! empty( $attributes['align'] ) ? $attributes['align'] : 'center' );
				$wrapper_classes[] = 'horizontal-align-' . ( ! is_wp_error( $attributes['horizontalalign'] ) && ! empty( $attributes['horizontalalign'] ) ? $attributes['horizontalalign'] : 'center' );
			}

			$token = self::get_github_lazy_load_token( $attributes['username'], $attributes['repo'] );

			// Preload in GitHub repo URLs.
			$preload_paths = array(
				esc_url_raw(
					add_query_arg(
						array(
							'username' => sanitize_key( $attributes['username'] ),
							'repo'     => sanitize_key( $attributes['repo'] ),
						),
						Functions::get_rest_url( 'wppic/v2/get_github_card_html' )
					)
				),
			);
			wp_add_inline_script(
				'wppic-github-info-card-lazy-load',
				'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( ' . wp_json_encode( $preload_paths ) . ' ) );',
				'after',
			);
			// Add attributes as localized vars.
			wp_add_inline_script(
				'wppic-github-info-card-lazy-load',
				'wppicGithubInfoCardLazyLoad.cardAttributes[\'' . sanitize_title( strtolower( $attributes['username'] ) . '_' . sanitize_title( strtolower( $attributes['repo'] ) ) ) . '\'] = ' . wp_json_encode( $attributes ) . ';',
				'after',
			);
			ob_start();
			?>
			<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" id="<?php echo esc_attr( $attributes['uniqueid'] ); ?>" data-token="<?php echo esc_attr( $token ); ?>" data-username="<?php echo esc_attr( strtolower( $attributes['username'] ) ); ?>" data-repo="<?php echo esc_attr( strtolower( $attributes['repo'] ) ); ?>" data-is-github-card-loading="true">
				<div class="wppic-github-info-card-wrapper">
					<div class="wppic-github-info-card-loading" >
					<article class="github-skeleton-card" aria-busy="true" aria-label="Loading repository card">
						<div class="github-skeleton-badges">
							<span class="github-skeleton-pill skeleton-loader"></span>
							<span class="github-skeleton-pill skeleton-loader"></span>
						</div>

						<div class="github-skeleton-header">
							<div class="github-skeleton-avatar skeleton-loader"></div>
							<div class="github-skeleton-title skeleton-loader">
							<div class="github-skeleton-line github-skeleton-lg github-skeleton-w-60 skeleton-loader"></div>
							<div class="github-skeleton-line github-skeleton-md github-skeleton-w-40 skeleton-loader"></div>
							</div>
						</div>

						<div class="github-skeleton-meta">
							<span class="github-skeleton-chip github-skeleton-w-4 skeleton-loader"></span>
							<span class="github-skeleton-chip github-skeleton-w-4 skeleton-loader"></span>
							<span class="github-skeleton-chip github-skeleton-w-4 skeleton-loader"></span>
							<span class="github-skeleton-chip github-skeleton-w-4 skeleton-loader"></span>
						</div>

						<div class="github-skeleton-desc">
							<div class="github-skeleton-line github-skeleton-w-90 skeleton-loader"></div>
							<div class="github-skeleton-line github-skeleton-w-70 skeleton-loader"></div>
						</div>

						<div class="github-skeleton-footer">
							<div class="github-skeleton-date github-skeleton-w-40 skeleton-loader"></div>
							<div class="github-skeleton-cta skeleton-loader"></div>
						</div>
						</article>
					</div><!-- .wppic-github-info-card-loading -->
				</div><!-- .wppic-github-info-card-wrapper -->
			</div><!-- .wppic-github-info-card -->
			<?php
			return ob_get_clean();
		}
		$asset_data = $maybe_local_cache;
		if ( ! $asset_data ) {
			if ( current_user_can( 'manage_options' ) ) {
				echo esc_html__( 'GitHub Card Not Found', 'wp-plugin-info-card' );
			}
			return ob_get_clean();
		}

		// Turn asset data into an array.
		$asset_data = json_decode( wp_json_encode( $asset_data ), true );

		ob_start();

		// Get Home URL.
		$has_homepage_url      = false;
		$homepage_url          = Functions::sanitize_attribute( $asset_data, 'homepage', 'url' );
		$homepage_url_override = Functions::sanitize_attribute( $attributes, 'homepageurloverride', 'url' );
		if ( ! is_wp_error( $homepage_url ) && ! empty( $homepage_url ) ) {
			$has_homepage_url = true;
		}
		if ( ! is_wp_error( $homepage_url_override ) && ! empty( $homepage_url_override ) ) {
			$homepage_url     = $homepage_url_override;
			$has_homepage_url = true;
		}

		// Get GitHub URL. No override.
		$has_github_url = false;
		$github_url     = Functions::sanitize_attribute( $asset_data, 'github_url', 'url' );
		if ( ! is_wp_error( $github_url ) && ! empty( $github_url ) ) {
			$has_github_url = true;
		}

		// Get stargazer's count for GitHub URL.
		$has_stargazers_count = false;
		$stargazers_count_url = '';
		if ( $has_github_url ) {
			$has_stargazers_count = true;
			$stargazers_count_url = sprintf( '%s/stargazers', $github_url );
		}

		// Get watchers GitHub URL.
		$has_watchers_count = false;
		$watchers_count_url = '';
		if ( $has_github_url ) {
			$has_watchers_count = true;
			$watchers_count_url = sprintf( '%s/watchers', $github_url );
		}

		// Get forks GitHub URL.
		$has_forks_count = false;
		$forks_count_url = '';
		if ( $has_github_url ) {
			$has_forks_count = true;
			$forks_count_url = sprintf( '%s/forks', $github_url );
		}

		// Get sponsors URL.
		$has_sponsors_url = false;
		$sponsors_url     = Functions::sanitize_attribute( $asset_data, 'sponsors_url', 'url' );
		if ( ! is_wp_error( $sponsors_url ) && ! empty( $sponsors_url ) ) {
			$has_sponsors_url = true;
		}
		$sponsors_url_override = Functions::sanitize_attribute( $attributes, 'sponsorsurloverride', 'url' );
		if ( ! is_wp_error( $sponsors_url_override ) && ! empty( $sponsors_url_override ) ) {
			$sponsors_url     = $sponsors_url_override;
			$has_sponsors_url = true;
		}

		// Get organization URL.
		$has_org_name_url      = false;
		$org_name_url          = Functions::sanitize_attribute( $asset_data, 'org_url', 'url' );
		$org_name_url_override = Functions::sanitize_attribute( $attributes, 'organizationurloverride', 'url' );
		if ( ! is_wp_error( $org_name_url ) && ! empty( $org_name_url ) ) {
			$has_org_name_url = true;
		}
		if ( ! is_wp_error( $org_name_url_override ) && ! empty( $org_name_url_override ) ) {
			$org_name_url     = $org_name_url_override;
			$has_org_name_url = true;
		}

		// Get avatar image.
		$default_github_avatar = Functions::get_default_github_avatar_url();
		$has_avatar_image      = false;
		$avatar_image          = Functions::sanitize_attribute( $asset_data, 'avatar', 'url' );
		$avatar_image_override = Functions::sanitize_attribute( $attributes, 'avatarimageurl', 'url' );
		if ( ! is_wp_error( $avatar_image_override ) && ! empty( $avatar_image_override ) ) {
			$avatar_image     = $avatar_image_override;
			$has_avatar_image = true;
		} elseif ( ! is_wp_error( $avatar_image ) && ! empty( $avatar_image ) ) {
			$has_avatar_image = true;
		}
		if ( ! $has_avatar_image ) {
			$has_avatar_image = true;
			$avatar_image     = $default_github_avatar;
		}

		// Get org name.
		$org_name          = Functions::sanitize_attribute( $asset_data, 'login', 'string' );
		$org_name_override = Functions::sanitize_attribute( $attributes, 'loginoverride', 'string' );
		if ( ! is_wp_error( $org_name_override ) && ! empty( $org_name_override ) ) {
			$org_name = $org_name_override;
		}

		// Get repo full name.
		$repo_full_name = Functions::sanitize_attribute( $asset_data, 'full_name', 'string' );
		if ( is_wp_error( $repo_full_name ) ) {
			$repo_full_name = '';
		}

		// Get repo name.
		$repo_name = Functions::sanitize_attribute( $asset_data, 'name', 'string' );
		if ( is_wp_error( $repo_name ) ) {
			$repo_name = '';
		}
		$repo_name_override = Functions::sanitize_attribute( $attributes, 'nameoverride', 'string' );
		if ( ! is_wp_error( $repo_name_override ) && ! empty( $repo_name_override ) ) {
			$repo_name = $repo_name_override;
		}

		// Get repo description.
		$repo_description = Functions::sanitize_attribute( $asset_data, 'description', 'string' );
		if ( is_wp_error( $repo_description ) ) {
			$repo_description = '';
		}

		// Get counts.
		$stargazers_count = Functions::sanitize_attribute( $asset_data, 'stargazers_count', 'integer' );
		if ( is_wp_error( $stargazers_count ) ) {
			$stargazers_count = 0;
		}
		$watchers_count = Functions::sanitize_attribute( $asset_data, 'watchers_count', 'integer' );
		if ( is_wp_error( $watchers_count ) ) {
			$watchers_count = 0;
		}
		$forks_count = Functions::sanitize_attribute( $asset_data, 'forks_count', 'integer' );
		if ( is_wp_error( $forks_count ) ) {
			$forks_count = 0;
		}
		$subscribers_count = Functions::sanitize_attribute( $asset_data, 'subscribers_count', 'integer' );
		if ( is_wp_error( $subscribers_count ) ) {
			$subscribers_count = 0;
		}

		// Get latest release tag name and URL.
		$latest_release_tag_name = Functions::sanitize_attribute( $asset_data, 'latest_release_tag_name', 'string' );
		$latest_release_url      = Functions::sanitize_attribute( $asset_data, 'latest_release_url', 'url' );
		if ( is_wp_error( $latest_release_tag_name ) || is_wp_error( $latest_release_url ) ) {
			$latest_release_tag_name = '';
			$latest_release_url      = '';
		}
		$latest_release_download_url = Functions::sanitize_attribute( $asset_data, 'latest_release_download_url', 'url' );
		if ( ! is_wp_error( $latest_release_download_url ) && ! empty( $latest_release_download_url ) ) {
			$latest_release_url = $latest_release_download_url;
		}
		if ( empty( $latest_release_url ) ) {
			$latest_release_url = $github_url;
		}
		// Get version override.
		$version_override = Functions::sanitize_attribute( $attributes, 'versionoverride', 'string' );
		if ( ! is_wp_error( $version_override ) && ! empty( $version_override ) && ! empty( $latest_release_tag_name ) ) {
			$latest_release_tag_name = $version_override;
		}

		// Get last updated date.
		$show_last_updated = (bool) $attributes['showlastupdated'];
		$last_updated_date = Functions::sanitize_attribute( $asset_data, 'updated_at', 'string' );
		if ( is_wp_error( $last_updated_date ) ) {
			$last_updated_date = '';
		}

		// Get button type and URL.
		$button_type        = $attributes['buttontype'];
		$parent_button_type = Functions::sanitize_attribute( $attributes, 'parentbuttontype', 'string' ); // This is passed from the parent block. If not passed, this errors, and it's as if a shortcode passed it.
		$button_override    = (bool) $attributes['overridebutton'];
		if ( ! is_wp_error( $parent_button_type ) && ! empty( $parent_button_type ) && ! $button_override ) {
			$button_type = $parent_button_type;
		}

		switch ( $button_type ) {
			case 'github':
				$button_url  = $github_url;
				$button_text = __( 'View on GitHub', 'wp-plugin-info-card' );
				break;
			case 'website':
				$button_url  = $homepage_url;
				$button_text = __( 'View Website', 'wp-plugin-info-card' );
				break;
			case 'sponsor':
				$button_url  = $sponsors_url;
				$button_text = __( 'Sponsor', 'wp-plugin-info-card' );
				break;
			case 'download':
				$button_url  = $latest_release_url;
				$button_text = __( 'Download', 'wp-plugin-info-card' );
				break;
			case 'star':
				$button_url  = $stargazers_count_url;
				$button_text = __( 'Star', 'wp-plugin-info-card' );
				break;
			case 'custom':
				$button_url  = $attributes['overridebuttonurl'];
				$button_text = $attributes['overridebuttontext'];
				break;
			default:
				$button_url  = $github_url;
				$button_text = __( 'View on GitHub', 'wp-plugin-info-card' );
				break;
		}
		// Failsafe.
		if ( $button_override && ! empty( $attributes['overridebuttontext'] ) ) {
			$button_text = $attributes['overridebuttontext'];
		}

		$has_language = false;
		$language     = Functions::sanitize_attribute( $asset_data, 'language', 'string' );
		if ( ! is_wp_error( $language ) && ! empty( $language ) ) {
			$has_language = true;
		}
		$has_license = false;
		$license     = Functions::sanitize_attribute( $asset_data, 'license', 'string' );
		if ( ! is_wp_error( $license ) && ! empty( $license ) ) {
			if ( 'NOASSERTION' !== $license ) {
				$has_license = true;
			}
		}

		// Get wrapper classes.
		$wrapper_classes = array(
			'wppic-github-info-card',
			'layout-' . $attributes['layout'],
			'cols-' . ( $attributes['numchildren'] > 1 ? $attributes['cols'] : 1 ),
		);
		if ( 0 === $attributes['numchildren'] ) {
			$wrapper_classes[] = 'wppic-margin-spacing-' . $attributes['marginspacing'];
			$wrapper_classes[] = 'wppic-margin-spacing-target-' . $attributes['marginspacingtarget'];
			$wrapper_classes[] = $attributes['class'];
			$wrapper_classes[] = 'is-style-wppic-github-' . $attributes['style'];
			$wrapper_classes[] = 'align' . ( ! is_wp_error( $attributes['align'] ) && ! empty( $attributes['align'] ) ? $attributes['align'] : 'center' );
			$wrapper_classes[] = 'horizontal-align-' . ( ! is_wp_error( $attributes['horizontalalign'] ) && ! empty( $attributes['horizontalalign'] ) ? $attributes['horizontalalign'] : 'center' );
		}

		if ( ( 'is-style-wppic-github-custom' === $attributes['class'] || 'custom' === $attributes['style'] ) && 0 === $attributes['numchildren'] ) {
			?>
			<style>
				#<?php echo esc_attr( $attributes['uniqueid'] ); ?> {
					--wppic-github-background-color: <?php echo esc_attr( $attributes['backgroundcolor'] ); ?>;
					--wppic-github-text-color: <?php echo esc_attr( $attributes['textcolor'] ); ?>;
					--wppic-github-icon-color: <?php echo esc_attr( $attributes['iconcolor'] ); ?>;
					--wppic-github-icon-color-hover: <?php echo esc_attr( $attributes['iconcolorhover'] ); ?>;
					--wppic-github-border: <?php echo esc_attr( $attributes['bordercolor'] ); ?>;
					--wppic-github-language-bg: <?php echo esc_attr( $attributes['languagebgcolor'] ); ?>;
					--wppic-github-language-color: <?php echo esc_attr( $attributes['languagetextcolor'] ); ?>;
					--wppic-github-sponsors-color: <?php echo esc_attr( $attributes['sponsorscolor'] ); ?>;
					--wppic-github-button-bg: <?php echo esc_attr( $attributes['buttonbgcolor'] ); ?>;
					--wppic-github-button-bg-hover: <?php echo esc_attr( $attributes['buttonbgcolorhover'] ); ?>;
					--wppic-github-button-text-color: <?php echo esc_attr( $attributes['buttontextcolor'] ); ?>;
					--wppic-github-button-text-color-hover: <?php echo esc_attr( $attributes['buttontextcolorhover'] ); ?>;
					}
				</style>
			<?php
		}
		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" id="<?php echo esc_attr( $attributes['uniqueid'] ); ?>">
			<div class="wppic-github-info-card-wrapper">
			<?php
			if ( (bool) $attributes['showtopbar'] ) :
				?>
				<div class="wppic-github-info-card-header">
					<div class="wppic-github-info-card-header-left">
						<?php if ( $has_language ) : ?>
							<div class="wppic-github-info-card-header-language">
								<?php echo esc_html( $language ); ?>
							</div>
						<?php endif; ?>
						<?php if ( $has_license ) : ?>
							<div class="wppic-github-info-card-header-license">
								<?php echo esc_html( $license ); ?>
							</div>
						<?php endif; ?>
					</div>
					<div class="wppic-github-info-card-header-right">
						<?php
						if ( $has_homepage_url ) :
							?>
						<div class="wppic-github-info-card-header-icon wppic-github-info-card-icon-home">
							<a href="<?php echo esc_url( $homepage_url ); ?>">
								<svg class="wppic-github-info-card-icon-svg" width="20" height="20" viewBox="0 0 640 640" fill="none" xmlns="http://www.w3.org/2000/svg">
									<use xlink:href="#fa-icon-home" />
								</svg>
							</a>
						</div>
							<?php
						endif;
						?>
						<?php
						if ( $has_github_url ) :
							?>
						<div class="wppic-github-info-card-header-icon wppic-github-info-card-icon-github">
							<a href="<?php echo esc_url( $github_url ); ?>">
								<svg class="wppic-github-info-card-icon-svg" width="20" height="20" viewBox="0 0 640 640" fill="none" xmlns="http://www.w3.org/2000/svg">
									<use xlink:href="#fa-icon-github" />
								</svg>
							</a>
						</div>
							<?php
							endif;
						?>
						<?php
						if ( $has_sponsors_url ) :
							?>
						<div class="wppic-github-info-card-header-icon wppic-github-info-card-icon-heart">
							<a href="<?php echo esc_url( $sponsors_url ); ?>">
								<svg class="wppic-github-info-card-icon-svg" width="20" height="20" viewBox="0 0 640 640" fill="none" xmlns="http://www.w3.org/2000/svg">
									<use xlink:href="#fa-icon-heart" />
								</svg>
							</a>
						</div>
							<?php
						endif;
						?>
						<?php
						if ( $has_stargazers_count ) :
							?>
						<div class="wppic-github-info-card-header-icon wppic-github-info-card-icon-star">
							<a href="<?php echo esc_url( $stargazers_count_url ); ?>">
								<svg class="wppic-github-info-card-icon-svg" width="20" height="20" viewBox="0 0 640 640" fill="none" xmlns="http://www.w3.org/2000/svg">
									<use xlink:href="#fa-icon-star" />
								</svg>
							</a>
						</div>
							<?php
						endif;
						?>
					</div>
				</div><!-- .wppic-github-info-card-header -->
				<?php
			endif;
			?>
			<div class="wppic-github-info-card-author-section">
				<div class="wppic-github-info-card-author-section-avatar">
					<a href="<?php echo esc_url( $github_url ); ?>" title="<?php echo esc_attr( $repo_full_name ); ?>">
						<img
							class="wppic-github-info-card-avatar-img"
							src="<?php echo esc_url( $avatar_image ); ?>"
							alt="<?php echo esc_attr( $repo_full_name ); ?>"
							loading="lazy"
							decoding="async"
							<?php if ( $avatar_image !== $default_github_avatar ) : ?>
							data-wppic-avatar-fallback="<?php echo esc_url( $default_github_avatar ); ?>"
							<?php endif; ?>
						/>
					</a>
				</div>
				<div class="wppic-github-info-card-author-section-info">
					<div class="wppic-github-info-card-author-section-name">
						<a href="<?php echo esc_url( $github_url ); ?>">
							<?php echo esc_html( $repo_name ); ?>
						</a>
					</div>
					<?php if ( (bool) $attributes['showauthorbar'] ) : ?>
						<div class="wppic-github-info-card-author-section-login">
							<?php echo esc_html__( 'By', 'wp-plugin-info-card' ); ?>
							<?php
							if ( $has_org_name_url ) :
								?>
								<a href="<?php echo esc_url( $org_name_url ); ?>">
									<?php echo esc_html( $org_name ); ?>
								</a>
								<?php
							else :
								?>
								<?php echo esc_html( $org_name ); ?>
								<?php
							endif;
							?>
						</div>
					<?php endif; ?>
				</div>
			</div><!-- .wppic-github-info-card-author-section -->
			<?php if ( (bool) $attributes['showstatsbar'] ) : ?>
				<div class="wppic-github-info-card-meta">
					<div class="wppic-github-info-card-meta-item">
						<?php if ( $has_stargazers_count ) : ?>
							<a href="<?php echo esc_url( $stargazers_count_url ); ?>">
								<div class="wppic-github-info-card-meta-item-icon">
									<svg class="wppic-github-info-card-icon-svg" width="20" height="20" viewBox="0 0 640 640" fill="none" xmlns="http://www.w3.org/2000/svg">
										<use xlink:href="#fa-icon-star" />
									</svg>
								</div>
								<div class="wppic-github-info-card-meta-item-text">
									<?php echo esc_html( Functions::abbreviate_number( $stargazers_count, 1 ) ); ?>
								</div>
							</a>
						<?php endif; ?>
					</div>
					<div class="wppic-github-info-card-meta-item">
						<?php if ( $has_forks_count ) : ?>
							<a href="<?php echo esc_url( $forks_count_url ); ?>">
								<div class="wppic-github-info-card-meta-item-icon">
									<svg class="wppic-github-info-card-icon-svg" width="20" height="20" viewBox="0 0 640 640" fill="none" xmlns="http://www.w3.org/2000/svg">
										<use xlink:href="#fa-icon-fork" />
									</svg>
								</div>
								<div class="wppic-github-info-card-meta-item-text">
									<?php echo esc_html( Functions::abbreviate_number( $forks_count ) ); ?>
								</div>
							</a>
						<?php endif; ?>
					</div>
					<div class="wppic-github-info-card-meta-item">
						<?php if ( $has_watchers_count ) : ?>
							<a href="<?php echo esc_url( $watchers_count_url ); ?>">
								<div class="wppic-github-info-card-meta-item-icon">
									<svg class="wppic-github-info-card-icon-svg" width="20" height="20" viewBox="0 0 640 640" fill="none" xmlns="http://www.w3.org/2000/svg">
										<use xlink:href="#fa-icon-eye" />
									</svg>
								</div>
								<div class="wppic-github-info-card-meta-item-text">
									<?php echo esc_html( Functions::abbreviate_number( $subscribers_count ) ); ?>
								</div>
							</a>
						<?php endif; ?>
					</div>
					<?php if ( ! empty( $latest_release_tag_name ) ) : ?>
					<div class="wppic-github-info-card-meta-item">
							<a href="<?php echo esc_url( $latest_release_url ); ?>">
								<div class="wppic-github-info-card-meta-item-icon">
									<svg class="wppic-github-info-card-icon-svg" width="20" height="20" viewBox="0 0 640 640" fill="none" xmlns="http://www.w3.org/2000/svg">
										<use xlink:href="#fa-icon-code" />
									</svg>
								</div>
								<div class="wppic-github-info-card-meta-item-text">
									<?php echo esc_html( mb_strimwidth( $latest_release_tag_name, 0, 10, '...' ) ); ?>
								</div>
							</a>
					</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="wppic-github-info-card-description">
				<?php echo esc_html( $repo_description ); ?>
			</div>
			<div class="wppic-github-info-card-buttons">
				<div class="wppic-github-info-card-buttons-left">
					<?php
					if ( $show_last_updated ) :
						?>
						<div class="wppic-github-info-card-last-updated">
							<span class="wppic-github-info-card-last-updated-label"><?php echo esc_html__( 'Last updated:', 'wp-plugin-info-card' ); ?></span> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $last_updated_date ) ) ); ?>
						</div>
					<?php endif; ?>
				</div>
				<div class="wppic-github-info-card-buttons-right">
					<div class="wppic-github-info-card-buttons-right-button">
						<a
							href="<?php echo esc_url( $button_url ); ?>"
							class="wppic-github-button button-reset wppic-github-button-<?php echo esc_attr( $button_type ); ?>"
						>
							<?php echo esc_html( $button_text ); ?>
						</a>
					</div>
				</div>
			</div>
			</div><!-- .wppic-github-info-card-wrapper -->
		</div><!-- .wppic-github-info-card -->

		<?php
		return ob_get_clean();
	}

	/**
	 * Issue or reuse a public lazy-load token for one GitHub owner/repo.
	 *
	 * The token is not tied to a user, so a cached page can hydrate for every visitor.
	 * It only authorizes the repository this site already rendered.
	 *
	 * @param string $username Repository owner.
	 * @param string $repo     Repository name.
	 * @return string Token printed into the skeleton markup.
	 */
	private static function get_github_lazy_load_token( $username, $repo ) {
		$username = sanitize_title( $username );
		$repo     = sanitize_title( $repo );
		$ttl      = 12 * HOUR_IN_SECONDS;
		$repo_key = 'wppic_github_lazy_' . sanitize_key( $username . '/' . $repo );
		$existing = get_transient( $repo_key );

		if ( is_string( $existing ) && '' !== $existing ) {
			$stored = get_transient( 'wppic_github_lazy_token_' . $existing );
			if ( is_array( $stored ) ) {
				set_transient( $repo_key, $existing, $ttl );
				set_transient( 'wppic_github_lazy_token_' . $existing, $stored, $ttl );
				return $existing;
			}
		}

		$token   = wp_generate_password( 32, false );
		$payload = array(
			'username' => $username,
			'repo'     => $repo,
		);
		set_transient( $repo_key, $token, $ttl );
		set_transient( 'wppic_github_lazy_token_' . $token, $payload, $ttl );

		return $token;
	}

	/**
	 * Confirm a lazy-load token was minted for this owner and repo.
	 *
	 * Missing, expired, and mismatched tokens return the same error so callers
	 * cannot tell those cases apart.
	 *
	 * @param string $token    Token from the request.
	 * @param string $username Sanitized owner.
	 * @param string $repo     Sanitized repository.
	 * @return true|WP_Error
	 */
	private static function verify_github_lazy_load_token( $token, $username, $repo ) {
		$token           = is_string( $token ) ? $token : '';
		$stored          = '' === $token ? false : get_transient( 'wppic_github_lazy_token_' . $token );
		$stored_username = ( is_array( $stored ) && isset( $stored['username'] ) ) ? (string) $stored['username'] : '';
		$stored_repo     = ( is_array( $stored ) && isset( $stored['repo'] ) ) ? (string) $stored['repo'] : '';
		$username_ok     = '' !== $username && strlen( $stored_username ) === strlen( $username ) && hash_equals( $stored_username, $username );
		$repo_ok         = '' !== $repo && strlen( $stored_repo ) === strlen( $repo ) && hash_equals( $stored_repo, $repo );

		if ( ! $username_ok || ! $repo_ok ) {
			return new \WP_Error(
				'wppic_github_token_invalid',
				__( 'GitHub card could not be loaded.', 'wp-plugin-info-card' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Get GitHub card HTML.
	 *
	 * @param object $request The request object.
	 * @return WP_REST_Response|WP_Error The GitHub card HTML, or an error before any remote fetch.
	 */
	public function get_github_card_html( $request ) {
		$attributes = $request->get_param( 'cardAttributes' );
		$username   = sanitize_title( (string) $request->get_param( 'username' ) );
		$repo       = sanitize_title( (string) $request->get_param( 'repo' ) );
		$token      = sanitize_text_field( (string) $request->get_param( 'token' ) );
		$verified   = self::verify_github_lazy_load_token( $token, $username, $repo );
		if ( is_wp_error( $verified ) ) {
			return $verified;
		}

		// Ignore any client force flag. Public hydration may only read the normal cache.
		$github_data = wppic_api_parser( 'github', $username . '/' . $repo, 720, '', false, false );
		if ( empty( $github_data ) ) {
			return new \WP_Error(
				'wppic_github_not_found',
				__( 'No data found.', 'wp-plugin-info-card' ),
				array( 'status' => 404 )
			);
		}
		$attributes = Functions::sanitize_array_recursive( $attributes );

		define( 'WPPIC_REST_REQUEST', true );
		$html = self::shortcode_github_info_card( $attributes, '', 'github-info-card', $github_data );
		return rest_ensure_response( array( 'html' => $html ) );
	}

	/**
	 * REST API callback for getting profile badges HTML.
	 *
	 * @param WP_REST_Request $request REST API request object.
	 * @return WP_REST_Response|WP_Error REST API response.
	 */
	public function get_profile_badges_html( $request ) {
		// Validate one-time use token.
		$token = sanitize_key( $request->get_param( 'token' ) );
		if ( empty( $token ) ) {
			return new \WP_Error(
				'missing_token',
				__( 'Missing security token.', 'wp-plugin-info-card' ),
				array( 'status' => 400 )
			);
		}

		// Get token data from transient.
		$token_data = get_transient( 'wppic_badge_token_' . $token );
		if ( false === $token_data ) {
			return new \WP_Error(
				'invalid_token',
				__( 'Invalid or expired security token.', 'wp-plugin-info-card' ),
				array( 'status' => 401 )
			);
		}

		// Get author slug from request (needed for nonce verification).
		$author_slug = sanitize_title( $request->get_param( 'authorSlug' ) );

		// Verify REST API nonce (additional security layer).
		$nonce = isset( $token_data['nonce'] ) ? $token_data['nonce'] : '';
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wppic_badge_load_' . $author_slug ) ) {
			return new \WP_Error(
				'invalid_nonce',
				__( 'Invalid security nonce.', 'wp-plugin-info-card' ),
				array( 'status' => 401 )
			);
		}

		// Verify author slug matches token.
		if ( $author_slug !== $token_data['author_slug'] ) {
			return new \WP_Error(
				'author_mismatch',
				__( 'Author slug mismatch.', 'wp-plugin-info-card' ),
				array( 'status' => 400 )
			);
		}

		// Check attempt limit (allow up to 3 retries for network errors).
		$attempts = isset( $token_data['attempts'] ) ? absint( $token_data['attempts'] ) : 0;
		if ( $attempts >= 3 ) {
			// Delete token after max attempts.
			delete_transient( 'wppic_badge_token_' . $token );
			return new \WP_Error(
				'max_attempts',
				__( 'Maximum retry attempts exceeded.', 'wp-plugin-info-card' ),
				array( 'status' => 429 )
			);
		}

		// Increment attempt counter.
		$token_data['attempts'] = $attempts + 1;
		set_transient( 'wppic_badge_token_' . $token, $token_data, 10 * MINUTE_IN_SECONDS );

		// Fetch profile data (will use multi-layer cache or scrape if needed).
		$profile_data = Functions::wppic_get_profile_data( $author_slug, false );

		if ( empty( $profile_data ) ) {
			return new \WP_Error(
				'no_profile_data',
				__( 'No profile data found.', 'wp-plugin-info-card' ),
				array( 'status' => 404 )
			);
		}

		// Extract badges from profile data (check both 'badges' and 'member_badges' keys for compatibility).
		$badges = $profile_data['badges'] ?? $profile_data['member_badges'] ?? array();

		if ( empty( $badges ) ) {
			return new \WP_Error(
				'no_badges',
				__( 'No badges found for this profile.', 'wp-plugin-info-card' ),
				array( 'status' => 404 )
			);
		}

		// Validate badges.
		$valid_badges = self::validate_badges( $badges );

		if ( empty( $valid_badges ) ) {
			return new \WP_Error(
				'no_valid_badges',
				__( 'No valid badges found.', 'wp-plugin-info-card' ),
				array( 'status' => 404 )
			);
		}

		// Get hideHeading from token data. Needed to render badge HTML. Used in render_badge_list().
		$hide_heading = isset( $token_data['hideHeading'] ) ? (bool) $token_data['hideHeading'] : false;

		// Render only badge HTML (not the wrapper).
		$html = self::render_badge_list( $valid_badges, $hide_heading );

		// Delete token after successful use.
		delete_transient( 'wppic_badge_token_' . $token );

		return rest_ensure_response( array( 'html' => $html ) );
	}

	/**
	 * Get badge data structure (mirrors JS badges array).
	 *
	 * @return array Array of badge data.
	 */
	public static function get_badge_data() {
		$badges = array(
			array(
				'id'       => 'badge-code',
				'source'   => 'wporg',
				'class'    => 'badge-code',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-editor-code',
				'label'    => __( 'Core Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-code-committer',
				'source'   => 'wporg',
				'class'    => 'badge-code-committer has-overlay',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-editor-code',
				'label'    => __( 'Core Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-design',
				'source'   => 'wporg',
				'class'    => 'badge-design has-overlay',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-art',
				'label'    => __( 'Design Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-design-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-design-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-art',
				'label'    => __( 'Design Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-plugins',
				'source'   => 'wporg',
				'class'    => 'badge-plugins',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-admin-plugins',
				'label'    => __( 'Plugin Developer', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-themes',
				'source'   => 'wporg',
				'class'    => 'badge-themes',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-admin-appearance',
				'label'    => __( 'Theme Developer', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-organizer',
				'source'   => 'wporg',
				'class'    => 'badge-organizer',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-tickets',
				'label'    => __( 'WordCamp Organizer', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-speaker',
				'source'   => 'wporg',
				'class'    => 'badge-speaker',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-megaphone',
				'label'    => __( 'WordCamp Speaker', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-meta-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-meta-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-networking',
				'label'    => __( 'Meta Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-translation-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-translation-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-translation',
				'label'    => __( 'Translation Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-translation-editor',
				'source'   => 'wporg',
				'class'    => 'badge-translation-editor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-translation',
				'label'    => __( 'Translation Editor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-marketing',
				'source'   => 'wporg',
				'class'    => 'badge-marketing has-overlay',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-format-status',
				'label'    => __( 'Marketing Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-marketing-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-marketing-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-format-status',
				'label'    => __( 'Marketing Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-support-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-support-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-format-chat',
				'label'    => __( 'Support Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-community-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-community-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-groups',
				'label'    => __( 'Community Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-plugins-reviewer',
				'source'   => 'wporg',
				'class'    => 'badge-plugins-reviewer has-overlay',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-admin-plugins',
				'label'    => __( 'Plugins Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-themes-reviewer',
				'source'   => 'wporg',
				'class'    => 'badge-themes-reviewer has-overlay',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-admin-appearance',
				'label'    => __( 'Themes Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-wordpress-tv-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-wordpress-tv-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-video-alt2',
				'label'    => __( 'WordPress TV Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-wordcamp-volunteer',
				'source'   => 'wporg',
				'class'    => 'badge-wordcamp-volunteer',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-hammer',
				'label'    => __( 'WordCamp Volunteer', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-documentation',
				'source'   => 'wporg',
				'class'    => 'badge-documentation has-overlay',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-admin-page',
				'label'    => __( 'Documentation Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-documentation-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-documentation-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-admin-page',
				'label'    => __( 'Documentation Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-accessibility-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-accessibility-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-universal-access',
				'label'    => __( 'Accessibility Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-accessibility',
				'source'   => 'wporg',
				'class'    => 'badge-accessibility has-overlay',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-universal-access',
				'label'    => __( 'Accessibility Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-mobile',
				'source'   => 'wporg',
				'class'    => 'badge-mobile',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-smartphone',
				'label'    => __( 'Mobile Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-training',
				'source'   => 'wporg',
				'class'    => 'badge-training has-overlay',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-welcome-learn-more',
				'label'    => __( 'Training Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-training-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-training-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-welcome-learn-more',
				'label'    => __( 'Training Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-media-corps-team',
				'source'   => 'wporg',
				'class'    => 'badge-media-corps-team has-overlay',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-format-status',
				'label'    => __( 'Media Corps Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-media-corps-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-media-corps-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-format-status',
				'label'    => __( 'Media Corps Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-wp-cli',
				'source'   => 'wporg',
				'class'    => 'badge-wp-cli has-overlay',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-arrow-right-alt2',
				'label'    => __( 'WP CLI Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-wp-cli-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-wp-cli-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-arrow-right-alt2',
				'label'    => __( 'WP CLI Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-hosting',
				'source'   => 'wporg',
				'class'    => 'badge-hosting has-overlay',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-cloud',
				'label'    => __( 'Hosting Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-hosting-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-hosting-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-cloud',
				'label'    => __( 'Hosting Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-tide',
				'source'   => 'wporg',
				'class'    => 'badge-tide has-overlay',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-tide',
				'label'    => __( 'Tide Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-tide-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-tide-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-tide',
				'label'    => __( 'Tide Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-security-team',
				'source'   => 'wporg',
				'class'    => 'badge-security-team',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-lock',
				'label'    => __( 'Security Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-security-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-security-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-lock',
				'label'    => __( 'Security Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-bbpress',
				'source'   => 'wporg',
				'class'    => 'badge-bbpress has-overlay',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-buddicons-bbpress-logo',
				'label'    => __( 'BBPress Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-bbpress-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-bbpress-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-buddicons-bbpress-logo',
				'label'    => __( 'BBPress Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-test',
				'source'   => 'wporg',
				'class'    => 'badge-test has-overlay',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-welcome-view-site',
				'label'    => __( 'Test Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-test-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-test-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-welcome-view-site',
				'label'    => __( 'Test Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-patterns-team',
				'source'   => 'wporg',
				'class'    => 'badge-patterns-team',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-layout',
				'label'    => __( 'Patterns Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-pattern-author',
				'source'   => 'wporg',
				'class'    => 'badge-pattern-author',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-layout',
				'label'    => __( 'Patterns Author', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-photos-team',
				'source'   => 'wporg',
				'class'    => 'badge-photos-team',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-camera',
				'label'    => __( 'Photos Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-photo-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-photo-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-camera',
				'label'    => __( 'Photo Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-sustainability-team',
				'source'   => 'wporg',
				'class'    => 'badge-sustainability-team',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-admin-site-alt3',
				'label'    => __( 'Sustainability Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-sustainability-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-sustainability-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-admin-site-alt3',
				'label'    => __( 'Sustainability Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-playground',
				'source'   => 'wporg',
				'class'    => 'badge-playground',
				'iconType' => 'image',
				'icon'     => 'data:image/svg+xml,%3Csvg viewBox=\'0 0 264 264\' fill=\'none\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath fill-rule=\'evenodd\' clip-rule=\'evenodd\' d=\'M31.4142 96.1617C25.6576 104.102 22.6201 113.859 22.1316 124.582C22.0526 126.317 22.0403 128.076 22.094 129.858C22.8215 153.981 35.6471 182.078 58.7836 205.215C94.1918 240.623 141.219 251.881 167.838 232.585C156.641 229.946 145.313 225.834 134.159 220.348C131.296 220.223 128.216 219.849 124.914 219.189C119.268 218.06 113.299 216.122 107.218 213.365C107.217 213.364 107.217 213.363 107.217 213.362C96.0955 208.321 84.6002 200.542 74.028 189.97C63.4568 179.399 55.6787 167.905 50.6372 156.784C50.6368 156.784 50.6364 156.784 50.636 156.784C47.879 150.702 45.9405 144.732 44.8113 139.086C44.1511 135.786 43.7778 132.706 43.6523 129.843C38.1659 118.688 34.0528 107.359 31.4142 96.1617ZM9.23712 163.298C10.043 162.493 10.8885 161.741 11.7707 161.043C14.3643 168.613 17.7868 176.167 21.9822 183.554C21.4183 186.043 21.2992 189.453 22.2134 194.024C24.0281 203.098 29.6869 214.237 39.7259 224.276C49.765 234.315 60.9044 239.974 69.9778 241.789C74.5503 242.703 77.9607 242.584 80.4503 242.019C87.8372 246.214 95.3905 249.636 102.961 252.229C102.262 253.112 101.51 253.958 100.704 254.765C83.8651 271.603 49.7393 264.778 24.4815 239.521C-0.776242 214.263 -7.6014 180.137 9.23712 163.298ZM93.0817 170.917C143.597 221.433 211.849 235.083 245.526 201.406C257.702 189.229 263.692 172.533 263.988 153.789C264.512 120.693 247.287 81.2134 215.037 48.9631C164.521 -1.55248 96.2698 -15.2028 62.5928 18.4742C50.4 30.6671 44.4109 47.392 44.129 66.1665C43.6329 99.2474 60.8558 138.691 93.0817 170.917ZM164.222 179.881C165.16 184.571 165.519 188.815 165.411 192.623C146.44 186.02 126.336 173.684 108.326 155.674C90.3161 137.664 77.98 117.56 71.377 98.5895C75.1847 98.481 79.4293 98.84 84.1196 99.7781C100.09 102.972 118.652 112.641 135.006 128.995C151.359 145.348 161.028 163.911 164.222 179.881ZM77.8372 33.7186C68.7098 42.8461 63.8395 57.692 66.2929 77.3346C92.1159 74.8241 124.303 87.8029 150.25 113.75C176.197 139.698 189.176 171.884 186.666 197.707C206.308 200.16 221.154 195.29 230.281 186.163C240.437 176.007 245.322 158.772 240.713 135.728C236.14 112.861 222.46 86.8754 199.793 64.2075C177.125 41.5396 151.139 27.86 128.272 23.2866C105.228 18.6777 87.9929 23.563 77.8372 33.7186Z\' fill=\'%233858E9\'/%3E%3C/svg%3E',
				'label'    => __( 'Playground Developer', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-playground-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-playground-contributor',
				'iconType' => 'image',
				'icon'     => 'data:image/svg+xml,%3Csvg viewBox=\'0 0 264 264\' fill=\'none\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath fill-rule=\'evenodd\' clip-rule=\'evenodd\' d=\'M31.4142 96.1617C25.6576 104.102 22.6201 113.859 22.1316 124.582C22.0526 126.317 22.0403 128.076 22.094 129.858C22.8215 153.981 35.6471 182.078 58.7836 205.215C94.1918 240.623 141.219 251.881 167.838 232.585C156.641 229.946 145.313 225.834 134.159 220.348C131.296 220.223 128.216 219.849 124.914 219.189C119.268 218.06 113.299 216.122 107.218 213.365C107.217 213.364 107.217 213.363 107.217 213.362C96.0955 208.321 84.6002 200.542 74.028 189.97C63.4568 179.399 55.6787 167.905 50.6372 156.784C50.6368 156.784 50.6364 156.784 50.636 156.784C47.879 150.702 45.9405 144.732 44.8113 139.086C44.1511 135.786 43.7778 132.706 43.6523 129.843C38.1659 118.688 34.0528 107.359 31.4142 96.1617ZM9.23712 163.298C10.043 162.493 10.8885 161.741 11.7707 161.043C14.3643 168.613 17.7868 176.167 21.9822 183.554C21.4183 186.043 21.2992 189.453 22.2134 194.024C24.0281 203.098 29.6869 214.237 39.7259 224.276C49.765 234.315 60.9044 239.974 69.9778 241.789C74.5503 242.703 77.9607 242.584 80.4503 242.019C87.8372 246.214 95.3905 249.636 102.961 252.229C102.262 253.112 101.51 253.958 100.704 254.765C83.8651 271.603 49.7393 264.778 24.4815 239.521C-0.776242 214.263 -7.6014 180.137 9.23712 163.298ZM93.0817 170.917C143.597 221.433 211.849 235.083 245.526 201.406C257.702 189.229 263.692 172.533 263.988 153.789C264.512 120.693 247.287 81.2134 215.037 48.9631C164.521 -1.55248 96.2698 -15.2028 62.5928 18.4742C50.4 30.6671 44.4109 47.392 44.129 66.1665C43.6329 99.2474 60.8558 138.691 93.0817 170.917ZM164.222 179.881C165.16 184.571 165.519 188.815 165.411 192.623C146.44 186.02 126.336 173.684 108.326 155.674C90.3161 137.664 77.98 117.56 71.377 98.5895C75.1847 98.481 79.4293 98.84 84.1196 99.7781C100.09 102.972 118.652 112.641 135.006 128.995C151.359 145.348 161.028 163.911 164.222 179.881ZM77.8372 33.7186C68.7098 42.8461 63.8395 57.692 66.2929 77.3346C92.1159 74.8241 124.303 87.8029 150.25 113.75C176.197 139.698 189.176 171.884 186.666 197.707C206.308 200.16 221.154 195.29 230.281 186.163C240.437 176.007 245.322 158.772 240.713 135.728C236.14 112.861 222.46 86.8754 199.793 64.2075C177.125 41.5396 151.139 27.86 128.272 23.2866C105.228 18.6777 87.9929 23.563 77.8372 33.7186Z\' fill=\'%233858E9\'/%3E%3C/svg%3E',
				'label'    => __( 'Playground Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-core-ai-team',
				'source'   => 'wporg',
				'class'    => 'badge-core-ai-team has-overlay',
				'iconType' => 'image',
				'icon'     => 'data:image/svg+xml,%3Csvg viewBox=\'0 0 20 20\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M17 5H16V3H11V5H9V3H4V5H3C1.9 5 1 5.9 1 7V15C1 16.1 1.9 17 3 17H17C18.1 17 19 16.1 19 15V7C19 5.9 18.1 5 17 5ZM17.5 15C17.5 15.3 17.3 15.5 17 15.5H3C2.7 15.5 2.5 15.3 2.5 15V7C2.5 6.7 2.7 6.5 3 6.5H17C17.3 6.5 17.5 6.7 17.5 7V15Z\' stroke=\'none\' fill=\'%237A00DF\' fill-rule=\'evenodd\' clip-rule=\'evenodd\'%3E%3C/path%3E%3Cpath d=\'M14 10L14.1474 10.3983C14.3406 10.9206 14.4373 11.1817 14.6278 11.3722C14.8183 11.5627 15.0794 11.6594 15.6017 11.8526L16 12L15.6017 12.1474C15.0794 12.3406 14.8183 12.4373 14.6278 12.6278C14.4373 12.8183 14.3406 13.0794 14.1474 13.6017L14 14L13.8526 13.6017C13.6594 13.0794 13.5627 12.8183 13.3722 12.6278C13.1817 12.4373 12.9206 12.3406 12.3983 12.1474L12 12L12.3983 11.8526C12.9206 11.6594 13.1817 11.5627 13.3722 11.3722C13.5627 11.1817 13.6594 10.9206 13.8526 10.3983L14 10Z\' stroke=\'none\' fill=\'%237A00DF\' fill-rule=\'evenodd\' clip-rule=\'evenodd\'%3E%3C/path%3E%3Cpath d=\'M12 8L12.0737 8.19915C12.1703 8.46029 12.2186 8.59086 12.3139 8.68611C12.4091 8.78136 12.5397 8.82968 12.8009 8.92631L13 9L12.8009 9.07369C12.5397 9.17032 12.4091 9.21864 12.3139 9.31389C12.2186 9.40914 12.1703 9.53971 12.0737 9.80085L12 10L11.9263 9.80085C11.8297 9.53971 11.7814 9.40914 11.6861 9.31389C11.5909 9.21864 11.4603 9.17032 11.1991 9.07369L11 9L11.1991 8.92631C11.4603 8.82968 11.5909 8.78136 11.6861 8.68611C11.7814 8.59086 11.8297 8.46029 11.9263 8.19915L12 8Z\' stroke=\'none\' fill=\'%237A00DF\' fill-rule=\'evenodd\' clip-rule=\'evenodd\'%3E%3C/path%3E%3Cpath d=\'M10.25 10L10.3421 10.2489C10.4629 10.5754 10.5233 10.7386 10.6424 10.8576C10.7614 10.9767 10.9246 11.0371 11.2511 11.1579L11.5 11.25L11.2511 11.3421C10.9246 11.4629 10.7614 11.5233 10.6424 11.6424C10.5233 11.7614 10.4629 11.9246 10.3421 12.2511L10.25 12.5L10.1579 12.2511C10.0371 11.9246 9.97671 11.7614 9.85764 11.6424C9.73857 11.5233 9.57536 11.4629 9.24893 11.3421L9 11.25L9.24893 11.1579C9.57536 11.0371 9.73857 10.9767 9.85764 10.8576C9.97671 10.7386 10.0371 10.5754 10.1579 10.2489L10.25 10Z\' stroke=\'none\' fill=\'%237A00DF\' fill-rule=\'evenodd\' clip-rule=\'evenodd\'%3E%3C/path%3E%3C/svg%3E',
				'label'    => __( 'Core AI Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-core-ai-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-core-ai-contributor',
				'iconType' => 'image',
				'icon'     => 'data:image/svg+xml,%3Csvg viewBox=\'0 0 20 20\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M17 5H16V3H11V5H9V3H4V5H3C1.9 5 1 5.9 1 7V15C1 16.1 1.9 17 3 17H17C18.1 17 19 16.1 19 15V7C19 5.9 18.1 5 17 5ZM17.5 15C17.5 15.3 17.3 15.5 17 15.5H3C2.7 15.5 2.5 15.3 2.5 15V7C2.5 6.7 2.7 6.5 3 6.5H17C17.3 6.5 17.5 6.7 17.5 7V15Z\' stroke=\'none\' fill=\'%237A00DF\' fill-rule=\'evenodd\' clip-rule=\'evenodd\'%3E%3C/path%3E%3Cpath d=\'M14 10L14.1474 10.3983C14.3406 10.9206 14.4373 11.1817 14.6278 11.3722C14.8183 11.5627 15.0794 11.6594 15.6017 11.8526L16 12L15.6017 12.1474C15.0794 12.3406 14.8183 12.4373 14.6278 12.6278C14.4373 12.8183 14.3406 13.0794 14.1474 13.6017L14 14L13.8526 13.6017C13.6594 13.0794 13.5627 12.8183 13.3722 12.6278C13.1817 12.4373 12.9206 12.3406 12.3983 12.1474L12 12L12.3983 11.8526C12.9206 11.6594 13.1817 11.5627 13.3722 11.3722C13.5627 11.1817 13.6594 10.9206 13.8526 10.3983L14 10Z\' stroke=\'none\' fill=\'%237A00DF\' fill-rule=\'evenodd\' clip-rule=\'evenodd\'%3E%3C/path%3E%3Cpath d=\'M12 8L12.0737 8.19915C12.1703 8.46029 12.2186 8.59086 12.3139 8.68611C12.4091 8.78136 12.5397 8.82968 12.8009 8.92631L13 9L12.8009 9.07369C12.5397 9.17032 12.4091 9.21864 12.3139 9.31389C12.2186 9.40914 12.1703 9.53971 12.0737 9.80085L12 10L11.9263 9.80085C11.8297 9.53971 11.7814 9.40914 11.6861 9.31389C11.5909 9.21864 11.4603 9.17032 11.1991 9.07369L11 9L11.1991 8.92631C11.4603 8.82968 11.5909 8.78136 11.6861 8.68611C11.7814 8.59086 11.8297 8.46029 11.9263 8.19915L12 8Z\' stroke=\'none\' fill=\'%237A00DF\' fill-rule=\'evenodd\' clip-rule=\'evenodd\'%3E%3C/path%3E%3Cpath d=\'M10.25 10L10.3421 10.2489C10.4629 10.5754 10.5233 10.7386 10.6424 10.8576C10.7614 10.9767 10.9246 11.0371 11.2511 11.1579L11.5 11.25L11.2511 11.3421C10.9246 11.4629 10.7614 11.5233 10.6424 11.6424C10.5233 11.7614 10.4629 11.9246 10.3421 12.2511L10.25 12.5L10.1579 12.2511C10.0371 11.9246 9.97671 11.7614 9.85764 11.6424C9.73857 11.5233 9.57536 11.4629 9.24893 11.3421L9 11.25L9.24893 11.1579C9.57536 11.0371 9.73857 10.9767 9.85764 10.8576C9.97671 10.7386 10.0371 10.5754 10.1579 10.2489L10.25 10Z\' stroke=\'none\' fill=\'%237A00DF\' fill-rule=\'evenodd\' clip-rule=\'evenodd\'%3E%3C/path%3E%3C/svg%3E',
				'label'    => __( 'Core AI Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-buddypress',
				'source'   => 'wporg',
				'class'    => 'badge-buddypress has-overlay',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-buddicons-buddypress-logo',
				'label'    => __( 'BuddyPress Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-buddypress-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-buddypress-contributor',
				'iconType' => 'dashicon',
				'icon'     => 'dashicons-buddicons-buddypress-logo',
				'label'    => __( 'BuddyPress Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-openverse',
				'source'   => 'wporg',
				'class'    => 'badge-openverse has-overlay',
				'iconType' => 'image',
				'icon'     => 'data:image/svg+xml,%3Csvg viewBox=\'0 0 200 200\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M142.044 93.023c16.159 0 29.259-13.213 29.259-29.512 0-16.298-13.1-29.511-29.259-29.511s-29.259 13.213-29.259 29.511c0 16.299 13.1 29.512 29.259 29.512ZM28 63.511c0 16.24 12.994 29.512 29.074 29.512V34C40.994 34 28 47.19 28 63.511ZM70.392 63.511c0 16.24 12.994 29.512 29.074 29.512V34c-15.998 0-29.074 13.19-29.074 29.511ZM142.044 165.975c16.159 0 29.259-13.213 29.259-29.512 0-16.298-13.1-29.511-29.259-29.511s-29.259 13.213-29.259 29.511c0 16.299 13.1 29.512 29.259 29.512ZM70.392 136.414c0 16.257 12.994 29.544 29.074 29.544v-59.006c-15.999 0-29.074 13.204-29.074 29.462ZM28 136.414c0 16.34 12.994 29.544 29.074 29.544v-59.006c-16.08 0-29.074 13.204-29.074 29.462Z\' fill=\'%23C52B9B\'/%3E%3C/svg%3E',
				'label'    => __( 'Openverse Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-openverse-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-openverse-contributor',
				'iconType' => 'image',
				'icon'     => 'data:image/svg+xml,%3Csvg viewBox=\'0 0 200 200\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M142.044 93.023c16.159 0 29.259-13.213 29.259-29.512 0-16.298-13.1-29.511-29.259-29.511s-29.259 13.213-29.259 29.511c0 16.299 13.1 29.512 29.259 29.512ZM28 63.511c0 16.24 12.994 29.512 29.074 29.512V34C40.994 34 28 47.19 28 63.511ZM70.392 63.511c0 16.24 12.994 29.512 29.074 29.512V34c-15.998 0-29.074 13.19-29.074 29.511ZM142.044 165.975c16.159 0 29.259-13.213 29.259-29.512 0-16.298-13.1-29.511-29.259-29.511s-29.259 13.213-29.259 29.511c0 16.299 13.1 29.512 29.259 29.512ZM70.392 136.414c0 16.257 12.994 29.544 29.074 29.544v-59.006c-15.999 0-29.074 13.204-29.074 29.462ZM28 136.414c0 16.34 12.994 29.544 29.074 29.544v-59.006c-16.08 0-29.074 13.204-29.074 29.462Z\' fill=\'%23C52B9B\'/%3E%3C/svg%3E',
				'label'    => __( 'Openverse Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-performance-team',
				'source'   => 'wporg',
				'class'    => 'badge-performance-team has-overlay',
				'iconType' => 'image',
				'icon'     => 'data:image/svg+xml,%3C%3Fxml version=\'1.0\' encoding=\'UTF-8\'%3F%3E%3Csvg version=\'1.1\' viewBox=\'0 0 94 94\' xmlns=\'http://www.w3.org/2000/svg\' xmlns:cc=\'http://creativecommons.org/ns%23\' xmlns:dc=\'http://purl.org/dc/elements/1.1/\' xmlns:rdf=\'http://www.w3.org/1999/02/22-rdf-syntax-ns%23\'%3E%3Cdefs%3E%3Cstyle%3Epath%7Bfill:%230073aa;%7D%3C/style%3E%3C/defs%3E%3Cpath d=\'m39.21 20.85h-11.69c-1.38 0-2.5 1.12-2.5 2.5v11.69c0 1.38 1.12 2.5 2.5 2.5h11.69c1.38 0 2.5-1.12 2.5-2.5v-11.69c0-1.38-1.12-2.5-2.5-2.5z\'/%3E%3Cpath d=\'M41.71,58.96v11.69c0,.66-.26,1.3-.73,1.77-.47,.47-1.11,.73-1.77,.73h-11.69c-.66,0-1.3-.26-1.77-.73-.47-.47-.73-1.11-.73-1.77v-21.37c0-.4,.1-.79,.28-1.14,.03-.06,.07-.12,.1-.18,.21-.33,.49-.61,.83-.82l11.67-7.04c.44-.27,.95-.39,1.47-.36,.51,.03,1,.23,1.4,.55,.26,.21,.47,.46,.63,.75,.16,.29,.26,.61,.29,.94,.02,.11,.02,.22,.02,.34v5.38s0,.07,0,.11v11.08s0,.04,0,.07Z\'/%3E%3Cpath d=\'M68.98,30.23v16.84c0,.33-.06,.65-.19,.96-.13,.3-.31,.58-.54,.81l-6.88,6.88c-.23,.23-.51,.42-.81,.54-.3,.13-.63,.19-.96,.19h-13.15c-.66,0-1.3-.26-1.77-.73-.47-.47-.73-1.11-.73-1.77v-11.69c0-.66,.26-1.3,.73-1.77,.47-.47,1.11-.73,1.77-.73h13.08s1.11,0,1.11-1.11-1.11-1.11-1.11-1.11h-13.08c-.66,0-1.3-.26-1.77-.73s-.73-1.11-.73-1.77v-11.69c0-.66,.26-1.3,.73-1.77,.47-.47,1.11-.73,1.77-.73h13.15c.33,0,.65,.06,.96,.19,.3,.13,.58,.31,.81,.54l6.88,6.88c.23,.23,.42,.51,.54,.81,.13,.3,.19,.63,.19,.96Z\'/%3E%3C/svg%3E',
				'label'    => __( 'Performance Team', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-performance-contributor',
				'source'   => 'wporg',
				'class'    => 'badge-performance-contributor',
				'iconType' => 'image',
				'icon'     => 'data:image/svg+xml,%3C%3Fxml version=\'1.0\' encoding=\'UTF-8\'%3F%3E%3Csvg version=\'1.1\' viewBox=\'0 0 94 94\' xmlns=\'http://www.w3.org/2000/svg\' xmlns:cc=\'http://creativecommons.org/ns%23\' xmlns:dc=\'http://purl.org/dc/elements/1.1/\' xmlns:rdf=\'http://www.w3.org/1999/02/22-rdf-syntax-ns%23\'%3E%3Cdefs%3E%3Cstyle%3Epath%7Bfill:%230073aa;%7D%3C/style%3E%3C/defs%3E%3Cpath d=\'m39.21 20.85h-11.69c-1.38 0-2.5 1.12-2.5 2.5v11.69c0 1.38 1.12 2.5 2.5 2.5h11.69c1.38 0 2.5-1.12 2.5-2.5v-11.69c0-1.38-1.12-2.5-2.5-2.5z\'/%3E%3Cpath d=\'M41.71,58.96v11.69c0,.66-.26,1.3-.73,1.77-.47,.47-1.11,.73-1.77,.73h-11.69c-.66,0-1.3-.26-1.77-.73-.47-.47-.73-1.11-.73-1.77v-21.37c0-.4,.1-.79,.28-1.14,.03-.06,.07-.12,.1-.18,.21-.33,.49-.61,.83-.82l11.67-7.04c.44-.27,.95-.39,1.47-.36,.51,.03,1,.23,1.4,.55,.26,.21,.47,.46,.63,.75,.16,.29,.26,.61,.29,.94,.02,.11,.02,.22,.02,.34v5.38s0,.07,0,.11v11.08s0,.04,0,.07Z\'/%3E%3Cpath d=\'M68.98,30.23v16.84c0,.33-.06,.65-.19,.96-.13,.3-.31,.58-.54,.81l-6.88,6.88c-.23,.23-.51,.42-.81,.54-.3,.13-.63,.19-.96,.19h-13.15c-.66,0-1.3-.26-1.77-.73-.47-.47-.73-1.11-.73-1.77v-11.69c0-.66,.26-1.3,.73-1.77,.47-.47,1.11-.73,1.77-.73h13.08s1.11,0,1.11-1.11-1.11-1.11-1.11-1.11h-13.08c-.66,0-1.3-.26-1.77-.73s-.73-1.11-.73-1.77v-11.69c0-.66,.26-1.3,.73-1.77,.47-.47,1.11-.73,1.77-.73h13.15c.33,0,.65,.06,.96,.19,.3,.13,.58,.31,.81,.54l6.88,6.88c.23,.23,.42,.51,.54,.81,.13,.3,.19,.63,.19,.96Z\'/%3E%3C/svg%3E',
				'label'    => __( 'Performance Contributor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-credits-graduate',
				'source'   => 'wporg',
				'class'    => 'badge-credits-graduate',
				'iconType' => 'image',
				'icon'     => 'data:image/svg+xml,%3Csvg viewBox=\'0 0 153 101\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M124.71 0.313477C128.306 0.313593 131.781 1.67792 134.363 4.14648C136.95 6.61941 138.43 10.003 138.43 13.5605V80.334C138.43 80.4792 138.419 80.6221 138.398 80.7617H149.347C150.135 80.7618 150.892 81.0752 151.45 81.6328C152.008 82.1906 152.321 82.9476 152.321 83.7363V90.3408C152.321 93.3359 150.567 95.8424 148.164 97.4727C145.766 99.0994 142.683 99.9209 139.609 99.9209H13.1055C10.0319 99.9207 6.94885 99.0993 4.55078 97.4727C2.14786 95.8424 0.394531 93.3356 0.394531 90.3408V83.7363C0.394752 82.0937 1.7265 80.7619 3.36914 80.7617H14.3174C14.2973 80.6221 14.2861 80.4792 14.2861 80.334V13.5605C14.2863 10.003 15.766 6.61941 18.3525 4.14648C20.9346 1.67806 24.4094 0.313477 28.0059 0.313477H124.71ZM6.34375 90.3408C6.34375 90.849 6.64235 91.7029 7.89062 92.5498C9.14417 93.4001 11.015 93.9705 13.1055 93.9707H139.609C141.7 93.9707 143.571 93.4 144.824 92.5498C146.073 91.7028 146.372 90.8491 146.372 90.3408V86.7109H92.0605C91.427 88.0591 90.0578 88.993 88.4697 88.9932H64.2373C62.6491 88.9932 61.2791 88.0593 60.6455 86.7109H6.34375V90.3408ZM28.0059 6.2627C25.9032 6.2627 23.9118 7.06303 22.4639 8.44727C21.0206 9.82714 20.2355 11.6694 20.2354 13.5605V80.334C20.2354 80.4792 20.2242 80.6221 20.2041 80.7617H132.511C132.491 80.6221 132.479 80.4792 132.479 80.334V13.5605C132.479 11.6695 131.695 9.82712 130.252 8.44727C128.804 7.06304 126.813 6.26281 124.71 6.2627H28.0059ZM121.352 11.1504C125.185 11.1507 128.292 14.2578 128.292 18.0908V75.7383H24.4229V18.0908C24.4233 14.2578 27.5302 11.1506 31.3633 11.1504H121.352ZM78.0664 25.126C77.4697 23.3867 75.0099 23.3868 74.4131 25.126L73.9951 26.3447L70.6953 35.9668C70.4353 36.7247 69.7335 37.2436 68.9326 37.2705L57.7959 37.6445C56.0132 37.7045 55.2579 39.9422 56.6396 41.0703L65.5947 48.3818C66.1873 48.8657 66.4387 49.6541 66.2354 50.3916L63.1104 61.7207C62.6268 63.474 64.6144 64.8633 66.0947 63.8066L75.1172 57.3662C75.7885 56.8871 76.6901 56.8871 77.3613 57.3662L86.3848 63.8066C87.8651 64.8631 89.8517 63.474 89.3682 61.7207L86.2441 50.3916C86.0408 49.6541 86.2922 48.8657 86.8848 48.3818L95.8398 41.0703C97.2214 39.9421 96.4653 37.7044 94.6826 37.6445L83.5459 37.2705C82.7452 37.2436 82.0442 36.7246 81.7842 35.9668L78.0664 25.126Z\' fill=\'%23AE47F2\'%3E%3C/path%3E%3C/svg%3E',
				'label'    => __( 'Credits Graduate', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-credits-mentor',
				'source'   => 'wporg',
				'class'    => 'badge-credits-mentor',
				'iconType' => 'image',
				'icon'     => 'data:image/svg+xml,%3Csvg width=\'101\' height=\'101\' viewBox=\'0 0 101 101\' fill=\'none\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M95.4912 0.0292969C98.4759 0.0292969 100.895 2.449 100.896 5.43359V6.98633C100.896 6.99919 100.895 7.01255 100.895 7.02539V67.374C100.895 67.3873 100.896 67.4007 100.896 67.4141V68.25C100.895 71.2346 98.4759 73.6543 95.4912 73.6543H79.0479C76.3339 73.6544 74.3869 76.2701 75.165 78.8701L79.4951 93.3369C80.3217 96.098 78.7346 98.9404 75.9502 99.6846C73.166 100.428 70.2386 98.7931 69.4121 96.0322L62.8008 73.9482C62.7497 73.7777 62.597 73.6545 62.4189 73.6543H54.7842C54.6062 73.6546 54.4534 73.7777 54.4023 73.9482L47.791 96.0322C47.7268 96.2468 47.6481 96.4539 47.5596 96.6543C47.7056 95.9459 47.7842 95.2126 47.7842 94.4609V72.8584C47.7864 72.7949 47.7891 72.7308 47.7891 72.667V67.3867H88.5449C91.9025 67.3864 94.625 64.6643 94.625 61.3066V11.3145C94.6249 7.95676 91.9025 5.23467 88.5449 5.23438H41.8076C40.4665 3.15659 38.6984 1.38085 36.6279 0.0292969H95.4912ZM16.5586 45.2432L15.793 45.2695C14.7445 45.3049 14.2998 46.6216 15.1123 47.2852L16.5586 48.4658V61.3066C16.5586 64.6645 19.2809 67.3866 22.6387 67.3867H42.3848V72.667C42.3848 72.6692 42.3843 72.6723 42.3828 72.6738L42.3799 72.6797V94.4609C42.3799 96.4027 41.3549 98.1039 39.8174 99.0566C37.9573 97.8742 37.0369 95.5786 37.708 93.3369L42.0391 78.8701C42.8174 76.27 40.8703 73.6545 38.1562 73.6543H30.0107C29.2689 73.0417 28.3175 72.6739 27.2803 72.6738C26.2431 72.6738 25.2917 73.0417 24.5498 73.6543H17.25C14.9248 73.6541 12.943 72.1853 12.1807 70.125V65.5439C12.2698 65.3025 12.3739 65.0685 12.4951 64.8447C13.0315 63.8543 13.5723 62.8148 13.5723 61.6885V30.6006C13.726 30.5254 13.8829 30.4569 14.043 30.3965C14.8439 30.0939 15.6929 29.8932 16.5586 29.7549V45.2432ZM46.5625 76.0986L46.3887 76.3008C46.4638 76.2178 46.5341 76.1311 46.6035 76.0439C46.5893 76.0617 46.5769 76.081 46.5625 76.0986ZM46.8672 75.6885C46.8067 75.7785 46.7418 75.8656 46.6758 75.9521C46.7418 75.8656 46.8067 75.7785 46.8672 75.6885ZM39.6826 29.4385C40.0353 29.4385 40.372 29.5077 40.6807 29.6309C40.7581 29.6617 40.8373 29.6898 40.918 29.7109L41.1641 29.7793C41.737 29.95 42.2995 30.1906 42.8398 30.5049L53.7402 36.8457L53.8379 36.8975C54.3035 37.1195 54.8562 37.0556 55.2598 36.7344L55.3447 36.6621L60.0908 32.2031C62.2662 30.1596 65.6749 30.278 67.7041 32.4668C69.7329 34.6556 69.6145 38.0863 67.4395 40.1299L59.0137 48.0459C57.0628 49.8781 54.1203 49.972 52.0703 48.3936C52.0175 48.3529 51.9619 48.3147 51.9043 48.2812L44.415 43.9248C43.5145 43.4013 42.385 44.0511 42.3848 45.0928V61.9824H22.6387L22.502 61.9688C22.1944 61.9056 21.9629 61.6329 21.9629 61.3066V59.5801L25.877 56.7871C26.2717 56.5053 26.8015 56.5055 27.1963 56.7871L32.4395 60.5293C33.3101 61.1506 34.4784 60.3339 34.1943 59.3027L32.3789 52.7168C32.2594 52.2832 32.4065 51.8197 32.7549 51.5352L37.9609 47.2852C38.7737 46.6216 38.3289 45.3047 37.2803 45.2695L30.8076 45.0518C30.3367 45.0357 29.9243 44.7309 29.7715 44.2852L27.6113 37.9863C27.2603 36.9633 25.8129 36.9632 25.4619 37.9863L25.2266 38.6738L23.3018 44.2852C23.1489 44.731 22.7367 45.0359 22.2656 45.0518L21.9629 45.0615V29.4385H39.6826ZM88.6807 10.6523C88.9884 10.7154 89.2206 10.9881 89.2207 11.3145V61.3066L89.207 61.4434C89.1529 61.707 88.9444 61.9148 88.6807 61.9688L88.5449 61.9824H47.7891V52.1396L49.0068 52.8477C53.1133 55.8372 58.8744 55.5913 62.7139 51.9854V51.9844L71.1396 44.0684C75.4798 39.9906 75.7158 33.1602 71.668 28.793C67.6009 24.406 60.7525 24.1666 56.3906 28.2637L53.8447 30.6543L45.5576 25.833H45.5566C44.5747 25.2619 43.5452 24.8346 42.4961 24.541C42.2605 24.4537 42.0195 24.3764 41.7734 24.3105C43.5634 21.5585 44.6044 18.2745 44.6045 14.7471C44.6045 13.3322 44.436 11.9567 44.1201 10.6387H88.5449L88.6807 10.6523ZM38.4863 10.6387C38.9471 11.9222 39.2002 13.305 39.2002 14.7471C39.2001 21.4626 33.7565 26.9069 27.041 26.9072C25.2277 26.9072 23.5087 26.5066 21.9629 25.7949V11.3145C21.963 10.9882 22.1944 10.7155 22.502 10.6523L22.6387 10.6387H38.4863ZM27.041 2.58691C29.9037 2.58705 32.5331 3.57902 34.6104 5.23438H22.6387C19.2809 5.23446 16.5587 7.95667 16.5586 11.3145V20.9082C15.4941 19.101 14.8809 16.9961 14.8809 14.7471C14.8809 8.03128 20.3252 2.58691 27.041 2.58691Z\' fill=\'%238329E3\'/%3E%3Cpath d=\'M39.6836 29.4385C41.176 29.4385 42.3867 30.6482 42.3867 32.1406V72.6738H42.3857V94.4609C42.3857 97.4457 39.9662 99.8652 36.9814 99.8652C33.9969 99.865 31.5772 97.4456 31.5771 94.4609V76.9785C31.5769 74.6011 29.6499 72.6739 27.2725 72.6738C24.8949 72.6738 22.9671 74.6011 22.9668 76.9785V94.4619C22.9668 97.4467 20.5472 99.8661 17.5625 99.8662C14.5779 99.866 12.1582 97.4466 12.1582 94.4619V64.7373C12.1582 64.5269 12.1709 64.3194 12.1943 64.1152V63.6328C12.1943 62.8859 11.1617 62.6905 10.8887 63.3857C9.79743 66.1638 6.65534 67.5442 3.87109 66.4688C1.08709 65.3932 -0.285301 62.2692 0.805664 59.4912L11.1787 33.082C11.8797 31.2974 13.4269 30.0895 15.1816 29.7393C16.8394 29.4084 18.6113 29.4385 20.3018 29.4385H39.6836ZM27.6406 37.9834C27.2897 36.9601 25.8421 36.9601 25.4912 37.9834L25.2559 38.6709L23.3311 44.2822C23.1782 44.728 22.7659 45.0329 22.2949 45.0488L15.8223 45.2666C14.7736 45.3018 14.3289 46.6187 15.1416 47.2822L20.3477 51.5322C20.6961 51.8168 20.8432 52.2802 20.7236 52.7139L18.9082 59.2998C18.624 60.331 19.7924 61.1478 20.6631 60.5264L25.9062 56.7842C26.3011 56.5024 26.8308 56.5024 27.2256 56.7842L32.4688 60.5264C33.3394 61.1476 34.5078 60.3309 34.2236 59.2998L32.4072 52.7139C32.2878 52.2802 32.4357 51.8167 32.7842 51.5322L37.9902 47.2822C38.803 46.6187 38.3582 45.3018 37.3096 45.2666L30.8369 45.0488C30.3659 45.033 29.9537 44.7281 29.8008 44.2822L27.6406 37.9834ZM27.0439 2.58691C33.7597 2.58698 39.2041 8.03132 39.2041 14.7471C39.204 21.4627 33.7596 26.9072 27.0439 26.9072C20.3284 26.907 14.8839 21.4627 14.8838 14.7471C14.8838 8.0314 20.3283 2.58711 27.0439 2.58691Z\' fill=\'%238329E3\'/%3E%3C/svg%3E',
				'label'    => __( 'Credits Mentor', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
			array(
				'id'       => 'badge-campus-connect-participant',
				'source'   => 'wporg',
				'class'    => 'badge-campus-connect-participant',
				'iconType' => 'image',
				'icon'     => 'data:image/svg+xml,%3Csvg viewBox=\'0 0 94 101\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M21.7881 28.6436C21.832 28.6569 21.8945 28.6761 21.9736 28.7002C22.1449 28.7524 22.3962 28.8285 22.7158 28.9258C23.2606 29.0915 24.0053 29.3173 24.8936 29.5869C15.8132 36.3177 9.92292 47.11 9.92285 59.2432C9.92288 79.6104 26.4591 96.1403 46.834 96.1406C67.1722 96.1406 83.7461 79.6106 83.7461 59.2432C83.746 47.1547 77.8856 36.3979 68.8594 29.6621C69.8297 29.3687 70.6403 29.1239 71.2227 28.9473C71.5424 28.8503 71.7935 28.7737 71.9648 28.7217C72.0504 28.6957 72.1168 28.6756 72.1611 28.6621C72.183 28.6555 72.1997 28.6499 72.2109 28.6465C72.212 28.6461 72.2129 28.6449 72.2139 28.6445L72.2148 28.6455L73.3379 28.3076C82.0583 35.7825 87.585 46.8774 87.585 59.2637C87.5847 81.7726 69.3371 100.02 46.8281 100.02C24.3193 100.019 6.07253 81.7724 6.07227 59.2637C6.07227 46.8547 11.619 35.7419 20.3672 28.2666L21.7881 28.6436ZM56.8369 90.3848C53.6627 91.4547 50.3404 92.0449 46.834 92.0449C43.9183 92.0449 41.0392 91.6398 38.3818 90.8281C41.3716 82.0466 44.3616 73.3385 47.3145 64.6309L56.8369 90.3848ZM16.6777 45.9971C21.9192 60.3502 27.1601 74.7406 32.4385 89.0938C21.4391 83.7804 14.0205 72.2679 14.0205 59.2432C14.0205 54.4466 14.8691 50.0926 16.6777 45.9971ZM75.7734 43.5615C78.3572 48.3213 79.6484 53.561 79.6484 59.2432C79.6484 71.3824 73.2626 82.267 63.4072 87.9492C66.9876 78.3929 70.5683 68.7626 74.1855 59.2432C75.0714 56.8817 76.9177 52.343 75.7734 43.5615ZM33.4834 32.1787C36.4511 33.068 39.4564 33.9612 41.7764 34.6328C42.9334 34.9678 43.9373 35.2529 44.6865 35.4561C45.0577 35.5567 45.3925 35.6441 45.6621 35.709C45.794 35.7407 45.9419 35.7744 46.0859 35.8027C46.1159 35.8086 46.5124 35.8945 46.9619 35.8955H46.9707C47.4225 35.8955 47.8214 35.8097 47.8516 35.8037C47.9956 35.7755 48.1436 35.7416 48.2754 35.71C48.5447 35.6454 48.8792 35.5582 49.25 35.458C49.9989 35.2556 51.0031 34.9717 52.1602 34.6377C54.4798 33.9681 57.4849 33.0773 60.4531 32.1904C61.6278 31.8395 62.7971 31.4845 63.9189 31.1475C65.7953 32.2784 67.5527 33.5936 69.166 35.0752C66.656 34.6694 63.0752 36.5145 63.0752 40.9053C63.0752 43.6356 64.7365 45.9231 66.3975 48.6533C67.6894 50.9041 68.4277 53.6718 68.4277 57.7305C68.4277 63.2282 63.2598 76.1797 63.2598 76.1797L52.0762 45.2959C54.0547 45.2226 55.0877 44.6769 55.1025 44.6689C56.9481 44.4845 56.7264 40.0563 54.8809 40.167C54.8346 40.1708 49.5502 40.6104 46.0957 40.6104C42.8973 40.6103 37.5394 40.1705 37.4961 40.167C35.6506 40.0565 35.429 44.595 37.2744 44.6689L40.6699 44.9639L45.3213 57.5459L38.6396 79.0576L26.0537 45.2959C28.0757 45.2224 30.354 45.0027 30.3721 45.001C32.2175 44.7794 31.9959 40.8315 30.1504 40.9053C30.1185 40.9077 24.788 41.3115 21.4023 41.3115C20.738 41.3115 20.0367 41.3113 19.2617 41.2744C21.9798 37.1137 25.6059 33.6312 29.8604 31.0859C31.0298 31.4384 32.2538 31.8103 33.4834 32.1787ZM46.9551 0.0195312C48.959 0.0212104 90.586 12.9767 90.6797 13.0059V13.0264C91.3865 13.2384 92.0018 13.6453 92.4375 14.1895C92.8731 14.7336 93.1074 15.3878 93.1074 16.0586C93.1073 16.7292 92.8729 17.3827 92.4375 17.9268C92.0018 18.4709 91.3865 18.8778 90.6797 19.0898L71.1064 24.9629C71.1064 24.9629 70.5318 25.1362 69.5605 25.4307L69.5615 25.4316C69.4097 25.4777 69.2477 25.5255 69.0771 25.5771C68.2296 25.8339 67.1588 26.1571 65.9482 26.5225C65.5201 26.6515 65.0746 26.7864 64.6152 26.9248L64.6133 26.9238C62.8254 27.4625 60.8285 28.064 58.8379 28.6582L58.8389 28.6592C58.5391 28.7487 58.2391 28.8368 57.9404 28.9258C55.4411 29.6704 53.0044 30.3907 51.0625 30.9512C50.7654 31.0369 50.4794 31.1175 50.207 31.1953C49.4191 31.4204 48.7415 31.6117 48.2109 31.7549C47.8015 31.8654 47.4798 31.9468 47.2637 31.9961C47.1678 32.018 47.0924 32.0326 47.0391 32.041C47.0087 32.0457 46.9861 32.0508 46.9707 32.0508L46.9688 32.0498L46.9678 32.0508C46.1302 32.0493 22.8291 24.9424 22.8291 24.9424L5.97949 20.4727V35.9258C5.97949 36.7734 5.40525 37.8615 4.93945 38.1865C4.47263 38.5112 2.35186 38.5748 1.86914 38.1865C1.38676 37.7978 0.829102 36.7734 0.829102 35.9258V16.0381C0.829091 15.3675 1.06362 14.714 1.49902 14.1699C1.93456 13.6257 2.54916 13.218 3.25586 13.0059C3.34531 12.978 44.9561 0.0195312 46.9551 0.0195312Z\' fill=\'%233858E9\'%3E%3C/path%3E%3C/svg%3E',
				'label'    => __( 'Campus Connect Participant', 'wp-plugin-info-card' ),
				'custom'   => false,
			),
		);

		// Sort badges alphabetically by label.
		usort(
			$badges,
			function ( $a, $b ) {
				return strcmp( $a['label'], $b['label'] );
			}
		);

		return $badges;
	}

	/**
	 * Look up badge data by class name.
	 *
	 * @param string $badge_class Badge class name to look up.
	 * @return array|null Badge data array or null if not found.
	 */
	public static function lookup_badge( $badge_class ) {
		$badges = self::get_badge_data();
		// Split the input badge class to get the base class (first part).
		$input_classes = explode( ' ', trim( $badge_class ) );
		$base_class    = ! empty( $input_classes ) ? end( $input_classes ) : $badge_class;

		foreach ( $badges as $badge ) {
			// Check if the badge class matches (handle classes with spaces).
			$badge_classes = explode( ' ', trim( $badge['class'] ) );
			$badge_base    = ! empty( $badge_classes[0] ) ? $badge_classes[0] : $badge['class'];

			// Match if the base class matches, or if the full badge_class is in the badge's classes.
			if ( $base_class === $badge_base || in_array( $badge_class, $badge_classes, true ) ) {
				return $badge;
			}
		}
		return null;
	}

	/**
	 * Validate badge class names.
	 *
	 * @param array $badge_classes Array of badge class names to validate.
	 * @return array Array of valid badge class names.
	 */
	public static function validate_badges( $badge_classes ) {
		$valid_badges = array();
		foreach ( $badge_classes as $badge_class ) {
			// Handle both string and array formats.
			$class = is_string( $badge_class ) ? $badge_class : ( $badge_class['class'] ?? '' );
			if ( ! empty( $class ) && self::lookup_badge( $class ) ) {
				$valid_badges[] = $class;
			}
		}
		return $valid_badges;
	}

	/**
	 * Build wrapper classes for profile badges.
	 *
	 * @param array $args {
	 *     Array of sanitized arguments.
	 *
	 *     @type string $align       Alignment: left|center|right.
	 *     @type string $layout      Layout: horizontal|centered.
	 *     @type int    $cols        Number of columns.
	 *     @type bool   $hideHeading Whether to hide badge headings.
	 * }
	 * @return array Array of wrapper class names.
	 */
	public static function build_profile_badges_wrapper_classes( $args ) {
		$badge_layout = $args['badgeLayout'] ?? 'grid';
		$is_grid      = 'grid' === $badge_layout;
		$is_flex      = 'flex' === $badge_layout;

		$wrapper_classes = array(
			$is_grid ? 'wppic-badges-grid' : 'wppic-badges-flex',
			'align' . $args['align'],
			'layout-' . $args['layout'],
		);

		if ( $is_grid ) {
			$wrapper_classes[] = 'is-grid';
			$wrapper_classes[] = 'cols-' . $args['cols'];
		} elseif ( $is_flex ) {
			$wrapper_classes[] = 'is-flex';
		}

		if ( $args['hideHeading'] ) {
			$wrapper_classes[] = 'has-no-title';
		}

		return $wrapper_classes;
	}

	/**
	 * Normalize and sanitize profile badges arguments.
	 *
	 * @param array $args Raw arguments.
	 * @return array Normalized and sanitized arguments.
	 */
	public static function normalize_profile_badges_args( $args = array() ) {
		// Normalize arguments with defaults.
		$defaults = array(
			'uniqueId'     => '',
			'anchor'       => '',
			'align'        => 'center',
			'type'         => 'static',
			'authorSlug'   => '',
			'baseSize'     => 16,
			'badges'       => array(),
			'lastUpdated'  => '',
			'colGap'       => 20,
			'rowGap'       => 20,
			'cols'         => 2,
			'layout'       => 'horizontal',
			'badgeLayout'  => 'grid',
			'hideHeading'  => false,
			'headingColor' => '#000000',
		);

		$args = wp_parse_args( $args, $defaults );

		// Sanitize inputs.
		$badge_layout = sanitize_text_field( $args['badgeLayout'] ?? 'grid' );
		// Validate badgeLayout: only allow 'grid' or 'flex'.
		if ( ! in_array( $badge_layout, array( 'grid', 'flex' ), true ) ) {
			$badge_layout = 'grid';
		}

		return array(
			'uniqueId'     => sanitize_text_field( $args['uniqueId'] ),
			'anchor'       => sanitize_text_field( $args['anchor'] ),
			'align'        => sanitize_text_field( $args['align'] ),
			'type'         => sanitize_text_field( $args['type'] ),
			'authorSlug'   => sanitize_text_field( $args['authorSlug'] ),
			'baseSize'     => absint( $args['baseSize'] ),
			'badges'       => is_array( $args['badges'] ) ? $args['badges'] : array(),
			'lastUpdated'  => sanitize_text_field( $args['lastUpdated'] ),
			'colGap'       => absint( $args['colGap'] ),
			'rowGap'       => absint( $args['rowGap'] ),
			'cols'         => absint( $args['cols'] ),
			'layout'       => sanitize_text_field( $args['layout'] ),
			'badgeLayout'  => $badge_layout,
			'hideHeading'  => filter_var( $args['hideHeading'], FILTER_VALIDATE_BOOLEAN ),
			'headingColor' => sanitize_hex_color( $args['headingColor'] ),
		);
	}

	/**
	 * Render profile badges (shared helper for block and shortcode).
	 *
	 * @param array $args {
	 *     Array of arguments.
	 *
	 *     @type string  $uniqueId          Unique identifier for the wrapper.
	 *     @type string  $anchor            Anchor ID (overrides uniqueId if set).
	 *     @type string  $align             Alignment: left|center|right.
	 *     @type string  $type              Badge type: static|dynamic.
	 *     @type string  $authorSlug        WordPress.org author slug (for dynamic).
	 *     @type int     $baseSize          Base font size in pixels.
	 *     @type array   $badges            Array of badge class names (for static).
	 *     @type string  $lastUpdated       Last update timestamp (for dynamic).
	 *     @type int     $colGap            Column gap in pixels.
	 *     @type int     $rowGap            Row gap in pixels.
	 *     @type int     $cols              Number of columns.
	 *     @type string  $layout            Layout: horizontal|centered.
	 *     @type bool    $hideHeading       Whether to hide badge headings.
	 *     @type string  $headingColor      Heading text color.
	 *     @type string  $wrapperAttributes Optional. Pre-built wrapper attributes (for blocks).
	 * }
	 * @return string Rendered HTML.
	 */
	/**
	 * Render a list of badges.
	 *
	 * @param array $badge_classes Array of badge class names.
	 * @param bool  $hide_heading  Whether to hide badge headings.
	 * @return string Rendered badge HTML.
	 */
	private static function render_badge_list( $badge_classes, $hide_heading = false ) {
		$content = '';
		if ( empty( $badge_classes ) ) {
			return $content;
		}

		// Validate badges.
		$valid_badges = self::validate_badges( $badge_classes );
		foreach ( $valid_badges as $badge_class ) {
			$badge_data = self::lookup_badge( $badge_class );
			if ( ! $badge_data ) {
				continue;
			}

			// Build badge HTML.
			$badge_html = '<div class="wppic-profile-badge">';

			// Add heading if not hidden.
			if ( ! $hide_heading ) {
				$badge_html .= '<h3 class="wppic-profile-badge-title">' . esc_html( $badge_data['label'] ) . '</h3>';
			}

			// Build badge icon/icon container.
			// Use the user's badge_class to preserve any modifiers (e.g., "has-overlay").
			// Add data-label to badge container only when headings are hidden (for tooltips).
			$badge_container_attrs = 'class="badge ' . esc_attr( $badge_class ) . '"';
			if ( $hide_heading ) {
				$badge_container_attrs .= ' data-label="' . esc_attr( $badge_data['label'] ) . '"';
			}
			$badge_html .= '<div ' . $badge_container_attrs . '>';

			// Handle icon type.
			if ( 'image' === $badge_data['iconType'] ) {
				// Image icon - alt text already provides accessibility.
				// Use esc_attr() for data URIs (SVG data URIs) instead of esc_url() to preserve special characters.
				$badge_html .= '<img src="' . esc_attr( $badge_data['icon'] ) . '" alt="' . esc_attr( $badge_data['label'] ) . '" aria-label="' . esc_attr( $badge_data['label'] ) . '" />';
			} else {
				// Dashicon - include badge class from badge data plus icon class.
				// Add accessibility attributes: role="img" and aria-label for screen readers.
				$badge_html .= '<span class="dashicons ' . esc_attr( $badge_data['class'] ) . ' ' . esc_attr( $badge_data['icon'] ) . '" role="img" data-label="' . esc_attr( $badge_data['label'] ) . '" aria-label="' . esc_attr( $badge_data['label'] ) . '"></span>';
			}

			$badge_html .= '</div>'; // End .badge.
			$badge_html .= '</div>'; // End .wppic-profile-badge.

			$content .= $badge_html;
		}

		return $content;
	}

	/**
	 * Render loading skeleton for profile badges.
	 *
	 * @param array  $args       Normalized badge arguments.
	 * @param string $author_slug Author slug for data attribute.
	 * @param string $token      One-time use token for data attribute.
	 * @param string $unique_id  Unique ID for data attribute.
	 * @return string Loading skeleton HTML.
	 */
	private static function render_loading_skeleton( $args, $author_slug = '', $token = '', $unique_id = '' ) {
		// Always show 6 skeleton badges for consistent, realistic appearance.
		$skeleton_count = 6;

		// Build classes to match the wrapper (including column classes).
		$badge_layout = $args['badgeLayout'] ?? 'grid';
		$is_grid      = 'grid' === $badge_layout;
		$is_flex      = 'flex' === $badge_layout;
		$cols         = absint( $args['cols'] ?? 2 );
		$align        = sanitize_text_field( $args['align'] ?? 'center' );

		if ( $is_grid ) {
			$skeleton_classes = array(
				'wppic-profile-badges-loading',
				'align' . $align,
				'cols-' . $cols,
			);
		} elseif ( $is_flex ) {
			$skeleton_classes = array(
				'wppic-badges-flex',
				'wppic-profile-badges-loading',
				'is-flex',
				'align' . $align,
			);
		} else {
			// Fallback to grid.
			$skeleton_classes = array(
				'wppic-profile-badges-loading',
				'align' . $align,
				'cols-' . $cols,
			);
		}

		$data_attrs = array(
			'class'           => implode( ' ', $skeleton_classes ),
			'aria-busy'       => 'true',
			'aria-label'      => esc_attr__( 'Loading badges...', 'wp-plugin-info-card' ),
			'data-is-loading' => 'true',
		);

		if ( ! empty( $author_slug ) ) {
			$data_attrs['data-author-slug'] = esc_attr( $author_slug );
		}
		if ( ! empty( $token ) ) {
			$data_attrs['data-token'] = esc_attr( $token );
		}
		if ( ! empty( $unique_id ) ) {
			$data_attrs['data-unique-id'] = esc_attr( $unique_id );
		}

		$attrs_string = '';
		foreach ( $data_attrs as $key => $value ) {
			$attrs_string .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		$content = '<div' . $attrs_string . '>';
		for ( $i = 0; $i < $skeleton_count; $i++ ) {
			$content .= '<div class="wppic-profile-badge-skeleton"></div>';
		}
		$content .= '</div>';

		return $content;
	}

	/**
	 * Enqueue lazy load script for profile badges.
	 *
	 * @param array $args Normalized badge arguments.
	 * @return void
	 */
	private static function enqueue_profile_badges_lazy_load( $args ) {
		// Only enqueue once per page load.
		static $enqueued = false;
		if ( $enqueued ) {
			return;
		}
		$enqueued = true;

		// Register and enqueue script.
		$script_url = Functions::get_plugin_url( 'dist/profile-badges-lazy-load.js' );
		$script_ver = Functions::get_plugin_version();

		wp_enqueue_script(
			'wppic-profile-badges-lazy-load',
			$script_url,
			array( 'wp-api-fetch' ),
			$script_ver,
			true
		);

		// Localize script with REST API URL and nonce.
		wp_localize_script(
			'wppic-profile-badges-lazy-load',
			'wppicProfileBadgesLazyLoad',
			array(
				'restUrl'   => Functions::get_rest_url( 'wppic/v2/get_profile_badges_html' ),
				'restNonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	public static function render_profile_badges( $args = array() ) {
		// Normalize and sanitize all arguments (skip if already normalized).
		if ( ! empty( $args['_already_normalized'] ) ) {
			unset( $args['_already_normalized'] );
			$sanitized = $args;
		} else {
			$sanitized = self::normalize_profile_badges_args( $args );
		}

		// Extract sanitized values for easier use.
		$unique_id     = $sanitized['uniqueId'];
		$anchor        = $sanitized['anchor'];
		$align         = $sanitized['align'];
		$type          = $sanitized['type'];
		$author_slug   = $sanitized['authorSlug'];
		$base_size     = $sanitized['baseSize'];
		$badges        = $sanitized['badges'];
		$last_updated  = $sanitized['lastUpdated'];
		$col_gap       = $sanitized['colGap'];
		$row_gap       = $sanitized['rowGap'];
		$cols          = $sanitized['cols'];
		$layout        = $sanitized['layout'];
		$badge_layout  = $sanitized['badgeLayout'];
		$hide_heading  = $sanitized['hideHeading'];
		$heading_color = $sanitized['headingColor'];

		// Generate unique ID if not provided.
		if ( empty( $unique_id ) ) {
			$unique_id = 'wppic-badges-' . uniqid();
		}

		// Use anchor if provided, otherwise use uniqueId.
		$wrapper_id = ! empty( $anchor ) ? $anchor : $unique_id;

		// Build wrapper classes.
		$wrapper_classes = self::build_profile_badges_wrapper_classes( $sanitized );

		// Check if wrapper attributes were provided (from block context).
		$wrapper_attributes = isset( $args['wrapperAttributes'] ) ? $args['wrapperAttributes'] : null;

		// Extract ID from wrapper attributes if provided (for CSS selector).
		$css_wrapper_id = $wrapper_id;
		if ( ! empty( $wrapper_attributes ) ) {
			// Extract id from wrapper attributes string (format: id="value" or id='value').
			if ( preg_match( '/id=["\']([^"\']+)["\']/', $wrapper_attributes, $matches ) ) {
				$css_wrapper_id = $matches[1];
			}
		}

		// Generate inline CSS variables (shared for both grid and flex layouts).
		// Set CSS variables directly on the element for better specificity and reliability.
		$inline_styles = sprintf(
			'--wppic-grid-row-gap: %1$spx; --wppic-grid-col-gap: %2$spx; --wppic-base-size: %3$spx; --wppic-heading-color: %4$s;',
			esc_attr( $row_gap ),
			esc_attr( $col_gap ),
			esc_attr( $base_size ),
			esc_attr( $heading_color )
		);

		// Build content based on type.
		$content = '';
		if ( 'dynamic' === $type ) {
			// Dynamic badges: check cache first, then show skeleton if needed.
			if ( ! empty( $author_slug ) ) {
				// Layer 1: Check transient cache (72 hours) - fastest access.
				$cached_data = get_transient( 'wppic_profile_' . sanitize_key( $author_slug ) );

				// Layer 2: If transient expired, check post type cache (up to 2 weeks).
				if ( false === $cached_data ) {
					$post = get_page_by_path( $author_slug, OBJECT, array( 'wppic_profiles' ) );
					if ( $post ) {
						$last_updated = get_post_meta( $post->ID, '_wppic_last_updated', true );
						$post_data    = get_post_meta( $post->ID, '_wppic_profile_data', true );

						// If post data exists and is less than 2 weeks old.
						if ( $last_updated && ( time() - $last_updated ) < ( 14 * DAY_IN_SECONDS ) && ! empty( $post_data ) ) {
							// Sanitize post data before using.
							$cached_data = Functions::sanitize_profile_data( $post_data );
							// Refresh transient with post data (72-hour expiration).
							set_transient( 'wppic_profile_' . sanitize_key( $author_slug ), $cached_data, 72 * HOUR_IN_SECONDS );
						}
					}
				}

				if ( false !== $cached_data && ( ! empty( $cached_data['badges'] ) || ! empty( $cached_data['member_badges'] ) ) ) {
					// Render badges from cache immediately.
					$cached_badges = $cached_data['badges'] ?? $cached_data['member_badges'] ?? array();
					$content       = self::render_badge_list( $cached_badges, $hide_heading );
				} else {
					// No cache: show loading skeleton and generate token for lazy loading.
					// Generate one-time use token.
					$token = wp_generate_password( 32, false );

					// Create REST API nonce for additional security.
					$nonce = wp_create_nonce( 'wppic_badge_load_' . $author_slug );

					// Store token with author slug, nonce, hideHeading, and attempt counter.
					$token_data = array(
						'author_slug' => $author_slug,
						'nonce'       => $nonce,
						'hideHeading' => $hide_heading,
						'attempts'    => 0,
						'created'     => time(),
					);

					// Store in transient with 10-minute expiration.
					set_transient( 'wppic_badge_token_' . $token, $token_data, 10 * MINUTE_IN_SECONDS );

					// Render loading skeleton with data attributes.
					$content = self::render_loading_skeleton( $sanitized, $author_slug, $token, $unique_id );

					// Enqueue lazy load script.
					self::enqueue_profile_badges_lazy_load( $sanitized );
				}
			} elseif ( current_user_can( 'manage_options' ) ) {
				$content = '<div class="wppic-badges-content">' . esc_html__( 'No author slug provided for dynamic badges. Please provide an author slug to display badges.', 'wp-plugin-info-card' ) . '</div>';
			} else {
				// Show nada since user has no privs to see the content.
				$content = '';
			}
		} else {
			// Render static badges.
			$content = '';
			if ( ! empty( $badges ) ) {
				// Validate badges.
				$valid_badges = self::validate_badges( $badges );
				$content      = self::render_badge_list( $valid_badges, $hide_heading );
			}
		}

		// Build HTML output.
		// Since badge data comes from trusted source (hardcoded array) and all attributes
		// are already escaped with esc_attr()/esc_html(), we can output directly.
		// wp_kses_post() would strip data URIs, so we bypass it for trusted badge content.
		ob_start();
		?>
		<?php if ( ! empty( $wrapper_attributes ) ) : ?>
			<?php
				// Generate CSS variables.
				$grid_styles = sprintf(
					'#%1$s.wppic-badges-grid,' . PHP_EOL .
					'#%1$s.wppic-badges-flex {' . PHP_EOL .
					'	--wppic-grid-row-gap: %2$spx;' . PHP_EOL .
					'	--wppic-grid-col-gap: %3$spx;' . PHP_EOL .
					'	--wppic-base-size: %4$spx;' . PHP_EOL .
					'	--wppic-heading-color: %5$s;' . PHP_EOL .
					'}',
					esc_attr( $css_wrapper_id ),
					esc_attr( $row_gap ),
					esc_attr( $col_gap ),
					esc_attr( $base_size ),
					esc_attr( $heading_color )
				);
			?>
			<div id="<?php echo esc_attr( $css_wrapper_id ); ?>" <?php echo wp_kses_post( $wrapper_attributes ); ?>>
				<style><?php echo esc_html( $grid_styles ); ?></style>

				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Badge data is trusted, attributes already escaped. ?>
			</div>
		<?php else : ?>
			<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" id="<?php echo esc_attr( $wrapper_id ); ?>" style="<?php echo esc_attr( $inline_styles ); ?>">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Badge data is trusted, attributes already escaped. ?>
			</div>
		<?php endif; ?>
		<?php
		return ob_get_clean();
	}

	/**
	 * Shortcode handler for profile badges.
	 *
	 * @param array $atts    Shortcode attributes.
	 * @return string Rendered HTML.
	 */
	public static function shortcode_profile_badges( $atts ) {
		// Default attributes matching block.json defaults.
		$defaults = array(
			'unique_id'     => '',
			'anchor'        => '',
			'align'         => 'center',
			'type'          => 'dynamic',
			'author_slug'   => '',
			'base_size'     => 16,
			'badges'        => '',
			'last_updated'  => '',
			'col_gap'       => 20,
			'row_gap'       => 20,
			'cols'          => 2,
			'layout'        => 'horizontal',
			'badge_layout'  => 'grid',
			'hide_heading'  => false,
			'heading_color' => '#000000',
		);

		$atts = wp_parse_args( $atts, $defaults );

		// Parse badges from comma-separated string to array first (before determining type).
		$badges_string = sanitize_text_field( $atts['badges'] );
		$has_badges    = ! empty( $badges_string );
		$has_author    = ! empty( $atts['author_slug'] );

		// Sanitize and normalize type.
		$type = strtolower( trim( $atts['type'] ) );

		// Update the type in atts.
		$atts['type'] = $type;

		// Convert shortcode attribute names (snake_case) to block attribute names (camelCase).
		$block_args                = array();
		$block_args['uniqueId']    = sanitize_text_field( $atts['unique_id'] );
		$block_args['anchor']      = sanitize_text_field( $atts['anchor'] );
		$block_args['align']       = sanitize_text_field( $atts['align'] );
		$block_args['type']        = sanitize_text_field( $atts['type'] );
		$block_args['authorSlug']  = sanitize_text_field( $atts['author_slug'] );
		$block_args['baseSize']    = absint( $atts['base_size'] );
		$block_args['lastUpdated'] = sanitize_text_field( $atts['last_updated'] );
		$block_args['colGap']      = absint( $atts['col_gap'] );
		$block_args['rowGap']      = absint( $atts['row_gap'] );
		$block_args['cols']        = absint( $atts['cols'] );
		$block_args['layout']      = sanitize_text_field( $atts['layout'] );
		// Validate badge_layout: only allow 'grid' or 'flex'.
		$badge_layout = sanitize_text_field( $atts['badge_layout'] );
		if ( ! in_array( $badge_layout, array( 'grid', 'flex' ), true ) ) {
			$badge_layout = 'grid';
		}
		$block_args['badgeLayout']  = $badge_layout;
		$block_args['hideHeading']  = filter_var( $atts['hide_heading'], FILTER_VALIDATE_BOOLEAN );
		$block_args['headingColor'] = sanitize_hex_color( $atts['heading_color'] );

		// Parse badges from comma-separated string to array.
		// Handle badges that may have spaces (e.g., "badge-accessibility has-overlay").
		if ( $has_badges ) {
			// Split by comma, but preserve spaces within each badge class string.
			$badges_array         = explode( ',', $badges_string );
			$badges_array         = array_map( 'trim', $badges_array );
			$badges_array         = array_filter( $badges_array );
			$block_args['badges'] = array_values( $badges_array );
		} else {
			$block_args['badges'] = array();
		}

		// Call shared helper.
		return self::render_profile_badges( $block_args );
	}
}
