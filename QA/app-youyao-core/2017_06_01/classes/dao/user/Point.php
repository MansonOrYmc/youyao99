<?php

class Dao_User_Point {
	private $pdo;

	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
	}

	public function get_total_income_point($uid) {
		$sql = <<<SQL
SELECT sum(point) FROM LKYou.t_user_points
WHERE validate_time<UNIX_TIMESTAMP(CURRENT_TIMESTAMP)
AND status=1
AND type=1
AND uid=:uid
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$result = $stmt->execute(array('uid' => $uid));
		if ($result) {
			return $stmt->fetchColumn();
		}
		else {
			return FALSE;
		}
	}

    public function get_total_point_by_type($uid, $type) {
        $sql = "select sum(point) from LKYou.t_user_points where validate_time<UNIX_TIMESTAMP(CURRENT_TIMESTAMP) and status = 1 and type = 1 and uid = ? and source = ?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($uid, $type));
        return $stmt->fetchColumn();
    }

	public function get_total_outgo_point($uid) {
		$sql = <<<SQL
SELECT sum(point) FROM LKYou.t_user_points
WHERE status=1
AND type=2
AND uid=:uid
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$result = $stmt->execute(array('uid' => $uid));
		if ($result) {
			return $stmt->fetchColumn();
		}
		else {
			return FALSE;
		}
	}

	public function get_available_point_detail($uid, $limit, $offset) {
		$sql = <<<SQL
SELECT * FROM LKYou.t_user_points
WHERE validate_time<UNIX_TIMESTAMP(CURRENT_TIMESTAMP)
AND status=1
AND type=1
AND uid=:uid
UNION
SELECT * FROM LKYou.t_user_points
WHERE type=2
AND status =1
AND uid=:uid
ORDER BY id DESC
LIMIT :limit
OFFSET :offset
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->bindParam('limit', $limit, PDO::PARAM_INT);
		$stmt->bindParam('offset', $offset, PDO::PARAM_INT);
		$stmt->bindParam('uid', $uid, PDO::PARAM_INT);
		$result = $stmt->execute();
		if ($result) {
			return $stmt->fetchAll();
		}
		else {
			return FALSE;
		}
	}

	public function add_point_use_log($uid, $point_value, $order_id,$remark) {
		$sql = <<<SQL
INSERT INTO LKYou.t_user_points (uid,point,create_time,validate_time,remark,order_id,type,source)VALUES(
:uid,:point_value,unix_timestamp(current_time),unix_timestamp(current_time),:remark,:order_id,2,'pay callback')
SQL;
		$stmt = $this->pdo->prepare($sql);
		$result = $stmt->execute(array(
			'uid' => $uid,
			'point_value' => $point_value,
			'order_id' => $order_id,
			'remark' => $remark
		));
		return $result;
	}

	public function count_point_source($uid, $source) {
		$sql = <<<SQL
SELECT count(id) FROM LKYou.t_user_points WHERE uid=:uid AND source=:source AND type=1
SQL;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid, 'source' => $source));
		return $stmt->fetchColumn();
	}

	public function count_point_source_by_date($uid, $source, $date) {
		$sql = <<<SQL
SELECT count(id) FROM LKYou.t_user_points WHERE uid=:uid AND source=:source AND type=1 AND FROM_UNIXTIME(create_time,'%Y-%m-%d') = :date
SQL;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array(
			'uid' => $uid,
			'source' => $source,
			'date' => $date
		));
		return $stmt->fetchColumn();
	}

	public function add_user_point($uid, $point, $remark, $source, $expire_time) {
		$sql = <<<SQL
INSERT INTO LKYou.t_user_points (uid,point,create_time,validate_time,expire_time,remark,type,source)
VALUES(:uid,:point,unix_timestamp(CURRENT_TIMESTAMP),unix_timestamp(CURRENT_TIMESTAMP),:expire_time,:remark,:type,:source)
SQL;
		$stmt = $this->pdo->prepare($sql);
		$result = $stmt->execute(array(
			'uid' => $uid,
			'point' => intval($point),
			'expire_time' => $expire_time,
			'remark' => htmlentities($remark, ENT_QUOTES, 'UTF-8'),
			'type' => 1,
			'source' => $source
		));
		return $result;
	}

    public function get_use_point_by_order($uid, $order_id) {
        $sql = " select sum(point) from t_user_points where type = 2 and uid = :uid and order_id = :order_id ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(
            'uid' => $uid,
            'order_id' => $order_id,
        ));
        $result = $stmt->fetchColumn();
        return $result;

    }
}
