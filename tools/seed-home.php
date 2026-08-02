<?php
/**
 * One-time, idempotent Home Page content seeder.
 *
 * Run with:
 * php wp-cli.phar eval-file wp-content/themes/estatein-hello-child/tools/seed-home.php
 *
 * This file is intentionally not loaded by the theme. It creates editable WordPress,
 * ACF, Contact Form 7, menu, and Elementor records for the initial local build.
 *
 * @package Estatein
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	wp_die( 'This provisioning file must be run through WP-CLI.' );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/nav-menu.php';

/**
 * Return a deterministic Elementor element ID.
 *
 * @param string $key Seed key.
 * @return string
 */
function estatein_seed_element_id( $key ) {
	return substr( md5( 'estatein-' . $key ), 0, 8 );
}

/**
 * Create an Elementor container.
 *
 * @param string $key      Seed key.
 * @param string $classes  CSS classes.
 * @param array  $elements Child elements.
 * @param array  $settings Additional settings.
 * @return array
 */
function estatein_seed_container( $key, $classes, $elements = array(), $settings = array() ) {
	return array(
		'id'       => estatein_seed_element_id( $key ),
		'elType'   => 'container',
		'settings' => array_merge(
			array(
				'css_classes'  => $classes,
				'content_width' => 'full',
			),
			$settings
		),
		'elements' => $elements,
	);
}

/**
 * Create an Elementor widget.
 *
 * @param string $key         Seed key.
 * @param string $widget_type Widget type.
 * @param array  $settings    Widget settings.
 * @return array
 */
function estatein_seed_widget( $key, $widget_type, $settings ) {
	return array(
		'id'         => estatein_seed_element_id( $key ),
		'elType'     => 'widget',
		'widgetType' => $widget_type,
		'settings'   => $settings,
		'elements'   => array(),
	);
}

/**
 * Create a heading widget.
 *
 * @param string $key     Seed key.
 * @param string $title   Heading content.
 * @param string $tag     HTML heading tag.
 * @param string $classes CSS classes.
 * @return array
 */
function estatein_seed_heading( $key, $title, $tag = 'h2', $classes = '' ) {
	return estatein_seed_widget(
		$key,
		'heading',
		array(
			'title'        => $title,
			'header_size'  => $tag,
			'_css_classes' => $classes,
		)
	);
}

/**
 * Create a text-editor widget.
 *
 * @param string $key     Seed key.
 * @param string $content HTML content.
 * @param string $classes CSS classes.
 * @return array
 */
function estatein_seed_text( $key, $content, $classes = '' ) {
	return estatein_seed_widget(
		$key,
		'text-editor',
		array(
			'editor'       => $content,
			'_css_classes' => $classes,
		)
	);
}

/**
 * Create a button widget.
 *
 * @param string $key     Seed key.
 * @param string $text    Button label.
 * @param string $url     Link URL.
 * @param string $classes CSS classes.
 * @return array
 */
function estatein_seed_button( $key, $text, $url, $classes = '' ) {
	return estatein_seed_widget(
		$key,
		'button',
		array(
			'text'         => $text,
			'link'         => array( 'url' => $url ),
			'_css_classes' => $classes,
		)
	);
}

/**
 * Create an image widget.
 *
 * @param string $key           Seed key.
 * @param string $url           Image URL.
 * @param int    $attachment_id Media ID, when available.
 * @param string $classes       CSS classes.
 * @return array
 */
function estatein_seed_image( $key, $url, $attachment_id = 0, $classes = '' ) {
	return estatein_seed_widget(
		$key,
		'image',
		array(
			'image'        => array(
				'url' => $url,
				'id'  => $attachment_id,
			),
			'image_size'   => 'full',
			'_css_classes' => $classes,
		)
	);
}

