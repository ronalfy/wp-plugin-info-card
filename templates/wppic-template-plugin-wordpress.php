<?php
/**
 * Template for the WordPress plugin card.
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


// Needed to load the rating star function.
require_once ABSPATH . 'wp-admin/includes/template.php';

// Icon URL.
if ( ! empty( $wppic_data->icons['svg'] ) ) {
	$icon = $wppic_data->icons['svg'];
} elseif ( ! empty( $wppic_data->icons['2x'] ) ) {
	$icon = $wppic_data->icons['2x'];
} elseif ( ! empty( $wppic_data->icons['1x'] ) ) {
	$icon = $wppic_data->icons['1x'];
}

// Define card image.
if ( ! empty( $image ) ) {
	// $image is the custom image URL if you provided it
	$logo = '<img src="' . esc_url( $image ) . '" class="wp-pic-plugin-icon" alt="">';
} elseif ( isset( $icon ) ) {
	$logo = '<img src="' . esc_url( $icon ) . '" class="wp-pic-plugin-icon" alt="">';
} else {
	$logo = '<span class="wp-pic-plugin-icon" data="no-image"></span>';
}

// Active installs. Omitted when this response must not include license totals.
$active_installs_text = '';
if ( isset( $wppic_data->active_installs ) ) {
	if ( $wppic_data->active_installs >= 1000000 ) {
		$active_installs_text = _x( '1+ Million', 'Active plugin installs', 'wp-plugin-info-card' );
	} else {
		$active_installs_text = number_format_i18n( $wppic_data->active_installs ) . '+';
	}
}

$download_label = esc_html__( 'Download', 'wp-plugin-info-card' );
if ( isset( $wppic_data->is_edd ) && $wppic_data->is_edd ) {
	$download_label = esc_html__( 'More Details', 'wp-plugin-info-card' ); // EDD uses the download link for the more details link.
}

// If author_url is set, use it instead of the author profile.
$author_url = '';
if ( isset( $wppic_data->author_profile ) && ! empty( $wppic_data->author_profile ) ) {
	$author_url = $wppic_data->author_profile;
}

/***************************************************************
 * Start template
 */
?>
<div class="wp-pic-wordpress" style="display: none;">
	<div class="wp-pic-wordpress-content">
		<div class="wp-pic-plugin-card-top">
			<div class="wp-pic-column-name">
				<h3>
					<a href="<?php echo esc_url( $wppic_data->url ); ?>"  title="<?php /* Translators: %s is the plugin name */ printf( esc_attr__( 'More information about %s', 'wp-plugin-info-card' ), esc_attr( $wppic_data->name ) ); ?>">
						<?php echo esc_html( $wppic_data->name ); ?>
						<?php echo wp_kses_post( $logo ); ?>
					</a>
				</h3>
			</div>
			<div class="wp-pic-action-links">
				<a class="wp-pic-action-buttons" href="<?php echo esc_url( $wppic_data->download_link ); ?>" title="<?php echo esc_attr( $download_label ); ?>"><?php echo esc_html( $download_label ); ?></a>
			</div>
			<div class="wp-pic-column-description">
				<p><?php echo wp_kses_post( $wppic_data->short_description ); ?></p>
				<?php if ( ! empty( $author_url ) ) : ?>
					<p class="authors"><?php esc_html_e( 'By', 'wp-plugin-info-card' ); ?> <cite><a href="<?php echo esc_url( $author_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $wppic_data->author ); ?></a></cite></p>
				<?php else : ?>
					<p class="authors"><?php esc_html_e( 'By', 'wp-plugin-info-card' ); ?> <cite><?php echo esc_html( $wppic_data->author ); ?></cite></p>
				<?php endif; ?>
			</div>
			<?php echo wp_kses_post( $wppic_data->credit ); ?>
		</div>
		<div class="wp-pic-plugin-card-bottom">
			<div class="wp-pic-column-rating" title="<?php /* Translators: %s is the number of ratings */ printf( esc_attr( _n( '(based on %s rating)', '(based on %s ratings)', $wppic_data->num_ratings, 'wp-plugin-info-card' ) ), esc_html( number_format_i18n( $wppic_data->num_ratings ) ) ); ?>">
				<?php
				wp_star_rating(
					array(
						'rating' => $wppic_data->rating,
						'type'   => 'percent',
						'number' => $wppic_data->num_ratings,
					)
				);
				?>
				<span class="wp-pic-num-ratings" aria-hidden="true">(<?php echo esc_html( number_format_i18n( $wppic_data->num_ratings ) ); ?>)</span>
			</div>
			<div class="wp-pic-column-updated">
				<strong><?php esc_html_e( 'Last Updated:', 'wp-plugin-info-card' ); ?></strong> <?php /* Translators: %s is the time ago */ printf( esc_html__( '%s ago', 'wp-plugin-info-card' ), esc_html( human_time_diff( strtotime( $wppic_data->last_updated_mk ) ) ) ); ?>
			</div>
			<?php if ( '' !== $active_installs_text ) : ?>
			<div class="wp-pic-column-downloaded">
				<?php /* Translators: %s is the number of active installs */ printf( esc_html__( '%s Active Installs', 'wp-plugin-info-card' ), esc_html( $active_installs_text ) ); ?>
			</div>
			<?php endif; ?>	
			<div class="wp-pic-column-compatibility">
				<span class="wp-pic-compatibility-compatible"><?php esc_html_e( 'Compatible with WordPress', 'wp-plugin-info-card' ); ?> <?php echo esc_html( $wppic_data->requires ); ?></span>
			</div>
		</div>
	</div>
</div>