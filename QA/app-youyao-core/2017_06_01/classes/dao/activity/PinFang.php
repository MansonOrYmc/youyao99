<?php
apf_require_class("APF_DB_Factory");

class Dao_Activity_PinFang {
    
    public function send_pinfang_message($params) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "insert into t_pinfang_communication (to_uid, from_uid, content, create_date) 
        values (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(array($params['to_uid'], $params['from_uid'], $params['content'],time()));
    }
    
    public function get_message_lists($uid) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from t_pinfang_communication where (to_uid = ? or from_uid = ? ) order by id desc";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($uid,$uid));
        return $stmt->fetchAll();
    }

    
    public function get_pinfang_lists() {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from t_pinfang_list where status > 0 order by id desc";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function get_indivi_pinfang_list($uid) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from t_pinfang_list where status > 0 and uid = ? order by id desc";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($uid));
        return $stmt->fetch();
    }

    public function get_pinfang_byid($id) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from t_pinfang_list where id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($id));
        return $stmt->fetch();
    }
    
    public function send_pinfang_request($params) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "insert into t_pinfang_list (bid,uid, nickname, sex, age,province,room_model,contact,location,start_date,end_date,
        request_sex,request_age,request_photo,the_word) 
        values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(array($params['bid'], $params['uid'], $params['nickname'], $params['sex'], $params['age']
        , $params['province'], $params['room_model'], $params['contact'], $params['location'], strtotime($params['start_date']), strtotime($params['end_date']), $params['request_sex'], 
        $params['request_age'], $params['request_photo'], $params['the_word']));
    }

	public function accept_request_list($params) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "update t_pinfang_list set status = 2 where uid = ? ";
		$stmt = $pdo->prepare($sql);

		return $stmt->execute(array($params['f_uid']));
	}

	public function accept_request_message($params) {

		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "update t_pinfang_communication set status = 2 where to_uid = ? and from_uid = ?";
		$stmt = $pdo->prepare($sql);

		return $stmt->execute(array($params['f_uid'],$params['a_uid']));
	}

	public function reject_request_message($params) {

		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "update t_pinfang_communication set status = 3 , cancel_reason = ? where to_uid = ? and from_uid = ?";
		$stmt = $pdo->prepare($sql);

		return $stmt->execute(array($params['cancel_reason'], $params['f_uid'],$params['a_uid']));
	}
}
