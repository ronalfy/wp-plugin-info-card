<?php
/**
 * Set up add plugin functionality.
 *
 * @package WPPIC
 */

namespace MediaRon\WPPIC\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MediaRon\WPPIC\Functions;

/**
 * Init settings class for WPPIC.
 */
class Settings {
	/**
	 * Registers and outputs placeholder for settings.
	 *
	 * @since 1.0.0
	 */
	public static function settings_page() {
		self::get_settings_header();
		?>
		<div class="wppic-settings-wrapper">
			<?php
			self::get_tab_content();
			?>
		</div>
		<?php
		self::get_admin_header();
		self::get_settings_footer();
		?>
		<?php
	}

	/**
	 * Get the content for the selected tab.
	 */
	public static function get_tab_content() {
		$current_tab = Functions::get_admin_tab();
		if ( empty( $current_tab ) ) {
			$current_tab = 'home';
		}
		/**
		 * Output the tab's content via hook.
		 *
		 * @since 4.1.0
		 *
		 * @param string $current_tab The current tab.
		 * @param string $current_sub_tab The current sub tab.
		 */
		do_action( 'wppic_output_' . $current_tab, $current_tab, '' );
	}

	/**
	 * Output Generic Settings Place holder for React goodness.
	 */
	public static function get_settings_header() {
		$user      = wp_get_current_user();
		$user_id   = $user->ID;
		$firstname = get_user_meta( $user_id, 'first_name', true );
		$lastname  = get_user_meta( $user_id, 'last_name', true );
		$website   = home_url();

		$support_url = add_query_arg(
			array(
				'firstname' => $firstname,
				'lastname'  => $lastname,
				'site'      => $website,
				'email'     => $user->data->user_email,
				'license'   => '',
				'product'   => 'WP Plugin Info Card',
				'subject'   => 'WP Plugin Info Card Help',
				'support'   => 'Yes',
			),
			'https://docs.mediaron.com/product/wp-plugin-info-card'
		);
		?>
		<header id="wppic-admin-header">
			<div id="wppic-admin-header-content">
				<div class="wppic-admin-header-logo">
					<h1>
						<a href="<?php echo esc_url( Functions::get_settings_url() ); ?>" class="wppic-admin-logo"><img src="<?php echo esc_url( Functions::get_plugin_url( 'assets/img/wppic-horizontal.png' ) ); ?>" alt="WP Plugin Info Card" class="desktop-logo" /><img src="<?php echo esc_url( Functions::get_plugin_url( 'assets/img/wppic.png' ) ); ?>" alt="WP Plugin Info Card" aria-hidden="true" class="mobile-logo" width="125" heigh="125" /></a>
					</h1>
				</div>
				<div class="wppic-admin-header-tabs">
					<?php self::get_settings_tabs(); ?>
				</div>
			</div>
		</header>
		<?php
	}

