<?php
/**
 * Extended tool registry and lightweight interfaces.
 *
 * Complex file operations are deliberately performed in the visitor's browser.
 * Network diagnostics use the hardened, rate-limited endpoints in api-extra.php.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Extra tools requested for the expanded toolkit.
 *
 * @return array<string,array<string,mixed>>
 */
function alltools_extra_definitions() {
    $rows = array(
        /* Documents and PDF. */
        array( 'pdf-to-word', 'PDF to Word Converter', 'Convert a PDF into an editable Word-compatible document.', 'pdf-tools', 'download', 'blue', 'Convert', 'Popular' ),
        array( 'word-to-pdf', 'Word to PDF Converter', 'Convert DOCX or text documents into a clean PDF.', 'pdf-tools', 'download', 'red', 'Convert', 'Popular' ),
        array( 'pdf-to-excel', 'PDF to Excel Converter', 'Extract PDF text and tables into a spreadsheet-ready CSV file.', 'pdf-tools', 'grid', 'green', 'Convert', '' ),
        array( 'excel-to-pdf', 'Excel to PDF Converter', 'Turn CSV or spreadsheet data into a printable PDF.', 'pdf-tools', 'download', 'purple', 'Convert', '' ),
        array( 'pdf-to-powerpoint', 'PDF to PowerPoint Converter', 'Turn PDF pages into a downloadable PowerPoint presentation.', 'pdf-tools', 'image', 'orange', 'Convert', '' ),
        array( 'powerpoint-to-pdf', 'PowerPoint to PDF Converter', 'Extract slide text from PPTX and create a readable PDF.', 'pdf-tools', 'download', 'pink', 'Convert', '' ),
        array( 'edit-pdf', 'Edit PDF Online', 'Add text to a PDF and download the edited document.', 'pdf-tools', 'word', 'blue', 'Edit', 'Popular' ),
        array( 'sign-pdf', 'Sign PDF Online', 'Add a typed or drawn signature to a PDF in your browser.', 'pdf-tools', 'check', 'green', 'Sign', 'Popular' ),
        array( 'ocr-pdf', 'OCR PDF Online', 'Extract selectable text from scanned PDF pages.', 'pdf-tools', 'search', 'purple', 'Extract Text', 'OCR' ),
        array( 'password-protect-pdf', 'Protect PDF with Password', 'Encrypt a PDF with a password before sharing it.', 'pdf-tools', 'shield', 'green', 'Protect', '' ),
        array( 'unlock-pdf', 'Unlock PDF', 'Open a password-protected PDF and save an unlocked copy.', 'pdf-tools', 'shield', 'orange', 'Unlock', '' ),
        array( 'add-page-numbers-pdf', 'Add Page Numbers to PDF', 'Add customizable page numbers to every PDF page.', 'pdf-tools', 'word', 'blue', 'Add Numbers', '' ),
        array( 'organize-pdf', 'Organize PDF Pages', 'Reorder, keep, duplicate or remove PDF pages.', 'pdf-tools', 'grid', 'purple', 'Organize', '' ),
        array( 'redact-pdf', 'Redact PDF Online', 'Cover sensitive information on selected PDF pages.', 'pdf-tools', 'shield', 'red', 'Redact', '' ),
        array( 'crop-pdf', 'Crop PDF Online', 'Crop PDF page margins and download the adjusted file.', 'pdf-tools', 'resize', 'orange', 'Crop', '' ),

        /* Image. */
        array( 'background-remover', 'Background Remover', 'Remove a plain image background and export transparent PNG.', 'image-tools', 'image', 'purple', 'Remove Background', 'High Demand' ),
        array( 'image-upscaler', 'Image Upscaler & Enhancer', 'Enlarge an image up to 4× with smooth high-quality scaling.', 'image-tools', 'resize', 'blue', 'Upscale', 'Popular' ),
        array( 'image-to-text', 'Image to Text OCR', 'Extract editable text from photos and scanned images.', 'image-tools', 'word', 'green', 'Extract Text', 'OCR' ),
        array( 'heic-to-jpg', 'HEIC to JPG Converter', 'Convert iPhone HEIC photos into widely supported JPG images.', 'image-tools', 'image', 'orange', 'Convert', 'Popular' ),
        array( 'svg-to-png', 'SVG to PNG Converter', 'Render an SVG file as a high-quality PNG image.', 'image-tools', 'image', 'blue', 'Convert', '' ),
        array( 'avif-to-jpg', 'AVIF to JPG Converter', 'Convert AVIF images into compatible JPG files.', 'image-tools', 'image', 'pink', 'Convert', '' ),
        array( 'gif-maker', 'GIF Maker', 'Create an animated GIF from multiple images.', 'image-tools', 'image', 'purple', 'Create GIF', '' ),
        array( 'gif-compressor', 'GIF Compressor', 'Reduce GIF dimensions and file size for easier sharing.', 'image-tools', 'resize', 'green', 'Compress', '' ),
        array( 'photo-blur-tool', 'Blur Image Online', 'Apply adjustable blur to an image and download the result.', 'image-tools', 'image', 'blue', 'Blur', '' ),
        array( 'pixelate-image', 'Pixelate Image', 'Create a pixelated or mosaic effect with adjustable block size.', 'image-tools', 'grid', 'orange', 'Pixelate', '' ),
        array( 'meme-generator', 'Meme Generator', 'Add top and bottom captions to an image.', 'image-tools', 'word', 'pink', 'Create Meme', '' ),
        array( 'photo-collage-maker', 'Photo Collage Maker', 'Combine multiple images into a clean downloadable collage.', 'image-tools', 'grid', 'purple', 'Create Collage', '' ),
        array( 'transparent-background-maker', 'Transparent Background Maker', 'Make a selected background color transparent.', 'image-tools', 'image', 'green', 'Make Transparent', '' ),
        array( 'product-photo-background-changer', 'Product Background Changer', 'Replace a plain product-photo background with a new color.', 'image-tools', 'image', 'blue', 'Change Background', '' ),

        /* Video and audio. */
        array( 'video-compressor', 'Video Compressor', 'Compress MP4, MOV and WebM videos in your browser.', 'video-tools', 'download', 'purple', 'Compress', 'Popular' ),
        array( 'video-trimmer', 'Video Trimmer', 'Cut a selected section from a video without installing software.', 'video-tools', 'resize', 'blue', 'Trim', '' ),
        array( 'merge-videos', 'Merge Videos', 'Combine multiple video clips into one file.', 'video-tools', 'grid', 'green', 'Merge', '' ),
        array( 'resize-video', 'Resize Video', 'Change video resolution for web and social platforms.', 'video-tools', 'resize', 'orange', 'Resize', '' ),
        array( 'crop-video', 'Crop Video', 'Crop a video to a custom width, height and position.', 'video-tools', 'resize', 'pink', 'Crop', '' ),
        array( 'video-to-gif', 'Video to GIF Converter', 'Convert a short video clip into an animated GIF.', 'video-tools', 'image', 'purple', 'Convert', 'Popular' ),
        array( 'gif-to-mp4', 'GIF to MP4 Converter', 'Convert an animated GIF into an efficient MP4 video.', 'video-tools', 'image', 'blue', 'Convert', '' ),
        array( 'extract-audio', 'Extract Audio from Video', 'Save the audio track from a video as MP3.', 'audio-tools', 'pulse', 'green', 'Extract', '' ),
        array( 'mute-video', 'Mute Video', 'Remove audio from a video and download a silent copy.', 'video-tools', 'pulse', 'orange', 'Mute', '' ),
        array( 'change-video-speed', 'Change Video Speed', 'Speed up or slow down a video.', 'video-tools', 'gauge', 'blue', 'Change Speed', '' ),
        array( 'mp4-to-webm', 'MP4 to WebM Converter', 'Convert MP4 video into web-friendly WebM.', 'video-tools', 'code', 'green', 'Convert', '' ),
        array( 'mov-to-mp4', 'MOV to MP4 Converter', 'Convert MOV video into compatible MP4.', 'video-tools', 'code', 'purple', 'Convert', 'Popular' ),
        array( 'audio-converter', 'Audio Converter', 'Convert common audio files to MP3, WAV or OGG.', 'audio-tools', 'pulse', 'blue', 'Convert', '' ),
        array( 'mp3-cutter', 'MP3 Cutter', 'Trim an audio file to the exact section you need.', 'audio-tools', 'resize', 'orange', 'Cut', '' ),
        array( 'audio-compressor', 'Audio Compressor', 'Reduce audio bitrate and file size.', 'audio-tools', 'download', 'green', 'Compress', '' ),

        /* Website, DNS, email and diagnostics. */
        array( 'website-health-checker', 'All-in-One Website Health Checker', 'Audit uptime, SSL, redirects, headers, SEO and page health.', 'website-tools', 'shield', 'green', 'Run Audit', 'Recommended' ),
        array( 'dns-propagation-checker', 'DNS Propagation Checker', 'Compare DNS records across trusted public resolvers.', 'website-tools', 'globe', 'blue', 'Check DNS', 'Popular' ),
        array( 'ip-blacklist-checker', 'IP Blacklist Checker', 'Check whether an IP appears on common DNS blacklists.', 'website-tools', 'shield', 'red', 'Check IP', '' ),
        array( 'spf-record-checker', 'SPF Record Checker', 'Find and validate a domain SPF email policy.', 'website-tools', 'check', 'green', 'Check SPF', '' ),
        array( 'dkim-record-checker', 'DKIM Record Checker', 'Verify a DKIM public key using its selector.', 'website-tools', 'check', 'purple', 'Check DKIM', '' ),
        array( 'dmarc-record-checker', 'DMARC Record Checker', 'Inspect a domain DMARC policy and reporting settings.', 'website-tools', 'shield', 'blue', 'Check DMARC', '' ),
        array( 'dmarc-record-generator', 'DMARC Record Generator', 'Build a valid DMARC DNS record with guided options.', 'website-tools', 'code', 'orange', 'Generate', '' ),
        array( 'email-header-analyzer', 'Email Header Analyzer', 'Parse email headers and review delivery and authentication details.', 'website-tools', 'search', 'purple', 'Analyze', '' ),
        array( 'port-checker', 'Port Checker', 'Test a permitted public service port on a remote host.', 'website-tools', 'pulse', 'green', 'Check Port', '' ),
        array( 'ping-test', 'Website Ping Test', 'Measure DNS and secure HTTP response latency.', 'website-tools', 'pulse', 'blue', 'Run Test', '' ),
        array( 'traceroute-tool', 'Safe Route Diagnostic', 'Resolve a public host and inspect network ownership without shell access.', 'website-tools', 'globe', 'orange', 'Diagnose', 'Safe Mode' ),
        array( 'website-screenshot-generator', 'Website Screenshot Generator', 'Create desktop or mobile website screenshot previews.', 'website-tools', 'image', 'purple', 'Create Screenshot', '' ),
        array( 'cms-detector', 'CMS Detector', 'Detect WordPress, Shopify, Wix and other common platforms.', 'website-tools', 'search', 'blue', 'Detect', '' ),
        array( 'hosting-provider-checker', 'Hosting Provider Checker', 'Find the public IP, ASN and network provider for a domain.', 'website-tools', 'globe', 'green', 'Check Hosting', '' ),
        array( 'subdomain-finder', 'Subdomain Finder', 'Discover public subdomains from certificate transparency records.', 'website-tools', 'search', 'purple', 'Find Subdomains', '' ),
        array( 'http-headers-viewer', 'HTTP Headers Viewer', 'View response headers returned by a public website.', 'website-tools', 'code', 'orange', 'View Headers', '' ),
        array( 'core-web-vitals-checker', 'Core Web Vitals Checker', 'Check Lighthouse performance and Core Web Vitals metrics.', 'website-tools', 'gauge', 'green', 'Check Vitals', '' ),
        array( 'mobile-friendly-test', 'Mobile-Friendly Test', 'Review viewport, responsive signals and mobile performance.', 'website-tools', 'image', 'blue', 'Run Test', '' ),
        array( 'website-technology-detector', 'Website Technology Detector', 'Detect frameworks, analytics, CDN and common site technologies.', 'website-tools', 'code', 'purple', 'Detect', '' ),

        /* Marketing, business and practical utilities. */
        array( 'email-signature-generator', 'Email Signature Generator', 'Create a responsive professional HTML email signature.', 'marketing-tools', 'word', 'blue', 'Generate', 'Popular' ),
        array( 'whatsapp-link-generator', 'WhatsApp Link Generator', 'Create a click-to-chat WhatsApp link with a prepared message.', 'marketing-tools', 'link', 'green', 'Create Link', 'Popular' ),
        array( 'utm-builder', 'UTM Campaign URL Builder', 'Build consistent trackable campaign URLs for analytics.', 'marketing-tools', 'link', 'purple', 'Build URL', 'Popular' ),
        array( 'barcode-generator', 'Barcode Generator', 'Generate a downloadable Code 128 barcode.', 'business-tools', 'grid', 'blue', 'Generate', '' ),
        array( 'link-shortener', 'URL Shortener', 'Create a compact shareable link for a public web page.', 'marketing-tools', 'link', 'blue', 'Shorten URL', '' ),
        array( 'digital-business-card-generator', 'Digital Business Card Generator', 'Create a downloadable contact card and vCard file.', 'business-tools', 'user', 'purple', 'Create Card', '' ),
        array( 'purchase-order-generator', 'Purchase Order Generator', 'Create a professional purchase order and download it as PDF.', 'business-tools', 'download', 'green', 'Create', '' ),
        array( 'proforma-invoice-generator', 'Proforma Invoice Generator', 'Create a professional proforma invoice PDF.', 'business-tools', 'download', 'blue', 'Create', '' ),
        array( 'credit-note-generator', 'Credit Note Generator', 'Create a clean credit note document for a customer.', 'business-tools', 'download', 'orange', 'Create', '' ),
        array( 'freelance-proposal-generator', 'Freelance Proposal Generator', 'Build a simple client proposal and download a PDF.', 'business-tools', 'word', 'purple', 'Create Proposal', '' ),
        array( 'timesheet-generator', 'Timesheet Generator', 'Calculate work hours and export a timesheet CSV.', 'business-tools', 'calendar', 'green', 'Generate', '' ),
        array( 'salary-to-hourly-calculator', 'Salary to Hourly Calculator', 'Convert annual salary into monthly, weekly and hourly pay.', 'calculators', 'calculator', 'blue', 'Calculate', '' ),
        array( 'currency-converter', 'Currency Converter', 'Convert currencies using current reference exchange rates.', 'calculators', 'bank', 'green', 'Convert', '' ),
        array( 'unit-converter', 'Unit Converter', 'Convert length, weight, temperature, area and data units.', 'calculators', 'resize', 'purple', 'Convert', '' ),
        array( 'time-zone-converter', 'Time Zone Converter', 'Convert a date and time between world time zones.', 'calculators', 'globe', 'orange', 'Convert', '' ),
        array( 'mortgage-calculator', 'Mortgage Calculator', 'Estimate monthly mortgage payments and total interest.', 'calculators', 'bank', 'blue', 'Calculate', '' ),
        array( 'bmi-calculator', 'BMI Calculator', 'Calculate adult body mass index using metric or imperial units.', 'calculators', 'calculator', 'green', 'Calculate', '' ),

        /* Developer and content utilities. */
        array( 'html-email-preview', 'HTML Email Preview', 'Preview responsive HTML email code safely in a sandbox.', 'developer-tools', 'code', 'blue', 'Preview', '' ),
        array( 'text-difference-checker', 'Text Difference Checker', 'Compare two texts line by line and highlight changes.', 'text-tools', 'word', 'purple', 'Compare', '' ),
        array( 'sql-formatter', 'SQL Formatter', 'Format and indent SQL queries for easier reading.', 'developer-tools', 'code', 'green', 'Format', '' ),
        array( 'csv-to-json', 'CSV to JSON Converter', 'Convert CSV rows into formatted JSON objects.', 'developer-tools', 'code', 'orange', 'Convert', '' ),
        array( 'json-to-csv', 'JSON to CSV Converter', 'Convert an array of JSON objects into CSV.', 'developer-tools', 'code', 'pink', 'Convert', '' ),
    );

    $out = array();
    foreach ( $rows as $row ) {
        $out[ $row[0] ] = array(
            'title'    => $row[1],
            'short'    => $row[1],
            'desc'     => $row[2],
            'long'     => $row[2] . ' Fast, easy to use and designed with privacy in mind.',
            'icon'     => $row[4],
            'color'    => $row[5],
            'category' => $row[3],
            'badge'    => $row[7],
            'featured' => in_array( $row[0], array( 'background-remover', 'pdf-to-word', 'image-to-text', 'video-compressor', 'website-health-checker', 'whatsapp-link-generator' ), true ),
            'cta'      => $row[6],
            'illo'     => in_array( $row[3], array( 'image-tools', 'video-tools' ), true ) ? 'image-convert' : 'code',
        );
    }
    return $out;
}

