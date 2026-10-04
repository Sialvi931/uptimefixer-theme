<?php
/**
 * Helper functions and tool data registry.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** UTF-8 aware text slicing with a safe fallback for hosts without mbstring. */
function alltools_text_slice( $text, $start, $length = null ) {
    $text = (string) $text;
    if ( function_exists( 'mb_substr' ) ) return null === $length ? mb_substr( $text, $start ) : mb_substr( $text, $start, $length );
    return null === $length ? substr( $text, $start ) : substr( $text, $start, $length );
}

/**
 * Get a theme setting.
 *
 * @param string $key
 * @param mixed  $default
 * @return mixed
 */
function alltools_get( $key, $default = '' ) {
    $settings = get_option( 'alltools_settings', array() );
    if ( ! is_array( $settings ) ) {
        $settings = array();
    }
    return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
}

/**
 * Get a homepage setting.
 */
function alltools_home( $key, $default = '' ) {
    $s = get_option( 'alltools_settings_home', array() );
    if ( ! is_array( $s ) ) {
        $s = array();
    }
    return isset( $s[ $key ] ) ? $s[ $key ] : $default;
}

/**
 * Get a contact setting.
 */
function alltools_contact( $key, $default = '' ) {
    $s = get_option( 'alltools_settings_contact', array() );
    if ( ! is_array( $s ) ) {
        $s = array();
    }
    return isset( $s[ $key ] ) ? $s[ $key ] : $default;
}

/**
 * Single source of truth for all tools.
 *
 * @return array
 */
