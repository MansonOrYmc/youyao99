<?php

class UserTokenInterceptor extends APF_Interceptor {

    public function before () {
        $ret = parent::before();
        if ($ret != self::STEP_CONTINUE) {
            return $ret;
        }
        $req = APF::get_instance()->get_request();
        $params = $req->get_parameters();
        if(!self::auth_token($params)) return self::STEP_EXIT;
        return self::STEP_CONTINUE;
    }

    public function auth_token($params) {
		header("Content-type: application/json");
        if($params['auth_type'] == "internal") {
            if(!$params['auth_key'] == "9371d966ed8749fd959b8dfed2de7f") {
                echo "Authentication failure";
                return FALSE;
            }
        } elseif($params['user_token']) {
            $sign_dao = new Dao_User_Sign();
            $auth_info = $sign_dao->get_record_by_sid($params['user_token']);
            // 因为手机端还会带 mobile_userid 所以会和mobile_userid比较，之后会直接吧user_token作为用户
            if(empty($auth_info['uid']) || $auth_info['uid'] != $params['mobile_userid'] || $auth_info['timestamp'] < strtotime("-1 years")) {
                Util_Json::render(458,null,"error token","登录状态过期，请重新登录");
                return false;
            }else {
                Util_MobileUser::set_user($auth_info);
            }
        }else{
//            Util_Json::render(458,null,"error token","登录状态过期，请重新登录");
//            return false;
        }
        return true;
    }

}
