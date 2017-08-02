<?php
apf_require_class("Dao_Room_RoomInfo");
apf_require_class("Util_MemCacheKey");
apf_require_class("Util_MemCacheTime");
apf_require_class("APF_Cache_Factory");

class Dao_Room_RoomInfoMemcache extends Dao_Room_RoomInfo{
    public function get_roominfo_by_uids($arruids,$status=1) {
        $key = Util_MemCacheKey::get_roominfo_by_uids(md5(implode(',',$arruids)));
        $functionName = 'get_roominfo_by_uids';
        APF::get_instance()->benchmark_begin(__CLASS__." $functionName");

        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $value = $memcache->get($key);
        if (!$value) {
            $time = Util_MemCacheTime::get_one_hour();
            $value = parent::get_roominfo_by_uids($arruids);

            $memcache->add($key, $value, 0, $time);
        }
        APF::get_instance()->benchmark_end(__CLASS__." $functionName");
        return $value;
    }

    public function get_roomstatus_by_uids($arruids,$startdate,$enddate) {
        $key = Util_MemCacheKey::get_roominfo_by_uids(md5(implode(',',$arruids)).$startdate.$enddate);
        $functionName = 'get_roomstatus_by_uids';
        APF::get_instance()->benchmark_begin(__CLASS__." $functionName");

        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $value = $memcache->get($key);
        if (!$value) {
            $time = Util_MemCacheTime::get_one_hour();
            $value = parent::get_roomstatus_by_uids($arruids,$startdate,$enddate); 
            $memcache->add($key, $value, 0, $time);
        }
        APF::get_instance()->benchmark_end(__CLASS__." $functionName");
        return $value;
    }
}