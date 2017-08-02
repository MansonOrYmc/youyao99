<?php
/*
 * 房间信息变更业务类
 * Vruan负责
 * 如需使用，请告知Vruan
 * */
class Bll_Room_Update {
    private $roomdao;
    private $bookingdao;
    public function __construct() {
        $this->roomdao    = new Dao_Room_RoomInfo();
        $this->miniroomdao = new Dao_Minimumstay_Minimumstay();
    }



    public function get_minimum_stay_dates($nid){
        return $this->roomdao->get_minimum_stay_dates($nid);
    }

    //只需要增加房间名字就好
    public function add_new_room($uid,$tltle,$dest_id,$status=0){
        return $this->roomdao->add_new_room($uid,$tltle,$dest_id,$status);
    }
    /*
     * 写入房间数据
     * 需要注意几点
     * ①最短入住需要关联其他时段表
     * ②速定需要关联其他表
     * ③node基本信息只需要update就好
     * ④field字段保存等
     * */
    public function update_room_record($nid, $fields,$baseinfo,$user, $type='room') {
        self::push_to_mq($nid, $fields,$baseinfo);
        $config = array(0=>"非速订",1=>"速订");
        if($type == 'user'){
            $hid = $nid;
            $this->roomdao->set_room_base_info_byuid($hid, $baseinfo);
        } else {
            $hid = Bll_Room_Static::get_uid_by_nid($nid);
            $speed = Bll_Room_Static::is_speed_room($nid);
            if(!$fields['field_data_field_image']){unset($fields['field_data_field_image']);}
            $this->miniroomdao->set_minimumstay_date($nid,$baseinfo['uid'],$baseinfo['ministay_dates']);
            $this->miniroomdao->set_speed_date($nid,$baseinfo['uid'],$baseinfo['speed_dates']);
            $price_bll = new Bll_Price_Price();
            $ip = APF::get_instance()->get_request()->get_remote_ip();
            $price_bll->set_base_price($nid, $baseinfo['base_price'], '', $user->uid, $ip);
            unset($baseinfo['ministay_dates']);
            unset($baseinfo['uid']);
            unset($baseinfo['speed_dates']);
            unset($baseinfo['base_price']);
            $this->roomdao->zzk_set_room_base_info($nid,$baseinfo);
            $match = $this->match_field_params($fields); // 依次查询各表字段匹配， 优先级由上到下
            $match['entity_id']=$nid;
            $fieldbll = new Bll_Field_Info();
            $fieldbll->write_field_record($match,'node','article');
            if($baseinfo['speed_room'] != $speed && isset($baseinfo['speed_room'])){
                $bll = new Bll_Homestay_StayInfo();
                $bll->insert_homestay_log($hid,array("将房间($nid:".$baseinfo['title'].")从".$config[$speed]."改成了".$config[$baseinfo['speed_room']]));
                $dock_bll = new Bll_homestay_Docking();
                if($baseinfo['speed_room'] == 0 && $dock_bll->check_room($nid, $hid)) {
                    $dock_bll->send_unlist_room($nid, $hid);
                }
                if($baseinfo['speed_room'] == 1 && $dock_bll->check_homestay($hid)) {
                    $dock_bll->send_list_room($nid, $hid);
                }
            }
        }
        Util_Common::real_time_update_solr($hid, 'node');

    }

    private function match_field_params(&$params) {

        $fieldbll = new Bll_Field_Info();
        $fieldRecord = array();
        $keys = array_keys($params);
        $tables = $fieldbll->get_field_table_column('node', 'article');
        foreach($tables as $fieldArr) {
            foreach($fieldArr as $tableName=>$column) {
                if(in_array($tableName,$keys)) {
                    $value = $params[$tableName];
                    $fieldRecord[$tableName][reset($column)] = $value;
                    unset($params[$tableName]);
                }
            }
        }
        return $fieldRecord;
    }

    public function push_to_mq($nid, $fields,$baseinfo){
        //目前根据房间是否为速订来判断
        if($baseinfo['speed_room'])
        {
            $field_bll = new Bll_Field_Info();
            $fid = $fields['field_data_field_room_beds']['field_room_beds_tid'];
            $maxpeople = ($field_bll->get_taxonomy_term_data($fid));
            $maxpeople = $maxpeople[$fid];
            if($maxpeople == "10+"){$maxpeople = 10;}
            $mianji = $fields['field_data_field_mianji']['field_mianji_value'];
            $fid = $fields['field_data_field__chuangxing']['field__chuangxing_tid'];
            $bed_type = ($field_bll->get_taxonomy_term_data($fid));
            $bed_type=$bed_type[$fid];

            if($fields['field_data_field_image']['field_image_fid']){
                //说明只有一个图片
                $fids[]=$fields['field_data_field_image'];
            }else{
                foreach($fields['field_data_field_image'] as $v){
                    $fids[]=$v;
                }
            }
            $imagesbll = new Bll_Images_Info();
            $pics = $imagesbll->get_images_byfid($fids);
            foreach($pics as &$va){
                $va = Util_Image::imglink($va, 'homepic800x600.jpg');
            }
            $dest_id = Bll_Room_Static::get_destid_by_nid($nid);
            if($dest_id = 10||$dest_id = 15){$mianji.="坪";}else{$mianji.="平方米";}
            $data = array(
                'action'=>'UPDATE',
                'outerId'=>$nid,
                'name'=>$baseinfo['title'],
                'maxOccupancy'=>$maxpeople,
                'area'=>$mianji,
                'floor'=>$baseinfo['room_floor']."层",
                'bedType'=>$bed_type,
                'bedSize'=>empty($baseinfo['bed_style_remark'])?"":$baseinfo['bed_style_remark']."米",
                'internet'=>$baseinfo['wifi'],
                'service'=>$baseinfo['roomsetting'],
                'windowType'=>(!($baseinfo['roomsetting'][36]))?1:0,
                'outHid'=>$baseinfo['uid'],
                'pics'=>$pics,
            );
            Util_Docking::update_room($nid ,$data);
        }
    }

}