/**
 * Create a decorative SVG image in an HTML widget.
 *
 * Theme-bundled SVGs do not have Media Library IDs. Using an HTML widget avoids
 * asking Elementor's attachment image manager to resolve a non-existent ID.
 *
 * @param string $key     Seed key.
 * @param string $url     SVG URL.
 * @param string $classes CSS classes.
 * @return array
 */
function estatein_seed_svg( $key, $url, $classes = '' ) {
	return estatein_seed_widget(
		$key,
		'html',
		array(
			'html'         => '<img src="' . esc_url( $url ) . '" alt="">',
			'_css_classes' => $classes,
		)
	);
}

/**
 * Find or import a local theme image into the Media Library.
 *
 * @param string $key           Unique import key.
 * @param string $relative_path Theme-relative source path.
 * @param string $title         Media title.
 * @param string $alt           Alternative text.
 * @return int
 */
function estatein_seed_media( $key, $relative_path, $title, $alt ) {
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_estatein_seed_key',
			'meta_value'     => $key,
		)
	);

	if ( $existing ) {
		return (int) $existing[0];
	}

	$source = get_stylesheet_directory() . '/' . ltrim( $relative_path, '/' );
	if ( ! is_readable( $source ) ) {
		WP_CLI::warning( 'Missing seed image: ' . $source );
		return 0;
	}

	$temp_file = wp_tempnam( basename( $source ) );
	if ( ! $temp_file || ! copy( $source, $temp_file ) ) {
		WP_CLI::warning( 'Could not stage seed image: ' . $source );
		return 0;
	}

	$file_array = array(
		'name'     => basename( $source ),
		'tmp_name' => $temp_file,
	);
	$media_id   = media_handle_sideload( $file_array, 0, $title );

	if ( is_wp_error( $media_id ) ) {
		@unlink( $temp_file );
		WP_CLI::warning( $media_id->get_error_message() );
		return 0;
	}

	update_post_meta( $media_id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $media_id, '_estatein_seed_key', $key );
	return (int) $media_id;
}

/**
 * Ensure a property taxonomy term exists.
 *
 * @param string $name Term name.
 * @param string $taxonomy Taxonomy name.
 * @return int
 */
function estatein_seed_term( $name, $taxonomy ) {
	$term = term_exists( $name, $taxonomy );
	if ( ! $term ) {
		$term = wp_insert_term( $name, $taxonomy );
	}

	if ( is_wp_error( $term ) ) {
		WP_CLI::warning( $term->get_error_message() );
		return 0;
	}

	return (int) ( is_array( $term ) ? $term['term_id'] : $term );
}

/**
 * Create or update a sample property without duplicating it.
 *
 * @param array $property Property record.
 * @return int
 */
function estatein_seed_property( $property ) {
	$existing = get_page_by_path( $property['slug'], OBJECT, 'property' );
	$postarr  = array(
		'post_type'    => 'property',
		'post_status'  => 'publish',
		'post_title'   => $property['title'],
		'post_name'    => $property['slug'],
		'post_excerpt' => $property['description'],
		'post_content' => '<p>' . esc_html( $property['description'] ) . '</p>',
	);

	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$post_id       = wp_update_post( $postarr, true );
	} else {
		$post_id = wp_insert_post( $postarr, true );
	}

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( $post_id->get_error_message() );
		return 0;
	}

	$meta = array(
		'property_card_image'       => $property['image_id'],
		'property_short_description' => $property['description'],
		'property_price'             => $property['price'],
		'property_currency'          => 'USD',
		'property_bedrooms'          => $property['bedrooms'],
		'property_bathrooms'         => $property['bathrooms'],
		'property_area'              => $property['area'],
		'property_area_unit'         => 'sq ft',
		'featured_property'          => 1,
		'property_display_order'     => $property['order'],
	);

	foreach ( $meta as $key => $value ) {
		update_post_meta( $post_id, $key, $value );
	}

	if ( $property['image_id'] ) {
		set_post_thumbnail( $post_id, $property['image_id'] );
	}

	$type_id = estatein_seed_term( $property['type'], 'property_type' );
	if ( $type_id ) {
		wp_set_object_terms( $post_id, array( $type_id ), 'property_type' );
	}

	$location_id = estatein_seed_term( $property['location'], 'property_location' );
	if ( $location_id ) {
		wp_set_object_terms( $post_id, array( $location_id ), 'property_location' );
	}

	return (int) $post_id;
}

