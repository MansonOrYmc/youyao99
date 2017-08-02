<?php 
class Mail_Queue{
    public function run(){

        global $argv;
        $dao_mail_queue = new Dao_Mail_Queue();
        $thread = null;
        if(isset($argv[2])) {
            $thread = $argv[2] - 1;
        }
        $queues = $dao_mail_queue->get_queue($thread);
        foreach ($queues as $key => $value) {
            mb_internal_encoding("utf-8"); 
            $to = mb_convert_kana(trim($value["to"]), "a");
            $id = $value["id"];
            if(!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $dao_mail_queue->update_queue_status(array($id), 2);
                continue;
            }
            $subject = $value["subject"];
            $body = $value["body"];
            $cc = $value["cc"];
            $reply = $value["reply"];
//            print_r(array($id,$to));
            $result = Util_SmtpMail::send_direct($to, $subject, $body,$cc,$reply);
            if ($result == true) {
                $dao_mail_queue->update_queue_status(array($id), 1);
                echo "to:" . $to . " is ok!\n";
            } else {
                $dao_mail_queue->update_queue_retry(array($id));
                echo "to:" . $to . " is fail!\n";
            }
        }

    }
}





