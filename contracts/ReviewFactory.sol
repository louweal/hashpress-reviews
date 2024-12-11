//SPDX-License-Identifier: MIT
pragma solidity >=0.7.0 <0.9.0;

import "./Review.sol";

contract ReviewFactory {
    function deployReview(string memory data) external payable {
        new Review(data);
    }
}