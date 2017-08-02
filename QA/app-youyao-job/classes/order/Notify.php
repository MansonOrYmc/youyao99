<?php
class Order_Notify
{
	private $pdo;
	private $bll_msg;
    public function run()
    {
    	$this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
    	$this->bll_msg = new Bll_User_Msg();
    	$order_list = $this->get_order_list();
    	
    	$guest = array();
    	$stay  = array();
    	
    	foreach ($order_list as $key=>$value){
    	    $stay[$value['uid']][]=	$value;
    	    $guest[$value['guest_telnum']][]=$value;
    	}
    	
    	$bll_stay = new Bll_Customer_Info();
    	
    
    	// 民宿
    	foreach ($stay as $key =>$value){
    		$g_info = "";
    		foreach ($value as $k=>$v){
    			$g_info.= "姓名：".$v['guest_name']." 手机号码：".$v['guest_telnum']." 入住时间：".$v['guest_date']." 入住天数：".$v['guest_days']."天<br />";
    		}
    		$stay_info = $bll_stay->get_user_info_byid(array($key));
    		if($stay_info[0]['mail']!=''){
    		    $this->send_stay_mail($stay_info[0]['mail'],$g_info);
    		}
    		if($stay_info[0]['send_sms_telnum']!=''){
    			$phone=$stay_info[0]['send_sms_telnum']; 
		    	if(substr($phone,0,2)=="09"){	
    		    	$this->send_stay_msg($phone);
		    	}
    		}
    		
    	} 	
    	
       // 客人
    	$bll_order  = new Bll_Order_OrderInfo();
    	foreach ($guest as $key =>$value){
    		$g_info = "";
    		$value2 = $bll_order->get_order_byphone($value[0]['guest_telnum']);
    		foreach ($value2 as $k=>$v){
    			$stay_info = $bll_stay->get_user_info_byid(array($v['uid']));
    			$g_info.= "名宿名称：".$v['uname']." 联系方式：".$stay_info[0]['tel_num']." 入住时间：".$v['guest_date']." 入住天数：".$v['guest_days']."天<br />";
    		}
    		$this->send_guest_mail($value[0]['guest_mail'],$g_info); 
    		
    		if($key!=''){
    		    $this->send_guest_msg($key);
    		}
    	}
  
    	
    }
    
    public function get_order_list() {
        $sql = "select * from  t_homestay_booking  where guest_date in ('2015-02-16','2015-02-17','2015-02-18','2015-02-19','2015-02-20','2015-02-21','2015-02-22','2015-02-23','2015-02-24') and status in (2,6) order by id asc limit 5000,1500";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function send_stay_mail($mail,$stay_info){
    	$subject = "春節賣出的房，建議您再次確認喔！";
    	$body = "親愛的民宿主人好:<br />
    	          春節將至，老闆是不是每天忙忙盲呢？忙餘之際，麻煩您一定要<font color=\"red\">double check</font>賣出的跨年和元旦，以及日期相近的房間喔！<br />
                  您可以登入自在客後台，email或私信詢問每位成交客人的入住時間，您不僅可以提供更好的服務，以確保客人到住時100%有房保證，<br />
                  並且也可以看出您可能還有空房的機會，登入後台開房提供給自在客幫助您成交，謝謝大家對自在客的支持！<br />
                  以下是您家在2015年02月16日至2015年02月24日入住的客人信息:<br />".$stay_info;

    	echo "\n".$body."\n";
    	Util_SmtpMail::send($mail, $subject, $body);
    	sleep(1);
    }
    
    public function send_stay_msg($phone){
    	echo $phone."\n";
    	$arr = array('oid'=>0,
    	             'sid'=>0,
    	             'uid'=>0,
    	             'mobile'=>$phone,
    	             'content'=>'【自在客】春節賣出的房，建議您再次確認喔!詳情請查看您的email',
    	             'area'=>2,
    	             'retry'=>0);
    	$this->bll_msg->send_msg($arr);
    }
    
    public function send_guest_mail($mail,$info){
        $subject = "自在客温馨提示：别忘了联系下民宿主人";
    	$body = "亲爱的用户：<br />
                  感谢您通过自在客预订台湾民宿~ <br />
                  因恰逢春节的到来，台湾民宿入住火爆，加上当地过年这段时间游客众多，所以交通、美食等攻略等方面您一定要在出发前做好规划哦~<br />
                  为了方便您的旅途行程，并且有更好的入住体验，您可以在入住的前几天跟您所预定的民宿老板私信或者邮件联系，询问交通、美食、景点等相关事宜~<br />
                  最后祝您有一个愉快的台湾之旅~<br />
                  以下是您的入住信息：<br />".$info;

    	echo "\n".$body."\n";
    	Util_SmtpMail::send($mail, $subject, $body);	
    	sleep(1);
    }
    
    public function send_guest_msg($phone){
    	$phone = ltrim($phone,'+86');
    	$phone = ltrim($phone,'86');
    	$phone = trim($phone);

        $arr = array('oid'=>0,
    	             'sid'=>0,
    	             'uid'=>0,
    	             'mobile'=>$phone,
    	             'content'=>'【自在客】感谢您通过自在客预订民宿~旅途前的功课要做好哦,提前跟预定的民宿老板联系确认，询问交通、美食、景点等相关事宜,祝您旅行愉快',
                     'area'=>1,
                     'retry'=>0);
    	$this->bll_msg->send_msg($arr);	
    }
    
}