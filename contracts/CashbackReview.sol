// SPDX-License-Identifier: MIT
pragma solidity ^0.8.2;

contract CashbackReview {
    enum State { Pending, Reviewed }
    
    // State variables
    // transactionId = customer@bSeconds.bNanoseconds
    // Slot 1
    address payable public customer; 
    // Slot 2
    address payable public shopOwner;
    // Slot 3
    uint64 public cashback;
    uint32 public bSeconds; // buy timestamp in seconds
    uint32 public bNanoseconds; // buy timestamp nanoseconds
    uint32 public rSeconds; // review timestamp in seconds, range valid until 7 February 2106 06:28:15
    uint32 public iSeconds; // minimum number of seconds (interval) between buy and review
    State public state;
    // Slot 4
    string public cid;

    // Payable constructor
    constructor(address _customer, uint32 _bSeconds, uint32 _bNanoseconds, uint64 _cashback, address _shopOwner, uint32 _iSeconds ) payable {
        // require(msg.value > 0, "No value send"); 
        // require(msg.value >= cashback, "Incorrect value send"); 
        customer = payable(_customer);
        shopOwner = payable(_shopOwner);
        bSeconds = _bSeconds;
        bNanoseconds = _bNanoseconds;
        cashback = _cashback;
        iSeconds = _iSeconds;
        state = State.Pending; 
        // require(shopOwner.send(msg.value - cashback), "Failed to send");
    }


    // Payable function to attach a review
    function AttachReview(string memory _cid) external inState(State.Pending) onlyCustomer {
        require(block.timestamp >= (bSeconds + iSeconds), "Review attempt is too soon");
        cid = _cid;
        rSeconds = uint32(block.timestamp); // block timestamp is in seconds
        state = State.Reviewed;
        customer.transfer(cashback); // customer gets his cashback
    }

        // modifiers

    /// The function cannot be called at the current state.
    error InvalidState();

    modifier inState(State state_) {
        if (state != state_) {
            revert InvalidState();
        }
        _;
    }

    /// Only the customer can call this function
    error OnlyCustomer();

    modifier onlyCustomer() {
        if (msg.sender != customer) {
            revert OnlyCustomer();
        }
        _;
    }
}