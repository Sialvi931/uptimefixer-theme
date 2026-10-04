<?php
/**
 * Growth-suite tool definitions and interfaces for Uptime Fixer 3.0.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** @return array<string,array<string,mixed>> */
function alltools_growth_definitions() {
    $rows = array(
        /* Advanced PDF. */
        array( 'compare-pdf', 'Compare PDF Files', 'Compare the extracted text of two PDF versions and highlight changes.', 'pdf-tools', 'search', 'blue', 'Compare PDFs', 'Popular' ),
        array( 'repair-pdf', 'Repair PDF', 'Rebuild a readable PDF structure and download a repaired copy.', 'pdf-tools', 'shield', 'green', 'Repair PDF', '' ),
        array( 'scan-to-pdf', 'Scan Images to PDF', 'Combine photos or document scans into a clean multi-page PDF.', 'pdf-tools', 'image', 'purple', 'Create PDF', 'Popular' ),
        array( 'pdf-to-pdfa', 'PDF to PDF/A', 'Prepare a flattened archival PDF copy and report its validation limitations.', 'pdf-tools', 'download', 'orange', 'Create Archival PDF', '' ),
        array( 'extract-pdf-pages', 'Extract PDF Pages', 'Select page ranges and save them as a separate PDF.', 'pdf-tools', 'resize', 'blue', 'Extract Pages', '' ),
        array( 'pdf-to-text', 'PDF to Text Converter', 'Extract readable text from PDF pages into a TXT file.', 'pdf-tools', 'word', 'green', 'Extract Text', 'Popular' ),
        array( 'html-to-pdf', 'HTML to PDF Converter', 'Turn safe HTML content into a downloadable PDF document.', 'pdf-tools', 'code', 'purple', 'Create PDF', '' ),
        array( 'fill-pdf-form', 'Fill PDF Form', 'Fill existing interactive PDF form fields in your browser.', 'pdf-tools', 'check', 'blue', 'Fill and Download', '' ),

        /* Advanced image. */
        array( 'exif-metadata-remover', 'EXIF Metadata Remover', 'Strip photo metadata by safely re-encoding the image in your browser.', 'image-tools', 'shield', 'green', 'Remove Metadata', 'Privacy' ),
        array( 'image-metadata-viewer', 'Image Metadata Viewer', 'Inspect image dimensions, file information and common JPEG EXIF fields.', 'image-tools', 'search', 'blue', 'View Metadata', '' ),
        array( 'svg-optimizer', 'SVG Optimizer', 'Remove comments, editor metadata and unnecessary whitespace from SVG files.', 'image-tools', 'code', 'purple', 'Optimize SVG', '' ),
        array( 'image-to-ico', 'PNG/JPG to ICO Converter', 'Create a Windows-compatible ICO icon from a PNG or JPG image.', 'image-tools', 'image', 'orange', 'Create ICO', '' ),
        array( 'color-palette-generator', 'Color Palette Generator', 'Extract a useful color palette from any uploaded image.', 'image-tools', 'image', 'pink', 'Generate Palette', '' ),
        array( 'face-blur-tool', 'Face Blur Tool', 'Detect and blur faces, with a manual fallback when detection is unavailable.', 'image-tools', 'shield', 'blue', 'Blur Faces', 'Privacy' ),

        /* Video and audio. */
        array( 'add-subtitles-to-video', 'Add Subtitles to Video', 'Burn an SRT subtitle file into a video locally in your browser.', 'video-tools', 'word', 'purple', 'Add Subtitles', '' ),
        array( 'srt-subtitle-editor', 'SRT Subtitle Editor', 'Edit, renumber and validate SRT subtitle files.', 'video-tools', 'word', 'blue', 'Format SRT', '' ),
        array( 'rotate-video', 'Rotate Video', 'Rotate video by 90, 180 or 270 degrees.', 'video-tools', 'resize', 'orange', 'Rotate Video', '' ),
        array( 'loop-video', 'Loop Video', 'Repeat a short video multiple times and export one file.', 'video-tools', 'grid', 'green', 'Loop Video', '' ),
        array( 'screen-recorder', 'Online Screen Recorder', 'Record a chosen screen, window or browser tab without uploading it.', 'video-tools', 'image', 'red', 'Start Recording', 'Popular' ),
        array( 'voice-recorder', 'Online Voice Recorder', 'Record microphone audio locally and download the recording.', 'audio-tools', 'pulse', 'purple', 'Start Recording', 'Popular' ),
        array( 'audio-noise-reducer', 'Audio Noise Reducer', 'Apply high-pass, low-pass and normalization filters to spoken audio.', 'audio-tools', 'pulse', 'green', 'Reduce Noise', '' ),

        /* Website and validation. */
        array( 'whois-rdap-lookup', 'WHOIS / RDAP Lookup', 'Look up public domain registration data using RDAP.', 'website-tools', 'globe', 'blue', 'Look Up Domain', 'Popular' ),
        array( 'dnssec-checker', 'DNSSEC Checker', 'Check DS and DNSKEY records for a domain.', 'website-tools', 'shield', 'green', 'Check DNSSEC', '' ),
        array( 'mx-lookup', 'MX Record Lookup', 'Find mail exchanger records and priorities for a domain.', 'website-tools', 'search', 'purple', 'Look Up MX', '' ),
        array( 'reverse-dns-lookup', 'Reverse DNS Lookup', 'Find the PTR hostname associated with a public IP address.', 'website-tools', 'globe', 'orange', 'Reverse Lookup', '' ),
        array( 'ip-asn-lookup', 'IP & ASN Information', 'Review public IP, ASN, network provider and approximate region information.', 'website-tools', 'globe', 'blue', 'Check IP / ASN', '' ),
        array( 'http2-http3-checker', 'HTTP/2 & HTTP/3 Checker', 'Inspect protocol hints and Alt-Svc headers for HTTP/2 and HTTP/3 support.', 'website-tools', 'gauge', 'green', 'Check Protocols', '' ),
        array( 'cdn-cache-detector', 'CDN & Cache Detector', 'Detect common CDN signatures and inspect caching headers.', 'website-tools', 'search', 'purple', 'Detect CDN', '' ),
        array( 'cookie-scanner', 'Website Cookie Scanner', 'List cookies set by the initial public page response.', 'website-tools', 'search', 'orange', 'Scan Cookies', '' ),
        array( 'bulk-url-status-checker', 'Bulk URL Status Checker', 'Check HTTP status and response time for up to 20 public URLs.', 'website-tools', 'check', 'blue', 'Check URLs', 'Popular' ),
        array( 'sitemap-validator', 'XML Sitemap Validator', 'Validate sitemap XML, URL limits and public URL entries.', 'website-tools', 'code', 'green', 'Validate Sitemap', '' ),
        array( 'schema-markup-validator', 'Schema Markup Validator', 'Extract and validate JSON-LD structured data from a public webpage.', 'website-tools', 'code', 'purple', 'Validate Schema', '' ),
        array( 'html-error-validator', 'HTML Error Validator', 'Check a public page with the W3C-compatible Nu validation service.', 'developer-tools', 'code', 'red', 'Validate HTML', '' ),
        array( 'accessibility-checker', 'Website Accessibility Checker', 'Run accessibility audits and return actionable failures.', 'website-tools', 'check', 'blue', 'Check Accessibility', 'Recommended' ),
        array( 'lighthouse-audit', 'Complete Lighthouse Audit', 'Audit performance, accessibility, best practices and SEO.', 'website-tools', 'gauge', 'purple', 'Run Lighthouse', 'Recommended' ),
        array( 'page-size-checker', 'Page Size & Resource Checker', 'Measure HTML size and count common page resources.', 'website-tools', 'gauge', 'orange', 'Check Page Size', '' ),
        array( 'ttfb-checker', 'TTFB Checker', 'Measure time to first byte and total initial response time.', 'website-tools', 'gauge', 'green', 'Measure TTFB', '' ),
        array( 'ssl-certificate-decoder', 'SSL Certificate Decoder', 'Decode a pasted PEM certificate without transmitting it.', 'developer-tools', 'shield', 'blue', 'Decode Certificate', '' ),
        array( 'csr-generator', 'CSR Generator', 'Generate a private key and certificate signing request in your browser.', 'developer-tools', 'shield', 'purple', 'Generate CSR', 'Secure' ),
        array( 'email-deliverability-checker', 'Email Deliverability Checker', 'Review MX, SPF and DMARC setup without sending an email.', 'website-tools', 'check', 'green', 'Check Deliverability', '' ),

        /* SEO data tools. */
        array( 'keyword-generator', 'Keyword Generator', 'Generate keyword ideas with genuine search-volume data when an SEO API is configured.', 'marketing-tools', 'search', 'blue', 'Generate Keywords', 'API' ),
        array( 'keyword-difficulty-checker', 'Keyword Difficulty Checker', 'Review keyword volume, competition and SERP difficulty signals.', 'marketing-tools', 'gauge', 'purple', 'Check Difficulty', 'API' ),
        array( 'backlink-checker', 'Backlink Checker', 'Review a domain backlink profile using configured commercial data.', 'marketing-tools', 'globe', 'green', 'Check Backlinks', 'API' ),
        array( 'domain-authority-checker', 'Domain Authority Checker', 'Estimate domain strength from backlink rank and referring-domain metrics.', 'marketing-tools', 'gauge', 'orange', 'Check Authority', 'API' ),
        array( 'keyword-rank-checker', 'Keyword Rank Checker', 'Find ranking keywords and positions for a public domain.', 'marketing-tools', 'search', 'blue', 'Check Rankings', 'API' ),
        array( 'website-traffic-estimator', 'Website Traffic Calculator', 'Estimate organic visits, traffic growth, pageviews and ad revenue from your own keyword and analytics inputs without an API.', 'marketing-tools', 'gauge', 'green', 'Calculate Traffic', 'No API' ),
        array( 'competitor-keyword-checker', 'Competitor Keyword Checker', 'Compare ranking keywords shared by two domains.', 'marketing-tools', 'search', 'purple', 'Compare Competitors', 'API' ),
        array( 'ai-visibility-checker', 'AI Visibility Checker', 'Probe configured AI responses for transparent brand-mention signals.', 'marketing-tools', 'search', 'pink', 'Check AI Visibility', 'AI' ),
        array( 'google-trends-comparison-tool', 'Google Trends Comparison', 'Open a correctly encoded Google Trends comparison for up to five topics.', 'marketing-tools', 'gauge', 'blue', 'Compare Trends', '' ),

        /* File conversion and AI. */
        array( 'epub-to-pdf', 'EPUB to PDF Converter', 'Extract EPUB chapters and create a readable PDF.', 'pdf-tools', 'word', 'purple', 'Convert to PDF', '' ),
        array( 'pdf-to-epub', 'PDF to EPUB Converter', 'Extract PDF text and build a standards-based EPUB ebook.', 'pdf-tools', 'word', 'blue', 'Convert to EPUB', '' ),
        array( 'font-converter', 'Font Converter', 'Convert TTF, OTF, WOFF and WOFF2 fonts locally.', 'developer-tools', 'word', 'green', 'Convert Font', '' ),
        array( 'zip-creator', 'ZIP File Creator', 'Compress multiple files into a ZIP archive in your browser.', 'developer-tools', 'download', 'blue', 'Create ZIP', '' ),
        array( 'zip-extractor', 'ZIP File Extractor', 'Inspect and securely extract selected ZIP contents.', 'developer-tools', 'download', 'orange', 'Extract ZIP', '' ),
        array( 'archive-converter', 'ZIP / TAR Archive Converter', 'Convert between ZIP and uncompressed TAR archives locally.', 'developer-tools', 'download', 'purple', 'Convert Archive', '' ),
        array( 'ai-text-summarizer', 'AI Text Summarizer', 'Create a concise summary using a securely configured AI API.', 'text-tools', 'word', 'blue', 'Summarize Text', 'AI' ),
        array( 'pdf-summarizer', 'AI PDF Summarizer', 'Extract a PDF and summarize its content with a configured AI API.', 'pdf-tools', 'word', 'green', 'Summarize PDF', 'AI' ),
        array( 'audio-transcription', 'Audio Transcription', 'Transcribe an uploaded audio recording through a secure server-side AI API.', 'audio-tools', 'pulse', 'purple', 'Transcribe Audio', 'AI' ),
        array( 'text-to-speech', 'Text to Speech', 'Generate downloadable speech audio through a secure AI API.', 'audio-tools', 'pulse', 'orange', 'Create Speech', 'AI' ),
        array( 'ai-image-generator', 'AI Image Generator', 'Generate an original image from a prompt through a secure AI API.', 'image-tools', 'image', 'pink', 'Generate Image', 'AI' ),
        array( 'product-description-generator', 'Product Description Generator', 'Create product copy from supplied facts using a configured AI API.', 'marketing-tools', 'word', 'green', 'Generate Description', 'AI' ),
    );

    $out = array();
    foreach ( $rows as $row ) {
        $out[ $row[0] ] = array(
            'title' => $row[1], 'short' => $row[1], 'desc' => $row[2],
            'long' => $row[2] . ( 'website-traffic-estimator' === $row[0]
                ? ' Calculations stay in the browser and use only the figures you enter; no live competitor analytics are claimed.'
                : ' Clear limitations are shown and sensitive credentials are never sent to the visitor browser.' ),
            'category' => $row[3], 'icon' => $row[4], 'color' => $row[5],
            'cta' => $row[6], 'badge' => $row[7], 'featured' => false, 'illo' => 'code',
        );
    }
    return $out;
}

