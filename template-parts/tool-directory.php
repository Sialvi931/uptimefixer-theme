<?php
/** Shared archive view. Variables are supplied by the archive/taxonomy templates. */
if ( ! defined( 'ABSPATH' ) ) exit;

add_filter( 'body_class', function( $classes ) {
    $classes[] = 'ufx-tool-directory-page';
    return $classes;
} );

$categories = alltools_tool_categories();
$total = isset( $GLOBALS['wp_query']->found_posts ) ? (int) $GLOBALS['wp_query']->found_posts : 0;
get_header();
?>

<section class="ufx-directory-hero">
    <div class="at-container">
        <nav class="at-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'alltools' ); ?>">
            <ol>
                <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'alltools' ); ?></a></li>
                <li class="at-bc-sep" aria-hidden="true">›</li>
                <?php if ( $directory_category ) : ?>
                    <li><a href="<?php echo esc_url( get_post_type_archive_link( 'alltool' ) ); ?>"><?php esc_html_e( 'All Tools', 'alltools' ); ?></a></li>
                    <li class="at-bc-sep" aria-hidden="true">›</li>
                <?php endif; ?>
                <li aria-current="page"><?php echo esc_html( $directory_title ); ?></li>
            </ol>
        </nav>
        <span class="ufx-directory-kicker"><?php echo esc_html( $directory_kicker ); ?></span>
        <h1><?php echo esc_html( $directory_title ); ?></h1>
        <p><?php echo esc_html( $directory_description ); ?></p>
        <form class="ufx-directory-search" role="search" action="<?php echo esc_url(home_url('/')); ?>"><label class="screen-reader-text" for="ufx-directory-search">Search tools</label><input type="search" id="ufx-directory-search" name="s" placeholder="What do you need to do?" required><input type="hidden" name="post_type" value="alltool"><button type="submit">Search tools <?php echo alltools_diagonal_arrow(); ?></button></form>
        <div class="ufx-directory-count">
            <?php printf( esc_html( _n( '%s published tool', '%s published tools', $total, 'alltools' ) ), esc_html( number_format_i18n( $total ) ) ); ?>
        </div>
    </div>
</section>

<nav class="ufx-directory-categories" aria-label="<?php esc_attr_e( 'Tool categories', 'alltools' ); ?>">
    <div class="at-container">
        <a class="<?php echo $directory_category ? '' : 'is-current'; ?>" href="<?php echo esc_url( get_post_type_archive_link( 'alltool' ) ); ?>"><?php esc_html_e( 'All tools', 'alltools' ); ?></a>
        <?php foreach ( $categories as $category_slug => $category ) : ?>
            <?php if ( ! alltools_public_tool_count_in_cat( $category_slug ) ) continue; ?>
            <a class="<?php echo $directory_category === $category_slug ? 'is-current' : ''; ?>" href="<?php echo esc_url( alltools_category_url( $category_slug ) ); ?>"><?php echo esc_html( $category['label'] ); ?></a>
        <?php endforeach; ?>
    </div>
</nav>

<section class="ufx-home-section ufx-home-directory" id="tools">
    <div class="at-container">
        <?php if ( have_posts() ) : ?>
            <div class="ufx-home-tool-grid ufx-home-tool-grid-all">
                <?php while ( have_posts() ) : the_post();
                    $slug = sanitize_key( get_post_meta( get_the_ID(), '_alltool_slug', true ) );
                    $tool = alltools_get_tool( $slug );
                    if ( ! $tool ) continue;
                    $color = alltools_color( $tool['color'] );
                ?>
                    <a href="<?php the_permalink(); ?>" class="ufx-home-tool-card" data-cat="<?php echo esc_attr( $tool['category'] ); ?>">
                        <span class="ufx-home-tool-icon" style="--tool-bg:<?php echo esc_attr( $color['bg'] ); ?>;--tool-fg:<?php echo esc_attr( $color['fg'] ); ?>;"><?php echo alltools_icon_svg( $tool['icon'], 22 ); ?></span>
                        <h2><?php echo esc_html( $tool['title'] ); ?></h2>
                        <p><?php echo esc_html( $tool['desc'] ); ?></p>
                        <em><?php echo alltools_icon_svg( 'arrow', 16 ); ?></em>
                    </a>
                <?php endwhile; ?>
            </div>
            <nav class="ufx-directory-pagination" aria-label="<?php esc_attr_e( 'Directory pages', 'alltools' ); ?>">
                <?php the_posts_pagination( array(
                    'mid_size'  => 2,
                    'prev_text' => __( 'Previous', 'alltools' ),
                    'next_text' => __( 'Next', 'alltools' ),
                ) ); ?>
            </nav>
        <?php else : ?>
            <div class="at-card at-card-pad-lg">
                <h2><?php esc_html_e( 'No tools found', 'alltools' ); ?></h2>
                <p><?php esc_html_e( 'Return to the complete directory and choose another category.', 'alltools' ); ?></p>
                <a class="at-btn at-btn-primary" href="<?php echo esc_url( get_post_type_archive_link( 'alltool' ) ); ?>"><?php esc_html_e( 'View all tools', 'alltools' ); ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
