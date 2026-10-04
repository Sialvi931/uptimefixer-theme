<?php
/**
 * Complete per-tool Yoast SEO defaults, social metadata and Schema support.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function alltools_yoast_is_active() {
    return defined( 'WPSEO_VERSION' ) || class_exists( 'WPSEO_Options' );
}

/** Normalize and shorten SEO text while always ending on a complete sentence. */
function alltools_seo_trim_text( $text, $limit = 155 ) {
    $text = preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $text ) );
    $text = trim( (string) $text );
    $length = function_exists( 'mb_strlen' ) ? mb_strlen( $text ) : strlen( $text );
    if ( $length <= $limit ) return $text;

    $sentences = preg_split( '/(?<=[.!?])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY );
    $complete = '';
    foreach ( $sentences as $sentence ) {
        $candidate = trim( $complete . ' ' . trim( $sentence ) );
        $candidate_length = function_exists( 'mb_strlen' ) ? mb_strlen( $candidate ) : strlen( $candidate );
        if ( $candidate_length > $limit ) break;
        $complete = $candidate;
    }
    if ( $complete ) return $complete;

    $cut = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, $limit - 1 ) : substr( $text, 0, $limit - 1 );
    $cut = preg_replace( '/\s+\S*$/u', '', $cut );
    return rtrim( (string) $cut, " \t\n\r\0\x0B,.;:!-–—" ) . '.';
}

/** The exact description used by versions 2.x and 3.0, for safe migration. */
function alltools_legacy_tool_meta_description( $tool ) {
    $description = isset( $tool['long'] ) ? $tool['long'] : ( isset( $tool['desc'] ) ? $tool['desc'] : '' );
    return wp_html_excerpt( wp_strip_all_tags( $description ), 155, '…' );
}

/** Build a concise, unique description that contains the exact focus keyphrase. */
function alltools_tool_meta_description( $tool, $slug = '' ) {
    $name = ! empty( $tool['title'] ) ? $tool['title'] : 'Online Tool';
    $summary = ! empty( $tool['desc'] ) ? $tool['desc'] : ( ! empty( $tool['long'] ) ? $tool['long'] : 'Complete the task online.' );
    $summary = rtrim( trim( wp_strip_all_tags( $summary ) ), " \t\n\r\0\x0B.!?" ) . '.';
    $badge = ! empty( $tool['badge'] ) ? strtoupper( (string) $tool['badge'] ) : '';
    $category = ! empty( $tool['category'] ) ? $tool['category'] : 'other-tools';

    if ( in_array( $badge, array( 'AI', 'API' ), true ) ) {
        $benefit = ' Uses a securely configured provider; usage limits apply.';
    } elseif ( 'website-tools' === $category ) {
        $benefit = ' Run a fresh check and review clear technical results.';
    } elseif ( in_array( $category, array( 'pdf-tools', 'image-tools', 'video-tools', 'audio-tools' ), true ) ) {
        $benefit = ' Use clear controls and download the result—no signup required.';
    } elseif ( 'calculators' === $category ) {
        $benefit = ' Get an instant, easy-to-read result—no signup required.';
    } else {
        $benefit = ' Fast, easy to use, and no signup required.';
    }

    $text = $name . ': ' . $summary . $benefit;
    $length = function_exists( 'mb_strlen' ) ? mb_strlen( $text ) : strlen( $text );
    if ( $length < 120 ) {
        if ( in_array( $badge, array( 'AI', 'API' ), true ) ) {
            $text .= ' Review the generated or third-party result before use.';
        } elseif ( 'website-tools' === $category ) {
            $text .= ' Use the findings to guide troubleshooting and improvements.';
        } elseif ( in_array( $category, array( 'pdf-tools', 'image-tools', 'video-tools', 'audio-tools' ), true ) ) {
            $text .= ' Keep the original file and verify the finished download.';
        } elseif ( 'calculators' === $category ) {
            $text .= ' Recalculate anytime with different values or options.';
        } else {
            $text .= ' Review, copy, export, or download the output when available.';
        }
    }

    return alltools_seo_trim_text( $text, 155 );
}

/** Build a search-friendly title while leaving room for the site name. */
function alltools_tool_seo_title( $tool, $slug = '' ) {
    $name = ! empty( $tool['title'] ) ? trim( wp_strip_all_tags( $tool['title'] ) ) : 'Online Tool';
    $base = 'Free ' . $name . ' Online';
    $length = function_exists( 'mb_strlen' ) ? mb_strlen( $base ) : strlen( $base );
    if ( $length > 48 ) $base = $name . ' Online';
    return $base . ' | %%sitename%%';
}

