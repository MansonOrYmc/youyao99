<?php
class Util_TravelFund {
	
	private $fcode_info;
	
	public function __construct() {
                $this->fcode_info = new Dao_Fcode_FcodeInfo();
        }
    
	public function get_fcode($uid){

		$user_info = $this->fcode_info;	
		if($fcode = $user_info->get_fcode_by_uid($uid)){
			return $fcode;
		}else{
			$fcode = substr(base_convert(md5($uid),16,32),0,4);
			if($result = $user_info->update_fcode_by_uid($uid, $fcode)){
				return $fcode;
			}
		}
	}

	public function insert_fcode($d_uid, $fcode, $channel) {
		
		$fund = APF::get_instance()->get_config('fcode_fund','activity');
		$info = $this->fcode_info;
		$s_uid = $info->get_uid_by_fcode($fcode);
		if(empty($s_uid)){
			return 'Not Found Fcode';
		} elseif ($s_uid == $d_uid) {
			return 'Bad Fcode';
		} elseif ($this->user_fcode_status($d_uid)){
			return 'Has been Used';
		}
		
		try{
			$update_f_fund = $info->update_user_fund($d_uid, $fund);
			$fcode_succ_id = $info->insert_fcode_succ($d_uid, $s_uid, $channel, $fund);
		}catch(Exception $e){
			return 'Caught exception:'.$e->getMessage();
		}
		
		return 'success';
		
	}
	
	public function user_fcode_status($uid) {

		$info = $this->fcode_info;
		$status = $info->get_row_by_tuid($uid);

		return $status;
	}

	public function get_fcode_list($uid, $status, $page) {
		
		$info = $this->fcode_info;
		$datas = $info->get_row_list($uid, $status, $page);

		$response = array();
                $userdao = new Dao_User_UserInfo();
                $booking = new Dao_Room_Booking();
                foreach($datas as $row){
			$data = array();
                        $data['status'] = $row['channel']>=0 ? $row['status'] : $row['channel'];
                        if($row['channel']<0){
                                $data['bid'] = $row['d_uid'];
                                $data['roomname'] = '';
                                $bids[] = $row['d_uid'];
                        }else{
                                $data['duid'] = $row['d_uid'];
                                $data['dname'] = '';
                                $uids[] = $row['d_uid'];
                        }
                        $data['fund'] = $row['fund'];
                        $data['date'] = date('Y-m-d',$row['create_date']);
			$rows[] = $data;
                }
                $books = !empty($bids)?$booking->get_roombooking_by_ids($bids):array();
                $names = !empty($uids)?$userdao->get_userinfo_by_ids($uids):array();
                foreach($rows as $key=>$value){
                        $response[$key] = $value;
                        if($value['bid']){
                                foreach($books as $k=>$v){
                                        if($v['id']==$value['bid']){
                                                $response[$key]['roomname'] = $v['room_name'];
                                        }
                                }
                        }elseif($value['duid']){
                                foreach($names as $k=>$v){
                                        if($v['uid']==$value['duid']){
                                                $response[$key]['dname'] = $v['name'];
                                                $response[$key]['dmail'] = $v['mail'];
                                        }
                                }
                        }
                }
		
		return $response;
	}

	public function get_friend_byuid($uid) {
		
		$polaris = $this->fcode_info;
		$uids = $polaris->get_fuid_byuid($uid);

		if($uids['s_uid'] <> $uid) {
			$result = $uids['s_uid'];
		}else if($uids['d_uid'] <> $uid) {
			$result = $uids['d_uid'];
		}

		return $result;

	}
  
}
