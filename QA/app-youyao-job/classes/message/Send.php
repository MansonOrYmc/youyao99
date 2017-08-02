<?php 
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Message_Send{

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

            // 通过uid发送
            if($type == 'send_to_uid') {
                echo " Start Send \n";
                try{
                    $notify_number = count($to_uid);
                    if($notify_number > 0){
                        $msg_bll->send_marketing_msg_by_sid($to_uid, $message_id);
                        $msg_bll->add_msg_notify($message_id, $notify_number);
                    }
                }catch(Exception $e) {
                    echo "send failed \n";
                    APF_DB_Factory::get_instance()->close_pdo_all();
                    print_r($e->getMessage());
                    echo "\n";

                    $msg = new MsgQueue();
                    $rk = "message.send.byuid";
                    $exchange = "market_exchange";
                    if(!empty($to_uid) && !$data['retry']) { // 重试一次
                        $data = json_encode(array('message_id' => $message_id, 'type' => 'send_to_uid', 'to_uid'=>$uid, 'retry' => 1));
                        $msg->sender($data, $rk, $exchange);
                    }
                }
                usleep(500000);
            }
            // undefind type
            else {
                echo "undefind type \n";
            }

        };
        
        /* QUEUE CONSUMER_TAG NO_LOCAL NO_ACK EXCLUSIVE NOWAIT CALLBAK */
        $channel->basic_consume('message_send_queue', '', false, true, false, false, $callback);


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