function alltools_all() {
    $tools = array(

        /* ===== CALCULATORS ===== */
        'age-calculator' => array(
            'title'      => 'Age Calculator',
            'short'      => 'Age Calculator',
            'desc'       => 'Calculate your exact age in years, months, days.',
            'long'       => 'Calculate your exact age in years, months, days and more. Find out your age in different time units instantly.',
            'icon'       => 'user',
            'color'      => 'green',
            'category'   => 'calculators',
            'badge'      => '',
            'featured'   => true,
            'cta'        => 'Calculate',
            'illo'       => 'calendar',
        ),
        'percentage-calculator' => array(
            'title'      => 'Percentage Calculator',
            'short'      => 'Percentage Calculator',
            'desc'       => 'Calculate percentage with 5 different calculation modes.',
            'long'       => 'Calculate percentages with 5 different calculation modes — basic, change, of what, add, and subtract percentage.',
            'icon'       => 'percent',
            'color'      => 'purple',
            'category'   => 'calculators',
            'badge'      => '5 Modes',
            'featured'   => true,
            'cta'        => 'Calculate',
            'illo'       => 'percent',
        ),
        'emi-calculator' => array(
            'title'      => 'EMI Calculator',
            'short'      => 'EMI Calculator',
            'desc'       => 'Calculate EMI with amortization schedule and detailed report.',
            'long'       => 'Calculate loan EMI with amortization schedule and detailed report. See exact monthly payment, total interest, and full payment breakdown.',
            'icon'       => 'calculator',
            'color'      => 'orange',
            'category'   => 'calculators',
            'badge'      => '',
            'featured'   => true,
            'cta'        => 'Calculate',
            'illo'       => 'bank',
        ),

        /* ===== IMAGE TOOLS ===== */
        'image-converter' => array(
            'title'      => 'Image Converter',
            'short'      => 'Image Converter',
            'desc'       => 'Convert images between popular formats instantly.',
            'long'       => 'Convert your images between popular formats instantly. Fast, secure, and completely free to use.',
            'icon'       => 'image',
            'color'      => 'purple',
            'category'   => 'image-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Convert',
            'illo'       => 'image-convert',
        ),
        'image-resizer' => array(
            'title'      => 'Image Resizer',
            'short'      => 'Image Resizer',
            'desc'       => 'Resize images to custom dimensions online.',
            'long'       => 'Resize your images to custom dimensions in seconds. Maintain quality and aspect ratio with ease.',
            'icon'       => 'resize',
            'color'      => 'orange',
            'category'   => 'image-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Resize',
            'illo'       => 'image-resize',
        ),
        'favicon-generator' => array(
            'title'      => 'Favicon Generator',
            'short'      => 'Favicon Generator',
            'desc'       => 'Generate favicon in 8 different sizes + HTML code.',
            'long'       => 'Create a favicon from your logo or image in seconds. Download all required sizes and add it to your website easily.',
            'icon'       => 'star',
            'color'      => 'blue',
            'category'   => 'image-tools',
            'badge'      => '8 Sizes',
            'featured'   => false,
            'cta'        => 'Generate',
            'illo'       => 'favicon',
        ),

        /* ===== TEXT TOOLS ===== */
        'word-counter' => array(
            'title'      => 'Word Counter',
            'short'      => 'Word Counter',
            'desc'       => 'Count words, characters, paragraphs and more.',
            'long'       => 'Count words, characters, sentences, and more. Analyze your text with instant insights.',
            'icon'       => 'word',
            'color'      => 'green',
            'category'   => 'text-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Count',
            'illo'       => 'word',
        ),
        'character-counter' => array(
            'title'      => 'Character Counter',
            'short'      => 'Character Counter',
            'desc'       => 'Count characters, letters, numbers and more.',
            'long'       => 'Count characters, words, sentences, and paragraphs in your text. Get detailed text statistics instantly.',
            'icon'       => 'char',
            'color'      => 'purple',
            'category'   => 'text-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Count',
            'illo'       => 'character',
        ),
        'case-converter' => array(
            'title'      => 'Case Converter',
            'short'      => 'Case Converter',
            'desc'       => 'Convert text to different cases in 10 modes.',
            'long'       => 'Convert text to different cases instantly. Supports 10 popular case conversion modes.',
            'icon'       => 'case',
            'color'      => 'orange',
            'category'   => 'text-tools',
            'badge'      => '10 Modes',
            'featured'   => false,
            'cta'        => 'Convert',
            'illo'       => 'case',
        ),
        'remove-duplicate-lines' => array(
            'title'      => 'Remove Duplicate Lines',
            'short'      => 'Remove Duplicate Lines',
            'desc'       => 'Remove duplicate lines from text instantly.',
            'long'       => 'Remove duplicate lines from your text instantly. Clean up your lists, data, and notes with one click.',
            'icon'       => 'duplicate',
            'color'      => 'green',
            'category'   => 'text-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Remove',
            'illo'       => 'cleaner',
        ),
        'remove-extra-spaces' => array(
            'title'      => 'Remove Extra Spaces',
            'short'      => 'Remove Extra Spaces',
            'desc'       => 'Remove extra spaces from text and make it clean.',
            'long'       => 'Remove extra spaces from your text and make it clean, readable and consistent.',
            'icon'       => 'spaces',
            'color'      => 'pink',
            'category'   => 'text-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Remove',
            'illo'       => 'cleaner',
        ),

        /* ===== OTHER TOOLS ===== */
        'qr-code-generator' => array(
            'title'      => 'QR Code Generator',
            'short'      => 'QR Code Generator',
            'desc'       => 'Generate QR codes for URL, text, email, phone, SMS, WiFi, vCard and more.',
            'long'       => 'Create custom QR codes for URLs, text, contacts, WiFi and more. Fast, free and easy to use.',
            'icon'       => 'qr',
            'color'      => 'blue',
            'category'   => 'other-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Generate',
            'illo'       => 'qr',
        ),

        /* ===== WEBSITE TOOLS ===== */
        'website-uptime-checker' => array(
            'title'    => 'Website Uptime Checker',
            'short'    => 'Uptime Checker',
            'desc'     => 'Check if any website is online or down instantly.',
            'long'     => 'Check if any website is online or down instantly. Get response time, server info and IP address in seconds.',
            'icon'     => 'pulse',
            'color'    => 'green',
            'category' => 'website-tools',
            'badge'    => '',
            'featured' => false,
            'cta'      => 'Check',
            'illo'     => 'uptime',
        ),
        'website-speed-test' => array(
            'title'    => 'Website Speed Test',
            'short'    => 'Speed Test',
            'desc'     => 'Analyze page loading speed and response time.',
            'long'     => 'Analyze page loading speed and response time. Get detailed performance metrics for any website.',
            'icon'     => 'gauge',
            'color'    => 'blue',
            'category' => 'website-tools',
            'badge'    => '',
            'featured' => false,
            'cta'      => 'Test',
            'illo'     => 'gauge',
        ),
        'http-status-checker' => array(
            'title'    => 'HTTP Status Checker',
            'short'    => 'HTTP Status',
            'desc'     => 'Check HTTP status codes and server responses.',
            'long'     => 'Check HTTP status codes and server responses for any URL. View full response headers instantly.',
            'icon'     => 'code',
            'color'    => 'purple',
            'category' => 'website-tools',
            'badge'    => '',
            'featured' => false,
            'cta'      => 'Check',
            'illo'     => 'http',
        ),
        'ssl-checker' => array(
            'title'    => 'SSL Checker',
            'short'    => 'SSL Checker',
            'desc'     => 'Verify SSL certificate validity and expiry date.',
            'long'     => 'Verify SSL certificate validity and expiry date. Inspect issuer, signature algorithm, and certificate chain.',
            'icon'     => 'lock',
            'color'    => 'green',
            'category' => 'website-tools',
            'badge'    => '',
            'featured' => false,
            'cta'      => 'Check',
            'illo'     => 'ssl',
        ),
        'dns-lookup' => array(
            'title'    => 'DNS Lookup',
            'short'    => 'DNS Lookup',
            'desc'     => 'Find DNS records for any domain name.',
            'long'     => 'Find DNS records (A, AAAA, MX, TXT, CNAME, NS) for any domain name. Fast and accurate lookups.',
            'icon'     => 'globe',
            'color'    => 'blue',
            'category' => 'website-tools',
            'badge'    => '',
            'featured' => false,
            'cta'      => 'Lookup',
            'illo'     => 'dns',
        ),
        'redirect-checker' => array(
            'title'    => 'Redirect Checker',
            'short'    => 'Redirect Checker',
            'desc'     => 'Trace URL redirects and final destination.',
            'long'     => 'Trace URL redirects and final destination. See the complete redirect chain with status codes and timings.',
            'icon'     => 'redirect',
            'color'    => 'pink',
            'category' => 'website-tools',
            'badge'    => '',
            'featured' => false,
            'cta'      => 'Trace',
            'illo'     => 'redirect',
        ),

        'domain-expiry-checker' => array(
            'title'    => 'Domain Expiry Checker',
            'short'    => 'Domain Expiry',
            'desc'     => 'Check domain registration and expiry dates.',
            'long'     => 'Check when a domain name was registered and when it may expire. Useful for renewals, SEO audits, and website ownership checks.',
            'icon'     => 'calendar',
            'color'    => 'orange',
            'category' => 'website-tools',
            'badge'    => 'RDAP',
            'featured' => false,
            'cta'      => 'Check',
            'illo'     => 'dns',
        ),
        'broken-link-checker' => array(
            'title'    => 'Broken Link Checker',
            'short'    => 'Broken Links',
            'desc'     => 'Scan a page and find broken internal or external links.',
            'long'     => 'Find broken links on any public webpage. The tool scans page links, checks their HTTP status, and highlights URLs that need fixing.',
            'icon'     => 'link',
            'color'    => 'red',
            'category' => 'website-tools',
            'badge'    => 'SEO',
            'featured' => false,
            'cta'      => 'Scan',
            'illo'     => 'http',
        ),
        'robots-txt-generator' => array(
            'title'    => 'Robots.txt Generator',
            'short'    => 'Robots.txt',
            'desc'     => 'Generate a clean robots.txt file for your website.',
            'long'     => 'Create a robots.txt file with crawl rules, sitemap URL, and common bot directives. Copy or download the file instantly.',
            'icon'     => 'code',
            'color'    => 'blue',
            'category' => 'website-tools',
            'badge'    => '',
            'featured' => false,
            'cta'      => 'Generate',
            'illo'     => 'http',
        ),
        'xml-sitemap-generator' => array(
            'title'    => 'XML Sitemap Generator',
            'short'    => 'XML Sitemap',
            'desc'     => 'Generate a simple XML sitemap from your URLs.',
            'long'     => 'Create a clean XML sitemap by entering website URLs. Set priority and change frequency, then copy or download the sitemap file.',
            'icon'     => 'globe',
            'color'    => 'green',
            'category' => 'website-tools',
            'badge'    => '',
            'featured' => false,
            'cta'      => 'Generate',
            'illo'     => 'dns',
        ),




        /* ===== PDF TOOLS ===== */
        'pdf-compressor' => array(
            'title'      => 'PDF Compressor',
            'short'      => 'PDF Compressor',
            'desc'       => 'Compress PDF files directly in your browser.',
            'long'       => 'Compress PDF files online without uploading them to a server. Reduce file size and download an optimized PDF instantly.',
            'icon'       => 'download',
            'color'      => 'purple',
            'category'   => 'pdf-tools',
            'badge'      => 'Browser Based',
            'featured'   => false,
            'cta'        => 'Compress',
            'illo'       => 'pdf',
        ),
        'merge-pdf' => array(
            'title'      => 'Merge PDF',
            'short'      => 'Merge PDF',
            'desc'       => 'Combine multiple PDF files into one document.',
            'long'       => 'Merge multiple PDF files into a single document in your browser. Reorder, combine, and download your merged PDF quickly.',
            'icon'       => 'duplicate',
            'color'      => 'blue',
            'category'   => 'pdf-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Merge',
            'illo'       => 'pdf',
        ),
        'split-pdf' => array(
            'title'      => 'Split PDF',
            'short'      => 'Split PDF',
            'desc'       => 'Extract selected pages from a PDF file.',
            'long'       => 'Split a PDF file online by extracting selected pages or page ranges. Process your PDF in the browser and download the new file.',
            'icon'       => 'scissors',
            'color'      => 'orange',
            'category'   => 'pdf-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Split',
            'illo'       => 'pdf',
        ),
        'jpg-to-pdf' => array(
            'title'      => 'JPG to PDF',
            'short'      => 'JPG to PDF',
            'desc'       => 'Convert JPG images into a PDF document.',
            'long'       => 'Convert JPG images into a clean PDF file. Upload one or more images, arrange them, and download a ready-to-share PDF.',
            'icon'       => 'image',
            'color'      => 'green',
            'category'   => 'pdf-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Convert',
            'illo'       => 'image-convert',
        ),

        'pdf-to-jpg' => array(
            'title'      => 'PDF to JPG',
            'short'      => 'PDF to JPG',
            'desc'       => 'Convert PDF pages into JPG images.',
            'long'       => 'Convert selected PDF pages into JPG images directly in your browser. Download each page as an image.',
            'icon'       => 'image',
            'color'      => 'purple',
            'category'   => 'pdf-tools',
            'badge'      => 'Browser Based',
            'featured'   => false,
            'cta'        => 'Convert',
            'illo'       => 'pdf',
        ),
        'invoice-generator' => array(
            'title'      => 'Invoice Generator',
            'short'      => 'Invoice Generator',
            'desc'       => 'Create a simple invoice and download as PDF.',
            'long'       => 'Create a clean invoice with business details, client details, line items, tax, discount, and totals. Download as PDF instantly.',
            'icon'       => 'download',
            'color'      => 'blue',
            'category'   => 'pdf-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Create',
            'illo'       => 'pdf',
        ),


        /* ===== EXTRA IMAGE TOOLS ===== */
        'image-compressor' => array(
            'title'      => 'Image Compressor',
            'short'      => 'Image Compressor',
            'desc'       => 'Compress JPG, PNG, or WebP images online.',
            'long'       => 'Compress images online while controlling quality, format, and output size. Everything runs directly in your browser.',
            'icon'       => 'image',
            'color'      => 'pink',
            'category'   => 'image-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Compress',
            'illo'       => 'image-resize',
        ),
        'color-picker-from-image' => array(
            'title'      => 'Color Picker from Image',
            'short'      => 'Color Picker',
            'desc'       => 'Pick HEX and RGB colors from any image.',
            'long'       => 'Upload an image and click anywhere to pick a color. Get HEX, RGB, and HSL color values instantly.',
            'icon'       => 'image',
            'color'      => 'purple',
            'category'   => 'image-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Pick Color',
            'illo'       => 'image-convert',
        ),

        'webp-converter' => array(
            'title'      => 'WebP Converter',
            'short'      => 'WebP Converter',
            'desc'       => 'Convert images to WebP for smaller website files.',
            'long'       => 'Convert JPG, PNG, or other image files to WebP directly in your browser. Optimize images for faster websites.',
            'icon'       => 'image',
            'color'      => 'green',
            'category'   => 'image-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Convert',
            'illo'       => 'image-convert',
        ),
        'jpg-to-png' => array(
            'title'      => 'JPG to PNG',
            'short'      => 'JPG to PNG',
            'desc'       => 'Convert JPG images into PNG files.',
            'long'       => 'Convert JPG images to PNG format in your browser. Fast, private, and easy to use.',
            'icon'       => 'image',
            'color'      => 'blue',
            'category'   => 'image-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Convert',
            'illo'       => 'image-convert',
        ),
        'png-to-jpg' => array(
            'title'      => 'PNG to JPG',
            'short'      => 'PNG to JPG',
            'desc'       => 'Convert PNG images into JPG files.',
            'long'       => 'Convert PNG images to JPG format with an optional background color and quality control.',
            'icon'       => 'image',
            'color'      => 'orange',
            'category'   => 'image-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Convert',
            'illo'       => 'image-convert',
        ),
        'image-cropper' => array(
            'title'      => 'Image Cropper',
            'short'      => 'Image Cropper',
            'desc'       => 'Crop images by custom position and size.',
            'long'       => 'Upload an image, set crop dimensions and position, preview the result, and download the cropped image instantly.',
            'icon'       => 'resize',
            'color'      => 'purple',
            'category'   => 'image-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Crop',
            'illo'       => 'image-resize',
        ),


        /* ===== DEVELOPER TOOLS ===== */
        'json-formatter' => array(
            'title'      => 'JSON Formatter',
            'short'      => 'JSON Formatter',
            'desc'       => 'Format, validate, minify, and copy JSON.',
            'long'       => 'Format and validate JSON online. Beautify, minify, sort keys, copy, and download clean JSON instantly.',
            'icon'       => 'code',
            'color'      => 'blue',
            'category'   => 'developer-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Format',
            'illo'       => 'http',
        ),

        'base64-encoder-decoder' => array(
            'title'      => 'Base64 Encoder Decoder',
            'short'      => 'Base64 Tool',
            'desc'       => 'Encode and decode Base64 text instantly.',
            'long'       => 'Encode plain text to Base64 or decode Base64 back to readable text. Works directly in your browser.',
            'icon'       => 'code',
            'color'      => 'purple',
            'category'   => 'developer-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Encode',
            'illo'       => 'http',
        ),
        'jwt-decoder' => array(
            'title'      => 'JWT Decoder',
            'short'      => 'JWT Decoder',
            'desc'       => 'Decode JWT header and payload safely.',
            'long'       => 'Decode JSON Web Tokens and inspect the header, payload, and signature parts. This tool does not verify signatures.',
            'icon'       => 'shield',
            'color'      => 'green',
            'category'   => 'developer-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Decode',
            'illo'       => 'http',
        ),
        'url-encoder-decoder' => array(
            'title'      => 'URL Encoder Decoder',
            'short'      => 'URL Encoder',
            'desc'       => 'Encode and decode URLs or query strings.',
            'long'       => 'Encode special characters for safe URLs or decode encoded strings back to readable text.',
            'icon'       => 'link',
            'color'      => 'blue',
            'category'   => 'developer-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Encode',
            'illo'       => 'http',
        ),
        'uuid-generator' => array(
            'title'      => 'UUID Generator',
            'short'      => 'UUID Generator',
            'desc'       => 'Generate random UUID v4 values.',
            'long'       => 'Generate one or many UUID v4 identifiers for apps, databases, testing, and development workflows.',
            'icon'       => 'grid',
            'color'      => 'orange',
            'category'   => 'developer-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Generate',
            'illo'       => 'http',
        ),
        'timestamp-converter' => array(
            'title'      => 'Timestamp Converter',
            'short'      => 'Timestamp',
            'desc'       => 'Convert Unix timestamps and readable dates.',
            'long'       => 'Convert Unix timestamps to readable dates and convert dates back to seconds or milliseconds.',
            'icon'       => 'calendar',
            'color'      => 'pink',
            'category'   => 'developer-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Convert',
            'illo'       => 'calendar',
        ),
        'hash-generator' => array(
            'title'      => 'Hash Generator',
            'short'      => 'Hash Generator',
            'desc'       => 'Generate MD5, SHA-1, SHA-256 and SHA-512 hashes.',
            'long'       => 'Generate common text hashes including MD5, SHA-1, SHA-256 and SHA-512. Useful for development and checksums.',
            'icon'       => 'lock',
            'color'      => 'green',
            'category'   => 'developer-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Generate',
            'illo'       => 'ssl',
        ),
        'regex-tester' => array(
            'title'      => 'Regex Tester',
            'short'      => 'Regex Tester',
            'desc'       => 'Test regular expressions against sample text.',
            'long'       => 'Write a regular expression, choose flags, test it against sample text, and view highlighted matches instantly.',
            'icon'       => 'code',
            'color'      => 'purple',
            'category'   => 'developer-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Test',
            'illo'       => 'http',
        ),
        'cron-expression-generator' => array(
            'title'      => 'Cron Expression Generator',
            'short'      => 'Cron Generator',
            'desc'       => 'Build common cron expressions quickly.',
            'long'       => 'Generate cron expressions for common schedules like hourly, daily, weekly, monthly, or a custom time.',
            'icon'       => 'calendar',
            'color'      => 'blue',
            'category'   => 'developer-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Generate',
            'illo'       => 'calendar',
        ),
        'password-generator' => array(
            'title'      => 'Password Generator',
            'short'      => 'Password Generator',
            'desc'       => 'Generate secure random passwords.',
            'long'       => 'Generate secure random passwords with custom length, symbols, numbers, uppercase, and lowercase options.',
            'icon'       => 'lock',
            'color'      => 'orange',
            'category'   => 'developer-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Generate',
            'illo'       => 'ssl',
        ),
        'lorem-ipsum-generator' => array(
            'title'      => 'Lorem Ipsum Generator',
            'short'      => 'Lorem Ipsum',
            'desc'       => 'Generate placeholder text for layouts.',
            'long'       => 'Generate lorem ipsum paragraphs, sentences, or words for design mockups, websites, and content placeholders.',
            'icon'       => 'word',
            'color'      => 'pink',
            'category'   => 'developer-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Generate',
            'illo'       => 'word-illo',
        ),


        /* ===== AGE TOOLS ===== */
        'check-friends-age' => array(
            'title'    => "Check Friend's Age",
            'short'    => "Friend's Age",
            'desc'     => "Find your friend's exact age, next birthday, and fun details instantly.",
            'long'     => "Calculate your friend's exact age, next birthday countdown, zodiac sign, generation.",
            'icon'     => 'user',
            'color'    => 'green',
            'category' => 'calculators',
            'badge'    => '',
            'featured' => false,
            'cta'      => 'Check',
            'illo'     => 'calendar',
        ),
        'share-age-result' => array(
            'title'    => 'Share Age Result',
            'short'    => 'Share Age Result',
            'desc'     => 'Share your age insights with friends in a beautiful way.',
            'long'     => 'Create and share a beautiful age insight card. Customize theme, select what to include, and share or download your card.',
            'icon'     => 'heart',
            'color'    => 'purple',
            'category' => 'calculators',
            'badge'    => '',
            'featured' => false,
            'cta'      => 'Share',
            'illo'     => 'calendar',
        ),

        /* ===== MORE IMAGE & SOCIAL TOOLS ===== */
        'youtube-thumbnail-size-fixer' => array(
            'title'      => 'YouTube Thumbnail Size Fixer',
            'short'      => 'YouTube Thumbnail',
            'desc'       => 'Resize any image to YouTube thumbnail size 1280×720.',
            'long'       => 'Upload an image and resize or crop it perfectly for YouTube thumbnails at 1280×720 pixels.',
            'icon'       => 'image',
            'color'      => 'red',
            'category'   => 'image-tools',
            'badge'      => '1280×720',
            'featured'   => false,
            'cta'        => 'Resize',
            'illo'       => 'image-resize',
        ),
        'instagram-post-resizer' => array(
            'title'      => 'Instagram Post Resizer',
            'short'      => 'Instagram Resizer',
            'desc'       => 'Resize images for Instagram post sizes instantly.',
            'long'       => 'Prepare images for Instagram square, portrait and story sizes in one click.',
            'icon'       => 'image',
            'color'      => 'pink',
            'category'   => 'image-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Resize',
            'illo'       => 'image-resize',
        ),
        'facebook-cover-resizer' => array(
            'title'      => 'Facebook Cover Resizer',
            'short'      => 'Facebook Cover',
            'desc'       => 'Resize images for Facebook cover size.',
            'long'       => 'Resize or crop images to the recommended Facebook cover dimensions.',
            'icon'       => 'image',
            'color'      => 'blue',
            'category'   => 'image-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Resize',
            'illo'       => 'image-resize',
        ),
        'linkedin-banner-resizer' => array(
            'title'      => 'LinkedIn Banner Resizer',
            'short'      => 'LinkedIn Banner',
            'desc'       => 'Resize images for LinkedIn banner size.',
            'long'       => 'Resize or crop images to LinkedIn profile and page banner dimensions.',
            'icon'       => 'image',
            'color'      => 'blue',
            'category'   => 'image-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Resize',
            'illo'       => 'image-resize',
        ),
        'twitter-header-resizer' => array(
            'title'      => 'Twitter / X Header Resizer',
            'short'      => 'X Header',
            'desc'       => 'Resize images for Twitter/X header size.',
            'long'       => 'Resize images to the recommended Twitter/X header dimensions quickly and easily.',
            'icon'       => 'image',
            'color'      => 'purple',
            'category'   => 'image-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Resize',
            'illo'       => 'image-resize',
        ),
        'social-media-image-resizer' => array(
            'title'      => 'Social Media Image Resizer',
            'short'      => 'Social Resizer',
            'desc'       => 'Resize images for popular social media sizes.',
            'long'       => 'Resize images for YouTube, Instagram, Facebook, LinkedIn and X using ready-made presets.',
            'icon'       => 'resize',
            'color'      => 'orange',
            'category'   => 'image-tools',
            'badge'      => 'Presets',
            'featured'   => false,
            'cta'        => 'Resize',
            'illo'       => 'image-resize',
        ),
        'image-watermark-tool' => array(
            'title'      => 'Image Watermark Tool',
            'short'      => 'Image Watermark',
            'desc'       => 'Add a text watermark to your images instantly.',
            'long'       => 'Upload an image, add a custom watermark, set opacity and position, then download the result.',
            'icon'       => 'image',
            'color'      => 'purple',
            'category'   => 'image-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Add',
            'illo'       => 'image-convert',
        ),
        'image-to-pdf' => array(
            'title'      => 'Image to PDF',
            'short'      => 'Image to PDF',
            'desc'       => 'Convert one or more images into a PDF file.',
            'long'       => 'Upload JPG, PNG or WebP images and combine them into a downloadable PDF document.',
            'icon'       => 'download',
            'color'      => 'red',
            'category'   => 'pdf-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Convert',
            'illo'       => 'image-convert',
        ),
        'bulk-image-compressor' => array(
            'title'      => 'Bulk Image Compressor',
            'short'      => 'Bulk Image Compressor',
            'desc'       => 'Compress multiple images in one go.',
            'long'       => 'Upload multiple images, compress them in your browser, and download each optimized image.',
            'icon'       => 'image',
            'color'      => 'green',
            'category'   => 'image-tools',
            'badge'      => 'Bulk',
            'featured'   => false,
            'cta'        => 'Compress',
            'illo'       => 'image-convert',
        ),

        /* ===== MORE WEBSITE / SEO TOOLS ===== */
        'meta-tag-generator' => array(
            'title'      => 'Meta Tag Generator',
            'short'      => 'Meta Tag Generator',
            'desc'       => 'Generate title, description and social meta tags.',
            'long'       => 'Generate clean SEO meta tags including title, description, canonical, Open Graph and Twitter Card tags.',
            'icon'       => 'code',
            'color'      => 'green',
            'category'   => 'website-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Generate',
            'illo'       => 'code',
        ),
        'open-graph-preview-tool' => array(
            'title'      => 'Open Graph Preview Tool',
            'short'      => 'Open Graph Preview',
            'desc'       => 'Preview how your content appears when shared.',
            'long'       => 'Preview an Open Graph card for social sharing with title, description, site name and image.',
            'icon'       => 'image',
            'color'      => 'blue',
            'category'   => 'website-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Preview',
            'illo'       => 'image-convert',
        ),
        'twitter-card-preview-tool' => array(
            'title'      => 'Twitter Card Preview Tool',
            'short'      => 'Twitter Card Preview',
            'desc'       => 'Preview a Twitter/X social card instantly.',
            'long'       => 'Build and preview a Twitter/X card with title, description and image before publishing.',
            'icon'       => 'image',
            'color'      => 'purple',
            'category'   => 'website-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Preview',
            'illo'       => 'image-convert',
        ),
        'keyword-density-checker' => array(
            'title'      => 'Keyword Density Checker',
            'short'      => 'Keyword Density',
            'desc'       => 'Analyze keyword frequency and density in your text.',
            'long'       => 'Check word count, frequency and keyword density to improve your on-page SEO writing.',
            'icon'       => 'search',
            'color'      => 'orange',
            'category'   => 'website-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Analyze',
            'illo'       => 'word',
        ),
        'serp-preview-tool' => array(
            'title'      => 'SERP Preview Tool',
            'short'      => 'SERP Preview',
            'desc'       => 'Preview your Google search result snippet.',
            'long'       => 'Preview your page title, URL and meta description like a Google search result snippet.',
            'icon'       => 'search',
            'color'      => 'green',
            'category'   => 'website-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Preview',
            'illo'       => 'search',
        ),
        'robots-txt-checker' => array(
            'title'      => 'Robots.txt Checker',
            'short'      => 'Robots.txt Checker',
            'desc'       => 'Validate a robots.txt file and spot common issues.',
            'long'       => 'Paste your robots.txt content and quickly check for missing directives and common formatting issues.',
            'icon'       => 'code',
            'color'      => 'blue',
            'category'   => 'website-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Check',
            'illo'       => 'code',
        ),
        'sitemap-url-extractor' => array(
            'title'      => 'Sitemap URL Extractor',
            'short'      => 'Sitemap Extractor',
            'desc'       => 'Extract all URLs from an XML sitemap.',
            'long'       => 'Paste an XML sitemap and extract all <loc> URLs instantly for review or export.',
            'icon'       => 'link',
            'color'      => 'purple',
            'category'   => 'website-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Extract',
            'illo'       => 'code',
        ),
        'hreflang-tag-generator' => array(
            'title'      => 'Hreflang Tag Generator',
            'short'      => 'Hreflang Generator',
            'desc'       => 'Generate hreflang tags for multilingual pages.',
            'long'       => 'Create correct hreflang link tags for alternate language and region versions of your pages.',
            'icon'       => 'code',
            'color'      => 'orange',
            'category'   => 'website-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Generate',
            'illo'       => 'code',
        ),
        'schema-markup-generator' => array(
            'title'      => 'Schema Markup Generator',
            'short'      => 'Schema Generator',
            'desc'       => 'Generate basic JSON-LD schema markup.',
            'long'       => 'Generate JSON-LD schema for Organization, Website, Article and LocalBusiness pages.',
            'icon'       => 'code',
            'color'      => 'green',
            'category'   => 'website-tools',
            'badge'      => 'JSON-LD',
            'featured'   => false,
            'cta'        => 'Generate',
            'illo'       => 'code',
        ),
        'canonical-url-checker' => array(
            'title'      => 'Canonical URL Checker',
            'short'      => 'Canonical Checker',
            'desc'       => 'Extract and inspect canonical tags from HTML.',
            'long'       => 'Paste a page source snippet and extract the canonical URL instantly.',
            'icon'       => 'link',
            'color'      => 'blue',
            'category'   => 'website-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Check',
            'illo'       => 'code',
        ),

        /* ===== MORE DEVELOPER TOOLS ===== */
        'html-minifier' => array(
            'title'      => 'HTML Minifier',
            'short'      => 'HTML Minifier',
            'desc'       => 'Minify HTML code instantly.',
            'long'       => 'Remove unnecessary spaces and comments from your HTML code in one click.',
            'icon'       => 'code',
            'color'      => 'purple',
            'category'   => 'developer-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Minify',
            'illo'       => 'code',
        ),
        'css-minifier' => array(
            'title'      => 'CSS Minifier',
            'short'      => 'CSS Minifier',
            'desc'       => 'Minify CSS code instantly.',
            'long'       => 'Compress your CSS by removing comments and extra whitespace with one click.',
            'icon'       => 'code',
            'color'      => 'blue',
            'category'   => 'developer-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Minify',
            'illo'       => 'code',
        ),
        'js-minifier' => array(
            'title'      => 'JS Minifier',
            'short'      => 'JS Minifier',
            'desc'       => 'Minify JavaScript code instantly.',
            'long'       => 'Quickly minify JavaScript by removing comments and unnecessary whitespace.',
            'icon'       => 'code',
            'color'      => 'orange',
            'category'   => 'developer-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Minify',
            'illo'       => 'code',
        ),
        'html-beautifier' => array(
            'title'      => 'HTML Beautifier',
            'short'      => 'HTML Beautifier',
            'desc'       => 'Beautify and indent your HTML code.',
            'long'       => 'Format messy HTML into a more readable structure with indentation.',
            'icon'       => 'code',
            'color'      => 'green',
            'category'   => 'developer-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Beautify',
            'illo'       => 'code',
        ),
        'css-beautifier' => array(
            'title'      => 'CSS Beautifier',
            'short'      => 'CSS Beautifier',
            'desc'       => 'Beautify and format CSS code.',
            'long'       => 'Format and indent CSS code to make it easier to read and edit.',
            'icon'       => 'code',
            'color'      => 'purple',
            'category'   => 'developer-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Beautify',
            'illo'       => 'code',
        ),
        'color-contrast-checker' => array(
            'title'      => 'Color Contrast Checker',
            'short'      => 'Contrast Checker',
            'desc'       => 'Check WCAG contrast ratio between two colors.',
            'long'       => 'Compare text and background colors to see the contrast ratio and WCAG compliance.',
            'icon'       => 'percent',
            'color'      => 'pink',
            'category'   => 'developer-tools',
            'badge'      => 'WCAG',
            'featured'   => false,
            'cta'        => 'Check',
            'illo'       => 'code',
        ),

        /* ===== MORE BUSINESS / PDF TOOLS ===== */
        'quotation-generator' => array(
            'title'      => 'Quotation Generator',
            'short'      => 'Quotation Generator',
            'desc'       => 'Create a professional quotation and download it as PDF.',
            'long'       => 'Generate a clean quotation with business and client details, line items and totals.',
            'icon'       => 'download',
            'color'      => 'green',
            'category'   => 'pdf-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Create',
            'illo'       => 'bank',
        ),
        'receipt-generator' => array(
            'title'      => 'Receipt Generator',
            'short'      => 'Receipt Generator',
            'desc'       => 'Create a simple payment receipt and download as PDF.',
            'long'       => 'Generate a receipt with payer, payee, line items and payment summary in PDF format.',
            'icon'       => 'download',
            'color'      => 'blue',
            'category'   => 'pdf-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Create',
            'illo'       => 'bank',
        ),
        'estimate-generator' => array(
            'title'      => 'Estimate Generator',
            'short'      => 'Estimate Generator',
            'desc'       => 'Create an estimate or proposal PDF quickly.',
            'long'       => 'Build a neat estimate document with scope items, amounts and notes.',
            'icon'       => 'download',
            'color'      => 'orange',
            'category'   => 'pdf-tools',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Create',
            'illo'       => 'bank',
        ),

        /* ===== MORE CALCULATORS ===== */
        'tax-calculator' => array(
            'title'      => 'Tax Calculator',
            'short'      => 'Tax Calculator',
            'desc'       => 'Calculate tax, subtotal and total amounts instantly.',
            'long'       => 'Calculate tax inclusive or exclusive amounts and see total payable values immediately.',
            'icon'       => 'percent',
            'color'      => 'green',
            'category'   => 'calculators',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Calculate',
            'illo'       => 'percent',
        ),
        'profit-margin-calculator' => array(
            'title'      => 'Profit Margin Calculator',
            'short'      => 'Profit Margin',
            'desc'       => 'Calculate profit, margin and markup.',
            'long'       => 'Enter cost and selling price to calculate gross profit, margin and markup.',
            'icon'       => 'calculator',
            'color'      => 'purple',
            'category'   => 'calculators',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Calculate',
            'illo'       => 'calculator',
        ),
        'paypal-fee-calculator' => array(
            'title'      => 'PayPal Fee Calculator',
            'short'      => 'PayPal Fee',
            'desc'       => 'Estimate PayPal fees and net earnings.',
            'long'       => 'Calculate estimated PayPal processing fees, net received amount or reverse-calculate the amount to request.',
            'icon'       => 'calculator',
            'color'      => 'blue',
            'category'   => 'calculators',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Calculate',
            'illo'       => 'calculator',
        ),
        'stripe-fee-calculator' => array(
            'title'      => 'Stripe Fee Calculator',
            'short'      => 'Stripe Fee',
            'desc'       => 'Estimate Stripe fees and your net payout.',
            'long'       => 'Calculate estimated Stripe payment processing fees and the amount you will receive.',
            'icon'       => 'calculator',
            'color'      => 'orange',
            'category'   => 'calculators',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Calculate',
            'illo'       => 'calculator',
        ),
        'freelance-hourly-rate-calculator' => array(
            'title'      => 'Freelance Hourly Rate Calculator',
            'short'      => 'Freelance Rate',
            'desc'       => 'Calculate your ideal freelance hourly rate.',
            'long'       => 'Estimate a sustainable freelance hourly rate based on target income, business costs and billable hours.',
            'icon'       => 'calculator',
            'color'      => 'green',
            'category'   => 'calculators',
            'badge'      => '',
            'featured'   => false,
            'cta'        => 'Calculate',
            'illo'       => 'calculator',
        ),

        /* ===== NO-API HIGH DEMAND TOOLS ===== */
        'pdf-rotate-tool' => array(
            'title'      => 'PDF Rotate Tool',
            'short'      => 'PDF Rotate',
            'desc'       => 'Rotate PDF pages online without any API.',
            'long'       => 'Upload a PDF and rotate all pages or selected pages directly in your browser.',
            'icon'       => 'download',
            'color'      => 'purple',
            'category'   => 'pdf-tools',
            'badge'      => 'No API',
            'featured'   => false,
            'cta'        => 'Rotate',
            'illo'       => 'image-convert',
        ),
        'pdf-watermark-tool' => array(
            'title'      => 'PDF Watermark Tool',
            'short'      => 'PDF Watermark',
            'desc'       => 'Add a text watermark to PDF pages.',
            'long'       => 'Add a custom text watermark to all pages of your PDF using client-side processing.',
            'icon'       => 'download',
            'color'      => 'blue',
            'category'   => 'pdf-tools',
            'badge'      => 'No API',
            'featured'   => false,
            'cta'        => 'Watermark',
            'illo'       => 'image-convert',
        ),
        'pdf-page-remover' => array(
            'title'      => 'PDF Page Remover',
            'short'      => 'PDF Page Remover',
            'desc'       => 'Remove selected pages from a PDF.',
            'long'       => 'Upload a PDF, enter page numbers to remove, and download the cleaned PDF.',
            'icon'       => 'download',
            'color'      => 'red',
            'category'   => 'pdf-tools',
            'badge'      => 'No API',
            'featured'   => false,
            'cta'        => 'Remove',
            'illo'       => 'image-convert',
        ),
        'resize-image-to-100kb' => array(
            'title'      => 'Resize Image to 100KB',
            'short'      => 'Image to 100KB',
            'desc'       => 'Compress an image to around 100KB.',
            'long'       => 'Upload an image and compress it toward a target size like 50KB, 100KB or 200KB.',
            'icon'       => 'image',
            'color'      => 'green',
            'category'   => 'image-tools',
            'badge'      => 'No API',
            'featured'   => false,
            'cta'        => 'Compress',
            'illo'       => 'image-convert',
        ),
        'passport-photo-maker' => array(
            'title'      => 'Passport Photo Maker',
            'short'      => 'Passport Photo',
            'desc'       => 'Create passport size photos from any image.',
            'long'       => 'Upload a portrait and generate a passport-style photo in common sizes.',
            'icon'       => 'image',
            'color'      => 'orange',
            'category'   => 'image-tools',
            'badge'      => 'No API',
            'featured'   => false,
            'cta'        => 'Create',
            'illo'       => 'image-resize',
        ),
        'profile-picture-maker' => array(
            'title'      => 'Profile Picture Maker',
            'short'      => 'Profile Picture',
            'desc'       => 'Create a square or circular profile picture.',
            'long'       => 'Crop and export a clean profile picture for social media accounts.',
            'icon'       => 'image',
            'color'      => 'purple',
            'category'   => 'image-tools',
            'badge'      => 'No API',
            'featured'   => false,
            'cta'        => 'Create',
            'illo'       => 'image-resize',
        ),
        'circle-crop-image' => array(
            'title'      => 'Circle Crop Image',
            'short'      => 'Circle Crop',
            'desc'       => 'Crop any image into a perfect circle.',
            'long'       => 'Upload an image and export it as a transparent PNG circle crop.',
            'icon'       => 'image',
            'color'      => 'pink',
            'category'   => 'image-tools',
            'badge'      => 'No API',
            'featured'   => false,
            'cta'        => 'Crop',
            'illo'       => 'image-resize',
        ),
        'security-headers-checker' => array(
            'title'      => 'Security Headers Checker',
            'short'      => 'Security Headers',
            'desc'       => 'Check important website security headers.',
            'long'       => 'Check whether a website uses important headers such as HSTS, CSP, X-Frame-Options and more.',
            'icon'       => 'shield',
            'color'      => 'green',
            'category'   => 'website-tools',
            'badge'      => 'No API',
            'featured'   => false,
            'cta'        => 'Check',
            'illo'       => 'code',
        ),
        'mixed-content-checker' => array(
            'title'      => 'Mixed Content Checker',
            'short'      => 'Mixed Content',
            'desc'       => 'Find insecure HTTP assets on HTTPS pages.',
            'long'       => 'Scan page HTML and find images, scripts, stylesheets and links loaded over insecure HTTP.',
            'icon'       => 'shield',
            'color'      => 'orange',
            'category'   => 'website-tools',
            'badge'      => 'No API',
            'featured'   => false,
            'cta'        => 'Check',
            'illo'       => 'code',
        ),

    );

    return apply_filters( 'alltools_registered_tools', $tools );
}

