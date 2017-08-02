<?php
class Util_Docking {

    public static $exchange = "open_exchange";

    public function add_homestay($data) {
        if( empty($data['uid']) || !self::check_homestay($data['uid']) ) return;
        $rk = "open.hotel.add.success";
        $data = self::homestay_data_handle($data); 
        $data['action'] = 'ADD';
        self::post($data, $rk);
    }

    public function update_homestay($hid, $data) {
        if( empty($hid) || !self::check_homestay($hid)) return;
        $rk = "open.hotel.modify.success"; 
        $data = array_merge(
                    array( 'uid' => $hid),
                    $data
                );
        $data = self::homestay_data_handle($data);
        $data['action'] = 'UPDATE';
        if( $data != array('outerId' => $hid)) {
            self::post($data, $rk);
        }
    }

    private function homestay_data_handle($data) { //处理下民宿数据
        $area_bll = new Bll_Area_Area();
        $imagesbll = new Bll_Images_Info();
        $dest_id = $data['dest_id'] ? $data['dest_id'] : $area_bll->get_home_destid_by_uid($data['uid']); 
        $area = file(CORE_PATH."/classes/includes/area");
        foreach($data as $k=>$v) {
            switch($k) {
                case "uid":
                    $result['outerId'] = $v;
                    break;
                case "name":
                    $result['name'] = $v;
                    break;
                case "dest_id":
/*
                    $result['domestic'] = ($v==12 || $v==10) ? 0 : 1;
                    if($v == 11) {
                        $result['country'] = "Japan";
                    }
                    if($v == 13) {
                        $result['country'] = "United States";
                    }
                    if($v == 15) {
                        $result['country'] = "Korea Rep.";
                    }
*/
                    $result['dest_id'] = $v;
                    break;
                case "local_code": 
/*
                    $type_code = $area_bll->get_loc_by_type_code(substr($v, 0, 7));
                    foreach($area as $area_row) {
                        $area_data = preg_split("/\s+/", $area_row);
                        if(strstr($area_data[1], $type_code['type_name'])) {
                            $result['city'] = $area_data[0];
                            break;
                        }
                    }
*/
                    $result['city'] = substr($v, 0, 7);
                    break;
                case "address":
                    $result['address'] = $v;
                    break;
                case "lon":
                    $result['longitude'] = $v;
                    break;
                case "lat":
                    $result['latitude'] = $v;
                    break;
                case "tel_num":
                    $result['tel'] = "00864008886232";
/*
                    $format_number = Util_ZzkCommon::phone_format_numbers(10, $v);
                    if(!$format_number[0]) break;
                    $format_number[0] = str_replace(array("+886", "-"), "", $format_number[0]);
                    if($data['dest_id'] == 10) {
                        $result['tel'] = "0886".$format_number[0];
                    }
                    if($data['dest_id'] == 11) {
                        $result['tel'] = "0081".$format_number[0];
                    }
                    if($data['dest_id'] == 12) {
                        $result['tel'] = "0086".$format_number[0];
                    }
                    if($data['dest_id'] == 13) {
                        $result['tel'] = "001".$format_number[0];
                    }
                    if($data['dest_id'] == 14) {
                        $result['tel'] = "00852".$format_number[0];
                    }
                    if($data['dest_id'] == 15) {
                        $result['tel'] = "0082".$format_number[0];
                    }
*/
                    break;
                case "field_data_field_aboutme":
                    $result['description'] = strip_tags($v);
                    break;
                case "field_data_field_image":
                    foreach($v as $img_v) {
                        $imgfid[] = $img_v['field_image_fid'];
                    }
                    $himages = $imagesbll->get_images_byfid($imgfid);
                    $k = 1;
                    foreach($himages as $img_row) {
                        $image[] = array(
                                'url' => Util_Image::imglink($row, 'homestay800x600.jpg'),
                                'isMain' => ($k==1? true : false),
                            );
                        $k++;
                    }
                    $result['pics'] = $image;
                    break;
            }
        }
        return $result;
    }

    public static function add_room($data) {
        if( empty($data['rid'] )) return;
        $result = self::check_room($data['rid']);
        if($result) {
            $rk = "open.roomType.add.success";
            self::post($data, $rk);
        }
    }

    public static function update_room($rid, $data) {
        if( empty($rid) ) return;
        $result = self::check_room($rid);
        if($result){
            $rk = "open.roomType.modify.success";
            self::post($data, $rk);
        }
    }

    public function add_rateplan($uid, $data) {
        if(!self::check_homestay($uid)) return;
        $dock_bll = new Bll_homestay_Docking();
        $home_row = $dock_bll->get_row_by_uid($uid);
        if($home_row['status']==0) {
            return false;
        }
        $room_bll = new Bll_Room_RoomInfo();
        $rids = explode(",", $home_row['rids']);
        $rk = "open.ratePlan.add.success";
        $refund_rule = array(
                'cancelPolicyType' => 5,
                'policyInfo' => array(
                        'timeBefore' => 24*30,
                    )
            );
        foreach($rids as $rid) {
            if(!self::check_room($rid)) return;
            $room_detail = $room_bll->get_room_detail_by_nid($rid);
            $data['room_style'] = $room_detail['room_price_count_check'];
            $data['room_num'] = $room_bll->zzk_node_module_by_field_room_beds($rid);
            $result = self::rateplan_handle($data);
            if(empty($result)) continue;
            $result = array_merge(array(
                        'action'         => 'ADD',
                        'rateplanCode'   => $rid,
                        'name'           => '预付',
                        'paymentType'    => 1,
                        'breakfastCount' => 0,
                        'cancelPolicy'   => $refund_rule,
                        'status'         => 1,
                    ), $result
                );
            $result['action'] = 'ADD';
            self::post($result, $rk);
        }
    }

