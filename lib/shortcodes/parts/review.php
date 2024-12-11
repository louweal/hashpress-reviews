<?php
?>
<div class="hashpress-reviews-review is-loading" id="<?php echo $review_transaction_id; ?>">
    <span class="hashpress-reviews-review__badge"></span>
    <div class="hashpress-reviews-review__header">
        <div class="hashpress-reviews-review__icon">N
        </div>
        <div class="hashpress-reviews-review__user">
            <div class="hashpress-reviews-review__username">Name</div>
        </div>
    </div>
    <div class="hashpress-reviews-review__subheader">
        <div class="hashpress-reviews-review__stars">
            <?php
            for ($j = 1; $j <= 5; $j++) {
            ?>
                <div class="hashpress-reviews-review__star is-solid" id="<?php echo $j; ?>"></div>
            <?php
            } //for
            ?>

        </div>
        <a class="hashpress-reviews-review__date1" target="_blank">
            <svg width="12" height="13" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#a)">
                    <path d="M4.063 2.844c0-.897.728-1.625 1.625-1.625.896 0 1.625.728 1.625 1.625v1.219h-3.25v-1.22Zm-1.22 1.219H1.22A1.22 1.22 0 0 0 0 5.28v5.282A2.438 2.438 0 0 0 2.438 13h6.5a2.438 2.438 0 0 0 2.437-2.438v-5.28a1.22 1.22 0 0 0-1.219-1.22H8.531V2.845A2.842 2.842 0 0 0 5.687 0a2.842 2.842 0 0 0-2.843 2.844v1.219Zm.61 1.218a.61.61 0 1 1 0 1.219.61.61 0 0 1 0-1.219Zm3.86.61a.61.61 0 1 1 1.218 0 .61.61 0 0 1-1.219 0Z" fill="#000" />
                </g>
                <defs>
                    <clipPath id="a">
                        <path fill="#fff" d="M0 0h11.375v13H0z" />
                    </clipPath>
                </defs>
            </svg><time></time>
        </a>
        <a class="hashpress-reviews-review__date2" target="_blank">
            <svg width="13" height="13" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#a)">
                    <path d="M6.5 11.375c3.59 0 6.5-2.364 6.5-5.281C13 3.176 10.09.812 6.5.812S0 3.176 0 6.095c0 1.145.45 2.204 1.211 3.07-.048.622-.29 1.175-.543 1.597a4.585 4.585 0 0 1-.51.693c-.016.015-.026.028-.034.035l-.007.008a.406.406 0 0 0 .29.693c.728 0 1.462-.226 2.071-.49a7.608 7.608 0 0 0 1.379-.777 7.774 7.774 0 0 0 2.643.454v-.002ZM3.25 5.281a.812.812 0 1 1 0 1.625.812.812 0 0 1 0-1.625Zm3.25 0a.812.812 0 1 1 0 1.625.812.812 0 0 1 0-1.625Zm2.437.813a.812.812 0 1 1 1.625 0 .812.812 0 0 1-1.625 0Z" fill="#000" />
                </g>
                <defs>
                    <clipPath id="a">
                        <path fill="#fff" d="M0 0h13v13H0z" />
                    </clipPath>
                </defs>
            </svg><time></time>
        </a>
    </div>
    <div class="hashpress-reviews-review__body">
        <p>This is a review</p>
    </div>
</div>