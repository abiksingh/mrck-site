<?php
/**
 * Structured fields for an œuvre, registered in PHP (version-controlled, deploys
 * with the plugin — the client never re-creates a field by hand).
 *
 * Uses the ACF field API, provided for free by Secure Custom Fields (SCF).
 * Guarded so the plugin still works (CPT + taxonomies) before SCF is active.
 *
 * @package mrck-archive
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( [
		'key'      => 'group_oeuvre_meta',
		'title'    => __( 'Fiche de l’œuvre', 'mrck' ),
		'position' => 'normal',
		'fields'   => [
			[ 'key' => 'field_oeuvre_annee', 'label' => __( 'Année', 'mrck' ), 'name' => 'annee', 'type' => 'number', 'instructions' => __( 'Année de création — utilisée pour le tri et les filtres.', 'mrck' ), 'wrapper' => [ 'width' => 50 ] ],
			[ 'key' => 'field_oeuvre_date_aff', 'label' => __( 'Date affichée', 'mrck' ), 'name' => 'date_affichee', 'type' => 'text', 'instructions' => __( 'Texte libre, ex. « vers 1955 ».', 'mrck' ), 'wrapper' => [ 'width' => 50 ] ],
			[ 'key' => 'field_oeuvre_h', 'label' => __( 'Hauteur (cm)', 'mrck' ), 'name' => 'hauteur_cm', 'type' => 'number', 'wrapper' => [ 'width' => 33 ] ],
			[ 'key' => 'field_oeuvre_w', 'label' => __( 'Largeur (cm)', 'mrck' ), 'name' => 'largeur_cm', 'type' => 'number', 'wrapper' => [ 'width' => 33 ] ],
			[ 'key' => 'field_oeuvre_d', 'label' => __( 'Profondeur (cm)', 'mrck' ), 'name' => 'profondeur_cm', 'type' => 'number', 'wrapper' => [ 'width' => 34 ] ],
			[ 'key' => 'field_oeuvre_dim_aff', 'label' => __( 'Dimensions affichées', 'mrck' ), 'name' => 'dimensions_affichees', 'type' => 'text', 'instructions' => __( 'Ex. « 65 × 50 cm ».', 'mrck' ) ],
			[ 'key' => 'field_oeuvre_support', 'label' => __( 'Support / matériaux', 'mrck' ), 'name' => 'support', 'type' => 'text', 'instructions' => __( 'Ex. « huile sur toile », « gouache sur papier ».', 'mrck' ) ],
			[ 'key' => 'field_oeuvre_inv', 'label' => __( 'N° d’inventaire', 'mrck' ), 'name' => 'numero_inventaire', 'type' => 'text', 'wrapper' => [ 'width' => 50 ] ],
			[ 'key' => 'field_oeuvre_signature', 'label' => __( 'Signature', 'mrck' ), 'name' => 'signature', 'type' => 'text', 'wrapper' => [ 'width' => 50 ] ],
			[ 'key' => 'field_oeuvre_credit', 'label' => __( 'Crédit / droits', 'mrck' ), 'name' => 'credit', 'type' => 'text' ],
			[ 'key' => 'field_oeuvre_galerie', 'label' => __( 'Galerie d’images', 'mrck' ), 'name' => 'galerie', 'type' => 'gallery', 'instructions' => __( 'Vues complémentaires (détails, verso…). L’image principale reste l’« image mise en avant ».', 'mrck' ) ],
		],
		'location' => [
			[
				[ 'param' => 'post_type', 'operator' => '==', 'value' => 'oeuvre' ],
			],
		],
	] );
} );
