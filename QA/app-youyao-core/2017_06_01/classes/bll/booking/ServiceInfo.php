<?php
class Bll_Booking_ServiceInfo {

    // 下单
    public function service_booking($customerId, $businessId, $email, $guestName, $mobile, $serviceList, $customerDestId=12, $channel,
                $customerProvince=null, $firstName=null, $lastName=null, $remark=null, $wechat=null, $ip=null) 
    {

        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/service/booking";
        if($ip===null) {
            $ip = Util_NetWorkAddress::get_client_ip();
            $ip = preg_replace('/,.*$/', '', $ip);
            if(!$ip) {
                $ip = '127.0.0.1';
            }
        }

        if(!$customerDestId) $customerDestId = 12;
        $curl_params = array(
                'customerId'     => $customerId,
                'businessId'     => $businessId,
                'email'          => $email,
                'guestName'      => $guestName,
                'mobile'         => $mobile,
                'serviceList'    => $serviceList,
                'customerDestId' => $customerDestId,
                'channel'        => strtoupper($channel),
            );
        foreach($serviceList as $row) {
            $servicetmp[] = array(
                    'serviceId'     => $row['package_Id'],
                    'serviceNumber' => $row['num'],
                    'useTime'      => $row['date'],
                );
        }
        $curl_params['serviceList'] = $servicetmp;
        $curl_params['ip'] = $ip;

        if($customerProvince) $curl_params['customerProvince'] = $customerProvince;
        if($firstName) $curl_params['firstName'] = $firstName;
        if($lastName) $curl_params['lastName'] = $lastName;
        if($remark) $curl_params['remark'] = $remark;
        if($wechat) $curl_params['wechat'] = $wechat;

        $curl_response = Util_Curl::post($java_url, json_encode($curl_params), array("Content-Type"=>"application/json;"));

        if($curl_response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_response, true));
        }
        return json_decode($curl_response['content'], true);

    }

    // C端 查询列表
    public function customer_filter_service_booking($customerId, $orderStatus="", $keywords=null) {
        if(!$customerId) return;

        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/service/user/query";
        $curl_params = array(
            'customerId' => $customerId,
            'orderStatus'=> $orderStatus,
        );

        if($keywords) {
            $curl_params['keywords'] = $keywords;
        }

        $curl_response = Util_Curl::get($java_url, $curl_params);
    
        if($curl_response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_response, true));
        }
        return json_decode($curl_response['content'], true);
    }

    // B端订单筛选
    public function bussiness_filter_service_booking($businessId, $orderStatus="", $businessOrderStatus="",
        $guestName=null, $mobile=null, $orderNo=null, 
        $orderTimeBegin=null, $orderTimeEnd=null, 
        $useTimeBegin=null, $useTimeEnd=null,
        $custom_id=null,$keywords=null)
    {
        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/service/business/query";
        //必填
        $curl_params = array(
            'businessId' => $businessId,
//            'orderStatus' => $orderStatus,
//            'businessOrderStatus' => $businessOrderStatus,
        );

        //非必填
        if($orderStatus)$curl_params['orderStatus']=$orderStatus;
        if($businessOrderStatus)$curl_params['businessOrderStatus']=$businessOrderStatus;
        if($guestName) $curl_params['guestName'] = $guestName;
        if($mobile) $curl_params['mobile'] = $mobile;
        if($orderNo) $curl_params['orderNo'] = $orderNo;
        if(strtotime($orderTimeBegin)) $curl_params['orderTimeBegin'] = $orderTimeBegin;
        if(strtotime($orderTimeEnd)) $curl_params['orderTimeEnd'] = $orderTimeEnd;
        if(strtotime($useTimeBegin)) $curl_params['useTimeBegin'] = $useTimeBegin;
        if(strtotime($useTimeEnd)) $curl_params['useTimeEnd'] = $useTimeEnd;
        if($custom_id)$curl_params['customerId']=$custom_id;

        if($keywords) {
            $curl_params['keywords'] = $keywords;
        }

        $curl_response = Util_Curl::get($java_url, $curl_params);
        if($curl_response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_response, true));
        }
        return json_decode($curl_response['content'], true);

    }

    //财务后台订单查询(操作筛选特色服务订单)
    public  function  operation_filter_spcservice(
        $useTimeBegin=null,$useTimeEnd=null,
        $pageSize=20, $pageNo=1, $bnbName=null,
        $userId=null, $orderNo=null,
        $country=null, $orderStatus=null, $payType=null,
        $amountBegin=null, $amountEnd=null,
        $orderTimeBegin=null, $orderTimeEnd=null

    ){
        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/service/finance/query";
        $country_config = APF::get_instance()->get_config('service_country', 'servicebooking');


        if(!$userId) {
            $user = APF::get_instance()->get_request()->get_userobject();
            $userId = $user->uid;
        }
        $curl_params['userId'] = $userId;
        $curl_params['country'] = $country;

        //非必填
        if($useTimeBegin) $curl_params['useTimeBegin'] = $useTimeBegin;
        if($useTimeEnd) $curl_params['useTimeEnd'] = $useTimeEnd;
        if($bnbName) $curl_params['bnbName'] = $bnbName;
        if($orderNo) $curl_params['orderNo'] = $orderNo;
        if($orderStatus) $curl_params['orderStatus'] = $orderStatus;
        if($payType) $curl_params['payType'] = $payType;
        if($amountBegin) $curl_params['amountBegin'] = $amountBegin;
        if($amountEnd) $curl_params['amountEnd'] = $amountEnd;
        if($orderTimeBegin) $curl_params['orderTimeBegin'] = $orderTimeBegin;
        if($orderTimeEnd) $curl_params['orderTimeEnd'] = $orderTimeEnd;

        $curl_response = Util_Curl::get($java_url, $curl_params);

// print_r($java_url."?".http_build_query($curl_params));

        if($curl_response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_response, true));
        }
        return json_decode($curl_response['content'], true);// json _decode对json格式的字符串进行解码
    }
    

    //导出变成已导出的查询
    public function  operation_explode(
        $orderNoList =null,$userId=null
    ){
        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/service/finance/prepare";

        if(!$userId) {
            $user = APF::get_instance()->get_request()->get_userobject();
            $userId = $user->uid;
        }
        $curl_params['userId'] = $userId;
        $curl_params['orderNoList'] = $orderNoList;

        $curl_response = Util_Curl::post($java_url, json_encode($curl_params), array("Content-Type"=>"application/json;"));

         /* print_r($java_url."?".http_build_query($curl_params));
        print_r($curl_response);*/
            
        if($curl_response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_response, true));
        }
          return json_decode($curl_response['content'], true);

    }
        

    //财务查询中将已导出变成导出
    public function  operation_beon(
        $orderNoList=null,$userId=null
    ){
        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/service/finance/not_reittance";

        if(!$userId) {
            $user = APF::get_instance()->get_request()->get_userobject();
            $userId = $user->uid;
        }
        $curl_params['userId'] = $userId;
        $curl_params['orderNoList'] = $orderNoList;

        $curl_response = Util_Curl::post($java_url, json_encode($curl_params), array("Content-Type"=>"application/json;"));

        /*   print_r($java_url."?".http_build_query($curl_params));
        print_r($curl_response);*/
        if($curl_response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_response, true));
        }
        return json_decode($curl_response['content'], true);
    }

        
