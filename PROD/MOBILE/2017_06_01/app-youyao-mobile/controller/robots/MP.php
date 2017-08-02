<?php
apf_require_class("APF_Controller");

class Robots_MPController extends APF_Controller {
    public function handle_request() {
        echo file_get_contents(APP_PATH.'MP_verify_D4Q9Fc6rr3FNqU8t.txt');
    }
}
