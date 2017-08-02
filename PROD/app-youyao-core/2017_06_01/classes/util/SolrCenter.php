<?php

require_once  dirname(__FILE__).'/../Solr/Service.php';

class Util_SolrCenter {

	public static function zzk_get_tw_room_se_service() {
		$solr = new Apache_Solr_Service(APF::get_instance()->get_config('solr_host'), APF::get_instance()->get_config('solr_port'), '/search/room/');
		return $solr;
	}

	public static function zzk_get_tw_user_se_service() {
		$solr = new Apache_Solr_Service(APF::get_instance()->get_config('solr_host'), APF::get_instance()->get_config('solr_port'), '/search/user/');
		return $solr;
	}
    
}

?>
