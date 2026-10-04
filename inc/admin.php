<?php
/**
 * Admin settings panel.
 * @package UptimeFixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'alltools_admin_menu' );
function alltools_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=alltool',
        __( 'Uptime Fixer Settings', 'alltools' ),
        __( 'Theme Settings', 'alltools' ),
        'manage_options',
        'alltools-settings',
        'alltools_render_settings_page'
    );
    add_submenu_page(
        'edit.php?post_type=alltool',
        __( 'Import Demo', 'alltools' ),
        __( 'Import Demo', 'alltools' ),
        'manage_options',
        'alltools-import',
        'alltools_render_import_page'
    );
}

function alltools_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
    $tabs = array(
        'general'      => __( 'General', 'alltools' ),
        'homepage'     => __( 'Homepage', 'alltools' ),
        'integrations' => __( 'Integrations', 'alltools' ),
        'contact'      => __( 'Contact', 'alltools' ),
        'footer'       => __( 'Footer', 'alltools' ),
    );
    if ( ! isset( $tabs[ $tab ] ) ) $tab = 'general';
    ?>
    <div class="wrap at-admin">
        <h1 class="at-admin-title">
            <span class="at-admin-logo">Uptime<span>Fixer</span></span>
            <?php esc_html_e( 'Theme Settings', 'alltools' ); ?>
        </h1>

        <h2 class="nav-tab-wrapper">
            <?php foreach ( $tabs as $t => $label ) : ?>
                <a href="?post_type=alltool&page=alltools-settings&tab=<?php echo esc_attr( $t ); ?>" class="nav-tab <?php echo $tab === $t ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
            <?php endforeach; ?>
        </h2>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="at-admin-form">
            <?php wp_nonce_field( 'alltools_save_settings', 'alltools_nonce' ); ?>
            <input type="hidden" name="action" value="alltools_save_settings">
            <input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>">

            <?php
            if ( $tab === 'general' ) {
                alltools_render_general_tab();
            } elseif ( $tab === 'homepage' ) {
                alltools_render_homepage_tab();
            } elseif ( $tab === 'integrations' ) {
                alltools_render_integrations_tab();
            } elseif ( $tab === 'contact' ) {
                alltools_render_contact_tab();
            } elseif ( $tab === 'footer' ) {
                alltools_render_footer_tab();
            }
            ?>

            <p class="submit">
                <button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Save Changes', 'alltools' ); ?></button>
            </p>
        </form>
    </div>
    <?php
}

function alltools_render_general_tab() { ?>
    <table class="form-table">
        <tr><th><label for="primary_color">Primary Color</label></th><td><input type="color" id="primary_color" name="primary_color" value="<?php echo esc_attr( alltools_get( 'primary_color', '#2563EB' ) ); ?>"></td></tr>
        <tr><th><label for="accent_color">Accent Color</label></th><td><input type="color" id="accent_color" name="accent_color" value="<?php echo esc_attr( alltools_get( 'accent_color', '#8B5CF6' ) ); ?>"></td></tr>
        <tr><th><label for="gtm_id">Google Tag Manager ID</label></th><td><input type="text" id="gtm_id" name="gtm_id" value="<?php echo esc_attr( alltools_get( 'gtm_id' ) ); ?>" placeholder="GTM-XXXXXXX" class="regular-text"><p class="description">Loads the official web container snippet and privacy-safe tool interaction events. Leave blank if the same container is already installed by Site Kit or another plugin.</p></td></tr>
        <tr><th><label for="ga_id">Google tag / Analytics ID</label></th><td><input type="text" id="ga_id" name="ga_id" value="<?php echo esc_attr( alltools_get( 'ga_id' ) ); ?>" placeholder="G-XXXXXXXXXX" class="regular-text"><p class="description">Optional direct Google tag. When a GTM ID is saved, configure Analytics inside GTM and leave this field blank to prevent duplicate measurement.</p></td></tr>
        <tr><th><label for="adsense_publisher_id">AdSense Publisher ID</label></th><td><input type="text" id="adsense_publisher_id" name="adsense_publisher_id" value="<?php echo esc_attr( alltools_get( 'adsense_publisher_id', 'pub-9901210156464210' ) ); ?>" placeholder="pub-0000000000000000" class="regular-text"><p class="description">Used for the AdSense verification meta tag and the domain-root ads.txt seller record.</p></td></tr>
    </table>
<?php }

/** Encrypt paid-provider credentials before storing them in WordPress options. */
function alltools_encrypt_secret( $value ) {
    $value = trim( (string) $value );
    if ( '' === $value ) return '';
    $key = hash( 'sha256', wp_salt( 'auth' ), true );
    if ( function_exists( 'sodium_crypto_secretbox' ) ) {
        $nonce = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
        return 's1:' . base64_encode( $nonce . sodium_crypto_secretbox( $value, $nonce, $key ) );
    }
    if ( function_exists( 'openssl_encrypt' ) ) {
        $iv = random_bytes( 12 ); $tag = '';
        $cipher = openssl_encrypt( $value, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag );
        return false === $cipher ? '' : 'o1:' . base64_encode( $iv . $tag . $cipher );
    }
    return '';
}

