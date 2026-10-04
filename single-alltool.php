<?php
/**
 * Single tool page template — matches all tool page designs (Images 1-9).
 * @package UptimeFixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

while ( have_posts() ) : the_post();

$slug      = get_post_meta( get_the_ID(), '_alltool_slug', true );
$tool      = alltools_get_tool( $slug );
$colors    = ! empty( $tool['color'] ) ? alltools_color( $tool['color'] ) : alltools_color( 'blue' );
$title     = get_the_title();
$long      = ! empty( $tool['long'] ) ? $tool['long'] : get_the_excerpt();
$category  = ! empty( $tool['category'] ) ? $tool['category'] : '';
$icon_name = ! empty( $tool['icon'] ) ? $tool['icon'] : 'grid';

// Sub-page overrides for speed test
$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : '';
if ( $slug === 'website-speed-test' && $view ) {
    $view_titles = array(
        'full_report'   => array( 'Website Speed Test Full Report',   'Detailed performance analysis and recommendations.',  'gauge' ),
        'opportunities' => array( 'Website Speed Test Opportunities', "Detailed optimization opportunities to improve your website's performance.", 'gauge' ),
        'waterfall'     => array( 'Website Resource Inventory',       'Server-side inventory of discoverable page resources and limited individual probes.', 'gauge' ),
    );
    if ( isset( $view_titles[ $view ] ) ) {
        $title     = $view_titles[ $view ][0];
        $long      = $view_titles[ $view ][1];
        $icon_name = $view_titles[ $view ][2];
    }
}

// "How it works" steps per tool
$hiw_data = array(
    'age-calculator' => array(
        array( 'calendar',   'Enter Date of Birth', 'Select your birth date from the calendar.' ),
        array( 'calendar',   'Choose As Of Date',    'Select the date you want to calculate your age on.' ),
        array( 'calculator', 'Click Calculate',      'Our tool will instantly calculate your age.' ),
        array( 'check',      'View Results',         'See your age in years, months, days and more.' ),
    ),
    'percentage-calculator' => array(
        array( 'grid',       'Pick a Mode',     'Choose from 5 different calculation modes.' ),
        array( 'word',       'Enter Values',    'Fill in the numbers — results update live.' ),
        array( 'percent',    'See Result',      'Get the exact percentage answer instantly.' ),
        array( 'copy',       'Copy Result',     'One-click copy your result to clipboard.' ),
    ),
    'emi-calculator' => array(
        array( 'word',       'Enter Loan Details', 'Input principal, rate, and tenure.' ),
        array( 'calculator', 'Get Monthly EMI',    'EMI calculated instantly.' ),
        array( 'grid',       'See Schedule',       'Full amortization month by month.' ),
        array( 'copy',       'Save Report',        'Copy or print your loan summary.' ),
    ),
    'image-converter' => array(
        array( 'upload',     'Upload Image',   'Choose or drag &amp; drop your image file.' ),
        array( 'grid',       'Select Formats', 'Choose the input and output formats.' ),
        array( 'zap',        'Convert',        'Click convert and we\'ll process your image.' ),
        array( 'download',   'Download',       'Download your converted image instantly.' ),
    ),
    'image-resizer' => array(
        array( 'upload',     'Upload Image',     'Upload or drag &amp; drop your image.' ),
        array( 'image',      'Set Dimensions',   'Choose custom size, format &amp; quality.' ),
        array( 'download',   'Resize &amp; Download', 'Click resize and download your new image.' ),
    ),
    'favicon-generator' => array(
        array( 'upload',     'Upload Image', 'Upload your logo or image in PNG, JPG, or SVG.' ),
        array( 'image',      'Customize',    'Choose background type and shape.' ),
        array( 'zap',        'Generate',     'Click generate to create all required sizes.' ),
        array( 'download',   'Download &amp; Use', 'Download the files and add them to your website.' ),
    ),
    'word-counter' => array(
        array( 'word',       'Enter Text',       'Type or paste your text in the editor.' ),
        array( 'grid',       'Get Instant Stats', 'See words, characters, sentences instantly.' ),
        array( 'check',      'Check Time',        'Get estimated reading &amp; speaking time.' ),
        array( 'copy',       'Copy &amp; Use',    'Copy your text or download as .txt.' ),
    ),
    'character-counter' => array(
        array( 'word',       'Enter Text',        'Type or paste your text in the input box.' ),
        array( 'grid',       'Get Instant Stats', 'See characters, words, sentences &amp; more.' ),
        array( 'check',      'Check Limits',      'Compare with limits for social platforms &amp; SEO.' ),
        array( 'star',       'Optimize Content',  'Refine your text to fit the platform needs.' ),
    ),
    'case-converter' => array(
        array( 'word',       'Enter Your Text', 'Type or paste the text you want to convert.' ),
        array( 'case',       'Choose a Case',   'Select one of the 10 case conversion modes.' ),
        array( 'check',      'View Result',     'See your converted text instantly in the output.' ),
        array( 'copy',       'Copy &amp; Use',  'Copy the result and use it anywhere you need.' ),
    ),
    'remove-duplicate-lines' => array(
        array( 'word',       'Paste Your Text',     'Add or paste your text into the input box.' ),
        array( 'duplicate',  'Clean Your Text',     'Remove duplicate lines with one click.' ),
        array( 'copy',       'Copy or Download',    'Copy the cleaned text or download as a .txt file.' ),
    ),
    'remove-extra-spaces' => array(
        array( 'word',       'Paste Your Text',     'Add or paste your text into the input box.' ),
        array( 'spaces',     'Clean Your Text',     'Remove extra spaces with one click.' ),
        array( 'copy',       'Copy or Download',    'Copy the cleaned text or download as a .txt file.' ),
    ),
    'qr-code-generator' => array(
        array( 'qr',         'Choose Type',          'Select the type of QR code you want to create.' ),
        array( 'word',       'Enter Information',    'Provide the required details based on the selected type.' ),
        array( 'image',      'Customize',            'Personalize your QR code with colors, styles and logo.' ),
        array( 'download',   'Generate &amp; Download', 'Preview your QR code and download in your preferred format.' ),
    ),
    'website-uptime-checker' => array(
        array( 'link',       'Enter URL',     'Enter the website URL you want to check.' ),
        array( 'pulse',      'We Check',      'We send a request to the server and analyze the response.' ),
        array( 'check',      'Get Results',   'View the status, response time, and server information.' ),
        array( 'calendar',   'Repeat When Needed', 'Run another on-demand check when you need a fresh observation.' ),
    ),
    'website-speed-test' => array(
        array( 'link',       'Enter URL',    'Enter the website URL you want to test.' ),
        array( 'gauge',      'Run Test',     'We analyze your page performance and speed.' ),
        array( 'grid',       'Get Results',  'View detailed metrics and performance score.' ),
        array( 'check',      'Improve',      'Follow recommendations to improve your site.' ),
    ),
    'http-status-checker' => array(
        array( 'link',       'Enter URL',    'Enter the website URL you want to check.' ),
        array( 'arrow',      'Send Request', 'We send an HTTP request to the server.' ),
        array( 'code',       'Get Response', 'Receive status code, headers, and response details.' ),
        array( 'check',      'View Results', 'See the results and server information instantly.' ),
    ),
    'ssl-checker' => array(
        array( 'globe',      'Enter URL',     'Enter the domain or website URL you want to check.' ),
        array( 'search',     'Check SSL',     'We retrieve and analyze the SSL certificate instantly.' ),
        array( 'shield',     'Verify Details','We verify validity, issuer, expiry date, and other key details.' ),
        array( 'check',      'View Results',  'Get a complete SSL report and security highlights.' ),
    ),
    'dns-lookup' => array(
        array( 'globe',      'Enter Domain',  'Type the domain name you want to lookup.' ),
        array( 'search',     'We Query DNS',  'We query DNS servers to fetch all available records.' ),
        array( 'grid',       'View Results',  'DNS records are organized by type for easy viewing.' ),
        array( 'shield',     'Use the Data',  'Use the information for troubleshooting or setup.' ),
    ),
    'redirect-checker' => array(
        array( 'link',       'Enter URL',      'Enter the website URL you want to check.' ),
        array( 'redirect',   'Trace Redirects','We follow each redirect step by step.' ),
        array( 'code',       'Collect Details','We collect status codes, protocols, and timings.' ),
        array( 'check',      'Show Results',   'View the full redirect chain and final destination.' ),
    ),
    'check-friends-age' => array(
        array( 'user',       'Enter Friend Details', 'Enter your friend\'s name and date of birth.' ),
        array( 'calendar',   'Choose Date',     'Select the date you want to calculate the age on.' ),
        array( 'check',      'Check Age',       'Click the button to get exact age and fun details.' ),
        array( 'heart',      'Explore More',    'View next birthday, zodiac sign, generation, and more.' ),
    ),
    'share-age-result' => array(
        array( 'calculator', 'Calculate Your Age', 'Use our age calculator to generate your detailed age insights.' ),
        array( 'grid',       'Customize Card',   'Choose a theme, select what to include, and add your personal touch.' ),
        array( 'heart',      'Share Instantly',  'Share via link, social media, or download and post anywhere.' ),
        array( 'user',       'Inspire Others',   'Help your friends discover their age insights and celebrate life!' ),
    ),
);

// FAQ per tool
$faq_data = array(
    'age-calculator' => array(
        array( 'How is age calculated?', 'We calculate the exact difference between the two dates, accounting for leap years and varying month lengths.' ),
        array( 'Can I calculate age for a different date?', 'Yes — change the "Calculate Age As Of" date to any past or future date.' ),
        array( 'How should I verify the age result?', 'Check both dates before relying on the result. The calculation accounts for calendar month lengths and leap years, but it can only use the dates entered.' ),
        array( 'How is my date handled?', 'The age calculation runs in the browser. Avoid entering extra personal details because only the dates are needed.' ),
    ),
    'percentage-calculator' => array(
        array( 'What are the 5 calculation modes?', 'Basic % of a number, % change, X is what % of Y, add %, and subtract %.' ),
        array( 'Does it update results automatically?', 'Yes — results update live as you type.' ),
        array( 'Can I copy the result?', 'Yes, every result has a one-click copy button.' ),
        array( 'Can I use it without an account?', 'Yes. The calculator can be used without creating a visitor account.' ),
    ),
    'emi-calculator' => array(
        array( 'What is EMI?', 'EMI (Equated Monthly Instalment) is the fixed amount you pay each month to repay a loan.' ),
        array( 'How is EMI calculated?', 'Using the formula EMI = P × r × (1+r)^n / ((1+r)^n − 1).' ),
        array( 'What does the amortization schedule show?', 'A month-by-month breakdown of principal, interest, and balance.' ),
        array( 'Can I use any currency?', 'The formula is currency-neutral, so use one consistent currency for the loan amount, payment and totals. Verify lender fees and local rules separately.' ),
    ),
    'image-converter' => array(
        array( 'Is there a file size limit for image conversion?', 'Browser memory and device performance set the practical limit. If a large image fails, try a smaller source or a desktop browser.' ),
        array( 'Which image formats are supported?', 'JPG, PNG, and WebP — all conversion combinations are supported.' ),
        array( 'Will converting images reduce quality?', 'There is a small quality drop only with JPG (lossy). PNG and WebP can be lossless.' ),
        array( 'How is the image handled?', 'Conversion is designed to run in the browser for this tool. Keep the original and avoid using sensitive images on a shared device.' ),
    ),
    'image-resizer' => array(
        array( 'Does resizing an image reduce its quality?', 'Downscaling preserves quality well; upscaling may cause minor blurriness.' ),
        array( 'What is the best format for resized images?', 'JPG for photos, PNG for graphics with transparency, WebP for smaller file sizes.' ),
        array( 'Can I resize multiple images at once?', 'Currently this tool processes one image at a time for best results.' ),
        array( 'Is there a limit to the image size I can upload?', 'The practical limit depends on browser memory and image dimensions. Very large images work more reliably on a desktop device.' ),
    ),
    'favicon-generator' => array(
        array( 'What is a favicon?', 'A small icon displayed in browser tabs, bookmarks, and on mobile home screens.' ),
        array( 'Where do I add the favicon to my website?', 'Upload the PNG files and paste the generated link tags in the &lt;head&gt; section.' ),
        array( 'What image works best for a favicon?', 'A square image at least 512×512 px with a simple, recognisable design.' ),
        array( 'Why do I need multiple favicon sizes?', 'Different devices and contexts use different sizes — phones, tablets, and desktops.' ),
    ),
    'word-counter' => array(
        array( 'How does the word counter work?', 'It counts words separated by whitespace and tracks characters, sentences, and paragraphs.' ),
        array( 'Does it count characters with or without spaces?', 'Both — you see total characters AND characters without spaces.' ),
        array( 'Can I copy or download my text?', 'Yes — both options are available below the editor.' ),
        array( 'Is there a limit to the text length?', 'The tool is intended for normal articles and documents. Extremely large text can be slower depending on browser memory and device performance.' ),
    ),
    'character-counter' => array(
        array( "What's the difference between total characters and characters (no spaces)?", 'Total includes every character; "no spaces" excludes spaces, tabs, and line breaks.' ),
        array( 'Do line breaks and paragraphs count in the total character count?', 'Yes — line breaks are counted as characters in the total.' ),
        array( 'How is the reading time calculated?', 'Based on an average reading speed of 200 words per minute.' ),
        array( 'How is my text handled?', 'Counting runs in the browser on this page. Avoid pasting confidential text into any shared or untrusted device.' ),
    ),
    'case-converter' => array(
        array( 'What is case conversion?', 'Changing text between different capitalisation styles like uppercase, lowercase, Title Case, etc.' ),
        array( 'How many conversion modes are available?', '10 popular modes including UPPER, lower, Title, Sentence, camelCase, PascalCase, snake_case, kebab-case, and more.' ),
        array( 'Is my data stored on your servers?', 'No — all conversion happens in your browser.' ),
        array( 'Can I use this tool on my mobile device?', 'Yes — fully responsive and works on all modern phones.' ),
        array( 'Is there a character limit?', 'The tool is intended for ordinary drafts and documents. Very large input can be limited by the browser or device.' ),
    ),
    'remove-duplicate-lines' => array(
        array( 'What does Remove Duplicate Lines do?', 'It deletes any line that appears more than once, keeping only one copy.' ),
        array( 'What is the difference between duplicate lines and extra spaces?', 'Duplicate lines means identical full lines; extra spaces means whitespace within or around lines.' ),
        array( 'How is my text handled?', 'This text-cleaning action runs locally in the browser. Check the result before replacing your original text.' ),
        array( 'Can I undo the changes?', 'Your original text remains in the input box — simply copy from there to restore.' ),
        array( 'Does it work on large text?', 'It can handle substantial plain text, but processing time and memory use depend on the number and length of lines.' ),
    ),
    'remove-extra-spaces' => array(
        array( 'What does Remove Extra Spaces do?', 'It collapses multiple spaces into single spaces and can trim line whitespace.' ),
        array( 'Will it remove all spaces?', 'No — it only removes extra/redundant whitespace, keeping the text readable.' ),
        array( 'Can I remove blank lines too?', 'Yes — toggle the "Remove blank lines" option.' ),
        array( 'Is there a length limit?', 'Browser memory sets the practical limit. For very large documents, work on a copy and process smaller sections if needed.' ),
    ),
    'qr-code-generator' => array(
        array( 'Can I generate a QR code without an account?', 'Yes. This page does not require a visitor account. The destination or service encoded in the QR code can have its own terms.' ),
        array( 'Can I edit my QR code after generating it?', 'You can change the data and regenerate. Once downloaded, the image itself is fixed.' ),
        array( 'What is the best error correction level to use?', 'Medium (15%) works well for most cases and balances size with reliability.' ),
        array( 'Can I add a logo to my QR code?', 'Yes — upload a logo and adjust its size in the customisation panel.' ),
    ),
    'website-uptime-checker' => array(
        array( 'What does "uptime" mean?', 'Uptime refers to the time a website is online and accessible. Higher uptime means better availability.' ),
        array( 'How often is the website checked?', 'Each click starts an on-demand check. This page is not a continuous monitoring or alerting service.' ),
        array( 'What if the website is behind a CDN?', 'We check from your server location — most CDNs respond globally so results should reflect real availability.' ),
        array( 'Why is the response time important?', 'Faster response times mean better user experience and improved SEO.' ),
        array( 'Can I monitor my website regularly?', 'This tool gives on-demand checks. For continuous monitoring, you would need a dedicated service.' ),
    ),
    'website-speed-test' => array(
        array( 'What does the performance score mean?', 'When a configured performance provider returns a score, use it as a diagnostic snapshot for the selected page and device. Compare like-for-like tests rather than treating one run as a guarantee.' ),
        array( 'What happens if lab performance data is unavailable?', 'The page may show clearly labelled live server or page diagnostics instead. It does not invent Lighthouse or Core Web Vitals measurements.' ),
        array( 'Can I test pages behind login or protected pages?', 'No — only publicly accessible pages can be tested.' ),
        array( 'How often should I test my website speed?', 'After major changes, or once a month for ongoing monitoring.' ),
    ),
    'http-status-checker' => array(
        array( 'What is an HTTP status code?', 'A 3-digit code returned by a server indicating the result of an HTTP request — 200 means success, 404 means not found, etc.' ),
        array( 'Why is my website returning a 301 redirect?', 'A 301 indicates the URL has permanently moved. The browser will follow the new location automatically.' ),
        array( 'What does a 404 error mean?', 'The page you requested does not exist on the server. Check the URL for typos or update internal links.' ),
        array( 'What causes a 500 server error?', 'Server-side issues — misconfigurations, crashed services, or unhandled exceptions in code.' ),
        array( 'Does this tool check HTTPS websites?', 'Yes — both HTTP and HTTPS URLs are fully supported.' ),
    ),
    'ssl-checker' => array(
        array( 'What is an SSL certificate?', 'A digital certificate that encrypts data between a browser and a website, enabling HTTPS.' ),
        array( 'Why is SSL important for my website?', 'A valid certificate enables HTTPS and helps browsers authenticate the site and encrypt traffic in transit.' ),
        array( 'What does it mean if my SSL certificate is expired?', 'Browsers will show security warnings to visitors. Renew immediately to avoid losing traffic.' ),
        array( 'How often should I check my SSL certificate?', 'At least once a month, or set up automatic renewal alerts with your certificate provider.' ),
    ),
    'dns-lookup' => array(
        array( 'What is DNS lookup?', 'DNS lookup queries name servers to retrieve information about a domain — IP addresses, mail servers, etc.' ),
        array( 'Why would I need to lookup DNS records?', 'For troubleshooting connectivity, verifying email setup, configuring services, or checking propagation after changes.' ),
        array( 'How often is the DNS data updated?', 'DNS records are cached based on their TTL — changes can take from minutes to 48 hours to propagate globally.' ),
        array( 'What do different DNS record types mean?', 'A maps to IPv4, AAAA to IPv6, MX for mail, TXT for verification/SPF, CNAME for aliases, NS for name servers.' ),
    ),
    'redirect-checker' => array(
        array( 'What is a redirect?', 'A redirect sends visitors and search engines from one URL to another — used when content moves or for tracking.' ),
        array( 'What are the different types of redirects?', '301 (permanent), 302 (temporary), 303 (see other), 307 (temporary preserve method), 308 (permanent preserve method).' ),
        array( 'Why should I check redirects?', 'Too many redirects slow page loads and can confuse search engines. Direct links are always best.' ),
        array( 'How many redirects are too many?', 'Browsers usually follow up to 10-20 redirects. SEO best practice is to keep chains to 1-2 hops maximum.' ),
        array( 'Does this tool check JavaScript redirects?', 'No — only server-side HTTP redirects (3xx status codes) are traced.' ),
    ),
    'check-friends-age' => array(
        array( 'How should I verify a friend age result?', 'Check the entered birth date and comparison date. The calculation accounts for calendar month lengths and leap years, but it cannot correct an incorrect date.' ),
        array( 'What details will I get about my friend?', 'You get exact age (years, months, days), total days, next birthday countdown, zodiac sign, and generation.' ),
        array( 'Can I share my friend\'s age result?', 'Yes — use the share buttons to copy result text, share via WhatsApp, or add a calendar reminder.' ),
        array( 'How is my friend\'s data handled?', 'The age calculation runs in the browser. Ask permission before entering or sharing another person\'s details, and include only what the tool needs.' ),
    ),
    'share-age-result' => array(
        array( 'Can I create a share result without an account?', 'Yes. The page does not require a visitor account.' ),
        array( 'Can I edit my share card after generating it?', 'Yes — customize name, caption, theme, and included sections anytime.' ),
        array( 'What formats are available for download?', 'You can download as PNG image or copy as plain text.' ),
        array( 'What should I check before sharing?', 'The card is prepared in the browser, but anything you share can be copied by its recipients. Remove personal details you do not want to make public.' ),
    ),
);

// Per-tool hero illustration SVG
$illos = array(
    'age-calculator'       => 'calendar',
    'percentage-calculator'=> 'percent',
    'emi-calculator'       => 'bank',
    'image-converter'      => 'image-convert',
    'image-resizer'        => 'image-resize',
    'favicon-generator'    => 'favicon',
    'word-counter'         => 'word-illo',
    'character-counter'    => 'char-illo',
    'case-converter'       => 'case-illo',
    'remove-duplicate-lines' => 'cleaner',
    'remove-extra-spaces'  => 'cleaner',
    'qr-code-generator'    => 'qr-illo',
    'website-uptime-checker' => 'uptime',
    'website-speed-test'    => 'gauge',
    'http-status-checker'   => 'http',
    'ssl-checker'           => 'ssl',
    'dns-lookup'            => 'dns',
    'redirect-checker'      => 'redirect',
    'check-friends-age'     => 'calendar',
    'share-age-result'      => 'calendar',
);
$illo_name = isset( $illos[ $slug ] ) ? $illos[ $slug ] : 'word-illo';

$steps = isset( $hiw_data[ $slug ] ) ? $hiw_data[ $slug ] : array();
$faqs  = isset( $faq_data[ $slug ] ) ? $faq_data[ $slug ] : array();
$quality_profile = alltools_tool_quality_profile( $slug, $tool );
if ( ! $steps ) $steps = alltools_tool_default_steps( $slug, $tool );
if ( ! $faqs ) $faqs = alltools_tool_default_faqs( $slug, $tool );
$editorial_content = alltools_tool_editorial_content( $slug );
$manual_content = trim( wp_strip_all_tags( get_the_content() ) );
$registry_content = ! empty( $tool['long'] ) ? trim( wp_strip_all_tags( $tool['long'] ) ) : '';
$is_managed_plugin_copy = function_exists( 'ufxots_generated_post_content' )
    && get_post_meta( get_the_ID(), '_ufxots_managed', true )
    && trim( (string) get_the_content() ) === trim( ufxots_generated_post_content( $tool ) );
$has_manual_content = ! $is_managed_plugin_copy && $manual_content && $manual_content !== $registry_content && str_word_count( $manual_content ) >= 40;
$related = alltools_public_related( $slug, 5 );
?>

<section class="ufx-tool-title" aria-labelledby="ufx-tool-heading">
  <div class="at-container">
    <nav class="ufx-tool-breadcrumb" aria-label="Breadcrumb">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">›</span>
      <a href="<?php echo esc_url( $category ? alltools_category_url( $category ) : get_post_type_archive_link( 'alltool' ) ); ?>"><?php echo esc_html( $category ? alltools_cat_label( $category ) : __( 'All tools', 'alltools' ) ); ?></a><span aria-hidden="true">›</span>
      <span aria-current="page"><?php echo esc_html( $title ); ?></span>
    </nav>
    <p class="ufx-tool-eyebrow"><?php echo esc_html( $category ? alltools_cat_label( $category ) : __( 'Everyday tools', 'alltools' ) ); ?></p>
    <h1 id="ufx-tool-heading"><?php echo esc_html( $title ); ?></h1>
    <?php if ( $long ) : ?><p class="ufx-tool-subtitle"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $long ), 24 ) ); ?></p><?php endif; ?>
  </div>
</section>

<!-- ═════════ TOOL INTERFACE ═════════ -->
<section class="at-tool-section">
    <div class="at-container">
        <?php
        $view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : '';
        if ( $slug === 'website-speed-test' && $view ) {
            $sub_fn = 'alltools_tool_website_speed_test_' . str_replace( '-', '_', $view );
            if ( function_exists( $sub_fn ) ) {
                call_user_func( $sub_fn );
            } else {
                alltools_tool_website_speed_test();
            }
        } else {
            $fn_name = 'alltools_tool_' . str_replace( '-', '_', $slug );
            if ( function_exists( $fn_name ) ) {
                call_user_func( $fn_name );
            } else {
                $rendered = apply_filters( 'alltools_render_tool', false, $slug );
                if ( ! $rendered ) {
                    echo '<div class="at-card"><p>' . esc_html__( 'Tool interface is being prepared.', 'alltools' ) . '</p></div>';
                }
            }
        }
        ?>
    </div>
</section>

<div class="at-container"><div class="ufx-tool-benefits"><div><?php echo alltools_icon_svg('zap',24); ?><span><strong>Simple controls</strong><small>Everything you need for the task.</small></span></div><div><?php echo alltools_icon_svg('check',24); ?><span><strong>Clear results</strong><small>Review your output before using it.</small></span></div><div><?php echo alltools_icon_svg('user',24); ?><span><strong>No signup</strong><small>Get started without an account.</small></span></div></div></div>
<!-- ═════════ HOW IT WORKS ═════════ -->
<?php if ( $steps ) : ?>
<section class="at-section">
    <div class="at-container">
        <h2>How it works</h2>
        <div class="at-card at-card-pad-lg">
            <div class="at-hiw-grid" data-steps="<?php echo count( $steps ); ?>">
                <?php
                $palette = array(
                    array( 'bg' => '#D1FAE5', 'fg' => '#10B981' ),
                    array( 'bg' => '#DBEAFE', 'fg' => '#2563EB' ),
                    array( 'bg' => '#EDE9FE', 'fg' => '#8B5CF6' ),
                    array( 'bg' => '#FED7AA', 'fg' => '#F97316' ),
                );
                foreach ( $steps as $i => $step ) :
                    $p = $palette[ $i % 4 ];
                ?>
                <div class="at-hiw-step">
                    <div class="at-hiw-icon" style="background:<?php echo esc_attr( $p['bg'] ); ?>;color:<?php echo esc_attr( $p['fg'] ); ?>;">
                        <?php echo alltools_icon_svg( $step[0], 22 ); ?>
                    </div>
                    <h4><?php echo (int)( $i + 1 ); ?>. <?php echo wp_kses_post( $step[1] ); ?></h4>
                    <p><?php echo wp_kses_post( $step[2] ); ?></p>
                </div>
                <?php if ( $i < count( $steps ) - 1 ) : ?>
                <div class="at-hiw-arrow" aria-hidden="true"><?php echo alltools_icon_svg( 'arrow', 18 ); ?></div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═════════ RELATED TOOLS ═════════ -->
<?php if ( $related ) :
    $cat_label = $category ? alltools_cat_label( $category ) : '';
?>
<section class="at-section">
    <div class="at-container">
        <div class="at-section-head-row">
            <h2><?php printf( esc_html__( 'Related %s', 'alltools' ), esc_html( $cat_label ) ); ?></h2>
            <a href="<?php echo esc_url( alltools_category_url( $category ) ); ?>" class="at-link"><?php printf( esc_html__( 'View All %s →', 'alltools' ), esc_html( $cat_label ) ); ?></a>
        </div>
        <div class="at-grid-5">
            <?php foreach ( $related as $r_slug => $r_tool ) :
                $rc = alltools_color( $r_tool['color'] );
                $rurl = alltools_tool_url( $r_slug );
            ?>
            <article class="at-tool-card">
                <div class="at-tool-card-top">
                    <div class="at-tool-icon" style="background:<?php echo esc_attr( $rc['bg'] ); ?>;color:<?php echo esc_attr( $rc['fg'] ); ?>;">
                        <?php echo alltools_icon_svg( $r_tool['icon'], 18 ); ?>
                    </div>
                    <?php if ( ! empty( $r_tool['badge'] ) ) : ?>
                        <span class="at-tool-badge"><?php echo esc_html( $r_tool['badge'] ); ?></span>
                    <?php endif; ?>
                </div>
                <h3><?php echo esc_html( $r_tool['title'] ); ?></h3>
                <p><?php echo esc_html( $r_tool['desc'] ); ?></p>
                <a href="<?php echo esc_url( $rurl ); ?>" class="at-tool-cta" style="color:<?php echo esc_attr( $rc['fg'] ); ?>;border-color:<?php echo esc_attr( $rc['fg'] ); ?>;">
                    <?php echo esc_html( $r_tool['cta'] ); ?>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═════════ FAQ ═════════ -->
<?php if ( $faqs ) : ?>
<section class="at-section" id="faq">
    <div class="at-container">
        <h2>Frequently Asked Questions</h2>
        <div class="at-faq-list">
            <?php foreach ( $faqs as $i => $faq ) : ?>
            <div class="at-faq-item">
                <button class="at-faq-q" aria-expanded="false" type="button">
                    <span><?php echo esc_html( $faq[0] ); ?></span>
                    <span class="at-faq-chev"><?php echo alltools_icon_svg('chevron',18); ?></span>
                </button>
                <div class="at-faq-a"><p><?php echo wp_kses_post( $faq[1] ); ?></p></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═════════ TASK-SPECIFIC GUIDANCE ═════════ -->
<section class="at-section">
    <div class="at-container">
        <h2><?php printf( esc_html__( 'About %s', 'alltools' ), esc_html( $title ) ); ?></h2>
        <div class="at-card at-card-pad-lg">
            <p><?php echo esc_html( $editorial_content ? $editorial_content[0] : $quality_profile['intro'] ); ?></p>
            <?php if ( $has_manual_content ) : ?>
                <div class="at-editorial-content"><?php the_content(); ?></div>
            <?php endif; ?>
        </div>

        <div class="at-guidance-grid">
            <article class="at-card at-guidance-card">
                <span class="at-guidance-label"><?php esc_html_e( 'Input', 'alltools' ); ?></span>
                <h3><?php esc_html_e( 'What to prepare', 'alltools' ); ?></h3>
                <p><?php echo esc_html( $quality_profile['input'] ); ?></p>
            </article>
            <article class="at-card at-guidance-card">
                <span class="at-guidance-label"><?php esc_html_e( 'Output', 'alltools' ); ?></span>
                <h3><?php esc_html_e( 'What the result represents', 'alltools' ); ?></h3>
                <p><?php echo esc_html( $quality_profile['output'] ); ?></p>
            </article>
            <article class="at-card at-guidance-card">
                <span class="at-guidance-label"><?php esc_html_e( 'Typical use', 'alltools' ); ?></span>
                <h3><?php esc_html_e( 'A practical example', 'alltools' ); ?></h3>
                <p><?php echo esc_html( $quality_profile['example'] ); ?></p>
                <ul class="at-quality-list">
                    <?php foreach ( $quality_profile['use_cases'] as $use_case ) : ?><li><?php echo esc_html( $use_case ); ?></li><?php endforeach; ?>
                </ul>
            </article>
            <article class="at-card at-guidance-card at-guidance-caution">
                <span class="at-guidance-label"><?php esc_html_e( 'Verification', 'alltools' ); ?></span>
                <h3><?php esc_html_e( 'Before you use the result', 'alltools' ); ?></h3>
                <ul class="at-quality-list">
                    <?php if ( $editorial_content ) : ?>
                        <?php foreach ( $editorial_content[1] as $tip ) : ?><li><?php echo esc_html( $tip ); ?></li><?php endforeach; ?>
                    <?php else : ?>
                        <?php foreach ( $quality_profile['checks'] as $check ) : ?><li><?php echo esc_html( $check ); ?></li><?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </article>
        </div>
        <div class="at-card at-guidance-card at-guidance-grid-detail">
            <span class="at-guidance-label"><?php esc_html_e( 'Privacy and processing', 'alltools' ); ?></span>
            <p><?php echo esc_html( $quality_profile['privacy'] ); ?></p>
        </div>
    </div>
</section>

<?php
endwhile;

get_footer();

/**
 * Render a tool hero illustration by name.
 */
