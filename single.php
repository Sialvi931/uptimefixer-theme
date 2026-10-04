<?php
/**
 * Single blog post template with visible editorial and author context.
 *
 * @package UptimeFixer
 */

wp_enqueue_style(
    'alltools-blog',
    ALLTOOLS_URI . 'assets/css/blog.css',
    array( 'alltools-main' ),
    ALLTOOLS_VERSION
);

get_header();
?>
<main id="primary" class="site-main">
    <?php while ( have_posts() ) : the_post(); ?>
        <?php
        $post_id       = get_the_ID();
        $categories    = get_the_category();
        $category_name = ! empty( $categories ) ? $categories[0]->name : __( 'Guide', 'alltools' );
        $reading_time  = max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 220 ) );
        $author_id     = (int) get_the_author_meta( 'ID' );
        $author_name   = get_the_author_meta( 'display_name', $author_id );
        $author_bio    = trim( wp_strip_all_tags( get_the_author_meta( 'description', $author_id ) ) );
        ?>
        <?php alltools_inner_title_bar( get_the_title() ); ?>

        <section class="at-section at-single-post-section">
            <div class="at-container at-single-post-wrap">
                <div class="ufx-post-meta-bar">
                    <strong><?php echo esc_html( $category_name ); ?></strong>
                    <span><?php printf( esc_html__( 'By %s', 'alltools' ), esc_html( $author_name ) ); ?></span>
                    <span aria-hidden="true">•</span>
                    <time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></time>
                    <span aria-hidden="true">•</span>
                    <span><?php printf( esc_html__( '%s min read', 'alltools' ), esc_html( $reading_time ) ); ?></span>
                    <?php if ( get_the_modified_time( 'U' ) > get_the_time( 'U' ) + DAY_IN_SECONDS ) : ?>
                        <span aria-hidden="true">•</span>
                        <span><?php printf( esc_html__( 'Updated %s', 'alltools' ), esc_html( get_the_modified_date( 'M j, Y' ) ) ); ?></span>
                    <?php endif; ?>
                </div>
                <?php if ( has_post_thumbnail() ) : ?>
                    <figure class="at-single-featured-image"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'decoding' => 'async', 'fetchpriority' => 'high' ) ); ?></figure>
                <?php endif; ?>

                <article <?php post_class( 'at-card at-post-content' ); ?>>
                    <?php the_content(); ?>
                    <?php wp_link_pages(); ?>
                </article>

                <aside class="at-card at-author-box" aria-label="<?php esc_attr_e( 'About the author', 'alltools' ); ?>">
                    <div class="at-author-avatar"><?php echo get_avatar( $author_id, 72, '', $author_name ); ?></div>
                    <div>
                        <span class="at-quality-kicker"><?php esc_html_e( 'About the author', 'alltools' ); ?></span>
                        <h2><?php echo esc_html( $author_name ); ?></h2>
                        <?php if ( $author_bio ) : ?>
                            <p><?php echo esc_html( $author_bio ); ?></p>
                        <?php else : ?>
                            <p><?php printf( esc_html__( '%s publishes practical guides for Uptime Fixer. Learn how the site approaches tools, privacy and result verification on the About page.', 'alltools' ), esc_html( $author_name ) ); ?></p>
                        <?php endif; ?>
                        <a class="at-link-arrow" href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About Uptime Fixer', 'alltools' ); ?> →</a>
                    </div>
                </aside>
            </div>
        </section>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
