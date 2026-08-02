<?php
/**
 * Site footer.
 *
 * @package Estatein
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$footer_groups = array(
	'estatein-footer-home' => array(
		'title' => __( 'Home', 'estatein' ),
		'items' => array( 'Hero Section' => '#home', 'Features' => '#services', 'Properties' => '#featured-properties', 'Testimonials' => '#testimonials', "FAQ's" => '#faqs' ),
	),
	'estatein-footer-about' => array(
		'title' => __( 'About Us', 'estatein' ),
		'items' => array( 'Our Story' => '#testimonials', 'Our Works' => '#featured-properties', 'How It Works' => '#services', 'Our Team' => '#testimonials', 'Our Clients' => '#testimonials' ),
	),
	'estatein-footer-properties' => array(
		'title' => __( 'Properties', 'estatein' ),
		'items' => array( 'Portfolio' => '#featured-properties', 'Categories' => '#featured-properties' ),
	),
	'estatein-footer-services' => array(
		'title' => __( 'Services', 'estatein' ),
		'items' => array( 'Valuation Mastery' => '#services', 'Strategic Marketing' => '#services', 'Negotiation Wizardry' => '#services', 'Closing Success' => '#services', 'Property Management' => '#services' ),
	),
	'estatein-footer-contact' => array(
		'title' => __( 'Contact Us', 'estatein' ),
		'items' => array( 'Contact Form' => '#footer-newsletter', 'Our Offices' => '#footer-newsletter' ),
	),
);
?>
<footer class="estatein-site-footer">
	<div class="estatein-footer-main">
		<div class="estatein-footer-brand" id="footer-newsletter">
			<a class="estatein-logo estatein-footer-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( ESTATEIN_THEME_URI . '/assets/icons/logo.svg' ); ?>" width="160" height="49" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"></a>
			<?php if ( get_option( 'estatein_newsletter_form_id' ) ) : ?>
				<?php echo do_shortcode( '[contact-form-7 id="' . absint( get_option( 'estatein_newsletter_form_id' ) ) . '" title="Footer Newsletter"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php else : ?>
				<form class="estatein-newsletter-form" action="#footer-newsletter" method="post">
					<label class="screen-reader-text" for="estatein-newsletter-email"><?php esc_html_e( 'Email address', 'estatein' ); ?></label>
					<input id="estatein-newsletter-email" type="email" placeholder="<?php esc_attr_e( 'Enter Your Email', 'estatein' ); ?>" disabled>
					<button type="submit" disabled aria-label="<?php esc_attr_e( 'Subscribe', 'estatein' ); ?>"><img src="<?php echo esc_url( ESTATEIN_THEME_URI . '/assets/icons/footer-send.svg' ); ?>" alt=""></button>
				</form>
			<?php endif; ?>
		</div>

		<div class="estatein-footer-links">
			<?php foreach ( $footer_groups as $location => $group ) : ?>
				<div class="estatein-footer-column">
					<h2><?php echo esc_html( $group['title'] ); ?></h2>
					<?php
					if ( has_nav_menu( $location ) ) {
						wp_nav_menu( array( 'theme_location' => $location, 'container' => false, 'menu_class' => 'estatein-footer-menu', 'depth' => 1 ) );
					} else {
						estatein_footer_menu_fallback( $group['items'] );
					}
					?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="estatein-footer-bottom">
		<div class="estatein-footer-bottom-inner">
			<div class="estatein-legal">
				<p><?php echo esc_html( get_theme_mod( 'estatein_footer_copyright', sprintf( __( '©%s Estatein. All Rights Reserved.', 'estatein' ), gmdate( 'Y' ) ) ) ); ?></p>
				<a href="#"><?php esc_html_e( 'Terms & Conditions', 'estatein' ); ?></a>
			</div>
			<div class="estatein-social-links" aria-label="<?php esc_attr_e( 'Social media', 'estatein' ); ?>">
				<?php foreach ( array( 'facebook', 'linkedin', 'twitter', 'youtube' ) as $network ) : ?>
					<a href="<?php echo esc_url( get_theme_mod( 'estatein_' . $network . '_url', '#' ) ); ?>" aria-label="<?php echo esc_attr( ucfirst( $network ) ); ?>"><img src="<?php echo esc_url( ESTATEIN_THEME_URI . '/assets/icons/social-' . $network . '.png' ); ?>" alt=""></a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>