function alltools_decrypt_secret( $stored ) {
    $stored = (string) $stored;
    $key = hash( 'sha256', wp_salt( 'auth' ), true );
    if ( 0 === strpos( $stored, 's1:' ) && function_exists( 'sodium_crypto_secretbox_open' ) ) {
        $raw = base64_decode( substr( $stored, 3 ), true );
        if ( false === $raw || strlen( $raw ) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ) return '';
        $nonce = substr( $raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
        $plain = sodium_crypto_secretbox_open( substr( $raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ), $nonce, $key );
        return false === $plain ? '' : $plain;
    }
    if ( 0 === strpos( $stored, 'o1:' ) && function_exists( 'openssl_decrypt' ) ) {
        $raw = base64_decode( substr( $stored, 3 ), true );
        if ( false === $raw || strlen( $raw ) <= 28 ) return '';
        $plain = openssl_decrypt( substr( $raw, 28 ), 'aes-256-gcm', $key, OPENSSL_RAW_DATA, substr( $raw, 0, 12 ), substr( $raw, 12, 16 ) );
        return false === $plain ? '' : $plain;
    }
    return '';
}

/** Constants in wp-config.php override encrypted database settings. */
function alltools_integration_secret( $key ) {
    $constants = array(
        'openai_api_key' => 'UPTIMEFIXER_OPENAI_API_KEY',
        'dataforseo_login' => 'UPTIMEFIXER_DATAFORSEO_LOGIN',
        'dataforseo_password' => 'UPTIMEFIXER_DATAFORSEO_PASSWORD',
        'pagespeed_api_key' => 'UPTIMEFIXER_PAGESPEED_API_KEY',
    );
    if ( isset( $constants[ $key ] ) && defined( $constants[ $key ] ) ) return trim( (string) constant( $constants[ $key ] ) );
    $values = get_option( 'alltools_integrations', array() );
    return isset( $values[ $key ] ) ? alltools_decrypt_secret( $values[ $key ] ) : '';
}

function alltools_render_integrations_tab() {
    $values = get_option( 'alltools_integrations', array() );
    $constant_names = array(
        'openai_api_key' => 'UPTIMEFIXER_OPENAI_API_KEY',
        'dataforseo_login' => 'UPTIMEFIXER_DATAFORSEO_LOGIN',
        'dataforseo_password' => 'UPTIMEFIXER_DATAFORSEO_PASSWORD',
        'pagespeed_api_key' => 'UPTIMEFIXER_PAGESPEED_API_KEY',
    );
    $fields = array(
        'openai_api_key' => array( 'OpenAI API key', 'AI summaries, transcription, speech and image generation.' ),
        'dataforseo_login' => array( 'DataForSEO login', 'Keyword, ranking, traffic and backlink data.' ),
        'dataforseo_password' => array( 'DataForSEO password', 'Stored encrypted and used only for server-to-server requests.' ),
        'pagespeed_api_key' => array( 'Google PageSpeed API key', 'Optional: increases audit quota and reliability.' ),
    );
    ?>
    <p><?php esc_html_e( 'Credentials are encrypted before storage, never localized into JavaScript and never exposed in page source. For stronger operational security, define the matching UPTIMEFIXER_* constants in wp-config.php instead.', 'alltools' ); ?></p>
    <table class="form-table">
        <?php foreach ( $fields as $key => $field ) : $from_constant = isset( $constant_names[ $key ] ) && defined( $constant_names[ $key ] ); $saved = $from_constant || ! empty( $values[ $key ] ); ?>
        <tr><th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field[0] ); ?></label></th><td><input type="password" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="" autocomplete="new-password" class="regular-text" placeholder="<?php echo $saved ? esc_attr__( 'Saved — leave blank to keep', 'alltools' ) : ''; ?>"><p class="description"><?php echo esc_html( $field[1] ); ?><?php echo $from_constant ? ' ' . esc_html__( 'Currently supplied by wp-config.php.', 'alltools' ) : ''; ?></p><?php if ( $saved && ! $from_constant ) : ?><label><input type="checkbox" name="remove_<?php echo esc_attr( $key ); ?>" value="1"> Remove saved credential</label><?php endif; ?></td></tr>
        <?php endforeach; ?>
    </table>
    <?php
}

