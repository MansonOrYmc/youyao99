<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 16/02/02
 * Time: 下午4:06
 */
class So_NicePartner{

    public static $dao;
    public static function get_dao(){
        if(!self::$dao){
            self::$dao = new Dao_Partner_Info();
        }
        return self::$dao;
    }

    public static function total_homestay_num($pid){
        $dao = self::get_dao();
        $infos = $dao->get_homestay_infos($pid);
        return count($infos);
    }
    public static function total_nights($pid,$checkin=false){
        $dao = self::get_dao();
        $num = $dao->total_nights($pid,$checkin);
        return $num;
    }

    public static function get_base_info($pid){
        $dao = self::get_dao();
        $r = $dao->get_base_info($pid);
        foreach($r as &$v){
            $v['created'] = date("Y-m-d",$v['created']);
        }
        return $r;
    }
    public static function get_order_infos($pid){
        $dao = self::get_dao();
        $r = $dao->get_clean_orders($pid);
        return $r;
    }
    //判断某用户是不是合伙人
    public static function is_partner($uid)
    {
        if(empty($uid)){
            return false;
        }
        $sql = "select count(*) as total from t_partner where `partner_id` ='$uid' and `status`='1' ";
        $r = DB::execSql($sql);
        return $r[0]['total'];
    }

}