<?php
apf_require_class("APF_DB_Factory");

class Dao_Booking_BookingInfo {

	private $lky_pdo;
	private $one_pdo;

	public function __construct() {
		$this->lky_pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
	}

	

}
?>