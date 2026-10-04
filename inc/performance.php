<?php
/**
 * Lightweight performance improvements for PageSpeed Insights.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return every [alltool] slug rendered on the current singular page.
 *
 * This keeps asset decisions accurate for both native single-tool pages and
 * ordinary pages that embed one or more tools with the shortcode.
 *
 * @return string[]
 */
function alltools_page_tool_slugs() {
    static $slugs = null;
    if ( null !== $slugs ) {
        return $slugs;
    }

    $slugs = array();
    if ( ! function_exists( 'is_singular' ) || ! is_singular() ) {
        return $slugs;
    }

    if ( is_singular( 'alltool' ) ) {
        $slug = sanitize_key( (string) get_post_meta( get_queried_object_id(), '_alltool_slug', true ) );
        if ( $slug ) {
            $slugs[] = $slug;
        }
    }

    $post = get_post();
    if ( $post instanceof WP_Post && has_shortcode( (string) $post->post_content, 'alltool' ) ) {
        $pattern = get_shortcode_regex( array( 'alltool' ) );
        if ( preg_match_all( '/' . $pattern . '/s', (string) $post->post_content, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                if ( '[' === $match[1] && ']' === $match[6] ) {
                    continue;
                }
                $atts = shortcode_parse_atts( $match[3] );
                if ( is_array( $atts ) && ! empty( $atts['slug'] ) ) {
                    $slugs[] = sanitize_key( $atts['slug'] );
                }
            }
        }
    }

    $slugs = array_values( array_unique( array_filter( $slugs ) ) );
    return $slugs;
}

/**
 * Identify which implementation bundle owns a tool slug.
 *
 * The companion plugin has its own renderer and JavaScript, so theme tool
 * bundles must not be downloaded on those pages. Theme extension bundles are
 * independent and can likewise be loaded without pulling every other bundle.
 */
function alltools_tool_asset_group( $slug ) {
    $slug = sanitize_key( $slug );
    if ( ! $slug ) {
        return '';
    }

    if ( 'website-image-extractor' === $slug ) {
        return 'plugin';
    }

    if ( function_exists( 'ufxots_tool_configs' ) ) {
        $plugin_tools = ufxots_tool_configs();
        if ( isset( $plugin_tools[ $slug ] ) ) {
            return 'plugin';
        }
    }

    if ( function_exists( 'alltools_seo_suite_definitions' ) ) {
        $seo_tools = alltools_seo_suite_definitions();
        if ( isset( $seo_tools[ $slug ] ) ) {
            return 'seo';
        }
    }

    if ( function_exists( 'alltools_growth_definitions' ) ) {
        $growth_tools = alltools_growth_definitions();
        if ( isset( $growth_tools[ $slug ] ) ) {
            return 'growth';
        }
    }

    if ( function_exists( 'alltools_extra_definitions' ) ) {
        $extra_tools = alltools_extra_definitions();
        if ( isset( $extra_tools[ $slug ] ) ) {
            return 'extra';
        }
    }

    return 'base';
}

/** @return string[] */
function alltools_page_tool_asset_groups() {
    $groups = array();
    foreach ( alltools_page_tool_slugs() as $slug ) {
        $group = alltools_tool_asset_group( $slug );
        if ( $group ) {
            $groups[] = $group;
        }
    }
    return array_values( array_unique( $groups ) );
}

/** Decide when any theme tool implementation bundle is needed. */
function alltools_should_load_tools_assets() {
    return (bool) array_intersect( alltools_page_tool_asset_groups(), array( 'base', 'extra', 'growth', 'seo' ) );
}

/** Decide when one independent theme tool implementation bundle is needed. */
function alltools_should_load_tool_group( $group ) {
    return in_array( sanitize_key( $group ), alltools_page_tool_asset_groups(), true );
}

/** Decide when the QR library is needed. */
function alltools_should_load_qr_asset() {
    return in_array( 'qr-code-generator', alltools_page_tool_slugs(), true );
}

/** Load the local font engine only for the font converter. */
function alltools_should_load_font_asset() {
    return in_array( 'font-converter', alltools_page_tool_slugs(), true );
}

/**
 * Remove default WordPress front-end assets that are not used by this custom tool theme.
 */
add_action( 'init', 'alltools_disable_unused_wp_frontend_assets' );
function alltools_disable_unused_wp_frontend_assets() {
    if ( is_admin() ) {
        return;
    }

    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}

add_action( 'wp_enqueue_scripts', 'alltools_dequeue_unused_wp_styles', 100 );
function alltools_dequeue_unused_wp_styles() {
    if ( is_admin() ) {
        return;
    }

    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'global-styles' );
    wp_dequeue_style( 'classic-theme-styles' );
}

