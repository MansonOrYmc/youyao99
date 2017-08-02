<?php
apf_require_class('APF_Controller');

class User_CouponController extends APF_Controller {
	public function handle_request() {
		$request = APF::get_instance()->get_request();
		$params = $request->get_parameters();
        if($params['multilang']) {
            Util_Language::set_locale_id($params['multilang']);
        }
        if($params['multiprice']) {
            Util_Currency::set_cy_id($params['multiprice']);
        }
		if (Util_Security::Security($params)) {
			$uid = $params['mobile_userid'];
			$type = $params['type'];
			$multilang = $params['multilang'];
			$use_type=$type;
			if (empty($uid) || empty($type)) {
				$response = array(
					'status' => 400,
					'data' => NULL,
					'userMsg' => 'Request_parameter_error',
					'msg' => 'Request_parameter_error'
				);
			}
			else {
				$bll_coupon = new Bll_Coupons_CouponsInfo();
				switch ($type) {
					case 'avai':
						$result = $bll_coupon->get_canuse_conpons($uid);
						break;
					case 'used':
						$result = $bll_coupon->get_used_coupons($uid);
						break;
					case 'expire':
						$result = $bll_coupon->get_expire_coupons($uid);
						break;
					default:
						$result = FALSE;
				}
				if (empty($result)) {
					$response = array(
						'status' => 200,
						'data' => array(),
						'userMsg' => 'No_data',
						'msg' =>  'No_data'
					);
				}
				else {
                    $inter_cy_config = APF::get_instance()->get_config('internal_exchange', 'area');
                    $inter_exchange = $inter_cy_config[$_REQUEST['multiprice']];
					$data = array();
					foreach ($result as $row) {
						$desc = '';
						$coupon_memo = '';
						$coupon_condition = '';
						if ($row['coupon_type'] == 2) {
							$type = 'discount_coupons';//'自在客打折券';
							$display = '9.9';
							//$unit = '折';
							$unit = null;
							$coupon_memo = 'Orders_for_a_total_of_9.9_discount';
                            if($params['multilang'] == 13) {
                                $display = '1%';
                                $unit = 'off';
                            }
						}
                        elseif (in_array($row['coupon_type'], array(3,4,5,6)) ) {
							$type = 'youyao' . 'coupons';//'自在客优惠券';
                            if($row['min_use_price']) {
                                $condition_price = $row['min_use_price'];
                            }else if($row['coupon_type']==3) {
                                $condition_price = 400; // 因为type等于3会在app有特殊展示，所以做此操作
                            }else if($row['coupon_type']==4) { 
                                $condition_price = 400;
                            }else if($row['coupon_type']==5) {
                                $condition_price = 500;
                            }else if($row['coupon_type']==6) {
                                $condition_price = 300;
                            }
                            $unit = Trans::t('key_price_unit', $_REQUEST['multiprice']);
                            $condition_price = round($condition_price * $inter_exchange);// 内部汇率
                            $display = round($row['pvalue'] * $inter_exchange);
                            $coupon_memo = Trans::t('order_over_%p_can_be_used', null, array( '%p'=> $condition_price)); //'單筆訂單满' . $condition_price . '使用';
                            $coupon_condition = '（'.$coupon_memo .'）';
                            
                        }
						else {
                            $display = round($row['pvalue'] * $inter_exchange);
                            $unit = Trans::t('key_price_unit', $_REQUEST['multiprice']);
							$type = 'youyao' . 'coupons';//'自在客优惠券';
						}
						$data[] = array(
							'coupon' => $row['coupon'],
							'coupon_display' => $display,
							'coupon_unit' => $unit,
							'coupon_desc' => $desc,
							'coupon_type' => $row['coupon_type'],
							'type' => $type,
							'endtime' => $row['expirydate'],
							'pvalue' => $row['pvalue'],
							'discountvalue' => $row['pvalue'],
							'coupon_memo' => $coupon_memo,
							'coupon_condition' => $coupon_condition,
							'use_type'=>$use_type
						);
					}
					$response = array(
						'status' => 200,
						'data' => $data,
						'userMsg' => 'Request_successful',
						'msg' => 'Request_successful'
					);
				}
			}
		}
		else {
			$response = array(
				'status' => 400,
				'data' => NULL,
				'userMsg' => 'Request_parameter_error',
				'msg' => 'Request_parameter_error'
			);
		}
		header('Content-Type:application/json');
		Util_ZzkCommon::zzk_echo(json_encode($response));
	}
}
