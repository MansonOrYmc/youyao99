<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/8/5
 * Time: 下午5:51
 */
class Bll_Price_Info {

    private $roominfodao;
    private $homestaybll;
    private $discbll;
    private $debug = false;
    private $get_special_additional_price;
    public function __construct() {
        $this->roominfodao  = new Dao_Room_RoomInfo();
        $this->homestaybll = new Bll_Homestay_StayInfo();
        $this->discbll = new Bll_Disc_Info();
        $this->roomdao    = new Dao_Room_Status();
    }
    /*
     * 获取某一天，某个房型，的价格！非常单纯的价格！！！！
     * 当然，当disc 为  true时 考虑打折的情况
     * $day 的格式 形如 2015-04-11
     *
     */
    public function get_room_price($nid,$day,$disc = true)
    {
        $add_man = self::get_additional_price($nid,$day,$day,$type=1,$status=1);
        $room_obj = $this->roominfodao->room_detail_contact_order($nid);
        $add_bed = $room_obj->add_beds_price;
        if($disc)
        {
            //需要考虑打折情况
            $discs = $this->discbll->get_period_discs(strtotime($day),strtotime($day),$nid);

            $discounts = $discs[strtotime($day)];
        }else{
            $discounts = 1;
        }

        $prices = $this->roominfodao->get_room_status_tracs_by_nid_and_date($nid, $day);

        if ($prices['room_price']) {
            $result['original_price'] = $prices['room_price']*$discounts;
            $result['price_cn'] = Util_Common::zzk_price_convert($result['original_price'],$room_obj->dest_id);
        }else {
            $uid = $this->roominfodao->get_room_uid_by_nid($nid);
            $config = $this->roominfodao->fetch_room_price_config($uid);
            $r = $this->homestaybll->rpd_parse_v3($config['room_date'], $config['room_price'], $nid, $day);
            $result['original_price'] = $r["price"]*$discounts;
            $result['price_cn'] = Util_Common::zzk_price_convert($result['original_price'],$room_obj->dest_id);
        }
        $result['original_add_man_price'] = (int)($add_man[$day]*$discounts);
        $result['original_add_bed_price'] = (int)($add_bed*$discounts);
        $result['add_bed_price_cn'] = (int)((Util_Common::zzk_price_convert($add_bed,$room_obj->dest_id))*$discounts);
        $result['add_man_price_cn'] = (int)((Util_Common::zzk_price_convert($add_man[$day],$room_obj->dest_id))*$discounts);
        return $result;
    }

    public function get_room_price_with_detail($nid,$day,$disc=true)
    {
        $add_man = self::get_additional_price($nid,$day,$day,$type=1,$status=1);
        $room_obj = $this->roominfodao->room_detail_contact_order($nid);
        $add_bed = $room_obj->add_beds_price;
        if($disc)
        {
            //需要考虑打折情况
            $discs = $this->discbll->get_period_discs(strtotime($day),strtotime($day),$nid);

            $discounts = $discs[strtotime($day)];
        }else{
            $discounts = 1;
        }

        $prices = $this->roominfodao->get_room_status_tracs_by_nid_and_date($nid, $day);


        if ($prices['room_price']) {
            $base['original_price']=$prices['room_price'];
            $base['price_cn']= Util_Common::zzk_price_convert($base['original_price'],$room_obj->dest_id);

            $result['original_price'] = $prices['room_price']*$discounts;
            $result['price_cn'] = Util_Common::zzk_price_convert($result['original_price'],$room_obj->dest_id);
        }else {
            $uid = $this->roominfodao->get_room_uid_by_nid($nid);
            $config = $this->roominfodao->fetch_room_price_config($uid);
            $r = $this->homestaybll->rpd_parse_v3($config['room_date'], $config['room_price'], $nid, $day);

            $base['original_price']=$r['price'];
            $base['price_cn'] = Util_Common::zzk_price_convert($base['original_price'],$room_obj->dest_id);

            $result['original_price'] = $r["price"]*$discounts;
            $result['price_cn'] = Util_Common::zzk_price_convert($result['original_price'],$room_obj->dest_id);
        }
        $result['original_add_man_price'] = (int)($add_man[$day]*$discounts);
        $result['original_add_bed_price'] = (int)($add_bed*$discounts);
        $result['add_bed_price_cn'] = (int)((Util_Common::zzk_price_convert($add_bed,$room_obj->dest_id))*$discounts);
        $result['add_man_price_cn'] = (int)((Util_Common::zzk_price_convert($add_man[$day],$room_obj->dest_id))*$discounts);

        $base['original_add_man_price'] = (int)($add_man[$day]);
        $base['original_add_bed_price'] = (int)($add_bed);
        $base['add_bed_price_cn'] = (int)((Util_Common::zzk_price_convert($add_bed,$room_obj->dest_id)));
        $base['add_man_price_cn'] = (int)((Util_Common::zzk_price_convert($add_man[$day],$room_obj->dest_id)));


        $detail['actual']=$result;
        $detail['disc']=$discounts;
        $detail['base']=$base;

        $result['detail']=$detail;

        return $result;
    }

