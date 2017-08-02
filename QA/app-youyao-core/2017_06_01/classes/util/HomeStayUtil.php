<?php
class Util_HomeStayUtil {
    private $debug = true;

    private function log($msg)//即时输出调试使用
    {
        if ($this->debug == false)
            return;
        print_r($msg);
        echo "\r\n";
        ob_flush();
        flush();
    }

    /*
     * 获取具体某一天某个房间的价格
     * by vruan@2015-07-30
     * 参数 type='cn' 表示人民币
     * type='tw' 表示台币
     */
    function zzk_room_price($nid,$day,$type='cn',$discounts=1){
        self::log("房间号:".$nid);
        $room_obj = self::zzk_room_detail_contact_order($nid);
        $room_model = $room_obj->room_model;
        self::log("几人房:".$room_model);
        $room_count_check = $room_obj->room_price_count_check;  //房间计价方式
        self::log("room_price_count_check 如果是2,这个房型就是按人头收费!");
        self::log("room_price_count_check:".$room_count_check);
        $uid = $room_obj->uid;
        self::log("用户id:".$uid);
        $dest_id = $room_obj->dest_id;
        $default_model = $room_count_check==2?$room_model:"-1";  //如果是2就是按人计费，如果是1就按照房间计费
        #echo "计费方式:".$room_model."\t如果是2就是按人计费，如果是1就按照房间计费\n";
        // get room status items 获取房态信息
        //$rsItems = self::zzk_fetch_calendar_room_status($nid,$day,$day);


        // get room price config
        $priceConfig = self::zzk_fetch_room_price_config($uid);
        self::log("获取某用户的所有价格配置信息");
        self::log("接着获取某房型的具体价格配置信息");
        $pcItems = self::rps_parse_room_price_config($priceConfig, $nid);

        $dates = self::rps_generate_calendar_dates(date_create($day),date_create($day));

        // 取得所有天的非零，设置过的价格，
        // 优先日历单日设置的价格，如无则取批量价格
        // 忽略已成交的日期和价格
        $prices = array();
        foreach ($dates as $date) {
            $rpsItem1 = self::rps_find_date_within_price_config($date, $pcItems, $default_model);
            if (!empty($rpsItem1) && $rpsItem1['price'] > 0) { // 如果有批量房价，且房价大于0
                $prices[] = $rpsItem1['price'];
            }
        }
        // 如果有价格，排序
        if (!empty($prices)) {
            sort($prices);
            $result['price_tw'] = $prices[0];
            $result['price_cn'] = self::zzk_tw_price_convert($prices[0], $dest_id);
        }
        //return array('lowestPrice' => $result,
        //    'hasCalendarPrice' => !empty($rsItems),
        //    'hasConfigPrice' => !empty($pcItems)
        //);
        if($type == 'cn')
        return $result['price_cn']*$discounts;
        if($type == 'tw')
        return $result['price_tw']*$discounts;
    }

