<?php
apf_require_class("APF_DB_Factory");

class Dao_Activity_Activity {

	private $pdo;
	private $slave_pdo;

	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
	}

	public function add_activity_record($uid, $name, $value, $time, $ip) {
		$sql = 'INSERT INTO `t_activity_record` '
			. ' (`uid`,`activity_name`,`activity_value`,`create_time`,`ip`) '
			. ' VALUES (:uid,:name,:value,:time,:ip)';
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute(array(
			'uid' => $uid,
			'name' => $name,
			'value' => $value,
			'time' => $time,
			'ip' => $ip
		));
	}

	public function count_activity_record($uid, $activity_name, $create_date) {
		$sql = 'SELECT count(id) AS count FROM t_activity_record '
			. ' WHERE uid = :uid AND activity_name = :activity_name '
			. ' AND FROM_UNIXTIME(create_time,"%Y-%m-%d")=:create_date';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array(
			'uid' => $uid,
			'activity_name' => $activity_name,
			'create_date' => $create_date
		));
		$result = $stmt->fetchColumn();
		return $result;
	}

	public function count_ip_record($activity_name, $ip, $date) {
		$sql = 'SELECT count(id) FROM t_activity_record '
			. ' WHERE ip=:ip AND activity_name = :activity_name '
			. ' AND FROM_UNIXTIME(create_time,"%Y-%m-%d")=:create_date';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array(
			'ip' => $ip,
			'activity_name' => $activity_name,
			'create_date' => $date
		));
		$result = $stmt->fetchColumn();
		return $result;
	}

	public function add_order_record($order_info) {
		$sql = <<<'SQL'
INSERT INTO stats_db.t_double11_exist (room_id,status,create_time,oid,type)
VALUES(:room_id,:status,UNIX_TIMESTAMP(CURRENT_TIMESTAMP),:oid,:type)
SQL;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array(
			'room_id' => $order_info['room_id'],
			'status' => $order_info['status'],
			'oid' => $order_info['oid'],
			'type' => $order_info['type']
		));
	}

	public function get_activity_order($order_id) {
		$sql = 'SELECT * FROM stats_db.t_double11_exist WHERE oid=:order_id';
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array('order_id' => $order_id));
		return $stmt->fetch();
	}

	public function delete_room($room_id, $oid, $status = 1) {
		$sql = <<<SQL
UPDATE `stats_db`.`t_double11_exist` SET status=:status,create_time=unix_timestamp(current_timestamp) WHERE oid=:oid AND room_id=:room_id
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array(
			'room_id' => $room_id,
			'oid' => $oid,
			'status' => $status
		));
	}

	public function remove_room($room_id) {
		$sql = "delete from `stats_db`.`t_double11_exist` where `room_id` = '$room_id'";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
	}

	public function get_room($room_id) {
		$sql = "select * from `stats_db`.`t_double11_exist` where `room_id` = '$room_id' and status !=3 order by status asc";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		$r = $stmt->fetchAll();
		return $r[0];
	}

	public function batch_get_room($room_id_arr) {
		$room_id_str = join(',', $room_id_arr);
		$sql = "select * from `stats_db`.`t_double11_exist` where `room_id` in ($room_id_str) and status !=3";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		$r = $stmt->fetchAll();
		return $r;
	}

	public function get_room_byoid($oid) {
		$sql = "select * from `stats_db`.`t_double11_exist` where `oid` = '$oid'";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		$r = $stmt->fetchAll();
		return $r[0];
	}

	public function check_lottery_count($uid) {
		$sql = <<<SQL
SELECT count(id) FROM stats_db.t_double11_lottery
WHERE uid=:uid AND from_unixtime(create_time,"%Y-%m-%d") = :date
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid, 'date' => date('Y-m-d')));
		return $stmt->fetchColumn();
	}

	public function check_lottery_stock($grade) {
		$left_stock = $this->lottery_left_stock();
		if (intval($left_stock[$grade]) > 0) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	public function lottery_left_stock() {
		$stock_map = array(
			1 => 20000 / 3,
			2 => 20000 / 3,
			3 => 20000 / 3,
			4 => 50,
			5 => 40
		);
		$left_day = (strtotime('2015-11-18') - strtotime(date('Y-m-d'))) / (24 * 3600);

		$sql = "SELECT grade,count(id) AS num FROM stats_db.t_double11_lottery GROUP BY grade";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetchAll();
		$used_stock = array();
		foreach ($result as $row) {
			$used_stock[$row['grade']] = $row['num'];
		}
		$left_stock = array();
		foreach ($stock_map as $key => $stock) {
			switch ($key) {
				case 1:
					$left_stock[$key] = ($stock - $used_stock[$key] * 3) / $left_day / 3;
					break;
				case 2:
					$left_stock[$key] = ($stock - $used_stock[$key] * 5) / $left_day / 5;
					break;
				case 3:
					$left_stock[$key] = ($stock - $used_stock[$key] * 10) / $left_day / 10;
					break;
				case 4:
					$left_stock[$key] = ($stock - $used_stock[$key]) / $left_day;
					break;
				case 5:
					$left_stock[$key] = ($stock - $used_stock[$key]) / $left_day;
					break;
				default:
					break;
			}
		}
		return $left_stock;
	}

	public function insert_lottery_record($uid, $grade) {
		$sql = <<<SQL
INSERT INTO stats_db.t_double11_lottery
(uid,grade,create_time) VALUES
(:uid,:grade,unix_timestamp(current_timestamp));
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$result = $stmt->execute(array('uid' => $uid, 'grade' => $grade));
		return $result;
	}

	public function update_lottery_contact($uid, $name, $mobile, $address) {
		$sql = <<<SQL
UPDATE stats_db.t_double11_lottery
SET name=:name,mobile=:mobile,address=:address
WHERE uid=:uid ORDER BY id DESC LIMIT 1
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$result = $stmt->execute(array(
			'uid' => $uid,
			'name' => $name,
			'mobile' => $mobile,
			'address' => $address
		));
		return $result;
	}
}