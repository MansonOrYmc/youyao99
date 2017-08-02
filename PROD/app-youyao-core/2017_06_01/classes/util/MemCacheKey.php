<?php
//我需要一个对memcache key的管理的类
class Util_MemCacheKey {
    public static function get_stayinfo_by_ids ($param) {
        return "get_stayinfo_wy_ids" . $param;
    }
    public static function get_stay_by_loc_typecode ($param) {
        return "get_stay_by_loc_utypecode" . $param;
    }

    public static function get_roominfo_by_uids ($param) {
        return "get_roominfo_by_uids" . $param;
    }
    public static function get_roomstatus_by_uids ($param) {
        return "get_roomstatus_by_uids" . $param;
    }
    public static function get_roombooking_by_nids ($param) {
        return "get_roombooking_by_nids" . $param;
    }
    public static function get_area_by_destid($param){
    	return "get_area_by_destid" . $param;
    }
    public static function get_roombooking_by_ids ($param) {
        return "get_roombooking_by_ids" . $param;
    }

    public static function get_rpconfig_by_uid($uid){
        return "get_rpconfig_by_uid" . $uid;
    }
    public static function get_uid_by_nid($nid){
        return "get_uid_by_nid" . $nid;
    }

    public static function get_lowest_room_price($checkin,$checkout,$nid,$promotion,$mutiprice=12){
        return "get_lowest_room_price" . $checkin.$checkout.$nid.$promotion.$mutiprice;
    }

    public static function get_order_submit_key(){
        return "zzk_order_submit";
    }
}
?>