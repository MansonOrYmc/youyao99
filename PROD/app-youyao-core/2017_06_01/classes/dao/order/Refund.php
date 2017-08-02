<?php
apf_require_class("APF_DB_Factory");

class Dao_Order_Refund {

    private $pdo;
    private $slave_pdo;

    public function __construct() {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
    }

    public function get_refund_by_bid($bid) {
        $sql = "select * from t_refund where bid = ?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($bid));
        return $stmt->fetch();
    }

    public function get_refund_by_bids($bids) {
        if(empty($bids)) return array(); 
        $sql = "select * from t_refund where bid in (".Util_Common::placeholders("?", count($bids)).")";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array_values($bids));
        return $stmt->fetchAll();
    }

}
