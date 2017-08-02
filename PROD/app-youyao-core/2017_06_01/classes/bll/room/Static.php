<?php

class  Bll_Room_Static {
    //房间信息静态化
    public static $static_room_dao;
    public static $get_minimumstay_date;
    public static $get_minimumstay;
    public static $get_uid_by_nid;
    public static $get_promotion_message;
    public static $get_destid_by_nid;
    public static $get_lowest_room_price;
    public static $get_disc_info_by_nid;
    public static $get_rooms_by_uids;
    public static function init() {
        if(empty(self::$static_room_dao)){
            self::$static_room_dao = new Dao_Minimumstay_Minimumstay();
        }
    }
    public static function get_minimumstay_date($nid){
    self::init();
    if(empty(self::$get_minimumstay_date[$nid])){
        self::$get_minimumstay_date[$nid] = self::$static_room_dao->get_minimumstay_date_by_rid($nid);
    }
    return self::$get_minimumstay_date[$nid];
    }

    public static function  get_room_title_by_nid($nid){
        self::init();
        self::$static_room_dao->get_room_title_by_nid($nid);
        return self::$static_room_dao->get_room_title_by_nid($nid);
    }

    public static function get_minimumstay($nid){
        self::init();
        if(empty(self::$get_minimumstay[$nid])){
            $r = self::$static_room_dao->get_is_minimumstay_by_rid($nid);
            self::$get_minimumstay[$nid] = (is_numeric($r['minimum_stay']) && $r['minimum_stay'] > 1)?$r['minimum_stay']:1;
        }
        return self::$get_minimumstay[$nid];
    }

    public static function get_uid_by_nid($nid){
        self::init();
        if(empty(self::$get_uid_by_nid[$nid])){
            $r = self::$static_room_dao->get_room_uid_by_nid($nid);
            self::$get_uid_by_nid[$nid] = $r;
        }
        return self::$get_uid_by_nid[$nid];
    }
    public static function get_destid_by_nid($nid){
        self::init();
        if(empty(self::$get_destid_by_nid[$nid])){

            $memcache = APF_Cache_Factory::get_instance()->get_memcache();
            $key =  __CLASS__.'-'.__FUNCTION__.'-'.$nid;
            $result = $memcache->get($key);
            if(empty($result)){
                $result = self::$static_room_dao->get_room_destid_by_nid($nid);
                $memcache->set($key,$result,null,86400);
            }
            self::$get_destid_by_nid[$nid] = $result;
        }
        return self::$get_destid_by_nid[$nid];
    }

    public static function get_promotion_message($nid){
        if(isset(self::$get_promotion_message[$nid])){return self::$get_promotion_message[$nid];}
        $bll = new Bll_Promotion_Message();
        $r = $bll->get_one_room_pm($nid);
        self::$get_promotion_message[$nid] = $r;
        return $r;
    }

    public static function get_disc_info_by_nid($checkin,$checkout,$nid){
        if(self::$get_disc_info_by_nid[$checkin.$checkout.$nid]){
            return self::$get_disc_info_by_nid[$checkin.$checkout.$nid];
        }else{
            if(empty($checkin)||(strtotime($checkin)<strtotime(date('Y-m-d')))){$checkin = date('Y-m-d');}
            if(empty($checkout)||strtotime($checkout)>strtotime(date('Y-m-d',strtotime('+12 months', strtotime(date('Y-m')))-60*60*24))){$checkout = date('Y-m-d',strtotime('+12 months', strtotime(date('Y-m')))-60*60*24);}
            $bll = new Bll_Disc_Info;
            $disc =$bll->get_period_discs($checkin,$checkout,$nid);
            self::$get_disc_info_by_nid[$checkin.$checkout.$nid] = (min($disc));
            return self::$get_disc_info_by_nid[$checkin.$checkout.$nid];
        }
    }

