<?php
apf_require_class("APF_DB_Factory");

class Dao_Room_RoomInfo {

    private $pdo;
    private $one_pdo;

    public function __construct() {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
	    $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
	    $this->one_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
    }

    public function get_minimum_stay_dates($nid){
        $sql = "select * from t_minimumstay_date where rid = '$nid' where status = 1 and ";
    }
    public function add_new_room($uid,$title,$dest_id,$status=0){
        $this->one_pdo->beginTransaction();
        //先写更新
        $sql = "insert drupal_node (uid,title,status,dest_id,type,language,created,comment) values (?,?,?,?,?,?,?,?)";
        $stmt = $this->one_pdo->prepare($sql);
        $stmt->execute(array($uid,$title,$status,$dest_id,'article','zh-hans',time(),2));
        $nid = (int)$this->one_pdo->lastInsertId();
        $sql = "insert drupal_node_revision (nid,uid,title,comment) values (?,?,?,?)";
        $stmt = $this->one_pdo->prepare($sql);
        $stmt->execute(array($nid,$uid,$title,2));
        $vid = (int)$this->one_pdo->lastInsertId();
        $sql = "update drupal_node set vid = ? where nid = ?";
        $stmt = $this->one_pdo->prepare($sql);
        $stmt->execute(array($vid,$nid));
        $this->one_pdo->commit();
        return $nid;
    }
    //把drupal_node的信息保存起来
    public function zzk_set_room_base_info($nid,$baseinfo){
        $apf = APF::get_instance();
        $req = $apf->get_request();
        $user = $req->get_userobject();
        $set = array();
        $baseinfo['changed'] = time();
        foreach($baseinfo as $key => $value){
            $set[] = $key." = '".$value."'";
        }
        $set_sql = implode(' ,',$set);
        //先写更新
        $sql = "update drupal_node set ".$set_sql." where nid = '$nid'";
        $stmt = $this->one_pdo->prepare($sql);
        return $stmt->execute();
    }

    public function set_room_base_info_byuid($uid, $baseinfo) {
        if(empty($baseinfo) || !$uid) return;
        $user = APF::get_instance()->get_request()->get_userobject();
        $baseinfo['changed'] = time();
        foreach($baseinfo as $key => $value){
            $set[] = $key." = '".$value."'";
        }
        $set_sql = implode(' ,',$set);
        $sql = "update drupal_node set ".$set_sql." where uid = '$uid'";
        $stmt = $this->one_pdo->prepare($sql);
        return $stmt->execute();
    }

    public function zzk_comment_avg_rating($nid, $uid=0) {
      $rate_num = 0;
      $rate_sum = 0;
      $avg_rate = 0;
      if($uid>0) {
        $sql = "select id, whole_exp from LKYou.t_comment_info where rid = ? and status = 1 and uid = ? and whole_exp > 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($nid, $uid));
        // $cids = zzkwww_select('t_comment_info', 'c')->fields('c', array('id','whole_exp'))->condition('rid', $nid, '=')->condition('status', 1, '=')->condition('uid', $uid, '<>')->execute()->fetchAll();
      }
      else{
        $sql = "select id, whole_exp from LKYou.t_comment_info where rid = ? and status = 1 and whole_exp > 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($nid));
        // $cids = zzkwww_select('t_comment_info', 'c')->fields('c', array('id','whole_exp'))->condition('rid', $nid, '=')->condition('status', 1, '=')->execute()->fetchAll();
      }
      $cids = $stmt->fetchAll(PDO::FETCH_OBJ);
      //var_dump($cids);
      foreach($cids as $cid){
    #     $rating = db_select('field_data_field_rating', 'r')->fields('r', array('field_rating_rating'))->condition('entity_id', $cid, '=')->condition('deleted', 0, '=')->execute()->fetchField();
         $rating = $cid->whole_exp;
         $rate_num += 1;
         $rate_sum += $rating;
      }
      $avg_rate = $rate_sum > 0 ? round($rate_sum/$rate_num,1) : 0;
      return $avg_rate;
    }

    public function get_room_title_by_nid($nid) {
        $sql = "select title from drupal_node where nid = ?";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute(array($nid));
        return $stmt->fetchColumn();
    }

