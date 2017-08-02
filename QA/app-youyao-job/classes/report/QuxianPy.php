<?php

class Report_QuxianPy {

    public function run() {
/*
        $q_list = $this->get_parent_info();

        foreach($q_list as $key=>$value){
            $py = $this->get_pinyin( $value['type_name'] );
            if($py!=$value['name_code']){
                $this->update_quxian_record(array('name_code'=>$py,'id'=>$value['id']));
                sleep(1);
            }
        }
*/
        /*
        $hs = $this->get_all_hs();
        foreach($hs as $key=>$value){
            $loc =  $this->get_parent_info($value['loc_typecode']);
            if(!$loc || $loc['type_code']==$value['local_code']){
                continue;
            }
            $this->update_quxian_record( array('local_code'=>$loc['type_code'],'id'=>$value['pid']));
        }
        */

        $loc = $this->get_all_loc();

        foreach($loc as $key=>$value){

            if(strpos($value['type_code'],$value['id'])===false){
                $this->update_quxian_record( array('type_code'=>"1100".$value['id'],'id'=>$value['id']));
            }

        }

    }



    public function get_parent_info($id){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from t_loc_type where locid=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($id));
        return $stmt->fetch();
    }

    public function update_quxian_record($info){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "update  t_loc_type set `type_code`='".$info['type_code']."' where id=".$info['id'].";\n";
        //$stmt = $pdo->prepare($sql);
        echo $sql;
        //return $stmt->execute(array($info['name_code'], $info['id']));
    }

    public function  get_pinyin($hz){
       $json =  Util_Common::curl_get('http://skapi.sinaapp.com/pinyin/index.php?ie=utf-8&wd='.urlencode($hz));
       $json = json_decode($json);
       $json = $json->pinyin;
       $py = '';
       foreach($json as $v){
           $py=$py.$v;
        }
        return  $py;
    }

    public function get_all_hs(){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select b.pid,b.loc_typecode,b.local_code from one_db.drupal_users a left join LKYou.t_weibo_poi_tw b  on a.poi_id = b.pid   where a.dest_id=10 and a.status=1 order by a.uid asc limit 70000,5000";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }


    public function get_all_loc(){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from  t_loc_type where dest_id=10";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

}