<?php
class Util_New_Solr {
    //为了解决youyao的solr构建等一系列问题,建了这个类

    public static function get_homestay_info_from_solr($uid){
        $solr_url = "http://".APF::get_instance()->get_config('solr_host').":".APF::get_instance()->get_config('solr_port')."/search/user/select?";
        $solr_url .= "q=id:$uid&wt=json";
        $result = Util_Common::curl_get($solr_url);
        $result = json_decode($result,true);
        $result = $result['response']['docs'][0];
        unset($result['_version_']);
        return $result;
    }

    public static function update_homestay_info_to_solr($uid,$homestay){
        $solr_url = "http://".APF::get_instance()->get_config('solr_host').":".APF::get_instance()->get_config('solr_port')."/search/user/update";
        $result = self::get_homestay_info_from_solr($uid);
        if(is_array($homestay)){
            foreach($homestay as $key=>$value){
                if(isset($result[$key])){
                    $result[$key] = $value;
                }
            }
            $json = json_encode(array($result),true);
            $r = Util_Common::curl_json_post($solr_url,$json);
            Util_Common::curl_xml_post($solr_url, "<commit/>");
            return $r;
        }
        return false;
    }

    public static function get_room_info_from_solr($rid){
        $solr_url = "http://".APF::get_instance()->get_config('solr_host').":".APF::get_instance()->get_config('solr_port')."/search/room/select?";
        $solr_url .= "q=id:$rid&wt=json";
        $result = Util_Common::curl_get($solr_url);
        $result = json_decode($result,true);
        $result = $result['response']['docs'][0];
        unset($result['_version_']);
        return $result;
    }

    /**
     * @param $rid
     * @param $room
     * @param $admin
     * @throws Exception
     * 用于构建房间信息
     */
    public static function update_room_info_to_solr($rid, $room, $admin){
//        $host = APF::get_instance()->get_config('rabbitmq_host');
//        $port = APF::get_instance()->get_config('rabbitmq_port');
//        $vhost = APF::get_instance()->get_config('rabbitmq_vhost');
//        $login = APF::get_instance()->get_config('rabbitmq_login');
//        $pass = APF::get_instance()->get_config('rabbitmq_pass');
//        $connection = new AMQPConnection(array('host' =>$host, 'port' =>$port, 'vhost' =>$vhost, 'login' =>$login, 'password' => $pass));
//        $connection->connect();
//        $channel = new AMQPChannel($connection);
//        $exchange = new AMQPExchange($channel);
//        $exchange->setName("solr_build");
//        $exchange->setType(AMQP_EX_TYPE_DIRECT);
//        $queue = new AMQPQueue($channel);
//        $queue->setName("solr_room_build_queue");
//        $message = array("rid"=>$rid,'room'=>$room,'admin'=>$admin);
//        $exchange->publish(json_encode($message,true), "route_key_build_room");
//        $connection->disconnect();
        $output = false;
        $log = new Util_New_Logger('build_room_info');
        $log->info("开始构建房间($rid)信息by".$admin);
        $solr_url = "http://".APF::get_instance()->get_config('solr_host').":".APF::get_instance()->get_config('solr_port')."/search/room/update";
        $result = self::get_room_info_from_solr($rid);
        if(is_array($room)){
            foreach($room as $key=>$value){
                if(isset($result[$key]) and ($result[$key] != $value)){
                    $log->info("开始构建房间字段:".$key.var_export($result[$key],true));
                    $result[$key] = $value;
                    $log->info("结束构建房间字段:".$key.var_export($result[$key],true));
                }
            }
            $json = json_encode(array($result),true);
            $r = Util_Common::curl_json_post($solr_url,$json);
            $r1 = Util_Common::curl_xml_post($solr_url, "<commit/>");
            $log->info($r);
            $log->info($r1);
            $output = true;
        }
        $log->info("结束构建房间($rid)信息by".$admin);
        return $output;
    }
}
