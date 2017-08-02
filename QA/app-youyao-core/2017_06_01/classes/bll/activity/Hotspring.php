<?php
class Bll_Activity_Hotspring {
     private $dao;
	 public function __construct() {
        $this->dao    = new Dao_Activity_HotspringMemcache();
    }
    
    public function add_hotspring_record($params){
    	return  $this->dao->add_hotspring_record($params);
    }

     public function get_hotspring_records(){
    	return  $this->dao->get_hotspring_records();
    }
}