<?php
/**
 * Template for the plugin ratings card.
 *
 * @package WP_Plugin_Info_Card
 */

/***************************************************************
 * $wppic_data Object contain the following values:
 * url, name, slug, version, author, author_profile, contributors, requires, tested, requires, rating, num_ratings, ratings,
 * active_installs, downloaded, last_updated, last_updated_mk, added, homepage, short_description, download_link, donate_link, icons, banners
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Fix for requiered version with extra info. EG: WP 3.9, BP 2.1+.
if ( is_numeric( $wppic_data->requires ) ) {
	$wppic_data->requires = 'WP ' . $wppic_data->requires . '+';
}

// Icon URL.
if ( ! empty( $wppic_data->icons['svg'] ) ) {
	$icon = $wppic_data->icons['svg'];
} elseif ( ! empty( $wppic_data->icons['2x'] ) ) {
	$icon = $wppic_data->icons['2x'];
} elseif ( ! empty( $wppic_data->icons['1x'] ) ) {
	$icon = $wppic_data->icons['1x'];
}

// Define card image.
// $image is the custom image URL if you provided it.
if ( ! empty( $image ) ) {
	$background_image = esc_url( $image );
} elseif ( isset( $icon ) ) {
	$background_image = esc_url( $icon );
} else {
	$background_image = '#';
}

// Plugin banner.
$banner = '';
if ( ! empty( $wppic_data->banners['low'] ) ) {
	$banner = 'style="background-image: url(' . esc_attr( $wppic_data->banners['low'] ) . ' );"';
}

$download_label = __( 'Download', 'wp-plugin-info-card' );
if ( isset( $wppic_data->is_edd ) && $wppic_data->is_edd ) {
	$download_label = __( 'More Details', 'wp-plugin-info-card' );
}

$installs_url = sprintf( 'https://wordpress.org/plugins/%s/advanced/', $wppic_data->slug );
if ( isset( $wppic_data->is_edd ) && $wppic_data->is_edd ) {
	$installs_url = $wppic_data->download_link;
}


/***************************************************************
 * Start template
 */
?>
<div class="wp-pic-ratings wp-pic-plugin-ratings wp-pic-card" style="display: none;">
	<div class="wp-pic-ratings wp-pic-ratings-front">
		<div class="wp-pic-ratings-content">
			<div class="wp-pic-logo wp-pic-rating-logo"><a class="wp-pic-logo-anchor" href="<?php echo esc_url( $wppic_data->url ); ?>" target="_blank" title="<?php esc_attr_e( 'WordPress.org Plugin Page', 'wp-plugin-info-card' ); ?>"><img src="<?php echo esc_url_raw( $background_image ); ?>" width="85" height="85" alt="WordPress plugin logo" /></a></div>
			<div class="wp-pic-rating-row">
				<?php
				$rating = round( $wppic_data->rating / 20, 1 );

				?>
				<div class="wp-pic-rating">
					<?php
					$percentage     = round( $wppic_data->rating / 20, 1 ) * 20;
					$alt_percentage = absint( $percentage );
					if ( 100 === $percentage ) {
						$percentage = 0;
					}
					?>
					<span class="wp-pic-rating-background" style="display: inline-block; letter-spacing: 5px; background: linear-gradient(90deg, var(--wppic-plugin-ratings-card-star-color) <?php echo esc_attr( $percentage ); ?>%, #cccccc <?php echo esc_attr( $alt_percentage ); ?>%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
						★★★★★
					</span>
				</div>
			</div>
			<?php
			/**
			 * Filter the number of words to trim in the plugin name.
			 *
			 * @param int $plugin_trim_words Number of words to trim the plugin name.
			 * @param object $wppic_data Plugin data.
			 *
			 * @since 5.1.0
			 */
			$plugin_trim_words = apply_filters( 'wppic_plugin_title_trim_words', 7, $wppic_data );
			?>
			<div class="wp-pic-name"><?php echo esc_html( wp_trim_words( $wppic_data->name, $plugin_trim_words ) ); ?></div>
			<?php /* Translators: %d is the number of ratings for a plugin */ ?>
			<div class="wp-pic-rating-stats"><?php echo esc_html( round( $wppic_data->rating / 20, 1 ) ); ?> <?php printf( esc_html__( 'stars based on %s ratings', 'wp-plugin-info-card' ), esc_html( number_format_i18n( absint( $wppic_data->num_ratings ) ) ) ); ?></div>
		</div>
		<div class="wp-pic-bottom wp-pic-bottom-ratings">
			<div class="wp-pic-bar">
				<a href="<?php echo esc_url( $wppic_data->download_link ); ?>">
					<div class="wp-pic-bar-item">
						<div class="wp-pic-plugin-screenshots-meta-item-svg">
							<svg width="24" height="24">
								<use xlink:href="#wppic-icon-code"></use>
							</svg>
						</div>
						<div class="wp-pic-plugin-screenshots-meta-item-label">
							v<?php echo esc_html( $wppic_data->version ); ?>
						</div>
					</div>
				</a>
				<a href="<?php echo esc_url( $wppic_data->url ); ?>">
					<div class="wp-pic-bar-item">
						<div class="wp-pic-plugin-screenshots-meta-item-svg">
							<svg width="24" height="24">
								<use xlink:href="#wppic-icon-wordpress"></use>
							</svg>
						</div>
						<div class="wp-pic-plugin-screenshots-meta-item-label">
							<?php echo esc_html( $wppic_data->requires ); ?>
						</div>
					</div>
				</a>
				<?php if ( isset( $wppic_data->active_installs ) ) : ?>
				<a href="<?php echo esc_url( $installs_url ); ?>">
					<div class="wp-pic-bar-item">
						<div class="wp-pic-plugin-screenshots-meta-item-svg">
							<svg width="24" height="24">
								<use xlink:href="#wppic-icon-download-cloud"></use>
							</svg>
						</div>
						<div class="wp-pic-plugin-screenshots-meta-item-label">
							<?php echo esc_html( number_format_i18n( $wppic_data->active_installs ) ); ?>+ <?php esc_html_e( 'Installs', 'wp-plugin-info-card' ); ?>
						</div>
					</div>
				</a>
				<?php endif; ?>
			</div>
			<div class="wp-pic-download">
				<span><a href="<?php echo esc_url_raw( $wppic_data->download_link ); ?>"><?php echo esc_html( $download_label ); ?></a></span>
			</div>
		</div>
	</div>
</div>