function alltools_render_homepage_tab() { ?>
    <table class="form-table">
        <tr><th><label for="hero_title">Hero Title</label></th><td><input type="text" id="hero_title" name="hero_title" value="<?php echo esc_attr( alltools_home( 'hero_title', 'All the Tools You Need, All in' ) ); ?>" class="large-text"></td></tr>
        <tr><th><label for="hero_title_blue">Blue Title Text</label></th><td><input type="text" id="hero_title_blue" name="hero_title_blue" value="<?php echo esc_attr( alltools_home( 'hero_title_blue', 'One Place' ) ); ?>" class="large-text"><p class="description">This text appears in blue on the second line.</p></td></tr>
        <tr><th><label for="hero_sub">Hero Subtitle</label></th><td><textarea id="hero_sub" name="hero_sub" rows="2" class="large-text"><?php echo esc_textarea( alltools_home( 'hero_sub', 'Free online tools for calculations, conversions, images, text, and more. Fast, simple and easy to use.' ) ); ?></textarea></td></tr>
    </table>
<?php }

function alltools_render_contact_tab() { ?>
    <table class="form-table">
        <tr><th><label for="cf_email">Email</label></th><td><input type="email" id="cf_email" name="cf_email" value="<?php echo esc_attr( alltools_contact( 'email' ) ); ?>" class="regular-text"></td></tr>
        <tr><th><label for="cf_phone">Phone</label></th><td><input type="text" id="cf_phone" name="cf_phone" value="<?php echo esc_attr( alltools_contact( 'phone' ) ); ?>" class="regular-text"></td></tr>
        <tr><th><label for="cf_address">Address</label></th><td><textarea id="cf_address" name="cf_address" rows="3" class="large-text"><?php echo esc_textarea( alltools_contact( 'address' ) ); ?></textarea></td></tr>
    </table>
<?php }

function alltools_render_footer_tab() { ?>
    <table class="form-table">
        <tr><th><label for="footer_tagline">Footer Tagline</label></th><td><textarea id="footer_tagline" name="footer_tagline" rows="2" class="large-text"><?php echo esc_textarea( alltools_get( 'footer_tagline', 'A free collection of online tools to make your daily tasks easier.' ) ); ?></textarea></td></tr>
        <tr><th><label for="footer_disclaimer">Disclaimer Text</label></th><td><textarea id="footer_disclaimer" name="footer_disclaimer" rows="3" class="large-text"><?php echo esc_textarea( alltools_get( 'footer_disclaimer', 'Tools are provided as is. Local processing is used where possible; live public diagnostics are validated, rate-limited, and do not require a paid API key.' ) ); ?></textarea></td></tr>
        <tr><th><label for="footer_rights">Rights Text</label></th><td><input type="text" id="footer_rights" name="footer_rights" value="<?php echo esc_attr( alltools_get( 'footer_rights', 'All rights reserved.' ) ); ?>" class="regular-text"></td></tr>
        <tr><th><label for="social_fb">Facebook URL</label></th><td><input type="url" id="social_fb" name="social_fb" value="<?php echo esc_attr( alltools_get( 'social_fb' ) ); ?>" class="regular-text"></td></tr>
        <tr><th><label for="social_tw">Twitter URL</label></th><td><input type="url" id="social_tw" name="social_tw" value="<?php echo esc_attr( alltools_get( 'social_tw' ) ); ?>" class="regular-text"></td></tr>
        <tr><th><label for="social_in">LinkedIn URL</label></th><td><input type="url" id="social_in" name="social_in" value="<?php echo esc_attr( alltools_get( 'social_in' ) ); ?>" class="regular-text"></td></tr>
    </table>
<?php }

