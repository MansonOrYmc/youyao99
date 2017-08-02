<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/8/4
 * Time: 上午11:06
 */
class Bll_Disc_Info {
    private static $dao;
    public $base_disc_info;//为了减小数据连接，设置该变量，这是个数组，key是房间号
    static $get_cal_discs_02;
    public function __construct() {
        self::$dao    = new Dao_Disc_Info();
    }
    static function get_dao(){
        if(!empty(self::$dao)){
            return self::$dao;
        }
        else{
            self::$dao = new Dao_Disc_Info();
            return self::$dao;
        }
    }
    private function add_discs($discs)
    {
        foreach($discs as $key => $value) {
            self::$dao->update_info($value);
        }
    }
    public function insert_discs($disc,$sDate,$eDate,$nid,$least_days)
    {
        if(self::check_date_time($sDate)) {$sDate = strtotime($sDate);}
        if(self::check_date_time($eDate)) {$eDate = strtotime($eDate);}
        //$sDate 是时间戳
        if($disc<=1&&$disc>=0){
            $disc = $disc."_".$disc."_".$disc."_".$disc."_".$disc."_".$disc."_".$disc;
        }
        $week_disc = explode("_", $disc);
        while($sDate<=$eDate) {
            $x = date("w", $sDate);
            //echo $x;

            $d = array('nid' => $nid, 'occ_date' => $sDate, 'disc' => $week_disc[$x],'least_days' => $least_days);
            $discs[] = $d;
            $sDate += 24 * 3600;
        }
        self::add_discs($discs);
    }

    public function get_month_discs($month,$year,$nid)
    {
        $disc_type = self::get_disc_type_by_nid($nid);
        if($disc_type == disc_type::$putong||$disc_type == disc_type::$putongforever){
            $disc = true;
        }else{ $disc = false;}
        $days_in_month = date('t',mktime(0,0,0,$month,1,$year));//这个月一共有多少天
        $date_s = strtotime("$year-$month-1 00:00:00");
        $date_e = strtotime("$year-$month-$days_in_month 00:00:00");
        $discs = self::$dao->get_period_disc($date_s,$date_e,$nid);

        while($date_s<=$date_e) {
            $result[$date_s] = 1;
            $date_s += 24 * 3600;
        }

        if($disc){
            foreach($discs as $key=>$disc) {
                $result[date('Y-m-d',$disc['occ_date'])] = $disc['disc'];
            }
        }

        return $result;
    }

    //循环所有日期 如果缺少日期价格，则用批量房价替代

    public function get_cal_discs($nid)
    {
        //获取一年的打折信息
        //但是如果打折类型为连住的，打折信息取消显示
        //目前还是需要展示
        $disc_type = self::get_disc_type_by_nid($nid);
        if($disc_type == disc_type::$putong||$disc_type == disc_type::$putongforever){
            $disc = true;
        }else{ $disc = false;}

        $start = date('Y');
        $end = date('Y',strtotime('+12 months', strtotime($start."-01-01")));
        $date_s = strtotime($start."-01-01");
        $date_e = strtotime($end."-12-31");
        $discs = self::$dao->get_period_disc($date_s,$date_e,$nid);

        while($date_s<=$date_e) {
            $result[date('Y-m-d',$date_s)] = 1;
            $date_s += 24 * 3600;
        }
        if($disc){
            foreach($discs as $key=>$disc) {
                $result[date('Y-m-d',$disc['occ_date'])] = $disc['disc'];
            }
        }
        return $result;
    }

