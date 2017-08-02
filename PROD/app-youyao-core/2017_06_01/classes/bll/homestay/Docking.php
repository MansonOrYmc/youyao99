<?php
class Bll_homestay_Docking {
    private $dockingdao;

    public function __construct() {
        $this->dockingdao  = new Dao_HomeStay_Docking();
    }

    public function check_homestay($uid) {
        $match = self::get_row_by_uid($uid);
        if( $match['status'] == 1) {
            return true;
        }else{
            return false;
        }
    }

    public function check_room($rid, $uid=0) {
        if(!$uid) {
            $room_bll = new Bll_Room_RoomInfo();
            $uid = $room_bll->get_room_uid_by_nid($rid);
        }
        $match = self::get_row_by_uid($uid);
        if( $match['status'] == 0) {
            return false;
        }
        $rids = explode(",", $match['rids']);
        if(in_array($rid, $rids)) {
            return true;
        } else {
            return false;
        }
    }

    public function check_room_by_channel($rid, $channel, $uid=0) {
        if(!$uid) {
            $room_bll = new Bll_Room_RoomInfo();
            $uid = $room_bll->get_room_uid_by_nid($rid);
        }
        $match = self::row_by_uidnchannel($uid, $channel);
        if( $match['status'] == 0) {
            return false;
        }
        $rids = explode(",", $match['rids']);
        if(in_array($rid, $rids)) {
            return true;
        } else {
            return false;
        }
    }

    public function get_all_active_list() {
        return $this->dockingdao->get_all_active_list();
    }

    public function get_row_by_uid($uid) {
        $param = array(
            'uid' => $uid,
        );
        $result = $this->dockingdao->get_row_by_param($param);
        return reset($result);
    }

    public function get_homestay_by_channel($channel) {
        $param = array(
            'channel' => $channel,
        );
        return $this->dockingdao->get_row_by_param($param);
    }

    public function row_by_uidnchannel($uid, $channel) {
        $param = array(
            'uid' => $uid,
            'channel' => $channel,
        );
        return reset($this->dockingdao->get_row_by_param($param));
    }

    public function add_homestay_byuids($uids) {
        $home_bll = new Bll_Homestay_StayInfo();
        foreach($uids as $uid) {
            $homeInfo = $home_bll->get_whole_stay_info_by_id($uid);
            Util_Docking::add_homestay($homeInfo);
            Util_Docking::add_rateplan($uid, $homeInfo);
            // 释放内存
            unset($homeInfo);
            $homeInfo = null;
        }
    }

    public function add_room_type_by_rids($rids) {
        $room_bll = new Bll_Room_Update();
        foreach($rids as $rid) {
            $complex = new Bll_Room_ComplexRoom($rid);
            $baseinfo = array(
                'speed_room' => $complex->base_room_info->speed_room,
                'title' => $complex->base_room_info->title,
                'room_floor' => $complex->base_room_info->room_floor,
                'wifi' => $complex->base_room_info->wifi,
                'roomsetting' => (array)$complex->base_room_info->roomsetting,
                'uid' => $complex->base_room_info->uid,
            );
            $fields = array(
                'field_data_field_room_beds'   => $complex->room_field_info->field_data_field_room_beds,
                'field_data_field_mianji'      => $complex->room_field_info->field_data_field_mianji,
                'field_data_field__chuangxing' => $complex->room_field_info->field_data_field__chuangxing,
            );
            $image = $complex->room_field_info->field_data_field_image;
            if($image['field_image_fid']) {
                $fields['field_data_field_image'][] = $image['field_image_fid'];
            } else {
                $k=0;
                foreach($image as $k=>$v) {
                    $k++;
                    if($k>15) break;
                    $fields['field_data_field_image'][] = $v['field_image_fid'];
                }
            }
            $room_bll->push_to_mq($rid, $fields,$baseinfo);
        }
    }