    /*
     * 获取某一个时间段，某个房型，的每一天的价格！也是非常单纯的价格！！！！
     * 当然，当disc 为  true时 考虑打折的情况
     */
    public function get_room_price_list_with_detail($nid,$in,$out ,$disc=true)
    {
        $add_man = self::get_additional_price($nid,$in,$out,$type=1,$status=1);
        $room_obj = $this->roominfodao->room_detail_contact_order($nid);
        $add_bed = $room_obj->add_beds_price;
        $result = array();
        $date_s = strtotime($in);
        $date_e = strtotime($out);
        while($date_s<$date_e) {
            $price=$this->roominfodao->get_room_status_tracs_by_nid_and_date($nid, date("Y-m-d",$date_s));
            if(!$price){$price['room_date'] = date("Y-m-d",$date_s);}
            $prices[] = $price;
            unset($price);
            $date_s += 24 * 3600;
        }
        $date_s = strtotime($in);
        $date_e = strtotime($out);

        $room_obj = $this->roominfodao->room_detail_contact_order($nid);
        $day = ($date_e - $date_s)/86400;//入住天数
        if($day < 1) {
            $result[$in]= self::get_room_price_with_detail($nid,$in);
            return $result;
        }

        $uid = $this->roominfodao->get_room_uid_by_nid($nid);
        $config = $this->roominfodao->fetch_room_price_config($uid);
        if($disc)
        {
            //需要考虑打折情况
            $discounts = $this->discbll->get_period_discs(strtotime($in),strtotime($out),$nid);
        }else{
            while($date_s<$date_e) {
                $discounts[$date_s] = 1;
                $date_s += 24 * 3600;
            }
        }


        foreach ($prices as $key => $price) {
            $d_disc = $discounts[strtotime($price['room_date'])];
            if ($price['room_price']) {
                $base['original_price'] = intval($price['room_price']);
                $base['price_cn'] = (int)(Util_Common::zzk_price_convert($price['room_price'], $room_obj->dest_id));

            } else {
                $r = $this->homestaybll->rpd_parse_v3($config['room_date'], $config['room_price'], $nid, $price['room_date']);

                $base['original_price'] = intval($r["price"]);
                $base['price_cn'] = (int)(Util_Common::zzk_price_convert($r["price"], $room_obj->dest_id));

            }

            //add_man  or add_bed
            $base['original_add_man_price'] = $add_man[$price['room_date']];
            $base['original_add_bed_price'] = $add_bed * $d_disc;
            $base['add_man_price_cn'] = (int)((Util_Common::zzk_price_convert($add_man[$price['room_date']], $room_obj->dest_id)));
            $base['add_bed_price_cn'] = (int)((Util_Common::zzk_price_convert($add_bed, $room_obj->dest_id)));


            $detail[$price['room_date']]['disc'] = $d_disc;
            $detail[$price['room_date']]['base'] = $base;

            foreach ($base as $k => $v) {
                if (is_numeric($v) && strpos($k, 'price')) {
                    $detail[$price['room_date']]['actual'][$k] = (int)$v * $d_disc;
                    $result[$price['room_date']][$k] = (int)$v * $d_disc;
                } else {
                    $detail[$price['room_date']]['actual'][$k] = $v;
                    $result[$price['room_date']][$k] = $v;

                }
            }
        }

        $result['detail'] = $detail;

        return $result;
    }


