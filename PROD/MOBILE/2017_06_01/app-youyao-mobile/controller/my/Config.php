<?php
apf_require_class("APF_Controller");

class My_ConfigController extends APF_Controller
{

    public function handle_request()
    {

        $params = APF::get_instance()->get_request()->get_parameters();


        $pm_filter_notice = array('type' => 'pm_filter_notice', 'value' => 1);
        $card_service_style = array('type' => 'card_service_style', 'value' => 1);

        $huanxin = array('type' => 'huanxin', 'value' => 0);

        if ($this->hxcheck($params)) $huanxin['value'] = 1;

        $bookmode = array('type' => 'quick_book', 'value' => 0);
        if ($_REQUEST['multilang'] == 12 || stripos($_SERVER['REQUEST_URI'], 'multilang=12') !== false) {
            $fcode = array('type' => 'fcode', 'value' => 1);
        } elseif ($_REQUEST['multilang'] == 10 || stripos($_SERVER['REQUEST_URI'], 'multilang=10') !== false) {
            $fcode = array('type' => 'fcode', 'value' => 1);
        } else {
            $fcode = array('type' => 'fcode', 'value' => 0);
        }

        $data=array($huanxin, $bookmode, $fcode,$pm_filter_notice,$card_service_style);

        $result = array(
            'status' => 200,
            'msg' => "",
            "userMsg" => "",
            'response' => $data,
            'data' => $data,

        );
        header('Content-Type:application/json');
        Util_ZzkCommon::zzk_echo(json_encode($result));
        return true;
    }

    private function  hxcheck($params)
    {

        if (time() < strtotime("2016-05-19 +9 hour"))
            return false;

        if ($params['os'] == 'android' && $params['version'] >= 82) return true;


        if ($params['os'] == 'ios' && version_compare($params['version'], '5.0.4', '>='))
            return true;

        if (strpos($_SERVER['HTTP_USER_AGENT'], '5.0.4')) {
            return true;
        }


        return false;

    }

}
