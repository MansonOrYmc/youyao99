<?php
apf_require_class("Dao_Field_Info");
//apf_require_class("Util_MemCacheKey");
//apf_require_class("Util_MemCacheTime");
apf_require_class("APF_Cache_Factory");

class Dao_Field_InfoMemcache extends Dao_Field_Info{
    public function get_node_field_by_nids($tables, $nid) {
        $key = md5(serialize($tables).'field_data_'.serialize($nid));

        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        //$value = $memcache->get($key);
        if (!$value) {
            $time = 60*60;
            $value = parent::get_node_field_by_nids($tables, $nid);

            $memcache->set($key, $value, 0, $time);
        }
        return $value;
    }

	public function get_field_config($field_names) {
		$key = md5("field_config_".serialize($field_names));
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
		$value = $memcache->get($key);

		if(!$value) {
			$time = 60*60;
			$value = parent::get_field_config($field_names);
			$memcache->set($key, $value, 0, $time);
		}

		return $value;
	}

	public function get_field_config_instance($type, $bundle) {
		$key = md5("field_config_instance_".$type.$bundle);

        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
		$value = $memcache->get($key);
        if (!$value) {
            $time = 60*60*24;
            $value = parent::get_field_config_instance($type, $bundle);

            $memcache->set($key, $value, 0, $time);
        }
        return $value;
	}

	public function get_taxonomy_term_data($tid, $vid) {
		$key = md5("taxonomy_term_data_".$tid.$vid);
		$memcache = APF_Cache_Factory::get_instance()->get_memcache();
		$value = $memcache->get($key);
		if (!$value) {
			$time = 60*60*24;
			$value = parent::get_taxonomy_term_data($tid, $vid);

			$memcache->set($key, $value, 0, $time);
		}
		return $value;
	}


}
