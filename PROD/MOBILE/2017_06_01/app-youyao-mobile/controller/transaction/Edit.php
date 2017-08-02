<?php
apf_require_class("APF_Controller");

class Transaction_EditController extends APF_Controller
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

        $yyid = isset($params['yyid']) ? $params['yyid'] : '';
        $token = isset($params['user_token']) ? $params['user_token'] : '';
        /* */
        $timestamp = time(); 
        $client_ip = Util_NetWorkAddress::get_client_ip();
        /* */
        $t_yyid = isset($params['t_yyid']) ? $params['t_yyid'] : '';
        $u_yyid = isset($params['yyid']) ? $params['yyid'] : '';

        $h_yyid = isset($params['h_yyid']) ? $params['h_yyid'] : '';
        $d_yyid = isset($params['d_yyid']) ? $params['d_yyid'] : '';
        $datei = date('Y-m-d', $timestamp);
        $tid = isset($params['tid']) ? $params['tid'] : '';
        $name = isset($params['name']) ? $params['name'] : '';
        $specs = isset($params['specs']) ? $params['specs'] : '';
        $t_num = isset($params['t_num']) ? $params['t_num'] : '';
        $buyer_hospital = isset($params['buyer_hospital']) ? $params['buyer_hospital'] : '';
        $trans_date = isset($params['trans_date']) ? $params['trans_date'] : '';
        $hospital_id = isset($params['hospital_id']) ? $params['hospital_id'] : '';
        $product_id = isset($params['product_id']) ? $params['product_id'] : '';
        $batch_number = isset($params['batch_number']) ? $params['batch_number'] : '';
        $email = isset($params['email']) ? $params['email'] : '';
        $version = date('ymdHi', $timestamp);
        $client_ip = $client_ip;

        $status = 1;
        $created = $timestamp;
        $res = array(
            'yyid' => $yyid,
            't_yyid' => $t_yyid,
            'u_yyid' => $u_yyid,
            'h_yyid' => $h_yyid,
            'd_yyid' => $d_yyid,
            'datei' => $datei,
            'tid' => $tid,
            'name' => $name,
            'specs' => $specs,
            't_num' => $t_num,
            'buyer_hospital' => $buyer_hospital,
            'trans_date' => $trans_date,
            'hospital_id' => $hospital_id,
            'product_id' => $product_id,
            'batch_number' => $batch_number,
            'email' => $email,
            'version' => $version,
            'status' => $status,
            'client_ip' => $client_ip,
            'created' => $created,
        );

        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($res, true));

        /* */
        $user_base_info  = array();
        $agent_info = array();
        $msg = "normal request";

        $bll_user = new Bll_User_UserInfo();
        if($bll_user->verify_user_access_token($yyid, $token)){ // 验证登录
            //$user_base_info = $bll_user->get_user_by_yyid($yyid);
            //Logger::info(__FILE__, __CLASS__, __LINE__, var_export($user_base_info, true));
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($res, true));
            $bll_transaction = new Bll_Transaction_Info();
            if(isset($res['t_yyid']) && empty($res['t_yyid'])){
               //add
               $t_yyid = $bll_transaction->add_transaction($yyid, $res);
               $res['t_yyid'] = $t_yyid;
            }
            else{
               //update
               $bll_transaction->set_transaction_by_yyid($yyid, $res['t_yyid'], $res);
            }
            $msg = "update success";
            $msg1 = "Successfully_modified";
        }
        else{
            $msg = "ACCESS DENIED";
            $msg1 = "ACCESS_DENIED";
        }



        Util_Json::render(200, null, $msg, $res);

        return ;
    }
/*
*/

}
