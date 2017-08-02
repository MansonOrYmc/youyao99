<?php

class Dao_Fcode_RecordSucc {


	public function add_fc_reocrd($params) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "INSERT INTO a_fcode_succ (s_uid, d_uid, channel,fund,status,create_date)
		VALUES (:s_uid, :d_uid, :channel,:fund,:status,:create_date)";
		$stmt = $pdo->prepare($sql);
		return $stmt->execute(array(
			's_uid' => $params['s_uid'],
			'd_uid' => $params['d_uid'],
			'channel' => $params['channel'],
			'fund' => $params['fund'],
			'status' => $params['status'],
			'create_date' => time()
		));
	}


	public function get_fc_recomm($uid) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$sql = "SELECT * FROM  a_fcode_succ WHERE d_uid =?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetch();
	}

	public function get_recomm_bydid($uid) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$sql = "SELECT * FROM  a_fcode_succ WHERE d_uid =?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetch();
	}


	public function update_fc_reocrd($id) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "UPDATE a_fcode_succ SET status=1 WHERE id=" . $id;
		$stmt = $pdo->prepare($sql);
		return $stmt->execute();
	}

	public function get_income_list($uid) {
		$sql = <<<'SQL'
SELECT one_db.drupal_users.name user_name,a_fcode_succ.* FROM a_fcode_succ
LEFT JOIN one_db.drupal_users ON a_fcode_succ.d_uid=one_db.drupal_users.uid
WHERE s_uid=:uid ORDER BY id DESC
SQL;
		$pdo = APF_DB_Factory::get_instance()->get_pdo('lkyslave');
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		return $stmt->fetchAll();
	}
}