add_filter( 'alltools_registered_tools', 'alltools_register_growth_tools' );
function alltools_register_growth_tools( $tools ) {
    return array_merge( $tools, alltools_growth_definitions() );
}

function alltools_growth_status_panel() {
    ?><div class="at-card"><h3><?php esc_html_e( 'Result', 'alltools' ); ?></h3><div class="ufx-growth-status at-help" role="status"><?php esc_html_e( 'Your result will appear here.', 'alltools' ); ?></div><div class="ufx-growth-output"></div><textarea class="at-textarea ufx-growth-output-text" rows="14" readonly hidden></textarea><canvas class="ufx-growth-canvas" hidden></canvas></div><?php
}

function alltools_growth_upload_shell( $slug, $accept, $multiple = false, $controls = '' ) {
    $tool = alltools_get_tool( $slug );
    ?><div class="at-tool-grid at-tool-grid-2 ufx-growth-tool" data-ufx-growth="<?php echo esc_attr( $slug ); ?>"><div class="at-card">
        <div class="at-upload-zone ufx-growth-drop" data-input="<?php echo esc_attr( $slug ); ?>-file"><div class="at-upload-icon"><?php echo alltools_icon_svg( 'upload', 28 ); ?></div><h3><?php echo esc_html( $tool['title'] ); ?></h3><p><?php esc_html_e( 'Choose files or drag and drop them here. Processing stays in your browser unless the tool clearly says it uses a configured API.', 'alltools' ); ?></p><label class="at-btn at-btn-outline" for="<?php echo esc_attr( $slug ); ?>-file"><?php esc_html_e( 'Choose File', 'alltools' ); ?></label><input hidden id="<?php echo esc_attr( $slug ); ?>-file" type="file" accept="<?php echo esc_attr( $accept ); ?>" <?php echo $multiple ? 'multiple' : ''; ?>><div class="at-help ufx-growth-file-name" aria-live="polite"></div></div>
        <?php echo wp_kses( $controls, array( 'div'=>array('class'=>true), 'label'=>array(), 'input'=>array('id'=>true,'class'=>true,'type'=>true,'min'=>true,'max'=>true,'step'=>true,'value'=>true,'placeholder'=>true,'checked'=>true), 'select'=>array('id'=>true,'class'=>true), 'option'=>array('value'=>true,'selected'=>true), 'textarea'=>array('id'=>true,'class'=>true,'rows'=>true,'placeholder'=>true), 'p'=>array('class'=>true), 'small'=>array(), 'strong'=>array() ) ); ?>
        <button type="button" class="at-btn at-btn-primary ufx-growth-run"><?php echo esc_html( $tool['cta'] ); ?></button></div><?php alltools_growth_status_panel(); ?></div><?php
}

