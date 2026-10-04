<?php
/**
 * Hardened AJAX endpoints for the extended website and data tools.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$ufx_extra_endpoints = array(
    'health_audit', 'dns_propagation', 'ip_blacklist', 'spf', 'dkim', 'dmarc',
    'port_check', 'safe_ping', 'route_diagnostic', 'site_detect', 'hosting',
    'subdomains', 'headers_view', 'pagespeed', 'mobile_test', 'currency', 'shorten',
);
foreach ( $ufx_extra_endpoints as $ufx_extra_endpoint ) {
    add_action( 'wp_ajax_ufx_' . $ufx_extra_endpoint, 'ufx_api_' . $ufx_extra_endpoint );
    add_action( 'wp_ajax_nopriv_ufx_' . $ufx_extra_endpoint, 'ufx_api_' . $ufx_extra_endpoint );
}
unset( $ufx_extra_endpoint, $ufx_extra_endpoints );

function ufx_extra_domain( $raw, $require_resolution = true ) {
    $raw = strtolower( trim( (string) $raw ) );
    if ( preg_match( '#^https?://#i', $raw ) ) {
        $raw = (string) wp_parse_url( $raw, PHP_URL_HOST );
    }
    $raw = rtrim( preg_replace( '#[/:].*$#', '', $raw ), '.' );
    if ( ! preg_match( '/^(?=.{4,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i', $raw ) ) return '';
    if ( preg_match( '/(?:^|\.)local(?:host)?$/i', $raw ) ) return '';
    return ! $require_resolution || ufx_host_is_public( $raw ) ? $raw : '';
}

function ufx_extra_public_ip_for_host( $host ) {
    if ( filter_var( $host, FILTER_VALIDATE_IP ) ) return ufx_ip_is_public( $host ) ? $host : '';
    $ips = gethostbynamel( $host );
    if ( ! is_array( $ips ) ) return '';
    foreach ( $ips as $ip ) {
        if ( ufx_ip_is_public( $ip ) ) return $ip;
    }
    return '';
}

function ufx_extra_headers_array( $response ) {
    $headers = wp_remote_retrieve_headers( $response );
    if ( is_object( $headers ) && method_exists( $headers, 'getAll' ) ) $headers = $headers->getAll();
    if ( ! is_array( $headers ) ) return array();
    $clean = array();
    foreach ( $headers as $key => $value ) {
        $clean[ sanitize_key( $key ) ] = sanitize_text_field( is_array( $value ) ? implode( ', ', $value ) : (string) $value );
    }
    return $clean;
}

function ufx_api_health_audit() {
    ufx_check_nonce();
    $url = ufx_clean_url( isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : '' );
    if ( ! $url ) wp_send_json_error( array( 'message' => 'Enter a valid public HTTP or HTTPS URL.' ), 400 );
    $start = microtime( true );
    $res = ufx_safe_remote_get( $url, array( 'timeout' => 18, 'redirection' => 4, 'user-agent' => 'UptimeFixer/2.0 Health Audit' ) );
    if ( is_wp_error( $res ) ) wp_send_json_error( array( 'message' => $res->get_error_message() ), 502 );
    $ms = (int) round( ( microtime( true ) - $start ) * 1000 );
    $code = (int) wp_remote_retrieve_response_code( $res );
    $headers = ufx_extra_headers_array( $res );
    $body = (string) wp_remote_retrieve_body( $res );
    $title = '';
    $description = '';
    if ( preg_match( '/<title[^>]*>(.*?)<\/title>/is', $body, $m ) ) $title = sanitize_text_field( html_entity_decode( wp_strip_all_tags( $m[1] ), ENT_QUOTES ) );
    if ( preg_match( '/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)/i', $body, $m ) || preg_match( '/<meta[^>]+content=["\']([^"\']*)["\'][^>]+name=["\']description["\']/i', $body, $m ) ) $description = sanitize_text_field( html_entity_decode( $m[1], ENT_QUOTES ) );
    $checks = array(
        array( 'label' => 'HTTP status', 'value' => $code ),
        array( 'label' => 'Response time', 'value' => $ms . ' ms' ),
        array( 'label' => 'HTTPS', 'value' => 0 === strpos( $url, 'https://' ) ? 'Yes' : 'No' ),
        array( 'label' => 'Page title', 'value' => $title ? $title : 'Missing' ),
        array( 'label' => 'Meta description', 'value' => $description ? $description : 'Missing' ),
        array( 'label' => 'Canonical', 'value' => false !== stripos( $body, 'rel="canonical"' ) || false !== stripos( $body, "rel='canonical'" ) ? 'Found' : 'Not detected' ),
        array( 'label' => 'Robots meta', 'value' => false !== stripos( $body, 'name="robots"' ) ? 'Found' : 'Not detected' ),
        array( 'label' => 'Viewport', 'value' => false !== stripos( $body, 'name="viewport"' ) ? 'Found' : 'Missing' ),
        array( 'label' => 'HSTS', 'value' => isset( $headers['strict-transport-security'] ) ? 'Present' : 'Missing' ),
        array( 'label' => 'Content Security Policy', 'value' => isset( $headers['content-security-policy'] ) ? 'Present' : 'Missing' ),
    );
    wp_send_json_success( array( 'url' => $url, 'status' => $code, 'results' => $checks ) );
}

function ufx_api_dns_propagation() {
    ufx_check_nonce();
    $domain = ufx_extra_domain( isset( $_POST['domain'] ) ? wp_unslash( $_POST['domain'] ) : '', false );
    $type = isset( $_POST['type'] ) ? strtoupper( sanitize_key( wp_unslash( $_POST['type'] ) ) ) : 'A';
    $allowed = array( 'A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS' );
    if ( ! $domain || ! in_array( $type, $allowed, true ) ) wp_send_json_error( array( 'message' => 'Enter a valid public domain and record type.' ), 400 );
    $endpoints = array(
        'Google Public DNS' => 'https://dns.google/resolve?name=' . rawurlencode( $domain ) . '&type=' . rawurlencode( $type ),
        'Cloudflare DNS' => 'https://cloudflare-dns.com/dns-query?name=' . rawurlencode( $domain ) . '&type=' . rawurlencode( $type ),
    );
    $results = array();
    foreach ( $endpoints as $label => $endpoint ) {
        $res = wp_safe_remote_get( $endpoint, array( 'timeout' => 10, 'headers' => array( 'Accept' => 'application/dns-json' ), 'user-agent' => 'UptimeFixer/2.0 DNS' ) );
        $data = is_wp_error( $res ) ? array() : json_decode( wp_remote_retrieve_body( $res ), true );
        $values = array();
        if ( ! empty( $data['Answer'] ) && is_array( $data['Answer'] ) ) foreach ( $data['Answer'] as $answer ) if ( isset( $answer['data'] ) ) $values[] = sanitize_text_field( (string) $answer['data'] );
        $results[] = array( 'resolver' => $label, 'value' => $values ? $values : array( 'No answer' ) );
    }
    wp_send_json_success( array( 'domain' => $domain, 'type' => $type, 'results' => $results ) );
}

function ufx_api_ip_blacklist() {
    ufx_check_nonce();
    $ip = isset( $_POST['ip'] ) ? sanitize_text_field( wp_unslash( $_POST['ip'] ) ) : '';
    if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) || ! ufx_ip_is_public( $ip ) ) wp_send_json_error( array( 'message' => 'Enter a valid public IPv4 address.' ), 400 );
    $reverse = implode( '.', array_reverse( explode( '.', $ip ) ) );
    $lists = array( 'Spamhaus ZEN' => 'zen.spamhaus.org', 'SpamCop' => 'bl.spamcop.net', 'Barracuda' => 'b.barracudacentral.org', 'SORBS' => 'dnsbl.sorbs.net' );
    $results = array();
    foreach ( $lists as $label => $zone ) {
        $listed = gethostbyname( $reverse . '.' . $zone );
        $hit = $listed !== $reverse . '.' . $zone && filter_var( $listed, FILTER_VALIDATE_IP );
        $results[] = array( 'label' => $label, 'value' => $hit ? 'Listed (' . $listed . ')' : 'Not listed' );
    }
    wp_send_json_success( array( 'ip' => $ip, 'results' => $results, 'notice' => 'DNSBL results are informational and may be rate-limited by providers.' ) );
}

function ufx_dns_txt_values( $name ) {
    $records = dns_get_record( $name, DNS_TXT );
    $out = array();
    if ( is_array( $records ) ) foreach ( $records as $record ) if ( ! empty( $record['txt'] ) ) $out[] = sanitize_text_field( $record['txt'] );
    return $out;
}

function ufx_api_spf() {
    ufx_check_nonce();
    $domain = ufx_extra_domain( isset( $_POST['domain'] ) ? wp_unslash( $_POST['domain'] ) : '', false );
    if ( ! $domain ) wp_send_json_error( array( 'message' => 'Enter a valid public domain.' ), 400 );
    $spf = array_values( array_filter( ufx_dns_txt_values( $domain ), function( $v ) { return 0 === stripos( $v, 'v=spf1' ); } ) );
    wp_send_json_success( array( 'domain' => $domain, 'record' => $spf ? $spf[0] : '', 'results' => array( array( 'label' => 'SPF record', 'value' => $spf ? $spf[0] : 'Not found' ), array( 'label' => 'Record count', 'value' => count( $spf ) ) ) ) );
}

function ufx_api_dmarc() {
    ufx_check_nonce();
    $domain = ufx_extra_domain( isset( $_POST['domain'] ) ? wp_unslash( $_POST['domain'] ) : '', false );
    if ( ! $domain ) wp_send_json_error( array( 'message' => 'Enter a valid public domain.' ), 400 );
    $records = ufx_dns_txt_values( '_dmarc.' . $domain );
    wp_send_json_success( array( 'domain' => $domain, 'results' => array( array( 'label' => 'DMARC record', 'value' => $records ? $records[0] : 'Not found' ), array( 'label' => 'Policy', 'value' => $records && preg_match( '/(?:^|;)\s*p=([^;]+)/i', $records[0], $m ) ? sanitize_text_field( $m[1] ) : 'None' ) ) ) );
}

function ufx_api_dkim() {
    ufx_check_nonce();
    $domain = ufx_extra_domain( isset( $_POST['domain'] ) ? wp_unslash( $_POST['domain'] ) : '', false );
    $selector = isset( $_POST['selector'] ) ? sanitize_key( wp_unslash( $_POST['selector'] ) ) : 'default';
    if ( ! $domain || ! $selector ) wp_send_json_error( array( 'message' => 'Enter a valid domain and selector.' ), 400 );
    $host = $selector . '._domainkey.' . $domain;
    $records = ufx_dns_txt_values( $host );
    wp_send_json_success( array( 'host' => $host, 'results' => array( array( 'label' => 'DKIM record', 'value' => $records ? $records[0] : 'Not found' ) ) ) );
}

function ufx_api_port_check() {
    ufx_check_nonce();
    $domain = ufx_extra_domain( isset( $_POST['domain'] ) ? wp_unslash( $_POST['domain'] ) : '', false );
    $port = isset( $_POST['port'] ) ? absint( $_POST['port'] ) : 443;
    $allowed = array( 21, 22, 25, 53, 80, 110, 143, 443, 465, 587, 993, 995 );
    if ( ! $domain || ! in_array( $port, $allowed, true ) ) wp_send_json_error( array( 'message' => 'Use a valid public domain and an allowed service port.' ), 400 );
    $ip = ufx_extra_public_ip_for_host( $domain );
    if ( ! $ip ) wp_send_json_error( array( 'message' => 'The domain did not resolve to a public IP.' ), 400 );
    $start = microtime( true ); $error_number = 0; $error_string = '';
    $socket = @stream_socket_client( 'tcp://' . $ip . ':' . $port, $error_number, $error_string, 4, STREAM_CLIENT_CONNECT );
    $ms = (int) round( ( microtime( true ) - $start ) * 1000 );
    $is_open = is_resource( $socket );
    if ( is_resource( $socket ) ) fclose( $socket );
    wp_send_json_success( array( 'host' => $domain, 'ip' => $ip, 'port' => $port, 'results' => array( array( 'label' => 'Port status', 'value' => $is_open ? 'Open' : 'Closed or filtered' ), array( 'label' => 'Connection time', 'value' => $ms . ' ms' ) ) ) );
}

function ufx_api_safe_ping() {
    ufx_check_nonce();
    $url = ufx_clean_url( isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : '' );
    if ( ! $url ) wp_send_json_error( array( 'message' => 'Enter a valid public URL.' ), 400 );
    $start = microtime( true );
    $res = ufx_safe_remote_head( $url, array( 'timeout' => 10, 'redirection' => 3, 'user-agent' => 'UptimeFixer/2.0 Ping' ) );
    $ms = (int) round( ( microtime( true ) - $start ) * 1000 );
    if ( is_wp_error( $res ) ) wp_send_json_error( array( 'message' => $res->get_error_message() ), 502 );
    wp_send_json_success( array( 'results' => array( array( 'label' => 'HTTP status', 'value' => wp_remote_retrieve_response_code( $res ) ), array( 'label' => 'Round trip', 'value' => $ms . ' ms' ), array( 'label' => 'Method', 'value' => 'Safe HTTPS/HTTP HEAD test' ) ) ) );
}

function ufx_extra_network_info( $domain ) {
    $ip = ufx_extra_public_ip_for_host( $domain );
    if ( ! $ip ) return new WP_Error( 'resolution_failed', 'The domain did not resolve to a public IP.' );
    $endpoint = 'https://ipwho.is/' . rawurlencode( $ip ) . '?fields=success,ip,type,continent,country,region,city,connection';
    $res = wp_safe_remote_get( $endpoint, array( 'timeout' => 10, 'user-agent' => 'UptimeFixer/2.0 Network' ) );
    $data = is_wp_error( $res ) ? array() : json_decode( wp_remote_retrieve_body( $res ), true );
    return array( 'ip' => $ip, 'data' => is_array( $data ) ? $data : array() );
}

function ufx_api_route_diagnostic() {
    ufx_check_nonce();
    $domain = ufx_extra_domain( isset( $_POST['domain'] ) ? wp_unslash( $_POST['domain'] ) : '' );
    if ( ! $domain ) wp_send_json_error( array( 'message' => 'Enter a valid public domain.' ), 400 );
    $info = ufx_extra_network_info( $domain );
    if ( is_wp_error( $info ) ) wp_send_json_error( array( 'message' => $info->get_error_message() ), 400 );
    $conn = isset( $info['data']['connection'] ) && is_array( $info['data']['connection'] ) ? $info['data']['connection'] : array();
    wp_send_json_success( array( 'results' => array( array( 'label' => 'Resolved IP', 'value' => $info['ip'] ), array( 'label' => 'ASN', 'value' => isset( $conn['asn'] ) ? $conn['asn'] : 'Unknown' ), array( 'label' => 'Network organization', 'value' => isset( $conn['org'] ) ? $conn['org'] : 'Unknown' ), array( 'label' => 'Diagnostic mode', 'value' => 'Safe DNS/ASN analysis; server shell access is disabled.' ) ) ) );
}

function ufx_api_hosting() {
    ufx_check_nonce();
    $domain = ufx_extra_domain( isset( $_POST['domain'] ) ? wp_unslash( $_POST['domain'] ) : '' );
    if ( ! $domain ) wp_send_json_error( array( 'message' => 'Enter a valid public domain.' ), 400 );
    $info = ufx_extra_network_info( $domain );
    if ( is_wp_error( $info ) ) wp_send_json_error( array( 'message' => $info->get_error_message() ), 400 );
    $data = $info['data']; $conn = isset( $data['connection'] ) && is_array( $data['connection'] ) ? $data['connection'] : array();
    wp_send_json_success( array( 'results' => array( array( 'label' => 'IP address', 'value' => $info['ip'] ), array( 'label' => 'Provider', 'value' => isset( $conn['isp'] ) ? $conn['isp'] : ( isset( $conn['org'] ) ? $conn['org'] : 'Unknown' ) ), array( 'label' => 'ASN', 'value' => isset( $conn['asn'] ) ? $conn['asn'] : 'Unknown' ), array( 'label' => 'Country', 'value' => isset( $data['country'] ) ? $data['country'] : 'Unknown' ) ) ) );
}

function ufx_api_site_detect() {
    ufx_check_nonce();
    $url = ufx_clean_url( isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : '' );
    if ( ! $url ) wp_send_json_error( array( 'message' => 'Enter a valid public URL.' ), 400 );
    $res = ufx_safe_remote_get( $url, array( 'timeout' => 15, 'redirection' => 4, 'limit_response_size' => 524288, 'user-agent' => 'UptimeFixer/2.0 Detector' ) );
    if ( is_wp_error( $res ) ) wp_send_json_error( array( 'message' => $res->get_error_message() ), 502 );
    $body = strtolower( (string) wp_remote_retrieve_body( $res ) ); $headers = ufx_extra_headers_array( $res ); $found = array();
    $patterns = array( 'WordPress' => array( 'wp-content', 'wp-includes' ), 'Shopify' => array( 'cdn.shopify.com', 'shopify-section' ), 'Wix' => array( 'wixstatic.com' ), 'Squarespace' => array( 'static.squarespace.com' ), 'Webflow' => array( 'webflow.js' ), 'React' => array( 'data-reactroot', '__next_data__' ), 'Google Analytics' => array( 'googletagmanager.com', 'google-analytics.com' ), 'Cloudflare' => array( 'cdn-cgi', 'cf-ray' ) );
    foreach ( $patterns as $label => $needles ) foreach ( $needles as $needle ) if ( false !== strpos( $body . ' ' . wp_json_encode( $headers ), strtolower( $needle ) ) ) { $found[] = $label; break; }
    wp_send_json_success( array( 'results' => array_map( function( $value ) { return array( 'label' => 'Detected', 'value' => $value ); }, $found ? array_unique( $found ) : array( 'No common platform signature detected' ) ) ) );
}

function ufx_api_subdomains() {
    ufx_check_nonce();
    $domain = ufx_extra_domain( isset( $_POST['domain'] ) ? wp_unslash( $_POST['domain'] ) : '', false );
    if ( ! $domain ) wp_send_json_error( array( 'message' => 'Enter a valid public domain.' ), 400 );
    $res = wp_safe_remote_get( 'https://crt.sh/?q=%25.' . rawurlencode( $domain ) . '&output=json', array( 'timeout' => 18, 'limit_response_size' => 1048576, 'user-agent' => 'UptimeFixer/2.0 Subdomains' ) );
    if ( is_wp_error( $res ) ) wp_send_json_error( array( 'message' => $res->get_error_message() ), 502 );
    $data = json_decode( wp_remote_retrieve_body( $res ), true ); $names = array();
    if ( is_array( $data ) ) foreach ( $data as $row ) if ( ! empty( $row['name_value'] ) ) foreach ( preg_split( '/\s+/', strtolower( $row['name_value'] ) ) as $name ) if ( preg_match( '/^(?:[a-z0-9-]+\.)+' . preg_quote( $domain, '/' ) . '$/i', $name ) ) $names[] = ltrim( $name, '*.' );
    $names = array_slice( array_values( array_unique( $names ) ), 0, 200 );
    wp_send_json_success( array( 'domain' => $domain, 'count' => count( $names ), 'results' => array_map( function( $name ) { return array( 'label' => 'Subdomain', 'value' => $name ); }, $names ) ) );
}

function ufx_api_headers_view() {
    ufx_check_nonce();
    $url = ufx_clean_url( isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : '' );
    if ( ! $url ) wp_send_json_error( array( 'message' => 'Enter a valid public URL.' ), 400 );
    $res = ufx_safe_remote_head( $url, array( 'timeout' => 12, 'redirection' => 4, 'user-agent' => 'UptimeFixer/2.0 Headers' ) );
    if ( is_wp_error( $res ) ) wp_send_json_error( array( 'message' => $res->get_error_message() ), 502 );
    $headers = ufx_extra_headers_array( $res ); $results = array();
    foreach ( $headers as $key => $value ) $results[] = array( 'header' => $key, 'value' => $value );
    wp_send_json_success( array( 'status' => wp_remote_retrieve_response_code( $res ), 'results' => $results ) );
}

/**
 * Request PageSpeed with an optional site key, bounded caching and stale-cache
 * fallback. PageSpeed reports can exceed 2 MB, especially with several audit
 * categories, so the previous response cap could truncate otherwise valid JSON.
 */
