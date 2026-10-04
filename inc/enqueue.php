<?php
/**
 * Enqueue scripts & styles.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts', 'alltools_enqueue' );
function alltools_enqueue() {
    wp_enqueue_style(
        'alltools-poppins',
        'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap',
        array(),
        null
    );

    // Critical above-the-fold CSS is printed inline in performance.php.
    wp_enqueue_style(
        'alltools-main',
        ALLTOOLS_URI . 'assets/css/main.min.css',
        array(),
        ALLTOOLS_VERSION
    );

    if ( is_post_type_archive( 'alltool' ) || is_tax( 'alltool_category' ) ) {
        wp_enqueue_style(
            'alltools-home',
            ALLTOOLS_URI . 'assets/css/home.css',
            array( 'alltools-main' ),
            ALLTOOLS_VERSION
        );
    }

    $primary = sanitize_hex_color( alltools_get( 'primary_color', '#2563EB' ) );
    $accent  = sanitize_hex_color( alltools_get( 'accent_color', '#8B5CF6' ) );
    $primary = $primary ? $primary : '#2563EB';
    $accent  = $accent ? $accent : '#8B5CF6';
    wp_add_inline_style( 'alltools-main', ":root{--at-primary:{$primary};--at-accent:{$accent};}" );

    wp_enqueue_script(
        'alltools-main-js',
        ALLTOOLS_URI . 'assets/js/main.js',
        array(),
        ALLTOOLS_VERSION,
        true
    );

    $tracking_slug = '';
    if ( is_singular( 'alltool' ) ) {
        $tracking_slug = sanitize_key( get_post_meta( get_queried_object_id(), '_alltool_slug', true ) );
    }
    wp_localize_script( 'alltools-main-js', 'UFX_TRACKING', array(
        'enabled'  => function_exists( 'alltools_gtm_id' ) && (bool) alltools_gtm_id(),
        'toolSlug' => $tracking_slug,
    ) );

    if ( ! function_exists( 'alltools_should_load_tools_assets' ) || ! alltools_should_load_tools_assets() ) {
        return;
    }

    // One small shared API object is enough for every independent theme bundle.
    wp_localize_script( 'alltools-main-js', 'UFX_API', array(
        'ajax'  => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'ufx_api' ),
    ) );

    $needs_base   = function_exists( 'alltools_should_load_tool_group' ) && alltools_should_load_tool_group( 'base' );
    $needs_extra  = function_exists( 'alltools_should_load_tool_group' ) && alltools_should_load_tool_group( 'extra' );
    $needs_growth = function_exists( 'alltools_should_load_tool_group' ) && alltools_should_load_tool_group( 'growth' );
    $needs_seo    = function_exists( 'alltools_should_load_tool_group' ) && alltools_should_load_tool_group( 'seo' );

    if ( $needs_extra || $needs_growth || $needs_seo ) {
        wp_enqueue_style(
            'alltools-tools-extra',
            ALLTOOLS_URI . 'assets/css/tools-extra.css',
            array( 'alltools-main' ),
            ALLTOOLS_VERSION
        );
    }

    if ( $needs_base ) {
        $deps = array( 'alltools-main-js' );
        if ( function_exists( 'alltools_should_load_qr_asset' ) && alltools_should_load_qr_asset() ) {
            wp_enqueue_script(
                'alltools-qrcode',
                ALLTOOLS_URI . 'assets/js/qrcode.min.js',
                array(),
                ALLTOOLS_VERSION,
                true
            );
            $deps[] = 'alltools-qrcode';
        }

        wp_enqueue_script(
            'alltools-tools-js',
            ALLTOOLS_URI . 'assets/js/tools.js',
            $deps,
            ALLTOOLS_VERSION,
            true
        );
    }

    // The media loader itself is tiny and only downloads FFmpeg after a user
    // starts a media operation. It is needed by selected extra/growth tools.
    if ( $needs_extra || $needs_growth ) {
        wp_enqueue_script(
            'alltools-media-engine',
            ALLTOOLS_URI . 'assets/js/media-engine.js',
            array(),
            ALLTOOLS_VERSION,
            true
        );
        wp_localize_script( 'alltools-media-engine', 'UFX_MEDIA', array(
            'base' => ALLTOOLS_URI . 'assets/vendor/ffmpeg/',
        ) );
    }

    if ( $needs_extra ) {
        wp_enqueue_script(
            'alltools-tools-extra-js',
            ALLTOOLS_URI . 'assets/js/tools-extra.js',
            array( 'alltools-main-js', 'alltools-media-engine' ),
            ALLTOOLS_VERSION,
            true
        );
    }

    if ( $needs_growth ) {
        $growth_deps = array( 'alltools-main-js', 'alltools-media-engine' );
        if ( function_exists( 'alltools_should_load_font_asset' ) && alltools_should_load_font_asset() ) {
            wp_enqueue_script(
                'alltools-fonteditor',
                ALLTOOLS_URI . 'assets/js/fonteditor-core.min.js',
                array(),
                ALLTOOLS_VERSION,
                true
            );
            $growth_deps[] = 'alltools-fonteditor';
        }

        wp_enqueue_script(
            'alltools-tools-growth-js',
            ALLTOOLS_URI . 'assets/js/tools-growth.js',
            $growth_deps,
            ALLTOOLS_VERSION,
            true
        );
        wp_localize_script( 'alltools-tools-growth-js', 'UFX_GROWTH', array(
            'fontWasm' => ALLTOOLS_URI . 'assets/js/woff2.wasm',
        ) );
    }

    if ( $needs_seo ) {
        wp_enqueue_script(
            'alltools-tools-seo-js',
            ALLTOOLS_URI . 'assets/js/tools-seo.js',
            array( 'alltools-main-js' ),
            ALLTOOLS_VERSION,
            true
        );
    }
}

/** Admin styles. */
add_action( 'admin_enqueue_scripts', 'alltools_admin_enqueue' );
function alltools_admin_enqueue( $hook ) {
    if ( strpos( (string) $hook, 'alltools' ) !== false ) {
        wp_enqueue_style(
            'alltools-admin',
            ALLTOOLS_URI . 'assets/css/admin.css',
            array(),
            ALLTOOLS_VERSION
        );
    }
}
