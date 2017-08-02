<?php
apf_require_class("Dao_User_UserInfo");
apf_require_class("Util_MemCacheKey");
apf_require_class("Util_MemCacheTime");
apf_require_class("APF_Cache_Factory");

class Dao_User_UserInfoMemcache extends Dao_User_UserInfo{
	public function get_users_column() {
		$key = md5("drupal_users_column");
		$memcache = APF_Cache_Factory::get_instance()->get_memcache();
		$value = $memcache->get($key);
		
		if (empty($value)) {
//			$time = Util_MemCacheTime::get_one_hour();
			$time = 60*60*24;
			$value = parent::get_users_column();
			$memcache->add($key, $value, 0, $time);
		}
		
		return $value;
	}

    public function get_all_uid_list($offset, $limit, $dest_ids) {
        $key = md5("all_uid_list_{$offset}_{$limit}_".json_encode($dest_ids));
		$memcache = APF_Cache_Factory::get_instance()->get_memcache();
		$value = $memcache->get($key);
		
		if (empty($value)) {
			$time = 60*60*24;
			$value = parent::get_all_uid_list($offset, $limit, $dest_ids);
			$memcache->set($key, $value, 0, $time);
		}
		
		return $value;
    }

    public function get_homestay_uid_list($offset, $limit, $dest_ids) {
        $key = md5("homestay_uid_list_{$offset}_{$limit}_".json_encode($dest_ids));
		$memcache = APF_Cache_Factory::get_instance()->get_memcache();
		$value = $memcache->get($key);
		
		if (empty($value)) {
			$time = 60*60*24;
			$value = parent::get_homestay_uid_list($offset, $limit, $dest_ids);
			$memcache->set($key, $value, 0, $time);
		}
		
		return $value;
	}

    public function get_customer_uid_list($offset, $limit, $dest_ids) {
        $key = md5("customer_uid_list_{$offset}_{$limit}_".json_encode($dest_ids));
		$memcache = APF_Cache_Factory::get_instance()->get_memcache();
		$value = $memcache->get($key);
		
		if (empty($value)) {
			$time = 60*60*24;
			$value = parent::get_customer_uid_list($offset, $limit, $dest_ids);
			$memcache->set($key, $value, 0, $time);
		}
		
		return $value;
	}
    public function verify_user_access_token($yyid, $token) {
                $key = md5("verify_user_access_token_{$yyid}_{$token}");
		$memcache = APF_Cache_Factory::get_instance()->get_memcache();
		$value = $memcache->get($key);
		
		if (empty($value)) {
			$time = 60*60*24;
			$value = parent::verify_user_access_token($yyid, $token);
			$memcache->set($key, $value, 0, $time);
		}
		
		return $value;
    }

}
