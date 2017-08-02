<?php
apf_require_class("APF_DB_Factory");

class Dao_Search_Advertisement {

	private $pdo;

	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
	}

	public function get_ad_position($dest_id = 10) {
		$sql = "SELECT * FROM t_ad_promotion WHERE type=1 AND status=1 AND dest_id = '$dest_id';";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetch();
	}

	public function get_ad_list($type = 0, $dest_id = 10) {
		if($type > 0){
			$condition = " AND type = '$type'";
		}else{
			$condition = " AND type > 1";
		}
		$sql = "SELECT * FROM t_ad_promotion WHERE status=1 AND dest_id = '$dest_id' $condition";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function insert_into_adpromotion($params) {
		$sql = "insert into t_ad_promotion (
					type, 
					value, 
					start_date, 
					end_date, 
					admin_uid, 
					dest_id, 
					create_time
				) values (
					'".$params['type']."',
					'".$params['value']."',
					'".$params['start_date']."',
					'".$params['end_date']."',
					'".$params['admin_uid']."',
					'".$params['dest_id']."',
					'".time()."'
				);";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute();
	}

	public function update_adpromotion($params , $condition) {
		$field = '';
		$where = '';
		foreach($params as $k=>$v) {
			$field .= empty($field) ? "$k = '$v'" : ", $k = '$v'";
		}
		foreach($condition as $key=>$val) {
			$where .= empty($where) ? "$key = '$val'" : "AND $key = '$val'";
		}
		$sql = "update t_ad_promotion set $field where $where ;" ;
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute();
	}

}