/**
 * Add an editable navigation menu and its links if needed.
 *
 * @param string $name  Menu name.
 * @param array  $items Menu item labels mapped to URLs.
 * @return int
 */
function estatein_seed_menu( $name, $items ) {
	$menu = wp_get_nav_menu_object( $name );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $name );
	} else {
		$menu_id = (int) $menu->term_id;
	}

	$existing_items  = wp_get_nav_menu_items( $menu_id );
	$existing_titles = array();
	foreach ( (array) $existing_items as $item ) {
		$existing_titles[] = $item->title;
	}

	foreach ( $items as $title => $url ) {
		if ( in_array( $title, $existing_titles, true ) ) {
			continue;
		}
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'  => $title,
				'menu-item-url'    => $url,
				'menu-item-status' => 'publish',
				'menu-item-type'   => 'custom',
			)
		);
	}

	return (int) $menu_id;
}

/**
 * Build the shared section header used across Home Page sections.
 *
 * @param string $key         Seed key.
 * @param string $title       Heading.
 * @param string $description Description.
 * @param string $button      Optional button label.
 * @param string $url         Optional button URL.
 * @return array
 */
function estatein_seed_section_header( $key, $title, $description, $button = '', $url = '' ) {
	$elements = array(
		estatein_seed_container(
			$key . '-copy',
			'estatein-section-copy',
			array(
				estatein_seed_heading( $key . '-title', $title, 'h2', 'estatein-section-title' ),
				estatein_seed_text( $key . '-description', '<p>' . esc_html( $description ) . '</p>', 'estatein-section-description' ),
			)
		),
	);

	if ( $button && $url ) {
		$elements[] = estatein_seed_button( $key . '-action', $button, $url, 'estatein-section-action' );
	}

	return estatein_seed_container( $key . '-header', 'estatein-section-header', $elements );
}

/**
 * Build the testimonial star row as decorative HTML.
 *
 * @return string
 */
function estatein_seed_stars_html() {
	$star = esc_url( get_stylesheet_directory_uri() . '/assets/icons/testimonial-star.svg' );
	$html = '<div class="estatein-stars" aria-label="5 out of 5 stars">';
	for ( $i = 0; $i < 5; $i++ ) {
		$html .= '<span class="estatein-star"><img src="' . $star . '" alt=""></span>';
	}
	return $html . '</div>';
}

/**
 * Build accessible static slider controls.
 *
 * @param int    $count Total slides.
 * @param string $label Item label.
 * @return string
 */
function estatein_seed_slider_controls( $count, $label ) {
	$icon_uri = trailingslashit( get_stylesheet_directory_uri() . '/assets/icons' );
	return '<div class="estatein-static-slider-footer"><p class="estatein-slider-count" aria-live="polite"><strong data-estatein-current>01</strong> <span>of ' . esc_html( sprintf( '%02d', $count ) ) . '</span></p><div class="estatein-slider-controls"><button type="button" class="estatein-slider-button" data-estatein-previous aria-label="Previous ' . esc_attr( $label ) . '"><img src="' . esc_url( $icon_uri . 'arrow-left.svg' ) . '" alt=""></button><button type="button" class="estatein-slider-button" data-estatein-next aria-label="Next ' . esc_attr( $label ) . '"><img src="' . esc_url( $icon_uri . 'arrow-right.svg' ) . '" alt=""></button></div></div>';
}

