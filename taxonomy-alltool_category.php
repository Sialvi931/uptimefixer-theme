<?php
/** Indexable tool-category hub. */
if ( ! defined( 'ABSPATH' ) ) exit;

$term = get_queried_object();
$directory_category = $term instanceof WP_Term ? $term->slug : '';
$categories = alltools_tool_categories();
$category = isset( $categories[ $directory_category ] ) ? $categories[ $directory_category ] : array();
$directory_title = ! empty( $category['label'] ) ? $category['label'] : single_term_title( '', false );
$directory_kicker = __( 'Tool category', 'alltools' );
$directory_description = ! empty( $category['description'] )
    ? $category['description']
    : trim( wp_strip_all_tags( term_description( $term ) ) );

require locate_template( 'template-parts/tool-directory.php' );

