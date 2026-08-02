<?php
/**
 * Theme setup, assets, menus, and reusable helpers.
 *
 * @package Estatein
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the theme's project-specific features.
 */
function estatein_theme_setup() {
	load_child_theme_textdomain( 'estatein', ESTATEIN_THEME_DIR . '/languages' );

	register_nav_menus(
		array(
			'estatein-primary'    => __( 'Estatein Primary Navigation', 'estatein' ),
			'estatein-footer-home' => __( 'Footer: Home', 'estatein' ),
			'estatein-footer-about' => __( 'Footer: About Us', 'estatein' ),
			'estatein-footer-properties' => __( 'Footer: Properties', 'estatein' ),
			'estatein-footer-services' => __( 'Footer: Services', 'estatein' ),
			'estatein-footer-contact' => __( 'Footer: Contact Us', 'estatein' ),
		)
	);

	add_image_size( 'estatein-property-card', 640, 470, true );
}
add_action( 'after_setup_theme', 'estatein_theme_setup', 20 );

/**
 * Ensure the primary navigation has exactly one current-page item.
 *
 * WordPress marks every same-page anchor link as current. Because the Home Page
 * navigation contains several anchor links, keep the first matching item only.
 *
 * @param string[] $classes CSS classes applied to the menu item's <li> element.
 * @param WP_Post  $item    Current menu item.
 * @param stdClass $args    wp_nav_menu() arguments.
 * @return string[]
 */
function estatein_limit_primary_current_menu_item( $classes, $item, $args ) {
	static $current_item_assigned = false;

	if ( empty( $args->theme_location ) || 'estatein-primary' !== $args->theme_location ) {
		return $classes;
	}

	$current_classes = array(
		'current-menu-item',
		'current_page_item',
	);
	$is_current      = (bool) array_intersect( $current_classes, $classes );
	$classes         = array_values( array_diff( $classes, $current_classes ) );

	if ( $is_current && ! $current_item_assigned ) {
		$classes[]             = 'current-menu-item';
		$current_item_assigned = true;
	}

	return array_values( array_unique( $classes ) );
}
add_filter( 'nav_menu_css_class', 'estatein_limit_primary_current_menu_item', 10, 3 );

/**
 * Enqueue public assets.
 */
function estatein_enqueue_assets() {
	$style_path  = ESTATEIN_THEME_DIR . '/assets/css/site.css';
	$script_path = ESTATEIN_THEME_DIR . '/assets/js/site.js';

	wp_enqueue_style(
		'estatein-urbanist',
		'https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'estatein-child',
		ESTATEIN_THEME_URI . '/assets/css/site.css',
		array( 'hello-elementor', 'hello-elementor-theme-style', 'estatein-urbanist' ),
		file_exists( $style_path ) ? (string) filemtime( $style_path ) : ESTATEIN_THEME_VERSION
	);

	wp_enqueue_script(
		'estatein-site',
		ESTATEIN_THEME_URI . '/assets/js/site.js',
		array(),
		file_exists( $script_path ) ? (string) filemtime( $script_path ) : ESTATEIN_THEME_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'estatein_enqueue_assets', 30 );

/**
 * Render a useful menu when no WordPress menu has been assigned yet.
 */
function estatein_primary_menu_fallback() {
	$home = home_url( '/' );
	?>
	<ul class="estatein-nav-list">
		<li class="current-menu-item"><a href="<?php echo esc_url( $home ); ?>"><?php esc_html_e( 'Home', 'estatein' ); ?></a></li>
		<li><a href="<?php echo esc_url( $home . '#testimonials' ); ?>"><?php esc_html_e( 'About Us', 'estatein' ); ?></a></li>
		<li><a href="<?php echo esc_url( $home . '#featured-properties' ); ?>"><?php esc_html_e( 'Properties', 'estatein' ); ?></a></li>
		<li><a href="<?php echo esc_url( $home . '#services' ); ?>"><?php esc_html_e( 'Services', 'estatein' ); ?></a></li>
	</ul>
	<?php
}

/**
 * Render a footer-menu fallback.
 *
 * @param array $items Label/anchor pairs.
 */
function estatein_footer_menu_fallback( $items ) {
	$home = home_url( '/' );
	?>
	<ul class="estatein-footer-menu">
		<?php foreach ( $items as $label => $anchor ) : ?>
			<li><a href="<?php echo esc_url( $home . $anchor ); ?>"><?php echo esc_html( $label ); ?></a></li>
		<?php endforeach; ?>
	</ul>
	<?php
}