/**
 * Get one tool's data by slug.
 */
function alltools_get_tool( $slug ) {
    $all = alltools_all();
    return isset( $all[ $slug ] ) ? $all[ $slug ] : array();
}

/**
 * Get category label by slug.
 */
function alltools_cat_label( $slug ) {
    $categories = alltools_tool_categories();
    return isset( $categories[ $slug ]['label'] ) ? $categories[ $slug ]['label'] : ucwords( str_replace( '-', ' ', $slug ) );
}

/**
 * Public tool-category hubs. This is the shared source for navigation,
 * taxonomy terms, archive copy and structured data.
 */
function alltools_tool_categories() {
    return array(
        'calculators' => array(
            'label' => 'Calculators', 'icon' => 'calculator', 'tone' => 'green',
            'description' => 'Free online calculators for dates, percentages, finance, measurements and everyday planning. Enter the requested values, review the assumptions and compare scenarios before relying on a result.',
        ),
        'image-tools' => array(
            'label' => 'Image Tools', 'icon' => 'image', 'tone' => 'blue',
            'description' => 'Resize, convert, compress, inspect and prepare images in your browser. Keep the original file and check dimensions, transparency and visible quality after processing.',
        ),
        'pdf-tools' => array(
            'label' => 'PDF Tools', 'icon' => 'download', 'tone' => 'red',
            'description' => 'Organize, convert, compress and inspect PDF documents with focused online utilities. Open every exported file and verify page order, orientation, text and links.',
        ),
        'video-tools' => array(
            'label' => 'Video Tools', 'icon' => 'image', 'tone' => 'purple',
            'description' => 'Prepare video clips, frames, subtitles and technical settings with browser-based tools. Large media jobs depend on device memory, so review the complete export before publishing.',
        ),
        'audio-tools' => array(
            'label' => 'Audio Tools', 'icon' => 'pulse', 'tone' => 'teal',
            'description' => 'Inspect, edit and prepare audio files and recordings online. Listen to the finished result and check levels, timing, format and compatibility before sharing.',
        ),
        'text-tools' => array(
            'label' => 'Text Tools', 'icon' => 'word', 'tone' => 'orange',
            'description' => 'Count, clean, compare and reformat text for writing and publishing tasks. Read the full output so names, quotations and intentional spacing remain correct.',
        ),
        'developer-tools' => array(
            'label' => 'Developer Tools', 'icon' => 'code', 'tone' => 'purple',
            'description' => 'Format, validate, encode, decode and inspect development data with focused utilities. Test generated output outside production and never paste live secrets or private keys.',
        ),
        'website-tools' => array(
            'label' => 'Website Tools', 'icon' => 'globe', 'tone' => 'green',
            'description' => 'Run point-in-time checks for public webpages, domains, DNS, certificates, redirects and site resources. Confirm important findings with the relevant host or provider.',
        ),
        'business-tools' => array(
            'label' => 'Business Tools', 'icon' => 'bank', 'tone' => 'blue',
            'description' => 'Create working calculations and drafts for common business tasks. Verify totals, dates, currency, tax treatment and local requirements before issuing a final document.',
        ),
        'marketing-tools' => array(
            'label' => 'Marketing Tools', 'icon' => 'link', 'tone' => 'pink',
            'description' => 'Build, inspect and preview campaign links, content and performance calculations. Check tracking parameters, spelling, brand details and platform rules before launch.',
        ),
        'other-tools' => array(
            'label' => 'Other Tools', 'icon' => 'grid', 'tone' => 'slate',
            'description' => 'Practical online utilities for focused tasks that do not fit a single specialist category. Enter only the required information and verify the result before using it elsewhere.',
        ),
    );
}

