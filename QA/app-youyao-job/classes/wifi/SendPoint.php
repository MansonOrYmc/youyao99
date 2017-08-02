<?php
apf_require_class("APF_DB_Factory");

class Wifi_SendPoint {

    private $lky;

    public function __construct() {
        $this->lky = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
    }

    public function run() {
        $point = 5;

        $bookinfo = self::get_wifi_booking();
        $order_ids = array();
        foreach($bookinfo as $row) {
            $order_ids[] = $row['hash_id'];
        }
        $already = self::get_send_history($order_ids);
        $already_send_order = array_column($already, 'order_id');
        $point_dao = new Dao_User_Point();
        foreach($bookinfo as $row) {
            if(in_array($row['hash_id'], $already_send_order)) continue;
            try{
                // 发送积分
                $point_dao->add_user_point($row['guest_uid'], $point, '购入特定商品赠送', 'commodity', strtotime('+1 years'));
                self::set_history($row['hash_id']);
            }catch(Exception $e) {
                print_r($e->getMessage());
            }
        }
    }

    // 获得订单
    public function get_wifi_booking() {    
        $uid = 441793;
        $time = strtotime("-2 days");

        $sql = "select * from t_homestay_booking book left join log_homestay_booking_trac trac on trac.bid = book.id where book.uid = ? and trac.status = 2 and trac.create_date > ? ; ";
        $stmt = $this->lky->prepare($sql);
        $stmt->execute(array($uid, $time));
        return $stmt->fetchAll();
    }

    // 获得已发送历史
    public function get_send_history($orders) {
        if(empty($orders)) return array();
        $sql = "select * from t_commodity_send_point where order_id in ( " . Util_Common::placeholders("?", count($orders)) . " ) and type = 1 and is_send = 1 ";
        $stmt = $this->lky->prepare($sql);
        $stmt->execute(array_values($orders));
        return $stmt->fetchAll();
    }

    //记录已发送
    public function set_history($order_id) {
        if(!$order_id) return;
        $sql = "insert into t_commodity_send_point (order_id, is_send, timestamp, type) values (?, 1, unix_timestamp(), 1)";
        $stmt = $this->lky->prepare($sql);
        return $stmt->execute(array($order_id));
    }

}
