<?php

class Bll_Room_RoomInfo {

	private $roomdao;
    private $bookingdao;
	 public function __construct() {
        $this->roomdao    = new Dao_Room_RoomInfoMemcache();
        $this->bookingdao = new Dao_Room_BookingMemcache();
		$this->statusdao  = new Dao_Room_Status();
    }

    public function zzk_room_detail_contact_order($roomid) {
      return $this->roomdao->room_detail_contact_order($roomid);
    }

    public function get_roominfo_by_uids($arruids){
    	return $this->roomdao->get_roominfo_by_uids($arruids);
    }

    // 因为上面一个方法在dao层里加入了默认查速订且status=1的条件， 所以写了这个方法
    public function get_roominfo_by_uids_withoutspeed($arruids, $status){
        if(!is_array($arruids)) $arruids = array($arruids);
        if(empty($arruids)) return;
    	return $this->roomdao->get_roominfo_by_uids_withoutspeed($arruids, $status);
    }

    public function update_room_num_by_nid_and_date($room_num, $nid, $room_date) {
      return $this->roomdao->dao_update_room_num_by_nid_and_date($room_num, $nid, $room_date);
    }

	public function get_stock_num_by_nid_and_date($nid, $room_date) {
		$room_price_count_check = $this->room_price_count_check($nid);
		if ($room_price_count_check == 1) {
			$result['stock_field'] = 'room_num';
			$result['stock_num'] = $this->roomdao->get_room_num_by_nid_and_date($nid, $room_date);
		}
		else {
			$result['stock_field'] = 'beds_num';
			$result['stock_num'] = $this->roomdao->get_beds_num_by_nid_and_date($nid, $room_date);
		}
		return $result;
	}

    public function node_room_trac_status_new($nid,$d) {
      $room_trac = $this->roomdao->dao_node_room_trac_status_new($nid,$d);
      if($room_trac['room_num']){
        $room_num = $room_trac['room_num'];
      }else{
        $room_num = 0;
      }
      return $room_num;
    }

	public function insert_room_tracs($info, $stock_field) {
		if ($stock_field == 'room_num') {
			$result = $this->roomdao->dao_insert_room_tracs_room_num($info);
		}
		elseif ($stock_field == 'beds_num') {
			$result = $this->roomdao->dao_insert_room_tracs_beds_num($info);
		}
		else {
			$result = FALSE;
		}
		return $result;
	}

    public function update_user_order_succ_by_nid($nid, $order_succ) {
      return $this->roomdao->dao_update_user_order_succ_by_nid($nid, $order_succ);
    }

    public function get_roomstatus_by_uids($arruids,$startdate,$enddate){
    	return $this->roomdao->get_roomstatus_by_uids($arruids,$startdate,$enddate);
    }

    public function get_roombooking_by_nids($arruids,$startdate,$enddate){
        return $this->bookingdao->get_roombooking_by_nids($arruids,$startdate,$enddate);
    }

    public function get_idleroom_list($ridlist,$startdate,$enddate){
        $roomstatus_list   = $this->get_roomstatus_by_uids($ridlist,$startdate,$enddate); 
        $roombooking_list  = $this->get_roombooking_by_nids($ridlist,$startdate,$enddate);

        $tmpbooking_list   = array(); 
        $days = floor((strtotime($enddate)-strtotime($startdate))/86400);
           
        for ($i = 0; $i < $days; $i++) {
          $date = date('Y-m-d',strtotime($startdate)+86400*$i);
          foreach ($roombooking_list as $key=>$value) {
          	    
          	    if(strtotime($date)<strtotime($value['guest_checkout_date']) &&  strtotime($value['guest_date'])<= strtotime($date)){
          			$tmpbooking_list[$value['nid']][$date] = (int)$tmpbooking_list[$value['nid']][$date]+(int)$value['room_num'];
          	    	
          	    }else{
          	    	
          	    }
          }  	
        }

        
        $idleromm_list = array(); 
        foreach ($roomstatus_list as $key=>$value) {
        	if(((int)$value['room_num'] - (int)$tmpbooking_list[$value['nid']][$value['room_date']]) >0){
        		$value['idlenum'] = $value['room_num'] - $tmpbooking_list[$value['nid']][$value['room_date']];
        		$idleromm_list[]  = $value;
        	}
        }
  
        return $idleromm_list;       
    }
    
