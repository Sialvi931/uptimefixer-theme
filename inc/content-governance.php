<?php
/**
 * Editorial controls for public discovery, indexing and duplicate URLs.
 *
 * The complete registry remains available to administrators and every tool can
 * still be opened directly. Every working tool remains visible to visitors,
 * while only reviewed, canonical pages are submitted to search engines.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Published, registered tools are indexable by default.
 *
 * Individual pages can still be held back with the Search visibility control.
 * Keeping the registry as the default set prevents a theme update from silently
 * applying noindex to hundreds of previously public tool URLs.
 */
function alltools_default_index_ready_slugs() {
    static $slugs = null;
    if ( null === $slugs ) {
        $slugs = function_exists( 'alltools_all' ) ? array_keys( alltools_all() ) : array();
        $slugs = (array) apply_filters( 'alltools_default_index_ready_slugs', $slugs );
        $slugs = array_values( array_unique( array_filter( array_map( 'sanitize_key', $slugs ) ) ) );
    }
    return $slugs;
}

/** Find the canonical post for a registered tool slug. */
function alltools_canonical_tool_post_id( $slug ) {
    $slug = sanitize_key( $slug );
    if ( ! $slug ) return 0;
    static $cache = null;
    if ( null === $cache ) {
        $cache = array();
        $posts = get_posts( array(
        'post_type'      => 'alltool',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'ID',
        'order'          => 'ASC',
        'no_found_rows'  => true,
        ) );
        foreach ( $posts as $post ) {
            $registered_slug = sanitize_key( get_post_meta( $post->ID, '_alltool_slug', true ) );
            if ( ! $registered_slug ) continue;
            if ( ! isset( $cache[ $registered_slug ] ) || $post->post_name === $registered_slug ) {
                $cache[ $registered_slug ] = (int) $post->ID;
            }
        }
    }
    return isset( $cache[ $slug ] ) ? $cache[ $slug ] : 0;
}

/** Whether this post is the one canonical URL for its registry slug. */
function alltools_is_canonical_tool_post( $post_id ) {
    $post_id = (int) $post_id;
    $slug = get_post_meta( $post_id, '_alltool_slug', true );
    if ( ! $slug ) return false;
    $canonical_id = alltools_canonical_tool_post_id( $slug );
    return ! $canonical_id || $canonical_id === $post_id;
}

/** Duplicate published CPT rows must not create repeated directory cards. */
function alltools_noncanonical_tool_post_ids() {
    static $duplicates = null;
    if ( null !== $duplicates ) return $duplicates;
    $ids = get_posts( array(
        'post_type'      => 'alltool',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ) );
    $duplicates = array_values( array_filter( array_map( 'intval', $ids ), function( $post_id ) {
        return ! alltools_is_canonical_tool_post( $post_id );
    } ) );
    return $duplicates;
}

/** Resolve the editorial index status for a tool slug or post ID. */
function alltools_is_tool_index_ready( $tool_or_post ) {
    $post_id = is_numeric( $tool_or_post ) ? (int) $tool_or_post : 0;
    $slug = $post_id ? get_post_meta( $post_id, '_alltool_slug', true ) : sanitize_key( $tool_or_post );
    if ( ! $slug ) return false;

    if ( $post_id && ! alltools_is_canonical_tool_post( $post_id ) ) return false;
    if ( ! $post_id ) $post_id = alltools_canonical_tool_post_id( $slug );

    if ( $post_id ) {
        $override = get_post_meta( $post_id, '_alltools_index_ready', true );
        if ( 'yes' === $override ) return true;
        if ( 'no' === $override ) return false;
    }
    return in_array( $slug, alltools_default_index_ready_slugs(), true );
}

/**
 * Complete working registry for directories and internal search.
 *
 * Index eligibility is intentionally handled separately by
 * alltools_is_tool_index_ready(), robots directives, and sitemap filters.
 */
