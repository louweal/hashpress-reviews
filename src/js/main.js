import { TransferTransaction, Hbar, AccountId } from "@hashgraph/sdk";
import { ContractId, ContractExecuteTransaction, ContractFunctionParameters } from "@hashgraph/sdk";
import { arrayify } from "@ethersproject/bytes";
import { defaultAbiCoder } from "@ethersproject/abi";

(function () {
    "use strict";

    console.log("reviews!");

    let transactionHistory = undefined;

    let body = document.querySelector("body");

    loadReviews();

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

                    reviewForm.addEventListener("submit", async function (event) {
                        event.preventDefault();

                        console.log("submitted");
                        let review = processForm(reviewForm, rating);
                        if (!review) return; //form processing / validation failed

                        review["transactionId"] = userTransaction;

                        let reviewString = JSON.stringify(review);

                        executeReviewTransaction(reviewString);
                    });
                }
            }
        }
    });

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

    function findUserTransaction(transactionHistory, account) {
        for (const transaction of transactionHistory) {
            if (transaction.startsWith(account)) {
                console.log(transaction);
                return transaction;
            }
        }
        return undefined;
    }

    // async function handleReviewSubmit() {

    // }

    async function fetchTransactionHistory() {
        let id = hashpressReviewsAPI.postId;

        // Fetch post transaction history by post ID
        try {
            const response = await fetch(`${hashpressReviewsAPI.getTransactionHistoryUrl}?id=${id}`, {
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

                // todo notice frontend and hide modal

                let activeModal = document.querySelector(".hashpress-reviews-modal.is-active");
                console.log(activeModal);
                if (activeModal) {
                    activeModal.classList.remove("is-active");
                    body.classList.remove("hashpress-reviews-modal-open");
                }
            } else {
                console.log(receipt.status);
            }
        } catch (e) {
            console.log(e);
        }
    }

    async function loadReviews(loadModalReviews = false) {
        let reviews;

        if (loadModalReviews) {
            reviews = document.querySelectorAll(".hashpress-reviews-modal .hashpress-reviews-review.is-loading");
        } else {
            reviews = document.querySelectorAll(
                ".hashpress-reviews-section > .hashpress-reviews-list > .hashpress-reviews-review.is-loading",
            );
        }

        console.log(reviews.length);

        [...reviews].forEach(async (review) => {
            let rId = review.id;
            if (rId) {
                let badge = review.querySelector(".hashpress-reviews-review__badge");
                let icon = review.querySelector(".hashpress-reviews-review__icon");
                let name = review.querySelector(".hashpress-reviews-review__username");
                let stars = review.querySelectorAll(".hashpress-reviews-review__star");
                let buyDate = review.querySelector(".hashpress-reviews-review__date1");
                let buyDateTime = review.querySelector(".hashpress-reviews-review__date1 time");
                let reviewDate = review.querySelector(".hashpress-reviews-review__date2");
                let reviewDateTime = review.querySelector(".hashpress-reviews-review__date2 time");
                let body = review.querySelector(".hashpress-reviews-review__body p");

                let { network, reviewData } = await fetchMirrornodeLogData(rId);
                let baseUrl = `https://${network}.mirrornode.hedera.com/api/v1/transactions/`;
                let contractBaseUrl = `https://${network}.mirrornode.hedera.com/api/v1/contracts/results/`;

                reviewData = JSON.parse(reviewData);

                // show testnet and previewnet badge
                if (network != "mainnet") {
                    badge.innerText = network;
                    badge.style.display = "block";
                }

                // set stars
                let i = 1;
                [...stars].forEach((star) => {
                    if (reviewData.rating >= i) {
                        star.classList.add("is-solid");
                    } else {
                        star.classList.remove("is-solid");
                    }
                    i += 1;
                });

                icon.innerText = reviewData.name[0].toUpperCase(); // set icon
                name.innerText = reviewData.name; // set name
                body.innerText = reviewData.message; // set message

                // set buy date info
                buyDate.setAttribute("href", `${baseUrl}${reviewData.transactionId}`);
                let bId = unparseTransactionId(reviewData.transactionId);
                let formattedBuyDate = formatTimestamp(bId.split("@")[1]);
                buyDateTime.innerText = formattedBuyDate;
                buyDateTime.addEventListener("mouseover", function () {
                    buyDateTime.innerText = bId.substring(0, 7) + "..." + bId.substring(bId.length - 7);
                });
                buyDateTime.addEventListener("mouseout", function () {
                    buyDateTime.innerText = formattedBuyDate;
                });

                // Set review date info
                reviewDate.setAttribute("href", `${contractBaseUrl}${parseTransactionId(rId)}`);

                let formattedReviewDate = formatTimestamp(rId.split("@")[1]);
                reviewDateTime.innerText = formattedReviewDate;
                reviewDateTime.addEventListener("mouseover", function () {
                    reviewDateTime.innerText = rId.substring(0, 7) + "..." + rId.substring(rId.length - 7);
                });
                reviewDateTime.addEventListener("mouseout", function () {
                    reviewDateTime.innerText = formattedReviewDate;
                });

                review.classList.remove("is-loading");
            }
        });
    }

    async function fetchMirrornodeLogData(transactionId) {
        let networks = ["testnet", "mainnet", "previewnet"];
        // console.log('fetch');

        for (let network of networks) {
            let url = `https://${network}.mirrornode.hedera.com/api/v1/contracts/results/${parseTransactionId(
                transactionId,
            )}`;

            try {
                const response = await fetch(url, {
                    method: "GET",
                    headers: {},
                });
                const text = await response.text(); // Parse it as text
                const data = JSON.parse(text); // Try to parse the response as JSON
                // The response was a JSON object

                let logs = data["logs"];
                if (logs) {
                    for (let log of logs) {
                        let hexData = log["data"];
                        if (hexData) {
                            let decodedData = await decodeHexString(hexData);
                            return { network, reviewData: decodedData }; // just returns the first log
                        }
                    }
                }
            } catch (err) {
                console.log(err);
                return null;
            }
        }
    }

    function parseTransactionId(transactionId) {
        let splitId = transactionId.split("@");
        let accountId = splitId[0];
        let timestamp = splitId[1].replace(".", "-");
        return `${accountId}-${timestamp}`;
    }

    function unparseTransactionId(transactionId) {
        const index = transactionId.indexOf("-");
        let newString = transactionId.slice(0, index) + "@" + transactionId.slice(index + 1);
        return newString.replace("-", ".");
    }

    function decodeHexString(hex) {
        return defaultAbiCoder.decode(["string"], arrayify(hex))[0];
    }

    function formatTimestamp(secondsNanoseconds) {
        const [seconds, nanoseconds] = secondsNanoseconds.split(".").map(Number);
        const milliseconds = seconds * 1000 + nanoseconds / 1e6;
        const date = new Date(milliseconds);
        const options = { year: "numeric", month: "long" };
        return date.toLocaleDateString(undefined, options).replace(",", "");
    }
})();
