<?php
/**
 * Blog posts index.
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
    <?php alltools_inner_title_bar( single_post_title( '', false ) ? single_post_title( '', false ) : 'Blog' ); ?>

    <section class="at-blog-section">
        <div class="at-container">
            <div class="at-blog-section-head">
                <div>
                    <span class="at-blog-kicker">Fresh from the blog</span>
                    <h2>Latest articles</h2>
                </div>
                <p>Actionable insights, without the unnecessary complexity.</p>
            </div>

            <?php if ( have_posts() ) : ?>
                <div class="at-blog-grid" aria-label="Latest blog articles">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php
                        $categories   = get_the_category();
                        $category     = ! empty( $categories ) ? $categories[0]->name : 'Insights';
                        $reading_time = max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 220 ) );
                        ?>
                        <article <?php post_class( 'at-blog-card' ); ?>>
                            <a class="at-blog-card-media" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( sprintf( 'Read %s', get_the_title() ) ); ?>">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'medium_large', array( 'class' => 'at-blog-card-image', 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
                                <?php else : ?>
                                    <span class="at-blog-card-placeholder" aria-hidden="true"><?php echo alltools_icon_svg( 'word', 54 ); ?></span>
                                <?php endif; ?>
                                <span class="at-blog-category"><?php echo esc_html( $category ); ?></span>
                            </a>

                            <div class="at-blog-card-body">
                                <div class="at-blog-card-meta">
                                    <span><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
                                    <span class="at-blog-meta-dot" aria-hidden="true"></span>
                                    <span><?php echo esc_html( $reading_time ); ?> min read</span>
                                </div>
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
                                <a class="at-blog-read-more" href="<?php the_permalink(); ?>">
                                    Read article
                                    <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <div class="at-blog-pagination">
                    <?php
                    the_posts_pagination( array(
                        'mid_size'  => 1,
                        'prev_text' => '← Previous',
                        'next_text' => 'Next →',
                    ) );
                    ?>
                </div>
            <?php else : ?>
                <div class="at-blog-empty">
                    <span aria-hidden="true"><?php echo alltools_icon_svg( 'word', 42 ); ?></span>
                    <h2>No posts yet</h2>
                    <p>Published articles will appear here unless an editor explicitly holds them back from public indexing.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php get_footer(); ?>
