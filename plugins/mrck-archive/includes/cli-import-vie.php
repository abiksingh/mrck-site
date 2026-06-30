<?php
/**
 * WP-CLI: import the "Vie" biography chapters from a JSON file.
 *
 *   wp mrck import-vie wp-content/mrck-import/chapitres.json --images=wp-content/mrck-import
 *
 * @package mrck-archive
 */

defined( 'ABSPATH' ) || exit;

if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	return;
}

WP_CLI::add_command( 'mrck import-vie', function ( $args, $assoc_args ) {
	$file       = $args[0];
	$images_dir = isset( $assoc_args['images'] ) ? rtrim( $assoc_args['images'], '/' ) : '';
	$force_imgs = isset( $assoc_args['force-images'] );

	if ( ! file_exists( $file ) ) {
		WP_CLI::error( "JSON not found: {$file}" );
	}
	$chapters = json_decode( (string) file_get_contents( $file ), true );
	if ( ! is_array( $chapters ) ) {
		WP_CLI::error( 'Could not parse JSON.' );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$sideload = function ( $path, $post_id, $caption ) {
		$tmp = wp_tempnam( basename( $path ) );
		if ( ! $tmp || ! copy( $path, $tmp ) ) {
			return 0;
		}
		$id = media_handle_sideload( [ 'name' => basename( $path ), 'tmp_name' => $tmp ], $post_id, $caption );
		if ( is_wp_error( $id ) ) {
			@unlink( $tmp );
			return 0;
		}
		if ( $caption ) {
			wp_update_post( [ 'ID' => $id, 'post_excerpt' => $caption ] );
		}
		return (int) $id;
	};

	$created = 0;
	$updated = 0;
	foreach ( $chapters as $ch ) {
		$title = trim( (string) ( $ch['title'] ?? '' ) );
		if ( '' === $title ) {
			continue;
		}

		$existing = get_posts( [ 'post_type' => 'chapitre', 'post_status' => 'any', 'title' => $title, 'fields' => 'ids', 'posts_per_page' => 1 ] );
		$existing = $existing ? (int) $existing[0] : 0;

		$content = implode( "\n\n", array_map(
			fn( $p ) => '<!-- wp:paragraph --><p>' . esc_html( $p ) . '</p><!-- /wp:paragraph -->',
			array_filter( (array) ( $ch['intro'] ?? [] ) )
		) );

		$postarr = [
			'post_type'    => 'chapitre',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_content' => $content,
			'menu_order'   => (int) ( $ch['order'] ?? 0 ),
		];
		if ( $existing ) {
			$postarr['ID'] = $existing;
		}

		$post_id = wp_insert_post( $postarr, true );
		if ( is_wp_error( $post_id ) ) {
			WP_CLI::warning( $post_id->get_error_message() );
			continue;
		}
		$existing ? $updated++ : $created++;

		if ( function_exists( 'update_field' ) && ! empty( $ch['subtitle'] ) ) {
			update_field( 'sous_titre', $ch['subtitle'], $post_id );
		}

		if ( $images_dir && ( $force_imgs || ! has_post_thumbnail( $post_id ) ) ) {
			$gallery = [];
			foreach ( (array) ( $ch['images'] ?? [] ) as $im ) {
				foreach ( (array) ( $im['files'] ?? [] ) as $rel ) {
					$path = $images_dir . '/' . $rel;
					if ( ! file_exists( $path ) ) {
						continue;
					}
					$att = $sideload( $path, $post_id, (string) ( $im['caption'] ?? '' ) );
					if ( $att ) {
						$gallery[] = $att;
					}
					break; // one display image per entry
				}
			}
			if ( $gallery ) {
				set_post_thumbnail( $post_id, $gallery[0] );
				if ( function_exists( 'update_field' ) ) {
					update_field( 'galerie', $gallery, $post_id );
				}
			}
		}

		WP_CLI::log( sprintf( '%s %s (#%d, %d images)', $existing ? 'Updated:' : 'Created:', $title, $post_id, count( (array) ( $ch['images'] ?? [] ) ) ) );
	}

	WP_CLI::success( "Vie imported — created {$created}, updated {$updated}." );
} );
