<?php

class Bll_Refund_RefundInfo {
    private $RefundInfoDao;

    public function __construct() {
        $this->RefundInfoDao = new Dao_Refund_RefundInfo();
    }
	
	public function get_refund_info_by_oid($oid) {
		if(empty($oid)) return;
		return $this->RefundInfoDao->get_refund_info_by_oid($oid);
	}

    public function get_refund_info_by_bids($bids) {
        if(empty($bids)) return;
        return $this->RefundInfoDao->get_refund_info_by_bids($bids);
    }
}
