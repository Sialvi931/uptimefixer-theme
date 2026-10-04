<?php
/** Editorial tool homepage. Queries content; never modifies stored posts. */
if ( ! defined( 'ABSPATH' ) ) exit;
add_filter( 'body_class', function( $classes ) { $classes[] = 'ufx-studio'; return $classes; } );
$tools = alltools_public_tools();
$index = array();
foreach ( $tools as $slug => $tool ) $index[] = array( 'title' => $tool['title'], 'summary' => $tool['desc'], 'category' => alltools_cat_label( $tool['category'] ), 'url' => alltools_tool_url( $slug ) );
$art = ALLTOOLS_URI . 'assets/images/studio/';
$archive = get_post_type_archive_link( 'alltool' );
$quick = array( 'image-compressor' => 'Compress an image', 'merge-pdf' => 'Merge PDF files', 'word-counter' => 'Count your words', 'qr-code-generator' => 'Make a QR code', 'unit-converter' => 'Convert units' );
$collections = array(
 array( 'image-tools', 'Image Studio', 'A little lighter. A lot sharper.', 'Compress, resize and convert your images.', 'image-studio', 'image', '01' ),
 array( 'pdf-tools', 'The PDF Desk', 'Paperwork, without the paper.', 'Merge, split and organize your documents.', 'pdf-desk', 'pdf', '02' ),
 array( 'text-tools', 'Room for better words.', 'Writing Room', 'Count, clean and format your text.', 'writing', 'writing', '03' ),
 array( 'calculators', 'Quick calculations', 'Everyday numbers, sorted.', 'From percentages to planning.', 'calculator', 'mini', '04' ),
 array( 'developer-tools', 'Developer utilities', 'Small fixes. Clean code.', 'Format, encode and inspect.', 'developer', 'mini', '05' ),
 array( 'website-tools', 'Website checkup', 'Get a clearer picture.', 'Inspect public pages and domains.', 'privacy', 'mini', '06' ),
);
$guides = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3, 'ignore_sticky_posts' => true, 'no_found_rows' => true, 'meta_query' => alltools_reviewed_post_meta_query() ) );
get_header();
?>
<div class="studio-topline at-container"><span><i></i> Small tools. A little more headspace.</span><span>No signup. Just get started.</span></div>
<section class="studio-hero at-container">
 <div class="studio-hero-copy">
  <p class="studio-eyebrow">FOR THE THINGS ON YOUR TO-DO LIST</p>
  <h1>Everyday tasks,<br><span>made lighter.</span></h1>
  <p class="studio-intro">The right little tool can make a big difference. Work with files, tidy up text and check your website — all in one place.</p>
  <form class="studio-search" data-home-tool-search role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
   <?php echo alltools_icon_svg( 'search', 22 ); ?>
   <label class="screen-reader-text" for="at-tool-search">Find a tool</label>
   <input id="at-tool-search" type="search" name="s" placeholder="What do you need to do?" autocomplete="off" aria-controls="studio-search-results" aria-expanded="false">
   <input type="hidden" name="post_type" value="alltool">
   <button type="submit" aria-label="Search tools"><?php echo alltools_icon_svg( 'arrow', 20 ); ?></button>
   <div id="studio-search-results" class="ufx-home-search-results" aria-label="Tool suggestions"><p class="ufx-home-search-empty" data-search-empty>No match yet. Try “image”, “PDF” or “text”.</p></div>
   <script type="application/json" data-home-tool-index><?php echo wp_json_encode( $index, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?></script>
  </form>
  <div class="studio-promises"><span><?php echo alltools_icon_svg( 'check', 18 ); ?> Free tools</span><span><?php echo alltools_icon_svg( 'shield', 18 ); ?> Clear privacy notes</span><span><?php echo alltools_icon_svg( 'globe', 18 ); ?> In your browser</span></div>
 </div>
 <div class="studio-hero-art"><img src="<?php echo esc_url( $art . 'hero.webp' ); ?>" alt="Floating image, document, calculator and code tools" width="1500" height="844" fetchpriority="high" decoding="async"><span class="studio-art-note">Less busywork.<br><em>More room to create.</em></span></div>
</section>
<section class="studio-quick at-container" aria-label="Quick actions"><div><h2>Jump right in.</h2><p>One task. One click.</p></div><?php foreach ( $quick as $slug => $label ) : if ( ! isset( $tools[$slug] ) ) continue; ?><a href="<?php echo esc_url( alltools_tool_url( $slug ) ); ?>"><?php echo alltools_icon_svg( $tools[$slug]['icon'], 23 ); ?><strong><?php echo esc_html( $label ); ?></strong><span aria-hidden="true"><?php echo alltools_diagonal_arrow(); ?></span></a><?php endforeach; ?></section>
<section class="studio-section at-container" id="categories">
 <div class="studio-heading"><div><p class="studio-eyebrow">A PLACE FOR EVERY TASK</p><h2>What are we working on?</h2></div><a class="studio-text-link" href="<?php echo esc_url( $archive ); ?>">Explore all tools <span aria-hidden="true"><?php echo alltools_diagonal_arrow(); ?></span></a></div>
 <div class="studio-collections">
 <?php foreach ( $collections as $c ) : if ( ! alltools_public_tool_count_in_cat( $c[0] ) ) continue; ?>
  <a class="studio-collection studio-<?php echo esc_attr( $c[5] ); ?>" href="<?php echo esc_url( alltools_category_url( $c[0] ) ); ?>" data-tool-category="<?php echo esc_attr( $c[0] ); ?>">
   <div class="studio-collection-copy"><span class="studio-card-number"><?php echo esc_html( $c[6] ); ?> / <?php echo esc_html( number_format_i18n( alltools_public_tool_count_in_cat( $c[0] ) ) ); ?> tools</span><h3><?php echo esc_html( $c[1] ); ?></h3><p><?php echo esc_html( $c[3] ); ?></p></div>
   <img src="<?php echo esc_url( $art . $c[4] . '.webp' ); ?>" alt="" loading="lazy" decoding="async" width="850" height="850">
   <span class="studio-card-bottom"><?php echo esc_html( $c[2] ); ?><b aria-hidden="true"><?php echo alltools_diagonal_arrow(); ?></b></span>
  </a>
 <?php endforeach; ?>
 </div>
 <div class="studio-more"><span>There’s more in the drawer:</span><?php foreach ( array( 'video-tools', 'audio-tools', 'business-tools', 'marketing-tools', 'other-tools' ) as $cat ) : if ( ! alltools_public_tool_count_in_cat( $cat ) ) continue; ?><a href="<?php echo esc_url( alltools_category_url( $cat ) ); ?>"><?php echo esc_html( alltools_cat_label( $cat ) ); ?> ↗</a><?php endforeach; ?></div>
</section>
<section class="studio-section studio-directory at-container" id="tools">
 <div class="studio-heading"><div><p class="studio-eyebrow">YOUR EVERYDAY SHORTLIST</p><h2>Useful from the first click.</h2></div><p>Simple inputs. Practical results.<br>Choose a tool and make a little progress.</p></div>
 <div class="studio-shortlist"><?php foreach ( array( 'website-image-extractor', 'image-resizer', 'pdf-to-text', 'json-formatter', 'password-generator', 'website-uptime-checker', 'case-converter', 'percentage-calculator' ) as $slug ) : if ( ! isset( $tools[$slug] ) ) continue; $t = $tools[$slug]; ?>
  <a href="<?php echo esc_url( alltools_tool_url( $slug ) ); ?>"><span class="studio-short-icon"><?php echo alltools_icon_svg( $t['icon'], 23 ); ?></span><div><h3><?php echo esc_html( $t['title'] ); ?></h3><p><?php echo esc_html( $t['desc'] ); ?></p></div><span aria-hidden="true"><?php echo alltools_diagonal_arrow(); ?></span></a>
 <?php endforeach; ?></div>
 <a class="studio-button" href="<?php echo esc_url( $archive ); ?>">Find your next tool <span><?php echo esc_html( number_format_i18n( count( $tools ) ) ); ?> to explore ↗</span></a>
</section>
<?php if ( $guides->have_posts() ) : ?>
<section class="studio-section at-container" id="guides">
 <div class="studio-heading"><div><p class="studio-eyebrow">A LITTLE KNOW-HOW GOES A LONG WAY</p><h2>Useful, even after you’re done.</h2></div><a class="studio-text-link" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Read the journal ↗</a></div>
 <div class="studio-guides"><?php $guide_i = 0; $fallbacks = array( 'guide-images', 'guide-security', 'guide-files' ); while ( $guides->have_posts() ) : $guides->the_post(); ?>
  <article class="studio-guide"><a href="<?php the_permalink(); ?>"><div class="studio-guide-image"><?php if ( has_post_thumbnail() ) the_post_thumbnail( 'large', array( 'loading' => 'lazy' ) ); else { ?><img src="<?php echo esc_url( $art . $fallbacks[$guide_i] . '.webp' ); ?>" alt="" width="850" height="638" loading="lazy"><?php } ?></div><div class="studio-guide-copy"><span class="studio-eyebrow">FROM THE JOURNAL</span><h3><?php the_title(); ?></h3><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p><span class="studio-guide-meta"><?php echo esc_html( get_the_date() ); ?> <b aria-hidden="true"><?php echo alltools_diagonal_arrow(); ?></b></span></div></a></article>
 <?php $guide_i++; endwhile; wp_reset_postdata(); ?></div>
</section>
<?php else : ?>
<section class="studio-section at-container" id="guides"><div class="studio-heading"><div><p class="studio-eyebrow">SMALL HABITS. BETTER RESULTS.</p><h2>Before you hit download.</h2></div></div><div class="studio-guides">
<?php foreach ( array( array('guide-images','Start with your original.','Keep the source image. Resize to the dimensions you need, then compress and compare the details.','image-compressor'),array('guide-security','Make every password different.','Generate a long, unique password for each account and keep it in a password manager.','password-generator'),array('guide-files','Check the finished document.','After merging or converting, open the result and review page order, text and image quality.','merge-pdf') ) as $tip ) : ?>
<article class="studio-guide"><div class="studio-guide-image"><img src="<?php echo esc_url( $art . $tip[0] . '.webp' ); ?>" alt="" width="850" height="638" loading="lazy"></div><div class="studio-guide-copy"><h3><?php echo esc_html($tip[1]); ?></h3><p><?php echo esc_html($tip[2]); ?></p><?php if(isset($tools[$tip[3]])) : ?><a class="studio-text-link" href="<?php echo esc_url(alltools_tool_url($tip[3])); ?>">Open the tool ↗</a><?php endif; ?></div></article>
<?php endforeach; ?></div></section>
<?php endif; ?>
<section class="studio-privacy at-container"><div><p class="studio-eyebrow">LESS FRICTION. MORE CONTROL.</p><h2>Your work deserves<br>a little privacy.</h2></div><div><?php echo alltools_icon_svg('shield',28); ?><h3>Browser processing</h3><p>Many file and text tools work locally. Check each tool’s privacy note for the details.</p></div><div><?php echo alltools_icon_svg('globe',28); ?><h3>Clear about connections</h3><p>Website checks contact public servers. Tools explain when an external service is involved.</p></div><div><?php echo alltools_icon_svg('check',28); ?><h3>No account to create</h3><p>Open a tool, add your input and get to work. Keep a copy of your original files.</p></div></section>
<section class="studio-section studio-bottom at-container"><div class="studio-note"><p class="studio-eyebrow">BUILT FOR THE EVERYDAY</p><h2>Big to-do list?<br><em>Start small.</em></h2><p>A smaller image. A cleaner document. One less thing to figure out. That’s what we’re here for.</p><a class="studio-text-link" href="<?php echo esc_url(home_url('/about/')); ?>">A little about us ↗</a></div><div class="studio-faq" id="studio-faq"><h2>A few things you might wonder.</h2><?php foreach ( alltools_studio_faqs() as $faq ) : ?><details><summary><?php echo esc_html($faq[0]); ?><span aria-hidden="true">+</span></summary><p><?php echo esc_html($faq[1]); ?></p></details><?php endforeach; ?><a class="studio-text-link" href="<?php echo esc_url(home_url('/contact/')); ?>">Something else? Get in touch ↗</a></div></section>
<?php get_footer(); ?>
