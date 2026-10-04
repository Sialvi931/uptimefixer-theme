<?php
/**
 * Hardened, no-key endpoints for live website and SEO inspections.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_ajax_ufx_seo_site_check', 'ufx_api_seo_site_check' );
add_action( 'wp_ajax_nopriv_ufx_seo_site_check', 'ufx_api_seo_site_check' );

/** Build a normalized origin from an already validated public URL. */
function ufx_seo_suite_origin( $url ) {
    $parts = wp_parse_url( $url );
    if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) return '';
    return strtolower( $parts['scheme'] ) . '://' . $parts['host'] . ( isset( $parts['port'] ) ? ':' . (int) $parts['port'] : '' );
}

/** Fetch a bounded public page through the central SSRF-safe wrapper. */
function ufx_seo_suite_fetch( $url, $limit = 2097152, $extra = array() ) {
    $args = array_merge( array(
        'timeout'             => 18,
        'redirection'         => 4,
        'limit_response_size' => min( 2097152, max( 4096, (int) $limit ) ),
        'user-agent'          => 'UptimeFixer/3.7 No-Key SEO Inspector',
    ), $extra );
    $response = ufx_safe_remote_get( $url, $args );
    if ( is_wp_error( $response ) ) return $response;
    return array(
        'url'     => $url,
        'code'    => (int) wp_remote_retrieve_response_code( $response ),
        'headers' => ufx_extra_headers_array( $response ),
        'body'    => (string) wp_remote_retrieve_body( $response ),
    );
}

/** Parse bounded HTML without loading remote resources. */
function ufx_seo_suite_dom( $html ) {
    if ( ! class_exists( 'DOMDocument' ) || ! class_exists( 'DOMXPath' ) ) {
        return new WP_Error( 'dom_missing', 'This server needs the PHP DOM extension for live page analysis.' );
    }
    $dom = new DOMDocument();
    $previous = libxml_use_internal_errors( true );
    $loaded = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . (string) $html, LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOERROR );
    libxml_clear_errors();
    libxml_use_internal_errors( $previous );
    if ( ! $loaded ) return new WP_Error( 'html_invalid', 'The fetched page could not be parsed as HTML.' );
    return array( 'dom' => $dom, 'xpath' => new DOMXPath( $dom ) );
}

/** Safely read the first matching meta value by name or property. */
function ufx_seo_suite_meta( $xpath, $key ) {
    $key = strtolower( (string) $key );
    foreach ( $xpath->query( '//meta[@content]' ) as $meta ) {
        $name = strtolower( trim( $meta->getAttribute( 'name' ) ) );
        $property = strtolower( trim( $meta->getAttribute( 'property' ) ) );
        if ( $name === $key || $property === $key ) return trim( $meta->getAttribute( 'content' ) );
    }
    return '';
}

/** First matching link href by rel token. */
function ufx_seo_suite_link_rel( $xpath, $token ) {
    $token = strtolower( trim( (string) $token ) );
    foreach ( $xpath->query( '//link[@rel][@href]' ) as $link ) {
        $rels = preg_split( '/\s+/', strtolower( trim( $link->getAttribute( 'rel' ) ) ) );
        if ( in_array( $token, $rels, true ) ) return trim( $link->getAttribute( 'href' ) );
    }
    return '';
}

/** Plain readable text with scripts, styles and hidden template content removed. */
function ufx_seo_suite_readable_text( $dom, $xpath ) {
    foreach ( $xpath->query( '//script|//style|//noscript|//svg|//template' ) as $node ) {
        if ( $node->parentNode ) $node->parentNode->removeChild( $node );
    }
    $body = $xpath->query( '//body' )->item( 0 );
    $text = $body ? $body->textContent : $dom->textContent;
    return trim( preg_replace( '/\s+/u', ' ', (string) $text ) );
}

/** Unicode-aware word list used by density and page word-count checks. */
function ufx_seo_suite_words( $text ) {
    preg_match_all( "/[\p{L}\p{N}]+(?:['’][\p{L}\p{N}]+)*/u", (string) $text, $matches );
    return isset( $matches[0] ) ? $matches[0] : array();
}