    /*
 * 获取一个月内的房间价格（原价）
 * by vruan@2015-08-03
 * 参数 discounts 为true 时，返回打折后的价格
 * 否则返回原价
 */
    function zzk_room_price_4_month($nid,$month,$year,$type='cn',$discounts=false)
    {

        $sDate = date_create("$year-$month-1");
        $list = date('t',mktime(0,0,0,$month,1,$year));
        $eDate = date_create("$year-$month-$list");
        //exit(var_dump($eDate));
        //房型及房间计算方式
        $room_obj = self::zzk_room_detail_contact_order($nid); //根据房间号，获取房间的相关信息
        $room_model = $room_obj->room_model;    //房间房型
        $room_count_check = $room_obj->room_price_count_check;  //房间计价方式
        $default_model = $room_count_check==2?$room_model:"-1";  //如果是2就是按人计费，如果是1就按照房间计费
        $uid = $room_obj->uid; //用户id
        $dest_id = $room_obj->dest_id;
        // get room status items
        //$rsItems = self::zzk_fetch_calendar_room_status($nid, $sDate->format('Y-m-d'), $eDate->format('Y-m-d'));
        //不考虑房态
        $rsItems = array();
        // get room price config from t_rpconfig_v2
        $priceConfig = self::zzk_fetch_room_price_config($uid);

        // 根据价格配置获取房间的价格配置信息
        $pcItems = self::rps_parse_room_price_config($priceConfig, $nid);


        $dates = self::rps_generate_calendar_dates($sDate, $eDate); //产生这段时间的日期对象们

        // 取得所有天的非零，设置过的价格，优先日历单日设置的价格，如无则取批量价格
        // 忽略已成交的日期和价格
        $prices = array();
        foreach ($dates as $date) {
            $rpsItem = self::rps_find_date_within_room_status($date, $rsItems, $room_count_check);
            $rpsItem1 = self::rps_find_date_within_price_config($date, $pcItems, $default_model);
            // 如果已订完，则忽略
            //if (!empty($rpsItem) && $rpsItem['num'] <= 0) {
            //    continue;
            //}
            // 如果有日历房价，则使用日历房价
            if (!empty($rpsItem) && $rpsItem['price'] > 0) {
                $prices[$date->format('Y-m-d')] = $rpsItem['price'];
            } else if (!empty($rpsItem1) && $rpsItem1['price'] > 0) { // 如果有批量房价，且房价大于0
                $prices[$date->format('Y-m-d')] = $rpsItem1['price'];
            }
            // if (!empty($rpsItem) && $rpsItem['price'] > 0) {
            //   $prices[] = $rpsItem['price'];
            // } else if (!empty($rpsItem1) && $rpsItem1['price'] > 0) {
            //   $prices[] = $rpsItem1['price'];
            // }
        }
        if($type == 'cn')
        {
            foreach($prices as $key=> &$price)
            {
                $price = ceil(self::zzk_tw_price_convert($price, $dest_id));
            }
            //return $result;
        }

        if($discounts)
        {
            $discount_info = new Dao_Discount_Info();
            foreach($prices as $key=> &$price)
            {
                $r = $discount_info->get_month_discounts($nid,$month,$year);
                //$list_date = $key;
                //$list_date_time = strtotime($list_date);
                //$r = $discount_info->get_day_discounts($list_date_time,$nid);
                //$x = date('w',$list_date_time);
                //if($x > 0 ) {$x--;} else {$x = 6;}
                //$disc = empty(explode('_',$r['discount'])[$x])?1:explode('_',$r['discount'])[$x];
                $price = ceil($price * $r[$key]);
            }
            return $prices;
        }else{
            return $prices;
        }
    }


    /*
     * 获取一段时间内的房间价格（原价）
     * by vruan@2015-07-30
     * 参数 discounts 为true 时，返回打折后的价格
     * 否则返回原价
     * 这个方法需要优化，待优化
     * 打折后的人民币价格是先转台币，然后打折，然后转成人民币
     */
    function zzk_room_price_4_period($nid,$sDate,$eDate,$type='cn',$discounts=false)
    {

        $bll = new Bll_Disc_Info();
        $r = $bll->get_period_discs($sDate,$eDate,$nid);
        $sDate = date_create(date("Y-m-d",$sDate));
        $eDate = date_create(date("Y-m-d",$eDate));
        //房型及房间计算方式
        $room_obj = self::zzk_room_detail_contact_order($nid); //根据房间号，获取房间的相关信息
        $room_model = $room_obj->room_model;    //房间房型
        $room_count_check = $room_obj->room_price_count_check;  //房间计价方式
        $default_model = $room_count_check==2?$room_model:"-1";  //如果是2就是按人计费，如果是1就按照房间计费
        $uid = $room_obj->uid; //用户id
        $dest_id = $room_obj->dest_id;
        // get room status items

        $rsItems = array();
        // get room price config from t_rpconfig_v2
        $priceConfig = self::zzk_fetch_room_price_config($uid);

        // 根据价格配置获取房间的价格配置信息
        $pcItems = self::rps_parse_room_price_config($priceConfig, $nid);


        $dates = self::rps_generate_calendar_dates($sDate, $eDate); //产生这段时间的日期对象们

        // 取得所有天的非零，设置过的价格，优先日历单日设置的价格，如无则取批量价格
        // 忽略已成交的日期和价格
        $prices = array();
        foreach ($dates as $date) {
            $rpsItem = self::rps_find_date_within_room_status($date, $rsItems, $room_count_check);
            $rpsItem1 = self::rps_find_date_within_price_config($date, $pcItems, $default_model);
            // 如果有日历房价，则使用日历房价
            if (!empty($rpsItem) && $rpsItem['price'] > 0) {
                $prices[$date->format('Y-m-d')] = $rpsItem['price'];
            } else if (!empty($rpsItem1) && $rpsItem1['price'] > 0) { // 如果有批量房价，且房价大于0
                $prices[$date->format('Y-m-d')] = $rpsItem1['price'];
            }
        }

        if($discounts)
        {
            foreach($prices as $key=> &$price)
            {
                $list_date = $key;
                $list_date_time = strtotime($list_date);
                $disc = $r[$list_date_time];
                $price = ceil($price * $disc);
            }
        }


        if($type == 'cn')
        {
            foreach($prices as $key=> &$price)
            {
                $price = ceil(self::zzk_tw_price_convert($price, $dest_id));
            }
        }

        return $prices;

    }