function alltools_growth_remote_shell( $slug, $label = 'Public domain or URL', $placeholder = 'example.com', $second = '' ) {
    $tool = alltools_get_tool( $slug );
    ?><div class="at-tool-grid at-tool-grid-2 ufx-growth-tool" data-ufx-growth="<?php echo esc_attr( $slug ); ?>"><div class="at-card"><h3><?php echo esc_html( $tool['title'] ); ?></h3><div class="at-field"><label><?php echo esc_html( $label ); ?></label><input id="<?php echo esc_attr( $slug ); ?>-value" class="at-input" autocomplete="off" placeholder="<?php echo esc_attr( $placeholder ); ?>"></div><?php if ( $second ) : ?><div class="at-field"><label><?php echo esc_html( $second ); ?></label><input id="<?php echo esc_attr( $slug ); ?>-second" class="at-input" autocomplete="off"></div><?php endif; ?><button type="button" class="at-btn at-btn-primary ufx-growth-run"><?php echo esc_html( $tool['cta'] ); ?></button></div><?php alltools_growth_status_panel(); ?></div><?php
}

function alltools_growth_text_shell( $slug, $placeholder, $second = false ) {
    $tool = alltools_get_tool( $slug );
    ?><div class="at-tool-grid at-tool-grid-2 ufx-growth-tool" data-ufx-growth="<?php echo esc_attr( $slug ); ?>"><div class="at-card"><h3><?php echo esc_html( $tool['title'] ); ?></h3><textarea id="<?php echo esc_attr( $slug ); ?>-input" class="at-textarea" rows="12" placeholder="<?php echo esc_attr( $placeholder ); ?>"></textarea><?php if ( $second ) : ?><textarea id="<?php echo esc_attr( $slug ); ?>-input-2" class="at-textarea" rows="5" placeholder="Second input"></textarea><?php endif; ?><button type="button" class="at-btn at-btn-primary ufx-growth-run"><?php echo esc_html( $tool['cta'] ); ?></button></div><?php alltools_growth_status_panel(); ?></div><?php
}

