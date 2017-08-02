<?php

apf_require_class('My_Controller', 'controller');

class User_RegisterController extends My_Controller
{
    public function init()
    {
        header('Content-Type: application/json');
    }

    public function codeVerify()
    {
        $param_arr = APF::get_instance()->get_request()->get_parameters();

        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($param_arr, true));
        $security = Util_Security::Security($param_arr);
          
        if (!$security) {
            $response = json_encode(array(
                'code' => 400,
                'codeMsg' => 'Illegal_request',
                'status' => 400,
                'msg' => "request forbidden",
                "userMsg" => 'Illegal_request',
            ));
            //Util_ZzkCommon::zzk_echo($response);
            //Logger::debug('codeVerify', json_encode(array_merge($response, array('guid' => $param_arr['guid']))));

            return false;
        }
        $phoneNum = $param_arr['phonenum'];
        $areaNum = '86';
        $code = $param_arr['code'];

        //添加ip策略,防止暴力破解验证码
        $ip = Util_NetWorkAddress::get_client_ip();
        $key = 'mobile_user_register_code_verify_' . md5($ip);
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();

        if (in_array($ip, array('127.0.0.1', '10.0.0.1')) || stripos($ip, '192.168.1') !== false) {
            $cache = 0;
        } else {
            $cache = $memcache->get($key);
        }