/**
 * Small critical CSS for first paint. The full stylesheet is still loaded after it.
 */
add_action( 'wp_head', 'alltools_print_critical_css', 1 );
function alltools_print_critical_css() {
    if ( is_admin() ) {
        return;
    }

    $primary = sanitize_hex_color( alltools_get( 'primary_color', '#2563EB' ) );
    $accent  = sanitize_hex_color( alltools_get( 'accent_color', '#8B5CF6' ) );
    $primary = $primary ? $primary : '#2563EB';
    $accent  = $accent ? $accent : '#8B5CF6';
    ?>
<style id="alltools-critical-css">
:root{--at-primary:<?php echo esc_html( $primary ); ?>;--at-primary-dark:#1D4ED8;--at-accent:<?php echo esc_html( $accent ); ?>;--at-text:#0F172A;--at-text-muted:#64748B;--at-text-soft:#94A3B8;--at-bg:#fff;--at-bg-alt:#F8FAFC;--at-card:#fff;--at-border:#E2E8F0;--at-radius:12px;--at-shadow:0 1px 2px rgba(15,23,42,.04);--at-container:1200px;--at-pad:24px;--at-font:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif}*{box-sizing:border-box}html{-webkit-text-size-adjust:100%;scroll-behavior:smooth}body{margin:0;font-family:var(--at-font);font-size:15px;line-height:1.6;color:var(--at-text);background:var(--at-bg-alt);-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;overflow-x:hidden}img,svg{max-width:100%;height:auto;display:block}a{color:var(--at-primary);text-decoration:none}h1,h2,h3,h4{margin:0 0 12px;line-height:1.25;color:var(--at-text)}p{margin:0 0 12px}.at-container{width:min(100%,var(--at-container));max-width:var(--at-container);margin:0 auto;padding:0 var(--at-pad)}.at-skip{position:absolute;left:-9999px}.at-main{min-height:60vh;background:var(--at-bg-alt)}.at-header{background:rgba(255,255,255,.94);border-bottom:1px solid var(--at-border);position:sticky;top:0;z-index:100;backdrop-filter:saturate(180%) blur(14px)}.at-header-inner{display:flex;align-items:center;justify-content:space-between;height:64px;gap:16px}.at-logo{display:flex;align-items:center;gap:8px;text-decoration:none;flex-shrink:0}.at-logo-text{font-size:22px;font-weight:800;color:var(--at-text);letter-spacing:-.3px}.at-logo-blue,.at-hero-min-blue{color:var(--at-primary)}.at-custom-logo{display:block;max-width:180px;max-height:42px;width:auto;height:auto;object-fit:contain}.at-nav{display:flex}.at-nav ul{list-style:none;margin:0;padding:0;display:flex;gap:4px}.at-nav li{position:relative}.at-nav a{display:flex;align-items:center;gap:4px;padding:8px 14px;color:var(--at-text);font-weight:500;font-size:14px;border-radius:8px;text-decoration:none;white-space:nowrap}.at-nav a:hover{color:var(--at-primary);background:var(--at-bg-alt);text-decoration:none}.at-submenu{display:none}.at-nav li:hover>.at-submenu{display:block;position:absolute;top:100%;left:0;min-width:240px;background:var(--at-card);border:1px solid var(--at-border);border-radius:12px;padding:8px;box-shadow:0 10px 30px rgba(15,23,42,.08);z-index:50}.at-header-right{display:flex;align-items:center;gap:4px;flex-shrink:0}.at-dark-toggle,.at-menu-toggle{width:40px;height:40px;background:transparent;border:0;border-radius:10px;color:var(--at-text);cursor:pointer;display:flex;align-items:center;justify-content:center}.at-menu-toggle{display:none}.at-mobile-nav{display:none}.at-hero-min{padding:80px 0 56px;text-align:center;background:var(--at-bg)}.at-hero-min-title{font-size:clamp(36px,5.5vw,60px);font-weight:800;line-height:1.1;letter-spacing:-1.5px;margin:0 0 20px;color:var(--at-text)}.at-hero-min-sub{font-size:17px;color:var(--at-text-muted);max-width:600px;margin:0 auto 36px;line-height:1.6}.at-hero-search{position:relative;max-width:640px;margin:0 auto}.at-hero-search-icon{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--at-text-soft);pointer-events:none}.at-hero-search input{width:100%;padding:14px 16px 14px 48px;border:1px solid var(--at-border);background:var(--at-card);color:var(--at-text);border-radius:12px;font-size:15px;font-family:var(--at-font);box-shadow:var(--at-shadow);outline:none}.at-section-soft{background:var(--at-bg);padding:32px 0 64px}.at-section-head-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;gap:16px}.at-section-head-row h2{margin:0;font-size:22px}.at-grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}.at-tool-grid-min{gap:16px}.at-tool-card-min{background:var(--at-card);border:1px solid var(--at-border);border-radius:14px;padding:20px;display:flex;flex-direction:column;align-items:flex-start;text-decoration:none;color:inherit;position:relative;min-height:160px;box-shadow:var(--at-shadow)}.at-tool-card-min-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:14px;flex:0 0 auto}.at-tool-card-min h3{font-size:15px;font-weight:700;margin:0 0 6px;color:var(--at-text)}.at-tool-card-min p{font-size:13px;color:var(--at-text-muted);margin:0 0 16px;line-height:1.5;flex-grow:1}.at-cat-grid-min{display:grid;grid-template-columns:repeat(5,1fr);gap:14px}.at-cat-card-min{display:flex;align-items:center;gap:14px;background:var(--at-card);border:1px solid var(--at-border);border-radius:12px;padding:16px 18px;text-decoration:none;color:var(--at-text);box-shadow:var(--at-shadow)}.at-cat-card-min-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}@media(max-width:1100px){.at-cat-grid-min{grid-template-columns:repeat(3,1fr)}}@media(max-width:900px){.at-nav{display:none}.at-menu-toggle{display:flex}.at-hero-min{padding:56px 0 36px}.at-hero-min-title{font-size:32px}.at-tool-grid-min{grid-template-columns:repeat(2,1fr)}.at-cat-grid-min{grid-template-columns:repeat(2,1fr)}}@media(max-width:600px){:root{--at-pad:16px}.at-tool-grid-min,.at-cat-grid-min{grid-template-columns:1fr}.at-section-head-row{align-items:flex-start;flex-direction:column}.at-hero-min-sub{font-size:15px}}
</style>
    <?php
}

/**
 * Load the full theme CSS asynchronously to reduce render blocking.
 */
add_filter( 'style_loader_tag', 'alltools_async_main_stylesheet', 10, 4 );
function alltools_async_main_stylesheet( $html, $handle, $href, $media ) {
    $async_handles = array( 'alltools-main', 'alltools-poppins' );
    if ( ! in_array( $handle, $async_handles, true ) || is_admin() ) {
        return $html;
    }

    $href  = esc_url( $href );
    $media = $media ? esc_attr( $media ) : 'all';
    $id    = esc_attr( $handle );

    return "<link rel='preload' id='{$id}-css' href='{$href}' as='style' media='{$media}' onload=\"this.onload=null;this.rel='stylesheet'\">\n" .
           "<noscript><link rel='stylesheet' id='{$id}-css-noscript' href='{$href}' media='{$media}'></noscript>\n";
}

/** Establish the font-provider connection only on pages that actually use it. */
add_filter( 'wp_resource_hints', 'alltools_google_font_resource_hints', 10, 2 );
function alltools_google_font_resource_hints( $urls, $relation_type ) {
    if ( 'preconnect' !== $relation_type || is_front_page() ) {
        return $urls;
    }
    $urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous' );
    return $urls;
}

/**
 * Defer non-critical JavaScript. All handles are written to tolerate deferred
 * execution and their WordPress dependency order remains intact.
 */
add_filter( 'script_loader_tag', 'alltools_defer_theme_scripts', 10, 3 );
function alltools_defer_theme_scripts( $tag, $handle, $src ) {
    $defer_handles = array(
        'alltools-main-js', 'alltools-tools-js', 'alltools-tools-extra-js',
        'alltools-tools-growth-js', 'alltools-tools-seo-js', 'alltools-fonteditor',
        'alltools-qrcode', 'alltools-media-engine', 'ufx-studio-controls',
        'ufxie-image-extractor', 'ufxots-suite', 'ufxots-suite-data',
        'ufxots-suite-media', 'ufxots-suite-pdf', 'ufxots-suite-marketing',
        'ufxots-suite-advanced',
    );

    if ( is_admin() || ! in_array( $handle, $defer_handles, true ) ) {
        return $tag;
    }

    if ( false !== strpos( $tag, ' defer' ) ) {
        return $tag;
    }

    return str_replace( '<script ', '<script defer ', $tag );
}

/** Improve image attributes for custom logos and thumbnails. */
add_filter( 'wp_get_attachment_image_attributes', 'alltools_optimize_attachment_image_attributes', 10, 3 );
function alltools_optimize_attachment_image_attributes( $attr, $attachment, $size ) {
    if ( empty( $attr['decoding'] ) ) {
        $attr['decoding'] = 'async';
    }

    if ( ! empty( $attr['class'] ) && false !== strpos( $attr['class'], 'custom-logo' ) ) {
        $attr['loading']       = 'eager';
        $attr['fetchpriority'] = 'high';
    }

    return $attr;
}
