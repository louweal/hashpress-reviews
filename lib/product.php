<?php

// Add Above the Related Products section
add_action('woocommerce_after_single_product_summary', 'woocommerce_after_single_product_summary_hook', 15);

function woocommerce_after_single_product_summary_hook()
{
    echo do_shortcode('[hashpress_reviews_section]');
}
