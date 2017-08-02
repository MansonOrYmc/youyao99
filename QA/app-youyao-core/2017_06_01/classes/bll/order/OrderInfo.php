<?php

class Bll_Order_OrderInfo
{
    private static $order_cache;
    private $orderInfoDao;

    public function __construct()
    {
        $this->orderInfoDao = new Dao_Order_OrderInfo();
    }

    public function acquire_order_certify_by_uid($uid)
    {
        return $this->orderInfoDao->acquire_order_certify_by_uid($uid);
    }
    public function acquire_order_certification_by_uid($uid)
    {
        $orderinfos = $this->orderInfoDao->acquire_order_base_certify_by_uid($uid);
        foreach ($orderinfos as $k => $info) {
            $homestay_uid = $info['uid'];
            $homestay_img = Util_Image::get_homestay_images($homestay_uid);
            $homestay_img = $homestay_img[0];
            $orderinfos[$k]['homestay_img'] = $homestay_img;
            $userbll = new Bll_User_UserInfo();
            $userInfo = $userbll->get_whole_user_info($homestay_uid);

            $dao_stay = new Dao_HomeStay_Stay();
            $result = $dao_stay->get_stayinfo_by_id($homestay_uid);
            $poi = (object) $result;
            $orderinfos[$k]['location']['lon'] = (float) $poi->lon;
            $orderinfos[$k]['location']['lat'] = (float) $poi->lat;
            $orderinfos[$k]['address'] = $poi->address;
            $orderinfos[$k]['utelnum'] = $userInfo['tel_num'];
            $orderinfos[$k]['checkinfo'] = $info['guest_date'] . ' | ' . $info['guest_days'] .
                '晚' . ' | ' . $info['room_num'] . '间';
        }
        return $orderinfos;
    }

    public function insert_homestay_booking($info)
    {
        return $this->orderInfoDao->dao_insert_homestay_booking($info);
    }

    public function insert_homestay_booking_addtion($order_id, $addtion)
    {
        return $this->orderInfoDao->dao_insert_homestay_booking_addtion($order_id, $addtion);
    }

    public function insert_homestay_booing_service($order_id, $service) 
    {
        if(empty($order_id) || empty($service)) return;
        $data = array();
        // 这里整理是为了防止数据库顺序出错
        foreach($service as $k=>$v) {
            $data[] = array(
                'bid'               => $order_id,
                'package_id'        => $v['package_id'],
                'service_category'  => $v['service_category'],
                'num'               => $v['num'],
                'price'             => $v['price'],
                'price_cn'          => $v['price_cn'],
                'create_time'       => time(),
            );
        }
        return $this->orderInfoDao->insert_homestay_booing_service($order_id, $data);
    }

    public function get_homestay_booking_service($order_id) {
        return $this->orderInfoDao->get_homestay_booking_service($order_id);
    }

    public function insert_log_homestay_booking_trac($info)
    {
        return $this->orderInfoDao->dao_insert_log_homestay_booking_trac($info);
    }

    public function zzk_first_order_new($uid, $success = '')
    {
        $num = '';
        $bll_homestay_info = new Bll_Homestay_StayInfo();
        $home_detail = $bll_homestay_info->zzk_home_detail($uid);
        $order_succ = (int) $home_detail->order_succ ? (int) $home_detail->order_succ : 0;
        $rebate_num = (int) $home_detail->rebate_num ? (int) $home_detail->rebate_num : 0;
        if ($order_succ == 0 && $rebate_num == 0 && $rebate_remark == '') {
            $num = '第一笔订单';
        }
        return $num;
    }

    public function zzk_update_nodisc_price($order_id, $default_total_price_nodisc_tw, $default_total_price_nodisc_cn)
    {
        return $this->orderInfoDao->set_nodisc_price($order_id, $default_total_price_nodisc_tw, $default_total_price_nodisc_cn);
    }


    public function zzk_mark_order_disc($order_id ,$disc){

        return $this->orderInfoDao->zzk_mark_order_disc($order_id,$disc);

    }


    //判断待支付的订单
    public function pre_pay_orders_by_roomid($nid)
    {
        return $this->orderInfoDao->pre_pay_orders_by_roomid($nid);
    }

    public function refund_order($info)
    {
        #warning
        $client_ip = '127.0.0.1'; //ip_address();
        $create_time = time();
        $mktime = mktime(21, 0, 0);
        if ($mktime < $create_time) {
            $mktime = $mktime + 60 * 60 * 24;
        }
        $order_id = $info['order_id'];
        // $order_id =
        $orderInfo = $this->orderInfoDao->acquire_condition_order_by_oid($order_id);
        if ($orderInfo) {
            $d_value_day = (strtotime($orderInfo['guest_date'] . ' 21:00') - $mktime) / 60 / 60 / 24;
            if ($d_value_day <= 7) {
                $values['d_value_day'] = '七天以内';
            }
            $bid = $orderInfo['id'];
            $refundExist = $this->orderInfoDao->acquire_exist_refund_order_by_oid($bid);
            if ($refundExist) {
                return array('status' => 0, 'msg' => '已提交了退款申请');
            }

            $admin_uid = $this->orderInfoDao->acquire_customer_admin_uid_by_email($orderInfo['guest_mail']);
            $sale_name = $this->orderInfoDao->acquire_user_name_by_admin_uid($admin_uid);
            $values['uname'] = $orderInfo['uname'];
            $values['guest_mid'] = $bid;
            $values['guest_name'] = $orderInfo['guest_name'];

            $homestay_trac_update_date = $this->orderInfoDao->acquire_homestay_booking_trac_by_bid($bid);

            $refundInsertInfo = array($bid, $client_ip, $create_time, $info['uid'], $info['guest_name'], $info['guest_mail'], $info['guest_telnum'], $info['guest_content'] . '---来自iPhone客户端', $homestay_trac_update_date, $sale_name);
            // $this->orderInfoDao->insert_refund_by_info($refundInsertInfo);
            var_dump($refundInsertInfo);

            $homestayTracInfo = array($bid, '7', '提交了退订申请', $create_time, $info['uid'], $client_ip, '退订申请', $orderInfo['total_price_tw']);
            // $this->orderInfoDao->insert_homestay_trac_by_info($homestayTracInfo);
            var_dump($homestayTracInfo);

            // send_mail()
            return array('status' => 1, 'msg' => 'OK');

        } else {
            return array('status' => 0, 'msg' => '没有查找到订单信息');
        }
    }

    private function send_mail($create_time, $bid, $guest_name, $guest_telnum, $guest_mail, $guest_content, $date, $update_date, $guest_date, $sales_name, $values)
    {
        $date = date('Y-m-d H:i', $create_time);
        $from = 'noreply@youyao.com';
        $to = 'kding@youyao.com';
        $cc = 'kding@youyao.com';

        // $to = 'cancel@youyao.com';
        // $cc = 'dl-sales@youyao.com';

        $body[] = '<div>';
        $body[] = '<p>您好，自在客：</p>';
        $body[] = '<p style="text-indent:16px;">我想要申请退订,我的具体信息如下：</p>';
        $body[] = '<table style="">
           <tr style="background:#e4e4e4;">
             <th style="text-align:right;margin-right:8px;">姓名：</th><th style="text-align:left;margin-left:10px;">' . $guest_name . '</th>
           </tr>
           <tr style="background:#e4e4e4;">
             <th style="text-align:right;margin-right:8px;">我想要取消的订单：</th><th style="text-align:left;margin-left:10px;"><a href="http://optools.youyao.com/order/' . $bid . '/trac" >#' . $bid . '</a></th>
            </tr>
           <tr style="background:#efefef">
             <th style="text-align:right;margin-right:8px;">联系号码：</th><th style="text-align:left;margin-left:10px;">' . $guest_telnum . '</th>
           </tr>
           <tr style="background:#e4e4e4;">
             <th style="text-align:right;margin-right:8px;">联系邮箱：</th><th style="text-align:left;margin-left:10px;">' . $guest_mail . '</th>
           </tr>
           <tr style="background:#efefef">
             <th style="text-align:right;margin-right:8px;">退订内容及原因：</th><th style="width:350px;text-align:left;margin-left:10px;">' . $guest_content . '</th>
           </tr>';
        $body[] = '<tr style="background:#e4e4e4"><th style="text-align:right;margin-right:8px;">申请时间：</th><th style="text-align:left;margin-left:10px">' . $date . '</th></tr>
               <tr style="background:#efefef">
                  <th style="text-align:right;margin-right:8px;">成交时间：</th><th style="text-align:left;margin-left:10px">' . $update_date . '</th></tr>
               <tr style="background:#e4e4e4;">
                  <th style="text-align:right;margin-right:8px;">入住时间：</th><th style="text-align:left;margin-left:10px">' . $guest_date . '</th></tr>
               <tr style="background:#efefef">
                  <th style="text-align:right;margin-right:8px;">所属销售：</th><th style="text-align:left;margin-left:10px">' . $sales_name . '</th></tr>
              </table></div>';
        $params = array(
            'body' => $body,
            'headers' => array('Cc' => $cc),
            'values' => $values,
        );
    }

    public function get_order_byphone($phone)
    {
        return $this->orderInfoDao->get_order_byphone($phone);
    }

    public function get_order_byphone_without_filter($phone)
    {
        return $this->orderInfoDao->get_order_byphone_without_filter($phone);
    }

