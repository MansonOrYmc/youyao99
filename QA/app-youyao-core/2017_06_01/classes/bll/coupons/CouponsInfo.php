<?php

class Bll_Coupons_CouponsInfo {

	private $couponsdao;

	public function __construct() {
		$this->couponsdao = new Dao_Coupons_CouponsInfoMemcache();
	}

	public function update_conpon($oid, $conpon) {
		return $this->couponsdao->dao_update_conpon($oid, $conpon);
	}

	public function set_conpon_use($order_id) {
		return $this->couponsdao->set_conpon_use($order_id);
	}

	public function add_conpon_use($params) {
		return $this->couponsdao->add_conpon_use($params);
	}

	public function get_canuse_conpons($uid, $order_id = NULL) {
		$coupons = $this->couponsdao->get_canuse_conpons($uid);
		foreach ($coupons as $key => $coupon) {
			if ($coupon['zfans_coupon'] > 0) {
				$coupons[$key]['status'] = 0;
			}elseif($coupon['status'] == 1){
				unset($coupons[$key]);
			}
		}
		return $coupons;
	}

	public function get_expire_coupons($uid){
		return $this->couponsdao->get_expire_coupons($uid);
	}

	public function get_used_coupons($uid) {
		return $this->couponsdao->get_used_coupons($uid);
	}

	public function get_conpon_use($order_id) {
		return $this->couponsdao->get_conpon_use($order_id);
	}

	public function get_coupon_value($coupon_code, $order_id = NULL) {
		$coupon = $this->get_coupon($coupon_code,$order_id);
		return $coupon['pvalue'];
	}
// only the coupon_type==2
	public function get_type2_coupon_by_order($coupon_code,$order_id){
		$discount = 0.05;
		$zfc = Zfans_Coupon::getObject($coupon_code);
		if ($zfc) {
			$coupon = $this->couponsdao->get_conpon_raw_data($coupon_code);
			$coupon['status'] = 0;
			$zfcStatus = $zfc->getCouponStatus();
			if ($zfcStatus == 'invalid') {
				return NULL;
			} else if ($zfcStatus == 'no_quota') {
				$coupon['pvalue'] = 0;
				return $coupon;
			}
			if($zfc->getRawCoupon()->role == "blogger" && $_REQUEST['multiprice'] != 10) {
				return NULL;
			}
			if ($coupon['coupon_type'] == 2) {
				$discount = $zfc->getCouponDiscount();
			}
			// if (!$zfc->isValid()) {
			// 	$coupon['pvalue'] = 0;
			// 	return $coupon;
			// }
		} else {
			$coupon = $this->couponsdao->get_valid_coupon($coupon_code);
		}
		if (empty($coupon)) {
			return NULL;
		}

		$bll_order = new Bll_Order_OrderInfo();
		if ($coupon['coupon_type'] == 2) {
			list($order_price_detail, $count_num) = $bll_order->order_price_detail($order_id);
			$coupon_value = array();
			foreach ($order_price_detail as $date => $price) {
				$value = round($price * $discount);
				$coupon_value[$date] = $value;
			}
			$coupon['count_num'] = $count_num;
			$coupon['detail_value'] = $coupon_value;
			$coupon['pvalue'] = array_sum($coupon_value) * $count_num;
		}
		if ($_REQUEST['multiprice'] == 10) {
			$coupon['pvalue'] *= 5;
		}
		return $coupon;

	}

