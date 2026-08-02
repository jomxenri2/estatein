<?php
/**
 * Dynamic featured-property cards for Elementor's Shortcode widget.
 *
 * @package Estatein
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Format a property price safely.
 *
 * @param int $post_id Property post ID.
 * @return string
 */
function estatein_format_property_price( $post_id ) {
	$amount   = (float) get_post_meta( $post_id, 'property_price', true );
	$currency = (string) get_post_meta( $post_id, 'property_currency', true );
	$symbols  = array( 'USD' => '$', 'EUR' => '€', 'GBP' => '£' );
	$symbol   = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : $currency . ' ';

	return $symbol . number_format_i18n( $amount, 0 );
}

/**
 * Get the first property type label.
 *
 * @param int $post_id Property post ID.
 * @return string
 */
function estatein_get_property_type_label( $post_id ) {
	$terms = get_the_terms( $post_id, 'property_type' );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return __( 'Property', 'estatein' );
	}

	return $terms[0]->name;
}

/**
 * Render featured properties.
 *
 * @param array $attributes Shortcode attributes.
 * @return string
 */
function estatein_featured_properties_shortcode( $attributes ) {
	$attributes = shortcode_atts( array( 'limit' => 12 ), $attributes, 'estatein_featured_properties' );
	$limit      = min( 12, max( 1, absint( $attributes['limit'] ) ) );

	$query = new WP_Query(
		array(
			'post_type'           => 'property',
			'post_status'         => 'publish',
			'posts_per_page'      => $limit,
			'ignore_sticky_posts' => true,
			'meta_query'          => array(
				array(
					'key'     => 'featured_property',
					'value'   => '1',
					'compare' => '=',
				),
			),
			'meta_key'            => 'property_display_order',
			'orderby'             => array( 'meta_value_num' => 'ASC', 'date' => 'DESC' ),
		)
	);

	if ( ! $query->have_posts() ) {
		return '<p class="estatein-empty-state">' . esc_html__( 'No featured properties are available yet.', 'estatein' ) . '</p>';
	}

	$icon_uri = trailingslashit( ESTATEIN_THEME_URI . '/assets/icons' );
	ob_start();
	?>
	<div class="estatein-slider estatein-property-slider" data-estatein-slider>
		<div class="estatein-property-grid estatein-card-track" data-estatein-track tabindex="0" aria-label="<?php esc_attr_e( 'Featured properties', 'estatein' ); ?>">
			<?php while ( $query->have_posts() ) : ?>
				<?php
				$query->the_post();
				$post_id     = get_the_ID();
				$image_id    = absint( get_post_meta( $post_id, 'property_card_image', true ) );
				$description = (string) get_post_meta( $post_id, 'property_short_description', true );
				$bedrooms    = (string) get_post_meta( $post_id, 'property_bedrooms', true );
				$bathrooms   = (string) get_post_meta( $post_id, 'property_bathrooms', true );
				$type_label  = estatein_get_property_type_label( $post_id );
				?>
				<article class="estatein-property-card" data-estatein-slide>
					<a class="estatein-property-image" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'View %s', 'estatein' ), get_the_title() ) ); ?>">
						<?php
						if ( $image_id ) {
							echo wp_get_attachment_image(
								$image_id,
								'estatein-property-card',
								false,
								array( 'loading' => 'lazy', 'decoding' => 'async' )
							);
						}
						?>
					</a>
					<div class="estatein-property-card-body">
						<h3 class="estatein-property-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="estatein-property-description"><?php echo esc_html( $description ); ?> <a href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read More', 'estatein' ); ?></a></p>
						<ul class="estatein-property-meta" aria-label="<?php esc_attr_e( 'Property details', 'estatein' ); ?>">
							<li><img src="<?php echo esc_url( $icon_uri . 'bedrooms.svg' ); ?>" alt="" width="20" height="20"><span><?php echo esc_html( $bedrooms . '-Bedroom' ); ?></span></li>
							<li><img src="<?php echo esc_url( $icon_uri . 'bathrooms.svg' ); ?>" alt="" width="20" height="20"><span><?php echo esc_html( $bathrooms . '-Bathroom' ); ?></span></li>
							<li><span aria-hidden="true">▥</span><span><?php echo esc_html( $type_label ); ?></span></li>
						</ul>
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
add_shortcode( 'estatein_featured_properties', 'estatein_featured_properties_shortcode' );
