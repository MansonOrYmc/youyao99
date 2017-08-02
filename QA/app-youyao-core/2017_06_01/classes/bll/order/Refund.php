<?php

class Bll_Order_Refund {
    private $orderInfoDao;

    public function __construct() {
        $this->refundDao = new Dao_Order_Refund();
    }

    public function get_refund_by_bid($bid) {
        return $this->refundDao->get_refund_by_bid($bid);
    }

    public function get_refund_by_bids($bids) {
        return $this->refundDao->get_refund_by_bids($bids);
    }
}

