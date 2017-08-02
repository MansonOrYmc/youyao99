<?php

class Bll_Room_Package {

	private $packagedao;

	public function __construct() {
		$this->packagedao    = new Dao_Room_package();
	}

	public function get_package_bynids($nids) {
		if(!is_array($nids)) {
			$nids = array($nids);
		}
		return $this->packagedao->get_package_bynids($nids);
	}

	public function get_package_byids($ids) {
		if(!is_array($ids)) {
			$ids = array($ids);
		}
		return $this->packagedao->get_package_byids($ids);
	}

}
