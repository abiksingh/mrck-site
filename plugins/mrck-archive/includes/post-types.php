<?php
/**
 * The "Œuvre" custom post type — one entry per catalogued work.
 *
 * @package mrck-archive
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	$labels = [
		'name'               => __( 'Œuvres', 'mrck' ),
		'singular_name'      => __( 'Œuvre', 'mrck' ),
		'menu_name'          => __( 'Archive', 'mrck' ),
		'add_new'            => __( 'Ajouter', 'mrck' ),
		'add_new_item'       => __( 'Ajouter une œuvre', 'mrck' ),
		'edit_item'          => __( 'Modifier l’œuvre', 'mrck' ),
		'new_item'           => __( 'Nouvelle œuvre', 'mrck' ),
		'view_item'          => __( 'Voir l’œuvre', 'mrck' ),
		'view_items'         => __( 'Voir les œuvres', 'mrck' ),
		'search_items'       => __( 'Rechercher une œuvre', 'mrck' ),
		'not_found'          => __( 'Aucune œuvre', 'mrck' ),
		'not_found_in_trash' => __( 'Aucune œuvre dans la corbeille', 'mrck' ),
		'all_items'          => __( 'Toutes les œuvres', 'mrck' ),
	];

	register_post_type( 'oeuvre', [
		'labels'        => $labels,
		'public'        => true,
		'has_archive'   => 'oeuvres',
		'menu_icon'     => 'dashicons-art',
		'menu_position' => 5,
		'supports'      => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes', 'custom-fields' ],
		'rewrite'       => [ 'slug' => 'oeuvres', 'with_front' => false ],
		'show_in_rest'  => true,
		'rest_base'     => 'oeuvres',
	] );
} );
