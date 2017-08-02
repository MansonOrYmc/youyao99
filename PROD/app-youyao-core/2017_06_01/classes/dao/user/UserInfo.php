<?php
apf_require_class("APF_DB_Factory");

class Dao_User_UserInfo {

	private $pdo;
	private $slave_pdo;
	private $one_pdo;
	private $one_slave_pdo;

	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("master");
	}

	public function getLikeUserNames($query) {
		$sql = "SELECT uid, name FROM drupal_users WHERE name like '".$query."%' order by name asc limit 20";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function get_comment_list($uid) {
		$sql = <<<SQL
SELECT  *
FROM t_homestay_booking
JOIN t_comment_info ON t_homestay_booking.id=t_comment_info.order_id
WHERE t_comment_info.uid = :uid
ORDER BY t_homestay_booking.id DESC
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}


	public function get_comment_hash_ids($uid) {
		$sql = <<<SQL
SELECT  t_homestay_booking.hash_id
FROM t_homestay_booking
JOIN t_comment_info ON t_homestay_booking.hash_id=t_comment_info.order_id
WHERE t_comment_info.uid = :uid
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function get_uid_by_pid($pid) {
		$sql = 'SELECT uid FROM drupal_users WHERE poi_id = :poi_id';
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array(':poi_id' => $pid));
		return $stmt->fetchColumn();
	}

	public function isUserZFansRefer($uid) {
        // 粉客下线
        return 0;
		$sql = "SELECT * FROM LKYou.t_zfans_refer WHERE uid = ? AND status = 1";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		$zfans = $stmt->fetch();

		return empty($zfans) ? 0 : 1;
	}

	public function get_user_info_by_email($email) {
		$email = trim($email);
		$sql = "SELECT * FROM drupal_users WHERE mail = ?";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($email));
		return $stmt->fetch();
	}

	public function get_user_info_by_phone_num($mobile_num) {
                $mobile_num = trim($mobile_num);
		$sql = "select uid, yyid, name, pass, mail, mail_verified, mobile_num, mobile_verified, wechat, weibo, tengqq, tel_num, access, login, picture, v_status, v_date, client_ip, last_client_ip, created, update_date from t_users where mobile_num = ? limit 1;";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array($mobile_num));
		return $stmt->fetch();
	}

	public function get_user_jiaotongzixun($uid) {
		$uid = trim($uid);
		$sql = "SELECT field__jiaotongzixun_value FROM drupal_field_data_field__jiaotongzixun WHERE entity_id = ?";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetch();
	}

	public function get_user_jiaotongtu($uid) {
		$uid = trim($uid);
		$sql = "SELECT field_jiaotongtu_fid AS fid FROM drupal_field_data_field_jiaotongtu WHERE entity_id = ?";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		$arr = $stmt->fetch();
		if (!empty($arr['fid'])) {
			if ($arr['fid'] < 200000) {
				$sql = "SELECT uri FROM drupal_file_managed WHERE fid = ?";
				$stmt = $this->one_slave_pdo->prepare($sql);
				$stmt->execute(array($arr['fid']));
				$arr = $stmt->fetch();
				$arr['uri'] = strtr($arr['uri'], array(
					'public://field/image[current-date:raw]/' => '',
					'public://' => ''
				));
				$arr['uri'] = 'http://img1.zzkcdn.com/' . $arr['uri'] . '-roompic.jpg';
			}
			else {
				$sql = "SELECT uri FROM LKYou.t_img_managed WHERE fid = ?";
				$stmt = $this->one_slave_pdo->prepare($sql);
				$stmt->execute(array($arr['fid']));
				$arr = $stmt->fetch();
				$arr['uri'] = 'http://img1.zzkcdn.com/' . $arr['uri'] . '/2000x1500.jpg-roompic.jpg';
			}
		}
		else {
			$arr = FALSE;
		}

		return $arr;
	}

	public function get_user_zhuyishixiang($uid) {
		$uid = trim($uid);
		$sql = "SELECT field_zhuyishixiang_value FROM drupal_field_data_field_zhuyishixiang WHERE entity_id = ?";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetch();
	}

	public function dao_update_user_fund_by_uid($fund, $uid) {
		$sql = "UPDATE drupal_users SET fund = ? WHERE uid = ?";
		$stmt = $this->one_pdo->prepare($sql);
		return $stmt->execute(array($fund, $uid));
	}

	public function dao_get_user_fund_by_uid($uid) {
		$sql = "SELECT fund FROM drupal_users WHERE uid = ?";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchColumn();
	}

	public function get_user_by_phone_num($mobile_num) {
		$sql = "select uid, yyid, name, pass, mail, mail_verified, mobile_num, mobile_verified, wechat, weibo, tengqq, tel_num, access, login, picture, v_status, v_date, client_ip, last_client_ip, created, update_date from t_users where mobile_num = ?  and v_status = 1 limit 1;";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array( "$mobile_num" ));
		return $stmt->fetch();
	}

	public function get_user_by_yyid($yyid) {
		$sql = "select uid, yyid, name, pass, mail, mail_verified, mobile_num, mobile_verified, wechat, weibo, tengqq, tel_num, access, login, picture, v_status, v_date, client_ip, last_client_ip, created, update_date from t_users where yyid = ?  and v_status = 1 limit 1;";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array( "$yyid" ));
		return $stmt->fetch();
	}

	public function set_user_by_yyid($yyid, $data) {
                $data['yyid'] = $yyid;
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($data, true));
		$sql = "update t_users set name = :name, mail = :mail, mail_verified = :mail_verified, wechat = :wechat, weibo = :weibo, tengqq = :tengqq, tel_num = :tel_num, picture = :picture where yyid = :yyid ;";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($data);
	}

	public function get_agent_by_yyid($u_yyid) {
		$sql = "select aid, yyid, u_yyid, truename, address, employer_company, expertise, identitycard, points, birthday, city, work, education, status, created, update_date from t_agent where u_yyid = ? limit 1;";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array( "$u_yyid" ));
		return $stmt->fetch();
	}

	public function get_agent_serve_scope($u_yyid) {
		$sql = "select h_yyid, count(*) drug_num from t_serve_scope where u_yyid = ? group by 1;";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array( "$u_yyid" ));
		return $stmt->fetchAll();
	}

	public function get_agent_hospital_drugs($u_yyid, $h_yyid) {
		$sql = "select d_yyid from t_serve_scope where u_yyid = ? and h_yyid = ? order by id desc;";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array( "$u_yyid", "$h_yyid" ));
		return $stmt->fetchAll();
	}

	public function get_agent_hospital_drug_active($u_yyid, $h_yyid, $d_yyid) {
		$sql = "select count(*) as c from t_serve_scope where status = 1 and u_yyid = ? and h_yyid = ? and d_yyid = ? order by id desc;";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array( "$u_yyid", "$h_yyid", "$d_yyid" ));
		return $stmt->fetchColumn();
	}

	public function get_drug($d_yyid) {
		$sql = "select yyid, name, e_name, specs, '' img_url from t_drug where yyid = ? and status = 1 limit 1;";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array( "$d_yyid" ));
		return $stmt->fetch();
	}

	public function set_agent_by_yyid($u_yyid, $data) {
                $data['u_yyid'] = $u_yyid;
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($data, true));
		$sql = "update t_agent set truename = :truename, address = :address, employer_company = :employer_company, expertise = :expertise, identitycard = :identitycard, birthday = :birthday, city = :city, work = :work, education = :education where u_yyid = :u_yyid ;";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($data);
	}

	public function add_agent_by_yyid($u_yyid, $data) {
                $data['u_yyid'] = $u_yyid;
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($data, true));
		$sql = "insert into t_agent (aid, yyid, u_yyid, truename, address, employer_company, expertise, identitycard, points, birthday, city, work, education, status, created, update_date) values(0, replace(upper(uuid()),'-',''), :u_yyid, :truename, :address, :employer_company, :expertise, :identitycard, 0, :birthday, :city, :work, :education, 1, unix_timestamp(), now());";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($data);
	}

	public function add_serve_scope($u_yyid, $data) {
                $data['u_yyid'] = $u_yyid;
                if(isset($data['yyid'])) unset($data['yyid']);
                if(isset($data['s_yyid'])) unset($data['s_yyid']);
		$sql = "insert into `t_serve_scope` (`id`, `yyid`, `u_yyid`, `d_yyid`, `h_yyid`, `status`, `datei`, `created`, `update_date`) values(0, replace(upper(uuid()),'-',''), :u_yyid, :d_yyid, :h_yyid, :status, :datei, :created, now());";
                $stmt = $this->pdo->prepare($sql);
                Logger::info(__FILE__, __CLASS__, __LINE__, "sql: $sql");
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($data, true));
                $res = $stmt->execute($data);
                /* */
                $last_id = $this->pdo->lastInsertId();
                //Logger::info(__FILE__, __CLASS__, __LINE__, "last_id: $last_id");
                $d_yyid = self::get_serve_scope_yyid_by_id($last_id);
                return $d_yyid;
	}

        private function get_serve_scope_yyid_by_id($id) {
                $sql = "select `yyid` from `t_serve_scope` where `id` = ? ;";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array("$id"));
                $yyid = $stmt->fetchColumn();
                if(!empty($yyid) && strlen($yyid) == 32){
                   //$yyid = '';
                }
                else{
                   $yyid = '';
                }
                return $yyid;
        }

	public function set_serve_scope($u_yyid, $s_yyid, $data) {
                $data['u_yyid'] = $u_yyid;
                $data['yyid'] = $s_yyid;
                if(isset($data['s_yyid'])) unset($data['s_yyid']);
                if(isset($data['created'])) unset($data['created']);
		$sql = "update `t_serve_scope` set `d_yyid` = :d_yyid, `h_yyid` = :h_yyid, `status` = :status, `datei` = :datei where `yyid` = :yyid and `u_yyid` = :u_yyid ;";
                Logger::info(__FILE__, __CLASS__, __LINE__, "sql: $sql");
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($data, true));
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($data);
	}
        
        public function get_serve_scope($u_yyid, $yyid) {
                $sql = "select `yyid`, `u_yyid`, `d_yyid`, `h_yyid`, `status`, `datei`, `created`, `update_date` from `t_serve_scope` where `yyid` = ?  and `u_yyid` = ?;";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array("$yyid", "$u_yyid"));
                $row = $stmt->fetch();
                if(isset($row['created'])) $row['created'] = date('y-m-d H:i:s', $row['created']);
                //Logger::info(__FILE__, __CLASS__, __LINE__, var_export($row, true));
                return $row;
        }
      
        public function get_serve_scope_list($u_yyid, $limit, $offset)
        {
            $sql = "select `yyid`, `d_yyid`, `h_yyid`, `created`, `update_date` from `t_serve_scope` where status = 1 and `u_yyid` = :u_yyid LIMIT :limit OFFSET :offset ;";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':u_yyid', $u_yyid, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $en = array();
            foreach($rows as $k=>$r){
              $r['created'] = isset($r['created']) ? date('Y-m-d H:i:s', $r['created']) : '';
              $en[$k] = $r;
            }

            return $en;
        }

        public function get_hospital_name($h_yyid)
        {
            $sql = "select `name` from `t_hospital` where status = 1 and `yyid` = :yyid limit 1;";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':yyid', $h_yyid, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn();
        }

        public function get_serve_scope_count($u_yyid)
        {
            $c = 0;
            $get_count_sql = "select count(*) from `t_serve_scope` where status = 1 and u_yyid = ? ;";
            $stmt = $this->pdo->prepare($get_count_sql);
            $stmt->execute(array("$u_yyid"));
            $c = $stmt->fetchColumn();
            return $c;
        }

	public function verify_user_access_token($yyid, $token) {
                $v = 0;
		$sql = "select uid, yyid, sid, client_ip, status, created, login_from from t_user_session where yyid = ?  and sid = ? limit 1;";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array( "$yyid", "$token" ));
		$row = $stmt->fetch();
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($row, true));
                if(!empty($row)){
                  $v = 1;
                }
                return $v; 
	}

	public function get_user_by_name_or_email($uname) {
		$sql = "SELECT uid, name, pass, mail, send_sms_telnum, status, dest_id FROM drupal_users WHERE name = ? OR mail = ?";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($uname, $uname));
		return $stmt->fetch();
	}


	public function get_user_by_uid($uid) {
		$sql = "SELECT uid, name, pass, mail, send_sms_telnum, status, dest_id,phone_num FROM one_db.drupal_users WHERE uid = ?";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetch();
	}


	public function dao_update_user_order_succ_by_uid($uid, $order_succ) {
		$sql = "UPDATE drupal_users SET order_succ= (order_succ + ?) WHERE uid=?";
		$stmt = $this->one_pdo->prepare($sql);
		return $stmt->execute(array($order_succ, $uid));
	}

	public function get_user_head_pic_by_uid($userID) {
		$sql = "SELECT picture,picture_version FROM drupal_users WHERE uid = ?";
		$stmt = $this->one_pdo->prepare($sql);
		$stmt->execute(array($userID));
		$result = $stmt->fetch();
		if ($result['picture_version']) {
			$picsql = "SELECT uri FROM t_img_managed WHERE fid = ?";
			$stmt = $this->pdo->prepare($picsql);
		}
		else {
			$picsql = "SELECT uri FROM drupal_file_managed WHERE fid = ?";
			$stmt = $this->one_pdo->prepare($picsql);
		}

		$stmt->execute(array($result['picture']));
		$pic = $stmt->fetchColumn();
		return $result['picture_version'] ? $pic . "/2000x1500.jpg" : $pic;
		/*
				$sql = "select filename from drupal_file_managed a, drupal_users b where b.picture = a.fid and b.uid = ?";
				$stmt = $this->one_slave_pdo->prepare($sql);
				$stmt->execute(array($userID));
				$result =  $stmt->fetchColumn();
				return $result;
		*/
	}

    public function get_user_head_pic_by_multi_uid($uids) {
        if(empty($uids)) return;
        $sql = "SELECT users.uid,img.uri FROM one_db.drupal_users users left join LKYou.t_img_managed img on users.picture = img.fid WHERE users.uid in (".Util_Common::placeholders("?", count($uids)).") and users.picture > 0";
        $stmt = $this->one_pdo->prepare($sql);
        $stmt->execute($uids);
        return $stmt->fetchAll();
    }

	public function acquire_user_list_count($email, $uid) {
		if (empty($email)) {
//            $sql = "SELECT
//(SELECT count(id) FROM t_homestay_booking WHERE   guest_uid=?) AS ordercount,
//(SELECT count(id) FROM t_homestay_booking WHERE   guest_uid=? AND status = 4) AS waitordercount,
//(SELECT count(id) FROM t_homestay_booking WHERE   guest_uid=? AND status IN (2,6) AND unix_timestamp(guest_date) < (unix_timestamp() + 24*60*60) ) AS tobeusedcount,
//(SELECT count(id) FROM t_homestay_booking WHERE status IN (2, 6) AND guest_checkout_date > now() AND guest_uid = ?) AS cretificateCount FROM dual";
//			$stmt = $this->slave_pdo->prepare($sql);
//			$stmt->execute(array($uid, $uid, $uid, $uid));
//			$results = $stmt->fetchAll();
//			$result = $results[0];
            $sql = "select *
                from 
                t_homestay_booking book 
                left join 
                t_homestay_booking_addition addl on book.id = addl.order_id 
                where book.guest_uid = ? and (addl.user_deleted = 0 or addl.user_deleted is null)";
            $stmt = $this->slave_pdo->prepare($sql);
            $stmt->execute(array($uid));
            $results = $stmt->fetchAll();
		}
		else {
//			$sql = "SELECT
//(SELECT count(id) FROM t_homestay_booking WHERE (guest_mail = ? OR guest_uid=?)) AS ordercount,
//(SELECT count(id) FROM t_homestay_booking WHERE (guest_mail = ? OR guest_uid=?) AND status = 4) AS waitordercount,
//(SELECT count(id) FROM t_homestay_booking WHERE (guest_mail = ? OR guest_uid=?) AND status IN (2,6) AND unix_timestamp(guest_date) < (unix_timestamp() + 24*60*60) ) AS tobeusedcount,
//(SELECT count(id) FROM t_homestay_booking WHERE status IN (2, 6) AND guest_checkout_date > now() AND guest_uid = ?) AS cretificateCount FROM dual";
//			$stmt = $this->slave_pdo->prepare($sql);
//			$stmt->execute(array($email, $uid, $email, $uid, $email, $uid, $uid));
//			$results = $stmt->fetchAll();
//			$result = $results[0];
            $sql = "select *
                from 
                t_homestay_booking book 
                left join 
                t_homestay_booking_addition addl on book.id = addl.order_id 
                where (book.guest_uid = ? or book.guest_mail = ?) and (addl.user_deleted = 0 or addl.user_deleted is null)";
            $stmt = $this->slave_pdo->prepare($sql);
            $stmt->execute(array($uid, $email));
            $results = $stmt->fetchAll();
		}

        $result['ordercount'] = 0;
        $result['pendingorder'] = 0;
        $result['waitordercount'] = 0;
        $result['tobeusedcount'] = 0;
        $result['cretificateCount'] =0;
        foreach($results as $row) {
            $result['ordercount']++;
            if(in_array($row['status'], array(0, 1))) 
                $result['pendingorder'] ++;
            if($row['status'] == 4) 
                $result['waitordercount'] ++;
            if(in_array($row['status'], array(2,6)) && strtotime($row['guest_date']) > (time() - 24 * 60 * 60 ) )
                $result['tobeusedcount'] ++;
            if(in_array($row['status'], array(2,6)) && strtotime($row['guest_checkout_date']) < time())
                $result['cretificateCount'] ++;
        }

		$sql = "SELECT (SELECT credit_value FROM drupal_users WHERE uid = :uid) AS creditcount";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		$results_two = $stmt->fetchAll();
		$result_two = $results_two[0];

		$returnResults = array();
		foreach ($result as $k => $v) {
			$returnResults[$k] = $v;
		}
		foreach ($result_two as $k => $v) {
			$returnResults[$k] = $v;
		}
		$bll_coupon = new Bll_Coupons_CouponsInfo();
		$returnResults['couponcount'] = strval(count($bll_coupon->get_canuse_conpons($uid)));
		$returnResults['msgcount'] = $this->msgcount($uid);
		$returnResults['isZFans'] = $this->isUserZFansRefer($uid);
		$returnResults['collections'] = $this->get_user_collections($uid);
		$commentinfo = new Bll_User_UserInfo();
		$returnResults['waitingcomment'] = count($commentinfo->get_waiting_for_comment_list($uid));
		$returnResults['unreadmsg'] = $this->get_unreadmsg_count($uid);

		$picture = $commentinfo->get_user_head_pic_by_uid($uid);
		$returnResults['headpic'] = empty($picture) ? Util_Avatar::dispatch_avatar($uid) : $picture;
		$dao_order_info = new Dao_Order_OrderInfo();
//		$pendingorder = $dao_order_info->get_pendingOrder_by_uid($uid);
//		$returnResults['pendingorder'] = $pendingorder;
		$returnResults['dest_id'] = $this->get_user_dest_id($uid);

		//判断是否显示 分享优惠
//        $bll_fcode  = new Bll_Activity_Fcode();
//        if($bll_fcode->check_share($uid)>0){
//            $returnResults['lxjj']=1;
//        }else{
//            $returnResults['lxjj']=0;
//        }

		//分享优惠活动下线
		$returnResults['lxjj'] = 0;

		return $returnResults;
	}

	public function msgcount($uid) {
		$sql = <<<SQL
SELECT count(DISTINCT recipient) FROM (
SELECT recipient
FROM
  (SELECT DISTINCT
  pm_message.mid,
     recipient
   FROM one_db.drupal_pm_message pm_message, one_db.drupal_pm_index pm_index
   WHERE pm_message.mid = pm_index.mid AND pm_message.author = :uid
         AND pm_index.deleted = 0
         AND NOT (pm_index.recipient = :uid OR pm_index.recipient = 0)
   UNION
   SELECT DISTINCT
   pm_message.mid,
     pm_message.author recipient
   FROM one_db.drupal_pm_index pm_index, one_db.drupal_pm_message pm_message
   WHERE pm_index.recipient = :uid AND pm_message.author != :uid
         AND pm_message.mid = pm_index.mid
  ) tmp
ORDER BY mid DESC) tmp2
SQL;
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		return $stmt->fetchColumn();
	}

