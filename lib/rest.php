<?php

// register rest routes
add_action('rest_api_init', function () {
    register_rest_route('hashpress_reviews/v1', '/get_transaction_history', array(
        'methods' => 'GET',
        'callback' => 'hashpress_reviews_get_transaction_history',
        'permission_callback' => 'hashpress_reviews_validate_nonce',
    ));
    register_rest_route('hashpress_reviews/v1', '/update_review_history', array(
        'methods' => 'POST',
        'callback' => 'hashpress_reviews_update_review_history',
        'permission_callback' => 'hashpress_reviews_validate_nonce',
    ));
});

function hashpress_reviews_validate_nonce(WP_REST_Request $request)
{
    $nonce = $request->get_header('X-WP-Nonce');
    if (wp_verify_nonce($nonce, 'wp_rest')) {
        return true;
    }
    return new WP_Error('rest_forbidden', __('Invalid nonce.'), ['status' => 403]);
}


function hashpress_reviews_get_transaction_history(WP_REST_Request $request)
{
    $id = $request->get_param('id');

    // Controleer of de post bestaat
    if (!get_post($id)) {
        return new WP_REST_Response(['error' => 'Invalid post ID'], 404);
    }

    // Controleer of de meta key bestaat
    if (!metadata_exists('post', $id, 'hashpress_transaction_history')) {
        return new WP_REST_Response(['error' => 'Meta key not found'], 404);
    }

    $data = get_post_meta($id, 'hashpress_transaction_history', true);

    if (!$data) {
        return new WP_REST_Response(['error' => 'Data not found'], 404);
    }

    return new WP_REST_Response($data, 200);
}

function hashpress_reviews_update_review_history(WP_REST_Request $request)
{
    $post_id = $request->get_param('postId');
    $review_id = $request->get_param('reviewId');

    if (!$post_id || !$review_id) {
        return new WP_Error('missing_data', 'post_id and review_id are required', ['status' => 400]);
    }

    update_review_history($post_id, $review_id);

    return rest_ensure_response(['success' => true]);
}
