<?php
apf_require_class("APF_Controller");

class Hospital_EditController extends APF_Controller
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

        $yyid = $params['yyid'];
        $token = $params['user_token'];
        /* */
        $timestamp = time(); 
        $h_yyid = isset($params['h_yyid']) ? $params['h_yyid'] : '';
        $u_yyid = isset($params['u_yyid']) ? $params['u_yyid'] : '';
        $name = isset($params['name']) ? $params['name'] : '';
        $grade = isset($params['grade']) ? $params['grade'] : '';
        $tel_num = isset($params['tel_num']) ? $params['tel_num'] : '';
        $address = isset($params['address']) ? $params['address'] : '';
        $traffic_guide = isset($params['traffic_guide']) ? $params['traffic_guide'] : '';
        $medical_guide = isset($params['medical_guide']) ? $params['medical_guide'] : '';
        $introduction = isset($params['introduction']) ? $params['introduction'] : '';
        $hos_dean = isset($params['hos_dean']) ? $params['hos_dean'] : '';
        $annual_income = isset($params['annual_income']) ? $params['annual_income'] : '';
        $use_rate = isset($params['use_rate']) ? $params['use_rate'] : '';
        $p_department = isset($params['p_department']) ? $params['p_department'] : '';
        $kn_department = isset($params['kn_department']) ? $params['kn_department'] : '';
        $bed_num = isset($params['bed_num']) ? $params['bed_num'] : '';
        $operation_num = isset($params['operation_num']) ? $params['operation_num'] : '';
        $outpatient_num = isset($params['outpatient_num']) ? $params['outpatient_num'] : '';
        $loc_code = isset($params['loc_code']) ? $params['loc_code'] : '';
        $status = 1;
        $views = 0;
        $ver = date('ymdHi', $timestamp);
        $created = $timestamp;

        $res = array(
            'h_yyid' => $h_yyid,
            'u_yyid' => $u_yyid,
            'name' => $name,
            'grade' => $grade,
            'tel_num' => $tel_num,
            'address' => $address,
            'traffic_guide' => $traffic_guide,
            'medical_guide' => $medical_guide,
            'introduction' => $introduction,
            'hos_dean' => $hos_dean,
            'annual_income' => $annual_income,
            'use_rate' => $use_rate,
            'p_department' => $p_department,
            'kn_department' => $kn_department,
            'bed_num' => $bed_num,
            'operation_num' => $operation_num,
            'outpatient_num' => $outpatient_num,
            'loc_code' => $loc_code,
            'status' => $status,
            'views' => $views,
            'ver' => $ver,
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
            $bll_hospital = new Bll_Hospital_Info();
            if(isset($res['h_yyid']) && empty($res['h_yyid'])){
               //add
               //$bll_user->add_agent_by_yyid($yyid, $agent);
               $h_yyid = $bll_hospital->add_hospital($yyid, $res);
               $res['h_yyid'] = $h_yyid;
               $bll_hospital->add_hospital_sk($yyid, $res);
            }
            else{
               //update
               //$bll_user->set_agent_by_yyid($yyid, $agent);
               $bll_hospital->set_hospital_by_yyid($yyid, $res['h_yyid'], $res);
               $bll_hospital->add_hospital_sk($yyid, $res);
            }
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
####################################################################
Variables List
####################################################################
$hid = "";  // Primary Key: Unique hospital ID. bigint(20) unsigned 
$yyid = "";  // UNIQUE Key: uuid char(32) 
$h_yyid = "";  // UNIQUE Key: uuid char(32) 
$u_yyid = "";  // UNIQUE Key: uuid char(32) 
$name = "";  // 名称 varchar(120) 
$grade = "";  // 医院等级 varchar(50) 
$tel_num = "";  // 联系电话 varchar(200) 
$address = "";  // 医院地址 varchar(200) 
$traffic_guide = "";  // 交通指南 varchar(1000) 
$medical_guide = "";  // 就医指南 varchar(1000) 
$introduction = "";  // 医院介绍 text 
$active_doctor = "";  // 在职医生 int(11) 
$bed_num = "";  // 病床数量 int(11) 
$operation_num = "";  // 年手术量 int(11) 
$outpatient_num = "";  // 年门诊量 int(11) 
$loc_code = "";  // 字符排序分层 varchar(60) 
$status = "";  // 是否显示。1为显示，0为隐藏 tinyint(1) 
$views = "";  // 访问量 int(11) 
$map_long = "";  // longitude 经线，经度 ，116.404 ，x char(10) 
$map_lat = "";  // latitude 纬线， 纬度 ，39.915 ，y char(10) 
$map_zoom = "";  // map zoom char(10) 
$imgs_num = "";  // 上传图片数量 int(11) 
$ver = "";  // 版本号 varchar(20) 
$created = "";  // Timestamp for when record was created. int(11) 
$update_date = "";  // timestamp 
####################################################################
Array Statement
####################################################################
$res = array(
    'hid' => $hid,
    'yyid' => $yyid,
    'h_yyid' => $h_yyid,
    'u_yyid' => $u_yyid,
    'name' => $name,
    'grade' => $grade,
    'tel_num' => $tel_num,
    'address' => $address,
    'traffic_guide' => $traffic_guide,
    'medical_guide' => $medical_guide,
    'introduction' => $introduction,
    'active_doctor' => $active_doctor,
    'bed_num' => $bed_num,
    'operation_num' => $operation_num,
    'outpatient_num' => $outpatient_num,
    'loc_code' => $loc_code,
    'status' => $status,
    'views' => $views,
    'map_long' => $map_long,
    'map_lat' => $map_lat,
    'map_zoom' => $map_zoom,
    'imgs_num' => $imgs_num,
    'ver' => $ver,
    'created' => $created,
    'update_date' => $update_date
);
####################################################################
Insert Statement
####################################################################
insert into `t_hospital_sk` (`hid`, `yyid`, `h_yyid`, `u_yyid`, `name`, `grade`, `tel_num`, `address`, `traffic_guide`, `medical_guide`, `introduction`, `active_doctor`, `bed_num`, `operation_num`, `outpatient_num`, `loc_code`, `status`, `views`, `map_long`, `map_lat`, `map_zoom`, `imgs_num`, `ver`, `created`, `update_date`) values(:hid, :yyid, :h_yyid, :u_yyid, :name, :grade, :tel_num, :address, :traffic_guide, :medical_guide, :introduction, :active_doctor, :bed_num, :operation_num, :outpatient_num, :loc_code, :status, :views, :map_long, :map_lat, :map_zoom, :imgs_num, :ver, :created, :update_date);
####################################################################
Update Statement
####################################################################
update `t_hospital_sk` set `hid` = :hid, `yyid` = :yyid, `h_yyid` = :h_yyid, `u_yyid` = :u_yyid, `name` = :name, `grade` = :grade, `tel_num` = :tel_num, `address` = :address, `traffic_guide` = :traffic_guide, `medical_guide` = :medical_guide, `introduction` = :introduction, `active_doctor` = :active_doctor, `bed_num` = :bed_num, `operation_num` = :operation_num, `outpatient_num` = :outpatient_num, `loc_code` = :loc_code, `status` = :status, `views` = :views, `map_long` = :map_long, `map_lat` = :map_lat, `map_zoom` = :map_zoom, `imgs_num` = :imgs_num, `ver` = :ver, `created` = :created, `update_date` = :update_date where `hid` = :hid ;
####################################################################
Select Statement
####################################################################
select `hid`, `yyid`, `h_yyid`, `u_yyid`, `name`, `grade`, `tel_num`, `address`, `traffic_guide`, `medical_guide`, `introduction`, `active_doctor`, `bed_num`, `operation_num`, `outpatient_num`, `loc_code`, `status`, `views`, `map_long`, `map_lat`, `map_zoom`, `imgs_num`, `ver`, `created`, `update_date` from `t_hospital_sk` where `hid` = ? ;
*/

}
