<?php

apf_require_class("APF_Controller");

class Theme_MultiThemeController extends APF_Controller
{
    const MULTI_LANG_CN = 12;
    const MULTI_LANG_TW = 10;

    public function handle_request()
    {
        $req = APF::get_instance()->get_request();
        $param_arr = $req->get_parameters();
        $dest_id = $param_arr['destid'];

        if (empty($dest_id)) {
            $msg = "destid can't be null ";
            Util_Json::render(400, null, $msg, $msg);
            return;
        }

        $list = array();
        $translate = true;

        switch (intval($dest_id)) {
            case 10: {
                if ($param_arr['multilang'] == self::MULTI_LANG_TW) {
                    $uid = intval($param_arr['mobile_userid']);
                    if (!empty($uid)) {
                    } else {
                        //todo 不送了
                        if (false)
                            $horiad_data[] = array(
                                'title' => '',
                                'image' => 'http://static.zzkcdn.com/mobile/app/theme/QQ%E5%9B%BE%E7%89%8720160325105651.jpg',
                                'type' => 'custom',
                                'android' => array(
                                    'target' => 'com.youyao.taiwanlodge.mine.RegisterActivity',
                                ),
                                'ios' => array(
                                    'target' => 'SignupWithPhoneViewController',
                                    'storyboard' => 0,
                                ),
                            );
                    }

                    $theme_bll = new Bll_Theme_ThemeInfo();
                    $results = $theme_bll->get_theme_list_by_dest_id(10, self::MULTI_LANG_TW);

                    $first_theme = array_shift($results);
                    $list[] = $this->get_themes(array($first_theme));
//                    $horiad_data[]=$this->get_city_object("yilan","宜蘭","http://static.zzkcdn.com/yilangzhaopian.jpg","CITY",true);
//
//                    $horiad_data[] = array_merge(
//                        Push_Pusher::homestay_recomend_push(273374),
//                        array(
//                            'title' => ' ',
//                            'image' => 'http://static.zzkcdn.com/banner960X450.jpg',
//                        ));



                    if (!empty($horiad_data)) {
                        $list[] = array(
                            'type' => 'horiad',
                            'data' => $horiad_data,
                        );
                    }

                    $list[] = array(
                        'type' => 'half_city',
                        'data' => array(
                            $this->get_city_object('tainan', '臺南', 'http://static.zzkcdn.com/app/theme/kending2.jpg', 'CITY'),
                            $this->get_city_object('hualian', '花蓮', 'http://static.zzkcdn.com/app/theme/hualian2.jpg', 'CITY'),
                        ),
                    );

                    $list[] = array(
                        'type' => 'half_city',
                        'data' => array(
                            $this->get_city_object('taidong', '臺東', 'http://static.zzkcdn.com/tainantainan.jpg', 'CITY'),
                            $this->area_more('10', '臺灣', 'http://static.zzkcdn.com/taiwantainansearch.jpg', 'CITY'),
                        ),
                    );

                    // $list[3] = $this->get_big_city('penghu', '最美海上煙火秀', 'http://static.zzkcdn.com/mobile/app/theme/%E6%BE%8E%E6%B9%96%E8%8A%B1%E7%81%AB%E7%AF%80.jpg?imageView2/1/interlace/1/q/50', 'CITY');
                    //$list[7] = $this->get_big_city('mazhu', '醉倒在浪漫藍眼淚中', 'http://static.zzkcdn.com/mobile/app/theme/40-1.jpg?imageView2/1/interlace/1/q/20', 'SCENIC_SPOTS');
//                    $theme_bll = new Bll_Theme_ThemeInfo();
//                    $results = $theme_bll->get_theme_list_by_dest_id(10, self::MULTI_LANG_TW);
                    $prev_length = count($list) - 1;
                    foreach ($results as $row) {
                        $list[$row['delta']+$prev_length] = $this->get_themes(array($row));
                    }

                    $list[] = $this->get_big_city(10, '臺灣', 'http://static.zzkcdn.com/_0002_tw%EF%BC%8Dfan.jpg', 'CITY');

                    ksort($list);
//                    foreach ($list as $row) {
//                        $tmp_arr[] = $row;
//                    }
                    $list =$this->make_half_city_below_banner( $list);
                    $translate = false;
                } else {
                    $horiad_data = array();
                    $type = 'webview';
                    $storyboard = 1;
                    $iostarget = "WebViewController";
                    $androidtarget = "com.youyao.taiwanlodge.WebViewAcivity";
                    if ($param_arr['os'] == 'android' && $param_arr['version'] > 70) {
                        $type = 'newwebview';
                        $androidtarget = 'com.youyao.taiwanlodge.webview.PromoWebView_Activity';
                    }
                    if ($param_arr['os'] == 'ios' && version_compare($param_arr['version'], '5.0.6', '>')) {
                        $storyboard = 0;
                        $iostarget = "WebBaseViewController";
                    }
//                    $horiad_data[] = array(
//                        'image' => 'http://static.zzkcdn.com/xuyuanbannerlaode.jpg',
//                        'type' => $type,
//                        'url' => 'http://m.youyao.com/activity/wish',
//                        'title' => '',
//                        'android' => array(
//                            'target' => $androidtarget,
//                            'bundle' => array(
//                                'url' => 'http://m.youyao.com/activity/wish',
//                            ),
//                        ),
//                        'ios' => array(
//                            'target' => $iostarget,
//                            'bundle' => array(
//                                'url' => 'http://m.youyao.com/activity/wish',
//                                'showShare' => 1,//WebBaseViewController
//                                'toolbarHidden' => 0,//WebViewController
//                                'pageTitle' => '许愿活动',
//                            ),
//                            'storyboard' => $storyboard,
//                        ),
//                    );
                    $uid = intval($param_arr['mobile_userid']);
                    //todo 不送了
                    if (false)
                        if (empty($uid) && $param_arr['multiprice'] != 10) {
                            $horiad_data[] = array(
                                'title' => '',
                                'image' => 'http://static.zzkcdn.com/mobile/app/theme/%E6%95%88%E6%9E%9C%E5%9B%BE.png',
                                'type' => 'custom',
                                'android' => array(
                                    'target' => 'com.youyao.taiwanlodge.mine.RegisterActivity',
                                ),
                                'ios' => array(
                                    'target' => 'SignupWithPhoneViewController',
                                    'storyboard' => 0,
                                ),
                            );
                        }
                    $horiad_data[] = array_merge(
                        Push_Pusher::homestay_recomend_push(273374),
                        array(
                            'title' => ' ',
                            'image' => 'http://static.zzkcdn.com/banner960X450.jpg',
                        ));
                    if($param_arr['multiprice'] != 10){
                        $horiad_data[] = array_merge(
                            Push_Pusher::homestay_recomend_push(273374),
                            array(
                                'title' => ' ',
                                'image' => 'http://static.zzkcdn.com/banner_gaotie_ticket.jpg',
                            ));
                    }

                    //if (!empty($uid) && $param_arr['multiprice'] != 10) {
                    //    $userinfo = new Bll_User_UserInfo();
                    //    $waitingorders = $userinfo->get_waiting_for_comment_list($uid);
                    //    $waitingcount = count($waitingorders);
                    //
                    //    if (!empty($waitingcount)) {
                    //        $horiad_data[] = $this->webview_format(
                    //            '',
                    //            'http://static.zzkcdn.com/mobile/img/comment/comment_banner_2.jpg',
                    //            'http://m.youyao.com/activity/comment/tos/'
                    //        );
                    //    }
                    //
                    //}

                    if (!empty($horiad_data)) {
                        $list[] = array(
                            'type' => 'horiad',
                            'data' => $horiad_data,
                        );
                    }
                    $list[] = array(
                        'type' => 'half_city',
                        'data' => array(
                            $this->get_city_object('kending', '垦丁', 'http://static.zzkcdn.com/app/theme/kending2.jpg', 'SCENIC_SPOTS'),
                            $this->get_city_object('hualian', '花莲', 'http://static.zzkcdn.com/app/theme/hualian2.jpg', 'CITY'),
                        ),
                    );
                    $list[] = array(
                        'type' => 'half_city',
                        'data' => array(
                            $this->get_city_object('taibei', '台北', 'http://static.zzkcdn.com/taiwantaibei.png', 'CITY'),
                            $this->area_more('10', '台湾', 'http://static.zzkcdn.com/taiwantaibiemore.png', 'CITY'),
                        ),
                    );

                    $theme_bll = new Bll_Theme_ThemeInfo();
                    $results = $theme_bll->get_theme_list_by_dest_id(10);
                    $prev_length = count($list) - 1;

                    foreach ($results as $row) {
                        $list[$row['delta'] + $prev_length] = $this->get_themes(array($row));
                    }


                    $list[] = $this->get_big_city('taibei', '去台北的彩虹桥吹吹风', 'http://static.zzkcdn.com/app/theme/taibei-2.jpg-800x600.jpg', 'CITY');
                    $list[] = $this->get_homestays(array(324894));
                    $list[] = $this->get_homestays(array(16184));

                    $homestay_arr = array(96111, 75189, 942);
                    foreach ($homestay_arr as $row) {
                        $list[] = $this->get_homestays(array($row));
                    }
                    $list[] = $this->get_big_city(10, '台湾', 'http://static.zzkcdn.com/_0006_tw.jpg', 'CITY');

                    ksort($list);
//                    foreach ($list as $row) {
//                        $tmp_arr[] = $row;
//                    }
                    $list =$this->make_half_city_below_banner( $list);
                }
                break;
            }
            case 11: {
                if ($param_arr['multilang'] == self::MULTI_LANG_CN) {

                    $horiad_data[] = $this->webview_format(
                        '住民宿就找自在客办日签',
                        'http://static.zzkcdn.com/mobile/app/activity/japan/yangdao_.jpg',
                        'http://m.youyao.com/activity/japan/visa/'
                    );
                }
                if (!empty($horiad_data)) {
                    $list[] = array(
                        'type' => 'horiad',
                        'data' => $horiad_data,
                    );
                }
                $theme_bll = new Bll_Theme_ThemeInfo();
                $results = $theme_bll->get_theme_list_by_dest_id(11);

                $first_theme = array_shift($results);
                $list[] = $this->get_themes(array($first_theme));



                /* 私人订制
                $horiad_d[] = $this->webview_format(
                    '   ',
                    'http://static.zzkcdn.com/3D447C12@57FECD4A.B4B98857.jpg',
                    'http://m.youyao.com/apost/view/21880?'
                );
                $list[] = array(
                    'type' => 'horiad',
                    'data' => $horiad_d,
                );
                 */

                $list[] = $this->get_big_city('jingdou', '京都 日本人的心灵之乡', 'http://static.zzkcdn.com/mobile/app/theme/kyoto.jpg');
                $list[] = array(
                    'type' => 'half_city',
                    'data' => array(
                        $this->get_city_object('dongjing', '东京', 'http://static.zzkcdn.com/mobile/app/theme/tokyou2.jpg', 'CITY'),
                        $this->get_city_object('daban', '大阪', 'http://static.zzkcdn.com/mobile/app/theme/osaka.jpg', 'CITY'),
                    ),
                );
                $list[] = array(
                    'type' => 'half_city',
                    'data' => array(
                        $this->get_city_object('jingdou', '京都', 'http://static.zzkcdn.com/mobile/app/activity/japan/kyoto4.jpg', 'CITY'),
                        $this->area_more('11', '日本', 'http://static.zzkcdn.com/mobile/app/activity/japan/more_home.jpg', 'CITY'),
                    ),
                );

                //$webview[] = $this->webview_format(null, 'http://static.zzkcdn.com/app/promo/chf2.jpg-800x600.jpg', 'https://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=909572018&idx=1&sn=34bd96803d76709ac96fefb92ad301bd&scene=1&srcid=1223cDGpDeOyPcmlxU6nMbBj&key=62bb001fdbc364e5e92537d8d62fa798846a78fbb1fd2271eb4fc4328c714880f742d7ab44c2e7e87b1353bec0db2b38&ascene=0&uin=Mzg5Nzk0MDM1&devicetype=iMac+MacBookPro12%2C1+OSX+OSX+10.10.4+build(14E46)&version=11020012&pass_ticket=Aj854Z4KrEzGwnXV3g5L9XTuc67MZC9uYxqvqRrOTDiqih%2Fs2n4bA8DGiy6FjywC');
                //$webview[] = $this->webview_format(null, 'http://static.zzkcdn.com/app/promo/wqlg2.jpg-800x600.jpg', 'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=818418800&idx=1&sn=25efdd05564374f420082a9c38365040&scene=21#wechat_redirect');
                //$list[] = array('type' => 'themes', 'data' => $webview);
                $homestay_arr = array(317120, 101029);
                foreach ($results as $row) {
                    $list[] = $this->get_themes(array($row));
                }
                foreach ($homestay_arr as $row) {
                    $list[] = $this->get_homestays(array($row));
                }
                if ($_REQUEST["multilang"] == 10) {
                    $list[] = $this->get_big_city(11, '日本', 'http://static.zzkcdn.com/0000_jp%EF%BC%8Dfan.jpg', 'CITY');
                } else {
                    $list[] = $this->get_big_city(11, '日本', 'http://static.zzkcdn.com/_0004_jp.jpg', 'CITY');
                }
                $list =$this->make_half_city_below_banner( $list);
                break;
            }
            case 12: {
                if (!empty($horiad_data)) {
                    $list[] = array(
                        'type' => 'horiad',
                        'data' => $horiad_data,
                    );
                }
                $theme_bll = new Bll_Theme_ThemeInfo();
                $results = $theme_bll->get_theme_list_by_dest_id(12);

                $list[] = array('type' => 'themes', 'data' => 
                    array(
                        $this->webview_format(
                            '小村落的净心之旅',
                            'http://static.zzkcdn.com/app/theme/xiaocunluo.jpg',
                            'http://m.youyao.com/apost/view/22257'
                        )
                    )
                );
                foreach ($results as $row) {
                    $list[] = $this->get_themes(array($row));
                }

                $city_conf = array(
                    'moganshan' => array(
                        'title' => '莫干山·春风又绿江南岸',
                        'img_url' => 'http://static.zzkcdn.com/mobile/app/theme/IMG_0170.JPG?imageView2/1/interlace/1/q/20',
                    ),
                );
                foreach ($city_conf as $namecode => $v) {
                    $list[] = $this->get_big_city($namecode, $v['title'], $v['img_url']);
                }
                $list[] = array(
                    'type' => 'half_city',
                    'data' => array(
                        $this->get_city_object('beijing', '北京', 'http://static.zzkcdn.com/app/theme/beijing.jpg-800x600.jpg', 'CITY'),
                        $this->get_city_object('hangzhou', '杭州', 'http://static.zzkcdn.com/app/theme/hangzhou.png-800x600.jpg', 'CITY'),
                    ),
                );
                $list[] = array(
                    'type' => 'half_city',
                    'data' => array(
                        $this->get_city_object('moganshan', '莫干山', 'http://static.zzkcdn.com/dalumoganshan.png', 'CITY'),
                        $this->area_more('12', '大陆', 'http://static.zzkcdn.com/dalumoganshan-more.png', 'CITY'),
                    ),
                );

                $list[] = $this->get_big_city('xinchang', '绍兴·新昌 私藏旅行地', 'http://static.zzkcdn.com/appxinchang.jpg');
                $homestay_arr = array(
                    275362,
                    429951,
                    425597,
                    428863,
                    357375,
                );
                foreach ($homestay_arr as $row) {
                    $list[] = $this->get_homestays(array($row));
                }
                //$webview[] = $this->webview_format(null, 'http://static.zzkcdn.com/app/theme/seaside2.jpg-800x600.jpg', 'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=904970569&idx=1&sn=a2230307080d26e061faafcbfec029ed#rd/?campaign_code=edm_zh');
                //$webview[] = $this->webview_format(null, 'http://static.zzkcdn.com/app/theme/wenquanren.jpg-800x600.jpg', 'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=888299976&idx=1&sn=d8a491e1356e8158d72033f5c63c7c00#rd/?campaign_code=edm_zh');
                //$list[] = array('type' => 'themes', 'data' => $webview);
                if ($_REQUEST["multilang"] == 10) {
                    $list[] = $this->get_big_city(12, '大陸', 'http://static.zzkcdn.com/_0001_dalu%EF%BC%8Dfan.jpg', 'CITY');
                } else {
                    $list[] = $this->get_big_city(12, '大陆', 'http://static.zzkcdn.com/_0005_dalu.jpg', 'CITY');
                }
                $list =$this->make_half_city_below_banner( $list);
                break;
            }
            case 15: {
                $theme_bll = new Bll_Theme_ThemeInfo();
                $results = $theme_bll->get_theme_list_by_dest_id(15);
                $first_theme = array_shift($results);
                $list[] = $this->get_themes(array($first_theme));



                $horiad_d[] =  array_merge(array('title' => '   ',
                    'image' =>'http://static.zzkcdn.com/hanguobaoche.jpg',
                ), Push_Pusher::homestay_recomend_push(449926));

                //$horiad_d[] = $this->webview_format(
                //    '   ',
                //    'http://static.zzkcdn.com/sirendingzhihanguo.jpg',
                //    'http://r.xiumi.us/stage/v5/2o4Gg/17494943'
                //);
                $list[] = array(
                    'type' => 'horiad',
                    'data' => $horiad_d,
                );

                    $city_conf = array(
                    'hongda' => array(
                        'title' => '弘大 年轻人的时尚聚集地',
                        'img_url' => 'http://static.zzkcdn.com/app/theme/hongda.jpg',
                    ),
                );

                foreach ($city_conf as $namecode => $v) {
                    $list[] = $this->get_big_city($namecode, $v['title'], $v['img_url']);
                }

                $list[] = array(
                    'type' => 'half_city',
                    'data' => array(
                        $this->get_city_object('beicun', '北村', 'http://static.zzkcdn.com/app/theme/beicun.jpg-800x600.jpg', 'SCENIC_SPOTS'),
                        $this->get_city_object('nanshan', '南山', 'http://static.zzkcdn.com/app/theme/nanshanta.jpg', 'SCENIC_SPOTS'),
                    ),
                );

                $list[] = array(
                    'type' => 'half_city',
                    'data' => array(
                        $this->get_city_object('dongdamen', '东大门', 'http://static.zzkcdn.com/hanguodongdamen.png', 'CITY'),
                        $this->area_more('15', '韩国', 'http://static.zzkcdn.com/handguodongdamenmore.png', 'CITY'),
                    ),
                );
                //$webview[] = $this->webview_format('', 'http://static.zzkcdn.com/app/promo/hw3.jpg-800x600.jpg', 'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=897186506&idx=1&sn=d32576f9e1e2cabbd387331719628cb5&scene=1&srcid=1224bAn81EJtzB0yEEPh6v2R&from=singlemessage&isappinstalled=0');
                //$webview[] = $this->webview_format(null, 'http://static.zzkcdn.com/app/promo/jzd2.jpg-800x600.jpg', 'http://mp.weixin.qq.com/s?__biz=MzA5OTMwODkyOA==&mid=814524336&idx=1&sn=fc041c29b2aa3e6187eba4283e25d125&scene=1&srcid=1224ChPuWsrU8NQauagdqb0I&from=singlemessage&isappinstalled=0');
                //$list[] = array(
                //'type' => 'themes',
                //'data' => $webview,
                //);

                $homestay_arr = array(355234, 366127);
                foreach ($results as $row) {
                    $list[] = $this->get_themes(array($row));
                }
                foreach ($homestay_arr as $row) {
                    $list[] = $this->get_homestays(array($row));
                }
                if ($_REQUEST["multilang"] == 10) {
                    $list[] = $this->get_big_city(15, '韓國', 'http://static.zzkcdn.com/_0003_kr%EF%BC%8Dfan.jpg', 'CITY');
                } else {
                    $list[] = $this->get_big_city(15, '韩国', 'http://static.zzkcdn.com/_0007_kr.jpg', 'CITY');
                }
                $list =$this->make_half_city_below_banner( $list);
            }
        }

        if (empty($list)) {
            Util_Json::render(500, null, '没有数据', '没有数据');
        } else {
            Util_Json::render(200, $list, null, null, $translate);
        }
    }


