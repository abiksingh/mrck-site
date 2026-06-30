<?php
/**
 * Fallback template.
 *
 * @package mrck-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="wrap">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'entry' ); ?> data-anim="reveal">
				<h2 class="entry__title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
				<?php the_excerpt(); ?>
			</article>
			<?php
		endwhile;

		the_posts_pagination( [ 'mid_size' => 1 ] );
	else :
		?>
		<p><?php esc_html_e( 'Rien à afficher pour le moment.', 'mrck' ); ?></p>
	<?php endif; ?>
</section>
<?php
get_footer();
