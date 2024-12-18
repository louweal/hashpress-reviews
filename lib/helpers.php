<?php

if (!function_exists('convert_timestamp')) {
    function convert_timestamp($timestamp)
    {
        list($seconds, $milliseconds) = explode('.', $timestamp);

        return date('F Y', $seconds);
    }
}

function update_review_history($post_id, $transaction_id)
{
    $current_review_history = get_post_meta($post_id, "hashpress_review_history", true);

    if (empty($transaction_id)) return;

    if (empty($current_review_history) || !is_array($current_review_history)) {
        $current_review_history = array();
    }

    $current_review_history[] = $transaction_id;
    update_post_meta($post_id, "hashpress_review_history", $current_review_history);
}

function decode_hex_string($hexData)
{
    if (substr($hexData, 0, 2) === "0x") {
        $hexData = substr($hexData, 2);
    }

    $offsetHex = substr($hexData, 0, 64);
    $offset = hexdec($offsetHex);
    $lengthHexStart = ($offset * 2);
    $lengthHex = substr($hexData, $lengthHexStart, 64);
    $length = hexdec($lengthHex);
    $dataHexStart = $lengthHexStart + 64;
    $dataHex = substr($hexData, $dataHexStart, $length * 2);

    return rtrim(hex2bin($dataHex), "\0");
}

function parse_transaction_id($transactionId)
{
    // Split the transaction ID into account and timestamp parts
    $splitId = explode("@", $transactionId);
    $accountId = $splitId[0];
    $timestamp = str_replace(".", "-", $splitId[1]);
    return $accountId . "-" . $timestamp;
}

function fetch_mirrornode_log_data($transactionId)
{
    $networks = ["testnet", "mainnet", "previewnet"];
    $baseUrl = "https://%s.mirrornode.hedera.com/api/v1/contracts/results/%s";

    // Loop over all networks
    foreach ($networks as $network) {
        // Construct the URL
        $url = sprintf($baseUrl, $network, parse_transaction_id($transactionId));

        try {
            // Fetch data from the MirrorNode API
            $response = file_get_contents($url); // You could also use cURL if needed
            if ($response === FALSE) {
                throw new Exception("Error fetching data from the MirrorNode API");
            }

            // Decode the JSON response
            $data = json_decode($response, true);

            // Check for 'logs' in the data
            if (isset($data["logs"])) {
                foreach ($data["logs"] as $log) {
                    // Check for 'data' in each log
                    if (isset($log["data"])) {
                        $hexData = $log["data"];
                        // Decode the hex string
                        $decodedData = decode_hex_string($hexData);
                        return ['network' => $network, 'raw' => $hexData, 'reviewData' => $decodedData]; // Return the first valid log
                    }
                }
            }
        } catch (Exception $e) {
            // Log the error and continue to the next network
            error_log($e->getMessage());
            return null;
        }
    }

    return null; // Return null if no data was found in any network
}


