<?php

apf_require_class('My_Controller', 'controller');

class User_PhoneLoginController extends My_Controller
{
    /**
     * @var APF_Request
     */
    private $request;

    public function init()
    {
        $this->request = APF::get_instance()->get_request();
        header('Content-Type:application/json');
    }

    public function sendSms()
    {
        $param_arr = $this->request->get_parameters();

        $security = Util_Security::Security($param_arr);
        if (!$security) {
            Util_Json::render(400, null, "request forbidden", 'Illegal_request');
            return false;
        }

        $phoneNum = $param_arr['phoneNum'];
        $areaNum = $param_arr['areaNum'];

        if ($this->phone_num_format_check($areaNum, $phoneNum)) {
            $userbll = new Bll_User_UserInfo();
            $codelist = $userbll->get_sms_captcha_by_phone($phoneNum);

            $interval = APF::get_instance()->get_config("phone_captcha_time");
            if ($codelist[0]['create_time'] > time() - $interval) {
                $response = array(
                    'status' => 201,
                    'data' => array(
                        'phoneNum' => $phoneNum,
                        'interval' => $interval - (time() - $codelist[0]['create_time']),
                    ),
                    'userMsg' => $interval . 'seconds_not_resend_verification_code',
                    'msg' => $interval .  'seconds_not_resend_verification_code',
                );
            } else {
                $code = $userbll->insert_sms_captcha($phoneNum);
                if ($code) {
                    $areaNumMap = array(
                        '86' => 1,
                        '886' => 2,
                    );
                    $area = $areaNumMap[strval(intval($areaNum))];
                    $dest_id = 10;
                    $smsbll = new Bll_Sms_SMSInfo();
                    $content = 'verify_code' . $code . ' ，'.'Welcome_please_complete_verification';
                    $args = array(
                        0, //    "oid"
                        0, //    "sid"
                        0, //    "uid"
                        $phoneNum, //    "mobile"
                        $content, //    "content"
                        $area, //    "area"
                        $dest_id, //    "dest_id"
                        0, //    "retry"
                        0, //    "status"
                        time(), //    "create_time"
                    );
                    $smsbll->bll_send_sms_notify($args);
                }
                $response = array(
                    'status' => 200,
                    'data' => array(
                        'phoneNum' => $phoneNum,
                        'interval' => $interval,
                        'displayMsg' => 'Verificationcodehasbeensent',
                    ),
                    'userMsg' => 'Verificationcodehasbeensent',
                    'msg' => 'Verificationcodehasbeensent',
                );
            }

        } else {
            $response = array(
                'status' => 400,
                'data' => null,
                'userMsg' => 'Wrong_format_of_phone_number',
                'msg' => 'Wrong_format_of_phone_number',
            );
        }
        Util_Json::render($response['status'], $response['data'], $response['msg'], $response['userMsg']);
        return false;
    }

    public function codeVerify()
    {
        $param_arr = $this->request->get_parameters();
        $areaNum = $param_arr['areaNum'];
        $phone = $param_arr['phoneNum'];
        $code = $param_arr['code'];

        // 再验证一次信息
        if ($this->phone_num_format_check($areaNum, $phone)) {
            if ($this->verify_smscode($phone, $code)) {
                $userbll = new Bll_User_UserInfo();
                $userInfo = $userbll->get_user_info_by_phone_num($phone);
                if (empty($userInfo)) {
                    $areaNumMap = array(
                        '86' => 12,
                        '886' => 10,
                    );
                    $dest_id = $areaNumMap[strval(intval($areaNum))];

                    $default_name = 'zzk' . substr(md5($phone), -6, 6) . '_' . time();
                    $default_mail = $default_name . "@zzkzzk.com";
                    $default_password = 'youyao';
                    $name = $default_name;
                    $mail = $default_mail;

                    $user_controller = new User_RegisterController();
                    $response = $user_controller->user_write($name, $phone, $mail, $default_password, $dest_id, $param_arr['os'], substr_replace($phone, '***', 3, -3));
                    $content = 'next_time_you_can_use_phone_number_directly' . $phone . 'Login_initial_password:' . $default_password . '，'.'convenient_change_the_password';
                    $params = array(
                        'oid' => 0,
                        'sid' => 0,
                        'uid' => $response['data']['userid'],
                        'mobile' => $phone,
                        'content' => $content,
                        'area' => $dest_id == 12 ? 1 : 2,
                    );
                    $sms = new Util_Notify();
                    $sms->send_sms_notify($params);
                    $userInfo = $userbll->get_user_info_by_phone_num($phone);
                    $userInfo['user_token'] = $response['user_token'];
                } else { // 触发一下登录操作
                    $response = $userbll->signin($phone, "", true);
                    $userInfo['user_token'] = $response['user_token'];
                }
            } else {
                $err_msg = 'SMS_verification_code_error';
            }
            if (!empty($err_msg)) {
                $response = array(
                    'status' => 400,
                    'data' => null,
                    'userMsg' => $err_msg,
                    'msg' => $err_msg,
                );
            } else {

                $userInfo = $userbll->get_data_by_user($userInfo);

                if ($param_arr['os'] == 'ios' && $param_arr['version'] > 4.6) {
                    $needextra = 1;
                }
                if ($param_arr['os'] == 'android' && $param_arr['version'] > 47) {
                    $needextra = 1;
                }

                if ($userInfo && $needextra) {
                    $mult_uids = $userbll->get_mult_uids($userInfo['userid']);
                    $extra = array();

                    foreach ($mult_uids as $kk => $v) {
                        $b_uid = $v['b_uid'];
                        $b_info = $userbll->get_user_by_uid($b_uid);
                        $info_data = $userbll->get_data_by_user($b_info);
                        $extra[] = $info_data;
                    }
                    $userInfo['extra'] = $extra;
                }

                $response = array(
                    'status' => 200,
                    'data' => $userInfo,
                    'userMsg' => null,
                    'msg' => null,
                );
            }
        } else {
            $response = array(
                'status' => 400,
                'data' => null,
                'userMsg' => 'phonenumformatisincorrect',
                'msg' => 'phonenumformatisincorrect'
            );
        }
        Util_ZzkCommon::zzk_echo(json_encode($response));
    }

    private function phone_num_format_check($areaNum, $phoneNum)
    {
        $areaNum = intval($areaNum);
        if (($areaNum == 86 && preg_match('/\d{11}/', $phoneNum)) ||
            ($areaNum == 886 && preg_match('/\d{9,10}/', $phoneNum))
        ) {
            return true;
        } else {
            return false;
        }

    }

    private function verify_smscode($phonenum, $smsCode)
    {

        $userbll = new Bll_User_UserInfo();
        $phone = $userbll->get_sms_captcha_by_phone($phonenum);
        foreach ($phone as $row) {
            if ($row['code'] == $smsCode) {
                return true;
            }
        }

        return false;
    }
}