function alltools_public_tools( $category = '' ) {
    static $public = null;
    if ( null === $public ) {
        $public = alltools_all();
    }
    if ( ! $category ) return $public;
    return array_filter( $public, function( $tool ) use ( $category ) {
        return ! empty( $tool['category'] ) && $category === $tool['category'];
    } );
}

function alltools_public_tool_count_in_cat( $category ) {
    return count( alltools_public_tools( $category ) );
}

function alltools_public_related( $current_slug, $limit = 5 ) {
    $current = alltools_get_tool( $current_slug );
    if ( ! $current ) return array();
    $category_tools = alltools_public_tools( $current['category'] );
    $slugs = array_keys( $category_tools );
    $current_index = array_search( $current_slug, $slugs, true );
    if ( false === $current_index ) $current_index = 0;
    $related = array();
    $available = max( 0, count( $slugs ) - 1 );
    $take = min( max( 0, (int) $limit ), $available );
    for ( $offset = 1; $offset <= $available && count( $related ) < $take; $offset++ ) {
        $slug = $slugs[ ( $current_index + $offset ) % count( $slugs ) ];
        if ( $slug === $current_slug || ! isset( $category_tools[ $slug ] ) ) continue;
        $tool = $category_tools[ $slug ];
        $related[ $slug ] = $tool;
    }
    return $related;
}

