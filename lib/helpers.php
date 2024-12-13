<?php

/**
 * Template:			helpers.php
 * Description:			Custom functions used by the plugin
 */


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
                'max_reviews' =>  6,
                'button_text' => "Write review",
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

    $transaction_ids = get_post_meta($post_id, 'hashpress_transaction_history', true);
    $review_history = get_post_meta($post_id, 'hashpress_review_history', true);

    ob_start();

    if (!is_admin()) {
        if ($max_reviews != 0) {
?>
            <section class="hashpress-reviews-section" data-id="<?php echo $post_id; ?>">
                <?php $num_reviews = $review_history ? count($review_history) : 0;
                ?>

                <h2>Reviews (<?php echo $num_reviews; ?>)</h2>

                <!-- <p style="border:1px solid red; padding: 1rem; margin-top: 1rem; font-style:italic; font-size:14px">Test instructions: Buy this item with HederaPay and 'use' it for the minimum required (demo) timespan of 2 minutes. Then reconnect to this website with the same wallet you bought the item with and a 'Write review' button appears!</p> -->


                <div class="hashpress-reviews-list">

                    <?php if ($num_reviews > 0) { ?>
                        <?php for ($i = ($num_reviews - 1); $i >= ($num_reviews - min($num_reviews, $max_reviews)); $i--) {
                            $review_transaction_id = $review_history[$i];
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
                        <div class="btn show-hashpress-reviews-modal"><?php echo $button_text; ?></div>

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
                                            // <?php for ($i = 0; $i < $num_reviews; $i++) {
                                            $review_transaction_id = $review_history[$i];
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

                    <div class="hashpress-reviews-write-review-wrapper" data-post-id="<?php echo $post_id; ?>">

                        <div class="btn hashpress-reviews-write-review">Write review</div>

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
