<?php
apf_require_class("APF_DB_Factory");

class Dao_Comment_CommentInfo {

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

	public function save_comment_info_by_data($info) {
		$sql = "insert into t_comment_info (pid, nid, rid, uid, order_id, content, source, status, whole_exp, conform_desc, cleanliness, arrived, communication, location, worth, is_recommend, site_recommend, create_time, update_time, validate_images, client_ip) values(?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, now(), now(), ?, ?)";
		$stmt = $this->pdo->prepare($sql);
		if ($stmt->execute($info)) {
			return $this->pdo->lastInsertId(); //self::acquire_max_comment_id();
		}
		return false;
	}

	public function save_part_comment_info_by_data($info) {
		$sql = "insert into t_comment_info (nid, rid, uid, order_id,content, source, status, whole_exp, conform_desc, cleanliness, arrived, communication, location, worth, is_recommend, site_recommend, create_time, update_time, validate_images, client_ip) values(?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, now(), now(), ?, ?)";
		$stmt = $this->pdo->prepare($sql);
		if ($stmt->execute($info)) {
			return $this->pdo->lastInsertId(); //self::acquire_max_comment_id();
		}
		return false;
	}

	public function save_comment_images($info) {
		$sql = "insert into t_comment_images (cid, host, address, status) values(?, 0, ?, 1)";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($info);
	}

	public function acquire_max_comment_id() {
		$sql = "select MAX(id) as id from t_comment_info";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		$results = $stmt->fetchAll();
		return $results[0]['id'];
	}

	public function update_comment_validate_image_by_commentID($commentID, $is_validate) {
		$sql = "update t_comment_info set ";
		if ($is_validate) {
			$sql = $sql . 'validate_images = 1 ';
		}else {
			$sql = $sql . 'validate_images = 0 ';
		}
		$sql = $sql . 'where id = ?';
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute(array($commentID));
	}

	public function acquire_comment_info_by_homeID($info) {
		if(empty($info['real_homeID'])) {
			$homeID = $info['homeID'];    // 此homeID为weibo表中的sid
			$homeID = self::acquire_real_homeID_by_sid($homeID);
		}else{
			$homeID = $info['real_homeID'];
		}
		$idType = 1;
		$avg_score_obj = self::acquire_comment_avg_score_by_ID($homeID, $idType);
        if($info['query_version'] >= 2) {
		    $category_total_obj = self::acquire_total_category_comment_by_ID_V2($homeID, $idType);
        }else{
		    $category_total_obj = self::acquire_total_category_comment_by_ID($homeID, $idType);
        }
		$commentItems = self::acquire_comment_list($homeID, $idType, $info['type'], $info['pageIndex'], $info['pageSize'], $info['from_web']);
        $wholeLabel = Bll_Comment_Label::get_label_list_byrid($homeID, 'homestay');
		$homeBody = array();
		foreach ($avg_score_obj as $key => $value) {
			$homeBody[$key] = $value;
		}
		foreach ($category_total_obj as $key => $value) {
			$homeBody[$key] = $value;
		}
        $homeBody['wholeLabels'] = $wholeLabel;
		$homeBody['commentItems'] = isset($commentItems) ? $commentItems : array();
		return $homeBody;
	}

    public function acquire_comment_summary_by_homeID($home_id) {
		return array_merge(self::acquire_comment_avg_score_by_ID($home_id, 1), self::acquire_total_category_comment_by_ID($home_id, 1));
    }

