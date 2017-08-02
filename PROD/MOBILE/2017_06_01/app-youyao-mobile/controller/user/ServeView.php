<?php
apf_require_class("APF_Controller");

class User_ServeViewController extends APF_Controller
{

    public function handle_request()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
        header("Access-Control-Allow-Headers:SIG,DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");

        header("Content-type: application/json; charset=utf-8");
        
        $req = APF::get_instance()->get_request();
        $params = $req->get_parameters();
        $security = Util_Security::Security($params);
        if (!$security) {
            Util_Json::render(400, null, 'request forbidden', 'Illegal_request');
            return false;
        }
        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($params, true));

        $yyid = isset($params['yyid']) ? $params['yyid'] : '';  // user's yyid
        $token = isset($params['user_token']) ? $params['user_token'] : '';
        /* */
        $timestamp = time();

        $s_yyid = isset($params['s_yyid']) ? $params['s_yyid'] : ''; //t_serve_scope's yyid


        $res = array(
            'yyid' => $yyid,
            's_yyid' => $s_yyid,
        );

        /* */
        $msg = "normal request";
        $data = array();

        $bll_user = new Bll_User_UserInfo();
        if($bll_user->verify_user_access_token($yyid, $token)){
            if(empty($s_yyid)){
               //add
               $res['s_yyid'] = '';
            }
            else{
               //update
               /*
               $bll_user->set_serve_scope($yyid, $s_yyid, $res);
               $bll_user->get_serve_scope_list($u_yyid, $limit, $offset);
               $bll_user->get_serve_scope_count($u_yyid);
               */
               $data = $bll_user->get_serve_scope($yyid, $s_yyid);
            }
            $msg = "view success";
            $msg1 = "Successfully_modified";
        }
        else{
            $msg = "ACCESS DENIED";
            $msg1 = "ACCESS_DENIED";
        }

        Util_Json::render(200, null, $msg, $data);

        return ;
    }
}
