<?php
apf_require_class("APF_Controller");

class Drug_EditController extends APF_Controller
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
        /* */
        $d_yyid = isset($params['d_yyid']) ? $params['d_yyid'] : '';
        $u_yyid = isset($params['u_yyid']) ? $params['u_yyid'] : '';
        $name = isset($params['name']) ? $params['name'] : '';
        $c_name = isset($params['c_name']) ? $params['c_name'] : '';
        $e_name = isset($params['e_name']) ? $params['e_name'] : '';
        $py_name = isset($params['py_name']) ? $params['py_name'] : '';
        $type = isset($params['type']) ? $params['type'] : '';
        $approval_id = isset($params['approval_id']) ? $params['approval_id'] : '';
        $specs = isset($params['specs']) ? $params['specs'] : '';
        $dosage_form = isset($params['dosage_form']) ? $params['dosage_form'] : '';
        $indication = isset($params['indication']) ? $params['indication'] : '';
        $ingredients = isset($params['ingredients']) ? $params['ingredients'] : '';
        $shape = isset($params['shape']) ? $params['shape'] : '';
        $usage = isset($params['usage']) ? $params['usage'] : '';
        $adverse_reaction = isset($params['adverse_reaction']) ? $params['adverse_reaction'] : '';
        $taboo = isset($params['taboo']) ? $params['taboo'] : '';
        $attentions = isset($params['attentions']) ? $params['attentions'] : '';
        $attentions_pw = isset($params['attentions_pw']) ? $params['attentions_pw'] : '';
        $attentions_ch = isset($params['attentions_ch']) ? $params['attentions_ch'] : '';
        $attentions_oa = isset($params['attentions_oa']) ? $params['attentions_oa'] : '';
        $overdose = isset($params['overdose']) ? $params['overdose'] : '';
        $chinical_trial = isset($params['chinical_trial']) ? $params['chinical_trial'] : '';
        $toxicology = isset($params['toxicology']) ? $params['toxicology'] : '';
        $pharmacokinetics = isset($params['pharmacokinetics']) ? $params['pharmacokinetics'] : '';
        $storage_conditions = isset($params['storage_conditions']) ? $params['storage_conditions'] : '';
        $package = isset($params['package']) ? $params['package'] : '';
        $period_validity = isset($params['period_validity']) ? $params['period_validity'] : '';
        $performance_standards = isset($params['performance_standards']) ? $params['performance_standards'] : '';
        $price = isset($params['price']) ? $params['price'] : '';
        $preparation = isset($params['preparation']) ? $params['preparation'] : '';
        $adaptation_department = isset($params['adaptation_department']) ? $params['adaptation_department'] : '';
        $therapeutic_field = isset($params['therapeutic_field']) ? $params['therapeutic_field'] : '';
        $product_advantage = isset($params['product_advantage']) ? $params['product_advantage'] : '';
        $channel = isset($params['channel']) ? $params['channel'] : '';
        $business_type = isset($params['business_type']) ? $params['business_type'] : '';
        $medical_insurance = isset($params['medical_insurance']) ? $params['medical_insurance'] : '';
        $competitive_products = isset($params['competitive_products']) ? $params['competitive_products'] : '';
        $manufacturer = isset($params['manufacturer']) ? $params['manufacturer'] : '';
        $l_info = isset($params['l_info']) ? $params['l_info'] : '';
        $v_info = isset($params['v_info']) ? $params['v_info'] : '';
        $imgs_num = 0;
        $status = 1;
        $created = $timestamp;

        $res = array(
            'd_yyid' => $d_yyid,
            'u_yyid' => $u_yyid,
            'name' => $name,
            'c_name' => $c_name,
            'e_name' => $e_name,
            'py_name' => $py_name,
            'type' => $type,
            'approval_id' => $approval_id,
            'specs' => $specs,
            'dosage_form' => $dosage_form,
            'indication' => $indication,
            'ingredients' => $ingredients,
            'shape' => $shape,
            'usage' => $usage,
            'adverse_reaction' => $adverse_reaction,
            'taboo' => $taboo,
            'attentions' => $attentions,
            'attentions_pw' => $attentions_pw,
            'attentions_ch' => $attentions_ch,
            'attentions_oa' => $attentions_oa,
            'overdose' => $overdose,
            'chinical_trial' => $chinical_trial,
            'toxicology' => $toxicology,
            'pharmacokinetics' => $pharmacokinetics,
            'storage_conditions' => $storage_conditions,
            'package' => $package,
            'period_validity' => $period_validity,
            'performance_standards' => $performance_standards,
            'price' => $price,
            'preparation' => $preparation,
            'adaptation_department' => $adaptation_department,
            'therapeutic_field' => $therapeutic_field,
            'product_advantage' => $product_advantage,
            'channel' => $channel,
            'business_type' => $business_type,
            'medical_insurance' => $medical_insurance,
            'competitive_products' => $competitive_products,
            'manufacturer' => $manufacturer,
            'l_info' => $l_info,
            'v_info' => $v_info,
            'imgs_num' => $imgs_num,
            'status' => $status,
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
            $bll_drug = new Bll_Drug_Info();
            if(isset($res['d_yyid']) && empty($res['d_yyid'])){
               //add
               $d_yyid = $bll_drug->add_drug($yyid, $res);
               $res['d_yyid'] = $d_yyid;
            }
            else{
               //update
               $bll_drug->set_drug_by_yyid($yyid, $res['d_yyid'], $res);
            }
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($res, true));
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
