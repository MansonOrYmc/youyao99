<?php
class Dao_Translate_Info {
    private $pdo;
	private $slave_pdo;

	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
    }

	public function get_trans_by_key($key, $dest_id) {
		if(!$dest_id) $dest_id = 10;
		$dest_arr = array(
			$dest_id,
			Const_Default_Dest_ID,
		);
		$dest_str = implode(",", $dest_arr);
		$sql = "select l_desc from m_dest_language where l_key = ? and dest_id in ($dest_str) order by dest_id desc, id desc" ;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($key));
		return $stmt->fetchColumn();
		
	}

    public function get_trans_by_multikey($key_list, $dest_id) {
        if(empty($key_list)) return;
        if(!$dest_id) $dest_id = 10;
        $sql = "select l_key,l_desc from m_dest_language where l_key in (".Util_Common::placeholders("?", count($key_list)).") and dest_id = ? order by id desc";
        $stmt = $this->slave_pdo->prepare($sql);
        foreach($key_list as $row) {
            $pdo_value[] = trim($row);
        }
        $pdo_value[] = $dest_id;
        $stmt->execute($pdo_value);
        return $stmt->fetchAll();
    }

	public function get_key_by_str($str, $dest_id) {

		if(!$str) return;
		$dest_condition = "";
		if($dest_id) $dest_condition = "and dest_id = '$dest_id'";
		$sql = "select l_key from m_dest_language where l_desc = ? $dest_condition order by dest_id desc, id desc" ;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array($str));
		return $stmt->fetchColumn();
		
	}

    public function set_trans($key, $str, $dest_id) {
        if(!$key || !$str || !$dest_id) return;
        $sql = "insert into m_dest_language (`l_key`, `l_desc`, `dest_id`, `create_time`) values (?, ?, ?, unix_timestamp()) on duplicate key update `l_desc` = values(`l_desc`);";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($key, $str, $dest_id));
    }

}
