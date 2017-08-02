<?php

class Report_Oder {
    private $end_date;
    private $start_date;

    public function run() {
        $this->end_date = date('Y-m-d',time());
        $this->start_date = date('Y-m-d',time()-86400);

// order_source   ios android   web_unset  msite

        //payment_source   ios android   web msite

        $days = 40;

        $morning = mktime(0,2,0,9,11,2015);

        for($i=0;$i<=$days;$i++){
            $end   = $morning-$i*86400;
            $start = $morning-($i+1)*86400;
            $data  = $this->get_total_jy_m($start,$end);
            echo  date('Y-m-d',$start)." 移动成交量：".$data['total']."\n";
        }

        $morning = mktime(0,2,0,9,11,2015);

        for($i=0;$i<=$days;$i++){
            $end   = $morning-$i*86400;
            $start = $morning-($i+1)*86400;
            $data  = $this->get_total_jy($start,$end);
            echo  date('Y-m-d',$start)." 网站成交量：".$data['total']."\n";
        }


    }


    public function get_total_order_m($start,$end){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select count(*) as total from LKYou.t_homestay_booking where  create_time BETWEEN  $start and  $end and order_source in ('ios','android','msite')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function get_total_order_web($start,$end){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select count(*) as total from LKYou.t_homestay_booking where create_time BETWEEN  $start and  $end and order_source='web_unset'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function get_total_jy_m($start,$end){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select sum(a.room_num*a.guest_days) as total from LKYou.t_homestay_booking a
                where a.status in (2,6) and order_source in ('ios','android','msite') and exists (select * from LKYou.log_homestay_booking_trac b
                where a.id = b.bid and b.status = 2 and b.create_date  BETWEEN  $start and  $end)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function get_total_jy($start,$end){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select sum(a.room_num*a.guest_days) as total from LKYou.t_homestay_booking a
                where a.status in (2,6) and order_source='web_unset' and exists (select * from LKYou.log_homestay_booking_trac b
                where a.id = b.bid and b.status = 2 and b.create_date  BETWEEN  $start and  $end)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }



}