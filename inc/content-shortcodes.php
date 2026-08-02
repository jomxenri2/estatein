<?php
/**
 * Dynamic testimonial and FAQ cards for Elementor shortcode widgets.
 *
 * @package Estatein
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the shared slider footer.
 *
 * @param int    $count Total number of cards.
 * @param string $label Singular accessible label for the controls.
 * @return string
 */
function estatein_dynamic_slider_footer( $count, $label ) {
	$icon_uri = trailingslashit( ESTATEIN_THEME_URI . '/assets/icons' );

	ob_start();
	?>
	<div class="estatein-slider-footer">
		<p class="estatein-slider-count" aria-live="polite"><strong data-estatein-current>01</strong> <span><?php echo esc_html( sprintf( __( 'of %02d', 'estatein' ), $count ) ); ?></span></p>
		<div class="estatein-slider-controls">
			<button type="button" class="estatein-slider-button" data-estatein-previous aria-label="<?php echo esc_attr( sprintf( __( 'Previous %s', 'estatein' ), $label ) ); ?>"><img src="<?php echo esc_url( $icon_uri . 'arrow-left.svg' ); ?>" alt=""></button>
			<button type="button" class="estatein-slider-button" data-estatein-next aria-label="<?php echo esc_attr( sprintf( __( 'Next %s', 'estatein' ), $label ) ); ?>"><img src="<?php echo esc_url( $icon_uri . 'arrow-right.svg' ); ?>" alt=""></button>
		</div>
	</div>
	<?php

	return ob_get_clean();
}

/**
 * Render a row of testimonial rating stars.
 *
 * @param int $rating Rating from one to five.
 * @return string
 */
function estatein_testimonial_stars( $rating ) {
	$rating   = min( 5, max( 1, absint( $rating ) ) );
	$star_url = ESTATEIN_THEME_URI . '/assets/icons/testimonial-star.svg';

	ob_start();
	?>
	<div class="estatein-stars" aria-label="<?php echo esc_attr( sprintf( _n( '%d out of 5 star', '%d out of 5 stars', $rating, 'estatein' ), $rating ) ); ?>">
		<?php for ( $star = 0; $star < $rating; $star++ ) : ?>
			<span class="estatein-star"><img src="<?php echo esc_url( $star_url ); ?>" alt=""></span>
		<?php endfor; ?>
	</div>
	<?php

	return ob_get_clean();
}

/**
 * Render testimonials stored in the ACF-managed Testimonial post type.
 *
 * @param array $attributes Shortcode attributes.
 * @return string
 */
