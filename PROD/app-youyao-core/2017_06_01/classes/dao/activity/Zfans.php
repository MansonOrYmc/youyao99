<?php
apf_require_class("APF_DB_Factory");

class Dao_Activity_Zfans {
	private $pdo;
	private $slave_pdo;

	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");		
		$this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
	}

    public function get_zfans_by_uid($uid) {
        $sql = 'SELECT * FROM t_zfans_refer WHERE uid=:uid';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		return $stmt->fetch();
    }

    //检查用户是否为粉客
	public function check_user($uid, $email) {
		$sql = 'SELECT * FROM t_zfans_refer WHERE uid=:uid AND email=:email AND status=1 AND (role is null or role!="blogger")';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid, 'email' => $email));
		return $stmt->fetch();
	}

    public function check_blogger($uid) {
		$sql = 'SELECT * FROM t_zfans_refer WHERE uid=:uid AND role="blogger"';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		return $stmt->fetch();
    }

	public function check_wdaccount_exist($uid) {
		$sql = 'SELECT * FROM t_zfans_withdraw_account WHERE uid=:uid AND status = 1 ORDER BY id DESC LIMIT 1';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		return $stmt->fetch();
	}

	public function is_zfans($uid) {
		$sql = 'SELECT * FROM t_zfans_refer WHERE uid=:uid AND status=1';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		return $stmt->fetch();
	}

	public function stat_roomnights_total($uid) {
		$sql = 'SELECT sum(order_room_num*order_guest_days) rn FROM t_zfans_balance_log WHERE uid=:uid AND status=1 AND type = 1 and (amount+points) > 0';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		return $stmt->fetchColumn();
	}

	public function stat_roomnights_month($uid) {
		$beginThismonth = mktime(0, 0, 0, date('m'), 1, date('Y'));
		$sql = 'SELECT sum(order_room_num*order_guest_days) rn FROM t_zfans_balance_log WHERE uid=:uid AND status=1 AND type = 1 and (amount+points) > 0 and order_succ_time >= :ts';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid, 'ts' => $beginThismonth));
		return $stmt->fetchColumn();
	}

	public function stat_order_amount($uid) {
		$sql = 'SELECT sum(amount) FROM t_zfans_balance_log WHERE uid=:uid AND status=1 AND type > 0 AND amount > 0';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		return $stmt->fetchColumn();
	}

	public function stat_order_amounted($uid, $with_valid=false) {
        if($with_valid) {
	        $sql = 'SELECT sum(amount) FROM t_zfans_balance_log WHERE uid=:uid AND bonus_valid_time<unix_timestamp() AND status=1 AND type > 0';
        } else {
		    $sql = 'SELECT sum(amount) FROM t_zfans_balance_log WHERE uid=:uid AND status=1 AND type > 0';
        }
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		$amounted = $stmt->fetchColumn();
		if (!$amounted) {
			return FALSE;
		}

		$totalWithdraw = $this->withdraw_total_amount($uid);
		$withdraw_queue = $this->withdraw_queue_amount($uid);
        $withdraw_points = $this->withdraw_to_points($uid);
		if (($totalWithdraw + $withdraw_queue + $withdraw_points) > $amounted) {
			return 0;
		}
		else {
			$result = round(floatval($amounted) - floatval($totalWithdraw) - floatval($withdraw_queue) - floatval($withdraw_points), 2);
			return number_format($result, 2, '.', '');
		}
	}

    //收益记录
	public function stat_order($uid, $limit = 10, $offset = 0) {
		$sql = <<<SQL
SELECT a.*,b.uname,b.city_name,b.guest_date,b.guest_checkout_date FROM t_zfans_balance_log a LEFT JOIN t_homestay_booking b on a.oid=b.id WHERE a.uid=:uid AND a.type > 0 AND a.status = 1
ORDER BY a.id DESC
LIMIT :limit OFFSET :offset
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}
    //收益记录数量
    public function stat_order_num($uid) {
        $sql = 'SELECT count(id) FROM t_zfans_balance_log WHERE uid=:uid AND type > 0 AND status != -1';
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        return $stmt->fetchColumn();
    }


    //退订记录
    public function orderCancel($uid, $limit = 10, $offset = 0) {
        $sql = <<<SQL
SELECT * FROM t_zfans_balance_log WHERE uid=:uid AND type > 0 and status in (0, -1)
ORDER BY update_time DESC
LIMIT :limit OFFSET :offset
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    //退订记录数量
    public function orderCancelNum($uid) {
        $sql = 'SELECT count(id) FROM t_zfans_balance_log WHERE uid=:uid AND type > 0 and status=-1';
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        return $stmt->fetchColumn();
    }

    //提现记录
	public function withdraw_records($uid, $limit = 10, $offset = 0)
    {
		$sql = "SELECT * FROM t_zfans_balance_log WHERE uid = :uid AND type = -1 ORDER BY id DESC LIMIT :limit OFFSET :offset";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}

    //提现记录数量
    public function stat_withdraw_num($uid)
    {
        $sql = 'SELECT count(id) FROM t_zfans_balance_log WHERE uid=:uid AND type = -1';
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        return $stmt->fetchColumn();
    }

    //发展的粉客的成交记录
    public function recommTransaRecords($uid,$limit = 10, $offset = 0)
    {
        $sql = <<<SQL
select * from (select b.id,
b.uid,
b.uname,
b.weixin_id,
b.phone_num,
b.email, b.city_name,
date(from_unixtime(b.create_time)) zfans_ct,
date(from_unixtime(max(a.order_succ_time))) latest_ot,
count(distinct a.oid) orders, sum(a.order_room_num*a.order_guest_days) rn,
sum(amount) amount
from t_zfans_refer b
left join t_zfans_balance_log a
on (a.uid = b.uid and a.status = 1 and a.type = 1 and a.order_succ_time >= unix_timestamp('2015-05-01'))
where b.status = 1 and b.refer_uid=:uid
group by b.uid
order by orders desc, b.id asc) as c
LIMIT :limit OFFSET :offset
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function recommTransaNum($uid)
    {
        $sql = <<<SQL
select count(*) from (select b.id,
b.uid,
b.uname,
b.weixin_id,
b.phone_num,
b.email, b.city_name,
date(from_unixtime(b.create_time)) zfans_ct,
date(from_unixtime(max(a.order_succ_time))) latest_ot,
count(distinct a.oid) orders, sum(a.order_room_num*a.order_guest_days) rn,
sum(amount) amount
from t_zfans_refer b
left join t_zfans_balance_log a
on (a.uid = b.uid and a.status = 1 and a.type = 1 and a.order_succ_time >= unix_timestamp('2015-05-01'))
where b.status = 1 and b.refer_uid=:uid
group by b.uid
order by orders desc, b.id asc) as c
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        return $stmt->fetchColumn();
    }


    public function withdraw_date_check($uid, $date) {
		$sql = 'SELECT * FROM t_zfans_balance_log WHERE uid = :uid AND type = -1 AND status in (0,1) AND FROM_UNIXTIME(create_time,"%Y-%m") = :date';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array(
			'uid' => $uid,
			'date' => $date
		));
		return $stmt->fetchAll();
	}


	public function withdraw_total_amount($uid) {
		$sql = "SELECT sum(amount) FROM t_zfans_balance_log WHERE uid = :uid AND status = 1 AND type < 0 AND type != -3";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		$amount = $stmt->fetchColumn();
		return $amount ? $amount : 0;
	}

	public function withdraw_queue_amount($uid) {
		$sql = "SELECT sum(amount) FROM t_zfans_balance_log WHERE uid = :uid AND status = 0 AND type < 0 AND type != -3";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		$amount = $stmt->fetchColumn();
		return $amount ? $amount : 0;
	}

    public function withdraw_to_points($uid) {
        $sql = "SELECT sum(amount) FROM t_zfans_balance_log WHERE uid = :uid AND status = 1 AND type = -3";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        $amount = $stmt->fetchColumn();
        return $amount ? $amount : 0;
    }

	public function withdraw_apply($uid, $amount) {
		if ($uid <= 0 || $amount <= 0) {
			return false;
		}

		$availableAmount = $this->stat_order_amounted($uid);
		if ($availableAmount < $amount) {
			return false;
		}

		$sql = "INSERT INTO t_zfans_balance_log SET uid = :uid, amount = :amount, status=0, type = -1, create_time = unix_timestamp(),comments='用户申请'";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid, 'amount' => $amount));
		$id = (int)$this->pdo->lastInsertId();

		return $id;
	}

	public function wdaccount_get($uid) {
		$sql = "SELECT * FROM t_zfans_withdraw_account WHERE uid = :uid ORDER BY update_time DESC";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		$result = $stmt->fetchAll();
		$wda0 = array();
		$wda1 = array();

		foreach ($result as $row) {
			if (!empty($wda0) && !empty($wda1)) {
				break;
			}
			if (empty($wda0) && $row['status'] == 0) {
				$wda0 = $row;
			}
			if (empty($wda1) && $row['status'] == 1) {
				$wda1 = $row;
			}
		}

		return array('wda0' => $wda0, 'wda1' => $wda1);
	}

	public function check_wdaccount($account, $name) {
		$sql = 'SELECT * FROM t_zfans_withdraw_account WHERE zfb_account = :account AND zfb_name = :name AND status >= 0 LIMIT 1';
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array('account' => $account, 'name' => $name));
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function wdaccount_apply($uid, $values) {
		if ($uid <= 0 || empty($values)) return false;

		$sql = "INSERT INTO t_zfans_withdraw_account SET uid = :uid, zfb_account = :zfba, zfb_name = :zfbn, bank_master_name = :bank_master_name,
                bank_master_code = :bank_master_code, bank_branch_name = :bank_branch_name, bank_branch_code = :bank_branch_code, bank_account = :bank_account,
                bank_username = :bank_username , create_time = unix_timestamp()";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array(
            'uid' => $uid,
            'zfba' => $values['zfb_account'],
            'zfbn' => $values['zfb_name'],
            'bank_master_name' => $values['bank_master_name'],
            'bank_master_code' => $values['bank_master_code'],
            'bank_branch_name' => $values['bank_branch_name'],
            'bank_branch_code' => $values['bank_branch_code'],
            'bank_account' => $values['bank_account'],
            'bank_username' => $values['bank_username']
        ));
		$id = (int)$this->pdo->lastInsertId();

		return $id;
	}

	public function wdaccount_update($uid, $value) {
		$sql = <<<SQL
UPDATE t_zfans_withdraw_account SET zfb_account=:zfb_account AND zfb_name=:zfb_name AND update_time=unix_timestamp()
WHERE uid=:uid AND status = 1
SQL;
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute(array(
			'uid' => $uid,
			'zfb_name' => $value['$value'],
			'zfb_account' => $value['zfb_account']
		));
	}

	public function wdaccount_cancel($uid, $accound_id) {
		$sql = 'UPDATE t_zfans_withdraw_account SET status=-1 , update_time=now() WHERE uid=:uid AND id=:id';
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute(array(
			'uid' => $uid,
			'id' => $accound_id
		));
	}
