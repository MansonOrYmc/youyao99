<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/8/4
 * Time: 下午5:50
 */
class Log_Room {
    private $dao;
    private $nid;
    private $uid;
    private $log_type;
    private $name;
    private $name_conf = array(
        1=>"discount"
    );

    public function __construct($nid,$log_type=1) {
        $roomInfo = new Dao_Room_RoomInfo();
        $uid = $roomInfo->room_detail_contact_order($nid)->uid;
        $this->dao =  new Dao_Room_Log();
        $this->nid = $nid;
        $this->uid = empty($uid)?0:$uid;
        $this->log_type = $log_type;
        $this->name = $this->name_conf[$this->log_type];
    }

    public function log_info($body)
    {
        $log = array(
            'nid' => $this->nid,
            'uid' => $this->uid,
            'log_name' => $this->name,
            'log_type' => $this->log_type,
            'log_body' => $body
        );
        $this->dao->insert_log($log);
    }

}