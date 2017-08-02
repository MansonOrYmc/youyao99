<?php
apf_require_class("APF_Controller");

class User_UserInfoController extends APF_Controller
{

    public function handle_request()
    {

        header("Content-type: application/json; charset=utf-8");

        $req = APF::get_instance()->get_request();
        $params = $req->get_parameters();
        $security = Util_Security::Security($params);
        if (!$security) {
            echo json_encode(Util_Beauty::wanna(array(
                'code' => 0,
                'codeMsg' => 'Illegal_request',
                'status' => 'fail',
                'msg' => "request forbidden",
                "userMsg" => 'Illegal_request',
            )));

            return false;
        }
        $action = $params['action'];
        if ($action == 'headPic') { // 查看用户头像
            self::handle_headPic($params);
        } else if ($action == 'listCount') { // 个人中心(订单、私信、收藏、代金券。。)数目
            self::handle_listCount($params);
        } else if ($action == 'profile') {
            self::handle_profile($params);
        } else if ($action == 'signin') { // 用户登录
            self::handle_signin($params);
        } else if ($action == 'coupon') { // 优惠信息
            self::handle_coupons($params['uid'], $params['order_id']);
        } else if ($action == 'collect') {
            self::handle_collect($params);
        } else if ($action == 'lxjj') {
            self::handle_lxjj_ok($params['uid']);
        } else if ($action == 'logout') {
            self::handle_logout($params);
        } else if ($action == 'multstay') {
            self::handle_multstay($params);
        } elseif ($action == 'forget') {
            $this->handle_forget($params);
        } elseif ($action == 'homestayCenter') { //民宿个人中心
            $this->handle_homestayCenter($params);
        } elseif ($action == 'fund') {
            self::handle_fund($params['uid'], $params['order_id']);
        } elseif ($action == 'modifyNickname') {
            self::handle_modifynickname($params);
        }elseif ($action=='simpleUser'){
            self::handle_simple_user($params);
        }elseif($action == 'updateCurrency') {
            self::updateUserCurrency($params);
        }

        return false;
    }

    private function handle_collect($params)
    {
        $type = $params['type'];
        $uid = $params['uid'];
        $result = array(
            'status' => 'Ok',
            'msg' => "",
            "userMsg" => "",

        );
        if (empty($uid)) {
            $result['status'] = 'fail';
            $result['msg'] = 'Uid_can_not_be_empty';
            Util_ZzkCommon::zzk_echo(json_encode(Util_Beauty::wanna($result)));
            return false;
        }

        $rooms = json_decode($params['rooms'], true);
        $homes = json_decode($params['homes'], true);
        $homestays = json_decode($params['homestays'], true);

        $user_dao = new Dao_User_UserInfo();

        foreach ($homestays as $k => $v) {
            $user_id = $user_dao->get_uid_by_pid($v);
            array_push($homes, $user_id);
        }

        $user_info = new Bll_User_UserInfo();

        if ($type == 'insert') {
            $collections = $user_info->get_collect_by_uid($uid);
            foreach ($rooms as $r) {
                $b = false;
                foreach ($collections as $k => $v) {
                    if ($v['type'] == 'r' && $v['hid'] == $r) {
                        $b = true;
                        break;
                    }
                }

                if ($b) {
                    $user_info->update_collect_by_uid($uid, 'r', $r, 1);
                } else {
                    $user_info->insert_collect_by_uid($uid, 'r', $r);
                }

            }
            foreach ($homes as $h) {
                $b = false;
                foreach ($collections as $k => $v) {
                    if ($v['hid'] == $h && $v['type'] == 'h') {
                        $b = true;
                        break;
                    }
                }

                if ($b) {
                    $user_info->update_collect_by_uid($uid, 'h', $h, 1);
                } else {
                    $user_info->insert_collect_by_uid($uid, 'h', $h);
                }

            }
        } else if ($type == 'delete') {

            foreach ($rooms as $r) {

                $user_info->update_collect_by_uid($uid, 'r', $r);
            }

            foreach ($homes as $h) {
                $user_info->update_collect_by_uid($uid, 'h', $h);

            }

        }

        Util_ZzkCommon::zzk_echo(json_encode(Util_Beauty::wanna($result)));
        return true;

    }

