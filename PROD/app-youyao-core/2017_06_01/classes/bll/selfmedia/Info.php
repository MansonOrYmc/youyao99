<?php

class Bll_Selfmedia_Info {

    private $selfmediadao;
    public function __construct() {
        $this->selfmediadao  = new Dao_Selfmedia_Info();
    }

	public function get_media_byid($id) {
		return $this->selfmediadao->get_media_byid($id);
	}

	public function update_media($params) {
		return $this->selfmediadao->update_media($params);
	}

	public function create_media($params) {
		return $this->selfmediadao->create_media($params);
	}

}
