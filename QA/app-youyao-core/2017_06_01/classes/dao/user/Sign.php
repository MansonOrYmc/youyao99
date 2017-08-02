<?php
apf_require_class("APF_DB_Factory");

class Dao_User_Sign {

    private $pdo;

    public function __construct() {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("master");
    }

    public function write_session_record($data) {
        $sql = "insert into t_user_session (uid, yyid, sid, client_ip, status, created, login_from) values(:uid, :yyid, :sid, :client_ip, :status, :created, :login_from);";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function get_record_by_sid($yyid, $sid) {
        $sql = "select uid, yyid, sid, client_ip, status, created, login_from from t_user_session where yyid = ? and sid = ? limit 1;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($yyid, $sid));
        return $stmt->fetch();
    }

    public function remove_record_by_sid($yyid, $sid) {
        $sql = "delete from t_user_session where yyid = ? and sid = ? ;";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($yyid, $sid));
    }

}