    private function handle_headPic($info)
    {
        $userID = isset($info['userID']) ? $info['userID'] : '';
        $returnJSON = array('code' => 0, 'codeMsg' => '');
        if (strlen($userID) <= 0) {
            $returnJSON['codeMsg'] = 'UserID_is_required';
            Util_ZzkCommon::zzk_echo(json_encode(Util_Beauty::wanna($returnJSON)));
            return false;
        }

        $bll_userInfo = new Bll_User_UserInfo();
        $userInfo = $bll_userInfo->get_user_head_pic_by_uid($userID);
        if (!$userInfo) {
            $userInfo = Util_Avatar::dispatch_avatar($info['userID']);
        }
        if ($userInfo) {
            $returnJSON['code'] = 1;
            $returnJSON['body'] = array('headPic' => $userInfo);
        } else {
            $returnJSON['codeMsg'] = 'There_is_no_corresponding_data_in_the_database';
        }

        Util_ZzkCommon::zzk_echo(json_encode(Util_Beauty::wanna($returnJSON)));
        return true;
    }

    private function handle_listCount($info)
    {
        $os = isset($info['os']) ? $info['os'] : '';
        $version = isset($info['version']) ? $info['version'] : '';
        $guid = isset($info['guid']) ? $info['guid'] : '';

        $email = isset($info['email']) ? $info['email'] : '';
        $uid = isset($info['uid']) ? $info['uid'] : '';
        $returnJSON = array('code' => 0, 'codeMsg' => '');

//        if (empty($uid) && empty($email)) {
        //            $returnJSON['codeMsg'] = 'email,uid不能都为空!';
        //            echo json_encode($returnJSON);
        //            return false;
        //        }
        //  更新，  email 可以为空，通过guest_uid判断
        //if (strlen($email) <= 0) {
        //            $returnJSON['codeMsg'] = 'email为必填项!';
        //            echo json_encode($returnJSON);
        //            return false;
        //        } else
        if (empty($uid)) {
            $returnJSON['codeMsg'] = 'UserID_is_required';
            Util_ZzkCommon::zzk_echo(json_encode(Util_Beauty::wanna($returnJSON)));
            return false;
        }

        $bll_userInfo = new Bll_User_UserInfo();
        $dao_userInfo = new Dao_User_UserInfo();

        $isAdmin = $dao_userInfo->isAdmin($uid);

        $userInfo = $bll_userInfo->acquire_user_list_count($email, $uid);
        if($isAdmin) {
            $total_order_list = Bll_Booking_ServiceInfo::bussiness_filter_service_booking($uid);
        }else {
            $total_order_list = Bll_Booking_ServiceInfo::customer_filter_service_booking($uid);
        }
        $userInfo['ordercount'] += count($total_order_list['info']);
        foreach($total_order_list['info'] as $row) {
            if($row['orderStatus']=='CREATE') $userInfo['waitordercount']++;
            if($row['orderStatus']=='PAYED' && strtotime($row['useTime']) > (time() - 60*60*24)) $userInfo['tobeusedcount']++;
        }
//        //判断是否显示旅行基金
        //        $bll_fcode = new Bll_Activity_Fcode();
        //        if ($bll_fcode->check_share($uid)>0){
        //            $lxjj = 1;
        //        }else{
        //            $lxjj = 0;
        //        }
        //旅行基金活动下线
        $userInfo['lxjj'] = 0;

//$needextra 表示是否显示民宿分馆
        if ($os == 'ios' && $version > 4.6) {
            $needextra = 1;
        }
        if ($os == 'android' && $version > 48) {
            $needextra = 1;
        }
        if ($uid > 0 && $needextra) {

            if ($isAdmin) {
                $extra = array();
                $mult_uids = $bll_userInfo->get_mult_uids($uid);
                foreach ($mult_uids as $k => $v) {
                    $b_uid = $v['b_uid'];
                    $b_mail = $dao_userInfo->get_user_mail_by_uid($b_uid);
                    $listcount = $bll_userInfo->acquire_user_list_count($b_mail, $b_uid);
                    $total_order_list = Bll_Booking_ServiceInfo::bussiness_filter_service_booking($b_uid);
                    $listcount['ordercount'] += count($total_order_list['info']);
                    foreach($total_order_list['info'] as $row) {
                        if($row['orderStatus']=='CREATE') $listcount['waitordercount']++;
                        if($row['orderStatus']=='PAYED' && strtotime($row['useTime']) > (time() - 60*60*24)) $listcount['tobeusedcount']++;
                    }
                    $info = $bll_userInfo->get_user_by_uid($b_uid);
                    $info['pid'] = $dao_userInfo->getPid($b_uid);
                    unset($info['pass']);
                    $info['userid'] = $info['uid'];
                    $extra[] = $listcount + $info;

                }
                $userInfo['extra'] = $extra;
            }
        }

        $point_bll = new Bll_User_Point();
        $total_point = intval($point_bll->get_total_available_point($uid));
        $userInfo['pointCount'] = $total_point;

        if (!empty($guid)) {
            $bll_push = new Bll_Push_Register();
            $bll_push->user_bind_guid($uid, $guid);
        }
        if ($userInfo) {
            //币种
            $multiprice = isset($info['multiprice']) ? $info['multiprice'] : 12;
            $r2 = UserUtil::getAllcurrencyType($multiprice);
            $userInfo['currency_type'] = UserUtil::getUserCurrency($uid, $multiprice);
            $userInfo['currency_all_type'] = $r2;
            $returnJSON['code'] = 1;
            $returnJSON['codeMsg'] = 'Successful_operation';
            $returnJSON['body'] = $userInfo;
        } else {
            $returnJSON['code'] = 0;
            $returnJSON['codeMsg'] = 'Failure_to_operate_the_database';
        }

        Util_ZzkCommon::zzk_echo(json_encode(Util_Beauty::wanna($returnJSON)));
    }

