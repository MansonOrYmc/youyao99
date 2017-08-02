<?php
apf_require_class("APF_DB_Factory");

class Dao_Room_Booking {

	private $slave_pdo;

	public function __construct(){
		$this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
	}

    public function get_roombooking_by_nids($arraynids,$startdate,$enddate) {
        $ids = implode(',',$arraynids);
        $sql = "select * from t_homestay_booking where nid in ($ids) and status in (2,6) and ( 
                    ( unix_timestamp(guest_date) <= ? and ? < unix_timestamp(guest_checkout_date) ) or
                    ( unix_timestamp(guest_date) <= ? and ? < unix_timestamp(guest_checkout_date) )
                )";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array(
                    strtotime($startdate),strtotime($startdate),
                    strtotime($enddate),strtotime($endate)));
        return $stmt->fetchAll();
    }

    public function get_roombooking_by_ids($arrayids) {
        $ids = implode(',',$arrayids);
        $sql = "select * from t_homestay_booking where id in ($ids)";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($ids));
        return $stmt->fetchAll();
    }
    
   public function get_roombooking_bystatus($status) {
        $ids = implode(',',$arrayids);
        $sql = "select * from t_homestay_booking where status=?";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($status));
        return $stmt->fetchAll();
    }
}
