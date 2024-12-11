//SPDX-License-Identifier: MIT
pragma solidity >=0.7.0 <0.9.0;

import "./CashbackReview.sol";

contract CashbackReviewFactory {
    function deployCashbackReview(uint32 _bSeconds, uint32 _bNanoseconds, uint64 _cashback, address _shopOwner, uint32 _iSeconds
) external payable {
        (new CashbackReview){value: msg.value}(msg.sender, _bSeconds, _bNanoseconds, _cashback, _shopOwner, _iSeconds);
    }
}

