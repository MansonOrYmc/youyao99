<?php
/**
 * Created by PhpStorm.
 * User: chanlevel
 * Date: 16/1/27
 * Time: 下午6:09
 */

apf_require_class("APF_Controller");

class User_AdminUserInfoController extends APF_Controller
{

    /**
     * 子类通过实现本方法添加业务逻辑
     * @return mixed string|array 直接返回字符串表示页面类名称；返回数组包含
     * 两个成员，第一个是页面类名称，第二个为页面类使用的变量。
     * @example 返回'Hello_Apf_Demo'，APF会加载Hello_Apf_DemoPage类。
     * @example 返回array('Hello_Apf_Demo', array('foo' => 'bar'))，APF会加载
     * Hello_Apf_Demo类，而且在对应的phtml文件中可以直接使用变量$foo，其值为'bar'。
     *
     * 注意，返回字符串是为了兼容旧有代码，不推荐使用。
     */
    public function handle_request()
    {
        // TODO: Implement handle_request() method.
        header("Content-type: application/json; charset=utf-8");

        $req = APF::get_instance()->get_request();
        $params = $req->get_parameters();
        $security = Util_Security::Security($params);
        if (!$security) {
            echo json_encode(array(
                'code' => 0,
                'codeMsg' => '非法请求',
                'status' => 400,
                'msg' => "request forbidden",
                "userMsg" => "非法请求",
            ));

            return false;
        }

        $uid = $params['uid'];
        if (empty($uid)) {
            $uid = $params['mobile_userid'];
        }

        $params['uid'] = $params['mobile_userid'];
        if (empty($uid)) {
            Util_Json::render(400, null, 'uid needed', 'uid needed');
            return false;
        }

        $params['uid'] = $uid;
        $action = $params['action'];
        if ($action == 'profile') {
            $this->handle_profile($uid);
        } else if ($action == 'homes') {
            $this->handle_homes($params);
        } else if ($action == 'availablerooms') {
            $this->get_solr_homestayrooms($params);
        } else if ($action == "getnotice") {
            $this->get_notice($params);
        }
    }

    private function handle_profile($uid)
    {

        $home_bll = new Bll_Homestay_StayInfo();
        $user_bll = new Bll_User_UserInfo();
        $msg_bll  = new Bll_User_Msg();
        $home_info = $home_bll->get_homestay_by_id($uid);
        //   Util_Avatar::dispatch_avatar($uid);
        $avatar = Util_Image::img_url_generate($home_info->user_photo_file, $home_info->user_photo_version_i);
        $nickname = $user_bll->get_user_nickname_by_uid($uid);
        $msg_count = $msg_bll->get_message_list_byuid($uid);
        $verified = $home_info->verified_by_zzk;
        $result = array(
            'avatar' => $avatar,
            'nickname' => $nickname,
            'username' => $home_info->username,
            'verify' => $verified,
            'message_num' => count($msg_count),
        );
        Util_Json::render(200, $result);
    }

    private function handle_homes($params)
    {
        $uid = $params['uid'];

        $guid = $params['guid'];
        if (!empty($guid)) {
            $bll_push = new Bll_Push_Register();
            $bll_push->user_bind_guid($uid, $guid);
        }
        $uidlist = $this->get_childhomestay_ids($uid);


        $data = array();
        foreach ($uidlist as $id) {
            $data[] = $this->get_simple_homeinfo($id);
        }

        foreach ($data as $k => $home) {
            $rooms = $home['rooms'];
            foreach ($rooms as $key => $v) {
                $ridlist[] = $v['room_id'];
            }
        }

        $bll_disc = new Bll_Disc_Info();
        $disc_room_list = $bll_disc->get_disc_roomids_by_roomids($ridlist);
        if ($disc_room_list) {
            $discids = array_column($disc_room_list, 'nid');
            foreach ($data as $k => $home) {
                $rooms = $home['rooms'];
                foreach ($rooms as $key => $v) {
                    if (in_array($v['room_id'], $discids)) {
                        $data[$k]['rooms'][$key]['disc'] = 1;
                    } else  $data[$k]['rooms'][$key]['disc'] = 0;
                }
            }
        }


        Util_Json::render(200, $data,null,null,false);

    }

// get homestay rooms from the db  status in (0,1)
    private function get_simple_homeinfo($uid)
    {
        $bll_orderInfo = new Bll_Order_OrderInfo();
        $homestay_bll = new Bll_Homestay_StayInfo();

        $userinfo_bll = new Bll_User_UserInfo();
        $homestay_info = $userinfo_bll->get_whole_user_info($uid);

        $user_info = $homestay_bll->get_whole_stay_info_by_id($uid);
        if ($user_info['type'] == 15) {
            $rooms = $homestay_bll->get_bnd_room_by_uid($uid);
            if ($rooms) {
                $rooms = array($rooms);
            }else{
                $rooms = array();
            }
        } else {
            $rooms = $homestay_bll->get_exist_rooms_by_uid($uid);
            if(!$rooms) $rooms = array();
        }
//        foreach ($rooms as $k => $room) {
        //            if (!$room['status']) unset($rooms[$k]);
        //        }

      
        $image = Util_Image::get_homestay_image($uid);

        $status = $homestay_info['poi_id'] && $homestay_info['status']; // 0 下架    poi=0  为待审核

        $home_status = $status ? 1 : 0;
        if (!$homestay_info['poi_id']) {
            $home_status = -1;
        }

// home_status  -1 待审核   0 下架   1 上架


        $dao = new Dao_Homestay_Score();
        $rank = $dao->get_score_rank_arr($uid);

        $rank = array_values($rank);

        $first=current($rank);
        $rank_str=$first ?  sprintf("%s: 第%d名", $first['type_name'], $first['rank']) : '';

        if ($home_status == -1) {
            $notice = '审核中';
        } else if ($home_status == 0) {
            $notice = '已下架';
        } else { //已上架
//            $last_order = $bll_orderInfo->get_last_pending_order_by_uid($uid);
//            if (empty($last_order)) {
//                $notice = '';
//            } else {
//                $last_order_time = $last_order['create_time'];
//                $tx = $this->timediff($last_order_time, time());
//                $notice = $tx . '前有新訂單';
//            }
            $notice = $rank_str;
        }

        return array(
            'homestay_uid' => $uid,
            'status' => $home_status,
            'last_order' => empty($last_order_time) ? 0 : $last_order_time,
            'room_count' => count(($rooms)),
            'room_num' => '共' . count($rooms) . '间房',
            'image' => $image,
            'homestay_name' => $homestay_info['name'],
            'notice' => $notice,
            'rank' => $rank,
            'rank_str' => $rank_str,
            'rooms' => $rooms,
        );
    }

