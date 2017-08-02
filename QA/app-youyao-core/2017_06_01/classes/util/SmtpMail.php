<?php
class Util_SmtpMail {

    private static $log;

    private static function log($body,$i)
    {
        if(empty(Util_SmtpMail::$log))
        {
            Util_SmtpMail::$log = new Log_Mail();
        }
        Util_SmtpMail::$log->log_info($body,$i);
    }

    public static function send($to, $subject,$body, $cc=null,$reply=null) {

        if(preg_match('/親愛的用戶开发嘌/',$subject)){
            return false;
        }else
        if(preg_match('/@zzkzzk.com/',$to)){
            return false;
        }else{
        $to = preg_replace('/\.zzk\.group\.[a-zA-Z0-9]+/', '', $to);
        $mail = new Dao_Mail_Queue();
        return $mail->insert_queue(array(
            'to' => $to,
            'subject' => $subject,
            'body' => $body
        ));
        }
    }

    public static function send_multiple_mail($data) {

        $mail_info = array();
        $n = 0;
        foreach($data as $row) {
            $already_update[] = $row;
            $mail_info[] = array(
                'to'      => $row['to'],
                'subject' => $row['subject'],
                'body'    => $row['body'],
            );

            $n++;
            if($n > 1000) {
                $data_over = array_values(array_diff($data, $already_update));
                self::send_multiple_mail($data_over);
                break;
            }
        }
        $mail = new Dao_Mail_Queue();

        $mail->insert_multiple_queue($mail_info);
    }


    public static function send_direct($to, $subject,$body,$cc=null,$reply=null){
       $to = preg_replace('/\.zzk\.group\.[a-zA-Z0-9]+/', '', $to);
       $body=stripslashes($body);
       $pattern = "/^([0-9A-Za-z\\-_\\.]+)@(((gmail|hotmail|outlook)\\.[a-z]{2,3}(\\.[a-z]{2})?)|(yahoo\\.com\\.tw))$/i";
       if (preg_match( $pattern, $to )||preg_match('/.tw/',$to))
       {
           return Util_SmtpMail::send_mg($to,$subject,$body,$cc,$reply);
       }
       else {
           $aaa = strstr($to,".jp");
           $bbb = strstr($to,".kr");
           if(!empty($aaa) or !empty($bbb)){
               return Util_SmtpMail::send_mg($to,$subject,$body,$cc,$reply);
           }
           return Util_SmtpMail::send_qq($to,$subject,$body,$cc,$reply);
       }
    }

    public static function send_qq($to, $subject,$body,$cc=null,$reply=null)
    {
        //send by qq
        $csmtp = new Mail_Csmtp(APF::get_instance()->get_config('smtp_server'),25);
        $csmtp->login(APF::get_instance()->get_config('smtp_user'),APF::get_instance()->get_config('smtp_pass'));
        $r =  $csmtp->send($to,$subject,$body,$cc,$reply);
        $i=10;
        while(!$r&&$i<1){
            $i--;
            $r =  $csmtp->send($to,$subject,$body,$cc,$reply);
        }
        if($r)
        {
            //Util_SmtpMail::log(json_encode(array("to"=>$to,"subject"=>$subject,"server"=>"QQMail","status"=>"sucess")),1);
            //echo "QQMail : to:" . $to . " is ok!\n";
        }
        else {
            //Util_SmtpMail::log(json_encode(array("to"=>$to,"subject"=>$subject,"server"=>"QQMail","status"=>"failed")),1);
            //echo "QQMail : to:" . $to . " is falil!\n";
            return Util_SmtpMail::send_mg($to,$subject,$body,$cc,$reply);
        }
        return $r;
    }

    public static  function send_mg($to,$subject,$body,$cc=null,$reply=null)
    {
        //send by mailgun
        //if failed ,send by qq
        $csmtp = new Mail_Csmtp("smtp.mailgun.org",25);
        $csmtp->login("noreply@youyao.com","gyttxdsh");
        $r =  $csmtp->send($to,$subject,$body,$cc,$reply);
        if($r)
        {
            //Util_SmtpMail::log(json_encode(array("to"=>$to,"subject"=>$subject,"server"=>"MailGun","status"=>"sucess")),2);
            //echo "MailGun : to:" . $to . " is ok!\n";
        }
        else {
            //Util_SmtpMail::log(json_encode(array("to"=>$to,"subject"=>$subject,"server"=>"MailGun","status"=>"failed")),2);
            //echo "MailGun : to:" . $to . " is falil!\n";
            $r = Util_SmtpMail::send_qq($to,$subject,$body,$cc,$reply);
        }
        return $r;
    }
}

