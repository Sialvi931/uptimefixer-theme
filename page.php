<?php
/**
 * Generic page template.
 * @package UptimeFixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

while ( have_posts() ) : the_post();
    $slug = get_post_field( 'post_name', get_the_ID() );
?>

<?php if ( $slug === 'about' ) : ?>
<!-- ABOUT PAGE -->
<?php alltools_inner_title_bar( 'About Uptime Fixer' ); ?>

<section class="at-section">
    <div class="at-container">
        <div class="at-prose-grid">
            <div class="at-prose">
                <?php $published_tools = count( alltools_public_tools() ); ?>
                <p class="at-lead">Uptime Fixer is a free collection of practical online tools designed to make daily tasks easier — calculations, conversions, image manipulation, text processing, website diagnostics, and more.</p>
                <h2>Our Mission</h2>
                <p>We believe everyone deserves access to useful tools without unnecessary paywalls or signups. We explain how each tool works so you can choose the right utility and review its result before using it.</p>
                <h2>Privacy First</h2>
                <p>Local processing is used wherever the feature allows it. Live public website diagnostics use protected, rate-limited site endpoints and do not require paid API keys. The relevant tool page explains the processing involved. We do not sell tool inputs or use them to build advertising profiles.</p>
                <h2>Editorial and Testing Standards</h2>
                <p>Tools are tested against representative inputs before they are added to the public directory. Pages that still need testing or original guidance remain available for development but are excluded from search indexes and sitemaps. Articles are published publicly only after an editor marks them as fact-checked.</p>
                <h2>Built by DigiPlex Creations</h2>
                <p>Uptime Fixer is a product of <a href="https://digiplexcreations.org.uk/" target="_blank" rel="noopener">DigiPlex Creations</a>, a UK-based web development studio passionate about building clean, user-first digital products.</p>
                <h2>What's Next</h2>
                <p>We're constantly expanding the toolbox — PDF tools, colour utilities, unit converters, and more are on the way. Have an idea? <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Reach out</a>.</p>
                <?php if ( get_the_content() ) the_content(); ?>
            </div>
            <aside class="at-prose-aside">
                <div class="at-card">
                    <h3>Quick Facts</h3>
                    <ul class="at-fact-list">
                        <li><strong><?php echo esc_html( number_format_i18n( $published_tools ) ); ?>+</strong><span>Free tools available</span></li>
                        <li><strong>Local</strong><span>Processing where possible</span></li>
                        <li><strong>0</strong><span>Account required</span></li>
                        <li><strong>Reviewed</strong><span>Before search indexing</span></li>
                    </ul>
                </div>
                <div class="at-card">
                    <h3>Our Values</h3>
                    <ul class="at-value-list">
                        <li><?php echo alltools_icon_svg('shield',16); ?> Privacy first</li>
                        <li><?php echo alltools_icon_svg('zap',16); ?> Fast results</li>
                        <li><?php echo alltools_icon_svg('heart',16); ?> Always free</li>
                        <li><?php echo alltools_icon_svg('check',16); ?> Accessible to all</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php elseif ( $slug === 'contact' ) : ?>
<!-- CONTACT PAGE -->
<?php alltools_inner_title_bar( 'Get in Touch' ); ?>

<section class="at-section">
    <div class="at-container">
        <div class="at-tool-grid at-tool-grid-2">
            <div class="at-card">
                <h2>Contact Information</h2>
                <?php if ( alltools_contact('email') ) : ?>
                <div class="at-contact-row"><span class="at-contact-icon" style="background:#DBEAFE;color:#2563EB;"><?php echo alltools_icon_svg('mail',18); ?></span><div><strong>Email</strong><a href="mailto:<?php echo esc_attr( alltools_contact('email') ); ?>"><?php echo esc_html( alltools_contact('email') ); ?></a></div></div>
                <?php endif; ?>
                <?php if ( alltools_contact('phone') ) : ?>
                <div class="at-contact-row"><span class="at-contact-icon" style="background:#D1FAE5;color:#10B981;"><?php echo alltools_icon_svg('phone',18); ?></span><div><strong>Phone</strong><a href="tel:<?php echo esc_attr( alltools_contact('phone') ); ?>"><?php echo esc_html( alltools_contact('phone') ); ?></a></div></div>
                <?php endif; ?>
                <?php if ( alltools_contact('address') ) : ?>
                <div class="at-contact-row"><span class="at-contact-icon" style="background:#FED7AA;color:#F97316;"><?php echo alltools_icon_svg('shield',18); ?></span><div><strong>Address</strong><span><?php echo esc_html( alltools_contact('address') ); ?></span></div></div>
                <?php endif; ?>
                <div class="at-contact-row"><span class="at-contact-icon" style="background:#EDE9FE;color:#8B5CF6;"><?php echo alltools_icon_svg('link',18); ?></span><div><strong>Website</strong><a href="https://digiplexcreations.org.uk/" target="_blank" rel="noopener">digiplexcreations.org.uk</a></div></div>
            </div>

            <div class="at-card">
                <h2>Send a Message</h2>
                <?php if ( isset( $_GET['contact'] ) ) :
                    if ( $_GET['contact'] === 'success' ) : ?>
                        <div class="at-alert at-alert-success">✓ Thanks! Your message has been sent.</div>
                    <?php elseif ( $_GET['contact'] === 'error' ) : ?>
                        <div class="at-alert at-alert-error">✗ Something went wrong. Please try again.</div>
                    <?php endif;
                endif; ?>
                <form class="at-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'alltools_contact', 'alltools_contact_nonce' ); ?>
                    <input type="hidden" name="action" value="alltools_contact_submit">
                    <div aria-hidden="true" style="position:absolute;left:-9999px;height:1px;overflow:hidden;"><label>Website<input type="text" name="cf_website" tabindex="-1" autocomplete="off"></label></div>
                    <div class="at-grid-2">
                        <div class="at-field"><label>Your Name *</label><input type="text" name="cf_name" class="at-input" required></div>
                        <div class="at-field"><label>Email *</label><input type="email" name="cf_email" class="at-input" required></div>
                    </div>
                    <div class="at-field"><label>Subject *</label><input type="text" name="cf_subject" class="at-input" required></div>
                    <div class="at-field"><label>Message *</label><textarea name="cf_message" class="at-textarea" rows="5" required></textarea></div>
                    <button type="submit" class="at-btn at-btn-primary">Send Message →</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php else : ?>
<!-- GENERIC PAGE -->
<?php alltools_inner_title_bar( get_the_title() ); ?>

<section class="at-section">
    <div class="at-container">
        <div class="at-prose"><?php the_content(); ?></div>
    </div>
</section>
<?php endif; ?>

<?php endwhile; get_footer();
