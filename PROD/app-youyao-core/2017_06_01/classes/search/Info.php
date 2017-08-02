<?php
class Search_Info {

    private $search_soa_url;
    private $page_size = 30;

    private $keyWords;
    private $destId = 10; // taiwan
    private $searchid;
    private $checkInDate;
    private $checkOutDate;
    private $searchType = 'CITY';
    private $page = 1;
    private $price;
    private $service = array();
    private $roomModel;
    private $order = 1;
    private $multiprice = 12;
    private $multilang = 12;

    public $filterArgs = array();

    public function __construct() {
        $this->search_soa_url       = APF::get_instance()->get_config("search_soa_url");
//        $this->search_soa_url       = "http://192.168.8.61:8090/";
    }

    public function search_rooms() {
        $args = array(
            'keyWords'     => $this->keyWords,
            'destId'       => $this->destId,
            'searchid'     => $this->searchid,
            'checkInDate'  => $this->checkInDate,
            'checkOutDate' => $this->checkOutDate,
            'searchType'   => $this->searchType,
            'page'         => $this->page,
            'price'        => $this->price,
            'roomModel'    => $this->roomModel,
            'order'        => $this->order,
            'multiprice'   => $this->multiprice,
            'multilang'    => Util_Language::get_locale_id(),
        );
        if(!empty($this->service)) { // 空字符串，空数组会输出json
            $args['service'] = json_encode($this->service);
        }

        $url = $this->search_soa_url;
if($_GET['nbnla']=='ljl'){
print_r($url."/?".http_build_query($args));
}
        $data = Util_Curl::get($url, $args);
        $response = json_decode($data['content'], true);
        $result = $response['info'];

        return $result;
    }

    public function homenum($params) {
        $args = array(
            'destId' => $params['dest_id'],
            'searchType' => $params['search_type'],
            'searchid' => $params['search_id'],
            'page'         => $this->page,
            'order'        => $this->order,
            'multiprice'   => $this->multiprice,
            'multilang'    => Util_Language::get_locale_id(),
        );
        $url = $this->search_soa_url;
        $data = Util_Curl::get($url, $args);
//        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($url, true));
//        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($args, true));
        $response = json_decode($data['content'], true);
        $result = $response['info'];

        return $result['ngroups'];
    }

    public function set_search_params($params) {

    }

