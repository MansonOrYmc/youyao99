<?php
apf_require_class("APF_Controller");

class Doctor_BaseInfoController extends APF_Controller
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

        $default_yyid = "9AE3C6B2587E11E79E6C68F728954D54";

        if ( isset($params['yyid']) && strlen($params['yyid']) == 32 ) {
                   $yyid = strtoupper($params['yyid']);
        } else {
                   $yyid = $default_yyid;
        }

        $security = Util_Security::Security($params);

        if (!$security) {
            echo json_encode(Util_Beauty::wanna(array(
                'code' => 0,
                'codeMsg' => 'Illegal_request',
                'status' => 'fail',
                'msg' => "request forbidden",
                "userMsg" => 'Illegal_request',
                "params" => $params,
            )));

            return false;
        }

        $doctor = self::get_doctor_data($yyid);

        echo json_encode(Util_Beauty::wanna(array(
            'code' => 1,
            'codeMsg' => 'normal_request',
            //"params" => $params,
            "doctor" => $doctor,
        )));

        return false;
    }

    private function get_doctor_data($yyid)
    {

        $sql = "select yyid, name, job_title, degree, photo, expertise, h_yyid, hd_yyid, u_yyid, spider_url, loc_code, oncall, r_score, views, created, update_date, intro, depart_name from t_doctor where yyid = ?  and status=1 limit 1;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($yyid));
        $row = $stmt->fetch();
        if(isset($row['photo']) && !empty($row['photo'])){
           $row['photo'] = IMG_CDN . strtolower($row['photo'])."/headpic.jpg";
        }
        return $row;
    }
   /*
    select did, yyid, name, job_title, degree, photo, expertise, h_yyid, hd_yyid, u_yyid, spider_url, loc_code, oncall, r_score, status, views, created, update_date, hospital_url, department_url, photo_url, dwsite_url, intro, depart_name from t_doctor where did = ? ;
   */


}