    /**
     * author: lec
     *
     * @param $nid
     * @param $in
     * @param $out
     * @param bool|true $disc
     * @return array    获取房价   呈现每天的详细价格  datelist可以获得每天的详细价格计算
     */
    public function  get_total_price_with_detail($nid, $in, $out, $room_num, $guest_num, $add_bed_num = 0)
    {

        $msg_normal = "normal";
        $msg_add = "add_man_or_bed";
        $msg = $msg_normal;
        $room_obj = $this->roominfodao->room_detail_contact_order($nid);
        $room_model = $room_obj->room_model;  //房型
        if (empty($room_model)) {
            $room_model = 1;
        }
        $total_room_model = $room_model * $room_num;  //房型*房间数 （实际上是可入住最大人数）
        // $result = self::get_room_price_4_days($nid,$in,$out,$disc);

        $plist = self::get_room_price_list_with_detail($nid, $in, $out);

        $detailList = $plist['detail'];


        foreach ($detailList as $k => $v) {

            $day_full = $this->calculate_price($v, $room_obj, $room_num, $guest_num, $add_bed_num);


            //计算一个根据原价生成的价格  用于计算折扣差价
            $day_bf=$this->calculate_price($v, $room_obj, $room_num, $guest_num, $add_bed_num,false);

            $disc=$v['disc'];
            $day=$day_full['topay'];
            $msg=$day_full['msg'];
            $base_price += $day['base_price'];
            $base_price_cn += $day['base_price_cn'];
            $original_price += $day['original_price'];
            $price_cn += $day['price_cn'];
            $original_add_man_price += $day['add_man']['original_add_man_price'];
            $add_man_price_cn += $day['add_man']['add_man_price_cn'];
            $original_add_bed_price += $day['add_bed']['original_add_bed_price'];
            $add_bed_price_cn += $day['add_bed']['add_bed_price_cn'];
            $add_bed_num = $day['add_bed']['add_bed_num'];
            $add_man_num = $day['add_man']['add_man_num'];

            $day_actual_total_price = $day['total_price'];
            $day_actual_total_price_cn = $day['total_price_cn'];

            //基于base 价格生成的价格
            $day_base_total_price=$day_bf['topay']['total_price'];
            $day_base_total_price_cn=$day_bf['topay']['total_price_cn'];

            //记录基于base 价格生成的原价
            $day_full['topay']['base_total_price']=$day_bf['topay']['total_price'];
            $day_full['topay']['base_total_price_cn']=$day_bf['topay']['total_price_cn'];
            //原价和折扣的差值
            $day_full['topay']['disc_price']=$day_base_total_price-$day_actual_total_price;
            $day_full['topay']['disc_price_cn']=$day_base_total_price_cn-$day_actual_total_price_cn;
            $day_full['today']['disc']=$disc;

            //所有日期的折扣之和
            $disc_price+=$day_full['topay']['disc_price'];
            $disc_price_cn+=$day_full['topay']['disc_price_cn'];

            $pricelist[$k]=$day_full;

        }


//add_beds_check

        $add_price_extra = array(
            'room_price_count_check' => $room_obj->room_price_count_check,
            'disc_price' => $disc_price,
            'disc_price_cn' => $disc_price_cn,
            'base_price' => $base_price,
            'base_price_cn' => $base_price_cn,
            'add_man' => array(
                'add_man_num' => $add_man_num,
                'add_man_price_cn' => $add_man_price_cn,
                'original_add_man_price' => $original_add_man_price
            ),
            'add_bed' => array(
                'add_bed_num' => $add_bed_num,
                'add_bed_price_cn' => $add_bed_price_cn,
                'original_add_bed_price' => $original_add_bed_price
            ),
        );
        return array("msg" => $msg, "original_price" => $original_price, "price_cn" => $price_cn, 'add_price_extra' => $add_price_extra,'datelist'=>$pricelist);

    }