	public function get_coupon($coupon_code, $order_id = NULL, $params = array()) {
		$discount = 0.05;
		$zfc = Zfans_Coupon::getObject($coupon_code);
		if ($zfc) {
			$coupon = $this->couponsdao->get_conpon_raw_data($coupon_code);
			$coupon['status'] = 0;
			$zfcStatus = $zfc->getCouponStatus();
			if ($zfcStatus == 'invalid') {
				return NULL;
			} else if ($zfcStatus == 'no_quota') {
				$coupon['pvalue'] = 0;
				return $coupon;
			}
            if($zfc->getRawCoupon()->role == "blogger" && $params['multiprice'] != 10) {
                return NULL;
            }
			if ($coupon['coupon_type'] == 2) {
				$discount = $zfc->getCouponDiscount();
			}
			// if (!$zfc->isValid()) {
			// 	$coupon['pvalue'] = 0;
			// 	return $coupon;
			// }
		} else {
			$coupon = $this->couponsdao->get_valid_coupon($coupon_code);
		}
		if (empty($coupon)) {
			return NULL;
		}

		if (!empty($order_id)) {
			//9.5折优惠券
			$bll_order = new Bll_Order_OrderInfo();
			if ($coupon['coupon_type'] == 2) {
				list($order_price_detail, $count_num) = $bll_order->order_price_detail($order_id);
				$coupon_value = array();
				foreach ($order_price_detail as $date => $price) {
					$value = round($price * $discount);
					$coupon_value[$date] = $value;
				}
				$coupon['count_num'] = $count_num;
				$coupon['detail_value'] = $coupon_value;
				$coupon['pvalue'] = array_sum($coupon_value) * $count_num;
			}
			//满400使用优惠券
			elseif (in_array($coupon['coupon_type'],array(3,4,5,6)) ) {
				$order = $bll_order->order_load($order_id);
                if($coupon['min_use_price']) { 
                    $condition_price = $coupon['min_use_price'];
                } else if($coupon['coupon_type']==3) {
                    $condition_price = 400; // 因为type等于3会在app有特殊展示，所以做此操作
                } else if($coupon['coupon_type']==4) {
                    $condition_price = 400;
                } else if($coupon['coupon_type']==5) {
                    $condition_price = 500;
                } else if($coupon['coupon_type']==6) {
                    $condition_price = 300;
                }

				if ($order->total_price < $condition_price) {
					$coupon['original_value'] = $coupon['pvalue'];
					$coupon['pvalue'] = 0;
				}
			}
		}
		elseif (in_array($coupon['coupon_type'], array(2, 3, 4))) {
			$coupon['pvalue'] = 0;
		}

        $point_ratio_config = APF::get_instance()->get_config('ratio', 'point');
        $point_ratio = $point_ratio_config[$_REQUEST['multiprice']] ? $point_ratio_config[$_REQUEST['multiprice']] : 1;
        $coupon['pvalue'] *= $point_ratio;
        $coupon['original_value'] *= $point_ratio;

		return $coupon;
	}
	public function ava_coupon_by_price($coupon_code,$price,$params=array()){
		$discount = 0.05;
		$zfc = Zfans_Coupon::getObject($coupon_code);
		if ($zfc) {
			$coupon = $this->couponsdao->get_conpon_raw_data($coupon_code);
			$coupon['status'] = 0;
			$zfcStatus = $zfc->getCouponStatus();
			if ($zfcStatus == 'invalid') {
				return NULL;
			} else if ($zfcStatus == 'no_quota') {
				$coupon['pvalue'] = 0;
				return $coupon;
			}
			if($zfc->getRawCoupon()->role == "blogger" && $params['multiprice'] != 10) {
				return NULL;
			}
			if ($coupon['coupon_type'] == 2) {
				$discount = $zfc->getCouponDiscount();
			}
			// if (!$zfc->isValid()) {
			// 	$coupon['pvalue'] = 0;
			// 	return $coupon;
			// }
		} else {
			$coupon = $this->couponsdao->get_valid_coupon($coupon_code);
		}
		if (empty($coupon)) {
			return NULL;
		}

		if (!empty($price)) {
			//9.5折优惠券
			$bll_order = new Bll_Order_OrderInfo();
			if ($coupon['coupon_type'] == 2) {
			return true;
			}
			//满400使用优惠券
			elseif (in_array($coupon['coupon_type'],array(3,4,5,6)) ) {
				if($coupon['min_use_price']) {
					$condition_price = $coupon['min_use_price'];
				} else if($coupon['coupon_type']==3) {
					$condition_price = 400; // 因为type等于3会在app有特殊展示，所以做此操作
				} else if($coupon['coupon_type']==4) {
					$condition_price = 400;
				} else if($coupon['coupon_type']==5) {
					$condition_price = 500;
				} else if($coupon['coupon_type']==6) {
					$condition_price = 300;
				}

				if ($price < $condition_price) {
					return false;
				}
			}
		}
		elseif (in_array($coupon['coupon_type'], array(2, 3, 4))) {
			return false;
		}


		return true;

	}

