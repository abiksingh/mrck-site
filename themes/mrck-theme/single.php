<?php
/**
 * Single post — used for Actualités (news).
 *
 * @package mrck-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'article' ); ?>>
		<header class="article__head" data-anim="reveal">
			<p class="article__date"><?php echo esc_html( get_the_date() ); ?></p>
			<h1 class="article__title"><?php the_title(); ?></h1>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="article__media" data-anim="reveal"><?php the_post_thumbnail( 'oeuvre_full' ); ?></div>
		<?php endif; ?>

		<div class="article__body prose" data-anim="reveal"><?php the_content(); ?></div>

		<p class="article__back" data-anim="reveal">
			<a href="<?php echo esc_url( get_permalink( (int) get_option( 'page_for_posts' ) ) ); ?>">← <?php esc_html_e( 'Toutes les actualités', 'mrck' ); ?></a>
		</p>
	</article>
	<?php
endwhile;

get_footer();
