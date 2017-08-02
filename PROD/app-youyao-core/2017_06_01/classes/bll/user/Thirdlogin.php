<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/8/15
 * Time: 下午11:37
 */
class Bll_User_Thirdlogin {
    private $userInfoDao;

    public function __construct() {
        $this->userInfoDao = new Dao_User_UserInfo();
    }

    public function third_login_register($third_id,$login_type,$login_photo,$nickname,$pass=null,$mail=null,$dest_id, $fcode=null, $register_source='')
    {
        //echo "hello~3";
        $bll_user = new Bll_User_UserInfo();
        //首先判断是否已经跟自在客账号关联
        $r = $this->userInfoDao->is_third_login_register($third_id);
        //print_r($r);

        if($r['uid']>0)
        {
            //表示已经关联过了，无需注册，返回用户信息即可
            $this->userInfoDao->update_t_third_login(array('login_id'=>$third_id,'login_photo'=>$login_photo,'nickname'=>$nickname,'login_type'=>$login_type));
            require_once CORE_PATH . 'classes/includes/password.inc';
            $user = $this->userInfoDao->get_user_by_uid($r['uid']);
            $r1 = $bll_user->signin($user['mail'], "", true);
//            $r1 = $bll_user->get_data_by_user($user);
//            if(empty($r1['nickname'])){
//                $r1['nickname']=$nickname;
//                $this->userInfoDao->insert_nickname($r1['userid'], $nickname);
//            }
            return $r1;
        }else{
            //表示还没注册过，需要注册个新账号
            if($pass){
                $default_password = $pass;
            }else{
                $default_password = 'youyao';
            }
            $default_name = 'zzk'.substr(md5($third_id),-6,6).'_'.time();
            if($mail){

            }else{
                $mail = $default_name."@zzkzzk.com";
            }
            $new_account = array(
                'name' => $default_name,
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
                'mobile_number' => '',
                'dest_id' => $dest_id
            );
            $new_user = $bll_user->user_register($new_account, $new_account);
            //print_r($new_user);

            if ($new_user['uid']>0) {
                if($fcode) {
                    $dao_coupon = new Dao_Coupons_CouponsInfo();
                    $bll_coupon = new Bll_Coupons_CouponsInfo();
                    $expire_date = date('Y-m-d', strtotime('+3 month'));
                    $fcode_register_type = 'customerinsert';
                    $s_uid = So_NiceEncryption::f_code_decode($fcode);
                    $s_dest_id = $bll_user->get_user_dest_id($s_uid);
                    $fcode_coupons_config = APF::get_instance()->get_config('coupon', 'fcode');
                    $coupon_category_config = $bll_coupon->get_coupon_category();
                    foreach($coupon_category_config as $row) {
                        $coupon_category[$row['id']] = $row;
                    }
                    foreach($fcode_coupons_config[$s_dest_id] as $row) {
                        $coupon = $coupon_category[$row['category']];
                        if($coupon['value']) {
                            for($i=1;$i<=$row['num'];$i++) {
                                $coupon_code = $dao_coupon->give_coupon($new_user['uid'], $coupon['value'], $expire_date, $coupon['ownner'], $coupon['type'], $coupon['id'], $coupon['min_use_price']);
                            }
                        }
                    }

//                    $minprice_config = APF::get_instance()->get_config('coupon', 'fcode');
//                    $minprice_config = $minprice_config[$s_dest_id];
//                    $coupons_num = 1;
//                    if($minprice_config['num']) $coupons_num = $minprice_config['num'];
//                    for($i=1;$i<=$coupons_num;$i++) {
//                        $coupon_code = $dao_coupon->give_coupon($new_user['uid'], $minprice_config['value'], $expire_date, $minprice_config['ownner'], $minprice_config['type']);
//                    }
                    $rel = Fcode_TW::insert($s_uid, $new_user['uid'], $fcode, $phone, $coupon_code, $fcode_channel, $fcode_register_type);
                }
                $sign_user = $bll_user->signin($default_name, $default_password);
                //print_r($sign_user);
                $params['uid'] = $new_user['uid'];
                $params['login_id'] = $third_id;
                $params['login_type'] = $login_type;
                $src = Util_Curl::http_get_data($login_photo);
                //print_r($src);
                $tmpimg = "/tmp/tmp.png";
                $fp = fopen($tmpimg,'w');
                fwrite($fp,$src);
                $hashcode=Util_Curl::upload_curl_pic($tmpimg);
                $hashcode = json_decode($hashcode);
                $hashcode = $hashcode->url[0];

                $params['login_photo'] =$login_photo;
                $params['nickname'] =$nickname;
                $this->userInfoDao->insert_t_third_login($params);
                if(!empty($login_photo)){
                    $this->userInfoDao->insert_t_img_managed($hashcode,$params['uid']);
                }
                $this->userInfoDao->insert_nickname($new_user['uid'], $nickname);
                $sign_user['nickname'] = $nickname;
                return $sign_user;
            }
        }
    }
}