function alltools_render_illo( $name, $colors ) {
    $fg = isset( $colors['fg'] ) ? $colors['fg'] : '#2563EB';
    $bg = isset( $colors['bg'] ) ? $colors['bg'] : '#DBEAFE';
    ?>
    <svg viewBox="0 0 360 240" xmlns="http://www.w3.org/2000/svg" class="at-illo-svg" aria-hidden="true">
        <!-- soft purple-pink blob bg -->
        <ellipse cx="220" cy="120" rx="180" ry="110" fill="url(#illo-blob)" opacity="0.55"/>
        <defs>
            <linearGradient id="illo-blob" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#E0E7FF"/>
                <stop offset="100%" stop-color="#FCE7F3"/>
            </linearGradient>
        </defs>

        <!-- Plant -->
        <rect x="30" y="170" width="42" height="34" rx="3" fill="#2563EB"/>
        <path d="M51 170 C 40 140, 25 130, 30 105 C 42 122, 49 140, 51 170 Z" fill="#10B981"/>
        <path d="M51 170 C 62 140, 78 130, 73 105 C 60 122, 53 140, 51 170 Z" fill="#10B981"/>

        <!-- Sparkles -->
        <text x="300" y="40" font-size="14" fill="#8B5CF6" opacity="0.6">✦</text>
        <text x="330" y="80" font-size="10" fill="#EC4899" opacity="0.5">✦</text>
        <text x="290" y="180" font-size="12" fill="#2563EB" opacity="0.4">✦</text>

        <?php if ( $name === 'calendar' ) : ?>
            <!-- Calendar -->
            <rect x="130" y="40" width="140" height="130" rx="8" fill="#fff" stroke="#CBD5E1" stroke-width="2"/>
            <rect x="130" y="40" width="140" height="30" rx="8" fill="#2563EB"/>
            <rect x="130" y="60" width="140" height="10" fill="#2563EB"/>
            <rect x="148" y="32" width="6" height="18" rx="2" fill="#1E293B"/>
            <rect x="246" y="32" width="6" height="18" rx="2" fill="#1E293B"/>
            <rect x="148" y="32" width="6" height="18" rx="2" fill="#1E293B"/>
            <!-- Clock on calendar -->
            <circle cx="240" cy="140" r="22" fill="#fff" stroke="#10B981" stroke-width="3"/>
            <line x1="240" y1="140" x2="240" y2="124" stroke="#10B981" stroke-width="2.5"/>
            <line x1="240" y1="140" x2="252" y2="140" stroke="#10B981" stroke-width="2.5"/>
            <!-- Calendar dots -->
            <g fill="#E2E8F0">
                <circle cx="150" cy="90" r="3"/><circle cx="170" cy="90" r="3"/><circle cx="190" cy="90" r="3"/><circle cx="210" cy="90" r="3"/>
                <circle cx="150" cy="110" r="3"/><circle cx="170" cy="110" r="3"/><circle cx="190" cy="110" r="3"/>
            </g>

        <?php elseif ( $name === 'percent' ) : ?>
            <circle cx="200" cy="120" r="65" fill="#fff" stroke="#8B5CF6" stroke-width="3"/>
            <text x="200" y="142" text-anchor="middle" font-size="56" font-weight="800" fill="#8B5CF6">%</text>
            <text x="280" y="80" font-size="20" font-weight="700" fill="#10B981">+</text>
            <text x="120" y="180" font-size="20" font-weight="700" fill="#EC4899">−</text>

        <?php elseif ( $name === 'bank' ) : ?>
            <!-- Bank building -->
            <polygon points="200,40 130,80 270,80" fill="#F97316"/>
            <rect x="130" y="80" width="140" height="100" fill="#FED7AA"/>
            <rect x="130" y="170" width="140" height="14" fill="#F97316"/>
            <rect x="150" y="100" width="12" height="60" fill="#F97316"/>
            <rect x="178" y="100" width="12" height="60" fill="#F97316"/>
            <rect x="206" y="100" width="12" height="60" fill="#F97316"/>
            <rect x="234" y="100" width="12" height="60" fill="#F97316"/>
            <text x="200" y="68" text-anchor="middle" font-size="18" fill="#fff" font-weight="700">$</text>

        <?php elseif ( $name === 'image-convert' ) : ?>
            <!-- two doc icons + arrows -->
            <rect x="130" y="70" width="70" height="90" rx="6" fill="#8B5CF6"/>
            <polygon points="180,70 200,70 200,90 180,90" fill="#A78BFA"/>
            <rect x="146" y="100" width="38" height="28" rx="3" fill="#fff" opacity="0.4"/>
            <circle cx="155" cy="112" r="3" fill="#fff"/>
            <path d="M148 124 L 162 110 L 184 124 Z" fill="#fff" opacity="0.6"/>

            <rect x="240" y="70" width="70" height="90" rx="6" fill="#2563EB"/>
            <polygon points="290,70 310,70 310,90 290,90" fill="#3B82F6"/>
            <rect x="256" y="100" width="38" height="28" rx="3" fill="#fff" opacity="0.4"/>
            <circle cx="265" cy="112" r="3" fill="#fff"/>
            <path d="M258 124 L 272 110 L 294 124 Z" fill="#fff" opacity="0.6"/>

            <!-- Arrows in middle -->
            <circle cx="220" cy="115" r="22" fill="#10B981"/>
            <path d="M210 112 L 218 108 L 218 116 Z" fill="#fff"/>
            <path d="M230 118 L 222 122 L 222 114 Z" fill="#fff"/>

        <?php elseif ( $name === 'image-resize' ) : ?>
            <!-- image with selection -->
            <rect x="140" y="60" width="130" height="100" rx="6" fill="#DBEAFE" stroke="#2563EB" stroke-width="2" stroke-dasharray="4 3"/>
            <polygon points="155,140 180,110 200,125 230,90 255,140" fill="#2563EB"/>
            <circle cx="215" cy="85" r="8" fill="#FBBF24"/>
            <!-- corner handles -->
            <?php foreach ( array( array(140,60), array(270,60), array(140,160), array(270,160) ) as $pt ): ?>
                <rect x="<?php echo $pt[0]-5; ?>" y="<?php echo $pt[1]-5; ?>" width="10" height="10" fill="#2563EB"/>
            <?php endforeach; ?>
            <!-- crop tool icon -->
            <rect x="285" y="155" width="38" height="38" rx="6" fill="#8B5CF6"/>
            <path d="M295 165 L 295 185 L 315 185" stroke="#fff" stroke-width="2" fill="none"/>
            <path d="M315 175 L 315 165 L 305 165" stroke="#fff" stroke-width="2" fill="none"/>

        <?php elseif ( $name === 'favicon' ) : ?>
            <!-- Browser window -->
            <rect x="120" y="50" width="180" height="130" rx="6" fill="#fff" stroke="#CBD5E1" stroke-width="2"/>
            <rect x="120" y="50" width="180" height="22" rx="6" fill="#F1F5F9"/>
            <circle cx="132" cy="61" r="3" fill="#EF4444"/>
            <circle cx="142" cy="61" r="3" fill="#F59E0B"/>
            <circle cx="152" cy="61" r="3" fill="#10B981"/>
            <!-- Tab -->
            <rect x="160" y="44" width="80" height="30" rx="6" fill="#fff" stroke="#CBD5E1" stroke-width="2"/>
            <rect x="168" y="55" width="14" height="14" rx="3" fill="#2563EB"/>
            <text x="175" y="66" text-anchor="middle" font-size="10" fill="#fff">★</text>
            <text x="195" y="65" font-size="9" fill="#1E293B" font-weight="600">Your Website</text>
            <text x="234" y="65" font-size="11" fill="#94A3B8">×</text>
            <text x="245" y="65" font-size="14" fill="#94A3B8">+</text>
            <!-- magnifying glass -->
            <circle cx="280" cy="160" r="28" fill="none" stroke="#1E293B" stroke-width="4"/>
            <line x1="298" y1="178" x2="315" y2="195" stroke="#1E293B" stroke-width="5" stroke-linecap="round"/>
            <rect x="266" y="146" width="28" height="28" rx="5" fill="#2563EB"/>
            <text x="280" y="167" text-anchor="middle" font-size="18" fill="#fff">★</text>

        <?php elseif ( $name === 'word-illo' ) : ?>
            <!-- laptop with W -->
            <rect x="125" y="55" width="170" height="110" rx="8" fill="#1E293B"/>
            <rect x="133" y="63" width="154" height="94" rx="4" fill="#fff"/>
            <rect x="105" y="165" width="210" height="14" rx="3" fill="#94A3B8"/>
            <rect x="155" y="80" width="36" height="36" rx="6" fill="#D1FAE5"/>
            <text x="173" y="106" text-anchor="middle" font-size="22" font-weight="800" fill="#10B981">W</text>
            <rect x="200" y="85" width="80" height="6" rx="2" fill="#E2E8F0"/>
            <rect x="200" y="98" width="60" height="6" rx="2" fill="#E2E8F0"/>
            <rect x="200" y="111" width="70" height="6" rx="2" fill="#E2E8F0"/>
            <!-- 123 badge -->
            <rect x="260" y="140" width="50" height="22" rx="11" fill="#10B981"/>
            <text x="285" y="156" text-anchor="middle" font-size="13" font-weight="800" fill="#fff">123</text>

        <?php elseif ( $name === 'char-illo' ) : ?>
            <rect x="125" y="55" width="170" height="110" rx="8" fill="#fff" stroke="#CBD5E1" stroke-width="2"/>
            <rect x="125" y="55" width="170" height="20" rx="8" fill="#F1F5F9"/>
            <circle cx="135" cy="65" r="3" fill="#EF4444"/>
            <circle cx="145" cy="65" r="3" fill="#F59E0B"/>
            <circle cx="155" cy="65" r="3" fill="#10B981"/>
            <rect x="145" y="90" width="60" height="60" rx="8" fill="#EDE9FE"/>
            <text x="175" y="135" text-anchor="middle" font-size="38" font-weight="800" fill="#8B5CF6">Aa</text>
            <rect x="225" y="140" width="60" height="22" rx="11" fill="#10B981"/>
            <text x="255" y="156" text-anchor="middle" font-size="13" font-weight="800" fill="#fff">1234</text>

        <?php elseif ( $name === 'case-illo' ) : ?>
            <rect x="155" y="55" width="160" height="110" rx="8" fill="#fff" stroke="#CBD5E1" stroke-width="2"/>
            <rect x="155" y="55" width="160" height="20" rx="8" fill="#F1F5F9"/>
            <circle cx="165" cy="65" r="3" fill="#EF4444"/>
            <circle cx="175" cy="65" r="3" fill="#F59E0B"/>
            <circle cx="185" cy="65" r="3" fill="#10B981"/>
            <rect x="170" y="88" width="38" height="38" rx="6" fill="#FED7AA"/>
            <text x="189" y="115" text-anchor="middle" font-size="20" font-weight="800" fill="#F97316">Aa</text>
            <rect x="215" y="95" width="80" height="6" rx="2" fill="#E2E8F0"/>
            <rect x="215" y="108" width="60" height="6" rx="2" fill="#E2E8F0"/>
            <rect x="215" y="121" width="70" height="6" rx="2" fill="#E2E8F0"/>
            <!-- floating labels -->
            <rect x="110" y="80" width="80" height="22" rx="11" fill="#fff" stroke="#10B981" stroke-width="2"/>
            <text x="150" y="96" text-anchor="middle" font-size="11" font-weight="700" fill="#10B981">UPPERCASE</text>
            <rect x="245" y="140" width="68" height="22" rx="11" fill="#fff" stroke="#2563EB" stroke-width="2"/>
            <text x="279" y="156" text-anchor="middle" font-size="11" font-weight="700" fill="#2563EB">Title Case</text>
            <rect x="220" y="175" width="80" height="22" rx="11" fill="#FCE7F3"/>
            <text x="260" y="191" text-anchor="middle" font-size="11" font-weight="700" fill="#EC4899">snake_case</text>
            <text x="320" y="80" font-size="48" font-weight="900" fill="#8B5CF6" opacity="0.7">T</text>

        <?php elseif ( $name === 'cleaner' ) : ?>
            <!-- webpage -->
            <rect x="140" y="50" width="160" height="120" rx="8" fill="#fff" stroke="#CBD5E1" stroke-width="2"/>
            <rect x="140" y="50" width="160" height="20" rx="8" fill="#F1F5F9"/>
            <circle cx="150" cy="60" r="3" fill="#EF4444"/>
            <circle cx="160" cy="60" r="3" fill="#F59E0B"/>
            <circle cx="170" cy="60" r="3" fill="#10B981"/>
            <!-- checkmark lines -->
            <g transform="translate(155,85)">
                <?php for ( $r = 0; $r < 5; $r++ ) :
                    $cy = $r * 14; ?>
                <circle cx="6" cy="<?php echo $cy + 5; ?>" r="6" fill="#D1FAE5"/>
                <path d="M3 <?php echo $cy + 5; ?> L 6 <?php echo $cy + 8; ?> L 10 <?php echo $cy + 2; ?>" stroke="#10B981" stroke-width="2" fill="none" stroke-linecap="round"/>
                <rect x="18" y="<?php echo $cy + 2; ?>" width="<?php echo 70 - $r * 5; ?>" height="6" rx="2" fill="#E2E8F0"/>
                <?php endfor; ?>
            </g>
            <!-- broom -->
            <g transform="translate(265,90) rotate(20)">
                <rect x="0" y="0" width="6" height="50" rx="3" fill="#8B5CF6"/>
                <path d="M -8 50 L 14 50 L 18 75 L -12 75 Z" fill="#3B82F6"/>
                <line x1="-8" y1="60" x2="-10" y2="75" stroke="#1E40AF" stroke-width="1"/>
                <line x1="-2" y1="60" x2="-3" y2="75" stroke="#1E40AF" stroke-width="1"/>
                <line x1="3" y1="60" x2="3" y2="75" stroke="#1E40AF" stroke-width="1"/>
                <line x1="8" y1="60" x2="9" y2="75" stroke="#1E40AF" stroke-width="1"/>
                <line x1="14" y1="60" x2="16" y2="75" stroke="#1E40AF" stroke-width="1"/>
            </g>

        <?php elseif ( $name === 'qr-illo' ) : ?>
            <!-- phone -->
            <rect x="120" y="50" width="80" height="150" rx="14" fill="#1E293B"/>
            <rect x="126" y="62" width="68" height="120" rx="4" fill="#fff"/>
            <!-- phone QR -->
            <g transform="translate(140,75)">
                <?php
                $pattern = array(
                    '11100011','10110101','10001011','11011110','01001011','10110001','11100110','01011011',
                );
                foreach ( $pattern as $row => $bits ) :
                    for ( $col = 0; $col < strlen( $bits ); $col++ ) :
                        if ( $bits[ $col ] === '1' ) :
                ?>
                <rect x="<?php echo $col * 5; ?>" y="<?php echo $row * 5; ?>" width="5" height="5" fill="#1E293B"/>
                <?php
                        endif;
                    endfor;
                endforeach;
                ?>
            </g>
            <rect x="135" y="155" width="50" height="20" rx="10" fill="#8B5CF6"/>
            <text x="160" y="169" text-anchor="middle" font-size="10" font-weight="700" fill="#fff">Scan Me</text>

            <!-- big QR next to phone -->
            <rect x="220" y="60" width="120" height="120" rx="6" fill="#fff" stroke="#CBD5E1" stroke-width="2"/>
            <g transform="translate(232,72)">
                <?php
                $bigpat = array(
                    '1111111001011111111','1000001011001000001','1011101110001011101','1011101011101011101',
                    '1011101100011011101','1000001001101000001','1111111010101111111','0000000111000000000',
                    '1101011000111100100','0011100110111011010','1010101001100110001','0100110010001000011',
                    '0000000111101010111','1111111001000010101','1000001110011110001','1011101011001110101',
                    '1011101010100110101','1000001001101000111','1111111100011110011',
                );
                foreach ( $bigpat as $row => $bits ) :
                    for ( $col = 0; $col < strlen( $bits ); $col++ ) :
                        if ( $bits[ $col ] === '1' ) :
                ?>
                <rect x="<?php echo $col * 5; ?>" y="<?php echo $row * 5; ?>" width="5" height="5" fill="#1E293B"/>
                <?php
                        endif;
                    endfor;
                endforeach;
                ?>
            </g>

        <?php elseif ( $name === 'uptime' ) : ?>
            <!-- Browser window with online check -->
            <rect x="130" y="50" width="180" height="120" rx="10" fill="#fff" stroke="#CBD5E1" stroke-width="2"/>
            <rect x="130" y="50" width="180" height="22" rx="10" fill="#F1F5F9"/>
            <circle cx="142" cy="61" r="3" fill="#EF4444"/><circle cx="152" cy="61" r="3" fill="#F59E0B"/><circle cx="162" cy="61" r="3" fill="#10B981"/>
            <rect x="148" y="86" width="144" height="22" rx="11" fill="#F1F5F9" stroke="#E2E8F0"/>
            <text x="158" y="101" font-size="11" font-family="ui-monospace,monospace" fill="#64748B">https://example.com</text>
            <!-- Online badge with pulse -->
            <circle cx="180" cy="140" r="20" fill="none" stroke="#10B981" stroke-width="3"/>
            <path d="M 168 138 L 172 144 L 180 134 L 188 144 L 192 138" stroke="#10B981" stroke-width="2" fill="none" stroke-linecap="round"/>
            <rect x="220" y="128" width="70" height="24" rx="12" fill="#D1FAE5"/>
            <text x="255" y="144" text-anchor="middle" font-size="11" font-weight="700" fill="#10B981">ONLINE</text>
            <!-- Shield -->
            <path d="M 280 170 L 290 165 L 300 170 L 300 185 Q 290 195 280 185 Z" fill="#2563EB"/>
            <path d="M 285 177 L 289 181 L 295 174" stroke="#fff" stroke-width="2" fill="none"/>

        <?php elseif ( $name === 'gauge' ) : ?>
            <!-- Browser window with speedometer -->
            <rect x="130" y="50" width="180" height="120" rx="10" fill="#fff" stroke="#CBD5E1" stroke-width="2"/>
            <rect x="130" y="50" width="180" height="20" rx="10" fill="#F1F5F9"/>
            <circle cx="142" cy="60" r="3" fill="#EF4444"/><circle cx="152" cy="60" r="3" fill="#F59E0B"/><circle cx="162" cy="60" r="3" fill="#10B981"/>
            <rect x="140" y="80" width="100" height="6" rx="2" fill="#E2E8F0"/>
            <rect x="140" y="92" width="80" height="6" rx="2" fill="#E2E8F0"/>
            <!-- Speed gauge -->
            <path d="M 195 155 A 50 50 0 0 1 295 155" fill="none" stroke="#E2E8F0" stroke-width="10" stroke-linecap="round"/>
            <path d="M 195 155 A 50 50 0 0 1 270 115" fill="none" stroke="url(#gauge-grad)" stroke-width="10" stroke-linecap="round"/>
            <defs>
                <linearGradient id="gauge-grad" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0" stop-color="#10B981"/><stop offset="0.5" stop-color="#3B82F6"/><stop offset="1" stop-color="#8B5CF6"/>
                </linearGradient>
            </defs>
            <line x1="245" y1="155" x2="265" y2="120" stroke="#1E293B" stroke-width="3" stroke-linecap="round"/>
            <circle cx="245" cy="155" r="5" fill="#1E293B"/>
            <!-- Side panel -->
            <rect x="260" y="62" width="86" height="60" rx="6" fill="#fff" stroke="#CBD5E1" stroke-width="1"/>
            <rect x="268" y="70" width="42" height="3" rx="1" fill="#10B981"/>
            <rect x="316" y="70" width="22" height="3" rx="1" fill="#10B981"/>
            <rect x="268" y="82" width="38" height="3" rx="1" fill="#F59E0B"/>
            <rect x="316" y="82" width="22" height="3" rx="1" fill="#F59E0B"/>
            <rect x="268" y="94" width="34" height="3" rx="1" fill="#3B82F6"/>
            <rect x="316" y="94" width="22" height="3" rx="1" fill="#3B82F6"/>
            <rect x="268" y="106" width="42" height="3" rx="1" fill="#3B82F6"/>
            <rect x="316" y="106" width="22" height="3" rx="1" fill="#3B82F6"/>

        <?php elseif ( $name === 'http' ) : ?>
            <!-- Browser window + status codes -->
            <rect x="120" y="80" width="120" height="100" rx="8" fill="#fff" stroke="#CBD5E1" stroke-width="2"/>
            <rect x="120" y="80" width="120" height="18" rx="8" fill="#F1F5F9"/>
            <circle cx="130" cy="89" r="2.5" fill="#EF4444"/><circle cx="138" cy="89" r="2.5" fill="#F59E0B"/><circle cx="146" cy="89" r="2.5" fill="#10B981"/>
            <circle cx="180" cy="130" r="20" fill="#DBEAFE"/>
            <circle cx="180" cy="130" r="14" fill="#fff" stroke="#2563EB" stroke-width="2"/>
            <rect x="170" y="124" width="20" height="3" rx="1" fill="#2563EB"/>
            <rect x="170" y="130" width="14" height="3" rx="1" fill="#2563EB"/>
            <rect x="170" y="136" width="18" height="3" rx="1" fill="#2563EB"/>
            <!-- Status code chips -->
            <line x1="240" y1="130" x2="270" y2="105" stroke="#CBD5E1" stroke-width="1.5" stroke-dasharray="3 3"/>
            <line x1="240" y1="130" x2="270" y2="130" stroke="#CBD5E1" stroke-width="1.5" stroke-dasharray="3 3"/>
            <line x1="240" y1="130" x2="270" y2="155" stroke="#CBD5E1" stroke-width="1.5" stroke-dasharray="3 3"/>
            <rect x="270" y="92" width="60" height="26" rx="6" fill="#D1FAE5"/>
            <text x="300" y="109" text-anchor="middle" font-size="14" font-weight="800" fill="#10B981">200</text>
            <rect x="270" y="117" width="60" height="26" rx="6" fill="#DBEAFE"/>
            <text x="300" y="134" text-anchor="middle" font-size="14" font-weight="800" fill="#2563EB">301</text>
            <rect x="270" y="142" width="60" height="26" rx="6" fill="#FED7AA"/>
            <text x="300" y="159" text-anchor="middle" font-size="14" font-weight="800" fill="#F97316">404</text>
            <rect x="270" y="167" width="60" height="26" rx="6" fill="#FEE2E2"/>
            <text x="300" y="184" text-anchor="middle" font-size="14" font-weight="800" fill="#EF4444">500</text>

        <?php elseif ( $name === 'ssl' ) : ?>
            <!-- Browser with lock + shield -->
            <rect x="130" y="60" width="180" height="100" rx="10" fill="#fff" stroke="#CBD5E1" stroke-width="2"/>
            <rect x="130" y="60" width="180" height="18" rx="10" fill="#2563EB"/>
            <circle cx="142" cy="69" r="2.5" fill="#fff" opacity="0.7"/><circle cx="152" cy="69" r="2.5" fill="#fff" opacity="0.7"/><circle cx="162" cy="69" r="2.5" fill="#fff" opacity="0.7"/>
            <rect x="148" y="90" width="150" height="20" rx="4" fill="#F1F5F9"/>
            <rect x="155" y="96" width="8" height="8" rx="2" fill="#10B981"/>
            <text x="170" y="103" font-size="9" font-family="ui-monospace,monospace" fill="#64748B">https://example.com</text>
            <!-- Big shield with lock -->
            <path d="M 195 120 L 225 115 L 255 120 L 255 145 Q 225 165 195 145 Z" fill="#2563EB"/>
            <rect x="216" y="128" width="18" height="14" rx="2" fill="#fff"/>
            <path d="M 220 128 L 220 124 Q 225 119 230 124 L 230 128" stroke="#fff" stroke-width="2" fill="none"/>
            <!-- Document beside -->
            <rect x="265" y="115" width="50" height="48" rx="4" fill="#F1F5F9" stroke="#CBD5E1" stroke-width="1"/>
            <rect x="270" y="122" width="38" height="3" rx="1" fill="#CBD5E1"/>
            <rect x="270" y="130" width="30" height="3" rx="1" fill="#CBD5E1"/>
            <rect x="270" y="138" width="35" height="3" rx="1" fill="#CBD5E1"/>
            <circle cx="305" cy="160" r="6" fill="#10B981"/>
            <path d="M 302 160 L 304 162 L 308 158" stroke="#fff" stroke-width="1.5" fill="none"/>

        <?php elseif ( $name === 'dns' ) : ?>
            <!-- Globe + record pills -->
            <circle cx="150" cy="120" r="34" fill="#fff" stroke="#2563EB" stroke-width="2.5"/>
            <ellipse cx="150" cy="120" rx="34" ry="14" fill="none" stroke="#2563EB" stroke-width="1.5"/>
            <line x1="116" y1="120" x2="184" y2="120" stroke="#2563EB" stroke-width="1.5"/>
            <path d="M 150 86 Q 130 120 150 154 Q 170 120 150 86" fill="none" stroke="#2563EB" stroke-width="1.5"/>
            <rect x="124" y="158" width="52" height="16" rx="3" fill="#F1F5F9" stroke="#CBD5E1"/>
            <text x="150" y="170" text-anchor="middle" font-size="9" font-family="ui-monospace,monospace" fill="#64748B">example.com</text>
            <!-- Dashed lines to records -->
            <line x1="190" y1="100" x2="220" y2="80" stroke="#CBD5E1" stroke-width="1.5" stroke-dasharray="3 3"/>
            <line x1="190" y1="115" x2="220" y2="110" stroke="#CBD5E1" stroke-width="1.5" stroke-dasharray="3 3"/>
            <line x1="190" y1="130" x2="220" y2="140" stroke="#CBD5E1" stroke-width="1.5" stroke-dasharray="3 3"/>
            <line x1="190" y1="145" x2="220" y2="170" stroke="#CBD5E1" stroke-width="1.5" stroke-dasharray="3 3"/>
            <!-- A record -->
            <rect x="220" y="70" width="120" height="22" rx="5" fill="#fff" stroke="#10B981" stroke-width="1.5"/>
            <rect x="225" y="75" width="14" height="12" rx="2" fill="#D1FAE5"/>
            <text x="232" y="84" text-anchor="middle" font-size="8" font-weight="800" fill="#10B981">A</text>
            <text x="247" y="84" font-size="9" font-family="ui-monospace,monospace" fill="#1E293B">93.184.216.34</text>
            <!-- MX -->
            <rect x="220" y="98" width="120" height="22" rx="5" fill="#fff" stroke="#F97316" stroke-width="1.5"/>
            <rect x="225" y="103" width="18" height="12" rx="2" fill="#FED7AA"/>
            <text x="234" y="112" text-anchor="middle" font-size="8" font-weight="800" fill="#F97316">MX</text>
            <text x="248" y="112" font-size="9" font-family="ui-monospace,monospace" fill="#1E293B">mail.example.com</text>
            <!-- TXT -->
            <rect x="220" y="126" width="120" height="22" rx="5" fill="#fff" stroke="#10B981" stroke-width="1.5"/>
            <rect x="225" y="131" width="20" height="12" rx="2" fill="#D1FAE5"/>
            <text x="235" y="140" text-anchor="middle" font-size="8" font-weight="800" fill="#10B981">TXT</text>
            <text x="250" y="140" font-size="9" font-family="ui-monospace,monospace" fill="#1E293B">v=spf1...</text>
            <!-- NS -->
            <rect x="220" y="154" width="120" height="22" rx="5" fill="#fff" stroke="#EC4899" stroke-width="1.5"/>
            <rect x="225" y="159" width="16" height="12" rx="2" fill="#FCE7F3"/>
            <text x="233" y="168" text-anchor="middle" font-size="8" font-weight="800" fill="#EC4899">NS</text>
            <text x="246" y="168" font-size="9" font-family="ui-monospace,monospace" fill="#1E293B">ns1.example.com</text>

        <?php elseif ( $name === 'redirect' ) : ?>
            <!-- Three browser windows with arrows -->
            <rect x="120" y="90" width="70" height="55" rx="6" fill="#fff" stroke="#CBD5E1" stroke-width="2"/>
            <rect x="120" y="90" width="70" height="10" rx="6" fill="#F1F5F9"/>
            <circle cx="127" cy="95" r="1.5" fill="#94A3B8"/><circle cx="132" cy="95" r="1.5" fill="#94A3B8"/><circle cx="137" cy="95" r="1.5" fill="#94A3B8"/>
            <rect x="128" y="108" width="54" height="4" rx="1" fill="#E2E8F0"/>
            <rect x="128" y="116" width="42" height="4" rx="1" fill="#E2E8F0"/>
            <rect x="128" y="124" width="48" height="4" rx="1" fill="#E2E8F0"/>
            <!-- Arrow 1 -->
            <path d="M 195 117 L 215 117" stroke="#2563EB" stroke-width="2.5" stroke-linecap="round"/>
            <polygon points="212,113 220,117 212,121" fill="#2563EB"/>
            <!-- Mid browser -->
            <rect x="225" y="90" width="70" height="55" rx="6" fill="#fff" stroke="#CBD5E1" stroke-width="2"/>
            <rect x="225" y="90" width="70" height="10" rx="6" fill="#F1F5F9"/>
            <circle cx="232" cy="95" r="1.5" fill="#94A3B8"/><circle cx="237" cy="95" r="1.5" fill="#94A3B8"/><circle cx="242" cy="95" r="1.5" fill="#94A3B8"/>
            <rect x="233" y="108" width="54" height="4" rx="1" fill="#E2E8F0"/>
            <rect x="233" y="116" width="42" height="4" rx="1" fill="#E2E8F0"/>
            <rect x="233" y="124" width="48" height="4" rx="1" fill="#E2E8F0"/>
            <!-- Arrow 2 -->
            <path d="M 300 117 L 320 117" stroke="#2563EB" stroke-width="2.5" stroke-linecap="round"/>
            <polygon points="317,113 325,117 317,121" fill="#2563EB"/>
            <!-- Final browser (green) -->
            <rect x="330" y="90" width="70" height="55" rx="6" fill="#fff" stroke="#10B981" stroke-width="2"/>
            <rect x="330" y="90" width="70" height="10" rx="6" fill="#D1FAE5"/>
            <circle cx="337" cy="95" r="1.5" fill="#10B981"/><circle cx="342" cy="95" r="1.5" fill="#10B981"/><circle cx="347" cy="95" r="1.5" fill="#10B981"/>
            <rect x="338" y="108" width="54" height="4" rx="1" fill="#A7F3D0"/>
            <rect x="338" y="116" width="42" height="4" rx="1" fill="#A7F3D0"/>
            <rect x="338" y="124" width="48" height="4" rx="1" fill="#A7F3D0"/>
            <!-- Green check badge -->
            <circle cx="395" cy="150" r="14" fill="#10B981"/>
            <path d="M 388 150 L 393 155 L 402 145" stroke="#fff" stroke-width="2.5" fill="none" stroke-linecap="round"/>
            <!-- Dashed underline between all -->
            <path d="M 155 175 Q 260 200 365 175" fill="none" stroke="#3B82F6" stroke-width="1.5" stroke-dasharray="4 3"/>

        <?php else : ?>
            <!-- Generic card -->
            <rect x="140" y="60" width="120" height="120" rx="10" fill="#fff" stroke="#CBD5E1" stroke-width="2"/>
            <rect x="170" y="90" width="60" height="60" rx="10" fill="<?php echo esc_attr( $bg ); ?>"/>
            <text x="200" y="135" text-anchor="middle" font-size="32" fill="<?php echo esc_attr( $fg ); ?>">★</text>
        <?php endif; ?>
    </svg>
    <?php
}
