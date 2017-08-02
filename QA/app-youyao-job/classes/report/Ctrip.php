<?php

class Report_Ctrip {

    public function run() {

        $room_list =  $this->get_room_list();

        $fx = array('309'=>1,'310'=>2,'315'=>3,'313'=>4,'321'=>5,'312'=>6,'322'=>7);
        $cx = array('316'=>'双人床','317'=>'单人床','318'=>'榻榻米','324'=>'大通铺','325'=>'上下铺');

        $list = array();

        foreach($room_list as $key=>$value){
            $tmp =  array();
            $tmp['title']=$value['title'];
            $tmp['fangxin']= $fx[$value['field_room_beds_tid']];
            $tmp['room_floor']=$value['room_floor'];
            $tmp['wifi']=$value['wifi'];
            $tmp['cx'] = $cx[$value['field__chuangxing_tid']];
            $hs = $this->get_homestay_detail($value['uid']);
            $tmp['name']=$hs['name'];
            $list[] = $tmp;
        }
        $str_title = "酒店名称\t房型\t入住人数\t所在楼层\t网络\t床型\t\n";
        $str = "";
        foreach($list as $key=>$value){
            $str = $str.$value['name']."\t".$value['title']."\t".$value['fangxin']."\t".$value['room_floor']."\t".$value['wifi']."\t".$value['cx']."\t\n";
        }
        echo $str_title;
        echo $str;
    }



    public function get_room_list(){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
        $sql = "select * from one_db.drupal_node a left join one_db.drupal_field_data_field_room_beds b on  a.nid=b.entity_id left join one_db.drupal_field_data_field__chuangxing c on a.nid=c.entity_id  where  a.status=1 and  a.uid in (181,204,305,305,890,1063,1102,1176,1176,1214,1432,1706,2602,2726,2944,2965,3463,3989,9112,9488,14215,15845,16696,18962,18968,19713,20145,20853,20992,22581,22772,22935,30358,48083,82179,123821,140365,298849,324894)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function get_homestay_detail($uid){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
        $sql = "select * from one_db.drupal_users where  uid='$uid'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function get_room_price($nid){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from LKYou.t_room_status_tracs where  nid='$nid'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }




//  drupal_field_data_field_room_beds




}