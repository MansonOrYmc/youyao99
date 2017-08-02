<?php
apf_require_class('My_Controller', 'controller');

class User_FindphoneController extends My_Controller {
	public function init() {
		header('Content-Type:application/json');
	}

	public function phoneVerify() {
		$param_arr = APF::get_instance()->get_request()->get_parameters();

		$security = Util_Security::Security($param_arr);
		if (!$security) {
			Util_ZzkCommon::zzk_echo(json_encode(array(
				'code' => 400,
				'codeMsg' => 'Illegal_request',
				'status' => 400,
				'msg' => "request forbidden",
				"userMsg" => 'Illegal_request',
			)));

			return FALSE;
		}

		$phoneNum = $param_arr['phoneNum'];
		$areaNum = $param_arr['areaNum'];

		$userbll = new Bll_User_UserInfo();
		if ($this->phone_num_format_check($areaNum, $phoneNum)) {
			$userInfo = $userbll->get_user_info_by_phone_num($phoneNum);
			if (empty($userInfo)) {
				$response = array(
					'status' => 400,
					'data' => NULL,
					'userMsg' => 'The_phone_number_is_not_registered',
					'msg' => 'The_phone_number_is_not_registered'
				);
			}
			else {
				$userbll = new Bll_User_UserInfo();
				$codelist = $userbll->get_sms_captcha_by_phone($phoneNum);
				$ip = Util_NetWorkAddress::get_client_ip();
				$key = 'mobile_user_find_phone_verify_' . md5($ip);
				$memcache = APF_Cache_Factory::get_instance()->get_memcache();
				if (in_array($ip, array('116.228.208.194', '27.115.103.178'))) {
					$cache = 0;
				}
				else {
//					$cache = $memcache->get($key);
				}
				if ($cache > 4) {
					$response = array(
						'status' => 400,
						'data' => NULL,
						'userMsg' => 'Please_try_again_later',
						'msg' => 'Please_try_again_later'
					);
				}
				else {
					$memcache->set($key, $cache + 1, 0, 3600);

					$interval = APF::get_instance()
						->get_config("phone_captcha_time");
					if ($codelist[0]['create_time'] > time() - $interval) {
						$response = array(
							'status' => 201,
							'data' => array(
								'phoneNum' => $phoneNum,
								'interval' => $interval - (time() - $codelist[0]['create_time'])
							),
							'userMsg' => $interval . 'seconds_not_resend_verification_code',
							'msg' => $interval .  'seconds_not_resend_verification_code'
						);
					}
					else {
						$code = $userbll->insert_sms_captcha($phoneNum);
						if ($code) {
							$areaNumMap = array(
								'86' => 1,
								'886' => 2
							);
							$area = $areaNumMap[strval(intval($areaNum))];
							$dest_id = 10;
							$smsbll = new Bll_Sms_SMSInfo();
							$content = 'verify_code'. $code . "，".'verify_code_content_key2';
							$args = array(
								0,                //	"oid"
								0,                //	"sid"
								0,                //	"uid"
								$phoneNum,        //	"mobile"
								$content,        //	"content"
								$area,                //	"area"
								$dest_id,                //	"dest_id"
								0,                //	"retry"
								0,                //	"status"
								time(),            //	"create_time"
							);
							$smsbll->bll_send_sms_notify($args);
						}
						$response = array(
							'status' => 200,
							'data' => array(
								'phoneNum' => $phoneNum,
								'interval' => $interval,
								'displayMsg' => 'Verificationcodehasbeensent'
							),
							'userMsg' => 'Verificationcodehasbeensent',
							'msg' => 'Verificationcodehasbeensent'
						);
					}
				}
			}
		}
		else {
			$response = array(
				'status' => 400,
				'data' => NULL,
				'userMsg' => 'Wrong_format_of_phone_number',
				'msg' =>  'Wrong_format_of_phone_number'
			);
		}
		Util_ZzkCommon::zzk_echo(json_encode($response));
	}

