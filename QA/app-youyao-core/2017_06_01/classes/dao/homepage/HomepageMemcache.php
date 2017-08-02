<?php
apf_require_class("APF_Cache_Factory");

class Dao_Homepage_HomepageMemcache extends Dao_Homepage_HomepageInfo {
    public function homepage_cache($preview = 0) {
        if($preview == 1){
            $value = parent::homepage_cache($preview);
        }else{
            $memcache = APF_Cache_Factory::get_instance()->get_memcache();
            $key = 'homepage_bigpic';
            $value = $memcache->get($key);
            if(!$value||get_cfg_var('vruan')=='handsome') {
                $time = 86400;
                $value = parent::homepage_cache($preview);
                $memcache->set($key, $value, 0, $time);
            }
        }
        return $value;
    }

    public function homepage_cache_all($preview = 0) {
        if($preview == 1){
            $value = parent::homepage_cache_all(1);
        }else{
            $memcache = APF_Cache_Factory::get_instance()->get_memcache();
            $key = 'homepage_cache';
            $value = $memcache->get($key);
            if(!$value||get_cfg_var('vruan')=='handsome') {
                $time = 86400;
                $value = parent::homepage_cache_all();
                $memcache->set($key, $value, 0, $time);
            }
        }
        return $value;
    }

    public function get_dest_list() {
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $key = 'dest_list';
        $value = $memcache->get($key);
        if(!$value) {
            $time = 86400;
            $value = parent::get_dest_list();
            $memcache->add($key, $value, 0, $time);
        }
        return $value;
    }

    public function get_loc_list() {
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $key = 'loc_list';
        $value = $memcache->get($key);
        if(!$value) {
            $time = 86400;
            $value = parent::get_loc_list();
            $memcache->add($key, $value, 0, $time);
        }
        return $value;
    }

    public function get_home_stay_images($id, $limit = 100) {
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $key = 'home_stay_image_forchange3'.$id;
        $value = $memcache->get($key);
        if(!$value) {
            $time = 300;
            $value = parent::get_home_stay_images($id, $limit);
            $memcache->add($key, $value, 0, $time);
        }
        return $value;
    }

    public function homepage_data_cache(){
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $key = 'homepage_data';
        $value = $memcache->get($key);
        if(!$value) {
            $time = 3600*24;
            $value = parent::homepage_data_cache();
            $memcache->add($key, $value, 0, $time);
        }
        return $value;
    }

    public function clear_page_cache() {
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $key = 'homepage_cache';
        $value = $memcache->delete($key);
    }

//    public function is_homestay_firstorder($uid){
//        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
//        $key = 'is_homestay_firstorder';
//        $value = $memcache->get($key);
//        if(!$value) {
//            $time = 3600*24;
//            $value = parent::is_homestay_firstorder($uid);
//            $memcache->add($key, $value, 0, $time);
//        }
//        return $value;
//    }

}
