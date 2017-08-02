<?php
apf_require_class("APF_Controller");

class Doctor_ListController extends APF_Controller
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

        $default_yyid = "6F86727E527411E79E6C68F728954D54"; // 422,1,2  / 朝阳

        if ( isset($params['yyid']) && strlen($params['yyid']) == 32 ) {
                   $yyid = strtoupper($params['yyid']);
        } else {
                   $yyid = $default_yyid;
        }

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
        $doctor_list = array();

        $location = self::get_location($yyid);
        if(isset($location['type_code']) && !empty($location['type_code'])){
           $doctor_list = self::get_doctor_list($location['type_code'], $page_size, $page_start);
           $total = self::get_doctor_count($location['type_code']);
        }


        echo json_encode(Util_Beauty::wanna(array(
            'code' => 1,
            'codeMsg' => 'normal_request',
            //"params" => $params,
            "page_num" => $page_num,
            "page_size" => $page_size,
            "total" => $total,
            "location" => $location,
            "doctor_list" => $doctor_list,
        )));

        return false;
    }

    private function get_doctor_list($loc_code, $limit, $offset)
    {
        if(empty($loc_code) || !is_numeric($limit) || !is_numeric($offset)){
            return array();
        }

        $sql = "select yyid, name, job_title, degree, photo, expertise, h_yyid, hd_yyid, u_yyid, loc_code, oncall, r_score, views, created, update_date, depart_name from t_doctor where loc_code LIKE :keyword and status=1 order by did asc LIMIT :limit OFFSET :offset ;";
        $keyword = "".$loc_code."%";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':keyword', $keyword, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $jobs = $stmt->fetchAll();
        $job = array();
        foreach($jobs as $k=>$j){
          if(isset($j['photo']) && !empty($j['photo'])){
            $j['photo'] = IMG_CDN . strtolower($j['photo'])."/headpic.jpg";
          }
          $job[$k] = $j;
        }

        return $job;
    }

    private function get_doctor_count($loc_code)
    {
        if(empty($loc_code)) {
            return array();
        }
        
        $c = 0;
        $get_count_sql = "select count(*) c from t_doctor where loc_code like ? and status=1;";
        $stmt = $this->pdo->prepare($get_count_sql);
        $stmt->execute(array("$loc_code%"));
        $c = $stmt->fetchColumn();
        return $c;
    }

    private function get_location($yyid)
    {
        if(empty($yyid)) {
            return array();
        }

        $sql = "select yyid, name, parent_id, type_code, name_code, area_level from t_location where status=1 and yyid = ? limit 1;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($yyid));
        $rows = $stmt->fetchAll();
        $row = array();
        foreach($rows as $k=>$v){
          $row = $v;
        }

        return $row;
    }
   /*
    select id, yyid, name, parent_id, type_code, type_desc, status, rank, name_code, map_x, map_y, map_zoom, hospital_num, area_level, hospital_list, created, update_date from t_location where id = ? ;

    select did, yyid, name, job_title, degree, photo, expertise, h_yyid, hd_yyid, u_yyid, spider_url, loc_code, oncall, r_score, status, views, created, update_date, hospital_url, department_url, photo_url, dwsite_url, intro, depart_name from t_doctor where did = ? ;
   */


}
