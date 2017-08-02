<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 16/3/1
 * Time: 上午11:51
 */
apf_require_class('APF_Controller');

class User_CollectController extends APF_Controller {
    public function handle_request() {
        $result = array();
        $request = APF::get_instance()->get_request();
        $params = $request->get_parameters();
        $uid = $params['uid'] ;
        $multilang=$params['multilang'];
        $multiprice=$params['multiprice'];
        $page= $params['page'];
        if(empty($page)){
            $page = 1;
        }
        if(empty($uid)){
            exit("error");
        }
        $user = new So_SimpleUser($uid);
        $result = $user->getCollectH($page);
        $result = So_NiceClean::clean_Array($result,array('id','uid','hid','type','create_at','remark'));
        foreach($result as &$data){
            if(empty($data['hid'])) continue;
            $hotel = new So_SimpleHotel($data['hid']);
            $service = array();
            $service['title'] = $hotel->getName();
            $service['loction'] = $hotel->getLocation();
            $service['type'] = '民宿';
            $service['address'] = $hotel->getAddress();
            if($multilang == 10){
                $service = Util_ZzkCommon::simple2tradition($service);
            }
            $service['image'] = $hotel->getPhoto();
            $service['sid'] = $hotel->getPid();
            $service['sid'] = $service['sid'].'';
            $data['service'] = $service;
            $data['startprice'] = $hotel->getMinprice();
            $data['startprice'] = Util_Common::zzk_price_convert($data['startprice'],$hotel->getDestId(),$multiprice);
        }
        header('Content-Type:application/json');
        echo(json_encode($result));
    }
}