<?php
apf_require_class("Dao_Coupons_CouponsInfo");
apf_require_class("APF_Cache_Factory");

class Dao_Coupons_CouponsInfoMemcache extends Dao_Coupons_CouponsInfo{

    public function get_coupon_category() {
        $key = md5("coupon_category");
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $value = $memcache->get($key);

        if (empty($value)) {
            $time = "60*60*24";
            $value = parent::get_coupon_category();
            $memcache->add($key, $value, 0, $time);
        }

        return $value;
    }

    public function get_coupon_category_byid($id) {
        $key = md5("coupon_category_".$id);
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $value = $memcache->get($key);

        if (empty($value)) {
            $time = "60*60*24";
            $value = parent::get_coupon_category_byid($id);
            $memcache->add($key, $value, 0, $time);
        }

        return $value;
    }

        
}