/** Return a compact metric row. */
function ufx_seo_suite_row( $label, $value ) {
    return array( 'label' => sanitize_text_field( (string) $label ), 'value' => sanitize_text_field( (string) $value ) );
}

/** Fetch a declared/default favicon with safe public-target validation. */
function ufx_seo_suite_probe_url( $url ) {
    $url = ufx_clean_url( $url, false );
    if ( ! $url ) return array( 'code' => 0, 'type' => '' );
    $response = ufx_safe_remote_head( $url, array( 'timeout'=>5, 'redirection'=>3, 'user-agent'=>'UptimeFixer/3.7 Asset Check' ) );
    if ( is_wp_error( $response ) || 405 === (int) wp_remote_retrieve_response_code( $response ) ) {
        $response = ufx_safe_remote_get( $url, array( 'timeout'=>6, 'redirection'=>3, 'limit_response_size'=>8192, 'user-agent'=>'UptimeFixer/3.7 Asset Check' ) );
    }
    if ( is_wp_error( $response ) ) return array( 'code' => 0, 'type' => '' );
    return array(
        'code' => (int) wp_remote_retrieve_response_code( $response ),
        'type' => sanitize_text_field( (string) wp_remote_retrieve_header( $response, 'content-type' ) ),
    );
}

/** Main allow-listed dispatcher. */
function ufx_api_seo_site_check() {
    ufx_check_nonce();
    $mode = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : '';
    $raw  = isset( $_POST['url'] ) ? alltools_text_slice( sanitize_text_field( wp_unslash( $_POST['url'] ) ), 0, 2000 ) : '';
    $keyword = isset( $_POST['keyword'] ) ? alltools_text_slice( sanitize_text_field( wp_unslash( $_POST['keyword'] ) ), 0, 120 ) : '';
    $allowed = array_keys( alltools_seo_suite_definitions() );
    if ( ! in_array( $mode, $allowed, true ) ) wp_send_json_error( array( 'message' => 'Unsupported website check.' ), 400 );

    $url = ufx_clean_url( $raw );
    if ( ! $url ) wp_send_json_error( array( 'message' => 'Enter a valid public website or URL.' ), 400 );
    $origin = ufx_seo_suite_origin( $url );
    if ( ! $origin ) wp_send_json_error( array( 'message' => 'The public website origin could not be determined.' ), 400 );

    /* Root text-file checks. */
    if ( 'ads-txt-checker' === $mode ) {
        $target = $origin . '/ads.txt';
        $data = ufx_seo_suite_fetch( $target, 1048576 );
        if ( is_wp_error( $data ) ) wp_send_json_error( array( 'message' => $data->get_error_message() ), 502 );
        $lines = preg_split( '/\r?\n/', $data['body'] );
        $records = array(); $comments = 0;
        foreach ( (array) $lines as $line ) {
            $line = trim( $line );
            if ( '' === $line ) continue;
            if ( '#' === substr( $line, 0, 1 ) ) { $comments++; continue; }
            $parts = array_map( 'trim', explode( ',', preg_replace( '/\s+#.*$/', '', $line ) ) );
            if ( count( $parts ) >= 3 && preg_match( '/^[a-z0-9.-]+$/i', $parts[0] ) && in_array( strtoupper( $parts[2] ), array( 'DIRECT', 'RESELLER' ), true ) ) {
                $records[] = array(
                    'Seller'       => sanitize_text_field( $parts[0] ),
                    'Publisher ID' => sanitize_text_field( $parts[1] ),
                    'Relationship' => sanitize_text_field( strtoupper( $parts[2] ) ),
                    'Authority ID' => isset( $parts[3] ) ? sanitize_text_field( $parts[3] ) : '—',
                );
            }
        }
        $google = array_filter( $records, function( $record ) { return 'google.com' === strtolower( $record['Seller'] ); } );
        wp_send_json_success( array(
            'results' => array(
                ufx_seo_suite_row( 'Checked URL', $target ),
                ufx_seo_suite_row( 'HTTP status', $data['code'] ),
                ufx_seo_suite_row( 'Valid seller records', count( $records ) ),
                ufx_seo_suite_row( 'Google seller records', count( $google ) ),
                ufx_seo_suite_row( 'Comment lines', $comments ),
            ),
            'details' => array_slice( $records, 0, 100 ),
            'notice'  => 200 === $data['code'] && $records ? 'The root ads.txt file is reachable and contains parseable seller records.' : 'The file is missing, blocked or contains no parseable seller records.',
        ) );
    }

    if ( 'live-robots-txt-tester' === $mode ) {
        $target = $origin . '/robots.txt';
        $data = ufx_seo_suite_fetch( $target, 524288 );
        if ( is_wp_error( $data ) ) wp_send_json_error( array( 'message' => $data->get_error_message() ), 502 );
        preg_match_all( '/^\s*user-agent\s*:\s*(.+)$/mi', $data['body'], $agents );
        preg_match_all( '/^\s*allow\s*:\s*(.*)$/mi', $data['body'], $allows );
        preg_match_all( '/^\s*disallow\s*:\s*(.*)$/mi', $data['body'], $disallows );
        preg_match_all( '/^\s*sitemap\s*:\s*(\S+)/mi', $data['body'], $sitemaps );
        $blocks_all = (bool) preg_match( '/user-agent\s*:\s*\*[^#]*?disallow\s*:\s*\/\s*(?:\r?\n|$)/is', $data['body'] );
        $details = array();
        foreach ( array_slice( isset( $sitemaps[1] ) ? $sitemaps[1] : array(), 0, 20 ) as $sitemap_url ) $details[] = array( 'Directive'=>'Sitemap', 'Value'=>sanitize_text_field( $sitemap_url ) );
        wp_send_json_success( array(
            'results' => array(
                ufx_seo_suite_row( 'Checked URL', $target ), ufx_seo_suite_row( 'HTTP status', $data['code'] ),
                ufx_seo_suite_row( 'User-agent groups', count( isset( $agents[1] ) ? $agents[1] : array() ) ),
                ufx_seo_suite_row( 'Allow directives', count( isset( $allows[1] ) ? $allows[1] : array() ) ),
                ufx_seo_suite_row( 'Disallow directives', count( isset( $disallows[1] ) ? $disallows[1] : array() ) ),
                ufx_seo_suite_row( 'Sitemap declarations', count( isset( $sitemaps[1] ) ? $sitemaps[1] : array() ) ),
                ufx_seo_suite_row( 'Site-wide block detected', $blocks_all ? 'Yes — review immediately' : 'No' ),
            ),
            'details' => $details,
            'notice'  => 'Robots.txt controls crawling, not guaranteed indexing. Review directives in their user-agent groups.',
        ) );
    }

    if ( 'ipv6-checker' === $mode ) {
        $host = (string) wp_parse_url( $url, PHP_URL_HOST );
        $records = defined( 'DNS_AAAA' ) ? dns_get_record( $host, DNS_AAAA ) : array();
        $details = array();
        foreach ( array_slice( is_array( $records ) ? $records : array(), 0, 20 ) as $record ) {
            if ( ! empty( $record['ipv6'] ) ) $details[] = array( 'Hostname'=>$host, 'IPv6 address'=>sanitize_text_field( $record['ipv6'] ) );
        }
        wp_send_json_success( array(
            'results' => array( ufx_seo_suite_row( 'Hostname', $host ), ufx_seo_suite_row( 'AAAA records', count( $details ) ), ufx_seo_suite_row( 'IPv6 DNS support', $details ? 'Detected' : 'Not detected' ) ),
            'details' => $details,
            'notice'  => 'An AAAA record shows IPv6 DNS configuration; it does not by itself test every network path.',
        ) );
    }

    if ( 'sitemap-url-analyzer' === $mode ) {
        $path = (string) wp_parse_url( $url, PHP_URL_PATH );
        $candidates = preg_match( '/\.xml(?:\.gz)?$/i', $path ) ? array( $url ) : array( $origin . '/sitemap_index.xml', $origin . '/wp-sitemap.xml', $origin . '/sitemap.xml' );
        $data = null; $target = '';
        foreach ( $candidates as $candidate ) {
            $attempt = ufx_seo_suite_fetch( $candidate, 2097152 );
            if ( ! is_wp_error( $attempt ) && 200 === $attempt['code'] && false !== stripos( $attempt['body'], '<loc' ) ) { $data = $attempt; $target = $candidate; break; }
        }
        if ( ! $data ) wp_send_json_error( array( 'message' => 'No readable XML sitemap was found at the supplied or common sitemap URLs.' ), 404 );
        if ( ! function_exists( 'simplexml_load_string' ) ) wp_send_json_error( array( 'message' => 'This server needs PHP SimpleXML for sitemap analysis.' ), 503 );
        $previous = libxml_use_internal_errors( true );
        $xml = simplexml_load_string( $data['body'], 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA );
        libxml_clear_errors(); libxml_use_internal_errors( $previous );
        if ( false === $xml ) wp_send_json_error( array( 'message' => 'The sitemap response is not valid XML.' ), 422 );
        $locs = $xml->xpath( '//*[local-name()="loc"]' );
        $type = 'sitemapindex' === strtolower( $xml->getName() ) ? 'Sitemap index' : 'URL set';
        $details = array();
        foreach ( array_slice( (array) $locs, 0, 100 ) as $loc ) $details[] = array( 'URL'=>sanitize_text_field( (string) $loc ) );
        wp_send_json_success( array(
            'results' => array( ufx_seo_suite_row( 'Sitemap URL', $target ), ufx_seo_suite_row( 'Document type', $type ), ufx_seo_suite_row( 'Location entries', count( (array) $locs ) ), ufx_seo_suite_row( 'HTTP status', $data['code'] ) ),
            'details' => $details,
            'notice'  => count( (array) $locs ) > 100 ? 'The first 100 entries are shown; the total count includes the complete bounded response.' : 'All detected locations are shown.',
        ) );
    }

    /* The remaining modes inspect one live HTML response. */
    $data = ufx_seo_suite_fetch( $url );
    if ( is_wp_error( $data ) ) wp_send_json_error( array( 'message' => $data->get_error_message() ), 502 );
    $parsed = ufx_seo_suite_dom( $data['body'] );
    if ( is_wp_error( $parsed ) ) wp_send_json_error( array( 'message' => $parsed->get_error_message() ), 503 );
    $dom = $parsed['dom']; $xpath = $parsed['xpath']; $headers = $data['headers'];
    $title_node = $xpath->query( '//title' )->item( 0 );
    $title = $title_node ? trim( $title_node->textContent ) : '';
    $description = ufx_seo_suite_meta( $xpath, 'description' );
    $robots = ufx_seo_suite_meta( $xpath, 'robots' );
    $canonical_raw = ufx_seo_suite_link_rel( $xpath, 'canonical' );
    $canonical = $canonical_raw ? ufx_speed_abs_url( $url, $canonical_raw ) : '';

    if ( 'live-canonical-checker' === $mode ) {
        $canonicals = array();
        foreach ( $xpath->query( '//link[@rel][@href]' ) as $link ) {
            $rels = preg_split( '/\s+/', strtolower( trim( $link->getAttribute( 'rel' ) ) ) );
            if ( in_array( 'canonical', $rels, true ) ) $canonicals[] = ufx_speed_abs_url( $url, $link->getAttribute( 'href' ) );
        }
        $self = $canonical && untrailingslashit( strtolower( $canonical ) ) === untrailingslashit( strtolower( $url ) );
        wp_send_json_success( array(
            'results' => array( ufx_seo_suite_row( 'Fetched URL', $url ), ufx_seo_suite_row( 'Canonical tags', count( $canonicals ) ), ufx_seo_suite_row( 'Canonical URL', $canonical ? $canonical : 'Not found' ), ufx_seo_suite_row( 'Self-referencing', $self ? 'Yes' : 'No / different URL' ) ),
            'details' => array_map( function( $item ) { return array( 'Canonical URL'=>sanitize_text_field( $item ) ); }, array_slice( $canonicals, 0, 10 ) ),
            'notice'  => 1 === count( $canonicals ) ? 'One canonical declaration was found.' : 'A page should normally expose one consistent canonical URL.',
        ) );
    }

    if ( 'meta-seo-analyzer' === $mode ) {
        $h1 = $xpath->query( '//h1' );
        $first_h1 = $h1->length ? trim( $h1->item( 0 )->textContent ) : '';
        wp_send_json_success( array(
            'results' => array(
                ufx_seo_suite_row( 'HTTP status', $data['code'] ), ufx_seo_suite_row( 'Page title', $title ? $title : 'Missing' ),
                ufx_seo_suite_row( 'Title length', function_exists( 'mb_strlen' ) ? mb_strlen( $title ) : strlen( $title ) ),
                ufx_seo_suite_row( 'Meta description', $description ? $description : 'Missing' ),
                ufx_seo_suite_row( 'Description length', function_exists( 'mb_strlen' ) ? mb_strlen( $description ) : strlen( $description ) ),
                ufx_seo_suite_row( 'Meta robots', $robots ? $robots : 'Not declared' ), ufx_seo_suite_row( 'H1 headings', $h1->length ),
                ufx_seo_suite_row( 'First H1', $first_h1 ? $first_h1 : 'Missing' ),
            ),
            'notice' => 'Google may generate title links and snippets from several page signals; these checks report the current HTML only.',
        ) );
    }

    if ( 'open-graph-checker' === $mode ) {
        $fields = array( 'og:title', 'og:description', 'og:image', 'og:url', 'og:type', 'og:site_name', 'twitter:card', 'twitter:title', 'twitter:description', 'twitter:image' );
        $results = array();
        foreach ( $fields as $field ) $results[] = ufx_seo_suite_row( $field, ( $value = ufx_seo_suite_meta( $xpath, $field ) ) ? $value : 'Missing' );
        wp_send_json_success( array( 'results'=>$results, 'notice'=>'The checker reads metadata in the fetched HTML; social platforms can cache an older preview.' ) );
    }

    if ( 'link-extractor' === $mode ) {
        $base_host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
        $seen = array(); $details = array(); $internal = 0; $external = 0; $nofollow = 0;
        foreach ( $xpath->query( '//a[@href]' ) as $link ) {
            $href = trim( $link->getAttribute( 'href' ) );
            if ( '' === $href || preg_match( '#^(?:#|mailto:|tel:|javascript:)#i', $href ) ) continue;
            $absolute = ufx_speed_abs_url( $url, $href );
            if ( ! $absolute || isset( $seen[ $absolute ] ) ) continue;
            $seen[ $absolute ] = true;
            $host = strtolower( (string) wp_parse_url( $absolute, PHP_URL_HOST ) );
            $type = $host === $base_host ? 'Internal' : 'External';
            if ( 'Internal' === $type ) $internal++; else $external++;
            $rel = strtolower( $link->getAttribute( 'rel' ) );
            if ( false !== strpos( $rel, 'nofollow' ) ) $nofollow++;
            if ( count( $details ) < 100 ) $details[] = array( 'Type'=>$type, 'Anchor'=>alltools_text_slice( trim( $link->textContent ), 0, 100 ), 'URL'=>sanitize_text_field( $absolute ), 'Rel'=>$rel ? sanitize_text_field( $rel ) : '—' );
        }
        wp_send_json_success( array( 'results'=>array( ufx_seo_suite_row('Unique links',count($seen)), ufx_seo_suite_row('Internal links',$internal), ufx_seo_suite_row('External links',$external), ufx_seo_suite_row('Nofollow links',$nofollow) ), 'details'=>$details, 'notice'=>'Duplicate destinations are consolidated; the first 100 unique links are shown.' ) );
    }

    if ( 'broken-image-checker' === $mode ) {
        $sources = array();
        foreach ( $xpath->query( '//img' ) as $image ) {
            $src = $image->getAttribute( 'src' );
            if ( ! $src ) $src = $image->getAttribute( 'data-src' );
            $absolute = $src ? ufx_speed_abs_url( $url, $src ) : '';
            if ( $absolute && ! isset( $sources[ $absolute ] ) ) $sources[ $absolute ] = true;
        }
        $details = array(); $broken = 0; $checked = 0;
        foreach ( array_slice( array_keys( $sources ), 0, 12 ) as $image_url ) {
            $probe = ufx_seo_suite_probe_url( $image_url ); $checked++;
            $ok = $probe['code'] >= 200 && $probe['code'] < 400;
            if ( ! $ok ) $broken++;
            $details[] = array( 'Status'=>$probe['code'] ? $probe['code'] : 'Failed', 'Result'=>$ok?'Reachable':'Broken / blocked', 'Image URL'=>sanitize_text_field($image_url) );
        }
        wp_send_json_success( array( 'results'=>array( ufx_seo_suite_row('Images found',count($sources)), ufx_seo_suite_row('Images tested',$checked), ufx_seo_suite_row('Failed sample',$broken) ), 'details'=>$details, 'notice'=>count($sources)>12?'A safe sample of 12 unique images was tested to keep the public check bounded.':'Every detected unique image was tested.' ) );
    }

    if ( 'compression-checker' === $mode ) {
        $encoding = isset( $headers['content-encoding'] ) ? $headers['content-encoding'] : '';
        wp_send_json_success( array( 'results'=>array( ufx_seo_suite_row('HTTP status',$data['code']), ufx_seo_suite_row('Content-Encoding',$encoding?$encoding:'Not advertised'), ufx_seo_suite_row('Vary',isset($headers['vary'])?$headers['vary']:'Not advertised'), ufx_seo_suite_row('Content-Type',isset($headers['content-type'])?$headers['content-type']:'Not advertised'), ufx_seo_suite_row('Downloaded HTML',size_format(strlen($data['body']),1)) ), 'notice'=>$encoding?'The response advertised content compression.':'No content-encoding header was visible to this server request; CDN behavior can vary by client.' ) );
    }

    if ( 'favicon-checker' === $mode ) {
        $icons = array();
        foreach ( $xpath->query( '//link[@rel][@href]' ) as $link ) {
            if ( false === strpos( strtolower( $link->getAttribute( 'rel' ) ), 'icon' ) ) continue;
            $icon = ufx_speed_abs_url( $url, $link->getAttribute( 'href' ) );
            if ( $icon && ! in_array( $icon, $icons, true ) ) $icons[] = $icon;
        }
        if ( ! $icons ) $icons[] = $origin . '/favicon.ico';
        $probe = ufx_seo_suite_probe_url( $icons[0] );
        $details = array(); foreach ( array_slice($icons,0,20) as $icon ) $details[] = array('Icon URL'=>sanitize_text_field($icon));
        wp_send_json_success( array( 'results'=>array( ufx_seo_suite_row('Declared/default icons',count($icons)), ufx_seo_suite_row('Primary icon',$icons[0]), ufx_seo_suite_row('Primary HTTP status',$probe['code']?$probe['code']:'Failed'), ufx_seo_suite_row('Primary content type',$probe['type']?$probe['type']:'Unknown') ), 'details'=>$details, 'notice'=>'A reachable favicon helps browsers and may be used as a search-result visual when eligibility requirements are met.' ) );
    }

    if ( 'hreflang-checker' === $mode ) {
        $details = array();
        foreach ( $xpath->query( '//link[@hreflang][@href]' ) as $link ) {
            $details[] = array( 'Language/region'=>sanitize_text_field($link->getAttribute('hreflang')), 'Alternate URL'=>sanitize_text_field(ufx_speed_abs_url($url,$link->getAttribute('href'))) );
            if ( count( $details ) >= 100 ) break;
        }
        $xdefault = array_filter($details,function($row){return 'x-default'===strtolower($row['Language/region']);});
        wp_send_json_success( array( 'results'=>array( ufx_seo_suite_row('Hreflang declarations',count($details)), ufx_seo_suite_row('x-default declaration',$xdefault?'Found':'Not found'), ufx_seo_suite_row('Canonical URL',$canonical?$canonical:'Not found') ), 'details'=>$details, 'notice'=>'This page-level check does not crawl alternate pages to confirm reciprocal hreflang links.' ) );
    }

    if ( 'webpage-keyword-density' === $mode ) {
        if ( '' === trim( $keyword ) ) wp_send_json_error( array( 'message'=>'Enter a keyword or phrase.' ), 400 );
        $text = ufx_seo_suite_readable_text( $dom, $xpath ); $words = ufx_seo_suite_words( $text );
        $pattern = '/(?<![\p{L}\p{N}])' . preg_quote( trim($keyword), '/' ) . '(?![\p{L}\p{N}])/iu';
        $matches = preg_match_all( $pattern, $text ); $keyword_words = max(1,count(ufx_seo_suite_words($keyword)));
        $density = count($words) ? ( $matches * $keyword_words / count($words) ) * 100 : 0;
        wp_send_json_success( array( 'results'=>array( ufx_seo_suite_row('Keyword',$keyword), ufx_seo_suite_row('Readable words',count($words)), ufx_seo_suite_row('Phrase occurrences',$matches), ufx_seo_suite_row('Estimated density',number_format($density,2).'%') ), 'notice'=>'Keyword density is descriptive, not a ranking score. Write naturally and satisfy the search intent.' ) );
    }

    if ( 'http-cache-checker' === $mode ) {
        $fields = array('cache-control'=>'Cache-Control','age'=>'Age','etag'=>'ETag','expires'=>'Expires','last-modified'=>'Last-Modified','vary'=>'Vary','cdn-cache-status'=>'CDN-Cache-Status','cf-cache-status'=>'CF-Cache-Status');$results=array();
        foreach($fields as $key=>$label)$results[]=ufx_seo_suite_row($label,isset($headers[$key])?$headers[$key]:'Not advertised');
        wp_send_json_success(array('results'=>$results,'notice'=>'Cache headers describe this response only; logged-in users, regions and CDN edges may receive different behavior.'));
    }

    if ( 'cors-header-checker' === $mode ) {
        $fields=array('access-control-allow-origin'=>'Allow-Origin','access-control-allow-methods'=>'Allow-Methods','access-control-allow-headers'=>'Allow-Headers','access-control-allow-credentials'=>'Allow-Credentials','access-control-expose-headers'=>'Expose-Headers','vary'=>'Vary');$results=array();
        foreach($fields as $key=>$label)$results[]=ufx_seo_suite_row($label,isset($headers[$key])?$headers[$key]:'Not advertised');
        wp_send_json_success(array('results'=>$results,'notice'=>'A normal HTML page does not always need CORS. Interpret these headers according to the resource and application use case.'));
    }

    if ( 'indexability-checker' === $mode ) {
        $xrobots = isset($headers['x-robots-tag'])?$headers['x-robots-tag']:'';
        $blocked = $data['code']<200||$data['code']>=400||false!==stripos($robots,'noindex')||false!==stripos($xrobots,'noindex');
        $robots_data=ufx_seo_suite_fetch($origin.'/robots.txt',262144);$root_block=false;
        if(!is_wp_error($robots_data)&&200===$robots_data['code'])$root_block=(bool)preg_match('/user-agent\s*:\s*\*[^#]*?disallow\s*:\s*\/\s*(?:\r?\n|$)/is',$robots_data['body']);
        if($root_block)$blocked=true;
        wp_send_json_success(array('results'=>array(ufx_seo_suite_row('HTTP status',$data['code']),ufx_seo_suite_row('Meta robots',$robots?$robots:'Not declared'),ufx_seo_suite_row('X-Robots-Tag',$xrobots?$xrobots:'Not advertised'),ufx_seo_suite_row('Canonical URL',$canonical?$canonical:'Not found'),ufx_seo_suite_row('Root robots block',$root_block?'Detected':'Not detected'),ufx_seo_suite_row('Page assessment',$blocked?'Blocked / needs review':'No common blocker detected')),'notice'=>'Only a search engine and its URL inspection data can confirm final indexing. This check reports common technical blockers.'));
    }

    if ( 'image-alt-text-checker' === $mode ) {
        $details=array();$total=0;$missing=0;$empty=0;$useful=0;
        foreach($xpath->query('//img') as $image){$total++;$has=$image->hasAttribute('alt');$alt=trim($image->getAttribute('alt'));if(!$has)$missing++;elseif(''===$alt)$empty++;else$useful++;if((!$has||''===$alt)&&count($details)<100)$details[]=array('Issue'=>$has?'Empty alt':'Missing alt','Image'=>sanitize_text_field($image->getAttribute('src')?$image->getAttribute('src'):$image->getAttribute('data-src')));}
        wp_send_json_success(array('results'=>array(ufx_seo_suite_row('Images',$total),ufx_seo_suite_row('Useful alt text',$useful),ufx_seo_suite_row('Empty alt attributes',$empty),ufx_seo_suite_row('Missing alt attributes',$missing)),'details'=>$details,'notice'=>'Empty alt text can be correct for decorative images; meaningful images should have concise contextual alternatives.'));
    }

    if ( 'lazy-loading-checker' === $mode ) {
        $images=$xpath->query('//img');$iframes=$xpath->query('//iframe');$lazy_images=0;$lazy_iframes=0;$details=array();
        foreach($images as $image){if('lazy'===strtolower($image->getAttribute('loading')))$lazy_images++;elseif(count($details)<100)$details[]=array('Type'=>'Image without loading=lazy','Source'=>sanitize_text_field($image->getAttribute('src')));}
        foreach($iframes as $frame){if('lazy'===strtolower($frame->getAttribute('loading')))$lazy_iframes++;elseif(count($details)<100)$details[]=array('Type'=>'Iframe without loading=lazy','Source'=>sanitize_text_field($frame->getAttribute('src')));}
        wp_send_json_success(array('results'=>array(ufx_seo_suite_row('Images',$images->length),ufx_seo_suite_row('Lazy-loaded images',$lazy_images),ufx_seo_suite_row('Iframes',$iframes->length),ufx_seo_suite_row('Lazy-loaded iframes',$lazy_iframes)),'details'=>$details,'notice'=>'Do not lazy-load an above-the-fold hero/LCP image. This reports attributes, not rendered viewport position.'));
    }

    if ( 'heading-structure-checker' === $mode ) {
        $results=array();$details=array();$previous=0;$jumps=0;
        for($level=1;$level<=6;$level++)$results[]=ufx_seo_suite_row('H'.$level.' headings',$xpath->query('//h'.$level)->length);
        foreach($xpath->query('//h1|//h2|//h3|//h4|//h5|//h6') as $heading){$level=(int)substr(strtolower($heading->nodeName),1);if($previous&&$level>$previous+1)$jumps++;$previous=$level;if(count($details)<100)$details[]=array('Level'=>'H'.$level,'Text'=>alltools_text_slice(trim($heading->textContent),0,180));}
        $results[]=ufx_seo_suite_row('Level jumps detected',$jumps);wp_send_json_success(array('results'=>$results,'details'=>$details,'notice'=>'Use headings to describe content hierarchy. Visual size alone should be controlled with CSS.'));
    }

    if ( 'webpage-word-count' === $mode ) {
        $text=ufx_seo_suite_readable_text($dom,$xpath);$words=ufx_seo_suite_words($text);$paragraphs=$xpath->query('//p');$characters=function_exists('mb_strlen')?mb_strlen($text):strlen($text);
        wp_send_json_success(array('results'=>array(ufx_seo_suite_row('Page title',$title?$title:'Missing'),ufx_seo_suite_row('Readable words',count($words)),ufx_seo_suite_row('Characters',$characters),ufx_seo_suite_row('Paragraph elements',$paragraphs->length),ufx_seo_suite_row('Estimated reading time',max(1,(int)ceil(count($words)/220)).' min')),'notice'=>'Navigation and footer text can be included; scripts, styles, templates and SVG text are removed.'));
    }

    wp_send_json_error( array( 'message' => 'The requested check did not return a result.' ), 500 );
}
