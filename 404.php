<?php
/**
 * 404 page.
 * @package UptimeFixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<?php alltools_inner_title_bar( __( 'Page Not Found', 'alltools' ) ); ?>

<section class="at-section at-404">
    <div class="at-container at-404-inner">
        <div class="at-404-graphic">
            <svg viewBox="0 0 360 200" width="280" height="180" xmlns="http://www.w3.org/2000/svg">
                <text x="180" y="140" text-anchor="middle" font-size="120" font-weight="900" fill="#EDE9FE" font-family="system-ui">404</text>
                <text x="180" y="138" text-anchor="middle" font-size="120" font-weight="900" fill="#8B5CF6" opacity="0.2" font-family="system-ui">404</text>
            </svg>
        </div>
        <p><?php esc_html_e( "Oops! We can't find the page you're looking for.", 'alltools' ); ?></p>

        <div style="margin-top:48px;width:100%;">
            <h2><?php esc_html_e( 'Try a Tool', 'alltools' ); ?></h2>
            <div class="at-grid-3">
            <?php
            $tools = array_slice( alltools_public_tools(), 0, 3, true );
            foreach ( $tools as $slug => $tool ) :
                $c = alltools_color( $tool['color'] );
                $url = alltools_tool_url( $slug );
            ?>
                <article class="at-tool-card">
                    <div class="at-tool-card-top">
                        <div class="at-tool-icon" style="background:<?php echo esc_attr( $c['bg'] ); ?>;color:<?php echo esc_attr( $c['fg'] ); ?>;"><?php echo alltools_icon_svg( $tool['icon'], 20 ); ?></div>
                    </div>
                    <h3><?php echo esc_html( $tool['title'] ); ?></h3>
                    <p><?php echo esc_html( $tool['desc'] ); ?></p>
                    <a href="<?php echo esc_url( $url ); ?>" class="at-tool-cta" style="color:<?php echo esc_attr( $c['fg'] ); ?>;border-color:<?php echo esc_attr( $c['fg'] ); ?>;"><?php echo esc_html( $tool['cta'] ); ?></a>
                </article>
            <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php get_footer();
