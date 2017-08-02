<?php
apf_require_class("APF_DB_Factory");

class Dao_Weixin_Events {

  private $lkymasterPdo;
  private $lkyslavePdo;

  public function __construct() {
    $this->lkymasterPdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
    $this->lkyslavePdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
  }

  public function logEvent($event) {
    // $sql = "insert into LKYou.t_weixin_events(event, identifier, timestamp, expiration) values(?, ?, ?, ?)";
    // $stmt = $this->lkymasterPdo->prepare($sql);
    // return $stmt->execute($eventData);    
  }
}
