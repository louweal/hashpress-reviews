<?php
add_shortcode('hashpress_reviews_section', 'hashpress_reviews_section_wrapper_function', 5);
function hashpress_reviews_section_wrapper_function($atts)
{
    $shortcode = true;
    $output = hashpress_reviews_section_function($atts, $shortcode);
    return $output;
}
