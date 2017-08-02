<?php
apf_require_class("APF_Cache_Factory");

class Dao_Comment_CommentMemcache extends Dao_Comment_CommentInfo {

    public function acquire_comment_summary_by_homeID($home_id) {
		$memcache = APF_Cache_Factory::get_instance()->get_memcache();
		$key = 'homestay_comment_summary_'. $home_id;
		$value = $memcache->get($key);
		if(!$value) {
			$time = 86400;
			$value = parent::acquire_comment_summary_by_homeID($home_id);
			$memcache->add($key, $value, 0, $time);
		}
		return $value;
    }
}
