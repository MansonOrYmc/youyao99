<?php
if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED',0);
}

$starttime = round(microtime(true) * 1000);

//error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING & ~E_STRICT);
error_reporting(E_ALL);

$base_uri = DIRECTORY_SEPARATOR=='/' ? dirname($_SERVER["SCRIPT_NAME"]) : str_replace('\\', '/', dirname($_SERVER["SCRIPT_NAME"]));
define("BASE_URI", $base_uri =='/' ? '' : $base_uri);
unset($base_uri);
define('APP_NAME', 'zzk');
define('APP_PATH', realpath(dirname(__FILE__)).'/');
define('CORE_PATH', APP_PATH.'../app-youyao-core/');
if(get_cfg_var('vruan')=='handsome'){
    define('MEDIAWIKI_PATH', APP_PATH.'../mediawiki-1.13.5/');
}else{
    define('MEDIAWIKI_PATH', '/home/tonycai/software/mediawiki-1.13.5/');
}
define('Const_Host_Domain', 'http://taiwan.youyao.com');
define('SYS_PATH', APP_PATH."../system/");
$G_LOAD_PATH = array(
	APP_PATH."../app-youyao-core/",
    APP_PATH,
    SYS_PATH
);
$G_CONF_PATH = array(
    APP_PATH."../app-youyao-core/config/",
    APP_PATH."config/",
    APP_PATH."../config/".APP_NAME."/"
);

header('Content-Type:text/html;Charset=utf-8');  
require_once(SYS_PATH."functions.php");
require 'vendor/autoload.php';
spl_autoload_register('apf_autoload');
apf_require_class("APF");
APF::get_instance()->set_request_class('ZzkRequest');
APF::get_instance()->run();
