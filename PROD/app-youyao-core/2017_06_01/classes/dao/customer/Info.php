<?php
apf_require_class("APF_DB_Factory");

class Dao_Customer_Info {
    public function get_customer_info_byid($ids) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $ids = implode(',',$ids);
        $sql = "select * from t_customer where id in ($ids)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function get_customer_info_bymail($mail) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from t_customer where email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($mail));
        return $stmt->fetch();
    }
    
    public function get_user_info_byid($ids) { 
        $pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
        $ids = implode(',',$ids);
        $sql = "select * from drupal_users where uid in (:uids)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array('uids'=>$ids));
        return $stmt->fetchAll();
    }

	public function count_customer_by_dest_id($dest_id,$date){
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$sql = 'SELECT count(id) FROM t_customer WHERE dest_id=:dest_id AND date(from_unixtime(create_time)) = :date AND 1!='.time();
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array('dest_id'=>$dest_id,'date'=>$date));
		return $stmt->fetchColumn();
	}

	public function get_customer_info_byphone($phone){
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$sql = "select * from t_customer where mobile = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array($phone));
		return $stmt->fetch();
	}

	public function get_customer_by_phone_email($phone, $email=null) {
        if(!$phone) return;
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$sql = "select * from t_customer where mobile like '%$phone%' order by last_order_date desc";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$row = $stmt->fetch();
		if(!$row && $email){
			$row = $this->get_customer_info_bymail($email);
		}
		return $row;
	}	

	public function insert_new_customer($params) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		foreach($params as $k=>$v) {
			$keyarr[] =  $k;
			$valuearr[] = "'".$v."'";
		} 
		$key = implode(",", $keyarr);
		$value = implode(",", $valuearr);
		$sql = "insert into t_customer ($key) values ($value) ";
		$stmt = $pdo->prepare($sql);
		return $stmt->execute();
	}
	
}