$media = array(
	'hero'       => estatein_seed_media( 'hero-building', 'assets/images/hero-building.png', 'Estatein hero building', 'Modern high-rise residential building at dusk' ),
	'property-1' => estatein_seed_media( 'property-01', 'assets/images/property-01.png', 'Seaside Serenity Villa', 'Contemporary villa with an infinity pool at sunset' ),
	'property-2' => estatein_seed_media( 'property-02', 'assets/images/property-02.png', 'Metropolitan Haven', 'Modern city residence with skyline views' ),
	'property-3' => estatein_seed_media( 'property-03', 'assets/images/property-03.png', 'Rustic Retreat Cottage', 'Warm countryside cottage surrounded by trees' ),
	'avatar-1'   => estatein_seed_media( 'testimonial-avatar-01', 'assets/images/testimonial-avatar-01.png', 'Wade Warren', 'Portrait of Wade Warren' ),
	'avatar-2'   => estatein_seed_media( 'testimonial-avatar-02', 'assets/images/testimonial-avatar-02.png', 'Emelie Thomson', 'Portrait of Emelie Thomson' ),
	'avatar-3'   => estatein_seed_media( 'testimonial-avatar-03', 'assets/images/testimonial-avatar-03.png', 'John Mans', 'Portrait of John Mans' ),
);

$gallery_keys = array(
	'gallery-1' => array( 'assets/images/property-big-01.png', 'Property gallery exterior', 'Large contemporary home exterior' ),
	'gallery-2' => array( 'assets/images/property-big-02.png', 'Property gallery pool', 'Luxury home and pool at dusk' ),
	'gallery-3' => array( 'assets/images/explore-01.png', 'Property gallery living room', 'Bright modern living room' ),
	'gallery-4' => array( 'assets/images/explore-02.png', 'Property gallery bedroom', 'Calm modern bedroom' ),
	'gallery-5' => array( 'assets/images/explore-03.png', 'Property gallery kitchen', 'Contemporary fitted kitchen' ),
	'gallery-6' => array( 'assets/images/explore-04.png', 'Property gallery lounge', 'Comfortable residential lounge' ),
	'gallery-7' => array( 'assets/images/explore-05.png', 'Property gallery interior', 'Modern property interior' ),
	'gallery-8' => array( 'assets/images/explore-06.png', 'Property gallery detail', 'Architectural property detail' ),
);

foreach ( $gallery_keys as $key => $gallery ) {
	$media[ $key ] = estatein_seed_media( $key, $gallery[0], $gallery[1], $gallery[2] );
}

$properties = array(
	array(
		'title'       => 'Seaside Serenity Villa',
		'slug'        => 'seaside-serenity-villa',
		'description' => 'A stunning 4-bedroom, 3-bathroom villa in a peaceful suburban neighborhood.',
		'price'       => 550000,
		'bedrooms'    => 4,
		'bathrooms'   => 3,
		'area'        => 2500,
		'type'        => 'Villa',
		'location'    => 'Malibu, California',
		'order'       => 1,
		'image_id'    => $media['property-1'],
	),
	array(
		'title'       => 'Metropolitan Haven',
		'slug'        => 'metropolitan-haven',
		'description' => 'A chic and fully-furnished 2-bedroom apartment with panoramic city views.',
		'price'       => 550000,
		'bedrooms'    => 2,
		'bathrooms'   => 2,
		'area'        => 1600,
		'type'        => 'Villa',
		'location'    => 'Manhattan, New York',
		'order'       => 2,
		'image_id'    => $media['property-2'],
	),
	array(
		'title'       => 'Rustic Retreat Cottage',
		'slug'        => 'rustic-retreat-cottage',
		'description' => 'An elegant 3-bedroom, 2.5-bathroom townhouse in a gated community.',
		'price'       => 550000,
		'bedrooms'    => 3,
		'bathrooms'   => 3,
		'area'        => 2100,
		'type'        => 'Villa',
		'location'    => 'Aspen, Colorado',
		'order'       => 3,
		'image_id'    => $media['property-3'],
	),
);

$property_ids = array();
foreach ( $properties as $property ) {
	$property_ids[] = estatein_seed_property( $property );
}

