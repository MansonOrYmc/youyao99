<?php
apf_require_class("APF_DB_Factory");

class Dao_HomeStay_Spot {
	public function get_spot_byid($destid = 0, $locid = 0) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$where = $where2 = "";
        $pdoValue = array();
		if ($locid > 0) {
			$where = " and id= ? ";
            $pdoValue[] = $locid;
		}
		if ($destid > 0) {
			$where2 = " and dest_id= ? ";
            $pdoValue[] = $destid;
		}
		$sql = "select * from t_loc_poi where status=1 $where $where2";
		$stmt = $pdo->prepare($sql);
		$stmt->execute($pdoValue);
		return $stmt->fetchAll();
	}

	public function get_spot_list_by_loc_id($dest_id, $loc_id) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$sql = 'SELECT * FROM t_loc_poi WHERE status=1 AND locid=:loc_id AND dest_id=:dest_id';
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array('loc_id' => $loc_id, 'dest_id' => $dest_id));
		return $stmt->fetchAll();
	}

	public function get_spot_bypid($id) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$sql = "SELECT * FROM t_loc_poi WHERE id=?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array($id));
		return $stmt->fetch();
	}

    public function get_t_loc_poi($locid = '', $id = null, $dest_id = 10) {
        $loc_poi = array();
        $slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select `id`,`locid`,`poi_type`,`poi_name`,`google_map_lat`,`google_map_lng`,`search_radius`, `name_code` from t_loc_poi ";
        $sql .= " where `dest_id` = ?";
        $pdoValue[] = $dest_id;
        if (isset($id)) {
            $sql .= " and `id` = ? ";
            $pdoValue[] = $id;
        }
        if (isset($locid) && $locid != '') {
            $sql .= " and `locid` = ? ";
            $pdoValue[] = $locid;
        }
        $sql .= " and `status` = 1 ";
        $sql .= " order by `poi_rank` desc ";
        $stmt = $slave_pdo->prepare($sql);
        $stmt->execute($pdoValue);
        $result = $stmt->fetchAll();

        foreach ($result as $r) {
            $loc_poi[] = $r;
        }
        return $loc_poi;
    }

    public function t_loc_poi_all($status = 1) {
        $slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from t_loc_poi where status = ?";
        $stmt = $slave_pdo->prepare($sql);
        $stmt->execute(array($status));
        return $stmt->fetchAll();
    }

    public function get_t_loc_type($dest_id = 10) { //taiwan
        $loc_type = array();
        $slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select `id`,`locid`,`name_code`,`type_name` from t_loc_type ";
        $sql .= "where `status` = 1 ";
        $sql .= "and `dest_id` = ? ";
        $sql .= "order by `rank` asc , `room_num` desc";
        $stmt = $slave_pdo->prepare($sql);
        $stmt->execute(array($dest_id));
        $results = $stmt->fetchAll();
        foreach ($results as $r) {
            $loc_type[] = $r;
        }
        return $loc_type;
    }

    public function t_loc_type_all($status) {
        $slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from t_loc_type where status = ?";
        $stmt = $slave_pdo->prepare($sql);
        $stmt->execute(array($status));
        return $stmt->fetchAll();
    }

    public function get_t_room_model_byid($id=14) {

        $id = (int)$id;
        $slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from t_room_model ";
        $sql .= "where `dest_id` = 10 ";
        $sql .= "and `status` = 1 ";
        $sql .= "and `id` = ? ";

        $stmt = $slave_pdo->prepare($sql);
        $stmt->execute(array($id));
        $result = $stmt->fetch();

        if ($result) {
            return $result;
        }
        return array();
    }

    public function get_t_room_price_byid($id) {

        $id = (int)$id;
        $slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from t_room_price ";
        $sql .= "where `dest_id` = 10 ";
        $sql .= "and `status` = 1 ";
        $sql .= "and `id` = ? ";

        $stmt = $slave_pdo->prepare($sql);
        $stmt->execute(array($id));
        $result = $stmt->fetch();

        if ($result) {
            return $result;
        }
        return array();


    }

    public function get_t_room_price() {   

        $slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select `id`,`price`,`trans_key`,`condtion`,`upper_limit`,`lower_Limit` from t_room_price ";
        $sql .= "where `dest_id` = 10 ";
        $sql .= "and `status` = 1 ";
        $sql .= "order by `rank` asc ";
        
        $stmt = $slave_pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        
        if ($result) {
            return $result;
        }
        return array();
    }

    public function get_t_room_model() {
        
        $slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select `id`,`model`,`trans_key`,`condtion` from t_room_model ";
        $sql .= "where `dest_id` = 10 ";
        $sql .= "and `status` = 1 ";
        $sql .= "order by `rank`  asc ";

        $stmt = $slave_pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        if ($result) {
            return $result;
        }
        return array();


    }

}

?>
