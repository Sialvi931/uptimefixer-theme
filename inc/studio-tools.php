<?php
/** Visual system for tool pages and the directory. */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Single clean north-east arrow; never rotate a horizontal arrow. */
function alltools_diagonal_arrow() {
    return '<svg class="ufx-diagonal-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M5 19 19 5M7 5h12v12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

add_filter( 'body_class', function( $classes ) {
    if ( is_singular( 'alltool' ) ) {
        $classes[] = 'ufx-tool-page';
        $slug = sanitize_key( get_post_meta( get_queried_object_id(), '_alltool_slug', true ) );
        $tool = $slug ? alltools_get_tool( $slug ) : array();
        $category = ! empty( $tool['category'] ) ? sanitize_html_class( $tool['category'] ) : 'other-tools';
        $classes[] = 'ufx-family-' . $category;
        $classes[] = 'ufx-tool-' . sanitize_html_class( $slug );
    } elseif ( is_post_type_archive( 'alltool' ) || is_tax( 'alltool_category' ) ) {
        $classes[] = 'ufx-tool-directory';
    }
    return $classes;
} );

add_action( 'wp_enqueue_scripts', function() {
    if ( ! is_singular( 'alltool' ) && ! is_post_type_archive( 'alltool' ) && ! is_tax( 'alltool_category' ) ) return;
    wp_enqueue_style( 'ufx-studio-tools', ALLTOOLS_URI . 'assets/css/studio-tools.css', array( 'alltools-main' ), ALLTOOLS_VERSION );
}, 100 );

add_action('wp_enqueue_scripts', function(){
    wp_enqueue_style('ufx-shell', ALLTOOLS_URI.'assets/css/studio-shell.css', array('alltools-main'), ALLTOOLS_VERSION);
    if(is_singular('alltool')) wp_enqueue_script('ufx-studio-controls', ALLTOOLS_URI.'assets/js/studio-controls.js', array('alltools-main-js'), ALLTOOLS_VERSION, true);
}, 110);