/** Generate natural Yoast Premium synonym variants without keyword stuffing. */
function alltools_tool_keyphrase_synonyms( $tool ) {
    $name = ! empty( $tool['title'] ) ? trim( wp_strip_all_tags( $tool['title'] ) ) : 'Online Tool';
    $plain = trim( preg_replace( '/\b(?:free|online)\b/i', '', $name ) );
    $synonyms = array( $plain . ' online', 'free ' . $plain );
    $replacements = array(
        '/\bConverter\b/i'  => 'Conversion Tool',
        '/\bCalculator\b/i' => 'Calculation Tool',
        '/\bChecker\b/i'    => 'Check Tool',
        '/\bGenerator\b/i'  => 'Maker',
        '/\bLookup\b/i'     => 'Search Tool',
    );
    foreach ( $replacements as $pattern => $replacement ) {
        if ( preg_match( $pattern, $plain ) ) {
            $synonyms[] = preg_replace( $pattern, $replacement, $plain );
            break;
        }
    }
    $synonyms = array_values( array_unique( array_filter( array_map( 'trim', $synonyms ) ) ) );
    return array_slice( $synonyms, 0, 3 );
}

/** One reusable SEO profile is used by Yoast fields, fallbacks and Schema. */
function alltools_tool_seo_profile( $slug, $tool ) {
    $name = ! empty( $tool['title'] ) ? trim( wp_strip_all_tags( $tool['title'] ) ) : 'Online Tool';
    $description = alltools_tool_meta_description( $tool, $slug );
    $synonyms = alltools_tool_keyphrase_synonyms( $tool );
    return array(
        'title'               => alltools_tool_seo_title( $tool, $slug ),
        'description'         => $description,
        'focus_keyphrase'     => $name,
        'synonyms'            => wp_json_encode( $synonyms ),
        'breadcrumb_title'    => $name,
        'opengraph_title'     => alltools_tool_seo_title( $tool, $slug ),
        'opengraph_desc'      => $description,
        'twitter_title'       => alltools_tool_seo_title( $tool, $slug ),
        'twitter_description' => $description,
        'schema_page_type'    => 'WebPage',
    );
}

/** Tool pages that already provide hand-written FAQ copy in single-alltool.php. */
function alltools_tools_with_custom_faqs() {
    return array(
        'age-calculator', 'percentage-calculator', 'emi-calculator', 'image-converter',
        'image-resizer', 'favicon-generator', 'word-counter', 'character-counter',
        'case-converter', 'remove-duplicate-lines', 'remove-extra-spaces', 'qr-code-generator',
        'website-uptime-checker', 'website-speed-test', 'http-status-checker', 'ssl-checker',
        'dns-lookup', 'redirect-checker', 'check-friends-age', 'share-age-result',
    );
}

/** Helpful category-aware instructions for every tool without custom steps. */
function alltools_tool_default_steps( $slug, $tool ) {
    $profile   = alltools_tool_quality_profile( $slug, $tool );
    $name      = $profile['name'];
    $operation = $profile['operation'];
    $media     = in_array( $profile['category'], array( 'pdf-tools', 'image-tools', 'video-tools', 'audio-tools' ), true );
    $run_titles = array(
        'calculate' => 'Calculate the Result', 'convert' => 'Convert the Source', 'compress' => 'Compress a Copy',
        'check' => 'Run a Fresh Check', 'validate' => 'Run the Validation', 'generate' => 'Generate a Draft',
        'format' => 'Format the Input', 'minify' => 'Create the Minified Copy', 'encode' => 'Encode the Value',
        'decode' => 'Decode the Value', 'resize' => 'Apply the New Size', 'merge' => 'Combine the Items',
        'split' => 'Create the Sections', 'extract' => 'Extract the Selection', 'record' => 'Start the Recording',
        'edit' => 'Apply the Edit', 'compare' => 'Compare the Sources', 'count' => 'Measure the Input',
        'preview' => 'Build the Preview', 'process' => 'Run ' . $name,
    );
    $run_title = isset( $run_titles[ $operation ] ) ? $run_titles[ $operation ] : 'Run ' . $name;
    $input_icon = $media ? 'upload' : ( 'website-tools' === $profile['category'] ? 'link' : 'word' );
    $result_icon = $media ? 'download' : 'check';

    return array(
        array( $input_icon, 'Add the Required Input', $profile['input'] ),
        array( 'grid', 'Review ' . $name . ' Options', 'Choose only the settings that apply to the result you need; leave optional controls unchanged when you are unsure.' ),
        array( 'zap', $run_title, 'Start the task and wait for the interface to return its result or a clear setup message.' ),
        array( $result_icon, 'Verify Before You Use It', $profile['checks'][0] ),
    );
}