//Excel 导出
    public function operation_filter_excel($country,$orderNoList,$userId)
    {
        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/service/finance/queryOrderNos";
        $country_config = APF::get_instance()->get_config('service_country', 'servicebooking');
        $curl_params['country'] = $country;
        $curl_params['orderNoList'] = $orderNoList;
        $curl_params['userId'] = $userId;
        $curl_response = Util_Curl::post($java_url, json_encode($curl_params), array("Content-Type"=>"application/json;"));

        if($curl_response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_response, true));
        }
        return json_decode($curl_response['content'], true);// json _decode对json格式的字符串进行解码
    }
    
    public function finance_remittance($orderNoList,$userId)
    {
        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/service/finance/has_remittance";
        $country_config = APF::get_instance()->get_config('service_country', 'servicebooking');

        $curl_params['orderNoList'] = $orderNoList;
        $curl_params['userId'] = $userId;

        $curl_response = Util_Curl::post($java_url, json_encode($curl_params), array("Content-Type"=>"application/json;"));

        if($curl_response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_response, true));
        }
        return json_decode($curl_response['content'], true);// json _decode对json格式的字符串进行解码
    }

    
    // 运营人员订单查询
    public function  operation_filter_service_booking(
            $orderNo=null,
            $useTimeBegin=null, $useTimeEnd=null,
            $orderTimeBegin=null, $orderTimeEnd=null, $orderStatus=null,
            $businessId=null, $businessOrderStatus=null,
            $mobile=null, $customerId=null, $guestName=null,
            $pageNo=1, $pageSize=20
    ) {

        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/service/admin/query";

        //非必填
        if($orderNo) $curl_params['orderNo'] = $orderNo;
        if($useTimeBegin) $curl_params['useTimeBegin'] = $useTimeBegin;
        if($useTimeEnd) $curl_params['useTimeEnd'] = $useTimeEnd;
        if($orderTimeBegin) $curl_params['orderTimeBegin'] = $orderTimeBegin;
        if($orderTimeEnd) $curl_params['orderTimeEnd'] = $orderTimeEnd;
        if($orderStatus) $curl_params['orderStatus'] = $orderStatus;
        if($businessId) $curl_params['businessId'] = $businessId;
        if($businessOrderStatus) $curl_params['businessOrderStatus'] = $businessOrderStatus;
        if($mobile) $curl_params['mobile'] = $mobile;
        if($customerId) $curl_params['customerId'] = $customerId;
        if($guestName) $curl_params['guestName'] = $guestName;
        if($pageNo) 
            $curl_params['pageNo'] = $pageNo;
        else
            $curl_params['pageNo'] = 1;
//        if($pageSize)
//            $curl_params['pageSize'] = $pageSize;
//        else
//            $curl_params['pageSize'] = 20;

        $curl_response = Util_Curl::get($java_url, $curl_params);

//print_r($java_url."?".http_build_query($curl_params));

        if($curl_response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_response, true));
        }

        return json_decode($curl_response['content'], true);// json _decode对json格式的字符串进行解码

    }

    // 根据订单号查询
    public function get_service_booking_byid($orderNo) {
        if(empty($orderNo)) return array();

        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/service/query";
        $curl_params['orderNo'] = $orderNo;
        $curl_response = Util_Curl::get($java_url, $curl_params);
        if($curl_response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_response, true));
        }
        return json_decode($curl_response['content'], true);

    }

    // 获得group pay id
    public function get_pay_number_byids($ids, $channel, $ip=null) {
        if(empty($ids)) return;

        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/pay/mergePay";

        if($ip===null) {
            $ip = Util_NetWorkAddress::get_client_ip();
            $ip = preg_replace('/,.*$/', '', $ip);
            if(!$ip) {
                $ip = '127.0.0.1';
            }
        }

        $curl_params['ip'] = $ip;
        $curl_params['orderNos'] = $ids;
        $curl_params['channel'] = $channel;

        $curl_response = Util_Curl::post($java_url, json_encode($curl_params), array("Content-Type"=>"application/json;"));
        if($curl_response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_response, true));
        }
        return json_decode($curl_response['content'], true);
    }

    // 通过group id 获得订单list
    public function get_order_ids_by_payid($outPayNo, $payStatus) {

        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/pay/query";
        $curl_params = array(
            'outPayNo' => $outPayNo,
            'payStatus' => $payStatus,
        );
        $curl_response = Util_Curl::get($java_url, $curl_params);
        
        return json_decode($curl_response['content'], true);
    }

    // 取消订单
    public function cancel_order_byid($orderNo, $userId) {
        if(!$orderNo) return;

        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/service/cancel";
        $curl_params = array(
            'orderNo' => $orderNo,
            'userId' => $userId,
        );

        $curl_response = Util_Curl::post($java_url, $curl_params);

        return json_decode($curl_response['content'], true);
    }

    // 支付成功
    public function pay_notify($outPayNo, $payAccount, $payNo, $paySource) {

        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/pay/callBack";
        $curl_params = array(
            'outPayNo'   => $outPayNo,
            'payAccount' => $payAccount,
            'payNo'      => $payNo,
            'paySource'  => $paySource,
        );

            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_params, true));

        $curl_response = Util_Curl::post($java_url, json_encode($curl_params), array("Content-Type"=>"application/json;"));

