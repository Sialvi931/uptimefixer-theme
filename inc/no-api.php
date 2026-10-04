<?php
/**
 * Provider-aware API safeguards.
 *
 * Keeps browser/local utilities and protected public diagnostics available,
 * while withholding tools whose required provider has not been configured.
 * Existing WordPress posts are preserved. Saving the relevant credential in
 * Theme Settings restores the original tool URL and handler automatically.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Tool definitions that are intentionally unavailable in the no-key build. */
function alltools_paid_api_tool_slugs() {
    return array(
        'keyword-generator',
        'keyword-difficulty-checker',
        'backlink-checker',
        'domain-authority-checker',
        'keyword-rank-checker',
        'competitor-keyword-checker',
        'ai-visibility-checker',
        'ai-text-summarizer',
        'pdf-summarizer',
        'audio-transcription',
        'text-to-speech',
        'ai-image-generator',
        'product-description-generator',
        'core-web-vitals-checker',
        'mobile-friendly-test',
        'accessibility-checker',
        'lighthouse-audit',
    );
}

/** Map provider-dependent tools to the server-side credential they require. */
function alltools_paid_api_tool_groups() {
    return array(
        'dataforseo' => array(
            'keyword-generator', 'keyword-difficulty-checker', 'backlink-checker',
            'domain-authority-checker', 'keyword-rank-checker', 'competitor-keyword-checker',
        ),
        'openai' => array(
            'ai-visibility-checker', 'ai-text-summarizer', 'pdf-summarizer',
            'audio-transcription', 'text-to-speech', 'ai-image-generator',
            'product-description-generator',
        ),
        'pagespeed' => array(
            'core-web-vitals-checker', 'mobile-friendly-test',
            'accessibility-checker', 'lighthouse-audit',
        ),
    );
}

/** Whether one provider group has its required server-side credential. */
function alltools_provider_is_configured( $provider ) {
    if ( ! function_exists( 'alltools_integration_secret' ) ) return false;
    if ( 'openai' === $provider ) return '' !== alltools_integration_secret( 'openai_api_key' );
    if ( 'pagespeed' === $provider ) return '' !== alltools_integration_secret( 'pagespeed_api_key' );
    if ( 'dataforseo' === $provider ) {
        return '' !== alltools_integration_secret( 'dataforseo_login' ) && '' !== alltools_integration_secret( 'dataforseo_password' );
    }
    return false;
}

/** Only tools without their required provider remain unavailable. */
function alltools_unavailable_paid_api_tool_slugs() {
    $unavailable = array();
    foreach ( alltools_paid_api_tool_groups() as $provider => $slugs ) {
        if ( ! alltools_provider_is_configured( $provider ) ) $unavailable = array_merge( $unavailable, $slugs );
    }
    return array_values( array_unique( $unavailable ) );
}

/** Remove paid/key-dependent tools after every registry extension has run. */
add_filter( 'alltools_registered_tools', 'alltools_remove_paid_api_tools', PHP_INT_MAX );
function alltools_remove_paid_api_tools( $tools ) {
    foreach ( alltools_unavailable_paid_api_tool_slugs() as $slug ) {
        unset( $tools[ $slug ] );
    }
    return $tools;
}

/** Map retired URLs to the closest working, no-key alternative. */
function alltools_paid_api_replacement_slug( $slug ) {
    $replacements = array(
        'core-web-vitals-checker'       => 'website-speed-test',
        'mobile-friendly-test'          => 'website-speed-test',
        'accessibility-checker'         => 'website-health-checker',
        'lighthouse-audit'              => 'website-speed-test',
        'keyword-generator'             => 'google-trends-comparison-tool',
        'keyword-difficulty-checker'    => 'google-trends-comparison-tool',
        'keyword-rank-checker'          => 'google-trends-comparison-tool',
        'competitor-keyword-checker'    => 'google-trends-comparison-tool',
        'backlink-checker'              => 'website-health-checker',
        'domain-authority-checker'      => 'website-health-checker',
        'ai-visibility-checker'         => 'google-trends-comparison-tool',
        'ai-text-summarizer'            => 'word-counter',
        'pdf-summarizer'                => 'pdf-to-text',
        'audio-transcription'           => 'voice-recorder',
        'text-to-speech'                => 'voice-recorder',
        'ai-image-generator'            => 'image-resizer',
        'product-description-generator' => 'word-counter',
    );
    return isset( $replacements[ $slug ] ) ? $replacements[ $slug ] : 'website-health-checker';
}

/** Find existing CPT rows whose provider is currently unavailable. */
function alltools_paid_api_tool_post_ids() {
    static $post_ids = null;
    if ( null !== $post_ids ) return $post_ids;

    $unavailable = alltools_unavailable_paid_api_tool_slugs();
    if ( ! $unavailable ) {
        $post_ids = array();
        return $post_ids;
    }

    $post_ids = get_posts( array(
        'post_type'      => 'alltool',
        'post_status'    => 'any',
        'numberposts'    => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_key'       => '_alltool_slug',
        'meta_value'     => $unavailable,
        'meta_compare'   => 'IN',
        'suppress_filters' => true,
    ) );

    return array_values( array_unique( array_map( 'intval', (array) $post_ids ) ) );
}

