<?php
/*
允许类自动加载
    G_LOAD_AUTO 启用标记
    $G_LOAD_PATH 自定义加载位置
加载规则：
    1、命名空间分隔符、类名中下划线，对应目录分隔符
    2、类名下划线分隔最后一部分对应文件名，其他部分以及命名空间转换为小写后对应目录名，扩展名为.php
    3、顺序加载，多个搜索位置中同名类以设置顺序先为准
    4、区分后缀、搜索controller、page、component、interceptor目录
    5、否则搜索classes目录
    *6、保持大小写，搜索lib、$G_LOAD_PATH、include_path目录
    *7、目录名、文件名全小写搜索
*/
if(defined('G_LOAD_AUTO') && G_LOAD_AUTO === true){
    function __autoload_apf1($classname) {
        if(!$classname){return true;}
        $parts = preg_split('/[\\\_]/',$classname);
        $classname = end($parts);
        unset($parts[count($parts)-1]);
        $prefix = '';
        $file = '';
        $path = '';
        if($parts){
            $path = strtolower(implode(DIRECTORY_SEPARATOR, $parts));
        }
        if(strlen($classname) > 10 && substr($classname,-10) == 'Controller'){
            $prefix = 'controller';
            $file = substr($classname,0,strlen($classname)-10).'.php';
        }elseif(strlen($classname) > 4 && substr($classname,-4) == 'Page'){
            $prefix = 'page';
            $file = substr($classname,0,strlen($classname)-4).'.php';
        }elseif(strlen($classname) > 9 && substr($classname,-9) == 'Component'){
            $prefix = 'component';
            $file = substr($classname,0,strlen($classname)-9).'.php';
        }elseif(strlen($classname) > 11 && substr($classname,-11) == 'Interceptor'){
            $prefix = 'interceptor';
            $file = substr($classname,0,strlen($classname)-11).'.php';
        }else{
            return true;
        }
        $parts = array();
        if($prefix){
            $parts[] = $prefix;
        }
        if($path){
            $parts[] = $path;
        }
        if($file){
            $parts[] = $file;
        }
        if($parts){
            $classname = implode(DIRECTORY_SEPARATOR, $parts);

            global $include_path;
            global $G_LOAD_PATH;
            set_include_path(implode(PATH_SEPARATOR,$G_LOAD_PATH));

            include_once $classname;

            set_include_path($include_path);
        }
    }
    function __autoload_apf2($classname) {
        if(!$classname){return true;}
        $parts = preg_split('/[\\\_]/',$classname);
        $classname = end($parts);
        unset($parts[count($parts)-1]);
        $prefix = 'classes';
        $file = $classname.'.php';
        $path = '';
        if($parts){
            $path = strtolower(implode(DIRECTORY_SEPARATOR, $parts));
        }
        $parts = array();
        $parts[] = $prefix;
        if($path){
            $parts[] = $path;
        }
        $parts[] = $file;
        $classname = implode(DIRECTORY_SEPARATOR, $parts);

        global $include_path;
        global $G_LOAD_PATH;
        set_include_path(implode(PATH_SEPARATOR,$G_LOAD_PATH));

        include_once $classname;

        set_include_path($include_path);
    }
    function __autoload($classname) {
        if(!$classname){return true;}
        $classname = preg_replace('/[\\\_]/',DIRECTORY_SEPARATOR,$classname);

        global $include_path;
        global $G_LOAD_PATH;
        $load_path = array();
        foreach ($G_LOAD_PATH as $path) {
            $load_path[] = $path.'lib'.DIRECTORY_SEPARATOR;
            $load_path[] = $path;
        }
        $load_path[] = $include_path;
        set_include_path(implode(PATH_SEPARATOR,$load_path));

        include_once $classname.'.php';

        set_include_path($include_path);
    }

    /*
    function __autoload ($classname) {
        $matches = array();
        if (preg_match('/(.+)controller$/i',$classname,$matches)) {
            apf_require_class($matches[1],'controller');
        } else if (preg_match('/(.+)page$/i',$classname,$matches)) {
            apf_require_class($matches[1],'page');
        } else  if (preg_match('/(.+)component$/i',$classname,$matches)) {
            apf_require_class($matches[1],'component');
        } else  if (preg_match('/(.+)interceptor$/i',$classname,$matches)) {
            apf_require_class($matches[1],'interceptor');
        } else {
            apf_require_class($classname);
        }
    }*/

    $include_path = get_include_path();
    spl_autoload_register('__autoload_apf1');
    spl_autoload_register('__autoload_apf2');
    //spl_autoload_register('__autoload');
    //spl_autoload_register('spl_autoload');
}