    private function handle_profile($info)
    {
        $os = isset($info['os']) ? $info['os'] : '';
        $version = isset($info['version']) ? $info['version'] : '';
        $guid = isset($info['guid']) ? $info['guid'] : '';

        $email = isset($info['email']) ? $info['email'] : '';
        $uid = isset($info['uid']) ? $info['uid'] : '';
        $returnJSON = array('code' => 0, 'codeMsg' => '');

        if (empty($uid)) {
            $returnJSON['codeMsg'] = 'UserID_is_required';
            Util_ZzkCommon::zzk_echo(json_encode($returnJSON));
            return false;
        }

        $bll_userInfo = new Bll_User_UserInfo();
        $userInfo['lxjj'] = 0;
        $dao_userInfo=new Dao_User_UserInfo();
        $isAdmin = $dao_userInfo->isAdmin($uid);

        $userInfo = $bll_userInfo->acquire_user_list_count($email, $uid);
        if($isAdmin) {
            $total_order_list = Bll_Booking_ServiceInfo::bussiness_filter_service_booking($uid);
        }else {
            $total_order_list = Bll_Booking_ServiceInfo::customer_filter_service_booking($uid);
        }
        $userInfo['ordercount'] += count($total_order_list['info']);
        foreach($total_order_list['info'] as $row) {
            if($row['orderStatus']=='CREATE') $userInfo['waitordercount']++;
            if($row['orderStatus']=='PAYED' && strtotime($row['useTime']) > time()) $userInfo['tobeusedcount']++;
        }

        $point_bll = new Bll_User_Point();
        $total_point = intval($point_bll->get_total_available_point($uid));
        $userInfo['pointCount'] = $total_point;
        if (!empty($guid)) {
            $bll_push = new Bll_Push_Register();
            $bll_push->user_bind_guid($uid, $guid);
        }

        $msg_bll  = new Bll_User_Msg();
        $msg_count = $msg_bll->get_message_list_byuid($uid);
        $unread_msg = 0;
        foreach($msg_count as $row) {
            if($row['is_read'] == 0) $unread_msg++;
        }
        $userInfo['message_num'] = count($msg_count);
        $userInfo['unread_message_num'] = $unread_msg;
        $userInfo['completion'] = $bll_userInfo->user_profile_completion($uid);

        if ($userInfo) {
            Util_Json::render(200, $userInfo);
        } else {
            Util_Json::render(400, null, 'Failure_to_operate_the_database', 'Failure_to_operate_the_database');
        }

        return true;
    }

