<?php
class Dao_Fcode_Flog {
	
   public function add_fc_log($params) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "insert into log_friend_code (uid, target, channel, create_date) values (?,?,?,?)";
		$stmt = $pdo->prepare($sql);
		return $stmt->execute(array($params['uid'], $params['target'], $params['channel'], time()));
    }

}
