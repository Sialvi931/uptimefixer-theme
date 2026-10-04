<?php
/**
 * Footer
 * @package UptimeFixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
</main>

<footer class="at-footer">
    <div class="at-container">
        <div class="at-footer-grid">

            <div class="at-footer-brand">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="at-logo"><?php echo alltools_logo_output(); ?></a>
                <h2 class="ufx-footer-heading">Less busywork.<br>More possibilities.</h2>
                <span class="ufx-footer-accent" aria-hidden="true"></span>
                <p><?php echo esc_html(alltools_get('footer_tagline','Useful tools for everyday tasks.')); ?></p>
            </div>
            <div><h4>Tools</h4><ul class="at-footer-list">
            <?php foreach(array('image-tools','pdf-tools','text-tools','calculators','developer-tools','website-tools','video-tools','audio-tools') as $cat) : if(!alltools_public_tool_count_in_cat($cat))continue; ?>
            <li><a href="<?php echo esc_url(alltools_category_url($cat)); ?>"><?php echo esc_html(alltools_cat_label($cat)); ?></a></li>
            <?php endforeach; ?></ul></div>
            <div><h4>Resources</h4><ul class="at-footer-list">
                <li><a href="<?php echo esc_url(get_post_type_archive_link('alltool')); ?>">All tools</a></li>
                <li><a href="<?php echo esc_url(home_url('/#categories')); ?>">Collections</a></li>
                <li><a href="<?php echo esc_url(home_url('/blog/')); ?>">Guides</a></li>
                <li><a href="<?php echo esc_url(home_url('/#studio-faq')); ?>">FAQs</a></li>
            </ul></div>
            <div><h4>Company</h4><ul class="at-footer-list">
                <li><a href="<?php echo esc_url(home_url('/about/')); ?>">About</a></li>
                <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a></li>
                <li><a href="<?php echo esc_url(get_privacy_policy_url() ?: home_url('/privacy-policy/')); ?>">Privacy Policy</a></li>
                <li><a href="<?php echo esc_url(home_url('/terms/')); ?>">Terms of Use</a></li>
            </ul></div>
        </div>

        <div class="at-footer-bottom">
            <p>&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php echo esc_html( alltools_get( 'footer_rights', 'All rights reserved.' ) ); ?></p>
            <p>Made with <span class="at-heart"><?php echo alltools_icon_svg('heart',14); ?></span> by <a href="https://digiplexcreations.org.uk/" target="_blank" rel="noopener">DigiPlex Creations</a></p>
        </div>
    </div>
</footer>

<div id="at-toast" class="at-toast" role="status" aria-live="polite"></div>

<?php wp_footer(); ?>
</body>
</html>