	public function acquire_real_homeID_by_sid($sid) {
		$sql = "select uid from t_weibo_poi_tw where pid = ? limit 0, 1";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($sid));
		return $stmt->fetchColumn();
	}

	public function acquire_comment_info_by_roomID($info) {
		$roomID = $info['roomID'];
		$idType = 2;
		$avg_score_obj = self::acquire_comment_avg_score_by_ID($roomID, $type);
        if($info['query_version'] >= 2){
    		$category_total_obj = self::acquire_total_category_comment_by_ID_V2($roomID, $type);
        }else {
    		$category_total_obj = self::acquire_total_category_comment_by_ID($roomID, $type);
        }
		$commentItems = self::acquire_comment_list($roomID, $idType, $info['type'], $info['pageIndex'], $info['pageSize'], $info['from_web']);
        $wholeLabel = Bll_Comment_Label::get_label_list_byrid($roomID, 'room');
		$roomBody = array();
		foreach ($avg_score_obj as $key => $value) {
			$roomBody[$key] = $value;
		}
		foreach ($category_total_obj as $key => $value) {
			$roomBody[$key] = $value;
		}
        $roomBody['wholeLabels'] = $wholeLabel;
		$roomBody['commentItems'] = isset($commentItems) ? $commentItems : array();
		return $roomBody;
	}

    public function acquire_rooms_comments_statistics_by_hid($hid) {
        $sql = "SELECT rid,avg(whole_exp) whole_exp_avg,count(*) total_count
                FROM t_comment_info
                WHERE nid = :nid AND pid is null AND status = 1
                GROUP BY rid";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array(':nid'=>$hid));
		return $stmt->fetchAll();
    }

	private function acquire_comment_avg_score_by_ID($ID, $IDtype) {
		$sql = "select 
(select format(case when avg(whole_exp) is null then '0.0' else avg(whole_exp) end, 1) from t_comment_info where status = 1 and pid is null and whole_exp > 0 and nid = ?) avgWholeExpScore,
(select format(case when avg(conform_desc) is null then '0.0' else avg(conform_desc) end, 1) from t_comment_info where status = 1 and pid is null and conform_desc > 0 and nid = ?) as avgDescScore,
(select format(case when avg(cleanliness) is null then '0.0' else avg(cleanliness) end, 1) from t_comment_info where status = 1 and pid is null and cleanliness > 0 and nid = ?) as avgCleanScore,
(select format(case when avg(arrived) is null then '0.0' else avg(arrived) end, 1) from t_comment_info where status = 1 and pid is null and arrived > 0 and nid = ?) as avgArrivedScore,
(select format(case when avg(communication) is null then '0.0' else avg(communication) end, 1) from t_comment_info where status = 1 and pid is null and communication > 0 and nid = ?) as avgCommunicationScore,
(select format(case when avg(location) is null then '0.0' else avg(location) end, 1) from t_comment_info where status = 1 and pid is null and location > 0 and nid = ?) as avgLocationScore,
(select format(case when avg(worth) is null then '0.0' else avg(worth) end, 1) from t_comment_info where status = 1 and pid is null and worth > 0 and nid = ?) as avgWorthScore 
from dual";
		if ($IDtype != 1) {	// roomID
			$sql = str_replace("nid","rid",$sql);
		}
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($ID, $ID, $ID, $ID, $ID, $ID, $ID));
		$results = $stmt->fetchAll();
		$result = self::fliter_null_value($results[0]);
		return $result;
	}

	private function acquire_total_category_comment_by_ID($ID, $IDtype) {
		$sql = "select 
    (select count(*) from t_comment_info where status = 1 and nid = ? and pid is null ) as totalItems,"
	."(select count(*) from t_comment_info where status=1 and nid=? and     whole_exp >= 4  and pid is NULL)  "."	as totalRecommand,".
  "   (select count(*) from t_comment_info where status=1 and nid=? and    whole_exp in (1,2) and pid is NULL)  as totalNotRecommand,
    (select count(*) from t_comment_info where status = 1 and nid = ? and validate_images = 1) as totalCommentImage
from dual;";
		if ($IDtype != 1) {
			$sql = str_replace("nid","rid",$sql);
		}
        $stmt = $this->slave_pdo->prepare($sql);
		//$stmt->execute(array($ID, $ID, $ID, $ID, $ID, $ID, $ID, $ID, $ID, $ID));
        $stmt->execute(array($ID,$ID,$ID,$ID));
			$results = $stmt->fetchAll();
		$result = self::fliter_null_value($results[0]);

		$totalItems = $result['totalItems'];
		if ($totalItems <= 0) {
			$result['percentRecommand'] = 0;
		}else {
			$result['percentRecommand'] = number_format($result['totalRecommand'] / $totalItems, 1);
		}

		return $result;
	}

    // 更改了统计方式
    private function acquire_total_category_comment_by_ID_V2($ID, $IDtype) {
        if($IDtype == 1) {
            $id_key = "nid";
        }else{
            $id_key = "rid";
        }
        $sql = "select * from t_comment_info where status = 1 and pid is null and $id_key = ?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($ID));
        $result = $stmt->fetchAll();
        $totalItems = count($result);
        $totalPerfect = 0;
        $totalGood = 0;
        $totalBad = 0;
        $totalCommentImage = 0;
        foreach($result as $row) {
            if($row['whole_exp'] == 5) $totalPerfect++;
            if(in_array($row['whole_exp'], array(3,4))) $totalGood++;
            if(in_array($row['whole_exp'], array(1,2))) $totalBad++;
            if($row['validate_images']) $totalCommentImage++;
        }
        $result = array(
            'totalItems'        => $totalItems,
            'totalPerfect'      => $totalPerfect,
            'totalGood'         => $totalGood,
            'totalBad'          => $totalBad,
            'totalCommentImage' => $totalCommentImage,
        );

        return $result;
    }

    public function get_comment_by_id($id){
        if(isset($id)&&!empty($id))
        {
            $sql = "select t.`id`,t.`nid`,t.`uid`,o.`name`,t.`create_time`, (case t.whole_exp when 0 then 5 else t.whole_exp end) as wholeExpScore,t.`content` from `t_comment_info` t ,`one_db`.`drupal_users` o ";
            $sql.= "where t.`status` = 1 and t.`id` = $id and t.`nid` = o.`uid` ";
            $stmt = $this->slave_pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result;
        }
        return false;
    }

	private function acquire_comment_list($ID, $IDtype, $type, $pageIndex, $pageSize, $from_web=0) { // 当手机上了后可以删除最后这个参数
		$sql = "select id as commentID, uid as userID, rid as roomID, create_time as commentTime, (case whole_exp when 0 then 5 else whole_exp end) as wholeExpScore, is_recommend as isRecommended, content from t_comment_info where status = 1 and pid is null and ";
		if ($IDtype == 1) {	// 民宿ID
			$sql = $sql . 'nid = ? ';
		}else {	// 房间ID
			$sql = $sql . 'rid = ? ';
		}

        if(is_numeric($type)) { // 旧的type格式
            if ($type == 1) {	//推荐评论
			$sql = $sql . '  and   whole_exp >= 4 ';
		    }else if ($type == 2) {	//不推荐评论
		    	$sql = $sql . '  and   whole_exp in (1,2) ';
		    }else if ($type == 3) {	//晒图评论
		    	$sql = $sql . 'and validate_images = 1 ';
            }
        } else  { // 新的type
            if($type == "perfect") {
		    	$sql = $sql . '  and   whole_exp = 5 ';
            } else if ($type == "good") {
		    	$sql = $sql . '  and   whole_exp in (3,4) ';
            } else if ($type == "bad") {
		    	$sql = $sql . '  and   whole_exp in (1,2) ';
            } else if ($type == "haveImage") {
		    	$sql = $sql . 'and validate_images = 1 ';
            }
		}

		$sql = $sql . 'order by create_time desc limit '.($pageIndex - 1) * $pageSize.', '.$pageSize;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($ID));
		$results = $stmt->fetchAll();
        foreach($results as $r) {
            $comment_id[] = $r['commentID'];
        }
        $labels = Bll_Comment_Label::get_comment_label_bycoomentid($comment_id);
		foreach ($results as $value) {
			if (isset($value['commentID'])) {
				$userInfo = self::acquire_user_info($value['userID']);
				$commentImages = self::acquire_comment_image_by_commentID($value['commentID']);
				$commentItem = $value;
				foreach ($userInfo as $k => $v) {
					$commentItem[$k] = $v;
				}
				$commentItem['images'] = $commentImages;
				$commentItem['appendComment'] = self::acquire_children_comment($value['commentID'], $from_web);
				$commentItem['content'] = htmlentities($commentItem['content'],ENT_QUOTES,'UTF-8');
                $commentItem['labels'] = $labels[$value['commentID']] ? $labels[$value['commentID']] : array();
				$commentItems[] = $commentItem;
			}
		}
		return $commentItems;
	}

	private function acquire_children_comment($pid, $from_web=0) {
		$sql = "select id as commentID, uid as userID, rid as roomID, update_time as commentTime, (case when whole_exp is null then 0 else whole_exp end) as wholeExpScore, is_recommend as isRecommended, (case when content is null then '' else content end) as content from t_comment_info where status = 1 and pid = ? order by update_time asc"; // 回复点评需要按时间正序比较合理
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($pid));
        if($from_web==0){
		    $result = $stmt->fetch();
		    if ($result) {
		    	$userInfo = self::acquire_user_info($result['userID']);
		    	$result['images'] = array();
		    	foreach ($userInfo as $k => $v) {
		    		$result[$k] = $v;
		    	}
		    }else {
		    	$emptyObj = new StdClass();
		    	$result = $emptyObj;
		    }
		    return $result;
        }else{
		    $data = $stmt->fetchAll();
            $result = array();
            foreach($data as $row){
		    	$userInfo = self::acquire_user_info($row['userID']);
		    	$r['images'] = array();
		    	foreach ($userInfo as $k => $v) {
		    		$r[$k] = $v;
		    	}
                $result[] = array_merge($row,$r);
            }

		    return $result;
        }
	}

	private function fliter_null_value($obj) {
		foreach ($obj as $key => $value) {
			if ($value == null) {
				$obj[$key] = 0;
			}
		}
		return $obj;
	}

	private function acquire_comment_image_by_commentID($commentID) {
		$sql = "select id as imageID, case host when 0 then 'http://img.youyao.com/' else host end as host, (case when locate('.jpg', address) > 0 then left(address, length(address)-4) else address end) as imageURI from t_comment_images where status = 1 and (address != '' and address is not null ) and cid = ?";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($commentID));
		return $stmt->fetchAll();
	}

	private function acquire_user_info($uid) {
		$sql = <<<SQL
select users.uid,nickname.field_nickname_value as nickname,orders.guest_mail,name as userName, picture,
ifnull(concat('http://img1.zzkcdn.com/',substr(file.uri,10),'-userphotomedium.jpg'),
concat('http://img1.zzkcdn.com/',img.uri,'/2000x1500.jpg-userphotomedium.jpg')) url
from one_db.drupal_users users
left join LKYou.t_img_managed img on users.picture=img.fid
LEFT JOIN one_db.drupal_file_managed file ON users.picture=file.fid
LEFT JOIN one_db.drupal_field_data_field_nickname nickname ON users.uid=nickname.entity_id
LEFT JOIN LKYou.t_homestay_booking orders on users.uid=orders.guest_uid
where users.status = 1 and users.uid = :uid
SQL;
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		$results = $stmt->fetchAll();
		$result = $results[0];
        $guest_mail_name = explode("@", $result['guest_mail']);
        $guest_mail_name = substr($guest_mail_name[0], 0, -2);
		$userName = $result['nickname'];
		$userName = $userName ? $userName : $result['userName'];
        $userName = ($userName && substr($userName, 0, 3) !== "zzk") ? $userName : $guest_mail_name;
		$img_url = $result['url'];

		if(empty($img_url)) {
			return array('userName'=>$userName, 'userProfile'=>Util_Avatar::dispatch_avatar($uid));
		}else {
			return array('userName'=>$userName, 'userProfile'=>$img_url);
		}
	}

	private function echo_log_message($info) {
		foreach ($info as $key => $value) {
			echo $key.'='.$value;
		}
	}


    public function get_ridlist_by_uid($uid)
    {
	    $sql = <<<SQL
SELECT DISTINCT rid FROM LKYou.t_comment_info
WHERE uid=:uid AND (order_id = '' OR order_id IS NULL OR order_id = 0 )
ORDER BY id ASC
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('uid'=>$uid));
        return $stmt->fetchAll();

    }
	public function get_orderids_by_uid($uid){
		$sql = 'SELECT DISTINCT order_id FROM LKYou.t_comment_info WHERE uid=:uid AND status=1';
		$stmt=$this->slave_pdo->prepare($sql);
		$stmt->execute((array('uid'=>$uid)));
		return $stmt->fetchAll();
	}


    public function get_commentlist_by_rid_uid_noorder($uid,$rid){
        $sql = 'select rid from t_comment_info where uid=:uid and nid=:nid and order_id=0 ORDER BY id ASC';
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('uid'=>$uid,'nid'=>$rid));
        return $stmt->fetchAll();

    }

	public function count_homestay_comment($homestay_uid) {
		$sql = <<<SQL
SELECT count(id) FROM LKYou.t_comment_info
WHERE pid IS NULL AND nid=:homestay_uid AND status=1 AND whole_exp>0
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array(
			'homestay_uid' => $homestay_uid
		));
		return $stmt->fetchColumn();
	}







    //add by LCY 2015/6/30
    //手机首页的民宿评评论数获取
    public function get_comment_count($nid){
        $sql = 'select count(*) from t_comment_info where status = 1 and nid = ?';
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($nid));
        return $result=$stmt->fetchColumn();
    }


    public function get_comment_by_orderid($oid)
    {
        $sql = 'select * from t_comment_info where order_id=? and status =1';
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($oid));
        return $stmt->fetchAll();

    }

    public function get_comment_by_orderids($oids) {
        $sql = "select * from t_comment_info where order_id in (".Util_Common::placeholders("?", count($oids)).") "; 
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array_values($oids));
        return $stmt->fetchAll();
    }


    //计算用户评论表的区间之内评论数
    public function count_comments($uid,$start,$end){
        $sql = "SELECT COUNT(*) FROM t_comment_info WHERE uid = :uid AND Date(create_time) > :start_date AND Date(create_time) <:end_date ";
        $stmt = $this->lky_slave_pdo->prepare($sql);
        $stmt->execute(array('uid'=>$uid,'start_date'=>$start,'end_date'=>$end));
        $result = $stmt->fetchColumn();
        return $result;
    }




}
?>