add_filter( 'alltools_registered_tools', 'alltools_register_extra_tools' );
function alltools_register_extra_tools( $tools ) {
    return array_merge( $tools, alltools_extra_definitions() );
}

/** Upload drop-zone used by document and media tools. */
function alltools_extra_upload_box( $slug, $accept, $multiple = false ) {
    $tool = alltools_get_tool( $slug );
    ?>
    <div class="at-upload-zone ufx-extra-drop" data-input="<?php echo esc_attr( $slug ); ?>-file">
        <div class="at-upload-icon"><?php echo alltools_icon_svg( 'upload', 28 ); ?></div>
        <h3><?php echo esc_html( $tool['title'] ); ?></h3>
        <p><?php esc_html_e( 'Choose a file or drag and drop it here.', 'alltools' ); ?></p>
        <label class="at-btn at-btn-outline" for="<?php echo esc_attr( $slug ); ?>-file"><?php esc_html_e( 'Choose File', 'alltools' ); ?></label>
        <input hidden id="<?php echo esc_attr( $slug ); ?>-file" type="file" accept="<?php echo esc_attr( $accept ); ?>" <?php echo $multiple ? 'multiple' : ''; ?>>
        <div class="at-help ufx-file-name" aria-live="polite"></div>
    </div>
    <?php
}