	public function codeVerify() {
		$param_arr = APF::get_instance()->get_request()->get_parameters();

		$phoneNum = $param_arr['phoneNum'];
		$areaNum = $param_arr['areaNum'];
		$code = $param_arr['code'];

		//添加ip策略,防止暴力破解验证码
		$ip = Util_NetWorkAddress::get_client_ip();
		$key = 'mobile_user_find_code_verify_' . md5($ip);
		$memcache = APF_Cache_Factory::get_instance()->get_memcache();

		if (in_array($ip, array('116.228.208.194', '27.115.103.178'))) {
			$cache = 0;
		}
		else {
//			$cache = $memcache->get($key);
		}

		if ($cache > 10) {
			$response = array(
				'status' => 400,
				'data' => NULL,
				'userMsg' => 'Please_try_again_later',
				'msg' => 'Please_try_again_later'
			);
		}
		else {
			$memcache->set($key, $cache + 1, 0, 3600);

			$userbll = new Bll_User_UserInfo();
			if ($this->phone_num_format_check($areaNum, $phoneNum)) {
				$userInfo = $userbll->get_user_info_by_phone_num($phoneNum);
				if (empty($userInfo)) {
					$response = array(
						'status' => 400,
						'data' => NULL,
						'userMsg' => 'The_phone_number_is_not_registered',
						'msg' => 'The_phone_number_is_not_registered'
					);
				}
				else {
					$userbll = new Bll_User_UserInfo();
					$codelist = $userbll->get_sms_captcha_by_phone($phoneNum);
					foreach ($codelist as $row) {
						if ($row['code'] == $code) {
							$response = array(
								'status' => 200,
								'data' => array(
									'phoneNum' => $phoneNum,
									'interval' => time() - $codelist[0]['create_time']
								),
								'userMsg' => 'Correct_codes',
								'msg' =>  'Correct_codes'
							);
							break;
						}
					}

					if (empty($response)) {
						$response = array(
							'status' => 400,
							'data' => NULL,
							'userMsg' => 'Verification Code incorrect',
							'msg' => 'Verification Code incorrect'
						);
					}
				}
			}
			else {
				$response = array(
					'status' => 400,
					'data' => NULL,
					'userMsg' => 'phonenumformatisincorrect',
					'msg' => 'phonenumformatisincorrect'
				);
			}
		}
		Util_ZzkCommon::zzk_echo(json_encode($response));
	}


	public function submit() {
		$param_arr = APF::get_instance()->get_request()->get_parameters();
		$areaNum = $param_arr['areaNum'];
		$phone = $param_arr['phoneNum'];
		$code = $param_arr['code'];
		$passWord = $param_arr['passWord'];
		$passWordConfirm = $param_arr['passWordConfirm'];

		//添加ip策略,防止暴力破解验证码
		$ip = Util_NetWorkAddress::get_client_ip();
		$key = 'mobile_user_find_phone_' . md5($ip);
		$memcache = APF_Cache_Factory::get_instance()->get_memcache();

		if (in_array($ip, array('116.228.208.194', '27.115.103.178'))) {
			$cache = 0;
		}
		else {
//			$cache = $memcache->get($key);
		}

		if ($cache > 10) {
			$response = array(
				'status' => 400,
				'data' => NULL,
				'userMsg' => 'Please_try_again_later',
				'msg' => 'Please_try_again_later'
			);
		}
		else {
			$memcache->set($key, $cache + 1, 0, 3600);

			// 再验证一次信息
			if ($this->phone_num_format_check($areaNum, $phone)) {
				$userbll = new Bll_User_UserInfo();
				$userInfo = $userbll->get_user_info_by_phone_num($phone);
				if (empty($userInfo)) {
					$err_msg = 'The_phone_number_is_not_registered';
				}
				elseif (!$this->verify_smscode($phone, $code)) {
					$err_msg = 'SMS_verification_code_error';
				}
				elseif (strlen($passWord) < 5 || strlen($passWord) > 20) {
					$err_msg = 'Passwordlengthdoesnotconformto';
				}
				elseif ($passWord != $passWordConfirm) {
					$err_msg = 'Two_input_password_does_not_correspond';
				}
				if (!empty($err_msg)) {
					$response = array(
						'status' => 400,
						'data' => NULL,
						'userMsg' => $err_msg,
						'msg' => $err_msg
					);
				}
				else {
					require_once CORE_PATH . 'classes/includes/password.inc';
					$hased_password = user_hash_password(trim($passWord), 15);
					$userInfoDao = new Dao_User_UserInfo();
					$result = $userInfoDao->update_user_password($userInfo['uid'], $hased_password);
					if ($result) {
						$response = array(
							'status' => 200,
							'data' => array(
								'displayMsg' => 'Password_reset_complete'
							),
							'userMsg' => 'Password_reset_complete',
							'msg' => 'Password_reset_complete'
						);
					}
					else {
						$response = array(
							'status' => 500,
							'data' => NULL,
							'userMsg' => 'Password_modification_fails',
							'msg' => 'Password_modification_fails'
						);
					}
				}
			}
			else {
				$response = array(
					'status' => 400,
					'data' => NULL,
					'userMsg' => 'phonenumformatisincorrect',
					'msg' => 'phonenumformatisincorrect'
				);
			}
		}
		Util_ZzkCommon::zzk_echo(json_encode($response));
	}


	private function phone_num_format_check($areaNum, $phoneNum) {
		$areaNum = intval($areaNum);
		if (($areaNum == 86 && preg_match('/\d{11}/', $phoneNum)) ||
			($areaNum == 886 && preg_match('/\d{9,10}/', $phoneNum))
		) {
			return TRUE;
		}
		else {
			return FALSE;
		}

	}

	private function verify_smscode($phonenum, $smsCode) {

		$userbll = new Bll_User_UserInfo();
		$phone = $userbll->get_sms_captcha_by_phone($phonenum);
		foreach ($phone as $row) {
			if ($row['code'] == $smsCode) {
				return TRUE;
			}
		}

		return FALSE;
	}

}
