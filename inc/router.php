<?php
/**
 * Auto-create tool posts and handle fallback URLs.
 * This makes tools work IMMEDIATELY after theme activation —
 * no manual "Import Demo" step needed.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Auto-create tool posts on theme activation.
 */
add_action( 'after_switch_theme', 'alltools_activation_setup' );
function alltools_activation_setup() {
    if ( ! current_user_can( 'switch_themes' ) ) return;
    alltools_ensure_tool_posts();
    alltools_ensure_pages();
    flush_rewrite_rules( false );
}

/**
 * Safety net: on every admin load, make sure tool posts exist.
 * (Runs only once thanks to the option flag.)
 */
add_action( 'admin_init', 'alltools_ensure_on_admin' );
function alltools_ensure_on_admin() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    if ( get_option( 'alltools_tools_ensured_v13' ) !== '1' ) {
        alltools_ensure_tool_posts();
        update_option( 'alltools_tools_ensured_v13', '1' );
    }
    if ( get_option( 'alltools_content_migration_v380' ) === '1' ) return;
    alltools_ensure_pages();
    alltools_migrate_legacy_footer_disclaimer();
    update_option( 'alltools_content_migration_v380', '1' );
    flush_rewrite_rules( false );
}

function alltools_default_privacy_policy_content() {
    return '<p><strong>Last updated: August 17, 2026</strong></p>
<p>This Privacy Policy explains what information Uptime Fixer processes when you browse the website, use a tool, submit the contact form, or view advertising. It also explains the choices available to you.</p>
<h2>Information You Provide</h2>
<p>You may enter text, numbers, files, URLs, domains, IP addresses, or other values into a tool. Many calculations, text actions, image conversions, and file operations are designed to run inside your browser. When a tool runs locally, the input is not intentionally sent to Uptime Fixer. Keep original files and avoid entering confidential information on shared devices.</p>
<p>Public website diagnostics need a server request. For those tools, the public URL, domain, or IP address needed for the selected check is sent to a protected Uptime Fixer endpoint. Requests are validated, rate-limited, and restricted to public internet targets. A tool that uses an external provider will identify that processing in its interface or guidance.</p>
<h2>Contact Messages</h2>
<p>If you use the contact form, we process the name, email address, subject, message, IP-derived security information, and submission time needed to answer the request and prevent abuse. Please do not send passwords, payment details, private keys, medical records, or other sensitive information. Contact correspondence may be retained while the request is active and for a reasonable period afterward for security and record-keeping.</p>
<h2>Technical Logs and Security</h2>
<p>Our hosting and security systems may record IP address, browser type, requested URL, referring page, device information, timestamps, response codes, and diagnostic events. These logs are used to operate the site, investigate errors, limit malicious traffic, and protect users. Retention depends on the hosting and security configuration and is limited to what is reasonably needed for those purposes.</p>
<h2>Cookies, Analytics, and Advertising</h2>
<p>The site may store a preference cookie, for example to remember dark mode. If analytics is enabled, it may collect aggregated usage and device information to help us understand which pages work well.</p>
<p>Uptime Fixer uses or may use Google AdSense. Google and its advertising partners may use cookies, local storage, device identifiers, IP address, and information about visits to this and other websites to deliver, measure, limit, and personalise ads where permitted. Depending on your location and consent choices, advertising may be personalised or non-personalised. You can learn how Google uses information from sites that use its services in <a href="https://policies.google.com/technologies/partner-sites" target="_blank" rel="noopener noreferrer">Google’s partner sites policy</a> and manage advertising preferences through <a href="https://myadcenter.google.com/" target="_blank" rel="noopener noreferrer">My Ad Center</a>.</p>
<p>Where required, a consent message may ask you to accept or reject non-essential storage and advertising purposes. You can also restrict cookies in your browser. Blocking some storage may affect preferences or advertising but should not prevent basic tool access.</p>
<h2>Sharing and Service Providers</h2>
<p>Information may be processed by hosting, security, analytics, advertising, email, and diagnostic service providers only as needed to supply their services. These providers operate under their own terms and privacy policies. We may also disclose information when required by law, to protect rights and safety, or in connection with a legitimate business transfer. Uptime Fixer does not sell tool inputs.</p>
<h2>International Processing</h2>
<p>Service providers may process information in countries other than your own. Privacy protections and legal access rules can differ by location. Providers are selected and configured with reasonable attention to security and data protection.</p>
<h2>Data Choices and Rights</h2>
<p>Depending on where you live, you may have rights to request access, correction, deletion, restriction, portability, or objection regarding personal information we control. You may also withdraw consent where processing relies on consent. These rights can be subject to identity verification and legal exceptions. Use the <a href="/contact/">contact page</a> to make a request.</p>
<h2>Children</h2>
<p>The website is a general-audience utility service and is not directed to children under the age at which they may independently consent to online data processing in their location. Do not submit a child’s personal information through the contact form or share it through generated content without appropriate permission.</p>
<h2>Security and Accuracy</h2>
<p>We use reasonable technical and organisational safeguards, but no website or transmission method is completely secure. Tool results should be reviewed before use, and important files should be backed up.</p>
<h2>Changes to This Policy</h2>
<p>We may update this policy when tools, providers, or legal requirements change. The date at the top will be revised when a material update is published.</p>
<h2>Contact</h2>
<p>Questions or privacy requests can be sent through the <a href="/contact/">Uptime Fixer contact page</a>.</p>';
}

