<?php

apf_require_class("APF_Controller");

class Wechat_UploadsingleformfileController extends APF_Controller{

    private $pdo;

    public function __construct() {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("master");
    }

    public function handle_request(){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
        header("Access-Control-Allow-Headers:SIG,DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");
        header("Content-type: application/json; charset=utf-8");

        $req = APF::get_instance()->get_request();
        $params = $req->get_parameters();

        $security = Util_Security::Security($params);
        $security = true;
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

        if ($_FILES["_FILE_"]["error"] > 0)
        {
          $resarr = array('url'=>'', 'msg'=>'Invalid file type uploaded.', 'status'=>'0', 'hashkey'=>'');
        }
        else
        {
          $r_file = $_FILES['_FILE_']['tmp_name'];
          $r_file = realpath($r_file);
          $t_file = $_FILES['_FILE_']['type'];
          $img_id = self::create_image_r();
          $yyid = self::get_last_yyid($img_id);
          $pickey = strtolower($yyid);
          Logger::info(__FILE__, __CLASS__, __LINE__, $pickey);

          $up_data = array("my_field"=>'@'.$r_file, 'type'=>$t_file, 'bucket'=> 'user' , 'pickey'=>$pickey); 

          $res = $this->curl_post(IMG_CDN_HOST, $up_data);
          $resarr = json_decode($res, TRUE);
          if(isset($resarr['url']) && !empty($resarr['url'])){
             self::update_image_status();
          }
        }
        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($resarr, true));
            
        echo json_encode(Util_Beauty::wanna(array(
            'code' => 1,
            'codeMsg' => 'normal_request',
            //"params" => $params,
            //"matches" => $matches,
            "res" => $resarr,
        )));

        return false;
    }

    public function curl_post($url, $data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        $response = curl_exec($ch);
        return $response;
    }
/*
####################################################################
Insert Statement
####################################################################
insert into t_images (id, yyid, o_yyid, info_type, img_url, domain, image_hashkey, description, datei, transfer_success, dl_retry, width, height, wh_ratio, wh_range, file_size, status, created, update_date) values(:id, :yyid, :o_yyid, :info_type, :img_url, :domain, :image_hashkey, :description, :datei, :transfer_success, :dl_retry, :width, :height, :wh_ratio, :wh_range, :file_size, :status, :created, :update_date);
####################################################################
Update Statement
####################################################################
update t_images set id = ?, yyid = ?, o_yyid = ?, info_type = ?, img_url = ?, domain = ?, image_hashkey = ?, description = ?, datei = ?, transfer_success = ?, dl_retry = ?, width = ?, height = ?, wh_ratio = ?, wh_range = ?, file_size = ?, status = ?, created = ?, update_date = ? where id = ? ;
####################################################################
Select Statement
####################################################################
select id, yyid, o_yyid, info_type, img_url, domain, image_hashkey, description, datei, transfer_success, dl_retry, width, height, wh_ratio, wh_range, file_size, status, created, update_date from t_images where id = ? ;

info_type COMMENT '1:user, 2:hospital, 3:doctor: 4:agent, 5:comments',
*/
    private function create_image_r($info_type = 1)
    {

     $id = 0;
     $o_yyid = "";
     $info_type = 1;
     $img_url = "";
     $domain = "";
     $image_hashkey = "";
     $description = "";
     $datei = date('Y-m-d', time());
     $transfer_success = "0";
     $dl_retry = "0";
     $width = "0";
     $height = "0";
     $wh_ratio = "0";
     $wh_range = "0";
     $file_size = "0";
     $status = "0";
     $created = "0";
     
     $res = array(
         'id' => $id,
         'o_yyid' => $o_yyid,
         'info_type' => $info_type,
         'img_url' => $img_url,
         'domain' => $domain,
         'image_hashkey' => $image_hashkey,
         'description' => $description,
         'datei' => $datei,
         'transfer_success' => $transfer_success,
         'dl_retry' => $dl_retry,
         'width' => $width,
         'height' => $height,
         'wh_ratio' => $wh_ratio,
         'wh_range' => $wh_range,
         'file_size' => $file_size,
         'status' => $status,
         'created' => time(),
     );


        $sql = "insert into t_images (id, yyid, o_yyid, info_type, img_url, domain, image_hashkey, description, datei, transfer_success, dl_retry, width, height, wh_ratio, wh_range, file_size, status, created) values(:id, replace(upper(uuid()),'-',''), :o_yyid, :info_type, :img_url, :domain, :image_hashkey, :description, :datei, :transfer_success, :dl_retry, :width, :height, :wh_ratio, :wh_range, :file_size, :status, :created);";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($res);
        $last_id = $this->pdo->lastInsertId();

        return $last_id;
    }

    private function get_last_yyid($id)
    {
        $yyid = ""; 
        $sql = "select yyid from t_images where id = ? ;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array("$id"));
        $yyid = $stmt->fetchColumn();
        return $yyid;
    }

    private function update_image_status($yyid)
    {
        $yyid = ""; 
        if(empty($yyid)){
          return false;
        }
        $sql = "update t_images set status = 1 where yyid = ? ;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array("$yyid"));
        return true;
    }

}

