<?php
apf_require_class("APF_DB_Factory");

class Dao_SpeedRoom_Date {

    private $pdo;
    private $slave_pdo;
    private $one_pdo;

    public function __construct() {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $this->one_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
    }
	
	public function get_speedroom_date_bynids($nids) {
		if(empty($nids)){
			return array();
		}
		$nid_str = implode(",", $nids);
try {
		$sql = "select * from t_speedroom_date where rid in ($nid_str) and status =1 ;";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
}catch(Exception $e){
	print_r($e->getMessage());
}
	}

}
