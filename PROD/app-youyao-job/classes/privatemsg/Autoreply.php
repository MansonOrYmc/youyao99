<?php
class Privatemsg_Autoreply {

	public function run() {

		$bll = new Bll_Privatemsg_PrivateMsgInfo();
		$orderbll = new Bll_Order_OrderInfo();
		$userbll = new Bll_User_UserInfo();
		$list = $bll->get_noreplay_privatemsg();
		foreach($list as $row) {
			$data = $row;
			$data['to'] = array(
							'leonchen@zizaike.com',
//							'shan@zizaike.com',
//							'tonycai@zizaike.com',
							);

//			$bll->send_mail($data);
			$bll->insert_privatemsg($row);
		}
	}
}
