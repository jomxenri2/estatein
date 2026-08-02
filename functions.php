<?php
/**
 * Estatein child-theme bootstrap.
 *
 * @package Estatein
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ESTATEIN_THEME_VERSION', '1.0.0' );
define( 'ESTATEIN_THEME_DIR', get_stylesheet_directory() );
define( 'ESTATEIN_THEME_URI', get_stylesheet_directory_uri() );

require_once ESTATEIN_THEME_DIR . '/inc/setup.php';
require_once ESTATEIN_THEME_DIR . '/inc/customizer.php';
require_once ESTATEIN_THEME_DIR . '/inc/property-shortcode.php';
require_once ESTATEIN_THEME_DIR . '/inc/content-shortcodes.php';