    private function area_more($namecode, $title, $img_url, $searchType)
    {
        if ($_GET['os'] == 'android' && $_GET['version'] < 81) {
            return $this->get_city_object($namecode, $title, $img_url, $searchType);
        }
        if ($_GET['os'] == 'ios' && version_compare($_GET['version'], '5.0.8', '<')) {
            return $this->get_city_object($namecode, $title, $img_url, $searchType);
        }
        return array(
            'type'=>'custom',
            'image' => $img_url,
            'title' => "  ",
            'android' => array(
                'target' => 'com.youyao.taiwanlodge.search.SearchMainActivity',
                'bundle'=>array(
                    'SHOW_PROM_VIEW'=>0,
                    'SHOW_BACK_ICON'=>1

                )

            ),
            'ios' => array(
                'target' => 'NewSearchController',
                'bundle' => array(
                    'isHaveNavigation' => 1,
                    'destid' => $namecode,
                    'isHaveTopic' => 1
                ),
                'storyboard' => 1,
            ),


        );
    }



    public static function webview_format($title, $img_url, $url)
    {
        $type = 'webview';
        $androidtarget = "com.youyao.taiwanlodge.WebView_Activity";
        if ($_GET['os'] == 'android' && $_GET['version'] > 70) {
            $type = 'newwebview';
            $androidtarget = 'com.youyao.taiwanlodge.webview.PromoWebView_Activity';
        }
        $storyboard = 1;
        $iostarget = "WebViewController";
        if ($_GET['os'] == 'ios' ) {
            $storyboard = 0;
            $iostarget = "WebBaseViewController";
        }

        return array(
            'image' => $img_url,
            'type' => $type,
            'url' => $url,
            'title' => $title,
            'android' => array(
                'target' => $androidtarget,
                'bundle' => array(
                    'url' => $url,
                ),
            ),
            'ios' => array(
                'target' => $iostarget,
                'bundle' => array(
                    'url' => $url,
                    'showShare' => 1,//WebBaseViewController
                ),
                'storyboard' => $storyboard,

            ),
        );
    }

