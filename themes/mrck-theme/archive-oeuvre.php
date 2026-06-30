<?php
/**
 * Archive of œuvres: server-rendered filter bar + grid (the no-JS / SEO baseline),
 * progressively enhanced into live REST filtering by src/js/archive.js.
 *
 * @package mrck-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

global $wp_query;
$total = (int) $wp_query->found_posts;

$techniques  = get_terms( [ 'taxonomy' => 'technique', 'hide_empty' => true, 'orderby' => 'name' ] );
$series      = get_terms( [ 'taxonomy' => 'serie', 'hide_empty' => true, 'orderby' => 'name' ] );
$collections = get_terms( [ 'taxonomy' => 'collection', 'hide_empty' => true, 'orderby' => 'name' ] );

$cur_tech   = isset( $_GET['technique'] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_GET['technique'] ) ) : [];
$cur_serie  = isset( $_GET['serie'] ) ? sanitize_title( wp_unslash( $_GET['serie'] ) ) : '';
$cur_coll   = isset( $_GET['collection'] ) ? sanitize_title( wp_unslash( $_GET['collection'] ) ) : '';
$cur_search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';
$archive_url = get_post_type_archive_link( 'oeuvre' );
?>
<section class="archive">
	<header class="archive__head" data-anim="reveal">
		<h1 class="archive__title"><?php esc_html_e( 'Archive des œuvres', 'mrck' ); ?></h1>
		<p class="archive__count" data-mrck-count>
			<?php printf( esc_html( _n( '%s œuvre', '%s œuvres', $total, 'mrck' ) ), esc_html( number_format_i18n( $total ) ) ); ?>
		</p>
	</header>

	<form class="filters" method="get" action="<?php echo esc_url( $archive_url ); ?>" data-mrck-filters>
		<div class="filters__search">
			<label class="screen-reader-text" for="f-search"><?php esc_html_e( 'Rechercher', 'mrck' ); ?></label>
			<input type="search" id="f-search" name="search" value="<?php echo esc_attr( $cur_search ); ?>" placeholder="<?php esc_attr_e( 'Rechercher une œuvre, une technique, une année…', 'mrck' ); ?>">
		</div>

		<?php if ( ! is_wp_error( $techniques ) && $techniques ) : ?>
			<fieldset class="filters__chips">
				<legend class="screen-reader-text"><?php esc_html_e( 'Technique', 'mrck' ); ?></legend>
				<?php foreach ( $techniques as $t ) : ?>
					<label class="chip">
						<input type="checkbox" name="technique[]" value="<?php echo esc_attr( $t->slug ); ?>" <?php checked( in_array( $t->slug, $cur_tech, true ) ); ?>>
						<span><?php echo esc_html( $t->name ); ?></span>
					</label>
				<?php endforeach; ?>
			</fieldset>
		<?php endif; ?>

		<div class="filters__selects">
			<label class="field">
				<span><?php esc_html_e( 'Série', 'mrck' ); ?></span>
				<select name="serie">
					<option value=""><?php esc_html_e( 'Toutes', 'mrck' ); ?></option>
					<?php foreach ( ( is_wp_error( $series ) ? [] : $series ) as $s ) : ?>
						<option value="<?php echo esc_attr( $s->slug ); ?>" <?php selected( $cur_serie, $s->slug ); ?>><?php echo esc_html( $s->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="field">
				<span><?php esc_html_e( 'Collection', 'mrck' ); ?></span>
				<select name="collection">
					<option value=""><?php esc_html_e( 'Toutes', 'mrck' ); ?></option>
					<?php foreach ( ( is_wp_error( $collections ) ? [] : $collections ) as $c ) : ?>
						<option value="<?php echo esc_attr( $c->slug ); ?>" <?php selected( $cur_coll, $c->slug ); ?>><?php echo esc_html( $c->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="field">
				<span><?php esc_html_e( 'Tri', 'mrck' ); ?></span>
				<select name="orderby">
					<option value="annee"><?php esc_html_e( 'Année', 'mrck' ); ?></option>
					<option value="title"><?php esc_html_e( 'Titre', 'mrck' ); ?></option>
				</select>
			</label>
		</div>

		<div class="filters__actions">
			<button type="submit" class="btn"><?php esc_html_e( 'Filtrer', 'mrck' ); ?></button>
			<a class="filters__reset" href="<?php echo esc_url( $archive_url ); ?>"><?php esc_html_e( 'Réinitialiser', 'mrck' ); ?></a>
		</div>
	</form>

	<?php if ( have_posts() ) : ?>
		<ul class="grid" id="oeuvre-grid" data-mrck-grid>
			<?php
			while ( have_posts() ) :
				the_post();
				$annee = get_post_meta( get_the_ID(), 'annee', true );
				?>
				<li class="card" data-anim="reveal">
					<a class="card__link" href="<?php the_permalink(); ?>">
						<span class="card__media">
							<?php
							if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'oeuvre_card', [ 'loading' => 'lazy', 'class' => 'card__img' ] );
							}
							?>
						</span>
						<span class="card__title"><?php the_title(); ?></span>
						<?php if ( $annee ) : ?><span class="card__year"><?php echo esc_html( $annee ); ?></span><?php endif; ?>
					</a>
				</li>
			<?php endwhile; ?>
		</ul>

		<div class="archive__more">
			<?php if ( $wp_query->max_num_pages > 1 ) : ?>
				<button class="btn btn--ghost" data-mrck-loadmore data-page="1" data-pages="<?php echo (int) $wp_query->max_num_pages; ?>">
					<?php esc_html_e( 'Voir plus', 'mrck' ); ?>
				</button>
				<noscript><?php the_posts_pagination( [ 'mid_size' => 1 ] ); ?></noscript>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<p class="archive__empty" data-mrck-grid><?php esc_html_e( 'Aucune œuvre ne correspond à ces critères.', 'mrck' ); ?></p>
	<?php endif; ?>
</section>
<?php
get_footer();
