<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/12/16
 * Time: 下午8:10
 */
class Sales_Zzkdiscpromotion{
    public static  $promotion_code = '1';
    const  promotion_type = 'zzk_bnb_disc';
    const  promotion_name = '平台促销';
    const  promotion_logo = '促';
    const promotion_pinyin = 'cu';
    public static $promotion_desc='';
    //实际上需要配置参与民宿，和民宿的具体优惠
    public static $bnb_config = array(
        //'66'=>'0.07'
    );


    private static $start_date = '2015-01-01';
    private static $end_date = '2016-12-01';
    private static $promotion_value = 0;


    private static function is_in_activity($uid,$oid=null){
        $r = isset(self::$bnb_config[$uid]);
        return $r;
    }

    private static function is_in_date($date){
        $r =  (strtotime($date) > strtotime(self::$start_date) && strtotime($date) < strtotime(self::$end_date));
        return $r;
    }

    public static function get_promotion_value($uid,$price,$date,$oid){
                if(self::is_in_date($date)&&self::is_in_activity($uid,$oid)){
                    self::$promotion_value =  round(($price*(self::$bnb_config[$uid])),0);
                }
        return self::$promotion_value;
    }

    public static function keep_promotion_in_mysql($order_id){
        if(!empty(self::$promotion_value)){
            $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
            $sql = "insert into t_order_promotion (`order_id`,`promotion_code`,`create_time`,`update_time`,`status`,`promotion_value`) values (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE update_time='".REQUEST_TIME."' ,promotion_value =  ".self::$promotion_value." ,status = 1";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute(array($order_id,self::$promotion_code,REQUEST_TIME,REQUEST_TIME,'1',self::$promotion_value));
        }
    }

    public static function getPromotionDesc($uid)
    {
        if(empty(self::$promotion_desc)){
            if(isset(self::$bnb_config[$uid]))
//            self::$promotion_desc = '预订立享'.(10-self::$bnb_config[$uid]*10).'折';
            self::$promotion_desc = '人气热卖';
            else self::$promotion_desc = self::promotion_name;
        }
        return self::$promotion_desc;
    }
}