    public function add_rates_by_rids($rids) {
        $pricebll = new Bll_Price_Info();
        $roombll = new Bll_Room_RoomInfo();
        $start = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+6 months', strtotime(date('Y-m'))) - 60 * 60 * 24);
        foreach($rids as $rid) {
            $all_price = $pricebll->get_all_price_bynid($rid);
            $room_detail = $roombll->get_room_detail_by_nid($rid);
            $inventory_price = array();
            for ($date = $start; strtotime($date) <= strtotime($end); $date = date('Y-m-d', strtotime("+1 days", strtotime($date)))) {
                if ($all_price[$date]) {
                    $data = array(
                        'room_date' => $date,
                        'room_price' => intval($all_price[$date]['price']),
                        'room_num' => intval($room_detail['room_price_count_check'] == 1 ? $all_price[$date]['room_num'] : $all_price[$date]['beds_num']),

                    );
                } else {
                    $data = array(
                        'room_date' => $date,
                        'room_price' => 0,
                        'room_num' => 0,
                    );
                }
                $inventory_price[] = $data;
            }
            if(empty($inventory_price)) continue;
            $result = array(
                'nid' => $rid,
                'inventory_price' => $inventory_price,
            );
            if(!empty($result)) {
                Util_Docking::add_price_status(array($result));
            }
            // 释放内存
            unset($result);
            $result = null;
        }
    }

    public function add_booking_rates_by_rids($rids) {
        $pricebll = new Bll_Price_Info();
        $roombll = new Bll_Room_RoomInfo();
        $start = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+13 months', strtotime(date('Y-m'))) - 60 * 60 * 24);
//        $end = date('Y-m-d', strtotime('+1 days'));
        foreach($rids as $rid) {
            $room_detail = $roombll->get_room_detail_by_nid($rid);
            if(!$this->check_room_by_channel($rid, 'booking', $room_detail['uid'])) continue;
            $all_price = $pricebll->get_all_price_bynid($rid);
            $inventory_price = array();
            for ($date = $start; strtotime($date) <= strtotime($end); $date = date('Y-m-d', strtotime("+1 days", strtotime($date)))) {
                if ($all_price[$date]) {
                    $data = array(
                        'room_date' => $date,
                        'room_price' => intval($all_price[$date]['price']),
                        'room_num' => intval($room_detail['room_price_count_check'] == 1 ? $all_price[$date]['room_num'] : $all_price[$date]['beds_num']),

                    );
                } else {
                    $data = array(
                        'room_date' => $date,
                        'room_price' => 0,
                        'room_num' => 0,
                    );
                }
                if($date <= date('Y-m-d', strtotime('+1 hours'))) $data['room_num'] = 0; // 日本当地时间当天不可订
                $inventory_price[] = $data;
            }
            if(empty($inventory_price)) continue;
            $result = array(
                'nid' => $rid,
                'inventory_price' => $inventory_price,
            );
            if(!empty($result)) {
                Util_Docking::add_booking_price(array($result));
            }
            // 释放内存
            unset($result);
            $result = null;
        }
    }

    public function send_unlist_room($nid, $uid = 0) {
        if(!$uid) {
            $room_bll = new Bll_Room_RoomInfo();
            $uid = $room_bll->get_room_uid_by_nid($rid);
        }
        $match = self::get_row_by_uid($uid);
        $rids = explode(",", $match['rids']);
        $new_rids = array();
        foreach($rids as $rid) {
            if($rid == $nid) continue;
            $new_rids[] = $rid;
        }
        $separator_rid = implode(",", $new_rids);
        
        $this->update_rows_byuid($uid, array("rids" => $separator_rid));
        //$to = 'sophieliu@youyao.com';
        //$subject = '有民宿把房间改成非速订了！！！';
        //$body = "有民宿把房间改成非速订了，这个房间是和第三方对接合作的，赶紧去第三方平台下架 房间地址：http://www.youyao.com/r/".$nid;
        //$from = "noreply@youyao.com";
        //Util_SmtpMail::send($to, $subject, $body, $from);

        return true;
    }