//只统计三个月内未读的条数   by  lec
	public function get_unreadmsg_count($uid)
	{
		//兼容老版本api 读取之后不更新 is_new的bug  取mid大于10000000
//SELECT b.*  FROM drupal_pm_index as a join `drupal_pm_message` as b  on a.mid=b.mid    WHERE recipient=66 AND is_new=1 AND a.mid>1000000 and
//		b.`timestamp` > UNIX_TIMESTAMP( DATE_ADD(now(), INTERVAL -3 MONTH))
		$sql = "   SELECT count(*)  FROM drupal_pm_index as a join `drupal_pm_message` as b  on a.mid=b.mid    WHERE recipient=:uid AND is_new=1 AND
		b.`timestamp` > UNIX_TIMESTAMP( DATE_ADD(now(), INTERVAL -3 MONTH)) ; ";
		//$sql = "SELECT count(*) FROM drupal_pm_index WHERE recipient=:uid AND is_new=1 AND mid>1000000";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		return $stmt->fetchColumn();
	}


	public function check_user_admin($uid) {
		$sql = 'SELECT * FROM drupal_users_roles WHERE uid = :uid AND rid=3';
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		return $stmt->fetch();
	}


	public function isAdmin($uid) {
		$sql = "SELECT uid FROM drupal_users_roles WHERE uid = ? AND rid = 5";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetch();
	}

	public function get_user_role_id($uid) {
		$sql = 'SELECT rid FROM drupal_users_roles WHERE uid = :uid';
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		return $stmt->fetch();
	}

	public function getPid($uid) {
		$sql = "SELECT pid FROM t_weibo_poi_tw WHERE uid = ?";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		$pid = $stmt->fetchColumn();
		if (empty($pid)) {
			$pid = 0;
		}
		return $pid;
	}

	public function getUid($pid) {
		$sql = "SELECT uid FROM t_weibo_poi_tw WHERE pid = ?";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($pid));
		return $stmt->fetchColumn();
	}

	public function getUserInfo($uid) {
		$sql = "SELECT u.uid, u.name, loc.type_name AS region, poi.pid FROM one_db.drupal_users u, LKYou.t_loc_type loc, LKYou.t_weibo_poi_tw poi WHERE u.uid = poi.uid AND u.uid = ? AND loc.locid = substring_index(poi.loc_typecode, ',', -1)";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetch();
	}

	public function wholeUserInfo($uid) {
		$sql = "SELECT * FROM drupal_users WHERE uid = ?";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetch();
	}

	public function getPoiInfoFromMaster($uid) {
		$sql = "SELECT * FROM t_weibo_poi_tw WHERE uid = ?";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetch();
	}

	/**
	 * @param $user wholeUserInfo返回的user对象
	 * @return 新记录id, or 0 failed
	 */
	public function createPoiInfoForUser($user) {
		$poiInfo = array();
		$poiInfo['uid'] = $user['uid'];
		$poiInfo['user_name'] = $user['name'];
		$poiInfo['phone'] = $user['phone_num'];
		$poiInfo['email'] = $user['mail'];
		$poiInfo['poiid'] = "B209465DD26BA0F8479F_308_{$user['name']}_{$user['name']}";

		$sql = "INSERT INTO t_weibo_poi_tw (uid, user_name, phone, email, poiid) VALUES (:uid, :user_name, :phone, :email, :poiid)";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($poiInfo);
	}

	public function get_userinfo_by_ids($uids) {
	$placeholders = Util_Common::placeholders("?", count($uids));
	$sql = "select * from drupal_users where uid in (".$placeholders.") order by field(uid, $placeholders)";
	$stmt = $this->one_slave_pdo->prepare($sql);
	$stmt->execute(array_merge($uids, $uids));
	return $stmt->fetchAll();
}

	public function get_user_status_by_uid($uid) {
		$sql = "SELECT status FROM drupal_users WHERE uid = ?";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchColumn();
	}

    public function get_h_favorite($uid, $hid) {
        $sql = "SELECT * FROM t_collect WHERE uid=:uid AND hid=:hid AND status=1";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array(':uid'=>(int)$uid, ':hid'=>(int)$hid));
		return $stmt->fetchColumn();
    }

	public function get_hs_holiday($uid) {
		$sql = "SELECT take_holiday FROM t_homestay_take_holiday WHERE uid = ?";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		$take_holiday = $stmt->fetchColumn();
		return $take_holiday ? $take_holiday : 0;
	}

	public function get_user_dest_id($uid) {
		$sql = "SELECT dest_id FROM drupal_users WHERE uid = ?";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchColumn();
	}

	public function get_customer_by_email($email) {
		$email = trim($email);
		$sql = "SELECT * FROM t_customer WHERE email = ?";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($email));
		return $stmt->fetch();
	}

	public function update_new_customer_by_info($info, $isnew = TRUE) {
		if ($isnew) {
			$sql = "UPDATE t_customer SET sales_flag = ?, first_admin_uid = ?, last_admin_uid = ? WHERE id = ?";
		}
		else {
			$sql = "UPDATE t_customer SET last_order_date = ?,sales_flag = ?, first_admin_uid = ?, last_admin_uid = ? WHERE id = ?";
		}
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($info);
	}

	public function insert_new_customer_by_info($info) {
		$sql = <<<SQL
INSERT INTO t_customer
(name, pnum, days, email, mobile, status, client_ip, create_time,
last_modify_date, last_order_date, province, remark, pcnum, pcage, campaign_code,zzkcamp,zfansref)
VALUES (:name, :pnum, :days, :email, :mobile, :status, :client_ip, :create_time,
:last_modify_date, :last_order_date, :province, :remark, :pcnum, :pcage, :campaign_code,:zzkcamp,:zfansref)
SQL;
		$stmt = $this->pdo->prepare($sql);
		if ($stmt->execute($info)) {
			return $this->pdo->lastInsertId();
		}
		return FALSE;
	}

	public function load_user_info($uid) {
		if ($uid > 30000000) {
			$pid = $uid - 30000000;
			$user = new stdClass;
			$user->uid = $pid;
			$pidSQL = "SELECT pid, title, user_name, address, email, phone FROM t_weibo_poi_tw WHERE pid = ?";
			$pidStml = $this->slave_pdo->prepare($pidSQL);
			$pidStml->execute(array($pid));
			$poi = $pidStml->fetch();
			if ($poi) {
				$user->name = $poi['title'];    //$user->dest_id == 11 ? $poi[0]->title : zzk_translate($poi[0]->title, "zh-cn");
				$user->master = $poi['user_name'];    //$user->dest_id == 11 ? $poi[0]->user_name : zzk_translate($poi[0]->user_name, "zh-cn");
				$user->address = $poi['address'];    //$user->dest_id == 11 ? $poi[0]->address : zzk_translate($poi[0]->address, "zh-cn");
				$user->uid = $uid;
				$user->poi_id = $pid;
				$user->pid = $pid;
				$user->roles = array('5' => '商家');
				$user->mail = $poi['email'];
				$user->tel_num = $poi['phone'];
				$user->send_sms_telnum = $poi['phone'];
			}
		}
		else {
			$user = self::wholeUserInfo($uid);
			if ($user) {
				$user = (object) $user;
			}
			$poi_id = 771335;
		}

		if (isset($user->poi_id) && $user->poi_id > 0) {
			$poi_id = $user->poi_id;
		}

		$poiSQL = "SELECT pid, title, lon, lat, address, poi_pic, phone, tags, set_price, user_name, web_user_name, uid, esid, content, loc_typecode, type, map, images, email, website, checkin_num, rooms, customer_level, remark, status, rebate_num, rebate_remark, self_service, blank_account, paypal_account, cn_blank_account, alipay_account, rev_percent FROM LKYou.t_weibo_poi_tw WHERE pid = ?";
		$poiStmt = $this->slave_pdo->prepare($poiSQL);
		$poiStmt->execute(array($poi_id));
		$poi = $poiStmt->fetch();
		if ($poi) {
			$user->poi = (object) $poi;
		}

		if (isset($user->field_aboutme['und'][0]['safe_value'])) {
			$user->field_aboutme['und'][0]['safe_value'] = $user->field_aboutme['und'][0]['safe_value'];    //$user->dest_id == 11 ? $u->field_aboutme['und'][0]['safe_value'] : zzk_translate($u->field_aboutme['und'][0]['safe_value'], "zh-cn");
		}

		//checkin checkout
		$checkSQL = "SELECT uid, checkin_at, checkout_at, checkin_stop FROM one_db.drupal_checkin_time WHERE uid = ?";
		$checkStmt = $this->one_slave_pdo->prepare($checkSQL);
		$checkStmt->execute(array($uid));
		$check_in_out = $checkStmt->fetch();
		if ($check_in_out) {
			if ($check_in_out['checkin_at']) {
				$user->checkin_at = $check_in_out['checkin_at'];
			}
			if ($check_in_out['checkout_at']) {
				$user->checkout_at = $check_in_out['checkout_at'];
			}
			if ($check_in_out['checkin_stop']) {
				$user->checkin_stop = $check_in_out['checkin_stop'];
			}
		}

		if (isset($user->name)) {
			self::get_sns_data_by_uid($user->uid, $user); //获得微信等联系方式
			$user->name = $user->name;    //$user->dest_id == 11 ? $u->name : zzk_translate($u->name, "zh-cn");
			$user->address = $user->address;    //$user->dest_id == 11 ? $u->address : zzk_translate($u->address, "zh-cn");
			$user->tel_num = $user->tel_num;    //$user->dest_id == 11 ? $u->tel_num : zzk_translate($u->tel_num, "zh-cn");
			$user->take_holiday = self::get_hs_holiday($user->uid);
		}

		$user->dest_id = self::get_user_dest_id($user->uid);

		return $user;
	}

	public function insert_user($user) {
		$sql = 'INSERT INTO `drupal_users` (`uid`,`name`,`mail`,`phone_num`,`pass`,`signature_format`,`status`,`timezone`,`init`,`created`,`fund`,`dest_id`,`zfansref`,`send_sms_telnum`,`register_source`) VALUES '
			. ' (:uid,:name,:mail,:phone_num,:pass,:signature_format,:status,:timezone,:init,:created,:fund,:dest_id,:zfansref,:send_sms_telnum,:register_source)';
		$stmt = $this->one_pdo->prepare($sql);
		return $stmt->execute($user);
	}

	public function next_user_id() {
		$sql = 'SELECT MAX(uid) FROM drupal_users';
		$stmt = $this->one_slave_pdo->query($sql);
		$existing_id = $stmt->fetchColumn();

		$stmt = $this->one_pdo->prepare('INSERT INTO drupal_sequences () VALUES ()');
		$stmt->execute();
		$new_id = $this->one_pdo->lastInsertId();
		// This should only happen after an import or similar event.
		if ($existing_id >= $new_id) {
			// If we INSERT a value manually into the sequences table, on the next
			// INSERT, MySQL will generate a larger value. However, there is no way
			// of knowing whether this value already exists in the table. MySQL
			// provides an INSERT IGNORE which would work, but that can mask problems
			// other than duplicate keys. Instead, we use INSERT ... ON DUPLICATE KEY
			// UPDATE in such a way that the UPDATE does not do anything. This way,
			// duplicate keys do not generate errors but everything else does.
			$stmt = $this->one_pdo->prepare('INSERT INTO drupal_sequences (value) VALUES (:value) ON DUPLICATE KEY UPDATE value = value');
			$stmt->execute(array(':value' => $existing_id));
			$stmt = $this->one_pdo->prepare('INSERT INTO drupal_sequences () VALUES ()');
			$stmt->execute();
			$new_id = $this->one_pdo->lastInsertId();
		}
		try {
			$max_id = $this->one_slave_pdo->query('SELECT MAX(value) FROM drupal_sequences')
				->fetchColumn();
			// We know we are using MySQL here, no need for the slower db_delete().
			$stmt = $this->one_pdo->prepare('DELETE FROM drupal_sequences WHERE value < :value');
			$result = $stmt->execute(array('value' => $max_id));
		} catch (PDOException $e) {
		}
		return $new_id;
	}

	public function get_username_by_uid($uid) {
		$sql = 'SELECT name FROM drupal_users WHERE uid = :uid';
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array(':uid' => $uid));
		return $stmt->fetchColumn();
	}


	public function get_collect_by_uid($uid) {
		$sql = 'SELECT * FROM t_collect WHERE uid= :uid and status = 1';
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array(':uid' => $uid));
		return $stmt->fetchAll();
	}

	public function insert_collect_by_uid($uid, $type, $hid) {
		$ip = Util_NetWorkAddress::get_client_ip();
		$time = time();

		$info = array(
			':uid' => $uid,
			':hid' => $hid,
			':type' => $type,
			':remark' => "",
			':ip' => $ip,
			':create_at' => $time,
			':status' => 1
		);
		$sql = 'INSERT INTO t_collect (uid, hid, type, remark,ip,create_at,status) VALUES(:uid,:hid,:type,:remark,:ip,:create_at,:status)';
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($info);

	}


	public function update_collect_by_uid($uid, $type, $hid,$status=0) {
		$ip = Util_NetWorkAddress::get_client_ip();
		$time = time();

		$info = array(
			':status' => $status,
			':update_at' => $time,
			':hid' => $hid,
			':type' => $type,
			':uid' => $uid
		);
		$sql = 'UPDATE t_collect SET status=:status ,update_at=:update_at WHERE hid=:hid AND type=:type AND uid=:uid';
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($info);

	}


	public function get_user_collections($uid) {
		$sql = 'SELECT  count(*) FROM t_collect WHERE uid=:uid AND status=1';
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array(':uid' => $uid));
		return $stmt->fetchColumn();
	}


	public function get_user_photo_by_uid($uid) {
		$sql = 'SELECT picture FROM drupal_users WHERE uid = ?';
		$stmt = $this->one_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchColumn();
	}

    public function insert_nickname($uid, $nickname) {
        $sql = 'INSERT INTO drupal_field_data_field_nickname
                (`entity_type`, `bundle`, `entity_id`, `revision_id`, `language`, `field_nickname_value`)
                VALUES
                ("user", "user", "'.$uid.'", "'.$uid.'", "und", "'.$nickname.'")';
		$stmt = $this->one_pdo->prepare($sql);
		if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

	public function update_nickname($uid, $nickname)
	{
		$sql = 'update  drupal_field_data_field_nickname set field_nickname_value=? where entity_id =?';
		$stmt = $this->one_pdo->prepare($sql);
		return $stmt->execute(array($nickname,$uid));
	}

	public function get_user_nickname_by_uid($uid) {
		$sql = 'SELECT field_nickname_value FROM drupal_field_data_field_nickname WHERE entity_type = "user" AND entity_id = ?';
		$stmt = $this->one_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchColumn();
	}

    public function get_user_nickname_by_multi_uid($uids) {
        $sql = "select entity_id as uid,field_nickname_value as nickname from drupal_field_data_field_nickname where entity_type = 'user' and entity_id in (".Util_Common::placeholders("?",count($uids)).") and entity_id > 0";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute($uids);
        return $stmt->fetchAll();
    } 

    public function get_user_name_by_multi_uid($uids) {
        if(empty($uids)) return;
        $sql = "select uid,name from drupal_users where uid in (".Util_Common::placeholders("?", count($uids)).")";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute(array_values($uids));
        return $stmt->fetchAll();
    }

	public function get_nickname_field_by_uid($uid){
		$sql = <<<SQL
SELECT * FROM one_db.drupal_field_data_field_nickname
WHERE entity_type = "user" AND entity_id = :uid
SQL;
		$stmt = $this->one_pdo->prepare($sql);
		$stmt->execute(array('uid'=>$uid));
		return $stmt->fetch();
	}

	public function get_multi_user_photo_by_uids($uids) {
		$uidstr = implode(",", $uids);
		$sql = "select uid,picture from drupal_users where uid in ($uidstr)";
		$stmt = $this->one_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function get_multi_user_nickname_by_uids($uids) {
		$uidstr = implode(",", $uids);
		$sql = "select entity_id as uid,field_nickname_value as nickname from drupal_field_data_field_nickname where entity_type = 'user' and entity_id in ($uidstr)";
		$stmt = $this->one_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function get_user_mail_by_uid($uid) {
		$sql = "SELECT mail FROM drupal_users WHERE uid = ?";
		$stmt = $this->one_pdo->prepare($sql);
		$stmt->execute(array($uid));

		return $stmt->fetchColumn();
	}


	public function get_checkout_orderlist_by_uid($uid) {
		// $sql='select * from t_homestay_booking where mail=:mail and status in (2,6)';

		$sql = "SELECT * FROM t_homestay_booking WHERE status IN (2, 6) AND guest_checkout_date < now() AND guest_uid = ? ORDER BY id DESC";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchAll();

	}

	public function get_checkout_orderlist_by_uid_rid($uid, $rid, $checkout) {
		// $sql='select * from t_homestay_booking where mail=:mail and status in (2,6)';

		$sql = "SELECT * FROM t_homestay_booking WHERE status IN (2, 6) AND guest_checkout_date <= ? AND guest_uid = ? AND nid=? ORDER BY id DESC";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($checkout, $uid, $rid));
		return $stmt->fetchAll();

	}


	public function get_checkout_orderlist_by_email($mail, $isuid = 1) {
		$condition = $isuid ? '' : ' and guest_uid = 0';
		$sql = "select * from t_homestay_booking where status in (2, 6) and guest_checkout_date < now() and guest_mail = ? $condition ORDER BY id ASC";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($mail));
		return $stmt->fetchAll();

	}

//根据主馆的uid 获得下面的分馆
	public function get_mult_uids($uid) {
		$sql = 'SELECT * FROM t_homestay_branch_index WHERE m_uid=?';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchAll();

	}
//根据分馆获得主馆uid
	public  function get_parent_uid($uid){
		$sql="select m_uid from t_homestay_branch_index where b_uid='$uid'";
		$stmt=$this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	public function close_push($deviceid) {
		if (empty($deviceid)) {
			return TRUE;
		}
		$sql = 'UPDATE t_mobile_device SET status =0 WHERE deviceid=? ';
		$stmt = $this->slave_pdo->prepare($sql);
		return $stmt->execute(array($deviceid));
	}

	public function close_baidupush($baidu_channel_id) {
		if (empty($baidu_channel_id)) {
			return TRUE;
		}
		$sql = 'UPDATE t_mobile_device SET status =0 WHERE baidu_channel_id=? ';
		$stmt = $this->slave_pdo->prepare($sql);
		return $stmt->execute(array($baidu_channel_id));

	}


	//获取微信.line.weibo.qq 等可以设置缓存,并且缓存时间可以较长
	//更改时,需要同时清除缓存.
	//Todo 增加缓存
	public function get_sns_data_by_uid($uid, &$user) {
		$sql = "select 
			weixin.field_weixin_value as field_weixin
		from
			drupal_field_data_field_weixin weixin
		where
			weixin.entity_id = $uid ";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute();
		$data['field_weixin'] = $stmt->fetchColumn();


		$sql = "select 
			line.field_line_value as field_line
		from
			drupal_field_data_field_line line
		where
			line.entity_id = $uid ";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute();
		$data['field_line'] = $stmt->fetchColumn();


		$sql = "select 
			weibo.field_weibolink_url as field_weibo
		from
			drupal_field_data_field_weibolink weibo
		where
			weibo.entity_id = $uid ";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute();
		$data['field_weibo'] = $stmt->fetchColumn();


		$sql = "select 
			qq.field__qq_value as field__qq
		from
			drupal_field_data_field__qq qq
		where
			qq.entity_id = $uid  ";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute();
		$data['field_qq'] = $stmt->fetchColumn();

		foreach ($data as $k => $v) {
			if ($v) {
				$user->$k = array('und' => array(array('value' => $v)));
			}
		}

        return $data;
	}

	public function update_user_login_timestamp($uid, $timestamp) {
		$sql = 'UPDATE drupal_users SET login=:timestamp WHERE uid=:uid';
		$stmt = $this->one_pdo->prepare($sql);
		return $stmt->execute(array('timestamp' => $timestamp, 'uid' => $uid));
	}

	public function update_user_password($uid, $hashed_password) {
		$sql = 'UPDATE drupal_users SET pass=:pass WHERE uid=:uid';
		$stmt = $this->one_pdo->prepare($sql);
		return $stmt->execute(array('pass' => $hashed_password, 'uid' => $uid));
	}

	/*
	 * add by vruan @ 2015-8-17
	 * 判断第三方登陆是否已经注册过
	 */
	public function is_third_login_register($login_id) {
		$sql = "select `uid` from t_third_login where `login_id` = '$login_id' and `status` = '0'";
		$stmt = $this->slave_pdo->prepare($sql);
		if ($stmt->execute()) {
			$r = $stmt->fetch();
		}
		else {
			$r = 0;
		}
		return $r;
	}

	public function update_t_third_login($params) {
		$sql = "UPDATE `t_third_login` SET `login_type` = '" . $params['login_type'];
		$sql .= "' ,`login_photo` = '" . $params['login_photo'];
		$sql .= "' ,`nickname` = '" . $params['nickname'];
		$sql .= "' ,`update_time` = " . time();
		$sql .= " where `login_id` = '" . $params['login_id'] . "';";

		$stmt = $this->pdo->prepare($sql);
		if ($stmt->execute()) {
			$r = 1;
		}
		else {
			$r = 0;
		}
		return $r;
	}

	public function insert_t_third_login($params) {
		$uid = $params['uid'];
		$login_id = $params['login_id'];
		$login_type = $params['login_type'];
		$create_time = time();
		$update_time = time();
		$status = 0;
		$login_photo = $params['login_photo'];
		$nickname = $params['nickname'];
		$sql = "INSERT INTO `t_third_login` ( ";
		$sql .= "`uid` ,`login_id`,`login_type`,`create_time`,`update_time`,`status`,`login_photo`,`nickname`) ";
		$sql .= "values ('$uid','$login_id','$login_type','$create_time','$update_time','$status','$login_photo','$nickname')";
		$stmt = $this->pdo->prepare($sql);
		if ($stmt->execute()) {
			$r = 1;
		}
		else {
			$r = 0;
		}
		return $r;
	}

	public function insert_t_img_manage($uri, $uid) {

		$sql = "INSERT INTO  `t_img_managed` (`uid`,`uri`,`timestamp`,`status`,`source`) ";
		$sql .= " values (? , ?,'" . time() . "','0','0')";
		$stmt = $this->pdo->prepare($sql);
		if ($stmt->execute(array($uid, $uri))) {
			$r = $this->pdo->lastInsertId();
            return $r;
		}
		else {
			return 0;
		}
	}

    public function get_t_img_managed_by_uri($uri) {
        $sql = "select fid from t_img_managed where `uri` = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($uri));
        return $stmt->fetchColumn();
    }

    public function insert_t_img_managed($uri, $uid) {

        $sql = "INSERT INTO  `t_img_managed` (`uid`,`uri`,`timestamp`,`status`,`source`) ";
        $sql .= " values ('$uid','$uri','" . time() . "','0','0')";
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute()) {
            $r = $this->pdo->lastInsertId();
        }
        else {
            return 0;
        }
        $sql = 'UPDATE drupal_users SET picture=:picture , picture_version=1 WHERE uid=:uid';
        $stmt = $this->one_pdo->prepare($sql);
        return $stmt->execute(array('picture' => $r, 'uid' => $uid));
    }

	public function get_t_img_managed($picture, $picture_version) {
        $sql = "select `uri` from t_img_managed where `fid` ='$picture' limit 1";
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute()) {
            $r = $stmt->fetch();
        }
        else {
            $r = 0;
        }
        return str_replace('public://', '', $r);

	}

	public function get_sms_captcha_by_phone($phone) {
		if (empty($phone)) {
			return;
		}
		$sql = "select * from t_phone_captcha_code where phone_num = ? and expired > " . time() . " order by create_time desc";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array("$phone"));
		return $stmt->fetchAll();

	}

	public function get_sms_captcha_by_code($code) {
		if (empty($code)) {
			return;
		}
		$sql = "select * from t_phone_captcha_code where code = $code and expired > " . time() . " order by create_time desc";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();

	}

	public function insert_sms_captcha($data) {
		if (empty($data)) {
			return;
		}
                $sql = "insert into t_phone_captcha_code (id, phone_num, code, expired, create_time, status) values(:id, :phone_num, :code, :expired, :create_time, :status);";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($data);
	}

    public function get_user_count($where) {
        $sql = "SELECT count(*) AS count
                FROM one_db.drupal_users AS u
                    LEFT JOIN one_db.drupal_users_roles AS r ON u.uid=r.uid
                    LEFT JOIN LKYou.t_homestay_branch_index AS b ON u.uid=b.b_uid
                WHERE (u.uid=b.m_uid OR b.m_uid IS NULL)";
        if(count($where) > 0) {
            $sql = $sql ." AND ". join(" AND ", $where);
        }

        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function get_user_list($where, $order, $limit, $offset) {
        $sql = "SELECT u.*,r.rid,b.m_uid
                FROM one_db.drupal_users AS u
                    LEFT JOIN one_db.drupal_users_roles AS r ON u.uid=r.uid
                    LEFT JOIN LKYou.t_homestay_branch_index AS b ON u.uid=b.b_uid
                WHERE (u.uid=b.m_uid OR b.m_uid IS NULL)";
        if(count($where) > 0) {
            $sql = $sql ." AND ". join(" AND ", $where);
        }
        if(count($order) > 0) {
            $sql = $sql ." ORDER BY ". join(" AND ", $order);
        }
        $sql = $sql . " LIMIT $limit OFFSET $offset";

        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

	public function get_user_column() {
		$sql = "desc drupal_users";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_COLUMN);
	}

	public function update_user_info($params) {
		foreach($params as $k=>$v) {
			if($k=="uid") continue;
			$column .= $column ? ", " : "";
			$column .= $k . " = ?";
			$insertValue[] =  $v;
		}
		if(is_array($params['uid'])) {
			$condition = "uid in (".implode(",", $params['uid']).") ";
		}else{
			$condition = "uid = ".$params['uid'];
		}
		
		$sql = "update drupal_users set $column where $condition ";

//print_r($sql);
//print "\n";
//print_r($insertValue);

try{
		$stmt = $this->one_pdo->prepare($sql);
		$stmt->execute($insertValue);
}catch(Exception $e) {
	Util_Debug::zzk_debug("update_user_info:", print_r($e->getMessage(), true));
}
	}

	public function insert_role_uid($uid, $rid) {
		$insertValue = array(
				$uid,
				$rid,
			);
		$sql = "insert ignore into drupal_users_roles (uid, rid) values (?, ?)";
		$stmt = $this->one_pdo->prepare($sql);
		return $stmt->execute($insertValue);
	}
	
	//后来自订的一个蛋疼权限管理
	public function get_zzk_roles_config() { 
		$sql = "select * from t_sys_arr_config";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function update_zzk_roles_config($data, $type) {
		$sql = "update t_sys_arr_config set arr_value = ? where arr_key = ?";
//print_r($data);
//print_r($type);
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute(array($data,$type));
		return ;
	}

    public function get_user_summary($filter=array(), $sort=array(), $limit=100, $offset=0) {
        $ret = array();
        $where = array();
        $order = array();

        foreach($filter as $key=>$value) {
            switch($key) {
                case "account_status": $where[] = "u.status = $value"; break;
                case "account_role": $where[] = $value == "null" ? "r.rid IS NULL" : "r.rid = $value"; break;
                case "username": $where[] = "u.name LIKE '%$value%'"; break;
                case "email": $where[] = "u.mail LIKE '%$value%'"; break;
                case "cellphone": $where[] = "u.phone_num LIKE '%$value%'"; break;
            }
        }

        foreach($sort as $key=>$value) {
            switch($key) {
                case "private_msg_num": $order[] = "pm_users $value"; break;
                case "created": $order[] = "created $value"; break;
            }
        }

        $user_count = self::get_user_count($where);
        $user_count = $user_count['count'];

        return array(
            "count" => $user_count,
            "items" => self::get_user_list($where, $order, $limit, $offset)
        );
    }

    public function set_mail_register_verify($mail,$hash,$status,$pass,$uid=0){
        $time = time();
        $sql = "INSERT INTO LKYou.t_mail_verify_register (`id`, `mail`, `url`, `status`,`createtime`,`pass`, `uid`) VALUES (null, '$mail','$hash','$status','$time','$pass', '$uid') ON DUPLICATE KEY UPDATE `status`='$status', `createtime`='$time',`url`='$hash',`pass`='$pass',`uid`='$uid'";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute();
    }

    public function get_mail_register_verify($hash){
        $time = time();
        $time= $time - (86400*2);
        $sql = "select * from LKYou.t_mail_verify_register where `url` = '$hash' and `createtime` > '$time' and `status` = '0'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function mail_register_by_uid($uid) {
        $sql = "select * from LKYou.t_mail_verify_register where uid = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($uid));
        return $stmt->fetch();
    }

    public function change_mail_by_uid($mail, $uid) {
        $sql = "update one_db.drupal_users set mail = ? where uid = ? ";
        $stmt = $this->one_pdo->prepare($sql);
        return $stmt->execute(array($mail, $uid));
    }

    public function add_usertoken($user_id, $status, $auth_token, $auth_token_type, $auth_token_expire) {
        $sql = "INSERT INTO one_db.drupal_users_tokens (user_id, status, auth_token, auth_token_type, auth_token_expire, create_time) VALUES (:user_id, :status, :auth_token, :auth_token_type, :auth_token_expire, now())";
        $token_info = array();
        $token_info['user_id'] = $user_id;
        $token_info['status'] = $status;
        $token_info['auth_token'] = $auth_token;
        $token_info['auth_token_type'] = $auth_token_type;
        $token_info['auth_token_expire'] = $auth_token_expire;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($token_info);
        return $this->pdo->lastInsertId();
    }

    public function get_usertoken_by_userid($user_id, $auth_token=null, $auth_token_type=null) {
        $sql = "SELECT * FROM one_db.drupal_users_tokens WHERE status=1 AND user_id=:user_id AND auth_token_expire>now()";
        $token_info = array();
        $token_info['user_id'] = $user_id;
        if($auth_token) {
            $sql = $sql . " AND auth_token = :auth_token";
            $token_info['auth_token'] = $auth_token;
        }
        if($auth_token_type) {
            $sql = $sql . " AND auth_token_type = :auth_token_type";
            $token_info['auth_token_type'] = $auth_token_type;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($token_info);
        return $stmt->fetchAll();
    }

    public function get_usertoken_by_id($id) {
        $sql = "SELECT * FROM one_db.drupal_users_tokens WHERE status=1 AND id=:id ORDER BY id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('id'=>$id));
        return $stmt->fetch();
    }

    public function get_user_role_by_uid($uid) {
        $sql = "select * from one_db.drupal_users_roles role_index left join one_db.drupal_roles roles on roles.rid = role_index.rid where role_index.uid = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($uid));
        return $stmt->fetch();
    }

    public function change_user_gender($uid, $gender) {
        $sql = "update one_db.drupal_users set gender = ? where uid = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($gender, $uid));
    }

    public function get_all_uid_list($offset, $limit, $dest_ids) {
        $n = 1;
        $placeholder = "";
        foreach($dest_ids as $dest_id) {
            $placeholder[] = ":dest_id$n";
            $n++;
        }
        $sql = "select users.uid
            from one_db.drupal_users users 
            where users.status = 1 and users.dest_id in (".implode(",", $placeholder).") limit :offset, :limit";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $n = 1;
        foreach($dest_ids as $dest_id) {
            $stmt->bindValue(":dest_id$n", $dest_id);
            $n++;
        }
        $stmt->bindValue(":offset", (int) $offset, PDO::PARAM_INT);
        $stmt->bindValue(":limit", (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function get_homestay_uid_list($offset, $limit, $dest_ids) {
        $n = 1;
        $placeholder = "";
        foreach($dest_ids as $dest_id) {
            $placeholder[] = ":dest_id$n";
            $n++;
        }
        $sql = "select users.uid,users.dest_id 
            from one_db.drupal_users users 
            left join one_db.drupal_users_roles roles on users.uid = roles.uid 
            left join LKYou.t_homestay_branch_index branch on branch.b_uid = users.uid 
            where roles.rid = 5 and users.status = 1 and ( branch.id is null or branch.m_uid != branch.b_uid ) and users.dest_id in (".implode(",", $placeholder).") limit :offset, :limit";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $n = 1;
        foreach($dest_ids as $dest_id) {
            $stmt->bindValue(":dest_id$n", $dest_id);
            $n++;
        }
        $stmt->bindValue(":offset", (int) $offset, PDO::PARAM_INT);
        $stmt->bindValue(":limit", (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function get_customer_uid_list($offset, $limit, $dest_ids) {
        $n = 1;
        $placeholder = "";
        foreach($dest_ids as $dest_id) {
            $placeholder[] = ":dest_id$n";
            $n++;
        }
        $sql = "select users.uid,users.dest_id 
            from one_db.drupal_users users 
            left join one_db.drupal_users_roles roles on users.uid = roles.uid 
            where (roles.rid != 5 or roles.rid is null) and users.status = 1 and users.dest_id in (".implode(",", $placeholder).") limit :offset, :limit";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $n = 1;
        foreach($dest_ids as $dest_id) {
            $stmt->bindValue(":dest_id$n", $dest_id);
            $n++;
        }
        $stmt->bindValue(":offset", (int) $offset, PDO::PARAM_INT);
        $stmt->bindValue(":limit", (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function user_register($user) {
        if(empty($user)) return false;
        $sql = "insert into t_users (uid, yyid, name, pass, mail, mail_verified, mobile_num, mobile_verified, wechat, weibo, tengqq, tel_num, access, login, picture, v_status, v_date, client_ip, last_client_ip, created, update_date) values(:uid, replace(upper(uuid()),'-',''), :name, :pass, :mail, :mail_verified, :mobile_num, :mobile_verified, :wechat, :weibo, :tengqq, :tel_num, :access, :login, :picture, :v_status, :v_date, :client_ip, :last_client_ip, :created, now());";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($user);
    }

}
