<?php

class Bll_Push_Register
{
    private $pushInfoDao;

    public function __construct()
    {
        $this->pushInfoDao = new Dao_Push_PushInfo();
    }

    public function jgpush_register($params)
    {
        $data = $this->pushInfoDao->get_pushinfo_byemail($params['email']);
        if ($data) {
            return $this->pushInfoDao->update_jfdevice($params['email'], $params['jgpush_id']);
        }
        return $this->pushInfoDao->insert_jfdevice($params['email'], $params['uid'], $params['deviceid'], $params['jgpush_id']);
    }

    public function device_register_by_guid($params)
    {//单个guid 只能绑定一个token 避免重复推送 的问题     所以  huaxin 不要在这里绑定
        //gcm jpush baidu apple huanxin
        $type_arr=array('gcm','baidu','baidu','jpush','apple');

        $guid = $params['guid'];
        $type = $params['type'];
        $os = $params['os'];
        $token = $params['token'];


        if(!in_array($type,$type_arr))
        {
            //todo
        }


        // 为了避免 设备重新安装后  token没变  guid会变 导致的重复推送问题. 在新的guid 绑定相同token的时候, 解除老guid绑定
        $exists = $this->pushInfoDao->get_device_by_token($token,$type);
        foreach ($exists as $k => $v) {
            if ($v['guid'] != $guid)
                $this->pushInfoDao->delete_push_device_token_by_guid($v['guid']);
        }

        $data = $this->pushInfoDao->get_device_token_by_guid($guid);

        if (!$data) {
            return $this->pushInfoDao->insert_push_device_token($guid, $token, $type, $os);
        } else {
            return $this->pushInfoDao->update_push_device_token($guid, $token, $type);
        }
    }

    public function save_apple_token($apple_token, $jpush_id)
    {
        $result = $this->pushInfoDao->get_device_by_jpush_id($jpush_id);
        if ($result)
            $this->pushInfoDao->update_apple_token_by_jpush($apple_token, $jpush_id);
        else $this->pushInfoDao->save_apple_token_by_jpush($apple_token, $jpush_id);
    }

    public function device_delete_by_guid($guid)
    {
        return $this->pushInfoDao->delete_push_device_token_by_guid($guid);
    }

    public function user_bind_guid($uid,$guid){
        return $this->pushInfoDao->user_bind_guid($uid,$guid);
    }

    public function unbind_guid($guid){
        return $this->pushInfoDao->unbind_guid($guid);
    }
    public function close_push($guid){
        return $this->pushInfoDao->close_push($guid);
    }

}