    public static function get_themes($themes)
    {
        $list = array();
        foreach ($themes as $k) {
            $list[] = Theme_MultiThemeController::get_theme_object($k['themeId'], $k['themeName'], $k['themePic'], $k['homestayNum'], $k['themeSubTitle'], $k['type'], $k['id']);
        }

        $themes = array(
            'type' => 'themes',
            'data' => $list,
        );
        return $themes;

    }

    public static function get_theme_object($theme_id, $theme_name, $theme_pic, $homestayNum, $sub_title, $type, $id,$no_title=false)
    {
        if ($sub_title == 'notext') {
            $subtitle = '';
        } elseif (!empty($sub_title)) {
            $subtitle = $sub_title;
        } else {
            $subtitle = Trans::t("total_%n_bnbs", null, array("%n" => $homestayNum ));
        }
        if ($type == 1) {
            return array(
                'image' => $theme_pic,
                'title' => $no_title ? " " : $theme_name,
                'subtitle' => $subtitle,
                'type' => 'theme',
                'android' => array(
                    'target' => 'com.youyao.taiwanlodge.mainlist.StyleActivity',
                    'bundle' => array(
                        'THEMEID' => $theme_id,
                        'THEMENAME' => $theme_name,
                    ),
                ),
                'ios' => array(
                    'target' => 'ThemeItemTableViewController',
                    'storyboard' => 1,
                    'bundle' => array(
                        'themeid' => $theme_id,
                        'themename' => $theme_name,

                    ),
                ),
            );
        } else {
            $type = 'webview';
            $androidtarget = "com.youyao.taiwanlodge.WebView_Activity";
            if ($_REQUEST['os'] == 'android' && $_REQUEST['version'] > 70) {
                $type = 'newwebview';
                $androidtarget = 'com.youyao.taiwanlodge.webview.PromoWebView_Activity';
            }
            $storyboard = 1;
            $iostarget = "WebViewController";
            if ($_REQUEST['os'] == 'ios' && version_compare($_REQUEST['version'], '5.0.6', '>')) {
                $storyboard = 0;
                $iostarget = "WebBaseViewController";
            }
            return array(
                'image' => $theme_pic,
                'title' => $theme_name,
                'subtitle' => $subtitle,
                'type' => $type,
                'android' => array(
                    'target' => $androidtarget,
                    'bundle' => array(
                        'url' => Util_Common::url('/app/theme/' . $id, 'm'),
                    ),
                ),
                'ios' => array(
                    'target' => $iostarget,
                    'bundle' => array(
                        'url' => Util_Common::url('/app/theme/' . $id.'?', 'm'),
                        'showShare' => 1,//WebBaseViewController
                        'toolbarHidden' => 0,//WebViewController
                        'pageTitle' => $theme_name,
                    ),
                    'storyboard' => $storyboard,
                ),
            );

        }

    }

