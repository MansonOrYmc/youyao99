<?php
class Solr_Rebuild{

    public function run(){
        global $argv;

        if($argv[2]=='hospital'){
            $source = 1;
            self::post_hospital_list();
            
        }elseif($argv[2]=='doctor'){
            $source = 2;
        }elseif($argv[2]=='company'){
            $source = 3;
            self::post_company_list();
        }else{
            exit();
        }
        #echo "$source \n";

    }

    public function post_hospital_list() {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $sql = "select yyid, name, grade, tel_num, address, traffic_guide, medical_guide, introduction, active_doctor, bed_num, operation_num, outpatient_num, loc_code, status, views, map_long, map_lat, map_zoom, imgs_num, created, update_date from t_hospital where 1=1 and name!='' order by hid asc;";
        $stmt = $pdo->prepare($sql);
       
        $post_url = Util_SolrJobUtil::get_post_url("/search/hospital/update");
        $stmt->execute();
        $jobs = $stmt->fetchAll();
        $job = array();

        $xml0 = Util_SolrJobUtil::build_xml_delete_by_query();
        echo "$xml0\n";
        print_r($res);
        $res = Util_SolrJobUtil::post_to_solr($post_url, $xml0);

        foreach($jobs as $k=>$j){
          echo $j['yyid']." , ".$j['name']."\n";
          $j['created'] = date("Y-m-d H:i:s", $j['created']);
          
          $xml = Util_SolrJobUtil::build_hospital_xml($j);
          echo "\n";
          $res = Util_SolrJobUtil::post_to_solr($post_url, $xml);
          print_r($res);
          echo "\n";
        }
          $xml1 = Util_SolrJobUtil::build_xml_commit();
          echo "$xml1\n";
          $xml2 = Util_SolrJobUtil::build_xml_optimize();
          echo "$xml2\n";

          $res = Util_SolrJobUtil::post_to_solr($post_url, $xml1);
          print_r($res);
          $res = Util_SolrJobUtil::post_to_solr($post_url, $xml2);
          print_r($res);

    }

    public function post_company_list() {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $sql = "select `yyid`, `name`, `tel_num`, `address`, `introduction`, `created`, `update_date` from `t_company` where status = 1 ;";
        $stmt = $pdo->prepare($sql);
       
        $post_url = Util_SolrJobUtil::get_post_url("/search/company/update");
        $stmt->execute();
        $jobs = $stmt->fetchAll();
        $job = array();

        $xml0 = Util_SolrJobUtil::build_xml_delete_by_query();
        echo "$xml0\n";
        print_r($res);
        $res = Util_SolrJobUtil::post_to_solr($post_url, $xml0);

        foreach($jobs as $k=>$j){
          echo $j['yyid']." , ".$j['name']."\n";
          $j['created'] = date("Y-m-d H:i:s", $j['created']);
          
          $xml = Util_SolrJobUtil::build_hospital_xml($j);
          echo "\n";
          $res = Util_SolrJobUtil::post_to_solr($post_url, $xml);
          print_r($res);
          echo "\n";
        }
          $xml1 = Util_SolrJobUtil::build_xml_commit();
          echo "$xml1\n";
          $xml2 = Util_SolrJobUtil::build_xml_optimize();
          echo "$xml2\n";

          $res = Util_SolrJobUtil::post_to_solr($post_url, $xml1);
          print_r($res);
          $res = Util_SolrJobUtil::post_to_solr($post_url, $xml2);
          print_r($res);

    }



}
