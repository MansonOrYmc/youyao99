<?php 
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Message_Getuid{

    public function run(){
        $host = APF::get_instance()->get_config('rabbitmq_host');
        $port = APF::get_instance()->get_config('rabbitmq_port') ? APF::get_instance()->get_config('rabbitmq_port') : "5672" ;
        $vhost = APF::get_instance()->get_config('rabbitmq_vhost') ? APF::get_instance()->get_config('rabbitmq_vhost') : "/open";

        /* HOST PORT USER PASS VHOST */
        $connection = new AMQPStreamConnection($host, $port, 'open.api', 'open.api', $vhost);
        $channel = $connection->channel();

        /* NAME PASSIVE DURABLE WXCLUSIVE AUTO DELETE */
//        $channel->queue_declare('commodity_solrHomestay_queue', false, false, false, false);

        echo date('Y-m-d H:i:s');
        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

        $callback = function($msg) {
            $msg_bll = new Bll_User_Msg();
            echo " [x] Received ", substr($msg->body, 0, 50), "\n";
            $data = json_decode($msg->body, true);

            $message_id = $data['message_id'];
            $to_uid = $data['to_uid'];
            $type = $data['type'];

            // 获得用户uid
            if($type == 'send_to_all') {

                $message_info = $msg_bll->get_message_by_id($message_id);
                $dest_ids = explode(",", $message_info['dest_id']);
                if($message_info['user_type'] == 1) {
                    $type = "homestay";
                }
                if($message_info['user_type'] == 2) {
                    $type = "customer";
                }
                if($message_info['user_type'] === 0) {
                    $type = "all";
                }

                $msg = new MsgQueue();
                $rk = "message.send.byuid";
                $exchange = "market_exchange";
                $limit = 1000;
                $offset = 0;
                do{
                    unset($uids);
                    unset($data);
                    $uids = null;
                    $data = null;
                    echo " Send to MQ from {$offset} limit {$limit} \n";
                    $uids = $msg_bll->get_uids_list_bytype($type, $dest_ids, $offset, $limit);
                    //$msg_bll->send_marketing_msg_by_sid($uids, $message_id);
                    if(!empty($uids)) {
                        $data = json_encode(array('message_id' => $message_id, 'type' => 'send_to_uid', 'to_uid'=>$uids));
                        $msg->sender($data, $rk, $exchange);
                    }
                    $offset+=1000;
                } while(!empty($uids));

            }
            // undefind type
            else {
                echo "undefind type \n";
            }

        };
        
        /* QUEUE CONSUMER_TAG NO_LOCAL NO_ACK EXCLUSIVE NOWAIT CALLBAK */
        $channel->basic_consume('message_getuid_queue', '', false, true, false, false, $callback);


        $shutdown = function($channel, $connection) {
            $channel->close();
            $connection->close();
        };

        register_shutdown_function($shutdown, $channel, $connection);

        $time = time();
        while( count($channel->callbacks)) { //防止php假死
            $channel->wait(null, false, 10);
        }
    }
}