    /*
     * 获取一个时间段的最低房价
     * 如果不传时间,就查接下来的一年的最低价
     * $promotion表示是否需要考虑促销之后的最低价
     * 将信息(静态化)和(缓存60秒)可以解决很多问题
     * */
    public static function get_lowest_room_price($checkin,$checkout,$nid,$promotion,$mutiprice=12){
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $key =  Util_MemCacheKey::get_lowest_room_price($checkin,$checkout,$nid,$promotion,$mutiprice);
        $result = $memcache->get($key);
        if(empty($result)){
            if(empty($checkin)||(strtotime($checkin)<strtotime(date('Y-m-d')))){$checkin = date('Y-m-d');}
            if(empty($checkout)||strtotime($checkout)>strtotime(date('Y-m-d',strtotime('+12 months', strtotime(date('Y-m')))-60*60*24))){$checkout = date('Y-m-d',strtotime('+12 months', strtotime(date('Y-m')))-60*60*24);}
            $uid = Bll_User_Static::get_uid_by_nid($nid);
            if(empty(self::$get_lowest_room_price[$nid.$checkin.$checkout.$mutiprice])){
                //如果缓存和静待化信息里都没有最低价格的话,需要从数据库获取.
                //获取房价表里的有价格的时间段的最低价,而不是遍历每一天,查完价格,再去找最低价.
                $sql = "SELECT  min(room_price) from LKYou.t_room_status_tracs where nid = ? and room_date BETWEEN ? and ? and room_price>0 ;";
                $lky_pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
                $stmt = $lky_pdo->prepare($sql);
                //最后一天的离店日期不用计算
                $stmt->execute(array($nid,$checkin,date('Y-m-d',(strtotime($checkout)-86400))));
                //临时最小价格$minprice
                $minprice = $stmt->fetchColumn();
                //获取房态表里没有房价的日期,用来后面从json串里遍历用!!!!!!
                $sql = "SELECT  room_date from LKYou.t_room_status_tracs where nid = ? and room_date BETWEEN ? and ? and room_price>0 order by room_date asc ;";
                $stmt = $lky_pdo->prepare($sql);
                $stmt->execute(array($nid,$checkin,$checkout));
                $validdates = $stmt->fetchAll(PDO::FETCH_COLUMN);
                //房态表有价格的日期需要排除
                $validdates = array_diff(Util_ZzkCommon::zzk_get_dates($checkin,date('Y-m-d',(strtotime($checkout)-86400))),$validdates);
                $dest_id = self::get_destid_by_nid($nid);
                //获取json串.只需要取一次
                $rpconfig = Bll_User_Static::get_rp_config($uid);
                $room_price = json_decode($rpconfig['room_price'],true);
                $room_date = json_decode($rpconfig['room_date'],true);
                foreach($room_price as $v){
                    if($v['rid'] == $nid){
                        $room_price = explode(',',$v['price']);
                        break;
                    }
                    $room_price = array();
                }
//下面这个过程需要把有效的价格保留,无效的价格去除.
                $room_price = array_reverse($room_price,true);//将优先级高的，排在前面
                foreach($room_price as $k =>$price_){
                    if(empty($price_))
                    {
                        unset($room_price[$k]);
                    }else{
                        //判断日期是否和dates_01重合,如果重合，则不unset
                        $dates = str_replace(array("-"),"",$room_date[$k]['data'][0]['QDate']);
                        $weekdays = explode(',',$room_date[$k]['data'][0]['WDate']);
                        foreach($weekdays as $weekdays_k=>&$weekdays_v){
                            if($weekdays_v == '7'){
                                $weekdays_v =0;
                            }
                        }
                        $dates = explode('|',$dates);
                        $is_need_2_unset = 1;
                        foreach($dates as $dates_v){
                            if(!empty($dates_v)){
                                $dates_useing = explode(',',$dates_v);
                                foreach($validdates as $validdates_k => $validdates_v){
                                    if( date('md',strtotime($validdates_v)) >= $dates_useing[0] and date('md',strtotime($validdates_v)) <=$dates_useing[1] and  in_array(date('w',strtotime($validdates_v)),$weekdays) )
                                    {
                                        unset($validdates[$validdates_k]);
                                        $is_need_2_unset =0;
                                    }
                                }
                            }
                        }
                        if($is_need_2_unset) {
                            unset($room_price[$k]);
                        }

                    }
                }
                //将剩下来的有效价格的最低价和房态表的最低价进行比较
                if(count($room_price)>0){
                    if(!empty($minprice)){
                        $minprice = (min(min($room_price),$minprice));
                    }else{
                        $minprice = (min($room_price));
                    }
                }

                //根据 $minprice 属性进行货币转换
                if(!$minprice){return 0;}
                $minprice = Util_Common::zzk_price_convert($minprice,$dest_id,$mutiprice);
                $result_0001 = $minprice;
                self::$get_lowest_room_price[$nid.$checkin.$checkout.$mutiprice]=$result_0001;
            }else{
                $result_0001 = self::$get_lowest_room_price[$nid.$checkin.$checkout.$mutiprice];
            }
            if($promotion){
                //是否需要考虑优惠
                $disc = self::get_disc_info_by_nid($checkin,$checkout,$nid);//获取房间一个时间段的最低折扣(某种意义上,这个不太准确)
                $result_0001 = (int)($result_0001*$disc);
                $result_0001-=Sales_Zzkdiscpromotion::get_promotion_value($uid,$result_0001,date('Y-m-d',REQUEST_TIME),null);
                $result_0001-=Sales_Firstorderpromotion::get_promotion_value($uid,$result_0001,date('Y-m-d',REQUEST_TIME),null);
            }
            if(empty($result_0001)){
                $memcache->set($key,'-1',null,60);
            }else{
                $memcache->set($key,$result_0001,null,60);
            }
            $result = $result_0001;
        }
        if($result=='-1'){
            $result = 0;
        }
        return $result;
    }

