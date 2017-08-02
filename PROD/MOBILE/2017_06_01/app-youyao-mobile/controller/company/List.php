<?php
apf_require_class("APF_Controller");
require CORE_PATH . 'classes/Solr/Service.php';
require CORE_PATH . 'classes/Solr/HttpTransport/Curl.php';

class Company_ListController extends APF_Controller
{
    private $pdo;
    private $solr_host;
    private $solr_port;

    public function __construct() {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $this->solr_host = APF::get_instance()->get_config('solr_host');
        $this->solr_port = APF::get_instance()->get_config('solr_port');
    }

    public function handle_request()
    {

        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
        header("Access-Control-Allow-Headers:SIG,DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");
        header("Content-type: application/json; charset=utf-8");

        $req = APF::get_instance()->get_request();
        $params = $req->get_parameters();

        $default_yyid = "6F86727E527411E79E6C68F728954D54"; // 422,1,2  / 朝阳

        if ( isset($params['yyid']) && strlen($params['yyid']) == 32 ) {
                   $yyid = strtoupper($params['yyid']);
        } else {
                   $yyid = $default_yyid;
        }

        $action = isset($params['action']) ? $params['action'] : "list";
        $keywords = isset($params['keywords']) ? $params['keywords'] : "";

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

        $location = array();
        $company_list = array();
        $total = 0;
        if($action == "list"){
        }
        else if($action == "search" && !empty($keywords)){

            $args['defType'] = 'edismax';
            $args['wt'] = 'json';
            $args['fl'] = 'yyid, name, grade, address';
            $args['qf'] = 'name^30 address^30 introduction';
            $args['pf'] = 'name^3000 address^1000';

            $solr = new Apache_Solr_Service($this->solr_host, $this->solr_port, '/search/company/', new Apache_Solr_HttpTransport_Curl());
            $keywords = str_replace(':', '', $keywords);
            $result = $solr->search($keywords, $page_start, $page_size, $args);
            $company_list = json_decode($result->getRawResponse(), true);
            $company_list = isset($company_list['response']) ? $company_list['response'] : array();
            $total = isset($company_list['numFound']) ? $company_list['numFound'] : array();
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($company_list, true));
        }

        echo json_encode(Util_Beauty::wanna(array(
            'code' => 1,
            'codeMsg' => 'normal_request',
            //"params" => $params,
            "page_num" => $page_num,
            "page_size" => $page_size,
            "total" => $total,
            "location" => $location,
            "keywords" => $keywords,
            "action" => $action,
            "company_list" => $company_list,
        )));

        return false;
    }

}
