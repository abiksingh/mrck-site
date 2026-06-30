<?php
/**
 * WP-CLI importer: load œuvres (and their images) from a CSV.
 *
 * Usage (inside wp-env):
 *   wp mrck import wp-content/mrck-import/oeuvres.csv --images=wp-content/mrck-import
 *
 * Idempotent: rows are matched on `numero_inventaire` (then exact title), so
 * re-running updates existing works instead of creating duplicates — you can
 * re-sync from the master spreadsheet any time.
 *
 * @package mrck-archive
 */

defined( 'ABSPATH' ) || exit;

if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	return;
}

class MRCK_Import_Command {

	/** Meta fields imported verbatim from same-named CSV columns. */
	const META_COLUMNS = [
		'annee', 'date_affichee', 'hauteur_cm', 'largeur_cm', 'profondeur_cm',
		'dimensions_affichees', 'support', 'numero_inventaire', 'signature', 'credit',
	];

	/** CSV column => taxonomy slug. */
	const TAXONOMY_COLUMNS = [
		'technique'  => 'technique',
		'serie'      => 'serie',
		'theme'      => 'theme_art',
		'collection' => 'collection',
	];

	/**
	 * Import œuvres from a CSV file.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Path to the CSV file.
	 *
	 * [--images=<dir>]
	 * : Directory containing the image files referenced in the `images` column.
	 *
	 * [--dry-run]
	 * : Parse and report what would happen without writing anything.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mrck import wp-content/mrck-import/oeuvres.csv --images=wp-content/mrck-import
	 *     wp mrck import oeuvres.csv --dry-run
	 *
	 * @when after_wp_load
	 */
	public function import( $args, $assoc_args ) {
		$file       = $args[0];
		$images_dir = isset( $assoc_args['images'] ) ? rtrim( $assoc_args['images'], '/' ) : '';
		$dry_run    = isset( $assoc_args['dry-run'] );
		$force_imgs = isset( $assoc_args['force-images'] );

		if ( ! file_exists( $file ) ) {
			WP_CLI::error( "CSV not found: {$file}" );
		}

		$handle = fopen( $file, 'r' );
		if ( ! $handle ) {
			WP_CLI::error( "Cannot open CSV: {$file}" );
		}

		if ( ! $dry_run ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$header = fgetcsv( $handle );
		if ( ! $header ) {
			WP_CLI::error( 'CSV appears to be empty.' );
		}
		$header = array_map( static fn( $h ) => strtolower( trim( (string) $h ) ), $header );

		$created = 0;
		$updated = 0;
		$skipped = 0;
		$line    = 1;

		while ( ( $data = fgetcsv( $handle ) ) !== false ) {
			$line++;
			$data = array_slice( array_pad( $data, count( $header ), '' ), 0, count( $header ) );
			$row  = array_combine( $header, array_map( static fn( $v ) => trim( (string) $v ), $data ) );

			if ( empty( $row['title'] ) ) {
				WP_CLI::warning( "Line {$line}: empty title — skipped." );
				$skipped++;
				continue;
			}

			$existing = $this->find_existing( $row );

			if ( $dry_run ) {
				WP_CLI::log( sprintf( '%s %s', $existing ? '[update]' : '[create]', $row['title'] ) );
				continue;
			}

			$postarr = [
				'post_type'    => 'oeuvre',
				'post_status'  => 'publish',
				'post_title'   => $row['title'],
				'post_content' => $row['description'] ?? '',
			];
			if ( $existing ) {
				$postarr['ID'] = $existing;
			}

			$post_id = wp_insert_post( $postarr, true );
			if ( is_wp_error( $post_id ) ) {
				WP_CLI::warning( "Line {$line}: " . $post_id->get_error_message() );
				$skipped++;
				continue;
			}
			$existing ? $updated++ : $created++;

			foreach ( self::META_COLUMNS as $key ) {
				if ( isset( $row[ $key ] ) && '' !== $row[ $key ] ) {
					$this->set_field( $key, $row[ $key ], $post_id );
				}
			}

			// Split on "|" only — series/collection names legitimately contain commas.
			foreach ( self::TAXONOMY_COLUMNS as $column => $taxonomy ) {
				if ( ! empty( $row[ $column ] ) ) {
					$terms = array_filter( array_map( 'trim', explode( '|', $row[ $column ] ) ) );
					wp_set_object_terms( $post_id, $terms, $taxonomy, false );
				}
			}

			$has_images = $existing && has_post_thumbnail( $post_id );
			if ( ! empty( $row['images'] ) && $images_dir && ( $force_imgs || ! $has_images ) ) {
				$gallery = [];
				foreach ( array_filter( array_map( 'trim', explode( ';', $row['images'] ) ) ) as $filename ) {
					$path = $images_dir . '/' . $filename;
					if ( ! file_exists( $path ) ) {
						WP_CLI::warning( "Line {$line}: image not found — {$path}" );
						continue;
					}
					$attachment_id = $this->sideload_image( $path, $post_id );
					if ( $attachment_id ) {
						update_post_meta( $attachment_id, '_wp_attachment_image_alt', $row['title'] );
						$gallery[] = $attachment_id;
					}
				}
				if ( $gallery ) {
					set_post_thumbnail( $post_id, $gallery[0] );
					$this->set_field( 'galerie', $gallery, $post_id );
				}
			}

			WP_CLI::log( sprintf( '%s %s (#%d)', $existing ? 'Updated:' : 'Created:', $row['title'], $post_id ) );
		}

		fclose( $handle );

		if ( $dry_run ) {
			WP_CLI::success( 'Dry run complete.' );
			return;
		}
		WP_CLI::success( "Import complete — created {$created}, updated {$updated}, skipped {$skipped}." );
	}

	/**
	 * Find an existing œuvre. The inventory number is the unique catalogue key, so
	 * when it is present we match on it ONLY — never fall back to title, or distinct
	 * works that happen to share a title (e.g. several "Autoportrait") would merge.
	 */
	private function find_existing( array $row ): int {
		$args = [ 'post_type' => 'oeuvre', 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => 1 ];
		if ( ! empty( $row['numero_inventaire'] ) ) {
			// Match inventory AND title: the source occasionally reuses one catalogue
			// number for several distinct works, which must remain separate records.
			$args['meta_key']   = 'numero_inventaire';
			$args['meta_value'] = $row['numero_inventaire'];
		}
		$args['title'] = $row['title'];
		$ids = get_posts( $args );
		return $ids ? (int) $ids[0] : 0;
	}

	/** Store a value through SCF/ACF when available, else as plain post meta. */
	private function set_field( string $key, $value, int $post_id ): void {
		if ( function_exists( 'update_field' ) ) {
			update_field( $key, $value, $post_id );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}

	/** Side-load an image file into the media library; returns the attachment ID. */
	private function sideload_image( string $path, int $post_id ): int {
		$tmp = wp_tempnam( basename( $path ) );
		if ( ! $tmp || ! copy( $path, $tmp ) ) {
			return 0;
		}
		$file_array    = [ 'name' => basename( $path ), 'tmp_name' => $tmp ];
		$attachment_id = media_handle_sideload( $file_array, $post_id );
		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $tmp );
			WP_CLI::warning( 'Image import failed: ' . $attachment_id->get_error_message() );
			return 0;
		}
		return (int) $attachment_id;
	}
}

WP_CLI::add_command( 'mrck', 'MRCK_Import_Command' );
