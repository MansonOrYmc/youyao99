<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/7/24
 * Time: 下午3:57
 */
apf_require_class("APF_DB_Factory");

class Dao_Paytype_Conf {

    private $lky_pdo;

    public function __construct() {
        $this->lky_pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
    }


    public function get_conf($dest){
        $sql = "SELECT pay_type FROM t_paytype_conf  WHERE  dest = :dest LIMIT 1";
        $stmt = $this->lky_pdo->prepare($sql);
        $stmt->execute(array('dest'=>$dest));
        return $stmt->fetch();
    }


}