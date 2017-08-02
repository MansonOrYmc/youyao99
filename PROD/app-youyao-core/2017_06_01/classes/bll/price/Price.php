<?php

class Bll_Price_Price {

    private $roominfodao;
	private $homestaybll;
    public function __construct() {
        $this->roominfodao  = new Dao_Room_RoomInfo();
		$this->homestaybll = new Bll_Homestay_StayInfo();
    }



    /*
     * 获取一段时间内的房态信息，价格优先级高于config_v2_price
     */
	public function get_room_status_tracs_price($nid, $checkin, $checkout) {
		$price = $this->roominfodao->get_room_status_tracs_by_nid_and_dateinterval($nid, $checkin, $checkout);
		return $price;
	}

	public function get_rpconfig_v2_price($nid, $dates, $uid=0) {
		if(empty($uid)){
			$roomdao = new Dao_Room_RoomInfo();
			$uid = $roomdao->get_room_uid_by_nid($nid);
		}

		$config = $this->roominfodao->fetch_room_price_config($uid);
		if(is_array($dates)){
			foreach($dates as $k=>$v){
				$price[] = $this->homestaybll->rpd_parse_v3($config['room_date'], $config['room_price'], $nid, $v);
			}
		}else{
			$price[] = $this->homestaybll->rpd_parse_v3($config['room_date'], $config['room_price'], $nid, $dates);	
		}

		return $price;
	}

    // 把rp_config这个表01-01到12-31 周一~周四 第一列的价格作为基准价
    public function get_base_price($nid, $uid) { 
        if(empty($uid)) {
			$roomdao = new Dao_Room_RoomInfo();
			$uid = $roomdao->get_room_uid_by_nid($nid);
        }

        $data = self::get_all_room_base_price_byuid($uid);

        return $data[$nid];
    }

    public function get_all_room_base_price_byuid($uid) {
        $config = $this->roominfodao->fetch_room_price_config($uid);

        $config_date = json_decode($config['room_date'], true);
        $config_price = json_decode($config['room_price'], true);

        if( is_numeric(strpos($config_date[0]['data'][0]['QDate'], "01-01,12-31")) &&   // 日期包含 01-01 到 12-31
            count(array_intersect(explode(",", $config_date[0]['data'][0]['WDate']), array(1,2,3,4))) == 4 // 星期包含1、2、3、4
        ) {
            foreach($config_price as $price_nid) {
                $data[$price_nid['rid']] = reset(explode(",", $price_nid['price']));
            }
        }

        return $data;
    }

    public function set_base_price($nid, $price, $uid, $useruid, $ip) {
        if(empty($uid)) {
            $roomdao = new Dao_Room_RoomInfo();
            $uid = $roomdao->get_room_uid_by_nid($nid);
        }

        $config = $this->roominfodao->fetch_room_price_config($uid);
        if($config) $type = "update";
        $config_date = json_decode($config['room_date'], true);
        $config_price = json_decode($config['room_price'], true);
        if(!$config_date) $config_date = array();
        if(!$config_price) $config_price = array();

        if( is_numeric(strpos($config_date[0]['data'][0]['QDate'], "01-01,12-31")) &&   // 日期包含 01-01 到 12-31
            count(array_intersect(explode(",", $config_date[0]['data'][0]['WDate']), array(1,2,3,4))) == 4 // 星期包含1、2、3、4
        ) {  // 匹配到了日期
            $result_date = $config_date;
            foreach($config_price as $k=>$price_nid) {
                $data[$price_nid['rid']] = reset(explode(",", $price_nid['price']));
                $result_price[$k] = $price_nid;
                if($price_nid['rid'] == $nid) {
                    $price_by_date = explode(",", $price_nid['price']); 
                    $price_by_date[0] = $price;
                    $result_price[$k]['price'] = implode(",", $price_by_date);
                    $match = 1;
                }
            }
        } else { // 没有匹配到日期在矩阵最左边加一列
            $result_date = array_merge(  // 和原来的日期合并
                    array(
                        array(
                            'data' => array(
                                        array(
                                            'QName' => '平日价',
                                            'QDate' => '01-01,12-31',
                                            'WDate' => '1,2,3,4,5,6,7',
                                            'qx' => '0',
                                        )
                                    )
                        )
                    ),
                    $config_date
                );
            foreach($config_price as $k=>$price_nid) { // 和原来的价格合并
                $data[$price_nid['rid']] = reset(explode(",", $price_nid['price']));
                $result_price[$k] = $price_nid;
                if($price_nid['rid'] == $nid) { $price_by_date = explode(",", $price_nid['price']); $price_by_date[0] = $price;
                    $result_price[$k]['price'] = implode(",", $price_by_date);
                    $match = 1;
                }
            }
        }
        if(!$match) {  // 如果之前没有这个房间，需要新增
            $result_price[] = array(
                'rid' => $nid,
                'price' => $price.Util_Common::placeholders(count($result_date), ","),
            );
        }
//print_r($config_date);
//print "\n";
//print_r($result_date);
//print "\n";
//print "\n";
//print_r($config_price);
//print "\n";
//print_r($result_price);
//exit();

        if($type=='update') {
            return self::update_rpconfig_byuid($uid, json_encode($result_date), json_encode($result_price), $useruid,$ip);
        }else {
            return self::insert_rpconfig($uid ,json_encode($result_date), json_encode($result_price), $useruid,$ip);
        }
        
    }