if ( ! empty( $property_ids[0] ) ) {
	for ( $i = 1; $i <= 8; $i++ ) {
		update_post_meta( $property_ids[0], 'property_gallery_image_' . $i, $media[ 'gallery-' . $i ] );
	}
}

// Create an editable Contact Form 7 newsletter form.
$newsletter_id = 0;
if ( class_exists( 'WPCF7_ContactForm' ) ) {
	$existing_forms = get_posts(
		array(
			'post_type'      => 'wpcf7_contact_form',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'title'          => 'Footer Newsletter',
		)
	);

	if ( $existing_forms ) {
		$form = WPCF7_ContactForm::get_instance( $existing_forms[0]->ID );
	} else {
		$form = WPCF7_ContactForm::get_template(
			array(
				'title'  => 'Footer Newsletter',
				'locale' => get_locale(),
			)
		);
	}

	$properties_form         = $form->get_properties();
	$properties_form['form'] = '<label class="screen-reader-text" for="estatein-newsletter-email">Your email</label>[email* subscriber-email id:estatein-newsletter-email autocomplete:email placeholder "Enter Your Email"][submit "Subscribe"]';
	$mail                    = $properties_form['mail'];
	$mail['subject']         = '[_site_title] New newsletter subscriber';
	$mail['body']            = "A visitor subscribed to the Estatein newsletter.\n\nEmail: [subscriber-email]\n\nSource: [_url]";
	$mail['additional_headers'] = 'Reply-To: [subscriber-email]';
	$properties_form['mail'] = $mail;
	$form->set_properties( $properties_form );
	$newsletter_id = (int) $form->save();
	update_option( 'estatein_newsletter_form_id', $newsletter_id );
}

$hero_url = $media['hero'] ? wp_get_attachment_image_url( $media['hero'], 'full' ) : get_stylesheet_directory_uri() . '/assets/images/hero-building.png';
$icon_url = trailingslashit( get_stylesheet_directory_uri() . '/assets/icons' );

$stats = array(
	array( '200+', 'Happy Customers' ),
	array( '10k+', 'Properties For Clients' ),
	array( '16+', 'Years of Experience' ),
);
$stat_elements = array();
foreach ( $stats as $index => $stat ) {
	$stat_elements[] = estatein_seed_container(
		'stat-' . $index,
		'estatein-stat-card',
		array(
			estatein_seed_heading( 'stat-number-' . $index, $stat[0], 'h3', 'estatein-stat-number' ),
			estatein_seed_text( 'stat-label-' . $index, '<p>' . esc_html( $stat[1] ) . '</p>', 'estatein-stat-label' ),
		)
	);
}

$hero = estatein_seed_container(
	'hero',
	'estatein-hero',
	array(
		estatein_seed_container(
			'hero-shell',
			'estatein-hero-shell',
			array(
				estatein_seed_container(
					'hero-content',
					'estatein-hero-content',
					array(
						estatein_seed_heading( 'hero-title', 'Discover Your Dream Property with Estatein', 'h1' ),
						estatein_seed_text( 'hero-copy', '<p>Your journey to finding the perfect property begins here. Explore our listings to find the home that matches your dreams.</p>', 'estatein-hero-copy' ),
						estatein_seed_container(
							'hero-buttons',
							'estatein-hero-buttons',
							array(
								estatein_seed_button( 'hero-learn', 'Learn More', '#services' ),
								estatein_seed_button( 'hero-browse', 'Browse Properties', '#featured-properties', 'estatein-primary-button' ),
							)
						),
						estatein_seed_container( 'hero-stats', 'estatein-stats', $stat_elements ),
					)
				),
				estatein_seed_container(
					'hero-visual',
					'estatein-hero-visual',
					array(
						estatein_seed_image( 'hero-image', $hero_url, $media['hero'], 'estatein-hero-image' ),
						estatein_seed_svg( 'discover-badge', $icon_url . 'discover-badge.png', 'estatein-discover-badge' ),
					)
				),
			)
		),
	),
	array( '_element_id' => 'home' )
);

