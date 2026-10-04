<?php
/**
 * Backend AJAX endpoints for the 6 website tools.
 * These do real HTTP/SSL/DNS checks server-side.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* All endpoints are exposed publicly via admin-ajax.php */
$ufx_endpoints = array( 'nonce', 'uptime', 'speed', 'http_status', 'ssl', 'dns', 'redirect', 'domain_expiry', 'broken_links', 'security_headers', 'mixed_content' );
foreach ( $ufx_endpoints as $ep ) {
    add_action( 'wp_ajax_ufx_'        . $ep, 'ufx_api_' . $ep );
    add_action( 'wp_ajax_nopriv_ufx_' . $ep, 'ufx_api_' . $ep );
}
unset( $ep, $ufx_endpoints );

/**
 * Normalize / validate a user-supplied URL or domain.
 */
function ufx_clean_url( $raw, $allow_no_scheme = true ) {
    $raw = trim( (string) $raw );
    if ( ! $raw ) return '';
    if ( ! preg_match( '#^https?://#i', $raw ) ) {
        if ( ! $allow_no_scheme ) return '';
        $raw = 'https://' . $raw;
    }
    $parts = wp_parse_url( $raw );
    if ( empty( $parts['host'] ) || ! empty( $parts['user'] ) || ! empty( $parts['pass'] ) ) return '';
    if ( isset( $parts['port'] ) && ! in_array( (int) $parts['port'], array( 80, 443 ), true ) ) return '';
    $host = strtolower( rtrim( (string) $parts['host'], '.' ) );
    if ( ! ufx_host_is_public( $host ) ) return '';
    return esc_url_raw( $raw, array( 'http', 'https' ) );
}

/** Block localhost, private, reserved, multicast and link-local targets. */
function ufx_ip_is_public( $ip ) {
    return false !== filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
}

function ufx_host_is_public( $host ) {
    $host = strtolower( trim( (string) $host, "[] .\t\n\r\0\x0B" ) );
    if ( ! $host || strlen( $host ) > 253 || 'localhost' === $host || preg_match( '/(?:^|\.)local(?:host)?$/i', $host ) ) return false;
    if ( filter_var( $host, FILTER_VALIDATE_IP ) ) return ufx_ip_is_public( $host );
    if ( ! preg_match( '/^[a-z0-9.-]+$/i', $host ) || false === strpos( $host, '.' ) ) return false;
    $ips = gethostbynamel( $host );
    if ( ! is_array( $ips ) || ! $ips ) return false;
    foreach ( $ips as $ip ) {
        if ( ! ufx_ip_is_public( $ip ) ) return false;
    }
    return true;
}

/** Safe wrappers enforce WordPress URL validation and bounded responses. */
function ufx_safe_remote_get( $url, $args = array() ) {
    $args['reject_unsafe_urls'] = true;
    $args['sslverify'] = true;
    $args['limit_response_size'] = isset( $args['limit_response_size'] ) ? min( 2097152, (int) $args['limit_response_size'] ) : 1048576;
    return wp_safe_remote_get( $url, $args );
}

function ufx_safe_remote_head( $url, $args = array() ) {
    $args['reject_unsafe_urls'] = true;
    $args['sslverify'] = true;
    return wp_safe_remote_head( $url, $args );
}

/** Per-IP throttling for public diagnostics. A nonce alone is not rate limiting. */
function ufx_rate_limit_public_request() {
    $action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'unknown';
    $ip     = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
    $key    = 'ufx_rl_' . substr( hash_hmac( 'sha256', $action . '|' . $ip, wp_salt( 'nonce' ) ), 0, 32 );
    $count  = (int) get_transient( $key );
    if ( $count >= 20 ) {
        wp_send_json_error( array( 'message' => 'Too many requests. Please wait a minute and try again.' ), 429 );
    }
    set_transient( $key, $count + 1, MINUTE_IN_SECONDS );
}

function ufx_extract_host( $url ) {
    if ( ! $url ) return '';
    $p = wp_parse_url( $url );
    return isset( $p['host'] ) ? $p['host'] : '';
}

function ufx_check_nonce() {
    $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
    if ( ! $nonce || ! wp_verify_nonce( $nonce, 'ufx_api' ) ) {
        wp_send_json_error( array( 'message' => 'Invalid request.' ), 403 );
    }
    ufx_rate_limit_public_request();
}

/** Harden existing UptimeFixer HTTP requests, including legacy calls. */
add_filter( 'http_request_args', 'ufx_harden_http_request_args', 10, 2 );
function ufx_harden_http_request_args( $args, $url ) {
    $agent = isset( $args['user-agent'] ) ? (string) $args['user-agent'] : '';
    if ( false !== stripos( $agent, 'UptimeFixer' ) ) {
        $args['reject_unsafe_urls'] = true;
        $args['sslverify'] = true;
        if ( empty( $args['limit_response_size'] ) ) $args['limit_response_size'] = 1048576;
    }
    return $args;
}

/**
 * Issue a fresh public nonce for cached pages.
 * Public website tools do not need a logged-in user, but AJAX should still use a current nonce.
 */
function ufx_api_nonce() {
    ufx_rate_limit_public_request();
    nocache_headers();
    wp_send_json_success( array(
        'nonce' => wp_create_nonce( 'ufx_api' ),
    ) );
}


function ufx_status_message( $code ) {
    $map = array(
        200 => 'OK', 201 => 'Created', 204 => 'No Content',
        301 => 'Moved Permanently', 302 => 'Found', 303 => 'See Other', 304 => 'Not Modified', 307 => 'Temporary Redirect', 308 => 'Permanent Redirect',
        400 => 'Bad Request', 401 => 'Unauthorized', 403 => 'Forbidden', 404 => 'Not Found', 429 => 'Too Many Requests',
        500 => 'Internal Server Error', 502 => 'Bad Gateway', 503 => 'Service Unavailable', 504 => 'Gateway Timeout',
    );
    return isset( $map[ (int) $code ] ) ? $map[ (int) $code ] : '';
}

function ufx_abs_redirect_url( $base, $location ) {
    $location = trim( (string) $location );
    if ( $location === '' ) return '';
    if ( preg_match( '#^https?://#i', $location ) ) return $location;

    $parts = wp_parse_url( $base );
    if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) return '';
    if ( strpos( $location, '//' ) === 0 ) return $parts['scheme'] . ':' . $location;

    $root = $parts['scheme'] . '://' . $parts['host'] . ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' );
    if ( strpos( $location, '/' ) === 0 ) return $root . $location;

    $path = isset( $parts['path'] ) ? $parts['path'] : '/';
    $dir  = preg_replace( '#/[^/]*$#', '/', $path );
    return $root . $dir . $location;
}

