<?php
class MsgQueue {
    public $cnn;
    public function __construct(){

        if($this->cnn) {
            return;
        }
        $host = APF::get_instance()->get_config('rabbitmq_host');
        $port = APF::get_instance()->get_config('rabbitmq_port') ? APF::get_instance()->get_config('rabbitmq_port') : "5672" ;
        $vhost = APF::get_instance()->get_config('rabbitmq_vhost') ? APF::get_instance()->get_config('rabbitmq_vhost') : "/open";
        $cnn = new AMQPConnection(
            array(
                'host'     => $host,
                'port'     => $port,
                'vhost'    => $vhost,
            )
        );

        $cnn->setLogin("open.api");
        $cnn->setPassword("open.api");
        $cnn->connect();

        if(!$cnn->isConnected()) {
            echo "Cannot connect to the broker";
        }

        $this->cnn = $cnn;
    }

    public function sender($text, $rk, $exchange) {

        $ch = new AMQPChannel($this->cnn);
        $ex = new AMQPExchange($ch);
        $ex->setName($exchange);
        $ex->setType("topic");
        $ex->setFlags("2");
        $ex->declare();

//        $q = new AMQPQueue($ch);
//        $q->declare("open_hotel_queue");

//        $q->bind($exchange, $rk);

        $msg = $ex->publish($text, $rk);
        if (!$msg){
            echo "error";
        }
//        echo 'Sended '.$msg."\n";

    }

    public function __destruct() {
        if(!$this->cnn->disconnect()) {
            echo "Could not disconnect";
        }
    }

}
