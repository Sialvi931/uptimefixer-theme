<?php
/**
 * Search results template.
 * @package UptimeFixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$q = get_search_query();
$all_tools = alltools_public_tools();
$tool_results = array();
foreach ( $all_tools as $slug => $t ) {
    if ( ! $q || stripos( $t['title'], $q ) !== false || stripos( $t['desc'], $q ) !== false ) {
        $tool_results[ $slug ] = $t;
    }
}
?>

<?php alltools_inner_title_bar( sprintf( __( 'Search: "%s"', 'alltools' ), $q ) ); ?>

<section class="at-section">
    <div class="at-container">
        <p class="at-help"><?php printf( esc_html__( '%d tools matched your search.', 'alltools' ), count( $tool_results ) ); ?></p>
        <?php if ( $tool_results ) : ?>
            <div class="at-grid-5">
                <?php foreach ( $tool_results as $slug => $tool ) :
                    $c = alltools_color( $tool['color'] );
                    $url = alltools_tool_url( $slug );
                ?>
                <article class="at-tool-card">
                    <div class="at-tool-card-top">
                        <div class="at-tool-icon" style="background:<?php echo esc_attr( $c['bg'] ); ?>;color:<?php echo esc_attr( $c['fg'] ); ?>;"><?php echo alltools_icon_svg( $tool['icon'], 18 ); ?></div>
                        <?php if ( ! empty( $tool['badge'] ) ) : ?>
                            <span class="at-tool-badge"><?php echo esc_html( $tool['badge'] ); ?></span>
                        <?php endif; ?>
                    </div>
                    <h3><?php echo esc_html( $tool['title'] ); ?></h3>
                    <p><?php echo esc_html( $tool['desc'] ); ?></p>
                    <a href="<?php echo esc_url( $url ); ?>" class="at-tool-cta" style="color:<?php echo esc_attr( $c['fg'] ); ?>;border-color:<?php echo esc_attr( $c['fg'] ); ?>;"><?php echo esc_html( $tool['cta'] ); ?></a>
                </article>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="at-card at-card-pad-lg" style="text-align:center;">
                <p><?php printf( esc_html__( 'No tools found for "%s". Try a different keyword.', 'alltools' ), esc_html( $q ) ); ?></p>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="at-btn at-btn-primary"><?php esc_html_e( '← Back to Home', 'alltools' ); ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer();
