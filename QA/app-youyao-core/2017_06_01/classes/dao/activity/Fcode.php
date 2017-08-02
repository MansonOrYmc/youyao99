<?php
class Dao_Activity_Fcode{

	private $slave_pdo;

	public function __construct(){
		$this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
	}
	public function check_share($uid){
		$sql = 'SELECT count(id) FROM t_homestay_booking WHERE guest_uid=:uid AND status in (2,6) AND guest_checkout_date<now()';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('uid'=>$uid));
		return $stmt->fetchColumn();
	}
}