<?php
/**
 * Uptime Fixer Theme Functions
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ALLTOOLS_VERSION', '4.4.0' );
define( 'ALLTOOLS_PATH', trailingslashit( get_template_directory() ) );
define( 'ALLTOOLS_URI', trailingslashit( get_template_directory_uri() ) );

/**
 * Safely load each inc file. If any file fails, log but don't crash.
 */
$alltools_includes = array(
    'helpers',
    'setup',
    'enqueue',
    'performance',
    'security',
    'cpt',
    'tools',
    'tools-extra',
    'tools-growth',
    'tools-seo',
    'shortcodes',
    'seo',
    'content-quality',
    'content-governance',
    'seo-yoast',
    'adsense',
    'admin',
    'importer',
    'router',
    'api',
    'api-extra',
    'api-growth',
    'api-seo',
    'no-api',
    'studio',
    'studio-tools',
);

foreach ( $alltools_includes as $alltools_inc_file ) {
    $alltools_inc_path = ALLTOOLS_PATH . 'inc/' . $alltools_inc_file . '.php';
    if ( file_exists( $alltools_inc_path ) ) {
        require_once $alltools_inc_path;
    }
}
unset( $alltools_inc_file, $alltools_inc_path );