$services_data = array(
	array( 'Find Your Dream Home', 'feature-home.svg' ),
	array( 'Unlock Property Value', 'feature-value.svg' ),
	array( 'Effortless Property Management', 'feature-management.svg' ),
	array( 'Smart Investments, Informed Decisions', 'feature-investment.svg' ),
);
$service_elements = array();
foreach ( $services_data as $index => $service ) {
	$service_elements[] = estatein_seed_container(
		'service-' . $index,
		'estatein-service-card',
		array(
			estatein_seed_svg( 'service-icon-' . $index, $icon_url . $service[1], 'estatein-service-icon' ),
			estatein_seed_heading( 'service-title-' . $index, $service[0], 'h3' ),
		)
	);
}
$services = estatein_seed_container(
	'services',
	'estatein-services',
	array( estatein_seed_container( 'services-grid', 'estatein-services-grid', $service_elements ) ),
	array( '_element_id' => 'services' )
);

$featured = estatein_seed_container(
	'featured',
	'estatein-section',
	array(
		estatein_seed_container(
			'featured-shell',
			'estatein-section-shell',
			array(
				estatein_seed_section_header( 'featured', 'Featured Properties', 'Explore our handpicked selection of featured properties. Each listing offers a glimpse into exceptional homes and investments available through Estatein. Click "View Details" for more information.', 'View All Properties', '#featured-properties' ),
				estatein_seed_widget( 'featured-shortcode', 'shortcode', array( 'shortcode' => '[estatein_featured_properties limit="12"]' ) ),
			)
		),
	),
	array( '_element_id' => 'featured-properties' )
);

$testimonial_data = array(
	array( 'Exceptional Service!', 'Our experience with Estatein was outstanding. Their team\'s dedication and professionalism made finding our dream home a breeze. Highly recommended!', 'Wade Warren', 'USA, California', 'avatar-1' ),
	array( 'Efficient and Reliable', 'Estatein provided us with top-notch service. They helped us sell our property quickly and at a great price. We couldn\'t be happier with the results.', 'Emelie Thomson', 'USA, Florida', 'avatar-2' ),
	array( 'Trusted Advisors', 'The Estatein team guided us through the entire buying process. Their knowledge and commitment to our needs were impressive. Thank you for your support!', 'John Mans', 'USA, Nevada', 'avatar-3' ),
);
$testimonial_cards = array();
foreach ( $testimonial_data as $index => $testimonial ) {
	$avatar_url = wp_get_attachment_image_url( $media[ $testimonial[4] ], 'thumbnail' );
	$testimonial_cards[] = estatein_seed_container(
		'testimonial-card-' . $index,
		'estatein-testimonial-card',
		array(
			estatein_seed_widget( 'testimonial-stars-' . $index, 'html', array( 'html' => estatein_seed_stars_html() ) ),
			estatein_seed_heading( 'testimonial-title-' . $index, $testimonial[0], 'h3' ),
			estatein_seed_text( 'testimonial-copy-' . $index, '<p>' . esc_html( $testimonial[1] ) . '</p>' ),
			estatein_seed_container(
				'testimonial-person-' . $index,
				'estatein-person',
				array(
					estatein_seed_image( 'testimonial-avatar-' . $index, $avatar_url, $media[ $testimonial[4] ] ),
					estatein_seed_container(
						'testimonial-meta-' . $index,
						'estatein-person-meta',
						array(
							estatein_seed_heading( 'testimonial-name-' . $index, $testimonial[2], 'h4', 'estatein-person-name' ),
							estatein_seed_text( 'testimonial-location-' . $index, '<p>' . esc_html( $testimonial[3] ) . '</p>', 'estatein-person-location' ),
						)
					)
				)
			),
		)
	);
}
$testimonials = estatein_seed_container(
	'testimonials',
	'estatein-section',
	array(
		estatein_seed_container(
			'testimonials-shell',
			'estatein-section-shell',
			array(
				estatein_seed_section_header( 'testimonials', 'What Our Clients Say', 'Read the success stories and heartfelt testimonials from our valued clients. Discover why they chose Estatein for their real estate needs.', 'View All Testimonials', '#testimonials' ),
				estatein_seed_widget( 'testimonials-shortcode', 'shortcode', array( 'shortcode' => '[estatein_testimonials limit="12"]' ) ),
			)
		),
	),
	array( '_element_id' => 'testimonials' )
);

