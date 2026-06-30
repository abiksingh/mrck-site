<?php
/**
 * Admin list-table tooling for the Œuvre screen: thumbnail + year columns,
 * sortable year, and taxonomy filter dropdowns. Essential once the catalogue
 * holds hundreds of works.
 *
 * @package mrck-archive
 */

defined( 'ABSPATH' ) || exit;

// Add Image + Année columns around the title.
add_filter( 'manage_oeuvre_posts_columns', function ( $columns ) {
	$out = [];
	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$out['mrck_thumb'] = __( 'Image', 'mrck' );
		}
		$out[ $key ] = $label;
		if ( 'title' === $key ) {
			$out['mrck_annee'] = __( 'Année', 'mrck' );
		}
	}
	return $out;
} );

add_action( 'manage_oeuvre_posts_custom_column', function ( $column, $post_id ) {
	if ( 'mrck_thumb' === $column ) {
		echo has_post_thumbnail( $post_id )
			? get_the_post_thumbnail( $post_id, [ 56, 56 ] )
			: '<span aria-hidden="true">—</span>';
	}
	if ( 'mrck_annee' === $column ) {
		$annee = get_post_meta( $post_id, 'annee', true );
		echo $annee ? esc_html( $annee ) : '<span aria-hidden="true">—</span>';
	}
}, 10, 2 );

// Make the Année column sortable.
add_filter( 'manage_edit-oeuvre_sortable_columns', function ( $columns ) {
	$columns['mrck_annee'] = 'mrck_annee';
	return $columns;
} );

add_action( 'pre_get_posts', function ( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'mrck_annee' === $query->get( 'orderby' ) ) {
		$query->set( 'meta_key', 'annee' );
		$query->set( 'orderby', 'meta_value_num' );
	}
} );

// Taxonomy filter dropdowns above the list table.
add_action( 'restrict_manage_posts', function ( $post_type ) {
	if ( 'oeuvre' !== $post_type ) {
		return;
	}
	foreach ( [ 'technique', 'serie', 'theme_art' ] as $tax ) {
		$taxonomy = get_taxonomy( $tax );
		if ( ! $taxonomy ) {
			continue;
		}
		$current = isset( $_GET[ $tax ] ) ? sanitize_text_field( wp_unslash( $_GET[ $tax ] ) ) : '';
		wp_dropdown_categories( [
			'show_option_all' => $taxonomy->labels->all_items,
			'taxonomy'        => $tax,
			'name'            => $tax,
			'value_field'     => 'slug',
			'selected'        => $current,
			'hierarchical'    => false,
			'hide_empty'      => false,
			'show_count'      => true,
			'orderby'         => 'name',
		] );
	}
} );
