<?php
/**
 * Generic page (Expositions, Publications, and any editorial page).
 *
 * @package mrck-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'page' ); ?>>
		<header class="page__head" data-anim="reveal">
			<h1 class="page__title"><?php the_title(); ?></h1>
		</header>
		<div class="page__body prose" data-anim="reveal"><?php the_content(); ?></div>
	</article>
	<?php
endwhile;

get_footer();
