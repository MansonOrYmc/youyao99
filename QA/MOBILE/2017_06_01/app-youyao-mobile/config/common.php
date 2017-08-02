<?php
$config['enabled_auto_router'] = true;
$config['charset'] = 'utf-8';
$config['minify_html'] = false;
$config['minify_js'] = false;



$config['ImpressionTracker']  = true;
$config['ClickTracker'] = false;

$config['error_handler'] = "apf_error_handler";
$config['exception_handler'] = "apf_exception_handler";



$config['solr_host'] = '192.168.8.8';
$config['solr_port'] = 8983;



//JSblockCache
$config['cacheable_minify_js'] = TRUE;





//ip在如下网段显示验证码
$config['ip_ver_code'] = array(
    '/^223\.203\.202\./',
);

//ip在如下网段显示404
$config['ip_to_404'] = array(
    //'/^192\.168\.190\./',
);

$config['user_page_favorite'] = 'FAVORITE';
$config['user_page_history'] = 'HISTORY';

$config['seo_prop_api_url'] = 'http://www.anjuke.com/seo/getOtherHouseInfo/';

$config['weixin_log_dir'] = '/Users/Andrew/work/var/logs/';