	/*
	* 获得最近N天最低房价
 	* return:array(price_cn,price_tw)
 	*/
 	function zzk_room_lowest_price($uid, $nid, $dateText, $dest_id) {
 		$result = array('price_cn' => 0, 'price_tw' => 0);
 		// get start and end date
 		$dateText = '2014-11';
 		$dateRange = self::rps_get_calendar_date_range($dateText, 6);
 		if (empty($dateRange)) {
 			return array('lowestPrice' => $result,
 				'hasCalendarPrice' => false,
 				'hasConfigPrice' => false
 				);
 		}

 		list($sDate, $eDate) = $dateRange;
 		$sDate = date_create($dateText);
 		//房型及房间计算方式
 		$room_obj = self::zzk_room_detail_contact_order($nid);
 		$room_model = $room_obj->room_model;    //房间房型
 		$room_count_check = $room_obj->room_price_count_check;  //房间计价方式
 		$default_model = $room_count_check==2?$room_model:"-1";  //如果是2就是按人计费，如果是1就按照房间计费
 		// get room status items
 		$rsItems = self::zzk_fetch_calendar_room_status($nid, $sDate->format('Y-m-d'), $eDate->format('Y-m-d'));
 		// get room price config
 		$priceConfig = self::zzk_fetch_room_price_config($uid);
 		$pcItems = self::rps_parse_room_price_config($priceConfig, $nid);
 		$dates = self::rps_generate_calendar_dates($sDate, $eDate);

 		// 取得所有天的非零，设置过的价格，优先日历单日设置的价格，如无则取批量价格
 		// 忽略已成交的日期和价格
 		$prices = array();
 		foreach ($dates as $date) {
 			$dayText = $date->format('m-d');
 			$month = $date->format('m');
 			$rpsItem = self::rps_find_date_within_room_status($date, $rsItems, $room_count_check);
 			$rpsItem1 = self::rps_find_date_within_price_config($date, $pcItems, $default_model);
 			// 如果已订完，则忽略
 			if (!empty($rpsItem) && $rpsItem['num'] <= 0) {
 				continue;
 			}
 			// 如果有日历房价，则使用日历房价
 			if (!empty($rpsItem) && $rpsItem['price'] > 0) {
 				$prices[] = $rpsItem['price'];
 			} else if (!empty($rpsItem1) && $rpsItem1['price'] > 0) { // 如果有批量房价，且房价大于0
 				$prices[] = $rpsItem1['price'];
 			}
 			// if (!empty($rpsItem) && $rpsItem['price'] > 0) {
		    //   $prices[] = $rpsItem['price'];
		    // } else if (!empty($rpsItem1) && $rpsItem1['price'] > 0) {
		    //   $prices[] = $rpsItem1['price'];
		    // }
 		}
 		// 如果有价格，排序
 		if (!empty($prices)) {
 			sort($prices);
 			$result['price_tw'] = $prices[0];
 			$result['price_cn'] = self::zzk_tw_price_convert($prices[0], $dest_id);
 		}
 		return array('lowestPrice' => $result,
		    'hasCalendarPrice' => !empty($rsItems),
		    'hasConfigPrice' => !empty($pcItems)
		  );
 	}

