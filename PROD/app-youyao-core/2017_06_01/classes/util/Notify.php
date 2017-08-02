<?php
class Util_Notify {

    //发短信队列 
	public static function send_sms_notify($notify) {
  		$dest_id = $notify['dest_id']?$notify['dest_id']:10;

  		$info = array(
  				$notify['oid'],
  				$notify['sid'],
  				$notify['uid'],
  				$notify['mobile'],
  				$notify['content'],
  				$notify['area'],
  				$dest_id,
  				0,
  				0,
  				REQUEST_TIME
  			);

  		$bll_sms = new Bll_Sms_SMSInfo();
  		$bll_sms->bll_send_sms_notify($info);
  		return 1;
	}

  //手机push提醒 // 废弃
  public static function send_mobile_notify($email, $msg) {
    $bll_push = new Bll_Push_PushInfo();
    $bll_push->bll_send_mobile_notify($email, $msg);
    return 1;
  }

    // 手机push 单条
	public static function push_message_client($email,$mobile,$guid,$message,$mtype,$value){
		$api_domain = APF::get_instance()->get_config('api_domain');
		$url=$api_domain."/push/send?email=".urlencode($email)."&guid=".urlencode($guid)."&phone=".$mobile."&message=".urlencode($message)."&pvalue=".$value."&mtype=".$mtype;
		return Util_Curl::get($url);
	}

    // 手机push 多个用户
    public static function push_message_to_multiple_client($uids, $message, $mtype, $value) {
        $params = array(
            'multi_uids' => $uids,
            'message'    => $message,
            'mtype'      => $mtype,
            'pvalue'     => $value,
        );

        $url = Util_Common::url('/push/send', 'api');
        Util_Curl::post($url, $params);
        return;
    }

	public static function get_push_mtype($str){
		$mtype_config = self::get_mtype_config();
		$ptype = 0;
		if($mtype_config[$str]){
			$ptype = $mtype_config[$str];
		}
		return $ptype;
	}

	private static function get_mtype_config(){
        $mtype_config = array(
            'guest_order' => 1,
            'admin_order' => 2,
            'homestay'    => 3,
            'guest_psms'  => 4,
            'admin_psms'  => 5, 
            'msg_detail'  => 6, 
        );
		return $mtype_config;
	}
	public static function get_mtype_ref($kv){
		$mtype_config = self::get_mtype_config();
		$mtype_str = 'guest_order';
		foreach($mtype_config as $key=>$value){
            if($value == $kv){
				$mtype_str = $key;
			}
		}

		return $mtype_str;
	}

}
?>
