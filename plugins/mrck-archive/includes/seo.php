<?php
/**
 * SEO + structured data — kept in the plugin so it survives theme changes and
 * stays tied to the catalogue data. This is the single biggest concrete win over
 * the JS-only source site, which is invisible to crawlers.
 *
 * Emits: meta description, canonical, Open Graph / Twitter, and JSON-LD
 * (VisualArtwork per œuvre, Person for the artist). Sitemaps are handled by
 * WordPress core (/wp-sitemap.xml), which already includes the public post types.
 *
 * @package mrck-archive
 */

defined( 'ABSPATH' ) || exit;

// Emit a single canonical (ours also covers archives); drop core's singular-only one.
remove_action( 'wp_head', 'rel_canonical' );

/** Stable facts about the artist (used across schema + copy). */
function mrck_artist(): array {
	return [
		'name'      => 'Marie-Renée Chevallier-Kervern',
		'birthDate' => '1902-01-04',
		'deathDate' => '1987-11-19',
		'sameAs'    => 'https://fr.wikipedia.org/wiki/Marie-Ren%C3%A9e_Chevallier-Kervern',
	];
}

function mrck_person_schema(): array {
	$a = mrck_artist();
	return [
		'@type'       => 'Person',
		'name'        => $a['name'],
		'birthDate'   => $a['birthDate'],
		'deathDate'   => $a['deathDate'],
		'nationality' => 'Française',
		'jobTitle'    => [ 'Peintre', 'Dessinatrice', 'Graveuse', 'Céramiste' ],
		'sameAs'      => [ $a['sameAs'] ],
	];
}

/** Per-context meta description. */
function mrck_meta_description(): string {
	$a = mrck_artist();
	if ( is_singular( 'oeuvre' ) ) {
		$id    = get_queried_object_id();
		$parts = array_filter( [
			get_the_title( $id ),
			get_post_meta( $id, 'date_affichee', true ) ?: get_post_meta( $id, 'annee', true ),
			get_post_meta( $id, 'support', true ),
			get_post_meta( $id, 'dimensions_affichees', true ),
		] );
		return implode( ', ', $parts ) . '. Œuvre de ' . $a['name'] . '.';
	}
	if ( is_singular( 'chapitre' ) ) {
		return wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 40 );
	}
	if ( is_post_type_archive( 'oeuvre' ) ) {
		return 'Archive et catalogue raisonné de ' . $a['name'] . ' (1902–1987) : peintures, étoffes cousues, œuvres sur papier, gravures et céramiques. Recherche et filtres par technique, série, collection et année.';
	}
	if ( is_post_type_archive( 'chapitre' ) ) {
		return 'La vie de ' . $a['name'] . ', de la Bretagne natale au cœur de l’abstraction.';
	}
	if ( is_front_page() ) {
		return $a['name'] . ' (1902–1987) — peintre, dessinatrice, graveuse et céramiste. Archive et catalogue raisonné.';
	}
	return (string) get_bloginfo( 'description' );
}

/** Canonical URL (filter params on the archive collapse to the clean archive URL). */
function mrck_canonical_url(): string {
	if ( is_singular() ) {
		return (string) get_permalink();
	}
	if ( is_post_type_archive() ) {
		return (string) get_post_type_archive_link( (string) ( get_query_var( 'post_type' ) ?: 'oeuvre' ) );
	}
	return home_url( '/' );
}

add_action( 'wp_head', function () {
	$desc      = mrck_meta_description();
	$title     = wp_get_document_title();
	$canonical = mrck_canonical_url();

	$og_image = '';
	if ( is_singular() && has_post_thumbnail() ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'oeuvre_full' );
		if ( $src ) {
			$og_image = $src[0];
		}
	}

	echo "\n<!-- MRCK SEO -->\n";
	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canonical ) );
	printf( '<meta property="og:type" content="%s">' . "\n", is_singular() ? 'article' : 'website' );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $canonical ) );
	printf( '<meta property="og:locale" content="fr_FR">' . "\n" );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	if ( $og_image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $og_image ) );
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	} else {
		echo '<meta name="twitter:card" content="summary">' . "\n";
	}
}, 5 );

add_action( 'wp_head', function () {
	$graph = [];

	if ( is_singular( 'oeuvre' ) ) {
		$id  = get_queried_object_id();
		$img = has_post_thumbnail( $id ) ? ( wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'oeuvre_full' )[0] ?? '' ) : '';
		$h   = get_post_meta( $id, 'hauteur_cm', true );
		$w   = get_post_meta( $id, 'largeur_cm', true );

		$graph[] = array_filter( [
			'@type'       => 'VisualArtwork',
			'name'        => get_the_title( $id ),
			'image'       => $img ?: null,
			'creator'     => mrck_person_schema(),
			'dateCreated' => get_post_meta( $id, 'annee', true ) ?: null,
			'artMedium'   => get_post_meta( $id, 'support', true ) ?: null,
			'artform'     => implode( ', ', wp_get_post_terms( $id, 'technique', [ 'fields' => 'names' ] ) ) ?: null,
			'width'       => $w ? [ '@type' => 'QuantitativeValue', 'value' => $w, 'unitCode' => 'CMT' ] : null,
			'height'      => $h ? [ '@type' => 'QuantitativeValue', 'value' => $h, 'unitCode' => 'CMT' ] : null,
			'inLanguage'  => 'fr',
			'url'         => get_permalink( $id ),
		] );
	} elseif ( is_post_type_archive( 'oeuvre' ) ) {
		$graph[] = [ '@type' => 'CollectionPage', 'name' => 'Archive des œuvres', 'about' => mrck_person_schema() ];
	} elseif ( is_front_page() || is_singular( 'chapitre' ) || is_post_type_archive( 'chapitre' ) ) {
		$graph[] = mrck_person_schema();
	}

	if ( ! $graph ) {
		return;
	}
	$data = [ '@context' => 'https://schema.org', '@graph' => $graph ];
	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 6 );