	/*
	* 给定日期和月数，计算日历上的起始月1号日期和结束月最后日期
 	*/
 	function rps_get_calendar_date_range($dateText, $numMonth) {
 		if (!($sDate = date_create($dateText))) {
 			return array();
 		}
 		$addMonth = $numMonth - 1;
 		if ($addMonth > 11) {
 			$addMonth = 11;
 		}
 		$eDate = date_create($sDate->format('Y-m-d'));
 		if ($addMonth > 0) {
 			$eDate->add(\DateInterval::createFromDateString("$addMonth months"));
 		}
 		$sDate = date_create(date('Y-m-01', $sDate->getTimestamp()));
 		$eDate = date_create(date('Y-m-t', $eDate->getTimestamp()));
 		return array($sDate, $eDate);
 	}

 	/*
	author:axing
	function:zzk_room_detail_contact_order 下订单的时候实时读取的房间信息
	para:nid 房间id
	return:object
	*/
	function zzk_room_detail_contact_order($nid){
		$roomInfo = new Dao_Room_RoomInfo();
		return $roomInfo->room_detail_contact_order($nid);
	}

	/*
	 * 获取房态数据，用于动态显示日历房态信息，不排除room_num = 0的数据
	 */
	function zzk_fetch_calendar_room_status($nid, $sDateText, $eDateText)
	{
		$roomInfo = new Dao_Room_RoomInfo();
	 	return $roomInfo->fetch_calendar_room_status($nid, $sDateText, $eDateText);
	}

    //获取uid下的所有价格配置
	function zzk_fetch_room_price_config($uid)
	{
		$roomInfo = new Dao_Room_RoomInfo();
	 	return $roomInfo->fetch_room_price_config($uid);
	}

	/*
	 * parse database string price config info to array structure
	 * return an array of price items,
	 */
	// note: every pcItem is an Array like this:
	// array(2) {
	//   'dateRange' =>
	//   class stdClass#268 (4) {
	//     public $QName =>
	//     string(15) "自定义名称"
	//     public $QDate =>
	//     string(35) "04-27,05-05|12-24,01-05|01-18,02-28"
	//     public $WDate =>
	//     string(13) "1,2,3,4,5,6,7"
	//     public $qx =>
	//     string(1) "1"
	//   }
	//   'price' =>
	//   int(4500)
	// }
	function rps_parse_room_price_config($priceConfig, $nid)
	{
	  if (empty($priceConfig)) {
	    return array();
	  }

	  $roomDates = json_decode($priceConfig['room_date']);
	  $roomPrices = json_decode($priceConfig['room_price']);
	  if (!is_array($roomDates) || !is_array($roomPrices)) { // incase data in db is wrong.
	    return array();
	  }

	  // 拆分价格信息，每个时间段设置一个价格
	  $prices = array();
	  foreach ($roomPrices as $roomPrice) {
	    if ((int)$roomPrice->rid == $nid) {
	      $prices = explode(',', $roomPrice->price);
	      break;
	    }
	  }

	  //拆分时间段配置，并设置对应价格
	  $pcItems = array();
	  foreach ($roomDates as $dateRanges) {
	    //获取当前时间段对应的价格
	    $price = array_shift($prices);
	    if (is_null($price)) {
	      $price = 0;
	    }
	    //数据库中一个价格可以对应多个时间段设置，所以要进一步拆分
	    foreach ($dateRanges->data as $dateRange) {
	      $pcItems[] = array(
	        "dateRange" => $dateRange,
	        'price' => (int)$price,
	      );
	    }
	  }

	  // 根据时间段权重，倒排序
	  usort($pcItems, function($a, $b) {
	    return (int)$b['dateRange']->qx - (int)$a['dateRange']->qx;
	  });

	  return $pcItems;
	}