/** Canonical, crawlable URL for a tool-category hub. */
function alltools_category_url( $slug ) {
    $slug = sanitize_key( $slug );
    if ( ! $slug ) return home_url( '/all-tools/' );
    $term = get_term_by( 'slug', $slug, 'alltool_category' );
    if ( $term && ! is_wp_error( $term ) ) {
        $url = get_term_link( $term );
        if ( ! is_wp_error( $url ) ) return $url;
    }
    return home_url( user_trailingslashit( 'tool-category/' . $slug ) );
}

/**
 * Count tools in a category.
 */
function alltools_count_in_cat( $cat_slug ) {
    $all = alltools_all();
    $count = 0;
    foreach ( $all as $tool ) {
        if ( isset( $tool['category'] ) && $tool['category'] === $cat_slug ) {
            $count++;
        }
    }
    return $count;
}

/**
 * Tools filtered by category.
 */
function alltools_in_cat( $cat_slug ) {
    $all = alltools_all();
    $out = array();
    foreach ( $all as $slug => $tool ) {
        if ( isset( $tool['category'] ) && $tool['category'] === $cat_slug ) {
            $out[ $slug ] = $tool;
        }
    }
    return $out;
}

/**
 * Related tools (same category).
 */
function alltools_related( $current_slug, $limit = 5 ) {
    $all     = alltools_all();
    $current = isset( $all[ $current_slug ] ) ? $all[ $current_slug ] : null;
    if ( ! $current ) {
        return array();
    }
    $out = array();
    foreach ( $all as $slug => $tool ) {
        if ( $slug === $current_slug ) {
            continue;
        }
        if ( isset( $tool['category'] ) && $tool['category'] === $current['category'] ) {
            $out[ $slug ] = $tool;
            if ( count( $out ) >= $limit ) {
                break;
            }
        }
    }
    return $out;
}