function alltools_default_terms_content() {
    return '<p><strong>Last updated: August 17, 2026</strong></p>
<p>These Terms of Use apply when you visit Uptime Fixer or use its calculators, converters, file utilities, website diagnostics, articles, and related features. By using the site, you agree to these terms. If you do not agree, do not use the site.</p>
<h2>Permitted Use</h2>
<p>You may use the public tools for lawful personal or business tasks. You are responsible for the inputs you submit, the rights needed to process them, and the way you use any result. Only inspect websites, servers, domains, addresses, files, and accounts that you own or are authorised to test.</p>
<h2>Prohibited Conduct</h2>
<p>You must not use the site to break the law; violate privacy, copyright, contractual, or other rights; upload malicious material; bypass access controls or rate limits; scan private networks; disrupt the service; scrape at abusive volume; impersonate another person; or attempt to obtain credentials, source secrets, or non-public information. Automated access must respect published technical controls and must not degrade availability.</p>
<h2>Tool Inputs and Files</h2>
<p>Many tools are designed to process information in the browser, while public diagnostics require a protected server request and some clearly identified features may use a third-party service. Processing details and practical limits can vary by tool. Do not submit information you are not authorised to process. Keep original copies of important files and remove sensitive downloads from shared devices.</p>
<h2>No Professional Advice</h2>
<p>Calculations, diagnostics, articles, generated material, and examples are general informational aids. They are not legal, financial, medical, tax, safety, engineering, cybersecurity, or other professional advice. Results can be incomplete, rounded, delayed, affected by device or network conditions, or based on the values supplied. Obtain qualified advice and authoritative records for important decisions.</p>
<h2>Accuracy and Availability</h2>
<p>We work to keep reviewed tools useful and descriptions accurate, but we do not promise that every feature will always be available, compatible, current, secure, or error-free. A public status, speed, DNS, redirect, certificate, or HTTP check is a point-in-time observation from a particular environment and is not continuous monitoring. We may change, restrict, suspend, or remove features without notice.</p>
<h2>Third-Party Services and Links</h2>
<p>The site may contain advertisements, links, embedded libraries, or results supplied by third parties. Those services have separate terms, privacy practices, availability, and content. A link or advertisement does not mean Uptime Fixer endorses the third party. Dealings with a third party are between you and that provider.</p>
<h2>Intellectual Property</h2>
<p>The Uptime Fixer name, theme design, original explanations, graphics, and site code are protected by applicable intellectual-property laws unless stated otherwise. You retain rights in material you lawfully submit. Using a tool does not transfer ownership of the site or third-party libraries to you. Generated output can be subject to the rights of input owners and applicable law.</p>
<h2>Disclaimer of Warranties</h2>
<p>To the maximum extent permitted by law, the site and its tools are provided “as is” and “as available,” without express or implied warranties, including warranties of accuracy, fitness for a particular purpose, non-infringement, uninterrupted operation, or compatibility. Nothing in these terms excludes a warranty or right that cannot legally be excluded.</p>
<h2>Limitation of Liability</h2>
<p>To the maximum extent permitted by law, Uptime Fixer and its operators will not be liable for indirect, incidental, special, consequential, or punitive loss; loss of profits, data, goodwill, or business opportunity; or damage caused by reliance on a tool result, third-party service, interruption, or unauthorised use. This limitation does not apply where liability cannot legally be limited.</p>
<h2>Indemnity</h2>
<p>Where permitted by law, you agree to be responsible for claims, losses, and reasonable costs arising from your unlawful use of the site, your violation of these terms, or material you process without the necessary rights or authority.</p>
<h2>Changes and Severability</h2>
<p>We may update these terms as the site changes. Continued use after an update means the revised terms apply. If a provision is found unenforceable, the remaining provisions continue to apply. Failure to enforce a provision is not a waiver.</p>
<h2>Contact</h2>
<p>Questions about these terms can be sent through the <a href="/contact/">Uptime Fixer contact page</a>.</p>';
}