    private function calculate_price($day, $room_obj, $room_num, $guest_num, $add_bed_num = 0,$afterdisc=true)
    {
        $msg = 'normal';
        $disc = $day['disc'];
       // $actual = $day['actual'];
       // $base = $day['base'];

        if ($afterdisc) {
            $price = $day['actual'];
        } else {
            $price = $day['base'];
        }

        $room_model = $room_obj->room_model;  //房型
        if (empty($room_model)) {
            $room_model = 1;
        }

        if ($add_bed_num >= 1) {//加床的价钱是所有的~
            $add_bed_price = $price['original_add_bed_price'] * $add_bed_num;
            $add_bed_price_cn = $price['add_bed_price_cn'] * $add_bed_num;
            $msg = 'add_man_or_bed';
        } else {
            $add_bed_price = 0;
            $add_bed_price_cn = 0;
        }


        if ($room_obj->room_price_count_check == 2) {
            //按人去计算房间价格
            if ($guest_num <= $room_model) {
                $topay['original_price'] = $price['original_price'] * $guest_num;
                $topay['price_cn'] = $price['price_cn'] * $guest_num;

                $base_price = $topay['original_price'];
                $base_price_cn = $topay['price_cn'];

                $topay['original_price'] = $price['original_price'] + $add_bed_price;
                $topay['price_cn'] = $price['price_cn'] + $add_bed_price_cn;
            } else {
                $msg = 'add_man_or_bed';
                $topay['original_price'] = $price['original_price'] * $room_model;
                $topay['price_cn'] = $price['price_cn'] * $room_model;

                $base_price = $topay['original_price'];
                $base_price_cn = $topay['price_cn'];


                $add_man_num = $guest_num - $room_model;
                if ($room_obj->add_bed_check) {
                    $topay['original_price'] = $topay['original_price'] + $add_man_num * $price['original_add_man_price'] + $add_bed_price;
                    $topay['price_cn'] = $topay['price_cn'] + $add_man_num * $price['add_man_price_cn'] + $add_bed_price_cn;
                }
            }
        } else {
            //按房间数去计算价格
            if ($guest_num <= $room_model) {
                $base_price = $price['original_price'] * $room_num;

                $base_price_cn = $price['price_cn'] * $room_num;

                $topay['original_price'] = $base_price + $add_bed_price;

                $topay['price_cn'] = $base_price_cn * $room_num + $add_bed_price_cn;


            } else {
                $msg = "add_man_or_bed";
                $topay['original_price'] = $price['original_price'] * $room_num;
                $topay['price_cn'] = $price['price_cn'] * $room_num;

                $base_price = $topay['original_price'];
                $base_price_cn = $topay['price_cn'];

                $add_man_num = $guest_num - $room_model;
                if ($room_obj->add_bed_check) {
                    $topay['original_price'] = $base_price + $add_man_num * $price['original_add_man_price'] + $add_bed_price;
                    $topay['price_cn'] = $base_price_cn + $add_man_num * $price['add_man_price_cn'] + $add_bed_price_cn;
                }
            }
        }




        $topay['base_price'] = $base_price;
        $topay['base_price_cn'] = $base_price_cn;


        $topay['add_man'] = array(
            'add_man_num' => $add_man_num,
            'add_man_price_cn' => $price['add_man_price_cn'] * $add_man_num,
            'original_add_man_price' => $price['original_add_man_price'] * $add_man_num
        );
        $topay['add_bed'] = array(
            'add_bed_num' => $add_bed_num,
            'add_bed_price_cn' => $add_bed_price_cn,
            'original_add_bed_price' => $add_bed_price
        );
        $topay['total_price'] = $topay['original_price'];
        $topay['total_price_cn'] = $topay['price_cn'];

        $day['topay'] = $topay;
        $day['msg'] = $msg;
        $day['disc']=$disc;

        unset($topay['original_price']);
        unset($topay['price_cn']);
        return $day;

    }


