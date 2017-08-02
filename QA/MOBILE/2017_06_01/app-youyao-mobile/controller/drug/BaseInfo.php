<?php
apf_require_class("APF_Controller");

class Drug_BaseInfoController extends APF_Controller
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

        $yyid = "";
        if(isset($params['yyid'])){
          $yyid = $params['yyid'];
        }

        Logger::info(__FILE__, __CLASS__, __LINE__, $yyid);
        $drug = array();
        $bll_drug = new Bll_Drug_Info();
        if(strlen($yyid) == 32){
           $drug = $bll_drug->get_drug($yyid);
           Logger::info(__FILE__, __CLASS__, __LINE__, var_export($drug, true));
        }
        if(isset($drug['l_info']) && empty($drug['l_info'])){
           $drug['l_info'] = array(
               array('title'=>'新药扩展文章标题', 'link'=>'http://www.youyao99.com/t.html', 'image'=>'https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png', 'desc'=>'一段介绍文字，200字以内。','update_time'=>time()),
               array('title'=>'新药扩展文章标题', 'link'=>'http://www.youyao99.com/t.html', 'image'=>'https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png', 'desc'=>'一段介绍文字，200字以内。','update_time'=>time()),
               array('title'=>'新药扩展文章标题', 'link'=>'http://www.youyao99.com/t.html', 'image'=>'https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png', 'desc'=>'一段介绍文字，200字以内。','update_time'=>time()),
            );
        }
        else if(!empty($drug['l_info'])){
           $drug['l_info'] = json_decode($drug['l_info'], true);
        }

        if(isset($drug['v_info']) && empty($drug['v_info'])){
           $drug['v_info'] = array(
               array('title'=>'新药扩展文章标题', 'link'=>'http://www.youyao99.com/t.html', 'image'=>'https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png', 'desc'=>'一段介绍文字，200字以内。','update_time'=>time()),
               array('title'=>'新药扩展文章标题', 'link'=>'http://www.youyao99.com/t.html', 'image'=>'https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png', 'desc'=>'一段介绍文字，200字以内。','update_time'=>time()),
               array('title'=>'新药扩展文章标题', 'link'=>'http://www.youyao99.com/t.html', 'image'=>'https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png', 'desc'=>'一段介绍文字，200字以内。','update_time'=>time()),
            );
        }
        else if(!empty($drug['v_info'])){
           $drug['v_info'] = json_decode($drug['v_info'], true);
        }
        echo json_encode(Util_Beauty::wanna(array(
            'code' => 1,
            'codeMsg' => 'normal_request',
            //"params" => $params,
            "drug" => $drug,
        )));

        return false;
    }

}
