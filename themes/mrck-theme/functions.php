<?php
/**
 * Theme setup + Vite asset pipeline.
 *
 * @package mrck-theme
 */

defined( 'ABSPATH' ) || exit;

define( 'MRCK_THEME_VERSION', '0.1.0' );

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'mrck', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ] );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'align-wide' );

	register_nav_menus( [
		'primary' => __( 'Menu principal', 'mrck' ),
	] );

	// Image sizes tuned for the archive grid and detail pages.
	add_image_size( 'oeuvre_card', 800, 0, false );
	add_image_size( 'oeuvre_full', 1800, 0, false );
} );

/**
 * Enqueue the Vite bundle.
 *
 * Dev  : when no build manifest exists, load ES modules straight from the Vite
 *        dev server (`npm run dev`) for instant HMR.
 * Prod : read dist/.vite/manifest.json and enqueue the hashed, cache-busted assets.
 */
add_action( 'wp_enqueue_scripts', function () {
	$entry         = 'src/js/main.js';
	$dev_server    = 'http://localhost:5173';
	$manifest_path = get_template_directory() . '/dist/.vite/manifest.json';

	if ( ! file_exists( $manifest_path ) ) {
		// --- Development: Vite dev server (HMR) ---
		add_action( 'wp_head', function () use ( $dev_server, $entry ) {
			printf( '<script type="module" src="%s"></script>' . "\n", esc_url( $dev_server . '/@vite/client' ) );
			printf( '<script type="module" src="%s"></script>' . "\n", esc_url( $dev_server . '/' . $entry ) );
		}, 1 );
		return;
	}

	// --- Production: built assets ---
	$manifest = json_decode( (string) file_get_contents( $manifest_path ), true );
	if ( empty( $manifest[ $entry ] ) ) {
		return;
	}
	$item = $manifest[ $entry ];

	foreach ( (array) ( $item['css'] ?? [] ) as $i => $css ) {
		wp_enqueue_style( 'mrck-style-' . $i, get_theme_file_uri( 'dist/' . $css ), [], MRCK_THEME_VERSION );
	}
	if ( ! empty( $item['file'] ) ) {
		wp_enqueue_script_module( 'mrck-main', get_theme_file_uri( 'dist/' . $item['file'] ), [], MRCK_THEME_VERSION );
	}
} );

/** Mark the active menu item for assistive technology (RGAA / WCAG 4.1.2). */
add_filter( 'nav_menu_link_attributes', function ( $atts, $item ) {
	$classes = (array) ( $item->classes ?? [] );
	if ( array_intersect( [ 'current-menu-item', 'current_page_item', 'current-menu-parent' ], $classes ) ) {
		$atts['aria-current'] = 'page';
	}
	return $atts;
}, 10, 2 );