/**
 * Permalink for a tool.
 */
function alltools_tool_url( $slug ) {
    $slug = sanitize_key( $slug );
    static $permalinks = null;
    if ( null === $permalinks ) {
        $permalinks = array();
        $posts = get_posts( array(
            'post_type'      => 'alltool',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ) );
        foreach ( $posts as $post ) {
            $registered_slug = sanitize_key( get_post_meta( $post->ID, '_alltool_slug', true ) );
            if ( ! $registered_slug ) continue;
            if ( ! isset( $permalinks[ $registered_slug ] ) || $post->post_name === $registered_slug ) {
                $permalinks[ $registered_slug ] = get_permalink( $post );
            }
        }
    }
    if ( isset( $permalinks[ $slug ] ) ) return $permalinks[ $slug ];
    return home_url( '/?alltool=' . urlencode( $slug ) );
}

/**
 * Color palette for tool icons.
 */
function alltools_color( $name ) {
    $map = array(
        'green'  => array( 'bg' => '#D1FAE5', 'fg' => '#10B981' ),
        'purple' => array( 'bg' => '#EDE9FE', 'fg' => '#8B5CF6' ),
        'orange' => array( 'bg' => '#FED7AA', 'fg' => '#F97316' ),
        'blue'   => array( 'bg' => '#DBEAFE', 'fg' => '#2563EB' ),
        'pink'   => array( 'bg' => '#FCE7F3', 'fg' => '#EC4899' ),
        'red'    => array( 'bg' => '#FEE2E2', 'fg' => '#EF4444' ),
        'yellow' => array( 'bg' => '#FEF3C7', 'fg' => '#EAB308' ),
        'teal'   => array( 'bg' => '#CCFBF1', 'fg' => '#14B8A6' ),
        'dark'   => array( 'bg' => '#1F2937', 'fg' => '#F9FAFB' ),
    );
    return isset( $map[ $name ] ) ? $map[ $name ] : $map['blue'];
}

