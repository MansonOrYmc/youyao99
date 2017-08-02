<?php
apf_require_class("APF_Controller");

class Theme_RecommController extends APF_Controller
{

    public function handle_request()
    {

        header("Content-type: application/json");

        $req = APF::get_instance()->get_request();
        $params = $req->get_parameters();

        $data = self::warpInitData();
        $action = $params['action'];
        if ($action == 'home') {
            $results = array();
            $ad = array();
            $promo = array();
            $horiad = array();
            $destid = $params['destid'];
            $os = $params['os'];
            $version = $params['version'];
            $uid = $params['uid'];
            if ($destid == 10) {
                if (Util_Activity::is_firstappdiscount_available()) {
                    if (($os == 'ios' && $version < 4.6 && $version > 4.4) || ($os == 'android' && $version < 48 && $version > 44)) {
                        if ($os == 'ios') {
                            $firstAppImage = 'http://static.zzkcdn.com/iosbanner-800X200.jpg';
                        } else {
                            $firstAppImage = 'http://static.zzkcdn.com/SDDK-android-700X100-20150721.JPG';
                        }

                        $banner5 = array(
                            'image' => $firstAppImage,
                            'title' => 'APP首笔优惠',
                            'type' => '1',
                            'url' => 'http://m.youyao.com/activity/tos/',

                        );
                        array_push($ad, $banner5);

                    }
                }

                foreach ($data[0] as $value) {
                    $theme = array(
                        'themeId' => $value['themeId'],
                        'themeName' => $value['themeName'],
                        'themePic' => $value['themePic'],
                        'homestayNum' => count($data[1][$value['themeId']]),
                    );
                    if ($value['themeId'] == 3) {
                        $theme['homestayNum'] = 0;
                    }
//                    array_push($results, $theme);
                }
                $theme_bll = new Bll_Theme_ThemeInfo();
                $results = $theme_bll->get_theme_list_by_dest_id($destid);

                $promo = $this->get_promo($params);

            } elseif ($destid == 11) {
                $promo1 = array(
                    'title' => '京都 日本的心灵之乡',
                    'image' => 'http://static.zzkcdn.com/promos_japan_jingdu.jpg-800x600.jpg',
                    'type' => 'destination',
                    'androidtarget' => 'com.youyao.taiwanlodge.search.SearchResultActivity',
                    'namecode' => 'jingdou',
                    'bundle' => array(
                        'namecode' => 'jingdou',
                    ),
                );
                $promo3 = array(
                    'title' => '食在大阪',
                    'image' => 'http://static.zzkcdn.com/promos_japan_daban.jpg-800x600.jpg',
                    'type' => 'destination',
                    'androidtarget' => 'com.youyao.taiwanlodge.search.SearchResultActivity',
                    'namecode' => 'daban',
                    'bundle' => array(
                        'namecode' => 'daban',
                    ),
                );
                if (($os == 'ios' && $version >= 4.3) || ($os == 'android' && $version > 41)) {
                    $promo = array_merge($promo, array(
                        $promo1,
                        $promo3,
                        array(
                            'title' => '人人都爱北海道',
                            'image' => 'http://static.zzkcdn.com/app/promo/bhd.png-800x600.jpg',
                            'type' => 'destination',
                            'androidtarget' => 'com.youyao.taiwanlodge.search.SearchResultActivity',
                            'namecode' => 'beihaidao',
                            'bundle' => array(
                                'namecode' => 'beihaidao',
                            ),
                        ),
                        array(
                            'title' => '',
                            'image' => 'http://static.zzkcdn.com/app/promo/chf2.jpg-800x600.jpg',
                            'androidtarget' => 'com.youyao.taiwanlodge.WebViewActivity_',
                            'type' => 'webview',
                            'url' => 'https://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=909572018&idx=1&sn=34bd96803d76709ac96fefb92ad301bd&scene=1&srcid=1223cDGpDeOyPcmlxU6nMbBj&key=62bb001fdbc364e5e92537d8d62fa798846a78fbb1fd2271eb4fc4328c714880f742d7ab44c2e7e87b1353bec0db2b38&ascene=0&uin=Mzg5Nzk0MDM1&devicetype=iMac+MacBookPro12%2C1+OSX+OSX+10.10.4+build(14E46)&version=11020012&pass_ticket=Aj854Z4KrEzGwnXV3g5L9XTuc67MZC9uYxqvqRrOTDiqih%2Fs2n4bA8DGiy6FjywC',
                        ),
                        array(
                            'title' => '',
                            'image' => 'http://static.zzkcdn.com/app/promo/wqlg2.jpg-800x600.jpg',
                            'androidtarget' => 'com.youyao.taiwanlodge.WebViewActivity_',
                            'type' => 'webview',
                            'url' => 'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=818418800&idx=1&sn=25efdd05564374f420082a9c38365040&scene=21#wechat_redirect',
                        ),
                    ));
                }
                $theme_bll = new Bll_Theme_ThemeInfo();
                $results = $theme_bll->get_theme_list_by_dest_id($destid);
            } elseif ($destid == 12) {
                $promo1 = array(
                    'title' => '莫干山冬季野奢',
                    'image' => 'http://static.zzkcdn.com/app/theme/moganshan2.jpg-800x600.jpg',
                    'type' => 'destination',
                    'androidtarget' => 'com.youyao.taiwanlodge.search.SearchResultActivity',
                    'namecode' => 'moganshan',
                    'bundle' => array(
                        'namecode' => 'moganshan',
                    ),
                );
                $promo2 = array(
                    'title' => '从化温泉季',
                    'image' => 'http://static.zzkcdn.com/app/theme/wenquan3.jpg-800x600.jpg',
                    'type' => 'destination',
                    'androidtarget' => 'com.youyao.taiwanlodge.search.SearchResultActivity',
                    'namecode' => 'conghua',
                    'bundle' => array(
                        'namecode' => 'conghua',
                    ),
                );
                if (($os == 'ios' && $version >= 4.3) || ($os == 'android' && $version > 41)) {
                    array_push($promo, $promo1);
                    array_push($promo, $promo2);
                    $promo = array_merge($promo, array(
                        array(
                            'title' => '',
                            'image' => 'http://static.zzkcdn.com/app/theme/seaside2.jpg-800x600.jpg',
                            'androidtarget' => 'com.youyao.taiwanlodge.WebViewActivity_',
                            'type' => 'webview',
                            'url' => 'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=904970569&idx=1&sn=a2230307080d26e061faafcbfec029ed#rd/?campaign_code=edm_zh',
                        ),
                    ));
                }
                $theme_bll = new Bll_Theme_ThemeInfo();
                $results = $theme_bll->get_theme_list_by_dest_id($destid);
            } elseif ($destid == 15) {
                $city_arr = array(
                    'mingdong' => array(
                        'title' => '明洞 · 时尚购物之都',
                        'image' => 'http://static.zzkcdn.com/app/promo/mingdong.jpg-800x600.jpg',
                    ),
                    'hongda' => array(
                        'title' => ' ',
                        'image' => 'http://static.zzkcdn.com/app/promo/hd.jpg-800x600.jpg',
                    ),
                );
                foreach ($city_arr as $name_code => $city) {
                    $data = array(
                        'title' => $city['title'],
                        'image' => $city['image'],
                        'type' => 'destination',
                        'androidtarget' => 'com.youyao.taiwanlodge.search.SearchResultActivity',
                        'namecode' => $name_code,
                        'bundle' => array(
                            'namecode' => $name_code,
                        ),
                    );
                    array_push($promo, $data);
                }
                $promo = array_merge($promo, array(
                    array(
                        'title' => '',
                        'image' => 'http://static.zzkcdn.com/app/promo/hw3.jpg-800x600.jpg',
                        'androidtarget' => 'com.youyao.taiwanlodge.WebViewActivity_',
                        'type' => 'webview',
                        'url' => 'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=897186506&idx=1&sn=d32576f9e1e2cabbd387331719628cb5&scene=1&srcid=1224bAn81EJtzB0yEEPh6v2R&from=singlemessage&isappinstalled=0',
                    ),
                    array(
                        'title' => '',
                        'image' => 'http://static.zzkcdn.com/app/promo/jzd2.jpg-800x600.jpg',
                        'androidtarget' => 'com.youyao.taiwanlodge.WebViewActivity_',
                        'type' => 'webview',
                        'url' => 'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=814524336&idx=1&sn=fc041c29b2aa3e6187eba4283e25d125&scene=1&srcid=1224ChPuWsrU8NQauagdqb0I&from=singlemessage&isappinstalled=0',
                    ),
                ));
                $theme_bll = new Bll_Theme_ThemeInfo();
                $results = $theme_bll->get_theme_list_by_dest_id($destid);
            }

            $recomm_stay = $this->get_recomm_homestay($destid);

            //  $custom=$this->get_custom($params['os']);
            //  $destination=$this->get_dest();

            foreach ($promo as $key => $value) {

                if ($value['type'] == 'destination') {
                    if (empty($value['searchType'])) {
                        $promo[$key]['searchType'] = 'CITY';
                    }
                    if (empty($value['locid'])) {
                        $promo[$key]['locid'] = $this->getlocidbynamecode($value['namecode']);
                    }

                    $promo[$key]['bundle']['locid'] = $promo[$key]['locid'];
                }
            }

            $horiad = $this->get_horiad($params);

            $returnResult = array(
                'code' => 1,
                'codeMsg' => '',
                'body' => array(
                    'horiad' => $horiad,
                    'promo' => $promo,
                    'ad' => $ad,
                    'theme' => $results,
                    'recomm' => $recomm_stay,
                ),
            );

            Util_ZzkCommon::zzk_echo(json_encode($returnResult));
        } elseif ($action == 'item') {
            $theme_id = isset($params['theme_id']) ? $params['theme_id'] : '';
            $returnJSON = array('code' => 0, 'codeMsg' => '');
            if (strlen($theme_id) <= 0) {
                $returnJSON['codeMsg'] = 'theme_id为必填项!';
                Util_ZzkCommon::zzk_echo(json_encode(Util_Beauty::wanna($returnJSON)));
                return false;
            }

            if (strpos($theme_id, '00') === false) {
                self::handleItemTheme($theme_id, $data);
            } else {
                $this->theme_list($theme_id);
            }
        }

        return false;
    }

