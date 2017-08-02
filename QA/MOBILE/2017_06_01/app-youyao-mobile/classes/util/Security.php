<?php

/**
 * Created by PhpStorm.
 * User: chanlevel
 * Date: 15/8/4
 * Time: 下午4:17
 */
class Util_Security {
	//static $APIKEY = "6F86727E527411E79E6C68F728954D54188D51B5534511E79E6C68F728954D54";
        static $APIKEY = WECHAT_SECURITY_APIKEY;

	/**
	 *
	 * 将接收到的所有的参数排序，拼接APIKEY，再通过两次MD5加密，与header中的sig比对
	 *
	 *
	 * @param $params
	 * @return bool
	 */
	public static function  Security($params) {
                #echo json_encode($_SERVER);
                #exit;
		#if (isset($_SERVER['HTTP_SIG'])) {
                #   return TRUE;
		#}
		if (empty($params)) {
                   return FALSE;
		}

		$os = $params['os'];
                if($os == 'wechat'){
                   //return TRUE;
                }
		$version = $params['version'];
		ksort($params);
		$str = "";
		foreach ($params as $key => $value) {
                    $value = stripslashes($value);
		    $str = $str . $key . '=' . $value . '&';
		}
		$str = $str . self::$APIKEY;
		$sig = md5(md5($str));

		$gotsig = $_SERVER['HTTP_SIG'];
                #echo json_encode(array('$gotsig'=>$gotsig));
                #exit;

		if ($sig != $gotsig) {
			return FALSE;
		}

		return TRUE;

	}
}
