<?php
/**
 * Content-quality helpers for tool pages, blog review and public archives.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Return a repeatable variation without randomising cached page copy. */
function alltools_quality_pick( $slug, $key, $choices ) {
    if ( empty( $choices ) ) return '';
    $hash = (int) sprintf( '%u', crc32( (string) $slug . '|' . (string) $key ) );
    return $choices[ $hash % count( $choices ) ];
}

/** Convert registry text into a complete plain-text sentence. */
function alltools_quality_sentence( $text, $fallback = '' ) {
    $text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $text ) ) );
    if ( '' === $text ) $text = $fallback;
    return rtrim( $text, " \t\n\r\0\x0B.!?" ) . '.';
}

/** Identify the main operation from the tool name and slug. */
function alltools_tool_operation( $slug, $tool ) {
    $haystack = strtolower( str_replace( '-', ' ', (string) $slug ) . ' ' . ( isset( $tool['title'] ) ? $tool['title'] : '' ) );
    $operations = array(
        'compare'    => array( 'compare', 'comparison', 'diff' ),
        'compress'   => array( 'compress', 'compression' ),
        'convert'    => array( 'convert', 'converter', 'conversion', ' to ' ),
        'calculate'  => array( 'calculate', 'calculator', 'estimator', 'estimate' ),
        'validate'   => array( 'validate', 'validator', 'lint' ),
        'check'      => array( 'check', 'checker', 'test', 'audit', 'analyzer', 'analyse', 'lookup', 'status', 'whois', 'rdap' ),
        'generate'   => array( 'generate', 'generator', 'maker', 'builder', 'creator', 'create' ),
        'format'     => array( 'format', 'formatter', 'beautifier', 'prettifier' ),
        'minify'     => array( 'minify', 'minifier' ),
        'encode'     => array( 'encode', 'encoder' ),
        'decode'     => array( 'decode', 'decoder' ),
        'resize'     => array( 'resize', 'resizer', 'crop', 'cropper', 'rotate', 'flipper' ),
        'merge'      => array( 'merge', 'join', 'combine' ),
        'split'      => array( 'split', 'separator' ),
        'extract'    => array( 'extract', 'extractor', 'remove', 'cleaner', 'strip' ),
        'record'     => array( 'record', 'recorder', 'capture' ),
        'edit'       => array( 'edit', 'editor', 'fill', 'sign', 'annotate', 'watermark' ),
        'count'      => array( 'count', 'counter' ),
        'preview'    => array( 'preview', 'simulator' ),
    );

    foreach ( $operations as $operation => $needles ) {
        foreach ( $needles as $needle ) {
            if ( false !== strpos( $haystack, $needle ) ) return $operation;
        }
    }
    return 'process';
}

/**
 * Build page-specific context from the registry, category and operation.
 * Copy remains factual and avoids invented accuracy, storage or provider claims.
 */