function ufx_compact_pagespeed_data( $data ) {
    $lh=isset($data['lighthouseResult'])&&is_array($data['lighthouseResult'])?$data['lighthouseResult']:array();$categories=array();$audit_ids=array('first-contentful-paint','largest-contentful-paint','cumulative-layout-shift','total-blocking-time','speed-index');
    foreach(array('performance','accessibility','best-practices','seo') as $category){if(!isset($lh['categories'][$category])||!is_array($lh['categories'][$category]))continue;$source=$lh['categories'][$category];$categories[$category]=array('score'=>isset($source['score'])?(float)$source['score']:null);if('accessibility'===$category&&isset($source['auditRefs']))foreach(array_slice((array)$source['auditRefs'],0,100) as $ref)if(!empty($ref['id']))$audit_ids[]=sanitize_key($ref['id']);}
    $audits=array();foreach(array_unique($audit_ids) as $id){if(empty($lh['audits'][$id])||!is_array($lh['audits'][$id]))continue;$source=$lh['audits'][$id];$audits[$id]=array('score'=>isset($source['score'])&&is_numeric($source['score'])?(float)$source['score']:null,'title'=>isset($source['title'])?sanitize_text_field($source['title']):'','displayValue'=>isset($source['displayValue'])?sanitize_text_field($source['displayValue']):'');}
    $out=array('lighthouseResult'=>array('categories'=>$categories,'audits'=>$audits));foreach(array('loadingExperience','originLoadingExperience') as $experience){if(!empty($data[$experience]['metrics']['INTERACTION_TO_NEXT_PAINT']))$out[$experience]=array('metrics'=>array('INTERACTION_TO_NEXT_PAINT'=>$data[$experience]['metrics']['INTERACTION_TO_NEXT_PAINT']));}
    return $out;
}

