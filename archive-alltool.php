<?php
/** Canonical, paginated directory for every published tool. */
if ( ! defined( 'ABSPATH' ) ) exit;

$directory_title = __( 'All Online Tools', 'alltools' );
$directory_kicker = __( 'Complete directory', 'alltools' );
$directory_description = __( 'Browse the complete Uptime Fixer collection. Every card links directly to a focused tool page with its interface, usage guidance, limitations and related utilities.', 'alltools' );
$directory_category = '';

require locate_template( 'template-parts/tool-directory.php' );

