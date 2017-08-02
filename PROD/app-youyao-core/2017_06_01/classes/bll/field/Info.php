<?php

class Bll_Field_Info {

    private $fielddao;
    public function __construct() {
        $this->fielddao    = new Dao_Field_InfoMemcache();
    }

	public function get_field_list($entity) {
		return $this->fielddao->get_field_list($entity);
	}

	public function get_field($id, $table_name, $bundle='article', $type='node') { // 已知表名取单个数据
        return $this->fielddao->get_field($id, $bundle, $type, $table_name);
	}

	public function get_user_field_by_uids($uid) {
        if(empty($uid)) return;
		$tables = $this->get_field_table_column('user', 'user');
		if(!is_array($uid)) $uid = array($uid);

		return $this->fielddao->get_user_field_by_uids($tables, $uid);

	}

	public function get_field_table_column($type, $bundle) {

		$instance = $this->get_field_config_instance($type, $bundle);
		foreach($instance as $row) {
			$tables_name[] = "'".$row['field_name']."'";
		}
		$config = $this->get_field_config($tables_name);
		foreach($config as $values) {
			$data = unserialize($values['data']);
			if($data['storage']['details']['sql']['FIELD_LOAD_CURRENT']){
				$tables[] = $data['storage']['details']['sql']['FIELD_LOAD_CURRENT'];
			}else{
				$tables[] = array("field_data_".$values['field_name'] => 
									array(	"value" => $values['field_name']."_value")
							); // drupal 的field表结构都写在module目录下面的.intall文件下，程序会去遍历文件，再查找数据
			}
		}
		
		return $tables;
		
	}

	public function get_node_field_by_nids($nid) { // 取出房间的所有字段数据
/*
		$instance = $this->get_field_config_instance('node', 'article');
		foreach($instance as $row) {
			$tables_name[] = "'".$row['field_name']."'";
		}
		$config = $this->get_field_config($tables_name);
		foreach($config as $values) {
			$data = unserialize($values['data']);
			$tables[] = $data['storage']['details']['sql']['FIELD_LOAD_CURRENT'];
		}
		
		if(!is_array($nid)) $nid = array($nid);
*/
        if(empty($nid)) return;
		$tables = $this->get_field_table_column('node', 'article');
		if(!is_array($nid)) $nid = array($nid);
		return $this->fielddao->get_node_field_by_nids($tables, $nid);
	}

	public function get_field_config($field_names) {
		if(!is_array($field_names)) {
			$field_names = array($field_names);
		}
		return $this->fielddao->get_field_config($field_names);
	}

	public function get_field_config_instance($type, $bundle) {
		return $this->fielddao->get_field_config_instance($type, $bundle);
	}

    //有tid的地方都调这里
	public function get_taxonomy_term_data($tid=null, $vid=null) {
		$data = $this->fielddao->get_taxonomy_term_data($tid, $vid);
		$result = array();
		foreach($data as $k=>$v) {
			$result[$v['tid']] = $v['name'];
		}
		return $result;
	}

	// user 的entity_id = user bundle = user;
	// node（房间） 的entity_id = node bundle = article
	public function write_field_record($params , $entity_type='user', $bundle='user') {
		$entity_id = $params['entity_id'];
        unset($params['entity_id']);
		if($params['revision_id']) {
			$revision_id = $params['revision_id'];
			unset($params['version_id']);
		}elseif($entity_type == 'node') {  // node需要查出revision_id
			$roombll = new Bll_Room_RoomInfo();
			$primary_id = reset($roombll->get_node_revision_bynid($entity_id));
			$entity_id = $primary_id['nid'];
			$revision_id = $primary_id['vid'];
		}
		if(empty($entity_id) || empty($params)) return;
		foreach($params as $tableName=>$value ) {
			$this->fielddao->insert_update_field_table($tableName, $entity_type, $bundle, $value, $entity_id, $revision_id);
		}
	}

	public function update_homestay_weixin($uid, $weixinId) {
		$this->fielddao->insert_update_field_table('field_data_field_weixin', 'user', 'user', $weixinId, $uid, $uid);
	}
	public function update_homestay_line($uid, $line) {
		$this->fielddao->insert_update_field_table('field_data_field_line', 'user', 'user', $line, $uid, $uid);
	}
	public function update_homestay_skype($uid, $skpye) {
		$this->fielddao->insert_update_field_table('field_data_field_skype', 'user', 'user', $skpye, $uid, $uid);
	}

	public function write_multi_field_record($params, $entity_id, $entity_type='user', $bundle='user') {

		if($entity_type=='node') { // node 需要查出revision_id
			$roombll = new Bll_Room_RoomInfo();
			$primary_id = reset($roombll->get_node_revision_bynid($entity_id));
			foreach($primary_id as $row) {
				$ids[] = array(
					'entity_id' => $row['uid'],
					'revision_id' => $row['vid'],
				);
			}
		} else {
			foreach($entity_id as $row) {
				$ids[] = array(
					'entity_id' => $row,
					'revision_id' => $row,
				);
			}
		}
		foreach($params as $tableName=>$value) {
			$this->fielddao->multi_insert_update_field_table($tableName, $entity_type, $bundle, $value, $ids);
		}
	}

}
