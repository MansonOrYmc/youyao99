<?php
apf_require_class("APF_DB_Factory");

class Dao_Groceries_GroceriesInfo {

	private $lky_pdo;
	private $lky_slave_pdo;
	private $one_pdo;

	public function __construct() {
		$this->lky_pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$this->lky_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
	}

	public function insert_fcode($info) {
		$sql = "insert into a_fcode_succ(s_uid, d_uid, channel, status, fund, create_date) values(?, ?, ?, ?, ?, ?)";
		$stmt = $this->lky_pdo->prepare($sql);
		return $stmt->execute($info);
	}

	public function get_fcode_by_duid($duid) {
		$sql = "select id, d_uid, s_uid from a_fcode_succ where d_uid = ? and status = 0";
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute($duid);
		return $stmt->fetch();
	}

	public function update_fcode_status_by_id($status, $uid) {
		$sql = "update a_fcode_succ set status = ? where id = ?";
		$stmt = $this->lky_pdo->prepare($sql);
		return $stmt->execute(array($status, $uid));
	}

	public function send_flood_notify($info) {
		$sql = "insert into drupal_flood(event, identifier, timestamp, expiration) values(?, ?, ?, ?)";
		$stmt = $this->one_pdo->prepare($sql);
		return $stmt->execute($info);
	}

	public function price_config_v2($uid) {
		$sql = "select room_date, room_price from t_rpconfig_v2 where status = 1 and uid = ?";
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetch();
	}

	public function paypal_queue_by_oid($oid) {
		$sql = "select id from t_paypal_queue where oid = ?";
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute(array($oid));
		return $stmt->fetchColumn();
	}

	public function insert_paypal_queue($info) {
		$sql = "insert into t_paypal_queue(oid, sid, uid, uname, paypal_account, total_price_cn, total_price_tw, rebate_num, rev_percent, customer_level, area, retry, status, create_time, dest_id) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0, 0, ?, ?)";
		$stmt = $this->lky_pdo->prepare($sql);
		return $stmt->execute($info);
	}

	public function update_paypal_queue_by_id($info) {
		$sql = "update t_paypal_queue set paypal_account = ?, total_price_cn = ?, total_price_tw = ?, rebate_num = ?, rev_percent = ?, customer_level = ? where id = ?";
		$stmt = $this->lky_pdo->prepare($sql);
		return $stmt->execute($info);
	}

}
?>