function hashpress_reviews_section_function($atts, $shortcode)
{
    global $post;
    $post_id = $post->ID;

    if (function_exists('is_product')) {
        if (is_product()) {
            global $product;
            $post_id = $product->get_id();
        }
    }

    $review_file =  __DIR__ . '/shortcodes/parts/review.php';

    // global settings
    if ($shortcode) {
        // Define the default attributes
        $atts = shortcode_atts(
            array(
                'max_reviews' =>  4,
                'button_text' => "All reviews",
            ),
            $atts,
            'hashpress_reviews_latest_reviews'
        );

        $max_reviews = intval($atts['max_reviews']);
        $button_text = esc_html($atts['button_text']);
    } else {
        // get data from gutenberg fields
        $max_reviews = get_field("max_reviews");
        $max_reviews = intval($max_reviews);
        $button_text = get_field("button_text");
    }

    if ($max_reviews == -1) {
        $max_reviews = 1e8;
    }

    // $transaction_ids = get_post_meta($post_id, 'hashpress_transaction_history', true);
    $review_history = get_post_meta($post_id, 'hashpress_review_history', true);

    ob_start();

    if (!is_admin()) {
        if ($max_reviews != 0) {
?>
            <section class="hashpress-reviews-section" data-id="<?php echo $post_id; ?>" id="reviews">
                <?php $num_reviews = $review_history ? count($review_history) : 0;
                ?>

                <h2>Reviews (<?php echo $num_reviews; ?>)</h2>

                <?php echo do_shortcode('[hashpress_connect]');
                ?>

                <div class="hashpress-reviews-list">

                    <?php if ($num_reviews > 0) { ?>
                        <?php for ($i = ($num_reviews - 1); $i >= ($num_reviews - min($num_reviews, $max_reviews)); $i--) {
                            $review_transaction_id = $review_history[$i];
                            $review_data = fetch_mirrornode_log_data($review_transaction_id); // used in the review_file
                        ?>
                            <?php
                            if (file_exists($review_file)) {
                                require $review_file;
                            } else {
                                echo "File not found: $review_file";
                            }
                            ?>
                        <?php } //foreach
                        ?>
                    <?php } else { ?>
                        <p>No reviews yet.</p>
                    <?php } ?>
                </div>


                <div class="hashpress-reviews-actions">
                    <?php if ($num_reviews > $max_reviews) { ?>
                        <div class="btn hashpress-reviews-toggle-modal"><?php echo $button_text; ?></div>

                        <div class="hashpress-reviews-modal">
                            <div class="hashpress-reviews-modal__bg"></div>

                            <div class="hashpress-reviews-modal__inner">
                                <div class="hashpress-reviews-modal__header">
                                    <button class="hashpress-reviews-modal__close">
                                        <svg width="15" height="20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M13.383 5.883a1.252 1.252 0 0 0-1.77-1.77L7.5 8.23 3.383 4.117a1.252 1.252 0 0 0-1.77 1.77L5.73 10l-4.113 4.117a1.252 1.252 0 0 0 1.77 1.77L7.5 11.77l4.117 4.113a1.252 1.252 0 0 0 1.77-1.77L9.27 10l4.113-4.117Z" fill="#000" />
                                        </svg>
                                    </button>

                                    <h2>Reviews (<?php echo $num_reviews; ?>)</h2>
                                </div>

                                <div class="hashpress-reviews-modal__body">
                                    <div class="hashpress-reviews-list">
                                        <?php for ($i = ($num_reviews - 1); $i >= 0; $i--) {
                                            $review_transaction_id = $review_history[$i];
                                            $review_data = fetch_mirrornode_log_data($review_transaction_id); // used in the review_file

                                            if (file_exists($review_file)) {
                                                require $review_file;
                                            } else {
                                                echo "File not found: $review_file";
                                            }
                                        } ?>
                                    </div>
                                </div>
                            </div>
                        </div><!-- modal -->
                    <?php }; //if
                    ?>

                    <div class="hashpress-reviews-write-review-wrapper">
                        <div class="btn hashpress-reviews-toggle-modal hashpress-reviews-write-review"><?php _e('Write review', 'hashpress'); ?></div>

                        <div class="hashpress-reviews-modal">
                            <div class="hashpress-reviews-modal__bg"></div>
                            <div class="hashpress-reviews-modal__inner">
                                <div class="hashpress-reviews-modal__header">
                                    <button class="hashpress-reviews-modal__close">
                                        <svg width="15" height="20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M13.383 5.883a1.252 1.252 0 0 0-1.77-1.77L7.5 8.23 3.383 4.117a1.252 1.252 0 0 0-1.77 1.77L5.73 10l-4.113 4.117a1.252 1.252 0 0 0 1.77 1.77L7.5 11.77l4.117 4.113a1.252 1.252 0 0 0 1.77-1.77L9.27 10l4.113-4.117Z" fill="#000" />
                                        </svg>
                                    </button>

                                    <h2>Write review</h2>
                                    <p>Note: It may take up to 2 minutes until your review can be displayed on the website.</p>

                                </div>

                                <div class="hashpress-reviews-modal__body">
                                    <form id="write-review" class="hashpress-reviews-modal__form">
                                        <div class="hashpress-reviews-rating" id="rating-wrapper">
                                            <span>Rating:</span>
                                            <div class="hashpress-reviews-stars">
                                                <?php for ($i = 5; $i >= 1; $i--) { ?>
                                                    <div class="hashpress-reviews-stars__star" id="<?php echo $i; ?>"></div>
                                                <?php } ?>
                                            </div>
                                            <span class="hashpress-reviews-rating__display"><span class="selected-rating">0</span>/5</span>
                                        </div>

                                        <input type="text" id="name" name="name" placeholder="Name">
                                        <textarea name="message" id="message" placeholder="Message"></textarea>
                                        <button type="submit" class="btn hashpress-reviews-submit-review">Submit</button>
                                        <div class="write-review-notices"></div>
                                    </form>
                                </div>
                            </div>
                        </div><!-- modal -->
                    </div><!-- wrapper-->
                </div><!-- hashpress-reviews-actions -->
            </section>
<?php
        }
    }
    $output = ob_get_clean();
    return $output;
}