    public function get_roombooking_by_ids($arrayids){
    	return $this->bookingdao->get_roombooking_by_ids($arrayids);
    }
    
    public function get_roombooking_bystatus($status) {
        return  $this->bookingdao->get_roombooking_bystatus($status); ;
    }

    public function get_room_name_by_nid($nid) {
        return $this->roomdao->get_room_title_by_nid($nid);
    }

    public function get_room_statue_by_nid($nid) {
        return $this->roomdao->get_room_statue_by_nid($nid);
    }

    public function get_dest_id_by_nid($nid) {
        return $this->roomdao->get_dest_id_by_nid($nid);
    }

    //判断是否是速订房
    public function zzk_speed_room($nid){
      if($nid){
        $speed_room = $this->roomdao->zzk_speed_room($nid);
        $speed_room = $speed_room ? 1 : 0;
      }  
      return $speed_room;
    }

    public function room_price_count_check($nid) {
      return $this->roomdao->room_price_count_check($nid);
    }

    /*
    author:axing
    function:房间几人房函数
    param:nid
    return:房型modules
    */
    function zzk_node_module_by_field_room_beds($nid){
      return $this->roomdao->node_module_by_field_room_beds($nid);
    }

    //useed:function room bookinged
    //op:nid room_id
    //return: array()
    public function _room_order_bookinged_speed_check_new($nid,$type) {
      $unavailable_days = array();
      $bll_order_info = new Bll_Order_OrderInfo();
      if(!$type){$result = $bll_order_info->pre_pay_orders_by_roomid($nid);}

        foreach ($result as $key => $b) {
        for($m_day = 0; $m_day <= (int)$b['guest_days']-1; $m_day++){
          $rw[$key][$m_day]['num'] = $b['room_num'];
          $rw[$key][$m_day]['day'] = date('Y-m-d', strtotime("$m_day day", strtotime($b['guest_date']. " 00:00:00")));
        }
      }
      $temp = array();
      $r_days = array();
      foreach ($rw as $k => $v) {
        foreach($v as $kk=>$vv){
          $key = $vv['day'];
          $temp[$key] = isset($temp[$key]) ? $vv['num'] + $temp[$key] : $vv['num'];
        }
      }
      foreach ($temp as $k => $v) {
        $results[] = array('day' => $k, 'num' => $v);
        $r_days[] = $k;
      }
      $row_days = array();
      $stauts_promotion = array();
      //目前的房间房态  
      $sta_result = $this->roomdao->get_room_status_tracs($nid);
      foreach ($sta_result as $k=>$v) {
        if(isset($v['room_num']) && $v['room_num'] == 0){
          $unavailable_days[] = $v['room_date'];
          $v['room_num'] = 0;
        }else {
          if($v['room_num'] > 0){
            foreach($results as $kk=>$vv){
               if($vv['day']==$v['room_date']){
                  if(($v['room_num'] - $vv['num'])<=0){
                     $unavailable_days[] = $v['room_date'];
                     $v['room_num'] = 0;
                  }else{
                     $row_days[] = $v['room_date'];
                     $v['room_num'] = $v['room_num'] - $vv['num'];
                  }
               }
             }
          }
        }
        $stauts_promotion[$k] = array('room_date'=>$v['room_date'],'room_num'=>$v['room_num']);
      }
      return $stauts_promotion;
    }

