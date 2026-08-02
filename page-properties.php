<?php
/**
 * Template Name: Estatein Properties Page
 * Template Post Type: page
 *
 * @package Estatein
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="estatein-properties-page">
	<?php
	while ( have_posts() ) {
		the_post();
		the_content();
	}
	?>
</main>
<?php
get_footer();
