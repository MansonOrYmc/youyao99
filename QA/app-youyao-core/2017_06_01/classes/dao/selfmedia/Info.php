<?php
apf_require_class("APF_DB_Factory");

class Dao_Selfmedia_Info {

    private $pdo;

    public function __construct() {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
    }
	
	public function get_media_byid($id) {
		$sql = "select * from t_selfmedia where id = ?";
try{
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($id));
        return $stmt->fetch();
}catch (Exception $e){
		print_r($e->getMessage());
}
	}
	
	public function update_media($params) {

		$sql = "update t_selfmedia set data = ? where id = ?";
try {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($params['data'],$params['id']));
}catch (Exception $e) {
		print_r($e->getMessage());
}
	}

	public function create_media($params) {

		$sql = "insert into t_selfmedia (author, create_time, data, status) values (?, ?, ?, ?)";
try {
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array($params['author'], $params['create_time'], $params['data'], $params['status']));
		return $this->pdo->lastInsertId();
}catch (Exception $e) {
		print_r($e->getMessage());
}
	}
}
