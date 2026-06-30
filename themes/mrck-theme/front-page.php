<?php
/**
 * Front page — hero + editable narrative blocks.
 *
 * @package mrck-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="hero" data-anim="reveal">
	<p class="hero__eyebrow"><?php esc_html_e( 'Archive', 'mrck' ); ?></p>
	<h1 class="hero__title"><?php bloginfo( 'name' ); ?></h1>
	<p class="hero__lede">
		<?php esc_html_e( 'Peintre, dessinatrice, graveuse et céramiste (1902–1987). La traversée d’un siècle, du motif breton à l’abstraction et aux étoffes cousues.', 'mrck' ); ?>
	</p>
	<a class="hero__cta" href="<?php echo esc_url( get_post_type_archive_link( 'oeuvre' ) ); ?>">
		<?php esc_html_e( 'Explorer l’archive', 'mrck' ); ?>
	</a>
</section>

<?php
// Editable narrative content (Gutenberg blocks) when a static front page is assigned.
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		if ( trim( (string) get_the_content() ) !== '' ) :
			?>
			<section class="prose" data-anim="reveal"><?php the_content(); ?></section>
			<?php
		endif;
	endwhile;
endif;

get_footer();
