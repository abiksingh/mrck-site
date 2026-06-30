<?php
/**
 * REST endpoint powering the live archive filter, plus a shared query builder
 * reused by the server-rendered archive (see archive-query.php) so JS and no-JS
 * paths always agree.
 *
 * GET /wp-json/mrck/v1/oeuvres?technique=&serie=&theme=&collection=&annee_min=&annee_max=&search=&orderby=&order=&page=&per_page=
 *
 * @package mrck-archive
 */

defined( 'ABSPATH' ) || exit;

const MRCK_TAX_PARAMS = [
	'technique'  => 'technique',
	'serie'      => 'serie',
	'theme'      => 'theme_art',
	'collection' => 'collection',
];

/**
 * Translate request params into WP_Query args. Shared by REST + the archive.
 *
 * @param array $params Raw request/query params.
 * @return array WP_Query args.
 */
function mrck_build_oeuvre_query_args( array $params ): array {
	$tax_query = [ 'relation' => 'AND' ];
	foreach ( MRCK_TAX_PARAMS as $param => $taxonomy ) {
		if ( empty( $params[ $param ] ) ) {
			continue;
		}
		$raw    = $params[ $param ];
		$values = is_array( $raw ) ? $raw : explode( ',', (string) $raw );
		$slugs  = array_filter( array_map( 'sanitize_title', $values ) );
		if ( $slugs ) {
			$tax_query[] = [ 'taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $slugs ];
		}
	}

	$meta_query = [];
	$min = isset( $params['annee_min'] ) ? (int) $params['annee_min'] : 0;
	$max = isset( $params['annee_max'] ) ? (int) $params['annee_max'] : 0;
	if ( $min || $max ) {
		$meta_query[] = [
			'key'     => 'annee',
			'type'    => 'NUMERIC',
			'value'   => [ $min ?: 0, $max ?: 9999 ],
			'compare' => 'BETWEEN',
		];
	}

	$orderby = in_array( $params['orderby'] ?? 'annee', [ 'annee', 'title', 'date' ], true ) ? $params['orderby'] : 'annee';

	$args = [
		'post_type'      => 'oeuvre',
		'post_status'    => 'publish',
		'posts_per_page' => min( 60, max( 1, (int) ( $params['per_page'] ?? 24 ) ) ),
		'paged'          => max( 1, (int) ( $params['page'] ?? 1 ) ),
		'order'          => ( strtoupper( (string) ( $params['order'] ?? 'ASC' ) ) === 'DESC' ) ? 'DESC' : 'ASC',
	];

	if ( 'annee' === $orderby ) {
		$args['orderby']  = 'meta_value_num';
		$args['meta_key'] = 'annee';
	} else {
		$args['orderby'] = $orderby;
	}

	if ( count( $tax_query ) > 1 ) {
		$args['tax_query'] = $tax_query;
	}
	if ( $meta_query ) {
		$args['meta_query'] = $meta_query;
	}
	if ( ! empty( $params['search'] ) ) {
		$args['s'] = sanitize_text_field( (string) $params['search'] );
	}

	return $args;
}

/** Compact card payload for one œuvre. */
function mrck_oeuvre_card_data( int $post_id ): array {
	$image    = null;
	$thumb_id = get_post_thumbnail_id( $post_id );
	if ( $thumb_id ) {
		$src = wp_get_attachment_image_src( $thumb_id, 'oeuvre_card' );
		if ( $src ) {
			$image = [
				'src'    => $src[0],
				'w'      => $src[1],
				'h'      => $src[2],
				'srcset' => wp_get_attachment_image_srcset( $thumb_id, 'oeuvre_card' ) ?: '',
				'alt'    => (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ),
			];
		}
	}

	return [
		'id'        => $post_id,
		'title'     => get_the_title( $post_id ),
		'permalink' => get_permalink( $post_id ),
		'year'      => get_post_meta( $post_id, 'annee', true ),
		'technique' => wp_get_post_terms( $post_id, 'technique', [ 'fields' => 'names' ] ),
		'serie'     => wp_get_post_terms( $post_id, 'serie', [ 'fields' => 'names' ] ),
		'image'     => $image,
	];
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'mrck/v1', '/oeuvres', [
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => '__return_true',
		'callback'            => function ( WP_REST_Request $request ) {
			$args           = mrck_build_oeuvre_query_args( $request->get_params() );
			$args['fields'] = 'ids';
			$query          = new WP_Query( $args );

			return new WP_REST_Response( [
				'items' => array_map( 'mrck_oeuvre_card_data', $query->posts ),
				'total' => (int) $query->found_posts,
				'pages' => (int) $query->max_num_pages,
				'page'  => max( 1, (int) ( $request->get_param( 'page' ) ?: 1 ) ),
			], 200 );
		},
	] );
} );