    /*
     * 获取某一个时间段，某个房型，的每一天的价格！也是非常单纯的价格！！！！
     * 当然，当disc 为  true时 考虑打折的情况
     */
    public function get_room_price_list($nid,$in,$out,$disc = true)
    {
        $add_man = self::get_additional_price($nid,$in,$out,$type=1,$status=1);
        $room_obj = $this->roominfodao->room_detail_contact_order($nid);
        $add_bed = $room_obj->add_beds_price;
        $result = array();
        $date_s = strtotime($in);
        $date_e = strtotime($out);
        while($date_s<$date_e) {
            $price=$this->roominfodao->get_room_status_tracs_by_nid_and_date($nid, date("Y-m-d",$date_s));
            if(!$price){$price['room_date'] = date("Y-m-d",$date_s);}
            $prices[] = $price;
            unset($price);
            $date_s += 24 * 3600;
        }
        $date_s = strtotime($in);
        $date_e = strtotime($out);

        $room_obj = $this->roominfodao->room_detail_contact_order($nid);
        $day = ($date_e - $date_s)/86400;//入住天数
        if($day < 1) {
            $result[$in]= self::get_room_price($nid,$in,$disc);
            return $result;
        }

        $uid = $this->roominfodao->get_room_uid_by_nid($nid);
        $config = $this->roominfodao->fetch_room_price_config($uid);
        if($disc)
        {
        //需要考虑打折情况
            $discounts = $this->discbll->get_period_discs(strtotime($in),strtotime($out),$nid);
        }else{
            while($date_s<$date_e) {
                $discounts[$date_s] = 1;
                $date_s += 24 * 3600;
            }
        }
        foreach($prices as $key => $price)
        {
            if ($price['room_price']) {
                $result[$price['room_date']]['original_price'] = round($price['room_price']*$discounts[strtotime($price['room_date'])]);
                $result[$price['room_date']]['price_cn'] = round(Util_Common::zzk_price_convert($price['room_price'],$room_obj->dest_id)*$discounts[strtotime($price['room_date'])]);
            }else {
                $r = $this->homestaybll->rpd_parse_v3($config['room_date'], $config['room_price'], $nid, $price['room_date']);
                $result[$price['room_date']]['original_price'] = round($r["price"]*$discounts[strtotime($price['room_date'])]);
                $result[$price['room_date']]['price_cn'] = round(Util_Common::zzk_price_convert($r["price"],$room_obj->dest_id)*$discounts[strtotime($price['room_date'])]);
            }
            $result[$price['room_date']]['original_add_man_price'] = $add_man[$price['room_date']]*$discounts[strtotime($price['room_date'])];
            $result[$price['room_date']]['original_add_bed_price'] = $add_bed*$discounts[strtotime($price['room_date'])];
            $result[$price['room_date']]['add_man_price_cn'] = round((Util_Common::zzk_price_convert($add_man[$price['room_date']],$room_obj->dest_id))*$discounts[strtotime($price['room_date'])]);
            $result[$price['room_date']]['add_bed_price_cn'] = round((Util_Common::zzk_price_convert($add_bed,$room_obj->dest_id))*$discounts[strtotime($price['room_date'])]);

        }
        return $result;
    }
    /*
     * 获取某一个时间段，某个房型，的总价格！也是非常单纯的价格！！！！
     * 当然，当disc 为  true时 考虑打折的情况
     */
    public function get_room_price_4_days($nid,$in,$out,$disc = true)
    {
        $money = array();
        $result = self::get_room_price_list($nid,$in,$out,$disc);
        if($result){
            foreach($result as $k => $v){
                $money['original_price'] += $v['original_price'];
                $money['original_add_man_price'] += $v['original_add_man_price'];
                $money['original_add_bed_price'] += $v['original_add_bed_price'];
                $money['add_man_price_cn'] += $v['add_man_price_cn'];
                $money['add_bed_price_cn'] += $v['add_bed_price_cn'];
                $money['price_cn'] += $v['price_cn'];
            }
            return $money;
        }
        return false;
    }

