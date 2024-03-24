<?php
/*
Plugin Name: Post View Count
Description: Track and display post view count.
Version: 1.0
Author: Your Name
*/

class Post_View_Count {

    public function __construct() {
        add_action('wp', array($this, 'track_post_views'));
        add_filter('manage_posts_columns', array($this, 'add_view_count_column'));
        add_action('manage_posts_custom_column', array($this, 'display_view_count_column'), 10, 2);
        add_filter('manage_edit-post_sortable_columns', array($this, 'make_view_count_column_sortable'));
        add_action('pre_get_posts', array($this, 'sort_posts_by_view_count'));
        add_shortcode('post_view_count', array($this, 'view_count_shortcode'));
    }

    // Function to increment view count
    public function increment_view_count($post_id) {
        $current_views = get_post_meta($post_id, 'post_view_count', true);
        $new_views = intval($current_views) + 1;
        update_post_meta($post_id, 'post_view_count', $new_views);
    }

    // Hook into WordPress to increment view count when post is viewed
    public function track_post_views() {
        if (is_single()) {
            global $post;
            if ($post) {
                $this->increment_view_count($post->ID);
            }
        }
    }

    // Function to add view count column to admin post list
    public function add_view_count_column($columns) {
        $columns['post_view_count'] = 'View Count';
        return $columns;
    }

    // Function to display view count in admin post list
    public function display_view_count_column($column, $post_id) {
        if ($column === 'post_view_count') {
            $view_count = get_post_meta($post_id, 'post_view_count', true);
            echo $view_count ? $view_count : '0';
        }
    }

    // Make the view count column sortable
    public function make_view_count_column_sortable($columns) {
        $columns['post_view_count'] = 'post_view_count';
        return $columns;
    }

    // Function to handle sorting by view count
    public function sort_posts_by_view_count($query) {
        if (!is_admin()) {
            return;
        }

        $orderby = $query->get('orderby');

        if ($orderby === 'post_view_count') {
            $query->set('meta_key', 'post_view_count');
            $query->set('orderby', 'meta_value_num');
        }
    }


    // Shortcode to display view count with style
    public function view_count_shortcode($atts) {
        $atts = shortcode_atts(array(
            'post_id' => get_the_ID(),
        ), $atts);

        $view_count = get_post_meta($atts['post_id'], 'post_view_count', true);
        
        // Apply style to the output
        $output = '<span class="post-view-count">';
        $output .= 'View Count: <strong>' . ($view_count ? $view_count : '0') . '</strong>';
        $output .= '</span>';

        return $output;
    }
}

new Post_View_Count();