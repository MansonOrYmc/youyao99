<?php
global $stripe_initialize;
if(!$stripe_initialize) {
    require CORE_PATH . "classes/includes/stripePhp/init.php";
    $stripe_initialize = \Stripe\Stripe::setApiKey( APF::get_instance()->get_config('stripe_serect_key') );
}
class Bll_Payment_Stripe {

    public function charge_by_order($order_id, $amount = 0) {

        $bll_order = new Bll_Order_OrderInfo();
        $order_info = reset($bll_order->get_order_info_by_hash_id($order_id));
        $addition_info = $bll_order->get_order_addition($order_info['id']);
        if(!in_array($order_info['status'], array(2, 6))) {
            return array(
                'status' => 'failed',
                'msg' => 'order has been cancelled',
            );
        }
        if($addition_info['paytype'] == 0) {
            return array(
                'status' => 'failed',
                'msg' => 'order has been paid',
            );
        }

        if(!$amount) {
            $amount = $order_info['total_price_tw'];
        }

        $customer = self::create_customer_by_order($order_id);
        if($customer['status'] == 'failed') {
            return $customer;
        }else{
            $customer_id = $customer['info']['customer_id'];
        }

        $trad_no = Bll_Booking_ServiceInfo::get_pay_number_byids(array($order_id), "Stripe_Credit");
        if($trad_no['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($trad_no, true));
        }
        $pay_no = $trad_no['info'];

        $desc = "Zizaike bnb reservations service " . $pay_no;
        $charge = self::charge($amount, 'jpy', $desc, $customer_id);
        if($charge['status'] == 'failed') {
            return array(
                'status' => 'failed',
                'info' => $charge['info'],
                'msg' => 'charge failed',
            ); 
        }
        $charge_id = $charge['info']['charge_id'];
        // 修改订单状态
        $bll_order->change_paytype($order_info['id']);
        Bll_Booking_ServiceInfo::pay_notify($pay_no, $charge_id, "contact@youyao.com", "stripe_credit");
        return array(
            'status' => 'success',
        );

    }

    // youyao order id  验证信用卡
    public function create_customer_by_order($order_id) {

        $order_dao = new Dao_Order_OrderInfo();
        $customer_info = $order_dao->stripe_customer_by_order_id($order_id);
        if($customer_info['customer_id']) {
            return array(
                'status' => 'success',
                'info' => array('customer_id' => $customer_info['customer_id']),
            );
        }

//        $open_info = Alitrip_Order::get_open_info($order_id);
//        $booking_id = str_replace("BOOKING", "", $open_info['openId']);
//        if(!$booking_id) {
//            return array(
//                'status' => 'failed',
//                'msg' => 'can not find booing order info',
//            );
//        }

        $booking_order_info = self::booking_order_info($order_id);
        $card_info = array();
        foreach($booking_order_info as $r) {
            if($r['customer']['ccNumber']) {
                $customer = $r['customer'];
                $exp_date = explode("/", $customer['ccExpirationDate']);
                $exp_month = (int) $exp_date[0];
                $exp_year = $exp_date[1];
                $card_info = array(
                    'number' => $customer['ccNumber'],
                    'exp_year'  => $exp_year,
                    'exp_month' => $exp_month,
                    'cvc' => $customer['ccCvc'],
                    'first_name' => $customer['firstName'],
                    'last_name'  => $customer['lastName'],
//                    'country' => $customer['countryCode'],
//                    'city' => $customer['city'],
//                    'address1' => $customer['address'],
//                    'zip_code' => $customer['zip'],
                );
                break;
            }
        }
        Logger::info(__FILE__, __CLASS__, __LINE__, var_export(array(
            'card_info' => $card_info,
        ), true));

        /* test card
        $card_info = array(
            'number' => '4000000000000127',
            'exp_year'  => '2021',
            'exp_month' => '6',
            'cvc' => '313',
            'first_name' => 'Leon',
            'last_name'  => 'Chen',
        );
         */
        if(empty($card_info)) {
            if(!$customer_info['order_id']) {
                $order_dao->create_stripe_customer($order_id, 0, 1); // 添加失败记录
            }
            return array(
                'status' => 'failed',
                'msg' => 'customer has no active card',
            );
        }
        $card_info = self::generate_stripe_card_info($card_info);

        $desc = "Customer for booking $order_id ";
        $customer = self::create_customer($desc, $card_info);
        if($customer['status'] == 'failed') {
            self::invalid_card($order_id); // 标记为失效信用卡
            if(!$customer_info['order_id']) { // 添加失败记录
                $order_dao->create_stripe_customer($order_id, 0, 1);
            }
            return array(
                'status' => 'failed',
                'msg' => 'card verify faild '.$customer['info']['error']['message'],
                'info' => $customer['info'],
            );
        }
        $customer = $customer['info'];
        if($customer_info['order_id']) {
            $order_dao->verified_stripe_customer_id($order_id, $customer->id);
        }else{
            $order_dao->create_stripe_customer($order_id, $customer->id);
        }

        return array(
            'status' => 'success',
            'info' => array(
                'customer_id' => $customer->id
            ),
        );

    }

    // booking order info
    public function booking_order_info($order_id) {
        if(!$order_id) return;
        $host = APF::get_instance()->get_config("java_open_api");
        $path = "booking/getReservations";
        $url = $host . "/" . $path;
        $params = array(
            'orderId' => $order_id,
        );
        $result = InternalRequest::send_request($url, $params, "GET");
        return $result['reservations'];
    }

    // 信用卡无效
    public function invalid_card($order_id) {

        $bll_order = new Bll_Order_OrderInfo();
        $order_info = reset($bll_order->get_order_info_by_hash_id($order_id));
        if(empty($order_info)) {
            return array(
                'status' => 'failed',
                'msg' => 'can not find order info',
            );
        }

        $path = "booking/invalidCreditCard";
        $host = APF::get_instance()->get_config("java_open_api");
        $url = $host . "/" . $path;
        $params = array(
            'orderId' => $order_id,
            'hotelId' => $order_info['uid'],
            'roomId' => $order_info['nid'],
        );
        
        Logger::info(__FILE__, __CLASS__, __LINE__, "invalid_card", var_export(array(
            'orderId' => $order_id,
            'hotelId' => $order_info['uid'],
            'roomId' => $order_info['nid'],
        ), true));
        $response = Util_Curl::post($url, $params);
        if($response['code'] != 200) {
            Logger::info(__FILE__, __METHOD__, __LINE__, var_export(
                array(
                    "url"=>$url,
                    "data"=>$data,
                    "response"=> $response
                ), true));
            return array();
        }
        $result = json_decode($response['content'], true);
        if($result['code'] != 200) {
            Logger::info(__FILE__, __METHOD__, __LINE__, var_export(
                array(
                    "url"=>$url,
                    "data"=>$data,
                    "response"=> $response
                ), true));
            return array();
        }

        return $result['info'];
    }

    // stripe 收款
    public function charge($amount, $currency = 'jpy', $desc, $customer_id = null, $card_info = null) {

        $parameters = array(
            "amount" => $amount,
            "currency" => $currency,
            "description" => $desc,
        );

        if($customer_id) {
            $parameters['customer'] = $customer_id;
        }else{
            $parameters['crad'] = self::generate_stripe_card_info($card_info);
        }

        try{
            $charge = \Stripe\Charge::create($parameters);
            $result = array(
                'status' => 'success',
                'info' => array(
                    'charge_id' => $charge->id,
                ),
            );
        }catch(\Stripe\Error\Base $e) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export(array(
                'code' => $e->getHttpStatus(),
                'jsonBody' =>$e->getJsonBody(),
            ), true));
            $result = array(
                'status' => 'failed',
                'info' => $e->getJsonBody(),
            );
        }
        return $result;
    }

    // stripe创建用户（验证信用卡，以后可以直接用用户付款）
    public function create_customer($desc, $card_info) {

        $parameters = array(
            'description' => $desc,
            'card' => self::generate_stripe_card_info($card_info),
        );
        try{
            $customer = \Stripe\Customer::create($parameters);
            $result = array(
                'status' => 'success',
                'info' => $customer,
            );
        }catch(\Stripe\Error\Base $e) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export(array(
                'code' => $e->getHttpStatus(),
                'jsonBody' =>$e->getJsonBody(),
            ), true));
            $result = array(
                'status' => 'failed',
                'info' => $e->getJsonBody(),
            );
        }
        return $result;
    }

    private function generate_stripe_card_info($card_info) {
        $stripe_card = array(
            'number'   => $card_info['number'],
            'exp_year'  => $card_info['exp_year'],
            'exp_month' => $card_info['exp_month'],
        );
        if($card_info['cvc'])     $stripe_card['cvc'] = $card_info['cvc'];
        if($card_info['country']) $stripe_card['address_country'] = $card_info['country'];
        if($card_info['state'])   $stripe_card['address_state'] = $card_info['state'];
        if($card_info['city'])    $stripe_card['address_city'] = $card_info['city'];
        if($card_info['address1']) $stripe_card['address_line1'] = $card_info['address1'];
        if($card_info['address2']) $stripe_card['address_line2'] = $card_info['address2'];
        if($card_info['zip_code']) $stripe_card['address_zip'] = $card_info['zip_code'];
        if($card_info['first_name'] || $card_info['last_name']) $stripe_card['name'] = $card_info['first_name'] . " " . $card_info['last_name'];

        return $card_info;
    }

}
