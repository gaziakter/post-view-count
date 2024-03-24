<?php
/*
Plugin Name: Post View Count
Description: Track and display post view count.
Version: 1.0
Author: Your Name
*/

// Function to increment view count
function pvc_increment_view_count($post_id) {
    $current_views = get_post_meta($post_id, 'post_view_count', true);
    $new_views = intval($current_views) + 1;
    update_post_meta($post_id, 'post_view_count', $new_views);
}

// Hook into WordPress to increment view count when post is viewed
function pvc_track_post_views() {
    if (is_single()) {
        global $post;
        if ($post) {
            pvc_increment_view_count($post->ID);
        }
    }
}
add_action('wp', 'pvc_track_post_views');

// Function to add view count column to admin post list
function pvc_add_view_count_column($columns) {
    $columns['post_view_count'] = 'View Count';
    return $columns;
}
add_filter('manage_posts_columns', 'pvc_add_view_count_column');

// Function to display view count in admin post list
function pvc_display_view_count_column($column, $post_id) {
    if ($column === 'post_view_count') {
        $view_count = get_post_meta($post_id, 'post_view_count', true);
        echo $view_count ? $view_count : '0';
    }
}
add_action('manage_posts_custom_column', 'pvc_display_view_count_column', 10, 2);

// Make the view count column sortable
function pvc_make_view_count_column_sortable($columns) {
    $columns['post_view_count'] = 'post_view_count';
    return $columns;
}
add_filter('manage_edit-post_sortable_columns', 'pvc_make_view_count_column_sortable');

// Function to handle sorting by view count
function pvc_sort_posts_by_view_count($query) {
    if (!is_admin()) {
        return;
    }

    $orderby = $query->get('orderby');

    if ($orderby === 'post_view_count') {
        $query->set('meta_key', 'post_view_count');
        $query->set('orderby', 'meta_value_num');
    }
}
add_action('pre_get_posts', 'pvc_sort_posts_by_view_count');

// Shortcode to display view count
function pvc_view_count_shortcode($atts) {
    $atts = shortcode_atts(array(
        'post_id' => get_the_ID(),
    ), $atts);

    $view_count = get_post_meta($atts['post_id'], 'post_view_count', true);
    return $view_count ? $view_count : '0';
}
add_shortcode('post_view_count', 'pvc_view_count_shortcode');
?>
