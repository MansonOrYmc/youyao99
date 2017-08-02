<?php
apf_require_class("APF_DB_Factory");

class Dao_Mail_Queue {

    private $status_pdo;

    public function __construct() {
        $this->status_pdo = APF_DB_Factory::get_instance()->get_pdo("statsmaster");
    }


    public function get_queue($thread=null){
        $condition = "";
        $limit = 50;
        $pdoValue = array();
        if(is_numeric($thread)) {
            $condition = " and id like ?";
            $pdoValue[] = '%'.$thread;
            $limit = 10;
        }
        $sql = "SELECT * FROM mail_queue  WHERE  status = 0 AND retry < 3 AND create_time > unix_timestamp('2015-12-03 00:00:00') $condition ORDER BY id DESC LIMIT $limit";
        $stmt = $this->status_pdo->prepare($sql);
        $stmt->execute($pdoValue);
        $queues = $stmt->fetchAll();
        $r=array();
        foreach ($queues as $key => $value) {
            $to = $value["to"];
            $subject = $value["subject"];
            $id = $value["id"];
            if(!preg_match('/親愛的用戶开发票/',$subject)&&!preg_match('/赵生/',$subject)&&!preg_match('/@zzkzzk.com/',$to)){
                $r[]=$value;
            }else{
                self::delete_queue($id);
            }
        }
        return $r;
    }

    public function delete_queue($id){
        $sql = "delete from mail_queue where `id` = '$id'";
        $stmt = $this->status_pdo->prepare($sql);
        $stmt->execute();
    }

    public function update_queue_status($ids,$status){

        $sql = "UPDATE mail_queue SET `status` = :status , `update_time` = :update_time WHERE `id` in (".implode(',',$ids).")";
        //exit($sql);

        $stmt = $this->status_pdo->prepare($sql);
        try{$result = $stmt->execute(array('status'=>$status,'update_time'=>time()));}
        catch(Exception $e) {
            var_dump($e);
            }
        return $result;
    }

    public function update_queue_retry($ids){

        $sql = "UPDATE mail_queue SET `retry` = ifNull(`retry`,0)+1 WHERE `id` in (".implode(',',$ids).")";
        //exit($sql);

        $stmt = $this->status_pdo->prepare($sql);
        try{$result = $stmt->execute();}
        catch(Exception $e) {
            var_dump($e);
            }
        return $result;
    }

    public function insert_queue($queue) {
        if (empty($queue['to'])) {
            return FALSE;
        }
        $to_list = explode(',', $queue['to']);
        $body = $queue['body'];
        $type = empty($queue['type'])?0:$queue['type'];
        $subject = $queue['subject'];
        $cc=$queue['cc'];
        $reply=$queue['reply'];
        if (empty($queue['priority'])) {
            $priority = 0;
        }
        else {
            $priority = $queue['priority'];
        }
        foreach ($to_list as $key => $to) {
            $params = array(
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
                'status' => 0,
                'create_time' => time(),
                'priority' => $priority,
                'type' => $type,
                'cc'=>$cc,
                'reply'=>$reply
            );

            $sql = <<<SQL
INSERT INTO mail_queue 
(`to`,`subject`,`body`,`status`,`create_time`,`priority`,`type`,`cc`,`reply`) VALUES
(:to,:subject,:body,:status,:create_time,:priority,:type,:cc,:reply)
SQL;

            $stmt = $this->status_pdo->prepare($sql);
            //exit(var_dump($stmt));
            //try{
            $result = $stmt->execute($params);

            if (!$result) {
                return FALSE;
            }
        }
        return TRUE;


    }

    public function insert_multiple_queue($data) {
        $pdo_val = array();
        $placeholder = array();
        foreach($data as $k=>$v) {
            $placeholder[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $pdo_val = array_merge(
                $pdo_val, 
                array_values(
                    array_merge(
                        array(
                            'to'          => null,
                            'subject'     => null,
                            'body'        => null,
                            'status'      => 0,
                            'create_time' => time(),
                            'priority'    => 0,
                            'type'        => 0,
                            'cc'          => null,
                            'reply'       => null,
                        ),
                        $v
                    )
                )
            );
        }
        $sql = "insert into mail_queue (`to`,`subject`,`body`,`status`,`create_time`,`priority`,`type`,`cc`,`reply`) values ". implode(", ", $placeholder) ;
        //print_r(count($placeholder));
        //print_r($sql);
        //print_r($pdo_val);
        $stmt = $this->status_pdo->prepare($sql);
        $stmt->execute($pdo_val);
        return TRUE;
    }

}