	/**
	 * Get the admin footer.
	 */
	public static function get_admin_header() {
		// Make sure we enqueue on the right admin screen.
		$screen = get_current_screen();
		if ( ! isset( $screen->id ) ) {
			return;
		}
		if ( isset( $screen->base ) && 'settings_page_wp-plugin-info-card' !== $screen->base ) {
			return;
		}
		?>
		<svg width="0" height="0" class="hidden">
			<symbol aria-hidden="true" data-prefix="fas" data-icon="home-heart" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" id="home-heart">
				<g class="fa-group"><path class="fa-secondary" fill="currentColor" d="M64.11 311.38V496a16.05 16.05 0 0 0 16 16h416a16.05 16.05 0 0 0 16-16V311.38c-6.7-5.5-44.7-38.31-224-196.4-180.11 158.9-217.6 191.09-224 196.4zm314.1-26.31a60.6 60.6 0 0 1 4.5 89.11L298 459.77a13.94 13.94 0 0 1-19.8 0l-84.7-85.59a60.66 60.66 0 0 1 4.3-89.11c24-20 59.7-16.39 81.6 5.81l8.6 8.69 8.6-8.69c22.01-22.2 57.71-25.81 81.61-5.81z" opacity="0.4"></path><path class="fa-primary" fill="currentColor" d="M378.21 285.07c-23.9-20-59.6-16.39-81.6 5.81l-8.6 8.69-8.6-8.69c-21.9-22.2-57.6-25.81-81.6-5.81a60.66 60.66 0 0 0-4.3 89.11l84.7 85.59a13.94 13.94 0 0 0 19.8 0l84.7-85.59a60.6 60.6 0 0 0-4.5-89.11zm192.6-48.8l-58.7-51.79V48a16 16 0 0 0-16-16h-64a16 16 0 0 0-16 16v51.7l-101.3-89.43a40 40 0 0 0-53.5 0l-256 226a16 16 0 0 0-1.2 22.61l21.4 23.8a16 16 0 0 0 22.6 1.2l229.4-202.2a16.12 16.12 0 0 1 21.2 0L528 284a16 16 0 0 0 22.6-1.21L572 259a16.11 16.11 0 0 0-1.19-22.73z"></path></g>
			</symbol>
			<symbol aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" id="file-code">
				<path fill="currentColor" class="fa-primary" d="M384 160L224 0V128c0 17.7 14.3 32 32 32H384zM153 289c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0L71 303c-9.4 9.4-9.4 24.6 0 33.9l48 48c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-31-31 31-31zM265 255c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l31 31-31 31c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l48-48c9.4-9.4 9.4-24.6 0-33.9l-48-48z"/><path fill="currentColor" class="fa-secondary" d="M0 64C0 28.7 28.7 0 64 0H224V128c0 17.7 14.3 32 32 32H384V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V64zM153 289c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0L71 303c-9.4 9.4-9.4 24.6 0 33.9l48 48c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-31-31 31-31zM265 255c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l31 31-31 31c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l48-48c9.4-9.4 9.4-24.6 0-33.9l-48-48z" opacity="0.4"/>
			</symbol>
			<symbol aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" id="brush">
				<path fill="currentColor" class="fa-primary" d="M0 288H384v32c0 35.3-28.7 64-64 64H256v64c0 35.3-28.7 64-64 64s-64-28.7-64-64V384H64c-35.3 0-64-28.7-64-64V288zM192 464a16 16 0 1 0 0-32 16 16 0 1 0 0 32z"/><path fill="currentColor" class="fa-secondary" d="M162.4 6c-1.5-3.6-5-6-8.9-6h-19c-3.9 0-7.5 2.4-8.9 6L104.9 57.7c-3.2 8-14.6 8-17.8 0L66.4 6c-1.5-3.6-5-6-8.9-6H48C21.5 0 0 21.5 0 48V256v22.4V288H9.6 374.4 384v-9.6V256 48c0-26.5-21.5-48-48-48H230.5c-3.9 0-7.5 2.4-8.9 6L200.9 57.7c-3.2 8-14.6 8-17.8 0L162.4 6z" opacity="0.4"/>
			</symbol>
			<symbol aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" id="icon-text">
				<path fill="currentColor" class="fa-primary" d="M384 288v16c0 17.7-14.3 32-32 32s-32-14.3-32-32V272c0-26.5 21.5-48 48-48H592c26.5 0 48 21.5 48 48v32c0 17.7-14.3 32-32 32s-32-14.3-32-32V288H512l0 128h16c17.7 0 32 14.3 32 32s-14.3 32-32 32H432c-17.7 0-32-14.3-32-32s14.3-32 32-32h16l0-128H384z"/><path fill="currentColor" class="fa-secondary" d="M64 96v32c0 17.7-14.3 32-32 32s-32-14.3-32-32V80C0 53.5 21.5 32 48 32H192 336c26.5 0 48 21.5 48 48v48c0 17.7-14.3 32-32 32s-32-14.3-32-32V96H224l0 320h32c17.7 0 32 14.3 32 32s-14.3 32-32 32H128c-17.7 0-32-14.3-32-32s14.3-32 32-32h32l0-320H64z" opacity="0.4" />
			</symbol>
			<symbol aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" id="icon-tower-broadcast">
				<path fill="currentColor" class="fa-primary" d="M320 183.4c19.1-11.1 32-31.7 32-55.4c0-35.3-28.7-64-64-64s-64 28.7-64 64c0 23.7 12.9 44.4 32 55.4V480c0 17.7 14.3 32 32 32s32-14.3 32-32V183.4z"/><path fill="currentColor" class="fa-secondary" d="M80.3 44C86.9 27.6 79 9 62.6 2.3S27.6 3.6 20.9 20C7.4 53.4 0 89.9 0 128s7.4 74.6 20.9 108c6.6 16.4 25.3 24.3 41.7 17.7S86.9 228.4 80.3 212C69.8 186.1 64 157.8 64 128s5.8-58.1 16.3-84zM555.1 20C548.4 3.6 529.8-4.3 513.4 2.3S489.1 27.6 495.7 44C506.2 69.9 512 98.2 512 128s-5.8 58.1-16.3 84c-6.6 16.4 1.3 35 17.7 41.7s35-1.3 41.7-17.7c13.5-33.4 20.9-69.9 20.9-108s-7.4-74.6-20.9-108zM170.6 76.8c7.1-16.2-.3-35.1-16.5-42.1s-35.1 .3-42.1 16.5C101.7 74.8 96 100.8 96 128s5.7 53.2 16 76.8c7.1 16.2 25.9 23.6 42.1 16.5s23.6-25.9 16.5-42.1C163.8 163.6 160 146.3 160 128s3.8-35.6 10.6-51.2zM464 51.2c-7.1-16.2-25.9-23.6-42.1-16.5s-23.6 25.9-16.5 42.1C412.2 92.4 416 109.7 416 128s-3.8 35.6-10.6 51.2c-7.1 16.2 .3 35.1 16.5 42.1s35.1-.3 42.1-16.5c10.3-23.6 16-49.6 16-76.8s-5.7-53.2-16-76.8z" opacity="0.4" />
			</symbol>
			<symbol aria-hidden="true" data-prefix="fas" data-icon="flask" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" id="flask">
				<path fill="currentColor" opacity="0.4" d="M309.5 320h-171L64.8 439.8c-.5 .9-.8 1.8-.8 2.8c0 3 2.4 5.4 5.4 5.4H378.6c3 0 5.4-2.4 5.4-5.4c0-1-.3-2-.8-2.8L309.5 320z"/><path fill="currentColor" d="M160 0H288h32c17.7 0 32 14.3 32 32s-14.3 32-32 32V196.8c0 11.8 3.3 23.5 9.5 33.5L437.7 406.2c6.7 10.9 10.3 23.5 10.3 36.4c0 38.3-31.1 69.4-69.4 69.4H69.4C31.1 512 0 480.9 0 442.6c0-12.8 3.6-25.4 10.3-36.4L118.5 230.4c6.2-10.1 9.5-21.7 9.5-33.5V64c-17.7 0-32-14.3-32-32s14.3-32 32-32h32zm32 64V196.8c0 23.7-6.6 46.9-19 67.1L64.8 439.8c-.5 .9-.8 1.8-.8 2.8c0 3 2.4 5.4 5.4 5.4H378.6c3 0 5.4-2.4 5.4-5.4c0-1-.3-2-.8-2.8L275 263.9c-12.4-20.2-19-43.4-19-67.1V64H192z"/>
			</symbol>
			<symbol aria-hidden="true" data-prefix="fad" data-icon="hands-helping" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 540 512" id="hands-helping">
				<g class="fa-group"><path class="fa-secondary" fill="currentColor" d="M224 248V121.68a31.78 31.78 0 0 1 15-27.1l33.5-20.9A64.48 64.48 0 0 1 306.4 64h102.21L512 4.28A32 32 0 0 1 555.72 16l80 138.6a32 32 0 0 1-11.7 43.7l-80 46.2V216a56 56 0 0 0-56-56H304v88a40 40 0 1 1-80 0z" opacity="0.4"></path><path class="fa-primary" fill="currentColor" d="M4.32 357.38A31.92 31.92 0 0 1 16 313.68l80-46.2v-47.3a63.86 63.86 0 0 1 31.1-54.8l64.89-39V248a72 72 0 1 0 144 0v-56H488a23.94 23.94 0 0 1 24 24v48a23.94 23.94 0 0 1-24 24h-8v64a32 32 0 0 1-32 32h-16a64.06 64.06 0 0 1-64 64H231.41L128 507.68A32 32 0 0 1 84.32 496z"></path></g>
			</symbol>
			<symbol aria-hidden="true" data-prefix="fad" data-icon="hourglass-clock" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" id="hourglass-clock">
				<path fill="currentColor" class="fa-primary" d="M432 512a144 144 0 1 0 0-288 144 144 0 1 0 0 288zm16-208v48h32c8.8 0 16 7.2 16 16s-7.2 16-16 16H432c-8.8 0-16-7.2-16-16V304c0-8.8 7.2-16 16-16s16 7.2 16 16z"  /><path class="fa-secondary" fill="currentColor" d="M0 32C0 14.3 14.3 0 32 0H64 320h32c17.7 0 32 14.3 32 32s-14.3 32-32 32V75c0 42.4-16.9 83.1-46.9 113.1L237.3 256l36 36c-11 23-17.2 48.8-17.2 76c0 59.5 29.6 112.1 74.8 144H320 64 32c-17.7 0-32-14.3-32-32s14.3-32 32-32V437c0-42.4 16.9-83.1 46.9-113.1L146.7 256 78.9 188.1C48.9 158.1 32 117.4 32 75V64C14.3 64 0 49.7 0 32zM96 64V75c0 19 5.6 37.4 16 53H272c10.3-15.6 16-34 16-53V64H96z" opacity="0.4"/>
			</symbol>
			<symbol aria-hidden="true" data-prefix="fad" data-icon="comment-dots" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" id="comment-dots">
				<path fill="currentColor" class="fa-primary" d="M96 240a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm128 0a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm160-32a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/><path class="fa-secondary" fill="currentColor" d="M256 448c141.4 0 256-93.1 256-208S397.4 32 256 32S0 125.1 0 240c0 45.1 17.7 86.8 47.7 120.9c-1.9 24.5-11.4 46.3-21.4 62.9c-5.5 9.2-11.1 16.6-15.2 21.6c-2.1 2.5-3.7 4.4-4.9 5.7c-.6 .6-1 1.1-1.3 1.4l-.3 .3 0 0 0 0 0 0 0 0c-4.6 4.6-5.9 11.4-3.4 17.4c2.5 6 8.3 9.9 14.8 9.9c28.7 0 57.6-8.9 81.6-19.3c22.9-10 42.4-21.9 54.3-30.6c31.8 11.5 67 17.9 104.1 17.9zM128 208a32 32 0 1 1 0 64 32 32 0 1 1 0-64zm128 0a32 32 0 1 1 0 64 32 32 0 1 1 0-64zm96 32a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z" opacity="0.4" />
			</symbol>
			<symbol aria-hidden="true" data-prefix="fad" data-icon="plug-plus" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" id="plug-plus">
				<path fill="currentColor" class="fa-primary" d="M288 368a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-80c-8.8 0-16 7.2-16 16v48H368c-8.8 0-16 7.2-16 16s7.2 16 16 16h48v48c0 8.8 7.2 16 16 16s16-7.2 16-16V384h48c8.8 0 16-7.2 16-16s-7.2-16-16-16H448V304c0-8.8-7.2-16-16-16z"/><path fill="currentColor" class="fa-secondary" d="M128 32v96H64V32C64 14.3 78.3 0 96 0s32 14.3 32 32zm192 0v96H256V32c0-17.7 14.3-32 32-32s32 14.3 32 32zM0 192c0-17.7 14.3-32 32-32H352c17.7 0 32 14.3 32 32c0 2.3-.3 4.6-.7 6.8C309.8 220 256 287.7 256 368c0 11.4 1.1 22.5 3.1 33.3c-11.1 5.1-22.9 9-35.1 11.5V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V412.8C87 398 32 333.4 32 256V224c-17.7 0-32-14.3-32-32z" opacity="0.4"/>
			</symbol>
			<symbol aria-hidden="true" data-prefix="fad" data-icon="image" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" id="wppic-image">
				<path d="M0 96C0 60.7 28.7 32 64 32H448c35.3 0 64 28.7 64 64V416c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V96zM323.8 202.5c-4.5-6.6-11.9-10.5-19.8-10.5s-15.4 3.9-19.8 10.5l-87 127.6L170.7 297c-4.6-5.7-11.5-9-18.7-9s-14.2 3.3-18.7 9l-64 80c-5.8 7.2-6.9 17.1-2.9 25.4s12.4 13.6 21.6 13.6h96 32H424c8.9 0 17.1-4.9 21.2-12.8s3.6-17.4-1.4-24.7l-120-176zM112 192a48 48 0 1 0 0-96 48 48 0 1 0 0 96z"/>
			</symbol>
			<symbol aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 460.298 460.297" id="wppic-flaticon-home">
				<g>
					<path d="M230.149,120.939L65.986,256.274c0,0.191-0.048,0.472-0.144,0.855c-0.094,0.38-0.144,0.656-0.144,0.852v137.041
						c0,4.948,1.809,9.236,5.426,12.847c3.616,3.613,7.898,5.431,12.847,5.431h109.63V303.664h73.097v109.64h109.629
						c4.948,0,9.236-1.814,12.847-5.435c3.617-3.607,5.432-7.898,5.432-12.847V257.981c0-0.76-0.104-1.334-0.288-1.707L230.149,120.939
						z"/>
					<path d="M457.122,225.438L394.6,173.476V56.989c0-2.663-0.856-4.853-2.574-6.567c-1.704-1.712-3.894-2.568-6.563-2.568h-54.816
						c-2.666,0-4.855,0.856-6.57,2.568c-1.711,1.714-2.566,3.905-2.566,6.567v55.673l-69.662-58.245
						c-6.084-4.949-13.318-7.423-21.694-7.423c-8.375,0-15.608,2.474-21.698,7.423L3.172,225.438c-1.903,1.52-2.946,3.566-3.14,6.136
						c-0.193,2.568,0.472,4.811,1.997,6.713l17.701,21.128c1.525,1.712,3.521,2.759,5.996,3.142c2.285,0.192,4.57-0.476,6.855-1.998
						L230.149,95.817l197.57,164.741c1.526,1.328,3.521,1.991,5.996,1.991h0.858c2.471-0.376,4.463-1.43,5.996-3.138l17.703-21.125
						c1.522-1.906,2.189-4.145,1.991-6.716C460.068,229.007,459.021,226.961,457.122,225.438z"/>
				</g>
			</symbol>
			<symbol aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" id="wppic-flaticon-screenshot">
				<path clip-rule="evenodd" d="m2.25 5c0-1.51878 1.23122-2.75 2.75-2.75h4c.41421 0 .75.33579.75.75s-.33579.75-.75.75h-4c-.69036 0-1.25.55964-1.25 1.25v4c0 .41421-.33579.75-.75.75s-.75-.33579-.75-.75zm4.75 4c0-1.10457.89543-2 2-2h6c1.1046 0 2 .89543 2 2v6c0 1.1046-.8954 2-2 2h-6c-1.10457 0-2-.8954-2-2zm12 12.75c1.5188 0 2.75-1.2312 2.75-2.75v-4c0-.4142-.3358-.75-.75-.75s-.75.3358-.75.75v4c0 .6904-.5596 1.25-1.25 1.25h-4c-.4142 0-.75.3358-.75.75s.3358.75.75.75z" fill="currentColor"/>
			</symbol>
			<symbol aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 329 329" id="wppic-edd">
				<path d="M280.109,48.056c-29.684,-29.694 -70.712,-48.056 -116.015,-48.056c-45.316,0 -86.338,18.363 -116.031,48.056c-29.701,29.694 -48.063,70.722 -48.063,116.031c0,45.31 18.363,86.338 48.056,116.032c29.694,29.7 70.722,48.056 116.038,48.056c45.309,0 86.337,-18.372 116.022,-48.056c29.693,-29.7 48.056,-70.722 48.056,-116.032c-0,-45.309 -18.369,-86.337 -48.063,-116.031Zm-5.412,226.638c-28.303,28.306 -67.413,45.815 -110.61,45.815c-43.196,0 -82.309,-17.509 -110.615,-45.815c-28.306,-28.307 -45.816,-67.413 -45.816,-110.607c0,-43.196 17.51,-82.303 45.816,-110.609c28.306,-28.306 67.412,-45.816 110.609,-45.816c43.197,0 82.303,17.51 110.61,45.816c28.306,28.306 45.812,67.413 45.812,110.609c0.006,43.194 -17.503,82.304 -45.806,110.607Z" fill="currentColor" /><path d="M305.244,162.719c-0.738,-77.331 -63.647,-139.794 -141.15,-139.794c-77.51,0 -140.425,62.469 -141.15,139.8l64.725,-64.725l21.706,21.706l-47.366,47.366l204.166,-0l-47.366,-47.366l21.707,-21.706l64.728,64.719Zm-141.157,-24.256l-60.143,-63.113l39.968,0l0,-31.109c0,-8.075 9.082,-14.694 20.175,-14.694c11.094,-0 20.175,6.612 20.175,14.694l0,31.109l39.969,0l-60.144,63.113Z" fill="currentColor" /><path d="M181.631,243.531c-3.206,-2.084 -7.268,-3.959 -12.169,-5.587c-3.746,-1.291 -6.831,-2.51 -9.237,-3.685c-2.388,-1.162 -4.15,-2.453 -5.278,-3.815c-1.156,-1.388 -1.697,-3.05 -1.697,-4.957c-0,-1.524 0.459,-2.975 1.444,-4.346c0.965,-1.369 2.475,-2.51 4.525,-3.372c2.072,-0.875 4.709,-1.347 7.984,-1.363c2.638,0.013 5.047,0.225 7.225,0.603c2.153,0.391 4.063,0.863 5.697,1.403c1.656,0.56 2.994,1.088 4.047,1.585l3.644,-10.988c-2.204,-1.106 -4.963,-2.025 -8.313,-2.768c-2.844,-0.629 -6.1,-0.991 -9.797,-1.116l0,-11.369l-10.015,0l-0,11.938c-1.607,0.259 -3.147,0.609 -4.604,1.031c-3.684,1.088 -6.85,2.597 -9.468,4.569c-2.597,1.962 -4.61,4.259 -5.991,6.89c-1.387,2.632 -2.072,5.532 -2.1,8.65c0.019,3.61 0.966,6.791 2.831,9.538c1.866,2.75 4.525,5.134 7.96,7.178c3.418,2.038 7.5,3.794 12.218,5.272c3.541,1.134 6.429,2.319 8.629,3.534c2.203,1.21 3.815,2.544 4.818,4c1.025,1.457 1.532,3.144 1.513,5.032c-0,2.071 -0.61,3.868 -1.797,5.421c-1.175,1.532 -2.912,2.735 -5.197,3.588c-2.284,0.862 -5.072,1.284 -8.341,1.312c-2.659,-0.018 -5.237,-0.246 -7.753,-0.678c-2.497,-0.45 -4.84,-1.031 -7,-1.756c-2.181,-0.725 -4.087,-1.512 -5.743,-2.394l-3.522,11.444c1.593,0.875 3.581,1.662 6.015,2.387c2.435,0.732 5.113,1.313 8.047,1.757c2.913,0.431 5.906,0.65 8.95,0.678l0.535,-0.006l-0,11.753l10.015,-0l0,-12.697c1.272,-0.247 2.481,-0.541 3.631,-0.881c4.019,-1.21 7.357,-2.86 9.997,-4.972c2.66,-2.094 4.629,-4.519 5.935,-7.253c1.3,-2.735 1.95,-5.629 1.95,-8.71c-0,-3.609 -0.772,-6.803 -2.366,-9.55c-1.609,-2.772 -4.003,-5.2 -7.222,-7.3Z" fill="currentColor" />
			</symbol>
			<symbol aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" id="wppic-flaticon-custom-plugin">
				<path d="m7 5c0 .55228.44772 1 1 1s1-.44772 1-1v-2c0-.55228-.44772-1-1-1s-1 .44772-1 1z"></path>
				<path d="m21 8c0 .55228-.4477 1-1 1h-1v3c0 3.5265-2.6077 6.4439-6 6.9291v2.0709c0 .5523-.4477 1-1 1s-1-.4477-1-1v-2.0709c-3.39229-.4852-6-3.4026-6-6.9291v-3h-1c-.55228 0-1-.44772-1-1 0-.55229.44772-1 1-1h16c.5523 0 1 .44772 1 1z"></path>
				<path d="m16 6c-.5523 0-1-.44772-1-1v-2c0-.55228.4477-1 1-1s1 .44772 1 1v2c0 .55228-.4477 1-1 1z"></path>
			</symbol>
			<symbol id="wppic-github-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
				<path fill="currentColor" d="M237.9 461.4C237.9 463.4 235.6 465 232.7 465C229.4 465.3 227.1 463.7 227.1 461.4C227.1 459.4 229.4 457.8 232.3 457.8C235.3 457.5 237.9 459.1 237.9 461.4zM206.8 456.9C206.1 458.9 208.1 461.2 211.1 461.8C213.7 462.8 216.7 461.8 217.3 459.8C217.9 457.8 216 455.5 213 454.6C210.4 453.9 207.5 454.9 206.8 456.9zM251 455.2C248.1 455.9 246.1 457.8 246.4 460.1C246.7 462.1 249.3 463.4 252.3 462.7C255.2 462 257.2 460.1 256.9 458.1C256.6 456.2 253.9 454.9 251 455.2zM316.8 72C178.1 72 72 177.3 72 316C72 426.9 141.8 521.8 241.5 555.2C254.3 557.5 258.8 549.6 258.8 543.1C258.8 536.9 258.5 502.7 258.5 481.7C258.5 481.7 188.5 496.7 173.8 451.9C173.8 451.9 162.4 422.8 146 415.3C146 415.3 123.1 399.6 147.6 399.9C147.6 399.9 172.5 401.9 186.2 425.7C208.1 464.3 244.8 453.2 259.1 446.6C261.4 430.6 267.9 419.5 275.1 412.9C219.2 406.7 162.8 398.6 162.8 302.4C162.8 274.9 170.4 261.1 186.4 243.5C183.8 237 175.3 210.2 189 175.6C209.9 169.1 258 202.6 258 202.6C278 197 299.5 194.1 320.8 194.1C342.1 194.1 363.6 197 383.6 202.6C383.6 202.6 431.7 169 452.6 175.6C466.3 210.3 457.8 237 455.2 243.5C471.2 261.2 481 275 481 302.4C481 398.9 422.1 406.6 366.2 412.9C375.4 420.8 383.2 435.8 383.2 459.3C383.2 493 382.9 534.7 382.9 542.9C382.9 549.4 387.5 557.3 400.2 555C500.2 521.8 568 426.9 568 316C568 177.3 455.5 72 316.8 72zM169.2 416.9C167.9 417.9 168.2 420.2 169.9 422.1C171.5 423.7 173.8 424.4 175.1 423.1C176.4 422.1 176.1 419.8 174.4 417.9C172.8 416.3 170.5 415.6 169.2 416.9zM158.4 408.8C157.7 410.1 158.7 411.7 160.7 412.7C162.3 413.7 164.3 413.4 165 412C165.7 410.7 164.7 409.1 162.7 408.1C160.7 407.5 159.1 407.8 158.4 408.8zM190.8 444.4C189.2 445.7 189.8 448.7 192.1 450.6C194.4 452.9 197.3 453.2 198.6 451.6C199.9 450.3 199.3 447.3 197.3 445.4C195.1 443.1 192.1 442.8 190.8 444.4zM179.4 429.7C177.8 430.7 177.8 433.3 179.4 435.6C181 437.9 183.7 438.9 185 437.9C186.6 436.6 186.6 434 185 431.7C183.6 429.4 181 428.4 179.4 429.7z"/>
			</symbol>
		</svg>
		<?php do_action( 'dlxplugins/ajaxify/admin/header_content' ); ?>
		<?php
	}
	/**
	 * Output the top-level admin tabs.
	 */
	public static function get_settings_tabs() {

		$settings_url_base = Functions::get_settings_url( 'home' );

		$tabs = array();
		/**
		 * Filer the output of the tab output.
		 *
		 * Potentially modify or add your own tabs.
		 *
		 * @since 1.0.0
		 *
		 * @param array $tabs Associative array of tabs.
		 */
		$tabs       = apply_filters( 'wppic_admin_tabs', $tabs );
		$tab_html   = '<nav class="wppic-admin-options">';
		$tabs_count = count( $tabs );
		if ( $tabs && ! empty( $tabs ) && is_array( $tabs ) ) {
			$active_tab = Functions::get_admin_tab();
			if ( null === $active_tab ) {
				$active_tab = 'home';
			}
			$is_tab_match = false;
			if ( 'home' === $active_tab ) {
				$active_tab = 'home';
			} else {
				foreach ( $tabs as $tab ) {
					$tab_get = isset( $tab['get'] ) ? $tab['get'] : '';
					if ( $active_tab === $tab_get ) {
						$is_tab_match = true;
					}
				}
				if ( ! $is_tab_match ) {
					$active_tab = 'home';
				}
			}
			$do_action = false;
			foreach ( $tabs as $tab ) {
				$classes = array();
				$tab_get = isset( $tab['get'] ) ? $tab['get'] : '';
				if ( $active_tab === $tab_get ) {
					$classes[] = 'nav-tab-active';
					$do_action = isset( $tab['action'] ) ? $tab['action'] : false;
				} elseif ( ! $is_tab_match && 'home' === $tab_get ) {
					$classes[] = 'nav-tab-active';
					$do_action = isset( $tab['action'] ) ? $tab['action'] : false;
				}
				$tab_url   = isset( $tab['url'] ) ? $tab['url'] : '';
				$tab_label = isset( $tab['label'] ) ? $tab['label'] : '';
				$tab_html .= sprintf(
					'<a href="%s" class="%s" id="eff-%s"><svg class="wppic-icon wppic-icon-tab">%s</svg><span>%s</span></a>',
					esc_url( $tab_url ),
					esc_attr( implode( ' ', $classes ) ),
					esc_attr( $tab_get ),
					sprintf( '<use xlink:href="#%s"></use>', esc_attr( $tab['icon'] ) ),
					esc_html( $tab['label'] )
				);
			}
			$tab_html .= '</nav>';
			if ( $tabs_count > 0 ) {
				echo wp_kses( $tab_html, Functions::get_kses_allowed_html() );
			}

			$current_tab     = Functions::get_admin_tab();
			$current_sub_tab = Functions::get_admin_sub_tab();

			/**
			 * Filer the output of the sub-tab output.
			 *
			 * Potentially modify or add your own sub-tabs.
			 *
			 * @since 1.0.0
			 *
			 * @param array $sub_tabs Associative array of sub tabs.
			 * @param string $current_tab The current tab.
			 * @param string $current_sub_tab The current sub tab.
			 */
			$sub_tabs = apply_filters( 'wppic_admin_sub_tabs', array(), $current_tab, $current_sub_tab );

			// Check to see if no tabs are available for this view.
			if ( null === $current_tab && null === $current_sub_tab ) {
				$current_tab = 'home';
			}
			ob_start();
			if ( $sub_tabs && ! empty( $sub_tabs ) && is_array( $sub_tabs ) ) {
				if ( null === $current_sub_tab ) {
					$current_sub_tab = '';
				}
				$is_tab_match      = false;
				$first_sub_tab     = current( $sub_tabs );
				$first_sub_tab_get = $first_sub_tab['get'];
				if ( $first_sub_tab_get === $current_sub_tab ) {
					$active_tab = $current_sub_tab;
				} else {
					$active_tab = $current_sub_tab;
					foreach ( $sub_tabs as $tab ) {
						$tab_get = isset( $tab['get'] ) ? $tab['get'] : '';
						if ( $active_tab === $tab_get ) {
							$is_tab_match = true;
						}
					}
					if ( ! $is_tab_match ) {
						$active_tab = $first_sub_tab_get;
					}
				}
				$sub_tab_html_array = array();
				$do_subtab_action   = false;
				$maybe_sub_tab      = '';
				foreach ( $sub_tabs as $sub_tab ) {
					$classes = array( 'wppic-sub-tab' );
					$tab_get = isset( $sub_tab['get'] ) ? $sub_tab['get'] : '';
					if ( $active_tab === $tab_get ) {
						$classes[]        = 'wppic-sub-tab-active';
						$do_subtab_action = true;
						$current_sub_tab  = $tab_get;
					} elseif ( ! $is_tab_match && $first_sub_tab_get === $tab_get ) {
						$classes[]        = 'wppic-sub-tab-active';
						$do_subtab_action = true;
						$current_sub_tab  = $first_sub_tab_get;
					}
					$tab_url   = isset( $sub_tab['url'] ) ? $sub_tab['url'] : '';
					$tab_label = isset( $sub_tab['label'] ) ? $sub_tab['label'] : '';
					if ( $current_sub_tab === $tab_get ) {
						$sub_tab_html_array[] = sprintf( '<span class="%s" id="wppic-tab-%s">%s</span>', esc_attr( implode( ' ', $classes ) ), esc_attr( $tab_get ), esc_html( $sub_tab['label'] ) );
					} else {
						$sub_tab_html_array[] = sprintf( '<a href="%s" class="%s" id="wppic-tab-%s">%s</a>', esc_url( $tab_url ), esc_attr( implode( ' ', $classes ) ), esc_attr( $tab_get ), esc_html( $sub_tab['label'] ) );
					}
				}
				if ( ! empty( $sub_tab_html_array ) ) {
					echo '<nav class="wppic-sub-links">' . wp_kses_post( rtrim( implode( ' | ', $sub_tab_html_array ), ' | ' ) ) . '</nav>';
				}
			}
			$subtab_content = ob_get_clean();

		}
	}

	/**
	 * Run script and enqueue stylesheets and stuff like that.
	 */
	public static function get_settings_footer() {
		?>
		<?php
	}
}
