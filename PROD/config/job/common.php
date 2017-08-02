<?php
$config['charset'] = 'utf-8';
$config['minify_html'] = false;
$config['minify_js'] = false;
$config['soj_log_path'] = '/data2/log/soj/soj.log';
$config['base_domain'] = ".youyao99.com";
$config['cookie_domain'] = ".youyao99.com";


$config['ImpressionTracker']  = true;
$config['ClickTracker'] = false;

$config['error_handler'] = "apf_error_handler";
$config['exception_handler'] = "apf_exception_handler";


$config['debug_allow_patterns'] = array('/^127\.0\.0\./', '/^192\.168\.1\./','/^192\.168\.201\./', '/^10\.0\.0\./', '/^180.168.34.162$/', '/^116.228.192.34$/');

$config['rabbitmq_host'] = "192.168.100.18";
$config['rabbitmq_port'] = "5672";

$config['java_open_api'] = "http://open.api.youyao99.com";
$config['java_add_item'] = "http://open.api.youyao99.com";
$config['wallet_api'] = 'http://api.internal.youyao99.com/2.0/trade/wallet/';
$config['stripe_serect_key'] = 'sk_live_hm9ubX8DKJlUgP23ESeyTcuP';

$config['solr_host'] = '127.0.0.1';
$config['solr_port'] = 8983;
