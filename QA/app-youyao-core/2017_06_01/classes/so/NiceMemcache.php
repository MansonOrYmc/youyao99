<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/11/11
 * Time: 下午5:35
 */
class So_NiceMemcache{
    // 公钥
    public static $memcache;


    public static function get_instance(){
        return APF_Cache_Factory::get_instance()->get_memcache();
    }


}