    public function send_list_room($nid, $uid = 0) {
        if(!$uid) {
            $room_bll = new Bll_Room_RoomInfo();
            $uid = $room_bll->get_room_uid_by_nid($rid);
        }
        $match = self::get_row_by_uid($uid);
        $rids = explode(",", $match['rids']);
        $new_rids = array();
        foreach($rids as $rid) {
            $new_rids[] = $rid;
        }
        $new_rids[] = $nid;
        $separator_rid = implode(",", $new_rids);
        $this->update_rows_byuid($uid, array("rids" => $separator_rid));
        return true;
    }
 
    public function update_rows_byuid($uid, $params) {
        if(!$uid || empty($params)) return false;
        $data = array();
        $fields= array(
                'uid',
                'rids',
                'status',
                'channel',
                'operator_uid',
            );
        foreach($params as $key=>$row) {
            if(in_array($key, $fields)) {
                $data[$key] = $row;
            }
        }
        return $this->dockingdao->update_rows_by_uid($uid, $data);
    }

    public function get_homestay_docking_by_channel($channel) {
        return $this->dockingdao->get_homestay_docking_by_channel($channel);
    }

    public function get_room_list_by_channel($channel) {
        return explode(",", reset($this->dockingdao->get_room_list_by_channel($channel)));
    }

    public function add_temairazu_record($uid) {
        if(!$uid) return;
        return $this->dockingdao->add_docking_record($uid, "", "temairazu", 1);
    }

    // 阿里度假要用到其他的route key
    public function add_aliholiday($nids, $uid=null) {
        $home_bll = new Bll_Homestay_StayInfo();
        $dock_bll = new Dao_HomeStay_Docking();
        if($uid != null) $homeinfo = $home_bll->get_whole_stay_info_by_id($uid);

        foreach($nids as $nid) {
            $roominfo = new Bll_Room_ComplexRoom($nid);
            if($uid != $roominfo->get_uid()) {
                $uid = $roominfo->get_uid();

                // 重新赋值民宿信息
                unset($homeinfo);
                $homeinfo = null;
                $homeinfo = $home_bll->get_whole_stay_info_by_id($uid);
            }
            $data = self::aliholiday_handle($homeinfo, $roominfo);
            $response = self::send_to_java($data);
            $item_id = $response['alitrip_travel_item_base_add_response']['travel_item']['item_id'];
            if($item_id) {
                $dock_bll->add_aliholiday_mapping($nid, $uid, $item_id);
            }
//            print_r($homeinfo);

            // 释放内存
            unset($roominfo);
            $roominfo = null;
        }
    }

    public function update_aliholiday_price($rids) {

        $pricebll = new Bll_Price_Info();
//        $roombll = new Bll_Room_RoomInfo();
        $start = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+179 days', strtotime(date('Y-m'))) - 60 * 60 * 24);
        foreach($rids as $rid) {
            $all_price = $pricebll->get_all_price_bynid($rid, $start, $end);
            // 按人卖的情况不上传
//            $room_detail = $roombll->get_room_detail_by_nid($rid);
            $inventory_price = array();
            for ($date = $start; strtotime($date) <= strtotime($end); $date = date('Y-m-d', strtotime("+1 days", strtotime($date)))) {
                if ($all_price[$date]) {
                    $data = array(
                        'room_date' => $date,
                        'room_price' => intval($all_price[$date]['price']),
                        'room_num' => $all_price[$date]['room_num'],

                    );
                }
                $inventory_price[] = $data;
//                if(count($inventory_price)==2) break;
            }
            if(empty($inventory_price)) continue;
//            print_r($rid);
//            print_r($inventory_price);
            Util_Docking::update_aliholiday_price($rid, $inventory_price, "add");
            // 释放内存
            unset($inventory_price);
            $inventory_price = null;
        }

    }

