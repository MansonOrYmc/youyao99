<?php
if(file_exists("version.php")) {
    $release_version = trim(file_get_contents("version.php"));
    chdir("/Users/manson/Sites/php-v2/QA/MOBILE/".$release_version."/app-youyao-mobile");
}

if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED',0);
}

$starttime = round(microtime(true) * 1000);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING & ~E_STRICT);

$base_uri = DIRECTORY_SEPARATOR=='/' ? dirname($_SERVER["SCRIPT_NAME"]) : str_replace('\\', '/', dirname($_SERVER["SCRIPT_NAME"]));
define("BASE_URI", $base_uri =='/' ? '' : $base_uri);
unset($base_uri);
define('APP_NAME', 'yyao');
define('APP_PATH', getcwd().'/');
define('SYS_PATH', APP_PATH."../../../system/");
define('CORE_PATH', APP_PATH.'../app-youyao-core/');
define('MEDIAWIKI_PATH', '/home/tonycai/software/mediawiki-1.13.5/');
define('Const_Host_Domain', 'http://m.youyao99.com');

$G_LOAD_PATH = array(
    CORE_PATH,
    APP_PATH,
    SYS_PATH
);
$G_CONF_PATH = array(
    CORE_PATH."config/",
    APP_PATH."config/",
    APP_PATH."../../../config/".APP_NAME."/"
);

header('Content-Type:text/html;Charset=utf-8');

require_once(SYS_PATH."functions.php");
require APP_PATH.'vendor/autoload.php';
spl_autoload_register('apf_autoload');
apf_require_class("APF");
APF::get_instance()->set_request_class('ZzkRequest');
APF::get_instance()->run();
