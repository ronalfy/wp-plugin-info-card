<?php
/**
 * Template for the plugin large card.
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
 ***************************************************************/

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
	$background_image = 'style="background-image: url(' . esc_url( $icon ) . ' );"';
} else {
	$background_image = 'data="no-image"';
}

// Plugin banner.
$banner = '';
if ( ! empty( $wppic_data->banners['low'] ) ) {
	$banner = '<img src="' . esc_url( $wppic_data->banners['low'] ) . '" alt="' . esc_attr( $wppic_data->name ) . '" />';
}

$reviews_url = sprintf(
	'https://wordpress.org/support/view/plugin-reviews/%s',
	$wppic_data->slug
);
if ( isset( $wppic_data->reviews_url ) && ! empty( $wppic_data->reviews_url ) ) {
	$reviews_url = $wppic_data->reviews_url;
}

// If author_url is set, use it instead of the author profile.
$author_url = '';
if ( isset( $wppic_data->author_profile ) && ! empty( $wppic_data->author_profile ) ) {
	$author_url = $wppic_data->author_profile;
}


$download_label = esc_html__( 'Direct Download', 'wp-plugin-info-card' );
if ( isset( $wppic_data->is_edd ) && $wppic_data->is_edd ) {
	$download_label = esc_html__( 'More Details', 'wp-plugin-info-card' );
}


/***************************************************************
 * Start template
 */
?>
<div class="wp-pic-large" style="display: none;">
	<div class="wp-pic-large-content">
		<a class="wp-pic-asset-bg" href="<?php echo esc_url( $wppic_data->url ); ?>" target="_blank" title="<?php esc_attr_e( 'WordPress.org Plugin Page', 'wp-plugin-info-card' ); ?>">
			<?php echo wp_kses_post( $banner ); ?>
			<span class="wp-pic-asset-bg-title"><span><?php echo esc_html( $wppic_data->name ); ?></span></span>
		</a>
		<div class="wp-pic-half-first">
			<a class="wp-pic-logo" href="<?php echo esc_url( $wppic_data->url ); ?>" <?php echo wp_kses_post( $background_image ); ?> target="_blank" title="<?php esc_attr_e( 'WordPress.org Plugin Page', 'wp-plugin-info-card' ); ?>"></a>
			<p class="wp-pic-author"><?php esc_html_e( 'Author(s):', 'wp-plugin-info-card' ); ?>
				<?php if ( ! empty( $author_url ) ) : ?>
					<a href="<?php echo esc_url( $author_url ); ?>"><?php echo esc_html( $wppic_data->author ); ?></a>
				<?php else : ?>
					<?php echo esc_html( $wppic_data->author ); ?>
				<?php endif; ?>
			</p>
			<p class="wp-pic-version"><span><?php esc_html_e( 'Current Version:', 'wp-plugin-info-card' ); ?></span> <?php echo esc_html( $wppic_data->version ); ?></p>
			<p class="wp-pic-updated"><span><?php esc_html_e( 'Last Updated:', 'wp-plugin-info-card' ); ?></span> <?php echo esc_html( $wppic_data->last_updated ); ?></p>
			<p><a class="wp-pic-dl-link" href="<?php echo esc_url( $wppic_data->download_link ); ?>" title="<?php esc_html_e( 'Direct download', 'wp-plugin-info-card' ); ?>"><?php echo esc_html( $download_label ); ?></a></p>
			<?php echo wp_kses_post( $wppic_data->credit ); ?>
		</div>
		<div class="wp-pic-half-last">
			<div class="wp-pic-bottom">
				<div class="wp-pic-bar">
					<a href="<?php echo esc_url( $reviews_url ); ?>" class="wp-pic-rating" target="_blank" title="<?php esc_html_e( 'Ratings', 'wp-plugin-info-card' ); ?>">
						<?php echo esc_html( round( $wppic_data->rating ) ); ?>%<em><?php esc_html_e( 'Ratings', 'wp-plugin-info-card' ); ?></em>
					</a>
					<?php if ( isset( $wppic_data->active_installs ) ) : ?>
					<a href="<?php echo esc_url( $wppic_data->download_link ); ?>" class="wp-pic-downloaded" target="_blank" title="<?php esc_html_e( 'Direct download', 'wp-plugin-info-card' ); ?>">
						<?php echo esc_html( number_format_i18n( $wppic_data->active_installs ) ); ?>+<em><?php esc_html_e( 'Installs', 'wp-plugin-info-card' ); ?></em>
					</a>
					<?php endif; ?>
					<a href="<?php echo esc_url( $wppic_data->url ); ?>" class="wp-pic-requires" target="_blank" title="<?php esc_html_e( 'WordPress.org Plugin Page', 'wp-plugin-info-card' ); ?>">
						<?php echo esc_html( $wppic_data->requires ); ?><em><?php esc_html_e( 'Requires', 'wp-plugin-info-card' ); ?></em>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
