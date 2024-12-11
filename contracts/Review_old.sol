// SPDX-License-Identifier: MIT
pragma solidity ^0.8.2;

contract Review {
    event MessageEmitted(string message); // declare event

    constructor(string memory _data)  {
        emit MessageEmitted(_data); // emit event
    }
}