/////////////////////////////////////////CouponPromotion//////////////////////////////
    //获取佣金转的优惠券列表
    public function get_bonus_coupon_list($uid,$limit=10,$offset=0)
    {
        $sql = <<<SQL
SELECT a.coupon,a.pvalue,date(from_unixtime(a.create_date)) create_date,a.expirydate,a.status, unix_timestamp() cur_time FROM t_coupons a inner join t_zfans_coupons b on (a.coupon = b.coupon and a.uid = :uid AND a.coupon_type = 1) ORDER BY b.create_time DESC
LIMIT :limit OFFSET :offset
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
		$results = array();
		foreach ($rows as $row) {
			$row['status_text'] = $row['status'] == 0 ? (strtotime($row['expirydate']) < $row['cur_time'] ? "已过期" : "可使用") : "已使用";
			$results[] = $row;
		}
		return $results;
    }
    //获取佣金转的优惠券数量
    public function total_Bonus_Num($uid)
    {
        $sql = <<<SQL
SELECT count(*) FROM t_coupons a inner join t_zfans_coupons b on (a.coupon = b.coupon and a.uid = :uid AND a.coupon_type = 1) ORDER BY b.create_time DESC
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        return $stmt->fetchColumn();
    }
    //获取佣金转的可使用优惠券的数量
    public function can_Use_Bonus_Num($uid,$nowtime)
    {
        $sql = <<<SQL
SELECT count(*) FROM t_coupons a inner join t_zfans_coupons b on (a.coupon = b.coupon and a.uid = :uid AND a.coupon_type = 1 and a.expirydate>'$nowtime' and a.status=0) ORDER BY b.create_time DESC
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        return $stmt->fetchColumn();
    }


    //未使用
	public function get_code($uid) {
		$sql = 'SELECT * FROM t_zfans_coupons WHERE uid = :uid AND coupon_quota>1 ORDER BY id DESC LIMIT 1';
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function get_zfans_coupons($uid) {
		$sql = 'SELECT * FROM t_zfans_coupons WHERE uid = :uid AND coupon_status = 1 AND coupon_quota > 1 ORDER BY id DESC LIMIT 2';
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function get_zfans_coupon_limits($type='coupon_amount') {
		$sql = 'SELECT * FROM t_zfans_limits WHERE type = :type ORDER BY id DESC LIMIT 1';
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array('type' => $type));
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}



	public function get_zfans_roomnights($uid) {
		$sql = "select sum(order_room_num*order_guest_days) rn from t_zfans_balance_log where status = 1 and type = 1 and uid = :uid";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		$row = $stmt->fetch(PDO::FETCH_OBJ);
		return $row ? $row->rn : 0;
	}


	public function generate_discount_coupon($uid,$email) {
		if (empty($uid) || empty($email)) {
			return FALSE;
		}

		$coupon_code = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 7);
		$lastused = date('Y-m-d');
		$datetime = date('Y-m-d h:m:s');
		$expire_date = date('Y-m-d', strtotime('+7 days'));

		try {
			$this->pdo->beginTransaction();
			$params = array(
				'uid' => $uid,
				'coupon' => $coupon_code,
				'lastused' => $lastused,
				'expiry_date' => $expire_date,
				'submittedby' => '',
				'success' => 0,
				'fail' => 0,
				'status' => 0,
				'create_date' => time(),
				'update_date' => $datetime,
				'pvalue' => 5,
				'locked' => 1,
				'ownner' => 'andrew',
				'coupon_type' => 2
			);
			$sql = <<<SQL
INSERT INTO LKYou.t_coupons
(uid,coupon,lastused,expirydate,submittedby,success,fail,status,create_date,update_date,pvalue,locked,ownner,coupon_type) VALUES
(:uid,:coupon,:lastused,:expiry_date,:submittedby,:success,:fail,:status,:create_date,:update_date,:pvalue,:locked,:ownner,:coupon_type)
SQL;
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute($params);
			$this->pdo->commit();
			return $this->get_code($uid);
		} catch (PDOException $e) {
			$this->pdo->rollBack();
			return FALSE;
		}
	}

	public function generate_code($uid,$email, $value, $quota, $coupon_percent=null) {
		if (empty($uid) || empty($value) || empty($quota)) {
			return FALSE;
		}
		#if ($this->get_zfans_roomnights($uid) < 10) {
		#	return FALSE;
		#}

		$coupon_code = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 7);
		$lastused = date('Y-m-d');
		$datetime = date('Y-m-d h:m:s');
		$expire_date = date('Y-m-d', strtotime('+1 year'));

		try {
			$this->pdo->beginTransaction();
			$params = array(
				'uid' => $uid,
				'coupon' => $coupon_code,
				'lastused' => $lastused,
				'expiry_date' => $expire_date,
				'submittedby' => '',
				'success' => 0,
				'fail' => 0,
				'status' => 0,
				'create_date' => time(),
				'update_date' => $datetime,
				'pvalue' => $value,
				'locked' => 1,
				'ownner' => 'andrew',
				'coupon_type' => 2
			);
			$sql = <<<SQL
INSERT INTO LKYou.t_coupons
(uid,coupon,lastused,expirydate,submittedby,success,fail,status,create_date,update_date,pvalue,locked,ownner,coupon_type) VALUES
(:uid,:coupon,:lastused,:expiry_date,:submittedby,:success,:fail,:status,:create_date,:update_date,:pvalue,:locked,:ownner,:coupon_type)
SQL;
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute($params);

			$params = array(
				'uid' => $uid,
				'email' => $email,
				'coupon' => $coupon_code,
				'coupon_quota' => $quota,
                'coupon_percent' => $coupon_percent,
				'expire_date' => $expire_date,
				'create_time' => time(),
				'update_time' => $datetime,
				'coupon_value' => $value
			);
			$sql = <<<SQL
INSERT INTO t_zfans_coupons
(uid,email,coupon,coupon_quota,expire_date,create_time,update_time,coupon_value,coupon_percent) VALUES
(:uid,:email,:coupon,:coupon_quota,:expire_date,:create_time,:update_time,:coupon_value,:coupon_percent)
SQL;
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute($params);
			$this->pdo->commit();
			return $this->get_code($uid);
		} catch (PDOException $e) {
			$this->pdo->rollBack();
			return FALSE;
		}
	}

	public function create_coupon_from_bonus($uid, $email, $coupon_value) {
		$owner = 'zfans_bonus';
		$expire_date = date("Y-m-", strtotime("+13 month")) . "01";

		try {
			$this->pdo->beginTransaction();

			$availableAmount = $this->stat_order_amounted($uid);
			if ($availableAmount < $coupon_value) {
				return FALSE;
			}
			$dao_coupon = new Dao_Coupons_CouponsInfo();
			$coupon_code = $dao_coupon->give_coupon($uid, $coupon_value, $expire_date, $owner);
			if (!$coupon_code) {
				$this->pdo->rollBack();
				return FALSE;
			}

			$sql = <<<SQL
INSERT INTO t_zfans_coupons (uid,email,coupon,coupon_type,coupon_value,create_time,expire_date,mail_sent)
VALUES (:uid,:email,:coupon,:coupon_type,:coupon_value,unix_timestamp(),:expire_date,1)
SQL;
			$stmt = $this->pdo->prepare($sql);
			$exeResult = $stmt->execute(array(
				'uid' => $uid,
				'email' => $email,
				'coupon' => $coupon_code,
				'coupon_type' => 1,
				'coupon_value' => $coupon_value,
				'expire_date' => $expire_date,
			));
			if (!$exeResult) {
				$this->pdo->rollBack();
				return FALSE;
			}

			$sql = "INSERT INTO t_zfans_balance_log (uid,type,amount,status,create_time,comments) VALUES (:uid,-2,:amount,1,unix_timestamp(),:comments)";
			$stmt = $this->pdo->prepare($sql);
			$exeResult = $stmt->execute(array(
				'uid' => $uid,
				'amount' => $coupon_value,
				'comments' => "bonus2coupon:$coupon_code",
			));
			if (!$exeResult) {
				$this->pdo->rollBack();
				return FALSE;
			}
			$this->pdo->commit();
			return array(
				'coupon_code' => $coupon_code,
				'coupon_value' => $coupon_value
			);
		} catch (PDOException $e) {
			$this->pdo->rollBack();
			return FALSE;
		}
	}

	public function is_zfans_already_applied($uid) {
		$sql = 'SELECT * FROM t_zfans_refer WHERE uid=:uid';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		return $stmt->fetch();
	}

	public function zfans_apply($params) { 
		if ($this->is_zfans_already_applied($params[':uid'])) {
            unset($params[':role']);
			$sql = "update t_zfans_refer set uid = :uid, email = :email, refer_uid = :refer_uid, create_time = :create_time, uname = :uname, phone_num = :phone_num, weixin_id = :weixin_id, city_name = :city_name, booking_info = :booking_info, join_info = :join_info, refer_info = :refer_info, source_info = :source_info, self_intro = :self_intro, promotion_info = :promotion_info, suggestion_info = :suggestion_info, advantage_info = :advantage_info, work_info = :work_info, blog = :blog, facebook = :facebook where uid = :uid";
		} else {
	    $sql = "insert into t_zfans_refer (uid, email, status, refer_uid, role, create_time, uname, phone_num, weixin_id, city_name, booking_info, join_info, refer_info, source_info, self_intro, promotion_info, suggestion_info, advantage_info, work_info, blog, facebook) values (:uid, :email,
				:status,
				:refer_uid,
                :role,
				:create_time,
				:uname,
				:phone_num,
				:weixin_id,
				:city_name,
				:booking_info,
				:join_info,
				:refer_info,
				:source_info,
				:self_intro,
				:promotion_info,
				:suggestion_info,
				:advantage_info,
				:work_info,
                :blog,
                :facebook
				) ";
		}
      $stmt = $this->pdo->prepare($sql);
      return $stmt->execute($params);
  }

}