$faq_data = array(
	array( 'How do I search for properties on Estatein?', 'Learn how to use our user-friendly search tools to find properties that match your criteria.' ),
	array( 'What documents do I need to sell my property through Estatein?', 'Find out about the necessary documentation for listing your property with us.' ),
	array( 'How can I contact an Estatein agent?', 'Discover the different ways you can get in touch with our experienced agents.' ),
);
$faq_cards = array();
foreach ( $faq_data as $index => $faq ) {
	$faq_cards[] = estatein_seed_container(
		'faq-card-' . $index,
		'estatein-faq-card',
		array(
			estatein_seed_heading( 'faq-title-' . $index, $faq[0], 'h3' ),
			estatein_seed_text( 'faq-copy-' . $index, '<p>' . esc_html( $faq[1] ) . '</p>' ),
			estatein_seed_button( 'faq-button-' . $index, 'Read More', '#footer-newsletter', 'estatein-card-button' ),
		)
	);
}
$faqs = estatein_seed_container(
	'faqs',
	'estatein-section',
	array(
		estatein_seed_container(
			'faqs-shell',
			'estatein-section-shell',
			array(
				estatein_seed_section_header( 'faqs', 'Frequently Asked Questions', 'Find answers to common questions about Estatein\'s services, property listings, and the real estate process. We\'re here to provide clarity and assist you every step of the way.', 'View All FAQs', '#faqs' ),
				estatein_seed_widget( 'faqs-shortcode', 'shortcode', array( 'shortcode' => '[estatein_faqs limit="12"]' ) ),
			)
		),
	),
	array( '_element_id' => 'faqs' )
);

$cta = estatein_seed_container(
	'final-cta',
	'estatein-final-cta',
	array(
		estatein_seed_container(
			'final-cta-shell',
			'estatein-final-cta-shell',
			array(
				estatein_seed_container(
					'final-cta-copy',
					'estatein-final-cta-copy',
					array(
						estatein_seed_heading( 'final-cta-title', 'Start Your Real Estate Journey Today', 'h2' ),
						estatein_seed_text( 'final-cta-text', '<p>Your dream property is just a click away. Whether you\'re looking for a new home, a strategic investment, or expert real estate advice, Estatein is here to assist you every step of the way. Take the first step towards your real estate goals and explore our available properties or get in touch with our team for personalized assistance.</p>' ),
					)
				),
				estatein_seed_button( 'final-cta-button', 'Explore Properties', '#featured-properties', 'estatein-primary-button' ),
			)
		),
	)
);

$elementor_data = array(
	estatein_seed_container( 'home-root', 'estatein-home', array( $hero, $services, $featured, $testimonials, $faqs, $cta ) ),
);

$home_page = get_page_by_path( 'home', OBJECT, 'page' );
$page_data = array(
	'post_title'   => 'Home',
	'post_name'    => 'home',
	'post_type'    => 'page',
	'post_status'  => 'publish',
	'post_content' => '',
);
if ( $home_page ) {
	$page_data['ID'] = $home_page->ID;
	$home_id         = wp_update_post( $page_data, true );
} else {
	$home_id = wp_insert_post( $page_data, true );
}

if ( is_wp_error( $home_id ) ) {
	WP_CLI::error( $home_id->get_error_message() );
}

