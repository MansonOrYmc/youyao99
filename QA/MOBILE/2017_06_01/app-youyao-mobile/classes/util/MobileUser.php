<?php

class Util_MobileUser {

    public static $user;

    public function set_user($user) {
        self::$user = $user;
    }

    public function get_user() {
        if(self::$user) {
            $user = self::$user;
        }
        else{
            $req = APF::get_instance()->get_request();
            $params = $req->get_parameters();
            if($params['user_token']) {
                $sign_dao = new Dao_User_Sign();
                $auth_info = $sign_dao->get_record_by_sid($params['user_token']);
                if($auth_info['uid']) {
                    $user = $auth_info['uid'];
                    self::$user = $user;
                }
            }
            elseif($params['mobile_userid']) {
                $user['uid'] = $params['mobile_userid'];
                self::$user['uid'] = $params['mobile_userid'];
            }
        }
        if(!$user['uid']) {
            return self::anonymous_user();
        }
    }

    public function anonymous_user() {
        $user['uid'] = 0;
        $user['hostname'] = Util_NetWorkAddress::get_client_ip();
        $user['roles'] = array();
        $user['roles'][1] = 'anonymous user';
        return $user;
    }

}
