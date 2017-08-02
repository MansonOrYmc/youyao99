<?php
$config['charset'] = 'utf-8';
$config['minify_html'] = false;
$config['minify_js'] = false;
$config['soj_log_path'] = '/data2/log/soj/soj.log';
$config['postfeed_log_path'] = '/data2/log/soj/postfeed.log';
$config['soj_mobilelog_path'] = '/data2/log/soj/mobile.log';
$config['cookie_domain'] = ".youyao.com";
$config['base_domain'] = ".youyao.com";
$config['java_soa'] = 'http://api.internal.youyao.com/2.0';
$config['mediawiki_path'] = '/home/tonycai/software/mediawiki-1.13.5/';

$config['ImpressionTracker']  = true;
$config['ClickTracker'] = false;

$config['error_handler'] = "apf_error_handler";
$config['exception_handler'] = "apf_exception_handler";

$config['solr_host'] = '127.0.0.1';
$config['solr_port'] = 8983;

$config['debug_allow_patterns'] = array('/^127\.0\.0\./', '/^192\.168\.28\./','/^192\.168\.201\./', '/^10\.0\.0\./', '/^116.228.208.194$/', '/^116.228.192.34$/');

$config['weixin_log_dir'] = '/data2/logs/weixin';

$config['api_base_domain'] = "http://api.youyao.com";
$config['calendar_obtain_api'] = $config['api_base_domain'] . "/calendar/obtain";
$config['room_set_price_api'] = $config['api_base_domain'] . "/calendar/price";
$config['room_set_state_api'] = $config['api_base_domain'] . "/calendar/state";

$config['trans_api'] = "http://74.207.253.49/translate/translator.php";
$config['mapi'] = "http://www.youyao.com/mapi.php";
$config['user_profile_api'] = $config['api_base_domain'] . "/user/userheadpicnickname";
$config['buyerorderlist_api'] = $config['api_base_domain'] . "/order/buyerorderlist";

$config['msg_list'] = $config['api_base_domain'] . "/pmsg/list";
$config['msg_booking_list'] = $config['api_base_domain'] . "/pmsg/booking";
$config['msg_history_list'] = $config['api_base_domain'] . "/pmsg/history";
$config['easemob_client_api'] =  $config['api_base_domain'] . "/im/emclient";
$config['easemob_user_api'] =  $config['api_base_domain'] . "/im/emuser";

$config['blocked_words_api'] =  $config['api_base_domain'] . "/pmsg/keyregular";
$config['pmsg_internotify_api'] = $config['api_base_domain'] . "/pmsg/internotify";

$config['rabbitmq_host'] = "192.168.100.18";
$config['rabbitmq_port'] = "5672";

$config['dsh_path'] = "/usr/local/bin/dsh";
$config['solr_job_server_username'] = "";
$config['solr_job_server'] = "192.168.100.18";
$config['solr_job_dir'] = "/data2/tonycai/one.search.job";

#$config['java_inter_server'] = "http://api.internal.youyao.com:80/2.0";
$config['java_inter_server'] = "http://192.168.100.18:8091";

$config['search_soa_url'] = "http://api.internal.youyao.com/2.0/search/room";

$config['ical_url_domain'] = "https://ssl.open.api.youyao.com/calendar/ical/";

$config['java_open_api'] = "http://open.api.youyao.com";

$config['java_service_soa'] = 'http://api.internal.youyao.com/2.0/';
$config['java_add_item'] = "http://open.api.youyao.com";
$config['java_trans_api'] = 'http://api.internal.youyao.com/2.0/common';
$config['comment_label_api'] = 'http://api.internal.youyao.com/2.0/commodity';
$config['usercenter_api'] = 'http://api.internal.youyao.com/2.0/common/center';
$config['wallet_api'] = 'http://api.internal.youyao.com/2.0/trade/wallet/';
$config['stripe_serect_key'] = 'sk_live_hm9ubX8DKJlUgP23ESeyTcuP';

//yunpian.com
$config['sms_provider_tpl_url'] = 'https://sms.yunpian.com/v2/sms/tpl_single_send.json'; //短信服务商地址
$config['sms_provider_market_apikey']  = '123ab355b9f9578877586b226d4d5083'; //短信服务商私钥 // sms marketing
$config['sms_provider_captcha_apikey'] = '1dcdacbca3e107b28bf5244e6bb288dc'; // 专用注册登录验证码通道

//手机验证码时间
$config['phone_captcha_time'] = 60*1; //重发
$config['phone_captcha_expired'] = 60*20; // 过期

define('IMG_CDN', 'http://os05bf8go.bkt.clouddn.com/');
define('IMG_CDN_DOCTOR', 'http://os05bf8go.bkt.clouddn.com/');
define('IMG_CDN_USER', 'http://ose50t86c.bkt.clouddn.com');
define('IMG_CDN_YYMIX', 'http://ose5opmxm.bkt.clouddn.com');
define('IMG_CDN_AGENT', 'http://ose5conpo.bkt.clouddn.com');
define('IMG_CDN_HOST', 'http://up.youyao99.com/');
define('WECHAT_SECURITY_APIKEY', '6F86727E527411E79E6C68F728954D54188D51B5534511E79E6C68F728954D54');
