// SPDX-License-Identifier: MIT
pragma solidity ^0.8.2;

contract Review {
    event ReviewEmitted(string _data); // declare event

    function writeReview(string memory _data) external {
        emit ReviewEmitted(_data); // emit event
    }
}