<?php
/**
 * No-key website and SEO inspection tools.
 *
 * These tools report observable public-page facts. They do not invent ranking,
 * traffic, authority or backlink metrics.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** @return array<string,array<string,mixed>> */
function alltools_seo_suite_definitions() {
    $rows = array(
        array( 'ads-txt-checker', 'Ads.txt Checker & Validator', 'Check whether a root ads.txt file is reachable and review its authorized seller records.', 'shield', 'green', 'Check Ads.txt' ),
        array( 'live-robots-txt-tester', 'Live Robots.txt Tester', 'Fetch a website robots.txt file and review user-agent, allow, disallow and sitemap directives.', 'code', 'blue', 'Test Robots.txt' ),
        array( 'live-canonical-checker', 'Live Canonical URL Checker', 'Fetch a public page and inspect its live rel=canonical declaration.', 'link', 'purple', 'Check Canonical' ),
        array( 'meta-seo-analyzer', 'Meta Title & Description Analyzer', 'Review a live page title, meta description, robots directive and primary heading.', 'search', 'green', 'Analyze Meta Tags' ),
        array( 'open-graph-checker', 'Open Graph & Social Card Checker', 'Inspect live Open Graph and Twitter/X card metadata used for social previews.', 'image', 'blue', 'Check Social Tags' ),
        array( 'link-extractor', 'Internal & External Link Extractor', 'Extract public page links and separate internal, external and nofollow destinations.', 'link', 'purple', 'Extract Links' ),
        array( 'broken-image-checker', 'Broken Image Checker', 'Find page images and test a bounded sample for missing or failed image responses.', 'image', 'red', 'Check Images' ),
        array( 'compression-checker', 'Gzip & Brotli Compression Checker', 'Inspect live response headers for content compression and variation signals.', 'gauge', 'green', 'Check Compression' ),
        array( 'ipv6-checker', 'Website IPv6 Support Checker', 'Check whether a public hostname publishes one or more IPv6 AAAA records.', 'globe', 'blue', 'Check IPv6' ),
        array( 'favicon-checker', 'Website Favicon Checker', 'Find declared favicon files and verify whether the primary icon is reachable.', 'image', 'orange', 'Check Favicon' ),
        array( 'hreflang-checker', 'Live Hreflang Checker', 'Inspect alternate-language link tags and review language or region values.', 'globe', 'purple', 'Check Hreflang' ),
        array( 'webpage-keyword-density', 'Webpage Keyword Density Checker', 'Calculate a supplied phrase frequency against readable text fetched from a public page.', 'search', 'orange', 'Check Keyword' ),
        array( 'http-cache-checker', 'HTTP Cache Header Checker', 'Review Cache-Control, ETag, Expires, Age, Last-Modified and Vary headers.', 'gauge', 'blue', 'Check Cache' ),
        array( 'cors-header-checker', 'CORS Headers Checker', 'Inspect public response headers for cross-origin resource sharing declarations.', 'shield', 'green', 'Check CORS' ),
        array( 'sitemap-url-analyzer', 'XML Sitemap URL Analyzer', 'Fetch an XML sitemap or sitemap index and count its declared page locations.', 'code', 'purple', 'Analyze Sitemap' ),
        array( 'indexability-checker', 'Page Indexability Checker', 'Review HTTP status, robots directives, canonical URL and common page-level indexing blockers.', 'search', 'green', 'Check Indexability' ),
        array( 'image-alt-text-checker', 'Image Alt Text Checker', 'Find images with useful, empty or missing alt attributes on a public page.', 'image', 'blue', 'Check Alt Text' ),
        array( 'lazy-loading-checker', 'Lazy Loading Checker', 'Check image and iframe loading attributes without running a synthetic lab test.', 'gauge', 'orange', 'Check Lazy Loading' ),
        array( 'heading-structure-checker', 'Heading Structure Checker', 'Review live H1–H6 counts and heading order for a public webpage.', 'word', 'purple', 'Check Headings' ),
        array( 'webpage-word-count', 'Webpage Word Count Checker', 'Measure readable page words, paragraphs, characters and estimated reading time.', 'word', 'green', 'Count Page Words' ),
    );

    $out = array();
    foreach ( $rows as $row ) {
        $out[ $row[0] ] = array(
            'title'    => $row[1],
            'short'    => $row[1],
            'desc'     => $row[2],
            'long'     => $row[2] . ' The check uses protected, rate-limited server requests and requires no paid API key.',
            'icon'     => $row[3],
            'color'    => $row[4],
            'category' => 'website-tools',
            'badge'    => 'No API',
            'featured' => in_array( $row[0], array( 'ads-txt-checker', 'meta-seo-analyzer', 'indexability-checker', 'broken-image-checker' ), true ),
            'cta'      => $row[5],
            'illo'     => 'code',
        );
    }
    return $out;
}

add_filter( 'alltools_registered_tools', 'alltools_register_seo_suite_tools', 30 );
function alltools_register_seo_suite_tools( $tools ) {
    return array_merge( $tools, alltools_seo_suite_definitions() );
}

add_filter( 'alltools_render_tool', 'alltools_render_seo_suite_tool', 30, 2 );
function alltools_render_seo_suite_tool( $rendered, $slug ) {
    $tools = alltools_seo_suite_definitions();
    if ( ! isset( $tools[ $slug ] ) ) return $rendered;

    $domain_only = 'ipv6-checker' === $slug;
    $sitemap     = 'sitemap-url-analyzer' === $slug;
    $keyword     = 'webpage-keyword-density' === $slug;
    $label       = $domain_only ? __( 'Public hostname', 'alltools' ) : ( $sitemap ? __( 'Sitemap URL or website domain', 'alltools' ) : __( 'Public webpage or domain', 'alltools' ) );
    $placeholder = $domain_only ? 'example.com' : ( $sitemap ? 'https://example.com/sitemap_index.xml' : 'https://example.com/' );
    ?>
    <div class="at-tool-grid at-tool-grid-2 ufx-growth-tool ufx-seo-suite-tool" data-ufx-seo="<?php echo esc_attr( $slug ); ?>">
        <div class="at-card">
            <h3><?php echo esc_html( $tools[ $slug ]['title'] ); ?></h3>
            <p class="at-help"><?php echo esc_html( $tools[ $slug ]['desc'] ); ?></p>
            <div class="at-field">
                <label for="<?php echo esc_attr( $slug ); ?>-url"><?php echo esc_html( $label ); ?></label>
                <input type="text" id="<?php echo esc_attr( $slug ); ?>-url" class="at-input" autocomplete="off" inputmode="url" placeholder="<?php echo esc_attr( $placeholder ); ?>">
            </div>
            <?php if ( $keyword ) : ?>
                <div class="at-field">
                    <label for="<?php echo esc_attr( $slug ); ?>-keyword"><?php esc_html_e( 'Keyword or phrase', 'alltools' ); ?></label>
                    <input type="text" id="<?php echo esc_attr( $slug ); ?>-keyword" class="at-input" maxlength="120" placeholder="website speed test">
                </div>
            <?php endif; ?>
            <button type="button" class="at-btn at-btn-primary ufx-seo-suite-run"><?php echo esc_html( $tools[ $slug ]['cta'] ); ?></button>
        </div>
        <div class="at-card">
            <h3><?php esc_html_e( 'Live Results', 'alltools' ); ?></h3>
            <div class="ufx-growth-status at-help" role="status"><?php esc_html_e( 'Enter a public target to begin.', 'alltools' ); ?></div>
            <div class="ufx-growth-output ufx-seo-suite-output"></div>
        </div>
    </div>
    <?php
    return true;
}
