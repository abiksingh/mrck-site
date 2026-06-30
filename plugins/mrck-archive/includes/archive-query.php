<?php
/**
 * Make the server-rendered œuvre archive honour the same filter params as the
 * REST endpoint, so filtering works without JavaScript, stays shareable/SEO-able,
 * and matches the live JS results exactly.
 *
 * @package mrck-archive
 */

defined( 'ABSPATH' ) || exit;

add_action( 'pre_get_posts', function ( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'oeuvre' ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public read-only filtering.
	$args = mrck_build_oeuvre_query_args( wp_unslash( $_GET ) );

	foreach ( [ 'tax_query', 'meta_query', 'meta_key', 'orderby', 'order', 's' ] as $key ) {
		if ( isset( $args[ $key ] ) ) {
			$query->set( $key, $args[ $key ] );
		}
	}
	$query->set( 'posts_per_page', 24 );
} );
