<?php
/**
 * Editable Gutenberg block: "Œuvres en avant" — drop a curated (or recent)
 * selection of works onto any page. PHP-rendered via the ACF block API (provided
 * free by Secure Custom Fields) so there is no JS build step.
 *
 * @package mrck-archive
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_register_block_type' ) ) {
		return;
	}

	acf_register_block_type( [
		'name'            => 'oeuvres-en-avant',
		'title'           => __( 'Œuvres en avant', 'mrck' ),
		'description'     => __( 'Une sélection d’œuvres de l’archive.', 'mrck' ),
		'category'        => 'widgets',
		'icon'            => 'art',
		'keywords'        => [ 'oeuvres', 'archive', 'works', 'mrck' ],
		'mode'            => 'preview',
		'supports'        => [ 'align' => [ 'wide', 'full' ], 'mode' => false, 'jsx' => true ],
		'render_callback' => 'mrck_render_oeuvres_block',
	] );

	acf_add_local_field_group( [
		'key'      => 'group_block_oeuvres',
		'title'    => __( 'Œuvres en avant', 'mrck' ),
		'fields'   => [
			[ 'key' => 'field_blk_titre', 'label' => __( 'Titre', 'mrck' ), 'name' => 'titre', 'type' => 'text' ],
			[ 'key' => 'field_blk_oeuvres', 'label' => __( 'Œuvres', 'mrck' ), 'name' => 'oeuvres', 'type' => 'relationship', 'post_type' => [ 'oeuvre' ], 'filters' => [ 'search', 'taxonomy' ], 'return_format' => 'id', 'instructions' => __( 'Laissez vide pour afficher des œuvres récentes.', 'mrck' ) ],
			[ 'key' => 'field_blk_max', 'label' => __( 'Nombre (si vide ci-dessus)', 'mrck' ), 'name' => 'max', 'type' => 'number', 'default_value' => 6 ],
		],
		'location' => [ [ [ 'param' => 'block', 'operator' => '==', 'value' => 'acf/oeuvres-en-avant' ] ] ],
	] );
} );

/**
 * Render callback for the block.
 *
 * @param array $block Block settings.
 */
function mrck_render_oeuvres_block( $block ) {
	$titre = get_field( 'titre' );
	$ids   = get_field( 'oeuvres' );
	$max   = (int) ( get_field( 'max' ) ?: 6 );

	if ( empty( $ids ) ) {
		$ids = get_posts( [ 'post_type' => 'oeuvre', 'posts_per_page' => $max, 'fields' => 'ids', 'orderby' => 'date', 'order' => 'DESC' ] );
	}
	if ( empty( $ids ) ) {
		if ( is_admin() ) {
			echo '<p>' . esc_html__( 'Sélectionnez des œuvres.', 'mrck' ) . '</p>';
		}
		return;
	}

	$class = 'featured';
	if ( ! empty( $block['className'] ) ) {
		$class .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class .= ' align' . $block['align'];
	}

	echo '<section class="' . esc_attr( $class ) . '" data-anim="reveal">';
	if ( $titre ) {
		echo '<h2 class="featured__title">' . esc_html( $titre ) . '</h2>';
	}
	echo '<ul class="grid">';
	foreach ( $ids as $id ) {
		$annee = get_post_meta( $id, 'annee', true );
		$thumb = has_post_thumbnail( $id ) ? get_the_post_thumbnail( $id, 'oeuvre_card', [ 'loading' => 'lazy', 'class' => 'card__img' ] ) : '';
		printf(
			'<li class="card"><a class="card__link" href="%s"><span class="card__media">%s</span><span class="card__title">%s</span>%s</a></li>',
			esc_url( get_permalink( $id ) ),
			$thumb,
			esc_html( get_the_title( $id ) ),
			$annee ? '<span class="card__year">' . esc_html( $annee ) . '</span>' : ''
		);
	}
	echo '</ul></section>';
}
