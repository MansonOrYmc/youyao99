<?php
class Bll_Booking_BookingInfo
{
    private $bookingInfoDao;

    public function __construct()
    {
        $this->bookingInfoDao = new Dao_Booking_BookingInfo();
    }

    public function booking_form_submit($params)
    {
        $params = array_map(function ($payload) {
            if (is_array($payload)) {
                return $payload;
            }
            $payload = htmlentities($payload, ENT_QUOTES, 'UTF-8');
            return $payload;
        }, $params);

        $source = isset($params['source']) ? (int) $params['source'] : 0;
        $version = isset($params['version']) ? $params['version'] : '';
        $order_source = isset($params['order_source']) ? $params['order_source'] : '';

        $book_room_id = isset($params['book_room_id']) ? $params['book_room_id'] : 0;
        $guest_name = isset($params['guest_name']) ? $params['guest_name'] : '';
        $guest_first_name = isset($params['guest_first_name']) ? $params['guest_first_name'] : '';
        $guest_last_name = isset($params['guest_last_name']) ? $params['guest_last_name'] : '';
        $guest_number = isset($params['guest_number']) ? $params['guest_number'] : 0;
        $child_number = isset($params['guest_child_number']) ? $params['guest_child_number'] : 0; //儿童数量
        $room__number = isset($params['room_num']) && !empty($params['room_num']) ? $params['room_num'] : 1;
        $guest_date = isset($params['guest_date']) ? $params['guest_date'] : date('Y-m-d', REQUEST_TIME + 86400);
        $guest_days = isset($params['guest_days']) ? $params['guest_days'] : null;
        $guest_telnum = isset($params['guest_telnum']) ? $params['guest_telnum'] : '';
        $guest_mail = isset($params['guest_mail']) ? $params['guest_mail'] : '';
        $guest_uid = isset($params['guest_uid']) ? $params['guest_uid'] : '';
        $guest_etc = isset($params['guest_etc']) ? $params['guest_etc'] : '';
        $coupon = isset($params['coupon']) ? $params['coupon'] : '';
        $guest_checkout_date = isset($params['guest_checkout_date']) ? $params['guest_checkout_date'] : date('Y-m-d', strtotime($guest_date) + (86400 * $guest_days));
        $uid = isset($params['uid']) ? $params['uid'] : 0;
        $guest_wechat = isset($params['wechat']) ? $params["wechat"] : '';
        $guest_line = isset($params['line']) ? $params['line'] : '';
        $guest_child_age = isset($params['guest_child_age']) ? $params['guest_child_age'] : '';
        $guid = isset($params['guid']) ? $params['guid'] : '';
        $multilang = isset($params['multilang']) ? $params['multilang'] : '12';
        if (empty($book_room_id)) {
            return array('code' => 400, 'codeMsg' => '请求参数错误');
        }

        if (strlen($guest_name) <= 0) {
            return array('code' => 403, 'codeMsg' => '姓名不能为空');
        }

        if (strlen($guest_telnum) <= 0) {
            return array('code' => 404, 'codeMsg' => '联系电话不能为空');
        }

        if (strlen($guest_mail) <= 0) {
            return array('code' => 405, 'codeMsg' => '电子邮箱不能为空');
        }

        $book_home_id = 0;
        if ($book_home_id == 0) {
            $uid = Bll_User_Static::get_uid_by_nid($book_room_id);
        }
        $userInfo = new Dao_User_UserInfo();
        $user_info = $userInfo->load_user_info($uid);
        $form_state = array();
/*
    $params['addition_service'] = array(
            array(
                'package_id'        => service_package_id,
                'num'               => num,
            ),
        );
*/
        $form_state['values'] = array( //roomid为空，直接预定民宿
            'taobaoId' => $params['taobaoId'],
            'recipient' => $user_info,
            'name' => $user_info->name,
            'mail' => $user_info->mail,
            'guest_name' => $guest_name,
            'guest_first_name' => $guest_first_name,
            'guest_last_name' => $guest_last_name,
            'guest_number' => $guest_number,
            'guest_date' => $guest_date,
            'guest_days' => $guest_days,
            'guest_checkout_date' => $guest_checkout_date,
            'guest_mail' => $guest_mail,
            'guest_uid' => $guest_uid,
            'guest_telnum' => $guest_telnum,
            'guest_wechat' => $guest_wechat,
            'guest_line_id' => $guest_line,
            'guest_etc' => $guest_etc,
            'order_source' => $order_source,
            'coupon' => $coupon,
            'subject' => $user_info->name . " - 訂房咨詢",
            'message' => '您好,
						我在自在客旅行網站上看到您的民宿信息和漂亮的照片，我非常喜歡！感謝您提供的资料！',
            'guest_child_age' => $guest_child_age,
            'multilang' => $multilang,
            'baoche_id' => $params['baoche_id'] ? $params['baoche_id'] : 0,
            'baoche_price' => $params['baoche_price'] ? $params['baoche_price'] : 0,
            'baoche_price_cn' => $params['baoche_price_cn'] ? $params['baoche_price_cn'] : 0,
            'other_service_id' => $params['other_service_id'] ? $params['other_service_id'] : 0,
            'other_service_price' => $params['other_service_price'] ? $params['other_service_price'] : 0,
            'other_service_price_cn' => $params['other_service_price_cn'] ? $params['other_service_price_cn'] : 0,
            'addition_service' => $params['addition_service'] ? $params['addition_service'] : array(),
            'paytype' => $order_source == 'booking' ? 1 : 0,
            'no_show' => 0,
        );
        if ($book_room_id != 0) { //房间id不为空
            $form_state['values']['book_room_id'] = $book_room_id;
            $form_state['values']['guest_child_number'] = $child_number;
            $form_state['values']['room_num'] = $room__number;
        }
         /*var_dump($form_state['values']);
        exit();*/
        $form_state['from_source'] = $source;
        $form_state['guid'] = $guid;

        return self::contact_personal_form_submit($form_state);
    }