    private function handle_multstay($info)
    {
        $uid = isset($info['uid']) ? $info['uid'] : '';
        $bll_userInfo = new Bll_User_UserInfo();
        $mult_uids = $bll_userInfo->get_mult_uids($uid);
        if (count($mult_uids) > 1) {
            echo json_encode(array(
                'code' => 1,

            ));
        } else {
            echo json_encode(array(
                'code' => 0,

            ));
        }
        return false;
    }

    private function handle_signin($info)
    {

        $os = isset($info['os']) ? $info['os'] : '';
        $version = isset($info['version']) ? $info['version'] : '';

        $email = isset($info['username']) ? $info['username'] : '';
        $pass = isset($info['password']) ? $info['password'] : '';

        $returnJSON = array('code' => 0, 'codeMsg' => '');
        if (strlen($email) <= 0) {
            $returnJSON['codeMsg'] = 'Username_is_required';
           // Util_ZzkCommon::zzk_echo(json_encode($returnJSON));
            $this->render($returnJSON);
            Logger::debug('login', json_encode(array_merge($returnJSON, array('guid' => $info['guid']))));
            return false;
        } else if (strlen($pass) <= 0) {
            $returnJSON['codeMsg'] = 'Password_is_required';
            $this->render($returnJSON);
            Logger::debug('login', json_encode(array_merge($returnJSON, array('guid' => $info['guid']))));
            return false;
        }

        $bll_userInfo = new Bll_User_UserInfo();
        $userInfo = $bll_userInfo->signin($email, $pass);

        if ($os == 'ios' && $version > 4.6) {
            $needextra = 1;
        }
        if ($os == 'android' && ($version<80|| $version > 47)) {
            $needextra = 1;
        }

        if ($userInfo && $needextra) {
            $mult_uids = $bll_userInfo->get_mult_uids($userInfo['userid']);
            $extra = array();

            foreach ($mult_uids as $kk => $v) {
                $b_uid = $v['b_uid'];
                $b_info = $bll_userInfo->get_user_by_uid($b_uid);
                $info_data = $bll_userInfo->get_data_by_user($b_info);
                $info_data['uid'] = $info['userid'];
                $extra[] = $info_data;
            }
            $userInfo['extra'] = $extra;
        }

        if ($userInfo && $userInfo['status'] != 1) {
            $returnJSON['code'] = 0;
            $returnJSON['codeMsg'] = 'The_user_does_not_activate_check_your_activation_email';
        } elseif ($userInfo) {
            $returnJSON['code'] = 1;
            $returnJSON['codeMsg'] = 'Successful_operation';
            $returnJSON['body'] = $userInfo;
        } else {
            $returnJSON['code'] = 0;
            $returnJSON['codeMsg'] = 'Username_or_password_is_incorrect';
        }

       $this->render($returnJSON);
//        Util_ZzkCommon::zzk_echo(json_encode($returnJSON));
        Logger::debug('login', json_encode(array_merge($returnJSON, array(
            'username' => $info['username'],
            'guid' => $info['guid'],
        ))));
        return true;
    }



