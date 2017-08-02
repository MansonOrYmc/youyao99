<?php
require_once APP_PATH.'../classes/lib/Baidu-Push-Server-SDK-Php-3.0.1/sdk.php';

class Mobile_Mpush{

    public static $controller = array(
        'guest_order' => array(
            'android' => 'com.zizaike.taiwanlodge.order.OrderDetail_Activity',
            'ios'     => 'OrderDetailViewController'
        ),
        'admin_order' => array(
            'android' => 'com.zizaike.taiwanlodge.host.ui.AdminOrderDetail_Activity',
            'ios'     => 'AdminOrderDetailViewController'
        ),
        'homestay_recomend' => array(
            'android' => 'com.zizaike.taiwanlodge.home.HomeStayDetailNew_Activity',
            'ios'     => 'RoomListViewController'
        ),
        'admin_psms' => array(
            'android' => 'com.zizaike.taiwanlodge.hoster.ui.activity.AdminHost_Activity',
            'ios'     => 'AdminMsgListViewController'
        ),
        'guest_psms' => array(
            'android' => 'com.zizaike.taiwanlodge.message.MyMessageActivity',
            'ios'     => 'MyMessagesViewController'
        ),
        'webview' => array(
            'android' => 'com.zizaike.taiwanlodge.WebViewAcivity',
            'ios'     => 'WebViewController'
        ),
        'msg_detail' => array(
            'android' => 'com.zizaike.taiwanlodge.mine.MsgDetailActivity',
            'ios'     => 'MyNoticeDetailViewController',
        ),
    );


    public function run(){
        echo "start\n";
        $bll_push  = new Bll_Push_PushInfo();
        $queue_list = $bll_push->get_queue_list();

        /*
        $queue_list = array(
            '0' => array('token' => '4109358292233415863', 'os'=>'android','title' => '你好，你好'),
            "1" => array('token' => 'APA91bEpg43Hnj2Ny9BeDcF7wnVde1kc81chmqiQY1cW339uHKdxvp8qMpqS51FJ_KQR4YYGnqPuuN8lxKtQC0OHvhSo2yaCdvkCI-TPhCu_uA4ROThMsg9CHos1pLA6G7wOkwsDSaX8', 'os'=>'android', 'title' => 'gcm,你好，你好'),
        );

        $queue_list = array(
            "1" => array('id'=>1,'token' => '071d1421e22', 'os'=>'ios','mtype'=>2,'ext'=>'{"pvalue":"1867396303"}', 'title' => 'jpush,你好，你好'),
        );
        */

        foreach($queue_list as $key=>$value){
            if($value['os']=='ios'){
                $extra = $this->get_push_params($value);
                $res = $this->apple_push($value,$extra);
                //var_dump($res);
            }elseif($value['os']=='android' && strlen($value['token'])>70){   //android  google
                $extra = $this->get_push_params($value);
                $this->google_push($value['token'],$value['title'],$extra);
            }elseif($value['os']=='android' && !empty($value['token'])){    //android baidu
                $extra = $this->get_push_params($value);
                $this->baidu_push($value['token'],$value['title'],$extra);
            }
            $bll_push->update_queue_status($value['id']);
            echo $value['id']." ".$value['token']."\n";
        }
        echo "end\n";

    }

    public function baidu_push($token,$title,$ext=array()){
        $sdk = new PushSDK();
        $channelId = $token;
        $message = array (
            'title' => '【自在客】',
            'description' => $title,
            'custom_content'=>array('behavior'=>$ext)
        );
        $opts = array (
            'msg_type' => 1
        );
        $rs = $sdk -> pushMsgToSingleDevice($channelId, $message, $opts);
        if($rs === false){
            print_r($sdk->getLastErrorCode());
            print_r($sdk->getLastErrorMsg());
        }else{
            print_r($rs);
        }

        return true;
    }