    public function contact_personal_form_submit($form_state)
    {
        $values = $form_state['values'];
        $guest_uid = $values['guest_uid'];
        $dest_id = $values['recipient']->dest_id ? $values['recipient']->dest_id : 10;

        $client_ip = Util_NetWorkAddress::get_client_ip();
        $province = "";
        if (empty($province) && !empty($client_ip)) {
            $client_ip = preg_replace('/,.*$/', '', $client_ip);
            $province = Util_NetWorkAddress::obtain_cityname_by_ip($client_ip);
        }
        $values['province'] = $province;
        $total_guest_nums = (int) $values['guest_number'] + (int) $values['guest_child_number']; //客人总数量

        $bll_room_info = new Bll_Room_RoomInfo();
        $total_count_price_arr = $bll_room_info->total_node_prices_and_add_beds($values['book_room_id'],
            $values['guest_date'], $values['guest_checkout_date'], $values['room_num'],
            $total_guest_nums);
        $default_total_price = $total_count_price_arr['total_count_price_cn'];
        if (($guest_uid == APF::get_instance()->get_config('uid', 'alitrip'))) {
            $room_info_bll = new Dao_Room_RoomInfo();
            $channel = strtoupper($values['order_source']);
            $room_discount = $room_info_bll->get_channel_discount($values['book_room_id'], $channel);
            $discount_rate = 1;
            if($room_discount['rate']) {
                $discount_rate = $room_discount['rate'];
            }
            $third_price = $_REQUEST['totalPrice'];
            if( $_REQUEST['currency'] == 'JPY') {
                $zzk_price = $total_count_price_arr['total_count_price_tw'];
            }else{
                $zzk_price = $default_total_price;
            }
            if ($third_price < ( (floor($zzk_price * $discount_rate) - 5 ) * 100 ) or (empty($third_price))) {
                Logger::info(__FILE__, __CLASS__, __LINE__, "third booking failed", var_export(array(
                    "request" => $_REQUEST,
                    "zzk_price" => $total_count_price_arr,
                    "discount" => $room_discount,
                ), true));
                return array('code' => 207, 'codeMsg' => Trans::t('Price_verification_fails'));
            }
        }
        $room_name = '';
        if (isset($values['book_room_id'])) {
            $room_name = $bll_room_info->get_room_name_by_nid($values['book_room_id']);
        }
        $values['room_name'] = $room_name;
       /* var_dump($values);
        exit();*/
        $customer_id = self::zzk_new_customer($values, $values['recipient']->name . "\n" . $values['room_name'] . "\n" . $values['guest_date'] . "\n" . $values['guest_checkout_date'] . "\n" . $values['guest_etc']);

        /* 同意条款后不再显示 */
        if ($values['article'] == '1') {
            setcookie("readarticle", '1', time() + 3600 * 24 * 30);
        }

        $user = array();

        $values['sender'] = $user;
        $values['sender']->name = $values['name'];
        $values['sender']->mail = $values['mail'];

        $singnatures = '';

        //判断民宿是否下架
        $homestay_dao = new Dao_HomeStay_Stay();
        $take_holiday = $homestay_dao->get_hs_holiday($values['recipient']->uid);
        $room_status = 1;
        if ($values['book_room_id']) {
            $room_status = $bll_room_info->get_room_statue_by_nid($values['book_room_id']);
        }

        // add by andrew 2014.11.29; 用户状态是0也不允许下单，之前下架功能不正常时，take_holiday没数据；
        $bll_user_info = new Bll_User_UserInfo();
        $homestay_status = $bll_user_info->get_user_status_by_uid($values['recipient']->uid);
        if ($take_holiday != 0 || $room_status != 1 || $homestay_status == 0) {
            return array('code' => 2, 'codeMsg' => Trans::t('BnB_racks_or_termination'));
        }

        //服务端判断是否是过期日期
        if ($values['guest_date'] < date('Y-m-d', time())) {
            $err_url = Const_Host_Domain . '/error?type=error&nid=' . $values['book_room_id'] . '&nodename=' . $room_name . '&g_order_days=' . $values['guest_date'] . '&g_c_date=' . $values['guest_checkout_date'] . '';
            return array('code' => 406, 'codeMsg' => Trans::t('Check-in_time_is_incorrect'), 'body' => array('goto_url' => $err_url));
        }

        if (empty($values['guest_checkout_date']) || empty($values['guest_date']) || $values['guest_checkout_date'] <= $values['guest_date']) {
            $err_url = Const_Host_Domain . '/error?type=error&nid=' . $values['book_room_id'] . '&nodename=' . $room_name . '&g_order_days=' . $values['guest_date'] . '&g_c_date=' . $values['guest_checkout_date'] . '';
            // zzk_err_order_debug('err_order_checkinout_debug_'.$_SERVER['HTTP_USER_AGENT'], $err_url);
            return array('code' => 407, 'codeMsg' => Trans::t('Check-out_time_is_incorrect'), 'body' => array('goto_url' => $err_url));
        }

        // add by andrew 2014.11.18, 确保入住和退房日期是合理的
        //判断房态
        if ($values['book_room_id'] && $values['guest_date'] && $values['guest_checkout_date']) {
            $order_days = "";
            $order_status = $bll_room_info->node_room_trac_status_check_news($values['book_room_id'], $values['guest_date'], $values['guest_checkout_date'], $values['room_num'], '', $total_guest_nums);
            if (count($order_status) >= 1) {
                foreach ($order_status as $k => $v) {
                    $order_days .= $v[1] . ',';
                }
                $order_days = trim($order_days, ',');
                $err_url = Const_Host_Domain . '/error?type=error&nid=' . $values['book_room_id'] . '&nodename=' . $room_name . '&g_order_days=' . $order_days . '&g_c_date=' . $values['guest_checkout_date'] . '';
                // zzk_err_order_debug('err_order_debug_'.$_SERVER['HTTP_USER_AGENT'],$err_url);
                return array('code' => 408, 'codeMsg' => Trans::t('No_room_in_the_day'), 'body' => array('goto_url' => $err_url));
            }

            $minStay = new Bll_Minimumstay_Minimumstay();
            $minStayDays = $minStay->validateMinStayRequirement($values['book_room_id'], $values['guest_date'], $values['guest_checkout_date']);
            if ($minStayDays > 1) {
                $err_url = Const_Host_Domain . '/error?type=error&nid=' . $values['book_room_id'] . '&nodename=' . $room_name . '&g_order_days=' . $order_days . '&g_c_date=' . $values['guest_checkout_date'] . '';
                return array('code' => 409, 'codeMsg' => Trans::t('room_set_up_continuous_check','',array('%d'=>$minStayDays)), 'body' => array('goto_url' => $err_url));
            }
        }

        if ($values['guest_mail'] == 'osn_0102@163.com' || $values['guest_telnum'] == '13290102727' || $client_ip == '124.133.174.25') {
            $err_url = Const_Host_Domain . '/error?type=error&nid=' . $values['book_room_id'] . '&nodename=' . $room_name . '&g_order_days=' . $order_days . '&g_c_date=' . $values['guest_checkout_date'] . '';
            return array('code' => 404, 'codeMsg' => Trans::t('Contact_information_is_not_correct'), 'body' => array('goto_url' => $err_url));
        }

        // Save the anonymous user information to a cookie for reuse.
        if (!$user->uid) {
            Util_Cookie::user_cookie_save(array_intersect_key($values, array_flip(array('name', 'mail'))));
        }

        // Get the to and from e-mail addresses.
        $to = $values['recipient']->mail;
        // commented by tonycai
        $from = 'noreply@youyao.com';

        // Send the e-mail in the requested user language.
        $guest_telnum = $values['guest_telnum'];

        $book_room_id = isset($values['book_room_id']) ? $values['book_room_id'] : 0;
        $recipient_id = isset($values['recipient']->uid) ? $values['recipient']->uid : 0;
        $guest_mail = isset($values['guest_mail']) ? $values['guest_mail'] : '';
        $guest_date = isset($values['guest_date']) ? $values['guest_date'] : '';

        //modify by axing 2014-08-18
        $booking_create_time = REQUEST_TIME - 86400;
        $bll_homestay_info = new Bll_Homestay_StayInfo();
        $cc = $bll_homestay_info->get_stay_booking_count($guest_mail, $booking_create_time);
        if ($cc >= 20) {
            $set_message = '感谢您！您的订单已经成功提交，我们的客服将会在' . Util_Common::zzk_exchange_time() . '之内与您联系。';
            $goto_url = Const_Host_Domain . '/error?type=exce';
            return array('code' => 4, 'codeMsg' => $set_message, 'body' => array('goto_url' => $goto_url));
        }

        $guest_ckeckin = new DateTime($values['guest_date']);
        $guest_ckeckout = new DateTime($values['guest_checkout_date']);
        $guest_interval = $guest_ckeckin->diff($guest_ckeckout);

        if (!isset($values['guest_days'])) {
            $values['guest_days'] = $guest_interval->format('%a');
            $values['guest_days'] = (int) $values['guest_days'] > 0 ? (int) $values['guest_days'] : 1;
            if ($values['guest_days'] > 60) {
                $values['guest_days'] = 60;
            }
        }

        $coupon = trim($values['coupon']); //抵扣券
        $campaign_code = isset($_COOKIE['campaign_code']) && !empty($_COOKIE['campaign_code']) ? $_COOKIE['campaign_code'] : '';
        $zzkcamp = empty($_COOKIE['zzkcamp']) ? '' : $_COOKIE['zzkcamp'];
        $zfansref = !empty($_COOKIE['zfansref']) ? (int) $_COOKIE['zfansref'] : 0;

        //是否是速订房间
        //axing 2014-08-18
        $rev_percent = $values['recipient']->poi->rev_percent ? $values['recipient']->poi->rev_percent : 100;
        $loc_typecode = $values['recipient']->poi->loc_typecode;
        $self_service = $values['recipient']->poi->self_service;

        $loc_arr = explode(',', $loc_typecode);
        $loc_id_n = count($loc_arr);
        $loc_id = $loc_arr[$loc_id_n - 1];
        $bll_area_info = new Bll_Area_Area();
        $city = $bll_area_info->get_area_by_locid($loc_id, $values['recipient']->dest_id);
        $city_name = $city['type_name'];

        $total_guest_num = (int) $values['guest_number'] + (int) $values['guest_child_number'];
        if ($values['book_room_id']) {
            $room_detail = $bll_room_info->zzk_room_detail_contact_order($values['book_room_id']);
            $speed_room = $room_detail->speed_room ? 1 : 0;
        }

        $room_price_count_check = $room_detail->room_price_count_check ? $room_detail->room_price_count_check : 1;

        // add by andrew 2014.12.19 记录下单时汇率
        $destConfig = $bll_area_info->get_dest_config_by_destid($dest_id);
        if (empty($destConfig)) {
            $exchangeRate = 0.00;
        } else {
            $exchangeRate = $destConfig['exchange_rate'];
        }
        // end add...

        $uid_by_mail = 0;
        $new_user = null;
        if (!empty($values['guest_uid'])) {
            $uid_by_mail = $values['guest_uid'];
        } else {
            $sign_bll = new Bll_User_Sign();
            $new_user = $sign_bll->obtain_user_by_phone_mail(trim($values['guest_telnum']), trim($values['guest_mail']));
            $uid_by_mail = $new_user['userid'];
        }

        $insert_booking_info = array(
            'uid' => isset($values['recipient']) ? $values['recipient']->uid : 0,
            'nid' => isset($values['book_room_id']) ? $values['book_room_id'] : 0,
            'room_name' => $room_name,
            'uname' => isset($values['recipient']->name) ? $values['recipient']->name : "undefined",
            'umail' => $values['recipient']->mail,
            'mail_subject' => $values['subject'],
            'mail_body' => $values['message'],
            'guest_name' => trim($values['guest_name']),
            'guest_number' => !empty($values['guest_number']) ? $values['guest_number'] : 1,
            'guest_date' => $values['guest_date'],
            'guest_checkout_date' => $values['guest_checkout_date'],
            'guest_days' => !empty($values['guest_days']) ? $values['guest_days'] : 1,
            'guest_etc' => $values['guest_etc'],
            'guest_mail' => trim($values['guest_mail']),
            'guest_telnum' => Util_Common::filter_phone($values['guest_telnum']),
            'guest_wechat' => !empty($values['guest_wechat']) ? $values['guest_wechat'] : '',
            'room_num' => isset($values['room_num']) ? $values['room_num'] : 1,
            'mid' => !empty($user->uid) ? $user->uid : '',
            'guest_uid' => $uid_by_mail,
            'client_ip' => $client_ip,
            'create_time' => REQUEST_TIME,
            'last_modify_date' => REQUEST_TIME,
            'province' => $province,
            'city_name' => $city_name,
            'self_service' => $self_service,
            'coupon' => $coupon,
            'rev_percent' => $rev_percent,
#            'speed_room' => $speed_room, // 由于有速订转咨询单的情况，所以速订字段在下面update
            'speed_room' => 0,
            'dest_id' => $dest_id,
            'campaign_code' => $campaign_code,
            'zzkcamp' => $zzkcamp,
            'zfansref' => $zfansref,
            'guest_child_number' => !empty($values['guest_child_number']) ? $values['guest_child_number'] : 0,
            'guest_child_age' => $values['guest_child_age'],
            'exchange_rate' => $exchangeRate,
            'room_price_count_check' => $room_price_count_check,
            'order_source' => $values['order_source'],
            'customer_id' => $customer_id,
        );

        $bll_order_info = new Bll_Order_OrderInfo();

        $last_order_id = $bll_order_info->insert_homestay_booking($insert_booking_info);

        $hash_util = new Util_Hash();
        $hash_id = $hash_util->update_order_hash_id($last_order_id);
        Util_Common::async_curl_in_terminal(Util_Common::url("/m/send", "api"), $send_mail_params);

        if (isset($values['taobaoId'])) {
            Alitrip_Order::insert_order_id_taobao_id($values['taobaoId'], $hash_id, $_REQUEST['totalPrice']);
        }
        $hash_id = strval($hash_id);
        Logger::info(__FILE__, __CLASS__, __LINE__, $last_order_id);

        if (!empty($values['guest_line_id']) || !empty($values['multilang'])) {
            $bll_order_info->insert_homestay_booking_addtion($last_order_id,
                array('guest_line_id' => $values['guest_line_id'],
                    'guest_language' => $values['multilang'],
                    'baoche_id' => $values['baoche_id'],
                    'baoche_price' => $values['baoche_price'],
                    'baoche_price_cn' => $values['baoche_price_cn'],
                    'other_service_id' => $values['other_service_id'],
                    'other_service_price' => $values['other_service_price'],
                    'other_service_price_cn' => $values['other_service_price_cn'],
                    'guest_last_name' => $values['guest_last_name'],
                    'guest_first_name' => $values['guest_first_name'],
                    'paytype' => $values['paytype'],
                    'no_show' => $values['no_show'],
                )
            );
        }
        if(!empty($values['addition_service'])) {
            foreach($values['addition_service'] as $ser_row) {
                $service_id[] = $ser_row['package_id'];
            }
            $package = $bll_homestay_info->get_service_package_by_ids($service_id);
            foreach($values['addition_service'] as &$addition_service_row) {
                $addition_service_row['service_category'] = $package[$addition_service_row['package_id']]['category'];
                $addition_service_row['price'] = $package[$addition_service_row['package_id']]['price'];
                $addition_service_row['price_cn'] = Util_Common::zzk_tw_price_convert($package[$addition_service_row['package_id']]['price'], $dest_id);
            }
            $bll_order_info->insert_homestay_booing_service($last_order_id, $values['addition_service']);
        }

        $guid = empty($form_state['guid']) ? '' : $form_state['guid'];
        Util_Common::log_order_guid($last_order_id, $guid);
        //插入订单日志
        $insert_log_booking_trac_info = array(
            0,
            "提交咨询单",
            "提交咨询单",
            isset($total_price_tw) ? $total_price_tw : $total_count_price_arr['total_count_price_tw'],
            $uid_by_mail,
            $last_order_id,
            REQUEST_TIME,
            $client_ip,
        );
        $result = $bll_order_info->insert_log_homestay_booking_trac($insert_log_booking_trac_info);
        Logger::info(__FILE__, __CLASS__, __LINE__, $result);

        if (isset($values['guest_date']) && !empty($values['guest_date'])) {
            Util_Cookie::zzk_dsetcookie('checkin', $values['guest_date'], 31536000, 0, true);
        }
        if (isset($values['guest_checkout_date']) && !empty($values['guest_checkout_date'])) {
            Util_Cookie::zzk_dsetcookie('checkout', $values['guest_checkout_date'], 31536000, 0, true);
        }

        global $bnb_dest_id;
        $bnb_dest_id = $values['recipient']->dest_id;
        $guest_child_number = !empty($values['guest_child_number']) ? '+' . $bll_area_info->get_dest_language($bnb_dest_id, "children") . ':' . $values['guest_child_number'] . '(' . $values['guest_child_age'] . ')' : '';
        if ($values['guest_child_age']) {
            $guest_child_number .= '(' . $values['guest_child_age'] . ')';
        }

        //第一个咨询单
        $first_order = '';
        $first_order = $bll_order_info->zzk_first_order_new($values['recipient']->uid);

        if ($first_order) {
            $values['message'] .= '<ul style="margin-top:20px;line-height:25px;">
<li style="list-style:none;font-size:16px;margin-bottom:10px;color:#0E74B2;">' . $bll_area_info->get_dest_language($bnb_dest_id, "itismylist") . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "bookingid") . ':</strong> #' . $hash_id . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "checkinroom") . ':</strong> ' . $values['room_name'] . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "guestname") . ':</strong> ' . $values['guest_name'] . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "checkPax") . ':</strong> ' . $bll_area_info->get_dest_language($bnb_dest_id, "adult") . '：' . $values['guest_number'] . '&nbsp;' . $guest_child_number . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "roomnum") . ':</strong> ' . $values['room_num'] . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "Check in date") . ':</strong> ' . Util_Common::zzk_date_format($values['guest_date'], $bnb_dest_id) . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "Chack out date") . ':</strong> ' . Util_Common::zzk_date_format($values['guest_checkout_date'], $bnb_dest_id) . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "checkindays") . ':</strong> ' . $values['guest_days'] . $bll_area_info->get_dest_language($bnb_dest_id, "days") . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "comefrom") . ':</strong> ' . $province . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "othernotes") . ':</strong> ' . $values['guest_etc'] . '</li>
</ul>
';
            $values['message_subject'] = $bll_area_info->get_dest_language($bnb_dest_id, "yougotanewbooking");
        } else {
            $values['message'] .= '<ul style="margin-top:20px;line-height:25px;">
<li style="list-style:none;font-size:16px;margin-bottom:10px;color:#0E74B2;">' . $bll_area_info->get_dest_language($bnb_dest_id, "itismylist") . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "bookingid") . ':</strong> #' . $hash_id . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "checkinroom") . ':</strong> ' . $values['room_name'] . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "guestname") . ':</strong> ' . $values['guest_name'] . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "checkPax") . ':</strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "adult") . '： ' . $values['guest_number'] . '&nbsp;' . $guest_child_number . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "roomnum") . ':</strong> ' . $values['room_num'] . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "Check in date") . ':</strong> ' . Util_Common::zzk_date_format($values['guest_date'], $bnb_dest_id) . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "Chack out date") . ':</strong> ' . Util_Common::zzk_date_format($values['guest_checkout_date'], $bnb_dest_id) . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "checkindays") . ':</strong> ' . $values['guest_days'] . $bll_area_info->get_dest_language($bnb_dest_id, "days") . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "comefrom") . ':</strong> ' . $province . '</li>
<li style="list-style:none;font-size:14px;"><strong>' . $bll_area_info->get_dest_language($bnb_dest_id, "othernotes") . ':</strong> ' . $values['guest_etc'] . '</li>
</ul>
';
            $values['message_subject'] = $bll_area_info->get_dest_language($bnb_dest_id, "yougotanewbooking");
        }

        if ($values['recipient']->uid < 30000000) {
            $values['message'] .= '
 <div style="background:url(http://wiki.youyao.com/images/b/b1/I2.png) no-repeat 2px 5px;margin-top:20px;padding-left:14px;color:#333;">' . $bll_area_info->get_dest_language($bnb_dest_id, "please sign in") . '<a href="' . Const_Host_Domain . '">' . $bll_area_info->get_dest_language($bnb_dest_id, "youyao") . '</a>' . $bll_area_info->get_dest_language($bnb_dest_id, "thanvisitthewebtomanagedyoulis") . ':<br>
 http://taiwan.youyao.com/user/' . $values['recipient']->uid . '/orderlist
</div>
';
        }

        $guest_child_number = !empty($values['guest_child_number']) ? '  +儿童:' . $values['guest_child_number'] : '';
        if ($values['guest_child_age']) {
            $guest_child_number .= '(' . $values['guest_child_age'] . ')';
        }

        $values['message_to_user'] = '
<li style="list-style:none;font-size:16px;margin-bottom:10px;color:#0E74B2;">咨询单信息</li>
<li style="list-style:none;font-size:14px;"><strong>咨询单号:</strong> #' . $hash_id . '</li>
<li style="list-style:none;font-size:14px;"><strong>咨询民宿:</strong> ' . $values['recipient']->name . '</li>
<li style="list-style:none;font-size:14px;"><strong>咨询房间:</strong> ' . $values['room_name'] . '</li>
<li style="list-style:none;font-size:14px;"><strong>姓　　名:</strong> ' . $values['guest_name'] . '</li>
<li style="list-style:none;font-size:14px;"><strong>入住人数:</strong> 成人：' . $values['guest_number'] . '人' . $guest_child_number . '</li>
<li style="list-style:none;font-size:14px;"><strong>房间数量:</strong> ' . $values['room_num'] . '</li>
<li style="list-style:none;font-size:14px;"><strong>入住日期:</strong> ' . Util_Common::zzk_date_format($values['guest_date']) . '</li>
<li style="list-style:none;font-size:14px;"><strong>退房日期:</strong> ' . Util_Common::zzk_date_format($values['guest_checkout_date']) . '</li>
<li style="list-style:none;font-size:14px;"><strong>入住天數:</strong> ' . $values['guest_days'] . '天</li>
<li style="list-style:none;font-size:14px;"><strong>邮件地址:</strong>' . $values['guest_mail'] . '</li>
<li style="list-style:none;font-size:14px;"><strong>联系电话:</strong>' . $values['guest_telnum'] . '</li>
<li style="list-style:none;font-size:14px;"><strong>其　　它:</strong> ' . $values['guest_etc'] . '</li>
';

        $values['last_order_id'] = $last_order_id;
        $values['hash_id'] = $hash_id;

        // 不是速订 发咨询单给民宿   如果速订失败，下面会补发
        if (!$first_order &&
            !($guest_uid == APF::get_instance()->get_config('uid', 'alitrip')) &&
            $take_holiday != 2 &&
            !in_array($to, array('castillo.kt@gmail.com', 'banana8851600@hotmail.com', 'kenting852@yahoo.com.tw')) &&
            !$speed_room
        ) {
            //Util_ThemplateMail::contact_user_mail($to, $values, $from);
            // 2b 咨询单
            $send_mail_params = array(
                "action" => "b_order_consult",
                "order_id" => $hash_id,
                "send" => true,
            );
            Util_Common::async_curl_in_terminal(Util_Common::url("/m/send", "api"), $send_mail_params);
        }

        // Send a copy if requested, using current page language.
        //send mail to users
        // 不是速订 这里需要补发 咨询单邮件给客人
        if (!($guest_uid == APF::get_instance()->get_config('uid', 'alitrip')) &&
            !$speed_room
        ) {
            //Util_ThemplateMail::contact_user_check($values['guest_mail'], $values, $from);
            // 2c 咨询单
            $send_mail_params = array(
                "action" => "c_order_new",
                "order_id" => $hash_id,
                "send" => true,
            );
            Util_Common::async_curl_in_terminal(Util_Common::url("/m/send", "api"), $send_mail_params);
        }

        Logger::info(__FILE__, __CLASS__, __LINE__, '多目的地短信');

        //多目的地短信
        require_once dirname(__FILE__) . '/../../includes/unicode.inc';

        $sms_area = 2;
        if ($dest_id == 10) {
            $foparams['oid'] = $last_order_id;
            $foparams['values'] = $values;
            $sms_content = truncate_utf8("【自在客】您好，您有一咨詢單(" . $hash_id . ")。客人：" . Util_Common::zzk_translate($values['guest_name'], 'zh-tw') . "/" . substr($values['guest_date'], 5, 10) . "入住/" . substr($values['guest_checkout_date'], 5, 10) . "退房/" . Util_Common::zzk_translate($values['room_name'], 'zh-tw'), 60) . "...詳見網站";
        } elseif ($dest_id == 12) {
            $foparams['oid'] = $last_order_id;
            $foparams['values'] = $values;
            $sms_content = truncate_utf8("【自在客】您好，您有一咨询单(" . $hash_id . ")。客人：" . $values['guest_name'] . "/" . substr($values['guest_date'], 5, 10) . "入住/" . substr($values['guest_checkout_date'], 5, 10) . "退房/" . $values['room_name'], 60) . "...详见网站";
            $sms_area = 1;
        } else {
            $foparams['oid'] = $last_order_id;
            $foparams['values'] = $values;
            $sms_content = truncate_utf8("【自在客】您好，您有一咨詢單(" . $hash_id . ")。客人：" . Util_Common::zzk_translate($values['guest_name'], 'zh-tw') . "/" . substr($values['guest_date'], 5, 10) . "入住/" . substr($values['guest_checkout_date'], 5, 10) . "退房/" . Util_Common::zzk_translate($values['room_name'], 'zh-tw'), 60) . "...詳見網站";
        }

        if (!$first_order) {
            if (!$speed_room && $take_holiday != 2) {
                Util_Notify::send_sms_notify(array(
                    'oid' => $last_order_id,
                    'sid' => $values['recipient']->uid,
                    'uid' => !empty($uid_by_mail) ? $uid_by_mail : 0,
                    'mobile' => $values['recipient']->send_sms_telnum,
                    'content' => $sms_content,
                    'area' => $sms_area,
                    'dest_id' => $dest_id,
                ));
            }
        }

        //Push APP Notify
        if (isset($values['recipient']->mail)) {
            Util_Notify::send_mobile_notify($values['recipient']->mail, $sms_content);
            Util_Notify::push_message_client($values['recipient']->mail, '', '', $sms_content, Util_Notify::get_push_mtype('admin_order'), $hash_id);
        }

        Util_Common::flood_register_event('contact', Util_Common::variable_get('contact_threshold_window', 3600));
        //标记订单是否打折
        Util_Common::async_curl_in_terminal(Util_Common::url("/promotion/mark", "api"), array('order_id'=>$last_order_id));

        $from_source = $form_state['from_source']; //0: unknow 1: 网站 2: 移动网站 3: iPhone 4: Android
        if (($from_source == 3 || $from_source == 4) && !$speed_room) {
            $default_total_price_nodisc_tw = $total_count_price_arr['total_count_price_nodisc_tw'];
            $default_total_price_nodisc_cn = $total_count_price_arr['total_count_price_nodisc_cn'];
            $bll_order_info->zzk_update_nodisc_price($last_order_id, $default_total_price_nodisc_tw, $default_total_price_nodisc_cn);
            return array(
                'code' => 1,
                'codeMsg' => '移动端API下单成功[1]',
                'body' => array('order_id' => $hash_id, 'new_user' => $new_user, 'speed' => 0),
            );
        }

        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($values, true));
        //是否是速订房间

