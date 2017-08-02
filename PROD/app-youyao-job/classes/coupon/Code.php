<?php

require_once APP_PATH.'../classes/lib/Baidu-Push-Server-SDK-Php-3.0.1/sdk.php';

class Coupon_Code {
	private $pdo;

	public function run() {

		$jump = array(
			'type' => 'guest_order',
			'ios' => array(
				'target' => '',
				'storyboard' => 1,
				'bundle' => array(
					'oid' => ''
				)
			),
		);$ext =
		$aaa = Util_Jpush::send_message(array('071d1421e22'),'自在客ch98测试，收到请回复，谢谢!',$jump);
		var_dump($aaa);
		exit();




// 创建SDK对象.
		$sdk = new PushSDK();

		$channelId = '4109358292233415863';

// message content.
		$message = array (
			// 消息的标题.
			'title' => '',
			// 消息内容
			'description' => "hello, this message from baidu push service."
		);

// 设置消息类型为 通知类型.
		$opts = array (
			'msg_type' => 1
		);

// 向目标设备发送一条消息
		$rs = $sdk -> pushMsgToSingleDevice($channelId, $message, $opts);

// 判断返回值,当发送失败时, $rs的结果为false, 可以通过getError来获得错误信息.
		if($rs === false){
			print_r($sdk->getLastErrorCode());
			print_r($sdk->getLastErrorMsg());
		}else{
			// 将打印出消息的id,发送时间等相关信息.
			print_r($rs);
		}

		echo "done!";


  exit();









		   // $url='http://api.zizaike.com/push/jpush?registerid='..'&message='.urlencode();
			$this->curl_get($url);


		for ($i = 1; $i <=29; $i++) {
			$coupon_code = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 7);
			$content = <<<SQL
insert into LKYou.t_coupons(coupon, lastused, expirydate, submittedby, success, fail, status, create_date, update_date, pvalue, locked, ownner)
values('$coupon_code','2016-01-01', '2016-03-01', '', 0, 0, 0, unix_timestamp(), now(), 5, 0,'huzheng_test');
SQL;
			$content .= PHP_EOL;
			echo $content;
			//file_put_contents('coupons.sql', $content, FILE_APPEND);
		}


	}


}