        if ($cache > 30) {
            $response = array(
                'status' => 400,
                'data' => null,
                'userMsg' =>'Please try again later',
                'msg' => 'Please try again later',
            );
        } else {
            $memcache->set($key, $cache + 1, 0, 3600);
            $userbll = new Bll_User_UserInfo();
            if ($this->phone_num_format_check($areaNum, $phoneNum)) {
                $userInfo = $userbll->get_user_info_by_phone_num($phoneNum);
                if (!empty($userInfo)) {
                    $response = array(
                        'status' => 400,
                        'data' => null,
                        'userMsg' => 'The phone number has been registered', //手机号码已经注册
                        'msg' => 'The phone number has been registered',
                    );
                } else {
                    $userbll = new Bll_User_UserInfo();
                    $codelist = $userbll->get_sms_captcha_by_phone($phoneNum);
                    Logger::info(__FILE__, __CLASS__, __LINE__, var_export($codelist, true));
                    foreach ($codelist as $row) {
                        if ($row['code'] == $code) {
                            $response = array(
                                'status' => 200,
                                'data' => array(
                                    'phoneNum' => $phoneNum,
                                    'interval' => time() - $codelist[0]['create_time'],
                                ),
                                'userMsg' => 'Correct codes',
                                'msg' => 'Correct codes',
                            );
                            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($response, true));
                            break;
                        }
                    }

                    if (empty($response)) {
                        $response = array(
                            'status' => 400,
                            'data' => null,
                            'userMsg' => 'SMS verification code error',
                            'msg' => 'SMS verification code error',
                        );
                    }
                }
            } else {
                $response = array(
                    'status' => 400,
                    'data' => null,
                    'userMsg' => 'Malformed phone',
                    'msg' => 'Malformed phone',
                );
            }
        }
        Util_ZzkCommon::zzk_echo(json_encode($response));
        Logger::info('codeVerify', json_encode(array_merge($response, array('guid' => $param_arr['guid']))));
    }

    public function phoneVerify()
    {
        $param_arr = APF::get_instance()->get_request()->get_parameters();

        $security = Util_Security::Security($param_arr);
        if (!$security) {
            $response = json_encode(array(
                'code' => 400,
                'codeMsg' =>'Illegal_request',
                'status' => 400,
                'msg' => "request forbidden",
                "userMsg" => 'Illegal_request',
            ));
            Logger::debug('phoneVerify', json_encode(array_merge(json_decode($response, true), array('guid' => $param_arr['guid']))));

            return false;
        }
        $phoneNum = $param_arr['phonenum'];
        $areaNum = '86';
        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($param_arr, true));

        $userbll = new Bll_User_UserInfo();
        if ($this->phone_num_format_check($areaNum, $phoneNum)) {
            //$userInfo = $userbll->get_user_info_by_phone_num($phoneNum);
            $userInfo = array();
            if (!empty($userInfo)) {
                $response = array(
                    'status' => 400,
                    'data' => null,
                    'userMsg' => 'The_phone_number_has_been_registered',
                    'msg' => 'The_phone_number_has_been_registered',
                );
                //self::signin();
            } else {
                $cap_md5 = md5(strtolower($params['captcha']) . 'yya');
                $captval = explode(',', $_SESSION['captval']);
                //if ($cap_md5 != $captval[1]) {
                if (false) {
                    $response = array(
                        'status' => 400,
                        'data' => null,
                        'userMsg' => 'captcha error',
                        'msg' => 'captcha error',
                    );
                    Util_ZzkCommon::zzk_echo(json_encode($response));
                    Logger::info(__FILE__, __CLASS__, __LINE__, $cap_md5.":".$captval[1]);
                    Logger::info(__FILE__, __CLASS__, __LINE__, var_export($_SESSION, true));
                    return;
                }
                $userbll = new Bll_User_UserInfo();
                $codelist = $userbll->get_sms_captcha_by_phone($phoneNum);
                $ip = Util_NetWorkAddress::get_client_ip();
                $key = md5($ip);
                $memcache = APF_Cache_Factory::get_instance()->get_memcache();
                if (in_array($ip, array('127.0.0.1', '10.0.0.1')) || stripos($ip, '192.168.1') !== false) {
                    $cache = 0;
                } else {
                    $cache = $memcache->get($key);
                }
                if ($cache > 4) {
                    $response = array(
                        'status' => 400,
                        'data' => null,
                        'userMsg' => 'Please_try_again_later',
                        'msg' =>'Please_try_again_later',
                    );
                } else {
                    $memcache->set($key, $cache + 1, 0, 3600);

                    $interval = APF::get_instance()
                        ->get_config("phone_captcha_time");
                    if ($codelist[0]['create_time'] > time() - $interval) {
                        $response = array(
                            'status' => 201,
                            'data' => array(
                                'phoneNum' => $phoneNum,
                                'interval' => $interval - (time() - $codelist[0]['create_time']),
                            ),
                            'userMsg' => $interval . 'seconds_not_resend_verification_code',
                            'msg' => $interval . 'seconds_not_resend_verification_code',
                        );
                    } else {
                        $code = $userbll->insert_sms_captcha($phoneNum);
                        if ($code) {
                            $areaNumMap = array(
                                '86' => 1,
                            );
                            $area = $areaNumMap[strval(intval($areaNum))];
                            $dest_id = 10;
                            $str_phone = $area == 2 ? '070-1000-8888' : '400-888-8888'; // cs number
                            $smsbll = new Bll_Sms_SMSInfo();
                            $content = 'verify_code' . $code . ",".'apply_registration_call_number' . $str_phone;

                            $timestamp = time();
                            $u_yyid = '';
                            $res = array(   
                              'id' => 0,
                              'mobile' => $phoneNum,
                              'v_code' => $code,
                              'datei' => date('Y-m-d', $timestamp),
                              'u_yyid' => $u_yyid,
                              'status' => 1,
                              'created' => $timestamp,
                            );
                            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($res, true));
                            $smsbll->bll_send_sms_notify($res);
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
                }
            }
        } else {
            $response = array(
                'status' => 400,
                'data' => null,
                'userMsg' => 'Wrong_format_of_phone_number',
                'msg' => 'Wrong_format_of_phone_number',
            );
        }

        Util_ZzkCommon::zzk_echo(json_encode($response));
        Logger::debug('phoneVerify', json_encode(array_merge($response, array('guid' => $param_arr['guid']))));
    }

    public function submit()
    {
        $param_arr = APF::get_instance()->get_request()->get_parameters();
        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($param_arr, true));
        $areaNum = '86';
        $phone = $param_arr['phonenum'];
        $code = $param_arr['code'];
        $password = '';

        //添加ip策略,防止暴力破解验证码
        $ip = Util_NetWorkAddress::get_client_ip();
        $key = 'mobile_user_register_' . md5($ip);
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();

        if (in_array($ip, array(
            '120.132.26.244',
            '120.132.27.138',
            '120.132.21.8',
            '45.79.167.208')) || stripos($ip, '192.168.1') !== false) {
            $cache = 0;
        } else {
            $cache = $memcache->get($key);
        }

        if ($cache > 30) {
            $response = array(
                'status' => 400,
                'data' => null,
                'userMsg' => 'Please_try_again_later',
                'msg' => 'Please_try_again_later',
            );
        } else {
            $memcache->set($key, $cache + 1, 0, 3600);

            if (empty($phone)) {
                $response = array(
                    'status' => 400,
                    'data' => null,
                    'userMsg' => 'need phone number',
                    'msg' => 'Phone number can not be empty',
                );
                Util_ZzkCommon::zzk_echo(json_encode($response));
                Logger::debug('register', json_encode(array_merge($response, array('guid' => $param_arr['guid']))));
                return false;
            }

            $userbll = new Bll_User_UserInfo();

            if ($phone) {
                // 再验证一次信息
                if ($this->phone_num_format_check($areaNum, $phone)) {
                    $userInfo = $userbll->get_user_info_by_phone_num($phone);
                    if (!empty($userInfo)) {
                        //$err_msg = 'The_phone_number_has_been_registered';
                        $register = new User_RegisterController();
                        $register->signin();
                        return;
                    } elseif (!$this->verify_smscode($phone, $code)) {
                        //$err_msg = 'SMS_verification_code_error'; //tmp
                    } elseif (strlen($password) < 5 || strlen($password) > 20) {
                        //$err_msg = 'Passwordlengthdoesnotconformto';
                    }
                } else {
                    $err_msg = 'phonenumformatisincorrect';
                }

                if (!empty($err_msg)) {
                    $response = array(
                        'status' => 400,
                        'data' => null,
                        'userMsg' => $err_msg,
                        'msg' => $err_msg,
                    );
                } else {

                     // save user data
                    Logger::info(__FILE__, __CLASS__, __LINE__, "$phone , $code");
                    $response = $this->user_write($phone, $code);
                    Logger::info(__FILE__, __CLASS__, __LINE__, var_export($response, true));
                }

            }


        }
        Util_ZzkCommon::zzk_echo(json_encode($response));
        Logger::debug('register', json_encode(array_merge($response, array('guid' => $param_arr['guid']))));
    }

    public function signin()
    {
        $param_arr = APF::get_instance()->get_request()->get_parameters();
        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($param_arr, true));
        $areaNum = '86';
        $phone = $param_arr['phonenum'];
        $code = $param_arr['code'];
        $password = '';

        //添加ip策略,防止暴力破解验证码
        $ip = Util_NetWorkAddress::get_client_ip();
        $key = 'mobile_user_register_' . md5($ip);
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();

        if (in_array($ip, array(
            '120.132.26.244',
            '120.132.27.138',
            '120.132.21.8',
            '45.79.167.208')) || stripos($ip, '192.168.1') !== false) {
            $cache = 0;
        } else {
            $cache = $memcache->get($key);
        }

        if ($cache > 30) {
            $response = array(
                'status' => 400,
                'data' => null,
                'userMsg' => 'Please_try_again_later',
                'msg' => 'Please_try_again_later',
            );
        } else {
            $memcache->set($key, $cache + 1, 0, 3600);

            if (empty($phone)) {
                $response = array(
                    'status' => 400,
                    'data' => null,
                    'userMsg' => 'need phone number',
                    'msg' => 'Phone number can not be empty',
                );
                Util_ZzkCommon::zzk_echo(json_encode($response));
                Logger::debug('register', json_encode(array_merge($response, array('guid' => $param_arr['guid']))));
                return false;
            }

            $userbll = new Bll_User_UserInfo();

            if ($phone) {
                // 再验证一次信息
                if ($this->phone_num_format_check($areaNum, $phone)) {
                    $userInfo = $userbll->get_user_info_by_phone_num($phone);
                    if (empty($userInfo)) {
                        //$err_msg = 'The_phone_number_not_have_been_registered';
                    } elseif (!$this->verify_smscode($phone, $code)) {
                        $err_msg = 'SMS_verification_code_error'; //tmp
                    } elseif (strlen($password) < 5 || strlen($password) > 20) {
                        //$err_msg = 'Passwordlengthdoesnotconformto';
                    }
                } else {
                    $err_msg = 'phonenumformatisincorrect';
                }

                if (!empty($err_msg)) {
                    $response = array(
                        'status' => 400,
                        'data' => null,
                        'userMsg' => $err_msg,
                        'msg' => $err_msg,
                    );
                } else {

                    $userbll = new Bll_User_UserInfo();
                    $uinfo = $userbll->signin($phone, $code);
                    if(isset($uinfo['v_status']) && $uinfo['v_status'] == 1){
                        $response = array(
                          'status' => 200,
                          'data' => $uinfo,
                          'userMsg' => 'signin_success',
                          'msg' => 'signin_success',
                        );
                    }
                    else{
                        $response = array(
                          'status' => 200,
                          'data' => array(),
                          'userMsg' => 'signin_fail',
                          'msg' => 'signin_fail',
                        );
                    }

                    Logger::info(__FILE__, __CLASS__, __LINE__, var_export($response, true));
                }

            }


        }
        Util_ZzkCommon::zzk_echo(json_encode($response));
        Logger::debug('register', json_encode(array_merge($response, array('guid' => $param_arr['guid']))));
    }

    public function user_write($phone, $code)
    {
        if(empty($phone)){
            return false;
        }
        $timestamp = time();
        $client_ip = Util_NetWorkAddress::get_client_ip();
        $user = array(
            'uid' => 0,
            'name' => '',
            'pass' => '',
            'mail' => '',
            'mail_verified' => 0,
            'mobile_num' => $phone,
            'mobile_verified' => 1,
            'wechat' => '',
            'weibo' => '',
            'tengqq' => '',
            'tel_num' => '',
            'access' => $timestamp,
            'login' => $timestamp,
            'picture' => '',
            'v_status' => 1,
            'v_date' => $timestamp,
            'client_ip' => $client_ip,
            'last_client_ip' => $client_ip,
            'created' => $timestamp,
        );
        Logger::info(__FILE__, __CLASS__, __LINE__, var_export($user, true));
        $userbll = new Bll_User_UserInfo();
        $register = $userbll->user_register($user);

        if ($register) {
            $new_user = array();
            if (!empty($phone) && !empty($code)) {
                // !$this->verify_smscode($phone, $code)
                $new_user = $userbll->signin($phone, $code);
            }

            $response = array(
                'status' => 200,
                'data' => $new_user,
                'userMsg' => 'registration_success',
                'msg' => 'registration_success',
            );
        } else {
            $response = array(
                'status' => 500,
                'data' => null,
                'userMsg' => 'Registration_failed',
                'msg' => 'Registration_failed',
            );
        }
        return $response;
    }

    public function index()
    {
        $response = array(
            'status' => 400,
            'data' => null,
            'userMsg' => 'Request_parameter_error',
            'msg' => 'Request_parameter_error',
        );
        Util_ZzkCommon::zzk_echo(json_encode($response));
    }

    public function phone_num_format_check($areaNum, $phoneNum)
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

    public function verify_smscode($phonenum, $smsCode)
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