    public function zzk_solr_search_rooms($queryArgs) { // 本方法暂时用作新老兼容
//        print_r($queryArgs);
        $query = $queryArgs['query'];
        // 首先区分是否为关键词搜索
        $isKeywordSearch = !empty($queryArgs['query']) && !preg_match("/:/", $queryArgs['query']);

        // 目的的过滤
        $dest_id = $queryArgs['dest_id'];
        if($dest_id) { 
            $this->destId = $dest_id;
        }

        // 检查关键字中是否含有景点名称;
        //现在要增加搜索半径
        $poi_dao = new Dao_HomeStay_Spot();
        if ($isKeywordSearch) {
            $loc_poi = $poi_dao->get_t_loc_poi('', null, $this->destId);
            foreach ($loc_poi as $k => $v) {
                if (trim($queryArgs['query']) == $v['poi_name']) {
                    $this->keyWords   = '';
                    $this->searchid   = $v['id'];
                    if($v['poi_type'] == 1) {
                        $searchType = "BUSINES_CIRCLE";
                    }
                    elseif($v['poi_type'] == 2) {
                        $searchType = "SCENIC_SPOTS";
                    }
                    elseif($v['poi_type'] == 3) {
                        $searchType = "SPORTVAN";
                    }
                    $this->searchType = $searchType;
                    $query = '';
                    $queryArgs['sight'] = $v['id'];
                    $queryArgs['locid'] = $v['locid'];
                    $queryArgs['search_radius'] = $v['search_radius'];
                    $isKeywordSearch = false;
                    break;
                }

                // Todo 还不知道这里是干嘛
                if (is_numeric(strpos($queryArgs['query'], $v['poi_name']))) {
                    $queryArgs['sight'] = $v['id'];
                }
            }
        }
        foreach($queryArgs['service'] as $v) {
            if(in_array($dest_id, array(10, 12)) && $v == 'translate') continue;
            $this->service[$v] = 1;
        }

        //区县过滤
        $filterLocId = 0;
        // 如果传入参数有区县ID, 则使用传入的区县ID做过滤搜索
        $locs = $poi_dao->get_t_loc_type($dest_id);
        if (isset($queryArgs['locid']) && (int)$queryArgs['locid'] > 99) {
            $filterLocId = $queryArgs['locid'];
            foreach ($locs as $loc) {
                if (preg_match("/" . $loc['type_name'] . "/", $queryArgs['query']) || $loc['locid'] == $queryArgs['locid']) {
                    $filterLocId = $loc['locid'];
                    $queryArgs['locid'] = $loc['locid'];
                    $this->searchType = "CITY";
                    $this->searchid   = $loc['locid'];
                }
            }
        } else if ($isKeywordSearch) {
            $queryArgs['locid'] = $queryArgs['locid'] < 99 ? '' : $queryArgs['locid'];
            // 如果没有传入区县过滤，则判断搜索关键词是否是区县名字，如果是则把关键词搜索变成按照区县ID过滤
            // $array_cityname = array();
            $matchLoc = array();
            foreach ($locs as $loc) {
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
                $this->searchType = "CITY";
                $this->searchid   = $matchLoc['locid'];
            }

        }
        $queryArgs['is_keyword_search'] = $isKeywordSearch;
        if($isKeywordSearch) {
            $this->keyWords = $queryArgs['query'];
        }
        $queryArgs['query'] = $this->keyWords;

        if($queryArgs['guest_num']) {
            $this->roomModel = $queryArgs['guest_num'];
        }


        //房型过滤
        if($queryArgs['model']) {
            $m = $poi_dao->get_t_room_model_byid($queryArgs['model']);
            if (isset($m['condtion'])) {
                if ($m['condtion'] != 'unlimited') {
                    if ((int)$m['condtion'] >= 5) {
//                        $filters[] = "room_model:[5 TO *]";
                        $this->roomModel = 5;
                    } else {
                        $this->roomModel = $m['condition'];
//                        $filters[] = "room_model:" . $m['condtion'];
                    }
                }
            }
        }

        if(isset($queryArgs['max_price']) && isset($queryArgs['min_price'])) {
            $interConfig = APF::get_instance()->get_config("internal_exchange", "area");
            $inter_exchange = $interConfig[Util_Currency::get_cy_id()];
            $range_max_price = round(5000 * $inter_exchange);

            $max_price = $queryArgs['max_price'];
            if($max_price >= $range_max_price)  {
                $max_price = 1000000;
            }
            $this->price = round($queryArgs['min_price']/$inter_exchange) . "," . round($max_price/$inter_exchange);
        }

        if($queryArgs['boutique']) {
            $this->service['isBoutiqueBnb'] = 1;
        }
        //房价过滤
        $pr = $poi_dao->get_t_room_price_byid($queryArgs['price']);
        if (isset($pr['condtion'])) {
            if ($pr['condtion'] != 'unlimited') {
                $this->price = $pr['lower_Limit'] . "," . $pr['upper_limit'];               
//                $filters[] = "int_price:[" . $pr['lower_Limit'] . " TO " . $pr['upper_limit'] . "]";
            }
        }
        //速订过滤
        if (isset($queryArgs['speed']) && $queryArgs['speed'] != 0) {
            $this->service['speed_room'] = 1;
//            $filters[] = "speed_room:1";
        }
        //服务过滤

        //房态过滤. 如果是关键词搜索，不过滤房态
        if (!empty($queryArgs['checkin_date']) && !empty($queryArgs['checkout_date'])) {
            $this->checkInDate   = $queryArgs['checkin_date'];
            $this->checkOutDate  = $queryArgs['checkout_date'];
        }


        $dates_qs = null;
        //促销活动
        if (isset($queryArgs['discount']) && $queryArgs['discount'] != 0) {
            $this->service["promotion"] = 1;
        }
        $dates_qs = null;
        if (!$queryArgs['order']) {
            $queryArgs['order'] = 1;
        }
        $this->order = $queryArgs['order'];

        $page_num = 0;
        if (!empty($queryArgs['page']) && (int)$queryArgs['page'] > 0) {
            $this->page = $queryArgs['page'] + 1;
        }

        if (isset($queryArgs['sight']) && $queryArgs['sight'] > 0) {
            $sight_data = reset($poi_dao->get_t_loc_poi('', $queryArgs['sight'], $queryArgs['dest_id']));
//print_r($sight_data);
            if($sight_data['poi_type'] == 1) {
                $searchType = "BUSINES_CIRCLE";
            }
            elseif($sight_data['poi_type'] == 2) {
                $searchType = "SCENIC_SPOTS";
            }
            elseif($sight_data['poi_type'] == 3) {
                $searchType = "SPORTVAN";
            }
            $this->searchType = $searchType;
            $this->searchid   = $sight_data['id'];
        }

        $this->filterArgs = $queryArgs;

    }

    public function search_url($type, $value) { 
        $page_args = $this->filterArgs;
        $page_args[$type] = $pv;

        $search_type = "room";
        if($page_args['is_keyword_search']) {
            $search_type = "search";
        }
        $search_key  = $page_args['query'];
        $search_path = array();
        $search_get  = array();

        $path_value  = array();
        foreach ($page_args as $key => $value) {
            if ($key == 'name_code' && !empty($value)) {
                $search_key = "/" . $value;
            }
            if ($key == 'model' && (int)$value > 0) {
                $path_value['a'] = $value;
            }
            if ($key == 'order' && (int)$value > 0) {
                $path_value['o'] = $value;
            }
            if ($key == 'speed' && (int)$value > 0) {
                $path_value['s'] = $value;
            }
            if ($key == 'discount' && (int)$value > 0) {
                $path_value['d'] = $value;
            }
            if ($key == 'boutique' && (int)$value > 0) {
                $path_value['u'] = $value;
            }
            if ($key == 'locid' && (int)$value > 0) {
                $path_value['c'] = $value;
            }
            if ($key == 'sight' && (int)$value > 0) {
                $path_value['i'] = $value;
            }
            if ($key == 'page' && (int)$value > 0) {
                $path_value['p'] = $value;
            }
        }
        foreach($path_value as $k=>$v) {
            $search_path[] = $k.$v;
        }
        $url_path = array(
            $search_type,$search_key,
            implode("-", $search_path),
        );
        $url = implode("/", $url_path);

        return $url;
    }

}