    /*
     * 获取一个时间段内，某个房型，的价格。
     * 基于人数和预定房间数,以及加床数,或给出价格,或参考价格~
     * 返回值array("msg"=>$msg,"price"=>price) 参考价钱
     * 已经考虑打折的情况在里面了
     */
    public function get_total_price($nid,$in,$out,$room_num,$guest_num,$add_bed_num=0,$disc=true)
    {
        $msg_normal="normal";
        $msg_add="add_man_or_bed";
        $msg = $msg_normal;
        $room_obj = $this->roominfodao->room_detail_contact_order($nid);
        $room_model = $room_obj->room_model;  //房型
        if(empty($room_model)){$room_model = 1;}
        $total_room_model = $room_model * $room_num;  //房型*房间数 （实际上是可入住最大人数）
        $result = self::get_room_price_4_days($nid,$in,$out,$disc);
        if($add_bed_num>=1) {//加床的价钱是所有的~
            $add_bed_price = $result['original_add_bed_price'] * $add_bed_num;
            $add_bed_price_cn = $result['add_bed_price_cn'] * $add_bed_num;
            $msg = $msg_add;
        }else{
            $add_bed_price = 0;
            $add_bed_price_cn = 0;
        }
//add_beds_check
        if($room_obj->room_price_count_check==2){
            //按人去计算房间价格
            if($guest_num<=$total_room_model)
            {
                $result['original_price'] = $result['original_price'] * $guest_num;
                $result['price_cn'] = $result['price_cn'] * $guest_num;
                $base_price=$result['original_price'];
                $base_price_cn=$result['price_cn'];
                $result['original_price'] = $result['original_price'] + $add_bed_price;
                $result['price_cn'] = $result['price_cn'] + $add_bed_price_cn;
            }else{
                $msg = $msg_add;
                $result['original_price'] = $result['original_price'] * $total_room_model;
                $result['price_cn'] = $result['price_cn'] * $total_room_model;

                $base_price=$result['original_price'];
                $base_price_cn=$result['price_cn'];

                if($room_obj->add_bed_check){
                    $add_man_num = $guest_num - $total_room_model;

                    $result['original_price'] = $result['original_price'] + $add_man_num*$result['original_add_man_price'] +$add_bed_price;
                    $result['price_cn'] = $result['price_cn'] + $add_man_num*$result['add_man_price_cn'] + $add_bed_price_cn;
                }
            }
        }else{
            //按房间数去计算价格
            if($guest_num<=$total_room_model){
                $result['original_price'] =  $result['original_price'] * $room_num;
                $base_price=$result['original_price'];
                $result['original_price'] =  $result['original_price'] + $add_bed_price;

                $result['price_cn'] =  $result['price_cn'] * $room_num;
                $base_price_cn=$result['price_cn'];

                $result['price_cn'] =  $result['price_cn'] + $add_bed_price_cn;
            }else{
                $msg = $msg_add;
                $result['original_price'] = $result['original_price'] * $room_num;
                $result['price_cn'] = $result['price_cn'] * $room_num;

                $base_price=$result['original_price'];
                $base_price_cn=$result['price_cn'];

                if($room_obj->add_bed_check) {
                    $add_man_num = $guest_num - $total_room_model;
                    $result['original_price'] = $result['original_price'] + $add_man_num * $result['original_add_man_price'] + $add_bed_price;
                    $result['price_cn'] = $result['price_cn'] + $add_man_num * $result['add_man_price_cn'] + $add_bed_price_cn;
                }
            }
        }
        $add_price_extra = array(
            'room_price_count_check' => $room_obj->room_price_count_check,
            'base_price' => $base_price,
            'base_price_cn' => $base_price_cn,
            'add_man' => array(
                'add_man_num' => $add_man_num,
                'add_man_price_cn' => $result['add_man_price_cn']*$add_man_num,
                'original_add_man_price' => $result['original_add_man_price']*$add_man_num
            ),
            'add_bed' => array(
                'add_bed_num' => $add_bed_num,
                'add_bed_price_cn' => $add_bed_price_cn,
                'original_add_bed_price' => $add_bed_price
            ),
        );
        return array("msg"=>$msg,"original_price"=>$result['original_price'],"price_cn"=>$result['price_cn'],'add_price_extra'=>$add_price_extra);
    }

