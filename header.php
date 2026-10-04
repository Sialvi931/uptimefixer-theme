<?php
/**
 * Header
 * @package UptimeFixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="theme-color" content="#2563EB">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="at-skip" href="#at-main"><?php esc_html_e( 'Skip to content', 'alltools' ); ?></a>

<header class="at-header" id="at-header">
    <div class="at-container at-header-inner">

        <!-- Logo -->
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="at-logo" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
            <?php echo alltools_logo_output(); ?>
        </a>

        <!-- Clean desktop nav -->
        <?php
        $alltools_menu_items = alltools_default_menu();
        $alltools_tool_items = array_filter( $alltools_menu_items, function( $item ) {
            return ! empty( $item['sub'] );
        } );
        ?>
        <nav class="at-nav at-nav-clean" aria-label="Primary">
            <ul>
                <li><a href="<?php echo esc_url( get_post_type_archive_link( 'alltool' ) ); ?>">All tools</a></li>
                <li class="at-has-mega <?php echo ( is_tax( 'alltool_category' ) || is_post_type_archive( 'alltool' ) ) ? 'is-active' : ''; ?>">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>#categories">
                        <?php esc_html_e( 'Collections', 'alltools' ); ?>
                        <span class="at-caret">▾</span>
                    </a>
                    <div class="at-mega-menu at-simple-tools-menu">
                        <div class="at-mega-title">
                            <strong><?php esc_html_e( 'Browse Categories', 'alltools' ); ?></strong>
                            <span><?php esc_html_e( 'Choose a tool category', 'alltools' ); ?></span>
                        </div>
                        <?php
                        $alltools_menu_icons = array(
                            'calculators'     => 'calculator',
                            'image-tools'     => 'image',
                            'pdf-tools'       => 'download',
                            'video-tools'     => 'image',
                            'audio-tools'     => 'pulse',
                            'text-tools'      => 'char',
                            'developer-tools' => 'code',
                            'website-tools'   => 'globe',
                            'business-tools'  => 'bank',
                            'marketing-tools' => 'link',
                            'other-tools'     => 'grid',
                        );
                        ?>
                        <div class="at-mega-grid">
                            <?php foreach ( $alltools_tool_items as $item ) : ?>
                                <?php $icon_name = isset( $alltools_menu_icons[ $item['sub'] ] ) ? $alltools_menu_icons[ $item['sub'] ] : 'grid'; ?>
                                <a href="<?php echo esc_url( $item['url'] ); ?>">
                                    <span class="at-mega-icon"><?php echo alltools_icon_svg( $icon_name, 20 ); ?></span>
                                    <span class="at-mega-label"><?php echo esc_html( $item['label'] ); ?></span>
                                    <em><?php echo alltools_icon_svg( 'arrow', 14 ); ?></em>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <a class="at-mega-all" href="<?php echo esc_url( get_post_type_archive_link( 'alltool' ) ); ?>">
                            <?php esc_html_e( 'View all tools', 'alltools' ); ?>
                            <?php echo alltools_icon_svg( 'arrow', 15 ); ?>
                        </a>
                    </div>
                </li>
                <li class="<?php echo ( is_home() || is_singular( 'post' ) || is_page( 'blog' ) ) ? 'is-active' : ''; ?>"><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Guides', 'alltools' ); ?></a></li>
                <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About', 'alltools' ); ?></a></li>
                <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'alltools' ); ?></a></li>
            </ul>
        </nav>

        <!-- Right side -->
        <div class="at-header-right">
            <details class="ufx-header-search"><summary aria-label="Search tools"><?php echo alltools_icon_svg('search',20); ?></summary><form role="search" action="<?php echo esc_url(home_url('/')); ?>"><label for="ufx-header-query">Find a tool</label><input id="ufx-header-query" name="s" type="search" placeholder="Search tools…" required><input type="hidden" name="post_type" value="alltool"><button type="submit">Search</button></form></details>
            <?php if ( true ) : ?>
                <a class="at-header-cta" href="<?php echo esc_url( get_post_type_archive_link( 'alltool' ) ); ?>"><?php echo alltools_icon_svg( 'search', 16 ); ?><span><?php esc_html_e( 'Explore Tools', 'alltools' ); ?></span></a>
            <?php endif; ?>
            <button id="at-dark-toggle" class="at-dark-toggle" aria-label="Toggle dark mode" type="button">
                <span class="at-dark-icon"><?php echo alltools_icon_svg( 'moon', 20 ); ?></span>
            </button>
            <button id="at-menu-toggle" class="at-menu-toggle" aria-label="Open menu" type="button">
                <?php echo alltools_icon_svg( 'menu', 24 ); ?>
            </button>
        </div>
    </div>

    <!-- Mobile offcanvas drawer -->
    <div id="at-mobile-overlay" class="at-mobile-overlay" aria-hidden="true"></div>
    <aside id="at-mobile-nav" class="at-mobile-nav" aria-hidden="true" role="dialog" aria-label="Mobile menu">
        <div class="at-mobile-nav-head">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="at-mobile-logo">
                <?php echo alltools_logo_output(); ?>
            </a>
            <button id="at-menu-close" class="at-menu-close" aria-label="Close menu" type="button">×</button>
        </div>
        <div class="at-mobile-nav-scroll">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'alltools' ); ?></a>
            <a href="<?php echo esc_url( get_post_type_archive_link( 'alltool' ) ); ?>"><?php esc_html_e( 'Explore All Tools', 'alltools' ); ?></a>
            <div class="at-mobile-section-title"><?php esc_html_e( 'Tool Categories', 'alltools' ); ?></div>
            <?php foreach ( alltools_default_menu() as $item ) : ?>
                <?php if ( ! empty( $item['sub'] ) ) : ?>
                    <a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
            <div class="at-mobile-section-title"><?php esc_html_e( 'Pages', 'alltools' ); ?></div>
            <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Guides', 'alltools' ); ?></a>
            <a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About', 'alltools' ); ?></a>
            <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'alltools' ); ?></a>
        </div>
    </aside>
</header>

<main id="at-main" class="at-main">
