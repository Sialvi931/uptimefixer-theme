<?php
/**
 * Theme setup.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'after_setup_theme', 'alltools_setup' );
function alltools_setup() {
    load_theme_textdomain( 'alltools', get_template_directory() . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'custom-logo', array(
        'height'      => 40,
        'width'       => 160,
        'flex-height' => true,
        'flex-width'  => true,
    ) );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
    add_theme_support( 'responsive-embeds' );

    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'alltools' ),
        'footer'  => __( 'Footer Menu', 'alltools' ),
    ) );
}

/**
 * Default primary menu items if no menu is assigned.
 */
function alltools_default_menu() {
    $items = array(
        array( 'label' => 'Home',          'url' => home_url( '/' ) ),
        array( 'label' => 'Calculators',    'url' => alltools_category_url( 'calculators' ),     'sub' => 'calculators' ),
        array( 'label' => 'Image Tools',    'url' => alltools_category_url( 'image-tools' ),     'sub' => 'image-tools' ),
        array( 'label' => 'PDF Tools',      'url' => alltools_category_url( 'pdf-tools' ),       'sub' => 'pdf-tools' ),
        array( 'label' => 'Video Tools',    'url' => alltools_category_url( 'video-tools' ),     'sub' => 'video-tools' ),
        array( 'label' => 'Audio Tools',    'url' => alltools_category_url( 'audio-tools' ),     'sub' => 'audio-tools' ),
        array( 'label' => 'Text Tools',     'url' => alltools_category_url( 'text-tools' ),      'sub' => 'text-tools' ),
        array( 'label' => 'Developer Tools','url' => alltools_category_url( 'developer-tools' ), 'sub' => 'developer-tools' ),
        array( 'label' => 'Website Tools',  'url' => alltools_category_url( 'website-tools' ),   'sub' => 'website-tools' ),
        array( 'label' => 'Business Tools', 'url' => alltools_category_url( 'business-tools' ),  'sub' => 'business-tools' ),
        array( 'label' => 'Marketing Tools','url' => alltools_category_url( 'marketing-tools' ), 'sub' => 'marketing-tools' ),
        array( 'label' => 'Other Tools',    'url' => alltools_category_url( 'other-tools' ),     'sub' => 'other-tools' ),
        array( 'label' => 'Blog',          'url' => home_url( '/blog/' ) ),
        array( 'label' => 'About',         'url' => home_url( '/about/' ) ),
        array( 'label' => 'Contact',       'url' => home_url( '/contact/' ) ),
    );
    return array_values( array_filter( $items, function( $item ) {
        if ( 'Blog' === $item['label'] && ! alltools_reviewed_post_count() ) return false;
        return empty( $item['sub'] ) || alltools_public_tool_count_in_cat( $item['sub'] ) > 0;
    } ) );
}

/**
 * Add dark mode class to body if cookie is set.
 */
add_filter( 'body_class', 'alltools_body_class' );
function alltools_body_class( $classes ) {
    if ( isset( $_COOKIE['alltools_dark'] ) && $_COOKIE['alltools_dark'] === '1' ) {
        $classes[] = 'at-dark';
    } else {
        $classes[] = 'at-light';
    }
    return $classes;
}

/**
 * Pretty permalinks for the alltool CPT.
 */
add_action( 'admin_init', 'alltools_rewrite_flush', 99 );
function alltools_rewrite_flush() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    if ( get_option( 'alltools_flushed_v1' ) !== '1' ) {
        flush_rewrite_rules( false );
        update_option( 'alltools_flushed_v1', '1' );
    }
}


/**
 * Create a Blog page automatically and assign the Blog template.
 * This does not change the homepage; it only ensures /blog/ exists.
 */
add_action( 'admin_init', 'alltools_ensure_blog_page' );
function alltools_ensure_blog_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    if ( get_option( 'alltools_blog_page_ready' ) ) {
        return;
    }

    $blog = get_page_by_path( 'blog' );

    if ( ! $blog ) {
        $blog_id = wp_insert_post( array(
            'post_title'   => 'Blog',
            'post_name'    => 'blog',
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_content' => '',
        ) );
    } else {
        update_option( 'alltools_blog_page_ready', 1 );
        return; // Preserve the editor's existing Blog page template.
    }

    if ( $blog_id && ! is_wp_error( $blog_id ) ) {
        update_post_meta( $blog_id, '_wp_page_template', 'template-blog.php' );
        update_option( 'alltools_blog_page_ready', 1 );
    }
}
