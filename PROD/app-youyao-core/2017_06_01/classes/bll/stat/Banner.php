<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 16/1/25
 * Time: 下午5:00
 */
class Bll_Stat_Banner{

    public static function insert_into_mysql($name,$code){
        $sql = "insert into stats_db.t_banner_stat values(null,'$name',1,'$code','1') ON DUPLICATE KEY UPDATE click=click+1 ";
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $stmt = $pdo->prepare($sql);
        return $stmt->execute();
    }
}