<?php

/**
 * Created by PhpStorm.
 * User: chanlevel
 * Date: 15/7/22
 * Time: 下午3:51
 */
apf_require_class("APF_DB_Factory");

class Util_Activity {

	public static function  Activity_Format($price, $options = NULL) {
		return NULL;
		if (!Util_Activity::is_firstappdiscount_available()) {
			return NULL;
		}

		$uid = $options['mobile_userid'];
		$homestayid = $options['homestayid'];
		$roomid = $options['roomid'];

		//  $jiufen = array( );
		if ($uid > 0) {
			$bll_order = new Bll_Order_OrderInfo();
			$count = $bll_order->get_AppPay_count($uid);
			// if(!empty($count))return null;
			if (!empty($uid) && $count > 0) {
				return NULL;
			}
			$first = TRUE;
		}

		$price = intval($price);
		if (($_REQUEST['os'] == 'ios' && $_REQUEST['version'] == '4.9.1') || ($_REQUEST['multiprice'] == 10)) {
			return NULL;
		}
		else {
			$discount = 5;
		}
		$newprice = $price - $discount;
		$activity_notice = '首单优惠立减' . $discount;
		$activity_name = "首单优惠";
		$activity_code = 'firstApp';
		$url = "http://taiwan.youyao.com";

//        if (in_array($homestayid, $jiufen)) {
//            $discount = $price * 0.1;
//            if ($first) $discount += 15;
//            $discount=intval($discount);
//            $activity_name = '今日特价';
//            $activity_notice = '今日特价立减' . $discount;
//            $newprice = $price - $discount;
//        }


		return array(
			'price' => $price,
			'discount' => $discount,
			'newprice' => $newprice,
			'activity_notice' => $activity_notice,
			'activity_name' => $activity_name,
			'url' => $url,
			'activity_code' => $activity_code
		);

	}

	public static function checkorder($order_info) {
		$bll_order = new Bll_Order_OrderInfo();
		$same_order = $bll_order->get_same_order_byphoneuid_apppay($order_info->guest_telnum, $order_info->guest_uid);
		if (empty($same_order)) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	public static function is_firstappdiscount_available() {
		if (time() < strtotime('2016-09-30')) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	public static function is_coupon_pre_room_night() {
		return FALSE;
		if (time() > strtotime('2015-08-20')) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	/*
	 * 活动获取token
	 * */
	public static function get_token($params) {
		return md5(date('y-m-d', time()) . $params['activity_name'] . $params['type'] . $params['id']);
	}

	/*
	 * 房间加锁,订单id
	 * */
	public static function delete_room($roomid, $oid, $status) {
		$dao = new Dao_Activity_Activity();
		$dao->delete_room($roomid, $oid, $status);
	}

	public static function is_activity_order($order_id) {
		$dao = new Dao_Activity_Activity();
		$result = $dao->get_activity_order($order_id);
		if (!empty($result)) {
			return $result;
		}
		else {
			return FALSE;
		}
	}

	/*
	 * 判断活动房间库存
	 * */
	public static function is_room_exist($roomid, $order_id) {
		$dao = new Dao_Activity_Activity();
		$r = $dao->get_room($roomid);
		if ($r) {
			if ($r['status'] == 2) {
				if (time() - $r['create_time'] > 1800 || $order_id == $r['oid']) {
					return TRUE;//房间虽然被锁住了，但超时时间已过,房间又被放出来了
				}
				else {
					return FALSE;//房间被锁，且未到时间
				}
			}
			elseif ($r['status'] == 1) {
				return FALSE;//房间永久售空
			}
			else {
				return TRUE;
			}
		}
		else {
			return TRUE;
		}
	}

	public static function room_status_check($room_id_arr) {
		$dao = new Dao_Activity_Activity();
		$r = $dao->batch_get_room($room_id_arr);
		$result = array();
		foreach ($room_id_arr as $room_id) {
			$result[$room_id] = TRUE;
		}
		foreach ($r as $room) {
			if ($room['status'] == 2) {
				if (time() - $room['create_time'] > 1800) {
					$result[$room['room_id']] = TRUE;//房间虽然被锁住了，但超时时间已过,房间又被放出来了
				}
				else {
					$result[$room['room_id']] = FALSE;//房间被锁，且未到时间
				}
			}
			elseif ($room['status'] == 1) {
				$result[$room['room_id']] = FALSE;//房间永久售空
			}
			else {
				$result[$room['room_id']] = TRUE;
			}
		}
		return $result;
	}


	/*
	 * 判断是否是活动房间
	 */
	public static function is_1111_room() {

	}

}