/** Helpful visible FAQ content for tools that did not previously have an FAQ. */
function alltools_tool_default_faqs( $slug, $tool ) {
    $profile = alltools_tool_quality_profile( $slug, $tool );
    $name    = $profile['name'];
    $badge   = ! empty( $tool['badge'] ) ? strtoupper( (string) $tool['badge'] ) : '';
    $free_answer = in_array( $badge, array( 'AI', 'API' ), true )
        ? 'The page does not require a visitor account. Requests that use a configured provider can still be subject to provider availability and the site owner\'s usage limits.'
        : 'The page can be used without creating a visitor account. Your browser or device may limit unusually large files or demanding media jobs.';

    return array(
        array( 'What should I enter in ' . $name . '?', $profile['input'] . ' Start with a representative input and use only the options needed for your intended result.' ),
        array( 'What result should I expect from ' . $name . '?', $profile['output'] . ' ' . $profile['checks'][1] ),
        array( 'How should I check the result?', $profile['checks'][0] ),
        array( 'How does ' . $name . ' handle my input?', $profile['privacy'] ),
        array( 'Can I use ' . $name . ' without an account?', $free_answer ),
    );
}

/** Extra on-page copy gives every generated tool page useful, indexable context. */
function alltools_tool_seo_copy( $slug, $tool ) {
    $profile = alltools_tool_quality_profile( $slug, $tool );
    return array( $profile['intro'], $profile['example'], $profile['privacy'] );
}

/**
 * Safely write an automatically generated Yoast field.
 *
 * Manual editor values are never overwritten. A field is eligible only when it
 * is empty, still contains one of our known legacy defaults, or still matches
 * the value this theme recorded as automatic on the previous sync.
 */
function alltools_sync_yoast_field( $post_id, $meta_key, $new_value, $legacy_values = array(), &$auto_map = array() ) {
    $post_id   = (int) $post_id;
    $new_value = trim( (string) $new_value );
    if ( ! $post_id || '' === $new_value ) return false;

    $current = trim( (string) get_post_meta( $post_id, $meta_key, true ) );
    $tracked = isset( $auto_map[ $meta_key ] ) ? trim( (string) $auto_map[ $meta_key ] ) : '';
    $legacy  = array_values( array_filter( array_map( 'strval', (array) $legacy_values ) ) );

    $automatic = '' === $current
        || ( '' !== $tracked && hash_equals( $tracked, $current ) )
        || in_array( $current, $legacy, true );

    if ( ! $automatic ) return false;

    if ( $current !== $new_value ) {
        update_post_meta( $post_id, $meta_key, $new_value );
    }
    $auto_map[ $meta_key ] = $new_value;
    return true;
}

/** Fill empty/previously automatic Yoast fields for one tool page. */
function alltools_sync_yoast_meta_for_post( $post_id ) {
    $post_id = (int) $post_id;
    if ( ! $post_id || 'alltool' !== get_post_type( $post_id ) ) return false;

    $slug = sanitize_key( (string) get_post_meta( $post_id, '_alltool_slug', true ) );
    $tool = $slug ? alltools_get_tool( $slug ) : false;
    if ( ! $tool ) return false;

    $profile  = alltools_tool_seo_profile( $slug, $tool );
    $auto_map = get_post_meta( $post_id, '_alltools_auto_yoast_meta', true );
    $auto_map = is_array( $auto_map ) ? $auto_map : array();
    $changed  = false;

    $legacy_description = alltools_legacy_tool_meta_description( $tool );
    $changed = alltools_sync_yoast_field( $post_id, '_yoast_wpseo_title', $profile['title'], array(), $auto_map ) || $changed;
    $changed = alltools_sync_yoast_field( $post_id, '_yoast_wpseo_metadesc', $profile['description'], array( $legacy_description ), $auto_map ) || $changed;
    $changed = alltools_sync_yoast_field( $post_id, '_yoast_wpseo_focuskw', $profile['focus_keyphrase'], array(), $auto_map ) || $changed;
    $changed = alltools_sync_yoast_field( $post_id, '_yoast_wpseo_bctitle', $profile['breadcrumb_title'], array(), $auto_map ) || $changed;

    update_post_meta( $post_id, '_alltools_auto_yoast_meta', $auto_map );
    return $changed;
}

/**
 * SEO profile for normal site pages. Known utility pages receive hand-written
 * metadata; any other page receives a conservative title plus a description
 * derived from its excerpt/content without changing the page itself.
 */
