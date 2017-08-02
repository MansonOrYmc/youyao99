<?php
if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED',0);
}

$starttime = round(microtime(true) * 1000);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING & ~E_STRICT);

$base_uri = DIRECTORY_SEPARATOR=='/' ? dirname($_SERVER["SCRIPT_NAME"]) : str_replace('\\', '/', dirname($_SERVER["SCRIPT_NAME"]));
define("BASE_URI", $base_uri =='/' ? '' : $base_uri);
unset($base_uri);
define('APP_NAME', 'job');
define('APP_PATH', realpath(dirname(__FILE__)).'/');
define('SYS_PATH', APP_PATH."../../system/");

$G_LOAD_PATH = array(
    APP_PATH."../../app-youyao-core/current_release/",
    APP_PATH."../",
    SYS_PATH
);
$G_CONF_PATH = array(
    APP_PATH."../../app-youyao-core/current_release/config/",
    APP_PATH."../config/",
    APP_PATH."../../config/".APP_NAME."/"
);

$opts = getopt('', array(
        'class:'
));

$runnerClass = $opts['class'];
if(empty($runnerClass)) {
    exit("Use php bin/launcher.php --class=User_Job_Demo --xx=yy --xx=yy");
}

require_once(SYS_PATH."functions.php");
spl_autoload_register('apf_autoload');
apf_require_class("APF");

if(! class_exists($runnerClass)) {
    exit("$runnerClass is not exists\n");
}

$runner = new $runnerClass();
$runner->run();
