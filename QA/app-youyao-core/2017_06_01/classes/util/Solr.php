<?php
require_once  dirname(__FILE__).'/SolrCenter.php';
class Util_Solr {

/*
     * 根据传入的搜索参数执行搜索
     * queryArgs
     * array(
     'query' => '', // any valid solr query string
     'locid' => 0,
     'price' => 10,
     'model' => 14,
     'order' => 1,
     'page' => 0,
     'name_code' => '',
     'channel' => 'room',
     'checkin_date' => 'yyyy-mm-dd',
     'checkout_date' => 'yyyy-mm-dd',
     );
     * 如果关键词是区县名字，会在传入参数里增加区县name_code信息，便于网站代码使用，生成搜索URL.
     *
     * 返回Array(搜索结果，修改过的queryArgs)
     */
    public static  function zzk_solr_search_rooms($queryArgs) {
          $queryArgs =  Util_ZzkCommon::tradition2simple($queryArgs);
		  $query = $queryArgs['query'];
		  // 首先区分是否为关键词搜索
		  $isKeywordSearch = !empty($queryArgs['query']) && !preg_match("/:/", $queryArgs['query']);
		  // 检查关键字中是否含有景点名称;
		  if($isKeywordSearch){
		  	$bll_spot = new Bll_Spot_Spot();
		  	$loc_poi = $bll_spot->get_spot_byid();
		    foreach($loc_poi as $k=>$v){
		      if(trim($queryArgs['query'])==$v['poi_name']){
		        $queryArgs['query'] = '';
		        $query = '';
		        $queryArgs['sight'] = $v['id'];
		        $isKeywordSearch = false;
		        break;
		      }
		      if(is_numeric(strpos($queryArgs['query'],$v->poi_name))){
		        $queryArgs['sight'] = $v['id'];
		      }
		    }
		  }
		  // 目的的过滤
		  $dest_id = $queryArgs['dest_id'];
		  $filters = array("status:1", "dest_id:$dest_id");
		
		  //区县过滤
		  $filterLocId = 0;
		  // 如果传入参数有区县ID, 则使用传入的区县ID做过滤搜索
		  $bll_area = new Bll_Area_Area();
		  $locs = $bll_area->get_area_by_destid($dest_id);
		  if(isset($queryArgs['locid']) && (int)$queryArgs['locid'] > 99) {
		    $filterLocId = $queryArgs['locid'];
             // print_r($filterLocId);exit;   60511
		    foreach($locs as $loc) {
		      if(preg_match("/".$loc['type_name']."/",$queryArgs['query'])){
		         $filterLocId = $loc['locid'];
		         $queryArgs['locid'] = $loc['locid'];
		      }

		    }

          } else if ($isKeywordSearch) {
		    $queryArgs['locid'] = $queryArgs['locid'] < 99 ? '' : $queryArgs['locid'];
		    // 如果没有传入区县过滤，则判断搜索关键词是否是区县名字，如果是则把关键词搜索变成按照区县ID过滤
		    // $array_cityname = array();
		    $matchLoc = array();
		    foreach($locs as $loc) {
		      if ($queryArgs['query'] == $loc['type_name']) {
		        $matchLoc = $loc;
		        break;
		      }
		    }
		    // 如果关键词是区县名字，把搜索变成非关键词搜索, 并且设置name_code相关信息
		    if (!empty($matchLoc)) {
		      $filterLocId = $matchLoc['locid'];
		      $isKeywordSearch = false;
		      $query = "";
		
		      $queryArgs['locid'] = $matchLoc['locid'];
		      $queryArgs['name_code'] = $matchLoc['name_code'];
		    }
		
		    // $name_code = '';
		    // if(in_array($keys, $array_cityname)) {
		    //   $name_code = get_t_loc_type_locid_byname($keys); 
		    //   $url = Const_Host_Domain."/room/".$name_code;
		    //   zzk_goto($url);
		    // }
		
		  }
		  if ($filterLocId > 0) {
		    if ($filterLocId == 60507) {
		      // 马祖包括连江
		      $filters[] = "(location_typeid:$filterLocId OR location_typeid:60510)";
		    } else if ($filterLocId == 2683) {
		      // 台东包括绿岛
		      $filters[] = "(location_typeid:$filterLocId OR location_typeid:60517)";
		    } else if ($filterLocId == 2745) {
		      // 屏东包括小琉球
		      $filters[] = "(location_typeid:$filterLocId OR location_typeid:60518)";
		    } else {
		      $filters[] = "loc_typeid:$filterLocId";
		    }
		  }
		  $queryArgs['is_keyword_search'] = $isKeywordSearch;
		
		  //房型过滤
		  
		  $model_dao = new Dao_Search_Room();
		  $m = $model_dao->get_t_room_model_byid($dest_id, $queryArgs['model']);
		  
		  if(isset($m['condtion'])) {
		    if($m['condtion']!='unlimited') {
		       if((int)$m['condtion']>=6){
		         $filters[] = "room_model:[6 TO *]";
		       }
		       else{
		         $filters[] = "room_model:".$m['condtion'];
		       }
		    }
		  }
		  //房价过滤
	      if (isset($queryArgs['price'])) {
              if($_GET['multiprice'==10]){
                  $filters[] = "int_price_tw:[" . $queryArgs['price'][0] . " TO " . $queryArgs['price'][1] . "]";
              }else{
				$filters[] = "int_price:[" . $queryArgs['price'][0] . " TO " . $queryArgs['price'][1] . "]";
              }
	      }
		  //速订过滤
		  if(isset($queryArgs['speed']) && $queryArgs['speed'] != 0){
		    $filters[] = "speed_room:1";
		  }
          //马蜂窝民宿ID过滤
          if(isset($queryArgs['uid'])){
              foreach($queryArgs['uid'] as $v)
              {
                  $arr_ids[] = $v;
              }
              $filters[]="uid:(".implode(' OR ',$arr_ids).")";
          }
		  //服务过滤
		  if(isset($queryArgs['service']) && $queryArgs['service'] > 0) {
		    switch ((int)$queryArgs['service']) {
		      case 1: 
		        $filters[] = "breakfast:1";
		        break;
		      case 2: 
		        $filters[] = "jiesong_service_i:1";
		        break;
		      case 3: 
		        $filters[] = "baoche_service_i:1";
		        break;
		      case 4: 
		        $filters[] = "other_service_i:1";
		        break;
		      case 10: 
		        $filters[] = "(breakfast:1 OR jiesong_service_i:1 OR baoche_service_i:1 OR other_service_i:1)";
		        break;
		        
              case 5:
                $service = self::get_stay_service($queryArgs['service_item']);  
                if($service){
                	$filters[] = "(".$service.")";
                } 
                break;   
		        
		      default:
		        # code...
		        break;
		    }
		  }
		  //分类标签过滤
//		  $qp = get_category_tags_query_param();
//		  $hcTagsQs = empty($_GET[$qp]) ? "" : $_GET[$qp];  
//		  if (!empty($hcTagsQs)) {
//		    $selectedTags = explode('-', $hcTagsQs);
//		    $filterTags = array();
//		    foreach ($selectedTags as $tag) {
//		      if (strpos($tag, "_aa") === false) {
//		        $filterTags[] = $tag;
//		      }
//		    }
//		    if (!empty($filterTags)) {
//		      $filters[] = "(category_tags_ss:(".implode(" OR ", $filterTags)."))";
//		    }
//		  }
		  //房态过滤. 如果是关键词搜索，不过滤房态
		  if (!$isKeywordSearch && !empty($queryArgs['checkin_date']) && !empty($queryArgs['checkout_date'])) {
		    $dates_qs = self::zzk_get_solr_room_dates_str($queryArgs['checkin_date'], $queryArgs['checkout_date']);
		    if (strlen($dates_qs) > 0) {
		      $filters[] = "(*:* AND NOT soldout_room_dates_ss:($dates_qs))";
		    }
		  }
		  
		  
         //附近，景点
         if (isset($queryArgs['lat']) && isset($queryArgs['lng']) && $queryArgs['distance']>0) {
            $filters[] = "{!geofilt pt=".$queryArgs['lat'].",".$queryArgs['lng']." sfield=latlng_p d=".$queryArgs['distance']." sort=geodist()+asc}";
         }
         
 		  if(isset($queryArgs['sight']) && $queryArgs['sight'] > 0){
		     $bll_spot = new Bll_Spot_Spot();
		  	 $lonlat = $bll_spot->get_spot_byid('',$queryArgs['sight']);
		     $filters[] = "{!geofilt pt=".$lonlat[0]['google_map_lat'].",".$lonlat[0]['google_map_lng']." sfield=latlng_p d=7}";
		     $queryArgs['lat'] = $lonlat[0]['google_map_lat'];
		     $queryArgs['lng'] = $lonlat[0]['google_map_lng'];
		  }
         
		  
		  $sort_field = self::zzk_get_solr_sort_string($queryArgs);
		
		  $page_num = 1;
		  if (!empty($queryArgs['page']) && (int)$queryArgs['page'] > 0) {
		    $page_num = (int)$queryArgs['page'];
		  }
		  $limit = 30;
		  if (!empty($queryArgs['limit']) && (int)$queryArgs['limit'] > 0) {
		    $limit = (int)$queryArgs['limit'];
		  }
		  $offset = ($page_num-1) * $limit;
		
		  if (empty($query)) {
		    $query = "*:*";
		  }
		  $params =  array(
		    'q.op' => 'OR',
		    'wt' =>' json',
		    'sort' => $sort_field,
		    'fq' => implode(" AND ", $filters),
		    'group' => 'true',
		    'group.field' => 'uid',
		    'group.offset' => 0,
		    'group.limit' => 100,
		    'group.sort' => $sort_field,
		    'group.format' => 'grouped',
		    'group.ngroups' => 'true',
		  );
		  if(isset($queryArgs['sight']) && $queryArgs['sight'] > 0){
		     $params['fl'] = "*, distance:geodist(latlng_p, ".$lonlat[0]['google_map_lat'].", ".$lonlat[0]['google_map_lng'].")";
		  }
		  
		  if (isset($queryArgs['lat']) && isset($queryArgs['lng']) && $queryArgs['distance']>0) {
		     $params['fl'] = "*, distance:geodist(latlng_p, ".$queryArgs['lat'].", ".$queryArgs['lng'].")";
		  }
		
		  if ($isKeywordSearch) {
		    $params['defType'] = 'edismax';
			 $params['qf'] = 'id^30 title^10 username^30 userpoi_address^10 loc_typename^5 content';
			 $params['pf'] = 'title^1000 username^3000 userpoi_address^1000 content^100';
//		    $params['qf'] = 'username^10 user_address^30';
//		    $params['pf'] = 'username^1000 user_address^3000';
		    $params['mm'] = 1;
		    //$params['ps'] = 10000;
		  }
           // var_dump($filters);
		  //var_dump($params);
		  $queryArgs['search_params'] = $params;
		  $solr = new Apache_Solr_Service(APF::get_instance()->get_config('solr_host'), APF::get_instance()->get_config('solr_port'), '/search/room/');
		  return array($solr->search($query, $offset, $limit, $params), $queryArgs);
    }
    
    
    /*
     * 构建房态查询日期字符串，
     * 输入入住和退房日期日期
     * 返回solr 无房日期过滤查询字符串
     */
    private  function zzk_get_solr_room_dates_str($start, $end, $max = 14) {
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

        return implode(" OR ", $dates);
    }
	
