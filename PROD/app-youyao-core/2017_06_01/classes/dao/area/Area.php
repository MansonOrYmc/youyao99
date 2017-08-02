<?php
apf_require_class("APF_DB_Factory");

class Dao_Area_Area {

	private $pdo;
	private $slave_pdo;
    private $one_pdo;

    public function __construct() {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
	    $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
    }

    public function get_area_by_destid($destid) {
        $hot_so = new So_Hot();
        $new =$hot_so->get_hot_list($destid);
        return $new;
//原来的逻辑被注释了！
//        $sql = "SELECT * FROM t_loc_type WHERE  dest_id=? AND status=1 ORDER BY rank ASC";
//        $stmt = $this->slave_pdo->prepare($sql);
//        $stmt->execute(array($destid));
//        return $stmt->fetchAll();
    }

	public function get_area_list_by_destid($destid) {
        $sql = "SELECT * FROM t_loc_type WHERE  dest_id=? AND status=1 ORDER BY rank ASC";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($destid));
        return $stmt->fetchAll();
	}

    public function get_first_area_by_destid($destid) {
        $sql = "SELECT * FROM t_loc_type WHERE  dest_id=? AND status=1 ORDER BY rank ASC LIMIT 1";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($destid));
        return $stmt->fetch();
    }

    public function get_area_by_locid($locid, $parent_id) {
        $sql = "select * from t_loc_type where parent_id = ? and locid = ?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($parent_id, $locid));
        return $stmt->fetch();
    }

    public function get_area_only_by_locid($locid) {
        $sql = "SELECT t_loc_type.*,t_dest_config.dest_name
                FROM t_loc_type LEFT JOIN t_dest_config
                ON t_loc_type.dest_id=t_dest_config.dest_id
                WHERE t_loc_type.locid = ?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($locid));
        return $stmt->fetch();
    }

    public function get_dest_config($dest_id) {
	    $sql = "select id, dest_id, domain, default_language, pay_channel, exchange_rate, dest_name, currency_ios_code, currency_code from t_dest_config where dest_id = ?";
	    $stmt = $this->slave_pdo->prepare($sql);
	    $stmt->execute(array($dest_id));
	    return $stmt->fetch();
    }

    public function dao_get_dest_language($dest_id,$key) {
	    $dest_id = $dest_id ? $dest_id : 10;
        $sql = "select id, l_key, dest_id, l_desc from m_dest_language where l_key = ? and dest_id in (".$dest_id.",".Const_Default_Dest_ID.") order by dest_id desc";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($key));
        return $stmt->fetch();
    }
    
    public function get_city_list() {
        $sql = "select * from t_dest_config where status = 1";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function get_area_by_id($ids) {
        $sql = "SELECT * FROM t_loc_type WHERE  id in (".implode(',',$ids).")";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

	public function get_area_by_cityname($cityname) {
		$sql = "SELECT * FROM t_loc_type WHERE type_name like '%".$cityname."%' and status = 1 ";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetch();
	}

    /**
     * @return array
     * 获取当前目的地的城市列表
     */
    public function get_t_loc_type($destid)
    {
        if(preg_match('/zh.youyao.com/',$_SERVER['HTTP_HOST'])||($_GET['dest']=='zh'))
        {
            $dest_id = 12;
        }else if(preg_match('/kr.youyao.com/',$_SERVER['HTTP_HOST'])||($_GET['dest']=='kr'))
        {
            $dest_id = 15;
        }else if(preg_match('/taiwan.youyao.com/',$_SERVER['HTTP_HOST'])||($_GET['dest']=='tw'))
        {
            $dest_id = 10;
        }else if(preg_match('/japan.youyao.com/',$_SERVER['HTTP_HOST'])||($_GET['dest']=='jp'))
        {
            $dest_id = 11;
        }else if(preg_match('/us.youyao.com/',$_SERVER['HTTP_HOST'])||($_GET['dest']=='us'))
        {
            $dest_id = 13;
        }else if(preg_match('/hk.youyao.com/',$_SERVER['HTTP_HOST'])||($_GET['dest']=='hk'))
        {
            $dest_id = 14;
        }else{
            $dest_id = 10;
        }
        if($destid>0){
            $dest_id=$destid;
        }

        $sql = "select `id` ,`locid`,`name_code`,`type_name` from `LKYou`.`t_loc_type` where `status` =1 and `dest_id`= '$dest_id' order by `rank` asc ,`room_num` desc ";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $results=$stmt->fetchAll();
        return $results;
    }

    public function get_dest_cities($destId) {
        $sql = "select * from t_loc_type where status = 1 and parent_id = :parent_id order by `rank` asc , `room_num` desc";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('parent_id' => $destId));
        return $stmt->fetchAll();
    }

    public function get_loc_type_by_id($id) {
        $sql = "select * from t_loc_type where id = :id";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('id' => $id));
        return $stmt->fetch();
    }

    public function get_loc_type_by_locid($locid) {
        $sql = "select * from t_loc_type where locid = :locid";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('locid' => $locid));
        return $stmt->fetch();
    }

    public function get_loc_type_by_namecode($namecode) {
        $sql = "SELECT * FROM LKYou.t_loc_type WHERE name_code = :namecode";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('namecode' => $namecode));
        return $stmt->fetch();
    }

    public function active_loc_type_by_namecode($namecode) {
        $sql = "SELECT * FROM LKYou.t_loc_type WHERE name_code = :namecode and status = 1";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('namecode' => $namecode));
        return $stmt->fetch();
    }

    public function get_loc_type_by_typecode($typecode) {
        $sql = "SELECT * FROM LKYou.t_loc_type WHERE type_code = :typecode";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('typecode' => $typecode));
        return $stmt->fetch();
    }

    public function get_city_regions_by_locid($locid) {
        if (empty($locid)) return array();
        if (!($loctype = $this->get_loc_type_by_locid($locid))) return array();

        return $this->get_city_regions_by_parent_id($loctype['parent_id']);
    }

    public function get_city_regions_by_parent_id($parentId, $area_level=null) {
        if (empty($parentId)) return array();

        $pdo_value = array(
                'parent_id' => $parentId,
            );
        if($area_level!==null) {
            $condition = "and area_level = :level";
            $pdo_value['level'] = $area_level;
        }
        $sql = "select * from t_loc_type where status = 1 and parent_id = :parent_id $condition";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute($pdo_value);
        return $stmt->fetchAll();
    }

    public function get_locid_by_namecode($namecode){
        $sql="select locid from t_loc_type where name_code = :namecode";
        $stmt=$this->slave_pdo->prepare($sql);
        $stmt->execute(array('namecode'=>$namecode));
        return $stmt->fetchColumn();
    }

    public function get_loc_by_type_code($type_code) {
        $sql = "select * from t_loc_type where type_code = ?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($type_code));
        return $stmt->fetch();
    }

    public function get_home_destid_by_uid($uid) {
        $sql = "select dest_id from drupal_users where uid = ?";
        $stmt = $this->one_pdo->prepare($sql);
        $stmt->execute(array($uid));
        return $stmt->fetchColumn();
    }

}