function alltools_extra_action_shell( $slug, $accept, $button, $multiple = false, $controls = '' ) {
    ?>
    <div class="at-tool-grid at-tool-grid-2 ufx-extra-tool" data-ufx-extra="<?php echo esc_attr( $slug ); ?>">
        <div class="at-card">
            <?php alltools_extra_upload_box( $slug, $accept, $multiple ); ?>
            <?php echo wp_kses( $controls, array( 'div' => array( 'class' => true ), 'label' => array(), 'input' => array( 'id' => true, 'class' => true, 'type' => true, 'min' => true, 'max' => true, 'step' => true, 'value' => true, 'placeholder' => true ), 'select' => array( 'id' => true, 'class' => true ), 'option' => array( 'value' => true, 'selected' => true ), 'textarea' => array( 'id' => true, 'class' => true, 'rows' => true, 'placeholder' => true ), 'p' => array( 'class' => true ), 'small' => array() ) ); ?>
            <button type="button" class="at-btn at-btn-primary ufx-extra-run"><?php echo esc_html( $button ); ?></button>
        </div>
        <div class="at-card">
            <h3><?php esc_html_e( 'Result', 'alltools' ); ?></h3>
            <div class="ufx-extra-status at-help" role="status"><?php esc_html_e( 'Your result will appear here.', 'alltools' ); ?></div>
            <div class="ufx-extra-output"></div>
            <canvas class="ufx-extra-canvas" hidden></canvas>
        </div>
    </div>
    <?php
}