    function update_rpconfig_byuid($uid, $room_date, $room_price, $last_modify_uid, $ip, $status=1) {
        $params = array(
            'status' => $status,
            'room_date' => $room_date, // json串
            'room_price' => $room_price, // json串
            'client_ip' => $ip,
            'last_modify_uid' => $last_modify_uid,
        );

        $condition = array(
            'uid' => $uid,
        );

        $this->roominfodao->update_rpconfig($params, $condition);
    }

    function insert_rpconfig($uid, $room_date, $room_price, $last_modify_uid, $ip, $status=1) {
        $params = array(
            'uid' => $uid,
            'data' => '假日', // 全部都是这个值，懒得做区分了
            'create_date' => time(),
            'status' => $status,
            'room_date' => $room_date, // json串
            'room_price' => $room_price, // json串
            'client_ip' => $ip,
            'last_modify_uid' => $last_modify_uid,
        );

        $this->roominfodao->insert_rpconfig($params);
    }

    function check_price($uid, $nid, $checkin, $checkout) {

        if(!$uid || !$nid || !$checkin || !$checkout) return;
        $sdate = date('Y-m-d', strtotime('-7 days', strtotime($checkin)));
        $edate = $checkout;
        $status_dao = new Dao_Room_Status();
        $home_bll = new Bll_Homestay_StayInfo();
        $room_result = $status_dao->get_room_status_byid($nid,$sdate,$edate);
        $bad_price = 1;

        foreach($room_result as $v) {
            if($v['room_date'] < $checkin){
                $oprice[$v['room_date']] = $v['room_price'];
            }else{
                $tprice[$v['room_date']] = $v['room_price'];
            }
        }

        for($i=$sdate; $i<$edate; $i=date('Y-m-d',strtotime('+1 days', strtotime($i)))) {
            if(empty($oprice[$i]) && $i<$checkin) {
                $rp_config = $home_bll->get_homestay_room_price($uid, $nid, $i);
                $oprice[$i] = $rp_config['price'];
            }elseif(empty($tprice[$i]) && $i>=$checkin) {
                $rp_config = $home_bll->get_homestay_room_price($uid, $nid, $i);
                $tprice[$i] = $rp_config['price'];
            }
        }

        foreach($tprice as $k=>$v) {
            if($v < $oprice[date('Y-m-d',strtotime($k)-60*60*24*7)]/2){ // 与一个星期之前的价格做比较
                $bad_price = 0;
            }
            if($v < 100) { // 不能小于300台币
                $bad_price = 0;
            }
        }

        return $bad_price;
    }

}