    private function handle_coupons($uid, $order_id)
    {
        $coupons_list = array();
        $bll_cou = new Bll_Coupons_CouponsInfo();
        $coupons = $bll_cou->get_canuse_conpons($uid);
        $tmp_coupons = array();
        foreach ($coupons as $key => $value) {
            $display = '¥' . $value['pvalue'];
            if ($value['coupon_type'] == 2) {
                $type = '9.9_discount_coupons';
                $display = '9.9_discount';
                $value['pvalue'] = 0;
            } elseif ($value['coupon_type'] == 3) {
                $type = '400_full_use';
            } elseif ($value['coupon_type'] == 4){
                $type = '400_full_use';
            } elseif ($value['coupon_type'] == 5){
                $type = '500_full_use';
            } elseif ($value['coupon_type'] == 6){
                $type = '300_full_use';
            } elseif ($value['ownner'] == 'huzheng') {
                $type = "旅行红包";
            } else {
                $type = 'coupons';
            }
            $tmp_coupons[] = array(
                'coupon_display' => $display,
                'coupon' => $value['coupon'],
                'type' => $type,
                'endtime' => $value['expirydate'],
                'pvalue' => $value['pvalue'],
            );
        }
        $coupons_list['coupon_list'] = $tmp_coupons;
        $bll_user = new Bll_User_UserInfo();
        $user = $bll_user->get_whole_user_info($uid);
        $coupons_list['lvjj'] = $user['fund'];
        $coupons_list['lxjj'] = $user['fund'];
        Util_ZzkCommon::zzk_echo(json_encode($coupons_list));
        return true;
    }

    private function handle_lxjj_ok($uid)
    {
        $result = array(
            'status' => 'Ok',
            'msg' => "",
            "userMsg" => "",
            "data" => true,
        );

        $bll_fcode = new Bll_Activity_Fcode();
        if ($bll_fcode->check_share($uid) > 0) {
            $result['data'] = true;
        } else {
            $result['data'] = false;
        }
        Util_ZzkCommon::zzk_echo(json_encode($result));
    }
    private function handle_logout($info)
    {
        $os = isset($info['os']) ? $info['os'] : '';
        $version = isset($info['version']) ? $info['version'] : '';
        $email = isset($info['email']) ? $info['email'] : '';
        $uid = isset($info['uid']) ? $info['uid'] : '';

        $baiduid = isset($info['baiduid']) ? $info['baiduid'] : '';
        $deviceid = isset($info['deviceid']) ? $info['deviceid'] : '';
        $user_token = isset($info['user_token']) ? $info['user_token'] : '';

        $userinfo = new Bll_User_UserInfo();
        $userinfo->close_push($deviceid, $baiduid);
        $bll_push = new Bll_Push_Register();
        $bll_push->unbind_guid($info['guid']);
        if($user_token) {
            $sign_dao = new Dao_User_Sign();
            $sign_dao->remove_record_by_sid($user_token);
        }
        $returnJSON['code'] = 1;
        $returnJSON['codeMsg'] = 'Successful_operation';

        Util_ZzkCommon::zzk_echo(json_encode(Util_Beauty::wanna($returnJSON)));
        return true;

    }

    private function handle_forget($params)
    {
        //todo:需要添加验证码
        if (empty($params['email'])) {
            $response = array('status' => 'fail', 'userMsg' => 'Request_parameter_error');
        } else {
            $user_bll = new Bll_User_UserInfo();
            $result = $user_bll->send_reset_password_mail($params['email']);

            if ($result) {
                $response = array(
                    'status' => 'ok',
                    'data'=>array('userMsg'=>'Your_password_reset_link_sent_to_your_registered_email' . $params['email']),
                    'userMsg' => 'Your_password_reset_link_sent_to_your_registered_email'. $params['email'],
                );
            } else {
                $response = array('status' => 'fail', 'userMsg' => 'Sending_fails_check_whether_correct_mailbox_fill');
            }
        }
        header('Content-Type:application/json');
        Util_ZzkCommon::zzk_echo(json_encode(Util_Beauty::wanna($response)));
        return true;
    }