/** Redirect old API-tool URLs so visitors never land on a broken interface. */
add_action( 'template_redirect', 'alltools_redirect_retired_api_tools', -50 );
function alltools_redirect_retired_api_tools() {
    if ( is_admin() || wp_doing_ajax() ) return;

    $slug = '';
    if ( is_singular( 'alltool' ) ) {
        $slug = sanitize_key( (string) get_post_meta( get_queried_object_id(), '_alltool_slug', true ) );
    } elseif ( isset( $_GET['alltool'] ) ) {
        $slug = sanitize_key( wp_unslash( $_GET['alltool'] ) );
    }

    if ( ! in_array( $slug, alltools_unavailable_paid_api_tool_slugs(), true ) ) return;

    $replacement = alltools_paid_api_replacement_slug( $slug );
    nocache_headers();
    wp_safe_redirect( alltools_tool_url( $replacement ), 302, 'UptimeFixer' );
    exit;
}

/** Keep currently unavailable provider tools out of public discovery surfaces. */
add_action( 'pre_get_posts', 'alltools_hide_retired_api_tool_posts' );
function alltools_hide_retired_api_tool_posts( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) return;
    if ( ! $query->is_search() && ! $query->is_post_type_archive( 'alltool' ) && ! $query->is_tax( 'alltool_category' ) ) return;

    $query->set( 'post__not_in', array_values( array_unique( array_merge(
        (array) $query->get( 'post__not_in' ),
        alltools_paid_api_tool_post_ids()
    ) ) ) );
}

add_filter( 'rest_alltool_query', 'alltools_hide_retired_api_tools_from_rest' );
function alltools_hide_retired_api_tools_from_rest( $args ) {
    $args['post__not_in'] = array_values( array_unique( array_merge(
        isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array(),
        alltools_paid_api_tool_post_ids()
    ) ) );
    return $args;
}

add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', 'alltools_hide_retired_api_tools_from_yoast', 50 );
function alltools_hide_retired_api_tools_from_yoast( $post_ids ) {
    return array_values( array_unique( array_merge(
        is_array( $post_ids ) ? $post_ids : array(),
        alltools_paid_api_tool_post_ids()
    ) ) );
}

add_filter( 'wp_sitemaps_posts_query_args', 'alltools_hide_retired_api_tools_from_core_sitemap', 50, 2 );
function alltools_hide_retired_api_tools_from_core_sitemap( $args, $post_type ) {
    if ( 'alltool' !== $post_type ) return $args;
    $args['post__not_in'] = array_values( array_unique( array_merge(
        isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array(),
        alltools_paid_api_tool_post_ids()
    ) ) );
    return $args;
}

/** Disable only handlers whose server-side provider is not configured. */
add_action( 'init', 'alltools_disable_paid_api_ajax_routes', -100 );
function alltools_disable_paid_api_ajax_routes() {
    $callbacks = array(
        'pagespeed' => array(
            'pagespeed'         => 'ufx_api_pagespeed',
            'mobile_test'       => 'ufx_api_mobile_test',
            'growth_lighthouse' => 'ufx_api_growth_lighthouse',
        ),
        'dataforseo' => array(
            'growth_seo' => 'ufx_api_growth_seo',
        ),
        'openai' => array(
            'growth_ai_text'       => 'ufx_api_growth_ai_text',
            'growth_ai_image'      => 'ufx_api_growth_ai_image',
            'growth_transcription' => 'ufx_api_growth_transcription',
            'growth_speech'        => 'ufx_api_growth_speech',
        ),
    );

    foreach ( $callbacks as $provider => $provider_callbacks ) {
        if ( alltools_provider_is_configured( $provider ) ) continue;
        foreach ( $provider_callbacks as $action => $callback ) {
            remove_action( 'wp_ajax_ufx_' . $action, $callback );
            remove_action( 'wp_ajax_nopriv_ufx_' . $action, $callback );
        }
    }
}

/** Refresh rewrite/sitemap state once after installing this edition. */
add_action( 'admin_init', 'alltools_no_api_upgrade_tasks', 60 );
function alltools_no_api_upgrade_tasks() {
    if ( ! current_user_can( 'manage_options' ) || get_option( 'alltools_no_api_upgrade_v4' ) ) return;
    flush_rewrite_rules( false );
    if ( class_exists( 'WPSEO_Sitemaps_Cache' ) && is_callable( array( 'WPSEO_Sitemaps_Cache', 'clear' ) ) ) {
        WPSEO_Sitemaps_Cache::clear();
    }
    update_option( 'alltools_no_api_upgrade_v4', gmdate( 'c' ), false );
}

/** Do not silently override WordPress privacy; show the exact indexing blocker. */
add_action( 'admin_notices', 'alltools_search_visibility_notice' );
function alltools_search_visibility_notice() {
    if ( ! current_user_can( 'manage_options' ) || '0' !== (string) get_option( 'blog_public', '1' ) ) return;
    $url = admin_url( 'options-reading.php' );
    ?>
    <div class="notice notice-error">
        <p><strong><?php esc_html_e( 'Uptime Fixer indexing is currently blocked:', 'alltools' ); ?></strong>
        <?php esc_html_e( 'WordPress “Discourage search engines from indexing this site” is enabled.', 'alltools' ); ?>
        <a href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Open Reading Settings', 'alltools' ); ?></a></p>
    </div>
    <?php
}
