<?php
/**
 * Template for the plugin flex card.
 *
 * @package WP_Plugin_Info_Card
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/***************************************************************
 * $wppic_data Object contain the following values:
 * url, name, slug, version, author, author_profile, contributors, requires, tested, requires, rating, num_ratings, ratings,
 * active_installs, downloaded, last_updated, last_updated_mk, added, homepage, short_description, download_link, donate_link, icons, banners
 */

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
	$background_image = 'style="background-image: url( ' . esc_url( $image ) . ' );"';
} elseif ( isset( $icon ) ) {
	$background_image = 'style="background-image: url( ' . esc_attr( $icon ) . ' );"';
} else {
	$background_image = 'data="no-image"';
}

// Plugin banner.
$banner       = '';
$banner_image = isset( $wppic_data->banners['high'] ) ? $wppic_data->banners['high'] : '';
if ( empty( $banner_image ) ) {
	if ( isset( $wppic_data->banners['low'] ) ) {
		$banner_image = esc_url_raw( $wppic_data->banners['low'] );
	} else {
		$banner_image = esc_url_raw( plugins_url( 'assets/img/default-banner.png', WPPIC_FILE ) );
	}
}
$wppic_data->name = wp_trim_words( $wppic_data->name, 6 );

$reviews_url = sprintf(
	'https://wordpress.org/support/view/plugin-reviews/%s',
	$wppic_data->slug
);
if ( isset( $wppic_data->reviews_url ) && ! empty( $wppic_data->reviews_url ) ) {
	$reviews_url = $wppic_data->reviews_url;
}

$download_label = sprintf(
	/* translators: %s: Plugin Name */
	esc_html__( 'Download: %s', 'wp-plugin-info-card' ),
	$wppic_data->name
);
if ( isset( $wppic_data->is_edd ) && $wppic_data->is_edd ) {
	$download_label = esc_html__( 'More Details', 'wp-plugin-info-card' );
}

/***************************************************************
 * Start template
 */
?>
<div class="wp-pic-flip" style="display: none;">
	<div class="wp-pic-face wp-pic-front">
		<?php
		if ( ! empty( $banner_image ) ) :
			?>
		<div class="wp-pic-banner-wrapper">
			<a href="<?php echo esc_url( $wppic_data->url ); ?>" target="_blank" title="<?php esc_attr_e( 'WordPress.org Plugin Page', 'wp-plugin-info-card' ); ?>"><img src="<?php echo esc_url( $banner_image ); ?>" alt="<?php echo esc_attr( $wppic_data->name ); ?>" /></a>
		</div>
		<?php endif; ?>
		<div class="wp-pic-name-wrapper">
			<a class="wp-pic-name" href="<?php echo esc_url( $wppic_data->url ); ?>" target="_blank" title="<?php esc_attr_e( 'WordPress.org Plugin Page', 'wp-plugin-info-card' ); ?>"><strong><?php echo esc_html( $wppic_data->name ); ?></strong></a>
		</div>
		<div class="wp-pic-version-wrapper">
			<p class="wp-pic-version"><span><?php esc_html_e( 'Current Version:', 'wp-plugin-info-card' ); ?></span> <?php echo esc_html( $wppic_data->version ); ?></p>
		</div>
		<div class="wp-pic-updated-wrapper">
			<p class="wp-pic-updated"><span><?php esc_html_e( 'Last Updated:', 'wp-plugin-info-card' ); ?></span> <?php echo esc_html( $wppic_data->last_updated ); ?></p>
		</div>
		<div class="wp-pic-tested-wrapper">
			<p class="wp-pic-tested"><span><?php esc_html_e( 'Tested Up To:', 'wp-plugin-info-card' ); ?></span> <?php echo esc_html( $wppic_data->tested ); ?></p>
		</div>
		<div class="wp-pic-author-wrapper">
			<p class="wp-pic-author"><?php esc_html_e( 'Author(s):', 'wp-plugin-info-card' ); ?> <?php echo esc_html( $wppic_data->author ); ?></p>
		</div>
		<div class="wp-pic-bottom">
			<div class="wp-pic-bar">
				<a href="<?php echo esc_url( $reviews_url ); ?>" class="wp-pic-rating" target="_blank" title="<?php esc_attr_e( 'Ratings', 'wp-plugin-info-card' ); ?>">
					<?php echo esc_html( round( $wppic_data->rating ) ); ?>%<em><?php esc_html_e( 'Ratings', 'wp-plugin-info-card' ); ?></em>
				</a>
				<?php if ( isset( $wppic_data->active_installs ) ) : ?>
				<a href="<?php echo esc_url( $wppic_data->download_link ); ?>" class="wp-pic-downloaded" target="_blank" title="<?php esc_attr_e( 'Direct download', 'wp-plugin-info-card' ); ?>">
					<?php echo esc_html( number_format_i18n( $wppic_data->active_installs ) ); ?>+<em><?php esc_html_e( 'Installs', 'wp-plugin-info-card' ); ?></em>
				</a>
				<?php endif; ?>
				<a href="<?php echo esc_url( $wppic_data->url ); ?>" class="wp-pic-requires" target="_blank" title="<?php esc_attr_e( 'WordPress.org Plugin Page', 'wp-plugin-info-card' ); ?>">
					<?php echo esc_html( $wppic_data->requires ); ?><em><?php esc_html_e( 'Requires', 'wp-plugin-info-card' ); ?></em>
				</a>
			</div>
			<div class="wp-pic-download-link">
				<a href="<?php echo esc_url( $wppic_data->download_link ); ?>" target="_blank" title="<?php esc_attr_e( 'WordPress.org Plugin Page', 'wp-plugin-info-card' ); ?>">
				<span><?php echo esc_html( $download_label ); ?></span>
				</a>
			</div>
		</div>
	</div>
</div>