/* ──────────────────────────────────────────────────────
   1) UPTIME CHECKER
   ────────────────────────────────────────────────────── */
function ufx_api_uptime() {
    ufx_check_nonce();
    $url = ufx_clean_url( isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : '' );
    if ( ! $url ) wp_send_json_error( array( 'message' => 'Please enter a valid URL.' ) );

    $start = microtime( true );
    $res = ufx_safe_remote_head( $url, array(
        'timeout'     => 10,
        'redirection' => 5,
        'user-agent'  => 'UptimeFixer/1.0',
    ) );
    $elapsed_ms = (int) round( ( microtime( true ) - $start ) * 1000 );

    if ( is_wp_error( $res ) ) {
        // Try GET as fallback (some hosts block HEAD)
        $res = ufx_safe_remote_get( $url, array(
            'timeout'     => 10,
            'redirection' => 5,
            'user-agent'  => 'UptimeFixer/1.0',
        ) );
        $elapsed_ms = (int) round( ( microtime( true ) - $start ) * 1000 );
    }

    if ( is_wp_error( $res ) ) {
        wp_send_json_success( array(
            'online'   => false,
            'status'   => 0,
            'message'  => $res->get_error_message(),
            'response_time' => $elapsed_ms,
        ) );
    }

    $code    = wp_remote_retrieve_response_code( $res );
    $headers = wp_remote_retrieve_headers( $res );
    $server  = isset( $headers['server'] ) ? (string) $headers['server'] : 'Unknown';
    $online  = ( $code >= 200 && $code < 400 );

    // IP lookup
    $host = ufx_extract_host( $url );
    $ip   = $host ? @gethostbyname( $host ) : '';
    if ( $ip === $host ) $ip = '';

    wp_send_json_success( array(
        'online'        => $online,
        'status'        => $code,
        'response_time' => $elapsed_ms,
        'server'        => $server,
        'ip'            => $ip,
        'host'          => $host,
        'checked_at'    => current_time( 'mysql' ),
    ) );
}


/* ──────────────────────────────────────────────────────
   SPEED TEST HELPERS
   ────────────────────────────────────────────────────── */
function ufx_speed_format_bytes( $bytes ) {
    $bytes = max( 0, (int) $bytes );
    if ( $bytes >= 1048576 ) return number_format( $bytes / 1048576, 2 ) . ' MB';
    if ( $bytes >= 1024 ) return number_format( $bytes / 1024, 1 ) . ' KB';
    return $bytes . ' B';
}

function ufx_speed_url_origin( $url ) {
    $p = wp_parse_url( $url );
    if ( empty( $p['scheme'] ) || empty( $p['host'] ) ) return '';
    return strtolower( $p['scheme'] . '://' . $p['host'] . ( isset( $p['port'] ) ? ':' . $p['port'] : '' ) );
}

function ufx_speed_abs_url( $base, $value ) {
    $value = trim( html_entity_decode( (string) $value, ENT_QUOTES ) );
    if ( $value === '' ) return '';
    if ( strpos( $value, 'data:' ) === 0 || strpos( $value, 'mailto:' ) === 0 || strpos( $value, 'tel:' ) === 0 || strpos( $value, '#' ) === 0 ) return '';

    // First URL from srcset.
    if ( strpos( $value, ',' ) !== false && ! preg_match( '#^https?://#i', $value ) ) {
        $first = trim( explode( ',', $value )[0] );
        $bits  = preg_split( '/\s+/', $first );
        $value = isset( $bits[0] ) ? $bits[0] : $value;
    }

    if ( preg_match( '#^https?://#i', $value ) ) return esc_url_raw( $value );

    $bp = wp_parse_url( $base );
    if ( empty( $bp['scheme'] ) || empty( $bp['host'] ) ) return '';
    if ( strpos( $value, '//' ) === 0 ) return esc_url_raw( $bp['scheme'] . ':' . $value );

    $root = $bp['scheme'] . '://' . $bp['host'] . ( isset( $bp['port'] ) ? ':' . $bp['port'] : '' );
    if ( strpos( $value, '/' ) === 0 ) return esc_url_raw( $root . $value );

    $path = isset( $bp['path'] ) ? $bp['path'] : '/';
    $dir  = preg_replace( '#/[^/]*$#', '/', $path );
    $abs  = $root . $dir . $value;

    // Normalize /./ and /../ segments.
    $parts = wp_parse_url( $abs );
    if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) return esc_url_raw( $abs );
    $segments = array();
    foreach ( explode( '/', isset( $parts['path'] ) ? $parts['path'] : '' ) as $seg ) {
        if ( $seg === '' || $seg === '.' ) continue;
        if ( $seg === '..' ) array_pop( $segments );
        else $segments[] = $seg;
    }
    $path = '/' . implode( '/', $segments );
    $out  = $parts['scheme'] . '://' . $parts['host'] . ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' ) . $path;
    if ( isset( $parts['query'] ) ) $out .= '?' . $parts['query'];
    return esc_url_raw( $out );
}

function ufx_speed_resource_type( $url, $tag = '', $attrs = '' ) {
    $tag   = strtolower( (string) $tag );
    $attrs = strtolower( (string) $attrs );
    $path  = strtolower( (string) wp_parse_url( $url, PHP_URL_PATH ) );

    if ( $tag === 'script' || preg_match( '/\.(m?js)(\?|$)/', $path ) ) return 'script';
    if ( preg_match( '/\.(woff2?|ttf|otf|eot)(\?|$)/', $path ) || strpos( $attrs, 'as="font"' ) !== false || strpos( $attrs, "as='font'" ) !== false ) return 'font';
    if ( $tag === 'img' || $tag === 'source' || preg_match( '/\.(jpe?g|png|gif|webp|avif|svg|ico)(\?|$)/', $path ) ) return 'image';
    if ( $tag === 'iframe' ) return 'document';
    if ( $tag === 'link' && ( strpos( $attrs, 'stylesheet' ) !== false || preg_match( '/\.(css)(\?|$)/', $path ) ) ) return 'stylesheet';
    if ( preg_match( '/\.(css)(\?|$)/', $path ) ) return 'stylesheet';
    return 'document';
}

