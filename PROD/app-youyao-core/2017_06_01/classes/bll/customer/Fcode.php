<?php
class Bll_Customer_Fcode {
     private $dao;
	 public function __construct() {
        $this->dao    = new Dao_Fcode_RecordSuccMemcache();
    }

    public function add_fc_reocrd($fcode){
    	return $this->dao->add_fc_reocrd($fcode);
    }
    
    public function get_fc_recomm($uid){
    	return $this->dao->get_fc_recomm($uid);
    }
    
    public function get_recomm_bydid($uid){
    	return $this->dao->get_recomm_bydid($uid);
    }
    
    public function update_fc_reocrd($id){
    	return $this->dao->update_fc_reocrd($id);
    }
}