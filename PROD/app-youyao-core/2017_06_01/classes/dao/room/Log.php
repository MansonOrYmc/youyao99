<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/8/4
 * Time: 下午5:12
 */
apf_require_class("APF_DB_Factory");

class Dao_Room_Log {

    private $stat_pdo;

    public function __construct() {
        $this->stat_pdo = APF_DB_Factory::get_instance()->get_pdo("statsmaster");
    }

    public function insert_log($log){
        if(empty($log['nid'])) return false;
        else $nid = $log['nid'];
        if(!isset($log['uid'])) return false;
        else $uid = $log['uid'];
        if(empty($log['log_type'])) return false;
        else $log_type = $log['log_type'];
        if(empty($log['log_name'])) return false;
        else $log_name = $log['log_name'];
        if(empty($log['log_body'])) return false;
        else $log_body = $log['log_body'];

        $params = array(
            'nid' => $nid,
            'uid' => $uid,
            'log_type' => $log_type,
            'log_body' => $log_body,
            'log_name' => $log_name,
            'create_time' => time()
        );

        $sql = <<<SQL
INSERT INTO log_room
(`nid`,`uid`,`log_type`,`log_name`,`log_body`,`create_time`) VALUES
(:nid,:uid,:log_type,:log_name,:log_body,:create_time)
SQL;
        $stmt = $this->stat_pdo->prepare($sql);
        $result = $stmt->execute($params);

        if (!$result) {
            return false;
        }
        return true;
    }

}