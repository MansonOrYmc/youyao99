<?php
class Bll_Customer_Notify {
    public function send_msg($phone){
    	
		if(date('G',time()) < 8 || date('G',time())>20){
			$time_str = date('G',time())<8?'上午':'明天上午';
			$notify = "【自在客】您好，民宿主人在休息中,".$time_str."会来处理预订咨询单，请注意查收邮件和短信，如果您有其它问题可以加客服微信: syouyao";
			    $arr=array(
		     			 'oid'=>0,
		                 'sid'=>0,
		                 'uid'=>0,
		                 'mobile'=>$phone,
		                 'content'=>$notify,
		                 'area'=>1,
		                 'retry'=>0);
			$bll_msg = new Bll_User_Msg();
			$bll_msg->send_msg($arr);
		}
    }

}