/**
 * Render every extra tool through one allow-listed dispatcher.
 */
add_filter( 'alltools_render_tool', 'alltools_render_extra_tool', 10, 2 );
function alltools_render_extra_tool( $rendered, $slug ) {
    $defs = alltools_extra_definitions();
    if ( ! isset( $defs[ $slug ] ) ) {
        return $rendered;
    }

    $pdf_simple = array( 'pdf-to-word', 'pdf-to-excel', 'pdf-to-powerpoint', 'ocr-pdf', 'password-protect-pdf', 'unlock-pdf', 'add-page-numbers-pdf', 'organize-pdf', 'redact-pdf', 'crop-pdf', 'edit-pdf', 'sign-pdf' );
    if ( in_array( $slug, $pdf_simple, true ) ) {
        $controls = '';
        if ( 'password-protect-pdf' === $slug || 'unlock-pdf' === $slug ) {
            $controls = '<div class="at-field"><label>Password</label><input id="' . esc_attr( $slug ) . '-password" class="at-input" type="password" autocomplete="new-password" minlength="4"></div>';
        } elseif ( 'add-page-numbers-pdf' === $slug ) {
            $controls = '<div class="at-field"><label>Position</label><select id="add-page-numbers-pdf-position" class="at-input"><option value="bottom">Bottom center</option><option value="top">Top center</option></select></div>';
        } elseif ( 'organize-pdf' === $slug ) {
            $controls = '<div class="at-field"><label>Page order</label><input id="organize-pdf-pages" class="at-input" placeholder="Example: 3,1,2,5-7"></div>';
        } elseif ( 'redact-pdf' === $slug ) {
            $controls = '<div class="at-tool-grid at-tool-grid-2"><div class="at-field"><label>Page</label><input id="redact-pdf-page" class="at-input" type="number" min="1" value="1"></div><div class="at-field"><label>Rectangle x,y,w,h</label><input id="redact-pdf-rect" class="at-input" value="50,50,200,40"></div></div>';
        } elseif ( 'crop-pdf' === $slug ) {
            $controls = '<div class="at-field"><label>Margin to remove (points)</label><input id="crop-pdf-margin" class="at-input" type="number" min="0" max="100" value="18"></div>';
        } elseif ( 'edit-pdf' === $slug ) {
            $controls = '<div class="at-field"><label>Text to add</label><input id="edit-pdf-text" class="at-input" maxlength="200" placeholder="Text"></div><div class="at-tool-grid at-tool-grid-2"><div class="at-field"><label>Page</label><input id="edit-pdf-page" class="at-input" type="number" min="1" value="1"></div><div class="at-field"><label>Position x,y</label><input id="edit-pdf-position" class="at-input" value="50,50"></div></div>';
        } elseif ( 'sign-pdf' === $slug ) {
            $controls = '<div class="at-field"><label>Signature text</label><input id="sign-pdf-text" class="at-input" maxlength="80" placeholder="Your name"></div><div class="at-field"><label>Page</label><input id="sign-pdf-page" class="at-input" type="number" min="1" value="1"></div>';
        }
        alltools_extra_action_shell( $slug, '.pdf,application/pdf', $defs[ $slug ]['cta'], false, $controls );
        return true;
    }

    if ( in_array( $slug, array( 'word-to-pdf', 'powerpoint-to-pdf' ), true ) ) {
        alltools_extra_action_shell( $slug, 'word-to-pdf' === $slug ? '.docx,.txt,.rtf' : '.pptx', $defs[ $slug ]['cta'] );
        return true;
    }
    if ( 'excel-to-pdf' === $slug ) {
        alltools_extra_action_shell( $slug, '.csv,.xlsx,.xls,text/csv', $defs[ $slug ]['cta'] );
        return true;
    }

    $image_controls = array(
        'background-remover' => '<div class="at-tool-grid at-tool-grid-2"><div class="at-field"><label>Background color</label><input id="background-remover-color" class="at-input" type="color" value="#ffffff"></div><div class="at-field"><label>Tolerance</label><input id="background-remover-tolerance" class="at-input" type="range" min="0" max="180" value="45"></div></div><p class="at-help">Best for plain or studio backgrounds. Files stay in your browser.</p>',
        'transparent-background-maker' => '<div class="at-tool-grid at-tool-grid-2"><div class="at-field"><label>Color to remove</label><input id="transparent-background-maker-color" class="at-input" type="color" value="#ffffff"></div><div class="at-field"><label>Tolerance</label><input id="transparent-background-maker-tolerance" class="at-input" type="range" min="0" max="180" value="35"></div></div>',
        'product-photo-background-changer' => '<div class="at-tool-grid at-tool-grid-2"><div class="at-field"><label>Old background</label><input id="product-photo-background-changer-old" class="at-input" type="color" value="#ffffff"></div><div class="at-field"><label>New background</label><input id="product-photo-background-changer-new" class="at-input" type="color" value="#f1f5f9"></div></div>',
        'image-upscaler' => '<div class="at-field"><label>Scale</label><select id="image-upscaler-scale" class="at-input"><option value="2">2×</option><option value="3">3×</option><option value="4">4×</option></select></div>',
        'photo-blur-tool' => '<div class="at-field"><label>Blur strength</label><input id="photo-blur-tool-value" class="at-input" type="range" min="0" max="30" value="8"></div>',
        'pixelate-image' => '<div class="at-field"><label>Pixel size</label><input id="pixelate-image-value" class="at-input" type="range" min="2" max="60" value="12"></div>',
        'meme-generator' => '<div class="at-field"><label>Top text</label><input id="meme-generator-top" class="at-input" maxlength="100"></div><div class="at-field"><label>Bottom text</label><input id="meme-generator-bottom" class="at-input" maxlength="100"></div>',
    );
    if ( isset( $image_controls[ $slug ] ) ) {
        alltools_extra_action_shell( $slug, 'image/*', $defs[ $slug ]['cta'], false, $image_controls[ $slug ] );
        return true;
    }
    if ( in_array( $slug, array( 'image-to-text', 'heic-to-jpg', 'svg-to-png', 'avif-to-jpg', 'gif-compressor' ), true ) ) {
        $accept = 'image/*';
        if ( 'heic-to-jpg' === $slug ) $accept = '.heic,.heif,image/heic,image/heif';
        if ( 'svg-to-png' === $slug ) $accept = '.svg,image/svg+xml';
        if ( 'avif-to-jpg' === $slug ) $accept = '.avif,image/avif';
        alltools_extra_action_shell( $slug, $accept, $defs[ $slug ]['cta'] );
        return true;
    }
    if ( in_array( $slug, array( 'gif-maker', 'photo-collage-maker' ), true ) ) {
        alltools_extra_action_shell( $slug, 'image/*', $defs[ $slug ]['cta'], true );
        return true;
    }

    $media = array( 'video-compressor', 'video-trimmer', 'merge-videos', 'resize-video', 'crop-video', 'video-to-gif', 'gif-to-mp4', 'extract-audio', 'mute-video', 'change-video-speed', 'mp4-to-webm', 'mov-to-mp4', 'audio-converter', 'mp3-cutter', 'audio-compressor' );
    if ( in_array( $slug, $media, true ) ) {
        $accept = in_array( $slug, array( 'gif-to-mp4' ), true ) ? 'image/gif' : ( in_array( $slug, array( 'audio-converter', 'mp3-cutter', 'audio-compressor' ), true ) ? 'audio/*' : 'video/*' );
        $multiple = 'merge-videos' === $slug;
        $controls = '';
        if ( in_array( $slug, array( 'video-trimmer', 'video-to-gif', 'mp3-cutter' ), true ) ) $controls = '<div class="at-tool-grid at-tool-grid-2"><div class="at-field"><label>Start (seconds)</label><input id="' . esc_attr( $slug ) . '-start" class="at-input" type="number" min="0" value="0"></div><div class="at-field"><label>Duration (seconds)</label><input id="' . esc_attr( $slug ) . '-value" class="at-input" type="number" min="1" value="10"></div></div>';
        elseif ( 'video-compressor' === $slug ) $controls = '<div class="at-field"><label>Compression level (18 high quality – 38 smaller file)</label><input id="video-compressor-value" class="at-input" type="number" min="18" max="38" value="29"></div>';
        elseif ( 'resize-video' === $slug ) $controls = '<div class="at-field"><label>Output width (pixels)</label><input id="resize-video-width" class="at-input" type="number" min="160" max="3840" step="2" value="1280"></div>';
        elseif ( 'crop-video' === $slug ) $controls = '<div class="at-tool-grid at-tool-grid-2"><div class="at-field"><label>Crop width</label><input id="crop-video-width" class="at-input" type="number" min="2" step="2" value="720"></div><div class="at-field"><label>Crop height</label><input id="crop-video-height" class="at-input" type="number" min="2" step="2" value="720"></div><div class="at-field"><label>X position</label><input id="crop-video-x" class="at-input" type="number" min="0" value="0"></div><div class="at-field"><label>Y position</label><input id="crop-video-y" class="at-input" type="number" min="0" value="0"></div></div>';
        elseif ( 'change-video-speed' === $slug ) $controls = '<div class="at-field"><label>Playback speed</label><input id="change-video-speed-value" class="at-input" type="number" min="0.25" max="4" step="0.25" value="1.5"></div>';
        elseif ( 'audio-converter' === $slug ) $controls = '<div class="at-field"><label>Output format</label><select id="audio-converter-format" class="at-input"><option value="mp3">MP3</option><option value="wav">WAV</option><option value="ogg">OGG</option></select></div>';
        elseif ( 'audio-compressor' === $slug ) $controls = '<div class="at-field"><label>Output bitrate</label><select id="audio-compressor-bitrate" class="at-input"><option value="64k">64 kbps</option><option value="96k" selected>96 kbps</option><option value="128k">128 kbps</option></select></div>';
        if ( 'merge-videos' === $slug ) $controls .= '<p class="at-help">For reliable stream-copy merging, choose clips with the same format, resolution and codecs.</p>';
        $controls .= '<p class="at-help">Processing runs locally. Large media files may take time depending on your device.</p>';
        alltools_extra_action_shell( $slug, $accept, $defs[ $slug ]['cta'], $multiple, $controls );
        return true;
    }

    $remote = array( 'website-health-checker', 'dns-propagation-checker', 'ip-blacklist-checker', 'spf-record-checker', 'dmarc-record-checker', 'port-checker', 'ping-test', 'traceroute-tool', 'website-screenshot-generator', 'cms-detector', 'hosting-provider-checker', 'subdomain-finder', 'http-headers-viewer', 'core-web-vitals-checker', 'mobile-friendly-test', 'website-technology-detector' );
    if ( in_array( $slug, $remote, true ) || 'dkim-record-checker' === $slug ) {
        ?>
        <div class="at-tool-grid at-tool-grid-2 ufx-extra-tool" data-ufx-extra="<?php echo esc_attr( $slug ); ?>">
            <div class="at-card">
                <h3><?php echo esc_html( $defs[ $slug ]['title'] ); ?></h3>
                <div class="at-field"><label><?php echo 'ip-blacklist-checker' === $slug ? 'Public IP address' : 'Public domain or URL'; ?></label><input id="<?php echo esc_attr( $slug ); ?>-value" class="at-input" autocomplete="off" placeholder="<?php echo 'ip-blacklist-checker' === $slug ? '8.8.8.8' : 'example.com'; ?>"></div>
                <?php if ( 'dkim-record-checker' === $slug ) : ?><div class="at-field"><label>DKIM selector</label><input id="dkim-record-checker-selector" class="at-input" value="default"></div><?php endif; ?>
                <?php if ( 'port-checker' === $slug ) : ?><div class="at-field"><label>Port</label><select id="port-checker-port" class="at-input"><option>80</option><option>443</option><option>21</option><option>22</option><option>25</option><option>53</option><option>110</option><option>143</option><option>465</option><option>587</option><option>993</option><option>995</option></select></div><?php endif; ?>
                <?php if ( 'dns-propagation-checker' === $slug ) : ?><div class="at-field"><label>Record type</label><select id="dns-propagation-checker-type" class="at-input"><option>A</option><option>AAAA</option><option>CNAME</option><option>MX</option><option>TXT</option><option>NS</option></select></div><?php endif; ?>
                <?php if ( in_array( $slug, array( 'website-screenshot-generator', 'core-web-vitals-checker', 'mobile-friendly-test' ), true ) ) : ?><div class="at-field"><label>Device</label><select id="<?php echo esc_attr( $slug ); ?>-device" class="at-input"><option value="mobile">Mobile</option><option value="desktop">Desktop</option></select></div><?php endif; ?>
                <button type="button" class="at-btn at-btn-primary ufx-extra-run"><?php echo esc_html( $defs[ $slug ]['cta'] ); ?></button>
            </div>
            <div class="at-card"><h3>Results</h3><div class="ufx-extra-status at-help" role="status">Enter a public target to begin.</div><div class="ufx-extra-output"></div></div>
        </div>
        <?php
        return true;
    }

    if ( 'dmarc-record-generator' === $slug ) {
        alltools_extra_text_builder( $slug, array( 'Domain', 'Report email' ), array( 'Policy' => array( 'none', 'quarantine', 'reject' ) ) );
        return true;
    }
    if ( 'email-header-analyzer' === $slug ) {
        alltools_extra_textarea_tool( $slug, 'Paste the complete raw email headers here...' );
        return true;
    }

    if ( in_array( $slug, array( 'email-signature-generator', 'whatsapp-link-generator', 'utm-builder', 'barcode-generator', 'link-shortener', 'digital-business-card-generator', 'purchase-order-generator', 'proforma-invoice-generator', 'credit-note-generator', 'freelance-proposal-generator', 'timesheet-generator', 'salary-to-hourly-calculator', 'currency-converter', 'unit-converter', 'time-zone-converter', 'mortgage-calculator', 'bmi-calculator' ), true ) ) {
        alltools_extra_form_tool( $slug, $defs[ $slug ] );
        return true;
    }

    if ( in_array( $slug, array( 'html-email-preview', 'text-difference-checker', 'sql-formatter', 'csv-to-json', 'json-to-csv' ), true ) ) {
        alltools_extra_textarea_tool( $slug, 'Paste your content here...' );
        return true;
    }

    return $rendered;
}

