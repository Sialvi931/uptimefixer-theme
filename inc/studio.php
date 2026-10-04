<?php
/** Homepage assets and non-destructive SEO fallbacks. */
if ( ! defined( 'ABSPATH' ) ) exit;
function alltools_studio_faqs() {
 return array(
  array( 'Are the tools free to use?', 'The tools in the public collection are free to open and use. Some website checks depend on external services, which can have availability or usage limits.' ),
  array( 'Do my files leave my browser?', 'Many image, text and file tools process inputs locally. Website diagnostics send the public address you enter to the server. Read the privacy note on the tool you choose before adding sensitive information.' ),
  array( 'Do I need an account?', 'No account is required to use the public tools. Open a tool, follow its input guidance and review your result.' ),
  array( 'Will this work on my phone?', 'The site adapts to small screens. Large PDFs, video and audio tasks need more memory and may work better on a desktop browser.' ),
  array( 'Why might a website check fail?', 'The target website may block automated requests, time out or restrict access. Check the URL and try again later. A failed request does not always mean the website is down for everyone.' ),
 );
}
add_action( 'wp_enqueue_scripts', function() {
 if ( ! is_front_page() ) return;
 wp_enqueue_style( 'alltools-studio', ALLTOOLS_URI . 'assets/css/studio.css', array( 'alltools-main' ), ALLTOOLS_VERSION );
 // The homepage uses local system fonts. It needs no third-party font request.
 wp_dequeue_style( 'alltools-poppins' );
}, 25 );

/** Use the existing provider's output whenever it already has a description. */
add_filter( 'wpseo_metadesc', 'alltools_studio_meta_fallback', 50 );
function alltools_studio_meta_fallback( $description ) {
 if ( ! is_front_page() || '' !== trim( (string) $description ) ) return $description;
 return 'Free online tools for images, PDFs, text, calculations and website checks. Find the right tool, follow clear steps and get useful results without signing up.';
}

function alltools_studio_schema_items() {
 $items = array();
 $tools = alltools_public_tools();
 foreach ( array( 'image-compressor', 'merge-pdf', 'word-counter', 'qr-code-generator', 'unit-converter' ) as $slug ) {
  if ( ! isset( $tools[$slug] ) ) continue;
  $items[] = array( '@type' => 'ListItem', 'position' => count($items)+1, 'name' => $tools[$slug]['title'], 'url' => alltools_tool_url($slug) );
 }
 return array( '@type' => 'ItemList', '@id' => home_url('/#quick-tools'), 'name' => 'Quick actions', 'itemListElement' => $items, 'numberOfItems' => count($items) );
}
add_filter( 'wpseo_schema_graph', function( $graph ) {
 if ( is_front_page() ) $graph[] = alltools_studio_schema_items();
 return $graph;
}, 40 );
add_action( 'wp_head', function() {
 if ( ! is_front_page() || alltools_yoast_is_active() ) return;
 $url = home_url('/');
 $graph = array(
  array( '@type' => 'WebSite', '@id' => $url.'#website', 'url' => $url, 'name' => get_bloginfo('name'), 'inLanguage' => get_bloginfo('language') ),
  array( '@type' => 'CollectionPage', '@id' => $url.'#webpage', 'url' => $url, 'name' => wp_get_document_title(), 'isPartOf' => array('@id'=>$url.'#website'), 'mainEntity' => array('@id'=>$url.'#quick-tools') ),
  alltools_studio_schema_items(),
 );
 echo '<script type="application/ld+json">'.wp_json_encode(array('@context'=>'https://schema.org','@graph'=>$graph), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).'</script>';
}, 25 );

add_filter('wpseo_opengraph_image', 'alltools_studio_social_image');
add_filter('wpseo_twitter_image', 'alltools_studio_social_image');
function alltools_studio_social_image($image) {
    return is_front_page() && empty($image) ? ALLTOOLS_URI.'assets/images/studio/hero.webp' : $image;
}
add_action('wp_head', function() {
    if (!is_front_page() || alltools_yoast_is_active()) return;
    $image = esc_url(ALLTOOLS_URI.'assets/images/studio/hero.webp');
    echo '<meta property="og:image" content="'.$image.'"><meta name="twitter:image" content="'.$image.'">';
}, 26);
