<?php

class Bll_Push_PushInfo {
	private $pushInfoDao;

	public function __construct() {
		$this->pushInfoDao = new Dao_Push_PushInfo();
	}

	public function bll_send_mobile_notify($email, $msg) {
		if(empty($msg)){
			return false;
		}

		if(empty($email)) return 0;

		$devices = $this->pushInfoDao->dao_query_devices($email);
		$baidudevices = $this->pushInfoDao->dao_query_baidu_device($email);
		foreach ($devices as $device) {
			$device_token = $device['deviceid'];

			if (!empty($device_token)) {
				$send_info = array(
					$device_token,
					$msg,
					REQUEST_TIME
				);
            	$this->pushInfoDao->dao_send_mobile_notify($send_info);
        	}

        }

		foreach($baidudevices as $baidu){
			$baiduid = $baidu['baidu_channel_id'];
			if (!empty($baiduid)) {

				$baidu_send_info=array(
					$baiduid,
					$msg,
					REQUEST_TIME

				);
				$this->pushInfoDao->dao_send_mobile_notify_baidu($baidu_send_info);
			}
		}




		return 1;
	}

	public function get_push_queue($os) {

		if($os=="jg") {
			$dao = new Dao_Push_PushInfo();			
			$push_queue = $dao->get_ios_push_queue();
			$deviceid = array();
			foreach($push_queue as $k=>$v) {
				$deviceid[] = '"'.$v['device_token'].'"';
			}
			
			if($deviceid) {
				$device = $dao->get_jg_register_id($deviceid);	
			}
			$jg = array();
			foreach($device as $row) {
				$jg[$row['deviceid']] = $row['jgpush_id'];
			}

			$result = array();
			foreach($push_queue as $value) {
				if($jg[$value['device_token']]){
					$data = $value;
					$data['register_id'] = $jg[$value['device_token']];
					$result[] = $data;
				}
			}

			return $result;
		}

	}

	public function update_jgpush($ids) {

		if(empty($ids)) return;
		$dao = new Dao_Push_PushInfo();
		return $dao->push_complete_update($ids);
	}

	public function send_push_message($info){
		$dao = new Dao_Push_PushInfo();
		return $dao->send_push_message($info);
	}

	public function get_token_bymail($email){
		$dao = new Dao_Push_PushInfo();
		return $dao->dao_query_devices($email);
	}

	public function get_total_available_token_by_uid($uid){
		$dao = new Dao_Push_PushInfo();
		$guidlist = $dao->get_guid_by_uid($uid);
		$tokens = array();
		foreach ($guidlist as $guid) {
			$deviceInfo = $dao->get_token_by_guid($guid);
			$tokens[] = array(
				'token' => $deviceInfo['token'],
				'type' => $deviceInfo['type'],
				'os' => $deviceInfo['os']
			);
		}
		return $tokens;
	}


	public function get_pushqueue_list(){
		$dao = new Dao_Push_PushInfo();
		return $dao->get_pushqueue_list();
	}

	public function get_pushinfo_bytoken($token){
		$dao = new Dao_Push_PushInfo();
		return $dao->get_pushinfo_bytoken($token);
	}

	public function get_tokeninfo_byguid($guid){
		$dao = new Dao_Push_PushInfo();
		return $dao->get_tokeninfo_byguid($guid);
	}

	public function send_push_message_new($params){
		$dao = new Dao_Push_PushInfo();
		return $dao->send_push_message_new($params);
	}

    public function send_push_message_to_multiple($params) {
		$dao = new Dao_Push_PushInfo();
		return $dao->send_push_message_to_multiple($params);
    }

	public function get_tokeninfo_byuid($uid){
		$dao = new Dao_Push_PushInfo();
		return $dao->get_tokeninfo_byuid($uid);
	}

	public function get_tokeninfo_by_multiuid($uids){
		$dao = new Dao_Push_PushInfo();
		return $dao->get_tokeninfo_by_multiuid($uids);
	}

	public function get_queue_list(){
		$dao = new Dao_Push_PushInfo(); // deal dup next
		return $dao->get_queue_list();
	}
	public function update_queue_status($id){
		$dao = new Dao_Push_PushInfo();
		return $dao->update_queue_status($id);
	}

}
