<?php
/**
 * Template for the plugin card.
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
	$background_image = 'style="background-image: url( ' . esc_url_raw( $icon ) . ' );"';
} else {
	$background_image = 'data="no-image"';
}

// Plugin banner.
$banner = '';
if ( ! empty( $wppic_data->banners['low'] ) ) {
	$banner = 'style="background-image: url(' . esc_url_raw( $wppic_data->banners['low'] ) . ' );"';
}

$reviews_url = sprintf(
	'https://wordpress.org/support/view/plugin-reviews/%s',
	$wppic_data->slug
);
if ( isset( $wppic_data->reviews_url ) && ! empty( $wppic_data->reviews_url ) ) {
	$reviews_url = $wppic_data->reviews_url;
}

$flip_label = esc_html__( 'WordPress.org Plugin Page', 'wp-plugin-info-card' );
if ( isset( $wppic_data->is_edd ) && $wppic_data->is_edd ) {
	$flip_label = esc_html__( 'More Details', 'wp-plugin-info-card' );
}

$download_label = esc_html__( 'Direct Download', 'wp-plugin-info-card' );
if ( isset( $wppic_data->is_edd ) && $wppic_data->is_edd ) {
	$download_label = esc_html__( 'More Details', 'wp-plugin-info-card' );
}

/***************************************************************
 * Start template
 */
?>
<div class="wp-pic-flip" style="display: none;">
	<div class="wp-pic-face wp-pic-front">
		<a class="wp-pic-logo" href="<?php echo esc_url_raw( $wppic_data->url ); ?>" <?php echo wp_kses_post( $background_image ); ?> target="_blank" title="<?php esc_attr_e( 'WordPress.org Plugin Page', 'wp-plugin-info-card' ); ?>"></a>
		<a class="wp-pic-name" href="<?php echo esc_url_raw( $wppic_data->url ); ?>" target="_blank" title="<?php esc_attr_e( 'WordPress.org Plugin Page', 'wp-plugin-info-card' ); ?>"><?php echo esc_html( $wppic_data->name ); ?></a>
		<p class="wp-pic-author"><?php esc_html_e( 'Author(s):', 'wp-plugin-info-card' ); ?> <?php echo esc_html( $wppic_data->author ); ?></p>
		<div class="wp-pic-bottom">
			<div class="wp-pic-bar">
				<a href="<?php echo esc_url( $reviews_url ); ?>" class="wp-pic-rating" target="_blank" title="<?php esc_attr_e( 'Ratings', 'wp-plugin-info-card' ); ?>">
					<?php echo esc_html( round( $wppic_data->rating ) ); ?>%<em><?php esc_html_e( 'Ratings', 'wp-plugin-info-card' ); ?></em>
				</a>
				<?php if ( isset( $wppic_data->active_installs ) ) : ?>
				<a href="<?php echo esc_url_raw( $wppic_data->download_link ); ?>" class="wp-pic-downloaded" target="_blank" title="<?php esc_attr_e( 'Direct download', 'wp-plugin-info-card' ); ?>">
					<?php echo esc_html( number_format_i18n( $wppic_data->active_installs ) ); ?>+<em><?php esc_html_e( 'Installs', 'wp-plugin-info-card' ); ?></em>
				</a>
				<?php endif; ?>
				<a href="<?php echo esc_url( $wppic_data->url ); ?>" class="wp-pic-requires" target="_blank" title="<?php esc_attr_e( 'WordPress.org Plugin Page', 'wp-plugin-info-card' ); ?>">
					<?php echo esc_html( $wppic_data->requires ); ?><em><?php esc_html_e( 'Requires', 'wp-plugin-info-card' ); ?></em>
				</a>
			</div>
			<div class="wp-pic-download">
				<span><?php esc_html_e( 'More info', 'wp-plugin-info-card' ); ?></span>
			</div>
		</div>
	</div>
	<div class="wp-pic-face wp-pic-back">
		<a class="wp-pic-dl-ico" href="<?php echo esc_url_raw( $wppic_data->download_link ); ?>" title="<?php esc_html_e( 'Direct download', 'wp-plugin-info-card' ); ?>"></a>
		<p><a class="wp-pic-dl-link" href="<?php echo esc_url_raw( $wppic_data->download_link ); ?>" title="<?php esc_html_e( 'Direct download', 'wp-plugin-info-card' ); ?>"><?php echo esc_html( $download_label ); ?></a></p>
		<p class="wp-pic-version"><span><?php esc_html_e( 'Current Version:', 'wp-plugin-info-card' ); ?></span> <?php echo esc_html( $wppic_data->version ); ?></p>
		<p class="wp-pic-updated"><span><?php esc_html_e( 'Last Updated:', 'wp-plugin-info-card' ); ?></span> <?php echo esc_html( $wppic_data->last_updated ); ?></p>
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
			<a class="wp-pic-page" href="<?php echo esc_url( $wppic_data->url ); ?>" target="_blank" title="<?php echo esc_attr( $flip_label ); ?>"><?php echo esc_html( $flip_label ); ?></a>
		</div>
		<a class="wp-pic-asset-bg" <?php echo wp_kses_post( $banner ); ?> href="<?php echo esc_url( $wppic_data->url ); ?>" target="_blank" title="<?php echo esc_attr( $flip_label ); ?>">
			<span class="wp-pic-asset-bg-title"><span><?php echo esc_html( $wppic_data->name ); ?></span></span>
		</a>
		<div class="wp-pic-goback" title="<?php esc_attr_e( 'Back', 'wp-plugin-info-card' ); ?>"><span></span></div>
		<?php echo wp_kses_post( $wppic_data->credit ); ?>
	</div>
</div>