    //useed:function room bookinged  按照人计算的房价
    //op:nid room_id,speed(速订房)
    //return: array()
    public function _room_order_bookinged_speed_check_new_2($nid, $type) {
      $unavailable_days = array();
      $bll_order_info = new Bll_Order_OrderInfo();
      if(!$type){  //如果是速订订单还需要判断待支付的订单
        $result = $bll_order_info->pre_pay_orders_by_roomid($nid);
      }
      foreach ($result as $key => $b) {
        for($m_day = 0; $m_day <= (int)$b['guest_days']-1; $m_day++){
          $rw[$key][$m_day]['num'] = $b['room_num'];
          $rw[$key][$m_day]['day'] = date('Y-m-d', strtotime("$m_day day", strtotime($b['guest_date']. " 00:00:00")));
        }
      }
      $temp = array();
      $r_days = array();
      //将得到的结果重新数组
      foreach ($rw as $k => $v) {
        foreach($v as $kk=>$vv){
          $key = $vv['day'];
          $temp[$key] = isset($temp[$key]) ? $vv['num'] + $temp[$key] : $vv['num'];
        }
      }
      foreach ($temp as $k => $v) {
        $results[] = array('day' => $k, 'num' => $v);
        $r_days[] = $k;
      }
      $row_days = array();
      $stauts_promotion = array();
      //目前的房间房态  
      $sta_result = $this->roomdao->get_room_status_tracs($nid);
      foreach ($sta_result as $k=>$v) {
        if(isset($v['beds_num']) && $v['beds_num']==0){
          $unavailable_days[] = $v->room_date;
          $v['room_num'] = 0;
        }else {
          if($v['beds_num'] > 0){
            $v['room_num'] = $v['beds_num'];
            foreach($results as $kk=>$vv){
              if($vv['day']==$v['room_date']){
                if(($v['beds_num'] - $vv['num'])<=0){
                   $unavailable_days[] = $v['room_date'];
                   $v['room_num'] = 0;
                }else{
                   $row_days[] = $v['room_date'];
                   $v['room_num'] = $v['beds_num'] - $vv['num'];
                }
              }
            }
          }
        }
        $stauts_promotion[$k] = array('room_date'=>$v['room_date'],'room_num'=>$v['room_num']);
      }
      return $stauts_promotion;
    }


    //useed:function room bookinged
    //op:nid room_id
    //return: array()
    public function _room_order_bookinged_check_news($nid,$room_num,$speed_room){
      $unavailable_days = array();
      $row_days = array();
      //目前的房间房态
      $sta_result = $this->roomdao->get_room_status_tracs($nid);
      foreach ($sta_result as $k=>$v) {
        //房态直接是0的情况
        if(isset($v['room_num']) && $v['room_num'] == 0){
           $unavailable_days[] = $v['room_date'];
           $row_days[] = $v['room_date'];
            //房态是大于0的情况
        }elseif($v['room_num'] > 0){
           //剩余房态
           $status_num = $v['room_num'] - $room_num;
           if($status_num<0){
              $unavailable_days[] = $v['room_date'];
              $row_days[] = $v['room_date'];

           }
        }
      }
      return $unavailable_days;
    }

    /*
    $nid房间id
    $d1入住时间
    $d2退房时间
    $room_num 房间数量
    $type是否加上待支付的订单默认不是，1是
    */
    public function node_room_trac_status_check_news($nid,$d1,$d2,$room_num=1,$type='',$guest_num=0) {
      $flag = array();
      if($nid){
        //判断是否是速订房
        $speed_room = 0;
        $speed_room = self::zzk_speed_room($nid);
        //先判断房间计算方式
        $room_price_count_check = self::room_price_count_check($nid);
        if($room_price_count_check==2){  //2start 房间按人计算
          $room_model = self::zzk_node_module_by_field_room_beds($nid); //房型
          $order_booking = self::_room_order_bookinged_speed_check_new_2($nid,$type);  //房态数据
          $order_day = array();
          foreach($order_booking as $kk=>$vv){
            $order_day[$kk] = $vv['room_date'];
          }
          if(strtotime($d1)<strtotime(date('Y-m-d',time()))){
            $flag[] = array('O',$d1);
          }else {
            $day = (strtotime($d2) - strtotime($d1))/86400;
            for($i=0;$i<$day;$i++){
              $dd = date('Y-m-d',strtotime($d1) + $i*60*60*24);
              if(!in_array($dd,$order_day)){
                if(($room_model - $guest_num)<0){  //默认的时候为房型数据,房间数量做个差值
                  $flag[] = array('F',$dd);
                }
              }else {
                foreach($order_booking as $k=>$v){
                  if($v['room_date']==$dd && ($v['room_num'] - $guest_num)<0){  //房间数量做个差值
                    $flag[] = array('F',$dd);
                  }
                }
              }
            }
          }
          //2 end 
        }else { //1 start  房间按间计算
          if($speed_room){
            $order_booking = self::_room_order_bookinged_speed_check_new($nid,$type);
            $order_day = array();
            foreach($order_booking as $kk=>$vv){
              $order_day[$kk] = $vv['room_date'];
            }

            if(strtotime($d1) < strtotime(date('Y-m-d',time()))){
              $flag[] = array('O',$d1);
            }else {
              $day = (strtotime($d2) - strtotime($d1))/86400;
              for($i=0;$i<$day;$i++){
                $dd = date('Y-m-d',strtotime($d1) + $i*60*60*24);
                if(!in_array($dd,$order_day)){
                  if((1 - $room_num)<0){  //默认的时候是一间房
                    $flag[] = array('F',$dd);
                  }
                }else{
                  foreach($order_booking as $k=>$v){
                    if($v['room_date']==$dd && ($v['room_num'] - $room_num)<0){
                      $flag[] = array('F',$dd);
                    }
                  }
                }
              }
            }
          }else {
            $order_booking = self::_room_order_bookinged_check_news($nid,$room_num,$speed_room);
              if(strtotime($d1)<strtotime(date('Y-m-d',time()))){
               $flag[] = array('O',$d1);
            }else{
              $day = (strtotime($d2) - strtotime($d1))/86400;
              for($i=0;$i<$day;$i++){
                 $dd = date('Y-m-d',strtotime($d1) + $i*60*60*24);
                 foreach($order_booking as $k=>$v){
                   if($v==$dd){
                     $flag[] = array('F',$dd);
                   }
                 }
              }
            }
          }

        }

      }

        return $flag;
    }

