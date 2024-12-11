<?php

add_action('acf/init', 'realviews_latest_reviews_block');
function realviews_latest_reviews_block()
{
    // Check function exists.
    if (function_exists('acf_register_block_type')) {
        // Register the realviews review list block.
        acf_register_block_type(array(
            'name'              => 'realviews-latest-reviews',
            'title'             => __('Latest Review List (Realviews)', 'hfh'),
            'description'       => __('Show all reviews written for this product, post or page (Realviews)', 'hfh'),
            'render_template'   => dirname(plugin_dir_path(__FILE__)) . '/realviews/blocks/realviews-latest-reviews.php',
            'mode'              => 'edit',
            'category'          => 'common',
            'icon'              => 'admin-comments',
            'keywords'          => array('reviews', 'review', 'list', 'realviews', 'latest'),
        ));
    }
}

// must be hooked from main ?!
add_action('acf/init', 'add_latest_reviews_field_groups');
function add_latest_reviews_field_groups()
{
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group(array(
            'key' => 'group_realviews_latest_reviews', // Unique key for the field group
            'title' => 'Latest Reviews (Realviews)',
            'fields' => array(
                array(
                    'key' => 'max_reviews',
                    'label' => 'Number of reviews',
                    'name' => 'max_reviews',
                    'type' => 'number',
                    'instructions' => 'Set to -1 to show all reviews',
                    'required' => 0,
                    'default_value' => 2,
                ),
                array(
                    'key' => 'button_text',
                    'label' => 'Button text',
                    'name' => 'button_text',
                    'type' => 'text',
                    'required' => 0,
                    'default_value' => 'All reviews',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'block',
                        'operator' => '==',
                        'value' => 'acf/realviews-latest-reviews',
                    ),
                ),
            ),
        ));
    }
}

add_action('acf/init', 'add_hashpress_reviews_field_groups', 11);
function add_hashpress_reviews_field_groups()
{
    if (function_exists('acf_add_local_field_group')) {
        if (!acf_get_local_field_group('group_hashpress_pay_store')) {
            acf_add_local_field_group(array(
                'key' => 'group_hashpress_pay_store', // Unique key for the field group
                'title' => 'Store field',
                'fields' => array(
                    array(
                        'key' => 'field_store',
                        'label' => 'Store transactions',
                        'name' => 'field_store',
                        'type' => 'true_false',
                        'instructions' => 'Store transaction IDs in page metadata for reviewing.',
                        'ui' => 1,
                        'required' => 0,
                        'default_value' => 0,
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/hashpress-pay-block',
                        ),
                    ),
                ),
            ));
        }
    }
}