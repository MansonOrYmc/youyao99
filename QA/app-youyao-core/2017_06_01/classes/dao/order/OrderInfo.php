<?php
apf_require_class("APF_DB_Factory");

class Dao_Order_OrderInfo {

	private $pdo;
	private $slave_pdo;
	private $one_pdo;

	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
		$this->one_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
	}

	public function get_log_homestay_booking_trac($order_id,$status=4) {
		$sql = 'SELECT bid,only_save_comment,intro,status FROM log_homestay_booking_trac WHERE bid=:bid AND status='.$status;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('bid' => $order_id));
		return $stmt->fetchAll();
	}

    public function set_nodisc_price($oid,$price,$price_cn){
        $sql = 'INSERT INTO LKYou.t_homestay_booking_addition (order_id,original_price,original_price_cn)VALUES (:order_id,:original_price,:original_price_cn) ON DUPLICATE KEY UPDATE original_price=:original_price,original_price_cn=:original_price_cn;';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array(
            'order_id' => $oid,
            'original_price' => $price,
            'original_price_cn'=>$price_cn
        ));

    }
	public function zzk_mark_order_disc($order_id, $disc)
	{
		$sql = 'INSERT INTO LKYou.t_homestay_booking_addition (order_id,disc)VALUES (:order_id,:disc) ON DUPLICATE KEY UPDATE disc=:disc;';
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute(array(
			'order_id' => $order_id,
			'disc' => empty($disc) ? 0 : 1
		));
	}
	public function pay_order_load($rid, $sta = 4) {
		$sql = "SELECT tid, bid, content, intro, create_date FROM log_homestay_booking_trac WHERE bid = ? AND only_save_comment <> 1 AND status = ? ORDER BY tid DESC";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($rid, $sta));
		return $stmt->fetch();
	}

    public function get_lastest_trac($rid) {
		$sql = "SELECT tid, bid, content, intro, create_date FROM log_homestay_booking_trac WHERE bid = ? ORDER BY tid DESC";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($rid));
		return $stmt->fetch();
    }

	public function dao_count_log_homestay_booking_trac_by_bid($bid) {
		$sql = "SELECT count(*) AS count FROM log_homestay_booking_trac WHERE bid = ?";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($bid));
		return $stmt->fetchColumn();
	}

	public function dao_insert_log_homestay_booking_trac($info) {
		$sql = "INSERT INTO log_homestay_booking_trac(status, content, intro, price_tw, admin_uid, bid, create_date, client_ip) VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($info);
	}

	public function save_order_extra_info($data) {
		if (empty($data) || empty($data['oid'])) {
			return FALSE;
		}

		$conn = $this->pdo;
		$stmt = $conn->prepare("SELECT * FROM t_order_extra WHERE oid = ?");
		$stmt->execute(array($data['oid']));
		$row = $stmt->fetch(PDO::FETCH_OBJ);
		$stmt->closeCursor();

		$columnData = array(
			'oid' => $data['oid'],
			'partner' => $data['partner'],
		);
		$columnSql = "set partner = :partner";
		if (!empty($data['currency'])) {
			$columnData['currency'] = $data['currency'];
			$columnSql .= ", currency = :currency";
		}
		if (!empty($data['total_fee'])) {
			$columnData['total_fee'] = $data['total_fee'];
			$columnSql .= ", total_fee = :total_fee";
		}

		if ($row) {
			$action = "update";
			$sql = "update t_order_extra $columnSql where oid = :oid";
			$stmt = $conn->prepare($sql);
			$stmt->execute($columnData);
		}
		else {
			$action = "insert";
			$sql = "insert into t_order_extra $columnSql, oid = :oid, create_time = unix_timestamp()";
			$stmt = $conn->prepare($sql);
			$stmt->execute($columnData);
		}

		return $action;
	}

	public function save_order_payment_log($data) {
		if (empty($data) || empty($data['oid']) || empty($data['partner']) ||
			empty($data['out_trade_no']) || empty($data['payment_type']) ||
			empty($data['payment_source'])
		) {
			return FALSE;
		}

		$columnData = array(
			'oid' => $data['oid'],
			'partner' => $data['partner'],
			'out_trade_no' => $data['out_trade_no'],
			'payment_type' => $data['payment_type'],
			'payment_source' => $data['payment_source'],
		);
		$columnSql = "set partner = :partner, out_trade_no = :out_trade_no, payment_type = :payment_type, payment_source = :payment_source";
		if (!empty($data['trade_no'])) {
			$columnData['trade_no'] = $data['trade_no'];
			$columnSql .= ", trade_no = :trade_no";
		}
		if (!empty($data['currency'])) {
			$columnData['currency'] = $data['currency'];
			$columnSql .= ", currency = :currency";
		}
		if (!empty($data['total_fee'])) {
			$columnData['total_fee'] = $data['total_fee'];
			$columnSql .= ", total_fee = :total_fee";
		}

		$conn = $this->pdo;
		$sql = "insert into t_order_payment_log $columnSql, oid = :oid, create_time = unix_timestamp()";
		$stmt = $conn->prepare($sql);
		return $stmt->execute($columnData);
	}

	public function update_order_payment_log_status($outTradeNo, $status) {
		if (empty($outTradeNo) || empty($status)) {
			return FALSE;
		}

		$columnData = array(
			'out_trade_no' => $outTradeNo,
			'status' => $status,
		);

		$conn = $this->pdo;
		$sql = "UPDATE t_order_payment_log SET status = :status WHERE out_trade_no = :out_trade_no";
		$stmt = $conn->prepare($sql);
		return $stmt->execute($columnData);
	}

	public function fetch_pending_payment_log() {
		//取15分钟之前，支付状态为0的支付交易; 用户进入支付流程15分钟后，才发起主动查询; 30天前的交易不作处理
		$sql = "SELECT * FROM t_order_payment_log WHERE status = 0 AND create_time >= (unix_timestamp()-30*24*60*60) AND create_time < (unix_timestamp()-15*60) ORDER BY create_time DESC";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function dao_insert_homestay_booking($info) {
		$sql = <<<SQL
INSERT INTO t_homestay_booking
(uid, nid, room_name, uname, umail, mail_subject, mail_body, guest_name,
guest_number, guest_date, guest_checkout_date, guest_days, guest_etc,
guest_mail, guest_telnum, guest_wechat, room_num, mid, guest_uid, client_ip,
create_time, last_modify_date, province, city_name, self_service, coupon,
rev_percent, speed_room, dest_id, campaign_code, zzkcamp, zfansref, guest_child_number,
guest_child_age, exchange_rate, room_price_count_check, order_source,customer_id)
VALUES(:uid, :nid, :room_name, :uname, :umail, :mail_subject, :mail_body,
:guest_name, :guest_number, :guest_date, :guest_checkout_date, :guest_days,
:guest_etc, :guest_mail, :guest_telnum, :guest_wechat, :room_num, :mid,
:guest_uid, :client_ip, :create_time, :last_modify_date, :province,
:city_name, :self_service, :coupon, :rev_percent, :speed_room, :dest_id,
:campaign_code, :zzkcamp, :zfansref, :guest_child_number, :guest_child_age, :exchange_rate,
:room_price_count_check, :order_source, :customer_id)
SQL;
		$stmt = $this->pdo->prepare($sql);
		if ($stmt->execute($info)) {
			return $this->pdo->lastInsertId();
		}
		return FALSE;
	}

	public function dao_insert_homestay_booking_addtion($order_id, $addtion) {
		$sql = 'INSERT INTO LKYou.t_homestay_booking_addition (
            order_id,
            guest_line_id,
            guest_language,
            baoche_id,
            baoche_price,
            baoche_price_cn,
            other_service_id,
            other_service_price,
            other_service_price_cn,
            guest_first_name,
            guest_last_name,
            user_deleted,
            paytype,
            no_show
        )VALUES (
            :order_id,
            :guest_line_id,
            :guest_language,
            :baoche_id,
            :baoche_price,
            :baoche_price_cn,
            :other_service_id,
            :other_service_price,
            :other_service_price_cn,
            :guest_first_name,
            :guest_last_name,
            :user_deleted,
            :paytype,
            :no_show
        )';
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute(array(
			'order_id' => $order_id,
			'guest_line_id' => $addtion['guest_line_id'],
			'guest_language' => $addtion['guest_language'],
            'baoche_id' => $addtion['baoche_id'],
            'baoche_price' => $addtion['baoche_price'],
            'baoche_price_cn' => $addtion['baoche_price_cn'],
            'other_service_id' => $addtion['other_service_id'],
            'other_service_price' => $addtion['other_service_price'],
            'other_service_price_cn' => $addtion['other_service_price_cn'],
			'guest_first_name'=>$addtion['guest_first_name'],
			'guest_last_name'=>$addtion['guest_last_name'],
			'user_deleted'=> ($addtion['user_deleted'] ? $addtion['user_deleted'] : 0),
            'paytype' => $addtion['paytype'],
            'no_show' => $addtion['no_show'],
		));
	}

    public function insert_homestay_booing_service($order_id ,$service) {
        
        $pdo_values = array();
        foreach($service as $k=>$v) {
            $values_mark[] = "(" . Util_Common::placeholders("?", 7) . ")";
            $pdo_values = array_merge($pdo_values, array_values($v) );
        }

        $sql = "insert into t_homestay_booking_service (bid, package_id, service_category, num, price, price_cn, create_time) values ".implode(",", $values_mark);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($pdo_values);
    }

    public function get_homestay_booking_service($order_id) {
        $sql = "select * from t_homestay_booking_service where bid = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($order_id));
        return $stmt->fetchAll();
    }

	public function get_order_addition($order_id) {
		$sql = 'SELECT * FROM LKYou.t_homestay_booking_addition WHERE order_id=:order_id';
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array('order_id' => $order_id));
		return $stmt->fetch();
	}

    public function get_order_addition_by_hash($hash_id) {
        $sql = "select book.id as origin_id, addl.* from LKYou.t_homestay_booking book left join LKYou.t_homestay_booking_addition addl on book.id = addl.order_id where book.hash_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($hash_id));
        return $stmt->fetch();
    }

	public function get_pay_price($order_id) {
		$sql = 'SELECT * FROM LKYou.t_order_extra WHERE oid=:order_id';
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array('order_id' => $order_id));
		return $stmt->fetch();
	}
	public function acquire_order_homestay_booking_service($order_id){
		$sql="select * from t_homestay_booking_service WHERE  bid=?";
		$stmt=$this->pdo->prepare($sql);
		$stmt->execute(array($order_id));
		return $stmt->fetchAll();
	}

	public function acquire_max_homestay_booking_id() {
		$sql = "SELECT max(id) AS id FROM t_homestay_booking";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	public function pre_pay_orders_by_roomid($nid) {
		$sql = "SELECT guest_date, guest_days, room_num FROM t_homestay_booking WHERE nid = ? AND status = 4 AND nid <> 0";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($nid));
		return $stmt->fetchAll();
	}

	public function acquire_order_certify_by_uid($uid) {
		$sql = <<<SQL
SELECT ifnull(hash_id,id) AS order_id, uname, guest_number, guest_name, total_price,total_price_tw, guest_child_number, guest_date, guest_checkout_date
FROM LKYou.t_homestay_booking
WHERE status IN (2, 6) AND guest_checkout_date >= now() AND guest_uid = ?
ORDER BY guest_date
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchAll();
	}

	public function acquire_order_base_certify_by_uid($uid) {
		$sql = <<<SQL
SELECT ifnull(hash_id,id) AS order_id,uid, uname, guest_number, guest_name, total_price,total_price_tw, guest_child_number, guest_date, guest_checkout_date ,
guest_days,guest_telnum,umail,room_num
FROM LKYou.t_homestay_booking
WHERE status IN (2, 6) AND guest_checkout_date >= now() AND guest_uid = ?
ORDER BY guest_date
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchAll();
	}

	public function acquire_condition_order_by_oid($oid) {
		$sql = "SELECT id, guest_uid, guest_date, guest_mail, uname, guest_name, total_price_tw FROM t_homestay_booking WHERE status IN (2, 6) AND id = ?";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($oid));
		return $stmt->fetch();
	}

	public function acquire_exist_refund_order_by_oid($oid) {
		$sql = "SELECT count(id) FROM LKYou.t_refund WHERE bid = ?";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($oid));
		$result = $stmt->fetchColumn();
		if ($result > 0) {
			return TRUE;
		}
		return FALSE;
	}

	public function acquire_refund_status_by_oid($oid) {
		$sql = "SELECT refund_status FROM LKYou.t_refund WHERE bid = ?";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($oid));
		$result = $stmt->fetchColumn();
		return $result;
	}

	public function acquire_customer_admin_uid_by_email($email) {
		$sql = "SELECT first_admin_uid FROM t_customer WHERE email = ?";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($email));
		return $stmt->fetchColumn();
	}

	public function acquire_user_name_by_admin_uid($uid) {
		$sql = "SELECT name FROM drupal_users WHERE uid = ?";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchColumn();
	}

	public function acquire_homestay_booking_trac_by_bid($bid) {
		$sql = "SELECT update_date FROM log_homestay_booking_trac WHERE status = 2 AND bid = ? ORDER BY create_date DESC";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($bid));
		return $stmt->fetchColumn();
	}

	public function insert_refund_by_info($info) {
		$sql = "INSERT INTO t_refund(bid, client_ip, create_time, guest_uid, guest_name, guest_mail, guest_telnum, refund_content, sales_date, sales_name) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($info);
	}

	public function insert_homestay_trac_by_info($info) {
		$sql = "INSERT INTO log_homestay_booking_trac(bid, status, content, create_date, admin_uid, client_ip, intro, price_tw) VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($info);
	}

	public function get_order_byphone_without_filter($phone) {
		$sql = "SELECT * FROM t_homestay_booking WHERE guest_telnum like '%". $phone ."'";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function get_order_byphone($phone) {
		$sql = "SELECT * FROM t_homestay_booking WHERE status IN (2, 6) AND guest_date > now() AND guest_telnum = ?";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($phone));
		return $stmt->fetchAll();
	}

	public function get_order_by_email($email, $limit = 50, $offset = 0) {
		$sql = <<<SQL
SELECT id,hash_id,url_code,uname,room_name,total_price,guest_date,guest_checkout_date,status
FROM t_homestay_booking
WHERE guest_mail = :guest_mail
ORDER BY id DESC
LIMIT :limit
OFFSET :offset
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->bindParam(':guest_mail', $email, PDO::PARAM_STR);
		$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function get_order_by_guest_uid($uid, $limit = 50, $offset = 0) {
		$sql = <<<SQL
SELECT id,hash_id,url_code,uname,room_name,total_price,guest_date,guest_checkout_date,status
FROM t_homestay_booking
WHERE guest_uid = :guest_uid
ORDER BY id DESC
LIMIT :limit
OFFSET :offset
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->bindParam('guest_uid', $uid, PDO::PARAM_STR);
		$stmt->bindParam('limit', $limit, PDO::PARAM_INT);
		$stmt->bindParam('offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function count_order_by_guest_uid($uid) {
		$sql = "SELECT count(id) FROM t_homestay_booking WHERE guest_uid = :guest_uid";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->bindParam('guest_uid', $uid, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	public function update_homestay_booking($sql, $info) {
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($info);
	}

	public function get_homestay_booking_by_hash_id($hash_id) {
		$sql = <<<SQL
SELECT *
FROM t_homestay_booking
WHERE id>500000 AND hash_id = :hash_id
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->bindParam(':hash_id', $hash_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}

    public function get_multi_homestay_booking_by_hash_id($hash_ids) {
        $sql = "select * from t_homestay_booking where id > 500000 and hash_id in (".Util_Common::placeholders("?", count($hash_ids)).")";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array_values($hash_ids));
        return $stmt->fetchAll();
    }

	public function get_order_id_by_hash_id($hash_id) {
		$sql = <<<SQL
SELECT id
FROM t_homestay_booking
WHERE id>500000 AND hash_id = :hash_id
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->bindParam(':hash_id', $hash_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	public function get_homestay_booking_by_urlcode($urlcode) {
		$sql = <<<SQL
SELECT *
FROM t_homestay_booking
WHERE url_code = :urlcode
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->bindParam(':urlcode', $urlcode, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function get_homestay_booking_by_id($id) {
		$sql = <<<SQL
SELECT *
FROM t_homestay_booking
WHERE id = :id
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}


	public function get_order_info_byid($id) {
		$sql = "SELECT * FROM t_homestay_booking WHERE id = ?";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($id));
		return $stmt->fetch();
	}

	public function update_order_info_byid($order_id, $coupon) {
		$sql = "UPDATE t_homestay_booking SET coupon=? WHERE id = ?";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute(array($coupon, $order_id));
	}

	public function get_order_list_byuid($uid) {
		$sql = "SELECT * FROM t_homestay_booking WHERE guest_uid = ? AND status IN (2,6) ORDER BY id DESC";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchAll();
	}

	public function get_order_list_by_uid_rid($uid, $rid) {
		$sql = "SELECT * FROM t_homestay_booking WHERE guest_uid = ? AND nid=? AND status IN (2,6) ORDER BY id DESC";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($uid, $rid));
		return $stmt->fetchAll();

	}


	public function get_no_order_list_bybnbids($uids) {
		$uid_string = implode(",", $uids);
		$sql = "SELECT * FROM t_homestay_booking WHERE uid IN (?) AND status IN (0,1,2,4,6)";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($uid_string));
		return $stmt->fetchAll();
	}

	public function get_order_list_bybnb($uids, $params) {
		$condition = "";

		// 入住日期
		if ($params['checkin'] && $params['checkout'] && $params['checkin'] <= $params['checkout']) {
			$condition .= " and unix_timestamp(book.guest_date) >= '" . $params['checkin'] . "' and unix_timestamp(book.guest_checkout_date) <= '" . $params['checkout'] . "'";
		}

		// 更新日期
		if ($params['update_start'] && $params['update_end'] && $params['update_start'] <= $params['update_end']) {
			$condition .= " and unix_timestamp(book.update_date) >= '" . $params['update_start'] . "' and unix_timestamp(book.update_date) <= '" . $params['update_end'] . "'";
		}

		// 状态
		if (!empty($params['status'])) {
			$condition .= " and book.status in (" . implode($params['status']) . ")";
		}
		$limit = $condition ? "" : " limit 100";

		$sql = "select book.*,addl.guest_last_name,addl.guest_first_name from t_homestay_booking book left join t_homestay_booking_addition addl on book.id = addl.order_id where book.uid in (".implode(",", $uids).") " . $condition . " order by book.id " . $limit;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();

	}

	public function get_same_order_byphoneuid($phone, $uid) {
		if (preg_match('/^\+86\d+/', $phone) == 1) {
			$phone1 = $phone;
			$phone2 = substr($phone, 3);
		}
		else {
			$phone1 = '+86' . $phone;
			$phone2 = $phone;
		}
		$sql = "SELECT * FROM t_homestay_booking WHERE (guest_telnum = ? OR guest_telnum = ?) AND guest_uid != ? AND status IN (2,6) LIMIT 1";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($phone1, $phone2, $uid));
		return $stmt->fetch();
	}

	public function add_cancel_reocrd($params) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "INSERT INTO t_order_cancel (order_id, c_type, c_id, content, create_date) VALUES (?,?,?,?,?)";
		$stmt = $pdo->prepare($sql);
		return $stmt->execute(array(
			$params['order_id'],
			$params['c_type'],
			$params['c_id'],
			$params['content'],
			time()
		));
	}


	public function get_pendingOrder_by_uid($uid) {
		$sql = "SELECT count(*) FROM t_homestay_booking WHERE uid = ?  AND status IN (0,1)";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchColumn();

	}


	public function get_order_list_byguestuid($uid) {
		$sql = "SELECT * FROM t_homestay_booking WHERE guest_uid = ? ORDER BY id DESC";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchAll();
	}
    public function  get_order_by_guest_homestay($homestay_uid,$guest_uid){
        $sql = "SELECT * FROM t_homestay_booking WHERE uid=? AND guest_uid = ? limit 1";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($homestay_uid,$guest_uid));
        return $stmt->fetchAll();
    }

    public function check_order_by_guest_multihomestay($homestay_uid, $guest_uid) {
        $sql = "SELECT * FROM t_homestay_booking WHERE uid in (".Util_Common::placeholders("?", count($homestay_uid)).") and guest_uid = ? and status in (2,6) and unix_timestamp(guest_checkout_date) < ?";
        $stmt = $this->slave_pdo->prepare($sql);
        $pdo_value = array_values($homestay_uid);
        $pdo_value[] = $guest_uid;
        $pdo_value[] = time();
        $stmt->execute($pdo_value);
        return $stmt->fetchAll();
    }


	public function get_AppPay_count($uid) {
		//$sql = "select count(*) from t_homestay_booking where payment_source like '%ios%'or '%android%' and guest_mail=? and status in (2,6)";

		$sql = "SELECT count(*) FROM t_homestay_booking WHERE (payment_source  LIKE '%ios%'OR  payment_source  LIKE '%android%'   OR payment_type IN('iPhone_alipay_global','iPhone_wechatpay' ,'Android_alipay','iPhone_alipay') ) AND guest_uid=? AND status IN (2,6) ";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchColumn();

	}

	public function get_same_order_byphoneuid_apppay($phone, $uid) {
		if (preg_match('/^\+86\d+/', $phone) == 1) {
			$phone1 = $phone;
			$phone2 = substr($phone, 3);
		}
		else {
			$phone1 = '+86' . $phone;
			$phone2 = $phone;
		}
		$sql = "SELECT * FROM t_homestay_booking WHERE (guest_telnum = ? OR guest_telnum = ?) AND guest_uid != ? AND status IN (2,6) AND (payment_source  LIKE '%ios%'OR  payment_source  LIKE '%android%'   OR payment_type IN('iPhone_alipay_global','iPhone_wechatpay' ,'Android_alipay','iPhone_alipay')) LIMIT 1";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($phone1, $phone2, $uid));
		return $stmt->fetch();
	}

	public function get_order_by_uid_homestay_id($uid, $homestay_id) {
		$sql = "select * from t_homestay_booking where uid = $homestay_id and guest_uid = $uid";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetch();
	}

	public function get_last_order_by_uid($uid) {
		$sql = "select * from t_homestay_booking where uid = $uid order by create_time desc limit 1";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetch();
	}
	public function get_last_pending_order_by_uid($uid){
		$sql = "select * from t_homestay_booking where uid = $uid and status in (0,1) order by create_time desc limit 1";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetch();
	}

	public function get_order_list_by_where($where, $order, $limit) {
		if (!$where) {return array();}
		$sql = "SELECT * FROM LKYou.t_homestay_booking WHERE " . $where . " " . $order . " " . $limit;
        $stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

    public function get_order_transfer_info($oid){
        $sql = "select * from LKYou.t_paypal_queue_email where INSTR( oids, $oid ) > 0 ";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function get_order_list_count_by_where($where) {
        if (!$where) {
            return array();
        }
        $sql = "SELECT count(*) as num FROM t_homestay_booking WHERE " . $where ;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

	public function get_orderremit_info($orderid) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$sql = "SELECT * FROM log_homestay_booking_trac WHERE status=6 AND bid = ? ORDER BY tid DESC LIMIT 1";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array($orderid));
		return $stmt->fetch();
	}

    public function get_days_booking_by_huids($uids, $time, $limit=null) {
        $sql = "select * from t_homestay_booking where uid in (".Util_Common::placeholders("?", count($uids)).") and create_time > ? order by create_time desc";
        $sql = $limit ? $sql . " limit $limit" : $sql;
        $stmt = $this->slave_pdo->prepare($sql);
        $pdo_value = array_merge(array_values($uids), array($time));
        $stmt->execute($pdo_value);
        return $stmt->fetchAll();
    }

	public function get_order_log_for_host($order_id, $admin_uid) {
		$sql = <<<SQL
SELECT tid, content, create_date, admin_uid, intro, price_tw
FROM LKYou.log_homestay_booking_trac
WHERE bid=:order_id
AND admin_uid = :admin_uid
ORDER BY tid DESC
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array(
			'order_id' => $order_id,
			'admin_uid' => $admin_uid
		));
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function get_order_cancel_log($order_id) {
		$sql = <<<SQL
SELECT FROM_UNIXTIME(create_date) as create_time,intro
FROM LKYou.log_homestay_booking_trac
WHERE bid=:order_id AND status IN (3,5)
ORDER BY tid ASC
LIMIT 1
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array(
			'order_id' => $order_id
		));
		return $stmt->fetch();
	}

	public function get_order_remit_log($order_id) {
		$sql = <<<SQL
SELECT FROM_UNIXTIME(create_date) AS create_time,intro
FROM LKYou.log_homestay_booking_trac
WHERE bid=:order_id AND status IN (6)
ORDER BY tid ASC
LIMIT 1
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array(
			'order_id' => $order_id
		));
		return $stmt->fetch();
	}

    public function get_checked_out_booking_by_date($date) {
        $sql = "select * from t_homestay_booking where guest_checkout_date = ? and status in (2, 6)";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($date));
        return $stmt->fetchAll();
    }

    public function get_filter_order_list($email, $status, $keyword, $page_num=0, $guest_uid=0, $page_size=10) {
        if(empty($email) && empty($guest_uid)) return array();
        $sql = "select book.*,comment.id as comment_id from t_homestay_booking book force index(guest_mail, guest_uid) left join t_homestay_booking_addition addl on book.id = addl.order_id left join t_comment_info comment on book.hash_id = comment.order_id and comment.order_id > 0 ";
        $filter = array(); 
        $values = array();
        if($email && $guest_uid && $status != 'tobecomment') { // 带点评列表显示通过邮件查询的订单会导致点评的用户和下订单的用户不一致
            $filter[] = " (book.guest_mail = ? or book.guest_uid = ? ) ";
            $values[] = $email;
            $values[] = $guest_uid;
        } 
        elseif ( !empty($guest_uid)) {
            $filter[] = " book.guest_uid = ? ";
            $values[] = $guest_uid;
        }
        elseif ( !empty($email)) {
            $filter[] = " book.guest_mail = ? ";
            $values[] = $email;
        }

        if($keyword) {
            $simple = Util_ZzkCommon::tradition2simple($keyword);
            $tradition = Util_ZzkCommon::simple2tradition($keyword);
            $filter[] = "( book.uname like ? or book.id like ? or book.hash_id like ? or 
                           book.uname like ? or book.id like ? or book.hash_id like ?)";
            $values[] = "%".$simple."%";
            $values[] = "%".$simple."%";
            $values[] = "%".$simple."%";
            $values[] = "%".$tradition."%";
            $values[] = "%".$tradition."%";
            $values[] = "%".$tradition."%";
        }
        $order = " order by create_time desc ";

        //  sortbylastopera 按最后操作排序 total 全部  pending 待处理（确认中） executory 待支付 dealed 成交 tobeused待使用 tobecomment 待评价
        if($status == 'sortbylastopera') {
            $order = " order by update_date desc";
        }
        elseif($status == 'dealed') {
            $filter[] = " book.status in (2, 6) ";
        }
        elseif($status == 'executory' ) {
            $filter[] = " book.status = 4 ";
        }
        elseif($status == 'pending' ) {
            $filter[] = " book.status in (0, 1) ";
        }
        elseif($status == 'tobeused' ) {
            $filter[] = " book.status in (2, 6) ";
            $filter[] = " unix_timestamp(book.guest_date) > (unix_timestamp() - 24*60*60) ";
        }
        elseif($status == 'tobecomment') {
            $filter[] = " comment.id is null";
            $filter[] = " book.status in (2, 6) ";
            $filter[] = " book.guest_checkout_date < now() ";
        }

        $filter[] = '(addl.user_deleted = 0 or addl.user_deleted is null)';

        $sql = $sql . " where " . implode(" and ", $filter) . " group by book.id $order ";
        if($page_num > 0) {
            $offset = (int)($page_num-1)*$page_size;
            $limit = (int)$page_size;
            $sql = $sql . "limit $offset, $limit ";
        }

        $stmt = $this->slave_pdo->prepare($sql);

        $stmt->execute($values);
        $order_list = $stmt->fetchAll();
        $order_ids = array();
        foreach($order_list as $order) {
            $order_ids[] = $order['id'];
        }
        if(!empty($order_ids)) {
            $refund_bll = new Bll_Order_Refund();
            $refund_list = $refund_bll->get_refund_by_bids($order_ids);
        }

        $_refund_list = array();
        foreach($refund_list as $refund) {
            $_refund_list[$refund['bid']] = $refund;
        }

        $result = array();
        foreach($order_list as $order) {
            $data = $order;
            $data['refund_id'] = $_refund_list[$order['id']]['id'];
            $data['refund_status'] = $_refund_list[$order['id']]['refund_status'] ? $_refund_list[$order['id']]['refund_status'] : (int) -1;
            $result[] = $data;
        }

        return $result;

    }

    public function remove_order_byid($order_id) {
        $sql = "update LKYou.t_homestay_booking_addition set user_deleted = 1 where order_id = ? ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($order_id));
    }

    public function order_no_show($order_id) {
        $sql = "update LKYou.t_homestay_booking_addition set no_show = 1 where order_id = ? ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($order_id));
    }

    public function change_paytype($order_id, $paytype=0) {
        $sql = "update LKYou.t_homestay_booking_addition set paytype = ? where order_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($paytype, $order_id));
    }

    public function settle_order_litst_by_confirm($type, $from, $to=null) {
        $sql = <<<SQL
select book.* from 
    t_homestay_booking book 
    left join t_weibo_poi_tw poi on poi.uid = book.uid 
    left join log_homestay_booking_trac trac on book.id = trac.bid and trac.status = 2 and trac.only_save_comment = 0
where 
    book.status in (2, 6) 
    and poi.customer_level = ?
    and trac.create_date >= ? 
    and trac.create_date < ?
SQL;
        $stmt = $this->pdo->prepare($sql);
        $start = strtotime($from);
        if(!$to) {
            $to = $from;
        }
        $end = strtotime($to) + 24 * 60 * 60;
        $stmt->execute(array( $type, $start, $end ));
        return $stmt->fetchAll();
    }

    public function settle_order_list_by_checkin($type, $date) {
        $sql = <<<SQL
select book.* from 
    t_homestay_booking book 
    left join t_weibo_poi_tw poi on poi.uid = book.uid 
    left join log_homestay_booking_trac trac on book.id = trac.bid and trac.status = 2 and trac.only_save_comment = 0
where 
    book.status in (2, 6) 
    and poi.customer_level = ?
    and ( 
        book.guest_date = ?
        or ( book.guest_date < ? and trac.create_date >= ? )
    )
SQL;
        // 入住时间等于@date， 或者 入住时间小于@date而且成交日期 大于@date
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($type, $date, $date, strtotime($date)));
        return $stmt->fetchAll();
    }

    public function filter_booking_order($process, $name, $order_id, $create_from, $create_to, $expired, $page=1, $limit = 20) {
        $condition = array();
        $pdo_val = array();

        if($process == 'UNCHARGE' ) { // 待收取
            $condition[] = "addl.paytype = 1";
        }
        elseif($process == 'CHARGED' ) { // 收取成功
            $condition[] = "addl.paytype = 0";
        }
        elseif($process == 'INVALID' ) { // 卡有问题
            $condition[] = "map.card_type = 1";
        }

        if($name) {
            $condition[] = "book.uname like ?";
            $name = trim($name);
            $pdo_val[] = "%$name%";
        }

        if($order_id) {
            $condition[] = "book.hash_id like ?";
            $order_id = str_replace("#", "", $order_id);
            $order_id = trim($order_id);
            $pdo_val[] = "%$order_id%";
        }

        if($create_from) {
            $condition[] = "book.create_time >= ?";
            $pdo_val[] = strtotime($create_from);
        }
        if($create_to) {
            $condition[] = "book.create_time <= ?";
            $pdo_val[] = strtotime($create_from);
        }
        if(!$expired || $expired == "false") {
            $condition[] = "book.guest_date > subdate(current_date, 1)";
        }

        if(!$page) $page = 1;

        if(!empty($condition)) {
            $condition_str = "and " . implode(" and ", $condition);
        }
        $limit_str = " limit " . intval(($page-1)*$limit) . ", " . intval($limit);

        $sql = <<<SQL
select *, book.create_time as book_create_time, map.customer_id as card_customer_id from 
    t_homestay_booking book 
    left join t_homestay_booking_addition addl on book.id = addl.order_id
    left join t_stripecustomer_order_mapping map on map.order_id = book.hash_id
where
    book.order_source = 'booking'
    $condition_str
    order by book.guest_date
    $limit_str
SQL;

        $sqlCount = <<<SQL
select count(*)
    from
    t_homestay_booking book 
    left join t_homestay_booking_addition addl on book.id = addl.order_id
    left join t_stripecustomer_order_mapping map on map.order_id = book.hash_id
where
    book.order_source = 'booking'
    $condition_str
SQL;
        //try{
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute($pdo_val);
        $list = $stmt->fetchAll();
        //}catch(Exception $e) {
        //    print_r($e->getMessage());
        //}

        if($_GET['test']=='leon') {
            print_r($sql);
            print_r($pdo_val);
        }
        $stmt2 = $this->slave_pdo->prepare($sqlCount);
        $stmt2->execute($pdo_val);
        $count = $stmt2->fetchColumn();
        $pager['totalCount'] = $count;
        $pager['current'] = $page;

        if($count > $page*$limit) {
            $pager['hasNext'] = 1;
        }else{
            $pager['hasNext'] = 0;
        }

        if($page > 1) {
            $pager['hasPrev'] = 1;
        }else{
            $pager['hasPrev'] = 0;
        }

        $pager['prevPage'] = $page-1;
        $pager['nextPage'] = $page+1;
        $pager['totalPages'] = ceil($count / $limit);

        return array(
            'list'  => $list,
            'pager' => $pager,
        );
    }

    public function booking_order_by_created($time) {
        $sql = "select * from t_homestay_booking where order_source = 'booking' and create_time > ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($time));
        return $stmt->fetchAll();
    }

    public function booking_order_by_checkin($date) {
        $sql = "select * from t_homestay_booking where order_source = 'booking' and guest_date = ? ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($date));
        return $stmt->fetchAll();
    }

    public function stripe_customer_by_order_id($order_id) {
        $sql = "select * from LKYou.t_stripecustomer_order_mapping where order_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($order_id));

        return $stmt->fetch();
    }

    public function create_stripe_customer($order_id, $customer_id, $card_type=0) {
        $sql = "insert into LKYou.t_stripecustomer_order_mapping (order_id, customer_id, card_type, create_time) values (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(array($order_id, $customer_id, $card_type, time()));
    }

    public function verified_stripe_customer_id($order_id, $customer_id) {
        $sql = "update LKYou.t_stripecustomer_order_mapping set customer_id = ? card_type = 0 where order_id = ? ";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(array($customer_id, $order_id));
    }

}
