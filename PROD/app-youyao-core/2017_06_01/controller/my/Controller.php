<?php
apf_require_class('APF_Controller');

class My_Controller extends APF_Controller {

	protected $action;

	public function handle_request() {
		$apf = APF::get_instance();
		$controller = $apf->get_current_controller();
		$matches = $apf->get_request()->get_router_matches();
		$path = trim($matches[1], " \t\n\r\0\x0B/");
		if (empty($path)) {
			$this->action = 'index';
			$url_params = array();
		}
		else {
			$url_params = explode('/', $path);
			$this->action = array_shift($url_params);
		}

		if ($this->action != 'init' && method_exists($controller, $this->action)) {
			$result = call_user_func_array(array($controller, 'init'), array());
			if (is_array($result)) {
				return $result;
			}
			else {
				return call_user_func_array(array( $controller, $this->action ), $url_params);
			}
		}
		else {
			header('Location:/');
			return FALSE;
		}
	}
}
