<?php
apf_require_class("APF_Controller");
require CORE_PATH . 'classes/Solr/Service.php';
require CORE_PATH . 'classes/Solr/HttpTransport/Curl.php';

class Hospital_ListController extends APF_Controller
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
        $hospital_list = array();
        $total = 0;
        $bll_hospital = new Bll_Hospital_Info(); 
        if($action == "list"){
            $location = $bll_hospital->get_location($yyid);
            if(isset($location['type_code']) && !empty($location['type_code'])){
               $hospital_list = $bll_hospital->get_hospital_list($location['type_code'], $page_size, $page_start);
               $total = $bll_hospital->get_hospital_count($location['type_code']);
            }
        }
        else if($action == "search" && !empty($keywords)){

            $args['defType'] = 'edismax';
            $args['wt'] = 'json';
            $args['fq'] = 'status:1';
            $args['fl'] = 'yyid, name, grade, address';
            $args['qf'] = 'name address';
            $args['pf'] = 'name^3000 address^1000';
            #$args['qf'] = 'name^30 address^30 traffic_guide^10 medical_guide^5 introduction';
            #$args['pf'] = 'name^3000 address^1000 medical_guide^100';

            $solr = new Apache_Solr_Service($this->solr_host, $this->solr_port, '/search/hospital/', new Apache_Solr_HttpTransport_Curl());
            $keywords = str_replace(':', '', $keywords);
            $result = $solr->search($keywords, $page_start, $page_size, $args);
            $hospital_list = json_decode($result->getRawResponse(), true);
            $hospital_list = isset($hospital_list['response']) ? $hospital_list['response'] : array();
            $total = isset($hospital_list['numFound']) ? $hospital_list['numFound'] : array();
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($hospital_list, true));
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
            "hospital_list" => $hospital_list,
        )));

        return false;
    }

}
