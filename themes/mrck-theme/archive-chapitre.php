<?php
/**
 * "La Vie" — the biography, rendered as a chaptered long-form narrative.
 *
 * @package mrck-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

$chapters = new WP_Query( [
	'post_type'      => 'chapitre',
	'posts_per_page' => -1,
	'orderby'        => 'menu_order',
	'order'          => 'ASC',
] );
?>
<article class="vie">
	<header class="vie__head" data-anim="reveal">
		<p class="vie__eyebrow"><?php esc_html_e( 'Biographie', 'mrck' ); ?></p>
		<h1 class="vie__title"><?php esc_html_e( 'La vie', 'mrck' ); ?></h1>
		<p class="vie__lede"><?php esc_html_e( 'Marie-Renée Chevallier-Kervern (1902–1987) — la traversée d’un siècle, de la Bretagne natale au cœur de l’abstraction.', 'mrck' ); ?></p>
	</header>

	<?php
	$i = 0;
	while ( $chapters->have_posts() ) :
		$chapters->the_post();
		$i++;
		$sub = function_exists( 'get_field' ) ? get_field( 'sous_titre' ) : '';
		$gal = function_exists( 'get_field' ) ? get_field( 'galerie' ) : [];
		?>
		<section class="chapitre" id="chapitre-<?php echo (int) $i; ?>" data-anim="reveal">
			<header class="chapitre__head">
				<span class="chapitre__num"><?php echo esc_html( sprintf( '%02d', $i ) ); ?></span>
				<h2 class="chapitre__title"><?php the_title(); ?></h2>
				<?php if ( $sub ) : ?><p class="chapitre__sub"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
			</header>

			<?php if ( trim( (string) get_the_content() ) !== '' ) : ?>
				<div class="chapitre__intro prose"><?php the_content(); ?></div>
			<?php endif; ?>

			<?php if ( ! empty( $gal ) && is_array( $gal ) ) : ?>
				<div class="chapitre__gallery">
					<?php
					foreach ( $gal as $img ) :
						$src = $img['sizes']['oeuvre_card'] ?? $img['url'] ?? '';
						$cap = $img['caption'] ?? '';
						if ( ! $src ) {
							continue;
						}
						?>
						<figure class="chapitre__fig" data-anim="reveal">
							<img src="<?php echo esc_url( $src ); ?>" alt="<?php echo esc_attr( $img['alt'] ?: wp_strip_all_tags( (string) $cap ) ); ?>" loading="lazy">
							<?php if ( $cap ) : ?><figcaption><?php echo esc_html( $cap ); ?></figcaption><?php endif; ?>
						</figure>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>
		<?php
	endwhile;
	wp_reset_postdata();
	?>

	<nav class="vie__cta" data-anim="reveal">
		<a class="hero__cta" href="<?php echo esc_url( get_post_type_archive_link( 'oeuvre' ) ); ?>">
			<?php esc_html_e( 'Explorer l’archive des œuvres →', 'mrck' ); ?>
		</a>
	</nav>
</article>
<?php
get_footer();
