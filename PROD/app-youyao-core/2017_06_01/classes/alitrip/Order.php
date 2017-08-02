<?php
class Alitrip_Order{

    public static function error_handler($code,$message=null){
        $resultCode = APF::get_instance()->get_config('resultCode', "alitrip");
        $result = array("resultCode" => "200", "message" => "sucess", "info" => null);
        $result['resultCode'] = $code;
        if(empty($message)){
            $result['message'] = $resultCode[$code];
        }else{
            $result['message'] = $message;
        }
        Logger::info(__FILE__, __CLASS__, __LINE__, var_export(array(
            'code' => $code, 
            'message' => $result['message'], 
            'request' => $_REQUEST
        ), true));
        echo(json_encode($result));
        exit;
    }

    public static function submit_order($params){
        $result = array("resultCode" => "200", "message" => "sucess", "info" => null);
        $order = self::get_order_id_by_taobao_id($params['taobaoId']);
        if(!empty($order[0]['order_id'])){
            $result['info'] = array('orderId'=>$order[0]['order_id']);
            echo(json_encode($result));
            exit;
        }
        $bll =  new Bll_Booking_BookingInfo();
        $r =  $bll->booking_form_submit($params);
        if($r['code'] == '200' || $r['code'] == '201'){
            $order = self::get_order_id_by_taobao_id($params['taobaoId']);
            if(!empty($order[0]['order_id'])){
                $result['resultCode'] = $r['code'];
                $result['info'] = array('orderId'=>$order[0]['order_id']);
                echo(json_encode($result));
                exit;
            }
        }
        return $r;
    }

    public static function get_order_id_by_taobao_id($taobaoId){

        $sql = "SELECT * FROM LKYou.t_order_open WHERE `openId` = ? ";
        $stmt = APF_DB_Factory::get_instance()->get_pdo("lkyslave")->prepare($sql);
        $stmt->execute(array($taobaoId));

        return $stmt->fetchAll();
    }

    public static function insert_order_id_taobao_id($taobaoId,$hash_id,$price){
        $sql = "insert into LKYou.t_order_open values('$taobaoId','$hash_id','1','$price')";
        $stmt = APF_DB_Factory::get_instance()->get_pdo("lkyslave")->prepare($sql);
        $stmt->execute();
    }

    public static function get_open_info($hash_id){
        $sql = "SELECT * FROM LKYou.t_order_open WHERE `order_id` = ? ";
        $stmt = APF_DB_Factory::get_instance()->get_pdo("lkyslave")->prepare($sql);
        $stmt->execute(array($hash_id));

        return $stmt->fetch();
    }

    public static function cansel_order($taobaoId){
        $order = self::get_order_id_by_taobao_id($taobaoId);
        if(!empty($order[0]['order_id'])){
            if(empty($order[0]['status'])){
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($order, true));
                self::error_handler("205");
            }
            $hash_id = $order[0]['order_id'];
            $dao = new Dao_Order_OrderInfo();
            $order_info = $dao->get_homestay_booking_by_hash_id($hash_id);
            $order_info = $order_info[0];
            $bll_order_info = new Bll_Order_OrderInfo();
            $userbll = new Bll_User_UserInfo();
            $userInfo = $userbll->get_whole_user_info($order_info['uid']);
            $refund_orign_data = json_decode($userInfo['refund_rule'], true);
            $refund_all_day = $refund_orign_data['refund_list'][1]['day'];
            if(!$refund_all_day) $refund_all_day = 30;
            if(self::diffBetweenTwoDays($order_info['guest_date'],date("Y-m-d",time()))  > $refund_all_day){
                //可以退订
                $order_info['order_status'] = 5;
                $status_mapping = $bll_order_info->zzk_order_status_mapping();
                $order_status_changed = "订单进度从 " . $status_mapping[2] . " 到 " . $status_mapping[$order_info['order_status']] . "。 ";
                $user_uid = APF::get_instance()->get_config('uid','alitrip');
                $bll_order_info->zzk_save_order_trac_content($order_info['id'], $user_uid, $order_status_changed, '', $order_info['order_status'], $order_info);
                $result = array("resultCode" => "200", "message" => "sucess", "info" => null);
                $result['info'] = array('orderId'=>$order[0]['order_id']);
                echo(json_encode($result));
                exit;
            }else{
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($order_info, true));
                self::error_handler("208");
            }
        }else{
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($taobaoId, true));
            self::error_handler("204");
        }
    }

    function diffBetweenTwoDays ($day1, $day2)
    {
        $second1 = strtotime($day1);
        $second2 = strtotime($day2);
        if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
        }
        return ($second1 - $second2) / 86400;
    }

}
