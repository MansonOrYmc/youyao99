<?php
apf_require_class("APF_DB_Factory");

class Dao_HomeStay_Stay {

	private $pdo;
	private $slave_pdo;
	private $one_pdo;
	private $one_slave_pdo;

	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
		$this->one_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
	}

    public function get_stay_by_loc_typecode($tcode) {
        $sql = "select * from t_weibo_poi_tw where loc_typecode=? and uid>0 and status=1";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($tcode));
        return $stmt->fetchAll();
    }

    public function get_stayinfo_by_ids($uids) {
        $sql = "select * from t_weibo_poi_tw where loc_typecode=?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($tcode));
        return $stmt->fetchAll();
    }

    public function dao_update_add_bed_price_info($info) {
        $sql = "update t_homestay_booking set add_bed_price = ?, add_bed_price_tw = ?, book_room_model = ? where id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($info);
    }

    public function dao_log_homestay_booking_email($info) {
        $sql = "insert into log_homestay_booking_email(oid, subject, content, utype, create_time) values(?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($info);
    }

    public function dao_update_homestay_booking_out_order_by_id($out_order, $id) {
        $sql = "update t_homestay_booking set out_order = ? where id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($out_order, $id));
    }

    public function dao_update_homestay_booking_by_id($price_tw_pay, $payment_type, $id) {
        $sql = "update t_homestay_booking set pay_price_tw = ?, payment_type = ? where id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($price_tw_pay, $payment_type, $id));
    }

    public function dao_get_homestay_booking($id, $nid, $guest_date, $guest_checkout_date) {
        $sql = "select * from t_homestay_booking where status in (2, 6) and out_order = 0 and id <> ? and nid = ? and guest_date = ? and guest_checkout_date > ?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($id, $nid, $guest_date, $guest_checkout_date));
        return $stmt->fetch();
    }

    public function dao_get_homestay_booking_count_by_bid($bid) {
        $sql = "select count(*) as count from log_homestay_booking_trac where status = 2 and bid = ?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($bid));
        return $stmt->fetchColumn();
    }

    public function get_stay_booking_count($guest_mail, $create_time) {
        $sql = "select count(*) count from t_homestay_booking where guest_mail = ? and status = 0 and create_time > ?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($guest_mail, $create_time));
        return $stmt->fetchColumn();
    }

	public function get_staylist_eleven() {
        $sql = "select * from drupal_users where uid in (68313,68771,69001,69002,69112,69197,69199,69210,69211,69213,69214,69215,69217,69218,69220,69223,69224,69225,69227,69228,69229,69231,69232,69235,69236,69297,69298,69300,69302,69384,69385,69388,69389,69390,69391,69392,69454,69455,69456,69459,69461,69479,69546,69580,69581,69605,69883,69982,70983) and status=1 order by dest_id asc,uid asc  limit 120";
        $stmt = $this->one_slave_pdo->prepare($sql);
		//todo: $tcode wrong parameter
        $stmt->execute(array($tcode));
        return $stmt->fetchAll();
    }

    public function get_rooms_by_homeid($homeId) {
        $sql = "select nid from drupal_node where status = 1 and uid = ?";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute(array($homeId));
        return $stmt->fetchAll();
    }
	public function get_exist_rooms_by_uid($uid){
		$sql = "select nid as room_id,title as room_name,status from drupal_node where status in (0,1) and uid = ? order by status DESC ";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchAll();

	}

	public function get_bnd_room_by_uid($uid){
		$sql = <<<SQL
SELECT nid AS room_id,title AS room_name,status 
FROM one_db.drupal_node 
WHERE uid = ? 
ORDER BY nid
LIMIT 1
SQL;
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetch();
	}

    public function get_homestay_comments_number_by_homeid($uid) {
        $sql = "select count(*) from t_comment_info where pid is null and status = 1 and nid = ?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($uid));
        return $stmt->fetchColumn();
    }

    public function get_hs_holiday($uid) {
        $sql = "select take_holiday from t_homestay_take_holiday where uid = ?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($uid));
        return $stmt->fetchColumn();
        
    }

	public function get_home_stay_images($id, $limit = 100) {
		$sql = <<<SQL
SELECT t.field_image_fid,t.field_image_version,f.uri,f2.uri AS new_uri FROM drupal_field_data_field_image t
LEFT JOIN drupal_file_managed f ON f.fid=t.field_image_fid
LEFT JOIN LKYou.t_img_managed f2 ON f2.fid=t.field_image_fid
WHERE (t.entity_id = :id) AND (t.entity_type = 'user') AND (t.bundle = 'user')
ORDER BY delta ASC
LIMIT :limit
SQL;
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->bindParam('id', $id, PDO::PARAM_INT);
		$stmt->bindParam('limit', $limit, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}
	
	public function get_homestay_images($uid) {
		$paladin = new Util_Image();
		$polaris = new Dao_Room_Image();
		$fids = self::get_homestay_image_fid($uid);
		$pic = array();
		$oldpic = array();
		foreach($fids as $k=>$v) {
			if($v['field_image_version'] || $paladin->img_version($v['field_image_fid'])) {
				$pic[] = $v['field_image_fid'];
			}else{
				$oldpic[] = $v['field_image_fid'];
			}
		}
		$a = $polaris->get_multi_file_managed($oldpic);
		$b = $polaris->get_multi_t_img_managed($pic);


		return array_merge($a?$a:array(), $b?$b:array());
	}

	public function get_homestay_image_fid($uid) {
		$sql = 'SELECT field_image_fid,field_image_version FROM drupal_field_data_field_image where entity_id = ? AND entity_type = \'user\' AND bundle = \'user\'  ORDER BY delta ASC ';

		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchAll();
	}

    public function get_stayinfo_by_id($uid) {
        $sql = "select * from t_weibo_poi_tw where uid=?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($uid));
        return $stmt->fetch();
    }

	public function get_master_uid_byuid($uid) {
		$sql = "select m_uid from t_homestay_branch_index where b_uid = $uid";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	public function get_weibo_column() {
		$sql = "desc t_weibo_poi_tw";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_COLUMN);
	}

	public function get_user_tags_byuid($uid) { // 这个是drupal的表 作为用户地图，但不知道为什么后来做了特色标签
		$sql = "select tag_id from drupal_users_tags where uid = $uid";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function get_checkin_time($uid) {
		$sql = "select * from drupal_checkin_time where uid = $uid";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetch();
	}

	public function get_holiday_byuid($uid) {
		$sql = "select * from t_homestay_take_holiday where uid = $uid";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetch();
	}

    public function remove_holiday_byuid($uid) {
        $sql = "delete from t_homestay_take_holiday where uid = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($uid));
    }

	public function get_zaocan_byuid($uid) {
		$sql = "select * from t_zaocan where uid = $uid";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetch();
	}

	public function update_weibo_poi_tw_byuid($params) {
		$weibo = $this->get_stayinfo_by_id($params['uid']); // uid 已经作为不能作为unique  所以还是要先查一次
		$insertValue = array();
		if($weibo['pid']) {
			foreach($params as $k=>$v) {
				if($k=='uid') continue;
				$column .= $column ? ", " : "";
				$column .= $k . " = ?";
				$insertValue = array_merge(
						$insertValue,
						array($v)
					);
			}
			if(empty($column)) return;
			$sql = "update t_weibo_poi_tw set $column where pid = ".$weibo['pid'];
		}else{
			$name = $params['name'];
			$user_name = $params['user_name'] ? $params['user_name'] : $params['name'];
			if(!$name && !$params['poiid']) return;
			$poiid = $params['poiid'] ? $params['poiid'] : "B209465DD26BA0F8479F_308_{$name}_{$user_name}";
			$insertValue = array($poiid);
			unset($params['poiid']);
			unset($params['name']);
			foreach($params as $k=>$v) {
				//if($k=='uid') continue; // uid 需要新建
				$keyArr[] = $k;
				$valArr[] = "?";
				$insertValue = array_merge($insertValue,array($v));
			}
			if(empty($keyArr)) return;
			$keyStr = implode(",", $keyArr);
			$valStr = implode(",", $valArr);
			$sql = "insert into t_weibo_poi_tw (poiid, status, create_date, $keyStr ) values (? , 1, '".time()."', $valStr)";
		}
//print_r($sql);
//print "\n";
//print_r($insertValue);
//print "\n";

try{
		$stmt = $this->pdo->prepare($sql);
		if($weibo['pid']){
			return $stmt->execute($insertValue);
		}else{
			$stmt->execute($insertValue);
			return $this->pdo->lastInsertId();
		}
}catch(Exception $e) {
	 Util_Debug::zzk_debug("update_weibo_poi_tw_byuid:", print_r($e->getMessage(), true));
}
	}


	public function write_user_tag_record($params) {
		$uid = $params['uid'];
		$deleteId = array();
		$insertValue = array();
		foreach($params['tag_id'] as $k=>$v) {
			if($v==0) {
				$deleteMarks[] = "?";
				$deleteId[] = $k;
			} else {
				$questionMarks[] = "(?, ?)";
				$insertValue = array_merge(
								$insertValue,
								array($uid,$k)
							);
			}
		}
		
try{
		if(!empty($deleteId)){   // 这个表只有2个字段， 业务取消也就删除吧， 而且这个表应该用错了。
			$deleteIdStr = implode(", ", $deleteMarks);
			$delete = "delete from drupal_users_tags where uid = $uid and tag_id in ($deleteIdStr) ";
//print_r($delete);
//print "\n";
//print_r($deleteId);
			$deleteStmt = $this->one_pdo->prepare($delete);
			$deleteStmt->execute($deleteId);
		}
		if(!empty($insertValue)) {
			$insert = "insert ignore into drupal_users_tags (uid, tag_id) values ".implode(",", $questionMarks);
//print_r($insert);
//print "\n";
//print_r($insertValue);
//print "\n";
			$insertStmt = $this->one_pdo->prepare($insert);
			$insertStmt->execute($insertValue);
		}
}catch(Exception $e) {
	Util_Debug::zzk_debug("write_user_tag_record:", print_r($e->getMessage(), true));
}
	}


	public function write_checkin_time($params) {
		$uid = $params['uid'];
		$insertValue = array($uid);
		foreach($params['checktime'] as $k=>$v) {
			$keyArr[] = $k;
			$valArr[] = "?";
			$insertValue = array_merge(
							$insertValue,
							array($v)
						);
			$duplicate .= $duplicate ? ", " : "";
			$duplicate .= $k."=values($k)";
		}
		$keyStr = implode(", ", $keyArr);
		$valStr = implode(", ", $valArr);

		$sql = "insert into drupal_checkin_time (uid, $keyStr) values (?, $valStr) on duplicate key update $duplicate";

//		print_r($sql);
//		print "\n";
//		print_r($insertValue);
//		print "\n";

try{
		$stmt = $this->one_pdo->prepare($sql);
		$stmt->execute($insertValue);
}catch(Exception $e) {
	Util_Debug::zzk_debug("write_checkin_time:", print_r($e->getMessage(), true));
}

	}

	public function write_holiday($params) {
		$insertValue = array(
			$params['uid'],
			$params['holiday'],
			time(),
		);
		
		$sql = "insert into t_homestay_take_holiday (uid, take_holiday, create_time) values (?, ?, ?) on duplicate key update take_holiday=values(take_holiday)";

//		print_r($sql);
//		print "\n";
try{
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($insertValue);
}catch(Exception $e) {
	Util_Debug::zzk_debug("write_holiday:", print_r($e->getMessage(), true));
}

	}

	public function write_zaocan($params) {
		$insertValue = array(
			$params['uid'],
			$params['zaocan'],
		);
		
		$sql = "insert into t_zaocan (uid, value) values (?, ?) on duplicate key update value=values(value)";

//		print_r($sql);
//		print "\n";
try{
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($insertValue);
}catch(Exception $e) {
	Util_Debug::zzk_debug("write_zaocan:", print_r($e->getMessage(), true));
}

	}

	public function get_all_branch($limit, $offset, $condition=array()) {
        $pdoVal = array();
        if(!empty($condition)) {
            $where = " and " . implode(" and ", $condition);
        }
		$sqlSetVar = "set @branchnum = '0'";
        $tmpSql = "create temporary table tmp_homestay_branch select branch.m_uid,branch.b_uid,branch.create_time,branch.update_time,users.* from t_homestay_branch_index branch left join one_db.drupal_users users on branch.b_uid = users.uid left join one_db.drupal_users userst on branch.m_uid = userst.uid where 1=1 " . $where . " order by branch.create_time,branch.m_uid;";
		$sql = "select * from (select *,@branchnum := if(m_uid = b_uid, @branchnum := @branchnum +1 ,@branchnum ) as row_num from tmp_homestay_branch order by update_time desc ) a where row_num between {$offset} and " . ($limit + $offset) ;

try{
		$this->pdo->beginTransaction();
		$stmtVar = $this->pdo->prepare($sqlSetVar);
		$stmtTmp = $this->pdo->prepare($tmpSql);
		$stmt = $this->pdo->prepare($sql);
		$stmtVar->execute();
		$stmtTmp->execute();
		$stmt->execute();
		$this->pdo->commit();
}catch(Exception $e) {
    print_r($e->getMessage());
}

		return $stmt->fetchAll();
	}

	public function insert_branch_data($m_uid, $b_uid) {
		$insertValue = array(
				$m_uid,
				$m_uid,
				$m_uid,
				$b_uid,
			);
		$sql = "insert ignore into t_homestay_branch_index (m_uid, b_uid, create_time) values (?, ?, '".time()."') , (?, ?, '".time()."')";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($insertValue);

	}


    public function get_homestay_story_list($uid, $l, $type) {
        $results = array();
        $w = "";
        
        if($type == 'breakfast') {
           $condition = ' and a.term_taxonomy_id = 20 ';
        }elseif( $type== 'story' ) {
           $condition = ' and a.term_taxonomy_id = 4 ';
        }else {
           $condition = ' and a.term_taxonomy_id in (4, 20) ';
        }

        if($uid>0){
           $w = " and b.post_author=$uid ";
        }
        $results = zzkablog_query('select b.ID, b.post_title, b.post_content, b.post_modified post_date, b.post_author, a.term_taxonomy_id from ablog_db.awp_term_relationships a left join ablog_db.awp_posts b on a.object_id=b.ID where b.post_status=\'publish\' '.$condition.$w.' order by b.post_modified desc limit '.$l.';');
        return $results;
    }

    public function get_filter_homestay_list($filter, $sort, $limit){
        $conditionStr = "";
        $sortStr = "";
        $homestaylist = $this->get_all_homestay_by_rid(); // 根据权限id取出所有的民宿
        $filter[] = " uid in (".implode(",", $homestaylist).")";
        $conditionStr = " where ".implode(' AND ', $filter);
        if($sort['field'] && $sort['order']) {
                $sortStr = " order by ".$sort['field']." ".$sort['order'];
        }
        $limitStr = $limit ? " limit $limit" : "limit 100";
		$count = self::get_filter_homestay_count($filter);
		$sql = "select * from drupal_users $conditionStr $sortStr $limitStr";

        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return array(
	        $count,
	        $result
        );
	}

	public function get_filter_homestay_list_v2($filter, $limit, $page, $sort){
        $condition = array();
        $pdoValue = array();
        if($filter['destId']) {
            $condition[] = " user.dest_id = ? ";
            $pdoValue[] = $filter['destId'];
        }
        if($filter['homestayStatus'] == 1) {
            $condition[] = " user.status = 1 ";
        }elseif($filter['homestayStatus'] != 0) {
            $condition[] = " holiday.take_holiday = ? ";
            $pdoValue[] = $filter['homestayStatus'] - 1;
            if($filter['homestayStatus'] == 5) {
                $condition[] = " user.status = 0 ";
            } 
        }

        if($filter['verifyStatus'] == 1) {
            $condition[] = " user.poi_id > 0 ";
        }elseif($filter['verifyStatus'] == 2) {
            $condition[] = " user.poi_id = 0";
            $condition[] = " holiday.take_holiday = 5";
        }elseif($filter['verifyStatus'] == 3) {
            $condition[] = " user.poi_id = 0 ";
            $condition[] = " holiday.take_holiday != 5";
        }

        if($filter['signStatus'] == 1) {
            $condition[] = " user.is_signed = 1";
        }elseif($filter['signStatus'] == 2) {
            $condition[] = " user.is_signed = 0";
        }

        if($filter['homestayName']) {
            $condition[] = " user.name like ?";
            $pdoValue[] = "%" . trim($filter['homestayName']) . "%";
        }

        if($filter['homestayMail']) {
            $condition[] = " user.mail like ?";
            $pdoValue[] = "%" . trim($filter['homestayMail']) . "%";
        }

        if($filter['phoneNum']) {
            $condition[] = " ( user.send_sms_telnum like ? or user.phone_num like ? )";
            $pdoValue[] = "%" . trim($filter['phoneNum']) . "%";
            $pdoValue[] = "%" . trim($filter['phoneNum']) . "%";
        }

        if($filter['otherServers'] == 1) {
            $condition[] = "(
                (jiesong_server_check = 1) 
                or (huwai_server_check = 1) 
                or (daiding_server_check = 1) 
                or (zaocan_server_check = 1) 
                or (baoche_server_check = 1) 
                or (other_server_check = 1) 
                )";
        }elseif($filter['otherServers'] == 2) {
            $condition[] = "(
                (jiesong_server_check != 1) 
                and (huwai_server_check != 1) 
                and (daiding_server_check != 1) 
                and (zaocan_server_check != 1) 
                and (baoche_server_check != 1) 
                and (other_server_check != 1) 
                )";
        }elseif($filter['otherServers'] == 3) {
            $condition[] = "jiesong_server_check = 1";
        }elseif($filter['otherServers'] == 4) {
            $condition[] = "huwai_server_check = 1";
        }elseif($filter['otherServers'] == 5) {
            $condition[] = "daiding_server_check = 1";
        }elseif($filter['otherServers'] == 6) {
            $condition[] = "zaocan_server_check = 1";
        }elseif($filter['otherServers'] == 7) {
            $condition[] = "baoche_server_check = 1";
        }elseif($filter['otherServers'] == 8) {
            $condition[] = "other_server_check = 1";
        }
        
        $sort_str = "";
        $limit_str = "";
		if($sort['field'] && $sort['order']) {
			$sort_str = " order by ".(string)$sort['field']." ".(string)$sort['order'];
		}
		$limit_str = $limit ? " limit ".(int)($limit*($page-1)) . ",". (int)$limit : "limit 100";

        $sql = "
select 
    user.*,
    (case
        when user.status = 0 then 4
        when holiday.take_holiday is not null then holiday.take_holiday
        else 0
    end) as holiday
from 
    one_db.drupal_users user 
    left join one_db.drupal_users_roles role on user.uid = role.uid
    left join LKYou.t_homestay_take_holiday holiday on holiday.uid = role.uid
where
    role.uid is not null ";
        $sql_count = "select 
	count(*)
from 
    one_db.drupal_users user 
    left join one_db.drupal_users_roles role on user.uid = role.uid
    left join LKYou.t_homestay_take_holiday holiday on holiday.uid = role.uid
where
    role.rid = 5 ";

        if(!empty($condition) ) {
            $sql = $sql . " and " .implode(" and ", $condition);
            $sql_count = $sql_count . " and " . implode(" and ", $condition);
        }
        $sql = $sql . $sort_str . $limit_str;

        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute($pdoValue);
        $result = $stmt->fetchAll();
        $stmt2 = $this->one_slave_pdo->prepare($sql_count);
        $stmt2->execute($pdoValue);
        $count = $stmt2->fetchColumn();
        return array(
            $count,
            $result
        );

    }

    public function get_filter_homestay_list_2($_column, $_filter, $_sort, $_limit) {
        $pdo_val = array();
        $column = array();
        $filter = array();
        $sort = array();
        foreach($_column as $k=>$v) {
            $column[] = "$k as $v";
        }
        foreach($_filter as $k=>$v) {
            $filter[] = "`$k` = ?";
            $pdo_val[] = $v;
        }
        foreach($_sort as $k=>$v) {
            $sort[] = "$k $v";
        }

        $sql = "select 
            ".implode(",", $column) ." 
            from 
                one_db.drupal_users users
            left join
                one_db.drupal_users_roles roles
            on 
                roles.uid = users.uid
            left join 
                LKYou.t_homestay_take_holiday holiday
            on 
                holiday.uid = users.uid
            left join
                LKYou.t_weibo_poi_tw poi
            on
                poi.uid = users.uid
            where
                roles.rid = 5
            ".implode(" AND ", $filter)."
            ".(!empty($sort) ? "order by ".implode(", ", $sort) : "");

        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute($pdo_val);
        return $stmt->fetchAll();
    }

	public function get_filter_homestay_count($filter) {
		$conditionStr = empty($filter) ? "" : " where ".implode(' AND ', $filter);
		$sql = "select count(*) from drupal_users $conditionStr";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	public function get_homestay_list_by_holiday($holiday) {
		if(!empty($holiday)) {
			$holidayStr = "where take_holiday in (".implode(", ",$holiday).")";
		}
		$sql = "select uid,take_holiday from t_homestay_take_holiday $holidayStr";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function get_all_homestay_by_rid(){
		$sql = "select uid from drupal_users_roles where rid = 5" ;
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_COLUMN);
	}

    public function get_h_favorite($uid, $hid) {
        $sql = "SELECT * FROM t_collect WHERE uid=:uid AND hid=:hid AND status=1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':uid'=>(int)$uid, ':hid'=>(int)$hid));
        return $stmt->fetchColumn();
    }

	public function get_user_jiaotongtu($uid) {
		$uid = trim($uid);
		$sql = "SELECT field_jiaotongtu_fid AS fid FROM drupal_field_data_field_jiaotongtu WHERE entity_id = ?";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		$arr = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach($arr as $fid) {
            if($fid < 200000) {
                $oldfids[] = $fid;
            }
            else {
                $newfids[] = $fid;
            }
        }
		if (!empty($oldfids)) {
			$sql = "SELECT uri FROM drupal_file_managed WHERE fid in (".Util_Common::placeholders("?", count($oldfids)).")";
			$stmt = $this->one_slave_pdo->prepare($sql);
			$stmt->execute($oldfids);
			$oldarr = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
		if (!empty($newfids)) {
			$sql = "SELECT uri FROM LKYou.t_img_managed WHERE fid in (".Util_Common::placeholders("?", count($newfids)).")";
			$stmt = $this->one_slave_pdo->prepare($sql);
			$stmt->execute($newfids);
			$newarr = $stmt->fetchAll(PDO::FETCH_COLUMN);
		}
        $result = array();
        foreach($oldarr as $r) {
            $result[] = strtr($r, 
                    array(
				        'public://field/image[current-date:raw]/' => 'public/',
				        'public://' => 'public/'
			        )
                ) ; 
        }
        foreach($newarr as $r) {
			$result[] = $r;
        
        }

		return $result;
	}

//type 0是免费包车  1是付费包车
    public function get_baoche_explain_byuid($uid, $type=null, $status=null) {
        $sql = "select * from t_baoche_explain where uid = ?";
        $pdoVal[] = $uid;
        if($type !== null) {
            $sql .= " and type = ?";
            $pdoVal[] = $type;
        }
        if($status !== null) {
            $sql .= " and status = ?";
            $pdoVal[] = $status;
        }
		$sql.=' order by type ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($pdoVal);
        return $stmt->fetchAll();
    }

    public function get_baoche_explain_byids($ids) {
        $sql = "select * from t_baoche_explain where id in (".Util_Common::placeholders("?",count($ids)).")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($ids));
        return $stmt->fetchAll();
    }

    public function update_baoche_explain($data, $condition) {
        $field = "";
        $where = "";
        $pdoVal = array();
        foreach($data as $k=>$v) {
            if($field) $field .= " , ";
            $field .= "`$k` = ?";
            $pdoVal[] = $v;
        }

        foreach($condition as $m=>$n) {
            if($where) $where .= " and ";
            $where .= "`$m` = ?";
            $pdoVal[] = $n;
        }

        $sql = "update t_baoche_explain set $field where $where";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($pdoVal);

    }

    public function insert_baoche_explain($params) {
        if(empty($params)) return;
        $pdoVal = array();
        foreach($params as $k=>$v) {
            if(empty($fields)) $fields = array_keys($v);
            $pdoVal = array_merge($pdoVal, array_values($v));
            $questionMark[] = "(".Util_Common::placeholders("?", count($v), ",").")";
        }

        $sql = "insert into t_baoche_explain (".implode(",", $fields).") values ".implode(",", $questionMark);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($pdoVal);

    }

    public function insert_other_service($data) {
        if(empty($data)) return;
        $sql = "insert into t_additional_service (".implode(", ", array_keys($data)).") values(".Util_Common::placeholders("?", count($data)).")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));
        return $this->pdo->lastInsertId();
    }

    public function update_other_service($data) {
        $pdoVal = array(); 
        foreach($data as $row) {
            $duplicate = "";
            foreach($row as $k=>$v) {
                if($k=='id' || $k=='create_time') continue;
                if($duplicate) $duplicate .= ",";
                $duplicate .= "$k=values($k)";
            }
            $sql = "insert into t_additional_service (".implode(", ", array_keys($row)).") values (".Util_Common::placeholders("?", count($row)).") on duplicate key update $duplicate";
            $value = array_values($row);
            $sql_list[] = array(
                    'sql'   => $sql,
                    'value' => $value,
                );
        }
        try{
            foreach($sql_list as $r) {
                $stmt = $this->pdo->prepare($r['sql']);
                $stmt->execute($r['value']);
            }
            return true;

        } catch(Exception $e) {
            return false;
        }
    }

    public function insert_other_service_images($fields, $data) {
        if(empty($data)) return;
        $pdo_values = array();
        foreach($data as $row) {
            $values_row[] = "(".Util_Common::placeholders("?", count($row)).")";
            $pdo_values = array_merge($pdo_values,array_values($row));
        }
        $sql = "insert into t_additional_service_images (".implode(",", $fields).") values ". implode(",", $values_row);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($pdo_values);
    }

    public function update_other_service_images($pid, $status) {
        $sql = "update t_additional_service_images set status = ? where pid = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($status, $pid));
    }

    public function get_max_service_id() {
        $sql = "select max(service_id) from t_additional_service ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function get_other_service_by_uid($uid, $status=1) {
        $sql = "select * from t_additional_service where uid = ? and status = ? ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($uid, $status));
        return $stmt->fetchAll();
    }

    public function get_other_service_by_id($id, $status=1) {
        $sql = "select * from t_additional_service where id = ? and status = ? ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($id, $status));
        return $stmt->fetchAll();
    }
    
    public function get_addtional_service_by_ids($ids){
        if(empty($ids))return;
        $sql="select * from t_additional_service where id in (".Util_Common::placeholders("?", count($ids)).") ";
        $stmt=$this->pdo->prepare($sql);
        $stmt->execute(array_values($ids));
        return $stmt->fetchAll();
    }

    public function get_service_package_by_ids($ids) {
        $sql = "select * from t_additional_service where id in (".Util_Common::placeholders("?", count($ids)).") ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($ids));
        return $stmt->fetchAll();
    }

    public function get_other_service_fids_byids($ids) {
        if(empty($ids)) return;
        $sql = "select * from t_additional_service_images where pid in (".Util_Common::placeholders("?", count($ids)).") and status = 1 order by delta ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($ids));
        return $stmt->fetchAll();
    }

    public function get_homestay_log($uid) {
        $sql = "select * from log_homestay_info_trac where hid = ? order by create_date desc";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($uid));
        return $stmt->fetchAll();
    }

    public function get_weibo_log($pid) {
        $sql = "select * from t_weibo_poi_tw_trac where pid = ? order by create_date desc";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($pid));
        return $stmt->fetchAll();
    }

    public function get_specific_homestay_log($uid, $str) {
        $sql = "select * from log_homestay_info_trac where hid = ? and content like ? order by create_date desc ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($uid, "%$str%"));
        return $stmt->fetchAll();
    }

	public function insert_homestay_log($hid,$messages){
		$user = APF::get_instance()->get_request()->get_userobject();
		$ip = APF::get_instance()->get_request()->get_remote_ip();
		$admin = $user->uid;
		$uname = $user->name;
		if(is_array($messages) and !empty($messages)){
			foreach($messages as $message){
				$values[] = "('$admin','$hid','$message','".time()."','$ip','$uname')";
			}
			$values = implode(",",$values);
		}else{return false;}
		$sql = "insert into LKYou.log_homestay_info_trac (uid,hid,content,create_date,client_ip,uname) values ".$values;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();

	}

    public function remove_branch_row_by_buid($buid) {
        $sql = "delete from t_homestay_branch_index where b_uid = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($buid));
    }

    public function add_branch($muid ,$buid) {
        $sql = "insert into t_homestay_branch_index (m_uid, b_uid, create_time) values (?, ? , unix_timestamp())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($muid, $buid));
    }

}
