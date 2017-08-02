<?php
apf_require_class("APF_DB_Factory");

class Dao_Order_OrderLog {

	private $pdo;
	private $one_pdo;

	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
	}

	public function insert_price_change_log($params) {

		foreach ($params as $k => $v) {
			$field = $field ? $field . ", " . $k : $k;
			$arg = $arg ? $arg . ", '" . $v . "'" : "'" . $v . "'";
		}

		$sql = "insert into `t_pricechange_reason` ($field) values ($arg) ;";
		$stmt = $this->pdo->prepare($sql);
		$result = $stmt->execute();
		return $result;
	}

	public function insert_order_guid($order_id, $guid) {
		$sql = 'INSERT INTO log_homestay_booking_guid (order_id,guid)VALUES(:order_id,:guid)';
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute(array('order_id' => $order_id, 'guid' => $guid));
	}

	public function get_order_log_by_id($id, $status) {
		$condition = "";
		if($status > -1) {
			$condition = "and status = $status";
		}
		$idStr = implode(",", $id);
		$sql = "select * from log_homestay_booking_trac where bid in ($idStr) $condition";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();

	}

}