    public function zzk_order_status_mapping($dest_id = 10)
    {
        if ($dest_id == 10) {
            $a = array(
                '0' => '待處理',
                '1' => '處理中',
                '2' => '訂單成交',
                '3' => '訂單取消',
                '4' => '待付款',
                '5' => '訂單取消',
                '6' => '已匯款',
                '7' => '申請退款',
                '8' => '退款已確認',
                '9' => '需退款',
                '10' => '需退款', // 需要补汇
                '11' => '已退款',
                '12' => '退款取消',
            );
        } else {
            $bll_area = new Bll_Area_Area();
            $a = array(
                '0' => $bll_area->get_dest_language($dest_id, "Unread"),
                '1' => $bll_area->get_dest_language($dest_id, "Waiting to pay"),
                '2' => $bll_area->get_dest_language($dest_id, "Confirmed"),
                '3' => $bll_area->get_dest_language($dest_id, "cancel"),
                '4' => $bll_area->get_dest_language($dest_id, "Waiting to pay"),
                '5' => $bll_area->get_dest_language($dest_id, "cancel"),
                '6' => $bll_area->get_dest_language($dest_id, "Bank transferred"),
                '7' => $bll_area->get_dest_language($dest_id, "Confirmed"),
                '8' => $bll_area->get_dest_language($dest_id, "cancel"),
                '9' => $bll_area->get_dest_language($dest_id, "cancel"),
                '10' => $bll_area->get_dest_language($dest_id, "cancel"),
                '11' => $bll_area->get_dest_language($dest_id, "cancel"),
                '12' => $bll_area->get_dest_language($dest_id, "cancel"),
            );
        }

        return $a;
    }

