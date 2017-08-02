<?php
$config['charset'] = 'utf-8';
$config['minify_html'] = false;
$config['minify_js'] = false;


$config['smtp_server'] = "smtp.exmail.qq.com";
$config['smtp_user']   = "noreply@youyao.com";
$config['smtp_pass']   = "likeyong1205";

$config['mediawiki_path'] = '/home/tonycai/software/mediawiki-1.13.5/';
$config['drupal_hash_salt'] = '06QLfgvkItyOD3dBCPbTN60TZK3jeAL-ZYOfCpM3jBk';
$config['user_password_reset_timeout'] = 86400;

$config['cookie_domain'] = ".youyao.com";
$config['cookie_time']   = 1800;
$config['cookie_path'] = '/';

$config['easemob_app_name'] = "zzkhx";
$config['easemob_client_id'] = "YXA6ycLKkHPWEeW4XCVRbho4BQ";
$config['easemob_client_secret'] = "YXA6IW7oDFd5i57xI0_ehmj--Iq42nU";

//add by vruan 2015-11-13
//$config['java_soa'] = 'http://api.prod.youyao.com/2.0';
$config['java_soa'] = 'http://192.168.8.18:8083';

$config['translate_url'] = 'http://api.prod.youyao.com/2.0/common/translate?langue=';

$config['api_domain'] = 'http://api.youyao.com';

$config['old_pic_link_host'] = 'http://taiwan.youyao.com';
$config['ajk_api_url'] = 'anjuke.com/ajax/member/center/api/';
$config['anjuke_base_domain'] = 'anjuke.com';
$config['aifang_base_domain'] = 'aifang.com';
$config['haozu_base_domain'] = 'haozu.com';
$config['jinpu_base_domain'] = 'jinpu.com';
$config['xinfang_base_domain'] = 'fang.anjuke.com';
$config['user_center_base_domain'] = 'user.anjuke.com/';//用户中心

$config['static_domain'] = "static.haozu.com";
$config['base_domain'] = "local.dev.haozu.com";
$config['cookie_domain'] = ".dev.haozu.com";
$config['cookie_time']   = 1800;
$config['cookie_path'] = '/';

//$config['AuthCookieName'] = "aQQ_haozuauthinfos";
$config['AnjukeSecques']  = "Xi7@Sz";
//$config['LastUserCookieName'] = "aQQ_haozulastuser";
$config['ImpressionTracker']  = true;
$config['ClickTracker'] = false;

$config['error_handler'] = "apf_error_handler";
$config['exception_handler'] = "apf_exception_handler";


$config['debug_allow_patterns'] = array('/^127\.0\.0\./', '/^192\.168\.1\./','/^192\.168\.201\./', '/^10\.0\.0\./', '/^180.168.34.162$/', '/^116.228.192.34$/');


// property images
$config['size_thumbnail']['width'] = 100;
$config['size_thumbnail']['height'] = 75;
$config['size_larger']['width'] = 420;
$config['size_larger']['height'] = 315;

$config['image_server_domain'] = "images";
$config['image_server_base_domain'] = "qa.haozustatic.com";

$config['daoPropViewClass'] = 'DAO_Prop_PropertyMemcache';
$config['daoAreaClass'] = 'DAO_Area_AreaMemcache';
$config['daoCommunityMemcacheClass'] = 'DAO_Community_CommunityMemcache';

$config['getCommunityUrl'] = 'http://www.anjuke.com';
$config['propDueto'] = 15;
$config['send_message_key']   = '10';
$config['LogKWPath']   = '/home/ch98/kw.txt';

$config['autocomplete'] = 1;
$config['autosearchQueryUrl'] = 'http://10.0.0.130:8983/search-suggestion/select?';

$config['haozu_member_label'] = 'haozu';

$config['publishErrorLog'] = '/home/www/tmp/publishlog.txt';

$config['dfs']['upload_url'] = "http://upd1.ajkimg.com/upload";
$config['dfs']['display_host'] = "pic";
$config['dfs']['display_domain'] = "ajkimg.com";
$config['bbs_home_index'] = 'bbs.local.dev.haozu.com';

$config['HzMemberCookieName'] = "aQQ_hzweb_uid";
//$config['MemberCookieName'] = "aQQ_Memberauthinfos";
$config['MemberSecques']  = "hz@b5b8s";
$config['MemLastUserCookieName'] = "aQQ_Memberlastuser";
$config['bbs_base_domain'] = "bbs.local.dev.haozu.com";
//$config['MemberLastLoginId'] = "__memllid";

$config['allow_mobile'] = array('13817516665','13916672794','13524644183','13052285329', '18701926684');

// !!! 请使用config/service.php中的$config['communities_list']
//$config['list_comm_solr'] = 'http://10.0.1.145:8998/community/select?';
$config['list_comm_solr'] = 'http://192.168.201.109:8983/hz-community/select?fl=';

$config['global_city_id'] = 10;//全部城市对应ID

$config['beijing_promotion_proids'] = array(38400881,40075722,35259397,39783071,35111855,40074707,39924455,38698004,37061270,36903322,38868813,35843195,40074381,34881542,40064374,35290693,35290206,39812493,37727042,37060343);//北京推广页房源id

