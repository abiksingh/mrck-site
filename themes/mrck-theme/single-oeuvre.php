<?php
/**
 * Detail page for a single œuvre.
 *
 * @package mrck-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$has_acf  = function_exists( 'get_field' );
	$annee    = $has_acf ? get_field( 'annee' ) : get_post_meta( get_the_ID(), 'annee', true );
	$dims     = $has_acf ? get_field( 'dimensions_affichees' ) : '';
	$support  = $has_acf ? get_field( 'support' ) : '';
	$credit   = $has_acf ? get_field( 'credit' ) : '';
	$gallery  = $has_acf ? get_field( 'galerie' ) : [];
	?>
	<article <?php post_class( 'oeuvre' ); ?>>
		<header class="oeuvre__head" data-anim="reveal">
			<h1 class="oeuvre__title"><?php the_title(); ?></h1>
			<?php if ( $annee ) : ?>
				<p class="oeuvre__year"><?php echo esc_html( $annee ); ?></p>
			<?php endif; ?>
		</header>

		<div class="oeuvre__media" data-anim="reveal">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'oeuvre_full', [ 'class' => 'oeuvre__img', 'fetchpriority' => 'high', 'alt' => the_title_attribute( [ 'echo' => false ] ) ] );
			}
			if ( ! empty( $gallery ) && is_array( $gallery ) ) {
				foreach ( $gallery as $image ) {
					if ( ! is_array( $image ) ) {
						continue;
					}
					$src = $image['sizes']['oeuvre_full'] ?? $image['url'] ?? '';
					$alt = $image['alt'] ?? '';
					if ( $src ) {
						printf(
							'<img class="oeuvre__img" src="%s" alt="%s" loading="lazy">',
							esc_url( $src ),
							esc_attr( $alt ?: get_the_title() )
						);
					}
				}
			}
			?>
		</div>

		<div class="oeuvre__body">
			<?php if ( trim( (string) get_the_content() ) !== '' ) : ?>
				<div class="oeuvre__desc prose" data-anim="reveal"><?php the_content(); ?></div>
			<?php endif; ?>

			<dl class="oeuvre__meta" data-anim="reveal">
				<?php
				$rows = [
					__( 'Technique', 'mrck' )  => get_the_term_list( get_the_ID(), 'technique', '', ', ' ),
					__( 'Série', 'mrck' )      => get_the_term_list( get_the_ID(), 'serie', '', ', ' ),
					__( 'Thème', 'mrck' )      => get_the_term_list( get_the_ID(), 'theme_art', '', ', ' ),
					__( 'Collection', 'mrck' ) => get_the_term_list( get_the_ID(), 'collection', '', ', ' ),
					__( 'Dimensions', 'mrck' ) => $dims ? esc_html( $dims ) : '',
					__( 'Support', 'mrck' )    => $support ? esc_html( $support ) : '',
					__( 'Crédit', 'mrck' )     => $credit ? esc_html( $credit ) : '',
				];
				foreach ( $rows as $label => $value ) {
					if ( $value && ! is_wp_error( $value ) ) {
						echo '<dt>' . esc_html( $label ) . '</dt><dd>' . wp_kses_post( $value ) . '</dd>';
					}
				}
				?>
			</dl>

			<p class="oeuvre__back">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'oeuvre' ) ); ?>">
					<?php esc_html_e( '← Retour à l’archive', 'mrck' ); ?>
				</a>
			</p>
		</div>
	</article>
	<?php
endwhile;

get_footer();