function alltools_page_seo_profile( $post_id ) {
    $post_id = (int) $post_id;
    $post = $post_id ? get_post( $post_id ) : null;
    if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) return false;

    $slug     = sanitize_key( $post->post_name );
    $title    = trim( wp_strip_all_tags( get_the_title( $post_id ) ) );
    $front_id = (int) get_option( 'page_on_front' );

    $known = array(
        'about' => array(
            'About UptimeFixer | Free Online Tools',
            'Learn about UptimeFixer, how its free online tools work, the privacy approach behind browser-based processing, and how results should be verified.',
        ),
        'contact' => array(
            'Contact UptimeFixer | Help & Feedback',
            'Contact UptimeFixer with questions, feedback or tool issues. Share the page or tool you used and enough detail to help us investigate clearly.',
        ),
        'blog' => array(
            'Free Online Tool Guides & How-To Articles | UptimeFixer',
            'Read practical UptimeFixer guides for images, PDFs, text, website checks and everyday online tasks, with clear steps and useful verification tips.',
        ),
        'privacy-policy' => array(
            'Privacy Policy | UptimeFixer',
            'Read the UptimeFixer privacy policy to understand how site usage, browser-based tools, public website checks and submitted information are handled.',
        ),
        'privacy' => array(
            'Privacy Policy | UptimeFixer',
            'Read the UptimeFixer privacy policy to understand how site usage, browser-based tools, public website checks and submitted information are handled.',
        ),
        'terms' => array(
            'Terms of Use | UptimeFixer',
            'Read the UptimeFixer terms of use covering access to free online tools, acceptable use, limitations, third-party services and user responsibilities.',
        ),
        'terms-of-use' => array(
            'Terms of Use | UptimeFixer',
            'Read the UptimeFixer terms of use covering access to free online tools, acceptable use, limitations, third-party services and user responsibilities.',
        ),
        'terms-and-conditions' => array(
            'Terms of Use | UptimeFixer',
            'Read the UptimeFixer terms of use covering access to free online tools, acceptable use, limitations, third-party services and user responsibilities.',
        ),
        'faq' => array(
            'UptimeFixer FAQs | Free Online Tools Help',
            'Find answers about UptimeFixer tools, browser processing, website checks, supported devices, privacy, files and common troubleshooting questions.',
        ),
        'faqs' => array(
            'UptimeFixer FAQs | Free Online Tools Help',
            'Find answers about UptimeFixer tools, browser processing, website checks, supported devices, privacy, files and common troubleshooting questions.',
        ),
    );

    if ( $front_id && $post_id === $front_id ) {
        return array(
            'title'       => 'Free Online Tools for Everyday Tasks | %%sitename%%',
            'description' => 'Free online tools for images, PDFs, text, calculations and website checks. Find the right tool, follow clear steps and get useful results without signing up.',
        );
    }

    if ( isset( $known[ $slug ] ) ) {
        return array(
            'title'       => $known[ $slug ][0],
            'description' => alltools_seo_trim_text( $known[ $slug ][1], 155 ),
        );
    }

    $description = trim( wp_strip_all_tags( get_the_excerpt( $post_id ) ) );
    if ( '' === $description ) {
        $description = trim( wp_strip_all_tags( strip_shortcodes( (string) $post->post_content ) ) );
    }
    if ( '' === $description ) {
        $description = sprintf( 'Learn more about %s on UptimeFixer, with clear information and links to the relevant free online tools and resources.', $title ? $title : 'this page' );
    }

    return array(
        'title'       => ( $title ? $title : 'UptimeFixer' ) . ' | %%sitename%%',
        'description' => alltools_seo_trim_text( $description, 155 ),
    );
}

/** Fill only empty/automatic Yoast title and description fields for a page. */
function alltools_sync_yoast_page_meta_for_post( $post_id ) {
    $profile = alltools_page_seo_profile( $post_id );
    if ( ! $profile ) return false;

    $auto_map = get_post_meta( $post_id, '_alltools_auto_yoast_meta', true );
    $auto_map = is_array( $auto_map ) ? $auto_map : array();
    $changed  = false;
    $changed = alltools_sync_yoast_field( $post_id, '_yoast_wpseo_title', $profile['title'], array(), $auto_map ) || $changed;
    $changed = alltools_sync_yoast_field( $post_id, '_yoast_wpseo_metadesc', $profile['description'], array(), $auto_map ) || $changed;
    update_post_meta( $post_id, '_alltools_auto_yoast_meta', $auto_map );
    return $changed;
}

