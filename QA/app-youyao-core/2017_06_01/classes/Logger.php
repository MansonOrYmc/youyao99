<?php

class Logger
{
    public static function info()
    {
        $file_name = APP_PATH . 'log/' . date('Y-m-d') . '.log';
        $content = func_get_args();
        $log_dir = dirname($file_name);
        if (!is_dir($log_dir)) {
            $result = mkdir($log_dir, 0777, true);
        }
        $result = file_put_contents($file_name, date('H:i:s') . ' INFO' . "\t" . join("\t", $content) . PHP_EOL, FILE_APPEND);
    }

    public static function debug($prefix = '', $json)
    {
        $file_name = '/data2/log/soj/' . $prefix . date('Y-m-d') . '.log';
        $content = $json;
        $result = file_put_contents($file_name, $content . PHP_EOL, FILE_APPEND);
    }

    public static function writeToElk($connectStr = 'tcp://192.168.8.7:8888', $message = '', $context = array(), $level = 'INFO') {
        $fp = fsockopen($connectStr, null, $errno, $errMessage, 5);
        if($fp) {
            $context = array_merge($context, array('time' => date('Y-m-d H:i:s'), 'file'=>__FILE__, 'line' => __LINE__, 'class' => __CLASS__, 'method' => __FUNCTION__));
            $data = sprintf("%s %s %s", $level, $message, json_encode($context, JSON_UNESCAPED_SLASHES+JSON_UNESCAPED_UNICODE));
            fwrite($fp, $data);
            fclose($fp);
        } else {
            die($errMessage);
        }
    }
}
