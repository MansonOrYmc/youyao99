<?php

class Alipay_Global {

/*
	public function __construct() {
		require_once DRUPAL_ROOT . '/alipay/ali_create_trade/alipay.config.php';
    }
*/
	public function cny2usd_rate(){

		$rate = self::get_alipay_exchange_rate();
		foreach($rate as $row) {
			if($row[2] == "USD") {
				$result = $row;
			}
		}
		
		return $result;
	}

	public function get_alipay_exchange_rate() {
		require_once 'GlobalSDK/alipay.config.php';
		require_once 'GlobalSDK/alipay_submit.class.php';

		$params = array(
		    'service' => 'forex_rate_file',
		    'partner' => $alipay_config['partner'],
		    'sendFormat' => 'normal',
		);
		//$key = "98854unf57ml5si1x7fpeu72qz6rl8re";
		$aliclass = new AlipaySubmit($alipay_config);
		$params = $aliclass->buildRequestPara($params);
		$domain = "http://mapi.alipay.com/gateway.do";
		$uri = "";
		foreach($params as $k=>$v) {
		    $uri .= $uri ? "&".$k."=".$v : "?".$k."=".$v;
		}
		$url = $domain.$uri;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = curl_exec($ch);
		curl_close($ch);
		
		$output_array = explode("\n",$output);
		foreach($output_array as $row) {
		    $data[] = explode("|",$row);
		}
		
		return $data;
	}

}
