<?php
apf_require_class("Dao_HomeStay_Stay");
apf_require_class("Util_MemCacheKey");
apf_require_class("Util_MemCacheTime");
apf_require_class("APF_Cache_Factory");

class Dao_Homestay_StayMemcache extends Dao_HomeStay_Stay{
    public function get_stay_by_loc_typecode($tcode) {
        $key = Util_MemCacheKey::get_stay_by_loc_typecode(md5($tcode));
        $functionName = 'get_stay_by_loc_typecode';
        APF::get_instance()->benchmark_begin(__CLASS__." $functionName");

        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $value = $memcache->get($key);
        if (!$value) {
            $time = Util_MemCacheTime::get_one_hour();
            $value = parent::get_stay_by_loc_typecode($tcode);
            $memcache->add($key, $value, 0, $time); 
        }
        APF::get_instance()->benchmark_end(__CLASS__." $functionName");
        return $value;
    }

     public function get_stayinfo_by_ids($uids) {
        $key = Util_MemCacheKey::get_stayinfo_by_ids($uids);
        $functionName = 'get_stayinfo_by_ids';
        APF::get_instance()->benchmark_begin(__CLASS__." $functionName");
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $value = $memcache->get($key); 
        if (!$value) {
            $time = Util_MemCacheTime::get_one_hour();
            $value = parent::get_stayinfo_by_ids($uids);
            $memcache->add($key, $value, 0, $time);
        }
        APF::get_instance()->benchmark_end(__CLASS__." $functionName");
        return $value;
    }

    public function get_staylist_eleven() {
        return  parent::get_staylist_eleven();
     }

	public function get_weibo_column() {
		$key = md5("t_weibo_poi_tw_column");
		$memcache = APF_Cache_Factory::get_instance()->get_memcache();
		$value = $memcache->get($key); 

		if (!$value) {
//			$time = Util_MemCacheTime::get_one_hour();
			$time = "60*60*24";
			$value = parent::get_weibo_column();
			$memcache->add($key, $value, 0, $time);
		}

		return $value;
	}

}