    /*
    计算当天的价格
    author:axing
    parm:uid,nid,date
    return:array(price_cn,price_tw)
    */
    function zzk_node_price_by_date($uid,$nid,$mydate){
       $user_price_config = array();
       $result = array();
       $enddate = date('Y-m-d',strtotime($mydate) + $day*60*60*24);
       $user_price_config = $this->roomdao->get_room_status_tracs_by_nid_and_date($nid, $mydate);
       if(!empty($user_price_config)){
         $result['price_tw'] = $user_price_config['room_price'];
         $result['price_cn'] = Util_Common::zzk_tw_price_convert($user_price_config['room_price']);
       }else{
        $bll_stay_info = new Bll_Homestay_StayInfo();
         $user_price_config = $bll_stay_info->get_homestay_room_price($uid, $nid, $mydate);
         $result['price_tw'] = $user_price_config['price'];
         $result['price_cn'] = Util_Common::zzk_tw_price_convert($user_price_config['price']);
       }
       if(empty($result)){
          $result['price_tw'] = 0;
          $result['price_cn'] = 0;
       }
       return $result;
    }

    /*
    author:axing
    function:total_node_prices_and_add_beds()
    para:nid,check_in,check_out,room_num,guest_num,oid
    return:total price
    */
    public function total_node_prices_and_add_beds($nid, $d1, $d2, $room_num = 1, $total_guest_num, $add_bed_num = 0) {
        $bll = new Bll_Price_Info();
        $vruan_price = $bll->get_total_price($nid,$d1,$d2,$room_num,$total_guest_num,$add_bed_num);
        $vruan_price_nodisc = $bll->get_total_price($nid,$d1,$d2,$room_num,$total_guest_num,$add_bed_num,false);

        //房间信息
        $room_obj = $this->zzk_room_detail_contact_order($nid);
        $guest_days = (strtotime($d2) - strtotime($d1)) / 86400;  //入住天数

        //计算加人的价格
	    $book_room_model = $room_obj->room_model;  //房型
	    $add_bed_total_price_tw = 0;
	    $total_book_room_model = $book_room_model * $room_num;  //房型*房间数
	    //更新的数据初始值
	    $data_up['add_bed_price'] = 0;
	    $data_up['add_bed_price_tw'] = 0;
	    $data_up['book_room_model'] = 0;
	    //加人的条件
	    if ($total_book_room_model && ($total_guest_num > (int) $total_book_room_model) && $room_obj->room_price_count_check == 1) {
		    //每天的加人费用
		    $add_bed_price_tw = $room_obj->add_bed_price;   //台币
		    $add_bed_price = Util_Common::zzk_tw_price_convert($room_obj->add_bed_price, $room_obj->dest_id);  //人民币
		    //如果加人加床的则更新加人信息
		    if ($room_obj->add_bed_price) {
			    $data_up['add_bed_price'] = $add_bed_price;
			    $data_up['add_bed_price_tw'] = $add_bed_price_tw;
			    $data_up['book_room_model'] = $book_room_model;
		    }
	    }
        $add_price_extra = $vruan_price_nodisc['add_price_extra'];

        return array(
            'total_count_price_tw' => $vruan_price['original_price'],
            'total_count_price_nodisc_tw' => $vruan_price_nodisc['original_price'],
            'total_count_price_cn' => $vruan_price['price_cn'],
            'total_count_price_nodisc_cn' => $vruan_price_nodisc['price_cn'],
            'date_up' => $data_up,
            'add_price_extra'=>$add_price_extra
        );
    }

