(function () {
    "use strict";

    console.log("reviews!");

    let body = document.querySelector("body");

    let reviewSections = document.querySelectorAll(".hashpress-reviews-section");

    reviewSections.forEach((section) => {
        let modalToggles = section.querySelectorAll(
            ".hashpress-reviews-write-review, .hashpress-reviews-modal__close, .hashpress-reviews-modal__bg",
        );
        let modal = section.querySelector(".hashpress-reviews-modal");

        modalToggles.forEach((toggle) => {
            toggle.addEventListener("click", () => {
                modal.classList.toggle("is-active");
                body.classList.toggle("hashpress-reviews-modal-open");
            });
        });
    });

    handleReviewSubmit();

    function handleReviewSubmit() {
        let reviewForm = document.querySelector("#write-review");
        if (reviewForm) {
            const ratingWrapper = reviewForm.querySelector("#rating-wrapper");
            const ratingDisplay = ratingWrapper.querySelector(".selected-rating");
            let rating;

            const stars = ratingWrapper.querySelectorAll(".hashpress-reviews-stars__star");
            [...stars].forEach((star) => {
                star.addEventListener("click", function () {
                    // reset active states
                    [...stars].forEach((star) => {
                        star.classList.remove("is-active");
                    });

                    rating = +star.id;
                    ratingDisplay.innerText = +rating;
                    star.classList.add("is-active");
                });
            });

            reviewForm.addEventListener("submit", function (event) {
                event.preventDefault();
                console.log("submitted");
                let { passed, reviewString } = validateForm(reviewForm, rating);
                if (!passed) return; //form validation failed

                executeReviewTransaction(reviewString);
            });
        }
    }

    function validateForm(reviewForm, rating) {
        const notices = reviewForm.querySelector(".write-review-notices");
        notices.innerText = ""; // reset notices

        let submitButton = reviewForm.querySelector(".hashpress-reviews-submit-review");
        const transactionId = submitButton.dataset.transactionId;
        if (!transactionId) {
            console.log("transaction id missing");
            return;
        }
        const name = reviewForm.querySelector("#name").value;
        const message = reviewForm.querySelector("#message").value;
        let passed = true;

        if (!name || name === "") {
            notices.innerText += " Name is required. ";
            passed = false;
        } else {
            if (name.length <= 2) {
                notices.innerText += " Name is too short. ";
                passed = false;
            }
        }

        if (!message || message === "") {
            notices.innerText += " Message is required. ";
            passed = false;
        }

        if (!rating) {
            notices.innerText += " Rating is required. ";
            passed = false;
        } else {
            if (!(rating > 0 && rating <= 5)) {
                notices.innerText += " Rating is invalid. ";
                passed = false;
            }
        }

        if (message.length > 900) {
            notices.innerText += "Review is too long. ";
            passed = false;
        }

        if (!passed) {
            return { passed, undefined };
        }

        let review = {
            transactionId, // pay transaction
            rating,
            name,
            message,
        };

        const reviewString = JSON.stringify(review);
        return { passed, reviewString };
    }

    async function executeReviewTransaction(data) {
        const contractId = ContractId.fromString("0.0.4688625"); //0.0.4687987

        let fromAccount = AccountId.fromString(pairingData.accountIds[0]);
        let signer = hashconnect.getSigner(fromAccount);

        //Create the transaction to deploy a new CashbackReview contract
        let transaction = await new ContractExecuteTransaction()
            //Set the ID of the contract
            .setContractId(contractId)
            //Set the gas for the call
            .setGas(2000000)
            //Set the function of the contract to call
            .setFunction("writeReview", new ContractFunctionParameters().addString(data))
            .freezeWithSigner(signer);

        let transactionId;
        try {
            let response = await transaction.executeWithSigner(signer);

            //Confirm the transaction was executed successfully
            transactionId = response.transactionId.toString();
            let receipt = await response.getReceiptWithSigner(signer);
            console.log("The transaction status is " + receipt.status.toString());
            if (receipt.status._code === 22) {
                // console.log(transactionId);
                setQueryParamAndRedirect("review_transaction_id", transactionId);
            } else {
                console.log(receipt.status);
            }
        } catch (e) {
            console.log(e);

            // ignore weird hashconnect errors for now..
            // console.log(transactionId);
            // if (transactionId) {
            //     setQueryParamAndRedirect('review_transaction_id', transactionId);
            // }
        }
    }
})();