/** One safe site-wide pass: all tool pages plus all normal pages, no content edits. */
function alltools_sync_all_yoast_meta() {
    $tool_ids = get_posts( array(
        'post_type'      => 'alltool',
        'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ) );
    foreach ( $tool_ids as $post_id ) {
        alltools_sync_yoast_meta_for_post( $post_id );
    }

    $page_ids = get_posts( array(
        'post_type'      => 'page',
        'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ) );
    foreach ( $page_ids as $post_id ) {
        alltools_sync_yoast_page_meta_for_post( $post_id );
    }

    return array( 'tools' => count( $tool_ids ), 'pages' => count( $page_ids ) );
}

/** Run the metadata fill once after this optimized theme build is installed. */
add_action( 'admin_init', 'alltools_maybe_sync_yoast_meta', 75 );
function alltools_maybe_sync_yoast_meta() {
    if ( ! current_user_can( 'manage_options' ) || ! alltools_yoast_is_active() ) return;
    $sync_version = '4.4.0';
    if ( $sync_version === get_option( 'alltools_yoast_meta_sync_version' ) ) return;
    alltools_sync_all_yoast_meta();
    update_option( 'alltools_yoast_meta_sync_version', $sync_version, false );
}

/** Current normal-page SEO profile, excluding tool CPT pages. */
function alltools_current_page_seo_profile() {
    if ( ! is_front_page() && ! is_page() ) return false;
    $post_id = get_queried_object_id();
    return $post_id ? alltools_page_seo_profile( $post_id ) : false;
}

/** Respect manually entered Yoast values while filling normal page output. */
add_filter( 'wpseo_title', 'alltools_yoast_page_title_default', 18 );
function alltools_yoast_page_title_default( $title ) {
    $profile = alltools_current_page_seo_profile();
    if ( ! $profile ) return $title;
    $post_id = get_queried_object_id();
    if ( '' !== trim( (string) get_post_meta( $post_id, '_yoast_wpseo_title', true ) ) ) return $title;
    return str_replace( '%%sitename%%', get_bloginfo( 'name' ), $profile['title'] );
}

add_filter( 'wpseo_metadesc', 'alltools_yoast_page_description_default', 18 );
function alltools_yoast_page_description_default( $description ) {
    $profile = alltools_current_page_seo_profile();
    if ( ! $profile ) return $description;
    $post_id = get_queried_object_id();
    if ( '' !== trim( (string) get_post_meta( $post_id, '_yoast_wpseo_metadesc', true ) ) ) return $description;
    return $profile['description'];
}

add_filter( 'wpseo_opengraph_title', 'alltools_yoast_page_social_title_default', 18 );
add_filter( 'wpseo_twitter_title', 'alltools_yoast_page_social_title_default', 18 );
function alltools_yoast_page_social_title_default( $title ) {
    $profile = alltools_current_page_seo_profile();
    if ( ! $profile ) return $title;
    $post_id = get_queried_object_id();
    $meta_key = 'wpseo_twitter_title' === current_filter() ? '_yoast_wpseo_twitter-title' : '_yoast_wpseo_opengraph-title';
    if ( '' !== trim( (string) get_post_meta( $post_id, $meta_key, true ) ) ) return $title;
    return str_replace( '%%sitename%%', get_bloginfo( 'name' ), $profile['title'] );
}

add_filter( 'wpseo_opengraph_desc', 'alltools_yoast_page_social_description_default', 18 );
add_filter( 'wpseo_twitter_description', 'alltools_yoast_page_social_description_default', 18 );
function alltools_yoast_page_social_description_default( $description ) {
    $profile = alltools_current_page_seo_profile();
    if ( ! $profile ) return $description;
    $post_id = get_queried_object_id();
    $meta_key = 'wpseo_twitter_description' === current_filter() ? '_yoast_wpseo_twitter-description' : '_yoast_wpseo_opengraph-description';
    if ( '' !== trim( (string) get_post_meta( $post_id, $meta_key, true ) ) ) return $description;
    return $profile['description'];
}

/** Respect manual Yoast values and supply complete front-end fallbacks. */
function alltools_current_tool_seo_profile() {
    if ( ! is_singular( 'alltool' ) ) return false;
    $slug = get_post_meta( get_queried_object_id(), '_alltool_slug', true );
    $tool = alltools_get_tool( $slug );
    return $tool ? alltools_tool_seo_profile( $slug, $tool ) : false;
}

/**
 * Supply a self-referencing canonical when Yoast has no manual value.
 *
 * Yoast can omit the canonical while a URL is noindexed. After that URL is
 * restored to normal indexing, this fallback prevents a stale indexable page
 * from being emitted without a canonical during cache regeneration.
 */
add_filter( 'wpseo_canonical', 'alltools_yoast_tool_canonical_default', 20 );
function alltools_yoast_tool_canonical_default( $canonical ) {
    if ( '' !== trim( (string) $canonical ) || ! is_singular( 'alltool' ) ) return $canonical;
    $post_id = get_queried_object_id();
    if ( ! $post_id || ! alltools_is_tool_index_ready( $post_id ) ) return $canonical;
    return get_permalink( $post_id );
}

add_filter( 'wpseo_title', 'alltools_yoast_title_default' );
function alltools_yoast_title_default( $title ) {
    $profile = alltools_current_tool_seo_profile();
    if ( ! $profile ) return $title;
    $manual = (string) get_post_meta( get_queried_object_id(), '_yoast_wpseo_title', true );
    if ( '' !== trim( $manual ) ) return $title;
    return $profile ? str_replace( '%%sitename%%', get_bloginfo( 'name' ), $profile['title'] ) : $title;
}

add_filter( 'wpseo_metadesc', 'alltools_yoast_description_default' );
function alltools_yoast_description_default( $description ) {
    $profile = alltools_current_tool_seo_profile();
    if ( ! $profile ) return $description;
    $manual = (string) get_post_meta( get_queried_object_id(), '_yoast_wpseo_metadesc', true );
    if ( '' !== trim( $manual ) ) return $description;
    return $profile ? $profile['description'] : $description;
}

add_filter( 'wpseo_opengraph_title', 'alltools_yoast_opengraph_title_default' );
function alltools_yoast_opengraph_title_default( $title ) {
    $profile = alltools_current_tool_seo_profile();
    if ( ! $profile ) return $title;
    $manual = (string) get_post_meta( get_queried_object_id(), '_yoast_wpseo_opengraph-title', true );
    if ( '' !== trim( $manual ) ) return $title;
    return $profile ? str_replace( '%%sitename%%', get_bloginfo( 'name' ), $profile['opengraph_title'] ) : $title;
}

add_filter( 'wpseo_opengraph_desc', 'alltools_yoast_opengraph_desc_default' );
add_filter( 'wpseo_twitter_description', 'alltools_yoast_opengraph_desc_default' );
function alltools_yoast_opengraph_desc_default( $description ) {
    $profile = alltools_current_tool_seo_profile();
    if ( ! $profile ) return $description;
    $post_id = get_queried_object_id();
    $meta_key = 'wpseo_twitter_description' === current_filter() ? '_yoast_wpseo_twitter-description' : '_yoast_wpseo_opengraph-description';
    $manual = (string) get_post_meta( $post_id, $meta_key, true );
    if ( '' !== trim( $manual ) ) return $description;
    return $profile ? $profile['opengraph_desc'] : $description;
}

add_filter( 'wpseo_twitter_title', 'alltools_yoast_twitter_title_default' );
function alltools_yoast_twitter_title_default( $title ) {
    $profile = alltools_current_tool_seo_profile();
    if ( ! $profile ) return $title;
    $manual = (string) get_post_meta( get_queried_object_id(), '_yoast_wpseo_twitter-title', true );
    if ( '' !== trim( $manual ) ) return $title;
    return $profile ? str_replace( '%%sitename%%', get_bloginfo( 'name' ), $profile['twitter_title'] ) : $title;
}

/** SEO descriptions for the canonical tool archive and category hubs. */
function alltools_directory_seo_description() {
    if ( is_post_type_archive( 'alltool' ) ) {
        return alltools_seo_trim_text( 'Browse all free Uptime Fixer online tools for calculations, files, text, development, marketing and public website checks. No visitor signup required.', 155 );
    }
    if ( is_tax( 'alltool_category' ) ) {
        $term = get_queried_object();
        $categories = alltools_tool_categories();
        if ( $term instanceof WP_Term && isset( $categories[ $term->slug ]['description'] ) ) {
            return alltools_seo_trim_text( $categories[ $term->slug ]['description'], 155 );
        }
    }
    return '';
}

add_filter( 'wpseo_metadesc', 'alltools_yoast_directory_description', 30 );
add_filter( 'wpseo_opengraph_desc', 'alltools_yoast_directory_description', 30 );
add_filter( 'wpseo_twitter_description', 'alltools_yoast_directory_description', 30 );
function alltools_yoast_directory_description( $description ) {
    if ( '' !== trim( (string) $description ) ) return $description;
    $directory_description = alltools_directory_seo_description();
    return $directory_description ? $directory_description : $description;
}

/** Replace only the generic “Home” social title; respect hand-written Yoast copy. */
add_filter( 'wpseo_opengraph_title', 'alltools_home_social_title', 40 );
add_filter( 'wpseo_twitter_title', 'alltools_home_social_title', 40 );
function alltools_home_social_title( $title ) {
    if ( ! is_front_page() ) return $title;
    $front_id = get_queried_object_id();
    $meta_key = 'wpseo_twitter_title' === current_filter() ? '_yoast_wpseo_twitter-title' : '_yoast_wpseo_opengraph-title';
    if ( $front_id && '' !== trim( (string) get_post_meta( $front_id, $meta_key, true ) ) ) return $title;
    $page_title = $front_id ? trim( (string) get_the_title( $front_id ) ) : '';
    if ( '' === trim( (string) $title ) || 0 === strcasecmp( trim( (string) $title ), $page_title ) || 0 === strcasecmp( trim( (string) $title ), 'Home' ) ) {
        return sprintf( __( '%s – Free Online Tools', 'alltools' ), get_bloginfo( 'name' ) );
    }
    return $title;
}

/** Map internal categories to recognized Schema.org application categories. */
function alltools_schema_application_category( $category ) {
    $map = array(
        'calculators'     => 'UtilityApplication',
        'pdf-tools'       => 'BusinessApplication',
        'image-tools'     => 'MultimediaApplication',
        'video-tools'     => 'MultimediaApplication',
        'audio-tools'     => 'MultimediaApplication',
        'developer-tools' => 'DeveloperApplication',
        'business-tools'  => 'BusinessApplication',
        'marketing-tools' => 'BusinessApplication',
        'website-tools'   => 'DeveloperApplication',
        'text-tools'      => 'UtilityApplication',
    );
    return isset( $map[ $category ] ) ? $map[ $category ] : 'UtilityApplication';
}

/** Append a connected WebApplication node to Yoast's graph. */
add_filter( 'wpseo_schema_graph', 'alltools_yoast_tool_schema', 20, 2 );
function alltools_yoast_tool_schema( $graph, $context = null ) {
    if ( ! is_singular( 'alltool' ) ) return $graph;
    $post_id = get_queried_object_id();
    if ( ! alltools_is_tool_index_ready( $post_id ) ) return $graph;
    $slug = get_post_meta( $post_id, '_alltool_slug', true );
    $tool = alltools_get_tool( $slug );
    if ( ! $tool ) return $graph;
    $url = get_permalink( $post_id );
    $profile = alltools_tool_seo_profile( $slug, $tool );
    $category = ! empty( $tool['category'] ) ? $tool['category'] : 'other-tools';
    $quality = alltools_tool_quality_profile( $slug, $tool );

    $graph[] = array(
        '@type'               => 'WebApplication',
        '@id'                 => $url . '#webapplication',
        'url'                 => $url,
        'name'                => ! empty( $tool['title'] ) ? $tool['title'] : get_the_title( $post_id ),
        'description'         => $profile['description'],
        'applicationCategory' => alltools_schema_application_category( $category ),
        'operatingSystem'     => 'Any',
        'browserRequirements' => 'Requires JavaScript and a modern web browser.',
        'inLanguage'          => get_bloginfo( 'language' ),
        'isAccessibleForFree' => true,
        'mainEntityOfPage'    => array( '@id' => $url ),
        'offers'              => array( '@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD' ),
        'publisher'           => array( '@id' => home_url( '/#organization' ) ),
        'featureList'         => array_values( $quality['use_cases'] ),
    );

    return $graph;
}

/** Add an ItemList that describes only the tool cards visible on this page. */
add_filter( 'wpseo_schema_graph', 'alltools_yoast_directory_schema', 30, 2 );
function alltools_yoast_directory_schema( $graph, $context = null ) {
    if ( ! is_post_type_archive( 'alltool' ) && ! is_tax( 'alltool_category' ) ) return $graph;
    global $wp_query;
    if ( ! $wp_query || empty( $wp_query->posts ) ) return $graph;

    $url = get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ) );
    $offset = ( max( 1, (int) get_query_var( 'paged' ) ) - 1 ) * (int) $wp_query->get( 'posts_per_page' );
    $items = array();
    foreach ( $wp_query->posts as $index => $post ) {
        $slug = sanitize_key( get_post_meta( $post->ID, '_alltool_slug', true ) );
        $tool = alltools_get_tool( $slug );
        if ( ! $tool ) continue;
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $offset + $index + 1,
            'url'      => get_permalink( $post ),
            'name'     => $tool['title'],
        );
    }
    if ( $items ) {
        $graph[] = array(
            '@type'           => 'ItemList',
            '@id'             => $url . '#tool-list',
            'url'             => $url,
            'name'            => is_tax( 'alltool_category' ) ? single_term_title( '', false ) : __( 'All Online Tools', 'alltools' ),
            'numberOfItems'   => count( $items ),
            'itemListElement' => $items,
        );
    }

    return $graph;
}

/** Connect Yoast's WebPage node to the WebApplication main entity. */
add_filter( 'wpseo_schema_webpage', 'alltools_yoast_connect_webpage_schema', 20 );
function alltools_yoast_connect_webpage_schema( $data ) {
    if ( is_post_type_archive( 'alltool' ) || is_tax( 'alltool_category' ) ) {
        $data['mainEntity'] = array( '@id' => get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ) ) . '#tool-list' );
        return $data;
    }
    if ( ! is_singular( 'alltool' ) ) return $data;
    if ( ! alltools_is_tool_index_ready( get_queried_object_id() ) ) return $data;
    $url = get_permalink( get_queried_object_id() );
    $data['about'] = array( '@id' => $url . '#webapplication' );
    return $data;
}