function alltools_tool_quality_profile( $slug, $tool ) {
    $name      = ! empty( $tool['title'] ) ? wp_strip_all_tags( $tool['title'] ) : 'Online Tool';
    $summary   = alltools_quality_sentence( isset( $tool['desc'] ) ? $tool['desc'] : '', 'Complete the task shown on this page' );
    $category  = ! empty( $tool['category'] ) ? $tool['category'] : 'other-tools';
    $badge     = ! empty( $tool['badge'] ) ? strtoupper( (string) $tool['badge'] ) : '';
    $operation = alltools_tool_operation( $slug, $tool );

    $category_profiles = array(
        'calculators' => array(
            'input'   => 'the numbers, dates, units, or assumptions requested in the calculator',
            'output'  => 'a calculated result based only on the values and options you provide',
            'example' => 'compare a real-world scenario with a second set of values before making a plan',
            'uses'    => array( 'checking everyday figures', 'comparing two possible scenarios', 'recalculating after one value changes' ),
            'check'   => 'Check units, dates, rounding and every entered value. Financial, medical or legal decisions should be confirmed with a qualified professional.',
        ),
        'image-tools' => array(
            'input'   => 'a supported image and any size, format, quality, colour, or editing choices',
            'output'  => 'a new image or visual result that reflects the selected settings',
            'example' => 'prepare an image for a website, email, document, profile, or social post while keeping the original file',
            'uses'    => array( 'preparing web-ready visuals', 'creating a compatible image format', 'making a copy for a specific size or layout' ),
            'check'   => 'Inspect dimensions, transparency, colour and visible quality before replacing the source image. Keep the original as a backup.',
        ),
        'pdf-tools' => array(
            'input'   => 'one or more supported documents plus page, order, quality, or export options',
            'output'  => 'a new document assembled from the files and settings you choose',
            'example' => 'prepare a working copy for sharing, filing or review without changing the original document',
            'uses'    => array( 'organising a document workflow', 'preparing a shareable copy', 'extracting or changing only the pages you need' ),
            'check'   => 'Open the finished file and inspect page order, orientation, text, signatures and links. Keep an untouched copy of important documents.',
        ),
        'video-tools' => array(
            'input'   => 'a supported video plus the timing, size, format, speed, or editing controls shown',
            'output'  => 'a processed video created from the selected portion and export settings',
            'example' => 'make a shorter or more compatible copy for a presentation, website or social channel',
            'uses'    => array( 'preparing a shareable clip', 'adjusting playback or framing', 'creating a compatible export copy' ),
            'check'   => 'Play the complete export and check audio sync, framing, duration and quality. Large media jobs depend on browser memory and device performance.',
        ),
        'audio-tools' => array(
            'input'   => 'a supported audio source plus timing, format, level, speed, or recording choices',
            'output'  => 'a processed audio file or analysis created from your selected settings',
            'example' => 'prepare a listening or sharing copy for a recording, presentation, website or archive',
            'uses'    => array( 'preparing a compatible audio copy', 'editing a recording for a specific use', 'checking or adjusting an audio property' ),
            'check'   => 'Listen to the full result with headphones before publishing it. Check volume, clipping, timing and the selected output format.',
        ),
        'text-tools' => array(
            'input'   => 'the text you want to inspect or change, together with any matching or formatting options',
            'output'  => 'revised text or measurements derived from the exact text entered',
            'example' => 'clean a draft, check a length limit, or prepare consistent text before pasting it into another system',
            'uses'    => array( 'cleaning copied text', 'checking structure or length', 'preparing a consistent final draft' ),
            'check'   => 'Read the complete result before publishing it. Names, quotations, deliberate spacing and specialist terminology may need manual correction.',
        ),
        'developer-tools' => array(
            'input'   => 'the code, structured data, expression, token, or sample value requested by the interface',
            'output'  => 'a transformed, generated, or diagnostic result for the submitted technical input',
            'example' => 'inspect a small representative sample before applying the same change in a production workflow',
            'uses'    => array( 'checking data during development', 'preparing a value for testing', 'spotting formatting or validation problems before release' ),
            'check'   => 'Test the result in a non-production environment. Never paste passwords, private keys, live access tokens or confidential production data.',
        ),
        'website-tools' => array(
            'input'   => 'the public URL, domain, IP address, record type, or audit option requested by the checker',
            'output'  => 'a point-in-time observation from the public target and the checks available on this page',
            'example' => 'run a fresh diagnostic after a hosting, DNS, certificate, redirect, content or performance change',
            'uses'    => array( 'investigating a public-site problem', 'verifying a recent configuration change', 'collecting a diagnostic snapshot before troubleshooting' ),
            'check'   => 'Results can vary by location, cache, DNS propagation and temporary server conditions. Confirm important findings with the relevant host or service.',
        ),
        'business-tools' => array(
            'input'   => 'the business details, line items, dates, rates, or document options requested',
            'output'  => 'a working calculation, record or document created from the information supplied',
            'example' => 'prepare a draft for internal review before it is approved, issued, filed or sent to another party',
            'uses'    => array( 'preparing a consistent business draft', 'checking totals and line items', 'creating a record for review or export' ),
            'check'   => 'Verify names, dates, totals, tax treatment, currency and local requirements. The result is a draft and is not accounting or legal advice.',
        ),
        'marketing-tools' => array(
            'input'   => 'the URL, campaign details, channel settings, content, or preview data requested',
            'output'  => 'a marketing asset, tagged value, preview or check based on the supplied information',
            'example' => 'prepare a campaign component and review it before publishing or sharing it with a team',
            'uses'    => array( 'preparing campaign assets', 'checking links and presentation', 'keeping channel details consistent' ),
            'check'   => 'Preview the result on the real destination and check branding, links, spelling, tracking parameters and platform-specific requirements.',
        ),
        'other-tools' => array(
            'input'   => 'the source text, file, values, or options requested by the interface',
            'output'  => 'a result produced from the exact input and settings you choose',
            'example' => 'complete a repeatable browser task and review the result before using it elsewhere',
            'uses'    => array( 'handling a one-off task', 'preparing a reusable result', 'checking an output before the next step' ),
            'check'   => 'Review the entire result and retain the original input when it matters. Browser, device and third-party limits can affect very large tasks.',
        ),
    );

    $base = isset( $category_profiles[ $category ] ) ? $category_profiles[ $category ] : $category_profiles['other-tools'];

    $operation_notes = array(
        'convert'   => 'Confirm both the source and destination formats; conversion can change unsupported formatting, metadata or quality.',
        'compress'  => 'Compare file size with visible or audible quality and keep the uncompressed original.',
        'calculate' => 'A result is only as reliable as the entered values, selected units and underlying assumptions.',
        'validate'  => 'A clean automated check does not replace testing in the system where the data or code will be used.',
        'check'     => 'Treat the output as a current diagnostic snapshot rather than a permanent guarantee.',
        'generate'  => 'Generated material is a draft; check accuracy, ownership, formatting and suitability before use.',
        'decode'    => 'Decoded content is not automatically safe or authentic. Do not execute or trust unknown data.',
        'encode'    => 'Encoding changes representation, not confidentiality. It is not a substitute for encryption.',
        'merge'     => 'Check source order and inspect every combined page, track or item in the final output.',
        'split'     => 'Verify the selected ranges and make sure every required section appears in the exported files.',
        'extract'   => 'Compare the extracted or cleaned result with the source so meaningful content is not removed.',
        'resize'    => 'Upscaling, cropping and repeated export can affect detail, framing, transparency or quality.',
        'record'    => 'Check microphone, camera, tab and system-audio permissions before starting a real recording.',
        'format'    => 'Formatting improves readability but does not prove that the underlying data or code is valid.',
        'minify'    => 'Keep a readable source copy and test the minified output before deployment.',
        'edit'      => 'Review every edit against the untouched source before sharing or replacing the original.',
        'compare'   => 'A comparison highlights differences but still requires human review of their meaning and impact.',
        'count'     => 'Counts depend on how the tool treats whitespace, punctuation and structural boundaries.',
        'preview'   => 'A preview is an approximation; verify the final result in the target app, browser or platform.',
        'process'   => 'Check the result against the original input before using it in a live or important workflow.',
    );

    if ( in_array( $badge, array( 'AI', 'API' ), true ) ) {
        $privacy = 'This feature sends only the input required for the request to the provider configured by the site owner. Do not submit confidential, regulated or third-party personal data, and review generated output for mistakes before use.';
    } elseif ( 'website-tools' === $category ) {
        $privacy = 'The checker uses the public target you enter to make a diagnostic request. Do not place credentials or private access tokens in a URL.';
    } elseif ( in_array( $category, array( 'image-tools', 'pdf-tools', 'video-tools', 'audio-tools', 'text-tools', 'developer-tools' ), true ) ) {
        $privacy = 'Processing stays in the browser where the individual feature supports it. Live public checks use a protected, rate-limited site endpoint; avoid unnecessary sensitive data.';
    } else {
        $privacy = 'Enter only the information required for the result. Browser-based work stays on the device where supported, while public checks use protected, rate-limited site endpoints.';
    }

    $intro_openers = array(
        $name . ' is intended for a specific, repeatable task rather than a general-purpose editor.',
        'Use ' . $name . ' when you need a focused result without installing a separate desktop application.',
        $name . ' brings the required input, options and result into one focused workflow.',
        'This ' . $name . ' page is organised around the task described in the tool interface.',
    );
    $intro = alltools_quality_pick( $slug, 'intro', $intro_openers ) . ' ' . $summary . ' The result changes when the source data or selected options change, so it is easy to run a second comparison.';

    $detail_openers = array(
        'A practical way to use it is to ',
        'For a typical workflow, use it to ',
        'One useful scenario is to ',
        'It can help when you need to ',
    );
    $example = alltools_quality_pick( $slug, 'example', $detail_openers ) . $base['example'] . '. Start with a representative input, inspect the first result, then adjust one setting at a time if you need a different outcome.';

    $uses = array();
    foreach ( $base['uses'] as $use ) {
        $uses[] = ucfirst( $use ) . ' with ' . $name . '.';
    }

    return array(
        'name'           => $name,
        'category'       => $category,
        'operation'      => $operation,
        'summary'        => $summary,
        'intro'          => $intro,
        'input'          => ucfirst( $base['input'] ) . '.',
        'output'         => ucfirst( $base['output'] ) . '.',
        'example'        => $example,
        'use_cases'      => $uses,
        'checks'         => array( $base['check'], $operation_notes[ $operation ] ),
        'privacy'        => $privacy,
    );
}