    //订单价格
    //nid,d1,d2,room_num
    private function total_node_prices($nid, $d1, $d2, $room_num = 1) {
        //先判断现在的房态是否有
        $node_prices = 0;
        $day = (strtotime($d2) - strtotime($d1)) / 86400;
        if ($day > 0) {
            if ($day >= 30) {
                $node_prices = 0;
            }
            else {
                for ($i = 0; $i < $day; $i++) {
                    $dd = date('Y-m-d', strtotime($d1) + $i * 60 * 60 * 24);
                    $node_prices_2 = $this->_node_prices($nid, $dd) * $room_num;
                    if ($node_prices_2 == 0) {
                        return 0;
                    }
                    $node_prices = $node_prices + $node_prices_2;
                }
            }
        }
        else {
            $node_prices = $this->_node_prices($nid, $d1) * $room_num;
        }

        return $node_prices;
    }

    /*
    author:axing
    function:total_node_prices_man  按人计算房价
    para:$nid,check_in,check_out,guest_num
    */
    private function total_node_prices_man($nid, $d1, $d2, $guest_num = 1) {
        //先判断现在的房态是否有
        $node_prices = 0;
        $day = (strtotime($d2) - strtotime($d1)) / 86400;
        if ($day > 0) {
            if ($day >= 30) {
                $node_prices = 0;
            }
            else {
                for ($i = 0; $i < $day; $i++) {
                    $dd = date('Y-m-d', strtotime($d1) + $i * 60 * 60 * 24);
                    $node_prices_2 = $this->_node_prices($nid, $dd) * $guest_num;
                    if ($node_prices_2 == 0) {
                        return 0;
                    }
                    $node_prices = $node_prices + $node_prices_2;
                }
            }
        }
        else {
            $node_prices = $this->_node_prices($nid, $d1) * $guest_num;
        }

        return $node_prices;
    }

	public function room_price_detail($room_id, $checkin, $checkout) {
		$begin = new DateTime($checkin);
		$end = new DateTime($checkout);

		$interval = DateInterval::createFromDateString('1 day');
		$period = new DatePeriod($begin, $interval, $end);

		$price_detail = array();
		foreach ($period as $dt) {
			$date = $dt->format('Y-m-d');
			$bll_room = new Bll_Room_RoomInfo();
			$price_detail[$date] = $bll_room->_node_prices($room_id, $date);
		}
		return $price_detail;
	}

    private function _node_prices($nid, $d) {
        $prices = $this->roomdao->get_room_status_tracs_by_nid_and_date($nid, $d);
        if ($prices['room_price']) {
            return $prices['room_price'];
        }
        else {
            return $this->_month_node_price($nid, $d);
        }
    }

    private function _month_node_price($nid, $d) {
        Util_Debug::zzk_log("--------------_month_node_price---begin----------------");

        $uid = $this->roomdao->get_room_uid_by_nid($nid);
        $uid = !empty($uid) ? $uid : 0;

        $user_price_config = $this->roomdao->get_room_price_config_count($uid);
        if (isset($user_price_config) && $user_price_config > 0) {
            $bll_stay_info = new Bll_Homestay_StayInfo();
            $user_price_config = $bll_stay_info->get_homestay_room_price($uid, $nid, date('Y-m-d', strtotime($d)));
            Util_Debug::zzk_log($user_price_config);

            if (count($user_price_config)) {
                $day_name_set = array(
                    '0' => $user_price_config['w_name'],
                    '1' => $user_price_config['price'],
                    '2' => $user_price_config['price']
                );
            }
        }
        Util_Debug::zzk_log("--------------_month_node_price--end----------------");
        return empty($day_name_set[1]) ? FALSE : $day_name_set[1];
    }