function apf_error_handler($errno, $errstr, $errfile, $errline) {
    if (($errno & error_reporting()) != 0) {
        $level_names = array(
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE' );
        if (defined('E_STRICT')) {
            $level_names[E_STRICT]='E_STRICT';
        }
        $levels=array();
        $value=$errno;
        if (($value&E_ALL)==E_ALL) {
            $levels[]='E_ALL';
            $value&=~E_ALL;
        }
        foreach($level_names as $level=>$name) {
            if(($value&$level)==$level) $levels[]=$name;
        }

        $a = @debug_backtrace(false);

        $trace = format_trace($a);
        $apf = APF::get_instance();
        if ($apf->get_config('display_error')) {
            echo implode(' | ',$levels), " ". $errstr, ", TRACE: " ,$trace;
        }
        $logger = $apf->get_logger();
        $logger->warn("error_handler", implode(' | ',$levels), " ". $errstr, ", TRACE: " ,$trace);
    }
    return TRUE;
}

/**
 * 记录程序异常
 * @param Exception $exception
 */
function apf_exception_handler($exception) {
    $logger = APF::get_instance()->get_logger();
    $trace = format_trace($exception->getTrace());
    $trace = "exception '"
        . get_class($exception) . "' with message '"
        . $exception->getMessage()."' in "
        . $exception->getFile() . ":"
        . $exception->getLine() . " Stack trace: " . $trace;
    $logger->warn("apf_exception_handler", $trace);
    return TRUE;
}

/**
 * 格式化debug_backtrace
 * @param $trace
 */
function format_trace($trace) {
    if (!is_array($trace)) {
        return;
    }
    //出错时的url
    $error_url = @$_SERVER['REQUEST_URI'];
    $http_host = @$_SERVER['HTTP_HOST'];
    $http_refer = @$_SERVER['HTTP_REFERER'];
    $trace_str = "error_url : {$http_host}{$error_url} refer : $http_refer";
    foreach ($trace as $key=>$val) {
        $trace_str .= "#{$key} ".@$val['file']." (".@$val['line'].") : ";
        if (isset($val['class'])) {
            $trace_str .= "{$val['class']}{$val['type']}";
        }

        $trace_str .= "{$val['function']}(";
        if (is_array(@$val['args'])) {
            foreach ($val['args'] as $v) {
                $_v = preg_replace('#[\r\n \t]+#',' ',print_r($v,true));
                $_v = substr($_v,0,100);
                $trace_str .= $_v .",";
            }
            $trace_str = rtrim($trace_str,',');
        }
        $trace_str .=") ";
    }
    return $trace_str;
}

/**
 * 导入文件，对php原生require_once的封装和扩展
 * @param string $file 文件名
 * @param string $prefix 上一级目录
 * @return boolean
 */
function apf_require_file($file, $prefix="lib") {
    global $G_LOAD_PATH;
    if (defined('CACHE_PATH') && $prefix != "lib") {
        $f = apf_class_to_cache_file($file,$prefix);
        if (file_exists($f)) {
            if (!apf_required_files($file,$prefix)) {
                require_once "$f";
            }
            return true;
        }
    }
    foreach ($G_LOAD_PATH as $path) {
        if (file_exists("$path$prefix/$file")) {
            if (!defined('CACHE_PATH') || !apf_required_files($file,$prefix)) {
                require_once("$path$prefix/$file");
                if (defined('CACHE_PATH') && $prefix != "lib") {
                    apf_save_to_cache($file,$prefix,"$path$prefix/$file");
                }
            }
            return true;
        }
    }
    return false;
}

/**
 * 判断指定文件是否已经被载入（缓存）
 * @param string $file 文件名
 * @param string $prefix 上一级目录
 */
function apf_required_files ($file,$prefix) {
    global $cached_files;
    $f = $prefix . DIRECTORY_SEPARATOR . $file;
    if (in_array($f,$cached_files)) {
        return true;
    } else {
        $cached_files[] = $f;
    }
    return false;
}

/**
 * 判断源文件是否被压缩
 * @param string $file 文件名
 * @param string $prefix 上一级目录
 */
function apf_file_cache_exist ($file,$prefix) {
    $dest_file = apf_class_to_cache_file($file,$prefix);
    if (file_exists($dest_file)) {
        return $dest_file;
    } else {
        return false;
    }
}

/**
 * 返回源文件对应的压缩文件路径
 * @param string $file 文件名
 * @param string $prefix 上一级目录
 */
function apf_class_to_cache_file ($file,$prefix) {
    return CACHE_PATH . $prefix . "/" . $file;
}

/**
 * 去掉指定文件的空白、注释，然后存储到CACHE_PATH目录中
 * @param string $file 文件名
 * @param string $prefix 上一级目录
 * @param string $source 源文件名
 */
function apf_save_to_cache ($file,$prefix,$source) {
    $dest_file = apf_class_to_cache_file($file,$prefix);
    if (file_exists($dest_file)) {
        return ;
    }
    $dir = dirname($dest_file);
    if (!is_dir($dir)) {
        @mkdir($dir,0775,TRUE);
    }
    file_put_contents($dest_file,@php_strip_whitespace($source));
}

function import($class , $prefix="classes" , $firelog = true) {
    $file = apf_classname_to_filename($class,'.');
    $flag = true;

    if (!apf_require_file("$file.php", $prefix)) {
        if ($firelog) {
            $logger = APF::get_instance()->get_logger();
            //出错时的url
            $error_url = @$_SERVER['REQUEST_URI'];
            //屏蔽由于某些蜘蛛将url处理成小写时，引发的class not found
            if (preg_match('#\.js$|\.css$#', $error_url)) {
                return false;
            }
            $http_host = @$_SERVER['HTTP_HOST'];
            $http_refer = @$_SERVER['HTTP_REFERER'];
            $logger->error("'$prefix/$class' not found error_url : {$http_host}{$error_url} refer : $http_refer");
            //add by jackie for more error infomation
            ob_start();
            debug_print_backtrace();
            $trace = ob_get_contents();
            ob_end_clean();
            $logger->error($trace);
        }
        return false;
    }
    return $flag;
}

/**
 * 导入v2类
 * @param string $class 类名
 * @param string $prefix 父目录
 * @param string $firelog
 * @return boolean
 */
function apf_require_class($class, $prefix="classes" , $firelog = true) {
    if(defined('G_LOAD_AUTO') && G_LOAD_AUTO === true){ return true; }
    if($prefix=="classes" && class_exists($class)){
        return true;
    }
    $file = apf_classname_to_filename($class);
    $flag = true;
    if (!apf_require_file("$file.php", $prefix)) {
        if ($firelog) {
            $logger = APF::get_instance()->get_logger();
            //出错时的url
            $error_url = @$_SERVER['REQUEST_URI'];
            //屏蔽由于某些蜘蛛将url处理成小写时，引发的class not found
            if (preg_match('#\.js$|\.css$#', $error_url)) {
                return false;
            }
            //trigger_error("'$prefix/$class' not found", E_USER_ERROR);
            $http_host = @$_SERVER['HTTP_HOST'];
            $http_refer = @$_SERVER['HTTP_REFERER'];
            $logger->error("'$prefix/$class' not found error_url : {$http_host}{$error_url} refer : $http_refer");
            //add by jackie for more error infomation
            ob_start();
            debug_print_backtrace();
            $trace = ob_get_contents();
            ob_end_clean();
            $logger->error($trace);
        }
        return false;
    }
    return $flag;
}

/**
 * 导入v2控制器，apf_require_class的简单封装。
 * @param string $class 类名
 * @param string $firelog 日志开关
 * @return boolean
 */
function apf_require_controller($class, $firelog=true) {
    if(defined('G_LOAD_AUTO') && G_LOAD_AUTO === true){return true;}
    if(class_exists($class."Controller")){
        return true;
    }
    return apf_require_class($class, "controller" , $firelog);
}

/**
 * 导入v2拦截器，apf_require_class的简单封装。
 * @param string $class 类名
 * @return boolean
 */
function apf_require_interceptor($class) {
    if(defined('G_LOAD_AUTO') && G_LOAD_AUTO === true){return true;}
    if(class_exists($class."Interceptor")){
        return true;
    }
    return apf_require_class($class, "interceptor");
}

/**
 * 导入v2组件，apf_require_class的简单封装。
 * @param string $class 类名
 * @return boolean
 */
function apf_require_component($class) {
    if(!(defined('G_LOAD_AUTO') && G_LOAD_AUTO === false)){return true;}
    if(class_exists($class."Component")){
        return true;
    }
    return apf_require_class($class, "component");
}

/**
 * 导入v2页面，apf_require_class的简单封装。
 * @param string $class 类名
 * @return boolean
 */
function apf_require_page($class) {
    if(!(defined('G_LOAD_AUTO') && G_LOAD_AUTO === false)){return true;}
    if(class_exists($class."Page")){
        return true;
    }
    return apf_require_class($class, "page");
}

/**
 * 类名转换成文件路径
 * 例如V2b_Solr_Property则返回v2b/solr/
 * @param string $class Class name
 * @return string Relative path
 */
function apf_classname_to_path($class , $explode = '_') {
    $paths = explode('_', $class);
    $count = count($paths) - 1;
    $path = "";
    for ($i = 0; $i < $count; $i++) {
        $path .= strtolower($paths[$i]) . DIRECTORY_SEPARATOR;
    }
    return $path;
}

/**
 * 类名转换成文件名（木有后缀）
 * 类名由下划线分割，最后一部分为类名，之前的为相对路径
 * 例如Solr_Property标志solr目录下的property
 * @param string $class Class name
 * @return string Relative path
 */
function apf_classname_to_filename($class , $explode = '_') {
    $paths = explode($explode, $class);
    $count = count($paths) - 1;
    $path = "";
    for ($i = 0; $i < $count; $i++) {
        $path .= strtolower($paths[$i]) . DIRECTORY_SEPARATOR;
    }
    $class = $paths[$count];
    return "$path$class";
}

/**
 * return value of array key
 * @param mixed $key
 * @param array $array
 */
function apf_array_value($key,$array){
    return array_key_exists($key, $array)?$array[$key]:null;
}




/**
 * 金铺优化error_handler
 */
function jinpu_error_handler($errno, $errstr, $errfile, $errline) {
    if (($errno & error_reporting()) != 0) {

        $level_names = array(
            E_ERROR => 'E_ERROR', E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE', E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR', E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR', E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR', E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE' );
        if (defined('E_STRICT')) {
            $level_names[E_STRICT]='E_STRICT';
        }
        $levels=array();
        $value=$errno;
        if (($value&E_ALL)==E_ALL) {
            $levels[]='E_ALL';
            $value&=~E_ALL;
        }
        foreach($level_names as $level=>$name) {
            if(($value&$level)==$level) $levels[]=$name;
        }
        $a = debug_backtrace(false);
        $trace = jinpu_format_trace($a,__FUNCTION__);
        $apf = APF::get_instance();
        if ($apf->get_config('display_error')) {
            echo implode(' | ',$levels), " ". $errstr, ", TRACE: " ,$trace;
        }
        $logger = $apf->get_logger();
        $logger->warn("error_handler", implode(' | ',$levels), " ". $errstr, ", TRACE: " ,$trace);
    }
    return TRUE;
}

/**
 * 金铺优化exception_handler
 */
function jinpu_exception_handler($exception) {
    $logger = APF::get_instance()->get_logger();
    $trace = jinpu_format_trace($exception->getTrace(),__FUNCTION__);
    $trace = "exception '".get_class($exception)."' with message '".$exception->getMessage()."' in ".$exception->getFile().":".$exception->getLine().", TRACE:  ".$trace;
    $logger->warn("apf_exception_handler", $trace);
    return TRUE;
}


/**
 * 金铺优化格式化debug_backtrace
 * @param $trace
 */
function jinpu_format_trace($trace,$call_func='') {
    if (!is_array($trace)) {
        return;
    }
    //出错时的url
    $error_url = @$_SERVER['REQUEST_URI'];
    $http_host = @$_SERVER['HTTP_HOST'];
    $http_refer = @$_SERVER['HTTP_REFERER'];
    $trace_str = "error_url : {$http_host}{$error_url} refer : $http_refer";
    $trace_str .= defined('RELEASE_VERSION')?' version : '.RELEASE_VERSION:'';
    $file_tmp = '';
    foreach ($trace as $key=>$val) {
        if (@$val['class'] == 'APF' || !isset($val['file'])) {
            continue;    //escape APF trace and none file trace
        }
        $_file = preg_replace('#^/home/www/release/[\w-_]+/#', '', @$val['file']); //simplify file name
        if ($_file==$file_tmp) { //escape same file name
            $_file = '';
        } else {
            $file_tmp = $_file;
        }
        $trace_str .= " #{$key} ".$_file." (".@$val['line'].") : ";
        if (isset($val['class'])) {
            $trace_str .= "{$val['class']}{$val['type']}";
        }

        $trace_str .= "{$val['function']}(";
        if (is_array(@$val['args']) && $val['function'] != 'trigger_error' && $val['function'] != $call_func) { //escape trigger_error and call_func params
            foreach ($val['args'] as $v) {
                $_v = preg_replace('#[\r\n \t]+#',' ',@var_export($v,true));
                $_v = substr($_v,0,100);
                $trace_str .= $_v .",";
            }
            $trace_str = rtrim($trace_str,',');
        }
        $trace_str .=")";
    }
    return $trace_str;
}
function apf_autoload($classname){
    $suffixes = array(
            'Controller',
            'Page',
            'Component',
            'Interceptor'
    );
    $type = 'classes';
    $class = $classname;
    $classlen = strlen($classname);
    foreach($suffixes as $suffex) {
        $index = strrpos($classname, $suffex);
        if($index && (($classlen - strlen($suffex)) == $index)) {
            $class = substr($class, 0, $index);
            $type = $suffex;
            break;
        }
    }
    apf_require_class($class, strtolower($type), false);
}

if(!function_exists('array_column')){
    function array_column(array $input, $columnKey, $indexKey = null)
    {
        $array = array();
        foreach ($input as $value) {
            if ( ! isset($value[$columnKey])) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }

            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            } else {
                if ( ! isset($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if ( ! is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }

        return $array;
    }
}

function check_date_time($str, $format="Y-m-d"){
    $unixTime=strtotime($str);
    $checkDate= date($format, $unixTime);
    if($checkDate==$str)
        return 1;
    else
        return 0;
}