function alltools_extra_textarea_tool( $slug, $placeholder ) {
    $two = 'text-difference-checker' === $slug;
    ?>
    <div class="at-tool-grid <?php echo $two ? 'at-tool-grid-2' : ''; ?> ufx-extra-tool" data-ufx-extra="<?php echo esc_attr( $slug ); ?>">
        <div class="at-card"><h3>Input</h3><textarea id="<?php echo esc_attr( $slug ); ?>-input" class="at-textarea" rows="14" placeholder="<?php echo esc_attr( $placeholder ); ?>"></textarea><?php if ( $two ) : ?><textarea id="<?php echo esc_attr( $slug ); ?>-input-2" class="at-textarea" rows="14" placeholder="Paste the second text here..."></textarea><?php endif; ?><button type="button" class="at-btn at-btn-primary ufx-extra-run">Process</button></div>
        <div class="at-card"><h3>Output</h3><div class="ufx-extra-status at-help" role="status"></div><textarea class="at-textarea ufx-extra-output-text" rows="16" readonly></textarea><div class="ufx-extra-output"></div></div>
    </div>
    <?php
}

function alltools_extra_text_builder( $slug, $fields, $selects = array() ) {
    ?>
    <div class="at-tool-grid at-tool-grid-2 ufx-extra-tool" data-ufx-extra="<?php echo esc_attr( $slug ); ?>"><div class="at-card"><h3>Record settings</h3>
        <?php foreach ( $fields as $field ) : $id = sanitize_title( $field ); ?><div class="at-field"><label><?php echo esc_html( $field ); ?></label><input id="<?php echo esc_attr( $slug . '-' . $id ); ?>" class="at-input"></div><?php endforeach; ?>
        <?php foreach ( $selects as $label => $options ) : $id = sanitize_title( $label ); ?><div class="at-field"><label><?php echo esc_html( $label ); ?></label><select id="<?php echo esc_attr( $slug . '-' . $id ); ?>" class="at-input"><?php foreach ( $options as $option ) : ?><option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option><?php endforeach; ?></select></div><?php endforeach; ?>
        <button type="button" class="at-btn at-btn-primary ufx-extra-run">Generate</button></div><div class="at-card"><h3>DNS record</h3><textarea class="at-textarea ufx-extra-output-text" rows="12" readonly></textarea><div class="ufx-extra-output"></div></div></div>
    <?php
}