    private function zzk_get_solr_sort_string($queryArgs) {
		  $order = isset($queryArgs['order']) ? (int)$queryArgs['order'] : 1;
		  if(isset($queryArgs['sight']) && $queryArgs['sight'] > 0 && ($order < 2 ||$order > 9)){
		     $bll_spot = new Bll_Spot_Spot();
		  	 $lonlat = $bll_spot->get_spot_byid('',$queryArgs['sight']);		     
		     $queryArgs['order'] = $queryArgs['order']==11 ? 11 : 10;
		     $distFunc = "geodist(latlng_p,".$lonlat[0]['google_map_lat'].",".$lonlat[0]['google_map_lng'].")";
		  }else if(isset($queryArgs['lat']) && isset($queryArgs['lng'])){
	         $queryArgs['order'] = $queryArgs['order']==11 ? 11 : 10;
			 $distFunc = "geodist(latlng_p,".$queryArgs['lat'].",".$queryArgs['lng'].")";
		  }
		  
		  // 综合排序
		  // 计算公式
		  // log(民宿评论数量/30 + 10) * 民宿评论分数
		  // + log(房间评论数量/30 + 10) * 房间评论分数
		  // + 速定*110
		  // + 早餐*3 + 接送*3 + 包车*6 + 特色服务*9
		  // + 私信回复率 * 私信2小时内回复率 * 20
		  if ($distFunc) {
		    $sortFunc = "div(score_f, map($distFunc,0,1,1))";
		  } else {
		    $sortFunc = 'score_f';
		  }
		
		  if ($queryArgs['is_keyword_search']) {
		    //排序
		    //$sort_field = 'verified_by_zzk desc, score desc,speed_room desc, changed desc';
		    $sort_field = "verified_by_zzk desc, score desc, $sortFunc desc, changed desc";
		    if($order == 1) {
		      //$sort_field = 'verified_by_zzk desc, score desc,speed_room desc,changed desc';
		      $sort_field = "verified_by_zzk desc, score desc, $sortFunc desc, changed desc";
		    }
		    if($order == 2) {
		      $sort_field = "verified_by_zzk desc,int_price asc, score desc, $sortFunc desc, changed desc";
		    }
		    if($order == 3) {
		      $sort_field = "verified_by_zzk desc,int_price desc, score desc, $sortFunc desc, changed desc";
		    }
		    if($order == 4) {
		      $sort_field = "verified_by_zzk desc,order_succ asc, score desc, $sortFunc desc, changed desc";
		    }
		    if($order == 5) {
		      $sort_field = "verified_by_zzk desc,order_succ desc, score desc, $sortFunc desc, changed desc";
		    }
		    if($order == 7) {
		      $sort_field = "verified_by_zzk desc,hs_rating_avg_i desc, score desc, $sortFunc desc, changed desc";
		    }
		    if($order == 10) {
		      $sort_field = "verified_by_zzk desc, map($distFunc,0,1,1) asc, score desc, $sortFunc desc, changed desc";
		    }
		    if($order == 11 && !empty($distFunc)) {
		      $sort_field = "verified_by_zzk desc, $distFunc asc, score desc, $sortFunc desc, changed desc";
		    }
		    return $sort_field;
		  }
		
		  switch ($order) {
		    case 1:
		      $sort_field = "product(speed_room,110) desc, $sortFunc desc";
		      break;
		
		    case 2:
		      $sort_field = "int_price asc, $sortFunc desc";
		      break;
		
		    case 3:
		      $sort_field = "int_price desc, $sortFunc desc";
		      break;
		
		    case 4:
		      $sort_field = "order_succ asc, $sortFunc desc";
		      break;
		      $sort_field = "order_succ desc, $sortFunc desc";
		      break;
		
		    case 7:
		      $sort_field = "hs_rating_avg_i desc, $sortFunc desc";
		      break;
		
		    case 10:
		      $sort_field = "map($distFunc,0,1,1) asc, $sortFunc desc";
		      break;
		
		    case 11: {
			    if(!empty($distFunc)) {
				    $sort_field = "$distFunc asc, $sortFunc desc";
			    }else{
				    $sort_field = "$sortFunc desc";
			    }
			    break;
		    }
		    
		    default:
		      $sort_field = "product(speed_room,110) desc, $sortFunc desc";
		      break;
		  }
		
		  return "verified_by_zzk desc, $sort_field, changed desc";
    }
    
    
    //http://192.168.28.118:8983/search/user/select?wt=xml&fq=id%3A%2819700+OR+15753+OR+12456+OR+30987+OR+26537+OR+13246+OR+12559+OR+19219+OR+12454+OR+13323+OR+12452+OR+13108+OR+12370+OR+22639+OR+18248+OR+51112+OR+30322+OR+44297+OR+35605+OR+18025+OR+12295+OR+48214+OR+48473%29+AND+{!geofilt+pt%3D24.180666%2C120.645155+sfield%3Dlatlng_p+d%3D3+sort%3Dgeodist%28%29%2Basc}&fl=*%2C+distance%3Ageodist%28latlng_p%2C24.180666%2C120.645155%29&json.nl=map&q=*%3A*&start=0&rows=30&sort=geodist%28latlng_p,24.180666,120.645155%29%20asc
    
    
    
