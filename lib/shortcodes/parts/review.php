<?php
if ($review_data) {
    $network = $review_data['network'];
    $review_data_string = $review_data['reviewData'];
    $review_data_array = json_decode($review_data_string, true);
    $buy_transaction_id = $review_data_array['transactionId'];

    $rating = htmlspecialchars($review_data_array['rating']);
    $name = htmlspecialchars($review_data_array['name']);
    $initial = strtoupper($name[0]);
    $message = htmlspecialchars($review_data_array['message']);

    list($account, $buy_timestamp) = explode('@', $buy_transaction_id);
    list($account, $review_timestamp) = explode('@', $review_transaction_id);

?>
    <div class="hashpress-reviews-review">
        <span class="hashpress-reviews-review__badge"></span>
        <div class="hashpress-reviews-review__header">
            <div class="hashpress-reviews-review__icon"><?php echo $initial; ?>
            </div>
            <div class="hashpress-reviews-review__user">
                <div class="hashpress-reviews-review__username"><?php echo $name; ?></div>
            </div>
        </div>
        <div class="hashpress-reviews-review__subheader">
            <div class="hashpress-reviews-review__stars">

                <?php
                for ($j = 1; $j <= 5; $j++) {
                    $star_class = $rating >= $j ? "is-solid" : null;
                ?>
                    <div class="hashpress-reviews-review__star <?php echo $star_class; ?>" xxxid="<?php echo $j; ?>"></div>
                <?php
                } //for
                ?>

            </div>
            <a class="hashpress-reviews-review__date1" href="https://<?php echo $network; ?>.mirrornode.hedera.com/api/v1/transactions/<?php echo parse_transaction_id($buy_transaction_id); ?>" target="_blank">
                <svg width="12" height="13" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#a)">
                        <path d="M4.063 2.844c0-.897.728-1.625 1.625-1.625.896 0 1.625.728 1.625 1.625v1.219h-3.25v-1.22Zm-1.22 1.219H1.22A1.22 1.22 0 0 0 0 5.28v5.282A2.438 2.438 0 0 0 2.438 13h6.5a2.438 2.438 0 0 0 2.437-2.438v-5.28a1.22 1.22 0 0 0-1.219-1.22H8.531V2.845A2.842 2.842 0 0 0 5.687 0a2.842 2.842 0 0 0-2.843 2.844v1.219Zm.61 1.218a.61.61 0 1 1 0 1.219.61.61 0 0 1 0-1.219Zm3.86.61a.61.61 0 1 1 1.218 0 .61.61 0 0 1-1.219 0Z" fill="#000" />
                    </g>
                    <defs>
                        <clipPath id="a">
                            <path fill="#fff" d="M0 0h11.375v13H0z" />
                        </clipPath>
                    </defs>
                </svg><time><?php echo convert_timestamp($buy_timestamp); ?></time>
            </a>
            <a class="hashpress-reviews-review__date2" target="_blank" href="https://<?php echo $network; ?>.mirrornode.hedera.com/api/v1/contracts/results/<?php echo parse_transaction_id($review_transaction_id); ?>">
                <svg width="13" height="13" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13 6.5c0 3.141-2.91 5.688-6.5 5.688a7.316 7.316 0 0 1-1.907-.25c-.474.241-1.564.703-3.397 1.003-.162.026-.286-.143-.222-.294.288-.679.548-1.584.626-2.41C.604 9.239 0 7.93 0 6.5 0 3.359 2.91.812 6.5.812S13 3.36 13 6.5Zm-8.938 0a.812.812 0 1 0-1.624 0 .812.812 0 0 0 1.624 0Zm3.25 0a.812.812 0 1 0-1.624 0 .812.812 0 0 0 1.625 0Zm2.438.813a.812.812 0 1 0 0-1.625.812.812 0 0 0 0 1.625Z" fill="#000" />
                </svg><time><?php echo convert_timestamp($review_timestamp); ?></time>
            </a>
        </div>
        <div class="hashpress-reviews-review__body">
            <p><?php echo $message; ?></p>
        </div>
    </div>

<?php
} else {
?>
    <div class="hashpress-reviews-review">
        <div class="hashpress-reviews-review__body">
            <p>Your review is stored on the Hedera Network. It may take up to 2 minutes until it can be retrieved from the Mirrornode API.</p>
        </div>
    </div>
<?php
}
?>