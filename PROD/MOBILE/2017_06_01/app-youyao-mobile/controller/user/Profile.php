<?php
apf_require_class("APF_Controller");

class User_ProfileController extends APF_Controller
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
            Util_Json::render(400, null, "request forbidden", 'llegal_request');
            return false;
        }

        $yyid = $params['yyid'];
        $token = $params['user_token'];
        $base_info  = array();
        $agent_info = array();
        $msg = "normal request";

        $bll_user = new Bll_User_UserInfo();
        if($bll_user->verify_user_access_token($yyid, $token)){
            $base_info = $bll_user->get_user_by_yyid($yyid);
            //$row['photo'] = IMG_CDN_USER . strtolower($row['photo'])."/headpic.jpg";
            if(isset($base_info['picture']) && strlen($base_info['picture']) == 32){
              $base_info['picture_url'] = IMG_CDN_USER .'/'. strtolower($base_info['picture'])."/headpic.jpg";
            }
            $agent_info = $bll_user->get_agent_by_yyid($yyid);
            $serve_hospital_drugs = $bll_user->get_agent_serve_scope($yyid);
            
        }
        else{
            $msg = "ACCESS DENIED";
        }

        $data = array('base_info'=> $base_info , 'agent_info' => $agent_info, 'serve_hospital_drugs'=>$serve_hospital_drugs);
        $response = array(
                  'status' => 200,
                  'data' => $data,
                  'userMsg' => $msg,
                  'msg' => $msg,
              );

        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($response, true));
        Util_Json::render(200, $data, $msg,$msg);

        return false;
    }
}