	public function get_coupon_v2($coupon_code, $params = array()) { // 上面的get_coupon会判断订单，这个方法不判断订单，订单判断放到查询的地方
		$discount = 0.05;
		$zfc = Zfans_Coupon::getObject($coupon_code);
		if ($zfc) {
			$coupon = $this->couponsdao->get_conpon_raw_data($coupon_code);
			$coupon['status'] = 0;
			$zfcStatus = $zfc->getCouponStatus();
			if ($zfcStatus == 'invalid') {
				return NULL;
			} else if ($zfcStatus == 'no_quota') {
				$coupon['pvalue'] = 0;
				return $coupon;
			}
            if($zfc->getRawCoupon()->role == "blogger" && $params['multiprice'] != 10) {
                return NULL;
            }
			if ($coupon['coupon_type'] == 2) {
				$discount = $zfc->getCouponDiscount();
			}
			// if (!$zfc->isValid()) {
			// 	$coupon['pvalue'] = 0;
			// 	return $coupon;
			// }
		} else {
			$coupon = $this->couponsdao->get_valid_coupon($coupon_code);
		}
		if (empty($coupon)) {
			return NULL;
		}

/*
		if (!empty($order_id)) {
			//9.5折优惠券
			$bll_order = new Bll_Order_OrderInfo();
			if ($coupon['coupon_type'] == 2) {
				list($order_price_detail, $count_num) = $bll_order->order_price_detail($order_id);
				$coupon_value = array();
				foreach ($order_price_detail as $date => $price) {
					$value = round($price * $discount);
					$coupon_value[$date] = $value;
				}
				$coupon['count_num'] = $count_num;
				$coupon['detail_value'] = $coupon_value;
				$coupon['pvalue'] = array_sum($coupon_value) * $count_num;
			}
			//满400使用优惠券
			elseif (in_array($coupon['coupon_type'],array(3,4,5,6)) ) {
				$order = $bll_order->order_load($order_id);
                if($coupon['min_use_price']) { 
                    $condition_price = $coupon['min_use_price'];
                } else if($coupon['coupon_type']==3) {
                    $condition_price = 400; // 因为type等于3会在app有特殊展示，所以做此操作
                } else if($coupon['coupon_type']==4) {
                    $condition_price = 400;
                } else if($coupon['coupon_type']==5) {
                    $condition_price = 500;
                } else if($coupon['coupon_type']==6) {
                    $condition_price = 300;
                }

				if ($order->total_price < $condition_price) {
					$coupon['pvalue'] = 0;
				}
			}
		}
		elseif (in_array($coupon['coupon_type'], array(2, 3, 4))) {
			$coupon['pvalue'] = 0;
		}
*/

		if ($_REQUEST['multiprice'] == 10) {
			$coupon['pvalue'] *= 5;
		}

		return $coupon;
	}

