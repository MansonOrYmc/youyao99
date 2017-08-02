<?php
apf_require_class("APF_Controller");

class User_ProfileVerifyController extends APF_Controller
{

    public function handle_request()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
        header("Access-Control-Allow-Headers:SIG,DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");

        header("Content-type: application/json; charset=utf-8");
        
        $req = APF::get_instance()->get_request();
        $params = $req->get_parameters();
        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($params, true));
        $security = Util_Security::Security($params);
        if (!$security) {
            Util_Json::render(400, null, "request forbidden", 'llegal_request');
            return false;
        }

        if($params['action'] == 'send_smscode') {
            // @phonenum
            // @areanum
            $register = new User_RegisterController();
            $register->phoneVerify();
            return;
        }

        if($params['action'] == 'code_verify') {
            // @phonenum
            // @code
            // @areanum
            $register = new User_RegisterController();
            $register->codeVerify();
            return;
        }

        if($params['action'] == 'register') {
            // @phonenum
            // @code
            // @areanum
            $register = new User_RegisterController();
            $register->submit();
            return;
        }

        if($params['action'] == 'login') {
            // @phonenum
            // @code
            // @areanum
            $register = new User_RegisterController();
            $register->signin();
            return;
        }
    }
}
