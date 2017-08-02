<?php
apf_require_class("Dao_Room_Booking");
apf_require_class("Util_MemCacheKey");
apf_require_class("Util_MemCacheTime");
apf_require_class("APF_Cache_Factory");

class Dao_Room_BookingMemcache extends Dao_Room_Booking{
    public function get_roombooking_by_nids($arruids,$startdate,$enddate) {
        $key = Util_MemCacheKey::get_roombooking_by_nids($arruids);
        $functionName = 'get_roombooking_by_nids';
        APF::get_instance()->benchmark_begin(__CLASS__." $functionName");

        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $value = $memcache->get($key);
        if (!$value) {
            $time = Util_MemCacheTime::get_one_hour();
            $value = parent::get_roombooking_by_nids($arruids,$startdate,$enddate); 
            $memcache->add($key, $value, 0, $time);
        }
        APF::get_instance()->benchmark_end(__CLASS__." $functionName");
        return $value;
    }
    
    public function get_roombooking_by_ids($arrayids) {
        $key = Util_MemCacheKey::get_roombooking_by_ids(md5(implode(',',$arrayids)));
        $functionName = 'get_roombooking_by_ids';
        APF::get_instance()->benchmark_begin(__CLASS__." $functionName");

        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $value = $memcache->get($key);
        if (!$value) {
            $time = Util_MemCacheTime::get_one_hour();
            $value = parent::get_roombooking_by_ids($arrayids); 
            $memcache->add($key, $value, 0, $time);
        }
        APF::get_instance()->benchmark_end(__CLASS__." $functionName");
        return $value;
    }
    
    public function get_roombooking_bystatus($status) {
        return  parent::get_roombooking_bystatus($status); ;
    }
}