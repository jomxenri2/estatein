<?php
/**
 * Site header.
 *
 * @package Estatein
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="estatein-skip-link" href="#primary"><?php esc_html_e( 'Skip to content', 'estatein' ); ?></a>

<div class="estatein-announcement" data-estatein-announcement>
	<div class="estatein-announcement-inner">
		<p>✨ <?php echo esc_html( get_theme_mod( 'estatein_announcement_text', __( 'Discover Your Dream Property with Estatein', 'estatein' ) ) ); ?> <a href="<?php echo esc_url( get_theme_mod( 'estatein_announcement_link_url', home_url( '/#featured-properties' ) ) ); ?>"><?php echo esc_html( get_theme_mod( 'estatein_announcement_link_text', __( 'Learn More', 'estatein' ) ) ); ?></a></p>
		<button type="button" class="estatein-announcement-close" data-estatein-announcement-close aria-label="<?php esc_attr_e( 'Dismiss announcement', 'estatein' ); ?>">
		</button>
	</div>
</div>

<header class="estatein-site-header" data-estatein-header>
	<div class="estatein-header-inner">
		<a class="estatein-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<img src="<?php echo esc_url( ESTATEIN_THEME_URI . '/assets/icons/logo.svg' ); ?>" width="160" height="49" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
		</a>
		<button type="button" class="estatein-menu-toggle" data-estatein-menu-toggle aria-expanded="false" aria-controls="estatein-primary-navigation">
			<span></span><span></span><span></span><span class="screen-reader-text"><?php esc_html_e( 'Toggle navigation', 'estatein' ); ?></span>
		</button>
		<nav id="estatein-primary-navigation" class="estatein-primary-navigation" data-estatein-navigation aria-label="<?php esc_attr_e( 'Primary navigation', 'estatein' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'estatein-primary',
					'container'      => false,
					'menu_class'     => 'estatein-nav-list',
					'fallback_cb'    => 'estatein_primary_menu_fallback',
					'depth'          => 1,
				)
			);
			?>
		</nav>
		<a class="estatein-contact-link" href="<?php echo esc_url( home_url( '/#footer-newsletter' ) ); ?>"><?php esc_html_e( 'Contact Us', 'estatein' ); ?></a>
	</div>
</header>
