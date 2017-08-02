<?php
apf_require_class("APF_DB_Factory");

class Dao_Minimumstay_Minimumstay {

	public function get_minimumstay_date_by_hid($hid){
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "select * from t_minimumstay_date where status = 1 and hid = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($hid));
        return $stmt->fetch();
	}

	public function get_minimumstay_date_by_rid($rid){
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "select * from t_minimumstay_date where status = 1 and rid = ? ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($rid));
        return $stmt->fetchAll();
	}
    public function get_speed_date_by_rid($rid){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "select * from t_speedroom_date where status = 1 and rid = ? ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($rid));
        return $stmt->fetchAll();
    }


    public function set_minimumstay_date($rid,$uid,$dates){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        if(empty($rid)) return false;
        $sql = "update t_minimumstay_date set status = 0 where rid = '$rid' and hid = '$uid' ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        if($dates=='clear'){
            return true;
        }
        //第一步，先将此房间的所有status都改为0
        $sql = "update t_minimumstay_date set status = 0 where rid = '$rid' and hid = '$uid' ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        foreach($dates as $k => $date){
            $sql = "insert into t_minimumstay_date (rid,hid,start_date,end_date,status) values ('".$rid."' ,'".$uid."' ,'".$date['start_date']."' ,'".$date['end_date']."' ,'1')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
    }
    public function set_speed_date($rid,$uid,$dates){
        $apf = APF::get_instance();
        $req = $apf->get_request();
        $user = $req->get_userobject();
        if(!$user->roles[3]){return false;}
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        if(empty($rid)) return false;
        $sql = "update t_speedroom_date set status = 0 where rid = '$rid' and hid = '$uid' ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        if($dates=='clear'){
            return true;
        }
        //第一步，先将此房间的所有status都改为0
        $sql = "update t_speedroom_date set status = 0 where rid = '$rid' and hid = '$uid' ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        foreach($dates as $k => $date){
            $sql = "insert into t_speedroom_date (rid,hid,start_date,end_date,status) values ('".$rid."' ,'".$uid."' ,'".$date['start_date']."' ,'".$date['end_date']."' ,'1')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
    }


	public function get_is_minimumstay_by_rid($rid){
		$pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $sql = "select minimum_stay from drupal_node where nid = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($rid));
        return $stmt->fetch();
	}
    public function get_room_uid_by_nid($nid) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "select uid from one_db.drupal_node where nid = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($nid));
        return $stmt->fetchColumn();
    }
    public function get_room_destid_by_nid($nid) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "select dest_id from one_db.drupal_node where nid = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($nid));
        return $stmt->fetchColumn();
    }

    public function get_room_title_by_nid($nid){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "select title from one_db.drupal_node where nid = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($nid));
        return $stmt->fetchColumn();
    }
	
}