    public static function get_homestays($homestay_id_arr)
    {
        $homes = array();

        $bll_themeInfo = new Bll_Theme_ThemeInfo();
        foreach ($homestay_id_arr as $homestay_uid) {
            $query_id_arr[] = array('homeId' => $homestay_uid);
        }
        $homestay_arr = $bll_themeInfo->acquire_theme_info_by_data($query_id_arr);

        $bll_area = new Bll_Area_Area();
        $multi_price = empty($_REQUEST['multiprice']) ? 10 : intval($_REQUEST['multiprice']);
        $area = $bll_area->get_dest_config_by_destid($multi_price);

        foreach ($query_id_arr as $query_id) {
            $v = $homestay_arr[$query_id['homeId']];
            $v = array_map(function ($payload) {
                return trim($payload, " \t\n\r\0\x0B\xc2\xa0");
            }, $v);
            $homes[] = array(
                'image' => $v['cover'],
                'title' => $v['name'],
                'type' => 'homestay',
                'home' => array(
                    'speed_room' => $v['speed_room'],
                    'homestay_uid' => strval($v['uid']),
                    'price' => $v['lowestPrice'],
                    'address' => $v['address'],
                    'currency_sym' => $area['currency_code'],
                    'title' => $v['name'],
                    'image' => $v['cover'],
                ),
                'android' => array(
                    'target' => 'com.youyao.taiwanlodge.room.HomestayDetailNew_Activity',
                    'bundle' => array(
                        'homestayUid' => strval($v['uid']),
                        'homestayName' => $v['name'],
                    ),
                ),
                'ios' => array(
                    'target' => 'RoomListViewController',
                    'storyboard' => 0,
                    'bundle' => array(
                        'homestayUid' => strval($v['uid']),
                        'homeName' => $v['name'],
                    ),
                ),
            );
        }

        return array(
            'type' => 'homes',
            'data' => $homes,
        );
    }