function ufx_pagespeed_data( $url, $device, $categories = array( 'performance' ) ) {
    $device = 'desktop' === $device ? 'desktop' : 'mobile';
    $categories = array_values( array_intersect( array_map( 'sanitize_key', (array) $categories ), array( 'performance', 'accessibility', 'best-practices', 'seo' ) ) );
    if ( ! $categories ) $categories = array( 'performance' );
    sort( $categories );

    $cache_key = 'ufx_psi_v2_' . md5( $url . '|' . $device . '|' . implode( ',', $categories ) );
    $cached = get_transient( $cache_key );
    if ( is_array( $cached ) && ! empty( $cached['lighthouseResult'] ) && ! empty( $cached['_ufx_cached_at'] ) && time() - (int) $cached['_ufx_cached_at'] < 6 * HOUR_IN_SECONDS ) {
        $cached['_ufx_source'] = 'fresh-cache';
        $cached['_ufx_notice'] = 'A recent PageSpeed report was reused to avoid unnecessary provider quota usage.';
        return $cached;
    }

    $args = array( 'url' => $url, 'strategy' => $device );
    $endpoint = add_query_arg( $args, 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed' );
    foreach ( $categories as $category ) $endpoint .= '&category=' . rawurlencode( $category );
    $key = function_exists( 'alltools_integration_secret' ) ? alltools_integration_secret( 'pagespeed_api_key' ) : '';
    if ( $key ) $endpoint .= '&key=' . rawurlencode( $key );

    $res = wp_safe_remote_get( $endpoint, array(
        'timeout'             => 45,
        'limit_response_size' => count( $categories ) > 1 ? 12582912 : 8388608,
        'user-agent'          => 'UptimeFixer/3.2 PageSpeed',
    ) );
    $error_message = '';
    if ( is_wp_error( $res ) ) {
        $error_message = $res->get_error_message();
    } else {
        $code = (int) wp_remote_retrieve_response_code( $res );
        $body = (string) wp_remote_retrieve_body( $res );
        $data = json_decode( $body, true );
        if ( 200 === $code && is_array( $data ) && ! empty( $data['lighthouseResult'] ) ) {
            $data = ufx_compact_pagespeed_data( $data );
            $data['_ufx_cached_at'] = time();
            $data['_ufx_source'] = 'live-pagespeed';
            $data['_ufx_notice'] = $key ? 'Live Google PageSpeed report generated with the configured site API key.' : 'Live Google PageSpeed report generated using the provider\'s shared unauthenticated quota.';
            set_transient( $cache_key, $data, 7 * DAY_IN_SECONDS );
            return $data;
        }
        if ( is_array( $data ) && ! empty( $data['error']['message'] ) ) {
            $error_message = sanitize_text_field( $data['error']['message'] );
        } elseif ( JSON_ERROR_NONE !== json_last_error() ) {
            $error_message = 'The PageSpeed response was incomplete or invalid.';
        } else {
            $error_message = 'PageSpeed did not return a Lighthouse report.';
        }
    }

    // A report remains useful for several days when Google is temporarily busy.
    if ( is_array( $cached ) && ! empty( $cached['lighthouseResult'] ) ) {
        $cached['_ufx_source'] = 'stale-cache';
        $cached['_ufx_notice'] = 'Google PageSpeed is temporarily unavailable, so the most recent saved report is shown. Run the check again later for fresh lab data.';
        return $cached;
    }

    $message = 'Google PageSpeed is temporarily unavailable or its quota is exhausted.';
    if ( ! $key ) $message .= ' Add a PageSpeed API key under Uptime Fixer → Theme Settings → API Integrations for more reliable live reports.';
    if ( $error_message && false === stripos( $error_message, 'API key' ) ) $message .= ' Provider detail: ' . alltools_seo_trim_text( $error_message, 180 );
    return new WP_Error( 'pagespeed_failed', $message );
}

/** Fetch a bounded public-page snapshot for honest non-Lighthouse fallbacks. */
function ufx_public_page_snapshot( $url ) {
    $start = microtime( true );
    $res = ufx_safe_remote_get( $url, array(
        'timeout'             => 18,
        'redirection'         => 4,
        'limit_response_size' => 1048576,
        'user-agent'          => 'UptimeFixer/3.2 Fallback Audit',
    ) );
    $milliseconds = (int) round( ( microtime( true ) - $start ) * 1000 );
    if ( is_wp_error( $res ) ) return $res;
    return array(
        'url'     => $url,
        'code'    => (int) wp_remote_retrieve_response_code( $res ),
        'ms'      => $milliseconds,
        'headers' => ufx_extra_headers_array( $res ),
        'body'    => (string) wp_remote_retrieve_body( $res ),
    );
}

/** Core Web Vitals fallback: useful server/page facts without invented metrics. */
function ufx_pagespeed_fallback_results( $snapshot ) {
    $headers = isset( $snapshot['headers'] ) ? $snapshot['headers'] : array();
    $body = isset( $snapshot['body'] ) ? $snapshot['body'] : '';
    $has_title = (bool) preg_match( '/<title[^>]*>\s*[^<]+\s*<\/title>/is', $body );
    $has_viewport = (bool) preg_match( '/<meta[^>]+name=["\']viewport["\']/i', $body );
    $compressed = isset( $headers['content-encoding'] ) ? $headers['content-encoding'] : 'Not advertised';
    $cache = isset( $headers['cache-control'] ) ? $headers['cache-control'] : 'Not advertised';
    return array(
        array( 'label' => 'Audit mode', 'value' => 'Live server fallback (not Lighthouse)' ),
        array( 'label' => 'HTTP status', 'value' => (string) $snapshot['code'] ),
        array( 'label' => 'Server response snapshot', 'value' => (string) $snapshot['ms'] . ' ms' ),
        array( 'label' => 'Downloaded HTML', 'value' => size_format( strlen( $body ), 1 ) ),
        array( 'label' => 'HTTPS', 'value' => 0 === strpos( $snapshot['url'], 'https://' ) ? 'Yes' : 'No' ),
        array( 'label' => 'Compression', 'value' => sanitize_text_field( $compressed ) ),
        array( 'label' => 'Cache-Control', 'value' => sanitize_text_field( $cache ) ),
        array( 'label' => 'Page title', 'value' => $has_title ? 'Found' : 'Missing' ),
        array( 'label' => 'Mobile viewport', 'value' => $has_viewport ? 'Found' : 'Missing' ),
    );
}

function ufx_api_pagespeed() {
    ufx_check_nonce();
    $url = ufx_clean_url( isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : '' ); $device = isset( $_POST['device'] ) ? sanitize_key( wp_unslash( $_POST['device'] ) ) : 'mobile';
    if ( ! $url ) wp_send_json_error( array( 'message' => 'Enter a valid public URL.' ), 400 );
    $data = ufx_pagespeed_data( $url, $device );
    if ( is_wp_error( $data ) ) {
        $snapshot = ufx_public_page_snapshot( $url );
        if ( is_wp_error( $snapshot ) ) wp_send_json_error( array( 'message' => $data->get_error_message() . ' The fallback page check also failed: ' . $snapshot->get_error_message() ), 502 );
        $has_key=function_exists('alltools_integration_secret')&&alltools_integration_secret('pagespeed_api_key');
        wp_send_json_success( array(
            'url'     => $url,
            'device'  => $device,
            'source'  => 'server-fallback',
            'results' => ufx_pagespeed_fallback_results( $snapshot ),
            'notice'  => 'Google lab and Core Web Vitals data is unavailable right now. The live server snapshot above is shown instead and does not invent LCP, CLS or INP values. '.($has_key?'The configured PageSpeed key may be rate-limited; try again later.':'Add a PageSpeed API key under Uptime Fixer → Theme Settings → API Integrations for more reliable Lighthouse reports.'),
        ) );
    }
    $lh = $data['lighthouseResult']; $audits = isset( $lh['audits'] ) ? $lh['audits'] : array(); $score = isset( $lh['categories']['performance']['score'] ) ? round( 100 * $lh['categories']['performance']['score'] ) : '—';
    $metrics = array( 'first-contentful-paint' => 'First Contentful Paint', 'largest-contentful-paint' => 'Largest Contentful Paint', 'cumulative-layout-shift' => 'Cumulative Layout Shift', 'total-blocking-time' => 'Total Blocking Time', 'speed-index' => 'Speed Index' ); $results = array( array( 'label' => 'Lighthouse performance score', 'value' => $score . '/100' ) );
    foreach ( $metrics as $key => $label ) $results[] = array( 'label' => $label, 'value' => isset( $audits[ $key ]['displayValue'] ) ? sanitize_text_field( $audits[ $key ]['displayValue'] ) : 'Not available' );
    $field_metrics = isset( $data['loadingExperience']['metrics'] ) ? $data['loadingExperience']['metrics'] : ( isset( $data['originLoadingExperience']['metrics'] ) ? $data['originLoadingExperience']['metrics'] : array() );
    if ( isset( $field_metrics['INTERACTION_TO_NEXT_PAINT']['percentile'] ) ) {
        $results[] = array( 'label' => 'Field INP (75th percentile)', 'value' => absint( $field_metrics['INTERACTION_TO_NEXT_PAINT']['percentile'] ) . ' ms · ' . sanitize_text_field( isset( $field_metrics['INTERACTION_TO_NEXT_PAINT']['category'] ) ? $field_metrics['INTERACTION_TO_NEXT_PAINT']['category'] : 'Unrated' ) );
    }
    wp_send_json_success( array( 'url' => $url, 'device' => $device, 'source' => isset( $data['_ufx_source'] ) ? $data['_ufx_source'] : 'pagespeed', 'results' => $results, 'notice' => isset( $data['_ufx_notice'] ) ? $data['_ufx_notice'] : '' ) );
}

function ufx_api_mobile_test() {
    ufx_check_nonce();
    $url = ufx_clean_url( isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : '' );
    if ( ! $url ) wp_send_json_error( array( 'message' => 'Enter a valid public URL.' ), 400 );
    $res = ufx_safe_remote_get( $url, array( 'timeout' => 15, 'limit_response_size' => 524288, 'user-agent' => 'UptimeFixer/2.0 Mobile Test' ) );
    if ( is_wp_error( $res ) ) wp_send_json_error( array( 'message' => $res->get_error_message() ), 502 );
    $body = (string) wp_remote_retrieve_body( $res );
    $results = array(
        array( 'label' => 'Viewport meta tag', 'value' => preg_match( '/<meta[^>]+name=["\']viewport["\']/i', $body ) ? 'Found' : 'Missing' ),
        array( 'label' => 'Responsive CSS media queries', 'value' => false !== stripos( $body, '@media' ) ? 'Found inline' : 'Not detected inline' ),
        array( 'label' => 'Status', 'value' => wp_remote_retrieve_response_code( $res ) ),
    );
    wp_send_json_success( array( 'results' => $results ) );
}

function ufx_api_currency() {
    ufx_check_nonce();
    $amount = isset( $_POST['amount'] ) ? (float) $_POST['amount'] : 0; $from = isset( $_POST['from'] ) ? strtoupper( sanitize_key( wp_unslash( $_POST['from'] ) ) ) : ''; $to = isset( $_POST['to'] ) ? strtoupper( sanitize_key( wp_unslash( $_POST['to'] ) ) ) : '';
    if ( $amount < 0 || $amount > 1000000000 || ! preg_match( '/^[A-Z]{3}$/', $from ) || ! preg_match( '/^[A-Z]{3}$/', $to ) ) wp_send_json_error( array( 'message' => 'Enter a valid amount and ISO currency codes such as USD and EUR.' ), 400 );
    if ( $from === $to ) wp_send_json_success( array( 'amount' => $amount, 'from' => $from, 'to' => $to, 'result' => round( $amount, 4 ), 'date' => gmdate( 'Y-m-d' ) ) );
    $cache_key='ufx_fx_'.md5($from.'|'.$to);$cached=get_transient($cache_key);$rate=0;$date='';$notice='';
    if(is_array($cached)&&!empty($cached['rate'])&&!empty($cached['saved'])&&time()-(int)$cached['saved']<12*HOUR_IN_SECONDS){$rate=(float)$cached['rate'];$date=isset($cached['date'])?sanitize_text_field($cached['date']):'';$notice='A recent reference rate was reused to keep conversion fast and reliable.';}
    if(!$rate){$endpoint=add_query_arg(array('from'=>$from,'to'=>$to),'https://api.frankfurter.app/latest');$res=wp_safe_remote_get($endpoint,array('timeout'=>12,'limit_response_size'=>262144,'user-agent'=>'UptimeFixer/3.2 Currency'));$data=is_wp_error($res)?array():json_decode(wp_remote_retrieve_body($res),true);if(!empty($data['rates'][$to])){$rate=(float)$data['rates'][$to];$date=isset($data['date'])?sanitize_text_field($data['date']):'';set_transient($cache_key,array('rate'=>$rate,'date'=>$date,'saved'=>time()),7*DAY_IN_SECONDS);}elseif(is_array($cached)&&!empty($cached['rate'])){$rate=(float)$cached['rate'];$date=isset($cached['date'])?sanitize_text_field($cached['date']):'';$notice='The live rate provider is temporarily unavailable, so the most recent saved reference rate is shown.';}}
    if(!$rate)wp_send_json_error(array('message'=>'The reference exchange rate is temporarily unavailable. Please try again.'),502);
    wp_send_json_success(array('amount'=>$amount,'from'=>$from,'to'=>$to,'rate'=>$rate,'result'=>round($amount*$rate,4),'date'=>$date,'notice'=>$notice));
}

function ufx_api_shorten() {
    ufx_check_nonce();
    $url = ufx_clean_url( isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : '' );
    if ( ! $url ) wp_send_json_error( array( 'message' => 'Enter a valid public HTTP or HTTPS URL.' ), 400 );
    $endpoint = add_query_arg( array( 'format' => 'json', 'url' => $url ), 'https://is.gd/create.php' );
    $res = wp_safe_remote_get( $endpoint, array( 'timeout' => 12, 'limit_response_size' => 262144, 'user-agent' => 'UptimeFixer/3.2 URL Shortener' ) );
    $data = is_wp_error( $res ) ? array() : json_decode( wp_remote_retrieve_body( $res ), true );
    if ( ! empty( $data['shorturl'] ) && wp_http_validate_url( $data['shorturl'] ) ) wp_send_json_success( array( 'short_url' => esc_url_raw( $data['shorturl'] ), 'provider' => 'is.gd' ) );
    $fallback=add_query_arg(array('url'=>$url),'https://tinyurl.com/api-create.php');$fallback_res=wp_safe_remote_get($fallback,array('timeout'=>12,'limit_response_size'=>4096,'user-agent'=>'UptimeFixer/3.2 URL Shortener'));$short=is_wp_error($fallback_res)?'':trim(wp_remote_retrieve_body($fallback_res));
    if(!$short||!wp_http_validate_url($short))wp_send_json_error(array('message'=>'Both URL shortening providers are temporarily unavailable. Please try again.'),502);
    wp_send_json_success(array('short_url'=>esc_url_raw($short),'provider'=>'TinyURL'));
}