function alltools_extra_form_tool( $slug, $tool ) {
    $fields = array(
        'email-signature-generator' => array( 'Full name', 'Job title', 'Company', 'Email', 'Phone', 'Website', 'Logo URL' ),
        'whatsapp-link-generator' => array( 'Phone with country code', 'Message' ),
        'utm-builder' => array( 'Website URL', 'Campaign source', 'Campaign medium', 'Campaign name', 'Campaign term', 'Campaign content' ),
        'barcode-generator' => array( 'Barcode value' ),
        'link-shortener' => array( 'Public URL' ),
        'digital-business-card-generator' => array( 'Full name', 'Job title', 'Company', 'Phone', 'Email', 'Website' ),
        'purchase-order-generator' => array( 'PO number', 'Business name', 'Supplier', 'Items: description | qty | price', 'Notes' ),
        'proforma-invoice-generator' => array( 'Invoice number', 'Business name', 'Client', 'Items: description | qty | price', 'Notes' ),
        'credit-note-generator' => array( 'Credit note number', 'Business name', 'Client', 'Items: description | qty | price', 'Reason' ),
        'freelance-proposal-generator' => array( 'Your name or business', 'Client', 'Project title', 'Scope', 'Price', 'Timeline' ),
        'timesheet-generator' => array( 'Employee or freelancer', 'Entries: date | start | end | break minutes' ),
        'salary-to-hourly-calculator' => array( 'Annual salary', 'Hours per week', 'Weeks per year' ),
        'currency-converter' => array( 'Amount', 'From currency', 'To currency' ),
        'unit-converter' => array( 'Value', 'From unit', 'To unit' ),
        'time-zone-converter' => array( 'Date and time', 'From time zone', 'To time zone' ),
        'mortgage-calculator' => array( 'Loan amount', 'Annual interest rate', 'Term in years', 'Down payment' ),
        'bmi-calculator' => array( 'Weight (kg)', 'Height (cm)' ),
    );
    ?>
    <div class="at-tool-grid at-tool-grid-2 ufx-extra-tool" data-ufx-extra="<?php echo esc_attr( $slug ); ?>"><div class="at-card"><h3><?php echo esc_html( $tool['title'] ); ?></h3>
    <?php foreach ( $fields[ $slug ] as $index => $label ) : $is_long = false !== strpos( $label, 'Items:' ) || in_array( $label, array( 'Message', 'Notes', 'Reason', 'Scope', 'Entries: date | start | end | break minutes' ), true ); ?>
        <div class="at-field"><label><?php echo esc_html( $label ); ?></label><?php if ( $is_long ) : ?><textarea id="<?php echo esc_attr( $slug . '-field-' . $index ); ?>" class="at-textarea" rows="4"></textarea><?php else : ?><input id="<?php echo esc_attr( $slug . '-field-' . $index ); ?>" class="at-input" <?php echo 'time-zone-converter' === $slug && 0 === $index ? 'type="datetime-local"' : ''; ?>></div><?php endif; ?><?php if ( $is_long ) echo '</div>'; ?>
    <?php endforeach; ?>
    <button type="button" class="at-btn at-btn-primary ufx-extra-run"><?php echo esc_html( $tool['cta'] ); ?></button></div><div class="at-card"><h3>Preview / Result</h3><div class="ufx-extra-status at-help" role="status"></div><div class="ufx-extra-output"></div><textarea class="at-textarea ufx-extra-output-text" rows="10" readonly hidden></textarea></div></div>
    <?php
}