function ufx_speed_resource_label( $url ) {
    $p = wp_parse_url( $url );
    $path = isset( $p['path'] ) ? trim( $p['path'], '/' ) : '';
    if ( $path === '' ) return isset( $p['host'] ) ? $p['host'] : $url;
    $name = basename( $path );
    return $name ? $name : $path;
}

function ufx_speed_extract_resources( $html, $base_url ) {
    $resources = array();
    $seen      = array();
    $base_host = ufx_extract_host( $base_url );

    $add = function( $url, $type, $attrs = '' ) use ( &$resources, &$seen, $base_host ) {
        $url = esc_url_raw( $url );
        if ( ! $url || isset( $seen[ $url ] ) ) return;
        $seen[ $url ] = true;
        $host = ufx_extract_host( $url );
        $resources[] = array(
            'url'            => $url,
            'name'           => ufx_speed_resource_label( $url ),
            'type'           => $type,
            'status'         => 0,
            'size_bytes'     => 0,
            'size'           => '—',
            'start_ms'       => 0,
            'duration_ms'    => 0,
            'duration'       => '—',
            'blocking_ms'    => 0,
            'blocking_time'  => '0 ms',
            'is_third_party' => ( $host && $base_host && strtolower( $host ) !== strtolower( $base_host ) ),
            'host'           => $host,
            'attrs'          => $attrs,
        );
    };

    if ( ! is_string( $html ) || $html === '' ) return $resources;

    if ( preg_match_all( '/<script\b([^>]*)\bsrc=["\']([^"\']+)["\'][^>]*>/i', $html, $m, PREG_SET_ORDER ) ) {
        foreach ( $m as $match ) {
            $abs = ufx_speed_abs_url( $base_url, $match[2] );
            if ( $abs ) $add( $abs, 'script', $match[1] );
        }
    }
    if ( preg_match_all( '/<link\b([^>]*)\bhref=["\']([^"\']+)["\'][^>]*>/i', $html, $m, PREG_SET_ORDER ) ) {
        foreach ( $m as $match ) {
            $attrs = isset( $match[1] ) ? $match[1] : '';
            $abs = ufx_speed_abs_url( $base_url, $match[2] );
            if ( ! $abs ) continue;
            $type = ufx_speed_resource_type( $abs, 'link', $attrs );
            if ( in_array( $type, array( 'stylesheet', 'font', 'image' ), true ) || strpos( strtolower( $attrs ), 'preload' ) !== false ) {
                $add( $abs, $type, $attrs );
            }
        }
    }
    if ( preg_match_all( '/<(img|source)\b([^>]*?)\b(?:src|srcset)=["\']([^"\']+)["\'][^>]*>/i', $html, $m, PREG_SET_ORDER ) ) {
        foreach ( $m as $match ) {
            $abs = ufx_speed_abs_url( $base_url, $match[3] );
            if ( $abs ) $add( $abs, 'image', $match[2] );
        }
    }
    if ( preg_match_all( '/<iframe\b([^>]*)\bsrc=["\']([^"\']+)["\'][^>]*>/i', $html, $m, PREG_SET_ORDER ) ) {
        foreach ( $m as $match ) {
            $abs = ufx_speed_abs_url( $base_url, $match[2] );
            if ( $abs ) $add( $abs, 'document', $match[1] );
        }
    }

    return array_slice( $resources, 0, 45 );
}

function ufx_speed_probe_resource( $url ) {
    $start = microtime( true );
    $res = ufx_safe_remote_head( $url, array(
        'timeout'     => 2,
        'redirection' => 3,
        'user-agent'  => 'UptimeFixer/1.1',
    ) );

    if ( is_wp_error( $res ) || (int) wp_remote_retrieve_response_code( $res ) === 405 ) {
        $res = ufx_safe_remote_get( $url, array(
            'timeout'             => 3,
            'redirection'         => 3,
            'user-agent'          => 'UptimeFixer/1.1',
            'limit_response_size' => 32768,
        ) );
    }

    $duration = (int) round( ( microtime( true ) - $start ) * 1000 );
    if ( is_wp_error( $res ) ) {
        return array( 'status' => 0, 'size_bytes' => 0, 'duration_ms' => $duration );
    }
    $headers = wp_remote_retrieve_headers( $res );
    $len = 0;
    if ( isset( $headers['content-length'] ) ) $len = (int) $headers['content-length'];
    if ( ! $len ) $len = strlen( (string) wp_remote_retrieve_body( $res ) );
    return array(
        'status'      => (int) wp_remote_retrieve_response_code( $res ),
        'size_bytes'  => max( 0, $len ),
        'duration_ms' => max( 1, $duration ),
    );
}

function ufx_speed_build_opportunities( $resources, $size, $load_s, $req_count ) {
    $opps = array();
    $scripts = 0; $styles = 0; $images = 0; $third = 0; $large_images = 0; $missing_sizes = 0;
    foreach ( $resources as $r ) {
        if ( $r['type'] === 'script' ) $scripts++;
        if ( $r['type'] === 'stylesheet' ) $styles++;
        if ( $r['type'] === 'image' ) {
            $images++;
            if ( ! empty( $r['size_bytes'] ) && $r['size_bytes'] > 250000 ) $large_images++;
            if ( empty( $r['size_bytes'] ) ) $missing_sizes++;
        }
        if ( ! empty( $r['is_third_party'] ) ) $third++;
    }
    $render = $scripts + $styles;

    if ( $render > 0 ) $opps[] = array( 'title' => 'Reduce render-blocking CSS/JS', 'saving' => min( 900, $render * 80 ) . ' ms', 'priority' => $render > 8 ? 'High' : 'Medium', 'affected' => $render . ' resources', 'description' => 'CSS and JavaScript can delay the first visible render.', 'fix' => 'Defer non-critical JavaScript, inline critical CSS, and load unused files later.' );
    if ( $large_images > 0 || $missing_sizes > 3 ) $opps[] = array( 'title' => 'Optimize images', 'saving' => ufx_speed_format_bytes( max( 180000, $large_images * 220000 ) ), 'priority' => $large_images > 2 ? 'High' : 'Medium', 'affected' => max( $large_images, $images ) . ' images', 'description' => 'Large or uncompressed images increase page weight.', 'fix' => 'Use WebP/AVIF, resize images to display dimensions, and lazy-load below-the-fold images.' );
    if ( $size > 1200000 ) $opps[] = array( 'title' => 'Reduce total page size', 'saving' => ufx_speed_format_bytes( (int) ( $size * 0.25 ) ), 'priority' => $size > 3000000 ? 'High' : 'Medium', 'affected' => 'Main document/resources', 'description' => 'The tested page is heavier than recommended for fast loading.', 'fix' => 'Compress assets, remove unused files, and enable Brotli/Gzip compression.' );
    if ( $req_count > 35 ) $opps[] = array( 'title' => 'Reduce network requests', 'saving' => min( 1000, ( $req_count - 25 ) * 25 ) . ' ms', 'priority' => $req_count > 70 ? 'High' : 'Medium', 'affected' => $req_count . ' requests', 'description' => 'Too many files increase connection overhead.', 'fix' => 'Remove unused plugins/assets and combine small files where it is safe.' );
    if ( $third > 0 ) $opps[] = array( 'title' => 'Review third-party scripts', 'saving' => min( 700, $third * 90 ) . ' ms', 'priority' => $third > 5 ? 'Medium' : 'Low', 'affected' => $third . ' third-party requests', 'description' => 'External scripts can slow pages and add blocking time.', 'fix' => 'Keep only required third-party scripts and delay analytics/chat widgets.' );
    if ( $load_s > 2.5 ) $opps[] = array( 'title' => 'Improve server response and caching', 'saving' => number_format( min( 1.5, max( 0.2, $load_s * 0.25 ) ), 2 ) . ' s', 'priority' => $load_s > 4 ? 'High' : 'Medium', 'affected' => 'HTML document', 'description' => 'The page load time is slower than ideal.', 'fix' => 'Enable page cache, object cache, CDN, and optimize hosting/PHP performance.' );

    if ( ! $opps ) {
        $opps[] = array( 'title' => 'Keep current optimization setup', 'saving' => '—', 'priority' => 'Low', 'affected' => 'Page', 'description' => 'No major automated opportunities were detected.', 'fix' => 'Keep monitoring after adding new plugins, scripts, or images.' );
    }
    return array_slice( $opps, 0, 10 );
}

