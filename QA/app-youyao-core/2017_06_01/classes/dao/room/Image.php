<?php

class Dao_Room_Image extends Dao_Db {

	public function get_field_data_field_image($entity_id, $entity_type = 'node', $bundle = 'article') {
		$sql = <<<'SQL'
SELECT field_image_fid,field_image_version
FROM drupal_field_data_field_image
WHERE entity_id=:entity_id
AND entity_type=:entity_type
AND bundle=:bundle
ORDER BY delta
LIMIT 36 OFFSET 0
SQL;
		$stmt = $this->load_one_slave_db()->prepare($sql);
		$stmt->execute(array(
			'entity_id' => $entity_id,
			'entity_type' => $entity_type,
			'bundle' => $bundle
		));
		return $stmt->fetchAll();
	}

	public function batch_get_room_image($room_id_arr) {
		$sql = "SELECT node.nid,node.title,field_image_fid,field_image_version,f.uri,f2.uri,
  concat('http://img1.zzkcdn.com/',substr(f.uri,10),'-room210x130.jpg') url,
  concat('http://img1.zzkcdn.com/',f2.uri,'/2000x1500.jpg-room210x130.jpg') url2
FROM one_db.drupal_field_data_field_image t
LEFT JOIN one_db.drupal_node node ON t.entity_id=node.nid
  LEFT JOIN one_db.drupal_file_managed f ON f.fid=t.field_image_fid
  LEFT JOIN LKYou.t_img_managed f2 ON f2.fid=t.field_image_fid
WHERE entity_type = 'node' AND entity_id IN (".join(',', $room_id_arr).")
GROUP BY entity_id
ORDER BY entity_id, delta ;";
		$stmt = $this->load_lky_slave_db()->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function get_file_managed($fid) {
		$sql = 'SELECT uri FROM drupal_file_managed WHERE fid=:fid LIMIT 1';
		$stmt = $this->load_one_slave_db()->prepare($sql);
		$stmt->execute(array('fid' => $fid));
		return $stmt->fetchColumn();
	}

	public function get_t_img_managed($fid) {
		$sql = 'SELECT uri FROM t_img_managed WHERE fid=:fid LIMIT 1';
		$stmt = $this->load_lky_slave_db()->prepare($sql);
		$stmt->execute(array('fid' => $fid));
		return $stmt->fetchColumn();
	}

	public function get_multi_file_managed($fids) {
		if(empty($fids)){
			return;
		}
		$condition = implode(', ', $fids);
		$sql = 'SELECT uri FROM drupal_file_managed WHERE fid in ('.$condition.') ';
                $stmt = $this->load_one_slave_db()->prepare($sql);
                $stmt->execute();
                return $stmt->fetchAll();
	}

	public function get_multi_t_img_managed($fids) {
		if(empty($fids)){
			return;
		}
		$condition = implode(', ', $fids);
		$sql = 'SELECT uri FROM t_img_managed WHERE fid in ('.$condition.') ';
                $stmt = $this->load_lky_slave_db()->prepare($sql);
                $stmt->execute();
                return $stmt->fetchAll();
	}
}
