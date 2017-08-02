<?php
class Bll_User_Dictionary {

    public function city_list($dest_id) {
        $path = 'area';
        $data = array(
            'multilang' => Util_Language::get_locale_id(),
            'destId'    => $dest_id,
        );

        return self::set_request($path, $data);
    }

    public function city_by_id($id) {
        $path = "getArea";
        $data = array(
            'multilang' => Util_Language::get_locale_id(),
            'id'        => $id,
        );

        return self::set_request($path, $data);
    }

    public function dic_list($type) {
        $path = "dic";
        $data = array(
            'multilang'  => Util_Language::get_locale_id(),
            'multiprice' => Util_Currency::get_cy_id(),
            'type'      => $type,
        );

        return self::set_request($path, $data);
    }

    public function dic_by_id($id) {
        $path = "getDic";
        $data = array(
            'multilang' => Util_Language::get_locale_id(),
            'multiprice' => Util_Currency::get_cy_id(),
            'id'        => $id,
        );

        return self::set_request($path, $data);
    }

    private function set_request($path, $data, $type='GET') {

        $java_host = APF::get_instance()->get_config("usercenter_api");
        $url = $java_host . "/" .  $path;

//        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($url, true));
//        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($data, true));
        if($type == 'POST') {
            $response = Util_Curl::post($url, json_encode($data), array("Content-Type"=>"application/json;"));
        }
        elseif($type == 'GET') {
            $response = Util_Curl::get($url, $data);
        }
        if($response['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($response, true));
        }
        $result = json_decode($response['content'], true);
        if($result['code'] != 200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($result, true));
        }

        return $result['info'];
    }
}
