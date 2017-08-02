<?php
class Util_Currency {
    public static $cy_id; // 在拦截器里设置

    function get_cy_id() {
        if(self::$cy_id) return self::$cy_id;
        else return 12;
    }

    function set_cy_id($cy_id) {
        $area_bll = new Bll_Area_Area();
        // t_dest_config 以后最好就当做汇率表来用
        $dest_list = $area_bll->get_city_list();
        $currency_list = array_column($dest_list, "dest_id");
        if(in_array($cy_id, $currency_list)) {
            self::$cy_id = $cy_id;
        }else{
            $cookie_domain = APF::get_instance()->get_config('cookie_domain');
            unset($_COOKIE['currency_id']);
            setcookie('currency_id', null, -1, $cookie_domain);
        }
        return true;
    }

    function get_cookie_cy_id() {
        $cookie_id = $_COOKIE['currency_id'];
        if($cookie_id) {
            return $cookie_id;
        }
        return null;
    }

}
