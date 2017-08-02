<?php

class  Bll_User_UserInfo {
	private $userInfoDao;
	private $customerInfoDao;

	public function __construct() {
		$this->userInfoDao = new Dao_User_UserInfoMemcache();
		$this->customerInfoDao = new Dao_Customer_Info();
	}


	public function isUserZFansRefer($uid) {
		return $this->userInfoDao->isUserZFansRefer($uid);
	}

	public function get_user_info_by_email($email) {
		return $this->userInfoDao->get_user_info_by_email($email);
	}

	public function get_user_by_name_or_email($name) {
		return $this->userInfoDao->get_user_by_name_or_email($name);
	}

	public function get_user_info_by_phone_num($phone) {
		return $this->userInfoDao->get_user_by_phone_num($phone);
	}

	public function get_user_status_by_uid($uid) {
		return $this->userInfoDao->get_user_status_by_uid($uid);
	}

    public function get_h_favorite_status($uid, $hid) {
        return (bool)$this->userInfoDao->get_h_favorite($uid, $hid);
    }

	public function get_user_fund_by_uid($uid) {
		return $this->userInfoDao->dao_get_user_fund_by_uid($uid);
	}

	public function update_user_fund_by_uid($fund, $uid) {
		return $this->userInfoDao->dao_update_user_fund_by_uid($fund, $uid);
	}

	public function get_whole_user_info($uid) {
		if(!$uid || empty($uid)) return;
		if(is_array($uid)) {
			return $this->userInfoDao->get_userinfo_by_ids($uid);
		} else {
			return $this->userInfoDao->wholeUserInfo($uid);
		}
	}
	public function get_user_by_yyid($yyid) {
		if(empty($yyid)) return array();
		$userinfo = new Dao_User_UserInfo();
                return $userinfo->get_user_by_yyid($yyid);
	}

	public function set_user_by_yyid($yyid, $data) {
		if(empty($yyid)) return array();
		$userinfo = new Dao_User_UserInfo();
                return $userinfo->set_user_by_yyid($yyid, $data);
	}

	public function get_agent_by_yyid($u_yyid) {
		if(empty($u_yyid)) return array();
		$userinfo = new Dao_User_UserInfo();
                return $userinfo->get_agent_by_yyid($u_yyid);
	}

	public function get_agent_serve_scope($u_yyid) {
		if(empty($u_yyid)) return array();
		$userinfo = new Dao_User_UserInfo();
                $rows = $userinfo->get_agent_serve_scope($u_yyid);
                foreach($rows as $k=>$r){
                   $r['h_name'] = $userinfo->get_hospital_name($r['h_yyid']);
                   $en[$k] = $r;
                }
                return $en;
	}

	public function get_agent_hospital_drugs($u_yyid, $h_yyid) {
		if(empty($u_yyid)) return array();
		$userinfo = new Dao_User_UserInfo();
                $rows = $userinfo->get_agent_hospital_drugs($u_yyid, $h_yyid);
                foreach($rows as $k=>$r){
                   $r = $userinfo->get_drug($r['d_yyid']);
                   $en[$k] = $r;
                }
                return $en;
	}

	public function get_agent_hospital_drug_active($u_yyid, $h_yyid, $d_yyid) {
		if(empty($u_yyid)) return array();
		$userinfo = new Dao_User_UserInfo();
                return $userinfo->get_agent_hospital_drug_active($u_yyid, $h_yyid, $d_yyid);
	}

	public function set_agent_by_yyid($u_yyid, $data) {
		if(empty($u_yyid)) return array();
		$userinfo = new Dao_User_UserInfo();
                return $userinfo->set_agent_by_yyid($u_yyid, $data);
	}

	public function add_agent_by_yyid($u_yyid, $data) {
		if(empty($u_yyid)) return array();
		$userinfo = new Dao_User_UserInfo();
                return $userinfo->add_agent_by_yyid($u_yyid, $data);
	}
        /* serve scope */
	public function add_serve_scope($u_yyid, $data) {
		if(empty($u_yyid)) return array();
		$userinfo = new Dao_User_UserInfo();
                return $userinfo->add_serve_scope($u_yyid, $data);
	}

