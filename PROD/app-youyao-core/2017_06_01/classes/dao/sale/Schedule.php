<?php
apf_require_class("APF_DB_Factory");

class Dao_Sale_Schedule {

    private $pdo;

    public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
    }

    public function get_sales_schedule_bydate($date) {
        $sql = "select * from t_sales_schedule where date = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($date));
        return $stmt->fetchAll();
    }

    public function get_sales_schedule_byperiod($start, $end) {
        $sql = "select * from t_sales_schedule where date between ? and ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($start, $end));
        return $stmt->fetchAll();
    }

    public function insert_update_mid_bydate($mid, $date, $status, $operator_uid) {
        $sql = "insert t_sales_schedule (`date`, `status`, `mid`, `operator_uid`) values (?, ?, ?, ?) ON DUPLICATE KEY update status = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($date, $status, $mid, $operator_uid, $status));
    }

}