    public static function zzk_get_homestayinfo_byid($arr_ids,$lat=0,$lng=0,$order=0){
        $solr = new Apache_Solr_Service(APF::get_instance()->get_config('solr_host'), APF::get_instance()->get_config('solr_port'), '/search/user/');
        $fl   = "*";
        $sort = "";
        $order_field="";
        
    	switch ($order) { 
		    case 1:
		      $order_field = "int_price asc,verified_by_zzk desc, score desc, changed desc";
		      break;
		    case 2:
		      $order_field = "comment_num desc,verified_by_zzk desc, score desc, changed desc";
		      break;
		    case 3:
		      $order_field = "order_succ asc,verified_by_zzk desc, score desc, changed desc";
		      break;
		    case 4:
		      $order_field = "order_succ desc,verified_by_zzk desc, score desc, changed desc";
		      break;
		    default:
		      $order_field = "verified_by_zzk desc, score desc, changed desc";
		      break;
		  }
       
        
        if($lat!=0 && $lng!=0){
           $fl ="*, distance:geodist(latlng_p,".$lat.",".$lng.")";
           $sort = "geodist(latlng_p,".$lat.",".$lng.") ASC";
        }
        $query = "*:*";
		$params = array(
            'wt' => ' json',
            'fq' => "id:(".implode(' OR ',$arr_ids).")",
		    'fl' => $fl
         );
         if(!empty($sort)){
         	$params['sort'] = $sort;
         }
         if(!empty($order_field)){
         	$params['sort'] = empty($params['sort'])?$order_field:$params['sort'].",".$order_field;
         } 
		$solr_result = $solr->search($query, 0, 30, $params);
	    $data = json_decode($solr_result->getRawResponse(), TRUE);
		return $data['response']['docs'];
    }
    