    public static function get_cal_discs_02($nid){
        self::get_dao();
        if(!empty(self::$get_cal_discs_02[$nid])){
            return self::$get_cal_discs_02[$nid];
        }else{
            //获取一年的打折信息
            //目前还是需要展示
//            $memcache = APF_Cache_Factory::get_instance()->get_memcache();
//            $key_m = 'get_cal_discs_02'.$nid;
//            $value = $memcache->get($key_m);
            if(true) {
                $time = 60;
                $start = date('Y');
                $end = date('Y',strtotime('+12 months', strtotime($start."-01-01")));
                $date_s = strtotime($start."-01-01");
                $date_e = strtotime($end."-12-31");
                $discs = self::$dao->get_period_disc($date_s,$date_e,$nid);
                foreach($discs as $value){
                    if($value['occ_date']-1<0 && $value['disc']<1){
                        while($date_s<=$date_e) {
                            $result[date('Y-m-d',$date_s)] = array('disc'=>$value['disc'],'least_days'=>$value['least_days']);
                            $date_s += 24 * 3600;
                        }
                        self::$get_cal_discs_02[$nid] = $result;
                        return $result;
                    }
                }
                $result = false;
                while($date_s<=$date_e) {
                    $result[date('Y-m-d',$date_s)] = array('disc'=>1,'least_days'=>1);
                    $date_s += 24 * 3600;
                }
                foreach($discs as $key=>$disc) {
                    $result[date('Y-m-d',$disc['occ_date'])] = array('disc'=>$disc['disc'],'least_days'=>$disc['least_days']);
                }
                self::$get_cal_discs_02[$nid] = $result;
//                $memcache->set($key_m, $result, 0, $time);
                return $result;
            }
        }
    }


    public function get_period_discs($in,$out,$nid,$need_days=false)
    {
        if(self::check_date_time($in)) {$date_s = strtotime($in);} else {$date_s = $in;}
        if(self::check_date_time($out)) {$date_e = strtotime($out);} else {$date_e = $out;}
        if($date_s == $date_e){ $date_e+=3600*24;  }
        $least_days = 1;
        $base_disc = 1;
        //入住天数
        $days = ($date_e - $date_s)/(3600*24);if($days == 0){$days = 1;}
        //打折类型
        $disc_type = self::get_disc_type_by_nid($nid);

        $perioddiscs = self::$dao->get_period_disc($date_s,$date_e,$nid);



        //需要考虑连住打折的情况
//        if($disc_type == disc_type::$lianzhuforever){
//            $least_days = $this->base_disc_info[$nid][0]['least_days'];
//            //永久连住的情况下，需要判断时间段是否大于等于连住天数
//            if(($days - $least_days) > 0 || $days == $least_days){
//                $base_disc = $this->base_disc_info[$nid][0]['disc'];
//            }else{
//            }
//            while($date_s<$date_e) {
//                $result[$date_s]['disc'] = $base_disc;
//                $result[$date_s]['least_days'] = $least_days;
//                $date_s += 24 * 3600;
//            }
//        }elseif($disc_type == disc_type::$putongforever){
//            //普通打折永远有效
//            $base_disc = $this->base_disc_info[$nid][0]['disc'];
//            while($date_s<$date_e) {
//                $result[$date_s]['disc'] = $base_disc;
//                $result[$date_s]['least_days'] = $least_days;
//                $date_s += 24 * 3600;
//            }
//        }elseif($disc_type == disc_type::$lianzhu || $disc_type == disc_type::$putong){
            //连住打折，比较复杂，what a fuck!!!!!!!!!!

//        lec  只区分是否打折 不管类型,避免 设置了全年打折 又设置了单独打折造成的混淆
         if($disc_type!=disc_type::$nodisc){
            while($date_s<$date_e) {
                $result[$date_s]['disc'] = $base_disc;
                $result[$date_s]['least_days'] = $least_days;
                $date_s += 24 * 3600;
            }

            foreach($perioddiscs as $key=>$disc) {
                if(isset($result[$disc['occ_date']])) {
                    $result[$disc['occ_date']]['disc'] = $disc['disc'];
                    $result[$disc['occ_date']]['least_days'] = $disc['least_days'];
                }
            }

            /*临时变量初始化*/
            $tmpdisc = $result[strtotime($in)]['disc'];
            $tmpleast = $result[strtotime($in)]['least_days'];
            $tmpcount = 0;
            $tmparr = array();
            /*临时变量初始化*/

            foreach($result as $key => &$value){
                if($tmpdisc ==  $value['disc'] && $tmpleast == $value['least_days'])
                {
                    $tmparr[$key] = array('disc' => '1','least_days'=>$value['least_days']);
                    $tmpcount++;
                }
                else{
                    if($tmpcount >= $tmpleast){

                    }else{
                        foreach($tmparr as $tmpkey => $tmpvalue){
                            $result[$tmpkey] = $tmpvalue;
                        }
                    }
                    $tmpdisc =  $value['disc'];
                    $tmpleast =  $value['least_days'];
                    $tmpcount = 1;
                    unset($tmparr);
                    $tmparr[$key] = array('disc' => '1','least_days'=>$value['least_days']);
                }
            }

            if($tmpcount < $tmpleast){
                foreach($tmparr as $tmpkey => $tmpvalue){
                    $result[$tmpkey] = $tmpvalue;
                }
            }
        }elseif($disc_type == disc_type::$putong){
                //普通打折，实际是连住打折的特殊情况！！！！
        }else{
            //不打折
            while($date_s<$date_e) {
                $result[$date_s]['disc'] = $base_disc;
                $result[$date_s]['least_days'] = $least_days;
                $date_s += 24 * 3600;
            }
        }
        if($need_days){}else {
            foreach ($result as $re_k => &$re_v) {
                $re_v = $re_v['disc'];
            }
        }
        return $result;
    }

