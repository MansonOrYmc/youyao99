<?php
apf_require_class('APF_Controller');

class Error_Http404Controller extends APF_Controller{
	public function handle_request(){
		header('Content-Type:application/json');
		echo json_encode(array('error'=>'404 NOT FOUND'));
                exit;
	}
}
