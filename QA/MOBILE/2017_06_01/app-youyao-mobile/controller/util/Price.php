<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/11/5
 * Time: 下午7:31
 */
apf_require_class("APF_Controller");
class Util_PriceController extends APF_Controller
{
    public function handle_request()
    {
        if(!$_REQUEST['multiprice'])
        {
            $_REQUEST['multiprice']=12;
        }

        if($_REQUEST['price']>0) {
            echo Util_Common::zzk_price_convert($_REQUEST['price'],$_REQUEST['dest_id'],$_REQUEST['multiprice']);
        }
    }
}