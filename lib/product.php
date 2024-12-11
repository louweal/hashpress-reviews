<?php

/**
 * Template:			product.php
 * Description:			Hooks to change single product page
 */



// Add Above the Related Products section
add_action('woocommerce_after_single_product_summary', 'woocommerce_after_single_product_summary_hook', 15);

function woocommerce_after_single_product_summary_hook()
{
    echo do_shortcode('[realviews_latest_reviews]');
}