// 过滤非法关键字
$config['filter_keyword'] = "http://java01-002.a.ajkdns.com:8080/service-keywords-release/rest/keyword/listKeywordsByGroupId/";

// 400开通城市
$config['transfer_cities'] = array(11, 14);

// 是否打开转接服务
$config['is_transfer'] = 1;
$config['performance_is_allow'] = true;

// 是否开启列表关键字查找 拆字服务
$config['is_mmseg'] = true;

//好租租金趋势字体
$config['trend_en_font_url']  = "/usr/share/fonts/ARIAL.TTF";
$config['trend_cn_font_url'] = "/usr/share/fonts/MSYH.TTF";

$config['idc_proxy_domain'] = "http://user.haozu.com";

//JSblockCache
$config['cacheable_minify_js'] = TRUE;

// 是否使用上海试点导航  0 不使用  1 上海使用 2 全部使用
$config['is_special_header'] = 0;
$config['home_login_version'] = 2;
$config['soj_view_asy']=array(
      'View_Landlord_IndexPage_Zheng',//个人整租房源
      'View_Landlord_IndexPage_He',//个人合租房源
      'View_Prop_YepPage_Zheng',//经纪人整租
      'View_Prop_YepPage_He',//经纪人合租
      'View_Prop_ViewBrokerPage_Zheng',
      'View_Prop_ViewBrokerPage_He',
      'View_Prop_CaptureBrokerPage_Zheng',
      'View_Prop_CaptureBrokerPage_He',
      'View_Landlord_CapturePage_Zheng',
      'View_Landlord_CapturePage_He',
      'View_Sublessor_ViewPage_Zheng',
      'View_Sublessor_ViewPage_He'
);
//安居客在好租展示
$config['soj_view_asy_ajk'] = array(
      'View_Prop_ViewBrokerPage_Zheng',//安居客房经纪人整租房源
      'View_Prop_ViewBrokerPage_He',//安居客经纪人合租房源
);
$config['sms_key'] = '#$ERT7';

$config['display_dfs_domain_zu'] = 'http://pic{{host_id}}.ajkimg.com/display/zu';
//获取头部导航接口
$config['header_nav_api'] = 'http://www.anjuke.com/api/nav/?cityId=';

// 开发商
$config['developer_url'] = 'http://vip.aifang.com/';

// 网络经纪人
$config['net_agent_url'] = 'http://agent.anjuke.com/login';

// touch web domain
$config['touch_mobile_base_domain'] = 'm.anjuke.com';

//广告api
$config['ifx_api_url'] = 'http://ifx.aifang.com';
//开发商分销平台
$config['developer_fx'] = 'http://my.anjuke.com/fxd/';

//经纪人分销平台
$config['broker_fx'] = 'http://my.anjuke.com/fxb/';



//修改手机号、邮箱
$config['api_key'] = array("ef7545201a2bc5911cdb43527b18b8c1"=>"79921bd362e7da67");


//2013-10-21 个人房源注册以及自动登录接口配置
//注册、登录密钥
$config['register_api_key'] = "subscsjo59fappu34pasdf";
//注册地址
$config['register_api_url'] = "http://member.anjuke.com/memberapi/m";
//登录地址
$config['autologin_api_url'] = "http://member.anjuke.com/memberapi/autologin";


//ip在如下网段显示验证码
$config['ip_ver_code'] = array(
    '/^223\.203\.202\./',
);

//ip在如下网段显示404
$config['ip_to_404'] = array(
    //'/^192\.168\.190\./',
);

$config['rec_rent_pagerec'] = 'http://www.anjuke.com/rec/rent/pagerec';
$config["rent_prop_info"] = "http://api.anjuke.com/haozu/mobile/2.0/property.getPropsInfo";
$config["prop_search_by_area"] = "http://api.anjuke.com/haozu/mobile/2.0/property.searchByArea";
$config["prop_search_by_comm"] = "http://api.anjuke.com/haozu/mobile/2.0/property.searchByComm";
/*个人房源的url--直接配置成线上地址*/
$config['i_url'] = 'http://i.anjuke.com';

$config['user_page_favorite'] = 'FAVORITE';
$config['user_page_history'] = 'HISTORY';

$config['seo_prop_api_url'] = 'http://www.anjuke.com/seo/getOtherHouseInfo/';

define('REQUEST_TIME', (int) $_SERVER['REQUEST_TIME']);
//define('IMG_CDN', 'http://img1.zzkcdn.com');
define('IMG_CDN', 'http://7lrxoz.com5.z0.glb.qiniucdn.com/');
define("STATIC_CDN", "http://static.zzkcdn.com");

$config['zzkcdn_img1'] = 'http://img1.zzkcdn.com';
// 设置时区
date_default_timezone_set('Asia/Shanghai');

define('Const_Default_Dest_ID',10);

//手机验证码时间
$config['phone_captcha_time'] = 60*2; //重发
$config['phone_captcha_expired'] = 60*60; // 过期

$config['zzk_browserlanguages'] = array( // 语言应该和dest_id分离！！
 'zh'         => '12',
 'zh-cn'      => '12',
 'zh-hans-cn' => '12',
 'zh-hans'    => '12',
 'zh-tw'      => '10',
 'zh-hant-tw' => '10',
 'ja'         => '11',
 'en'         => '13',
 'ko'         => '15',
);

