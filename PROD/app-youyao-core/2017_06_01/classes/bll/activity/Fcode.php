<?php

class Bll_Activity_Fcode {
	private $dao;

	public function __construct() {
		$this->dao = new Dao_Fcode_FlogMemcache();
	}

	public function fund_usable_check($order_id, $uid) {
		$usage_order_limit = APF::get_instance()->get_config('usage_order_limit', 'activity');
		$usage_fund_limit = APF::get_instance()->get_config('usage_fund_limit', 'activity');

		$dao_user = new Dao_User_UserInfo();
		$fund = $dao_user->dao_get_user_fund_by_uid($uid);

		$bll_order = new Bll_Order_OrderInfo();
		//$order_info = $bll_order->get_order_info_byid($order_id);
		$order_info=$bll_order->order_load($order_id);
		$order_info=json_decode( json_encode( $order_info),true);
		$total_price = $order_info['total_price'];
		$same_order = $bll_order->get_same_order_byphoneuid($order_info['guest_telnum'], $order_info['guest_uid']);

		$coupons_list = array('lxjj' => 0, 'lxjj_error' => "");
		if (empty($same_order) && $total_price >= $usage_order_limit) {
			if($fund > $usage_fund_limit) {
				$coupons_list['lxjj'] = $usage_fund_limit;
				$coupons_list['lxjj_error'] = '每笔订单最多使用' . $usage_fund_limit . '元分享优惠';
			}else{
				$coupons_list['lxjj'] = empty($fund) ? 0 : $fund;
			}
		}
		elseif (!empty($same_order) && $fund > 0) {
			$coupons_list['lxjj_error'] = '对不起，您不是新用户，不能使用' . $usage_fund_limit . '元分享优惠';
		}
		elseif ($total_price < $usage_order_limit && $fund > 0) {
			$coupons_list['lxjj_error'] = '对不起，您的房价小于￥'.$usage_order_limit.'，不能使用' . $usage_fund_limit . '元分享优惠';
		}
		return $coupons_list;
	}

	public function add_fc_log($params) {
		return $this->dao->add_fc_log($params);
	}

	public function share_by_email($params) {
		$bll_user = new    Bll_Customer_Info();
		$user = $bll_user->get_user_info_byid(array($params['from']));

		if ($user && preg_match("/^[_.0-9a-z-]+@([0-9a-z][0-9a-z-]+.)+[a-z]{2,3}$/i", $params['email'])) {

			$arr_log = array(
				'uid' => $params['from'],
				'target' => $params['email'],
				'channel' => $params['channel']
			);
			$this->add_fc_log($arr_log);


			if($params['act']=='notice'){
				$subject = '亲爱的'.$user[0]['name'] . "，恭喜您领取到￥66元分享优惠！";
			}else{
				$subject = $user[0]['name'] . "为您提供了¥66用于您的第一次民宿之旅！--自在客";
			}
			$body = '
<html>
	<head>
		<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
		<title>推荐好友住民宿 ，轻松赚取分享优惠-自在客</title>
		<style type="text/css">
			* {font-family:"微软雅黑" "Microsoft YaHei" "黑体" "SimHei"}
			.main{
				text-align:center;
				width:700px;
				padding-bottom:40px;
			}
			.logo {
				height:90px;
				padding-top:40px;
			}
			.pic-word {
				position:relative;
				top:-180px;
				background:#FEC85A;
				background:rgba(254,200,90,0.9);
				line-height:48px;
				color:#fff;
				font-size:20px;
				margin:0 auto;
				padding:0 10px;
				display:inline-block;
			}
			.description {
				margin:0;
				font-size:20px;
				line-height:40px;
				color:#67655f;
			}
			.description em {
				color:#FF5459;
				font-style:normal;
			}
			.button {
				padding:10px 25px;
				border:1px solid #FF5459;
				margin:0 auto;
				margin-top:50px;
				display:inline-block;
				border-radius:3px;
				letter-spacing:1px;
			}
			.button a {
				color:#FF5459;
				text-decoration:none;
			}
		</style>
	</head>
	<body>
		<div class="main">
		<div class="logo">
			<img alt="自在客" src="http://pages.youyao.com/a/img/homepage3/red_logo_small.png" />
		</div>
		<div>
			<a href="'.($params['act']=='notice'? 'http://taiwan.youyao.com' :'http://taiwan.youyao.com/v2/a/f/c/?f=' . $user[0]['uid'] . '&channel=1').'" target="_blank"><img alt="自在客" src="http://pages.youyao.com/a/img/homepage3/mail_back.jpg"></a>
		</div>
'.($params['act']=='notice'?
		'<p class="pic-word">恭喜您领取到￥66元的分享优惠</p>
		<p class="description">现在就可以下订单享用这￥66元去游玩啦！<a href="http://taiwan.youyao.com" target="_blank">www.youyao.com</a>
		<br/>或是您也忍不住将这好消息分享给更多的新朋友来得到更多¥66呢?! 
		<br/><a href="http://taiwan.youyao.com/v2/activity/fc/?from=4482" target="_blank">点击邀请好友</a>
		</p>
		<span class="button"><a href="http://taiwan.youyao.com" target="_blank">&lt;自在客&gt;</a></span>
		</div>'
:		'<p class="pic-word">您收到了好友' . $user[0]['name'] . '的邀请 </p>
		<p class="description"> 您的好友' . $user[0]['name'] . '为您的第一次民宿之旅奉上<br/>了<em>¥66元</em>，千万别忘了说声谢谢哦！ </p>
		<span class="button"><a href="http://taiwan.youyao.com/v2/a/f/c/?f=' . $user[0]['uid'] . '&channel=1" target="_blank">立即领取您的¥66元分享优惠!</a></span>
		</div>
').'
<img style="display:none" src="http://taiwan.youyao.com/v2/action/log/?p=TestPage&h=mail&guid='.$params['email'].'&uid='.$params['from'].'&t='.time().'">
	</body>
</html>
		        ';

			Util_SmtpMail::send($params['email'], $subject, $body, NULL);

			return TRUE;
		}
		return FALSE;
	}
	
	public function check_share($uid) {
		$dao_fcode = new Dao_Activity_Fcode();
		return $dao_fcode->check_share($uid);
	}
	
}
