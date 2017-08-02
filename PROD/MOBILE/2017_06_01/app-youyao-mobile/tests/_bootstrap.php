<?php
// This is global bootstrap for autoloading

define('APP_PATH', realpath(dirname(__DIR__)).'/');
define('SYS_PATH', APP_PATH."../system/");
define('CORE_PATH', APP_PATH.'../app-youyao-core/');
define('APP_NAME', 'mobile');
global $G_LOAD_PATH;
$G_LOAD_PATH = array(
    APP_PATH."../app-youyao-core/",
    APP_PATH,
    SYS_PATH
);
global $G_CONF_PATH;
$G_CONF_PATH = array(
    APP_PATH."../app-youyao-core/config/",
    APP_PATH."config/",
    APP_PATH."../config/".APP_NAME."/"
);
require_once(SYS_PATH."functions.php");
require APP_PATH . '/vendor/autoload.php';
spl_autoload_register('apf_autoload');
apf_require_class("APF");
//APF::get_instance()->run();