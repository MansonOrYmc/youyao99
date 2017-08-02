<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 16/02/02
 * Time: 下午4:06
 */
class So_NiceRecommend{
    public static function insert_update_recommend($order_id,$rooms){
        $roomsql = implode(',',$rooms);
        $sql = "insert into LKYou.t_order_recommend (`order_id`,`rooms`) values ('$order_id','$roomsql')  ON DUPLICATE KEY UPDATE `rooms`='$roomsql';";
        return DB::execSql($sql);
    }

    public static function get_recommend_rooms($order_id){
        $sql = "select * from LKYou.t_order_recommend where rooms<>'' and order_id = '$order_id';";
        $r =  DB::execSql($sql);
        return $r[0]['rooms'];
    }
}