    public static function get_city_object($namecode, $title, $img_url, $searchType,$no_pic_title=false)
    {
        $bll_area = new Bll_Area_Area();
        //todo  景点要通过 t_loc_poi 来查找
        if (is_numeric($namecode)) {
            $destId = $namecode;
            $no_pic_title = true;
        } elseif ($namecode == 'kending') {
            $city['dest_id'] = 10;
            $city['locid'] = 919;
        } elseif ($namecode == 'mazhu') {
            $city['dest_id'] = 10;
            $city['locid'] = 920;
        }elseif ($namecode=='nanshan'){
            $city['dest_id'] = 15;
            $city['locid'] = 876;
        }elseif ($namecode=='beicun'){
            $city['dest_id'] = 15;
            $city['locid'] = 861;
        } else {
            $city = $bll_area->get_loc_type_by_namecode($namecode);
        }
        $destId = empty($destId) ? $city['dest_id'] : $destId;

        $data = array(
            'type' => 'searchresult',
            'image' => $img_url,
            'title' => $no_pic_title ? '' : $title,
            'android' => array(
                'target' => 'com.youyao.taiwanlodge.search.SearchLodgeActivity',
                'bundle' => array(
                    'searchType' => $searchType,
                    'locid' => $city['locid'],
                    'title' => $title,
                    'keyWords' => '',
                    'destId' => $destId,
                ),
            ),
            'ios' => array(
                'target' => 'SearchResultVC',
                'storyboard' => 0,
                'bundle' => array(
                    'districtType' => $searchType,
                    'locid' => $city['locid'],
                    'deatName' => $title,
                    'destId' => $destId,
                    'keyWord' => '',
                ),
            ),
        );
        return $data;

    }