    public static function get_rooms_by_uids($uids){
        $uids = implode(',',$uids);
        if(!empty(self::$get_rooms_by_uids[$uids])){
        }else{
            $dao = new Dao_Room_RoomInfo();
            self::$get_rooms_by_uids[$uids] = $dao->get_roominfo_by_uids_withoutspeed(explode(',',$uids),'1,0');
        }
        return self::$get_rooms_by_uids[$uids];
    }

    public static function get_lowest_bnb_price($checkin,$checkout,$uid,$promotion,$mutiprice=12){
        $room_bll = new Bll_Room_RoomInfo();
        $rooms = $room_bll->get_roomlist_by_uid($uid, $checkin, $checkout);
        $minprice = 1000000;
        foreach($rooms as $room){
            if(self::get_lowest_room_price($checkin,$checkout,$room->id,$promotion,$mutiprice) < $minprice and self::get_lowest_room_price($checkin,$checkout,$room->id,$promotion,$mutiprice)>0){
                $minprice = self::get_lowest_room_price($checkin,$checkout,$room->id,$promotion,$mutiprice);
            }
        }
        return $minprice;
    }

    public static function update_node_room_num($nid,$room_date,$room_num,$uid,$orderid,$admin) {
        $bll_stats = new Bll_Room_Status();
        $token = md5(time() + $orderid);
        $bll_stats->set_multiple_days_logs($nid, array($room_date), $admin, Util_NetWorkAddress::get_client_ip(), 1, 2, $token,$orderid);
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "insert into t_room_status_tracs values (null,'$nid','0','$room_date','".time()."','$uid','$room_num','0','0','".time()."','0','') ON DUPLICATE KEY UPDATE `room_num` = '$room_num',`room_num_old` = `room_num`";
        $stmt = $pdo->prepare($sql);
        $result =  $stmt->execute();
        $bll_stats->set_multiple_days_logs($nid, array($room_date), $admin, Util_NetWorkAddress::get_client_ip(), 2, 2, $token,$orderid);
        return $result;
    }

    public static function update_node_beds_num($nid,$room_date,$bed_num,$uid,$orderid,$admin) {
        $bll_stats = new Bll_Room_Status();
        $token = md5(time() + $orderid);
        $bll_stats->set_multiple_days_logs($nid, array($room_date), $admin, Util_NetWorkAddress::get_client_ip(), 1, 2, $token,$orderid);
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "insert into t_room_status_tracs values (null,'$nid','0','$room_date','".time()."','$uid','0','0','$bed_num','".time()."','0','') ON DUPLICATE KEY UPDATE `beds_num` = '$bed_num'";
        $stmt = $pdo->prepare($sql);
        $result =  $stmt->execute();
        $bll_stats->set_multiple_days_logs($nid, array($room_date), $admin, Util_NetWorkAddress::get_client_ip(), 2, 2, $token,$orderid);
        return $result;
    }

    public static function is_speed_room($nid){
        $homestaybll = new Bll_Homestay_StayInfo();
        $r = $homestaybll->get_roominfo_by_nid($nid);
        $base_room_info = $r[0];
        if(!$base_room_info['speed_room']){
            return 0;
        }else{
                $dao = new Dao_Minimumstay_Minimumstay();
                $ms_date = $dao->get_speed_date_by_rid($nid);
            if(count($ms_date)>0)
            {
                foreach($ms_date as $k=>$v){
                    if(strtotime($v['end_date'])-time()>0){
                        return 1;
                    }else{
                        continue;
                    }
                }
            }else{
                return 1;
            }
        }
    }
}