    private function handle_homestayCenter($params)
    {

//        $bll_userInfo = new Bll_User_UserInfo();
        $bll_homestay = new Bll_Homestay_StayInfo();
//        $userphoto = $bll_userInfo->get_user_head_pic_by_uid($params['uid']);
        $stay = $bll_homestay->get_whole_homestayinfo_by_uid($params['uid']);
        if ($stay['picture']) {
            $userphoto = Util_Image::zzk_db_file_managed($stay['picture'], $stay['picture_version']);
            $userphoto = str_replace(":/", "", $userphoto);
            $piclink = Util_Image::imglink($userphoto, 'userphoto.jpg');
        } else {
            $piclink = Util_Image::photo_default();
        }

//        $stay = $bll_userInfo->get_whole_user_info($params['uid']);
        $branch = $bll_homestay->get_homestay_branch_by_uid($params['uid']);
/*
$branch_id[] = $params['uid'];
foreach($branch as $row) {
$branch_id[] = $row['uid'];
}
$solr = Util_Solr::get_bnbinfo_by_ids($branch_id);

foreach($solr as $v) {
$solrdata[$v['id']] = $v;
}
 */
        $homedao = new Dao_HomeStay_Stay();
        $orderbll = new Bll_Order_OrderInfo();
        $image = $homedao->get_homestay_images($params['uid']);
        $lastbooking = $orderbll->get_last_order_by_uid($params['uid']);
        if ($lastbooking) {
            $date_format = Util_Common::interval_format($lastbooking['create_time']) . 'New_orders';
        } else {
            $date_format = 'No_order_information';
        }
        $branch_data[] = array(
            'uid' => $stay['uid'],
            'name' => $stay['name'],
            'room_num' => $stay['room_num'],
            'picture' => $image[0]['uri'] ? Util_Image::imglink($image[0]['uri'], 'homepic1024x768.jpg') : Util_Image::img_default_inlist(),
            'latest_order_time' => $date_format,
        );
        foreach ($branch as $row) {
            $pic = $homedao->get_homestay_images($row['uid']);
            $lastbooking = $orderbll->get_last_order_by_uid($row['uid']);
            if ($lastbooking) {
                $date_format = Util_Common::interval_format($lastbooking['create_time']) . 'New_orders';
            } else {
                $date_format = 'No_order_information';
            }
            $branch_data[] = array(
                'uid' => $row['uid'],
                'name' => $row['name'],
                'room_num' => $row['room_num'],
                'picture' => $pic[0]['uri'] ? Util_Image::imglink($pic[0]['uri'], 'homepic1024x768.jpg') : Util_Image::img_default_inlist(),
                'latest_order_time' => $date_format,
            );
        }

        $data = array(
            'uid' => $stay['uid'],
            'name' => $stay['name'],
            'mail' => $stay['mail'],
            'address' => $stay['address'],
            'loc_type' => $stay['type_name'],
            'photo' => $piclink,
            'homestay_list' => $branch_data,
        );
        Util_ZzkCommon::zzk_echo(json_encode($data));

    }

