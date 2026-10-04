<?php
/**
 * Demo importer.
 * @package UptimeFixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function alltools_render_import_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    ?>
    <div class="wrap at-admin">
        <h1 class="at-admin-title">
            <span class="at-admin-logo">Uptime<span>Fixer</span></span>
            <?php esc_html_e( 'Import Demo Content', 'alltools' ); ?>
        </h1>

        <?php if ( isset( $_GET['imported'] ) && $_GET['imported'] === '1' ) : ?>
            <div class="notice notice-success"><p>✅ Demo content imported successfully!</p></div>
        <?php endif; ?>

        <div class="at-import-box">
            <h2>One-Click Demo Setup</h2>
            <p>This will create:</p>
            <ul>
                <li>✓ <?php echo (int) count( alltools_all() ); ?> tool pages, including calculators, PDF, image, video, audio, business, developer and website tools</li>
                <li>✓ Essential pages: Home, About, Contact, Privacy Policy, Terms</li>
                <li>✓ Primary navigation menu</li>
                <li>✓ Default theme settings</li>
                <li>✓ Sets your homepage as the front page</li>
            </ul>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'alltools_import', 'alltools_import_nonce' ); ?>
                <input type="hidden" name="action" value="alltools_import_demo">
                <p>
                    <button type="submit" class="button button-primary button-hero">
                        🚀 Import Demo Content
                    </button>
                </p>
                <p class="description">Safe to run multiple times — existing posts, slugs, content, SEO fields, and statuses are preserved.</p>
            </form>
        </div>
    </div>
    <?php
}

add_action( 'admin_post_alltools_import_demo', 'alltools_run_import' );
function alltools_run_import() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No permission' );
    if ( ! isset( $_POST['alltools_import_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['alltools_import_nonce'] ), 'alltools_import' ) ) wp_die( 'Invalid nonce' );

    // 1. Create tool posts
    $tools = alltools_all();
    foreach ( $tools as $slug => $tool ) {
        // Check if exists
        $existing = get_posts( array(
            'post_type'   => 'alltool',
            'meta_key'    => '_alltool_slug',
            'meta_value'  => $slug,
            'numberposts' => 1,
            'post_status' => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash', 'auto-draft' ),
        ) );
        if ( ! empty( $existing ) ) {
            // Never rewrite an existing post, permalink, status, or editor copy.
            continue;
        }
        // Create
        $post_id = wp_insert_post( array(
            'post_type'    => 'alltool',
            'post_title'   => $tool['title'],
            'post_excerpt' => $tool['desc'],
            'post_content' => $tool['long'],
            'post_status'  => 'publish',
            'post_name'    => $slug,
        ) );
        if ( $post_id && ! is_wp_error( $post_id ) ) {
            update_post_meta( $post_id, '_alltool_slug', $slug );
        }
    }

    // 2. Create essential pages
    $pages = array(
        'home'    => array( 'Home', '' ),
        'about'   => array( 'About', 'About Uptime Fixer — your trusted free tools hub.' ),
        'contact' => array( 'Contact', 'Get in touch with us.' ),
        'privacy-policy' => array( 'Privacy Policy', '<h2>Your Privacy Matters</h2><p>Files are processed in your browser whenever supported and are not intentionally uploaded to this website. Live website diagnostics send only the public URL, domain or IP address required for the selected check through protected site endpoints. The public directory does not require paid AI, SEO or PageSpeed API credentials.</p><h2>Security</h2><p>Requests are validated, rate-limited and restricted to public internet targets. This website does not intentionally retain tool inputs. A public no-key service may be used where the individual tool clearly explains it.</p><h2>Cookies and Analytics</h2><p>A preference cookie may remember dark mode. Analytics may be enabled by the administrator.</p>' ),
        'terms'   => array( 'Terms of Use', '<h2>Acceptance</h2><p>By using Uptime Fixer, you accept these terms.</p><h2>Authorized Use</h2><p>Only inspect websites, servers and files you own or are authorized to test. Automated abuse, unauthorized scanning and private-network access attempts are prohibited.</p><h2>Use of Tools</h2><p>All tools are provided "as is" without warranty. Live diagnostics are point-in-time observations, and browser conversions may simplify complex source formatting. Verify important output before relying on it.</p>' ),
    );

    $home_id = 0;
    foreach ( $pages as $slug => $info ) {
        $existing = get_page_by_path( $slug );
        if ( $existing ) {
            $page_id = $existing->ID;
        } else {
            $page_id = wp_insert_post( array(
                'post_type'    => 'page',
                'post_title'   => $info[0],
                'post_name'    => $slug,
                'post_content' => $info[1],
                'post_status'  => 'publish',
            ) );
        }
        if ( $slug === 'home' && $page_id && ! is_wp_error( $page_id ) ) {
            $home_id = $page_id;
        }
    }

    // 3. Set home as front page
    if ( $home_id ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $home_id );
    }

    // 4. Default settings
    if ( ! get_option( 'alltools_settings' ) ) {
        update_option( 'alltools_settings', array(
            'primary_color'    => '#2563EB',
            'accent_color'     => '#8B5CF6',
            'footer_tagline'   => 'A free collection of online tools to make your daily tasks easier.',
            'footer_disclaimer'=> 'Tools are provided as is. Local processing is used where possible; live public diagnostics are validated, rate-limited, and do not require a paid API key.',
            'footer_rights'    => 'All rights reserved.',
        ) );
    }
    if ( ! get_option( 'alltools_settings_home' ) ) {
        update_option( 'alltools_settings_home', array(
            'hero_title'      => 'All the Tools You Need, All in',
            'hero_title_blue' => 'One Place',
            'hero_sub'        => 'Free online tools for calculations, conversions, images, text, and more. Fast, simple and easy to use.',
        ) );
    }

    flush_rewrite_rules( false );

    wp_safe_redirect( add_query_arg( 'imported', '1', admin_url( 'edit.php?post_type=alltool&page=alltools-import' ) ) );
    exit;
}
