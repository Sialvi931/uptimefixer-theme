<?php
/**
 * Reliable AdSense ads.txt support.
 *
 * Creates a real /ads.txt file in the WordPress document root when hosting
 * permissions allow it, and keeps an early WordPress response as a fallback.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Return the AdSense publisher ID used by this site. */
function alltools_adsense_publisher_id() {
    $configured = function_exists( 'alltools_get' ) ? alltools_get( 'adsense_publisher_id', 'pub-9901210156464210' ) : 'pub-9901210156464210';
    $publisher_id = (string) apply_filters( 'alltools_adsense_publisher_id', $configured );
    $publisher_id = preg_replace( '/^(?:ca-)?pub-/i', '', trim( $publisher_id ) );
    $publisher_id = preg_replace( '/\D+/', '', $publisher_id );

    return 16 === strlen( $publisher_id ) ? 'pub-' . $publisher_id : '';
}

/** Publish Google's supported ownership meta tag without duplicating ad units. */
add_action( 'wp_head', 'alltools_adsense_account_meta', 0 );
function alltools_adsense_account_meta() {
    $publisher_id = alltools_adsense_publisher_id();
    if ( ! $publisher_id ) return;
    echo '<meta name="google-adsense-account" content="' . esc_attr( 'ca-' . $publisher_id ) . '">' . "\n";
}

/**
 * Keep ad inventory limited to pages that pass the theme's editorial gate.
 * External ad plugins can use the alltools_ads_allowed_on_current_page filter.
 */
function alltools_ads_allowed() {
    $allowed = true;
    if ( is_admin() || is_feed() || is_search() || is_404() ) $allowed = false;
    if ( function_exists( 'alltools_current_content_needs_noindex' ) && alltools_current_content_needs_noindex() ) $allowed = false;
    if ( function_exists( 'alltools_should_noindex_current_page' ) && alltools_should_noindex_current_page() ) $allowed = false;
    return (bool) apply_filters( 'alltools_ads_allowed_on_current_page', $allowed );
}

/** Suppress conventionally enqueued AdSense scripts on non-reviewed inventory. */
add_filter( 'script_loader_tag', function( $tag, $handle ) {
    if ( alltools_ads_allowed() ) return $tag;
    $is_adsense = false !== stripos( (string) $tag, 'pagead2.googlesyndication.com' )
        || false !== stripos( (string) $tag, 'adsbygoogle' )
        || false !== stripos( (string) $handle, 'adsense' );
    return $is_adsense ? '' : $tag;
}, 999, 2 );

add_filter( 'body_class', function( $classes ) {
    if ( ! alltools_ads_allowed() ) $classes[] = 'ufx-ads-suppressed';
    return $classes;
}, 999 );

/** Return the exact authorised-seller record expected by Google. */
function alltools_ads_txt_record() {
    $publisher_id = alltools_adsense_publisher_id();
    return $publisher_id ? 'google.com, ' . $publisher_id . ', DIRECT, f08c47fec0942fa0' : '';
}

/** Check whether the current request is exactly the domain-root ads.txt URL. */
function alltools_is_ads_txt_request() {
    $request_path = isset( $_SERVER['REQUEST_URI'] )
        ? (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH )
        : '';

    return '/ads.txt' === untrailingslashit( $request_path );
}

/**
 * Serve ads.txt before normal routing when the request reaches WordPress.
 * A physical file created below normally takes precedence at web-server level.
 */
function alltools_serve_ads_txt() {
    if ( ! alltools_is_ads_txt_request() ) return;

    $method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
    if ( ! in_array( $method, array( 'GET', 'HEAD' ), true ) ) {
        status_header( 405 );
        header( 'Allow: GET, HEAD' );
        exit;
    }

    $record = alltools_ads_txt_record();
    if ( ! $record ) {
        status_header( 404 );
        exit;
    }

    status_header( 200 );
    header( 'Content-Type: text/plain; charset=utf-8' );
    header( 'Cache-Control: public, max-age=300, must-revalidate' );
    header( 'X-Content-Type-Options: nosniff' );

    if ( 'HEAD' !== $method ) echo $record . "\n";
    exit;
}
add_action( 'init', 'alltools_serve_ads_txt', -999 );
add_action( 'template_redirect', 'alltools_serve_ads_txt', 0 );

/** Record the result of physical-file installation for an admin-only notice. */
function alltools_set_ads_txt_install_status( $status ) {
    update_option( 'alltools_ads_txt_install_status', sanitize_key( $status ), false );
}

/**
 * Create or safely extend the real WordPress-root ads.txt file.
 * Existing seller records are preserved; only this site's exact Google line is
 * appended when missing. No unrelated root file is overwritten or deleted.
 */
function alltools_install_physical_ads_txt() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $record = alltools_ads_txt_record();
    if ( ! $record ) {
        alltools_set_ads_txt_install_status( 'invalid-publisher-id' );
        return;
    }

    $root   = wp_normalize_path( trailingslashit( ABSPATH ) );
    $target = wp_normalize_path( $root . 'ads.txt' );

    if ( $target !== $root . 'ads.txt' || is_link( $target ) ) {
        alltools_set_ads_txt_install_status( 'unsafe-target' );
        return;
    }

    $existing = '';
    if ( file_exists( $target ) ) {
        if ( ! is_file( $target ) || ! is_readable( $target ) ) {
            alltools_set_ads_txt_install_status( 'unreadable' );
            return;
        }
        $existing = (string) @file_get_contents( $target );
        if ( false !== strpos( $existing, $record ) ) {
            alltools_set_ads_txt_install_status( 'ready' );
            return;
        }
        if ( ! is_writable( $target ) ) {
            alltools_set_ads_txt_install_status( 'not-writable' );
            return;
        }
    } elseif ( ! is_writable( $root ) ) {
        alltools_set_ads_txt_install_status( 'root-not-writable' );
        return;
    }

    $contents = '' !== trim( $existing ) ? rtrim( $existing, "\r\n" ) . "\n" . $record . "\n" : $record . "\n";
    $written  = @file_put_contents( $target, $contents, LOCK_EX );

    if ( false === $written ) {
        alltools_set_ads_txt_install_status( 'write-failed' );
        return;
    }

    @chmod( $target, 0644 );
    clearstatcache( true, $target );
    $verified = is_readable( $target ) && false !== strpos( (string) @file_get_contents( $target ), $record );
    alltools_set_ads_txt_install_status( $verified ? 'ready' : 'verify-failed' );

    if ( $verified ) {
        do_action( 'litespeed_purge_url', home_url( '/ads.txt' ) );
    }
}
add_action( 'admin_init', 'alltools_install_physical_ads_txt', 2 );
add_action( 'after_switch_theme', 'alltools_install_physical_ads_txt' );

/** Show a clear fallback instruction only when hosting blocks root-file writes. */
add_action( 'admin_notices', 'alltools_ads_txt_admin_notice' );
function alltools_ads_txt_admin_notice() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $status = (string) get_option( 'alltools_ads_txt_install_status', '' );
    if ( '' === $status || 'ready' === $status ) return;
    ?>
    <div class="notice notice-warning">
        <p><strong><?php esc_html_e( 'Uptime Fixer ads.txt:', 'alltools' ); ?></strong>
        <?php esc_html_e( 'Your hosting permissions blocked automatic creation of the root /ads.txt file. Upload the ads.txt file bundled inside this theme to the WordPress/public_html root, then clear the hosting or CDN cache.', 'alltools' ); ?></p>
    </div>
    <?php
}
