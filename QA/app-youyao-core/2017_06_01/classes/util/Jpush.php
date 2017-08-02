<?php
class Util_Jpush {

    public static function send_message($register_id,$message,$ext=array()){
        $url = 'https://api.jpush.cn/v3/push';
        $auth = array('user'=>'288229ef4cc6120b47411e07','pass'=>'a58fdc76262084349b6cfed1');
        $header  = array('Content-Type'=>'application/json;charset=utf-8');
        $data    = array('platform'=>'all',
                         'audience'=>array('registration_id'=>$register_id),
                         'options'=>array('apns_production'=>true),
                         'notification'=>array('alert'=>$message,'ios'=>array('sound'=>'sound.caf','badge'=>1,'extras'=>array('behavior'=>$ext)))
        );
        return Util_Curl::post($url,$data,$header,$auth);
    }
}