/** Original, tool-specific context for the bundled reviewed pages. */
function alltools_tool_editorial_content( $slug ) {
    $content = array(
        'age-calculator' => array( 'Use this calculator when a calendar-accurate age matters. It compares a birth date with a chosen “as of” date, so the result can differ from simply dividing elapsed days by 365.', array( 'Confirm the day, month and year before calculating.', 'Use the “as of” field for historical or future age checks.', 'Do not treat the result as identity or eligibility verification.' ) ),
        'percentage-calculator' => array( 'Five modes cover common percentage questions: a percentage of a value, percentage change, reverse percentage, and adding or subtracting a percentage. The labels beside each field show which number belongs in each part of the formula.', array( 'Keep all inputs in the same unit.', 'For percentage change, the starting value is the reference value.', 'Round only the final result when accuracy matters.' ) ),
        'emi-calculator' => array( 'The EMI estimate uses principal, periodic interest and loan length to create a month-by-month repayment schedule. It is useful for comparing scenarios, but a lender may add fees, insurance, taxes or a different compounding convention.', array( 'Enter the annual rate quoted by the lender.', 'Compare total interest as well as the monthly payment.', 'Confirm the final schedule with the lender before committing.' ) ),
        'image-converter' => array( 'Image formats solve different problems: JPEG is compact for photographs, PNG supports transparency, and WebP often produces smaller web images. Conversion creates a new file and leaves the source unchanged.', array( 'Keep the original file until you inspect the download.', 'Choose PNG when transparency must be preserved.', 'Check dimensions, colour and sharpness after conversion.' ) ),
        'image-resizer' => array( 'Resizing changes pixel dimensions rather than merely changing how large an image appears on screen. Reducing dimensions usually cuts file size; enlarging beyond the source resolution can soften edges and reveal artefacts.', array( 'Lock the aspect ratio to avoid stretching.', 'Resize a copy rather than the only original.', 'Inspect text and fine details at 100% zoom.' ) ),
        'favicon-generator' => array( 'A favicon package supplies the square icon sizes used by browser tabs, bookmarks and device shortcuts. A simple high-contrast source works better than a detailed photograph at very small sizes.', array( 'Start with a square source of at least 512×512 pixels.', 'Preview the 16×16 version for legibility.', 'Upload the files and place the generated tags inside the site head.' ) ),
        'word-counter' => array( 'The counter measures words, characters, sentences and paragraphs as you type. Reading and speaking times are estimates based on average rates, so specialist or highly technical text may take longer.', array( 'Use the no-spaces character count for strict platform limits.', 'Treat reading time as an estimate, not a guarantee.', 'Proofread the text separately; counting does not check meaning.' ) ),
        'character-counter' => array( 'This tool separates total characters from characters excluding whitespace, which is useful because publishing platforms count limits differently. It also provides supporting word, sentence and paragraph totals.', array( 'Check which counting rule your destination uses.', 'Remember that punctuation and emoji can count toward a limit.', 'Paste the final version again after editing.' ) ),
        'case-converter' => array( 'Case conversion changes capitalization and separators without rewriting the underlying message. Modes such as camelCase, PascalCase, snake_case and kebab-case are intended for different writing and development conventions.', array( 'Review acronyms and proper names after conversion.', 'Use snake_case or kebab-case only where the target system expects it.', 'Keep a copy of deliberately mixed-case source text.' ) ),
        'remove-duplicate-lines' => array( 'Duplicate-line removal compares complete lines and keeps a single occurrence. It is well suited to cleaning lists, IDs and copied records, but near-matches with different spacing or punctuation remain distinct.', array( 'Normalize spacing first if near-duplicates should match.', 'Decide whether capitalization should make lines distinct.', 'Compare line counts before replacing the source.' ) ),
        'remove-extra-spaces' => array( 'Whitespace cleanup makes pasted text consistent by collapsing redundant spaces and, when selected, removing blank lines. It does not understand layout intent, so poems, code and fixed-width tables need extra care.', array( 'Work on a copy when spacing carries meaning.', 'Preview paragraph breaks before copying the result.', 'Use a code formatter instead for source code.' ) ),
        'qr-code-generator' => array( 'A QR code stores the exact text, URL or contact payload entered at generation time. Error correction can improve scan reliability, while dense payloads and heavy styling can make the symbol harder to read.', array( 'Scan the finished code on more than one device.', 'Use a complete HTTPS URL for web destinations.', 'Keep strong contrast and a clear quiet zone around the code.' ) ),
        'website-uptime-checker' => array( 'Each run is an on-demand availability check from the site server. A successful response shows that the target answered that request; it is not a guarantee of continuous uptime or availability from every region.', array( 'Retest a failure from your own connection.', 'Check the returned status code and response time together.', 'Use a monitoring service when alerts and historical uptime are required.' ) ),
        'website-speed-test' => array( 'A speed test is a snapshot for one URL, device profile and test location. Network conditions, caches, consent banners and third-party scripts can change results between runs.', array( 'Run the same URL several times before drawing conclusions.', 'Prioritize measured bottlenecks over a single headline score.', 'Test important templates separately, not just the homepage.' ) ),
        'http-status-checker' => array( 'HTTP status codes describe how a server handled a request: 2xx usually means success, 3xx a redirect, 4xx a client-side problem and 5xx a server failure. Headers add context about caching, content and routing.', array( 'Enter the exact protocol and path you want to inspect.', 'Follow redirects to confirm the final destination.', 'Do not assume a 200 response means the page content is correct.' ) ),
        'ssl-checker' => array( 'The SSL check reads the public certificate presented by a domain and reports details such as issuer, validity period and host coverage. It cannot prove that the website itself is trustworthy or free of application vulnerabilities.', array( 'Check that the requested hostname appears in the certificate.', 'Renew well before the displayed expiry date.', 'Investigate chain or hostname errors in the server configuration.' ) ),
        'dns-lookup' => array( 'DNS records connect a domain with web, mail and verification services. Results can differ during propagation because recursive resolvers cache records according to their time-to-live values.', array( 'Query the exact hostname, not only the root domain.', 'Allow for TTL when checking a recent change.', 'Avoid publishing sensitive operational notes in TXT records.' ) ),
        'redirect-checker' => array( 'Redirect tracing follows the sequence from an entered URL to its final destination and records each HTTP hop. Long chains add latency and can reveal outdated protocol, hostname or path migrations.', array( 'Aim for one direct permanent redirect where possible.', 'Check both HTTP/HTTPS and www/non-www variants.', 'Confirm that the final page matches the intended destination.' ) ),
        'check-friends-age' => array( 'This friendly age view calculates calendar age and optional birthday details from the date supplied. Use it for informal planning and celebrations, not as proof of another person’s identity or legal age.', array( 'Ask permission before entering or sharing someone’s birth date.', 'Double-check the date format.', 'Keep generated details private unless the person agrees to share them.' ) ),
        'share-age-result' => array( 'The share card turns an age calculation into a visual summary. Review every included field before downloading or sharing because birth dates and age details can be personal information.', array( 'Include only details the person is comfortable publishing.', 'Preview the card on a small screen.', 'Remove the downloaded file from shared devices when finished.' ) ),
    );
    $editorial = isset( $content[ $slug ] ) ? $content[ $slug ] : array();
    return apply_filters( 'alltools_tool_editorial_content', $editorial, $slug );
}

