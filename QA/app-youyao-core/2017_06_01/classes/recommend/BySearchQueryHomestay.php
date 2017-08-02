<?php

require_once dirname(__FILE__) . '/../Solr/Service.php';

class Recommend_BySearchQueryHomestay
{

    private $homestayId;
    private $homestay;
    private $searchArgs;
    private $roomid;

    public function __construct($homestayId, $params)
    {
        $this->homestayId = $homestayId;
        $this->roomid=empty($params['rid']) ? 0 : $params['rid'];
        $searchArgs = new stdClass;
        $start = $this->format_checkin_date($params['checkin']);
        $end = $this->format_checkout_date($start, $params['checkout']);

        $searchArgs->checkin = (empty($params['checkin']) || $params['checkin']=='null') ? "" : $start;
        $searchArgs->checkout = (empty($params['checkout']) || $params['checkout']=='null') ? "" : $end;
        $searchArgs->minPrice = empty($params['lower_price']) ? -1 : (int) $params['lower_price'];
        $searchArgs->maxPrice = empty($params['upper_price']) ? -1 : (int) $params['upper_price'];
        $searchArgs->speed = empty($params['speed']) ? -1 : $params['speed'];
        $searchArgs->model = empty($params['model']) ? -1 : $params['model'];

        $this->searchArgs = $searchArgs;
    }

    public static function getSolrStayDatesArray($start, $end, $max = 14)
    {
        // 输入日期字符串，创建日期对象，如果输入非日期格式字符串，反馈空值，调用方需判断返回字符串长度。
        if (empty($start) || empty($end) || !($sDate = date_create($start)) || !($eDate = date_create($end))) {
            return "";
        }

        if ($sDate > $eDate) {
            $tmp = $sDate;
            $sDate = $eDate;
            $eDate = $tmp;
        }

        $date = $sDate;
        $dates = array();
        $count = 0;
        do {
            $dates[] = $date->format('m') . $date->format('d');
            $date->add(DateInterval::createFromDateString('1 days'));
            $count += 1;
            if ($count >= $max) {
                break;
            }
        } while ($date < $eDate);

        return $dates;
    }

