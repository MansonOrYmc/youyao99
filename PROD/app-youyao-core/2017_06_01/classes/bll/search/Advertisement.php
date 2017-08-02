<?php

class Bll_Search_Advertisement {

	private $addao;
    public function __construct() {
        $this->addao  = new Dao_Search_Advertisement();
    }

	public function get_ad_position($dest_id=10) { // 获得广告在搜索列表的位置
		$data = $this->addao->get_ad_position($dest_id);
		$result = json_decode($data['value'], true);
		return $result;
	}

	public function get_ad_list($type=0, $dest_id=10) { // 获得广告民宿的列表
		$data = $this->addao->get_ad_list($type, $dest_id);
		return $data;
	}

	public function change_ad_position($params){ // 更新广告位置信息

		if(empty($params['value'])
			|| empty($params['admin_uid'])
			|| empty($params['dest_id'])
		) {
			return false;
		}


		$fields = array(
			'value' => $params['value'],
			'admin_uid' => $params['admin_uid'],
		);
		$condition  = array(
			'dest_id' => $params['dest_id'],
			'type' => 1,
		);

		return $this->addao->update_adpromotion($fields, $condition);
	}

	public function add_new_list($params){ 	//添加新的推广民宿
		return $this->addao->insert_into_adpromotion($params);
	}

	public function remove_ad_bnb($params){ // 删除推广的民宿
		$fields = array(
			'status' => 0,
			'admin_uid' => $params['admin_uid'],
		);

		if($params['uid']){
			$condition = array(
				'value' => $params['uid']
			);
		}elseif($params['id']){
			$condition = array(
				'id' => $params['id']
			);
		}

		return $this->addao->update_adpromotion($fields, $condition);
	}
}
