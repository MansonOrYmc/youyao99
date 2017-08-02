<?php 
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Solr_Update{
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
            echo " [x] Received ", $msg->body, "\n";
            $data = json_decode($msg->body, true);

            $solr_server = APF::get_instance()->get_config("solr_job_server");
            $user_name   = APF::get_instance()->get_config("solr_job_server_username");
            $solr_dir    = APF::get_instance()->get_config("solr_job_dir");
            $dsh_path    = APF::get_instance()->get_config("dsh_path");

            if(!$solr_server || !$solr_dir) return;
    
            if($data['type'] == 'node'){
                $script_file = "post_room_byuid.php";
            } else {
                $script_file = "post_user_byuid.php";
            }

            if($user_name) $user_name = $user_name . "@";
            if(!$dsh_path) $dsh_path = "dsh";
    
            $shell = "$dsh_path -c -m $user_name$solr_server \"cd $solr_dir; php solr_jobs/$script_file {$data['uid']} \" > /dev/null 2>&1 &";
            print_r($shell);
            echo "\n";
    
            exec($shell, $output, $exit);

            sleep(1);

        };
        
        /* QUEUE CONSUMER_TAG NO_LOCAL NO_ACK EXCLUSIVE NOWAIT CALLBAK */
        $channel->basic_consume('commodity_solr_queue', '', false, true, false, false, $callback);


        $shutdown = function($channel, $connection) {
            $channel->close();
            $connection->close();
        };

        register_shutdown_function($shutdown, $channel, $connection);

        $time = time();
        while( count($channel->callbacks) && time() - $time < 50 ) { //防止php假死
            $channel->wait(null, false, 50);
        }
	}
}
