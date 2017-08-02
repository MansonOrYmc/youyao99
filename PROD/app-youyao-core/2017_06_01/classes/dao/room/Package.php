<?php
apf_require_class("APF_DB_Factory");

class Dao_Room_Package {

	private $pdo;
	private $one_pdo;
	
	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
		$this->one_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
	}

	public function get_package_bynids($nids) {

		if(empty($nids)) return;
		$nidStr = implode(",", $nids);
		$sql = "select * from t_package_price where rid in ($nidStr) ";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();

	}

	public function get_package_byids($ids) {

		if(empty($ids)) return;
		$idStr = implode(",", $ids);
		$sql = "select * from t_package_price where id in ($idStr) ";
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();

	}

}
