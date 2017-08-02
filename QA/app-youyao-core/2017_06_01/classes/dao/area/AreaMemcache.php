<?php
class Dao_Area_AreaMemcache extends Dao_Area_Area{

    public function get_area_by_destid($destid) {
        //TODO touch端热点调用
        $key = Util_MemCacheKey::get_area_by_destid($destid);
        $functionName = 'get_area_by_destid';
        APF::get_instance()->benchmark_begin(__CLASS__." $functionName");

        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $value = $memcache->get($key);
        if (!$value) {
            $time = Util_MemCacheTime::get_one_day();
            $value = parent::get_area_by_destid($destid);
            $memcache->add($key, $value, 0, $time);
        }
        APF::get_instance()->benchmark_end(__CLASS__." $functionName");
        return $value;
    }
	
	public function get_city_list(){
		$key = 'destinationInfo';
		$mc = APF_Cache_Factory::get_instance()->get_memcache();
		$value = $mc->get($key);
		if(!$value) {
			$time = Util_MemCacheTime::get_one_day();
			$value = parent::get_city_list();
			$mc->set($key, $value, 0, $time);
		}
		return $value;
	}
    /**
     * @return array
     * 获取当前目的地的城市列表
     */
    public function get_t_loc_type($destid){
        $key = 'get_t_loc_type_'.$destid;
        $mc = APF_Cache_Factory::get_instance()->get_memcache();
        $value = $mc->get($key);
        if(!$value) {
            $time = Util_MemCacheTime::get_one_day();
            $value = parent::get_t_loc_type($destid);
            $mc->set($key, $value, 0, $time);
        }
        return $value;
    }

	public function get_dest_config($destid)  {
		$key = "get_desnt_config_".$destid;
        $mc = APF_Cache_Factory::get_instance()->get_memcache();
		$value = $mc->get($key);
		if(!$value) {
			$time = 3600;
			$value = parent::get_dest_config($destid);
			$mc->set($key, $value, 0, $time);
		}

		return $value;
	}
}
