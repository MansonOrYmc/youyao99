<?php
apf_require_class("APF_DB_Factory");

class Dao_Taixinbank_Info {

    private $lky_pdo;
    private $one_pdo;

    public function __construct() {
        $this->lky_pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
    }

	public function get_code_byrecord($oid, $type=1) {
		$sql = "select * from t_taixin_virtual_account where oid = ? and type = ?";
		$stmt = $this->lky_pdo->prepare($sql);
		$stmt->execute(array($oid,$type));
		return $stmt->fetch();
	}

	public function insert_account_record($code, $params, $update) {
		$sql = "insert into t_taixin_virtual_account (
					oid,
					account,
					type,
					price,
					time_out,
					create_time
				) values (
					'".$params['oid']."', 
					'".$code."', 
					'".$params['account_type']."',
					'".$params['price']."', 
					'".date('Y-m-d',strtotime($update))."', 
					'".time()."' 
				) ON DUPLICATE KEY UPDATE 
					account = '".$code."',
					price = '".$params['price']."',
					time_out = '".date('Y-m-d',strtotime($update))."'
			";

try{
		$stmt = $this->lky_pdo->prepare($sql);
		return $stmt->execute();
}catch(Exception $e) {
		print_r($e->getMessage());
}
	}
}
