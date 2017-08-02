<?php
apf_require_class("APF_DB_Factory");

class Order_QunarUpdate {

    public function run(){
        $apf = APF::get_instance();
        $api = $apf->get_config("java_open_api")."/qunarService/queryQunar";

        $book_list = self::get_qunar_hide_order();
        foreach($book_list as $row) {
            $open_id = str_replace("QUNAR", "", $row['openId']);
            $query = Util_Curl::get($api, array("orderNums"=>$open_id));
            if($query['code']!=200){
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($open_id, true));
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($query, true));
                continue;
            }
            $book_info = json_decode($query['content'], true);
            if($book_info['code']!=200) {
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($open_id, true));
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($query, true));
                continue;
            }
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export(array($row['id'], $book_info['info']['contactEmail'], $book_info['info']['contactPhone']), true));
            self::update_booking_info_byid($book_info['info']['contactEmail'], $book_info['info']['contactPhone'], $row['id']);
        }
        
    }

    public function get_qunar_hide_order() {
        $sql = "select * from t_homestay_booking book left join t_order_open open on open.order_id = book.hash_id and book.id > 700000 where open.status = 1 and open.openId like 'QUNAR%' and book.guest_mail like '%***%' limit 20";
        $stmt = APF_DB_Factory::get_instance()->get_pdo("lkyslave")->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update_booking_info_byid($guest_mail, $guest_telnum, $id) {
        if(!$guest_mail || !$guest_telnum || !$id) return;
        $sql = "update t_homestay_booking set guest_mail = ? , guest_telnum = ? , update_date = update_date where id = ?";
        $stmt = APF_DB_Factory::get_instance()->get_pdo("lkymaster")->prepare($sql);
        return $stmt->execute(array($guest_mail, $guest_telnum, $id));
    }
}