    public static function get_stay_service($s_arr){
    	$service_config = array('zc'=>'breakfast:1',
    	                        'js'=>'jiesong_service_i:1',
    	                        'bc'=>'baoche_service_i:1',
    	                        'os'=>'other_service_i:1');
    	$str_ser = "";
		foreach ($s_arr as $key=>$value){
			if($str_ser){
				$str_ser=$str_ser." AND ".$service_config[$value];
			}else{
				$str_ser = $service_config[$value];
			}
			
		}
		
		return $str_ser;
    }
    
    
    public static function  get_roominfo_by_id($nid){
    	$query = "id:".$nid;
    	$params =  array(
		    'wt' =>' json'
		  );
	    $queryArgs['search_params'] = $params;
    	$solr = new Apache_Solr_Service(APF::get_instance()->get_config('solr_host'), APF::get_instance()->get_config('solr_port'), '/search/room/');
		return array($solr->search($query, 0, 1, $params), $queryArgs);
    }

    public static function  get_roominfo_by_ids($nids){
		$limit = count($nids);
		$nid_string = implode(" OR id:",$nids);
    	$query = "id:".$nid_string;
    	$params =  array(
		    'wt' =>' json',
			'group' => 'true',
			'group.field' => 'uid',
			'group.offset' => 0,
			'group.limit' => 100,
#			'group.sort' => '',
			'group.format' => 'grouped',
			'group.ngroups' => 'true',
		);
	    $queryArgs['search_params'] = $params;
    	$solr = new Apache_Solr_Service(APF::get_instance()->get_config('solr_host'), APF::get_instance()->get_config('solr_port'), '/search/room/');
		return $solr->search($query, 0, $limit, $params);
    }