    public function update_rateplan($uid, $data) {
        if(!self::check_homestay($uid)) return;
        $dock_bll = new Bll_homestay_Docking();
        $home_row = $dock_bll->get_row_by_uid($uid);
        if($home_row['status']==0) {
            return false;
        }
        $room_bll = new Bll_Room_RoomInfo();
        $rids = explode(",", $home_row['rids']);
        $rk = "open.ratePlan.modify.success";
        foreach($rids as $rid) {
            if(!self::check_room($rid)) return;
            $room_detail = $room_bll->get_room_detail_by_nid($rid);
            $data['room_style'] = $room_detail['room_price_count_check'];
            $data['room_num'] = $room_bll->zzk_node_module_by_field_room_beds($rid);
            $result = self::rateplan_handle($data);
            if(empty($result)) continue;
            $result['rateplanCode'] = $rid;
            $result['action'] = 'UPDATE';
            self::post($result, $rk);
        }
    }

    private function rateplan_handle($data) {
        foreach($data as $k=>$v) {
            if(!$v) continue;
            switch($k) {
                case 'offer_breakfast' :
                    if($v){
                        if($data['room_style'] == 1) {
                            $response['breakfastCount'] = $data['room_num'];
                        }else {
                            $response['breakfastCount'] = 1;
                        }
                    }else {
                        $response['breakfastCount'] = 0;
                    }
                    break;
                case 'status' :
                    $response['paymentType'] = $v ? 1 : 2;
                    break;
                case 'refund_rule' :
                    $refund_data = json_decode($v, true);
                    if($refund_data['refund_list'][1]['day'] && 
                       $refund_data['refund_list'][2]['day'] &&
                       $refund_data['refund_list'][2]['per']
                    ) {
                        if($refund_data['refund_list'][2]['per'] == 100) { // 退款比例填的100% （等于全退）
                            $response['cancelPolicy'] = array( 
                                    'cancelPolicyType' => 5,
                                    'policyInfo' => array(
                                            'timeBefore' => (int)$refund_data['refund_list'][2]['day']*24,
                                        )
                                );
    
                        }
                        else if ($refund_data['refund_list'][1]['day'] == $refund_data['refund_list'][2]['day']) {  // 两个日期一样，以第二个为准
                            $response['cancelPolicy'] = array( 
                                    'cancelPolicyType' => 4,
                                    'policyInfo' => array(
                                            (int)$refund_data['refund_list'][2]['day']*24 => (100 - (int) $refund_data['refund_list'][2]['per']),
                                        )
                                );
                        } else {
                            $response['cancelPolicy'] = array( 
                                    'cancelPolicyType' => 4,
                                    'policyInfo' => array(
                                            (int)$refund_data['refund_list'][1]['day']*24 => 0,
                                            (int)$refund_data['refund_list'][2]['day']*24 => (100 - (int) $refund_data['refund_list'][2]['per']),
                                        )
                                );
                        }
                    }
                    break;
            }
        }

        return $response;
    }

    public function add_price_status($data) {
        $rk = "open.rates.add.success";
        $room_info_bll = new Bll_Room_RoomInfo();
        foreach($data as $row){
            if(!self::check_room($row['nid'])) return;
            $room_info = array(
                        'out_rid'            => $row['nid'],
                        'rateplan_code'      => $row['nid'],
                        'vendor'             => null,
                );
            $dest_id = $room_info_bll->get_dest_id_by_nid($row['nid']);
            $room_info['data'] = array(
                    'use_room_inventory' => false,
                    'inventory_price'    => self::price_status_handle($row['inventory_price'], $dest_id),
                );
            $result[] = $room_info;
        }
        $response = array(
            'action' => 'ADD',
            'rateInventoryPriceMap' => $result,
        );
        self::post($response, $rk);
    }

    public function update_price_status($rid, $data) {
        if(!self::check_room($rid)) return;
        $rk = "open.rates.modify.success";
        $room_info = array(
            'out_rid'            => $rid,
            'rateplan_code'      => $rid,
            'vendor'             => null,
        );
        $room_info_bll = new Bll_Room_RoomInfo();
        $dest_id = $room_info_bll->get_dest_id_by_nid($rid);
        $room_info['data'] = array(
                'use_room_inventory' => false,
                'inventory_price'    => self::price_status_handle($data, $dest_id),
            );
        $result['rateInventoryPriceMap'] = array($room_info);
        $result['action'] = 'UPDATE';
        self::post($result, $rk);
    }

