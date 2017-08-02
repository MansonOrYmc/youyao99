<?php
class Bll_Activity_PinFang {
     private $dao;
	 public function __construct() {
        $this->dao    = new Dao_Activity_PinFang();
    }
    
    public function send_pinfang_message($params){
    	$sql =  $this->dao->send_pinfang_message($params);
		self::send_pinfang_message_mail_notify($params);
		return $sql;
    }
    
    public function get_message_lists($uid){
    	return  $this->dao->get_message_lists($uid);
    }
    
    public function get_pinfang_lists(){
    	$list =  $this->dao->get_pinfang_lists();
		$result = array();
		foreach($list as $row) {
			$row['start_date'] = date("Y-m-d", $row['start_date']);
			$row['end_date'] = date("Y-m-d", $row['end_date']);
			$result[] = $row;
		}
		return $result;
    }

	public function get_indivi_pinfang_list($uid){
		return   $this->dao->get_indivi_pinfang_list($uid);
	}

    public function send_pinfang_request($params){
    	return  $this->dao->send_pinfang_request($params);
    }
    
    public function get_pinfang_byid($id){
    	return  $this->dao->get_pinfang_byid($id);
    }  

	public function accept_request($params) {
		$list =  $this->dao->accept_request_list($params);
		$message =  $this->dao->accept_request_message($params);
		self::send_accept_mail_notify($params);
		return $list && $message ? 1 :0 ;
	}

	public function send_accept_mail_notify($params){
		$user_dao = new Dao_User_UserInfo();
        $to = $user_dao->get_user_mail_by_uid($params['a_uid']);
		$info = self::get_indivi_pinfang_list($params['f_uid']);
		$contact = json_decode($info['contact']);
		$from = 'noreply@youyao.com';
		$subject = '[自在客]对方接受了您的拼客申请!';
		$contactstr = '';
		foreach($contact as $k=>$v) {
			$contactstr .= $k."：".$v."<br/>";
		}
		$body = '
亲爱的拼客：</br>
<p style="text-indent:2px">恭喜您，'.$info['nickname'].'接受了您的拼客申请，以下是'.$info['nickname'].'的联系方式:</p>
<p style="background:#e7fdff;padding:3px 10px;">'.$contactstr.'</p>
<p>现在就去<a href="http://taiwan.youyao.com/v2/a/pinfang/list" style="padding:2px 5px;color:#fff;background:#FF5459;font-size:16px;">查看</a>吧</p>
'; 
		Util_SmtpMail::send($to, $subject, $body, $from);
	}

	public function send_pinfang_message_mail_notify($params){
		$info = self::get_indivi_pinfang_list($params['to_uid']);
		$contact = json_decode($info['contact']);
		$to = $contact->mail;
		$from = 'noreply@youyao.com';
		$subject = '[自在客]您收到拼客邀请啦!';
		$body = '
亲爱的拼客：</br>
<p style="text-indent:2px">您收到了拼客邀请，</p>
<p style="background:#e7fdff;padding:3px 10px;">'.$params['content'].'</p>
<p>现在就去<a href="http://taiwan.youyao.com/v2/a/pinfang/list" style="padding:2px 5px;color:#fff;background:#FF5459;font-size:16px;">查看</a>吧</p>
'; 
		Util_SmtpMail::send($to, $subject, $body, $from);
	}

	public function reject_request($params) {
		$sql = $this->dao->reject_request_message($params);
		self::reject_request_mail_notify($params);
		return $sql; 
	}

	public function reject_request_mail_notify($params){
		$user_dao = new Dao_User_UserInfo();
		$to = $user_dao->get_user_mail_by_uid($params['a_uid']);
		$from = 'noreply@youyao.com';
		$subject = '[自在客]您的拼客邀请被取消了';
		$body = '
亲爱的拼客：</br>
<p style="text-indent:2px">你的拼客邀请被取消了，取消原因：，</p>
<p style="background:#e7fdff;padding:3px 10px;">'.$params['cancel_reason'].'</p>
<p>现在就去<a href="http://taiwan.youyao.com/v2/a/pinfang/list" style="padding:2px 5px;color:#fff;background:#FF5459;font-size:16px;">查看</a>吧</p>
'; 
		Util_SmtpMail::send($to, $subject, $body, $from);
	}

}
