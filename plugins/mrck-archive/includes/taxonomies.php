<?php
/**
 * Browse / filter axes for the archive. Taxonomies (not meta) so they get
 * indexed term queries, term archive pages and stay fast as the catalogue grows.
 *
 * @package mrck-archive
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	// key => [ plural label, singular label, url slug ].
	$taxonomies = [
		'technique'  => [ __( 'Techniques', 'mrck' ), __( 'Technique', 'mrck' ), 'technique' ],
		'serie'      => [ __( 'Séries', 'mrck' ), __( 'Série', 'mrck' ), 'serie' ],
		'theme_art'  => [ __( 'Thèmes', 'mrck' ), __( 'Thème', 'mrck' ), 'theme' ],
		'collection' => [ __( 'Collections', 'mrck' ), __( 'Collection', 'mrck' ), 'collection' ],
	];

	foreach ( $taxonomies as $key => $meta ) {
		list( $plural, $singular, $slug ) = $meta;

		register_taxonomy( $key, 'oeuvre', [
			'labels'            => [
				'name'          => $plural,
				'singular_name' => $singular,
				'menu_name'     => $plural,
				'all_items'     => sprintf( /* translators: %s: taxonomy plural */ __( 'Toutes les %s', 'mrck' ), strtolower( $plural ) ),
				'edit_item'     => sprintf( /* translators: %s: taxonomy singular */ __( 'Modifier : %s', 'mrck' ), $singular ),
				'add_new_item'  => sprintf( /* translators: %s: taxonomy singular */ __( 'Ajouter : %s', 'mrck' ), $singular ),
				'search_items'  => sprintf( /* translators: %s: taxonomy plural */ __( 'Rechercher : %s', 'mrck' ), $plural ),
			],
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => [ 'slug' => $slug, 'with_front' => false ],
		] );
	}
} );
