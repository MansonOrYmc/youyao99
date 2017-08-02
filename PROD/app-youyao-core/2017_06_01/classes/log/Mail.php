<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/8/10
 * Time: 上午11:17
 */
class Log_Mail {
    private $dao;
    private $log_type;
    private $name;
    private $name_conf = array(
        0=>"unknown",
        1=>"QQMail",
        2=>"MailGun"
    );

    public function __construct() {
        $this->dao =  new Dao_Mail_Log();
    }

    public function log_info($body,$log_type=0)
    {
        $this->log_type = $log_type;
        $this->name = $this->name_conf[$this->log_type];
        $log = array(
            'log_name' => $this->name,
            'log_type' => $this->log_type,
            'log_body' => $body
        );
        $this->dao->insert_log($log);

    }
}