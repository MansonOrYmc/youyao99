<?php
apf_require_class("APF_Controller");

class My_RegisterpushidController extends APF_Controller
{

    public function handle_request()
    {

        header("Content-type: application/json");

        $req = APF::get_instance()->get_request();
        $params = $req->get_parameters();

        if ($params['type'] == 'jgpush') {
            self::handle_jgpush($params);
        } else if ($params['execute'] == 'bind') {
            self::bind_user_guid($params);
        } else if ($params['execute'] == 'unbind') {
            self::unbind_guid($params);
        } else if ($params['execute']=='close'){
            self::close_push($params);
        }
        else
            self::handle_devicetoken($params);
    }

    public function handle_jgpush($params)
    {

        if (!($params['email'] && $params['uid'] && $params['deviceid'] && $params['jgpush_id'])) {
            $jsonData=array(
                'msg' => 'empty params'
            );
            echo json_encode(Util_Beauty::wanna($jsonData));
            return;
        }

        try {
            $bll = new Bll_Push_Register();
            $bll->jgpush_register($params);
        } catch (Exception $e) {
            $jsonData=array(
                'error' => 500
            );
            echo json_encode(Util_Beauty::wanna($jsonData));
            print_r($e->getMessage());
            return;
        }

        if($params['beauty']=='true'){
            echo json_encode(Util_Beauty::wanna(array('code' => 1)));
        }else{
            echo json_encode(array('msg' => 'ok'));
        }
    }

    public function  handle_devicetoken($params)
    {
        $execute = $params['execute'];
        $type = $params['type'];
        $token = $params['token'];
        $guid = $params['guid'];
        $os = $params['os'];


        $bll = new Bll_Push_Register();
        if ($execute == 'delete') {
            $result = $bll->device_delete_by_guid($guid);
        } else if ($execute == 'add') {

//todo   ios jpush
            if ($type == 'jpush' && $os == 'ios' && $params['jgpush_id']) {
                //just save the apple_token with the jpush_id
                $jpush_id = $params['jgpush_id'];;
                $bll->save_apple_token($token, $jpush_id);

                $params['token'] = $params['jgpush_id'];
            }

            if (!($type && $token && $guid && $os)) {
                Util_Json::render(400, 'empty params', '缺少参数');
                return;
            }
            $result = $bll->device_register_by_guid($params);
        }


        if ($result) {
            Util_Json::render(200, 'ok', 'ok');
        } else {
            Util_Json::render(500, 'fail', "fail");
        }

    }
    public function bind_user_guid($params){

        $guid=$params['guid'];
        $uid=$params['mobile_userid'];

        if (empty($guid)||empty($uid)) {
            Util_Json::render(400, 'params needed', 'params needed');
            return;
        }

        $bll=new Bll_Push_Register();
       $result= $bll->user_bind_guid($uid,$guid);
        if($result){
            Util_Json::render(200,'ok','ok');
        }else Util_Json::render(500,'ok','ok');
    }
    //解除绑定    用于用户退出登录时
    public function unbind_guid($params)
    {

        if (!$params['guid']) {
            Util_Json::render(400, 'guid needed', 'guid needed');
            return;
        }

        $bll = new Bll_Push_Register();
        if ($bll->unbind_guid($params['guid'])) {
            Util_Json::render(200, 'ok', 'ok');
        } else {
            Util_Json::render(500, 'fail', 'fail');
        }

    }

    //关闭推送  用于用户设置接收推送
    public function close_push($params){
        if (!$params['guid']) {
            Util_Json::render(400, 'guid needed', 'guid needed');
            return;
        }

        $bll = new Bll_Push_Register();
        if ($bll->unbind_guid($params['guid'])) {
            Util_Json::render(200, 'ok', 'ok');
        } else {
            Util_Json::render(500, 'fail', 'fail');
        }

    }


}
