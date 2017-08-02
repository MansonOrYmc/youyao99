<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 16/2/3
 * Time: 下午3:44
 */
class So_NiceMoney{
    private static $money = array(10=>'NT$',11=>'円',12=>'¥',13=>'$',14=>'HK$',15=>'KRW');
    public static function getMoney($destid)
    {
        return self::$money[$destid];
    }
    public static function getPromotion($orders){
        $in = implode(" , ",$orders);
        $sql = "select sum(promotion_value) as order_profit ,order_id from t_order_promotion where order_id in( $in ) group by order_id ";
        return DB::execSql($sql);
    }
}