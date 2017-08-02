<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 16/3/1
 * Time: 下午12:14
 */
class So_SimpleRoom
{
    public $name;
    public $hotelID;
    public $id;
    public $dest_id;
    public $room_price_count_check; // 按人卖还是按间卖,1是按间卖
    public $imgs;

    /**
     * @param mixed $imgs
     */
    public function addImg($img)
    {
        $this->imgs[] = $img;
    }

    public function __construct($name,$destId,$room_price_count_check,$img)
    {
        $this->name = $name;
        $this->dest_id = $destId;
        $this->room_price_count_check = $room_price_count_check;
        $imgs = explode(",",$img);
        foreach($imgs as $img){
            $this->addImg($img);
        }
    }



    public function __destruct()
    {
        if(empty($this->id)){
            $time = time();
            if(!empty($this->hotelID) and !empty($this->name) and !empty($this->dest_id) and !empty($this->room_price_count_check)){
                $sql = "insert one_db.drupal_node (nid,uid,title,status,dest_id,type,language,created,comment,room_price_count_check)";
                $sql .= " values ( null ,'{$this->hotelID}','{$this->name}','0','{$this->dest_id}','article','zh-hans','$time','2','{$this->room_price_count_check}' )";
                $this->id = DB::execSql($sql,true);
                if(!empty($this->id)){
                    $sql = "insert one_db.drupal_node_revision (nid,uid,title,comment) values ('{$this->id}','{$this->hotelID}','{$this->name}','2')";
                    $vid = DB::execSql($sql,true);
                    $sql = "update one_db.drupal_node set vid = '$vid' where nid = '{$this->id}' ";
                    DB::execSql($sql,true);

                    foreach($this->imgs as $key=>$img){

                        $src = Util_Curl::http_get_data($img);
                        $tmpimg = "/tmp/tmp.png";
                        $fp = fopen($tmpimg,'w');
                        fwrite($fp,$src);
                        $hashcode=Util_Curl::upload_curl_pic($tmpimg);
                        $hashcode = json_decode($hashcode);
                        $hashcode = $hashcode->url[0];
                        $sql = "INSERT INTO  `t_img_managed` (`uid`,`uri`,`timestamp`,`status`,`source`) ";
                        $sql .= " values ('198611','$hashcode','" . time() . "','0','0')";
                        $r = DB::execSql($sql,true);
                        $sql = "insert into one_db.drupal_field_data_field_image ";
                        $sql .= " (`entity_type`,`bundle`,`entity_id`,`revision_id`,`language`,`delta`,`field_image_fid`,`field_image_version`) ";
                        $sql .= " values ( 'node','article','{$this->id}','{$this->id}','und','$key','$r','1') ";
                        DB::execSql($sql,true);
                    }

                }


            }
        }
    }
}
