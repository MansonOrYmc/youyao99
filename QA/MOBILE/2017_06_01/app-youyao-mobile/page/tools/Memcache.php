<?php
apf_require_page("Rent");
apf_require_class('PageHelper');

class Tools_MemcachePage extends RentPage {

    public static function use_javascripts() {
        return array_merge(parent::use_javascripts());
    }

	public function get_title() {
        return "memcache清除-租房";
    }

    public function get_view() {
    	$this->assign_data("input_name",APF::get_instance ()->get_request()->get_attribute ( "input_name"));
    	$this->assign_data("input_value",APF::get_instance ()->get_request()->get_attribute ( "input_value"));
    	$this->assign_data("act_name",APF::get_instance ()->get_request()->get_attribute ( "act_name"));
    	$this->assign_data("findcache",APF::get_instance ()->get_request()->get_attribute ( "findcache"));
        $this->assign_data("deletecache",APF::get_instance ()->get_request()->get_attribute ( "deletecache"));
    	$this->assign_data("memcache_group",APF::get_instance ()->get_request()->get_attribute ( "memcache_group"));
    	return "Memcache";
    }

}
?>