<?php
apf_require_class("APF_DB_Factory");

class Dao_Refund_RefundInfo {

    private $pdo;
    private $slave_pdo;
    private $one_pdo;

    public function __construct() {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $this->one_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
    }

	public function get_refund_info_by_oid($oid) {
		$sql = "select * from t_refund where bid = $oid";
		$stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
	}

    public function get_refund_info_by_bids($bids) {
        $ids = implode(',',$bids);
        $sql = "select * from t_refund where bid in ($ids) order by id desc";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

}