/* ──────────────────────────────────────────────────────
   2) SPEED TEST
   ────────────────────────────────────────────────────── */
function ufx_api_speed() {
    ufx_check_nonce();
    $url = ufx_clean_url( isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : '' );
    if ( ! $url ) wp_send_json_error( array( 'message' => 'Please enter a valid URL.' ) );

    $body = '';
    $size = 0;
    $code = 0;
    $load_s = 0;
    $ttfb_s = 0;
    $final_url = $url;

    $start = microtime( true );
    $res = ufx_safe_remote_get( $url, array(
        'timeout'     => 20,
        'redirection' => 5,
        'user-agent'  => 'UptimeFixer/2.0 (+https://uptimefixer.com)',
    ) );
    $load_s = microtime( true ) - $start;
    $ttfb_s = $load_s;
    if ( is_wp_error( $res ) ) wp_send_json_error( array( 'message' => $res->get_error_message() ) );
    $body = wp_remote_retrieve_body( $res );
    $size = strlen( (string) $body );
    $code = (int) wp_remote_retrieve_response_code( $res );
    $final_url = $url;

    $ttfb_ms = (int) round( $ttfb_s * 1000 );

    $assets = ufx_speed_extract_resources( (string) $body, $final_url );

    $resources = array();
    $resources[] = array(
        'url'            => $final_url,
        'name'           => ufx_speed_resource_label( $final_url ),
        'type'           => 'document',
        'status'         => $code,
        'size_bytes'     => max( 0, $size ),
        'size'           => ufx_speed_format_bytes( $size ),
        'start_ms'       => null,
        'probe_order'    => 1,
        'duration_ms'    => max( 1, (int) round( $load_s * 1000 ) ),
        'duration'       => (int) round( $load_s * 1000 ) . ' ms',
        'blocking_ms'    => null,
        'blocking_time'  => 'Not measured',
        'probed'         => true,
        'is_third_party' => false,
        'host'           => ufx_extract_host( $final_url ),
    );

    $probe_limit = 16;
    foreach ( $assets as $i => $r ) {
        if ( $i < $probe_limit ) {
            $probe = ufx_speed_probe_resource( $r['url'] );
            $r['status']      = $probe['status'];
            $r['size_bytes']  = $probe['size_bytes'];
            $r['duration_ms'] = $probe['duration_ms'];
            $r['probed']      = true;
        } else {
            $r['status']      = 0;
            $r['size_bytes']  = 0;
            $r['duration_ms'] = 0;
            $r['probed']      = false;
        }

        $r['start_ms']      = null;
        $r['probe_order']   = $i + 2;
        $r['size']          = $r['size_bytes'] ? ufx_speed_format_bytes( $r['size_bytes'] ) : '—';
        $r['duration']      = $r['probed'] ? $r['duration_ms'] . ' ms' : 'Not probed';
        $r['blocking_ms']   = null;
        $r['blocking_time'] = 'Not measured';
        $resources[] = $r;
    }

    $req_count = count( $resources );
    if ( $req_count < 1 ) $req_count = 1;

    $total_size = $size;
    foreach ( $resources as $r ) {
        if ( $r['type'] !== 'document' && ! empty( $r['size_bytes'] ) ) $total_size += (int) $r['size_bytes'];
    }

    $counts = array( 'all' => 0, 'document' => 0, 'script' => 0, 'stylesheet' => 0, 'image' => 0, 'font' => 0, 'third' => 0 );
    $largest = null;
    $seen_names = array();
    $dupes = 0;
    foreach ( $resources as $r ) {
        $counts['all']++;
        if ( isset( $counts[ $r['type'] ] ) ) $counts[ $r['type'] ]++;
        if ( ! empty( $r['is_third_party'] ) ) $counts['third']++;
        if ( ! $largest || (int) $r['size_bytes'] > (int) $largest['size_bytes'] ) $largest = $r;
        $key = strtolower( preg_replace( '/\?.*$/', '', $r['url'] ) );
        if ( isset( $seen_names[ $key ] ) ) $dupes++;
        $seen_names[ $key ] = true;
    }

    $score = 100;
    if ( $load_s > 1 ) $score -= ( $load_s - 1 ) * 14;
    if ( $ttfb_s > 0.6 ) $score -= ( $ttfb_s - 0.6 ) * 10;
    if ( $total_size > 1000000 ) $score -= ( $total_size / 1000000 - 1 ) * 8;
    if ( $req_count > 60 ) $score -= ( $req_count - 60 ) * 0.4;
    $score = max( 0, min( 100, (int) round( $score ) ) );

    $opps = ufx_speed_build_opportunities( $resources, $total_size, $load_s, $req_count );
    $render_blocking = 0;
    foreach ( $resources as $r ) {
        if ( in_array( $r['type'], array( 'script', 'stylesheet' ), true ) ) $render_blocking++;
    }

    $diagnostics = array(
        $req_count . ' total network requests detected.',
        'Document response code: ' . ( $code ? $code . ' ' . ufx_status_message( $code ) : 'Unknown' ) . '.',
        'Estimated total transfer size: ' . ufx_speed_format_bytes( $total_size ) . '.',
        'Third-party requests detected: ' . (int) $counts['third'] . '.',
    );

    wp_send_json_success( array(
        'score'       => $score,
        'rating'      => $score >= 90 ? 'Strong snapshot' : ( $score >= 75 ? 'Moderate snapshot' : ( $score >= 50 ? 'Needs review' : 'Weak snapshot' ) ),
        'load_time'   => number_format( $load_s, 2 ) . ' s',
        'load_ms'     => (int) round( $load_s * 1000 ),
        'fcp'         => 'Not measured',
        'fcp_ms'      => null,
        'lcp'         => 'Not measured',
        'lcp_ms'      => null,
        'ttfb'        => $ttfb_ms . ' ms',
        'ttfb_ms'     => $ttfb_ms,
        'tbt'         => 'Not measured',
        'tbt_ms'      => null,
        'cls'         => 'Not measured',
        'cls_num'     => null,
        'measurement_scope' => 'Server-side request and resource inventory; no browser Core Web Vitals are inferred.',
        'page_size'   => ufx_speed_format_bytes( $total_size ),
        'page_bytes'  => $total_size,
        'requests'    => $req_count,
        'http_code'   => $code,
        'tested_url'  => $final_url,
        'checked_at'  => current_time( 'mysql' ),
        'resources'   => $resources,
        'counts'      => $counts,
        'opportunities' => $opps,
        'diagnostics' => $diagnostics,
        'insights'    => array(
            'render_blocking' => $render_blocking . ' resources',
            'largest'         => $largest ? $largest['name'] . ' (' . ufx_speed_format_bytes( (int) $largest['size_bytes'] ) . ')' : '—',
            'duplicates'      => $dupes . ' duplicate requests',
            'slowest'         => ! empty( $resources ) ? $resources[0]['duration'] : '—',
        ),
    ) );
}