    public function dao_node_room_trac_status_new($nid,$d) {
        $sql = "select id, nid, room_date, room_price, room_num, uid from t_room_status_tracs where nid = ? and room_date = ? order by room_date desc";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($nid, $d));
        return $stmt->fetch();
    }

    public function get_room_num_by_nid_and_date($nid, $room_date) {
        $sql = "select room_num from t_room_status_tracs where nid = ? and room_date = ?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($nid, $room_date));
        return $stmt->fetchColumn();
    }

	public function get_beds_num_by_nid_and_date($room_id, $room_date) {
		$sql = "SELECT beds_num FROM t_room_status_tracs WHERE nid = :room_id AND room_date = :room_date";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array(
			'room_id' => $room_id,
			'room_date' => $room_date
		));
		return $stmt->fetchColumn();
	}

    public function dao_update_user_order_succ_by_nid($nid, $order_succ) {
        $sql = "update drupal_node set order_succ = (order_succ + ?) where nid = ?";
        $stmt = $this->one_pdo->prepare($sql);
        return $stmt->execute(array($order_succ, $nid));
    }

    public function get_room_uid_by_nid($nid) {
        $sql = "select uid from one_db.drupal_node where nid = ?";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute(array($nid));
        return $stmt->fetchColumn();
    }

    public function get_room_statue_by_nid($nid) {
        $sql = "select status from drupal_node where nid = ?";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute(array($nid));
        return $stmt->fetchColumn();
    }

    public function dao_update_room_num_by_nid_and_date($room_num, $nid, $room_date) {
        $sql = "update t_room_status_tracs set room_num = ? where nid = ? and room_date = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($room_num, $nid, $room_date));
    }

    public function room_node_beds($nid) {
        $sql = "select field_room_beds_tid from one_db.drupal_field_data_field_room_beds where entity_id = ?";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute(array($nid));
        return $stmt->fetchColumn();
    }

    public function dao_insert_room_tracs_room_num($info) {
        $sql = <<<SQL
INSERT INTO LKYou.t_room_status_tracs(nid,room_price,room_date,room_num,uid,create_date,update_date,flag1,log_booking)
VALUES(:room_id,'0',:room_date,:room_num,'1',:create_date,:update_date,'6',:log_booking)
ON DUPLICATE KEY UPDATE room_num=:room_num,uid='1',update_date=:update_date,log_booking=:log_booking
SQL;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($info);
    }

	public function dao_insert_room_tracs_beds_num($info) {
		$sql = <<<SQL
INSERT INTO LKYou.t_room_status_tracs(nid,room_price,room_date,beds_num,uid,create_date,update_date,flag1,log_booking)
VALUES(:room_id,'0',:room_date,:beds_num,'1',:create_date,:update_date,'6',:log_booking)
ON DUPLICATE KEY UPDATE beds_num=:beds_num,uid='1',update_date=:update_date,log_booking=:log_booking
SQL;
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($info);
	}

    public function get_room_status_tracs($nid) {
        $sql = "select id, nid, room_date, room_price, room_num, beds_num, uid from t_room_status_tracs where nid = ? order by room_date desc";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($nid));
        return $stmt->fetchAll();
    }

    public function get_room_status_tracs_valid_date($nid) {
        $sql = "select id, nid, room_date, room_price, room_num, beds_num, uid from t_room_status_tracs where nid = ? and room_date >= ? order by room_date asc";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($nid, date('Y-m-d')));
        return $stmt->fetchAll();
    }

    public function get_room_status_tracs_by_nid_and_date($nid, $room_date) {
        $sql = "select id, nid, room_date, room_price, room_num, uid from LKYou.t_room_status_tracs where nid = ? and room_date = ? order by room_price asc";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($nid, $room_date));
        return $stmt->fetch();
    }

    public function get_room_status_tracs_by_nid_and_dateinterval($nid, $checkin, $checkout) {
        $sql = "select id, nid, room_date, room_price, room_num, beds_num, uid from LKYou.t_room_status_tracs where nid = ? and room_date >= ? and room_date < ? order by room_price asc";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($nid, $checkin, $checkout));
        $r =$stmt->fetchAll();
        return $r;
    }

    public function zzk_speed_room($nid) {
        $sql = "select speed_room from drupal_node where nid = ?";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute(array($nid));
        return $stmt->fetchColumn();
    }

    public function room_price_count_check($nid) {
        $sql = "select room_price_count_check from drupal_node where nid = ?";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute(array($nid));
        return $stmt->fetchColumn();
    }

    public function get_roominfo_by_uids($arrayuids,$status=1) {
        $ids = implode(',',$arrayuids);
        $sql = "select * from drupal_node where uid in ($ids) and speed_room=? and status=1";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute(array($status));
        return $stmt->fetchAll();
    }

    // 和上面的类似
    public function get_roominfo_by_uids_withoutspeed($arrayuids, $status=null) {
        $uids = implode(',',$arrayuids);
        $status = is_array($status) ? implode(',',$status) : $status;
        if(!is_null($status)) {
            $statusStr = " and status in ($status) order by status desc";
        }
        $sql = "select * from drupal_node where uid in ($uids) $statusStr";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function get_roomstatus_by_uids($arrayuids,$startdate,$enddate) {
        $ids = implode(',',$arrayuids);
        $sql = "select * from t_room_status_tracs where nid in ($ids) and ?<=room_date and room_date<=?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($startdate,$enddate));
        return $stmt->fetchAll();
    }

    public function room_detail_contact_order($nid) {
        //房间信息
        $sql = "select speed_room, dest_id, add_bed_num, add_bed_price,add_beds_num,add_beds_price, uid, room_price_count_check,add_bed_check,add_beds_check,status from drupal_node where nid = ?";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute(array($nid));
        $node_row = $stmt->fetch();
        $node_row['dest_id'] = $node_row['dest_id']?$node_row['dest_id']:10;
        //房型
        $node_row['room_model'] = self::node_module_by_field_room_beds($nid);
        return (object)$node_row;
    }

    public function get_dest_id_by_nid($nid) {
        $sql = "select dest_id from drupal_node where nid = ?";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute(array($nid));
        return $stmt->fetchColumn();
    }

    /*
    author:axing
    function:房间几人房函数
    param:nid
    return:房型modules
    */
    function node_module_by_field_room_beds($nid){
        //原始的房型数组
        $array = array(
               '320'=>0,
               '309'=>1,
               '310'=>2,
               '315'=>3,
               '313'=>4,
               '321'=>5,
               '312'=>6,
               '322'=>7,
               '311'=>8,
               '323'=>9,
               '314'=>10,
                   );
        //通过nid查找房型
        $sql = "select field_room_beds_tid from drupal_field_data_field_room_beds where entity_id = ? limit 0, 1";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute(array($nid));
        $results = $stmt->fetchColumn();
        return $array[$results];
    }

    /*
     * 获取房态数据，用于动态显示日历房态信息，不排除room_num = 0的数据
     */
    function fetch_calendar_room_status($nid, $sDateText, $eDateText) {
        $sql = "select room_date, room_price, room_num, beds_num from t_room_status_tracs where nid = ? and room_date >= ? and room_date <= ? order by room_date asc";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($nid, $sDateText, $eDateText));
        return $stmt->fetchAll();
    }

    function fetch_room_price_config($uid) {
        $sql = "select room_date, room_price from t_rpconfig_v2 where uid = ?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($uid));
        $result = $stmt->fetch();
        if ($result) {
            return $result;
        }else {
            return array();
        }
    }

    function get_room_price_config_count($uid) {
        $sql = "select count(*) count from t_rpconfig_v2 where status = 1 and uid = ?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($uid));
        return $stmt->fetchColumn();
    }

	public function get_room_images($id, $limit = 10) {
		$sql = <<<SQL
SELECT t.field_image_fid,t.field_image_version,f.uri,f2.uri AS new_uri FROM drupal_field_data_field_image t
LEFT JOIN drupal_file_managed f ON f.fid=t.field_image_fid
LEFT JOIN LKYou.t_img_managed f2 ON f2.fid=t.field_image_fid
WHERE (t.entity_id = :id) AND (t.entity_type = 'node') AND (t.bundle = 'article')
ORDER BY delta ASC
LIMIT :limit
SQL;
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->bindParam('id', $id, PDO::PARAM_INT);
		$stmt->bindParam('limit', $limit, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function get_room_detail_by_nid($nid) {
		$sql = "SELECT * FROM drupal_node WHERE nid = ?";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($nid));
		return $stmt->fetch();
	}

	public function get_field_price_range_value($rid){
		$sql = 'SELECT field_price_range_value FROM drupal_field_data_field_price_range where entity_id = :entity_id';
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array('entity_id'=>$rid));
		return $stmt->fetchColumn();
	}

	public function get_field__fangjiashuoming_value($rid) {
		$sql = 'SELECT field__fangjiashuoming_value FROM drupal_field_data_field__fangjiashuoming WHERE entity_id = :entity_id';
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array('entity_id'=>$rid));
		return $stmt->fetchColumn();
	}

	public function get_body_value($rid){
		$sql = 'SELECT body_value FROM drupal_field_data_body WHERE entity_id=:entity_id';
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array('entity_id'=>$rid));
		return $stmt->fetchColumn();
	}

	public function get_roomsetting($rid){
		$sql = 'SELECT roomsetting FROM drupal_node where nid = :nid';
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array('nid'=>$rid));
		return $stmt->fetchColumn();
	}

	public function get_node_revision_bynid($nid) {
		$nidStr = implode(", ", $nid);
		$sql = "select * from drupal_node_revision where nid in ($nidStr) ";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

    public function get_room_addition($room_id) {
        $sql = 'SELECT room_floor,elevator,bed_style_remark,add_beds_check,add_beds_price,add_beds_num FROM one_db.drupal_node WHERE nid=:room_id';
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute(array('room_id' => $room_id));
        return $stmt->fetch();
    }

    public function get_room_favorite($uid, $nid) {
        $sql = "SELECT * FROM t_collect WHERE uid=:uid AND hid=:nid and type='r' AND status=1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':uid'=>(int)$uid, ':nid'=>(int)$nid));
        return $stmt->fetchColumn();
    }

    public function update_rpconfig($params, $condition){
        $pdoValue = array();
        foreach($params as $k=>$v) {
            if($field) $field .= ", ";
            $field .= $k." = :$k ";
            $pdoValue[":".$k] = $v;
        }
        foreach($condition as $key=>$val) {
            if($where) $where .= " AND ";
            $where .= $key." = :$key";
            $pdoValue[":".$key] = $val;
        }
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "update t_rpconfig_v2 set $field where $where";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($pdoValue);
        if(!empty($condition['uid'])){
            Bll_User_Static::update_rp_config($condition['uid']);
        }
    }

    public function insert_rpconfig($params) {
        $pdoValue = array();
        foreach($params as $key=>$val) {
            $field[] = $key;
            $placeholder[] = "?";
            $pdoValue[] = $val;
        }

        $sql = "insert into t_rpconfig_v2 (".implode(",", $field).") values (".implode(",", $placeholder).")";
        $stmt = $this->pdo->prepare($sql);
        return $stmt = $stmt->execute($pdoValue);
    }

    public function get_channel_discount($nid, $channel, $active=1) {
        $sql = "select * from t_open_discount where room_type_id = ? and channel = ? and active = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($nid, $channel, $active));
        return $stmt->fetch();
    }

    public function get_filter_room_list($filter, $page, $sort, $limit) {

        $condition = array();
        $pdo_val = array();
        if($filter['destId']) {
            $condition[] = 'node.dest_id = ?';
            $pdo_val[] = $filter['dest_id'];
        }
        if($filter['status'] > 0) {
            $condition[] = 'node.status = ?';
            $pdo_val[] = $filter['status'];
        }
        if($filter['speed']) {
            if($filter['speed'] == 'isSpeed') {
                $condition[] = 'node.speed_room = 1';
            } elseif($filter['speed'] == 'notSpeed') {
                $condition[] = 'node.speed_room = 0';
            } elseif($filter['speed'] == 'audit') {
                $condition[] = 'node.speed_room_apply in (1, 2)';
            }
        }
        if($fitler['homestayName']) {
            $condition[] = 'user.name like ?';
            $pdo_val[] = "%". trim($filter['homestayName']) ."%";
        }
        if($fitler['homestayMail']) {
            $condition[] = 'user.mail like ?';
            $pdo_val[] = "%". trim($filter['homestayMail']) ."%";
        }

        if(!empty($condition)) {
            $where = "where " . implode(" AND ", $condition);
        }

        if(!empty($sort)) {
            $order = "order by node.". $sort['field'] ." ". $sort['order'];
        }

        if($limit) {
            $range = "limit " . (($page-1) * $limit) . ", " . $limit;
        }

        $sql = "select 
            *,node.status as room_status, user.status as homestay_status, node.created as room_created, poi.type as homestay_type
from 
    one_db.drupal_node node 
    left join one_db.drupal_users user on node.uid = user.uid 
    left join LKYou.t_weibo_poi_tw poi on poi.uid = node.uid
$where $order $range";
        $sqlCount = "select count(*) from one_db.drupal_node node left join one_db.drupal_users user on node.uid = user.uid $where $order";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute($pdo_val);
        $stmt2 = $this->one_slave_pdo->prepare($sqlCount);
        $stmt2->execute($pdo_val);

        $result = $stmt->fetchAll();
        $numFound = $stmt2->fetchColumn();
        return array(
            $numFound,
            $result,
        );
    }

}
