<?php

class Dao_Fcode_FcodeInfo {

	private $pdo;
	private $slave_pdo;
	private $one_pdo;

	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
	}

	public function get_fcode_by_uid($uid) {
		$sql = "SELECT fcode FROM a_fcode WHERE uid = ?";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchColumn();

	}

	public function get_uid_by_fcode($fcode) {
		$sql = "SELECT uid FROM a_fcode WHERE fcode = ?";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($fcode));
		return $stmt->fetchColumn();
	}

	public function update_fcode_by_uid($uid, $fcode) {
		$sql = "insert a_fcode (uid, fcode) values(?, ?)";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute(array($uid, $fcode));

	}

	public function update_user_fund($uid, $fund) {
		$sql = "UPDATE drupal_users SET fund = ? WHERE uid = ?";
		$stmt = $this->one_pdo->prepare($sql);
		return $stmt->execute(array($fund, $uid));
	}

	public function insert_fcode_succ($d_uid, $s_uid, $channel, $fund) {
		$sql = "INSERT INTO a_fcode_succ (s_uid, d_uid, channel, status, fund, create_date) VALUES(?, ?, ?, ?, ?, ?)";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute(array(
			$s_uid,
			$d_uid,
			$channel,
			0,
			$fund,
			time()
		));
	}

	public function get_row_by_tuid($t_uid) {
		$sql = "SELECT s_uid FROM a_fcode_succ WHERE d_uid = ?";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($t_uid));
		return $stmt->fetchColumn();
	}

	public function get_row_list($uid, $status, $page) {
		$condition = '';
		$pagearg = '';
		if (!empty($status) || !empty($page)) {
			if ($status == 1) {
				$condition .= ' and status = 1 and channel > -1 ';
			}
			elseif ($status == 'x') {
				$condition .= ' and status = 0 and channel > -1 ';
			}
			elseif ($status == "-1") {
				$condition .= ' and channel = "-1"';
			}
			$pagearg .= " limit " . ($page * 20) . "," . (($page + 1) * 20) . " ";
		}
		$sql = "SELECT * FROM a_fcode_succ WHERE s_uid = ? " . $condition . " ORDER BY create_date ASC " . $pagearg;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchAll();
	}

	public function get_fuid_byuid($uid) {

		$sql = "select * from a_fcode_succ where (s_uid = ? or d_uid = ?) and channel > '-1' order by id desc";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($uid, $uid));
		return $stmt->fetch();

	}

}