function estatein_testimonials_shortcode( $attributes ) {
	$attributes = shortcode_atts( array( 'limit' => 12 ), $attributes, 'estatein_testimonials' );
	$limit      = min( 24, max( 1, absint( $attributes['limit'] ) ) );
	$query      = new WP_Query(
		array(
			'post_type'           => 'testimonial',
			'post_status'         => 'publish',
			'posts_per_page'      => $limit,
			'ignore_sticky_posts' => true,
			'meta_query'          => array(
				array(
					'key'     => 'testimonial_show_on_homepage',
					'value'   => '1',
					'compare' => '=',
				),
			),
			'meta_key'            => 'testimonial_display_order',
			'orderby'             => array( 'meta_value_num' => 'ASC', 'date' => 'ASC' ),
		)
	);

	if ( ! $query->have_posts() ) {
		return '<p class="estatein-empty-state">' . esc_html__( 'No testimonials are available yet.', 'estatein' ) . '</p>';
	}

	ob_start();
	?>
	<div class="estatein-slider estatein-testimonial-slider" data-estatein-slider>
		<div class="estatein-testimonial-track" data-estatein-track tabindex="0" aria-label="<?php esc_attr_e( 'Client testimonials', 'estatein' ); ?>">
			<?php while ( $query->have_posts() ) : ?>
				<?php
				$query->the_post();
				$post_id  = get_the_ID();
				$headline = (string) get_post_meta( $post_id, 'testimonial_headline', true );
				$quote    = (string) get_post_meta( $post_id, 'testimonial_quote', true );
				$name     = (string) get_post_meta( $post_id, 'testimonial_client_name', true );
				$location = (string) get_post_meta( $post_id, 'testimonial_client_location', true );
				$photo_id = absint( get_post_meta( $post_id, 'testimonial_client_photo', true ) );
				$rating   = absint( get_post_meta( $post_id, 'testimonial_rating', true ) );
				?>
				<article class="estatein-testimonial-card" data-estatein-slide>
					<?php echo wp_kses_post( estatein_testimonial_stars( $rating ?: 5 ) ); ?>
					<h3 class="estatein-card-heading"><?php echo esc_html( $headline ?: get_the_title() ); ?></h3>
					<p class="estatein-card-copy"><?php echo esc_html( $quote ); ?></p>
					<div class="estatein-person">
						<?php
						if ( $photo_id ) {
							echo wp_get_attachment_image( $photo_id, 'thumbnail', false, array( 'class' => 'estatein-person-photo', 'loading' => 'lazy', 'decoding' => 'async' ) );
						}
						?>
						<div class="estatein-person-meta">
							<h4 class="estatein-person-name"><?php echo esc_html( $name ?: get_the_title() ); ?></h4>
							<p class="estatein-person-location"><?php echo esc_html( $location ); ?></p>
						</div>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<?php echo wp_kses_post( estatein_dynamic_slider_footer( $query->post_count, 'testimonial' ) ); ?>
	</div>
	<?php
	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode( 'estatein_testimonials', 'estatein_testimonials_shortcode' );

/**
 * Render FAQs stored in the ACF-managed FAQ post type.
 *
 * @param array $attributes Shortcode attributes.
 * @return string
 */
function estatein_faqs_shortcode( $attributes ) {
	$attributes = shortcode_atts( array( 'limit' => 12 ), $attributes, 'estatein_faqs' );
	$limit      = min( 24, max( 1, absint( $attributes['limit'] ) ) );
	$query      = new WP_Query(
		array(
			'post_type'           => 'faq',
			'post_status'         => 'publish',
			'posts_per_page'      => $limit,
			'ignore_sticky_posts' => true,
			'meta_query'          => array(
				array(
					'key'     => 'faq_show_on_homepage',
					'value'   => '1',
					'compare' => '=',
				),
			),
			'meta_key'            => 'faq_display_order',
			'orderby'             => array( 'meta_value_num' => 'ASC', 'date' => 'ASC' ),
		)
	);

	if ( ! $query->have_posts() ) {
		return '<p class="estatein-empty-state">' . esc_html__( 'No FAQs are available yet.', 'estatein' ) . '</p>';
	}

	ob_start();
	?>
	<div class="estatein-slider estatein-faq-slider" data-estatein-slider>
		<div class="estatein-faq-track" data-estatein-track tabindex="0" aria-label="<?php esc_attr_e( 'Frequently asked questions', 'estatein' ); ?>">
			<?php while ( $query->have_posts() ) : ?>
				<?php
				$query->the_post();
				$post_id  = get_the_ID();
				$question = (string) get_post_meta( $post_id, 'faq_question', true );
				$answer   = (string) get_post_meta( $post_id, 'faq_answer', true );
				$url      = (string) get_post_meta( $post_id, 'faq_read_more_url', true );
				?>
				<article class="estatein-faq-card" data-estatein-slide>
					<h3 class="estatein-card-heading"><?php echo esc_html( $question ?: get_the_title() ); ?></h3>
					<p class="estatein-card-copy"><?php echo esc_html( $answer ); ?></p>
					<div class="estatein-card-button"><a class="elementor-button" href="<?php echo esc_url( $url ?: home_url( '/#footer-newsletter' ) ); ?>"><?php esc_html_e( 'Read More', 'estatein' ); ?></a></div>
				</article>
			<?php endwhile; ?>
		</div>
		<?php echo wp_kses_post( estatein_dynamic_slider_footer( $query->post_count, 'question' ) ); ?>
	</div>
	<?php
	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode( 'estatein_faqs', 'estatein_faqs_shortcode' );

/**
 * Render an Elementor saved template from a shortcode widget.
 *
 * Elementor Free keeps the section editable in the Saved Templates screen,
 * while this shortcode lets multiple pages share the same live section.
 *
 * @param array $attributes Shortcode attributes.
 * @return string
 */
function estatein_elementor_template_shortcode( $attributes ) {
	$attributes  = shortcode_atts( array( 'id' => 0 ), $attributes, 'estatein_elementor_template' );
	$template_id = absint( $attributes['id'] );
	$template    = $template_id ? get_post( $template_id ) : null;

	if ( ! $template || 'elementor_library' !== $template->post_type || 'publish' !== $template->post_status ) {
		return '';
	}

	if ( ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance->frontend ) ) {
		return '';
	}

	return \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id, true );
}
add_shortcode( 'estatein_elementor_template', 'estatein_elementor_template_shortcode' );