    function check_date_time($str, $format="Y-m-d"){
        $unixTime=strtotime($str);
        $checkDate= date($format, $unixTime);
        if($checkDate==$str)
            return 1;
        else
            return 0;
    }

    /*
        判断某个房间的打折类型
        ①是否是永久连住；nid | timestamp = 0 | status =1 | least_days = 2;   表示连住永远启动！
        ②是否是永久普通打折；
        ③是否是普通连住；
        ④是否是普通打折；
        0⃣️ 没有打折
    */
    function get_disc_type_by_nid($nid){
        if(!isset($this->base_disc_info[$nid])){$this->base_disc_info[$nid] = self::$dao->get_info($nid);}
        if($this->base_disc_info[$nid]){
            //遍历
            foreach($this->base_disc_info[$nid] as $disc){
                if($disc['occ_date'] < 1 && $disc['least_days'] > 1) {
                    //这个时候表示永久连住
                    return disc_type::$lianzhuforever;
                }elseif($disc['occ_date'] < 1 && $disc['least_days'] = 1){
                    //这个时候表示永久打折
                    return disc_type::$putongforever;
                }elseif($disc['occ_date'] > 1 && $disc['least_days'] > 1){
                    //普通连住
                    return disc_type::$lianzhu;
                }elseif($disc['occ_date'] > 1 && $disc['least_days'] = 1){
                    //普通打折
                    return disc_type::$putong;
                }else{
                    return disc_type::$nodisc;
                }
            }
        }else{
            //该房间如果没有打折信息，则返回false
            return disc_type::$nodisc;
        }
    }

    /*// todo  一个坑,, app 上线后 房间的折扣 是多变的
     * 获取折扣信息 按时间段区分 目前同一个房间 最小天数和打折比率肯定是相同的
     */
    function get_disc_info($nid){
        $result=array();
        $date_result=array();
        $disc_result=array();
        $least_days_result=array();
        $info=self::$dao->get_info($nid);
        if(!empty($info)){
            /*
             * 取得折扣
             */
            $disc_result=$info[0]["disc"];
            $least_days_result=$info[0]["least_days"];
            /*
             * 全时间段
             */
            if($info[0]["occ_date"]==0){

            }else{
                /*
             * 取连续时间段
             */
                $days_period=array();
                foreach($info as $key=>$value){
                    $days_period[$key]=$value["occ_date"];
                }
                $node=array();
                if(count($days_period)>1){
                    foreach($days_period as $key=>$value){
                        //if($key>0){
                        if(($days_period[$key] - $days_period[$key-1])>86400){
                            $node[]=$key;
                        }
                        // }
                    }
                }else{
                    $node[]=0;
                }

                $date_result=array();

                for($i=0;$i<count($node);$i++){
                    $date_result[$i]["start_date"]=$info[$node[$i]]["occ_date_format"];
                    if($i<count($node)-1){
                        $date_result[$i]["end_date"]=$info[$node[$i+1]-1]["occ_date_format"];
                    }else{
                        $date_result[$i]["end_date"]=$info[count($info)-1]["occ_date_format"];
                    }
                }
            }

        }
        $result["disc"]=$disc_result;
        $result["date"]=$date_result;
        $result["least_days"]=$least_days_result;
        return $result;
    }

