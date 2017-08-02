<?php

class Util_Hash {

	private $working_array;
	private $spin = 0;

	public function __construct($spin = 0) {
		if (!empty($spin)) {
			$this->spin = $spin;
		}
	}

	public function update_order_hash_id($order_id) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		do {
			$hash_id = $this->hash($order_id);
			$sql = 'SELECT id FROM t_homestay_booking WHERE hash_id=:hash_id';
			$stmt = $pdo->prepare($sql);
			$stmt->execute(array('hash_id' => $hash_id));
			$result = $stmt->fetchColumn();
		} while (empty($hash_id) || !empty($result));

		$sql = 'UPDATE t_homestay_booking SET hash_id=:hash_id WHERE id=:id';
		$stmt = $pdo->prepare($sql);
		$result = $stmt->execute(array(
			'id' => $order_id,
			'hash_id' => $hash_id
		));
		if($result){
			return $hash_id;
		}else{
			return false;
		}

	}

	public function hash($id) {
		if (!empty($id)) {
			$hash_id = mt_rand(1000000000, 2147483647);
		}
		else {
			$hash_id = FALSE;
		}
		return $hash_id;
	}
}