    // add by tonycai
    public function zzk_save_order_trac_content($bid, $uid, $order_status_changed, $content, $status, $v = array())
    {
        $user = Util_Signin::get_user();

        $content_f = Util_Common::zzk_make_links_blank($content);

        if (empty($uid) || empty($bid)) {
            return array('status' => true);
        }

        if (empty($content) && empty($status)) {
            return array('status' => true);
        }

        $total_price = isset($v['total_price']) ? $v['total_price'] : 0;
        $total_price = (int) $total_price;
        $total_price = $total_price < 0 ? 0 : $total_price;
        if ($total_price == 0 && $status == 4) {
            return array('status' => false, 'action' => array('code' => 4, 'codeMsg' => '订单金额不能为0元， 跟进记录保存失败。', 'body' => array('goto_url' => '')));
        }
        $sl = strlen($order_status_changed . $content);
        if ($sl > 600 || $sl == 0) {
            return array('status' => false, 'action' => array('code' => 4, 'codeMsg' => '跟进内容不能为空或者内容超过60个字。', 'body' => array('goto_url' => '')));
        }

        if (isset($v['total_price_tw'])) {
            $price_tw = (int) $v['total_price_tw'];
        }

        $log_trac_info = array(
            $status,
            $order_status_changed . $content,
            $content,
            $price_tw,
            $uid,
            $bid,
            REQUEST_TIME,
            Util_NetWorkAddress::get_client_ip(),
        );

        $this->insert_log_homestay_booking_trac($log_trac_info);

        $n = $this->orderInfoDao->dao_count_log_homestay_booking_trac_by_bid($bid);

        $data = array();

        if (isset($v['closed_reasons'])) {
            $data['closed_reasons'] = (int) $v['closed_reasons'];
        }
        if (isset($v['total_price_tw'])) {
            $data['total_price_tw'] = (int) $v['total_price_tw'];
        }
        if (isset($v['trade_no'])) {
            $data['trade_no_post'] = $v['trade_no'];
        }
        if (isset($v['out_trade_no'])) {
            $data['out_trade_no'] = $v['out_trade_no'];
        }
        if (isset($v['room_status'])) {
            $data['room_status'] = (int) $v['room_status'];
        }
        if (isset($v['room_num'])) {
            $data['room_num'] = (int) $v['room_num'];
        }
        // andrew 2014.12.09
        if (isset($v['total_price'])) {
            $data['total_price'] = $total_price;
        }

        if (isset($v['payment_source'])) {
            $data['payment_source'] = $v['payment_source'];
        }

        if (isset($v['speed_room'])) {
            $data['speed_room'] = $v['speed_room'];
        }

        $data['trac_log_num'] = $n;
        $data['status'] = $status;
        $data['last_admin_uid'] = $uid;
        $data['last_modify_date'] = REQUEST_TIME;
        $data['last_intro'] = $order_status_changed . $content;
        if ($total_price > 0 && $status == 4) {
            $obid = Util_Common::shortUrl_new($bid);
            $obid = strtoupper($obid[0]);
            $data['url_code'] = $obid;
            Util_Common::zzk_activity_add($bid);
        }

        $data_set_info = array();
        $data_set_sql = "update t_homestay_booking set ";
        foreach ($data as $key => $value) {
            $data_set_sql .= $key . "=?,";
            $data_set_info[] = $value;
        }
        if (count($data) > 0) {
            $data_set_sql = substr($data_set_sql, 0, strlen($data_set_sql) - 1);
            $data_set_sql .= " where id=?";
            $data_set_info[] = $bid;
            $this->orderInfoDao->update_homestay_booking($data_set_sql, $data_set_info);
        }

        $bll_area_info = new Bll_Area_Area();

        // send mail
        if ($total_price > 0 && $status == 4) {
            $order = self::order_load($bid, true);
            $child_number = $order->guest_child_number ? '/儿童' . $order->guest_child_number . '人' : '';
            if ($order->guest_child_age) {
                $child_number .= '(' . $order->guest_child_age . ")";
            }

//            Util_ThemplateMail::contact_user_order_payment($order->guest_mail, array('order'=>$order, 'order_message'=>$content_f), 'noreply@youyao.com');
            // 待支付
            if ($uid != "1") { // 不是自动发的待支付
                $trac = self::get_log_homestay_booking_trac($order->id, 4); // 查看发过几次待支付
                $send_mail_params = array(
                    "order_id" => $order->hash_id,
                    "send" => true,
                );
                if (count($trac) < 2) { // 首次发待支付
                    $send_mail_params["action"] = "c_order_to_be_paid";
                } else {
                    $send_mail_params["action"] = "c_order_price_change";
                }
                Util_Common::async_curl_in_terminal(Util_Common::url("/m/send", "api"), $send_mail_params);
            }

            $rev_percent = $order->rev_percent ? $order->rev_percent : 100;
            if ($rev_percent == 100) {
                $other_yf_price = '';
            } else {
                //客人
                $dest_row = $bll_area_info->get_dest_config_by_destid($order->dest_id);
                $yu_total_price = Util_Common::zzk_pay_price_format($order->total_price * ($rev_percent / 100));
                $dao_total_price = Util_Common::zzk_pay_price_format($order->total_price_tw * ((100 - $rev_percent) / 100));
                $other_yf_price = '/预付' . $yu_total_price . '元(RMB)/现付' . $dao_total_price . '元(' . $dest_row['currency_ios_code'] . ')';
            }
            $dao_user_info = new Dao_User_UserInfo();
            $guest_user = $dao_user_info->load_user_info($order->guest_uid);
			$sms_content = Trans::t("sms_c_order_to_be_paid_%n_%s_%d_%p_%u_%c_%b", $guest_user->dest_id,
				array(
					"%n" => $order->uname,
					"%s" => date("n-j", strtotime($order->guest_date)),
					"%d" => date("n-j", strtotime($order->guest_checkout_date)),
					"%p" => '',
					"%u" => Util_Common::url('/user/payment/' . $order->url_code),
					"%c" => '',
					"%b" => $order->hash_id,
				)
			);
			$sms_content = Util_Common::zzk_msg_keyword($sms_content);
			$sms_nofity = array(
				'oid' => $order->id,
				'sid' => $order->uid,
				'uid' => isset($order->guest_uid) ? $order->guest_uid : 0,
				'mobile' => $order->guest_telnum,
				'content' => $sms_content,
				'area' => ($guest_user->dest_id == 12 ? 1 : 2),
			);
            if($uid != "1") { // 不是自动发待支付
                Util_Notify::send_sms_notify($sms_nofity);
            }

            if (isset($order->guest_mail) && !empty($sms_content)) {
                Util_Notify::send_mobile_notify($order->guest_mail, $sms_content);
                Util_Notify::push_message_client($order->guest_mail, $order->guest_telnum, '', $sms_content, Util_Notify::get_push_mtype('guest_order'), $order->hash_id);
            }
        }

        $dao_user_info = new Dao_User_UserInfo();
        $bll_user_info = new Bll_User_UserInfo();
        $bll_room_info = new Bll_Room_RoomInfo();
        $bll_homestay_info = new Bll_Homestay_StayInfo();
        $bll_stats = new Bll_Room_Status();

        //订单支付成功之后进入这个函数 add by vruan @2015-11-03
        if ($total_price > 0 && $status == 2) {
            $order = self::order_load($bid, true);

            $dao = new Dao_Activity_Activity();
            if ($dao->get_room_byoid($order->id)) {
                $dao->delete_room($order->nid, $order->id, 1); //将房间永久放空
            }

            $token = md5(time() + $order->id);
            $date_arr = range(strtotime($order->guest_date), strtotime($order->guest_checkout_date) - 24 * 60 * 60, 24 * 60 * 60);
            $date_arr = array_map(create_function('$date_v', 'return date("Y-m-d", $date_v);'), $date_arr);
            $bll_stats->set_multiple_days_logs($order->nid, $date_arr, $uid, Util_NetWorkAddress::get_client_ip(), 1, 1, $token, $order->id);

            $log_info = $this->orderInfoDao->pay_order_load($bid);
            $order->intro = $log_info['intro'] == "已经确定有房间，暂时为您保留，请尽快付款。" ? "" : $log_info['intro'];
            $provider = $dao_user_info->load_user_info($order->uid);

            //Util_ThemplateMailsecond::contact_user_order_succ($order->guest_mail, array('order'=>$order), 'noreply@youyao.com');
            //global $bnb_dest_id;
            //$bnb_dest_id = $order->dest_id;
            //Util_ThemplateMail::contact_provider_order_succ($provider->mail, array('order'=>$order), 'noreply@youyao.com');

            // 2b 成交
            $send_mail_params = array(
                "action" => "b_order_confirmed",
                "order_id" => $order->hash_id,
                "send" => true,
            );
            Util_Common::async_curl_in_terminal(Util_Common::url("/m/send", "api"), $send_mail_params);
            // 2c 成交
            $send_mail_params = array(
                "action" => "c_order_traded",
                "order_id" => $order->hash_id,
                "send" => true,
            );
            if($order->order_source == 'booking') { // booking 发英文邮件 价格显示日币
                $send_mail_params['multilang'] = 13;
                $send_mail_params['multiprice'] = 11;
            }
            Util_Common::async_curl_in_terminal(Util_Common::url("/m/send", "api"), $send_mail_params);

            //记录成交邮件日志
            self::zzk_log_homestay_booking_email($bid);

            $child_number = $order->guest_child_number ? '/儿童' . $order->guest_child_number . '人' : '';
            if ($order->guest_child_age) {
                $child_number .= '(' . $order->guest_child_age . ")";
            }

            $rev_percent = $order->rev_percent ? $order->rev_percent : 100;
            if ($rev_percent == 100) {
                $other_yf_price = '';
                $tw_other_yf_price = '';
            } else {
                //客人
                $dest_row = $bll_area_info->get_dest_config($order->dest_id);
                $yu_total_price = Util_Common::zzk_pay_price_format($order->total_price * ($rev_percent / 100));
                $dao_total_price = Util_Common::zzk_pay_price_format($order->total_price_tw * ((100 - $rev_percent) / 100));
                $other_yf_price = '/预付' . $yu_total_price . '元(RMB)/现付' . $dao_total_price . '元(' . $dest_row['currency_ios_code'] . ')';
                //民宿主人
                $tw_yu_total_price = round($order->total_price_tw * ($rev_percent / 100));
                $tw_dao_total_price = round($order->total_price_tw * ((100 - $rev_percent) / 100));
                $tw_other_yf_price = ',現付:' . $tw_dao_total_price . '元(' . $dest_row['currency_ios_code'] . ')';
            }

            $open_info = Alitrip_Order::get_open_info($order->hash_id);
            if (!empty($open_info)) {
                $sms_content = "【自在客】订单(#" . $order->hash_id . ")付款成功了。民宿：" . $order->uname . "/" . $order->room_name . "/" . $provider->address . "/电话" . $provider->tel_num . ", 姓名：" . $order->guest_name . "/成人" . $order->guest_number . '人' . $child_number . "/入住" . substr($order->guest_date, 5, 10) . "/退房" . substr($order->guest_checkout_date, 5, 10) . ", 总房费:" .
                    ($open_info[0]['price'] / 100)
                    . "元(RMB)" . $other_yf_price . "。祝您旅行快乐！";
            } else {
                $sms_content = "【自在客】订单(#" . $order->hash_id . ")付款成功了。民宿：" . $order->uname . "/" . $order->room_name . "/" . $provider->address . "/电话" . $provider->tel_num . ", 姓名：" . $order->guest_name . "/成人" . $order->guest_number . '人' . $child_number . "/入住" . substr($order->guest_date, 5, 10) . "/退房" . substr($order->guest_checkout_date, 5, 10) . ", 总房费:" .
                $order->total_price
                    . "元(RMB)" . $other_yf_price . "。祝您旅行快乐！";
            }
            $dao_user_info = new Dao_User_UserInfo();
            $guest_user = $dao_user_info->load_user_info($order->guest_uid);
            $homestay_dao = new Dao_Homestay_StayMemcache();
            $check_time = $homestay_dao->get_checkin_time($order->uid);
            $checktime_key = "";
            if($check_time['checkin_at'] && $check_time['checkin_stop']) {
                $checktime_key = "checktime_between_%s_%d";
            } elseif($check_time['checkin_at']) {
                $checktime_key = "checktime_after_%s";
            }
            $trans_param = array(
                    "%n" => $order->uname,
                    "%s" => date("n-j", strtotime($order->guest_date)),
                    "%d" => date("n-j", strtotime($order->guest_checkout_date)),
                    "%p" => '',
                    "%u" => '',
                    "%c" => '',
                    "%b" => $order->hash_id,
                );
            if($checktime_key) {
                $trans_param['%t'] = Trans::t($checktime_key, $guest_user->dest_id, array(
                        "%s" => $check_time['checkin_at'],
                        "%d" => $check_time['checkin_stop'],
                    ));
            }
            $sms_content = Trans::t("sms_c_order_traded_%n_%s_%d_%p_%u_%c_%b_%c_%t", $guest_user->dest_id, $trans_param);
            $sms_content = Util_Common::zzk_msg_keyword($sms_content);
            $sms_nofity = array(
                'oid' => $order->id,
                'sid' => $order->uid,
                'uid' => isset($order->guest_uid) ? $order->guest_uid : 0,
                'mobile' => $order->guest_telnum,
                'content' => $sms_content,
                'area' => ($guest_user->dest_id == 12 ? 1 : 2),
            );
            Util_Notify::send_sms_notify($sms_nofity);
            if (isset($order->guest_mail)) {
                Util_Notify::send_mobile_notify($order->guest_mail, $sms_content);
                Util_Notify::push_message_client($order->guest_mail, $order->guest_telnum, '', $sms_content, Util_Notify::get_push_mtype('guest_order'), $order->hash_id);
            }
            $child_number = $order->guest_child_number ? '/兒童' . $order->guest_child_number . ' 人' : '';
            if ($order->dest_id == 10) { //日本
                $sms_content = "【自在客】訂單(" . $order->hash_id . ")已成交, 請您務必保留房間！客人：" . Util_Common::zzk_translate($order->guest_name, 'zh-tw') . "/成人" . $order->guest_number . '人' . $child_number . "/" . substr($order->guest_date, 5, 10) . "入住/" . substr($order->guest_checkout_date, 5, 10) . "退房/" . Util_Common::zzk_translate($order->room_name, 'zh-tw') . "" . $tw_other_yf_price . ' 詳見網站';
            } elseif ($order->dest_id == 12) { // 大陆
                $sms_content = "【自在客】订单(" . $order->hash_id . ")已成交, 请您务必保留房间！客人：" . $order->guest_name . "/成人" . $order->guest_number . '人' . $child_number . "/" . substr($order->guest_date, 5, 10) . "入住/" . substr($order->guest_checkout_date, 5, 10) . "退房/" . $order->room_name . "" . $tw_other_yf_price . ' 详见网站';
            } else { //其它
                $sms_content = "【自在客】訂單(" . $order->hash_id . ")已成交, 請您務必保留房間！客人：" . Util_Common::zzk_translate($order->guest_name, 'zh-tw') . "/成人" . $order->guest_number . '人' . $child_number . "/" . substr($order->guest_date, 5, 10) . "入住/" . substr($order->guest_checkout_date, 5, 10) . "退房/" . Util_Common::zzk_translate($order->room_name, 'zh-tw') . "" . $tw_other_yf_price . ' 詳見網站';
            }
            $sms_nofity = array(
                'oid' => $order->id,
                'sid' => $order->uid,
                'uid' => isset($order->guest_uid) ? $order->guest_uid : 0,
                'mobile' => $provider->send_sms_telnum,
                'content' => $sms_content,
                'dest_id' => $order->dest_id,
                'area' => 2,
            );
            Util_Notify::send_sms_notify($sms_nofity);
            if (isset($provider->mail)) {
                Util_Notify::send_mobile_notify($provider->mail, $sms_content);
                Util_Notify::push_message_client($provider->mail, $provider->send_sms_telnum, '', $sms_content, Util_Notify::get_push_mtype('admin_order'), $order->hash_id);
            }

            if ($order->uid < 30000000) {
                $bll_user_info->update_user_order_succ_by_uid($order->uid, $order->guest_days * $order->room_num);
                if ($order->nid > 0) {
                    $bll_room_info->update_user_order_succ_by_nid($order->nid, $order->guest_days * $order->room_num);
                }
            }

            //更新房态
            //先判断改订单之前是否有过成交，只算一次成交
            $succ_order_count = $bll_homestay_info->get_homestay_booking_count_by_bid($order->id);
            if ($succ_order_count == 1) { //一个订单只算一次
                for ($i = 0; $i < $order->guest_days; $i++) {
                    $dd = date('Y-m-d', strtotime($order->guest_date) + $i * 60 * 60 * 24);
                    $stock_result = $bll_room_info->get_stock_num_by_nid_and_date($order->nid, $dd);
                    if ($stock_result['stock_field'] == 'room_num') {
                        $room_num = $stock_result['stock_num'];
                        $new_room_num = $room_num > $order->room_num ? ($room_num - $order->room_num) : 0;
                        $trac_info = array(
                            'room_id' => $order->nid,
                            'room_date' => $dd,
                            'room_num' => $new_room_num,
                            'create_date' => time(),
                            'update_date' => time(),
                            'log_booking' => '[' . date('Y-m-d H:i:s', time()) . ']订单成交，房态由 ' . $room_num . ' 间变更为 ' . $new_room_num . ' 间',
                        );
                        $bll_room_info->insert_room_tracs($trac_info, $stock_result['stock_field']);
                    } elseif ($stock_result['stock_field'] == 'beds_num') {
                        $beds_num = $stock_result['stock_num'];
                        $new_beds_num = $beds_num > ($order->guest_number + $order->guest_child_number) ? ($beds_num - ($order->guest_number + $order->guest_child_number)) : 0;
                        $trac_info = array(
                            'room_id' => $order->nid,
                            'room_date' => $dd,
                            'beds_num' => $new_beds_num,
                            'create_date' => time(),
                            'update_date' => time(),
                            'log_booking' => '[' . date('Y-m-d H:i:s', time()) . ']订单成交，房态由 ' . $beds_num . ' 张床变更为 ' . $new_beds_num . ' 张床',
                        );
                        $bll_room_info->insert_room_tracs($trac_info, $stock_result['stock_field']);
                    } else {
                        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($stock_result, true));
                    }
                }
            }
            $bll_stats->set_multiple_days_logs($order->nid, $date_arr, $uid, Util_NetWorkAddress::get_client_ip(), 2, 1, $token, $order->id);

            $p = array(
                'oid' => $order->id,
                'sid' => $order->uid,
                'uid' => isset($order->guest_uid) ? $order->guest_uid : 0,
                'paypal_account' => $provider->poi->paypal_account,
                'rebate_num' => $provider->poi->rebate_num,
                'rev_percent' => $order->rev_percent,
                'customer_level' => $provider->poi->customer_level,
                'total_price_cn' => $order->total_price,
                'total_price_tw' => $order->total_price_tw,
                'uname' => $order->uname,
                'dest_id' => $order->dest_id,
            );
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($p, true));
            Util_Common::add_paypal_queue($p);

