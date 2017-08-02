<?php
class Bll_Area_Area {
     private $dao;
     private $get_dest_config_by_destid;
	 public function __construct() {
        $this->dao    = new Dao_Area_AreaMemcache();
    }
    
    public function get_area_by_destid($destid){
        //强制更换为hot!!!!
        //hahahhahaha
        $sohot = new So_Hot();
        return  $sohot->get_hot_list($destid);
        //退回请将注释去掉！！！！
    	//return  $this->dao->get_area_by_destid($destid);
    }

	// 查询目的地的所有区县
	public function get_area_list_by_destid($destid) {
		return $this->dao->get_area_list_by_destid($destid);
	}

    public function get_area_by_locid($locid, $parent_id=10) {
    	return $this->dao->get_area_by_locid($locid, $parent_id);
    }

    public function get_area_only_by_locid($locid) {
    	return $this->dao->get_area_only_by_locid($locid);
    }

    public function get_dest_config_by_destid($destid) {
        if(isset($this->get_dest_config_by_destid[$destid])){
        }else{
            $this->get_dest_config_by_destid[$destid] = $this->dao->get_dest_config($destid);
        }
        return $this->get_dest_config_by_destid[$destid];
    }
    
    public function get_loc_type_by_locid($locid) {
        if(!$locid) return;
        return $this->dao->get_loc_type_by_locid($locid);
    }

    public function get_loc_type_by_namecode($namecode) {
        if(!$namecode) return;
        return $this->dao->get_loc_type_by_namecode($namecode);
    }

    public function active_loc_type_by_namecode($namecode) {
        if(!$namecode) return 0 ;
        return $this->dao->active_loc_type_by_namecode($namecode);
    }

    public function get_dest_language($dest_id,$key) {
        $dest_lang = $this->dao->dao_get_dest_language($dest_id,$key);
        return $dest_lang['l_desc'];
    }
    
    public function get_city_list() {
        return $this->dao->get_city_list();
    }
    
   public function get_area_by_id($ids) {
		if(!$ids) return;
		if(!is_array($ids)) $ids = array($ids);
        return $this->dao->get_area_by_id($ids);
    }

	public function get_area_by_cityname($cityname) {
		return $this->dao->get_area_by_cityname($cityname);
	}

    public function search_current_dest_id() {
        if($_REQUEST['dest'])
        {
            return $_REQUEST['dest'];
        }
		$city = self::get_city_list();
        if ($dest_id = self::obtain_destid_by_domain($result)) {
            return $dest_id; 
        } else {
            return 10;
        }
    }

	public function get_current_desc_id() {
        if($_REQUEST['dest'])
        {
            return $_REQUEST['dest'];
        }
		$city = self::get_city_list();
		if($dest_id = self::obtain_destid_by_domain($city)) {
			return $dest_id;
		}else{
			return 12;
		}
		
	}

	function obtain_destid_by_domain($mappings) {
		$domain = strtolower($_SERVER["HTTP_HOST"]);
		if (!$domain) {
			return false;
		}
		foreach ($mappings as $mapping) {
			if (preg_match('/^'.$mapping['domain'].'/', $domain)) {
				return $mapping['dest_id'];
			}
		}
		return false;

	}

	public function get_locid_by_namecode($namecode){
		return $this->dao->get_locid_by_namecode($namecode);
	}

    public function get_area_array_by_typecode($typecode) {
        $ret = array();
        $last_loc = $this->get_loc_type_by_typecode($typecode);
        $ret[] = $last_loc;
        $loc = $this->dao->get_loc_type_by_id($last_loc['parent_id']);
        while($loc) {
            $ret[] = $loc;
            $loc = $this->dao->get_loc_type_by_id($loc['parent_id']);
        }
        return $ret;
    }

    public function get_loc_type_by_typecode($typecode) {
        return $this->dao->get_loc_type_by_typecode($typecode);
    }

    public function get_loc_by_type_code($type_code) {
        if(!$type_code) return;
        return $this->dao->get_loc_by_type_code($type_code);
    }

    public function get_home_destid_by_uid($uid) {
        if(!$uid) return;
        return $this->dao->get_home_destid_by_uid($uid);
    }

    public function get_dest_cities($parent_id) {
        if(!$parent_id) return;
        return $this->dao->get_dest_cities($parent_id);
    }
}