/* ──────────────────────────────────────────────────────
   3) HTTP STATUS CHECKER
   ────────────────────────────────────────────────────── */
function ufx_api_http_status() {
    ufx_check_nonce();
    $url = ufx_clean_url( isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : '' );
    if ( ! $url ) wp_send_json_error( array( 'message' => 'Please enter a valid URL.' ) );

    $start = microtime( true );
    $res = ufx_safe_remote_get( $url, array(
        'timeout'     => 10,
        'redirection' => 0, // don't follow — we want the actual code
        'user-agent'  => 'UptimeFixer/1.0',
    ) );
    $ms = (int) round( ( microtime( true ) - $start ) * 1000 );

    if ( is_wp_error( $res ) ) wp_send_json_error( array( 'message' => $res->get_error_message() ) );

    $code    = wp_remote_retrieve_response_code( $res );
    $headers = wp_remote_retrieve_headers( $res );

    // Convert WP_Http_Headers / array to plain associative array
    $headers_arr = array();
    if ( is_object( $headers ) && method_exists( $headers, 'getAll' ) ) {
        $headers_arr = $headers->getAll();
    } elseif ( is_array( $headers ) ) {
        $headers_arr = $headers;
    }
    $clean = array();
    foreach ( $headers_arr as $k => $v ) {
        $clean[ strtolower( $k ) ] = is_array( $v ) ? implode( ', ', $v ) : (string) $v;
    }

    // Status messages
    $status_msg = array(
        200 => 'OK', 201 => 'Created', 204 => 'No Content',
        301 => 'Moved Permanently', 302 => 'Found', 304 => 'Not Modified',
        400 => 'Bad Request', 401 => 'Unauthorized', 403 => 'Forbidden', 404 => 'Not Found',
        500 => 'Internal Server Error', 502 => 'Bad Gateway', 503 => 'Service Unavailable',
    );
    $msg = isset( $status_msg[ $code ] ) ? $status_msg[ $code ] : '';

    $category = 'success';
    if ( $code >= 300 && $code < 400 ) $category = 'redirect';
    elseif ( $code >= 400 && $code < 500 ) $category = 'client-error';
    elseif ( $code >= 500 ) $category = 'server-error';

    wp_send_json_success( array(
        'status'        => $code,
        'message'       => $msg,
        'category'      => $category,
        'response_time' => $ms,
        'url'           => $url,
        'content_type'  => isset( $clean['content-type'] ) ? $clean['content-type'] : '',
        'server'        => isset( $clean['server'] ) ? $clean['server'] : '',
        'headers'       => $clean,
    ) );
}

/* ──────────────────────────────────────────────────────
   4) SSL CHECKER
   ────────────────────────────────────────────────────── */