/**
 * Render an icon SVG by name.
 */
function alltools_icon_svg( $name, $size = 24 ) {
    $icons = array(
        'user'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        'percent'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>',
        'calculator'=> '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="8" y2="10"/><line x1="12" y1="10" x2="12" y2="10"/><line x1="16" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="8" y2="14"/><line x1="12" y1="14" x2="12" y2="14"/><line x1="16" y1="14" x2="16" y2="14"/><line x1="8" y1="18" x2="16" y2="18"/></svg>',
        'image'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg>',
        'resize'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>',
        'star'      => '<svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
        'word'      => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M5 4h14v3H5zM5 9h14v2H5zM5 13h10v2H5zM5 17h14v2H5z"/></svg>',
        'char'      => '<svg viewBox="0 0 24 24" fill="currentColor"><text x="12" y="18" font-size="18" font-weight="900" text-anchor="middle" font-family="system-ui">Aa</text></svg>',
        'case'      => '<svg viewBox="0 0 24 24" fill="currentColor"><text x="12" y="18" font-size="14" font-weight="900" text-anchor="middle" font-family="system-ui">Aa</text></svg>',
        'duplicate' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>',
        'spaces'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 6 4 18 6 18"/><line x1="20" y1="6" x2="20" y2="18"/><polyline points="18 18 20 18"/></svg>',
        'qr'        => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h7v7H3zM5 5v3h3V5zM14 3h7v7h-7zM16 5v3h3V5zM3 14h7v7H3zM5 16v3h3v-3zM14 14h2v2h-2zM18 14h3v2h-3zM14 17h3v2h-3zM19 19h2v2h-2zM14 20h2v1h-2z"/></svg>',
        'arrow'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
        'check'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
        'moon'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>',
        'sun'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>',
        'search'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
        'briefcase' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 7h-4V5a3 3 0 0 0-3-3h-2a3 3 0 0 0-3 3v2H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM10 5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2h-4z"/></svg>',
        'menu'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
        'close'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
        'chevron'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>',
        'download'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
        'upload'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>',
        'copy'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>',
        'trash'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>',
        'calendar'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
        'shield'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        'heart'     => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
        'zap'       => '<svg viewBox="0 0 24 24" fill="currentColor"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
        'grid'      => '<svg viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
        'link'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.72"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.72-1.72"/></svg>',
        'mail'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
        'phone'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
        'message'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
        'wifi'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>',
        'contact'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><circle cx="12" cy="10" r="3"/><path d="M7 17c1-2 3-3 5-3s4 1 5 3"/></svg>',
        'fb'        => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.378 14.192 5 15.115 5H18V0h-3.808C10.596 0 9 1.583 9 4.615V8z"/></svg>',
        'tw'        => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/></svg>',
        'in'        => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 2h-17A1.5 1.5 0 0 0 2 3.5v17A1.5 1.5 0 0 0 3.5 22h17a1.5 1.5 0 0 0 1.5-1.5v-17A1.5 1.5 0 0 0 20.5 2zM8 19H5v-9h3zM6.5 8.25A1.75 1.75 0 1 1 8.3 6.5a1.78 1.78 0 0 1-1.8 1.75zM19 19h-3v-4.74c0-1.42-.6-1.93-1.38-1.93A1.74 1.74 0 0 0 13 14.19a.66.66 0 0 0 0 .14V19h-3v-9h2.9v1.3a3.11 3.11 0 0 1 2.7-1.4c1.55 0 3.36.86 3.36 3.66z"/></svg>',
        'pulse'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
        'gauge'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 0 0-10 10 10 10 0 0 0 4 8h12a10 10 0 0 0 4-8 10 10 0 0 0-10-10z"/><path d="M12 14l4-4"/><circle cx="12" cy="14" r="1.5" fill="currentColor"/></svg>',
        'code'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
        'lock'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
        'globe'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
        'redirect'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 10 20 15 15 20"/><path d="M4 4v7a4 4 0 0 0 4 4h12"/></svg>',
        'scissors'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.48" x2="20" y2="20"/><line x1="8.12" y1="8.12" x2="12" y2="12"/></svg>',

    );
    $svg = isset( $icons[ $name ] ) ? $icons[ $name ] : $icons['grid'];
    return str_replace( '<svg ', '<svg width="' . intval( $size ) . '" height="' . intval( $size ) . '" ', $svg );
}

