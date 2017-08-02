<?php

/**
 * Created by PhpStorm.
 * User: chanlevel
 * Date: 16/6/17
 * Time: 下午3:34
 */
class Util_Order
{

    public static function  load_order($order_id)
    {
        if (substr($order_id, 0, 1) == "S") {
            return Bll_Booking_ServiceInfo::get_service_booking_byid($order_id);
        } else {
            $bll_order = new Bll_Order_OrderInfo();
            return $bll_order->order_load($order_id);
        }

    }


    public function check_service_order($pay_id, $trac_no, $youyao_account='unset', $source='unset') {
        Logger::info(__FILE__, __CLASS__, __LINE__, "\npayid=$pay_id\ntrac_no=$trac_no\nyouyao_account=$youyao_account\nsource=$source");
        if(substr($pay_id,0, 3) == 'PAY') {
            $response = Bll_Booking_ServiceInfo::get_order_ids_by_payid($pay_id, "CREATE");
            if(empty($response['info'])) {
                $response = Bll_Booking_ServiceInfo::get_order_ids_by_payid($pay_id, "PAYED");
            }
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($response, true));
            $result = array();
            if($response['code'] == 200) {
                foreach($response['info'] as $order_id) {
                    if(substr($order_id, '0', '1') == "S") { // 服务订单
                    } else {
                        $result[] = $order_id;
                    }
                }
            }
            $service_notify = Bll_Booking_ServiceInfo::pay_notify($pay_id, $trac_no, $youyao_account, $source);
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($service_notify, true));
            return $result;
        } else {
            preg_match('/book_homestay_([0-9]+)_/', $pay_id, $matches);
            if($matches[1]) {
                return array($matches[1]);
            }else{
                return array($pay_id);
            }
        }
    }

}