function ufx_api_ssl() {
    ufx_check_nonce();
    $raw = trim( (string) ( isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : '' ) );
    if ( ! $raw ) wp_send_json_error( array( 'message' => 'Please enter a domain or URL.' ) );

    $host = ufx_extract_host( ufx_clean_url( $raw ) );
    if ( ! $host ) $host = preg_replace( '#^https?://#', '', $raw );
    $host = preg_replace( '#/.*$#', '', $host );
    if ( ! $host || ! preg_match( '/^[a-z0-9.\-]+\.[a-z]{2,}$/i', $host ) ) {
        wp_send_json_error( array( 'message' => 'Invalid domain.' ) );
    }

    $ctx = stream_context_create( array(
        'ssl' => array(
            'capture_peer_cert' => true,
            'capture_peer_cert_chain' => true,
            'verify_peer' => false,
            'verify_peer_name' => false,
            'SNI_enabled' => true,
        ),
    ) );

    set_error_handler( function() {} );
    $sock = @stream_socket_client( 'ssl://' . $host . ':443', $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $ctx );
    restore_error_handler();

    if ( ! $sock ) wp_send_json_error( array( 'message' => 'Could not connect to ' . $host . ' on port 443.' ) );

    $params = stream_context_get_params( $sock );
    fclose( $sock );

    if ( empty( $params['options']['ssl']['peer_certificate'] ) ) {
        wp_send_json_error( array( 'message' => 'No SSL certificate could be retrieved.' ) );
    }

    $cert = openssl_x509_parse( $params['options']['ssl']['peer_certificate'] );
    if ( ! $cert ) wp_send_json_error( array( 'message' => 'SSL certificate could not be parsed.' ) );

    $issuer = '';
    if ( ! empty( $cert['issuer']['O'] ) ) $issuer = $cert['issuer']['O'];
    elseif ( ! empty( $cert['issuer']['CN'] ) ) $issuer = $cert['issuer']['CN'];

    $subject_cn = isset( $cert['subject']['CN'] ) ? $cert['subject']['CN'] : $host;
    $valid_from = isset( $cert['validFrom_time_t'] ) ? (int) $cert['validFrom_time_t'] : 0;
    $valid_to   = isset( $cert['validTo_time_t'] ) ? (int) $cert['validTo_time_t'] : 0;
    $now        = time();
    $days_left  = $valid_to ? (int) floor( ( $valid_to - $now ) / 86400 ) : 0;

    // Cert chain
    $chain = array();
    if ( ! empty( $params['options']['ssl']['peer_certificate_chain'] ) ) {
        foreach ( $params['options']['ssl']['peer_certificate_chain'] as $c ) {
            $p = openssl_x509_parse( $c );
            if ( $p ) {
                $chain[] = array(
                    'name'   => isset( $p['subject']['CN'] ) ? $p['subject']['CN'] : '',
                    'issuer' => isset( $p['issuer']['CN'] ) ? $p['issuer']['CN'] : '',
                );
            }
        }
    }

    wp_send_json_success( array(
        'valid'        => ( $now >= $valid_from && $now <= $valid_to ),
        'hostname'     => $host,
        'subject'      => $subject_cn,
        'issuer'       => $issuer ? $issuer : 'Unknown',
        'issuer_cn'    => isset( $cert['issuer']['CN'] ) ? $cert['issuer']['CN'] : '',
        'valid_from'   => $valid_from ? date( 'M d, Y', $valid_from ) : '',
        'valid_to'     => $valid_to ? date( 'M d, Y', $valid_to ) : '',
        'valid_from_time' => $valid_from ? date( 'H:i:s', $valid_from ) . ' UTC' : '',
        'valid_to_time'   => $valid_to ? date( 'H:i:s', $valid_to ) . ' UTC' : '',
        'days_remaining' => $days_left,
        'signature'    => isset( $cert['signatureTypeSN'] ) ? $cert['signatureTypeSN'] : 'Unknown',
        'chain'        => $chain,
    ) );
}

/* ──────────────────────────────────────────────────────
   5) DNS LOOKUP
   ────────────────────────────────────────────────────── */
function ufx_api_dns() {
    ufx_check_nonce();
    $raw = trim( (string) ( isset( $_POST['domain'] ) ? wp_unslash( $_POST['domain'] ) : '' ) );
    if ( ! $raw ) wp_send_json_error( array( 'message' => 'Please enter a domain.' ) );

    $host = preg_replace( '#^https?://#', '', $raw );
    $host = preg_replace( '#/.*$#', '', $host );
    if ( ! preg_match( '/^[a-z0-9.\-]+\.[a-z]{2,}$/i', $host ) ) {
        wp_send_json_error( array( 'message' => 'Invalid domain.' ) );
    }

    if ( ! function_exists( 'dns_get_record' ) ) {
        wp_send_json_error( array( 'message' => 'DNS lookup is not available on this server.' ) );
    }

    $types = array(
        'A'     => DNS_A,
        'AAAA'  => DNS_AAAA,
        'MX'    => DNS_MX,
        'TXT'   => DNS_TXT,
        'CNAME' => DNS_CNAME,
        'NS'    => DNS_NS,
    );
    $records = array();
    $counts  = array( 'all' => 0 );

    foreach ( $types as $name => $const ) {
        $result = @dns_get_record( $host, $const );
        $counts[ $name ] = is_array( $result ) ? count( $result ) : 0;
        if ( ! is_array( $result ) ) continue;
        $counts['all'] += $counts[ $name ];
        foreach ( $result as $r ) {
            $value = '';
            $priority = '';
            switch ( $name ) {
                case 'A':     $value = isset( $r['ip'] ) ? $r['ip'] : ''; break;
                case 'AAAA':  $value = isset( $r['ipv6'] ) ? $r['ipv6'] : ''; break;
                case 'MX':    $value = isset( $r['target'] ) ? $r['target'] : ''; $priority = isset( $r['pri'] ) ? $r['pri'] : ''; break;
                case 'TXT':   $value = isset( $r['txt'] ) ? $r['txt'] : ''; break;
                case 'CNAME': $value = isset( $r['target'] ) ? $r['target'] : ''; break;
                case 'NS':    $value = isset( $r['target'] ) ? $r['target'] : ''; break;
            }
            $records[] = array(
                'type'     => $name,
                'value'    => $value,
                'ttl'      => isset( $r['ttl'] ) ? (int) $r['ttl'] : 0,
                'priority' => $priority === '' ? '–' : (string) $priority,
            );
        }
    }

    // Nameservers
    $ns = array();
    $ns_records = @dns_get_record( $host, DNS_NS );
    if ( is_array( $ns_records ) ) {
        foreach ( $ns_records as $r ) if ( isset( $r['target'] ) ) $ns[] = $r['target'];
    }

    wp_send_json_success( array(
        'domain'      => $host,
        'records'     => $records,
        'counts'      => $counts,
        'total'       => count( $records ),
        'nameservers' => implode( ', ', array_slice( $ns, 0, 4 ) ),
        'checked_at'  => current_time( 'mysql' ),
    ) );
}

/* ──────────────────────────────────────────────────────
   6) REDIRECT CHECKER
   ────────────────────────────────────────────────────── */
