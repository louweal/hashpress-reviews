import { AccountId } from "@hashgraph/sdk";
import { ContractId, ContractExecuteTransaction, ContractFunctionParameters } from "@hashgraph/sdk";

(function () {
    "use strict";

    let hasReviewSection = document.querySelector(".hashpress-reviews-section");
    if (!hasReviewSection) return;

    console.log("reviews!");

    let transactionHistory = undefined;
    let body = document.querySelector("body");

    window.addEventListener("hashconnectInitDone", async function () {
        console.log("done!");

        if (window.pairingData) {
            transactionHistory = await fetchTransactionHistory();
            let account = window.pairingData.accountIds[0];
            let userTransaction = findUserTransaction(transactionHistory, account);

            if (userTransaction) {
                console.log("found transaction");

                // show button
                let reviewButton = document.querySelector(".hashpress-reviews-write-review"); // first button
                reviewButton.classList.add("is-active");

                let reviewForm = document.querySelector("#write-review"); // first form
                if (reviewForm) {
                    const ratingWrapper = reviewForm.querySelector("#rating-wrapper");
                    const ratingDisplay = ratingWrapper.querySelector(".selected-rating");
                    let rating;

                    const stars = ratingWrapper.querySelectorAll(".hashpress-reviews-stars__star");
                    console.log(stars);
                    [...stars].forEach((star) => {
                        star.addEventListener("click", function () {
                            // reset active states
                            [...stars].forEach((star) => {
                                star.classList.remove("is-active");
                            });

                            rating = +star.id;
                            console.log(rating);
                            ratingDisplay.innerText = +rating;
                            star.classList.add("is-active");
                        });
                    });

                    reviewForm.addEventListener("submit", async function (event) {
                        event.preventDefault();

                        console.log("submitted");
                        let review = processForm(reviewForm, rating);
                        if (!review) return; //form processing / validation failed

                        review["transactionId"] = userTransaction;

                        let reviewString = JSON.stringify(review);

                        executeReviewTransaction(reviewString);

                        let activeModal = document.querySelector(".hashpress-reviews-modal.is-active");
                        if (activeModal) {
                            activeModal.classList.remove("is-active");
                            body.classList.remove("hashpress-reviews-modal-open");
                        }
                    });
                }
            }
        }
    });

    let modals = document.querySelectorAll(".hashpress-reviews-modal");
    modals.forEach((modal) => {
        const modalShow = modal.previousElementSibling;
        const modalHide = modal.querySelectorAll(" .hashpress-reviews-modal__close, .hashpress-reviews-modal__bg");
        const modalToggles = [modalShow, ...modalHide];

        modalToggles.forEach((toggle) => {
            toggle.addEventListener("click", () => {
                modal.classList.toggle("is-active");
                body.classList.toggle("hashpress-reviews-modal-open");
            });
        });
    });

    function findUserTransaction(transactionHistory, account) {
        for (const transaction of transactionHistory) {
            if (transaction.startsWith(account)) {
                console.log(transaction);
                return transaction;
            }
        }
        return undefined;
    }

    async function fetchTransactionHistory() {
        let postId = hashpressReviewsAPI.postId;

        // Fetch post transaction history by post ID
        try {
            const response = await fetch(`${hashpressReviewsAPI.getTransactionHistoryUrl}?id=${postId}`, {
                method: "GET",
                headers: {
                    "X-WP-Nonce": hashpressReviewsAPI.nonce,
                },
            });

            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

            const data = await response.json();
            if (data.error) {
                console.error(`Error fetching data for ID ${id}: ${data.error}`);
                return;
            }
            return data;
        } catch (error) {
            console.error(`Error: Error fetching data for ID ${id}:`, error);
        }
    }

    function updateReviewHistory(reviewId) {
        fetch(hashpressReviewsAPI.updateReviewHistoryUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": hashpressReviewsAPI.nonce,
            },
            body: JSON.stringify({
                reviewId: reviewId,
                postId: hashpressReviewsAPI.postId,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    console.log("send reviewId success");
                } else {
                    console.error("Failed to update transaction IDs");
                }
            })
            .catch((error) => console.error("Error:", error));
    }

    function processForm(reviewForm, rating) {
        const notices = reviewForm.querySelector(".write-review-notices");
        notices.innerText = ""; // Reset notices

        const name = reviewForm.querySelector("#name").value.trim();
        const message = reviewForm.querySelector("#message").value.trim();

        const errors = [];

        if (!name) {
            errors.push("Name is required.");
        } else if (name.length <= 2) {
            errors.push("Name is too short.");
        }

        if (!message) {
            errors.push("Message is required.");
        } else if (message.length > 900) {
            errors.push("Review is too long.");
        }

        if (!rating) {
            errors.push("Rating is required.");
        } else if (rating <= 0 || rating > 5) {
            errors.push("Rating is invalid.");
        }

        // Display errors if any
        if (errors.length > 0) {
            notices.innerText = errors.join(" ");
            return undefined;
        }

        // Return the valid review data
        return { rating, name, message };
    }

    async function executeReviewTransaction(data) {
        console.log("executeReviewTransaction");
        const contractId = ContractId.fromString("0.0.4688625");

        let fromAccount = AccountId.fromString(pairingData.accountIds[0]);
        let signer = hashconnect.getSigner(fromAccount);

        let transaction = await new ContractExecuteTransaction()
            .setContractId(contractId)
            .setGas(2000000)
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
                console.log(transactionId);
                console.log("success!");
                updateReviewHistory(transactionId);

                // location.href = "#reviews";
                // location.reload();
            } else {
                console.log(receipt.status);
            }
        } catch (e) {
            console.log(e);
        }
    }
})();
