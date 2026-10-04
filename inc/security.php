<?php
/**
 * Lightweight hardening for the Uptime Fixer theme.
 *
 * This file adds safe WordPress hardening without changing the existing tool logic.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hide public WordPress/version hints.
 */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );

/**
 * Disable the built-in theme/plugin editor from wp-admin.
 * This reduces damage if an admin account is compromised.
 */
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
    define( 'DISALLOW_FILE_EDIT', true );
}
add_filter( 'file_edit_allowed', '__return_false' );

/**
 * Disable XML-RPC unless another plugin explicitly re-enables it later.
 * This helps reduce brute-force and pingback abuse.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'wp_headers', function( $headers ) {
    unset( $headers['X-Pingback'] );
    return $headers;
} );

/**
 * Generic login errors so attackers cannot confirm usernames.
 */
add_filter( 'login_errors', function() {
    return __( 'Invalid login details.', 'alltools' );
} );

/**
 * Stop simple author ID enumeration.
 */
add_action( 'template_redirect', function() {
    if ( ! is_admin() && isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        wp_safe_redirect( home_url( '/' ), 301 );
        exit;
    }
} );

/**
 * Remove public REST user endpoints to reduce user enumeration.
 */
add_filter( 'rest_endpoints', function( $endpoints ) {
    if ( isset( $endpoints['/wp/v2/users'] ) ) {
        unset( $endpoints['/wp/v2/users'] );
    }
    if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
        unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
    }
    return $endpoints;
} );

/**
 * Security headers.
 *
 * Google documents that AdSense resource hosts can change over time. A narrow
 * host list therefore interrupts ad delivery without warning. The default
 * policy permits HTTPS subresources while retaining the most useful document
 * boundaries. Sites with a nonce-based CSP can replace this value with the
 * alltools_content_security_policy filter.
 */
add_action( 'send_headers', function() {
    if ( headers_sent() || is_admin() ) {
        return;
    }

    header( 'X-Content-Type-Options: nosniff' );
    header( 'X-Frame-Options: SAMEORIGIN' );
    header( 'Referrer-Policy: strict-origin-when-cross-origin' );
    header( 'Permissions-Policy: camera=(), microphone=(self), display-capture=(self), geolocation=()' );
    $content_security_policy = "default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'self'; form-action 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' 'wasm-unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; font-src 'self' data: https:; img-src 'self' data: blob: https:; media-src 'self' data: blob: https:; connect-src 'self' blob: https:; worker-src 'self' blob: https:; frame-src https:; upgrade-insecure-requests";
    $content_security_policy = (string) apply_filters( 'alltools_content_security_policy', $content_security_policy );
    if ( '' !== trim( $content_security_policy ) ) {
        header( 'Content-Security-Policy: ' . trim( $content_security_policy ) );
    }

    if ( is_ssl() ) {
        header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains' );
    }
} );

/** Do not expose PHP error details to public visitors. */
add_filter( 'wp_php_error_message', function() {
    return __( 'A temporary error occurred. Please try again later.', 'alltools' );
} );

/**
 * Reduce exposure of REST links in the public page source.
 */
remove_action( 'wp_head', 'rest_output_link_wp_head' );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );

/**
 * Disable file editing in Customizer additional CSS export endpoints.
 */
add_filter( 'map_meta_cap', function( $caps, $cap ) {
    if ( 'edit_themes' === $cap || 'edit_plugins' === $cap ) {
        $caps[] = 'do_not_allow';
    }
    return $caps;
}, 10, 2 );
