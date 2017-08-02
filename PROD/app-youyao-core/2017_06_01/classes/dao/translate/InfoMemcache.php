<?php
apf_require_class("Dao_Translate_Info");
apf_require_class("APF_Cache_Factory");

class Dao_Translate_InfoMemcache extends Dao_Translate_Info {

	public function get_trans_by_key($key, $dest_id) {
        $mem_key = md5("Trans_$key");
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $trans_list = $memcache->get($mem_key);
        $value = $trans_list[$dest_id];

        if (empty($value)) {
            $time = 60*60*24*30;
            $value = parent::get_trans_by_key($key, $dest_id);
            $trans_list[$dest_id] = $value;
            $memcache->set($mem_key, $trans_list, 0, $time);
        }

        return $value;

	}

    public function set_trans($key, $str, $dest_id) {

        $mem_key = md5("Trans_$key");
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $trans_list = $memcache->get($mem_key);
        $trans_list[$dest_id] = null;

        parent::set_trans($key, $str, $dest_id);
        $time = 60*60*24*30;
        $memcache->set($mem_key, $trans_list, 0, $time);

        return true;

    }

/*
    public function get_trans_by_multikey($key_list, $dest_id) {
    }

	public function get_key_by_str($str, $dest_id) {
	}
*/

}