/** Explicitly include the tool CPT in both Yoast and WordPress core sitemaps. */
add_filter( 'wpseo_sitemap_exclude_post_type', 'alltools_include_tools_in_yoast_sitemap', 10, 2 );
function alltools_include_tools_in_yoast_sitemap( $excluded, $post_type ) {
    return $excluded; // Respect the site's Yoast content-type visibility setting.
}

add_filter( 'wp_sitemaps_post_types', 'alltools_include_tools_in_core_sitemap' );
function alltools_include_tools_in_core_sitemap( $post_types ) {
    if ( ! isset( $post_types['alltool'] ) ) {
        $object = get_post_type_object( 'alltool' );
        if ( $object ) $post_types['alltool'] = $object;
    }
    return $post_types;
}

/** Noindex only duplicate/utility archives and filtered homepage variants. */
function alltools_should_noindex_current_page() {
    // Archive visibility remains under WordPress / Yoast control.
    if ( is_front_page() && ( isset( $_GET['category'] ) || isset( $_GET['view'] ) ) ) return true;
    return false;
}

/**
 * Keep thin archives, the duplicate tool archive and unreviewed scaffold posts
 * out of search results while allowing search engines to follow their links.
 */
add_filter( 'wp_robots', 'alltools_noindex_thin_archives', 999 );
function alltools_noindex_thin_archives( $robots ) {
    if ( alltools_should_noindex_current_page() ) {
        unset( $robots['index'], $robots['nofollow'] );
        $robots['noindex'] = true;
        $robots['follow']  = true;
    }
    return $robots;
}

