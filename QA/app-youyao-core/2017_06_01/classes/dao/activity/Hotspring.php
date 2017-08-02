<?php
apf_require_class("APF_DB_Factory");

class Dao_Activity_Hotspring {
    
    public function add_hotspring_record($params) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "insert into a_luck_order (order_id, order_pay, phone_num, wish, create_date) 
        values (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(array($params['order_id'], $params['order_pay'], $params['phone_num'], $params['wish'],time()));
    }

    public function get_hotspring_records() {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from a_luck_order where is_luck!=1 order by id desc limit 200";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $tmp_comm = $stmt->fetchAll();
        
        $sql = "select * from a_luck_order where is_luck=1 order by id desc";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $tmp_sp = $stmt->fetchAll();
        return array_merge($tmp_comm,$tmp_sp);
    }
}