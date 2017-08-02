<?php

class Bll_Order_OrderLog {
        private $paradise;

        public function __construct() {
                $this->paradise = new Dao_Order_OrderLog();
        }

	public function set_price_change_log($params) {
		return $this->paradise->insert_price_change_log($params);
	}

	public function get_order_log_by_id($id, $status = -1) {
		if(!is_array($id)) $id = array($id);
		return $this->paradise->get_order_log_by_id($id, $status);
	}
}