add_filter( 'wpseo_robots_array', 'alltools_yoast_noindex_thin_archives', 999 );
function alltools_yoast_noindex_thin_archives( $robots ) {
    if ( alltools_should_noindex_current_page() ) {
        $robots['index']  = 'noindex';
        $robots['follow'] = 'follow';
    }
    return $robots;
}

/** Keep the useful tool archive and its eleven populated category hubs indexable. */
// Core already indexes public directories; do not override an explicit noindex.
function alltools_index_tool_directories( $robots ) {
    if ( '1' !== (string) get_option( 'blog_public' ) ) return $robots;
    if ( is_post_type_archive( 'alltool' ) || is_tax( 'alltool_category' ) ) {
        unset( $robots['noindex'], $robots['nofollow'] );
        $robots['index'] = true;
        $robots['follow'] = true;
    }
    return $robots;
}

// Yoast owns its index/noindex choices; do not force an editor's noindex to index.
function alltools_yoast_index_tool_directories( $robots ) {
    if ( '1' !== (string) get_option( 'blog_public' ) ) return $robots;
    if ( is_post_type_archive( 'alltool' ) || is_tax( 'alltool_category' ) ) {
        $robots['index'] = 'index';
        $robots['follow'] = 'follow';
    }
    return $robots;
}