	public function set_serve_scope($u_yyid, $s_yyid, $data) {
		if(empty($u_yyid) || empty($s_yyid)) return array();
		$userinfo = new Dao_User_UserInfo();
                return $userinfo->set_serve_scope($u_yyid, $s_yyid, $data);
	}

        public function get_serve_scope_list($u_yyid, $limit, $offset)
        {
		$userinfo = new Dao_User_UserInfo();
                $serve_list = $userinfo->get_serve_scope_list($u_yyid, $limit, $offset);
                if(!empty($serve_list)){
                   $bll_drug = new Bll_Drug_Info();
                   $bll_hospital = new Bll_Hospital_Info();
                   $en = array();
                   foreach($serve_list as $k=>$r){
                       $r['drug'] = isset($r['d_yyid']) ? $bll_drug->get_drug($r['d_yyid']) : array();
                       $r['hospital'] = isset($r['h_yyid']) ? $bll_hospital->get_hospital($r['h_yyid']) : array();
                       $en[$k] = $r;
                   }
                }
                return $en;
        }

        public function get_serve_scope_count($u_yyid)
        {
		$userinfo = new Dao_User_UserInfo();
                return $userinfo->get_serve_scope_count($u_yyid);
        }

        public function get_serve_scope($u_yyid, $yyid)
        {
		$userinfo = new Dao_User_UserInfo();
                $serve = $userinfo->get_serve_scope($u_yyid, $yyid);
                
                if(!empty($serve)){
                   $bll_drug = new Bll_Drug_Info();
                   $bll_hospital = new Bll_Hospital_Info();
                   $serve['drug'] = isset($serve['d_yyid']) ? $bll_drug->get_drug($serve['d_yyid']) : array();
                   $serve['hospital'] = isset($serve['h_yyid']) ? $bll_hospital->get_hospital($serve['h_yyid']) : array();
                }
                
                return $serve;
        }
        /* serve scope */

	public function verify_user_access_token($yyid, $token) {
                Logger::info(__FILE__, __CLASS__, __LINE__, "$yyid , $token");
		if(empty($yyid) || empty($token)) return false;
		//$userinfo = new Dao_User_UserInfo();
                //return $userinfo->verify_user_access_token($yyid, $token);
                return $this->userInfoDao->verify_user_access_token($yyid, $token);
	}

	public function get_user_role_id($uid) {
		return $this->userInfoDao->get_user_role_id($uid);
	}

	public function update_user_order_succ_by_uid($uid, $order_succ) {
		return $this->userInfoDao->dao_update_user_order_succ_by_uid($uid, $order_succ);
	}

	public function get_user_head_pic_by_uid($userID) {
		$result = $this->userInfoDao->get_user_head_pic_by_uid($userID);
        $result = strtr($result, array(
            'public://field/image[current-date:raw]/' => '',
            'public://' => ''
        ));
		if ($result) {
			return 'http://img1.zzkcdn.com/' . $result . '-userphotomedium.jpg';
		}
		return false;
	}

    public function get_user_head_pic_by_multi_uid($uids) {
        $data = $this->userInfoDao->get_user_head_pic_by_multi_uid($uids);
        $result = array();
        foreach($data as $row) {
            $result[$row['uid']] = strtr($row['uri'], array(
                    'public://field/image[current-date:raw]/' => 'public/',
                    'public://' => 'public/'
                ));
        }
        return $result;
    }

	public function acquire_user_list_count($email, $uid) {
		return $this->userInfoDao->acquire_user_list_count($email, $uid);
	}

	public function get_mult_uids($uid){
		return $this->userInfoDao->get_mult_uids($uid);
	}

	public function  get_parent_uid($uid)
	{
		return $this->userInfoDao->get_parent_uid($uid);
	}

	public function signin($mobile, $sms_code) {  // 短信登录不需要密码

                if(empty($mobile) || empty($sms_code)) return false;

		$rows = $this->get_sms_captcha_by_phone($mobile);
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($rows, true));
                $verify = 0 ;
                foreach ($rows as $row) {
                  if ($row['code'] == $sms_code) {
                    $verify = 1 ;
                  }
                }
                 
                if($verify) {
		   $result = $this->userInfoDao->get_user_by_phone_num($mobile);
                   Logger::info(__FILE__, __CLASS__, __LINE__, var_export($result, true));
                }

