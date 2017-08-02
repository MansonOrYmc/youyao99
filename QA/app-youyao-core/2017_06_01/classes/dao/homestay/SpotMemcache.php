<?php
apf_require_class("Dao_HomeStay_Spot");
apf_require_class("APF_Cache_Factory");

class Dao_Homestay_SpotMemcache extends Dao_HomeStay_Spot{

    public function get_t_room_price() {
        $key = md5("get_t_room_price");
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();

        $value = $memcache->get($key);
        if (!$value) {
            $time = 24 * 60 * 60;
            $value = parent::get_t_room_price();
            $memcache->add($key, $value, 0, $time); 
        }
        return $value;
    }

    public function get_t_room_model() {
        $key = md5("get_t_room_model");
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();

        $value = $memcache->get($key);
        if (!$value) {
            $time = 24 * 60 * 60;
            $value = parent::get_t_room_model();
            $memcache->add($key, $value, 0, $time); 
        }
        return $value;
    }

    public function t_loc_poi_all($status) {

        $key = md5("t_loc_poi_all" . $status);
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();

        $value = $memcache->get($key);
        if (!$value) {
            $time = 24 * 60 * 60;
            $value = parent::t_loc_poi_all($status);
            $memcache->add($key, $value, 0, $time); 
        }
        return $value;
    }

    public function t_loc_type_all($status) {

        $key = md5("t_loc_type_all" . $status);
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();

        $value = $memcache->get($key);
        if (!$value) {
            $time = 24 * 60 * 60;
            $value = parent::t_loc_type_all($status);
            $memcache->add($key, $value, 0, $time); 
        }
        return $value;
    }
}
