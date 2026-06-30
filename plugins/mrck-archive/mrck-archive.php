<?php
/**
 * Plugin Name:       MRCK Archive
 * Description:       Content backbone for the Marie-Renée Chevallier-Kervern digital archive: the Œuvre custom post type, taxonomies, fields, admin tooling, REST filtering and import pipeline. Kept in a plugin (not the theme) so the catalogue survives any redesign.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            MRCK
 * Text Domain:       mrck
 */

defined( 'ABSPATH' ) || exit;

define( 'MRCK_ARCHIVE_VERSION', '0.1.0' );
define( 'MRCK_ARCHIVE_DIR', plugin_dir_path( __FILE__ ) );

require_once MRCK_ARCHIVE_DIR . 'includes/post-types.php';
require_once MRCK_ARCHIVE_DIR . 'includes/taxonomies.php';
require_once MRCK_ARCHIVE_DIR . 'includes/fields.php';
require_once MRCK_ARCHIVE_DIR . 'includes/admin-columns.php';
require_once MRCK_ARCHIVE_DIR . 'includes/cli-import.php';
require_once MRCK_ARCHIVE_DIR . 'includes/rest-filter.php';
require_once MRCK_ARCHIVE_DIR . 'includes/archive-query.php';
require_once MRCK_ARCHIVE_DIR . 'includes/chapitres.php';
require_once MRCK_ARCHIVE_DIR . 'includes/cli-import-vie.php';

/**
 * Register the CPT + taxonomies on activation, then flush rewrite rules so the
 * /oeuvres/ permalinks work immediately.
 */
register_activation_hook( __FILE__, function () {
	do_action( 'init' );
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
