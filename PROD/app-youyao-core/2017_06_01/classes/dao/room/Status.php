<?php
apf_require_class("APF_DB_Factory");

class Dao_Room_Status {

    public function __construct() {
    }
	
    public function set_room_stlog($params) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "insert into log_room_status_tracs (nid, flag, room_date, room_num,order_id,uid,create_date,ip) 
		values (?, ?, ?, ?, ?, ?,?,?)";
		$stmt = $pdo->prepare($sql);
		return $stmt->execute(array($params['nid'], $params['flag'], $params['room_date'], $params['room_num'], $params['order_id']
		,$params['uid'],time(),$params['ip']));
    }

	public function set_multiple_date_log($params) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		foreach($params['days'] as $row) {
			$day .= $day ? ", '".$row."'"  :  "'".$row."'";
		}
		$sql = "insert into log_room_status_tracs (nid, flag, token, room_date, room_num, uid, step, create_date, ip, source, order_id) select nid, '".$params['flag']."', '".$params['token']."', room_date, ".$params['type'].", '".$params['uid']."', '".$params['step']."', '".time()."', '".$params['ip']."', '".$params['source']."', '".$params['order_id']."' from t_room_status_tracs where room_date in ($day) and nid = ".$params['nid'];
		$stmt = $pdo->prepare($sql);
		return $stmt->execute();
	}
    
    public function get_room_status_byid($nid,$start_day,$end_day){
    	$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "select * from t_room_status_tracs where nid=? and room_date<? and ?<=room_date";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($nid,$end_day,$start_day));
        return $stmt->fetchAll();
    }
    
    public function update_node_status($nid,$date,$status) {
    	$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "update t_room_status_tracs set status = ?,room_date= ?,update_date= ? where nid = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(array($status,$date,time(),$nid));
    }
    
    public function update_node_room_num($nid,$date,$room_num,$beds_num) {
    	$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "update t_room_status_tracs set room_num = ?,beds_num= ?,room_date=?,update_date= ? where nid = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(array($room_num,$beds_num,$date,time(),$nid));
    }

	public function insert_update_room_num($params) {

		if(!empty($params['room_num'])) {
			$duplicate = "room_num_old=room_num";
		}
		foreach($params as $k=>$v) {
			$keyarr[] = $k;
			$valuearr[] = "'$v'";
			if(!in_array($k, array('room_date','nid','create_date'))){
				$duplicate .= $duplicate? ", $k='$v'" : "$k='$v'";
			}
		}
		$key = implode(", ", $keyarr);
		$value = implode(", ", $valuearr);
		$sql = "insert into t_room_status_tracs ($key) values ($value) ON DUPLICATE KEY UPDATE $duplicate ";
//		print_r($sql);
//		print "<br/>";
try{
    	$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
}catch(Exception $e) {
	print_r($e->getMessage());
}
		return $stmt->rowCount();
	}


	public function insert_update_mulit_room($params, $type) {

		foreach($params as $row) {
			if(!empty($row['room_num']) && empty($duplicate))  $duplicate = "room_num_old=room_num";
			$valuearr = array();
			foreach($row as $k=>$v) {
				if(!$key) $keyarr[] = $k;
				$valuearr[] = "'".$v."'";
			}
			$key = "(".implode(", ", $keyarr).")";
			$value .= $value ? ", (".implode(", ", $valuearr).")" : "(".implode(", ", $valuearr).")" ;
		}
		foreach($keyarr as $r) {
			if( !in_array($r, array('room_date','nid','create_date')) && 
                !($type=='price' && in_array($r, array('beds_num', 'room_num'))) 
            ){
				$duplicate .= $duplicate? ", $r=values($r)" : "$r=values($r)";
            }
		}
		$sql = "insert into t_room_status_tracs $key values $value ON DUPLICATE KEY UPDATE $duplicate ";
// Util_Debug::zzk_debug("insert",print_r($sql,true));
//		print "<br/>";
try{
    	$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
}catch(Exception $e) {
	Util_Debug::zzk_debug("insert_update_mulit_room:", print_r($e->getMessage(), true));
}
		return $stmt->rowCount();
	}

    public function insert_special_additional_price($days, $params) {
        $questionMark = array();
        $insertVal = array();
        $fieldArr = array_keys($params);
        $fieldArr[] = "room_date";
        foreach($days as $row) {
            $questionMark[] = "(" . Util_Common::placeholders("?", count($params)+1). ")";
            $value = array_values($params);
            $value[] = $row;
            $insertVal = array_merge($insertVal,$value);
        }
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "insert into t_additional_price (" . implode(",", $fieldArr) . ") values " . implode(",", $questionMark) . " ON DUPLICATE KEY UPDATE price=values(price)";

try{
        $stmt = $pdo->prepare($sql);
        $stmt->execute($insertVal);
}catch(Exception $e) {
	Util_Debug::zzk_debug("insert_additional_price:", print_r($e->getMessage(), true));
}
        
        return $stmt->rowCount();

    }

    public function get_special_additional_price($params) {
        $conditionMark = array();
        $pdoVal = array();
        foreach($params as $k=>$v) {
            if($k=='room_date') {
                $conditionMark[] = "$k in (" . Util_Common::placeholders("?", count($v)) . ")";
                $pdoVal = array_merge($pdoVal,array_values($v));
            } else {
                $conditionMark[] = "$k = ?";
                $pdoVal[] = $v;
            }
        }
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "select * from t_additional_price where " . implode(" and ", $conditionMark) ;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($pdoVal);
        return $stmt->fetchAll();
    }

    public function get_unavaliable_room_status_bynid($nid, $date, $type="room_num") {
        $sql = "select * from t_room_status_tracs where nid = ? and $type = 0 and unix_timestamp(room_date) >= ?";
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($nid, strtotime($date)));
        return $stmt->fetchAll();
    }

}