    public function matchRooms()
    {
        $this->loadHomestay();
        if (empty($this->homestay)) {
            return array('status' => 'Failed', 'msg' => 'load user failed', "userMsg" => "民宿信息出错", "data" => array());
        }
        $baseFilters = array("status:1 AND uid:" . $this->homestayId);
        if(!empty($this->roomid)){
            $baseFilters[0]=$baseFilters[0].' AND id:'.$this->roomid;
        }
        $solr = new Apache_Solr_Service(
            APF::get_instance()->get_config('solr_host'),
            APF::get_instance()->get_config('solr_port'),
            '/search/room/');
        //$sort_field = $this->composeSolrSortString();
        $params = array(
            'q.op' => 'OR',
            'wt' => ' json',
            'sort' => "int_price asc",
            'fq' => implode(" AND ", $baseFilters),
//      'fl' => "id, uid, username, title, int_price,int_price_tw, speed_room, room_model, room_thum_img_file, soldout_room_dates_ss,room_comments_num_i,order_succ,add_bed_num,add_bed_price,dest_id,room_price_count_check_i,chuangxing,breakfast,wifi_i",
        );

        $ret = $solr->search("*:*", 0, 100, $params);
        $docs = $ret->response->docs;
        $rooms = array();
        $roomInfoDao = new Dao_Room_RoomInfo();
        $bll_area = new Bll_Area_Area();
        $multi_price = empty($_GET['multiprice']) ? 12 : intval($_GET['multiprice']);
        $area = $bll_area->get_dest_config_by_destid($multi_price);
        foreach ($docs as $doc) {
            if ($doc->id >= 2000000000) {
                continue;
            }

            $room = new stdClass;
            $room->is_bnb_cuxiao_i = $doc->is_bnb_cuxiao_i;
            $room->is_bnb_first_order_i = $doc->is_bnb_first_order_i;
            $room->id = $doc->id;
            $room->uid = $doc->uid;
            $room->username = $doc->username;
            $room->title = $doc->title;
#            $room->int_price = Util_Common::zzk_cn_price_convert($doc->int_price, $_GET['multiprice']);
            // 汇率每天都会变， 但是solr只有改动才会更新
            $room->int_price = Util_Common::zzk_price_convert($doc->int_price_tw, $doc->dest_id, $multi_price);
            $room->currency_sym = $area['currency_code'];
            $room->add_bed_num = $doc->add_bed_num;
            $room->add_bed_price = ($_GET['multiprice'] != 10) ? Util_Common::zzk_tw_price_convert($doc->add_bed_price, $doc->dest_id) : $doc->add_bed_price;
            //$room->int_price =$doc->int_price;
            $room->room_model = intval($doc->room_model);
            $room->speed_room = $doc->speed_room;
            $room->room_price_count_check = $doc->room_price_count_check_i ? $doc->room_price_count_check_i : 1;
            $room->room_comments_num_i = $doc->room_comments_num_i;
            $room->order_succ = $doc->order_succ;
            $room->soldout = 0;
            $room->images = Util_Image::getroomimages($doc->id);
            $room->image = Util_Image::getroomsmallimage($room->images);
            $room->avg_rating = $roomInfoDao->zzk_comment_avg_rating($doc->id);
            $room->soldout_room_dates_ss = $doc->soldout_room_dates_ss;
            $room->score = 0;
            $room->content = trim(strip_tags($doc->content));
            $room->dest_id = $doc->dest_id;
            $room_dao = new Dao_Room_RoomInfo();
            $room_addition = $room_dao->get_room_addition($doc->id);
            if (!empty($room_addition['room_floor'])) {
                $room->room_floor = (int)$room_addition['room_floor'];
            } else {
                $room->room_floor = null;
            }

            if ($room_addition['add_beds_check']) {
                $room->add_beds_price = Util_Common::zzk_price_convert($room_addition['add_beds_price'], $doc->dest_id, $multi_price);
                $room->add_beds_num = $room_addition['add_beds_num'];
            } else {
                $room->add_beds_price = null;
                $room->add_beds_num = null;
            }
            if ($doc->add_bed_check) {
                $room->add_bed_price = Util_Common::zzk_price_convert($doc->add_bed_price, $doc->dest_id, $multi_price);
                $room->add_bed_num = $doc->add_bed_num;
            } else {
                $room->add_bed_price = null;
                $room->add_bed_num = null;
            }

            if (empty($doc->roomsetting)) {
                $room_setting = array();
            } else {
                $room_setting = explode(',', $doc->roomsetting);
                $room_setting = array_map('trim', $room_setting);
            }

            $room->chuangxing = $doc->chuangxing . ($room_addition['bed_style_remark'] ? '(' . $room_addition['bed_style_remark'] . ')' : '');
            $room->breakfast = $doc->breakfast;
            $room->catering=$room->breakfast;
            $room->wifi = $doc->wifi_i;
            $room->bathroom = in_array('独立卫浴', $room_setting) ? 1 : 0;
            if (($key = array_search('独立卫浴', $room_setting)) !== false) {
                unset($room_setting[$key]);
            }
            $room->window = in_array('没有窗户', $room_setting) ? 0 : 1;
            if (($key = array_search('没有窗户', $room_setting)) !== false) {
                unset($room_setting[$key]);
            }
            $room->settings = array_values($room_setting);

            $donotconvert= strpos($doc->mianji,'平方');
            $mianji=preg_replace('/[^0-9]+\.[0-9]+/', '', $doc->mianji);
            if (!$donotconvert&&in_array($doc->dest_id, array(10, 15))) {
                $room->mianji = floatval($mianji * 3.3); //面积单位转换hh
            } else {
                $room->mianji = floatval($mianji);
            }

            $rooms[] = $room;
            if (is_array($room->soldout_room_dates_ss)) {
                //var_dump($room->soldout_room_dates_ss);
            }
        }

        $verify_speed = new Bll_Speedroom_Verify();
        $rooms = $verify_speed->verify_speed_date($rooms, $this->searchArgs->checkin, $this->searchArgs->checkout, 0);

        if (empty($rooms)) {
            return array('status' => 'Failed', 'msg' => 'got empty search results', 'userMsg' => "没有满足条件的房间", 'data' => $rooms);
        }

        //var_dump($this->searchArgs);

        // 初始匹配分数为0, 每个搜索条件增加1分，搜索条件为AND关系, 最后根据房间分数和匹配分数确定房间是否满足搜索条件
        $matchScore = 0;

        // 如果有入住日期条件，匹配分数+1, 房间如果在入住日期内有房，房间分数+1
        $stayDates = Recommend_BySearchQueryHomestay::getSolrStayDatesArray($this->searchArgs->checkin, $this->searchArgs->checkout);
        if (!empty($stayDates)) {
            $matchScore += 1;
            foreach ($rooms as $room) {
                $soldOut = false;
                if (is_array($room->soldout_room_dates_ss)) {
                    $soldoutDates = array_intersect($stayDates, $room->soldout_room_dates_ss);
                    if (!empty($soldoutDates)) {
                        $soldOut = true;
                        $room->soldout = 1;
                    }
                }
                if (!$soldOut) {
                    $room->score += 1;
                }
                unset($room->soldout_room_dates_ss);
            }
        }

        // 如果有价格范围，匹配分数+1, 如果房价在范围内，房间分数+1
        if ($this->searchArgs->minPrice >= 0 && $this->searchArgs->maxPrice > 0 &&
            $this->searchArgs->minPrice < $this->searchArgs->maxPrice) {
            $matchScore += 1;
            foreach ($rooms as $room) {
                if ($room->int_price >= $this->searchArgs->minPrice &&
                    $room->int_price <= $this->searchArgs->maxPrice) {
                    $room->score += 1;
                }
            }
        }

        // 如果房型条件，匹配分数+1, 如果房型在范围内，房间分数+1
        if ($this->searchArgs->model > 0) {
            $model_dao = new Dao_Search_Room();
            $m = $model_dao->get_t_room_model_byid($this->homestay->dest_id, $this->searchArgs->model);
            $modelNum = (int) $m['condtion'];
            if ($modelNum > 0) {
                $matchScore += 1;
                foreach ($rooms as $room) {
                    if ($modelNum > 5 && $room->room_model > 5 ||
                        $modelNum <= 5 && $room->room_model == $modelNum) {
                        $room->score += 1;
                    }
                }
            }
        }

        usort($rooms, function ($a, $b) {
            return $b->score - $a->score;
        });

        $matched = array();
        $unMatched = array();
        foreach ($rooms as $room) {
            if ($room->score >= $matchScore) {
                $matched[] = $room;
            } else {
                $unMatched[] = $room;
            }
        }
        usort($matched, function ($a, $b) {
            return $b->speed_room - $a->speed_room;
        });
        usort($unMatched, function ($a, $b) {
            return $b->speed_room - $a->speed_room;
        });

        $ret = array('status' => 'OK', 'msg' => 'recommnd success!', 'userMsg' => "",
            'data' => array('matchScore' => $matchScore, 'matched' => $matched, 'unmatched' => $unMatched));
        return $ret;
    }

