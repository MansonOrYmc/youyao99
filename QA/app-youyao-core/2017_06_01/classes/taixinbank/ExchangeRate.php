<?php

class Taixinbank_ExchangeRate {
	
	public function usd2twd_rate() {
		
		$response = self::get_exchange_rate();	
		$rateArg = explode("&", $response);
		$result = array();
		foreach($rateArg as $row) {
			$data = explode("=", $row);
			$result[$data[0]] = $data[1];
		}

		return $result['changerate_twd'];
	}

	public function get_exchange_rate() {
		
		$url = "https://aquarius.neweb.com.tw/CashSystemFrontEnd/querychangerate?merchantnumber=457641";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
//		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
		$err     = curl_errno( $ch );
		$errmsg  = curl_error($ch);
        $output = curl_exec($ch);
        curl_close($ch);

		return $output;
	}

	public function get_tx_exchange_rate() {
		
		require_once('TaixinConfig.php');
		$url = Taixin_Domain."/TSCBgwAPI/gwMerchantApiQueryExRate.aspx";
		$params = array(
			'merchantid' => MerchantID,
			'querytime' => date('Ymdhis',time()),
			'gw' => 'ALIPAY_I',
		);
//		print_r($params);
		$sign = Taixinbank_Sign::sign($params);
		$params['sign'] = $sign;
		foreach($params as $k=>$v) {
			$query .= $query ? "&" : "";
			$query .= $k."=".$v;
		}
		$url = $url."?".$query;
		$ch = curl_init();

//print_r($url);
		curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
//		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);
//		curl_setopt($ch, CURLOPT_POSTFIELDS, $params); // 貌似只能通过get传输
        curl_setopt($ch, CURLOPT_HEADER, 0);
		$err     = curl_errno( $ch );
		$errmsg  = curl_error($ch);
        $output = curl_exec($ch);
        curl_close($ch);

		try{
			$result = new SimpleXMLElement($output);
			return $result;
		}catch(Exception $e){

			return $output;
		}
	}

}
