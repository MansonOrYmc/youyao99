<?php

class AuthInterceptor extends APF_Interceptor {

    public function before () {
        $ret = parent::before();
        if ($ret != self::STEP_CONTINUE) {
            return $ret;
        }
        $req = APF::get_instance()->get_request();
        $params = $req->get_parameters();
        if(!self::auth_user($params)) return self::STEP_EXIT;
        self::set_language_currency($params);
        return self::STEP_CONTINUE;
    }

    public function auth_user() {
        $apf = APF::get_instance();
        $req = APF::get_instance()->get_request();
        $params = $req->get_parameters();

        if($params['auth_type'] == "cookie") {
            $user = $req->get_userobject();
            $home_bll = new Bll_Homestay_StayInfo();
            $room_bll = new Bll_Room_RoomInfo();
            if($params['nid']){ 
                $uid = $room_bll->get_room_uid_by_nid($params['nid']);
            } elseif($params['rid']){ 
                $uid = $room_bll->get_room_uid_by_nid($params['rid']);
            } elseif($params['me']) {
                $uid = $params['me'];
            }else{
                $uid = $params['uid'];
            }
            if(
                !$user->roles[3] &&
                !($user->uid == $uid) &&
                !($user->roles[5] && $home_bll->verify_is_branch($user->uid, $uid))
            ) {
                echo "Authentication failure";
                return FALSE;
            }

        } elseif($params['auth_type'] == "internal") {
            if(!$params['auth_key'] == "9371d966ed8749fd959b8dfed2de7f") {
                echo "Authentication failure";
                return FALSE;
            }
        } elseif($params['os']) {
            if(!Util_Security::Security($params)) return FALSE;
        } else {
            echo "Authentication failure";
            return FALSE;
        }

        return TRUE;

    }

    public function set_language_currency($params) {

        if($params['multilang']) {
            Util_Language::set_locale_id($params['multilang']);
        }
        if($params['multiprice']) {
            Util_Currency::set_cy_id($params['multiprice']);
        }
        return TRUE;
    }
}
