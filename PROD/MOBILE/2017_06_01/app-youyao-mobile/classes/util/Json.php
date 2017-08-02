<?php

class Util_Json
{
    public static function render($status, $data, $msg = null, $usr_msg = null, $translate = true, $extra = null)
    {
        $response = array(
            'status' => strval($status),
            'data' => $data,
            'userMsg' => $usr_msg,
            'msg' => $msg,
        );
        if($extra) {
            $response['extra'] = $extra;
        }
        header('Content-Type:application/json');
        if ($translate && $_REQUEST["multilang"] == 10) {
            $response_json_str = json_encode($response);
            $response_json_str = preg_replace_callback('/(?<!\\\\)\\\\u(\w{4})/', function ($matches) {
                return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
            }, $response_json_str);
            $url = 'http://api.prod.youyao.com/2.0/common/translate?langue=ZH_TW';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $response_json_str);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
            $translated_result = curl_exec($ch);
            $translated_result = json_decode($translated_result, true);
            if ($translated_result['code'] == 200) {
                echo json_encode(json_decode($translated_result['info'], true));
            }
        } else {
            echo json_encode($response);
        }
        return true;
    }
}
