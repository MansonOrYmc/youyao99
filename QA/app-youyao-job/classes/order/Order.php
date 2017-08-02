<?php
class Order_Order
{
	private $pdo;
    public function run(){
    	/*
		$file = fopen("/Users/ch98/Desktop/soj_path.txt", "r");
		$i=1;
		$js="";
		while(!feof($file)){		
/////		
//		$arr = json_decode(fgets($file),true);	
//		if(!empty($arr)){
//		    $this->insert_data($arr);
//		}
/////

	        $arr = json_decode(fgets($file),true);
	        if(!empty($arr)){
			    $this->insert_data_log($arr);
	        }
		}
		fclose($file);	
		
		
		
		*/
    	
    	
    	$succ = $this->get_succ_order();
    	
    foreach ($succ as $key=>$value){
		   $url="http://optools.zizaike.com/zzk_taiwan_send_email.php?oid=".$value['id']."&utype=1&op=send";
		   echo $url."\n";
    }

		
    }
    
    public function insert_data($params) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "insert into tmp_order_action (guid, createtime, v_check_in_click, v_check_out_click, v_book_room_id_click, v_guest_name_click,v_edit_guest_telnum,v_edit_guest_mail,v_edit_submit,ip) 
		values (?, ?, ?, ?, ?, ?,?,?,?,?)";
		$stmt = $pdo->prepare($sql);
		return $stmt->execute(array($params['guid'],substr($params['t'],0,10), $params['v_check_in_click'], $params['v_check_out_click'], 
		$params['v_book_room_id_click'],$params['v_guest_name_click'],$params['v_edit_guest_telnum'],
		$params['v_edit_guest_mail'],$params['v_edit_submit'],$params['ip']));
    }
    
    public function insert_data_log($params) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "insert into tmp_order_action (guid, createtime,url,ip) 
		values (?, ?, ?, ?)";
		$stmt = $pdo->prepare($sql);
		return $stmt->execute(array($params['guid'],substr($params['t'],0,10), $params['h'], $params['ip']));
    }
    
    public function get_succ_order(){
    	$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
    	$sql = "select id from LKYou.t_homestay_booking a where a.status in (2,6) 
    	and exists (select * from LKYou.log_homestay_booking_trac b where a.id = b.bid 
    	and b.status = 2 and b.create_date >=1421251200 and b.create_date < 1421424000)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    
    
    
}