/** Remove thin tag and author archive URLs from Yoast XML sitemaps. */
add_filter( 'wpseo_sitemap_exclude_taxonomy', 'alltools_exclude_thin_taxonomy_sitemaps', 10, 2 );
function alltools_exclude_thin_taxonomy_sitemaps( $excluded, $taxonomy ) {
    return $excluded;
}

add_filter( 'wpseo_sitemap_exclude_author', 'alltools_exclude_author_sitemap' );
function alltools_exclude_author_sitemap( $users ) {
    return $users;
}

/** Advertise exactly one canonical sitemap in robots.txt. */
add_filter( 'robots_txt', 'alltools_add_sitemap_to_robots', PHP_INT_MAX, 2 );
function alltools_add_sitemap_to_robots( $output, $public ) {
    if ( ! $public ) return $output;
    $sitemap = alltools_yoast_is_active() ? home_url( '/sitemap_index.xml' ) : home_url( '/wp-sitemap.xml' );
    $output = preg_replace( '#^\s*Sitemap:\s*\S+/(?:sitemap_index\.xml|wp-sitemap\.xml)\s*$#mi', '', (string) $output );
    return rtrim( $output ) . "\n\nSitemap: " . esc_url_raw( $sitemap ) . "\n";
}

/** Keep every URL listed in the Yoast sitemap index on HTTPS. */
add_filter( 'wpseo_sitemap_index', 'alltools_force_https_sitemap_index', PHP_INT_MAX );
function alltools_force_https_sitemap_index( $sitemap_index ) {
    return $sitemap_index; // URL schemes must follow the actual WordPress site URL.
}
