<?php

/**
 * Template:       		enqueue.php
 * Description:    		Add CSS and Javascript to the page
 */

add_action('wp_enqueue_scripts', 'enqueue_hashpress_reviews_script', 13);
function enqueue_hashpress_reviews_script()
{
    global $post;
    $post_id = (is_singular() && $post instanceof WP_Post) ? $post->ID : -1;

    // Enqueue the script
    $path = plugin_dir_url(dirname(__FILE__, 1));

    wp_enqueue_script('hashpress-reviews-main-script', $path .  'dist/main.bundle.js', array(), null, array(
        'strategy'  => 'defer', 'in_footer' => false
    ));

    wp_localize_script('hashpress-reviews-main-script', 'hashpressReviewsAPI', array(
        'nonce' => wp_create_nonce('wp_rest'),   // Nonce for security
        'getTransactionHistoryUrl' => rest_url('hashpress_reviews/v1/get_transaction_history'),
        'updateReviewHistoryUrl' => rest_url('hashpress_reviews/v1/update_review_history'),
        'postId' => $post_id,
    ));

    wp_enqueue_script('hashpress-reviews-vendor-script', $path .  'dist/vendors.bundle.js', array(), null, array(
        'strategy'  => 'defer', 'in_footer' => false
    ));
}



add_action('wp_enqueue_scripts', 'enqueue_hashpress_reviews_styles', 5);
function enqueue_hashpress_reviews_styles()
{
    $path = plugin_dir_url(dirname(__FILE__, 1));

    wp_enqueue_style(
        'hashpress-reviews-styles',
        $path . 'src/css/hashpress-reviews.css',
        array(),
        null,
        'all'
    );
}