    /**
     * 民宿取消订单推荐房间列表 status =1
     * @param $params
     */
    private function get_solr_homestayrooms($params)
    {
        $uid = $params['uid'];
        if ($params['order_id']) {
            $order_id = $params['order_id'];
            $order_bll = new Bll_Order_OrderInfo();
            $order = $order_bll->order_load($order_id);
            $current_room_id = $order ? $order->nid : '';
        } else {
            $current_room_id = null;
        }

        if (!empty($params['checkin'])) {
            $checkin = $params['checkin'];
        } elseif ($order) {
            $checkin = $order->guest_date;
        }
        if (!empty($params['checkout'])) {
            $checkout = $params['checkout'];
        } elseif ($order) {
            $checkout = $order->guest_checkout_date;
        }

        $uidlist = $this->get_childhomestay_ids($uid);

        $data = array();
        foreach ($uidlist as $id) {
            $info = $this->get_simple_homestayrooms_by_uid($id, $checkin, $checkout, $current_room_id);
            if (!is_null($info)) {
                $data[] = $info;
            }

        }

        Util_Json::render(200, $data);
    }

    private function get_simple_homestayrooms_by_uid($uid, $checkin = null, $checkout = null, $room_id = null)
    {
        $homestay_bll = new Bll_Homestay_StayInfo();
        $homestay_info = $homestay_bll->get_homestay_by_id($uid);
        if (is_null($homestay_info)) {
            return null;
        }

        $room_bll = new Bll_Room_RoomInfo();
        $room_list = $room_bll->get_roomlist_by_uid($uid, $checkin, $checkout);
        if (empty($room_list)) {
            return null;
        }

        foreach ($room_list as $k => $v) {
            if ($v->id == $room_id) {
                unset($room_list[$k]);
            }
            if ($v->bookable == false) {
                unset($room_list[$k]);
            }

        }

        $rooms = array();
        foreach ($room_list as $k => $room) {
            $rooms[] = array(
                'room_id' => $room->id,
                'room_name' => $room->title,
                'status' => $room->status,

            );
        }
        return array(
            'homestay_id' => $uid,
            'homestay_name' => $homestay_info->username,
            'rooms' => $rooms,
        );
    }

    private function get_childhomestay_ids($uid)
    {
        $bll_userInfo = new Bll_User_UserInfo();
        $mult_uids = $bll_userInfo->get_mult_uids($uid);

        $uidlist = array();
        $uidlist[] = $uid;
        foreach ($mult_uids as $v) {
            if (!in_array($v['b_uid'], $uidlist)) {
                $uidlist[] = $v['b_uid'];
            }

        }
        return $uidlist;
    }
    private function get_notice($params){
        $uid=$params['uid'];

        $user_bll = new Bll_User_UserInfo();
        $order_notice = $user_bll->get_pendingorder_by_uid($uid);

        $msg_notice = $user_bll->get_unreadmsg_by_uid($uid);

        $mine_notice = 0;

        $result = array(
            'order' => $order_notice,
            'msg' => $msg_notice,
            'mine' => $mine_notice,
        );
        Util_Json::render(200,$result);

    }

    public function timediff($begin_time, $end_time)
    {
        if ($begin_time < $end_time) {
            $starttime = $begin_time;
            $endtime = $end_time;
        } else {
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        $timediff = $endtime - $starttime;
        $days = intval($timediff / 86400);
        $remain = $timediff % 86400;
        $hours = intval($remain / 3600);
        $remain = $remain % 3600;
        $mins = intval($remain / 60);
        $secs = $remain % 60;
//        $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
        $str = '';
        if ($days > 0) {
            $str .= $days . '天';
        }

        if ($hours > 0) {
            $str .= $hours . '小時';
        }

        if ($mins > 0) {
            $str .= $mins . '分';
        }

        return $str;
    }

}
