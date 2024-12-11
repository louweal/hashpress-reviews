<?php
/*
Plugin Name: HashPress Reviews
Description: Integrate Hedera Smart Contracts into your WordPress website to get verifiable reviews.
Version: 0.1
Author: HashPress
Author URI: https://hashpresspioneers.com/
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}


require_once plugin_dir_path(__FILE__) . 'lib/enqueue.php';
require_once plugin_dir_path(__FILE__) . 'lib/helpers.php';
require_once plugin_dir_path(__FILE__) . 'lib/acf.php';
require_once plugin_dir_path(__FILE__) . 'lib/shortcodes/latest-reviews.php';
require_once plugin_dir_path(__FILE__) . 'lib/shortcodes/num-reviews.php';


// load these only if woocommerce is active
if (!class_exists('WooCommerce')) return;

require_once plugin_dir_path(__FILE__) . 'lib/product.php';


// todo add to pay

function enable_hashpress_core_from_reviews()
{
    $core = 'hashpress-core/main.php';

    if (in_array($core, apply_filters('active_plugins', get_option('active_plugins'))) == false) {
        activate_plugin($core);
    }
}
add_action('init', 'enable_hashpress_pay_from_reviews');

function enable_hashpress_pay_from_reviews()
{
    $pay = 'hashpress-pay/main.php';

    if (in_array($pay, apply_filters('active_plugins', get_option('active_plugins'))) == false) {
        activate_plugin($pay);
    }
}
add_action('init', 'enable_hashpress_pay_from_reviews');
