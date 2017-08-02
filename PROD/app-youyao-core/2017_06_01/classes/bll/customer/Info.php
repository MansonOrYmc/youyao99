<?php
class Bll_Customer_Info {
     private $dao;
	 public function __construct() {
        $this->dao    = new Dao_Customer_InfoMemcache();
    }

    public function get_customer_info_byid($ids){
    	return $this->dao->get_customer_info_byid($ids);
    }
    
    public function get_customer_info_bymail($mail){
       return $this->dao->get_customer_info_bymail($mail);	
    }

    public function get_customer_info_byphone($phone) {
        return $this->dao->get_customer_info_byphone($phone);
    }

    public function get_user_info_byid($ids){
       return $this->dao->get_user_info_byid($ids);	
    }

	public function insert_new_customer($data){

		$keyarr = array(
				'name', 
				'email', 
				'mobile', 
				'pnum', 
				'days', 
				'remark', 
				'status', 
				'province', 
				'client_ip', 
				'qq', 
				'last_admin_uid', 
				'last_modify_date', 
				'twcheckin', 
				'twcheckout', 
				'last_order_date', 
				'first_admin_uid', 
				'sales_flag', 
				'quality', 
				'pcnum', 
				'pcage', 
				'campaign_code', 
				'zzkcamp', 
				'dest_id', 
				'zfansref', 
			);
		foreach($data as $k=>$v) {
			if(!in_array($k, $keyarr)) continue;
			$params[$k] = $v;
		}
		$params['create_time'] = time();
		return $this->dao->insert_new_customer($params);
	}
    
}