    private function composeSolrSortString()
    {
        // 综合排序
        // 计算公式
        // log(民宿评论数量/30 + 10) * 民宿评论分数
        // + log(房间评论数量/30 + 10) * 房间评论分数
        // + 速定*110
        // + 早餐*3 + 接送*3 + 包车*6 + 特色服务*9
        // + 私信回复率 * 私信2小时内回复率 * 20
        $hsReviewScore = "product(log(sum(div(hs_comments_num_i, 30), 10)), hs_rating_avg_i)";
        $roomReviewScore = "product(log(sum(div(room_comments_num_i, 30), 10)), room_rating_avg_i)";
        $speedScore = "product(speed_room, 110)";
        $serviceScore = "sum(product(breakfast, 3), product(jiesong_service_i, 3), product(baoche_service_i, 6), product(other_service_i, 9))";
        $pmScore = "product(pm_reply_rate_i, pm_ht_rate_i, 0.002)";
        $totalScore = "sum($hsReviewScore, $roomReviewScore, $speedScore, $serviceScore, $pmScore)";
        if ($distFunc) {
            $sortFunc = "div($totalScore, map($distFunc,0,1,1))";
        } else {
            $sortFunc = $totalScore;
        }

        return "verified_by_zzk desc, $sortFunc desc, changed desc";
    }

    private function loadHomestay()
    {
        $solr = new Apache_Solr_Service(
            APF::get_instance()->get_config('solr_host'),
            APF::get_instance()->get_config('solr_port'),
            '/search/user/');
        $params = array(
            'q.op' => 'OR',
            'wt' => 'json',
            'fq' => 'id:' . $this->homestayId,
            'fl' => "id, dest_id, username",
        );

        $ret = $solr->search("*:*", 0, 100, $params);
        $docs = $ret->response->docs;
        if (empty($docs)) {
            $this->homestay = array();
        } else {
            $this->homestay = $docs[0];
        }

        return $this->homestay;
    }

    private function format_checkin_date($checkin)
    {
        $start = strtotime($checkin);
        $today = strtotime(date('Y-m-d', time()));
        $time = $checkin;
        if ($start < $today - 3600) {
            $time = date('Y-m-d', time() + 30 * 86400);
        }

        return $time;

    }

    private function format_checkout_date($checkin, $checkout)
    {
        $end = strtotime($checkout);
        $start = strtotime($checkin);
        $time = $checkout;
        if ($end < $start) {
            $time = date('Y-m-d', $start + 86400);
        }

        return $time;

    }

}
