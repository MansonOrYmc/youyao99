<?php
apf_require_class("APF_DB_Factory");

class Dao_Homepage_HomepageInfo {

	private $pdo;
	private $slave_pdo;

	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $this->one_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
	}

	public function homepage_cache($preview = 0) {
		if($preview == 1) {
			$sql = "select * from t_frontpage_cache where type = 4 and status in (1,2) and comment <> 'isdeleted' order by dest_id asc, weight asc, id desc ";
		} else {
			$sql = "select * from t_frontpage_cache where type = 4 and status = 1 order by dest_id asc, weight asc, id desc";
		}
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

    public function homepage_cache_all($preview = 0) {
		if($preview == 1) {
			$sql = "select * from t_frontpage_cache where status in (1,2) and comment <> 'isdeleted' order by dest_id asc, weight asc, id desc ";
		} else {
			$sql = "select * from t_frontpage_cache where status = 1 order by dest_id asc, weight asc, id desc";
		}
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
    }

    public function homepage_data_cache()
    {
        $sql = "select `type`,`htmlcontent` from t_frontpage_cache ";
        $sql.="where `type` in ('1', '2', '3') ";
        $sql.="and status = 1";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $homepage= $stmt->fetchAll();
        $result = new stdClass();
        foreach ($homepage as $key => $value) {
            $value = (object)$value;
            if ($value->type == 1) {
                $result->customer = number_format($value->htmlcontent);
            }
            if ($value->type == 2) {
                $result->comment = number_format($value->htmlcontent);
            }
            if ($value->type == 3) {
                $result->bnbmasterreply = number_format($value->htmlcontent);
            }
        }
        return $result;
    }
    
	public function get_dest_list() {
		$sql = "select id,dest_id,domain,default_language,pay_channel,exchange_rate,dest_name from t_dest_config where status = 1 ";	
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function get_loc_list() {
		$sql = "SELECT id,locid,name_code,type_name,dest_id FROM t_loc_type WHERE status = 1 ORDER BY rank ASC, room_num DESC ";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
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
    public function is_homestay_firstorder($uid){
        $sql = "select count(*) from LKYou.t_homestay_booking where status in (0,1,2,4,6,7) and uid= :uid ";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        return $stmt->fetchColumn();
    }

    public function modify_front_page_status($status, $condition) {
        $pdoVal[] = $status;
        $where = "";
        foreach($condition as $k=>$r) {
            if(!$where) $where = " where";
            else $where .= " and";
            $where .= " $k = ?";
            $pdoVal[] = $r;
        }
        $sql = "update t_frontpage_cache set status = ? " . $where;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($pdoVal);
    }

    public function add_new_item($type, $htmlcontent, $weight, $status, $dest_id, $admin_uid) {
        $sql = "insert into t_frontpage_cache (`type`, `htmlcontent`, `weight`, `status`, `dest_id`, uid) values (?,?,?,?,?,?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(
            $type, $htmlcontent, $weight, $status, $dest_id, $admin_uid
        ));
    }

} 
