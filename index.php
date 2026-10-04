<?php
/**
 * Fallback index template.
 * @package UptimeFixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<?php alltools_inner_title_bar( is_archive() ? get_the_archive_title() : __( 'Latest Posts', 'alltools' ) ); ?>

<section class="at-section">
    <div class="at-container">
        <?php if ( have_posts() ) : ?>
            <div class="at-grid-3">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article class="at-card">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium' ); ?></a>
                        <?php endif; ?>
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p class="at-help"><?php echo esc_html( get_the_date() ); ?></p>
                        <?php the_excerpt(); ?>
                        <a href="<?php the_permalink(); ?>" class="at-link"><?php esc_html_e( 'Read more →', 'alltools' ); ?></a>
                    </article>
                <?php endwhile; ?>
            </div>
            <?php the_posts_pagination(); ?>
        <?php else : ?>
            <p><?php esc_html_e( 'No content found.', 'alltools' ); ?></p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer();
