<?php
apf_require_class("APF_DB_Factory");

class Dao_Stats_JobIndex {
	
    public function get_sdata($key) {
    	$pdo = APF_DB_Factory::get_instance()->get_pdo("statsmaster");
        $sql = "select * from  job_index_status  where j_name=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($key));
        return $stmt->fetch();
    }
    
    public function update_sdata($params) {
    	$pdo = APF_DB_Factory::get_instance()->get_pdo("statsmaster");
        $sql = "update job_index_status set j_data=?,update_date=? where j_name=?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(array($params['j_data'],time(),$params['j_name']));
    }
}