	/*
	 * 根据给定的日期和月数，生成日历上的每一天数组，数组对象是Date
	 */
	function rps_generate_calendar_dates($sDate, $eDate)
	{
	  $dates = array();
	  $date = date_create($sDate->format('Y-m-d')); // 必须克隆一个新的日期对象，否则传入参数会被更改
	  do {
	    $dates[] = date_create($date->format('Y-m-d')); // 必须克隆一个新的对象，否则数组内的对象都是最后的日期
	    $date->add(\DateInterval::createFromDateString("1 day"));
	  } while ($date <= $eDate);

	  return $dates;
	}

	function rps_find_date_within_room_status($date, $rsItems, $room_count_check=1)
	{

	  $rpsItem = array();
	  $dateText = $date->format('Y-m-d');
        //var_dump($dateText);
	  if($room_count_check==2){  //按人计算
	    foreach ($rsItems as $rsItem) {
	      if ($dateText == $rsItem['room_date']) {
	        $num = 0;
	        if (isset($rsItem['beds_num']) && !is_null($rsItem['beds_num'])) {
	          $num = $rsItem['beds_num'];
	        }
	        $rpsItem = self::rps_generate_item($rsItem['room_price'], $num);
	        break;
	      }
	    }
	  }else{   //按房间计算
	    foreach ($rsItems as $rsItem) {
            //var_dump($rsItem);exit;
	      if ($dateText == $rsItem['room_date']) {
	        $num = 0;
	        if (isset($rsItem['room_num']) && !is_null($rsItem['room_num'])) {
	          $num = $rsItem['room_num'];
	        }
	        $rpsItem = self::rps_generate_item($rsItem['room_price'], $num);
	        break;
	      }
	    }
	  }

	  return $rpsItem;
	}

	function rps_generate_item($price, $num)
	{
	  return array('price' => $price, 'num' => $num);
	}

	function rps_find_date_within_price_config($date, $pcItems, $default_model="-1")
	{
	  $rpsItem = array();
	  $dayText = $date->format('m-d');
	  foreach ($pcItems as $pcItem) {
	    if (self::rps_is_date_in_pcItem($dayText, $date->format('N'), $pcItem)) {
	      $rpsItem = self::rps_generate_item((int)$pcItem['price'], $default_model);
	      break;
	    }
	  }

	  return $rpsItem;
	}

	// 检查给定的日期字符串mm-dd, 是否在批量价格设置Item(pcItem)的日期范围之内
	// note: every pcItem is an Array like this:
	// array(2) {
	//   'dateRange' =>
	//   class stdClass#268 (4) {
	//     public $QName =>
	//     string(15) "自定义名称"
	//     public $QDate =>
	//     string(35) "04-27,05-05|12-24,01-05|01-18,02-28"
	//     public $WDate =>
	//     string(13) "1,2,3,4,5,6,7"
	//     public $qx =>
	//     string(1) "1"
	//   }
	//   'price' =>
	//   int(4500)
	// }
	function rps_is_date_in_pcItem($dayText, $wDay, $pcItem)
	{
	  if (empty($pcItem) || !isset($pcItem['dateRange'])) {
	    return false;
	  }

	  $dateRanges = explode('|', $pcItem['dateRange']->QDate);
	  foreach ($dateRanges as $dateRange) {
	    $dayRange = explode(',', $dateRange);
	    if (!empty($dayRange) && count($dayRange) > 1) {
	      if ($dayText >= $dayRange[0] && $dayText <= $dayRange[1]) {
	        if ($pcItem['dateRange']->WDate == "0") { // 忘记填星期几
	          //$wDays = array(1,2,3,4,5,6,7);
	          $wDays = array(0);
	        } else {
	          $wDays = explode(',', $pcItem['dateRange']->WDate);
	        }
	        if (in_array($wDay, $wDays)) {
	          return true;
	        }
	      }
	    }
	  }
	  return false;
	}

	function zzk_tw_price_convert($price_tw,$dest_id=10) {
	  $row = self::get_dest_config($dest_id);
	  $money_rate = $row['exchange_rate'];
	  $price_cn = 0;
	  if($price_tw>0){
	     $price_cn = ceil($price_tw/$money_rate);
	  }
	  return $price_cn;
	}

	function get_dest_config($dest_id){
	    $areaInfo = new Dao_Area_Area();
	 	return $areaInfo->get_dest_config($dest_id);
	}

}
?>