            if (!empty($order->coupon)) {
                if ($order->coupon != 'fwmcb31' && $order->coupon != 'FWMCB31' && $order->coupon != '9rmqges' && $order->coupon != '9RMQGES') { //做温泉活动，指定的代金券在活动期内有效 axing
                    $bll_conpon_info = new Bll_Coupons_CouponsInfo();
                    $bll_conpon_info->update_conpon($order->id, $order->coupon);
                }
            }

            //处理分享优惠
            $bll_cou = new Bll_Coupons_CouponsInfo();
            $bll_cou->use_conpon($order->id);

            //判断是否有超卖的订单
            for ($i = 0; $i < $order->guest_days; $i++) {
                $dd = date('Y-m-d', strtotime($order->guest_date) + $i * 60 * 60 * 24);
                $old_row = $bll_homestay_info->get_homestay_booking($order->id, $order->nid, $dd, $dd);
                $room_status = $bll_room_info->node_room_trac_status_new($order->id, $dd);
                if ($room_status == 0 && !empty($old_row)) {
                    $bll_homestay_info->update_homestay_booking_out_order_by_id(1, $order->id);
                }
            }

            //计算应该汇款
            $price_tw_pay = ceil($order->total_price_tw * ($rev_percent - $provider->poi->rebate_num) / 100);
            $result = $bll_homestay_info->update_homestay_booking_by_id($price_tw_pay, $v['payment_type'], $bid);
        }

        if ($status == 3) {
            $order = self::order_load($bid, true);
            if ($order->status == 3) {
                //Util_ThemplateMail::contact_user_order_closed($order->guest_mail, array('order'=>$order, 'order_message'=>$content_f), 'noreply@youyao.com');
                $open_info = Alitrip_Order::get_open_info($order->hash_id);
                if (!empty($open_info)) {
                    $sql = "update LKYou.t_order_open set status = '0' where order_id = '{$order->hash_id}'";
                    $stmt = APF_DB_Factory::get_instance()->get_pdo("lkyslave")->prepare($sql);
                    $stmt->execute();
                }
                $provider = $dao_user_info->load_user_info($order->uid);
                global $bnb_dest_id;
                $bnb_dest_id = $order->dest_id;

                //Util_ThemplateMail::contact_provider_order_closed($provider->mail, array('order'=>$order, 'order_message'=>$content_f), 'noreply@youyao.com');

                //更新房态
                //先判断改订单之前是否有过成交，只算一次成交
                $succ_order_count = $bll_homestay_info->get_homestay_booking_count_by_bid($order->id);
                if ($succ_order_count == 1) { //一个订单只算一次
                    $bll_stats->set_close_room($order->nid, $order->guest_date, $order->guest_checkout_date, $user->uid, Util_NetWorkAddress::get_client_ip());
                    for ($i = 0; $i < $order->guest_days; $i++) {
                        $dd = date('Y-m-d', strtotime($order->guest_date) + $i * 60 * 60 * 24);
                        $status_num = 0;
                        $status_num = $bll_room_info->get_stock_num_by_nid_and_date($order->nid, $dd);
                        $status_num = $status_num['stock_num'] + $order->room_num;
                        $bll_room_info->update_room_num_by_nid_and_date($status_num, $order->nid, $dd);
                    }
                    $bll_stats->set_close_room($order->nid, $order->guest_date, $order->guest_checkout_date, $user->uid, Util_NetWorkAddress::get_client_ip());
                }

//                //取消原因
                //                require_once dirname(__FILE__).'/../../includes/unicode.inc';
                //                $order_clost_info = $content_f?$content_f:'您寻问的房间已满';
                //                $sms_content = "【自在客】您的".truncate_utf8($order->uname,5)."订单#".$order->hash_id."已取消(".truncate_utf8($order_clost_info,18)."...),详情请登录youyao.com";
                //                if($user->uid != $order->guest_uid ){  //不是自己取消的情况
                //                    $sms_content = Util_Common::zzk_msg_keyword($sms_content);
                //                    Util_Notify::send_sms_notify(array(
                //                        'oid'=> $order->id,
                //                        'sid'=> $order->uid,
                //                        'uid'=> isset($order->guest_uid)?$order->guest_uid:0,
                //                        'mobile'=> $order->guest_telnum,
                //                        'content'=> $sms_content,
                //                        'area'=> 1,
                //                    ));
                //                }
            }
        }
        if ($status == 5) {
            $order = self::order_load($bid, true);
            if ($order->status == 5) {
                $open_info = Alitrip_Order::get_open_info($order->hash_id);
                if (!empty($open_info)) {
                    $sql = "update LKYou.t_order_open set status = '0' where order_id = '{$order->hash_id}'";
                    $stmt = APF_DB_Factory::get_instance()->get_pdo("lkyslave")->prepare($sql);
                    $stmt->execute();
                }
                $send_mail_params = array(
                    "action" => "c_order_room_not_null", // 运营人员、民宿取消
                    "order_id" => $order->hash_id,
                    "send" => true,
                );
                if($order->order_source == 'booking') { // booking 发英文邮件 价格显示日币
                    $send_mail_params['multilang'] = 13;
                    $send_mail_params['multiprice'] = 11;
                }
                if ($uid == $order->guest_uid) {
                    $send_mail_params["action"] = "c_order_canceled"; // 2c 主动取消
                } else if ($uid == 1 && $order->speed_room == 1) {
                    $send_mail_params["action"] = "c_order_speed_pay_timeout"; // 2c 速订失效
                } else if ($uid == 1 && $order->speed_room == 0) {
                    $send_mail_params["action"] = "c_order_pay_timeout"; // 2c 普通订单失效
                }
                Util_Common::async_curl_in_terminal(Util_Common::url("/m/send", "api"), $send_mail_params);
//                Util_ThemplateMail::contact_user_order_closed($order->guest_mail, array('order'=>$order, 'order_message'=>$content_f), 'noreply@youyao.com');

                $provider = $dao_user_info->load_user_info($order->uid);
//                global $bnb_dest_id;
                //                $bnb_dest_id = $order->dest_id;
                //
                //                Util_ThemplateMail::contact_provider_order_closed($provider->mail, array('order'=>$order, 'order_message'=>$content_f), 'noreply@youyao.com');
                // 2b 取消
                $open_uid = APF::get_instance()->get_config('uid','alitrip');
                if (!$order->speed_room || $uid == $open_uid) {
                    $send_mail_params = array(
                        "action" => "b_order_cancel",
                        "order_id" => $order->hash_id,
                        "send" => true,
                    );
                    if ($order->guest_uid == $uid) {
                        $send_mail_params["cancel_reason"] = $content_f;
                    }
                    Util_Common::async_curl_in_terminal(Util_Common::url("/m/send", "api"), $send_mail_params);
                }

                $dao_user_info = new Dao_User_UserInfo();
                $guest_user = $dao_user_info->load_user_info($order->guest_uid);
                $hs = $dao_user_info->load_user_info($order->uid);
                $order_clost_info = $content_f ? $content_f : '您寻问的房间已满';
                if ($uid != $order->guest_uid) { //不是自己取消的情况
                    $sms_content = Trans::t("sms_c_order_room_not_null_%n_%s_%d_%p_%u_%c_%b", $guest_user->dest_id, array(
                        "%n" => $order->uname,
                        "%s" => date("n-j", strtotime($order->guest_date)),
                        "%d" => date("n-j", strtotime($order->guest_checkout_date)),
                        "%p" => Util_Common::zzk_price_convert($order->total_price, $order->dest_id, $guest_user->dest_id),
                        "%u" => "",
                        "%c" => $order_clost_info,
                        "%b" => $order->hash_id,
                    ));
                    Util_Notify::send_sms_notify(array(
                        'oid' => $order->id,
                        'sid' => $order->uid,
                        'uid' => $order->guest_uid,
                        'mobile' => $order->guest_telnum,
                        'content' => $sms_content,
                        'area' => ($guest_user->dest_id == 10 ? 2 : 1),
                    ));
                }
                if ($hs->send_sms_telnum && !$order->speed_room) {
                    require_once dirname(__FILE__) . '/../../includes/unicode.inc';
                    $hs_sms_content = "【自在客】客人" . $order->guest_name . "的订单#" . $order->hash_id . "已取消（" . ($content_f ? truncate_utf8($content_f, 18, false, true) : '其他原因') . ")";
                    Util_Notify::send_sms_notify(array(
                        'oid' => $order->id,
                        'sid' => $order->uid,
                        'uid' => $order->guest_uid,
                        'mobile' => $hs->send_sms_telnum,
                        'content' => $hs_sms_content,
                        'area' => 2,
                    ));
                }
                //取消原因
                //require_once dirname(__FILE__).'/../../includes/unicode.inc';
                //$order_clost_info = $content_f?$content_f:'您寻问的房间已满';
                //$sms_content = "【自在客】您的".truncate_utf8($order->uname,5)."订单#".$order->hash_id."已取消(".truncate_utf8($order_clost_info,18)."...),详情请登录youyao.com";
                //if($user->uid != $order->guest_uid ){  //不是自己取消的情况
                //    $sms_content = Util_Common::zzk_msg_keyword($sms_content);
                //    Util_Notify::send_sms_notify(array(
                //        'oid'=> $order->id,
                //        'sid'=> $order->uid,
                //        'uid'=> isset($order->guest_uid)?$order->guest_uid:0,
                //        'mobile'=> $order->guest_telnum,
                //        'content'=> $sms_content,
                //        'area'=> 1,
                //    ));
                //}
                //更新房态
                //先判断改订单之前是否有过成交，只算一次成交
                $succ_order_count = $bll_homestay_info->get_homestay_booking_count_by_bid($order->id);
                if ($succ_order_count == 1) { //一个订单只算一次
                    $bll_stats->set_close_room($order->nid, $order->guest_date, $order->guest_checkout_date, $user->uid, Util_NetWorkAddress::get_client_ip());
                    for ($i = 0; $i < $order->guest_days; $i++) {
                        $dd = date('Y-m-d', strtotime($order->guest_date) + $i * 60 * 60 * 24);
                        $status_num = 0;
                        $status_num = $bll_room_info->get_stock_num_by_nid_and_date($order->nid, $dd);
                        $status_num = $status_num['stock_num'] + $order->room_num;
                        $bll_room_info->update_room_num_by_nid_and_date($status_num, $order->nid, $dd);
                    }
                    $bll_stats->set_close_room($order->nid, $order->guest_date, $order->guest_checkout_date, $user->uid, Util_NetWorkAddress::get_client_ip());
                }

            }
        }

        if ($status == 6) {
            $order = self::order_load($bid, true);
            $provider = $dao_user_info->load_user_info($order->uid);
            if (isset($v['no_mail']) && $v['no_mail'] == 1) {
            } else {
                Util_ThemplateMail::contact_provider_payment_succ($provider->mail, array('order' => $order, 'order_message' => $content), 'noreply@youyao.com');
            }
        }

        if ($order->nid) { // 通过命令行异步推送房价房态
            Util_Common::async_curl_in_terminal(Util_Common::url("/homestay/docking", 'api'), array('rid' => json_encode($order->nid)));
        }

        return array('status' => true);
    }

    public function zzk_log_homestay_booking_email($oid)
    {

        $params['order'] = self::order_load($oid);

        $dao_user_info = new Dao_User_UserInfo();
        $acc = $dao_user_info->load_user_info($params['order']->uid);
        $acc->mail = preg_replace('/\.zzk\.group\.[a-zA-Z0-9]+/', '', $acc->mail);

        //支付说明
        $params['order']->intro = str_replace('已经确定有房间，暂时为您保留，请尽快付款。', '', $params['order']->intro);
        $params['order']->intro = str_replace('凭入住凭证入住', '', $params['order']->intro);

        if ($params['order']->intro) {
            $message_intro = '<li style="list-style:none;font-size:14px;"><strong>支付说明：</strong>' . $params['order']->intro . '</li>';
        }
        $child_number = $params['order']->guest_child_number ? '&nbsp;&nbsp;+ 儿童：' . $params['order']->guest_child_number . ' 人' : '';
        if ($params['order']->guest_child_age) {
            $child_number .= '(' . $params['order']->guest_child_age . ")";
        }
        $check_in_out = '';
        $check_in_out .= $acc->checkin_at ? '当天下午 ' . $acc->checkin_at . ' 入住' : '当天下午 15:00 入住';
        $check_in_out .= $acc->checkin_stop ? '，当天下午 ' . $acc->checkin_stop . ' 入住截止' : '，当天下午 20:00 入住截止';
        $check_in_out .= $acc->checkout_at ? '，第二天上午 ' . $acc->checkout_at . ' 退房' : '，第二天上午 11:00 退房';
        $rev_percent = $params['order']->rev_percent ? $params['order']->rev_percent : 100;
        if ($rev_percent == 100) {
            $other_yf_price = '';
        } else {
            //民宿主人
            $yu_total_price = Util_Common::zzk_pay_price_format($params['order']->total_price * ($rev_percent / 100));
            $dao_total_price = Util_Common::zzk_pay_price_format($params['order']->total_price_tw * ((100 - $rev_percent) / 100));
            $other_yf_price .= '<li style="list-style:none;font-size:14px;"><strong>预付费用：</strong><font color="#ff6602">' . $yu_total_price . ' 元(RMB)</font>，在自在客完成付款</li>';
            $other_yf_price .= '<li style="list-style:none;font-size:14px;"><strong>现付费用：</strong><font>' . $dao_total_price . ' 元(TWD)</font>，入住民宿时才付款，请准备好现金</li>';
        }

        $message_subject = $params['order']->uname . ' 给您的订单成交确认邮件';
        $message_body = '
<div style="font-size:26px; text-align:center; color: #0E74B2; font-weight:bold;">自在客民宿入住凭证</div>
      <div style="font-weight:bold; font-size:18px; color:#0E74B2;padding:10px 0px;">订单编号：#' . $params['order']->id . '</div>
      <div style="padding:10px 0px;clear:both;">
         <div style="line-height:25px;">
            <li style="list-style:none;font-size:16px;margin-bottom:5px;border-bottom:#efefef 1px solid;"><strong>民宿信息：</strong></li>
            <li style="list-style:none;font-size:14px;"><strong>入住民宿：</strong>' . $acc->name . '</li>
            <li style="list-style:none;font-size:14px;"><strong>入住房间：</strong>' . $params['order']->room_name . '</li>
            <li style="list-style:none;font-size:14px;"><strong>联系电话：</strong>' . $acc->tel_num . ' , ' . $acc->send_sms_telnum . '</li>
            <li style="list-style:none;font-size:14px;"><strong>联系邮箱：</strong>' . $acc->mail . '</li>
            <li style="list-style:none;font-size:14px;"><strong>民宿地址：</strong>' . $acc->address . '</li>
            <li style="list-style:none;font-size:14px;"><strong>登记时间：</strong>' . $check_in_out . '（请提前一天联系民宿主人，告知入住时间，谢谢！）</li>
            <li style="list-style:none;font-size:14px;"><strong>登记时间：</strong>' . $check_in_out . '（请提前一天联系民宿主人，告知入住时间，谢谢！）</li>
         </div>
         <div style="line-height:25px;margin-top:30px;">
            <li style="list-style:none;font-size:16px;margin-bottom:5px;border-bottom:#efefef 1px solid;"><strong>订单信息：</strong></li>
            <li style="list-style:none;font-size:14px;"><strong>入住客人：</strong>' . $params['order']->guest_name . '</li>
            <li style="list-style:none;font-size:14px;"><strong>预定人电话：</strong>' . $params['order']->guest_telnum . '</li>
            <li style="list-style:none;font-size:14px;"><strong>预定时间：</strong>' . date('Y-m-d', $params['order']->last_modify_date) . '</li>
            <li style="list-style:none;font-size:14px;"><strong>房间数量：</strong>' . $params['order']->room_num . '间</li>
            <li style="list-style:none;font-size:14px;"><strong>入住人数：</strong>成人：' . $params['order']->guest_number . '人' . $child_number . '</li>
            <li style="list-style:none;font-size:14px;"><strong>入住天数：</strong>' . $params['order']->guest_days . '天</li>
            <li style="list-style:none;font-size:14px;"><strong>入住日期：</strong>' . Util_Common::zzk_date_format($params['order']->guest_date) . '</li>
            <li style="list-style:none;font-size:14px;"><strong>退房日期：</strong>' . Util_Common::zzk_date_format($params['order']->guest_checkout_date) . '</li>
            ' . $message_beizhu . '
            ' . $message_intro . '
            <li style="list-style:none;font-size:14px;"><strong>总 &nbsp;房&nbsp; 费：</strong><font>' . $params['order']->total_price . '元（RMB）</font></li>
            ' . $other_yf_price . '
         </div>
      </div>
      <div style="clear:both;">&nbsp;</div>
<div><p style="text-indent:0;"><strong>惊喜:</strong>发邮件到<a target="_blank" href="mailto:contact@youyao.com">contact@youyao.com</a>报名申请,赢取由e-go台湾租车赞助的<strong>免费</strong>‘花莲-垦丁线’[边走边玩]名额。每日仅一个名额<a target="_blank" href="http://wiki.youyao.com/index.php/%E8%BE%B9%E8%B5%B0%E8%BE%B9%E7%8E%A9_%E8%8A%B1%E8%8E%B2-%E5%9E%A6%E4%B8%81%E8%A7%82%E5%85%89%E5%B7%B4%E5%A3%AB%E4%B8%80%E6%97%A5%E7%B2%BE%E5%8D%8E%E6%B8%B8%EF%BC%81">(详情)</a></p></div>
      <div style=" clear:both;background-color:#f6f6f6;  padding:10px;margin-top:20px;font-size:12px;line-height:22px;";>
        <srong style="font-size:14px;background:url(http://wiki.youyao.com/images/5/50/I4.png) no-repeat  0px;padding-left:17px;">温馨提示：</strong><br>
        1、入台后若换手机号，请主动联系民宿。<br>
        2、台湾法律规定—屋顶底下请勿吸烟。<br>
        3、如条件允许,可给房东准备一份小礼物,或是在离开的时候写一张小纸条表达谢意。<br>
        欢迎下载《自在客》手机应用，让您可以在手机上随时随地预订民宿，联系民宿主人，查看您的订单凭证。<br>
        下载地址：<a href="http://www.youyao.com/v2/smart_phone">http://www.youyao.com/v2/smart_phone</a><br />
        如果您需要机场接送，或环岛包车。请联系<a href="http://order.e-go.com.tw/index/cn/order/airport/edit?lang=zh_CN" target="_blank">e-go</a><br />
        请打印此邮件，作为入住凭证。您还可以直接联系民宿主人，了解游玩的相关信息以及注意事项等等。

      </div>
';
        //发给民宿主人
        if ($rev_percent != 100) {
            $sub_title = '，現付';
        }
        $message_subject_homestay = $params['order']->guest_name . ' 的訂單成交確認郵件(#' . $params['order']->id . ')' . $sub_title;
        $child_number_homestay = $params['order']->guest_child_number ? '&nbsp;&nbsp;+ 儿童：' . $params['order']->guest_child_number . ' 人' : '';
        if ($params['order']->guest_child_age) {
            $child_number_homestay .= '(' . $params['order']->guest_child_age . ")";
        }
        if ($rev_percent == 100) {
            $tw_other_yf_price_homestay = '';
        } else {
            $tw_yu_total_price_homestay = Util_Common::zzk_pay_price_format($params['order']->total_price_tw * ($rev_percent / 100));
            $tw_dao_total_price_homestay = Util_Common::zzk_pay_price_format($params['order']->total_price_tw * ((100 - $rev_percent) / 100));
            $tw_other_yf_price_homestay .= '<li style="list-style:none;font-size:14px;"><strong>總 房 費：</strong>' . $params['order']->total_price_tw . ' 元(TWD)</li>';
            $tw_other_yf_price_homestay .= '<li style="list-style:none;font-size:14px;"><strong>預付費用：</strong><font color="#ff6602">' . $tw_yu_total_price_homestay . ' 元(TWD)</font>，在自在>客完成付款</li>';
            $tw_other_yf_price_homestay .= '<li style="list-style:none;font-size:14px;"><strong>現付費用：</strong><font>' . $tw_dao_total_price_homestay . ' 元(TWD)</font>，入住民宿时才付款</li>';
        }
        $message_body_homestay = '
      <div style="font-size:26px; text-align:center; color: #0E74B2; font-weight:bold;">自在客訂單成交確認郵件</div>
      <div style="font-size:14px; padding:10px;color:#333;margin-top:10px;">您好，' . $params['order']->uname . '</div>
      <div style="padding:10px;clear:both;border:#efefef 1px solid; margin-top:10px;">
         <div style="color:#333;margin-top:5px;font-size:14px;line-height:20px;">恭喜，客人(' . $params['order']->guest_name . ')已經支付房款給自在客。<div style="font-size:14px;background:url(http://wiki.youyao.com/images/5/50/I4.png) no-repeat -2px 0px;padding-left:17px;padding-bottom:10px;margin-top:15px;">請您務必保留房間，自在客會在約定时间內給您轉賬。</div>如果有其它問題，
请聯系自在客客服人員contact@youyao.com。</div>
      </div>
      <div style="padding:10px;clear:both;border:#efefef 1px solid; margin-top:10px;line-height:25px;">
            <li style="list-style:none;font-size:16px;margin-bottom:10px;color:#0E74B2;">以下是客人的入住資料</li>
            <li style="list-style:none;font-size:14px;"><strong>訂單編號：</strong>#' . $params['order']->id . '</li>
            <li style="list-style:none;font-size:14px;"><strong>客人姓名：</strong>' . $params['order']->guest_name . '</li>
            <li style="list-style:none;font-size:14px;"><strong>入住房間：</strong>' . $params['order']->room_name . '</li>
            <li style="list-style:none;font-size:14px;"><strong>入住人數：</strong>成人：' . $params['order']->guest_number . '人' . $child_number_homestay . '</li>
            <li style="list-style:none;font-size:14px;"><strong>房間數量：</strong>' . $params['order']->room_num . '間</li>
            <li style="list-style:none;font-size:14px;"><strong>入住日期：</strong>' . Util_Common::zzk_date_format($params['order']->guest_date) . '</li>
            <li style="list-style:none;font-size:14px;"><strong>退房日期：</strong>' . Util_Common::zzk_date_format($params['order']->guest_checkout_date) . '</li>
            <li style="list-style:none;font-size:14px;"><strong>入住天数：</strong>' . $params['order']->guest_days . '天</li>
            <li style="list-style:none;font-size:14px;"><strong>郵箱地址：</strong>' . $params['order']->guest_mail . '</li>
            <li style="list-style:none;font-size:14px;"><strong>联系电话：</strong>' . $params['order']->guest_telnum . '</li>
            <li style="list-style:none;font-size:14px;"><strong>备注留言：</strong>' . $params['order']->guest_etc . '</li>
            ' . $tw_other_yf_price_homestay . '
      </div>
      <div style="clear:both;">&nbsp;</div>
      <div style=" clear:both;background-color:#f6f6f6;  padding:10px;margin-top:40px;font-size:12px;line-height:22px;";>
        <srong style="font-size:14px;background:url(http://wiki.youyao.com/images/5/50/I4.png) no-repeat -2px 0px;padding-left:17px;">温馨提示：</strong><br>
        自在客爲您提供《台灣民宿》手機應用，讓您可以在手機上隨時隨地處理訂單。<br>
        手機應用下載地址：<a href="http://pages.youyao.com/smart_phone" target="_blank">http://pages.youyao.com/smart_phone</a><br />
      </div>
    ';

        $bll_homestay_info = new Bll_Homestay_StayInfo();

        //发给客人的
        $guest_mail_info = array(
            $oid,
            $message_subject,
            $message_body,
            1,
            REQUEST_TIME,
        );
        $bll_homestay_info->log_homestay_booking_email($guest_mail_info);

        //发给民宿主人的
        $homestay_mail_info = array(
            $oid,
            $message_subject_homestay,
            $message_body_homestay,
            2,
            REQUEST_TIME,
        );
        $bll_homestay_info->log_homestay_booking_email($homestay_mail_info);
    }

    public function order_load($rid, $reset = false)
    {
        if ($reset || self::$order_cache == null || !in_array($rid, array(
            self::$order_cache->id,
            self::$order_cache->hash_id,
            self::$order_cache->url_code,
        ))
        ) {
            $order = array();
            //axing 20140402
            if (preg_match('/\d{10}/', $rid)) {
                $results = $this->orderInfoDao->get_homestay_booking_by_hash_id($rid);
            } elseif (strlen($rid) == 8 || strlen($rid) == 10) {
                $results = $this->orderInfoDao->get_homestay_booking_by_urlcode($rid);
            } elseif (is_numeric($rid)) {
                $results = $this->orderInfoDao->get_homestay_booking_by_id($rid);
            } else {
                return false;
            }
            if (isset($results[0])) {
                $order = (object) $results[0];
                $bll_user_info = new Bll_User_UserInfo();
                if (empty($order->guest_uid)) {
                    $guest_info = $bll_user_info->get_user_info_by_email($order->guest_mail);
                    $order->guest_uid = $guest_info['uid'];

                }
            }
            self::$order_cache = $order;
        }
        return self::$order_cache;
    }

    public function get_order_addition($order_id)
    {
        $order_addition = $this->orderInfoDao->get_order_addition($order_id);
        $baoche = explode(",", $order_addition['baoche_id']);
        $order_addition['baoche_id'] = $baoche[0];
        $order_addition['baoche_num'] = $baoche[1];
        if (!empty($order_addition['other_service_id'])) {
            $format_service = explode("|", $order_addition['other_service_id']);
            foreach ($format_service as $row) {
                $format_row = explode(",", $row);
                $other_service[] = array(
                    'id' => $format_row['0'],
                    'num' => $format_row['1'],
                );
            }
            $order_addition['other_service']['list'] = $other_service;
            $order_addition['other_service']['total_price'] = $order_addition['other_service_price'];
        }

        return $order_addition;
    }
        //nice_key  for app
    public function get_homestay_service_by_order($order, $multi_price=12,$nice_key=false)
    {   $bid=$order->id;
        $bll_area = new Bll_Area_Area();
        $multi_price = empty($multi_price) ? 12 : intval($multi_price);
        $area = $bll_area->get_dest_config_by_destid($multi_price);
        $total_services = $this->orderInfoDao->acquire_order_homestay_booking_service($bid);
        if(empty($total_services)){
            return null;
        }
        $serviceids = array_column($total_services, "package_id");
        $dao_stay = new Dao_HomeStay_Stay();

        $temp = array();
        if(!empty($serviceids)) {
            $temp = $dao_stay->get_service_package_by_ids($serviceids);
        }

        foreach ($temp as $k => $v) {
            $services[$v['id']] = $v;
        }

        foreach ($total_services as $key => $row) {


            $package_id = $row['package_id'];
            $category=$row['service_category'];
            $service = $services[$package_id];

            if ($multi_price == 12) {
                $price_pre = Util_Common::zzk_tw_price_convert($row['price'], $order->dest_id)  ;
            } else {
                $price_pre = Util_Common::zzk_price_convert($row['price'], $order->dest_id, $multi_price);
            }

            $data = array(
                'service_id' => $service['service_id'],
                'service_name' => $service['service_name'],
                'type' => $service['free'],
                'price' => $price_pre * $row['num'],
                'num' => $row['num'],
                'content' => $service['content'],
                'currency_sym' => $area['currency_code'],
                'name' => $service['title'],
                'id' => $package_id,
                'price_pre' => $price_pre
            );
            if($category == 'unset') {
                $data['name'] = $service['service_name'];
            }
            $list[$category][] = $data;
        }
        if ($nice_key) {
            $result['baoche']=$list['baoche'];
            $result['other_service'] = $list['unset'];
            $result['pickup_service'] = $list['jiesong'];
            $result['catering_service'] = $list['zaocan'];
            $result['outdoor_service'] = $list['huwai'];
            $result['ticket_service'] = $list['daiding'];
        } else {
            $result = $list;
        }

        return $result;
    }

    public function get_whole_order_addition($order_id)
    {
        $ret = array();
        $homestay_dao = new Dao_HomeStay_Stay();
        $order_addition = $this->orderInfoDao->get_order_addition($order_id);
        $order_service = $this->get_homestay_booking_service($order_id);
        if ($order_addition['baoche_id']) {
            $format_baoche_id = explode(",", $order_addition['baoche_id']);
            $baoche_service = $homestay_dao->get_baoche_explain_byids(array($format_baoche_id[0]));
            $baoche_service = $baoche_service[0];
            $ret[] = array(
                'category' => 'baoche',
                'service_id' => null,
                'service_name' => null,
                'package_id' => $baoche_service['id'],
                'package_name' => null,
                'num' => $format_baoche_id[1],
                'free' => $baoche_service['type'] === 0,
                'status' => $baoche_service['status'],
                'price' => $baoche_service['price'],
                'description' => $baoche_service['content'],
            );
        }
        if ($order_addition['other_service_id']) {
            $other_service = explode("|", $order_addition['other_service_id']);
            foreach ($other_service as $row) {
                $format_row = explode(",", $row);
                $other_service = $homestay_dao->get_other_service_by_id($format_row[0]);
                $other_service = $other_service[0];
                $ret[] = array(
                    'category' => 'other',
                    'service_id' => $other_service['service_id'],
                    'service_name' => $other_service['service_name'],
                    'package_id' => $other_service['id'],
                    'package_name' => $other_service['title'],
                    'num' => $format_row[1],
                    'free' => $other_service['free'],
                    'status' => $other_service['status'],
                    'price' => $other_service['price'],
                    'description' => $other_service['content'],
                );
            }
        }
        foreach($order_service as $row) {
            if($row['sid'] <= 1710) continue; //新老兼容
            $other_service = $homestay_dao->get_other_service_by_id($row['package_id']);
            $other_service = reset($other_service);
            $ret[] = array(
                    'category' => $other_service['category'] == 'unset' ? "other" : $other_service['category'] ,
                    'service_id' => $other_service['service_id'],
                    'service_name' => $other_service['service_name'],
                    'package_id' => $other_service['id'],
                    'package_name' => $other_service['title'],
                    'num' => $row['num'],
                    'free' => $other_service['free'],
                    'status' => $other_service['status'],
                    'price' => $other_service['price'],
                    'description' => $other_service['content'],
            );
        }

        return $ret;
    }

    public function get_pay_price($order_id)
    {
        return $this->orderInfoDao->get_pay_price($order_id);
    }

    public function order_price_detail($order_id)
    {
        $order = $this->order_load($order_id);
        if (empty($order)) {
            return false;
        }
        $bll_room = new Bll_Room_RoomInfo();
        $price_detail = $bll_room->room_price_detail($order->nid, $order->guest_date, $order->guest_checkout_date);
        $room_obj = $bll_room->zzk_room_detail_contact_order($order->nid);
        $total_guest_num = $order->guest_number + $order->guest_child_number;

        switch ($room_obj->room_price_count_check) {
            case 1:
                $count_num = $order->room_num;
                break;
            case 2:
                $count_num = $total_guest_num;
                break;
            default:
                $count_num = 1;
        }
        $book_room_model = $room_obj->room_model; //房型
        $add_bed_total_price_tw = 0;
        $total_book_room_model = $book_room_model * $order->room_num; //房型*房间数
        //加人的条件
        if (!empty($price_detail) && $total_book_room_model && ($total_guest_num > (int) $total_book_room_model)) {
            //加人费用
            $add_bed_total_price_tw = ($total_guest_num - $total_book_room_model) * $room_obj->add_bed_price;
        }

        $order_price_detail = false;
        foreach ($price_detail as $date => $price) {
            $room_price_tw = $price + $add_bed_total_price_tw;
            $room_price = ceil($room_price_tw / $order->exchange_rate);
            $room_price = Bll_Price_Check::check($room_price, $order->uid);
            $order_price_detail[$date] = $room_price;
        }
        if (empty($order_price_detail) || $order->total_price != (array_sum($order_price_detail) * $count_num)) {
            $avg_price = $order->total_price / ($order->guest_days * $count_num);
            foreach ($order_price_detail as $date => $price) {
                $order_price_detail[$date] = $avg_price;
            }
        }
        return array($order_price_detail, $count_num);
    }

    public function get_order_info_byid($id)
    {
        if(!$id) return array();
        return $this->orderInfoDao->get_order_info_byid($id);
    }

    public function get_multi_orderinfo_by_hash_id($hash_id) {
        if(empty($hash_id)) return array();
        if(!is_array($hash_id)) $hash_id = array($hash_id);
        return $this->orderInfoDao->get_multi_homestay_booking_by_hash_id($hash_id);
    }

    public function get_order_info_by_hash_id($hash_id) {
        return $this->orderInfoDao->get_homestay_booking_by_hash_id($hash_id);
    }

    public function acquire_exist_refund_order_by_oid($id)
    {
        return $this->orderInfoDao->acquire_exist_refund_order_by_oid($id);
    }

    public function acquire_refund_status_by_oid($id)
    {
        return $this->orderInfoDao->acquire_refund_status_by_oid($id);
    }

    public function update_order_info_byid($order_id, $coupon)
    {
        return $this->orderInfoDao->update_order_info_byid($order_id, $coupon);
    }

    public function get_order_list_byuid($uid)
    {
        return $this->orderInfoDao->get_order_list_byuid($uid);
    }

    public function get_no_check_order_list_byuid($uid)
    {
        $list = $this->orderInfoDao->get_order_list_byuid($uid);
        $result = array();
        array_multisort($list, 'SORT_DESC', 'SORT_NUMERIC');
        foreach ($list as $row) {
        }
    }

    public function get_order_list_bybnb($uids, $params)
    {
        if (empty($uids)) {
            return;
        }

        return $this->orderInfoDao->get_order_list_bybnb($uids, $params);
    }

    public function get_same_order_byphoneuid($phone, $uid)
    {
        return $this->orderInfoDao->get_same_order_byphoneuid($phone, $uid);
    }

    public function add_cancel_reocrd($params)
    {
        return $this->orderInfoDao->add_cancel_reocrd($params);
    }

    public function get_order_list_byguestuid($uid)
    {
        return $this->orderInfoDao->get_order_list_byguestuid($uid);
    }
    public function get_order_by_guest_homestay($homestay_uid,$guest_uid){
        return $this->orderInfoDao->get_order_by_guest_homestay($homestay_uid,$guest_uid);
    }

    public function get_AppPay_count($uid)
    {
        return $this->orderInfoDao->get_AppPay_count($uid);
    }

    public function get_same_order_byphoneuid_apppay($phone, $uid)
    {
        return $this->orderInfoDao->get_same_order_byphoneuid_apppay($phone, $uid);
    }

    public function get_last_order_by_uid($uid)
    {
        return $this->orderInfoDao->get_last_order_by_uid($uid);
    }
    public function get_last_pending_order_by_uid($uid)
    {
        return $this->orderInfoDao->get_last_pending_order_by_uid($uid);
    }
    public function get_order_list_by_homestayuid($uid, $page = 0, $sort = 0, $order_status = '', $booking_time = 0, $checkin_date = 0, $remit = 0, $keyword = '', $cid = null, $page_limit = 10)
    {
        if(is_array($uid)){
            $where = 'uid in ('.join(',',$uid).')';
        }else{
            $where = " uid = " . $uid;
        }
        $order = "order by update_date desc";
        if ($sort > 0) {
            $order = $this->build_order_where($sort);
        }
        if ($cid > 0) {
            $where = $where . " and guest_uid = $cid";
        }
        if ($booking_time > 0) {
            $where = $where . " and " . $this->build_booking_time_where($booking_time);
        }
        if (in_array($checkin_date, array('today', 'tomorrow', 'inweek'))) {
            $where = $where . " and " . $this->build_checkin_time_where($checkin_date);
        }
        if (in_array($remit, array('1', '2'))) {
            $where = $remit == 2 ? $where . " and status = 6 " : $where . " and status = 2 ";
        }
        if ($keyword != '') {
            $keyword = Util_ZzkCommon::tradition2simple($keyword);
            $where = $where . " and (guest_name  like  '%" . trim($keyword) . "%' or id like '%" . trim($keyword) . "%'  or hash_id like '%" . trim($keyword) . "%')";

        }
        if (in_array($order_status, array('pending', 'executory', 'dealed', 'canceled'))) {
            $where = $where . " and " . $this->build_status_where($order_status);
        }
        $limit = " limit " . $page * $page_limit . "," . ($page + 1) * $page_limit;
        $data = array();
        if ($sort == 3) {
            $order = "order by guest_date asc";
            $where_after = $where . " and guest_date >'" . date('Y-m-d', time() - 86400) . "'";
            $data_after = $this->orderInfoDao->get_order_list_by_where($where_after, $order, $limit);
            $where = $where . " and guest_date <'" . date('Y-m-d') . "'";
            $order = "order by guest_date desc";
            $data_pre = $this->orderInfoDao->get_order_list_by_where($where, $order, $limit);
            $data = array_merge($data_after, $data_pre);
        } else {
            $data = $this->orderInfoDao->get_order_list_by_where($where, $order, $limit);
        }

        return $data;
    }
    public function get_order_transfer_info($oid)
    {
        $data = $this->orderInfoDao->get_order_transfer_info($oid);
        return $data;
    }

    public function get_order_list_by_sql($where, $order, $limit)
    {
        $data = $this->orderInfoDao->get_order_list_by_where($where, $order, $limit);
        $count = $this->orderInfoDao->get_order_list_count_by_where($where);
        return array('data' => $data, 'Total' => $count['num']);
    }
    public function build_order_where($sort)
    {
        $sort_params = array('1' => 'order by update_date desc', '2' => 'order by create_time  desc', '3' => 'order by  guest_date desc');
        return $sort_params[$sort];
    }

    public function build_status_where($sort)
    {
        $order_params = array('pending' => '0', 'executory' => '4', 'dealed' => '2,6', 'canceled' => '3,5');
        return "status in (" . $order_params[$sort] . ")";
    }

    public function build_booking_time_where($booking)
    {
        if (!in_array($booking, array('1', '2', '3'))) {
            return '';
        }
        $today = time();
        $days = 30;
        $order_params = array('1' => $today - $days * 86400, '2' => $today - $days * 86400 * 2, '3' => $today - $days * 86400 * 3);
        return "create_time >" . $order_params[$booking];
    }

    public function build_checkin_time_where($booking)
    {
        $now = time();
        $days = 7;
        $ser_str = '2012-01-01';
        for ($i = 0; $i < $days; $i++) {
            $ser_str = $ser_str . "','" . date('Y-m-d', $now + 86400 * $i);
        }
        $order_params = array('today' => date('Y-m-d', $now), 'tomorrow' => date('Y-m-d', $now + 86400), 'inweek' => $ser_str);
        return "guest_date in ('" . $order_params[$booking] . "') and status in (2,6)";
    }

    public function get_orderremit_info($orderid)
    {
        return $this->orderInfoDao->get_orderremit_info($orderid);
    }

    /**
     * 更新txAlipay方式支付的订单支付成功支付状态，必须确定支付成功后才能调用该方法更新相应数据
     *
     * @param $order order_load 加载的order对象
     * @param $outTradeNo
     * @param $paySource
     * @param $partner
     * @param $currency
     * @param $totalFee
     */
    public function updateSuccessTaixinAlipayStatus($order, $outTradeNo, $paySource, $partner, $currency, $totalFee)
    {
        if (in_array($order->status, array(1, 4))) {
            Logger::info('taixin notify update status');
            //update status
            $result = $this->zzk_save_order_trac_content($order->id, $order->last_admin_uid, '支付宝台新，自动操作', '收款成功', 2, array('total_price' => $order->total_price, 'total_price_tw' => $order->total_price_tw, 'trade_no' => '', 'out_trade_no' => $outTradeNo, 'payment_type' => 'txalipay', 'payment_source' => $paySource));
            Logger::info('taixin notify update status result', var_export($result, true));
            $this->orderInfoDao->save_order_extra_info(array('oid' => $order->id, 'partner' => $partner, 'currency' => $currency, 'total_fee' => $totalFee));
        }
        // 更新payment_log表，记录支付已成功；防止重复查询
        $this->orderInfoDao->update_order_payment_log_status($outTradeNo, 1);
    }

    public function get_days_booking_by_huids($uids, $days)
    {
        if (!is_array($uids)) {
            $uids = array($uids);
        }

        if (empty($uids)) {
            return;
        }

        if ($days) {
            $time = strtotime("- {$days} days");
        } else {
            $time = strtotime(date('Y-m-d'));
        }
        return $this->orderInfoDao->get_days_booking_by_huids($uids, $time);
    }

    public function get_order_log_for_host($order_id, $admin_uid)
    {
        return $this->orderInfoDao->get_order_log_for_host($order_id, $admin_uid);
    }

    public function get_order_cancel_log($order_id)
    {
        return $this->orderInfoDao->get_order_cancel_log($order_id);
    }

    public function get_order_remit_log($order_id)
    {
        return $this->orderInfoDao->get_order_remit_log($order_id);
    }

    public function get_log_homestay_booking_trac($order, $status)
    {
        return $this->orderInfoDao->get_log_homestay_booking_trac($order_id);
    }

    public function get_checked_out_booking_by_date($date)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }

        return $this->orderInfoDao->get_checked_out_booking_by_date($date);
    }