/**
 * Ordinary published posts keep WordPress' normal public/indexable behaviour.
 * Editors can explicitly hold back a specific article with the value "no".
 */
function alltools_is_post_editorially_reviewed( $post_id ) {
    return 'no' !== get_post_meta( (int) $post_id, '_alltools_editorial_reviewed', true );
}

function alltools_reviewed_post_count() {
    static $count = null;
    if ( null === $count ) {
        $count = count( get_posts( array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => alltools_reviewed_post_meta_query(),
        ) ) );
    }
    return $count;
}

function alltools_reviewed_post_meta_query() {
    return array(
        'relation' => 'OR',
        array( 'key' => '_alltools_editorial_reviewed', 'compare' => 'NOT EXISTS' ),
        array( 'key' => '_alltools_editorial_reviewed', 'value' => 'no', 'compare' => '!=' ),
    );
}

/** IDs that must not enter XML sitemaps. */
function alltools_nonindexable_post_ids() {
    static $excluded = null;
    if ( null !== $excluded ) return $excluded;
    $ids = get_posts( array(
        'post_type'      => array( 'alltool', 'post' ),
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ) );
    $excluded = array();
    foreach ( $ids as $post_id ) {
        if ( 'alltool' === get_post_type( $post_id ) ) {
            if ( ! alltools_is_tool_index_ready( $post_id ) ) $excluded[] = (int) $post_id;
        } elseif ( ! alltools_is_post_editorially_reviewed( $post_id ) ) {
            $excluded[] = (int) $post_id;
        }
    }
    if ( ! alltools_reviewed_post_count() ) {
        $blog_page = get_page_by_path( 'blog' );
        if ( $blog_page ) $excluded[] = (int) $blog_page->ID;
    }
    $excluded = array_values( array_unique( $excluded ) );
    return $excluded;
}

add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', function( $ids ) {
    return array_values( array_unique( array_merge( (array) $ids, alltools_nonindexable_post_ids() ) ) );
}, 20 );

add_filter( 'wp_sitemaps_posts_query_args', function( $args, $post_type ) {
    if ( in_array( $post_type, array( 'alltool', 'post', 'page' ), true ) ) {
        $args['post__not_in'] = array_values( array_unique( array_merge(
            isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array(),
            alltools_nonindexable_post_ids()
        ) ) );
    }
    return $args;
}, 20, 2 );

/** Apply noindex,follow to unfinished tools and explicitly held-back articles. */
function alltools_current_content_needs_noindex() {
    if ( is_singular( 'alltool' ) ) return ! alltools_is_tool_index_ready( get_queried_object_id() );
    if ( is_singular( 'post' ) ) return ! alltools_is_post_editorially_reviewed( get_queried_object_id() );
    if ( is_page_template( 'template-blog.php' ) && ! alltools_reviewed_post_count() ) return true;
    return false;
}

add_filter( 'wp_robots', function( $robots ) {
    if ( alltools_current_content_needs_noindex() ) {
        unset( $robots['index'], $robots['nofollow'] );
        $robots['noindex'] = true;
        $robots['follow'] = true;
    }
    return $robots;
}, 1000 );

