<?php
/**
 * Presentational search and dynamic property archive for the Properties Page.
 *
 * @package Estatein
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Figma search controls without search behaviour.
 *
 * @return string
 */
function estatein_property_search_ui_shortcode() {
	$filters = array(
		'location'      => __( 'Location', 'estatein' ),
		'property-type' => __( 'Property Type', 'estatein' ),
		'price-range'   => __( 'Pricing Range', 'estatein' ),
		'property-size' => __( 'Property Size', 'estatein' ),
		'build-year'    => __( 'Build Year', 'estatein' ),
	);
	$icons   = array(
		'location'      => '<svg viewBox="0 0 24 24" role="presentation"><path d="M12 22s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z" fill="currentColor"/><circle cx="12" cy="11" r="2.5" fill="#141414"/></svg>',
		'property-type' => '<svg viewBox="0 0 24 24" role="presentation"><path d="m3 11.5 9-7.5 9 7.5M5.5 10.5V20h13v-9.5M9.5 20v-6h5v6"/></svg>',
		'price-range'   => '<svg viewBox="0 0 24 24" role="presentation"><circle cx="12" cy="12" r="9"/><path d="M15.5 8.5c-.8-.7-1.8-1-3-1-1.7 0-3 .9-3 2.2 0 3.3 6 1.4 6 4.7 0 1.4-1.3 2.3-3.2 2.3-1.3 0-2.6-.5-3.4-1.3M12 5.5v13"/></svg>',
		'property-size' => '<svg viewBox="0 0 24 24" role="presentation"><path d="M4 9V4h5M15 4h5v5M20 15v5h-5M9 20H4v-5M4.5 4.5l5.3 5.3M19.5 4.5l-5.3 5.3M19.5 19.5l-5.3-5.3M4.5 19.5l5.3-5.3"/></svg>',
		'build-year'    => '<svg viewBox="0 0 24 24" role="presentation"><rect x="3.5" y="5" width="17" height="16" rx="2"/><path d="M8 3v4M16 3v4M3.5 10h17"/></svg>',
	);

	ob_start();
	?>
	<div class="estatein-property-search" aria-label="<?php esc_attr_e( 'Property search preview', 'estatein' ); ?>">
		<div class="estatein-property-search-main">
			<label class="screen-reader-text" for="estatein-property-keywords"><?php esc_html_e( 'Search For A Property', 'estatein' ); ?></label>
			<input id="estatein-property-keywords" type="search" placeholder="<?php esc_attr_e( 'Search For A Property', 'estatein' ); ?>" autocomplete="off">
			<button type="button" class="estatein-button estatein-button-primary"><span aria-hidden="true" class="estatein-search-icon"></span><?php esc_html_e( 'Find Property', 'estatein' ); ?></button>
		</div>
		<div class="estatein-property-search-filters">
			<?php foreach ( $filters as $filter_id => $filter_label ) : ?>
				<label class="estatein-property-filter" for="estatein-<?php echo esc_attr( $filter_id ); ?>">
					<span class="estatein-property-filter-icon" aria-hidden="true"><?php echo $icons[ $filter_id ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="screen-reader-text"><?php echo esc_html( $filter_label ); ?></span>
					<select id="estatein-<?php echo esc_attr( $filter_id ); ?>" aria-label="<?php echo esc_attr( $filter_label ); ?>">
						<option selected><?php echo esc_html( $filter_label ); ?></option>
					</select>
				</label>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'estatein_property_search_ui', 'estatein_property_search_ui_shortcode' );

/**
 * Render all published Property posts as the Properties Page slider.
 *
 * @param array $attributes Shortcode attributes.
 * @return string
 */
function estatein_property_archive_shortcode( $attributes ) {
	$attributes = shortcode_atts( array( 'limit' => 12 ), $attributes, 'estatein_property_archive' );
	$limit      = min( 24, max( 1, absint( $attributes['limit'] ) ) );
	$query      = new WP_Query(
		array(
			'post_type'           => 'property',
			'post_status'         => 'publish',
			'posts_per_page'      => $limit,
			'ignore_sticky_posts' => true,
			'meta_key'            => 'property_display_order',
			'orderby'             => array( 'meta_value_num' => 'ASC', 'date' => 'DESC' ),
		)
	);

	if ( ! $query->have_posts() ) {
		return '<p class="estatein-empty-state">' . esc_html__( 'No properties are available yet.', 'estatein' ) . '</p>';
	}

	$icon_uri = trailingslashit( ESTATEIN_THEME_URI . '/assets/icons' );
	ob_start();
	?>
	<div class="estatein-slider estatein-property-archive-slider" data-estatein-slider>
		<div class="estatein-property-archive-track" data-estatein-track tabindex="0" aria-label="<?php esc_attr_e( 'Available properties', 'estatein' ); ?>">
			<?php while ( $query->have_posts() ) : ?>
				<?php
				$query->the_post();
				$post_id     = get_the_ID();
				$image_id    = absint( get_post_meta( $post_id, 'property_card_image', true ) );
				$tagline     = (string) get_post_meta( $post_id, 'property_category_tagline', true );
				$description = (string) get_post_meta( $post_id, 'property_short_description', true );
				if ( '' === $tagline ) {
					$tagline = estatein_get_property_type_label( $post_id );
				}
				?>
				<article class="estatein-property-card estatein-property-archive-card" data-estatein-slide>
					<a class="estatein-property-image" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'View %s', 'estatein' ), get_the_title() ) ); ?>">
						<?php if ( $image_id ) : ?>
							<?php echo wp_get_attachment_image( $image_id, 'estatein-property-card', false, array( 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endif; ?>
					</a>
					<div class="estatein-property-card-body">
						<p class="estatein-property-tagline"><?php echo esc_html( $tagline ); ?></p>
						<h3 class="estatein-property-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="estatein-property-description"><?php echo esc_html( wp_trim_words( $description, 18, '...' ) ); ?> <a href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read More', 'estatein' ); ?></a></p>
						<div class="estatein-property-footer">
							<div class="estatein-property-price"><span><?php esc_html_e( 'Price', 'estatein' ); ?></span><strong><?php echo esc_html( estatein_format_property_price( $post_id ) ); ?></strong></div>
							<a class="estatein-button estatein-button-primary" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View Property Details', 'estatein' ); ?></a>
						</div>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<div class="estatein-slider-footer">
			<p class="estatein-slider-count" aria-live="polite"><strong data-estatein-current>01</strong> <span><?php echo esc_html( sprintf( __( 'of %02d', 'estatein' ), $query->post_count ) ); ?></span></p>
			<div class="estatein-slider-controls">
				<button type="button" class="estatein-slider-button" data-estatein-previous aria-label="<?php esc_attr_e( 'Previous property', 'estatein' ); ?>"><img src="<?php echo esc_url( $icon_uri . 'arrow-left.svg' ); ?>" alt=""></button>
				<button type="button" class="estatein-slider-button" data-estatein-next aria-label="<?php esc_attr_e( 'Next property', 'estatein' ); ?>"><img src="<?php echo esc_url( $icon_uri . 'arrow-right.svg' ); ?>" alt=""></button>
			</div>
		</div>
	</div>
	<?php
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'estatein_property_archive', 'estatein_property_archive_shortcode' );
