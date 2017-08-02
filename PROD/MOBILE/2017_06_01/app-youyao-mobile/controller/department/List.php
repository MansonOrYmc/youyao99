<?php
apf_require_class("APF_Controller");

class Department_ListController extends APF_Controller
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

        $default_hyyid = "A21878A0534311E79E6C68F728954D54"; // 宜春市第三人民医院

        if ( isset($params['yyid']) && strlen($params['yyid']) == 32 ) {
                   $h_yyid = strtoupper($params['yyid']);
        } else {
                   $h_yyid = $default_hyyid;
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

        $hospital = array();
        $department_list = array();

        $hospital = self::get_hospital_data($h_yyid);
        if(isset($hospital['yyid']) && !empty($hospital['yyid'])){
           $department_list = self::get_department_list($hospital['yyid'], $page_size, $page_start);
           $total = self::get_department_count($hospital['yyid']);
        }

        echo json_encode(Util_Beauty::wanna(array(
            'code' => 1,
            'codeMsg' => 'normal_request',
            //"params" => $params,
            "page_num" => $page_num,
            "page_size" => $page_size,
            "total" => $total,
            "hospital" => $hospital,
            "department_list" => $department_list,
        )));

        return false;
    }

    private function get_department_list($h_yyid, $limit=10, $offset=0)
    {
        if(empty($h_yyid)){
            return array();
        }

        $sql = "select yyid, name, intro from t_department where h_yyid = :h_yyid and status = 1  order by rank desc LIMIT :limit OFFSET :offset ;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':h_yyid', $h_yyid, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $jobs = $stmt->fetchAll();
        $job = array();
        foreach($jobs as $k=>$j){
          $j['doctor_list'] = self::get_doctor_list($j['yyid']);
          $job[$k] = $j;
        }

        return $job;
    }

    private function get_department_count($h_yyid)
    {
        if(empty($h_yyid)){
            return 0;
        }

        $c = 0;
        $get_count_sql = "select count(*) from t_department where h_yyid = :h_yyid and status = 1 ;";
        $stmt = $this->pdo->prepare($get_count_sql);
        $stmt->bindParam(':h_yyid', $h_yyid, PDO::PARAM_STR);
        $stmt->execute();
        $c = $stmt->fetchColumn();
        return $c;
    }


    private function get_hospital_data($yyid)
    {
        $row = array();
        $sql = "select yyid, name, loc_code, grade, tel_num, address, map_long, map_lat, map_zoom, traffic_guide, medical_guide from t_hospital where yyid= ? and status=1 limit 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($yyid));
        $row = $stmt->fetch();
        return $row;
    }

    private function get_doctor_list($hd_yyid)
    {
        if(empty($hd_yyid)){
            return array();
        }

        $sql = "select yyid, name, job_title, degree, photo, expertise, oncall, r_score, views  from t_doctor where hd_yyid = ? and status=1 order by r_score desc ;";
        $keyword = "".$loc_code."%";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array("$hd_yyid"));
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

   /*
tonycai@whale:$ ~/bin/mysqltl4php.pl -tt_department
####################################################################
Variables List
####################################################################
$id = "";
$yyid = "";
$name = "";
$h_yyid = "";
$rank = "";
$status = "";
$spider_url = "";
$hospital_url = "";
$intro = "";
$created = "";
$update_date = "";
####################################################################
Array Statement
####################################################################
$res = array(
    'id' => $id,
    'yyid' => $yyid,
    'name' => $name,
    'h_yyid' => $h_yyid,
    'rank' => $rank,
    'status' => $status,
    'spider_url' => $spider_url,
    'hospital_url' => $hospital_url,
    'intro' => $intro,
    'created' => $created,
    'update_date' => $update_date
);
####################################################################
Insert Statement
####################################################################
insert into t_department (id, yyid, name, h_yyid, rank, status, spider_url, hospital_url, intro, created, update_date) values(:id, :yyid, :name, :h_yyid, :rank, :status, :spider_url, :hospital_url, :intro, :created, :update_date);
####################################################################
Update Statement
####################################################################
update t_department set id = ?, yyid = ?, name = ?, h_yyid = ?, rank = ?, status = ?, spider_url = ?, hospital_url = ?, intro = ?, created = ?, update_date = ? where id = ? ;
####################################################################
Select Statement
####################################################################
select id, yyid, name, h_yyid, rank, status, spider_url, hospital_url, intro, created, update_date from t_department where id = ? ;
####################################################################
   */


}
