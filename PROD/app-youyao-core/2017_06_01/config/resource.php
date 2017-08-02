<?php
// resources deliver
if (defined("RELEASE_VERSION")) {
    $config['version'] = str_replace("_", "", RELEASE_VERSION);
}
//$config['expires'] = strtotime("2008-06-01 00:00:00") + 3600 * 24 *3650;
//$config['last_modified'] = strtotime("2008-06-04 00:00:00");

//$config['yuicompressor_host'] = 'localhost';
//$config['yuicompressor_port'] = 9999;

$config['cdn_host'] = "include.axing2.dev.youyao.com";
$config['cdn_path'] = "/".APP_NAME;
$config['cdn_boundable_host'] = "include.axing2.dev.youyao.com";
$config['cdn_boundable_path'] = "/".APP_NAME;
$config['cdn_pure_static_host'] = "pages.local.dev.haozu.com";
$config['cdn_pure_static_path'] = "";
$config['cdn_v1_static_host'] = "static.anjuke.com";
$config['cdn_v1_static_path'] = "";

$config['boundable_resources'] = TRUE;

$config['prefix_uri'] = '/res';
$config['resource_type_single'] = 's';
$config['resource_type_boundable'] = 'b';

//add by kyou
//线上环境 设置
$config['cookie_api_domain']  = "api.anjuke.com";
$config['cookie_add_url']  = "http://".$config['cookie_api_domain']."/common/cookie/add/guid/";
$config['cookie_get_url']  = "http://".$config['cookie_api_domain']."/common/cookie/get/guid/";


//css img 版本

$config['version']  = "20140912";


// jockjs框架版本控制
$config['js_version'] = "2012120401";
$config['js_domain'] = "http://jockjs.fp10.anjuke.test";
$config['js_path'] = "/ujs/base/logger/dom.dom/dom.query/ajax/event/ui.panel/ui.autocomplete/cookie/site/utils.base/";

//padjockjs
$config['pjs_version'] = "2013_13_03_3";
$config['pjs_path'] = "/pjs/base/logger/dom.dom/dom.query/ajax/event/ui.panel/ui.autocomplete/cookie/site/utils.base/";


?>
