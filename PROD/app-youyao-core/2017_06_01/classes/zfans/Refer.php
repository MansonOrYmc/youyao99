<?php

class ZFans_Refer {
    
  private $lkyMaster;
  private $lkySlave;
  // private $oneMaster;
  // private $oneSlave;

  private $uid = 0;
  private $status = 0;

  private function __construct() {
    $this->lkyMaster = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
    $this->lkySlave = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
    // $this->oneMaster = APF_DB_Factory::get_instance()->get_pdo("master");
    // $this->oneSlave = APF_DB_Factory::get_instance()->get_pdo("slave");
  }

  public static function create($uid) {

  }
  
}

?>