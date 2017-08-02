<?php
class Util_Signin {

    static public $user;

    function anonymous_user() {
        if($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $user = new stdClass();
        $user->uid = 0;
        $user->hostname = $ip;
        $user->roles = array();
        $user->roles[1] = 'anonymous user';
        $user->cache = 0;
        return $user;
    }

	public function get_user()
	{
        // 用户在session_initialize()的时候给$user赋值
        if(!empty(self::$user)) {
            return self::$user;
        }

        return self::anonymous_user();

		$curl = curl_init();
                $ajaxdata = array(
                        'ajax_type' => 'get_user'
                );
                foreach($_COOKIE as $k=>$v){
                        $cookie_array[] =  $k."=".trim($v);
                }
                $cookie_data = implode(";", $cookie_array);
                $hostname = "taiwan.youyao.com";
//                if (preg_match('/(dev|test)/', $_SERVER['HTTP_HOST'])) {
//					$url = "http://$hostname/zzk_taiwan_ajax.php";
//                }else{
					$url = Util_Common::url("/zzk_taiwan_ajax.php","taiwan");
//				}
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $ajaxdata);
                curl_setopt($curl, CURLOPT_HEADER, 0);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_VERBOSE, 1);
                curl_setopt($curl, CURLOPT_COOKIE, $cookie_data);
                $user = json_decode(curl_exec($curl));
                curl_close($curl);
                $roles = $user->roles;
                $user->roles = array();
                foreach($roles as $k=>$v){
                    $user->roles[$k] = $v;
                }
		return $user;
	}

    public static function login($name,$oid,$pre_url,$nickname,$message='')
    {
        if(empty($pre_url))
        $pre_url = "http://www.youyao.com";
        $url = Util_Common::url('/login/thirdlogin.php','taiwan',null,array('name'=>$name,'pre_url'=>$pre_url,'nickname'=>$nickname,'message'=>$message));
        setcookie('third_login', 1, time()+3600, "/", 'youyao.com', 0, true);
        header("Location: $url");
        //确保重定向后，后续代码不会被执行
        exit;
    }

}