    public function price_status_handle($data, $dest_id) {
        foreach($data as $row) {
            if(!$row['room_date']) continue;
            foreach($row as $k=>$v) {
                switch($k) {
                    case 'room_date':
                        $base['date'] = $v;
                        break;
                    case 'room_price':
                        if($v > 100) {
                            $base['price'] = (int)(Util_Common::zzk_price_convert($v, $dest_id) * 100);
                            $base['status'] = 1;
                        } else {
                            $base['price'] = 99999999;
                            $base['status'] = 0;
                        }
                        break;
                    case 'room_num':
                        $base['quota'] = $v;
                        break;
                    case 'beds_num':
                        $base['quota'] = $v;
                        break;
                }
            }
            $result[] = $base;
        }
        return $result;
    }

    // 与上面的价格更新类似，后期需要java那边调整
    public function add_booking_price($data) {
        $rk = "open.bookingRates.modify.success";
        foreach($data as $row){
            if(!self::check_booking_room($row['nid'])) return;
            $room_info = array(
                'out_rid' => $row['nid'],
            );
            $room_info['data'] = array(
                'use_room_inventory' => false,
                'inventory_price'    => self::handle_booking_price($row['inventory_price']),
            );
            $result[] = $room_info;
        }
        $response = array(
            'action' => 'ADD',
            'rateInventoryPriceMap' => $result,
        );
        self::post($response, $rk);
    }

    public function handle_booking_price($data) {
        foreach($data as $row) {
            if(!$row['room_date']) continue;
            foreach($row as $k=>$v) {
                switch($k) {
                    case 'room_date':
                        $base['date'] = $v;
                        break;
                    case 'room_price':
                        if($v > 100) {
                            $base['price'] = (int)($v * 100);
                            $base['status'] = 1;
                        } else {
                            $base['price'] = 99999999;
                            $base['status'] = 0;
                        }
                        break;
                    case 'room_num':
                        $base['quota'] = $v;
                        break;
                    case 'beds_num':
                        $base['quota'] = $v;
                        break;
                }
            }
//            $base['status'] = 0; // booking 全部关房
            $result[] = $base;
        }
        return $result;
    }


    public function update_aliholiday_price($rid, $data, $type="update") {
        $item_id = self::check_aliholiday_room($rid);
        if(!$item_id) return false;
        $rk = "open.aliTravelPrice.modify.success";
        $room_info_bll = new Bll_Room_RoomInfo();
        $dest_id = $room_info_bll->get_dest_id_by_nid($rid);
        $prices = self::handle_aliholiday_price($data, $dest_id, $type);
        if(empty($prices)) return;

        $response['item_id'] = $item_id;
        if($type=="update"){
            $response['Action'] = "UPDATE";
        }else {
            $response['Action'] = "ADD";
            $response['sku']['package_name'] = '一晚住宿';
            $response['sku']['package_desc'] = '该套餐包含一晚住宿';
        }
        $response['sku']['outer_sku_id'] = $rid;
        $response['sku']['prices'] = $prices;

        self::post($response, $rk);
    }

    public function handle_aliholiday_price($data, $dest_id, $type="update") {
        foreach($data as $row) {
            $base = array();
            foreach($row as $k=>$r) {
                switch($k) {
                    case 'room_price':
                        if($r < 100) continue 3;
                        $base['price'] = (int)(Util_Common::zzk_price_convert($r, $dest_id) * 100);;
                        break;
                    case 'room_date':
                        if(strtotime($r) > strtotime("+179 days", strtotime(date("Y-m"))) - 60*60*24) continue 3;
                        $base['date'] = $r . " 20:00:00";
                        break;
                    case 'room_num' :
                        $base['stock'] = $r;
                        break;
                    case 'beds_num':
                        $base['stock'] = $r;
                        break;
                }
            }
            if($base) {
                $base['price_type'] = 1;
                if($type=="update") {
                    $base['operation'] = 3;
                }
                $result[] = $base;
            }
        }
        return $result;
    }

    public function check_homestay($uid) {
        $docking_bll = new Bll_homestay_Docking();
        return $docking_bll->check_homestay($uid);
    }

    public function check_room($rid, $uid=0) {
        $docking_bll = new Bll_homestay_Docking();
        return $docking_bll->check_room($rid, $uid);
    }

    public function check_aliholiday_room($rid, $uid=0) {
        $dock_dao = new Dao_HomeStay_Docking();
        $item_info = $dock_dao->get_aliholiday_itemid_by_roomid($rid);
        if($item_info['ali_itemid']){
            return $item_info['ali_itemid'];
        } else {
            return false;
        }
    }

    public function check_booking_room($rid, $uid=0) {
        $docking_bll = new Bll_homestay_Docking();
        return $docking_bll->check_room_by_channel($rid, 'booking', $uid);
    }

    public static function post($data, $rk, $exchange = null) {
        $msg = new MsgQueue();
        $data = json_encode($data);
//        print_r($data);
//        print "\n";
        $exchange = $exchange ? $exchange : self::$exchange;
        $msg->sender($data, $rk, $exchange);
    }
}
