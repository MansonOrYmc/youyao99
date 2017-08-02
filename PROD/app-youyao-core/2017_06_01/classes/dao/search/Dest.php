<?php
apf_require_class("APF_DB_Factory");

class Dao_Search_Dest {

    private $slave_pdo;
    private $mc;
    private $javaapi;

    public function __construct() {
        $this->javaapi = APF::get_instance()->get_config('java_soa').'/search/recommend/hot';
        $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $this->mc      = APF_Cache_Factory::get_instance()->get_memcache();

    }

    function get_dest_config($dest_id){
        $key     = "dest_id_".$dest_id;
        $result  = $this->mc->get($key);
        $result  = $result->data;
        if(!$result){
            $sql="select * from t_dest_config ";
            $sql.="where `dest_id` = '".$dest_id."' ";
            $stmt = $this->slave_pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $this->mc->set($key,$result,'3600');
        }
        return $result;
    }

    function get_t_loc_type_locname($locid){
        $sql ="select `type_name` from t_loc_type ";
        $sql .="where  `status` = 1 ";
        $sql .= "and `locid` = '".$locid."' ";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        $locname = $result['type_name'];
        return $locname;
    }

    function get_loc_trans_locname($locid) {
        $sql ="select `name_code` from t_loc_type ";
        $sql .="where  `status` = 1 ";
        $sql .= "and `locid` = '".$locid."' ";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        $locname = Trans::t($result['name_code']);
        return $locname;
    }

    function get_t_loc_type($dest_id){

        $hot_so = new So_Hot();
        $new =$hot_so->get_hot_list($dest_id);
        return $new;
//        $sql ="select `id`,`locid`,`name_code`,`type_name` from t_loc_type ";
//        $sql .="where  `status` = 1 ";
//        $sql .= "and `dest_id` = '".$dest_id."' ";
//        $sql .= "order by rank asc , room_num desc ";
//        $stmt = $this->slave_pdo->prepare($sql);
//        $stmt->execute();
//        $result = $stmt->fetchAll();
//        return $result;

    }

    public function get_hot($dest_id){

        $params = array(
            'multilang' => Util_Language::get_locale_id(),
        );
        $url = $this->javaapi . '?' . http_build_query($params);
        $data = file_get_contents($url);
        if ($data == ''||$data == false) {//判断$file_contents是否为空
            $ch = curl_init();
            $timeout = 2;
            curl_setopt($ch, CURLOPT_URL, $this->javaapi);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $data = curl_exec($ch);
            curl_close($ch);
        }

        $result =array();
        if($data){
            $data = json_decode($data);
            if($data->code == 200) {
                foreach($data->info as $v){
                    if($dest_id=='all'){
                        $result[]=$v;
                    }elseif($v->destId==$dest_id){
                        $result[]=$v;
                    }
                }
                return $result;
            }
        }
    }
}
