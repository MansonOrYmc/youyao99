<?php
class Bll_Follow_Record {
     private $dao;
	 public function __construct() {
        $this->dao    = new Dao_Follow_RecordMemcache();
    }
    public function add_follow_reocrd($params){
        $this->dao->add_follow_reocrd($params);
        $this->dao->add_oldfollow_reocrd($params);
    }
    
    public function get_reocrd_byorderid($order_id){
    	return $this->dao->get_reocrd_byorderid($order_id);
    }
    
    public function get_reocrd_byphone($phone){
       return $this->dao->get_reocrd_byphone($phone);	
    }
    
    public function get_reocrd_bydate($sdate,$edate){
       return $this->dao->get_reocrd_bydate($sdate,$edate);	
    }
}