    public function google_push($token, $title, $ext = array())
    {
        $fields = array(
            'registration_ids' => array($token),
            'data' => array(
            "message" => $title,
            'behavior' => json_encode($ext))
        );
       // return Util_Curl::post('http://106.186.117.229/index.php', json_encode($fields));
        $header[] = 'Authorization: key =AIzaSyB7wB6O-Mljof8GL1KgLVt9IXa5jg2WEJY';
        $ch = curl_init('http://106.186.117.229/index.php');
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('data' => json_encode($fields))));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        $result = curl_exec($ch);
        curl_close($ch);

    }

    public function jpush_push($token,$title,$ext=array()){
        return Util_Jpush::send_message(array($token),$title,$ext);
    }

    public function apple_push($value,$ext=array()){
        if(strlen($value['token'])!=64){
           $result = $this->jpush_push($value['token'],$value['title'],$ext);
        }else{
           //use old appstore push  never
        }

        return $result;
    }


    public function guest_order_push($order_id,$os){

        if($os=='android'){
            $jump = array(
                'type' => 'guest_order',
                'android' => array(
                    'target' => self::$controller['guest_order']['android'],
                    'bundle' => array(
                        'order_id' => $order_id
                    )

                )
            );
        }else{
            $jump = array(
                'type' => 'guest_order',
                'ios' => array(
                    'target' => self::$controller['guest_order']['ios'],
                    'storyboard' => 1,
                    'bundle' => array(
                        'oid' => $order_id
                    )
                ),
            );
        }

        return $jump;
    }


    public function admin_order_push($order_id,$os){

        if($os=='android'){
            $jump = array(
                'type' => 'admin_order',
                'android' => array(
                    'target' => self::$controller['admin_order']['android'],
                    'bundle' => array(
                        'order_id' => $order_id
                    )
                ));
        }else{
            $jump = array(
                'type' => 'admin_order',
                'ios' => array(
                    'target' => self::$controller['admin_order']['ios'],
                    'storyboard' => 1,
                    'bundle' => array(
                        'oid' => $order_id
                    )
                ));
        }

        return $jump;

    }

    public function homestay_recomend_push($homestay_uid,$os){

        if($os=='android'){
            $jump = array(
                'type' => 'homestay',
                'android' => array(
                    'target' =>  self::$controller['homestay_recomend']['android'],
                    'bundle' => array(
                        'homestay_uid' => $homestay_uid
                    )

                )
            );
        }else{
            $jump = array(
                'type' => 'homestay',
                'ios' => array(
                    'target' => self::$controller['homestay_recomend']['ios'],
                    'storyboard' => 0,
                    'bundle' => array(
                        'homestayUid' => $homestay_uid
                    )
                )
            );
        }

        return $jump;
    }

    public function admin_psms_push($pvalue,$os){
        if($os=='android'){
            $jump = array(
                'type' => 'admin_psms',
                'android' => array(
                    'target' => self::$controller['admin_psms']['android'],
                    'bundle' => array(
                        'TABINDEX'=>1
                    )
                ));
        }else{
            $jump = array(
                'type' => 'admin_psms',
                'ios' => array(
                    'target' => self::$controller['admin_psms']['ios'],
                    'storyboard' => 1,
                    'bundle' => array()
                ));
        }

        return $jump;

    }

    public function guest_psms_push($pvalue,$os){
        $type='guest_psms';
        if($os=='android'){
            $jump = array(
                'type' => $type,
                'android' => array(
                    'target' => self::$controller[$type]['android'],
                    'bundle' => array()
                ));
        }else{
            $jump = array(
                'type' => $type,
                'ios' => array(
                    'target' => self::$controller[$type]['ios'],
                    'storyboard' => 1,
                    'bundle' => array()
                ));
        }

        return $jump;

    }
    public function webview_push($os,$url){
        if($os=='android'){
            $jump = array(
                'type' => 'webview',
                'android' => array(
                    'target' => self::$controller['webview']['android'],
                    'bundle' => array(
                        'url' => $url,
                    )
                ));
        }else{
            $jump = array(
                'type' => 'webview',
                'ios' => array(
                    'target' => self::$controller['webview']['ios'],
                    'storyboard' => 1,
                    'bundle' => array(
                        'url' => $url,
                    )
                ));
        }

        return $jump;
    }

    public function msg_detail_push($pvalue, $os) {
        if($os=='android'){
            $jump = array(
                'type' => 'msg_detail',
                'android' => array(
                    'target' => self::$controller['msg_detail']['android'],
                    'bundle' => array(
                        'msg_id' => $pvalue,
                    )
                ));
        }else{
            $jump = array(
                'type' => 'msg_detail',
                'ios' => array(
                    'target' => self::$controller['msg_detail']['ios'],
                    'storyboard' => 0,
                    'bundle' => array(
                        'message_id' => $pvalue,
                    )
                ));
        }

        return $jump;
    }


    public function get_push_params($queue_item){
        $param = array();
        if(Util_Notify::get_mtype_ref($queue_item['mtype'])=='guest_order'){
            $ext = $queue_item['ext'];
            $ext = json_decode($ext,true);
            $param =  $this->guest_order_push($ext['pvalue'],$queue_item['os']);
        }elseif(Util_Notify::get_mtype_ref($queue_item['mtype'])=='admin_order'){
            $ext = $queue_item['ext'];
            $ext = json_decode($ext,true);
            $param =  $this->admin_order_push($ext['pvalue'],$queue_item['os']);
        }elseif(Util_Notify::get_mtype_ref($queue_item['mtype'])=='homestay'){
            $ext = $queue_item['ext'];
            $ext = json_decode($ext,true);
            $param =  $this->homestay_recomend_push($ext['pvalue'],$queue_item['os']);
        }elseif(Util_Notify::get_mtype_ref($queue_item['mtype'])=='guest_psms'){
            $ext = $queue_item['ext'];
            $ext = json_decode($ext,true);
            $param =  $this->guest_psms_push($ext['pvalue'],$queue_item['os']);
        }elseif(Util_Notify::get_mtype_ref($queue_item['mtype'])=='admin_psms'){
            $ext = $queue_item['ext'];
            $ext = json_decode($ext,true);
            $param =  $this->admin_psms_push($ext['pvalue'],$queue_item['os']);
        }elseif(Util_Notify::get_mtype_ref($queue_item['mtype'])=='msg_detail'){
            $ext = $queue_item['ext'];
            $ext = json_decode($ext,true);
            $param =  $this->msg_detail_push($ext['pvalue'],$queue_item['os']);
        }

        return $param;
    }

}