	public static function get_bnbinfo_priceguarantee($ids, $limit = 10){
		$query = "*:*";
		$condition = implode(" OR id:", $ids);
		$params = array(
			'wt' => 'json',
			'fq' => 'id:'.$condition,
			'sort' => 'loc_typeid asc',
		);
    	$solr = new Apache_Solr_Service(APF::get_instance()->get_config('solr_host'), APF::get_instance()->get_config('solr_port'), '/search/user/');
		$result = $solr->search($query, 0, $limit, $params);
		return $result;
	}

	public static function search_bnblist_by_name($name) {
		$query = $name;
		$params = array(
			'wt' => 'json',
			'defType' => 'edismax',
			'qf' => 'username^10',
			'mm' => 1,
		);
		$solr = new Apache_Solr_Service(APF::get_instance()->get_config('solr_host'), APF::get_instance()->get_config('solr_port'), '/search/user/');
		$result = $solr->search($query, 0, 5, $params);
		return $result;

	}

	public static function get_bnbinfo_by_ids($ids) {
		foreach($ids as $id) {
			$fq .= $fq ? " OR id:".$id : "id:".$id;
		}
		$params = array(
			'wt' => 'json',
			'mm' => 1,
			'fq' => $fq,
			'fl' => 'id,latest_success_time_s,default_image_s',
		);
		$solr = new Apache_Solr_Service(APF::get_instance()->get_config('solr_host'), APF::get_instance()->get_config('solr_port'), '/search/user/');
		$result = $solr->search('*:*', 0, 20, $params);
		$data = json_decode($result->getRawResponse(), TRUE);
		return $data['response']['docs'];
	}
       
}