    private function handle_fund($uid, $order_id)
    {
        $coupons_list = array();
        $bll_cou = new Bll_Coupons_CouponsInfo();
        $coupons = $bll_cou->get_canuse_conpons($uid);
        $tmp_coupons = array();
        foreach ($coupons as $key => $value) {
            $tmp_coupons[] = array(
                'coupon' => $value['coupon'],
                'type' => "旅行红包",
                'endtime' => "2015-10-20",
                'pvalue' => $value['pvalue'],
            );
        }
        $coupons_list['coupon_list'] = $tmp_coupons;

        $bll_order = new Bll_Order_OrderInfo();
        $order_info = $bll_order->get_order_info_byid($order_id);
        $same_order = $bll_order->get_same_order_byphoneuid($order_info['guest_telnum'], $order_info['guest_uid']);
        $fund_limit = APF::get_instance()->get_config('usage_fund_limit', 'activity');
        $bll_user = new Bll_User_UserInfo();
        $user = $bll_user->get_whole_user_info($uid);
        if (empty($same_order) && $order_info['total_price'] > 299) {
            $coupons_list['lvjj'] = $user['fund'] > $fund_limit ? $fund_limit : $user['fund'];
        } elseif (!empty($same_order) && $user['fund'] > 0) {
            $coupons_list['lvjj_error'] =Trans::t('same_phone_number_booking_%d','',array('%d'=>$fund_limit));
        } elseif ($order_info['total_price'] < 300 && $user['fund'] > 0) {
            $coupons_list['lvjj_error'] = Trans::t('your_rate_is_less_than_¥_300','',array("%d"=>$fund_limit));
        }
        Util_ZzkCommon::zzk_echo(json_encode($coupons_list));
        return true;
    }

    private function handle_modifynickname($params)
    {

        $uid = $params['uid'];
        if (empty($uid)) {
            $uid = $params['mobile_userid'];
        }

        $nickname = $params['nickname'];

        if (empty($uid)) {
            Util_Json::render(400, null, 'uid needed', 'uid needed');
            return;
        }
        if (empty($nickname)) {
            Util_Json::render(400, null, 'nickname needed', 'nickname needed');
            return;
        }

        $userinfo_bll = new Bll_User_UserInfo();
        $userinfo_bll->insert_or_update_nickname($uid, $nickname);

        $newnickname = $userinfo_bll->get_user_nickname_by_uid($uid);
        Util_Json::render(200, array(
            'nickname' => $newnickname,
        ));

    }
    private function handle_simple_user($params){
        $otherid=$params['other_uid'];
        if(empty($otherid)){
            Util_Json::render(400,null,'other_uid needed');
        }

        $bll_user=new Bll_User_UserInfo();
        $nickname = $bll_user->get_user_nickname_by_uid($otherid);
        if (empty($nickname)) {
            $user = $bll_user->get_user_by_uid($otherid);
            $nickname = $user->name;
        }

        $bll_userInfo = new Bll_User_UserInfo();
        $headpic = $bll_userInfo->get_user_head_pic_by_uid($otherid);
        if (!$headpic) {
            $headpic = Util_Avatar::dispatch_avatar($otherid);
        }

        $result = array(
            'nickname' => $nickname,
            'avatar' => $headpic
        );

        Util_Json::render(200,$result);


    }

    private function render($result)
    {
        $response = Util_Beauty::wanna($result);
        Util_ZzkCommon::zzk_echo(json_encode($response));

        return false;
    }

    /**
     * 更新用户使用的币种
     * method post 参数通用参数和post中的multi_price字段
     * @param $params
     */
    public static function updateUserCurrency($params) {
       //todo 校验是否为合法请求
        $req = APF::get_instance()->get_request();
        if($req->is_post_method()) {
            $uid = isset($params['uid']) ? $params['uid'] : $params['mobile_userid'];
            $ret = UserUtil::updateUserCurrency($uid, $params['multi_price']);
            if($ret) {
                Util_ZzkCommon::zzk_echo(json_encode(['status' => 200, 'message' => 'Successful_operation']));
            } else {
                Util_ZzkCommon::zzk_echo(json_encode(['status' => 1001, 'message' => 'Operation_failed']));
            }
        } else {
            Util_ZzkCommon::zzk_echo(json_encode(['status' => 401, 'message' => 'Request_incorrectly']));
        }
    }
}
