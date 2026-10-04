<?php
/**
 * Custom Post Type for tools.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'alltools_register_cpt' );
function alltools_register_cpt() {
    register_post_type( 'alltool', array(
        'labels' => array(
            'name'          => __( 'Tools', 'alltools' ),
            'singular_name' => __( 'Tool', 'alltools' ),
            'add_new'       => __( 'Add New Tool', 'alltools' ),
            'add_new_item'  => __( 'Add New Tool', 'alltools' ),
            'edit_item'     => __( 'Edit Tool', 'alltools' ),
            'new_item'      => __( 'New Tool', 'alltools' ),
            'view_item'     => __( 'View Tool', 'alltools' ),
            'search_items'  => __( 'Search Tools', 'alltools' ),
            'not_found'     => __( 'No tools found', 'alltools' ),
            'menu_name'     => __( 'Uptime Fixer', 'alltools' ),
        ),
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,
        'menu_icon'           => 'dashicons-admin-tools',
        'menu_position'       => 25,
        'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'rewrite'             => array( 'slug' => 'tools', 'with_front' => false ),
        'has_archive'         => 'all-tools',
        'capability_type'     => 'post',
    ) );

    register_taxonomy( 'alltool_category', array( 'alltool' ), array(
        'labels' => array(
            'name'          => __( 'Tool Categories', 'alltools' ),
            'singular_name' => __( 'Tool Category', 'alltools' ),
            'search_items'  => __( 'Search Tool Categories', 'alltools' ),
            'all_items'     => __( 'All Tool Categories', 'alltools' ),
            'edit_item'     => __( 'Edit Tool Category', 'alltools' ),
            'update_item'   => __( 'Update Tool Category', 'alltools' ),
            'add_new_item'  => __( 'Add New Tool Category', 'alltools' ),
            'menu_name'     => __( 'Tool Categories', 'alltools' ),
        ),
        'public'            => true,
        'publicly_queryable'=> true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'tool-category', 'with_front' => false ),
    ) );
}

/** Keep the public directory useful without producing one enormous page. */
add_action( 'pre_get_posts', 'alltools_directory_query_size' );
function alltools_directory_query_size( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) return;
    if ( $query->is_post_type_archive( 'alltool' ) || $query->is_tax( 'alltool_category' ) ) {
        $query->set( 'posts_per_page', 48 );
        $query->set( 'orderby', 'title' );
        $query->set( 'order', 'ASC' );
        if ( function_exists( 'alltools_noncanonical_tool_post_ids' ) ) {
            $query->set( 'post__not_in', array_values( array_unique( array_merge(
                (array) $query->get( 'post__not_in' ),
                alltools_noncanonical_tool_post_ids()
            ) ) ) );
        }
    }
}

/** Assign every registered tool to its clean, crawlable category hub. */
function alltools_sync_tool_category( $post_id ) {
    $post_id = (int) $post_id;
    if ( ! $post_id || 'alltool' !== get_post_type( $post_id ) ) return;
    $slug = sanitize_key( get_post_meta( $post_id, '_alltool_slug', true ) );
    $tool = $slug ? alltools_get_tool( $slug ) : array();
    $category = ! empty( $tool['category'] ) ? sanitize_key( $tool['category'] ) : '';
    if ( $category ) wp_set_object_terms( $post_id, array( $category ), 'alltool_category', false );
}

add_action( 'save_post_alltool', 'alltools_sync_tool_category', 40 );

/** Create category terms and sync existing tools once after this upgrade. */
add_action( 'admin_init', 'alltools_sync_tool_category_hubs', 70 );
function alltools_sync_tool_category_hubs() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    if ( '1' === get_option( 'alltools_category_hubs_v400' ) ) return;

    foreach ( alltools_tool_categories() as $slug => $category ) {
        $term = get_term_by( 'slug', $slug, 'alltool_category' );
        if ( ! $term ) {
            wp_insert_term( $category['label'], 'alltool_category', array(
                'slug'        => $slug,
                'description' => $category['description'],
            ) );
        } elseif ( '' === trim( (string) $term->description ) ) {
            wp_update_term( $term->term_id, 'alltool_category', array( 'description' => $category['description'] ) );
        }
    }

    $ids = get_posts( array(
        'post_type'      => 'alltool',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ) );
    foreach ( $ids as $post_id ) alltools_sync_tool_category( $post_id );

    flush_rewrite_rules( false );
    if ( class_exists( 'WPSEO_Sitemaps_Cache' ) && is_callable( array( 'WPSEO_Sitemaps_Cache', 'clear' ) ) ) {
        WPSEO_Sitemaps_Cache::clear();
    }
    update_option( 'alltools_category_hubs_v400', '1', false );
}

/**
 * Tool slug meta box.
 */
add_action( 'add_meta_boxes', 'alltools_add_meta_box' );
function alltools_add_meta_box() {
    add_meta_box(
        'alltools_slug_box',
        __( 'Tool Configuration', 'alltools' ),
        'alltools_render_meta_box',
        'alltool',
        'side',
        'high'
    );
}

function alltools_render_meta_box( $post ) {
    wp_nonce_field( 'alltools_meta_save', 'alltools_meta_nonce' );
    $current_slug = get_post_meta( $post->ID, '_alltool_slug', true );
    $all          = alltools_all();
    ?>
    <p>
        <label for="alltools_slug"><strong><?php esc_html_e( 'Tool Slug', 'alltools' ); ?></strong></label><br>
        <select id="alltools_slug" name="alltools_slug" style="width:100%;">
            <option value=""><?php esc_html_e( '— Select a tool —', 'alltools' ); ?></option>
            <?php foreach ( $all as $slug => $t ) : ?>
                <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current_slug, $slug ); ?>>
                    <?php echo esc_html( $t['title'] ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <small><?php esc_html_e( 'Connects this post to a tool definition.', 'alltools' ); ?></small>
    </p>
    <?php
}

add_action( 'save_post_alltool', 'alltools_save_meta', 10, 2 );
function alltools_save_meta( $post_id, $post ) {
    if ( ! isset( $_POST['alltools_meta_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( sanitize_key( $_POST['alltools_meta_nonce'] ), 'alltools_meta_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( isset( $_POST['alltools_slug'] ) ) {
        update_post_meta( $post_id, '_alltool_slug', sanitize_key( wp_unslash( $_POST['alltools_slug'] ) ) );
    }
}

/**
 * Admin columns.
 */
add_filter( 'manage_alltool_posts_columns', 'alltools_admin_columns' );
function alltools_admin_columns( $cols ) {
    $new = array();
    foreach ( $cols as $k => $v ) {
        $new[ $k ] = $v;
        if ( $k === 'title' ) {
            $new['tool_slug'] = __( 'Tool', 'alltools' );
        }
    }
    return $new;
}

add_action( 'manage_alltool_posts_custom_column', 'alltools_admin_column_value', 10, 2 );
function alltools_admin_column_value( $col, $post_id ) {
    if ( $col === 'tool_slug' ) {
        $s = get_post_meta( $post_id, '_alltool_slug', true );
        echo esc_html( $s ? $s : '—' );
    }
}