add_filter( 'wpseo_robots_array', function( $robots ) {
    if ( alltools_current_content_needs_noindex() ) {
        $robots['index'] = 'noindex';
        $robots['follow'] = 'follow';
    }
    return $robots;
}, 1000 );

/** Exclude only articles an editor explicitly marked as not ready. */
add_action( 'pre_get_posts', function( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) return;
    if ( $query->is_home() || $query->is_category() || $query->is_feed() ) {
        $visibility = alltools_reviewed_post_meta_query();
        $existing   = $query->get( 'meta_query' );
        $query->set( 'meta_query', $existing ? array( 'relation' => 'AND', $existing, $visibility ) : $visibility );
    }
}, 20 );

/** Hide empty quality-gated destinations from an existing custom menu. */
add_filter( 'wp_nav_menu_objects', function( $items ) {
    foreach ( $items as $key => $item ) {
        $query = wp_parse_url( $item->url, PHP_URL_QUERY );
        $params = array();
        if ( $query ) parse_str( $query, $params );
        if ( ! empty( $params['category'] ) && ! alltools_public_tool_count_in_cat( sanitize_key( $params['category'] ) ) ) {
            unset( $items[ $key ] );
            continue;
        }
        $path = untrailingslashit( (string) wp_parse_url( $item->url, PHP_URL_PATH ) );
        if ( 'blog' === basename( $path ) && ! alltools_reviewed_post_count() ) unset( $items[ $key ] );
    }
    return $items;
}, 20 );

/** Redirect legacy directory filters and duplicate tool posts to canonical URLs. */
add_action( 'template_redirect', function() {
    if ( is_front_page() && isset( $_GET['category'] ) ) {
        $category = sanitize_key( wp_unslash( $_GET['category'] ) );
        if ( isset( alltools_tool_categories()[ $category ] ) ) {
            wp_safe_redirect( alltools_category_url( $category ), 301 );
            exit;
        }
    }
    if ( is_front_page() && isset( $_GET['view'] ) && 'all' === sanitize_key( wp_unslash( $_GET['view'] ) ) ) {
        wp_safe_redirect( get_post_type_archive_link( 'alltool' ), 301 );
        exit;
    }
    if ( ! is_singular( 'alltool' ) ) return;
    $post_id = get_queried_object_id();
    $slug = get_post_meta( $post_id, '_alltool_slug', true );
    if ( ! $slug ) return;
    $canonical_id = alltools_canonical_tool_post_id( $slug );
    if ( $canonical_id && $canonical_id !== $post_id ) {
        wp_safe_redirect( get_permalink( $canonical_id ), 301 );
        exit;
    }
}, 0 );

/** Simple editorial controls in the WordPress editor. */
add_action( 'add_meta_boxes', function() {
    add_meta_box( 'alltools-search-visibility', __( 'Search visibility', 'alltools' ), 'alltools_render_visibility_meta_box', 'alltool', 'side', 'high' );
    add_meta_box( 'alltools-editorial-review', __( 'Editorial review', 'alltools' ), 'alltools_render_visibility_meta_box', 'post', 'side', 'high' );
} );

