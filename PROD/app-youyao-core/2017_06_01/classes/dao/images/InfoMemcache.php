<?php
apf_require_class("Dao_Images_Info");
apf_require_class("Util_MemCacheKey");
apf_require_class("Util_MemCacheTime");
apf_require_class("APF_Cache_Factory");

class Dao_Images_InfoMemcache extends Dao_Images_Info {
	public function get_multi_file_managed($fid) {
		$key = md5("get_multi_file_managed".json_encode($fid));

		$memcache = APF_Cache_Factory::get_instance()->get_memcache();
		$value = $memcache->get($key);
		if(!$value) {
			$time = 60*60*24;
			$value = parent::get_multi_file_managed($fid);
			$memcache->set($key, $value, 0, $time);
		}

		return $value;
	}

	public function get_multi_t_img_managed($fid) {
		$key = md5("get_multi_t_img_managed".json_encode($fid));

		$memcache = APF_Cache_Factory::get_instance()->get_memcache();
		$value = $memcache->get($key);
		if(!$value) {
			$time = 60*60*24;
			$value = parent::get_multi_t_img_managed($fid);
			$memcache->set($key, $value, 0, $time);
		}

		return $value;
	}

}