    public function get_room_uid_by_nid($nid) {
        if(!$nid) return ;
        return $this->roomdao->get_room_uid_by_nid($nid);
    }

    public function get_my_rooms_advance($uid, $limit=10) {
      $solr = Util_SolrCenter::zzk_get_tw_room_se_service();
//   $offset = 0;
//   $date_list = $fq1 = "";
// //
//   if($_COOKIE['checkin'] && $_COOKIE['checkout']){
//      $checkin   = $_COOKIE['checkin'];
//      $checkout  = $_COOKIE['checkout'];
//      $date_list = zzk_make_data_list($checkin, $checkout);
//   }
//   if(!empty($date_list)){
//      $fq1 = " + date_list:*".$date_list."*";
//   }
// //
//   $params =  array(
//      'qf'=>'uid^1000',
//          'wt'=>'json',
//          'sort'=>'int_price_tw asc',
//          'defType'=>'dismax',
//          'fq'=>'status:1',
//       );
//   $results = $solr->search($uid, $offset, $limit, $params);
//   $num = $results->response->numFound;
//   $docs = array();
//   if($num>0){
//     $docs = $results->response->docs;
//   }
//   return $docs;
    }
    
    public function get_room_detail_by_nid($nid){
    	 return $this->roomdao->get_room_detail_by_nid($nid);
    }

    // 更新多个日期的 房价，房态
	public function update_room_state_by_date($params) {

		$args = array(
					'nid' => $params['nid'],
					'create_date' => time(),
					'update_date' => time(),
					'uid' => $params['uid'],
				);

		$roominfo = self::get_room_detail_by_nid($params['nid']);
		if($params['room_price']) {
			$args['room_price'] = $params['room_price'];
            // 默认房态 
            $type = "price"; // 加这个参数是因为 如果只更新房价，不更新房态，会导致房态记录为0 ，本来可以下单，却不能下单了
            if($roominfo['room_price_count_check']==2) {
                $fieldbll = new Bll_Field_Info();
                $room_model_tid = reset($fieldbll->get_field($params['nid'], "room_beds"));
                $tax = $fieldbll->get_taxonomy_term_data($room_model_tid['field_room_beds_tid']);
                $args['beds_num'] = reset($tax);
            }else {
                $args['room_num'] = "1";
            }
		}
		if($roominfo['room_price_count_check']==2 && isset($params['room_num'])) {
			$args['beds_num'] = $params['room_num'];
		}elseif(isset($params['room_num'])){
			$args['room_num'] = $params['room_num'];
		}
		
		$days = $params['days'];
			
		foreach($days as $row) {
			$args['room_date'] = $row;
			$data[] = $args;
		}

        //推给第三方房价 同步发送mq
        Util_Docking::update_aliholiday_price($params['nid'], $data);
        //实时更新solr
        Util_Common::real_time_update_solr($roominfo['uid'], "node");

		$result = $this->statusdao->insert_update_mulit_room($data, $type);

        //推给第三方房价 异步发送mq
        Util_Common::async_curl_in_terminal(Util_Common::url("/homestay/docking", 'api'), array('rid'=>json_encode($params['nid'])));

		return $result/2;

	}

    public function update_mulit_price_status($params, $type='price') {
        $uid = APF::get_instance()->get_request()->get_userobject()->uid;
        $fieldbll = new Bll_Field_Info();
        foreach($params as $row) {
		    $roominfo = self::get_room_detail_by_nid($row['nid']);
            $args = array(
                'nid'         => $row['nid'],
                'room_date'   => $row['room_date'],
                'create_date' => time(),
                'update_date' => time(),
                'uid'         => $uid,
            );
            if($type=="price"){
                $args['room_price'] = $row['room_price'];
                if($roominfo['room_price_count_check']==2) {
                    $room_model_tid = reset($fieldbll->get_field($row['nid'], "room_beds"));
                    $tax = $fieldbll->get_taxonomy_term_data($room_model_tid['field_room_beds_tid']);
                    $args['beds_num'] = reset($tax);
                }else {
                    $args['room_num'] = "1";
                }
            }else {
                if($roominfo['room_price_count_check']==2) {
                    $room_model_tid = reset($fieldbll->get_field($row['nid'], "room_beds"));
                    $tax = $fieldbll->get_taxonomy_term_data($room_model_tid['field_room_beds_tid']);
                    $args['beds_num'] = reset($tax);
                }else {
                    $args['room_num'] = "1";
                }
            }
            $data[] = $args;
            $nids[$row['nid']] = $row['nid'];

            Util_Common::async_curl_in_terminal(Util_Common::url("/homestay/docking", 'api'), array('rid'=>json_encode($row['nid'])));
            Util_Common::real_time_update_solr($roominfo['uid'], "node");
        }

		$result = $this->statusdao->insert_update_mulit_room($data, $type);

		return $result/2;

    }

