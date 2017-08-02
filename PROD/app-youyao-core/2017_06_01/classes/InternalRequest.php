<?php
class InternalRequest {

    public function send_request($url, $data, $type) {

        if($type == 'POST') {
            $response = Util_Curl::post($url, json_encode($data), array("Content-Type"=>"application/json;"));
        }
        elseif($type == 'GET') {
            $response = Util_Curl::get($url, $data);
        }

        Logger::info(__FILE__, __METHOD__, __LINE__, var_export(
                array(
                    "url"=>$url,
                    "data"=>$data,
                    "response"=> $type,
                ), true)
            );
        if($response['code'] != 200) {
            Logger::info(__FILE__, __METHOD__, __LINE__, var_export(
                array(
                    "url"=>$url,
                    "data"=>$data,
                    "response"=> $response
                ), true));
            return array();
        }
        $result = json_decode($response['content'], true);
        if($result['code'] != 200) {
            Logger::info(__FILE__, __METHOD__, __LINE__, var_export(
                array(
                    "url"=>$url,
                    "data"=>$data,
                    "response"=> $response
                ), true));
            return array();
        }

        return $result['info'];
    }
}
