<?php
class Dao_Follow_Record {
    public function get_reocrd_byorderid($order_id) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from log_follow_record where order_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($order_id));
        return $stmt->fetchAll();
    }
    
    public function get_reocrd_byphone($phone) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from log_follow_record where phone_number=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($phone));
        return $stmt->fetchAll();
    }
    
    public function add_follow_reocrd($params) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "insert into log_follow_record (phone_number, order_id, order_uid, f_type, content, create_time,mid,source_id) 
		values (?, ?, ?, ?, ?, ?,?,?)";
		$stmt = $pdo->prepare($sql);
		return $stmt->execute(array($params['phone_number'], $params['order_id'], $params['order_uid'], $params['f_type'], 
		$params['content'], time(),$params['mid'],$params['source_id']));
    }
    
   public function add_oldfollow_reocrd($params) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "insert into log_homestay_booking_trac (bid, status, content, create_date, admin_uid, client_ip,intro,price_tw,only_save_comment) 
		values (?,?,?,?,?,?,?,?,?)";
		$stmt = $pdo->prepare($sql);  var_dump($params);
		echo $stmt->execute(array($params['bid'], $params['status'], $params['content'], time(), 
		$params['admin_uid'],$params['client_ip'],$params['intro'],$params['price_tw'],$params['only_save_comment']));
    }
    
    public function get_reocrd_bydate($sdate,$edate) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from log_follow_record where create_time between unix_timestamp('$sdate') and unix_timestamp('$edate')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array());
        return $stmt->fetchAll();
    }

}