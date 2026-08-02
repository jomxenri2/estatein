<?php
/**
 * Editable site-wide content exposed through the WordPress Customizer.
 *
 * @package Estatein
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register global Estatein settings.
 *
 * @param WP_Customize_Manager $customizer Customizer manager.
 */
function estatein_customize_register( $customizer ) {
	$customizer->add_section(
		'estatein_global',
		array(
			'title'       => __( 'Estatein Global Content', 'estatein' ),
			'description' => __( 'Edit the announcement, footer, and social links without touching theme files.', 'estatein' ),
			'priority'    => 30,
		)
	);

	$fields = array(
		'estatein_announcement_text' => array(
			'label'   => __( 'Announcement text', 'estatein' ),
			'default' => __( 'Discover Your Dream Property with Estatein', 'estatein' ),
			'type'    => 'text',
			'sanitize' => 'sanitize_text_field',
		),
		'estatein_announcement_link_text' => array(
			'label'   => __( 'Announcement link text', 'estatein' ),
			'default' => __( 'Learn More', 'estatein' ),
			'type'    => 'text',
			'sanitize' => 'sanitize_text_field',
		),
		'estatein_announcement_link_url' => array(
			'label'   => __( 'Announcement link URL', 'estatein' ),
			'default' => home_url( '/#featured-properties' ),
			'type'    => 'url',
			'sanitize' => 'esc_url_raw',
		),
		'estatein_footer_copyright' => array(
			'label'   => __( 'Footer copyright', 'estatein' ),
			'default' => sprintf( __( '©%s Estatein. All Rights Reserved.', 'estatein' ), gmdate( 'Y' ) ),
			'type'    => 'text',
			'sanitize' => 'sanitize_text_field',
		),
		'estatein_facebook_url' => array(
			'label' => __( 'Facebook URL', 'estatein' ), 'default' => '#', 'type' => 'url', 'sanitize' => 'esc_url_raw',
		),
		'estatein_linkedin_url' => array(
			'label' => __( 'LinkedIn URL', 'estatein' ), 'default' => '#', 'type' => 'url', 'sanitize' => 'esc_url_raw',
		),
		'estatein_twitter_url' => array(
			'label' => __( 'Twitter/X URL', 'estatein' ), 'default' => '#', 'type' => 'url', 'sanitize' => 'esc_url_raw',
		),
		'estatein_youtube_url' => array(
			'label' => __( 'YouTube URL', 'estatein' ), 'default' => '#', 'type' => 'url', 'sanitize' => 'esc_url_raw',
		),
	);

	foreach ( $fields as $key => $field ) {
		$customizer->add_setting(
			$key,
			array(
				'default'           => $field['default'],
				'sanitize_callback' => $field['sanitize'],
			)
		);

		$customizer->add_control(
			$key,
			array(
				'label'   => $field['label'],
				'setting' => $key,
				'section' => 'estatein_global',
				'type'    => $field['type'],
			)
		);
	}
}
add_action( 'customize_register', 'estatein_customize_register' );