    public static function get_big_city($namecode, $title, $img_url, $searchType = 'CITY')
    {
        return array(
            'type' => 'bigcity',
            'data' => array(Theme_MultiThemeController::get_city_object($namecode, $title, $img_url, $searchType)),

        );
    }

    public static function get_horiads($params = null)
    {


        $promos = array();
        $os = $params['os'];
        $version = $params['version'];
        $uid = $params['uid'];
        $uid = intval($uid);

        $url = 'http://m.youyao.com/activity/comment/tos/';

        $promo_camera = array(
            'title' => '',
            'image' => 'http://static.zzkcdn.com/app/theme/zhucesong25.jpg',
            'type' => 'custom',
            'android' => array(
                'target' => 'com.youyao.taiwanlodge.mine.RegisterActivity',
            ),
            'ios' => array(
                'target' => 'SignupWithPhoneViewController',
                'storyboard' => 0,
            ),

        );

        $storyboard = 1;
        $iostarget = "WebViewController";
        if ($os == 'ios' && version_compare($version, '5.0.6', '>')) {
            $storyboard = 0;
            $iostarget = "WebBaseViewController";
        }

        $promo_comment = array(
            'image' => 'http://static.zzkcdn.com/mobile/img/commentBanner.jpg',
            'type' => 'webview',
            'url' => $url,
            'android' => array(
                'target' => 'com.youyao.taiwanlodge.WebViewAcivity',
                'bundle' => array(
                    'url' => $url,
                ),
            ),
            'ios' => array(
                'target' => $iostarget,
                'bundle' => array(
                    'url' => $url,
                    'showShare' => 1,//WebBaseViewController
                ),
                'storyboard' => $storyboard,

            ),

        );

        array_push($promos, $promo_comment);
        //  array_push($promos, $promo_camera);

        if (empty($promos) || count($promos) == 0) {
            return null;
        }

        return array(
            'type' => 'horiad',
            'data' => $promos,

        );

    }

    private function make_half_city_below_banner($array)
    {
        $temp = array();

        $arr_half = array();
        foreach ($array as $k => $v) {
            if ($v['type'] == 'half_city') {
                $arr_half[] = $v;
                unset($array[$k]);
            }
        }

        $index = 0;
        foreach ($array as $k => $v) {
           if($index==1){
               $temp=array_merge($temp,$arr_half);
           }
            $temp[] = $v;
            $index++;
        }


        return $temp;


    }


}
