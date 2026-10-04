<?php
/**
 * Basic SEO tags.
 * @package UptimeFixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Return a validated GTM container ID or an empty string. */
function alltools_gtm_id() {
    $id = strtoupper( trim( (string) alltools_get( 'gtm_id', '' ) ) );
    return preg_match( '/^GTM-[A-Z0-9]+$/', $id ) ? $id : '';
}

/** Official Google Tag Manager web-container script. */
add_action( 'wp_head', 'alltools_gtm_head', 0 );
function alltools_gtm_head() {
    $id = alltools_gtm_id();
    if ( ! $id || is_admin() ) return;
    ?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo esc_js( $id ); ?>');</script>
<!-- End Google Tag Manager -->
    <?php
}

/** Official GTM no-script fallback, immediately after the opening body tag. */
add_action( 'wp_body_open', 'alltools_gtm_body', 0 );
function alltools_gtm_body() {
    $id = alltools_gtm_id();
    if ( ! $id || is_admin() ) return;
    ?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( $id ); ?>" height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
    <?php
}

add_action( 'wp_head', 'alltools_seo_meta', 1 );
function alltools_seo_meta() {
    if ( function_exists( 'alltools_yoast_is_active' ) && alltools_yoast_is_active() ) return;
    if ( ! is_singular() && ! is_front_page() && ! is_post_type_archive( 'alltool' ) && ! is_tax( 'alltool_category' ) ) return;

    $desc = '';
    $title = wp_get_document_title();

    if ( is_front_page() ) {
        $desc = alltools_home( 'hero_sub', 'Free online tools for calculations, conversions, images, text, and more.' );
    } elseif ( is_post_type_archive( 'alltool' ) || is_tax( 'alltool_category' ) ) {
        $desc = function_exists( 'alltools_directory_seo_description' ) ? alltools_directory_seo_description() : '';
    } elseif ( is_singular( 'alltool' ) ) {
        $slug = get_post_meta( get_the_ID(), '_alltool_slug', true );
        $tool = alltools_get_tool( $slug );
        if ( $tool ) {
            $desc = alltools_tool_meta_description( $tool, $slug );
        }
    } elseif ( is_singular() ) {
        $excerpt = get_the_excerpt();
        $desc = $excerpt ? $excerpt : '';
    }

    if ( ! $desc ) {
        $desc = get_bloginfo( 'description' );
    }

    $desc = alltools_seo_trim_text( $desc, 155 );

    echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
    $canonical_url = is_singular() ? get_permalink() : ( ( is_post_type_archive( 'alltool' ) || is_tax( 'alltool_category' ) ) ? get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ) ) : home_url() );
    echo '<meta property="og:url" content="' . esc_url( $canonical_url ) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
}

/**
 * Google Analytics from settings.
 */
add_action( 'wp_head', 'alltools_ga' );
function alltools_ga() {
    $id = trim( (string) alltools_get( 'ga_id', '' ) );
    if ( ! $id ) return;
    if ( alltools_gtm_id() ) return;
    if ( defined( 'GOOGLESITEKIT_VERSION' ) || class_exists( 'Google\\Site_Kit\\Plugin' ) ) return;
    $id = preg_replace( '/[^A-Z0-9\-]/i', '', $id );
    if ( ! $id ) return;
    ?>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $id ); ?>"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?php echo esc_js( $id ); ?>');
    </script>
    <?php
}

/**
 * JSON-LD schema for tool pages.
 */
add_action( 'wp_head', 'alltools_jsonld' );
function alltools_jsonld() {
    if ( function_exists( 'alltools_yoast_is_active' ) && alltools_yoast_is_active() ) return;
    if ( is_post_type_archive( 'alltool' ) || is_tax( 'alltool_category' ) ) {
        global $wp_query;
        if ( ! $wp_query || empty( $wp_query->posts ) ) return;
        $url = get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ) );
        $offset = ( max( 1, (int) get_query_var( 'paged' ) ) - 1 ) * (int) $wp_query->get( 'posts_per_page' );
        $items = array();
        foreach ( $wp_query->posts as $index => $post ) {
            $slug = sanitize_key( get_post_meta( $post->ID, '_alltool_slug', true ) );
            $tool = alltools_get_tool( $slug );
            if ( ! $tool ) continue;
            $items[] = array( '@type' => 'ListItem', 'position' => $offset + $index + 1, 'url' => get_permalink( $post ), 'name' => $tool['title'] );
        }
        $data = array(
            '@context'        => 'https://schema.org',
            '@type'           => 'ItemList',
            '@id'             => $url . '#tool-list',
            'url'             => $url,
            'name'            => is_tax( 'alltool_category' ) ? single_term_title( '', false ) : __( 'All Online Tools', 'alltools' ),
            'numberOfItems'   => count( $items ),
            'itemListElement' => $items,
        );
        echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
        return;
    }
    if ( ! is_singular( 'alltool' ) ) return;
    if ( ! alltools_is_tool_index_ready( get_queried_object_id() ) ) return;
    $slug = get_post_meta( get_the_ID(), '_alltool_slug', true );
    $tool = alltools_get_tool( $slug );
    if ( ! $tool ) return;
    $url = get_permalink();
    $profile = alltools_tool_seo_profile( $slug, $tool );
    $category = ! empty( $tool['category'] ) ? $tool['category'] : 'other-tools';
    $quality = alltools_tool_quality_profile( $slug, $tool );
    $application = array(
        '@type'               => 'WebApplication',
        '@id'                 => $url . '#webapplication',
        'name'                => ! empty( $tool['title'] ) ? $tool['title'] : get_the_title(),
        'description'         => $profile['description'],
        'applicationCategory' => alltools_schema_application_category( $category ),
        'operatingSystem'     => 'Any',
        'browserRequirements' => 'Requires JavaScript and a modern web browser.',
        'inLanguage'          => get_bloginfo( 'language' ),
        'url'                 => $url,
        'mainEntityOfPage'    => array( '@id' => $url ),
        'isAccessibleForFree' => true,
        'offers'              => array(
            '@type'        => 'Offer',
            'price'        => '0',
            'priceCurrency' => 'USD',
        ),
        'featureList'         => array_values( $quality['use_cases'] ),
    );
    $graph = array( $application );
    $data = array( '@context' => 'https://schema.org', '@graph' => $graph );
    echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
}
