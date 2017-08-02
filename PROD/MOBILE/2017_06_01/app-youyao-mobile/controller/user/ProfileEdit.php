<?php
apf_require_class("APF_Controller");

class User_ProfileEditController extends APF_Controller
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

        $yyid = $params['yyid'];
        $token = $params['user_token'];
        /* */
        $name = isset($params['name']) ? $params['name'] : '';
        $mail = isset($params['mail']) ? $params['mail'] : '';
        $wechat = isset($params['wechat']) ? $params['wechat'] : '';
        $weibo = isset($params['weibo']) ? $params['weibo'] : '';
        $tengqq = isset($params['tengqq']) ? $params['tengqq'] : '';
        $tel_num = isset($params['tel_num']) ? $params['tel_num'] : '';
        $picture = isset($params['picture']) ? $params['picture'] : '';
        $truename = isset($params['truename']) ? $params['truename'] : '';
        $address = isset($params['address']) ? $params['address'] : '';
        $employer_company = isset($params['employer_company']) ? $params['employer_company'] : '';
        $expertise = isset($params['expertise']) ? $params['expertise'] : '';
        $identitycard = isset($params['identitycard']) ? $params['identitycard'] : '';
        $birthday = isset($params['birthday']) ? $params['birthday'] : '';
        $city = isset($params['city']) ? $params['city'] : '';
        $work = isset($params['work']) ? $params['work'] : '';
        $education = isset($params['education']) ? $params['education'] : '';
        /* */
        $base_info  = array();
        $agent_info = array();
        $msg = "normal request";

        $bll_user = new Bll_User_UserInfo();
        if($bll_user->verify_user_access_token($yyid, $token)){
            $base_info = $bll_user->get_user_by_yyid($yyid);
            if(!empty($base_info)){
                /* user info */
                $user = array(
                    'name' => $name,
                    'mail' => $mail,
                    'mail_verified' => 0,
                    'wechat' => $wechat,
                    'weibo' => $weibo,
                    'tengqq' => $tengqq,
                    'tel_num' => $tel_num,
                    'picture' => $picture,
                );
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($user, true));
                $bll_user->set_user_by_yyid($yyid, $user);
                // save agent info
                $agent_info = $bll_user->get_agent_by_yyid($yyid);
                /* agent info */
                $agent = array(
                    'truename' => $truename,
                    'address' => $address,
                    'employer_company' => $employer_company,
                    'expertise' => $expertise,
                    'identitycard' => $identitycard,
                    'birthday' => $birthday,
                    'city' => $city,
                    'work' => $work,
                    'education' => $education,
                );
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($agent, true));
                if(empty($agent_info)){
                   //add
                   $bll_user->add_agent_by_yyid($yyid, $agent);
                }
                else{
                   //update
                   $bll_user->set_agent_by_yyid($yyid, $agent);
                }
                $base_info = $bll_user->get_user_by_yyid($yyid);
                $agent_info = $bll_user->get_agent_by_yyid($yyid);
                $msg = "update success";
                $msg1 = "Successfully_modified";
            }
        }
        else{
            $msg = "ACCESS DENIED";
            $msg1 = "ACCESS_DENIED";
        }


        $data = array('base_info'=> $base_info , 'agent_info' => $agent_info);
        Util_Json::render(200, $data, $msg, $msg1);

        return ;
    }
}