        if ($values['book_room_id']) {
            if ($speed_room) {
                //房间加人情况、及加人费用
                $date_up = array();
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($total_count_price_arr, true));
                $default_total_price_tw = $total_count_price_arr['total_count_price_tw'];
                $default_total_price_nodisc_tw = $total_count_price_arr['total_count_price_nodisc_tw'];
                $default_total_price_nodisc_cn = $total_count_price_arr['total_count_price_nodisc_cn'];
                $data_up = $total_count_price_arr['date_up'];
               
                //超过6个月的速订变成咨询单 //leon 12个月
                $endDate = date('Y-m-d', time() + 2 * 180 * 60 * 60 * 24);
                $endDate_flag = 1;
                if (date('Y-m-d', strtotime($values['guest_date'])) > $endDate) {
                    $endDate_flag = 0;
                }
                //超过人数并且没有设置加人费用的，则变成咨询单
                $outGuestnum_flag = 1;
                $room_num = isset($values['room_num']) ? $values['room_num'] : 1;
                if (($total_guest_num > $room_detail->room_model * $room_num) && !$room_detail->add_bed_check && !($total_count_price_arr['date_up']['add_bed_price_tw'])){

                    $outGuestnum_flag = 0;
                }


         //跨年期间的不给订

                /*$outTime_flag=1;
=======
                //跨年期间的不给订
                $outTime_flag=1;
>>>>>>> 550e673cbb94e9abcfbff9238c9fb0d473dcf45e
                $mintime1=strtotime('2016-12-30');
                $maxtime1=strtotime('2017-01-02');
                $mintime2=strtotime('2017-01-26');
                $maxtime2=strtotime('2017-02-06');

                $nowtime=strtotime($values['guest_date']);
                $endtime=strtotime($values['guest_checkout_date']);
                
                if(
                    ($mintime1<=$nowtime && $nowtime<=$maxtime1)
                    ||($mintime1<=$endtime && $endtime<=$maxtime1)
                    ||($nowtime>=$mintime2&& $nowtime<=$maxtime2)
                    ||($endtime>=$mintime2&& $endtime<=$maxtime2)
                    ||($nowtime>=$mintime1 && $endtime>=$maxtime1)
                    ||($nowtime>=$mintime2 && $endtime>=$maxtime2)
                ){
                    $outTime_flag =0;
<<<<<<< HEAD
                }*/
           
                //是否加人
                $add_man_check = 1;
                if ($total_guest_num > ($room_detail->room_model + $room_detail->add_bed_num) * $room_num) {
                    $add_man_check = 0;
                }
                $sp_invail_date = 1;
                $sp_verify_bll = new Dao_SpeedRoom_Date();
                $sp_date = $sp_verify_bll->get_speedroom_date_bynids(array($values['book_room_id']));
                foreach ($sp_date as $row) {
                    if (strtotime($row['start_date']) <= strtotime($values['guest_date'])
                        && !empty($row)
                        && strtotime($values['guest_checkout_date']) < strtotime($row['end_date'])
                    ) {
                        $sp_invail_date = 1;
                        break;
                    } else {
                        $sp_invail_date = 0;
                    }
                }
                // 每天的价格 必须大于7天前的 1/2
                /*
                $bad_price = 1;
                $price_bll = new Bll_Price_Price();
                $bad_price = $price_bll->check_price($values['recipient']->uid, $values['book_room_id'], $values['guest_date'], $values['guest_checkout_date']);
                 */

                if ($speed_room && !($default_total_price_tw > 100 && $endDate_flag == 1 && $outGuestnum_flag == 1 && $add_man_check == 1 && $sp_invail_date == 1 )) {
                    Logger::info(__FILE__, __CLASS__, __LINE__, var_export(array(
                        "hash_id" => $hash_id,
                        "default_total_price_tw" => $default_total_price_tw,
                        "endDate_flag" => $endDate_flag,
                        'outGuestnum_flag' => $outGuestnum_flag,
                        'add_man_check' => $add_man_check,
                        'sp_invail_date' => $sp_invail_date,
                        'total_num' => $total_num,
                        'room_num' => $room_num,
                        'room_detail' => $room_detail,
                        'bad_price' => $bad_price,
                    ), true));
                }
                //订单价格大于100
                if ($default_total_price_tw > 100 && $endDate_flag == 1 && $outGuestnum_flag == 1 && $add_man_check == 1 && $sp_invail_date == 1 ) {
                    $user_uid = '1';
                    $v['speed_room'] = 1; //只有价格通过验证才会更新订单的速订字段
                    $v['order_status'] = 4;
                    $v['total_price_tw'] = $default_total_price_tw;
                    $total_price_tw = isset($v['total_price_tw']) && !empty($v['total_price_tw']) ? (int) $v['total_price_tw'] : 0;
                    //$v['total_price'] = Util_Common::zzk_tw_price_convert($total_price_tw,$room_detail->dest_id);
                    $v['total_price'] = $default_total_price;

                    if (($guest_uid == APF::get_instance()->get_config('uid', 'alitrip'))) {
                        $v['order_status'] = 2;
                    }
                    if ($values['order_source'] == 'booking') { // booking 没有折扣
                        $v['total_price_tw'] = $default_total_price_nodisc_tw;
                        $v['total_price'] = $default_total_price_nodisc_cn;
                    }

                    $v['content'] = '已经确定有房间，暂时为您保留，请尽快付款。';
                    $order_status_changed = "";
                    $status_mapping = $bll_order_info->zzk_order_status_mapping();
                    $order_status_changed = "订单进度从 " . $status_mapping[0] . " 到 " . $status_mapping[$v['order_status']] . "。 ";
                    $order_status_changed .= "金额从 0 元 到 " . $v['total_price'] . "元。 ";

                    $return_status_info = $bll_order_info->zzk_save_order_trac_content($last_order_id, $user_uid, $order_status_changed, $v['content'], $v['order_status'], $v);
                    //订单需要保存原价

                    $bll_order_info->zzk_update_nodisc_price($last_order_id, $default_total_price_nodisc_tw, $default_total_price_nodisc_cn);
                    if (!$return_status_info['status']) {
                        return $return_status_info['action'];
                    }

                    if ($from_source == 3 || $from_source == 4) { // iphone 或者 android
                        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
                        $key = Util_MemCacheKey::get_order_submit_key();
                        $memcache->set($key, time(), null, 86400);
                        return array('code' => 1, 'codeMsg' => '移动端API下单成功[2]', 'body' => array('order_id' => $hash_id, 'new_user' => $new_user, 'speed' => 1));
                    }

                    $obid = Util_Common::shortUrl_new($last_order_id);
                    $obid = strtoupper($obid[0]);
                    $goto_url = Const_Host_Domain . "/user/payment/" . $obid;

                    //如果满足加人费用，则更新booking一些加人字段的信息
                    if ($data_up['add_bed_price_tw']) {
                        $data_up_info = array(
                            $data_up['add_bed_price'],
                            $data_up['add_bed_price_tw'],
                            $data_up['book_room_model'],
                            $last_order_id,
                        );
                        $bll_homestay_info->update_add_bed_price_info($data_up_info);
                    }
                } else {

                    // 是速订 这里需要补发 咨询单邮件给民宿
                    if (!$first_order &&
                        !($guest_uid == APF::get_instance()->get_config('uid', 'alitrip')) &&
                        $take_holiday != 2 &&
                        !in_array($to, array('castillo.kt@gmail.com', 'banana8851600@hotmail.com', 'kenting852@yahoo.com.tw')) &&
                        $speed_room
                    ) {
                        //Util_ThemplateMail::contact_user_mail($to, $values, $from);
                        // 2b 咨询单
                        $send_mail_params = array(
                            "action" => "b_order_consult",
                            "order_id" => $hash_id,
                            "send" => true,
                        );
                        Util_Common::async_curl_in_terminal(Util_Common::url("/m/send", "api"), $send_mail_params);
                    }

                    // Send a copy if requested, using current page language.
                    //send mail to users
                    // 是速订 这里需要补发 咨询单邮件给客人
                    if (!($guest_uid == APF::get_instance()->get_config('uid', 'alitrip')) && $speed_room) {
                        //Util_ThemplateMail::contact_user_check($values['guest_mail'], $values, $from);
                        // 2c 咨询单
                        $send_mail_params = array(
                            "action" => "c_order_new",
                            "order_id" => $hash_id,
                            "send" => true,
                        );
                        Util_Common::async_curl_in_terminal(Util_Common::url("/m/send", "api"), $send_mail_params);
                    }

                    if ($from_source == 3 || $from_source == 4) { // iphone 或者 android
                        $bll_cus = new Bll_Customer_Notify();
                        $bll_cus->send_msg(Util_Common::filter_phone($values['guest_telnum']));
                        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
                        $key = Util_MemCacheKey::get_order_submit_key();
                        $memcache->set($key, time(), null, 86400);
                        return array('code' => 1, 'codeMsg' => '移动端API下单成功[3]', 'body' => array('order_id' => $hash_id, 'new_user' => $new_user, 'speed' => 0));
                    }
                    $goto_url = Const_Host_Domain . '/contactok';
                    $memcache = APF_Cache_Factory::get_instance()->get_memcache();
                    $key = Util_MemCacheKey::get_order_submit_key();
                    $memcache->set($key, time(), null, 60);
                    return array(
                        'code' => 201,
                        'codeMsg' => '网站端下单成功[速订]',
                        'body' => array(
                            'order_id' => $hash_id,
                            'goto_url' => $goto_url,
                            'new_user' => $new_user,
                        ),
                    );
                }
                $memcache = APF_Cache_Factory::get_instance()->get_memcache();
                $key = Util_MemCacheKey::get_order_submit_key();
                $memcache->set($key, time(), null, 60);
                return array('code' => 200, 'codeMsg' => '网站端下单成功[速订]', 'body' => array('goto_url' => $goto_url, 'new_user' => $new_user));
            }else{
                $default_total_price_nodisc_tw = $total_count_price_arr['total_count_price_nodisc_tw'];
                $default_total_price_nodisc_cn = $total_count_price_arr['total_count_price_nodisc_cn'];
                $bll_order_info->zzk_update_nodisc_price($last_order_id, $default_total_price_nodisc_tw, $default_total_price_nodisc_cn);
            }
        }
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $key = Util_MemCacheKey::get_order_submit_key();
        $memcache->set($key, time(), null, 60);
        return array(
            'code' => 201,
            'codeMsg' => '网站端下单成功[非速订]',
            'body' => array(
                'order_id' => $hash_id,
                'goto_url' => Const_Host_Domain . '/contactok',
                'new_user' => $new_user,
            ),
        );
    }

    public function zzk_new_customer($v, $remark = "")
    {
        //new customer
        if (!isset($v['guest_mail'])) {
            return false;
        }

        $userInfo = new Dao_User_UserInfo();

        $dest_id = $v['recipient']->dest_id ? $v['recipient']->dest_id : 10;

        $polaris = new Bll_Sale_DispatchCustomer();
        $response = $polaris->get_next_sale($dest_id, $v['guest_telnum'], $v['guest_uid'], $v['guest_mail']);
        $output_sale['response'] = $response;
        $customer_id = intval($response['cid']);

        if ($customer_id == 0) {
            $new_customer_info = array(
                'name' => trim($v['guest_name']),
                'pnum' => !empty($v['guest_number']) ? $v['guest_number'] : 1,
                'days' => !empty($v['guest_days']) ? $v['guest_days'] : 1,
                'email' => trim($v['guest_mail']),
                'mobile' => Util_Common::filter_phone($v['guest_telnum']),
                'status' => 1,
                'client_ip' => Util_NetWorkAddress::get_client_ip(),
                'create_time' => REQUEST_TIME,
                'last_modify_date' => REQUEST_TIME,
                'last_order_date' => REQUEST_TIME,
                'province' => $v['province'],
                'remark' => $remark,
                'pcnum' => !empty($v['guest_child_number']) ? $v['guest_child_number'] : 0,
                'pcage' => $v['guest_child_age'],
                'campaign_code' => isset($_COOKIE['campaign_code']) && !empty($_COOKIE['campaign_code']) ? $_COOKIE['campaign_code'] : '',
                'zzkcamp' => !empty($_COOKIE['zzkcamp']) ? $_COOKIE['zzkcamp'] : '',
                'zfansref' => !empty($_COOKIE['zfansref']) ? (int) $_COOKIE['zfansref'] : 0,
            );
            $last_customer_id = $userInfo->insert_new_customer_by_info($new_customer_info);
            $customer_id = $last_customer_id;
            if ($last_customer_id) {
                $sale_flag_data_up = array(
                    $output_sale['response']['group'],
                    $output_sale['response']['mid'],
                    $output_sale['response']['mid'],
                    $last_customer_id,
                );
                $userInfo->update_new_customer_by_info($sale_flag_data_up, true);
            }
        } else {
            $update_flag = array(
                REQUEST_TIME,
                $output_sale['response']['group'],
                $output_sale['response']['mid'],
                $output_sale['response']['mid'],
                $customer_id,
            );
            $userInfo->update_new_customer_by_info($update_flag, false);
        }

        $polaris->set_sale_cus_count($output_sale['response']['mid'], $output_sale['response']['group'], $dest_id);

        return $customer_id;
    }

    public function format_child_param($char)
    {
        $list = explode(";", $char);
        $data = array();
        foreach ($list as $row) {
            if (empty($row)) {
                continue;
            }

            $child = explode(",", $row);
            $data[] = array( //替换汉字方便翻译
                'age' => str_replace(array("年龄", "周岁"), "", $child[0]),
                'height' => str_replace(array("身高", "cm"), "", $child[1]),
            );
        }

        return $data;
    }

}
