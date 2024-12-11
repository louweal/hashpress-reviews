<?php
function hashpress_reviews_get_transaction_ids(WP_REST_Request $request)
{
    $id = $request->get_param('id');

    // Retrieve data from the transient using the unique ID
    $data = get_transient("hashpress_pay_{$id}");

    if (!$data) {
        return new WP_REST_Response(['error' => 'Data not found'], 404);
    }

    return new WP_REST_Response($data, 200);
}
