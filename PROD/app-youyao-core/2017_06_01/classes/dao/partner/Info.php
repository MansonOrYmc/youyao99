<?php
apf_require_class("APF_DB_Factory");
//请尽可能的减少数据库操作--by victor_ruan


class Dao_Partner_Info {
    private $pdo;
    private $mainInfo;
    private $orderInfo;
    private $promotions;
    public function __construct() {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
    }
    public function get_main_infos($pid){
        if(!isset($this->mainInfo[$pid])){
            $where = array("`partner_id` = '$pid'","`status`='1'");
            $this->mainInfo[$pid] = DB::getInfo(array('*'),array('database'=>'LKYou','table'=>'t_partner'),$where);
        }
        return $this->mainInfo[$pid];
    }
    public function get_homestay_infos($pid){
        $mainInfo = self::get_main_infos($pid);
        $homestay_infos = array();
        foreach($mainInfo as $info){
            $homestay_infos[$info['uid']][]=array('begin_date'=>$info['begin_date'],'end_date'=>$info['end_date'],'commission'=>$info['commission']);
        }
        return $homestay_infos;
    }

    /**
     * 民宿创建时间|民宿名|民宿地点|民宿ID
     */
    public function get_base_info($pid){
        $h_info = self::get_homestay_infos($pid);
        $hids = array_keys($h_info);
        $hids = implode(" , ",$hids);
        $sql = "select du.created,du.name,du.uid,type_name from one_db.drupal_users du,t_weibo_poi_tw tw,t_loc_type tp where du.uid in ( $hids ) and du.uid = tw.uid and (tp.type_code = tw.local_code or tp.locid = tw.loc_typecode);";
        $r= DB::execSql($sql);
        return $r;
    }
    /**
     * @param $pid
     * @param bool $checkin 是否只统计已经入住的订单
     * @return mixed
     */
    public function total_nights($pid, $checkin=false){
        $total_nights =0;
        $order_infos = self::get_order_infos($pid);
        foreach($order_infos as $order){
            if($checkin)
            {
                //如果只统计已入住
                if(strtotime($order['guest_date']) < time())
                $total_nights+=($order['guest_days']*$order['room_num']);
            }else{
                $total_nights+=($order['guest_days']*$order['room_num']);
            }
        }
        return $total_nights;
    }

    /**
     * @param $orderid
     * @param $pid
     * 合伙人获取每个订单信息
     */
    public function get_order_infos($pid){
        if(!isset($this->orderInfo[$pid])){
            $infos = self::get_homestay_infos($pid);
            $son_where = array();
            foreach($infos as $hid=>$dates){
                foreach($dates as $date){
                    $son_where[] = "( uid = '$hid' and create_time > '".$date['begin_date']."' and create_time < '".($date['end_date']+86400)."' )";
                }
            }
            $where_cluse = implode(" OR ",$son_where);
            $sql = "select * from t_homestay_booking where status in (2,6) and ( $where_cluse )";
            $this->orderInfo[$pid] = DB::execSql($sql);
        }
        return $this->orderInfo[$pid];
    }

    /*
     * 订单的清洗与补充
     * */
    public function get_clean_orders($pid){
        $order_infos = self::get_order_infos($pid);
        $fields=array(
            'order_id',//订单号
            'check_in',//入住时间
            'check_out',//离店日期
            'order_price',//订单金额(目的地币种)
            'pay_price',//打款金额(目的地币种)
            'is_payed_h',//是否已打款给民宿
            'is_payed_p',//是否已打款给合伙人
            'uid',//订单民宿号
            'deal_date',//成交时间
            'is_satyed',//是否入住
            'order_profit',//合伙人利润
            'currence',//币种
            'nights',//成交间夜
            );
        //入住状态 实际上可以用入住时间和当前日期比较得出
        //So_NiceMoney::getMoney($order_info['dest_id'])
        foreach($order_infos as &$order_info){
            $order_info['deal_date'] = date("Y-m-d",$order_info['create_time']);
            $order_info['currence'] = So_NiceMoney::getMoney($order_info['dest_id']);
            $order_info['order_id'] = $order_info['hash_id']?$order_info['hash_id']:$order_info['id'];
            $order_info['check_in'] = $order_info['guest_date'];
            $order_info['check_out'] = $order_info['guest_checkout_date'];
            $order_info['order_price'] = ($order_info['total_price_tw'] - self::get_promotion($order_info['id'],$pid,$order_info['exchange_rate']));
            $order_info['pay_price'] = (!empty($order_info['pay_price_tw']))?$order_info['pay_price_tw']:0;
            $order_info['is_payed_h'] = ($order_info['status']==6)?1:0;
            $order_info['is_satyed'] = (strtotime($order_info['guest_date']) < time())?1:0;
            if($order_info['pay_price'] > 0)
            $order_info['order_profit'] = self::get_profit($order_info,$pid);
            $order_info['nights'] = $order_info['guest_days']*$order_info['room_num'];
        }
        $order_infos = So_NiceClean::clean_Array($order_infos,$fields);
        return $order_infos;
    }

    public function get_profit($order_info,$pid){
        $infos = self::get_homestay_infos($pid);
        $commission = 0;
        foreach($infos[$order_info['uid']] as $info){
            if($order_info['create_time'] >= $info['begin_date'] and ($order_info['create_time'] < $info['end_date']+86400)){
                $commission = $info['commission'];
                continue;
            }
        }
        $result = ((($order_info['order_price'] - $order_info['pay_price'])*$commission)/100);
        return max(0,$result);
    }

    public function get_promotion($order_id,$pid,$exchange_rate=1){
        if(!$this->promotions[$pid]){
            $order_infos = self::get_order_infos($pid);
            $orders = array();
            foreach($order_infos as $order_info){
                $orders[] = $order_info['id'];
            }
            $promotions = So_NiceMoney::getPromotion($orders);
            foreach($promotions as $value){
                $this->promotions[$pid][$value['order_id']] = $value['order_profit']*$exchange_rate;
            }
        }
        return $this->promotions[$pid][$order_id];
    }


}
