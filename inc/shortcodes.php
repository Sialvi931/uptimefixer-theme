<?php
/**
 * Shortcodes.
 * @package UptimeFixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * [alltool slug="age-calculator"] — render a tool's HTML interface.
 */
function alltools_shortcode_tool( $atts ) {
    $atts = shortcode_atts( array( 'slug' => '' ), $atts, 'alltool' );
    $slug = sanitize_key( $atts['slug'] );
    if ( ! $slug ) return '';
    $fn = 'alltools_tool_' . str_replace( '-', '_', $slug );
    ob_start();
    if ( function_exists( $fn ) ) {
        call_user_func( $fn );
    } else {
        $rendered = apply_filters( 'alltools_render_tool', false, $slug );
        if ( ! $rendered ) {
            ob_end_clean();
            return '';
        }
    }
    return ob_get_clean();
}
add_shortcode( 'alltool', 'alltools_shortcode_tool' );

/**
 * [alltools_grid category="text-tools"] — render a tool grid.
 */
function alltools_shortcode_grid( $atts ) {
    $atts = shortcode_atts( array( 'category' => '' ), $atts, 'alltools_grid' );
    $cat  = sanitize_key( $atts['category'] );
    $tools = alltools_public_tools( $cat );
    if ( ! $tools ) return '';
    ob_start();
    echo '<div class="at-grid-5">';
    foreach ( $tools as $slug => $tool ) {
        $c   = alltools_color( $tool['color'] );
        $url = alltools_tool_url( $slug );
        ?>
        <article class="at-tool-card">
            <div class="at-tool-card-top">
                <div class="at-tool-icon" style="background:<?php echo esc_attr( $c['bg'] ); ?>;color:<?php echo esc_attr( $c['fg'] ); ?>;"><?php echo alltools_icon_svg( $tool['icon'], 18 ); ?></div>
                <?php if ( ! empty( $tool['badge'] ) ) : ?><span class="at-tool-badge"><?php echo esc_html( $tool['badge'] ); ?></span><?php endif; ?>
            </div>
            <h3><?php echo esc_html( $tool['title'] ); ?></h3>
            <p><?php echo esc_html( $tool['desc'] ); ?></p>
            <a href="<?php echo esc_url( $url ); ?>" class="at-tool-cta" style="color:<?php echo esc_attr( $c['fg'] ); ?>;border-color:<?php echo esc_attr( $c['fg'] ); ?>;"><?php echo esc_html( $tool['cta'] ); ?></a>
        </article>
        <?php
    }
    echo '</div>';
    return ob_get_clean();
}
add_shortcode( 'alltools_grid', 'alltools_shortcode_grid' );