    private function theme_list($theme_id_str)
    {
        list($dest_id, $theme_id) = explode('00', $theme_id_str);
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        if ($_GET['multiprice'] == 10) {
            $type = 'rmb';
        } else {
            $type = 'twd';
        }
        $response = $memcache->get('app_theme' . $theme_id . $type);
        if (empty($response)) {
            $bll_themeInfo = new Bll_Theme_ThemeInfo();
            $itemTheme = $bll_themeInfo->get_homestay_by_theme_id($theme_id);
            $itemInfo = $bll_themeInfo->acquire_theme_info_by_data($itemTheme);
            $response = array();
            foreach ($itemTheme as $item) {
                $response[] = $itemInfo[$item['homeId']];
            }
            $memcache->set('app_theme' . $theme_id . $type, $response, MEMCACHE_COMPRESSED, 3600);
        }
        if ($response) {
            $returnJSON['code'] = 1;
            $returnJSON['codeMsg'] = '操作成功!';
        } else {
            $returnJSON['code'] = 0;
            $returnJSON['codeMsg'] = '操作数据库失败!';
        }
        $returnJSON['body'] = $response;
        header('Content-Type:application/json');
        if ($_REQUEST["multilang"] == 10) {
            $response_json_str = json_encode($returnJSON);
            $response_json_str = preg_replace_callback('/(?<!\\\\)\\\\u(\w{4})/', function ($matches) {
                return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
            }, $response_json_str);
            $url = 'http://api.prod.youyao.com/2.0/common/translate?langue=ZH_TW';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $response_json_str);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
            $translated_result = curl_exec($ch);
            $translated_result = json_decode($translated_result, true);
            if ($translated_result['code'] == 200) {
                echo json_encode(Util_Beauty::wanna(json_decode($translated_result['info'], true)));
            }
        } else {
            echo json_encode(Util_Beauty::wanna($returnJSON));
        }
    }

    private function getlocidbynamecode($namecode)
    {
        $bll_area = new Bll_Area_Area();
        return $bll_area->get_locid_by_namecode($namecode);
    }

    private function handleItemTheme($theme_id, $data)
    {
        $itemTheme = $data[1][$theme_id];
        $bll_themeInfo = new Bll_Theme_ThemeInfo();
        $itemInfo = $bll_themeInfo->acquire_theme_info_by_data($itemTheme);
        foreach ($itemTheme as $item) {
            if ($theme_id == 10) {
                $itemInfo[$item['homeId']]['cover'] = strtr($itemInfo[$item['homeId']]['cover'], array('-roompic.jpg' => '-mobilePromo.jpg'));
            }
            $response[] = $itemInfo[$item['homeId']];
        }
        if ($response) {
            $returnJSON['code'] = 1;
            $returnJSON['codeMsg'] = '操作成功!';
        } else {
            $returnJSON['code'] = 0;
            $returnJSON['codeMsg'] = '操作数据库失败!';
        }
        $returnJSON['body'] = $response;
        Util_ZzkCommon::zzk_echo(json_encode(Util_Beauty::wanna($returnJSON)));
        return true;
    }

    public function warpInitData()
    {
        $theme10 = array(
            'themeId' => '10',
            'themeName' => '感恩·特惠',
            'themePic' => 'http://static.zzkcdn.com/app/theme/zzk_discount_room6.jpg-800x600.jpg',
        );

        $themes = array(
            $theme10,
            array(
                'themeId' => 14,
                'themeName' => '春节  感受他乡的年味',
                'themePic' => 'http://static.zzkcdn.com/app/theme/14.jpg',
            ),
            array(
                'themeId' => 12,
                'themeName' => '摄影师眼中的精致民宿',
                'themePic' => 'http://static.zzkcdn.com/app/theme/12.jpg',
            ),
            array(
                'themeId' => 11,
                'themeName' => '微小  但确切的幸福',
                'themePic' => 'http://static.zzkcdn.com/app/theme/11-3.jpg',
            ),
            array(
                'themeId' => 13,
                'themeName' => '宝贝们最爱的民宿',
                'themePic' => 'http://static.zzkcdn.com/app/theme/13.jpg',
            ),
        );

        return array(
            $themes,
            array(
                '10' => array(
                    array('homeId' => 372497),
                    array('homeId' => 1200),
                    array('homeId' => 148212),
                    array('homeId' => 19708),
                    array('homeId' => 355051),
                    array('homeId' => 286280),
                ),
                11 => array(
                    array('homeId' => 53484),
                    array('homeId' => 38517),
                    array('homeId' => 58192),
                    array('homeId' => 58467),
                    array('homeId' => 54968),
                    array('homeId' => 196824),
                    array('homeId' => 274880),
                    array('homeId' => 54968),
                    array('homeId' => 325034),
                    array('homeId' => 359849),
                    array('homeId' => 355336),
                ),
                12 => array(
                    array('homeId' => 531),
                    array('homeId' => 324894),
                    array('homeId' => 83329),
                    array('homeId' => 96111),
                    array('homeId' => 18972),
                    array('homeId' => 11430),
                    array('homeId' => 5232),
                    array('homeId' => 8371),
                    array('homeId' => 14183),
                    array('homeId' => 89839),
                    array('homeId' => 261024),
                    array('homeId' => 134126),
                    array('homeId' => 1351),
                    array('homeId' => 20771),
                    array('homeId' => 532),
                    array('homeId' => 124),
                ),
                13 => array(
                    array('homeId' => 1022),
                    array('homeId' => 1023),
                    array('homeId' => 37086),
                    array('homeId' => 2522),
                    array('homeId' => 4825),
                    array('homeId' => 9123),
                    array('homeId' => 17624),
                    array('homeId' => 7607),
                    array('homeId' => 14595),
                    array('homeId' => 14975),
                    array('homeId' => 795),
                    array('homeId' => 4209),
                    array('homeId' => 1600),
                    array('homeId' => 4232),
                ),
                14 => array(
                    array('homeId' => 10341),
                    array('homeId' => 30906),
                    array('homeId' => 93483),
                    array('homeId' => 35),
                    array('homeId' => 644),
                    array('homeId' => 383),
                    array('homeId' => 493),
                    array('homeId' => 45875),
                    array('homeId' => 2989),
                    array('homeId' => 105892),
                    array('homeId' => 148212),
                    array('homeId' => 141455),
                    array('homeId' => 38403),
                    array('homeId' => 14860),
                    array('homeId' => 70596),
                    array('homeId' => 75956),
                    array('homeId' => 119630),
                    array('homeId' => 54968),
                    array('homeId' => 9405),
                    array('homeId' => 105747),
                ),
            ),
        );
    }

    public function get_recomm_homestay($destid)
    {
        $recomm = array();
        if ($destid == 12) {
            $homestay_id_arr = array(
                array('homeId' => 366996),
                array('homeId' => 360422),
                array('homeId' => 323198),
            );
            $bll_themeInfo = new Bll_Theme_ThemeInfo();
            $homestay_arr = $bll_themeInfo->acquire_theme_info_by_data($homestay_id_arr);
            foreach ($homestay_arr as $homestay) {
                $recomm[] = $homestay;
            }
        } elseif ($destid == 15) {
            $homestay_id_arr = array(
                array('homeId' => 355234),
                array('homeId' => 366127),
            );
            $bll_themeInfo = new Bll_Theme_ThemeInfo();
            $homestay_arr = $bll_themeInfo->acquire_theme_info_by_data($homestay_id_arr);
            foreach ($homestay_arr as $homestay) {
                $recomm[] = $homestay;
            }
        }

        return $recomm;
    }

    public function get_promo($params = null)
    {
        if ($params == null) {
            return array();
        }

        $promos = array();
        $os = $params['os'];
        $version = $params['version'];
        $uid = $params['uid'];
        $uid = intval($uid);

        if ($os == 'android' && $version > 47 && $version < 62 && empty($uid)) {
            $promo_camera = array(
                'title' => '',
                'image' => 'http://static.zzkcdn.com/mobile/img/app_reigster.jpg-800x600.jpg',
                'androidtarget' => 'com.youyao.taiwanlodge.mine.PhoneRegisterActivity',
                'type' => 'webview',
            );
            array_push($promos, $promo_camera);
        }

        //查询用户是否有待点评，  有的话在首页显示点评送代金券的活动
        if (!empty($uid) && (strtotime('2016-01-31') > time()) && $params['multiprice'] != 10) {
            $userinfo = new Bll_User_UserInfo();
            $waitingorders = $userinfo->get_waiting_for_comment_list($uid);
            $waitingcount = count($waitingorders);

            if (!empty($waitingcount)) {
                $promo_uncomment = array(
                    'title' => "",
                    'image' => 'http://static.zzkcdn.com/app/promo/comment_banner3.jpg-800x600.jpg',
                    'androidtarget' => 'com.youyao.taiwanlodge.WebViewActivity_',
                    'type' => 'webview',
                    'url' => 'http://m.youyao.com/activity/comment/tos/',
                    'bundle' => array(),
                );
                array_push($promos, $promo_uncomment);
            }
        }
        //APP首单折扣
        //        $bll_order = new Bll_Order_OrderInfo();
        //        if($uid>0)
        //        $count = $bll_order->get_AppPay_count($uid);
        //
        ////未登陆或者登陆账号未app支付显示该推广
        //        if (Util_Activity::is_firstappdiscount_available() && $params['multiprice'] != 10)
        //            if (empty($count) || empty($uid)) {
        //            $promo_first = array(
        //                'title' => '',
        //                'image' => 'http://static.zzkcdn.com/app/promo/app_banner.jpg-800x600.jpg',
        //                'androidtarget' => 'com.youyao.taiwanlodge.WebViewActivity_',
        //                'type' => 'webview',
        //                'url' => 'http://m.youyao.com/activity/tos/'
        //            );
        //            if (($os == 'ios' && $version >= 4.6) || ($os == 'android' && $version > 47)) {
        ////                array_push($promos, $promo_first);
        //            }
        //        }

        $location = array(
            array(
                'title' => '去台北的彩虹桥吹吹风',
                'image' => 'http://static.zzkcdn.com/app/theme/taibei-2.jpg-800x600.jpg',
                'type' => 'destination',
                'androidtarget' => 'com.youyao.taiwanlodge.search.SearchResultActivity',
                'namecode' => 'taibei',
                'bundle' => array(
                    'namecode' => 'taibei',
                ),
            ),
            array(
                'title' => '花莲真的超级赞！',
                'image' => 'http://static.zzkcdn.com/app/theme/hualian.jpg-800x600.jpg',
                'type' => 'destination',
                'androidtarget' => 'com.youyao.taiwanlodge.search.SearchResultActivity',
                'namecode' => 'hualian',
                'bundle' => array(
                    'namecode' => 'hualian',
                ),
            ),
            array(
                'title' => '台南  寻记忆古早味',
                'image' => 'http://static.zzkcdn.com/app/theme/tainan.jpg-800x600.jpg',
                'type' => 'destination',
                'androidtarget' => 'com.youyao.taiwanlodge.search.SearchResultActivity',
                'namecode' => 'tainan',
                'bundle' => array(
                    'namecode' => 'tainan',
                ),
            ),
        );

        if (($os == 'ios' && $version >= 4.3) || ($os == 'android' && $version > 41)) {
            $promos = array_merge($promos, $location);
            array_push($promos, array(
                'title' => '',
                'image' => 'http://static.zzkcdn.com/app/theme/weixin2.jpg',
                'androidtarget' => 'com.youyao.taiwanlodge.WebViewActivity_',
                'type' => 'webview',
                'url' => 'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=514223328&idx=1&sn=123343c2d822646407292e3f4920d043&scene=20#rd',
            ));
        }

        return $promos;
    }

    public function get_horiad($params = null)
    {

        if ($params == null) {
            return null;
        }

        $promos = array();
        $os = $params['os'];
        $version = $params['version'];
        $uid = $params['uid'];
        $uid = intval($uid);

        if ($os == 'android' && $version > 47 && empty($uid)) {
            $promo_camera = array(
                'title' => '',
                'image' => 'http://static.zzkcdn.com/app/theme/zhucesong25.jpg',
                'androidtarget' => 'com.youyao.taiwanlodge.mine.PhoneRegisterActivity',
                'type' => 'webview',
            );
            array_push($promos, $promo_camera);
        } elseif ($os == 'ios' && version_compare($version, '4.9.6', '>=') && empty($uid)) {

            $promo_camera = array(
                'title' => '',
                'image' => 'http://static.zzkcdn.com/app/theme/zhucesong25.jpg',
                'type' => 'signupWithPhone',
            );
            array_push($promos, $promo_camera);
        }

        $activity_comment_dealline = false;
        //查询用户是否有待点评，  有的话在首页显示点评送代金券的活动
        if (!empty($uid) && $activity_comment_dealline && $params['multiprice'] != 10) {
            $userinfo = new Bll_User_UserInfo();
            $waitingorders = $userinfo->get_waiting_for_comment_list($uid);
            $waitingcount = count($waitingorders);

            if (!empty($waitingcount)) {
                $promo_uncomment = array(
                    'title' => "",
                    'image' => 'http://7xkg3j.com5.z0.glb.qiniucdn.com/mobile/img/commentBanner.jpg',
                    'androidtarget' => 'com.youyao.taiwanlodge.WebViewActivity_',
                    'type' => 'webview',
                    'url' => 'http://m.youyao.com/activity/comment/tos/',
                    'bundle' => array(),

                );
                //array_push($promos, $promo_uncomment);

            }

        }
        if (empty($promos) || count($promos) == 0) {
            return null;
        }

        return $promos;

    }
}
