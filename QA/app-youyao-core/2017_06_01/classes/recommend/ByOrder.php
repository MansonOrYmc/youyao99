<?php

require_once dirname(__FILE__).'/../Solr/Service.php';

class Recommend_ByOrder {

  private $order;
  private $orderId;
  private $recommend_locids = array(
    "2683" => array(60506, 2905, 2986),         //台东"),
    "2684" => array(60513,27737,2686),         //台中"),
    "2685" => array(60511,2736,2905),         //台北"),
    "2686" => array(2708,23139,27737),         //台南"),
    "2708" => array(27736,27737,2684),         //嘉义"),
    "2713" => array(60511,2736,2905),         //基隆"),
    "2736" => array(2905,2683,2685),         //宜兰"),
    "2745" => array(60518,60506,2683),        //"屏东"),
    "2793" => array(2684,2686,2986),        //"新竹"),
    "2817" => array(2685,60511,2905),        //"桃园"),
    "2875" => array(2986,2686,2685,60515,60511),        //"澎湖"),
    "2905" => array(2683,60506,2986),        //"花莲"),
    "2952" => array(2986,60506,2685,60515,60511,2905),        //"金门"),
    "2986" => array(60518,60506,2683),        //"高雄"),
    "23139" => array(2686,2986,2684),      //"云林"),
    "27736" => array(2708,2684,60513),    //"阿里山"),
    "27737" => array(2684,60513,2685),      //"彰化"),
    "27740" => array(2684,2686,2986),      //"苗栗"),
    "60506" => array(2683,2905,2685),      //"垦丁"),
    "60507" => array(2685,60511,2905),      //"马祖"),
    "60509" => array(2685,60511,2905),      //"新北"),
    "2674 " => array(60512,60513,2684),      //"南投"),
    "60512" => array(60513,2684,27737),    //"日月潭"),
    "60511" => array(2905,2736,2683),      //"九份"),
    "60513" => array(2684,2686,27736),      //"清境"),
    "60515" => array(2685,60511,2905),      //"淡水"),
    "60516" => array(60506,2683,2986),      //"恒春"),
    "60517" => array(2683,2905,2736),      //"绿岛"),
    "60518" => array(2986,60506,2683),    //"小琉球"),
    "60519" => array(60517,2683,60506),      //"兰屿"),
    "aaabbbccc" => array(60517,2683,60506),      //"test"),
  );

  public function __construct($orderId)
  {
    $this->orderId = $orderId;
  }

  public function recommend()
  {
    $this->loadOrder();
    if (empty($this->order)) {
      return array('status' => 'Failed', 'msg' => 'load order failed');
    }

    $filters = array("status:1", "dest_id:".$this->order->dest_id);

    // 类似价格区间
    $room_price = $this->order->total_price;
    if ($this->order->room_num > 0) {
      $room_price = $room_price / $this->order->room_num;
    }
    if ($this->order->guest_days > 0) {
      $room_price = $room_price / $this->order->guest_days;
    }
    $minPrice = (int)($room_price * 0.7 + 0.5);
    $maxPrice = (int)($room_price * 1.3 + 0.5);
    if ($minPrice < 20) {
      $minPrice = 20;
    }
    if ($maxPrice < $minPrice) {
      $maxPrice = (int)($minPrice * 1.5 + 0.5);
    }
    if ($maxPrice < 300) {
      $maxPrice = 300;
    }
    $filters[] = "int_price:[$minPrice TO $maxPrice]";

    if ($sDate = date_create($this->order->guest_checkout_date)) {
      $dates[] = $sDate->format('m') . $sDate->format('d');
      $sDate->add(\DateInterval::createFromDateString('1 days'));
      $dates[] = $sDate->format('m') . $sDate->format('d');
      $dates_qs = implode(" OR ", $dates);
      $filters[] = "(*:* AND NOT soldout_room_dates_ss:($dates_qs))";
    }

    $solr = new Apache_Solr_Service(
                    APF::get_instance()->get_config('solr_host'),
                    APF::get_instance()->get_config('solr_port'),
                    '/search/room/');
    $sort_field = $this->composeSolrSortString();
    $params =  array(
      'q.op' => 'OR',
      'wt' =>' json',
      'sort' => $sort_field,
      'fl' => "id, uid, username, int_price, loc_typename, loc_typecode",
      'group' => 'true',
      'group.field' => 'uid',
      'group.offset' => 0,
      'group.limit' => 100,
      'group.sort' => "int_price asc",
      'group.format' => 'grouped',
      'group.ngroups' => 'true',
    );

    // 推荐区县
    $locFilters = array();
    $locId = $this->getOrderLocid();
    if (!empty($locId)) {
      $rLocIds = $this->recommend_locids[$locId];
      if (!empty($rLocIds)) {
        foreach ($rLocIds as $locid) {
          $locFilters[] = "loc_typeid:$locid";
        }
      }
    }
    if (empty($locFilters)) {
      $locFilters[] = "";
    }
    //var_dump($locFilters);

    $numPerLoc = (int)(50 / count($locFilters));
    $hsItems = array();
    foreach ($locFilters as $locFilter) {
      //$filters[] = "(".implode(' OR ', $locFilters).")";
      if (empty($locFilter)) {
        $params['fq'] = implode(" AND ", $filters);
      } else {
        $params['fq'] = implode(" AND ", array_merge($filters, array($locFilter)));
      }
      //var_dump($params);

      $ret = $solr->search("*:*", 20, $numPerLoc, $params);
      $results = $ret->grouped->uid->groups;

      foreach ($results as $result) {
        $hsItems[] = $result->doclist->docs[0];
      }
    }

    if (empty($hsItems)) {
      return array('status' => 'Failed', 'msg' => 'got empty search results');
    }

    $ret = array(
      'status' => 'OK',
      'msg' => 'recommnd success!',
      'hsItems' => $hsItems);
    return $ret;
  }

  private function loadOrder()
  {
    $sql = "select id, nid, uid, uname, guest_name, guest_number, guest_mail, guest_date, guest_days, guest_etc, status, room_name, province, guest_uid, total_price, room_num, guest_checkout_date, guest_child_number, guest_child_age, book_room_model, dest_id from LKYou.t_homestay_booking where id = ?";

    $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($this->orderId));
    $order = $stmt->fetch(PDO::FETCH_OBJ);

    if ($order) {
      $this->order = $order;
    } else {
      $this->order = array();
    }

    return $this->order;
  }

  private function getOrderLocid()
  {
    $sql = "select loc_typecode from LKYou.t_weibo_poi_tw where uid = ?";

    $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($this->order->uid));
    $loc = $stmt->fetch(PDO::FETCH_OBJ);

    if ($loc) {
      $locIds = explode(',', $loc->loc_typecode);
      return trim($locIds[count($locIds) - 1]);
    }

    return "";
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

}