/** Browser-only traffic projection; it never invents live data for a domain. */
function alltools_growth_traffic_calculator() {
    ?>
    <div class="at-tool-grid at-tool-grid-2 ufx-growth-tool ufx-traffic-tool" data-ufx-growth="website-traffic-estimator">
        <div class="at-card">
            <h3><?php esc_html_e( 'Website Traffic Calculator', 'alltools' ); ?></h3>
            <p class="at-help"><?php esc_html_e( 'Use your own analytics or keyword data. Everything is calculated in this browser—no API key, account or file upload is required.', 'alltools' ); ?></p>

            <div class="at-field">
                <label for="traffic-domain"><?php esc_html_e( 'Website/domain (report label only)', 'alltools' ); ?></label>
                <input id="traffic-domain" class="at-input" autocomplete="off" placeholder="example.com">
            </div>

            <div class="at-tool-grid at-tool-grid-2 ufx-traffic-fields">
                <div class="at-field"><label for="traffic-current"><?php esc_html_e( 'Current monthly visits', 'alltools' ); ?></label><input id="traffic-current" class="at-input" type="number" min="0" step="1" value="10000"></div>
                <div class="at-field"><label for="traffic-growth"><?php esc_html_e( 'Expected monthly growth (%)', 'alltools' ); ?></label><input id="traffic-growth" class="at-input" type="number" min="-90" max="500" step="0.1" value="5"></div>
                <div class="at-field"><label for="traffic-months"><?php esc_html_e( 'Projection period (months)', 'alltools' ); ?></label><input id="traffic-months" class="at-input" type="number" min="1" max="36" step="1" value="6"></div>
                <div class="at-field"><label for="traffic-pages"><?php esc_html_e( 'Average pages per visit', 'alltools' ); ?></label><input id="traffic-pages" class="at-input" type="number" min="0.1" max="100" step="0.1" value="1.7"></div>
                <div class="at-field"><label for="traffic-rpm"><?php esc_html_e( 'Ad revenue RPM', 'alltools' ); ?></label><input id="traffic-rpm" class="at-input" type="number" min="0" step="0.01" value="3.50"></div>
                <div class="at-field"><label for="traffic-currency"><?php esc_html_e( 'Revenue currency', 'alltools' ); ?></label><select id="traffic-currency" class="at-input"><option value="$">USD ($)</option><option value="£">GBP (£)</option><option value="€">EUR (€)</option><option value="Rs ">PKR (Rs)</option></select></div>
            </div>

            <div class="at-field">
                <label for="traffic-keywords"><?php esc_html_e( 'Keyword, monthly volume/impressions, Google position', 'alltools' ); ?></label>
                <textarea id="traffic-keywords" class="at-textarea" rows="7" placeholder="website speed test, 12000, 4&#10;uptime checker, 5000, 2&#10;website tools, 8000, 8"></textarea>
                <p class="at-help"><?php esc_html_e( 'Optional: add one keyword per line. You can paste comma-, semicolon- or tab-separated rows from a spreadsheet. Position must be from 1 to 100.', 'alltools' ); ?></p>
            </div>

            <button type="button" class="at-btn at-btn-primary ufx-growth-run"><?php esc_html_e( 'Calculate Traffic', 'alltools' ); ?></button>
        </div>
        <?php alltools_growth_status_panel(); ?>
    </div>
    <?php
}

