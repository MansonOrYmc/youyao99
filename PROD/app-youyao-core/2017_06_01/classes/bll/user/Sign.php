<?php

class Bll_User_Sign {

    // 匿名下单登录
    public function obtain_user_by_phone_mail($phone, $mail) {

        $bll_user = new Bll_User_UserInfo();
        $uid_by_phone = $bll_user->get_user_info_by_phone_num($phone);
        if ($uid_by_phone) {
            $uid_by_mail = $uid_by_phone['uid'];
            $new_user = $bll_user->signin($uid_by_phone['phone_num'], null, true);
        }
        else{
            $uid_by_mail_info = $bll_user->get_user_info_by_email($mail);
            $uid_by_mail = $uid_by_mail_info['uid'];
            $new_user = $bll_user->signin($uid_by_mail_info['mail'], null, true);
        }

        if(!$new_user['userid']) {
            $default_password = 'youyao';
            $nickname = substr_replace($phone, '***', 3, -3);
            $new_account = array(
                'name' => 'zzk' . substr(md5($mail), -6, 6) . '_' . date('Y'),
                'mail' => $mail,
                'init' => $mail,
                'pass' => $default_password,
                'status' => '1',
                'roles' => array('2' => 1, '3' => 0, '4' => 0, '5' => 0),
                'notify' => '0',
                'timezone' => 'Asia/Shanghai',
                'form_id' => 'user_register_form',
                'signature_format' => 'plain_text',
                'administer_users' => 1,
                'order_user_reg_mail' => 1,
                'from' => '',
                'channel' => '',
                'cache' => '0',
                'hostname' => Util_NetWorkAddress::get_client_ip(),
                'uid' => '0',
                'dest_id' => ($_REQUEST['multilang'] ? $_REQUEST['multilang'] : 12), // 默认12
                'mobile_number' => $phone,
                'phone_num' => $phone,
            );

            if ($new_register_user = $bll_user->user_register($new_account, $new_account)) {
                $bll_user->insert_or_update_nickname($new_register_user['uid'], $nickname);
                $new_user = $bll_user->signin($mail, $default_password);
                $content = '【自在客】您好，您下次可直接使用手机号' . $phone . '登录自在客，初始密码:' . $default_password . '，为更方便安全的使用，建议您尽快修改密码';
                $params = array(
                    'oid' => 0,
                    'sid' => 0,
                    'uid' => $new_user['userid'],
                    'mobile' => $new_user['mobile'],
                    'content' => $content,
                    'area' => 1,
                );
                $sms = new Util_Notify();
                $sms->send_sms_notify($params);
            }

            $_SESSION['user_info'] = $new_user;

        }

        return $new_user;

    }
}
