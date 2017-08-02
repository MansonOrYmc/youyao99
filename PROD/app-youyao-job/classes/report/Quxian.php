<?php

class Report_Quxian {

    public function run() {

        $q_list[] = "京都	京都市北区、京都市上京区、京都市左京区、京都市中京区、京都市东山区、京都市下京区、京都市南区、京都市右京区、京都市伏见区、京都市山科区、京都市西京区";
        $q_list[] = "大阪	大阪市都岛区、大阪市福岛区、大阪市此花区、大阪市西区、大阪市港区、大阪市大正区、大阪市天王寺区、大阪市浪速区、大阪市西淀川区、大阪市东淀川区、大阪市东成区、大阪市生野区、大阪市旭区、大阪市城东区、大阪市阿倍野区、大阪市住吉区、大阪市东住吉区、大阪市西成区、大阪市淀川区、大阪市鹤见区、大阪市住之江区、大阪市平野区、大阪市北区、大阪市中央区";

        foreach($q_list as $key=>$value){
            $q_s =  explode('	',$value);
            $pn =  $q_s[0];
            $parent_info = $this->get_parent_info($pn);
            if(false){
                $py = $this->get_pinyin( $pn );
                $max_id = $this->get_maxid_info();
                $info = array('dest_id'=>11,'type_name'=>$pn,'parent_id'=>11,'type_code'=>'123','status'=>'1','rank'=>'45','name_code'=>$py,'locid'=>$max_id+2);
                $last_id  = $this->set_quxian_record($info);
                $parent_info = $this->get_parent_info($pn);
                $ty_code = $this->get_type_code($last_id,$parent_info['id']);
                $this->update_quxian_record(array('parent_id'=>$parent_info['id'],'type_code'=>$ty_code,'id'=>$last_id));
                $parent_info = $this->get_parent_info($pn);
            }


            $xian_str =  $q_s[1];
            $xian  =  explode('、',$xian_str);  //var_dump($q_s); exit();
            echo "\n";
            foreach($xian as $k=>$v){
                $v = trim($v);
                if(!$v){
                    continue;
                }

                $py = $this->get_pinyin( $v );
                $max_id = $this->get_maxid_info();
                $info = array('dest_id'=>11,'type_name'=>$v,'parent_id'=>$parent_info['id'],'type_code'=>'123','status'=>'1','rank'=>'45','name_code'=>$py,'locid'=>$max_id+2);
                if($ex = $this->get_parent_info($v)){
                    $last_id = $ex['id'];
                }else{
                    $last_id  = $this->set_quxian_record($info);
                }

                $ty_code = $this->get_type_code($last_id,$parent_info['id']);
                $this->update_quxian_record(array('parent_id'=>$parent_info['id'],'type_code'=>$ty_code,'id'=>$last_id));
                echo $v."  ";
            }
            // $ty_code = $this->get_type_code($parent_info['id']);
           // $this->update_quxian_record(array('parent_id'=>'11','type_code'=>$ty_code,'id'=>$parent_info['id']));


        }

//http://skapi.sinaapp.com/pinyin/index.php?wd=%E5%9E%A6%E4%B8%81&ie=utf-8

    }



    public function get_parent_info($name){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from t_loc_type where type_name = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($name));
        return $stmt->fetch();
    }

    public function set_quxian_record($info){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "insert into t_loc_type(dest_id,type_name,parent_id,type_code,status,rank,name_code,locid) values(?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);  var_dump($info);
        $stmt->execute(array($info['dest_id'], $info['type_name'], $info['parent_id'], $info['type_code'],
            $info['status'],$info['rank'],$info['name_code'],$info['locid']));
        return $pdo->lastInsertId();
    }

    public function update_quxian_record($info){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "update  t_loc_type set `parent_id`=?,`type_code`=? where id=?";
        $stmt = $pdo->prepare($sql);
        echo $sql;
        return $stmt->execute(array($info['parent_id'], $info['type_code'], $info['id']));
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

    public function  get_type_code($id,$parent_id=''){
        if(strlen($id)==3){
            $id = "00".$id;
        }elseif(strlen($id)==4){
            $id = "0".$id;
        }

        if(strlen($parent_id)==3){
            $parent_id = "00".$parent_id;
        }elseif(strlen($parent_id)==4){
            $parent_id = "0".$parent_id;
        }

        if($parent_id){
            $code =  "11".$parent_id.$id;

        }else{
            $code =  "11".$id;
        }

        return $code;

    }


    public function get_maxid_info(){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select max(locid) from t_loc_type";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }


}