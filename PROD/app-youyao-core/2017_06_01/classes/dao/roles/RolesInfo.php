<?php
apf_require_class("APF_DB_Factory");

class Dao_Get_Roles {

	private $pdo;
	private $one_pdo;
	private $one_slave_pdo;

	public function __construct() {
		$this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
		$this->one_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
	}

	public function zzk_sys_arr_config($key) {
		$key = trim($key);
		$sql = "select sysconfig.arr_value from t_sys_arr_config sysconfig where arr_key = ?";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($key));
		return $stmt->fetch();
	}

	//管理员用户
	public function zzk_admin_users(){
	//return array(76,59,7966,3424,28041,1,6499,5,10841,24784,34495,24763,920,35713,24763,31280,6226,13,4482,6,30916,35393,12903,3743,36915,40061,40621,47791,46579,49973,53568,53448,53035,54984,58948,59101,63003,24166,71102);
		return self::zzk_sys_arr_config('administrator_can_orders');
	}
	
	//开发人员
	public function zzk_admin_techs(){
	//return array(1,76,59,7966,6499,13,53568);
		return self::zzk_sys_arr_config('administrator_can_tech');
	}
	
	//首页编辑
	public function zzk_admin_hp_edit(){
		return self::zzk_sys_arr_config('administrator_can_hompage_edit');
	}
}