function ufx_api_redirect() {
    ufx_check_nonce();
    $url = ufx_clean_url( isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : '' );
    if ( ! $url ) wp_send_json_error( array( 'message' => 'Please enter a valid URL.' ) );

    $chain = array();
    $current = $url;
    $total_time = 0;
    $max_hops = 10;

    for ( $i = 0; $i < $max_hops; $i++ ) {
        $start = microtime( true );
        $code = 0;
        $headers = array();
        $proto = 'HTTP/1.1';

        $res = ufx_safe_remote_head( $current, array(
            'timeout'     => 12,
            'redirection' => 0,
            'user-agent'  => 'UptimeFixer/2.0',
        ) );
        if ( is_wp_error( $res ) ) wp_send_json_error( array( 'message' => $res->get_error_message() ) );
        $code = (int) wp_remote_retrieve_response_code( $res );
        $h = wp_remote_retrieve_headers( $res );
        if ( is_object( $h ) && method_exists( $h, 'getAll' ) ) $headers = array_change_key_case( $h->getAll(), CASE_LOWER );
        elseif ( is_array( $h ) ) $headers = array_change_key_case( $h, CASE_LOWER );

        $ms = (int) round( ( microtime( true ) - $start ) * 1000 );
        $total_time += $ms;
        $chain[] = array(
            'url'      => $current,
            'status'   => $code,
            'type'     => ufx_status_message( $code ),
            'protocol' => $proto,
            'time_ms'  => $ms,
            'time'     => $ms . ' ms',
        );

        $location = isset( $headers['location'] ) ? ( is_array( $headers['location'] ) ? reset( $headers['location'] ) : $headers['location'] ) : '';
        if ( $code < 300 || $code >= 400 || ! $location ) break;
        $next = ufx_abs_redirect_url( $current, $location );
        $next = ufx_clean_url( $next, false );
        if ( ! $next || $next === $current ) break;
        $current = $next;
    }

    if ( ! $chain ) wp_send_json_error( array( 'message' => 'Could not check this URL.' ) );

    $final = end( $chain );
    $is_perm = false;
    foreach ( $chain as $hop ) {
        if ( (int) $hop['status'] === 301 || (int) $hop['status'] === 308 ) { $is_perm = true; break; }
    }

    wp_send_json_success( array(
        'hops'         => count( $chain ),
        'redirects'    => max( 0, count( $chain ) - 1 ),
        'chain'        => $chain,
        'final_url'    => $final['url'],
        'final_status' => $final['status'],
        'total_time'   => $total_time,
        'is_permanent' => $is_perm,
        'checked_at'   => current_time( 'mysql' ),
    ) );
}


/* ──────────────────────────────────────────────────────
   7) DOMAIN EXPIRY CHECKER
   ────────────────────────────────────────────────────── */
function ufx_domain_from_raw( $raw ) {
    $raw = trim( strtolower( (string) $raw ) );
    $raw = preg_replace( '#^https?://#i', '', $raw );
    $raw = preg_replace( '#^www\.#i', '', $raw );
    $raw = preg_replace( '#/.*$#', '', $raw );
    $raw = preg_replace( '#:\d+$#', '', $raw );
    return preg_match( '/^[a-z0-9][a-z0-9\.-]*\.[a-z]{2,}$/i', $raw ) ? $raw : '';
}

function ufx_api_domain_expiry() {
    ufx_check_nonce();
    $domain = ufx_domain_from_raw( isset( $_POST['domain'] ) ? wp_unslash( $_POST['domain'] ) : '' );
    if ( ! $domain ) wp_send_json_error( array( 'message' => 'Please enter a valid domain.' ) );

    $endpoint = 'https://rdap.org/domain/' . rawurlencode( $domain );
    $res = wp_safe_remote_get( $endpoint, array(
        'timeout'    => 15,
        'sslverify'  => true,
        'user-agent' => 'UptimeFixer/1.7',
    ) );
    if ( is_wp_error( $res ) ) wp_send_json_error( array( 'message' => $res->get_error_message() ) );

    $code = (int) wp_remote_retrieve_response_code( $res );
    $body = wp_remote_retrieve_body( $res );
    $data = json_decode( $body, true );
    if ( $code < 200 || $code >= 300 || ! is_array( $data ) ) {
        wp_send_json_error( array( 'message' => 'Could not retrieve domain registration details.' ) );
    }

    $events = isset( $data['events'] ) && is_array( $data['events'] ) ? $data['events'] : array();
    $created = $updated = $expires = '';
    foreach ( $events as $event ) {
        $action = isset( $event['eventAction'] ) ? strtolower( (string) $event['eventAction'] ) : '';
        $date   = isset( $event['eventDate'] ) ? (string) $event['eventDate'] : '';
        if ( ! $date ) continue;
        if ( strpos( $action, 'registration' ) !== false && ! $created ) $created = $date;
        if ( strpos( $action, 'last changed' ) !== false || strpos( $action, 'last update' ) !== false ) $updated = $date;
        if ( strpos( $action, 'expiration' ) !== false || strpos( $action, 'expiry' ) !== false ) $expires = $date;
    }

    $registrar = '';
    if ( ! empty( $data['entities'] ) && is_array( $data['entities'] ) ) {
        foreach ( $data['entities'] as $entity ) {
            $roles = isset( $entity['roles'] ) && is_array( $entity['roles'] ) ? $entity['roles'] : array();
            if ( in_array( 'registrar', $roles, true ) ) {
                if ( ! empty( $entity['vcardArray'][1] ) && is_array( $entity['vcardArray'][1] ) ) {
                    foreach ( $entity['vcardArray'][1] as $v ) {
                        if ( isset( $v[0] ) && $v[0] === 'fn' && isset( $v[3] ) ) { $registrar = (string) $v[3]; break 2; }
                    }
                }
                if ( ! empty( $entity['handle'] ) ) { $registrar = (string) $entity['handle']; break; }
            }
        }
    }

    $nameservers = array();
    if ( ! empty( $data['nameservers'] ) && is_array( $data['nameservers'] ) ) {
        foreach ( $data['nameservers'] as $ns ) {
            if ( ! empty( $ns['ldhName'] ) ) $nameservers[] = strtolower( (string) $ns['ldhName'] );
        }
    }

    $expiry_ts = $expires ? strtotime( $expires ) : 0;
    $days_left = $expiry_ts ? (int) floor( ( $expiry_ts - time() ) / 86400 ) : null;
    $status = $expiry_ts ? ( $days_left >= 0 ? 'Active' : 'Expired' ) : 'Unknown';

    wp_send_json_success( array(
        'domain'      => $domain,
        'status'      => $status,
        'created'     => $created ? date( 'M d, Y', strtotime( $created ) ) : '—',
        'updated'     => $updated ? date( 'M d, Y', strtotime( $updated ) ) : '—',
        'expires'     => $expires ? date( 'M d, Y', strtotime( $expires ) ) : '—',
        'days_left'   => is_null( $days_left ) ? '—' : $days_left,
        'registrar'   => $registrar ? $registrar : '—',
        'nameservers' => $nameservers,
        'source'      => 'RDAP',
    ) );
}

/* ──────────────────────────────────────────────────────
   8) BROKEN LINK CHECKER
   ────────────────────────────────────────────────────── */
