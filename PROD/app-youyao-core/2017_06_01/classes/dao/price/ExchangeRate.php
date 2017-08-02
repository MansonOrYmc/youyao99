<?php

class Dao_Price_ExchangeRate {
	public function __construct() {
		$this->lky_slave_pdo = APF_DB_Factory::get_instance()
			->get_pdo("lkyslave");
	}

	public function get_dest_exchange_rate_by_time($dest_id, $time) {
		$sql = <<<SQL
SELECT exchange_rate
FROM LKYou.t_exchange_rate_log
WHERE dest_id=:dest_id AND UNIX_TIMESTAMP(update_time)<=:time AND status=1
ORDER BY id DESC LIMIT 1
SQL;
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute(array(
			'dest_id' => $dest_id,
			'time' => $time
		));
		return $stmt->fetchColumn();
	}
}