/**
 * Blog compatibility helpers.
 *
 * v3.5.2 deliberately does not score, flag, hide or noindex individual posts.
 * Content decisions remain with the site editor and existing post data stays
 * untouched. These functions remain available for template compatibility.
 */
function alltools_post_template_score( $post_id ) {
    return 0;
}

function alltools_post_is_reviewed( $post_id ) {
    return false;
}

function alltools_post_needs_review( $post_id ) {
    return false;
}

/** Remove only metadata created by the retired automatic review system. */
add_action( 'admin_init', 'alltools_remove_legacy_content_quality_flags' );
function alltools_remove_legacy_content_quality_flags() {
    if ( get_option( 'alltools_content_quality_cleanup_v2' ) || ! current_user_can( 'manage_options' ) ) return;

    delete_post_meta_by_key( '_alltools_content_quality' );
    delete_post_meta_by_key( '_alltools_content_reviewed' );
    delete_option( 'alltools_content_quality_scan_v1' );
    update_option( 'alltools_content_quality_cleanup_v2', gmdate( 'c' ), false );
}

/** Empty filter retained so existing templates include every published post. */
function alltools_public_quality_meta_query() {
    return array();
}

/** Check whether a category has enough published articles to index. */
function alltools_public_category_has_enough_posts( $term_id, $minimum = 5 ) {
    static $cache = array();
    $term_id = (int) $term_id;
    $minimum = max( 1, (int) $minimum );
    $key     = $term_id . ':' . $minimum;
    if ( isset( $cache[ $key ] ) ) return $cache[ $key ];
    $query = new WP_Query( array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $minimum,
        'fields'              => 'ids',
        'no_found_rows'       => true,
        'ignore_sticky_posts' => true,
        'cat'                 => $term_id,
    ) );
    $cache[ $key ] = count( $query->posts ) >= $minimum;
    return $cache[ $key ];
}

/** Check whether the public journal contains enough published posts. */
function alltools_public_blog_has_enough_posts( $minimum = 3 ) {
    static $cache = array();
    $minimum = max( 1, (int) $minimum );
    if ( isset( $cache[ $minimum ] ) ) return $cache[ $minimum ];
    $query = new WP_Query( array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $minimum,
        'fields'              => 'ids',
        'no_found_rows'       => true,
        'ignore_sticky_posts' => true,
    ) );
    $cache[ $minimum ] = count( $query->posts ) >= $minimum;
    return $cache[ $minimum ];
}

/** Locate static pages using the bundled Blog page template. */
function alltools_blog_template_page_ids() {
    return get_posts( array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'template-blog.php',
    ) );
}

/** Compatibility helper: automatic post flagging is retired. */
function alltools_posts_needing_review_count() {
    return 0;
}