		if (isset($result['yyid']) && strlen($result['yyid']) == 32) {
                    // 返回用户登录token
                    $token = Util_Common::hash_base64(uniqid(mt_rand(), TRUE));
                    $yyid = '';
                    $uid = '0';
                    $yyid = $result['yyid']; 
                    $uid = $result['uid']; 
                    $res = array(
                        'uid' => $uid,
                        'yyid' => $yyid,
                        'sid' => $token,
                        'client_ip' => Util_NetWorkAddress::get_client_ip(),
                        'status' => 1,
                        'created' => time(),
                        'login_from' => 'wechat',
                    );
                    $sign_dao = new Dao_User_Sign();
                    $sign_dao->write_session_record($res);
                    $result['user_token'] = $token;
                    Logger::info(__FILE__, __CLASS__, __LINE__, var_export($result, true));
                    return $this->get_data_by_user($result);
		}
		return false;
	}

	public function get_user_by_uid($uid)
	{
		return $this->userInfoDao->get_user_by_uid($uid);

	}

	public function get_data_by_user($user) {
		if ($user['v_status'] == 1) {
			return $user;
		}
		return FALSE;
	}


	public function get_unreadmsg_by_uid($uid){
		return $this->userInfoDao->get_unreadmsg_count($uid);
	}

	public function get_pendingorder_by_uid($uid){
		$orderinfo=new Dao_Order_OrderInfo();
		return $orderinfo->get_pendingOrder_by_uid($uid);

	}




	public function get_collect_by_uid($uid){
        if(!$uid) return array();
		return $this->userInfoDao->get_collect_by_uid($uid);
	}

    public function group_user_collect($uid) {
        $collect = $this->get_collect_by_uid($uid);
        $room = array();
        $home = array();
        foreach($collect as $row) {
            if($row['type'] == 'h')  $home[] = $row ;
            if($row['type'] == 'r')  $room[] = $row ;
        }

        return array(
            'room' => $room,
            'home' => $home,
        );
    }

	public function insert_collect_by_uid($uid,$type,$r){
		return $this->userInfoDao->insert_collect_by_uid($uid,$type,$r);
	}

	public function update_collect_by_uid($uid,$type,$r,$status=0){
		return $this->userInfoDao->update_collect_by_uid($uid,$type,$r,$status);
	}





	public function get_user_social_info_by_uid($uid, $photo_id="-1") {
		$picid = $photo_id > "-1" ? $photo_id : $this->userInfoDao->get_user_photo_by_uid($uid);
		$social = array();
		if($picid) {
			$pic = Util_Image::zzk_db_file_managed($picid);
			if(Util_Image::img_version($picid)) {
				$piclink = Util_Image::imglink_new($pic, "userphoto.jpg");
			}else{
				$pic = str_replace("public://","",$pic);
				$piclink = Const_Host_Domain."/sites/default/files/styles/userphoto/public/".$pic;
				$piclink = Util_Image::imglink_new($piclink, "userphoto.jpg");
			}
		}else{
			$piclink = Util_Image::photo_default();
		}

		$nickname = $this->userInfoDao->get_user_nickname_by_uid($uid);

		$social['photo'] = $piclink;
		$social['nickname'] = $nickname;

		return $social;
	}






	public function get_multi_user_social_info_by_uids($uids) {
		$pics = $this->userInfoDao->get_multi_user_photo_by_uids($uids);
		$nicknames = $this->userInfoDao->get_multi_user_nickname_by_uids($uids);

		$result = array();
		foreach($pics as $v) {
			if($v['picture']) {
				$pic = Util_Image::zzk_db_file_managed($v['picture']);
				if(Util_Image::img_version($v['picture'])) {
					$piclink = Util_Image::imglink_new($pic, "userphoto.jpg");
				}else{
					$pic = str_replace("public://","",$pic);
					$piclink = Const_Host_Domain."/sites/default/files/styles/userphoto/public/".$pic;
					$piclink = Util_Image::imglink_new($piclink, "userphoto.jpg");
				}
			}else{
				$piclink = Util_Image::photo_default();
			}
			$result[$v['uid']]['photolink'] = $piclink;
		}


		foreach($nicknames as $v) {
			$result[$v['uid']]['nickname_v'] = $v['nickname'];
		}

		return $result;
	}


	public function get_waiting_for_comment_list($uid, $offSet=-1, $pageSize=10) {

		//offSet 不设置的时候  返回全部数据

		$userinfo = new Dao_User_UserInfo();
		$commentinfo = new Dao_Comment_CommentInfo();
		$result = array();
		$checkout_orderlist_byuid = $userinfo->get_checkout_orderlist_by_uid($uid);
		$user_mail = $userinfo->get_user_mail_by_uid($uid);
		//获得所有已经离店的订单 通过email
		$checkout_orderlist_byemail = $userinfo->get_checkout_orderlist_by_email($user_mail, 0);
		//获得该用户 uid 及所关联邮箱所有订单
		$checkout_orderlist = array_merge($checkout_orderlist_byuid, $checkout_orderlist_byemail);
		//
		$comment_ridlist = $commentinfo->get_ridlist_by_uid($uid);
		$comment_ridlist = array_column($comment_ridlist, 'rid');
		//获取所有已经点评过的订单 通过id记录
	//	$new_comment_order = $this->userInfoDao->get_comment_list($uid);

	//	$comment_order_ids = array_column($new_comment_order, 'id');


	 		//获得所有通过hash_id 点评的hash_id
		//$comment_order_hash_ids=$this->userInfoDao->get_comment_hash_ids($uid);


		$comment_order_idlist=$commentinfo->get_orderids_by_uid($uid);

//		if($comment_order_ids){
//			$comment_order_ids=array_column($comment_order_ids,'order_id');
//		}
		foreach($comment_order_idlist as $v){
			$comment_order_ids[]=$v['order_id'];
		}

		foreach ($checkout_orderlist as $k => $v) {

			if (in_array($v['id'], $comment_order_ids) || in_array($v['hash_id'], $comment_order_ids)) {
				unset($checkout_orderlist[$k]);
				continue;
			}

			if ($_REQUEST['multiprice'] == 10) {
				$v['total_price'] = $v['total_price_tw'];
			}
			if (($key = array_search($v['nid'], $comment_ridlist)) !== FALSE) {
				unset($checkout_orderlist[$k]);
				unset($comment_ridlist[$key]);
			}
			else {
				$v['room']['images'] = Util_Image::getroomimages($v['nid']);
				$v['room']['image'] = Util_Image::getroomsmallimage( $v['room']['images']);
				if (!empty($v['hash_id'])) {
					$v['id'] = $v['hash_id'];
				}
				$result[] = $v;
			}

		}


		if($offSet==-1)return $result;


		return array_slice($result,$offSet,$pageSize);



	}


	public function close_push($deviceid,$baiduid)
	{
		$push= $this->userInfoDao->close_push($deviceid);
		$baidupush= $this->userInfoDao->close_baidupush($baiduid);
		if($push||$baidupush)return true;
		return false;
	}


	public function user_register($user) {
                // create user
                if(empty($user)) return false;
		$user_dao = new Dao_User_UserInfo();
                return $user_dao->user_register($user);
	}

	public function send_reset_password_mail($email) {
		$user_dao = new Dao_User_UserInfo();
		$user = $user_dao->get_user_info_by_email($email);

		if (empty($user)) {
			return FALSE;
		}

		$subject = "Replacement login information for [user:name] at [site:name]";
		$body = "[user:name],

A request to reset the password for your account has been made at [site:name].

You may now log in by clicking this link or copying and pasting it to your browser:

[user:one-time-login-url]

This link can only be used once to log in and will lead you to a page where you can set your password. It expires after one day and nothing will happen if it's not used.

--  [site:name] team";


		$l10n_bll = new Bll_L10n_Language();
		$subject = $l10n_bll->translate($subject);

		$body = $l10n_bll->translate($body);

		//todo:动态读取site:name
		$replacements['[site:name]'] = '自在客';
		$replacements['[user:name]'] = $user['name'];
		$replacements['[user:one-time-login-url]'] = $this->user_pass_reset_url($user);

		$subject = strtr($subject,$replacements);
		$body = strtr($body,$replacements);
		$mail = new Dao_Mail_Queue();
		return $mail->insert_queue(array(
			'to' => $email,
			'subject' => $subject,
			'body' => nl2br($body)
		));
	}

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function reset_password_by_phone($user_id, $dest_id, $phone_num) {
        #$token = $this->userInfoDao->get_usertoken_by_userid($user_id, null, "phone");
        #if($token) {
        #    $token = $token[sizeof($token) - 1]['auth_token'];
        #} else {
        #    $token = sprintf("%06d", mt_rand(1, 999999));
        #    $this->userInfoDao->add_usertoken($user_id, 1, $token, "phone", date('Y-m-d H:i:s', time()+86400));
        #}
        $token = sprintf("%06d", mt_rand(1, 999999));
        $this->userInfoDao->add_usertoken($user_id, 1, $token, "phone", date('Y-m-d H:i:s', time()+86400));
        $content = Trans::t('verify_code', $dest_id) . $token . Trans::t('verify_code_content_key2', $dest_id);
        $area = $dest_id == 10 ? 2 : 1;
		if($phone_num[0] == 1){
			$area = 1;
		}
        $params = array(
            'oid' => 0,
            'sid' => 0,
            'uid' => $user_id,
            'mobile' => $phone_num,
            'content' => $content,
            'area' => $area,
        );
        $sms = new Util_Notify();
        $result = $sms->send_sms_notify($params);
		if($result){
			return true;
		}else{
			return false;
		}
    }

    public function reset_password_by_email($user_id ,$email) {
        $token = $this->userInfoDao->get_usertoken_by_userid($user_id, null, "email");
        if($token) {
            $token_id = $token[sizeof($token) - 1]['id'];
            $token = $token[sizeof($token) - 1]['auth_token'];
        } else {
            $token = self::generateRandomString(20);
            $token_id = $this->userInfoDao->add_usertoken($user_id, 1, $token, "email", date('Y-m-d H:i:s', time()+86400));
        }
        $result = Util_Curl::http_get_data(Util_Common::url('', 'api') . "/m/send?user_id=" . $user_id . "&action=reset_password&token_id=" . $token_id . "&send=true");
		$result = json_decode($result,true);
		if($result['status'])
		return true;
		else return false;
	}

    public function validate_by_token($user_id, $token, $token_type=null) {
        $token_info = $this->userInfoDao->get_usertoken_by_userid($user_id, $token, $token_type);
        return (bool)$token_info;
    }

    public function get_token_by_id($id) {
        return $this->userInfoDao->get_usertoken_by_id($id);
    }

	private function user_pass_reset_url($user) {
		//todo:修改为根据地区决定域名
		$domain = 'http://taiwan.youyao.com/';
		$timestamp = REQUEST_TIME;
		return $domain.'user/reset/' . $user['uid'] . '/' . $timestamp . '/' . self::user_pass_rehash($user['pass'], $timestamp, $user['login']);
	}

	public static function user_pass_rehash($password, $timestamp, $login) {
		$drupal_hash_salt = APF::get_instance()->get_config('drupal_hash_salt');
		if(empty($drupal_hash_salt)){
			throw new Exception('hash slat can\'t be empty');
		}
		$data = $timestamp . $login;
		$key = $drupal_hash_salt . $password;
		$hmac = base64_encode(hash_hmac('sha256', $data, $key, TRUE));
		return strtr($hmac, array('+' => '-', '/' => '_', '=' => ''));
	}

	public function update_user_login_timestamp($uid, $timestamp) {
		return $this->userInfoDao->update_user_login_timestamp($uid, $timestamp);
	}

	public function get_sms_captcha_by_phone($phone) {
		if(empty($phone)) return;
		return $this->userInfoDao->get_sms_captcha_by_phone($phone);
	}

	public function get_sms_captcha_by_code($code) {
		return $this->userInfoDao->get_sms_captcha_by_code($code);
	}

    public function verify_sms_captcha($phone, $code) {
        //$userbll = new Bll_User_UserInfo();
        $codelist = $this->get_sms_captcha_by_phone($phone);
        $sms_verified = false;
        foreach ($codelist as $row) {
            if ($row['code'] == $code) {
                $sms_verified = true;
                break;
            }
        }

        return $sms_verified;
    }

	public function insert_sms_captcha($phone) {
		$code = substr(str_pad(base_convert(sha1(uniqid(rand().$phone)),36,10), 6 , 0, STR_PAD_LEFT), 0, 6);
		$expire = APF::get_instance()->get_config("phone_captcha_expired");
                $timestamp = time();
                $res = array(
                    'id' => 0,
                    'phone_num' => $phone,
                    'code' => $code,
                    'expired' => $timestamp + $expire,
                    'create_time' => $timestamp,
                    'status' => 1,
                );
		try{
			$this->userInfoDao->insert_sms_captcha($res);
			return $code;
		}catch(Exception $e){
			return;
		}
	}

	public function send_account_verify_mail($account) {

		if(empty($account['uid'])) return;
		$account = self::get_whole_user_info($account['uid']);
		if(strpos($account['mail'], 'zzkzzk') || empty($account['mail'])) return;

		$timestamp = time();
		$urlhash = Util_Common::user_pass_rehash($account['pass'], $timestamp, $account['login']);

		$url = "http://taiwan.youyao.com/user/registrationpassword/".$account['uid']."/$timestamp/$urlhash";

		$mailBody = Trans::t('register_body', $account['dest_id']);
		$subjectBody = Trans::t('register_subject', $account['dest_id']);
		$find =  array('[user:name]', '[user:registrationpassword-url]', "\n");
		$replace = array($account['name'], $url, "<br/>");
		$body = str_replace($find, $replace, $mailBody);
		$subject = str_replace($find, $replace, $subjectBody);
		$to = $account['mail'];
		$from = 'noreply@youyao.com';
		Util_SmtpMail::send($to, $subject, $body, $from);
	}
    public function send_account_verify_mail_new($mail,$pass,$fcode=null) {
        $timestamp = time();
        $urlhash = Util_Common::user_pass_rehash($mail, $timestamp, null);
        $pass = So_NiceEncryption::encrypt($pass,$mail);
        $this->userInfoDao->set_mail_register_verify($mail,$urlhash,'0',$pass);
        $url = "http://taiwan.youyao.com/v2/register/user/mail?verify=$urlhash";
        if($fcode) {
            $url .= "&fcode=$fcode";
        }
        $mailBody = Trans::t('register_body');
        $subjectBody = Trans::t('register_subject');
        $find =  array('[user:name]', '[user:registrationpassword-url]', "\n");
        $replace = array($mail, $url, "<br/>");
        $body = str_replace($find, $replace, $mailBody);
        $subject = str_replace($find, $replace, $subjectBody);
        $to = $mail;
        $from = 'noreply@youyao.com';
        Util_SmtpMail::send($to, $subject, $body, $from);
        return $urlhash;
    }

    public function bind_account_mail($mail, $uid) {
        $timestamp = time();
        $urlhash = Util_Common::user_pass_rehash($mail, $timestamp, null);
        $this->userInfoDao->set_mail_register_verify($mail,$urlhash,'0',"xx",$uid);
        $url = Util_Common::url("/v2/user/bindMail?verify=$urlhash");
        $mailBody = Trans::t('register_body');
        $subjectBody = Trans::t('register_subject');
        $find =  array('[user:name]', '[user:registrationpassword-url]', "\n");
        $replace = array($mail, $url, "<br/>");
        $body = str_replace($find, $replace, $mailBody);
        $subject = str_replace($find, $replace, $subjectBody);
        $to = $mail;
        $from = 'noreply@youyao.com';
        Util_SmtpMail::send($to, $subject, $body, $from);
        return $urlhash;
    }

    public function mail_register_by_uid($uid) {
        return $this->userInfoDao->mail_register_by_uid($uid);
    }

    public function change_mail_by_uid($email, $uid) {
        return $this->userInfoDao->change_mail_by_uid($email, $uid);
    }

	public function get_user_column() { // 获得user表的field 
		return $this->userInfoDao->get_user_column();
	}

	public function update_user_info($params) {
		if(empty($params)) return;
		if($params['pass']) {
			$pass = new bll_User_Password();
			$params['pass'] = $pass->user_hash_password($params['pass'], 15);
		}
		return $this->userInfoDao->update_user_info($params);
	}

	public function update_multi_user_info($params, $uids) {
		if(empty($params)) return;
		$params['uid'] = $uids;
		return $this->userInfoDao->update_user_info($params);
	}

	public function insert_user_info($account) {
		$dao_user = new Dao_User_UserInfo();
		$account['uid'] = $dao_user->next_user_id();
		$insert_data=array(
			'uid'=>$account['uid'],
			'name'=>$account['name'],
			'phone_num'=>($account['phone_num']?$account['phone_num']:0),
			'mail'=>$account['mail'],
			'pass'=>$account['pass'],
			'signature_format'=>$account['signature_format'],
			'status'=>$account['status'],
			'timezone'=>$account['timezone'],
			'init'=>$account['init'],
			'created'=>$account['created'],
			'fund'=>$account['fund'],
			'dest_id'=>$account['dest_id']?$account['dest_id']:10,
			'zfansref'=>'',
			'send_sms_telnum' => empty($account['mobile_number'])?'':$account['mobile_number'],
            'register_source' => '',
		);
		$success = $dao_user->insert_user($insert_data);

		return $account['uid'];
	}

	/**
	 * 	后面自制的蛋疼权限系统
	 *  原作者不是我
	 *  Type Parameters :
	 * 			administrator_can_orders         // 订单权限
	 * 			administrator_can_tech           // 技术人员
	 *			administrator_can_count          // 查看交易统计权限
	 *			zzk_sales_mapping                // 销售
	 *			zzk_sales_customer_mapping       // 不知道和上面有什么区别
	 *			administrator_can_hompage_edit   // 首页编辑 目前只有图片编辑用到了
	**/
	public function zzk_roles_config($uid, $type){
		$data = $this->userInfoDao->get_zzk_roles_config();
		$config = array();
		foreach($data as $row) {
			$config[$row['arr_key']] = json_decode($row['arr_value'], true);
		}

		if(!$type || !$uid) {
			return $config;
		}

		if(in_array($uid, $config[$type])) 
			return true;
		else
			return false;
	}

	/**
	 *  入参
	 *  当type为zzk_sales_mapping时           data为数组 例如 array('12903'=>'韩海燕 [G]' )
	 *  当type为zzk_sales_customer_mapping时  data为数组 例如 array('A'=>'郑婷婷 [A]')
	 *  其他情况data均为uid
	 */
	public function add_roles_row($data, $type) {
		$data = is_array($data) ? $data : array($data);
		$orign = self::zzk_roles_config();
		$orign = $orign[$type];
		if($type=="zzk_sales_mapping"){
			$result = $orign + $data;
		}else{
			$result = array_merge($orign, $data);
		}
		$json = json_encode($result);
		return $this->userInfoDao->update_zzk_roles_config($json, $type);
	}
	
	/*
	 * 当type为zzk_sales_customer_mapping $data 为组名 et. A , B 
	 * 其他情况为uid
     */
	public function remove_roles_row($data, $type) {
		$orign = self::zzk_roles_config(null, $type);
		$orign = $orign[$type];
		if($type == 'zzk_sales_mapping' || $type == 'zzk_sales_customer_mapping'){
			unset($orign[$data]);
			$result = $orign;
		} else {
			$result = array_splice($orign, array_search($data, $orign), 1);
		}
		return $this->userInfoDao->update_roles(json_encode($result), $type);
	}

    public function insert_nickname($uid, $nickname) {
        return $this->userInfoDao->insert_nickname($uid, $nickname);
    }

	public function insert_or_update_nickname($uid,$nickname){
		$origin=$this->userInfoDao->get_nickname_field_by_uid($uid);
		if($origin){
			return $this->userInfoDao->update_nickname($uid,$nickname);
		}else{
			return $this->userInfoDao->insert_nickname($uid,$nickname);
		}
	}

	public function insert_role_uid($uid, $rid) {
		$this->userInfoDao->insert_role_uid($uid, $rid);
	}

    public function get_user_jiaotongtu($uid) {
        return $this->userInfoDao->get_user_jiaotongtu($uid);
    }

    public function get_user_list($filter=array(), $sort=array(), $page=1, $page_size=100) {
        $limit = $page_size;
        $offset = ($page-1) * $page_size;
        return $this->userInfoDao->get_user_summary($filter, $sort, $limit, $offset);
    }

	public function get_user_nickname_by_uid($uid){
		$name = $this->userInfoDao->get_user_nickname_by_uid($uid);
        if($name) return $name;
        else return null;
	}
    
    public function get_nickname_by_multi_uid($uids) {
        if(empty($uids)) return;
        $data = $this->userInfoDao->get_user_nickname_by_multi_uid($uids);
        $result = array();
        foreach($data as $row) {
            $result[$row['uid']] = $row['nickname'];
        }
        $diff = array_diff($uids, array_flip($result));
        if(!empty($diff)) {
            $user_name = $this->userInfoDao->get_user_name_by_multi_uid($diff);
            foreach($user_name as $row) {
                $result[$row['uid']] = $row['name'];
            }
        }
        return $result;
    }

    public function get_username_by_uid($uid) {
        return $this->userInfoDao->get_username_by_uid($uid);
    }

    public function get_user_dest_id($uid) {
        if(empty($uid)) return;
        return $this->userInfoDao->get_user_dest_id($uid);
    }

    public function insert_t_img_manage($uri, $uid) {
        $result = $this->userInfoDao->get_t_img_managed_by_uri($uri);
        if($result) {
            return $result;
        } else {
            return $this->userInfoDao->insert_t_img_manage($uri, $uid);
        }
    }

    public function change_user_gender($uid, $gender) {
        if(!$uid || !$gender) return;
        return $this->userInfoDao->change_user_gender($uid, $gender);
    }

    public function get_sns_data_by_uid($uid) {
        if(!$uid) return;
        return $this->userInfoDao->get_sns_data_by_uid($uid);
    }

    public function user_profile_completion($uid, $userInfo=array()) {
        if(empty($userInfo)) {
            $bll_user = new Bll_User_UserInfo();
            $userInfo = $bll_user->get_whole_user_info($uid);
        }
        $k = 1; // 昵称都有默认的
        $all = 10;
        if($userInfo['picture']) $k++;
        if($userInfo['gender']) $k++;
        if($userInfo['birthday']) $k++;
        if($userInfo['mail'] && !preg_match('/@zzkzzk.com$/', $userInfo['mail'])) $k++;
        if($userInfo['phone_num']) $k++;
        if($userInfo['city']) $k++;
        if($userInfo['work']) $k++;
        if($userInfo['education']) $k++;
        if($userInfo['budget']) $k++;

        return round($k/$all, 2);
    }

    // 获得银行名称，账号
    public function bank_base_account($uid, $userInfo=null) {
        if(!$uid) return ;
        if(empty($userInfo)) {
            $home_bll = new Bll_Homestay_StayInfo();
            $userInfo = $home_bll->get_whole_stay_info_by_id($uid);
        }

        if($userInfo['bank_type_use'] == 1) {
            $area_bll = new Bll_Area_Area();
            $dest_info = $area_bll->get_dest_config_by_destid($userInfo['dest_id']);
            $bank_name = Trans::t('bank_%b', null, array('%b' => Trans::t($dest_info['domain']) ) );
            $account = $userInfo['blank_account'];
        }
        elseif ($userInfo['bank_type_use'] == 2){
            $bank_name = Trans::t('bank_%b', null, array('%b' => Trans::t('zh') ) );
            $account = $userInfo['cn_blank_account'];
        }
        elseif ($userInfo['bank_type_use'] == 3){
            $bank_name = Trans::t('Alipay');
            $account = $userInfo['alipay_account'];
        }
        elseif ($userInfo['bank_type_use'] == 4){
            $bank_name = Trans::t('homestaypayaltitle');
            $account = $userInfo['paypal_account'];
        }

        return array(
            'bank_name' => $bank_name,
            'bank_account' => $account,
        );
    }

    // 获得中间隐藏的银行信息
    public function shade_bank_info($uid, $bank_name=null, $bank_account=null) {
        if(!$uid) return;
        if($bank_name === null || $bank_account === null) {
            $bank_info = $this->bank_base_account($uid);
            if(!$bank_name) $bank_name = $bank_info['bank_name'];
            if(!$bank_account) $bank_account = $bank_info['bank_account'];
        }
        $shade_num = ceil(strlen($bank_account) / 2);
        $bank_shade = $bank_name . " " 
            . mb_substr($bank_account, 0 , ceil($shade_num/2))
            . str_pad('', $shade_num, '*')
            . mb_substr($bank_account, -ceil($shade_num/2));

        return $bank_shade;
    }

}