function alltools_render_visibility_meta_box( $post ) {
    wp_nonce_field( 'alltools_save_visibility', 'alltools_visibility_nonce' );
    if ( 'alltool' === $post->post_type ) {
        $value = get_post_meta( $post->ID, '_alltools_index_ready', true );
        ?>
        <p><label for="alltools_index_ready"><strong><?php esc_html_e( 'Indexing status', 'alltools' ); ?></strong></label></p>
        <select name="alltools_index_ready" id="alltools_index_ready" style="width:100%">
            <option value="" <?php selected( $value, '' ); ?>><?php esc_html_e( 'Theme default', 'alltools' ); ?></option>
            <option value="yes" <?php selected( $value, 'yes' ); ?>><?php esc_html_e( 'Reviewed — allow indexing', 'alltools' ); ?></option>
            <option value="no" <?php selected( $value, 'no' ); ?>><?php esc_html_e( 'Draft quality — noindex', 'alltools' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Published registered tools are indexable by default. Use Draft quality only when a specific page must be held back from search.', 'alltools' ); ?></p>
        <?php
    } else {
        $value = get_post_meta( $post->ID, '_alltools_editorial_reviewed', true );
        ?>
        <p><label for="alltools_editorial_reviewed"><strong><?php esc_html_e( 'Article visibility', 'alltools' ); ?></strong></label></p>
        <select name="alltools_editorial_reviewed" id="alltools_editorial_reviewed" style="width:100%">
            <option value="" <?php selected( $value, '' ); ?>><?php esc_html_e( 'Use normal WordPress visibility', 'alltools' ); ?></option>
            <option value="yes" <?php selected( $value, 'yes' ); ?>><?php esc_html_e( 'Fact-checked and indexable', 'alltools' ); ?></option>
            <option value="no" <?php selected( $value, 'no' ); ?>><?php esc_html_e( 'Hold back — noindex', 'alltools' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Existing posts remain in archives and sitemaps unless you explicitly hold one back.', 'alltools' ); ?></p><?php
    }
}

add_action( 'save_post', function( $post_id, $post ) {
    if ( ! isset( $_POST['alltools_visibility_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['alltools_visibility_nonce'] ) ), 'alltools_save_visibility' ) ) return;
    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( 'alltool' === $post->post_type ) {
        $value = isset( $_POST['alltools_index_ready'] ) ? sanitize_key( wp_unslash( $_POST['alltools_index_ready'] ) ) : '';
        if ( in_array( $value, array( 'yes', 'no' ), true ) ) update_post_meta( $post_id, '_alltools_index_ready', $value );
        else delete_post_meta( $post_id, '_alltools_index_ready' );
    } elseif ( 'post' === $post->post_type ) {
        $value = isset( $_POST['alltools_editorial_reviewed'] ) ? sanitize_key( wp_unslash( $_POST['alltools_editorial_reviewed'] ) ) : '';
        if ( in_array( $value, array( 'yes', 'no' ), true ) ) update_post_meta( $post_id, '_alltools_editorial_reviewed', $value );
        else delete_post_meta( $post_id, '_alltools_editorial_reviewed' );
    }
}, 20, 2 );

/** One concise dashboard reminder replaces guesswork about what is indexable. */
add_action( 'admin_notices', function() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen || ! in_array( $screen->base, array( 'dashboard', 'edit' ), true ) ) return;
    echo '<div class="notice notice-info is-dismissible"><p><strong>' . esc_html__( 'Uptime Fixer search visibility:', 'alltools' ) . '</strong> ' . esc_html__( 'Published registered tools and ordinary blog posts use normal WordPress indexing. Use Search visibility only to hold back a specific page that needs correction.', 'alltools' ) . '</p></div>';
} );

/**
 * Refresh sitemap and rewrite state once after restoring normal tool indexing.
 *
 * The previous release cached a sitemap containing only the small review list.
 * Clearing that cache is required so Yoast immediately rebuilds the alltool
 * sitemap from the complete published registry.
 */
add_action( 'admin_init', 'alltools_indexing_recovery_upgrade', 65 );
function alltools_indexing_recovery_upgrade() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    if ( '1' === get_option( 'alltools_indexing_recovery_v391' ) ) return;

    if ( class_exists( 'WPSEO_Sitemaps_Cache' ) && is_callable( array( 'WPSEO_Sitemaps_Cache', 'clear' ) ) ) {
        WPSEO_Sitemaps_Cache::clear();
    }
    flush_rewrite_rules( false );

    // LiteSpeed can otherwise continue serving cached noindex HTML after the
    // PHP rules have been corrected.
    do_action( 'litespeed_purge_all' );

    update_option( 'alltools_indexing_recovery_v391', '1', false );
}
