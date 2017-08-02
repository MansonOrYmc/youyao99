<?php
class Sms_HenxinSend{

    public function run(){
        global $argv;
        if($argv[2]=='homestay'){
            $source = 2;
        }elseif($argv[2]=='user'){
            $source = 1;
        }else{
            exit();
        }
        $sms_lists= $this->get_sms_list($source);
        foreach ($sms_lists as $key=>$value){
            $phone   = $value['mobile'];
            $content = $value['content'];

            if($phone=='' || $content==''){
                continue;
            }
            $fg='';
            if($value['area']==1){  //user
                $phone = $this->filter_zh_phone($phone);
                if(preg_match("/^1[34578]\d{9}$/", $phone)){
                    $this->user_sms($phone,$content);
                }else{
                    $fg = "zh error format";
                }
            }elseif($value['area']==2){   //tw
                $phone = $this->filter_tw_phone($phone);
                $first = substr($phone,0,1);
                if($first==9){
                    $phone = "0".$phone;
                }
                $content = $this->filter_tw_kw($content);
                if(preg_match("/^0[9]\d{8}$/", $phone)){
                    $this->taiwan_sms($phone,$content);
                }else{
                    $fg = "tw error format";
                }
            }
            $this->update_sms_succ($value['id']);
            echo  $value['id']." ".$value['mobile']." ".$fg."\n";
        }

    }

    public function get_sms_list($source) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "select * from t_sms_queue where id > 2140000 and status=0 and area='$source' and mobile !='' and content!='' order by id asc limit 20";
        $stmt =$pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update_sms_succ($id){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "update t_sms_queue set status=1,retry=0 where id=".$id;
        $stmt = $pdo->prepare($sql);
        return $stmt->execute();
    }

    public function taiwan_sms($phone,$content){
        $url   = "https://api.kotsms.com.tw/kotsmsapi-1.php?";
        #$url   = "http://kotsms.com.tw/ApiSend.php?";
        $content = Util_ZzkCommon::simple2tradition($content);
        $content =  mb_convert_encoding($content,'big5','utf-8');
        $content = urlencode($content);
        $phone   = urlencode($phone);
        $url     = $url."username=zizaike2015&password=2dg13sd23x&dstaddr=".$phone."&smbody=".$content."&dlvtime=0";
        // https 需要用到 curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false)
        $result = Util_Curl::http_get_data($url);
        return $result['code'];
    }

    public function user_sms($phone,$content){
        $url='http://202.85.215.202:8080/ws/Send.aspx?';
        $content =  iconv('utf-8','gbk',$content);
        $content = urlencode($content);
        $phone   = urlencode($phone);
        $url =  $url."CorpID=SY0088&Pwd=373527&Mobile=".$phone."&Content=".$content."&Cell=&SendTime=";
        $result = Util_Curl::get($url);
        return $result['code'];
    }

    public function filter_tw_phone($phone){
        return $phone;
    }
    public function filter_zh_phone($phone){
        return Util_Common::filter_phone($phone);
    }

    public function filter_tw_kw($content){
        $content = str_replace('【自在客】','[自在客]',$content);
        return $content;
    }


}
