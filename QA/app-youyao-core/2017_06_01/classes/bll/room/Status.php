<?php

class Bll_Room_Status {

	private $roomdao;
	 public function __construct() {
        $this->roomdao    = new Dao_Room_Status();
    }

    public function set_room_status($order_id,$uid,$ip){
	    	$bll_order = 	new Bll_Order_OrderInfo();
	    	$order     = $bll_order->get_order_info_byid($order_id);
	    	if(!$order){
	    		return ;
	    	}
	    	$nid       = $order['nid'];
	    	$start_day = $order['guest_date'];
	    	$end_day   = $order['guest_checkout_date'];
	    	
	    	$room_status = $this->roomdao->get_room_status_byid($nid, $start_day, $end_day);    	
	        $params = array('nid'=>$nid,
                    'flag'=>1,
        	        'room_date'=>'',
        	        'room_num'=>'',
                    'order_id'=>$order_id,
                    'uid'=>$uid,
                    'ip'=>$ip);	
	        foreach ($room_status as $key=>$value){
	        	$params['room_date'] = $value['room_date'];
	        	$params['room_num']  = $value['room_num'];
	    	    $this->roomdao->set_room_stlog($params);        	
	        }
	    	
    }
    
    public function set_close_room($nid,$startdate,$enddate,$uid,$ip){
    	$params = array('nid'=>$nid,
    		            'flag'=>2,
    	                'room_date'=>$startdate,
    	                'room_num'=>0,
    		            'uid'=>$uid,
    		            'ip'=>$ip);	
    	$this->roomdao->set_room_stlog($params);	
    	if($enddate!=$startdate){
	    	$params['room_date' ]= $enddate;
	    	$this->roomdao->set_room_stlog($params);
    	}
    	
    }

	// days 为json格式的日期 ["2015-09-10","2015-10-01"]
	// step 1:修改之前，2:修改之后
	// flag 1:订单操作，2:关房操作，3:手机日历表，4:PC民宿后台日历表操作，5:PC房间单页日历表，6:新版日历表设置房价，7:新版批量设置，8:日历同步
	public function set_multiple_days_logs($nid, $days, $uid, $ip, $step, $flag = 1, $token, $order_id) {
		$roombll = new Bll_Room_RoomInfo();
		$roominfo = $roombll->get_room_detail_by_nid($nid);
		$params = array('nid' => $nid,
						'uid' => $uid,
						'step' => $step,
						'ip' => $ip,
						'source' => 2,
						'days' => $days,
						'flag' => $flag,
						'token' => $token,
						'order_id' => $order_id,
					);
		if($roominfo['room_price_count_check']==2) {
			$params['type'] = 'beds_num';
		}else{
			$params['type'] = 'room_num';
		}
		return $this->roomdao->set_multiple_date_log($params);
		
	}

    /**
     * 按天设置的额外价格
     * @day 日期数组 可以传一天
     * @type 1：加人价，2：早餐价
     * @uid 操作人uid
     **/
    public function set_special_additional_price($nid, $price, $days, $type, $uid, $status=1) {
        if(!$nid) return;
        if(!is_array($days)) $days = array($days);
        if(empty($days)) return;
        $params = array(
                'nid' => $nid,
                'status' => 1,
                'type' => $type,
                'uid' => $uid,
                'price' => $price,
                'create_time' => time(),
            );
        return $this->roomdao->insert_special_additional_price(array_unique($days), $params);
    }

    public function get_special_additional_price($nid, $type, $days, $status = 1){
        if(!$nid) return;

        $params = array(
                'nid' => $nid,
                'status' => $status,
            );
        if($type) $params['type']= $type;
        if(!empty($days)) $params['room_date'] = array_unique($days);

        return $this->roomdao->get_special_additional_price($params);
    }

    public function get_unavaliable_room_status_bynid($nid ,$date=null, $room_price_count_check=1) {
        if(!$nid) return;
        if(!$date) $date = date("Y-m-d");
        $type = $room_price_count_check == 2 ? "beds_num" : "room_num";
        return $this->roomdao->get_unavaliable_room_status_bynid($nid, $date, $type);
    }

	
}