update_post_meta( $home_id, '_elementor_edit_mode', 'builder' );
update_post_meta( $home_id, '_elementor_template_type', 'wp-page' );
update_post_meta( $home_id, '_elementor_template', 'default' );
update_post_meta( $home_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '4.2.1' );
update_post_meta( $home_id, '_elementor_data', wp_slash( wp_json_encode( $elementor_data ) ) );
update_post_meta( $home_id, '_yoast_wpseo_title', 'Estatein | Find Your Perfect Property' );
update_post_meta( $home_id, '_yoast_wpseo_metadesc', 'Explore featured homes, trusted real estate guidance, and property opportunities with Estatein.' );

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', (int) $home_id );
update_option( 'elementor_disable_color_schemes', 'yes' );
update_option( 'elementor_disable_typography_schemes', 'yes' );

$base_url = trailingslashit( home_url() );
$menus = array(
	'estatein-primary' => estatein_seed_menu(
		'Primary Navigation',
		array(
			'Home'        => $base_url . '#home',
			'About Us'    => $base_url . '#services',
			'Properties'  => $base_url . '#featured-properties',
			'Services'    => $base_url . '#services',
			'Testimonials'=> $base_url . '#testimonials',
			'FAQs'        => $base_url . '#faqs',
		)
	),
	'estatein-footer-home' => estatein_seed_menu(
		'Footer Home',
		array(
			'Hero Section' => $base_url . '#home',
			'Features'     => $base_url . '#services',
			'Properties'   => $base_url . '#featured-properties',
			'Testimonials' => $base_url . '#testimonials',
			'FAQ’s'        => $base_url . '#faqs',
		)
	),
	'estatein-footer-about' => estatein_seed_menu(
		'Footer About Us',
		array(
			'Our Story'    => $base_url . '#home',
			'Our Works'    => $base_url . '#featured-properties',
			'How It Works' => $base_url . '#services',
			'Our Team'     => $base_url . '#testimonials',
			'Our Clients'  => $base_url . '#testimonials',
		)
	),
	'estatein-footer-properties' => estatein_seed_menu(
		'Footer Properties',
		array(
			'Portfolio'  => $base_url . '#featured-properties',
			'Categories' => $base_url . '#featured-properties',
		)
	),
	'estatein-footer-services' => estatein_seed_menu(
		'Footer Services',
		array(
			'Valuation Mastery'    => $base_url . '#services',
			'Strategic Marketing'  => $base_url . '#services',
			'Negotiation Wizardry' => $base_url . '#services',
			'Closing Success'      => $base_url . '#services',
			'Property Management'  => $base_url . '#services',
		)
	),
	'estatein-footer-contact' => estatein_seed_menu(
		'Footer Contact Us',
		array(
			'Contact Form' => $base_url . '#footer-newsletter',
			'Our Offices'  => $base_url . '#footer-newsletter',
		)
	),
);

$locations = get_theme_mod( 'nav_menu_locations', array() );
foreach ( $menus as $location => $menu_id ) {
	$locations[ $location ] = $menu_id;
}
set_theme_mod( 'nav_menu_locations', $locations );

// Set editable Customizer defaults explicitly so they are visible in the database.
set_theme_mod( 'estatein_announcement_text', 'Discover Your Dream Property with Estatein' );
set_theme_mod( 'estatein_announcement_link_text', 'Learn More' );
set_theme_mod( 'estatein_announcement_link_url', $base_url . '#featured-properties' );
set_theme_mod( 'estatein_footer_copyright', '©' . gmdate( 'Y' ) . ' Estatein. All Rights Reserved.' );

if ( class_exists( '\\Elementor\\Plugin' ) ) {
	\Elementor\Plugin::$instance->files_manager->clear_cache();
}

flush_rewrite_rules();

WP_CLI::success(
	sprintf(
		'Seeded Home page #%d, %d properties, %d media records, and newsletter form #%d.',
		$home_id,
		count( array_filter( $property_ids ) ),
		count( array_filter( $media ) ),
		$newsletter_id
	)
);