function ufx_api_broken_links() {
    ufx_check_nonce();
    $url = ufx_clean_url( isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : '' );
    if ( ! $url ) wp_send_json_error( array( 'message' => 'Please enter a valid URL.' ) );

    $limit = isset( $_POST['limit'] ) ? min( 50, max( 5, (int) $_POST['limit'] ) ) : 25;
    $page = ufx_safe_remote_get( $url, array(
        'timeout'     => 15,
        'redirection' => 5,
        'user-agent'  => 'UptimeFixer/1.7 BrokenLinkChecker',
    ) );
    if ( is_wp_error( $page ) ) wp_send_json_error( array( 'message' => $page->get_error_message() ) );

    $html = wp_remote_retrieve_body( $page );
    if ( ! is_string( $html ) || $html === '' ) wp_send_json_error( array( 'message' => 'Could not read page HTML.' ) );

    $links = array();
    $seen = array();
    if ( preg_match_all( '/<a\b[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $html, $m ) ) {
        foreach ( $m[1] as $href ) {
            $href = html_entity_decode( trim( $href ), ENT_QUOTES );
            if ( $href === '' || preg_match( '#^(mailto:|tel:|javascript:|data:|#)#i', $href ) ) continue;
            $abs = ufx_clean_url( ufx_speed_abs_url( $url, $href ), false );
            if ( ! $abs || isset( $seen[ $abs ] ) ) continue;
            $seen[ $abs ] = true;
            $links[] = $abs;
            if ( count( $links ) >= $limit ) break;
        }
    }

    $results = array();
    $broken = 0;
    $checked = 0;
    foreach ( $links as $link ) {
        $start = microtime( true );
        $res = ufx_safe_remote_head( $link, array(
            'timeout'     => 8,
            'redirection' => 3,
            'user-agent'  => 'UptimeFixer/1.7 BrokenLinkChecker',
        ) );
        if ( is_wp_error( $res ) ) {
            $res = ufx_safe_remote_get( $link, array(
                'timeout'     => 8,
                'redirection' => 3,
                'user-agent'  => 'UptimeFixer/1.7 BrokenLinkChecker',
            ) );
        }
        $ms = (int) round( ( microtime( true ) - $start ) * 1000 );
        $status = 0;
        $message = '';
        if ( is_wp_error( $res ) ) {
            $message = $res->get_error_message();
        } else {
            $status = (int) wp_remote_retrieve_response_code( $res );
            $message = ufx_status_message( $status );
        }
        $is_broken = ( $status === 0 || $status >= 400 );
        if ( $is_broken ) $broken++;
        $checked++;
        $results[] = array(
            'url'     => $link,
            'status'  => $status ? $status : 'Error',
            'message' => $message ? $message : ( $is_broken ? 'Request failed' : 'OK' ),
            'time'    => $ms . ' ms',
            'broken'  => $is_broken,
        );
    }

    wp_send_json_success( array(
        'page'      => $url,
        'found'     => count( $links ),
        'checked'   => $checked,
        'broken'    => $broken,
        'ok'        => max( 0, $checked - $broken ),
        'limit'     => $limit,
        'results'   => $results,
        'checked_at'=> current_time( 'mysql' ),
    ) );
}

/**
 * The AJAX config is registered via wp_localize_script() in inc/enqueue.php.
 * This function remains for backwards compatibility with older child snippets.
 */
function ufx_api_inline_nonce() {
    return;
}


/**
 * Security headers checker — no paid API required.
 */
function ufx_api_security_headers() {
    ufx_check_nonce();

    $url = isset( $_POST['url'] ) ? ufx_clean_url( wp_unslash( $_POST['url'] ) ) : '';
    if ( ! $url ) {
        wp_send_json_error( array( 'message' => 'Please enter a valid URL.' ), 400 );
    }

    $res = ufx_safe_remote_head( $url, array(
        'timeout'     => 15,
        'redirection' => 5,
        'user-agent'  => 'UptimeFixer Security Headers Checker',
    ) );

    if ( is_wp_error( $res ) ) {
        wp_send_json_error( array( 'message' => $res->get_error_message() ), 500 );
    }

    $headers = wp_remote_retrieve_headers( $res );
    $normalized = array();
    foreach ( $headers as $key => $value ) {
        $normalized[ strtolower( $key ) ] = is_array( $value ) ? implode( ', ', $value ) : (string) $value;
    }

    $checks = array(
        'strict-transport-security' => 'HTTP Strict Transport Security',
        'content-security-policy'   => 'Content Security Policy',
        'x-frame-options'           => 'X-Frame-Options',
        'x-content-type-options'    => 'X-Content-Type-Options',
        'referrer-policy'           => 'Referrer-Policy',
        'permissions-policy'        => 'Permissions-Policy',
    );

    $out = array();
    foreach ( $checks as $header => $label ) {
        $out[] = array(
            'header'  => $label,
            'present' => isset( $normalized[ $header ] ),
            'value'   => isset( $normalized[ $header ] ) ? $normalized[ $header ] : '',
        );
    }

    wp_send_json_success( array(
        'url'     => $url,
        'status'  => wp_remote_retrieve_response_code( $res ),
        'results' => $out,
    ) );
}

/**
 * Mixed content checker — scans page HTML for insecure HTTP assets.
 */
function ufx_api_mixed_content() {
    ufx_check_nonce();

    $url = isset( $_POST['url'] ) ? ufx_clean_url( wp_unslash( $_POST['url'] ) ) : '';
    if ( ! $url ) {
        wp_send_json_error( array( 'message' => 'Please enter a valid URL.' ), 400 );
    }

    $res = ufx_safe_remote_get( $url, array(
        'timeout'     => 20,
        'redirection' => 5,
        'user-agent'  => 'UptimeFixer Mixed Content Checker',
        'limit_response_size' => 1024 * 1024,
    ) );

    if ( is_wp_error( $res ) ) {
        wp_send_json_error( array( 'message' => $res->get_error_message() ), 500 );
    }

    $html = wp_remote_retrieve_body( $res );
    $items = array();

    if ( preg_match_all( '#(?:src|href)=["\'](http://[^"\']+)["\']#i', $html, $matches ) ) {
        foreach ( array_unique( $matches[1] ) as $asset ) {
            $items[] = esc_url_raw( $asset );
            if ( count( $items ) >= 100 ) {
                break;
            }
        }
    }

    wp_send_json_success( array(
        'url'    => $url,
        'count'  => count( $items ),
        'items'  => $items,
        'status' => wp_remote_retrieve_response_code( $res ),
    ) );
}
