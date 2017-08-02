<?php

class  Bll_User_Static {
    public static function get_rp_config($uid){
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $key =  Util_MemCacheKey::get_rpconfig_by_uid($uid);
        $result = $memcache->get($key);
        if(empty($result)){
            $lky_pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
            $sql = "select room_date, room_price from LKYou.t_rpconfig_v2 where uid = ?";
            $stmt = $lky_pdo->prepare($sql);
            $stmt->execute(array($uid));
            $rpconfig = $stmt->fetch();
            $memcache->set($key,$rpconfig,null,86400);
            $result = $rpconfig;
        }
        return $result;
    }

    public static function update_rp_config($uid){
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $key =  Util_MemCacheKey::get_rpconfig_by_uid($uid);
        $lky_pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "select room_date, room_price from LKYou.t_rpconfig_v2 where uid = ?";
        $stmt = $lky_pdo->prepare($sql);
        $stmt->execute(array($uid));
        $rpconfig = $stmt->fetch();
        $memcache->set($key,$rpconfig,null,86400);
    }

    public static function get_uid_by_nid($nid){
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $key =  Util_MemCacheKey::get_uid_by_nid($nid);
        $result = $memcache->get($key);
        if(empty($result)){
            $uid = Bll_Room_Static::get_uid_by_nid($nid);
            $memcache->set($key,$uid,null,86400*30);
            $result = $uid;
        }
        return $result;
    }

    public static function get_user_info_by_uid()
    {

    }

    public static function get_user_nickname_by_uid($uid){
        $bll = new Bll_User_UserInfo();
        $nickname =  $bll->get_user_nickname_by_uid($uid);
        if($nickname) return $nickname;
        return '自在客用户';
    }

    public static function get_breakfast_by_uid($uid){
        $dao  = new Dao_Homestay_StayMemcache();
        $breakfast = $dao->get_zaocan_byuid($uid);
        return $breakfast;
    }
}
