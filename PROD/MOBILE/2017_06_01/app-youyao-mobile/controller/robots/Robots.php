<?php
apf_require_class("APF_Controller");

class Robots_RobotsController extends APF_Controller {
    public function handle_request() {
        echo file_get_contents(APP_PATH.'robots.txt');
    }
}