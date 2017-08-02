<?php
apf_require_class("APF_DB_Factory");

class Dao_Sms_SMSInfo {

	private $pdo;

	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("master");
	}

	public function dao_send_sms_notify($data) {
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($data, true));
                $jd = self::dao_send_sms_notify_yunpian($data);
                Logger::info(__FILE__, __CLASS__, __LINE__, $jd );
		$sql = "insert into t_verifysms (id, mobile, v_code, datei, u_yyid, status, created) values(:id, :mobile, :v_code, :datei, :u_yyid, :status, :created);";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($data);
	}

	public function dao_send_sms_notify_yunpian($data) {

                /* */
                if(empty($data)){
                   return false;
                }

                $mobile = $data['mobile']; //手机号
                $captcha = $data['v_code'];
                $apikey = APF::get_instance()->get_config("sms_provider_captcha_apikey");

                $is_moblie = FALSE;
                if(strlen($mobile) == "11") 
                { 
                  //上面部分判断长度是不是11位 
                  if(preg_match("/1(3|4|5|7|8)[0-9]\d{8}/", $mobile, $matches)){
                    $is_moblie = TRUE;
                  }
                }
                
                
                $ch = curl_init();
                
                /* 设置验证方式 */
                
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded','charset=utf-8'));
                
                /* 设置返回结果为流 */
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                /* 设置超时时间*/
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                
                /* 设置通信方式 */
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                
                // 发送模板短信
                // 需要对value进行编码
                $sms = array('tpl_id'=>'1847902',
                            'tpl_value'=>('#code#').'='.urlencode($captcha),
                            'apikey'=>$apikey,
                            'mobile'=>$mobile
                           );
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($sms, true));
                
                //print_r ($data);
                $json_data = '';
                if($is_moblie){
                   $json_data = self::sms_tpl_send($ch, $sms);
                }
                /* */
                return $json_data;
	}

        private function sms_tpl_send($ch, $data){
            $provider = APF::get_instance()->get_config("sms_provider_tpl_url");
            if(empty($provider)){
              return false;
            }
            Logger::info(__FILE__, __CLASS__, __LINE__, $provider);
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($data, true));
            curl_setopt ($ch, CURLOPT_URL, $provider);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            return curl_exec($ch);
        }

/*
####################################################################
Variables List
####################################################################
$id = "";
$mobile = "";
$v_code = "";
$datei = "";
$u_yyid = "";
$status = "";
$created = "";
$update_date = "";
####################################################################
Array Statement
####################################################################
$res = array(
    'id' => $id,
    'mobile' => $mobile,
    'v_code' => $v_code,
    'datei' => $datei,
    'u_yyid' => $u_yyid,
    'status' => $status,
    'created' => $created,
    'update_date' => $update_date
);
####################################################################
Insert Statement
####################################################################
insert into t_verifysms (id, mobile, v_code, datei, u_yyid, status, created, update_date) values(:id, :mobile, :v_code, :datei, :u_yyid, :status, :created, :update_date);
####################################################################
Update Statement
####################################################################
update t_verifysms set id = ?, mobile = ?, v_code = ?, datei = ?, u_yyid = ?, status = ?, created = ?, update_date = ? where id = ? ;
####################################################################
Select Statement
####################################################################
select id, mobile, v_code, datei, u_yyid, status, created, update_date from t_verifysms where id = ? ;
*/
}
?>