    //计算加人价格
    public function get_additional_price($nid,$in,$out,$type=1,$status=1){
        if(!$nid) return;
        if($this->get_special_additional_price[$nid.'_'.$type."_".$in.$out.'_'.$status]){
            return $this->get_special_additional_price[$nid.'_'.$type."_".$in.$out.'_'.$status];
        }else{
            $date_s = strtotime($in);
            $date_e = strtotime($out);
            $room_obj = $this->roominfodao->room_detail_contact_order($nid);
            $add_man_price = $room_obj->add_bed_price;//加人价格
            if($date_s == $date_e){
                $days = array($in);
                $result[$in] = $add_man_price;
            }else{
                $days=array();
                while($date_s<$date_e) {
                    $days[] = date("Y-m-d",$date_s);
                    $result[date("Y-m-d",$date_s)] = $add_man_price;
                    $date_s += 24 * 3600;
                }
            }
            $params = array(
                'nid' => $nid,
                'status' => $status,
            );
            if($type) $params['type']= $type;
            if(!empty($days)) $params['room_date'] = array_unique($days);
            $result_1  = $this->roomdao->get_special_additional_price($params);
            foreach($result_1 as $v){
                if($v['price']){
                    $result[$v['room_date']] = $v['price'];
                }
            }
            $this->get_special_additional_price[$nid.'_'.$type."_".$in.$out.'_'.$status] =  $result;
            return $this->get_special_additional_price[$nid.'_'.$type."_".$in.$out.'_'.$status];
        }
    }
    public $get_all_price_bynid=array();
	// add by Leon 取出所有的价格
    public function get_all_price_bynid($nid, $start = null, $end = null) {
        $uid = $this->roominfodao->get_room_uid_by_nid($nid);
		$fieldbll = new Bll_Field_Info();
		$fielddata = $fieldbll->get_node_field_by_nids($nid);
		$people_config = APF::get_instance()->get_config("roompeoplenum", "roompeoplenum");
		$default_beds_num = $people_config[$fielddata[$nid]['field_data_field_room_beds']['field_room_beds_tid']];
        $tracs_price = $this->roominfodao->get_room_status_tracs_valid_date($nid); //日历表价格
        if(isset($this->get_all_price_bynid[$uid]))
        {$rp_price =  $this->get_all_price_bynid[$uid];}
        else{
            $rp_price = $this->roominfodao->fetch_room_price_config($uid); // 批量房价价格
            $this->get_all_price_bynid[$uid] = $rp_price;
        }
		$homestaybll = new Bll_Homestay_StayInfo();
        foreach($tracs_price as $row) {
			$result[$row['room_date']] = array(
						'date' => $row['room_date'],
						'price' => $row['room_price'],
						'room_num' => $row['room_num'],
						'beds_num' => $row['beds_num'],
				);
        }
		//循环所有日期 如果缺少日期价格，则用批量房价替代
        if($start===null) $start = date('Y-m-d');
		if($end===null)   $end = date('Y-m-d',strtotime('+12 months', strtotime(date('Y-m')))-60*60*24);
		for($date=$start; strtotime($date)<=strtotime($end); $date=date('Y-m-d', strtotime("+1 days", strtotime($date)))) {
			if(empty($result[$date]['price'])) {
				$rp_data = $homestaybll->rpd_parse_v3($rp_price['room_date'], $rp_price['room_price'], $nid, $date);
				$result[$date] = array(
						'date' => $rp_data['date'],
						'price' => $rp_data['price'],
						'room_num' => (empty($result[$date]) ? 1 : $result[$date]['room_num']), // 如果没有记录再用默认值
						'beds_num' => (empty($result[$date]) ? $default_beds_num : $result[$date]['beds_num']) , // 人数默认2人
				);
			}
		}
		return $result;
    }


    private function log($msg)//即时输出调试使用
    {
        if ($this->debug == true)
            return;
        print_r($msg);
        echo "<br>";
        ob_flush();
        flush();
    }
}








