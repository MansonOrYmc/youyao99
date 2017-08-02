<?php
apf_require_class("APF_DB_Factory");

class Dao_Search_Room {

	private $slave_pdo;

	public function __construct() {
		$this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
	}

	public function get_t_room_model_byid($dest_id, $id) {
		$sql = 'SELECT * FROM t_room_model WHERE dest_id=? AND id=? AND status=1';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($dest_id, $id));
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function get_t_room_price_byid($id, $dest_id) {
		$sql = 'SELECT * FROM t_room_price WHERE dest_id=? AND id=? AND status=1';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($dest_id, $id));
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function get_dest_by_type_name($type_name, $dest_id) {
		$sql = 'SELECT locid,name_code,type_name FROM t_loc_type WHERE dest_id=? AND type_name=? AND status=1 ORDER BY rank ASC,room_num DESC';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($dest_id, $type_name));
		return $stmt->fetch();
	}

	public function get_dest_by_name_code($name_code, $dest_id) {
		$sql = 'SELECT locid,name_code,type_name FROM t_loc_type WHERE dest_id=? AND name_code=? AND status=1 and LENGTH(type_code)< 12 ORDER BY rank ASC,room_num DESC';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($dest_id, $name_code));
		return $stmt->fetch();
	}

	public function get_dest_by_name_code_without_status_judge($name_code, $dest_id) {
		$sql = 'SELECT locid,name_code,type_name FROM t_loc_type WHERE dest_id=? AND name_code=?';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($dest_id, $name_code));
		return $stmt->fetch();
	}

	public function get_dest_id_by_name_code($name_code) {
		$sql = 'SELECT dest_id FROM t_loc_type WHERE name_code=:name_code AND status=1 ORDER BY rank ASC,room_num DESC';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('name_code' => $name_code));
		return $stmt->fetchColumn();
	}

	public function get_dest_id_by_name_code_without_status_judge($name_code) {
		$sql = 'SELECT dest_id FROM t_loc_type WHERE name_code=:name_code';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('name_code' => $name_code));
		return $stmt->fetchColumn();
	}

	public function get_dest_by_loc_id($loc_id, $dest_id) {
		$sql = 'SELECT locid,name_code,type_name FROM t_loc_type WHERE dest_id=:dest_id AND locid=:loc_id AND status=1';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array(
			'dest_id' => $dest_id,
			'loc_id' => $loc_id
		));
		return $stmt->fetch();
	}

	public function get_dest_by_loc_id_without_status_judge($loc_id, $dest_id) {
		$sql = 'SELECT locid,name_code,type_name FROM t_loc_type WHERE dest_id=:dest_id AND locid=:loc_id';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array(
				'dest_id' => $dest_id,
				'loc_id' => $loc_id
		));
		return $stmt->fetch();
	}

	public function get_city_info($name_code) {
		$sql = 'SELECT * FROM t_loc_type WHERE status=1 AND name_code=:name_code';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('name_code' => $name_code));
		return $stmt->fetch();
	}
}