add_filter( 'alltools_render_tool', 'alltools_render_growth_tool', 20, 2 );
function alltools_render_growth_tool( $rendered, $slug ) {
    $defs = alltools_growth_definitions();
    if ( ! isset( $defs[ $slug ] ) ) return $rendered;

    $single_pdf = array( 'repair-pdf', 'pdf-to-pdfa', 'extract-pdf-pages', 'pdf-to-text', 'fill-pdf-form', 'pdf-to-epub', 'pdf-summarizer' );
    if ( in_array( $slug, $single_pdf, true ) ) {
        $controls = '';
        if ( 'extract-pdf-pages' === $slug ) $controls = '<div class="at-field"><label>Pages</label><input id="extract-pdf-pages-range" class="at-input" placeholder="Example: 1-3,5,8"></div>';
        if ( 'fill-pdf-form' === $slug ) $controls = '<p class="at-help"><strong>Interactive fields found in the PDF will appear after you click the button.</strong></p>';
        alltools_growth_upload_shell( $slug, '.pdf,application/pdf', false, $controls ); return true;
    }
    if ( 'compare-pdf' === $slug ) { alltools_growth_upload_shell( $slug, '.pdf,application/pdf', true ); return true; }
    if ( 'scan-to-pdf' === $slug ) { alltools_growth_upload_shell( $slug, 'image/*', true ); return true; }
    if ( 'html-to-pdf' === $slug ) { alltools_growth_text_shell( $slug, '<h1>Document title</h1><p>Your HTML content...</p>' ); return true; }

    $image_uploads = array( 'exif-metadata-remover', 'image-metadata-viewer', 'svg-optimizer', 'image-to-ico', 'color-palette-generator', 'face-blur-tool' );
    if ( in_array( $slug, $image_uploads, true ) ) {
        $accept = 'svg-optimizer' === $slug ? '.svg,image/svg+xml' : 'image/*';
        $controls = 'face-blur-tool' === $slug ? '<div class="at-tool-grid at-tool-grid-2"><div class="at-field"><label>Manual face X (%)</label><input id="face-blur-tool-x" class="at-input" type="number" min="0" max="100" value="50"></div><div class="at-field"><label>Manual face Y (%)</label><input id="face-blur-tool-y" class="at-input" type="number" min="0" max="100" value="35"></div></div><div class="at-field"><label>Manual area size (%)</label><input id="face-blur-tool-size" class="at-input" type="number" min="5" max="80" value="25"></div>' : '';
        alltools_growth_upload_shell( $slug, $accept, false, $controls ); return true;
    }

    if ( 'add-subtitles-to-video' === $slug ) { alltools_growth_upload_shell( $slug, 'video/*,.srt,application/x-subrip', true ); return true; }
    if ( 'srt-subtitle-editor' === $slug ) { alltools_growth_text_shell( $slug, "1\n00:00:00,000 --> 00:00:03,000\nSubtitle text" ); return true; }
    if ( in_array( $slug, array( 'rotate-video', 'loop-video', 'audio-noise-reducer' ), true ) ) {
        $accept = 'audio-noise-reducer' === $slug ? 'audio/*' : 'video/*';
        $controls = 'rotate-video' === $slug ? '<div class="at-field"><label>Rotation</label><select id="rotate-video-value" class="at-input"><option value="90">90° clockwise</option><option value="180">180°</option><option value="270">270° clockwise</option></select></div>' : ( 'loop-video' === $slug ? '<div class="at-field"><label>Number of loops</label><input id="loop-video-value" class="at-input" type="number" min="2" max="10" value="2"></div>' : '' );
        alltools_growth_upload_shell( $slug, $accept, false, $controls ); return true;
    }
    if ( in_array( $slug, array( 'screen-recorder', 'voice-recorder' ), true ) ) {
        ?><div class="at-tool-grid at-tool-grid-2 ufx-growth-tool" data-ufx-growth="<?php echo esc_attr( $slug ); ?>"><div class="at-card"><h3><?php echo esc_html( $defs[ $slug ]['title'] ); ?></h3><p class="at-help">Permission is requested only after you press start. Nothing is uploaded.</p><button type="button" class="at-btn at-btn-primary ufx-growth-run"><?php echo esc_html( $defs[ $slug ]['cta'] ); ?></button><button type="button" class="at-btn at-btn-outline ufx-growth-stop" disabled>Stop & Download</button></div><?php alltools_growth_status_panel(); ?></div><?php return true;
    }

    if ( 'bulk-url-status-checker' === $slug ) { alltools_growth_text_shell( $slug, "https://example.com/\nhttps://example.org/" ); return true; }
    if ( 'ssl-certificate-decoder' === $slug ) { alltools_growth_text_shell( $slug, '-----BEGIN CERTIFICATE-----' ); return true; }
    if ( 'csr-generator' === $slug ) {
        ?><div class="at-tool-grid at-tool-grid-2 ufx-growth-tool" data-ufx-growth="csr-generator"><div class="at-card"><h3>CSR Details</h3><?php foreach ( array( 'Common name'=>'example.com', 'Organization'=>'Example Ltd', 'Country code'=>'US', 'State'=>'', 'City'=>'', 'Email'=>'admin@example.com' ) as $label=>$placeholder ) : $id = sanitize_title( $label ); ?><div class="at-field"><label><?php echo esc_html( $label ); ?></label><input id="csr-generator-<?php echo esc_attr( $id ); ?>" class="at-input" placeholder="<?php echo esc_attr( $placeholder ); ?>"></div><?php endforeach; ?><button type="button" class="at-btn at-btn-primary ufx-growth-run">Generate CSR</button></div><?php alltools_growth_status_panel(); ?></div><?php return true;
    }

    $remote = array(
        'whois-rdap-lookup', 'dnssec-checker', 'mx-lookup', 'reverse-dns-lookup', 'ip-asn-lookup', 'http2-http3-checker', 'cdn-cache-detector', 'cookie-scanner', 'sitemap-validator', 'schema-markup-validator', 'html-error-validator', 'accessibility-checker', 'lighthouse-audit', 'page-size-checker', 'ttfb-checker', 'email-deliverability-checker',
    );
    if ( in_array( $slug, $remote, true ) ) {
        $is_ip = in_array( $slug, array( 'reverse-dns-lookup', 'ip-asn-lookup' ), true );
        alltools_growth_remote_shell( $slug, $is_ip ? 'Public IP address' : 'Public domain or URL', $is_ip ? '8.8.8.8' : 'example.com' ); return true;
    }

    if ( 'website-traffic-estimator' === $slug ) { alltools_growth_traffic_calculator(); return true; }

    $seo = array( 'keyword-generator', 'keyword-difficulty-checker', 'backlink-checker', 'domain-authority-checker', 'keyword-rank-checker', 'competitor-keyword-checker', 'ai-visibility-checker' );
    if ( in_array( $slug, $seo, true ) ) {
        $domain_tools = array( 'backlink-checker', 'domain-authority-checker', 'keyword-rank-checker' );
        $second = in_array( $slug, array( 'competitor-keyword-checker', 'ai-visibility-checker' ), true ) ? ( 'competitor-keyword-checker' === $slug ? 'Competitor domain' : 'Questions or topics (comma separated)' ) : '';
        alltools_growth_remote_shell( $slug, in_array( $slug, $domain_tools, true ) ? 'Public domain' : ( 'ai-visibility-checker' === $slug ? 'Brand name' : 'Keyword' ), in_array( $slug, $domain_tools, true ) ? 'example.com' : 'website speed', $second ); return true;
    }
    if ( 'google-trends-comparison-tool' === $slug ) { alltools_growth_remote_shell( $slug, 'Topics (comma separated, maximum 5)', 'WordPress,Shopify,Wix' ); return true; }

    if ( 'epub-to-pdf' === $slug ) { alltools_growth_upload_shell( $slug, '.epub,application/epub+zip' ); return true; }
    if ( 'font-converter' === $slug ) { alltools_growth_upload_shell( $slug, '.ttf,.otf,.woff,.woff2,font/*', false, '<div class="at-field"><label>Output format</label><select id="font-converter-format" class="at-input"><option value="ttf">TTF</option><option value="woff">WOFF</option><option value="woff2">WOFF2</option><option value="svg">SVG Font</option></select></div>' ); return true; }
    if ( 'zip-creator' === $slug ) { alltools_growth_upload_shell( $slug, '*/*', true ); return true; }
    if ( 'zip-extractor' === $slug ) { alltools_growth_upload_shell( $slug, '.zip,application/zip' ); return true; }
    if ( 'archive-converter' === $slug ) { alltools_growth_upload_shell( $slug, '.zip,.tar,application/zip,application/x-tar' ); return true; }
    if ( 'audio-transcription' === $slug ) { alltools_growth_upload_shell( $slug, 'audio/*,.mp3,.wav,.m4a,.webm' ); return true; }
    if ( 'ai-text-summarizer' === $slug ) { alltools_growth_text_shell( $slug, 'Paste the text you want to summarize...' ); return true; }
    if ( 'text-to-speech' === $slug ) { alltools_growth_text_shell( $slug, 'Enter text to turn into speech...' ); return true; }
    if ( 'ai-image-generator' === $slug ) { alltools_growth_text_shell( $slug, 'Describe the original image you want to create...' ); return true; }
    if ( 'product-description-generator' === $slug ) { alltools_growth_text_shell( $slug, 'Product name, features, audience, tone and important facts...' ); return true; }

    return $rendered;
}
