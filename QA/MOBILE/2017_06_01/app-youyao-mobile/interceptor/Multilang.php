<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/10/12
 * Time: 下午6:55
 */

class MultilangInterceptor extends APF_Interceptor {

    public function before () {
        $ret = parent::before();
        if ($ret != self::STEP_CONTINUE) {
            return $ret;
        }
        $apf = APF::get_instance();
        $req = $apf->get_request();
        $params = $req->get_parameters();
        if(isset($params['multilang'])&&$params['multilang']=='10')
        {
            $params['multilang']=null;
            $query_string='';
            foreach($params as $k=>$v){
                if($k != 'multilang'){
                    $query_string.="&$k=$v";
                }
            }
            $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $url = str_replace('multilang=10','',$url);
            $url = str_replace('%3f','',$url);
            $url = str_replace('?','',$url);
            $url.=  '?vruan=handsome_man'.$query_string;
            $str = file_get_contents($url);
            if(!is_null(json_decode($str,true))){
                $str = json_decode($str,true);
                $str = Util_ZzkCommon::simple2tradition($str);
                //print_r($str);
                header('Content-Type:application/json');
                echo json_encode($str);
            }else {
                $str = Util_ZzkCommon::simple2tradition($str);
                echo $str;
            }
            exit;
        }
        return self::STEP_CONTINUE;
    }
}
?>