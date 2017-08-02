<?php
class Privatemsg_Addcustomer {

    public function run() {
		
		$bll = new Bll_Privatemsg_PrivateMsgInfo();
		$orderbll = new Bll_Order_OrderInfo();
		$userbll = new Bll_User_UserInfo();
		
		$list = $bll->get_noorder_privatemsg();
		foreach($list as $row) {
			$userinfo = $userbll->get_user_by_uid($row['user']);
			if ((!strpos($userinfo['mail'],'zzkzzk') && $userinfo['mail']) || $userinfo['send_sms_telnum']) {
				$homestayinfo = $userbll->get_user_by_uid($row['homestay']);
				$cusdata['dest_id'] = $homestayinfo['dest_id'] ? $homestayinfo['dest_id'] : 10;
				$cusdata['name'] = $userinfo['name'];
				$cusdata['phone'] = $userinfo['send_sms_telnum'] ? $userinfo['send_sms_telnum'] : "93112345678";
				$cusdata['email'] = $userinfo['mail'];
				$cusdata['uid'] = $row['user'];
//				$bll->add_pmsg_customer($cusdata);
			}
		}
	}

}
