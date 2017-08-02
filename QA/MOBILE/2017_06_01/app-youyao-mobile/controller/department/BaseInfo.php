<?php
apf_require_class("APF_Controller");

class Department_BaseInfoController extends APF_Controller
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

        $default_hdyyid = "5F211682537311E79E6C68F728954D54"; // 宜春市第三人民医院 精神科

        if ( isset($params['yyid']) && strlen($params['yyid']) == 32 ) {
                   $hd_yyid = strtoupper($params['yyid']);
        } else {
                   $hd_yyid = $default_hyyid;
        }

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
        $department = array();

        $department = self::get_department_data($hd_yyid);
        if(isset($department['h_yyid']) && !empty($department['h_yyid'])){
           $hospital = self::get_hospital_data($department['h_yyid']);
        }

        echo json_encode(Util_Beauty::wanna(array(
            'code' => 1,
            'codeMsg' => 'normal_request',
            //"params" => $params,
            "hospital" => $hospital,
            "department" => $department,
        )));

        return false;
    }

    private function get_department_data($hd_yyid)
    {
        if(empty($hd_yyid)){
            return array();
        }
        $department = array();
        $sql = "select yyid, h_yyid, name, intro from t_department where yyid = ? and status = 1  limit 1;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array("$hd_yyid"));
        $department = $stmt->fetch();
        if(isset($department['yyid']) && !empty($department['yyid'])){
           $department['doctor_list'] = self::get_doctor_list($department['yyid']);
        }
        return $department;

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
