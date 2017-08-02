<?php
class Order_PointNotice {

    public function __construct() {
    	$this->pdo_master = APF_DB_Factory::get_instance()->get_pdo("master");
    }

    public function run() {

    }
}
