<?php
/**
 * Created by PhpStorm.
 * User: chanlevel
 * Date: 16/5/24
 * Time: 上午11:22
 */

apf_require_class("APF_Controller");
apf_require_class("APF_Cache_Factory");


class Theme_GlobalController extends APF_Controller
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

    private static $currency, $multiprice, $multilang;

    public function handle_request()
    {
        // TODO: Implement handle_request() method.

        $params = Apf::get_instance()->get_request()->get_parameters();
//for test off
        // $this->get_off__home_by_uid(2522, 395);


        $bll_area = new Bll_Area_Area();
        self:: $multiprice = empty($params['multiprice']) ? 12 : $params['multiprice'];
        $area = $bll_area->get_dest_config_by_destid(self::$multiprice);
        self::$currency = $area['currency_code'];

        self::$multilang = empty($params['multilang']) ? 12 : $params['multilang'];
        Util_Language::set_locale_id(self::$multilang);

        $this->init_data($params);
        //  $this->fake_data();
    }


    public function fake_data()
    {

        $hot = array();


        $a = Theme_MultiThemeController::get_homestays(array(66));


        $hot[] = $a['data'][0];
        $hot[0]['home']['destDesc'] = '上海';


        $hot[] = $this->get_fake_hot_theme();
        $hot[] = $this->get_fake_hot_service();
        $hot[] = $this->get_fake_hot_activity();
        $hot[] = $this->get_fake_city();
        $promo = $this->get_fake_promo();


        $d = Theme_MultiThemeController::get_horiads();


        $banner[] = $d['data'][0];
        $banner[] = $d['data'][1];


        $result = array(
            'banner' => $banner,

            'destination' => $this->get_destination_all(),

            'offnow' => $this->get_off_now(),

            "hot" => $hot,

            'promo' => $promo

        );
        Util_Json::render(200, $result);
    }


    public function init_data($params)
    {

        $lang_id = $params['multilang'];
        if (empty($lang_id)) $lang_id = 12;

        $cache_key = __CLASS__ . $lang_id . '_' . $_GET['os'] . '_' . $_GET['version'].'_'.self::$multiprice;
        $cache_key .= '_d';//    special string, u can change it every time u wanna change the cache
        $cache_key = md5($cache_key);

        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
//        $cache = $memcache->get($cache_key);
        if (!empty($cache)) {
            Util_Json::render(200, $cache);
            return;
        }

        $data = $this->get_data_by_lang($lang_id);

        $result = $this->get_result_by_data($data);

        $result['destination'] = $this->get_destination_all();

        $memcache->set($cache_key, $result, 0, (24*3600));

        Util_Json::render(200, $result);

    }


    public function  get_data_by_lang($lang_id)
    {

        $bll_theme = new Bll_Theme_ThemeInfo();
        $result = $bll_theme->get_data_by_lang($lang_id);

        //  ugly  完全知识为了 能work
        $theme_list = $this->get_theme_from_t_app_theme();

        foreach ($theme_list as $k => $v) {
            $result[] = array(
                'title' => $v['name'],
                'img_url' => $v['img_url'],
                'ext' => json_encode(array('theme_id' => $v['id'])),
                'type' => 'theme',
                'category' => 'hot',
                'id' => 'theme_' . $v['id'],
                'priority' => $v['delta'],
                'multilang' => $v['multilang']
            );
        }
        usort($result, function ($a, $b) {
            return $a['priority'] < $b['priority'] ? -1 : 1;
        });

        $timelist = array_column($result, 'update_time');
        $max = max($timelist);
        foreach ($result as $k => $v) {
            $ids = $ids . '_' . $v['id'];
        }

        return $result;

    }


    public function get_destination_all()
    {
        $destination = array();

        if(self::$multilang == 13) {
            $destination[] = $this->get_destination(10, 'http://static.zzkcdn.com/global/taiwan.jpg');
            $destination[] = $this->get_destination(11, "http://static.zzkcdn.com/global/japan.jpg");
            $destination[] = $this->get_destination(12, 'http://static.zzkcdn.com/global/China.jpg');
            $destination[] = $this->get_destination(15, 'http://static.zzkcdn.com/global/Korea.jpg');
        }else{
            if (self::$multilang == 12) {
                $destination[] = $this->destinatinon_areamore('10', '台湾', 'http://static.zzkcdn.com/global_tw.png', 'CITY');
//                $destination[] = $this->destinatinon_areamore(11, '日本', 'http://static.zzkcdn.com/global_jp.png');
//                $destination[] = $this->destinatinon_areamore(12, '大陆', 'http://static.zzkcdn.com/global_dl.png');
//                $destination[] = $this->destinatinon_areamore(15, '韩国', 'http://static.zzkcdn.com/global_kr.png');

            } else {
                $destination[] = $this->get_destination(10, "http://static.zzkcdn.com/global_tw.png");
            }
            $destination[] = $this->get_destination(11, 'http://static.zzkcdn.com/global_jp.png');
            $destination[] = $this->get_destination(12, 'http://static.zzkcdn.com/dalupic.jpg');
            $destination[] = $this->get_destination(15, 'http://static.zzkcdn.com/hanguo.jpg');
        }


        return $destination;
    }


    public function get_result_by_data($data)
    {

        foreach ($data as $k => $v) {
            $ext = json_decode($v['ext'], true);
            $detail = array();
            $detail['title'] = $v['title'];
            $detail['image'] = $v['img_url'];
            switch ($v['type']) {
                case 'homestay':
                    $homestay_uid = $ext['homestay_uid'];

                    if ($v['category'] == 'offnow') {
                        $nid = $ext['nid'];

                        $home = Theme_GlobalController::get_off__home_by_uid($homestay_uid, $nid, $ext['start'], $ext['end']);
                        // 如果  限时特惠实际没有折扣  unset
                        if (empty($home['home']['promo'])) {
                            unset($data[$k]);
                            break;
                        }

                    } else $home = Theme_GlobalController::get_hot_home_by_uid($homestay_uid);

                    if (!$home) {
                        unset($data[$k]);
                        break;
                    }

                    // 数据库中未设置展示图片则取民宿默认
                    if (empty($detail['image'])) $detail['image'] = $home['home']['image'];
                    if (empty($detail['title'])) $detail['title'] = $home['home']['title'];
                    if ($v['category'] == 'banner') $detail['title'] = '';

                    $data[$k]['detail'] = array_merge($detail, $home);
                    break;
                case 'activity':
                case 'webview':

                    $url = $ext['url'];
                    $detail = $this->get_hot_activity($v['title'], $v['img_url'], $url);

                    $detail['type'] = $v['type'];

                    $data[$k]['detail'] = $detail;
                    break;

                case 'service':
                    $service_id = $ext['service_id'];
                    //避免重复
                    if (in_array($service_id, $service_id_list)) {
                        break;
                    }
                    $service_id_list[] = $service_id;

                    $detail = $this->get_service($detail['title'], $detail['image'], $service_id);
                    if ($v['category'] == 'banner') $detail['title'] = '';

                    if (!empty($detail))
                        $data[$k]['detail'] = $detail;
                    break;
                case 'theme':
                    $theme_id = $ext['theme_id'];
                    //避免重复的主题
                    if (in_array($theme_id, $theme_id_arr)) {
                        break;
                    }
                    $theme_id_arr[] = $theme_id;


                    $dao_theme = new Dao_Theme_ThemeInfo();
                    $result = $dao_theme->get_theme_by_id($theme_id);
                    if ($result) {
                        if (!empty($result['name'])) $detail['title'] = $result['name'];
                        if (!empty($result['img_url'])) $detail['image'] = $result['img_url'];
                    }


                    $type = empty($ext['type']) ? 0 : $ext['type'];
                    if (self::$multilang == 10) $type = 1;
                    $app_theme_id = $v['multilang'] . '00' . $theme_id;

                    $data[$k]['detail'] = Theme_MultiThemeController::get_theme_object($app_theme_id, $detail['title'], $detail['image'], 0, null, $type, $theme_id, $v['category'] == 'banner');
                    $data[$k]['detail']['type'] = 'theme';
                    break;
                case 'bigcity':
                    $name_code = $ext['name_code'];
                    $search_type = empty($ext['search_type']) ? "CITY" : $ext['search_type'];
                    $data[$k]['detail'] = Theme_MultiThemeController::get_city_object($name_code, $v['title'], $v['img_url'], $search_type);
                    break;

            }
        }

        // 自定义在这里添加
        if (self::$multilang == 12) {
            $banner[] = array(
                'title' => "",
                'image' => 'http://static.zzkcdn.com/mobile/app/theme/zzc_banner.jpg',
                'type' => 'webview',
                'android' => array(
                    'target' => 'com.youyao.taiwanlodge.WebView_Activity',
                    'bundle' => array(
                        'url' => 'http://starter.youyao.com?',
                    ),
                ),
                'ios' => array(
                    'target' => 'WebViewController',
                    'bundle' => array(
                        'url' => 'http://starter.youyao.com?',
                    ),
                    'storyboard' => 1,
                ),
            );
            if($_REQUEST['mobile_userid']) {
            $banner[] = array(
                'title' => "",
                'image' => 'http://static.zzkcdn.com/mobile/app/theme/jifen_banner.jpg',
                'type' => 'webview',
                'android' => array(
                    'target' => 'com.youyao.taiwanlodge.WebView_Activity',
                    'bundle' => array(
                        'url' => 'http://m.youyao.com/user/point/index?',
                    ),
                ),
                'ios' => array(
                    'target' => 'WebViewController',
                    'bundle' => array(
                        'url' => 'http://m.youyao.com/user/point/index?',
                    ),
                    'storyboard' => 1,
                ),
            );
            }
        }

        foreach ($data as $k => $v) {
            if (empty($v['detail'])) continue;

            switch ($v['category']) {
                case 'banner':
                    $v['detail']['title']='';
                    $banner[] = $v['detail'];
                    break;
                case 'hot':
                    // 国际版需区分hot
                    if(self::$multilang == 13) {
                        if($v['type'] == 'service') {
                            $hotService[] = $v['detail'];
                        }else{
                            $hotHomestay[] = $v['detail'];
                        }
                    }else{
                        $hot[] = $v['detail'];
                    }
                    break;
                case 'promo':
                    $promo[] = $v['detail'];
                    break;
                case 'offnow':
                    $offnow[] = $v['detail'];

            }
        }


        // 自定义在这里添加
        if (self::$multilang == 12) {
        }
// 放一张默认的....  todo
        if (empty($banner)) {
            $banner[] = $this->get_fake_hot_activity();
        }

        $result = array(
            'offnow' => $offnow,
            'banner' => $banner,
            'hot' => $hot,
            'promo' => $promo
        );
        // 国际版需区分hot
        if(self::$multilang==13){
            unset($result['hot']);
            $result['hotHomestay'] = $hotHomestay;
            $result['hotService'] = $hotService;
        }

        return $result;

    }

