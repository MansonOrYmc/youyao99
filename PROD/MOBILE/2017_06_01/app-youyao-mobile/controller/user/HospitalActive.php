<?php
apf_require_class("APF_Controller");

class User_HospitalActiveController extends APF_Controller
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
        $h_yyid = $params['h_yyid'];
        if ( isset($h_yyid) && strlen($h_yyid) == 32 ) {
                   $h_yyid = strtoupper($h_yyid);
        } else {
                   $h_yyid = '';
        }
        $d_yyid = $params['d_yyid'];
        if ( isset($d_yyid) && strlen($d_yyid) == 32 ) {
                   $d_yyid = strtoupper($d_yyid);
        } else {
                   $d_yyid = '';
        }
        $hospital = array();

        $yyid = $params['yyid'];
        $token = $params['user_token'];

        $msg = "normal request";
        $hospital_active = 1;

        $bll_user = new Bll_User_UserInfo();
        if($bll_user->verify_user_access_token($yyid, $token)){
            //hospital
            //$bll_hospital = new Bll_Hospital_Info();
            //$hospital = $bll_hospital->get_hospital($h_yyid);
            $c = $bll_user->get_agent_hospital_drug_active($yyid, $h_yyid, $d_yyid);
            if($c){
               $hospital_active = 0;
            }
        }
        else{
            $msg = "ACCESS DENIED";
        }

        $data = array('hospital_active'=> $hospital_active, );
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

