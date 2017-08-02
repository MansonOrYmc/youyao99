<?php
apf_require_class("APF_Controller");

class User_ServeListController extends APF_Controller
{

    public function handle_request()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
        header("Access-Control-Allow-Headers:SIG,DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");

        header("Content-type: application/json; charset=utf-8");
        
        $req = APF::get_instance()->get_request();
        $params = $req->get_parameters();

        $page_num = 0;
        $page_size = 10;

        if (isset($params['page']) && is_numeric($params['page'])) {
           $page_num = intval($params['page']);
        }
        $page_num = $page_num <= 0 ? 1 : $page_num;
        $page_start = ($page_num - 1) * $page_size;
        $total = 0;

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
            'u_yyid' => $yyid,
            's_yyid' => $s_yyid,
        );

        /* */
        $msg = "normal request";
        $list = array();

        $bll_user = new Bll_User_UserInfo();
        if($bll_user->verify_user_access_token($yyid, $token)){
            $list = $bll_user->get_serve_scope_list($yyid, $page_size, $page_start);
            $total = $bll_user->get_serve_scope_count($yyid);
            $msg = "list success";
            $msg1 = "Successfully_modified";
        }
        else{
            $msg = "ACCESS DENIED";
            $msg1 = "ACCESS_DENIED";
        }

        $data = array(
            'code' => 1,
            'codeMsg' => 'normal_request',
            "page_num" => $page_num,
            "page_size" => $page_size,
            "total" => $total,
            "action" => 'list',
            "serve_list" => $list,
        );
        
        Util_Json::render(200, null, $msg, $data);

        return ;
    }
}