/** Replace only known misleading legacy copy; preserve genuine custom settings. */
function alltools_migrate_legacy_footer_disclaimer() {
    $settings = get_option( 'alltools_settings', array() );
    if ( ! is_array( $settings ) ) $settings = array();
    $current = isset( $settings['footer_disclaimer'] ) ? (string) $settings['footer_disclaimer'] : '';
    if ( ! $current || false !== stripos( $current, 'all calculations and file processing happen in your browser' ) ) {
        $settings['footer_disclaimer'] = 'Tools are provided as is. Many tasks run in your browser; live public diagnostics use protected site endpoints, and clearly identified features may use external providers.';
        update_option( 'alltools_settings', $settings );
    }
}

/**
 * Create CPT posts for any tool that doesn't have one yet.
 */
function alltools_ensure_tool_posts() {
    if ( ! function_exists( 'alltools_all' ) ) return;
    $tools = alltools_all();
    foreach ( $tools as $slug => $tool ) {
        $existing = get_posts( array(
            'post_type'   => 'alltool',
            'meta_key'    => '_alltool_slug',
            'meta_value'  => $slug,
            'numberposts' => 1,
            'post_status' => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash', 'auto-draft' ),
            'fields'      => 'ids',
        ) );
        if ( ! empty( $existing ) ) continue;

        $post_id = wp_insert_post( array(
            'post_type'    => 'alltool',
            'post_title'   => $tool['title'],
            'post_excerpt' => isset( $tool['desc'] ) ? $tool['desc'] : '',
            'post_content' => isset( $tool['long'] ) ? $tool['long'] : '',
            'post_status'  => 'publish',
            'post_name'    => $slug,
        ) );
        if ( $post_id && ! is_wp_error( $post_id ) ) {
            update_post_meta( $post_id, '_alltool_slug', $slug );
        }
    }
}

/**
 * Create essential pages (Home, About, Contact, Privacy, Terms) if missing.
 */
function alltools_ensure_pages() {
    $pages = array(
        'home'           => array( 'Home', '' ),
        'about'          => array( 'About', '' ),
        'contact'        => array( 'Contact', '' ),
        'privacy-policy' => array( 'Privacy Policy', alltools_default_privacy_policy_content() ),
        'terms'          => array( 'Terms of Use', alltools_default_terms_content() ),
    );
    $home_id = 0;
    foreach ( $pages as $slug => $info ) {
        $existing = get_page_by_path( $slug );
        if ( $existing ) {
            if ( $slug === 'home' ) $home_id = $existing->ID;
            if ( 'privacy-policy' === $slug ) update_option( 'wp_page_for_privacy_policy', (int) $existing->ID );
            continue;
        }
        $page_id = wp_insert_post( array(
            'post_type'    => 'page',
            'post_title'   => $info[0],
            'post_name'    => $slug,
            'post_content' => $info[1],
            'post_status'  => 'publish',
        ) );
        if ( $page_id && ! is_wp_error( $page_id ) && $slug === 'home' ) {
            $home_id = $page_id;
        }
        if ( $page_id && ! is_wp_error( $page_id ) && 'privacy-policy' === $slug ) update_option( 'wp_page_for_privacy_policy', (int) $page_id );
    }
    if ( $home_id && get_option( 'show_on_front' ) !== 'page' ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $home_id );
    }
}

/**
 * Fallback router: if a tool URL is hit via ?alltool=X query param,
 * find the existing CPT post and redirect to its real permalink.
 */
add_action( 'template_redirect', 'alltools_fallback_router', 1 );
function alltools_fallback_router() {
    if ( empty( $_GET['alltool'] ) ) return;
    $slug = sanitize_key( wp_unslash( $_GET['alltool'] ) );
    $tool = alltools_get_tool( $slug );
    if ( ! $tool ) return;

    $posts = get_posts( array(
        'post_type'   => 'alltool',
        'meta_key'    => '_alltool_slug',
        'meta_value'  => $slug,
        'numberposts' => 1,
        'post_status' => 'publish',
    ) );
    if ( ! empty( $posts ) ) {
        wp_safe_redirect( get_permalink( $posts[0] ), 302 );
        exit;
    }

    // Never mutate the database from a public request. Activation/admin setup
    // creates every registered tool page in a capability-checked context.
    wp_safe_redirect( home_url( '/' ), 302 );
    exit;
}