	public function get_room_status_bynid($nid, $start_day, $end_day) {
		return $this->statusdao->get_room_status_byid($nid, $start_day, $end_day);
	}

	public function get_node_revision_bynid($nid) {
		if(!is_array($nid)) $nid = array($nid);
		if(empty($nid)) return;
		return $this->roomdao->get_node_revision_bynid($nid);
	}

    /**
     * gen soldout_room_dates_ss query string
     * copied from vruan
     * @author genyiwang <genyiwang@youyao.com>
     * @param $check_in
     * @param $check_out
     * @param $max
     * @return query string
     */
    public function gen_solr_room_dates_query_str($check_in, $check_out, $max=365) {
        if(empty($check_in) || !($sDate = date_create($check_in))) {
            return "";
        }
        if(empty($check_out) || !($eDate = date_create($check_out))) {
            return "";
        }

        if($sDate > $eDate) {
            list($sDate, $eDate) = array($eDate, $sDate);
        }

        $date = $sDate;
        $dates = array();
        $count = 0;
        do {
            $dates[] = $date->format('m') . $date->format('d');
            $date->add(\DateInterval::createFromDateString('1 days'));
            $count += 1;
            if ($count >= $max)
                break;
        } while ($date < $eDate);

        return implode(" OR ", $dates);
    }
	
    /**
     * get roomlist by uid, include book_enable
     * @author genyiwang <genyiwang@youyao.com>
     * @param $uid
     * @param $check_in
     * @param $check_out
     * @return array
     */
    public function get_roomlist_by_uid($uid, $check_in=null, $check_out=null, $format="json") {
        $solr = Util_SolrCenter::zzk_get_tw_room_se_service();
        $query = "uid:$uid AND id:[* TO 2000000000]";

        $params_all = array(
            "wt" => $format,
        );
        $results_all = $solr->search($query, 0, 10000, $params_all);
        $docs_all = $results_all->response->docs;

        if(empty($check_in) || !($sDate = date_create($check_in))) {
            return $docs_all;
        }
        if(empty($check_out) || !($eDate = date_create($check_out))) {
            return $docs_all;
        }

        //filter by date
        $date_qs = self::gen_solr_room_dates_query_str($check_in, $check_out);
        $params_filter = array (
            "wt" => $format,
            "fq" => "NOT soldout_room_dates_ss:($date_qs)",
            "sort" => "speed_room desc"
        );
        $results_filter = $solr->search($query, 0, 10000, $params_filter);
        $docs_filter = $results_filter->response->docs;

        //merge
        foreach($docs_filter as $row) {
            $row->bookable = true;
            $bookable_ids[] = $row->id;
            $docs_merge[] = $row;
        }
        foreach($docs_all as $row) {
            if(in_array($row->id, $bookable_ids)) {
                continue;
            }
            $row->bookable = false;
            $docs_merge[] = $row;
        }

        return $docs_merge;
    }

    public function get_image_uri_by_nid($nid, $num=30) {
        $room_images = $this->roomdao->get_room_images($nid, $num);
        $uri = array();
        foreach($room_images as $row) {
            if($row['field_image_version']) {
                $uri[] = $row['new_uri'];
            }
            else  {
                $row['uri'] = strtr($row['uri'], array(
                        'public://field/image[current-date:raw]/' => 'public/',
                        'public://' => 'public/',
                    ));
                $uri[] = $row['uri'];
            }
        }
        return $uri;
    }

    public function get_channel_discount($nid, $channel) {
        if(!$uid || !$channel) return;
        return $this->roomdao->get_channel_discount($nid, $channel);
    }

}