    public function  get_disc_roomids_by_uids($uids)
    {

        if (is_array($uids)) {
            array_map(function ($n) {
                return 'uid:' . $n;
            }, $uids);
            $query = implode('OR', $uids);
        } else $query = 'uid:' . $uids;

        $solr = Util_SolrCenter::zzk_get_tw_room_se_service();
        $params = array(
            "wt" => 'json',
        );
        $results = $solr->search($query, 0, 100, $params);

        $docs = $results->response->docs;
        foreach ($docs as $key => $value) {
            $disclist = $value->discount_room_dates_ss;
            if (empty($disclist)) continue;
            if ($disclist[0] != 'placeholder') $roomids[] = $value->id;
        }

        return $roomids;

    }
    public function get_disc_roomids_by_roomids($rids){
        if(empty($rids))return array();
        return self::$dao->get_disc_roomids_by_roomids($rids);
    }


    public function get_discrate_bynid($nid){
        return self::$dao->get_discrate_bynid($nid);
    }

    public function insert_room_disc($nid,$promotion,$least_days,$discount_rate,$promotion_dates){

        self::get_dao();
        return self::$dao->insert_room_disc($nid,$promotion,$least_days,$discount_rate,$promotion_dates);
    }

    public function bulk_insert_or_update_room_disc($nid,$days,$disc_rate,$least_day=1,$admin_id=1){
        if(empty($days)||empty($nid))return false;
        $dao=self::get_dao();
        $result=$dao->get_room_disc_by_nid_days($nid,$days);

        $exist_days=array_column($result,'occ_date_format');

        $toupdat_days=array();
        $toinsert_days=array();

        foreach($days as $day){
            if(in_array($day,$exist_days)){
                $toupdat_days[]=$day;
            }else{
                $toinsert_days[]=$day;
            }
        }

        if(empty($least_day))$least_day=1;
        if(empty($admin_id))$admin_id=1;

        if ($toinsert_days)
            $a = $dao->bulk_insert_room_disc($nid, $toinsert_days, $disc_rate, $least_day, $admin_id);

        if ($toupdat_days)
            $b = $dao->update_room_disc($nid, $toupdat_days, $disc_rate, $least_day, $admin_id);

        return $a||$b;
    }

    public function unset_room_disc($nid,$days,$uid=1){
        if(empty($uid))$uid=1;
        //取出全部的打折信息
        if(!isset($this->base_disc_info[$nid])) {
            $this->base_disc_info[$nid] = self::$dao->get_info($nid);
        }
        $info = $this->base_disc_info[$nid];
        //判断是否有全年打折
        $qndz = false;
        $have_set=array();
        foreach ($info as $k => $v) {
            if ($v['occ_date'] == 0) {
                //全年打折
                $qndz = true;
                $qninfo = array(
                    'least_days' => $v['least_days'],
                    'disc' => $v['disc']
                );
                unset($info[$k]);
            } else
               $have_set[]=$v['occ_date_format'];

        }
        //如果原来设置全年打折  则把原来occ_date==0 的改为 设置全年每日的折扣
        if ($qndz) {
            $today = strtotime(date('Y-m-d'));
            for ($i = 0; $i < 365; $i++) {
                $ocf = date('Y-m-d', $today + $i * 24 * 3600);
                if (!in_array($ocf, $have_set)) {
                    $todo[] = $ocf;
                }
            }
            self::$dao->close_all_year($nid, $uid);
            $this->bulk_insert_or_update_room_disc($nid, $todo, $qninfo['disc'], $qninfo['least_days'], $uid);

        }
        return self::$dao->unset_room_disc($nid, $days, $uid);
    }




}
class disc_type{
    /*
        判断某个房间的打折类型
        ①是否是永久连住；
        ②是否是永久普通打折；
        ③是否是普通连住；
        ④是否是普通打折；
        0⃣️ 没有打折
    */
    public static $lianzhuforever = 1;
    public static $putongforever = 2;
    public static $lianzhu = 3;
    public static $putong = 4;
    public static $nodisc =0;
}
