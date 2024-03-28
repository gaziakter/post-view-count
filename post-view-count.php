<?php
/*
 * Plugin Name:       Post View Count
 * Plugin URI:        https://gaziakter.com/plugins/post-view-count/
 * Description:       Handle the basics with this plugin.
 * Version:           1.10.3
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Gazi Akter
 * Author URI:        https://gaziakter.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       view-count
 * Domain Path:       /languages
 */

 if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Post_View_Count {

    public function __construct() {
        add_action( 'init', array($this, 'view_count_load_textdomain') );
        add_action('wp', array($this, 'track_post_views'));
        add_filter('manage_posts_columns', array($this, 'add_view_count_column'));
        add_action('manage_posts_custom_column', array($this, 'display_view_count_column'), 10, 2);
        add_filter('manage_edit-post_sortable_columns', array($this, 'make_view_count_column_sortable'));
        add_action('pre_get_posts', array($this, 'sort_posts_by_view_count'));
        add_shortcode('post_view_count', array($this, 'view_count_shortcode'));
        add_action( 'wp_enqueue_scripts', array($this, 'view_count_wp_enqueue_style') );

    }


    // Load textdomain
    public function view_count_load_textdomain() {
        load_theme_textdomain( 'view-count', plugin_dir_path( __FILE__ ) . '/languages' );
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
        $output .= '<strong>Post Views </strong> <br>';
        $output .= 'Total Views: <strong>' . ($view_count ? $view_count : '0') . '</strong>';
        $output .= '</span>';

        return $output;
    }

    function view_count_wp_enqueue_style(){
        wp_enqueue_style( 'style', plugin_dir_url( __FILE__ ) . "assets/css/style.css", null, time() );

    }
}

new Post_View_Count();