<?php
class Bll_Activity_Luck {
     private $dao;
	 public function __construct() {
        $this->dao    = new Dao_Activity_LuckMemcache();
    }
    
    public function add_luck_record($params){
    	return  $this->dao->add_luck_record($params);
    }

     public function get_luck_records(){
    	return  $this->dao->get_luck_records();
    }
}