/**
 * Breadcrumb output.
 */
function alltools_breadcrumb() {
    $sep = '<span class="at-bc-sep">›</span>';
    echo '<nav class="at-breadcrumb" aria-label="Breadcrumb"><ol>';
    echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'alltools' ) . '</a></li>';
    if ( is_singular( 'alltool' ) ) {
        $slug = get_post_meta( get_the_ID(), '_alltool_slug', true );
        $tool = alltools_get_tool( $slug );
        if ( ! empty( $tool['category'] ) ) {
            $cat_url = alltools_category_url( $tool['category'] );
            echo $sep . '<li><a href="' . esc_url( $cat_url ) . '">' . esc_html( alltools_cat_label( $tool['category'] ) ) . '</a></li>';
        }
        $view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : '';
        if ( $view && $slug === 'website-speed-test' ) {
            echo $sep . '<li><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></li>';
            $view_labels = array(
                'full_report'   => 'Full Report',
                'opportunities' => 'Opportunities',
                'waterfall'     => 'Waterfall',
            );
            $vl = isset( $view_labels[ $view ] ) ? $view_labels[ $view ] : ucwords( str_replace( '_', ' ', $view ) );
            echo $sep . '<li aria-current="page">' . esc_html( $vl ) . '</li>';
        } else {
            echo $sep . '<li aria-current="page">' . esc_html( get_the_title() ) . '</li>';
        }
    } elseif ( is_page() ) {
        echo $sep . '<li aria-current="page">' . esc_html( get_the_title() ) . '</li>';
    } elseif ( is_search() ) {
        echo $sep . '<li aria-current="page">' . esc_html__( 'Search', 'alltools' ) . '</li>';
    } elseif ( is_404() ) {
        echo $sep . '<li aria-current="page">' . esc_html__( '404', 'alltools' ) . '</li>';
    }
    echo '</ol></nav>';
}