//        if($curl_response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_response, true));
//        }

        try{
            $result = json_decode($curl_response['content'], true);
            if(!empty($result['info'])) {
                $userbll = new Bll_User_UserInfo();
                $order_status_config = APF::get_instance()->get_config("java_service_status", "servicebooking");
                $service_category_config = APF::get_instance()->get_config("service_category", "servicebooking");
                $service_category_cn_config = APF::get_instance()->get_config("service_category_cn", "servicebooking");
                $category_config = array_flip($service_category_config);
                $body = "";
                foreach($result['info'] as $row) {
                    if(!$row['businessId']) continue;
                    $hs_info = $userbll->get_whole_user_info($row['businessId']);
                    $body .= "订单号：<a href='".Util_Common::url('/admin/order?order_id='.$row['orderNo'], 'super')."' target='_blank'>" . $row['orderNo'] . "</a><br/>";
                    $body .= "成交时间：" . date('Y-m-d H:i:s') . "<br/>";
                    $body .= "民宿名称：<a href='" . Util_Common::url("/h/" . $row['businessId'], 'taiwan'). "'>" . $hs_info['name'] . " </a> <br/>";
                    $body .= "服务名称：" . $row['unitName'] . "<br/>";
                    $body .= "服务类型：" . $service_category_cn_config[$category_config[$order["additionalServiceType"]]] . "<br/>";
                    $body .= "使用时间：" . $row['useTime'] . "<br/>";
                    $body .= "份数：" . $row['unitNumber'] . "<br/>";
                    $body .= "总价：" . ( $row['totalPrice'] / $row['rmbRateCustomer'] ). "（人民币）<br/>";
                    $body .= "--------------------------------------<br/>";

                    //设置特色服务成交以后的消息推送
                    $trans_param = array(
                        "%n" => '',
                        "%s" => '',
                        "%d" => '',
                        "%p" => '',
                        "%u" => '',
                        "%c" => '',
                        "%b" => $row['orderNo'],
                    );

                    $sms_content = Trans::t("sms_b_service_traded_%n_%s_%d_%p_%u_%c_%b_%c_%t", $row['customerDestId'], $trans_param);
                    $sms_content = Util_Common::zzk_msg_keyword($sms_content);
                    Util_Notify::push_message_client($hs_info['mail'], '', '', $sms_content, Util_Notify::get_push_mtype('admin_order'), $row['orderNo']);
                }
                //end

                if($body) {
                    Util_SmtpMail::send("leonchen@youyao.com", "有特色服务单成交了", $body);
                    Util_SmtpMail::send("product@youyao.com", "有特色服务单成交了", $body);
                    if($row['businessDestId'] == 12) {
                        Util_SmtpMail::send("dl-mainland@youyao.com", "有特色服务单成交了", $body);
                    }
                    elseif($row['businessDestId'] == 11) {
                        Util_SmtpMail::send("dl-japan@youyao.com", "有特色服务单成交了", $body);
                    }
                    else{
                        Util_SmtpMail::send("dl-sales@youyao.com", "有特色服务单成交了", $body);
                    }
                }
            }
        } catch(Exception $e) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($e->getMessage(), true));
        }

        return json_decode($curl_response['content'], true);
    }

    // 申请退订
    public function refund_service($orderNo, $customerId, $refundReason) {

        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/service/refund";
        
        $curl_params = array(
            'orderNo'      => $orderNo,
            'customerId'   => $customerId,
            'refundReason' => $refundReason,
        );

        $curl_response = Util_Curl::post($java_url, json_encode($curl_params), array("Content-Type"=>"application/json;"));

        if($curl_response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_response, true));
        }
        return json_decode($curl_response['content'], true);
    }

    // 确认退款
    public function confirm_refund($orderNo) {

        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/service/refundSuccess";
        
        $curl_params = array(
            'orderNo'      => $orderNo,
        );

        $curl_response = Util_Curl::post($java_url, json_encode($curl_params), array("Content-Type"=>"application/json;"));

        if($curl_response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_response, true));
        }
        return json_decode($curl_response['content'], true);
        
    }

    // 订单备注
    public function remark_order($orderNo, $remark, $userId) {

        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/service/remark";
        
        $curl_params = array(
            'orderNo' => $orderNo,
            'userId'  => $userId,
            'remark'  => $remark,
        );

        $curl_response = Util_Curl::post($java_url, json_encode($curl_params), array("Content-Type"=>"application/json;"));

        if($curl_response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_response, true));
        }
        return json_decode($curl_response['content'], true);
    }

    public function remove_order($orderNo, $customerId) {

        $java_url = APF::get_instance()->get_config("java_service_soa")."/trade/service/delete";
        
        $curl_params = array(
            'orderNo'     => $orderNo,
            'customerId'  => $customerId,
        );

        $curl_response = Util_Curl::post($java_url, $curl_params);

        if($curl_response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($curl_response, true));
        }
        return json_decode($curl_response['content'], true);
    }

}
