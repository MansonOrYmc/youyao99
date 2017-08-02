<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/12/16
 * Time: 下午8:10
 */
class Sales_Firstorderpromotion{

    public static  $promotion_code = '2';
    const  promotion_type = 'zzk_bnb_firstorder';
    const  promotion_name = '首单立减';
    const  promotion_logo = '减';
    const promotion_pinyin = 'jian';
    public static $promotion_desc='抢鲜减30元';


    //实际上需要配置参与民宿，和民宿的具体优惠
    //public static $bnb_config = array('66'=>'30','1289'=>'30');

    private static $start_date = '2015-01-01';
    private static $end_date = '2015-12-01';
    private static $promotion_base_value = '30';
    private static $promotion_value = '0';

    private static function is_in_activity($uid,$oid){
        return false;
//        $r = !self::is_homestay_firstorder($uid,$oid);
//        return $r;
    }

    private static function is_in_date($date){
        $r =  (strtotime($date) > strtotime(self::$start_date) && strtotime($date) < strtotime(self::$end_date));
        return $r;
    }

    public static function get_promotion_value($uid,$price,$date,$oid){

        if(empty(self::$promotion_value)){
                if(self::is_in_date($date)&&self::is_in_activity($uid,$oid)){
                    self::$promotion_value = self::$promotion_base_value;
                }
        }
        return self::$promotion_value;
    }

    public static function keep_promotion_in_mysql($order_id){
        if(!empty(self::$promotion_value)){
            $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
            $sql = "insert into t_order_promotion (`order_id`,`promotion_code`,`create_time`,`update_time`,`status`,`promotion_value`) values (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE update_time='".REQUEST_TIME."' ,promotion_value = ".self::$promotion_value." ,status = 1";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute(array($order_id,self::$promotion_code,REQUEST_TIME,REQUEST_TIME,'1',self::$promotion_value));
        }
    }

    public static function is_homestay_firstorder($uid,$order_id){
        return true;
//        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
//        if($order_id){
//            $sql = "select count(*) from LKYou.t_order_promotion where order_id = '$order_id' and promotion_code = ".self::$promotion_code;
//            $stmt = $pdo->prepare($sql);
//            $stmt->execute();
//            $r = $stmt->fetchColumn();
//            if($r) return false;//实际上false代表参与了活动
//        }
//        $sql = "select count(*) from LKYou.t_homestay_booking where status in (2,6,7) and uid= :uid and id <> '$order_id'";
//        $stmt = $pdo->prepare($sql);
//        $stmt->execute(array('uid' => $uid));
//        return $stmt->fetchColumn();
    }

    public static function get_activity_bnb(){
        return array();
//        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
//        $sql = "select o.uid from  one_db.drupal_users_roles o left join LKYou.t_homestay_booking l on o.uid = l.uid and l.status in (0,1,2,4,6,7) where o.rid =5 and l.id is NULL;";
//        $stmt = $pdo->prepare($sql);
//        $stmt->execute();
//        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getPromotionDesc($uid)
    {
        return self::$promotion_desc;
    }
}