/** Compact title bar used on every non-home public template. */
function alltools_inner_title_bar( $title = '' ) {
    $title = trim( wp_strip_all_tags( (string) $title ) );
    if ( '' === $title ) {
        if ( is_search() ) $title = sprintf( __( 'Search: %s', 'alltools' ), get_search_query() );
        elseif ( is_404() ) $title = __( 'Page Not Found', 'alltools' );
        elseif ( is_archive() ) $title = get_the_archive_title();
        else $title = get_the_title();
    }
    $title = trim( wp_strip_all_tags( (string) $title ) );
    ?>
    <section class="ufx-inner-title-bar">
        <div class="at-container ufx-inner-title-bar-row">
            <a class="ufx-inner-back" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Back to homepage', 'alltools' ); ?>">
                <span aria-hidden="true">←</span> <?php esc_html_e( 'Back to Home', 'alltools' ); ?>
            </a>
            <h1><?php echo esc_html( $title ); ?></h1>
        </div>
    </section>
    <?php
}

/**
 * Site logo SVG.
 */
function alltools_logo_svg() {
    return '<svg viewBox="0 0 36 36" width="36" height="36" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="ufx-logo-g" x1="6" y1="4" x2="30" y2="32" gradientUnits="userSpaceOnUse">
                <stop offset="0" stop-color="#3B82F6"/>
                <stop offset="1" stop-color="#0B5CFF"/>
            </linearGradient>
        </defs>
        <rect x="3" y="3" width="30" height="30" rx="8" fill="url(#ufx-logo-g)"/>
        <path d="M11.4 11.2l13.4 13.4" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/>
        <path d="M24.6 11.2l-4.1 4.1m-5 5l-4.1 4.1" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/>
        <circle cx="18" cy="18" r="2.4" fill="#fff" opacity=".95"/>
    </svg>';
}

/**
 * Render the uploaded WordPress custom logo when available;
 * otherwise use the built-in Uptime Fixer mark.
 */
function alltools_logo_output() {
    if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) {
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        $alt = trim( get_post_meta( $custom_logo_id, '_wp_attachment_image_alt', true ) );
        if ( $alt === '' ) {
            $alt = get_bloginfo( 'name' );
        }
        return wp_get_attachment_image(
            $custom_logo_id,
            'full',
            false,
            array(
                'class'         => 'at-custom-logo custom-logo',
                'alt'           => $alt,
                'loading'       => 'eager',
                'decoding'      => 'async',
                'fetchpriority' => 'high',
            )
        );
    }
    $bundled_logo = ALLTOOLS_PATH . 'assets/images/uptime-fixer-logo.png';
    if ( file_exists( $bundled_logo ) ) {
        return '<img class="at-custom-logo at-bundled-logo" src="' . esc_url( ALLTOOLS_URI . 'assets/images/uptime-fixer-logo.png' ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" loading="eager" decoding="async" fetchpriority="high">';
    }
    return alltools_logo_svg();
}