    private function aliholiday_handle($homeinfo, $roominfo) {
        $base_info = array();
        $booking_rules = array();
        $freedom_item_ext = array();
        $refund_indo = array();
        $default_desc = "欢迎来到我家~卸下旅行中的疲惫，放松身心。不需要刻意安排进程，随性的逛逛，和当地人聊聊天，体验一天他乡的生活。很高兴来到我家，身在异乡的你完全不需要感觉拘谨，就当来拜访多年未见的朋友，欢迎回家！";
        $area_bll = new Bll_Area_Area();

        $base_info['trip_max_days'] = 1;
        $base_info['trip_min_days'] = 1;
        $content = trim($roominfo->get_content());
        $base_info['desc'] = empty($content) ? $default_desc : $content;
        $base_info['item_type'] = 5;
//        $dest_config = $area_bll->get_dest_config_by_destid($homeinfo['dest_id']);
//        $base_info['prov'] = $dest_config['dest_name'];
        $base_info['prov'] = "上海";

        if($homeinfo['local_code']) { // 有填新的地址
            $to_locations = $area_bll->get_loc_by_type_code(substr($homeinfo['local_code'], 0, 7));
        }else{ // 没填新的地址
            $to_locations = $area_bll->get_loc_type_by_locid(str_replace("1,8,553,", "", $homeinfo['loc_typecode']));
        }
        $to_locations = $to_locations['type_name'];

        $base_info['to_locations'] = $to_locations;
//        $base_info['city'] = $to_locations;
        $base_info['city'] = "上海";
        $base_info['out_id'] = $roominfo->get_nid();
        $base_info['accom_nights'] = 1;
        $base_info['title'] = "【度假】" . mb_substr($homeinfo['name'] . "-" . $roominfo->get_room_name(), 0, 26, "utf-8");

        $pics = array();
        $room_image = $roominfo->room_field_info->field_data_field_image;
        if($room_image['field_image_fid']) {
            $fids[] = $room_image['field_image_fid'];
        } else {
            $k=0;
            foreach($room_image as $k=>$v) {
                $k++;
                $fids[] = $v['field_image_fid'];
                if($k>2) break;
            }
        }
        foreach($homeinfo['field_data_field_image'] as $v) {
            if($k > 5) break;
            $fids[] = $v['field_image_fid'];
            $k++;
        }
        $img_bll = new Bll_Images_Info();
        $util_image = new Util_Image();
        $image_file = $img_bll->get_images_byfid($fids);
        foreach($image_file as $row) {
            $pics[] = $util_image->get_imgsrc_by_name($row, "homepic800x600.jpg");
        }
        $base_info['pic_urls'] = $pics;

        $booking_rules[] = array(
            'rule_type' => 'Fee_Included',
            'rule_desc' => '住宿费用一晚',
        );
        $booking_rules[] = array(
            'rule_type' => 'Fee_Excluded',
            'rule_desc' => '除费用包含外的其他个人额外消费',
        );
        $refund_data = json_decode($homeinfo['refund_rule'], true);
        if($refund_data['refund_list'][1]['day'] &&
           $refund_data['refund_list'][2]['day'] &&
           $refund_data['refund_list'][2]['per']
        ) {
            if($refund_data['refund_list'][2]['per'] == 100) { // 退款比例填的100% （等于全退）
                $refund_str = "入住" . ($refund_data['refund_list'][2]['day'] + 1)."天前可全额退款";
                $refund_regulations[] = ($refund_data['refund_list'][2]['day'] + 1) . "_" . ( $refund_data['refund_list'][2]['day'] + 1) . "_0"; // 全退
                $refund_regulations[] = $refund_data['refund_list'][2]['day'] . "_1_100"; // 不退

            }
            else if ($refund_data['refund_list'][1]['day'] == ($refund_data['refund_list'][2]['day'] + 1 )) {  // 两个日期一样，以第二个为准
                $refund_str = "入住" + ($refund_data['refund_list'][2]['day'] + 1). "天前可退{$refund_data['refund_list'][2]['per']}%";
                $refund_regulations[] =   // 部分退 
                    $refund_data['refund_list'][2]['day'] + 1 . "_" . 
                    $refund_data['refund_list'][2]['day'] + 1 . "_" . 
                    (100 - $refund_data['refund_list'][2]['per'] ) ;
                $refund_regulations[] = $refund_data['refund_list'][2]['day'] . "_1_100"; // 不退

            } else {
                $refund_str = "入住{$refund_data['refund_list'][1]['day']}天前全额退款， 入住前{$refund_data['refund_list'][1]['day']}到" . ($refund_data['refund_list'][2]['day'] + 1) . "天可退{$refund_data['refund_list'][2]['per']}%";
                $refund_regulations[] =   // 全退
                    ($refund_data['refund_list'][1]['day'] + 1 ) . "_" . 
                    ($refund_data['refund_list'][1]['day'] + 1 ) . "_" . 
                    "0";
                $refund_regulations[] =  // 部分退
                    $refund_data['refund_list'][1]['day'] . "_" . 
                    ( $refund_data['refund_list'][2]['day'] + 1 ) . "_" . 
                    (100 - $refund_data['refund_list'][2]['per'] ) ;
                $refund_regulations[] = $refund_data['refund_list'][2]['day'] . "_1_100"; // 不退

            }
        } else {
            $refund_str = $homeinfo['field_data_field__dingfangshuoming'];
            $refund_regulations[] = "30_30_0";   // 30天之前不可退
            $refund_regulations[] = "30_1_100";  // 30天之内不可退

        }

        $booking_rules[] = array(
            'rule_type' => 'Order_Info',
            'rule_desc' => $refund_str,
        );

        $freedom_item_ext['hotel_infos'] = array();
        $aboutme = trim($homeinfo['field_data_field_aboutme']);
        $freedom_item_ext['hotel_infos']['hotel_desc'] = empty($aboutme) ? $default_desc : $homeinfo['field_data_field_aboutme'];
        $freedom_item_ext['hotel_infos']['hotel_days'] = 1;

        $hotels = array();
        $hotels['cn_name'] = $homeinfo['name'] . "-" . $roominfo->get_room_name();
        Util_Language::$lang_id = 12;
        $hotels['house_type'] = $roominfo->get_room_bed_type();
        $hotels['hotel_level'] = '无星级';
        $hotels['city'] = $to_locations;
        $hotels['poi'] = round($homeinfo['lon'], 6) . "," . round($homeinfo['lat'], 6);
        $hotels['poi_resource'] = ($homeinfo['dest_id'] == 10 ? 'AMAP' : 'GOOGLE' ) ;
        $freedom_item_ext['hotel_infos']['hotel_list'][] = $hotels;
        $freedom_item_ext['other_infos'] = array(
            array(
                'desc' => '无',
                'type' => 8,
            ),
        );

        $refund_info['refund_regulations'] = $refund_regulations;
        $refund_info['refund_type'] = 1;

        $sales_info = array(
            'sale_type'    => 0,
            'confirm_type' => 2,
            'confirm_time' => 3,
        );

        $result = array(
            'base_info'        => $base_info,
            'booking_rules'    => $booking_rules,
            'freedom_item_ext' => $freedom_item_ext,
            'refund_info'      => $refund_info,
            'sales_info'       => $sales_info,
        );

        return $result;

    }

    public function send_to_java($data) {

        $url = APF::get_instance()->get_config("java_add_item") . "/aliTravelService/addItem";
        $curl_response = Util_Curl::post($url, json_encode($data), array("Content-Type"=>"application/json;"));
//        print_r($url);
//        print_r(json_encode($data));
//        print_r($curl_response);
        if($curl_response['code']==200) {
            return json_decode($curl_response['content'], true);
        } else {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_response, true));
        }
    }

}
