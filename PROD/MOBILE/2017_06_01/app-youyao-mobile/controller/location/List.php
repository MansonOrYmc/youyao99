<?php
apf_require_class("APF_Controller");

class Location_ListController extends APF_Controller
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
        
        $default_yyid = '10';
        if ( isset($params['yyid'])) {
                   $yyid = strtoupper($params['yyid']);
        } else {
                   $yyid = $default_yyid;
        }

        $page_num = 1;
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

        $transaction_list = array();


        $transaction_list = self::get_location_list($yyid, $page_size, $page_start);
        $total = self::get_location_count($yyid);


        echo json_encode(Util_Beauty::wanna(array(
            'code' => 1,
            'codeMsg' => 'normal_request',
            //"params" => $params,
            "page_num" => $page_num,
            "page_size" => $page_size,
            "total" => $total,
            "location_list" => $transaction_list,
        )));

        return false;
    }

    private function get_location_list($yyid, $limit, $offset)
    {

        if(!is_numeric($limit) || !is_numeric($offset)){
            return array();
        }

        $sql = "select yyid, name, type_code, type_desc, rank, name_code, map_x, map_y, map_zoom, area_level from t_location where status = 1 order by type_code asc LIMIT :limit OFFSET :offset ;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $jobs = $stmt->fetchAll();
        $job = array();
        foreach($jobs as $k=>$j){
          $job[$k] = $j;
        }

        return $job;
    }

    private function get_location_count($yyid='')
    {

        $c = 0;
        $get_count_sql = "select count(*) c from t_location where status=1;";
        $stmt = $this->pdo->prepare($get_count_sql);
        $stmt->execute();
        $c = $stmt->fetchColumn();
        return $c;
    }



   /*
####################################################################
Variables List
####################################################################
$id = "";
$yyid = "";
$name = "";
$parent_id = "";
$type_code = "";
$type_desc = "";
$status = "";
$rank = "";
$name_code = "";
$map_x = "";
$map_y = "";
$map_zoom = "";
$hospital_num = "";
$area_level = "";
$hospital_list = "";
$created = "";
$update_date = "";
####################################################################
Array Statement
####################################################################
$res = array(
    'id' => $id,
    'yyid' => $yyid,
    'name' => $name,
    'parent_id' => $parent_id,
    'type_code' => $type_code,
    'type_desc' => $type_desc,
    'status' => $status,
    'rank' => $rank,
    'name_code' => $name_code,
    'map_x' => $map_x,
    'map_y' => $map_y,
    'map_zoom' => $map_zoom,
    'hospital_num' => $hospital_num,
    'area_level' => $area_level,
    'hospital_list' => $hospital_list,
    'created' => $created,
    'update_date' => $update_date
);
####################################################################
Insert Statement
####################################################################
insert into t_location (id, yyid, name, parent_id, type_code, type_desc, status, rank, name_code, map_x, map_y, map_zoom, hospital_num, area_level, hospital_list, created, update_date) values(:id, :yyid, :name, :parent_id, :type_code, :type_desc, :status, :rank, :name_code, :map_x, :map_y, :map_zoom, :hospital_num, :area_level, :hospital_list, :created, :update_date);
####################################################################
Update Statement
####################################################################
update t_location set id = ?, yyid = ?, name = ?, parent_id = ?, type_code = ?, type_desc = ?, status = ?, rank = ?, name_code = ?, map_x = ?, map_y = ?, map_zoom = ?, hospital_num = ?, area_level = ?, hospital_list = ?, created = ?, update_date = ? where id = ? ;
####################################################################
Select Statement
####################################################################
select id, yyid, name, parent_id, type_code, type_desc, status, rank, name_code, map_x, map_y, map_zoom, hospital_num, area_level, hospital_list, created, update_date from t_location where id = ? ;

   */


}
