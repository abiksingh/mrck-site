<?php
/**
 * "Vie" biography chapters — a small post type for the chronological narrative.
 * Intro text lives in the editor (post_content, Gutenberg-editable); the
 * captioned images live in an ACF gallery; order via menu_order.
 *
 * @package mrck-archive
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'chapitre', [
		'labels'        => [
			'name'          => __( 'Vie — chapitres', 'mrck' ),
			'singular_name' => __( 'Chapitre', 'mrck' ),
			'menu_name'     => __( 'Vie', 'mrck' ),
			'add_new_item'  => __( 'Ajouter un chapitre', 'mrck' ),
			'edit_item'     => __( 'Modifier le chapitre', 'mrck' ),
			'all_items'     => __( 'Tous les chapitres', 'mrck' ),
		],
		'public'        => true,
		'has_archive'   => 'vie',
		'menu_icon'     => 'dashicons-book',
		'menu_position' => 6,
		'supports'      => [ 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ],
		'rewrite'       => [ 'slug' => 'vie', 'with_front' => false ],
		'show_in_rest'  => true,
	] );
} );

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group( [
		'key'      => 'group_chapitre',
		'title'    => __( 'Chapitre', 'mrck' ),
		'fields'   => [
			[ 'key' => 'field_chap_sub', 'label' => __( 'Sous-titre', 'mrck' ), 'name' => 'sous_titre', 'type' => 'text', 'instructions' => __( 'Ex. « années de formations, 1910-20 ».', 'mrck' ) ],
			[ 'key' => 'field_chap_gal', 'label' => __( 'Images', 'mrck' ), 'name' => 'galerie', 'type' => 'gallery', 'instructions' => __( 'Portraits, photographies et documents, avec légendes.', 'mrck' ) ],
		],
		'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'chapitre' ] ] ],
	] );
} );