//    public function get_price_to_by_paid_by_oid($oid) {
//        $bll = new Bll_Order_OrderInfo();
//        $order = $bll->order_load($oid);
//
//    }


//    public function get_filter_order_list($email, $status, $keyword, $page_num=0, $guest_uid=0) {
//        if(empty($email) && empty($guest_uid)) return array();
//        
//    }

    public function remove_order_byid($order_id) {
        $order_info = $this->orderInfoDao->get_order_addition_by_hash($order_id);
        if(!$order_info['order_id']) {
            $addition = array(
                'user_deleted' => 1,
            );
            return $this->orderInfoDao->dao_insert_homestay_booking_addtion($order_info['origin_id'], $addition);
        } else {
            return $this->orderInfoDao->remove_order_byid($order_info['order_id'], $uid);
        }
    }

    public function order_no_show($hash_id, $order_id, $home_id, $room_id) {
        if(!$order_id) {
            $order_info = $this->orderInfoDao->get_order_addition_by_hash($order_id);
            $order_id = $order_info['origin_id'];
        }

        $data = array(
            'orderId' => $hash_id, 
            'hotelId' => $home_id,
            'roomId'  => $room_id,
        );
        $response = Util_Curl::post("http://open.api.youyao.com/booking/noShow", $data);
//        Logger::info(__FILE__, __METHOD__, __LINE__, var_export($data, true));
        if($response['code'] != 200) {
            Logger::info(__FILE__, __METHOD__, __LINE__, var_export(
                array(
                    "data"=>$data,
                    "response"=> $response
                ), true));
        }
        $result = json_decode($response['content'], true);
        if($result['code'] != 200) {
            Logger::info(__FILE__, __METHOD__, __LINE__, var_export(
                array(
                    "data"=>$data,
                    "response"=> $response
                ), true));
        }

        return $this->orderInfoDao->order_no_show($order_id);
    }

    // 主键id
    public function change_paytype($order_id, $paytype = 0) {
        if(!$order_id) {
            return;
        }

        return $this->orderInfoDao->change_paytype($order_id, $paytype);
    }

    public function booking_order_by_created($time) {
        return $this->orderInfoDao->booking_order_by_created($date);
    }

    public function booking_order_by_checkin($date) {
        return $this->orderInfoDao->booking_order_by_checkin($date);
    }

}
