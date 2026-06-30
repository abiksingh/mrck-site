<?php
/**
 * Display helpers.
 *
 * Many of MRCK's abstract works are genuinely untitled ("Sans titre"). They are
 * distinct works (each has its own inventory number), so on the front end we
 * append the catalogue number to tell them apart — in cards, detail pages,
 * the REST grid and the document title alike.
 *
 * @package mrck-archive
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'the_title', function ( $title, $post_id = 0 ) {
	if ( is_admin() || ! $post_id || get_post_type( $post_id ) !== 'oeuvre' ) {
		return $title;
	}
	if ( 'sans titre' === mb_strtolower( trim( (string) $title ) ) ) {
		$inv = get_post_meta( $post_id, 'numero_inventaire', true );
		if ( $inv ) {
			$title .= ' · n° ' . $inv;
		}
	}
	return $title;
}, 10, 2 );