	public function use_conpon($order_id) {
		$bll_order = new Bll_Order_OrderInfo();
		$order_info = $bll_order->get_order_info_byid($order_id);

		$bll_cou = new Bll_Coupons_CouponsInfo();
		$bll_user = new Bll_User_UserInfo();
		$coupon_use_info = $bll_cou->get_conpon_use($order_id);
		if ($coupon_use_info['coupons'] == "LXJJ" && $order_info['guest_uid'] > 0) {  //分享优惠,目前是一次性扣完
			$user_info = $bll_user->get_whole_user_info($order_info['guest_uid']);
			$last_fund = $user_info['fund']-$coupon_use_info['account'];
			$bll_user->update_user_fund_by_uid($last_fund, $order_info['guest_uid']);
			$bll_cus = new Bll_Customer_Fcode();
			$fcode = array(
				's_uid' => $order_info['guest_uid'],
				'd_uid' => $order_id,
				'channel' => -1,
				'fund' => $coupon_use_info['account'],
			);
			$bll_cus->add_fc_reocrd($fcode);

			
			/* 2015-07-08
			$bll_cus = new Bll_Customer_Fcode();
			$recomm_person = $bll_cus->get_fc_recomm($order_info['guest_uid']);
			
			if (!empty($recomm_person) && $this->check_lxjj_validate($order_id) == 1) { //给推荐人加分享优惠
				$recomm_person_info = $bll_user->get_whole_user_info($recomm_person['s_uid']);
				$fund = $recomm_person_info['fund'] + $recomm_person['fund'];
				$bll_user->update_user_fund_by_uid($fund, $recomm_person['s_uid']);
				$bll_cus->update_fc_reocrd($recomm_person['id']);
				syslog(LOG_ERR, "CH98-LXJJ-2: " . $order_id);  
				$info = array(   //给使用者发短信
					'oid' => 0,
					'sid' => 0,
					'uid' => 0,
					'mobile' => $order_info['guest_telnum'],
					'content' => '【自在客】您的分享优惠已经使用完毕,赶紧邀请朋友参与，获取更多分享优惠吧!',
					'area' => 1
				);
				Util_Notify::send_sms_notify($info);

				if ($recomm_person_info['send_sms_telnum']) {  //给推荐人发短信
					$info = array(
						'oid' => 0,
						'sid' => 0,
						'uid' => 0,
						'mobile' => $recomm_person_info['send_sms_telnum'],
						'content' => '【自在客】您已经获得66元自在客分享优惠，赶紧去过一天他乡的生活吧!',
						'area' => 1
					);
					Util_Notify::send_sms_notify($info);
				}
			}  */
			
		}
		elseif (!empty($coupon_use_info['coupons'])) {  //优惠券
			$bll_order = new Bll_Order_OrderInfo();
			// note by andrew 2015.06.08
			//$user_info = $bll_user->get_whole_user_info($order_info['guest_uid']);

			//更新t_homestay_booking coupon字段
			$bll_order->update_order_info_byid($order_id, $coupon_use_info['coupons']);

			// 更新zfans_coupon使用次数
			$zfc = Zfans_Coupon::getObject($coupon_use_info['coupons']);
			if ($zfc) {
				$zfc->useCoupon();
			}else {
				// 更新t_coupons表
				$bll_cou = new Bll_Coupons_CouponsInfo();
				$oid = $order_info['guest_uid'] ? $order_info['guest_uid'] : '#' . $order_id;
				$bll_cou->update_conpon($oid, $coupon_use_info['coupons']);
			}
		}
		if (!empty($coupon_use_info['point_account']) && !empty($coupon_use_info['point_uid'])) {
			$bll_point = new Bll_User_Point();
			$total_point = $bll_point->get_total_available_point($coupon_use_info['point_uid']);
			$order_price = $order_info['total_price'] - $coupon_use_info['account'] - $coupon_use_info['activity_discount'];
			$available_point = min($total_point, $order_price);
			if ($available_point < $coupon_use_info['point_account']) {
				$point = $available_point - 1;
			}
			else {
				$point = $coupon_use_info['point_account'];
			}
			$bll_point->add_point_use_log($coupon_use_info['point_uid'], $point, $order_id,'订单成交('.$order_info['hash_id'].')');
		}

		$bll_cou->set_conpon_use($order_id);

		return TRUE;
	}

	public function check_lxjj_validate($order_id) {
		$bll_order = new Bll_Order_OrderInfo();
		$order_info = $bll_order->get_order_info_byid($order_id);
		$order_phone = $order_info['guest_telnum'];

		$need_score = 1;
		if ($order_info['guest_uid'] > 0) {
			$bll_cus = new Bll_Customer_Fcode();
			$rec_info = $bll_cus->get_recomm_bydid($order_info['guest_uid']);

			if ($rec_info['s_uid'] > 0) {
				$order_list = $bll_order->get_order_list_byuid($rec_info['s_uid']);
				foreach ($order_list as $key => $value) {
					if ($value['guest_telnum'] == $order_phone) {
						$need_score = 0;
						break;
					}
				}
			}

		}

		return $need_score;
	}

	public function get_a_coupon($coupon_code){
		return $this->couponsdao->get_conpon_raw_data($coupon_code);
	}
	public function is_coupon_checkin($code){
		$r = $this->couponsdao->get_a_coupon_used($code);
		$r = $r[0];
		$order_bll = new Bll_Order_OrderInfo();
		$order_info = $order_bll->get_order_info_byid($r['order_id']);
		if( !empty($order_info) and REQUEST_TIME >= strtotime($order_info['guest_date'])){
			return true;
		}
		else{
			return false;
		}
	}
	public function is_coupon_used($code){
		$r = $this->couponsdao->get_a_coupon_used($code);
		return !empty($r);
	}

	public function get_order_id_by_code($code){
		$r = $this->couponsdao->get_a_coupon_used($code);
		$r = $r[0];
		return $r['order_id'];
	}
	public function is_coupon_expired($code){
		$r =  $this->couponsdao->get_conpon_raw_data($code);
		if(REQUEST_TIME >= strtotime($r['expirydate'])){
			return true;
		}else{
			return false;
		}
	}

    public function get_coupon_category() {
        return $this->couponsdao->get_coupon_category();
    }

    public function get_coupon_category_byid($id) {
        return $this->couponsdao->get_coupon_category_byid($id);
    }

}
