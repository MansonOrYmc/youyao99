<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/8/10
 * Time: 上午11:28
 */

apf_require_class("APF_DB_Factory");

class Dao_Mail_Log {

    private $stat_pdo;

    public function __construct() {
        $this->stat_pdo = APF_DB_Factory::get_instance()->get_pdo("statsmaster");
    }

    public function insert_log($log){

        if(!isset($log['log_type'])) return false;
        else $log_type = $log['log_type'];
        if(empty($log['log_name'])) return false;
        else $log_name = $log['log_name'];
        if(empty($log['log_body'])) return false;
        else $log_body = $log['log_body'];


        $params = array(
            'log_type' => $log_type,
            'log_body' => $log_body,
            'log_name' => $log_name,
            'create_time' => time()
        );

        $sql = <<<SQL
INSERT INTO log_mail
(`log_type`,`log_name`,`log_body`,`create_time`) VALUES
(:log_type,:log_name,:log_body,:create_time)
SQL;


        $stmt = $this->stat_pdo->prepare($sql);

        $result = $stmt->execute($params);


        if (!$result) {
            return false;
        }
        return true;
    }

}