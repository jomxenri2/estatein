<?php
/**
 * Idempotently seed dynamic Home Page records and replace static Elementor cards.
 *
 * Run from the WordPress root with:
 * php wp-content/themes/estatein-hello-child/tools/migrate-dynamic-home.php
 *
 * @package Estatein
 */

if ( 'cli' !== PHP_SAPI ) {
	exit( "This migration can only run from the command line.\n" );
}

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

/**
 * Return an attachment ID by its media slug.
 *
 * @param string $slug Attachment slug.
 * @return int
 */
function estatein_migration_attachment_id( $slug ) {
	$attachment = get_page_by_path( $slug, OBJECT, 'attachment' );
	if ( $attachment ) {
		return (int) $attachment->ID;
	}

	$matches = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_wp_attached_file',
					'value'   => $slug,
					'compare' => 'LIKE',
				),
			),
		)
	);

	return $matches ? (int) $matches[0] : 0;
}

/**
 * Create or update a post and its ACF values.
 *
 * @param string $post_type Post type.
 * @param string $slug      Post slug.
 * @param string $title     Post title.
 * @param array  $fields    ACF field keys mapped to values.
 * @return int
 */
function estatein_migration_upsert( $post_type, $slug, $title, $fields ) {
	$existing = get_page_by_path( $slug, OBJECT, $post_type );
	$postarr  = array(
		'post_type'   => $post_type,
		'post_status' => 'publish',
		'post_title'  => $title,
		'post_name'   => $slug,
	);

	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$post_id       = wp_update_post( $postarr, true );
	} else {
		$post_id = wp_insert_post( $postarr, true );
	}

	if ( is_wp_error( $post_id ) ) {
		throw new RuntimeException( $post_id->get_error_message() );
	}

	foreach ( $fields as $field_key => $value ) {
		if ( function_exists( 'update_field' ) ) {
			update_field( $field_key, $value, $post_id );
		} else {
			update_post_meta( $post_id, $field_key, $value );
		}
	}

	return (int) $post_id;
}

$avatar_ids = array(
	estatein_migration_attachment_id( 'testimonial-avatar-01' ),
	estatein_migration_attachment_id( 'testimonial-avatar-02' ),
	estatein_migration_attachment_id( 'testimonial-avatar-03' ),
);

$testimonials = array(
	array( 'exceptional-service', 'Wade Warren — Exceptional Service', 'Exceptional Service!', 'Our experience with Estatein was outstanding. Their team\'s dedication and professionalism made finding our dream home a breeze. Highly recommended!', 'Wade Warren', 'USA, California', $avatar_ids[0], 1 ),
	array( 'efficient-and-reliable', 'Emelie Thomson — Efficient and Reliable', 'Efficient and Reliable', 'Estatein provided us with top-notch service. They helped us sell our property quickly and at a great price. We couldn\'t be happier with the results.', 'Emelie Thomson', 'USA, Florida', $avatar_ids[1], 2 ),
	array( 'trusted-advisors', 'John Mans — Trusted Advisors', 'Trusted Advisors', 'The Estatein team guided us through the entire buying process. Their knowledge and commitment to our needs were impressive. Thank you for your support!', 'John Mans', 'USA, Nevada', $avatar_ids[2], 3 ),
	array( 'seamless-property-journey', 'Sophia Turner — Seamless Property Journey', 'Seamless Property Journey', 'From our first consultation through closing, Estatein kept every detail clear and on schedule. The entire experience felt simple and well supported.', 'Sophia Turner', 'USA, Texas', $avatar_ids[0], 4 ),
);

$testimonial_field_keys = array(
	'headline' => 'field_6a6faeccb5120',
	'quote'    => 'field_6a6faedbb5121',
	'name'     => 'field_6a6faeecb5122',
	'location' => 'field_6a6faef4b5123',
	'photo'    => 'field_6a6faef4b5124',
	'rating'   => 'field_6a6faf0cb5125',
	'show'     => 'field_6a6faf2ab5126',
	'order'    => 'field_6a6faf33b5127',
);

foreach ( $testimonials as $testimonial ) {
	estatein_migration_upsert(
		'testimonial',
		$testimonial[0],
		$testimonial[1],
		array(
			$testimonial_field_keys['headline'] => $testimonial[2],
			$testimonial_field_keys['quote']    => $testimonial[3],
			$testimonial_field_keys['name']     => $testimonial[4],
			$testimonial_field_keys['location'] => $testimonial[5],
			$testimonial_field_keys['photo']    => $testimonial[6],
			$testimonial_field_keys['rating']   => 5,
			$testimonial_field_keys['show']     => 1,
			$testimonial_field_keys['order']    => $testimonial[7],
		)
	);
}

$faqs = array(
	array( 'search-for-properties', 'How do I search for properties on Estatein?', 'Learn how to use our user-friendly search tools to find properties that match your criteria.', 1 ),
	array( 'documents-to-sell-property', 'What documents do I need to sell my property through Estatein?', 'Find out about the necessary documentation for listing your property with us.', 2 ),
	array( 'contact-an-estatein-agent', 'How can I contact an Estatein agent?', 'Discover the different ways you can get in touch with our experienced agents.', 3 ),
	array( 'schedule-a-property-viewing', 'How do I schedule a property viewing?', 'Choose a convenient viewing time and connect with an Estatein agent who can guide you through the property.', 4 ),
);

$faq_field_keys = array(
	'question' => 'field_6a6fadd081bb5',
	'answer'   => 'field_6a6fae1d81bb6',
	'url'      => 'field_6a6fae2781bb7',
	'show'     => 'field_6a6fae3081bb8',
	'order'    => 'field_6a6fae6081bb9',
);

