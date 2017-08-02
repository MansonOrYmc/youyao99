<?php
apf_require_class("APF_Controller");

class Trend_DrugController extends APF_Controller
{
    private $pdo;

    public function __construct() {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("master");
    }

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
            echo json_encode(Util_Beauty::wanna(array(
                'code' => 0,
                'codeMsg' => 'Illegal_request',
                'status' => 'fail',
                'msg' => "request forbidden",
                "userMsg" => 'Illegal_request',
            )));

            return false;
        }


        $drug_list = array();

        $bll_drug = new Bll_Drug_Info();
        $drug_list = $bll_drug->get_drug_list(2, 0);

        $u_yyid = $params['u_yyid'];
        $token = $params['user_token'];
        $date_range = $params['date_range']; ////0:全部 1:本月 2:上月 3:本年
        $date_range = intval($date_range);
        if($date_range > 3 || $date_range < 0){
            $date_range = 0;
        }
        
        $bll_user = new Bll_User_UserInfo();
        $trend = array();
        $msg = "ACCESS ALLOWED";
        $msg1 = "ACCESS_ALLOWED";

        if($bll_user->verify_user_access_token($u_yyid, $token)){

            $bll_trend = new Bll_Trend_Info();
            $trend = $bll_trend->get_trend_list($date_range, $page_size, $page_start);
            $total = $bll_trend->get_trend_count($date_range);

            foreach($drug_list as $k=>$drug){
               $drug['t_num'] = $bll_trend->get_trend_drug_sum($date_range, $drug['name']);
               $drug_list[$k] = $drug;
            }


        }
        else{
            $msg = "ACCESS DENIED";
            $msg1 = "ACCESS_DENIED";
        }


        $trend_info = array(
            'code' => 1,
            'codeMsg' => 'normal_request',
            "page_num" => $page_num,
            "page_size" => $page_size,
            "date_range" => $date_range,
            "total" => $total,
            "trend" => $trend,
        );

        $data = array('drug_list'=> $drug_list , 'trend_info' => $trend_info);
        Util_Json::render(200, $data, $msg, $msg1);

        return ;
    }

}