add_action( 'admin_post_alltools_save_settings', 'alltools_save_settings_handler' );
function alltools_save_settings_handler() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No permission' );
    if ( ! isset( $_POST['alltools_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['alltools_nonce'] ), 'alltools_save_settings' ) ) wp_die( 'Invalid nonce' );

    $tab = isset( $_POST['tab'] ) ? sanitize_key( $_POST['tab'] ) : 'general';

    if ( $tab === 'general' || $tab === 'footer' ) {
        $opts = get_option( 'alltools_settings', array() );
        if ( ! is_array( $opts ) ) $opts = array();
        $allowed = array( 'primary_color' => 'sanitize_hex_color', 'accent_color' => 'sanitize_hex_color', 'gtm_id' => 'sanitize_text_field', 'ga_id' => 'sanitize_text_field', 'adsense_publisher_id' => 'sanitize_text_field',
                          'footer_tagline' => 'sanitize_textarea_field', 'footer_disclaimer' => 'sanitize_textarea_field', 'footer_rights' => 'sanitize_text_field',
                          'social_fb' => 'esc_url_raw', 'social_tw' => 'esc_url_raw', 'social_in' => 'esc_url_raw' );
        foreach ( $allowed as $key => $sanitizer ) {
            if ( isset( $_POST[ $key ] ) ) {
                $val = wp_unslash( $_POST[ $key ] );
                $opts[ $key ] = is_callable( $sanitizer ) ? call_user_func( $sanitizer, $val ) : sanitize_text_field( $val );
                if ( $key === 'primary_color' || $key === 'accent_color' ) {
                    if ( ! $opts[ $key ] ) {
                        $opts[ $key ] = $key === 'primary_color' ? '#2563EB' : '#8B5CF6';
                    }
                } elseif ( 'gtm_id' === $key ) {
                    $opts[ $key ] = strtoupper( trim( $opts[ $key ] ) );
                    if ( $opts[ $key ] && ! preg_match( '/^GTM-[A-Z0-9]+$/', $opts[ $key ] ) ) $opts[ $key ] = '';
                } elseif ( 'ga_id' === $key ) {
                    $opts[ $key ] = strtoupper( trim( $opts[ $key ] ) );
                    if ( $opts[ $key ] && ! preg_match( '/^(?:G|AW|DC|UA)-[A-Z0-9-]+$/', $opts[ $key ] ) ) $opts[ $key ] = '';
                }
            }
        }
        update_option( 'alltools_settings', $opts );
    }

    if ( $tab === 'homepage' ) {
        $opts = array(
            'hero_title'      => isset( $_POST['hero_title'] ) ? sanitize_text_field( wp_unslash( $_POST['hero_title'] ) ) : '',
            'hero_title_blue' => isset( $_POST['hero_title_blue'] ) ? sanitize_text_field( wp_unslash( $_POST['hero_title_blue'] ) ) : '',
            'hero_sub'        => isset( $_POST['hero_sub'] )   ? sanitize_textarea_field( wp_unslash( $_POST['hero_sub'] ) ) : '',
        );
        update_option( 'alltools_settings_home', $opts );
    }

    if ( $tab === 'contact' ) {
        $opts = array(
            'email'   => isset( $_POST['cf_email'] )   ? sanitize_email( wp_unslash( $_POST['cf_email'] ) )         : '',
            'phone'   => isset( $_POST['cf_phone'] )   ? sanitize_text_field( wp_unslash( $_POST['cf_phone'] ) )    : '',
            'address' => isset( $_POST['cf_address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cf_address'] ) ) : '',
        );
        update_option( 'alltools_settings_contact', $opts );
    }

    if ( $tab === 'integrations' ) {
        $values = get_option( 'alltools_integrations', array() );
        if ( ! is_array( $values ) ) $values = array();
        $secret_keys = array( 'openai_api_key', 'dataforseo_login', 'dataforseo_password', 'pagespeed_api_key' );
        foreach ( $secret_keys as $key ) {
            if ( ! empty( $_POST[ 'remove_' . $key ] ) ) {
                unset( $values[ $key ] );
                continue;
            }
            $plain = isset( $_POST[ $key ] ) ? trim( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ) : '';
            if ( '' === $plain ) continue;
            $encrypted = alltools_encrypt_secret( $plain );
            if ( $encrypted ) $values[ $key ] = $encrypted;
        }
        update_option( 'alltools_integrations', $values, false );
        delete_option( 'alltools_category_hubs_v400' );
        delete_option( 'alltools_no_api_upgrade_v4' );
        if ( class_exists( 'WPSEO_Sitemaps_Cache' ) && is_callable( array( 'WPSEO_Sitemaps_Cache', 'clear' ) ) ) {
            WPSEO_Sitemaps_Cache::clear();
        }
    }

    wp_safe_redirect( add_query_arg( array( 'page' => 'alltools-settings', 'tab' => $tab, 'updated' => 'true' ), admin_url( 'edit.php?post_type=alltool' ) ) );
    exit;
}

/* ── CONTACT FORM SUBMISSION ──────────────────────────── */
add_action( 'admin_post_nopriv_alltools_contact_submit', 'alltools_handle_contact_submit' );
add_action( 'admin_post_alltools_contact_submit',        'alltools_handle_contact_submit' );
function alltools_handle_contact_submit() {
    if ( ! isset( $_POST['alltools_contact_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['alltools_contact_nonce'] ) ), 'alltools_contact' ) ) {
        wp_die( 'Security check failed.' );
    }
    if ( ! empty( $_POST['cf_website'] ) ) {
        wp_die( 'Invalid submission.' );
    }
    $contact_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
    $contact_key = 'ufx_contact_' . substr( hash_hmac( 'sha256', $contact_ip, wp_salt( 'nonce' ) ), 0, 24 );
    if ( get_transient( $contact_key ) ) {
        wp_die( 'Please wait before sending another message.' );
    }
    set_transient( $contact_key, 1, MINUTE_IN_SECONDS );
    $name    = sanitize_text_field( wp_unslash( isset( $_POST['cf_name'] )    ? $_POST['cf_name']    : '' ) );
    $email   = sanitize_email(      wp_unslash( isset( $_POST['cf_email'] )   ? $_POST['cf_email']   : '' ) );
    $subject = sanitize_text_field( wp_unslash( isset( $_POST['cf_subject'] ) ? $_POST['cf_subject'] : '' ) );
    $message = sanitize_textarea_field( wp_unslash( isset( $_POST['cf_message'] ) ? $_POST['cf_message'] : '' ) );
    $name    = alltools_text_slice( $name, 0, 100 );
    $subject = alltools_text_slice( $subject, 0, 160 );
    $message = alltools_text_slice( $message, 0, 5000 );

    $contact_page = get_page_by_path( 'contact' );
    $redirect = $contact_page ? get_permalink( $contact_page ) : home_url( '/contact/' );

    if ( ! $name || ! is_email( $email ) || ! $subject || ! $message ) {
        wp_safe_redirect( add_query_arg( 'contact', 'error', $redirect ) );
        exit;
    }
    $to      = alltools_contact( 'email' ) ? alltools_contact( 'email' ) : get_option( 'admin_email' );
    $headers = array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $name . ' <' . $email . '>' );
    $body    = "Name: {$name}\nEmail: {$email}\n\n{$message}";
    $sent    = wp_mail( $to, '[Uptime Fixer] ' . $subject, $body, $headers );
    wp_safe_redirect( add_query_arg( 'contact', $sent ? 'success' : 'error', $redirect ) );
    exit;
}
