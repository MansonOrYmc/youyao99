<?php
apf_require_class("APF_Controller");

class Test_ApiTestController extends APF_Controller{
  
  public function handle_request() {
    $apf = APF::get_instance();
    $req = APF::get_instance()->get_request();
    $params = $req->get_parameters();    

    $orderId = 0;
    $urlParts = array();
    $rm = $req->get_router_matches();
    if (!empty($rm) && count($rm) > 1) {
      $urlParts = explode('/', $rm[1]);
      $orderId = (int)$urlParts[0];
    }

    $result = array("status" => "1", "msg" => "bad parameter", "urlParts" => $urlParts);
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
  }
}