foreach ( $faqs as $faq ) {
	estatein_migration_upsert(
		'faq',
		$faq[0],
		$faq[1],
		array(
			$faq_field_keys['question'] => $faq[1],
			$faq_field_keys['answer']   => $faq[2],
			$faq_field_keys['url']      => home_url( '/#footer-newsletter' ),
			$faq_field_keys['show']     => 1,
			$faq_field_keys['order']    => $faq[3],
		)
	);
}

// Remove malformed meta keys created during the initial ACF field-name correction.
$legacy_meta = array(
	'testimonial' => array(
		'headlinetestimonial_headline',
		'testimonialtestimonial_quote',
		'client_nametestimonial_client_name',
		'client_locationtestimonial_client_location',
		'client_phototestimonial_client_photo',
		'ratingtestimonial_rating',
		'show_on_homepagetestimonial_show_on_homepage',
		'display_ordertestimonial_display_order',
	),
	'faq'         => array(
		'questionfaq_question',
		'display_orderfaq_display_order',
	),
);

foreach ( $legacy_meta as $post_type => $meta_keys ) {
	$post_ids = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	foreach ( $post_ids as $post_id ) {
		foreach ( $meta_keys as $meta_key ) {
			delete_post_meta( $post_id, $meta_key );
			delete_post_meta( $post_id, '_' . $meta_key );
		}
	}
}

$property_image_id = estatein_migration_attachment_id( 'property-01' );
$property_id       = estatein_migration_upsert(
	'property',
	'coastal-elegance-residence',
	'Coastal Elegance Residence',
	array(
		'property_card_image'        => $property_image_id,
		'property_short_description' => 'A bright 3-bedroom, 2-bathroom residence offering relaxed coastal living and modern comfort.',
		'property_price'             => 625000,
		'property_currency'          => 'USD',
		'property_bedrooms'          => 3,
		'property_bathrooms'         => 2,
		'property_area'              => 1950,
		'property_area_unit'         => 'sq ft',
		'featured_property'          => 1,
		'property_display_order'     => 4,
	)
);

if ( $property_image_id ) {
	set_post_thumbnail( $property_id, $property_image_id );
}

$villa = term_exists( 'Villa', 'property_type' );
if ( ! $villa ) {
	$villa = wp_insert_term( 'Villa', 'property_type' );
}
if ( ! is_wp_error( $villa ) ) {
	wp_set_object_terms( $property_id, array( (int) ( is_array( $villa ) ? $villa['term_id'] : $villa ) ), 'property_type' );
}

$location = term_exists( 'Santa Monica, California', 'property_location' );
if ( ! $location ) {
	$location = wp_insert_term( 'Santa Monica, California', 'property_location' );
}
if ( ! is_wp_error( $location ) ) {
	wp_set_object_terms( $property_id, array( (int) ( is_array( $location ) ? $location['term_id'] : $location ) ), 'property_location' );
}

/**
 * Replace the static Elementor cards with dynamic shortcode widgets.
 *
 * @param array $elements Elementor elements.
 * @return array
 */
function estatein_migration_dynamic_sections( $elements ) {
	foreach ( $elements as &$element ) {
		if ( isset( $element['widgetType'], $element['settings']['shortcode'] ) && 'shortcode' === $element['widgetType'] && false !== strpos( $element['settings']['shortcode'], 'estatein_featured_properties' ) ) {
			$element['settings']['shortcode'] = '[estatein_featured_properties limit="12"]';
		}

		$anchor = isset( $element['settings']['_element_id'] ) ? $element['settings']['_element_id'] : '';
		if ( in_array( $anchor, array( 'testimonials', 'faqs' ), true ) && ! empty( $element['elements'][0]['elements'][0] ) ) {
			$shortcode = 'testimonials' === $anchor ? '[estatein_testimonials limit="12"]' : '[estatein_faqs limit="12"]';
			$element['settings']['css_classes'] = 'estatein-section';
			unset( $element['settings']['attributes'] );
			$element['elements'][0]['elements'] = array(
				$element['elements'][0]['elements'][0],
				array(
					'id'         => substr( md5( 'estatein-dynamic-' . $anchor ), 0, 8 ),
					'elType'     => 'widget',
					'widgetType' => 'shortcode',
					'settings'   => array( 'shortcode' => $shortcode ),
					'elements'   => array(),
				),
			);
		}

		if ( ! empty( $element['elements'] ) ) {
			$element['elements'] = estatein_migration_dynamic_sections( $element['elements'] );
		}
	}
	unset( $element );

	return $elements;
}

$home_id = (int) get_option( 'page_on_front' );
$data    = json_decode( (string) get_post_meta( $home_id, '_elementor_data', true ), true );

if ( $home_id && is_array( $data ) ) {
	$data = estatein_migration_dynamic_sections( $data );
	update_post_meta( $home_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
	wp_update_post( array( 'ID' => $home_id ) );
}

if ( class_exists( '\\Elementor\\Plugin' ) ) {
	\Elementor\Plugin::$instance->files_manager->clear_cache();
}

wp_cache_flush();

printf(
	"Dynamic Home migration complete: %d testimonials, %d FAQs, %d featured properties.\n",
	(int) wp_count_posts( 'testimonial' )->publish,
	(int) wp_count_posts( 'faq' )->publish,
	(int) ( new WP_Query( array( 'post_type' => 'property', 'post_status' => 'publish', 'posts_per_page' => -1, 'meta_key' => 'featured_property', 'meta_value' => '1', 'fields' => 'ids' ) ) )->found_posts
);