// 为了运营人员可以快速 设置,  做了 兼容方案   只是 只能放在hot 里面
    private function get_theme_from_t_app_theme()
    {
        $dao_theme = new Dao_Theme_ThemeInfo();
        $result = $dao_theme->get_theme_by_dest_id(1, self::$multilang);
        return $result;

    }


    public static function get_service($title, $img, $serviceid)
    {
        if ($_REQUEST['os'] == 'android' && $_REQUEST['version'] <= 92) {
            return false;
        }
        if ($_REQUEST['os'] == 'ios' && version_compare($_REQUEST['version'], '5.0.8', '<=')) {
            return false;
        }

        if (empty($title) || empty($img)) {
            $bll_home = new Bll_Homestay_StayInfo();
            if (empty($title)) {
                $info = $bll_home->get_other_service_by_id($serviceid);
                if (empty($info)) return false;
                $item = $info[0];
                if ($item['category'] == 'unset')
                    $title = $item['service_name'];
                else $title = $item['title'];
            }

            $imgs = $bll_home->get_other_service_images_byids($serviceid);
            $temp = array_values($imgs[$serviceid]);
            $img = Util_Image::imglink($temp[0], 'homepic800x600.jpg');

        }

        if (empty($img) || empty($title)) return false;

        return array_merge(Push_Pusher::service_recommend_push($serviceid)
            , array(
                'title' => $title,
                'image' => $img,
                'type' => 'service'
            ));

    }

    public static function get_destination($destid, $img)
    {

        $area = new Bll_Area_Area();
        $result = $area->get_dest_config_by_destid($destid);


        $dest_name = Trans::t($result['domain']);
        return array(
            'title' => $dest_name,
            'image' => $img,
            'android' => array(
                'target' => "com.youyao.taiwanlodge.mainlist.DestinationActivity",
                'bundle' => array(
                    'DEST_ID' => $destid,
                    'DEST_NAME' => $dest_name
                )
            ),
            'ios' => array(
                'target' => 'SecondFontPageController',
                'storyboard' => 0,
                'bundle' => array(
                    'destid' => $destid,
                    'destname' => $dest_name
                )
            )
        );
    }


    private static function destinatinon_areamore($namecode, $title, $img_url, $searchType='CITY')
    {
        if ($_GET['os'] == 'android' && $_GET['version'] < 81) {
            return Theme_MultiThemeController::get_city_object($namecode, $title, $img_url, $searchType);
        }

        if ($_GET['os'] == 'android' && $_GET['version'] < 94) {

            return array(
                'type' => 'custom',
                'image' => $img_url,
                'title' => $title,
                'android' => array(
                    'target' => 'com.youyao.taiwanlodge.search.SearchMainActivity',
                    'bundle' => array(
                        'SHOW_PROM_VIEW' => 0,
                        'SHOW_BACK_ICON' => 1

                    )

                ),
                'ios' => array(
                    'target' => 'NewSearchController',
                    'bundle' => array(
                        'isHaveNavigation' => 1,
                        'isHaveTopic'=>1,
                        'destid' => $namecode
                    ),
                    'storyboard' => 1,
                ),


            );

        }
            if ($_GET['os'] == 'ios' && version_compare($_GET['version'], '5.0.8', '<')) {
            return Theme_MultiThemeController::get_city_object($namecode, $title, $img_url, $searchType);
        }

        return array(
            'type' => 'custom',
            'image' => $img_url,
            'title' => $title,
            'android' => array(
                'target' => 'com.youyao.taiwanlodge.search.DestSearchMainActivity',
                'bundle' => array(
                    'SHOW_PROM_VIEW' => 0,
                    'SHOW_BACK_ICON' => 1,
                    'DEST_ID'=>$namecode

                )

            ),
            'ios' => array(
                'target' => 'NewSearchController',
                'bundle' => array(
                    'isHaveNavigation' => 1,
                    'isHaveTopic'=>1,
                    'destid' => $namecode
                ),
                'storyboard' => 1,
            ),


        );
    }

    public static function get_hot_activity($title, $image, $url)
    {

        $result = Theme_MultiThemeController::webview_format($title, $image, $url);
        $result['type'] = 'activity';
        $result['title'] = $title;
        $result['image'] = $image;
        return $result;
    }


    public static function get_fake_hot_activity()
    {

        $r = Theme_MultiThemeController::webview_format('', 'http://static.zzkcdn.com/xindexuyuanbanner.jpg', 'http://m.youyao.com/activity/wish');
        $result = $r;
        $result['type'] = 'activity';
        return $result;
    }

    public static function get_fake_city()
    {
        $r = Theme_MultiThemeController::get_city_object('taibei', '去台北  的彩虹桥吹吹风', 'http://static.zzkcdn.com/app/theme/taibei-2.jpg-800x600.jpg', 'CITY');
        return $r;
    }


    public static function get_fake_hot_theme()
    {

        $theme_bll = new Bll_Theme_ThemeInfo();
        $results = $theme_bll->get_theme_list_by_dest_id(15);
        $first_theme = array_shift($results);
        $r = Theme_MultiThemeController::get_themes(array($first_theme));
        $result = $r['data'][0];
        $result['type'] = 'theme';
        return $result;
    }


    public static function  get_fake_hot_service()
    {
        return array(
            'image' => 'https://d13yacurqjgara.cloudfront.net/users/518045/screenshots/2733518/aisberg_2_1x.png',
            'type' => 'service',
            'title' => '8p'

        );


    }

    public static function  get_fake_promo()
    {

        $promo[] = Theme_MultiThemeController::webview_format(
            '邀请好友',
            'http://static.zzkcdn.com/globalfcbanner.png',
            'http://m.youyao.com/fcode/index?uid=' . $_REQUEST['mobile_userid']
        );


        return $promo;
    }


    public static function get_off_now()
    {

//        $ll = Theme_MultiThemeController::get_homestays(array(66, 40080, 57638));
//        $homes = $ll['data'];
//
//        foreach ($homes as $k => $v) {
//
//            $homes[$k]['home']['destDesc'] = '上海';
//            $smallprice = $v['home']['price'];
//            $homes[$k]['home']['promo'] = array(
//                'bigprice' => 2 * $smallprice,
//                'smallprice' => $smallprice,
//                'name' => 'huihuihui',
//                'start' => date('m-d'),
//                'end' => date('m-d', time() + 10 * 24 * 3600)
//            );
//        }
//

        $off[] = Theme_GlobalController::get_off__home_by_uid(66, 395);

        $off[] = Theme_GlobalController::get_off__home_by_uid(57638, 23818);

        $off[] = Theme_GlobalController::get_off__home_by_uid(1320, 10709);

        return $off;
    }


    public function get_hot_home_by_uid($uid)
    {
        $home = Theme_GlobalController::get_base_home_by_uid($uid);
        if (!$home) return false;
        $result['home'] = $home;
        return array_merge($result, Push_Pusher::homestay_recomend_push($uid));

    }

    public static function  get_base_home_by_uid($uid)
    {
        $bll_home = new Bll_Homestay_StayInfo();
        $home = $bll_home->get_homestay_by_id($uid);
        if (empty($home)) return false;

        $price = Util_Common::zzk_cn_price_convert($home->int_room_price, self::$multiprice);;


        $region = Trans::t(end($home->location_transkey_ss));
        if(!empty($region)){
            $region = end($home->location_typename);
        }
        $result['region'] = empty($region) ? $home->loc_typename : $region;

        return array(
            'speed_room' => $home->hs_speed_room_i,
            'homestay_uid' => $uid,
            'price' => $price,
            'address' => $home->address,
            'currency_sym' => self:: $currency,
            'title' => $home->username,
            'image' => Util_Image::get_homestay_image($uid),
            'destDesc' => $region
        );

    }


    public static function  get_off__home_by_uid($uid, $nid, $start = null, $end = null)
    {

        $home = Theme_GlobalController::get_base_home_by_uid($uid);
        if (!$home) return false;

        if ($nid) {// 已经设置房间的 通过房间查询
            $promo = Theme_GlobalController::get_off_by_nid($nid, $start, $end);
        } else {

            $dao_home = new Dao_HomeStay_Stay();
            $rooms = $dao_home->get_rooms_by_homeid($uid);

            if (empty($rooms)) return false;

            foreach ($rooms as $room) {
                $nid = $room['nid'];
                $promo = Theme_GlobalController::get_off_by_nid($nid, $start, $end);
                if ($promo) break;
            }
        }

        $home['promo'] = empty($promo) ? null : $promo;
        $result['home'] = $home;
        return array_merge($result, Push_Pusher::homestay_recomend_push($uid));
    }


    public static function get_off_by_nid($nid, $start_date = null, $end_date = null)
    {
        if (empty($start_date)) $start_date = date('Y-m-d');
        if (empty($end_date)) $end_date = date('Y-m-d', time() + 90 * 24 * 3600);

        $bll_disc = new Bll_Disc_Info();

        $disc_info = $bll_disc->get_disc_info($nid);
        $_disc = $disc_info['disc'];
        if (empty($_disc) && $_disc >= 1) return false;
//echo json_encode($disc_info);exit;
        $has_disc = false;

        $bll_static = new Bll_Room_Static();
        if(self::$multilang == 13) {
            $disc_name = (1 - $_disc) * 100 . '%OFF';
        }else{
            $disc_name = (10 * $_disc) . '折';
        }
        if (empty($disc_info['date'])) {
            //永久打折
            $lowest_price = $bll_static->get_lowest_room_price(null, null, $nid, false, self:: $multiprice);

        } else {
            //判断 今天之后的折扣
            $disc_info_date = $disc_info['date'];
            foreach ($disc_info_date as $k => $v) {
                $_st = $v['start_date'];
                $_en = $v['end_date'];

                if (strtotime($start_date) > strtotime($_st) && strtotime($start_date) < strtotime($_en)) {
                    //  $start_date = $_st;
                    $has_disc = true;
                    $end_date = $_en;
                    break;
                }
            }
            if (!$has_disc) return false;
//            $start_date = $disc_info['date'][0]['start_date'];
//            $end_date = $disc_info['date'][0]['end_date'];

            $lowest_price = $bll_static->get_lowest_room_price($start_date, $end_date, $nid, false, self::$multiprice);

        }

        if (strtotime($start_date) > time()) {
            $start = date('m.d', strtotime($start_date));
        } else $start = date('m.d');
        $end = date('m.d', strtotime($end_date));


        $r = array(
            'bigprice' => intval($lowest_price),
            'smallprice' => intval($lowest_price * $_disc),
            'name' => $disc_name,
            'start' => $start,
            'end' => $end
        );

        return $r;

    }


}


