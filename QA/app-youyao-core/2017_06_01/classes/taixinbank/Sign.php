<?php

class Taixinbank_Sign {
	
	public function sign($params) {
		require_once('TaixinConfig.php');
		ksort($params); // 文档要求是按照ASCII排序
		$queryString = "";
		foreach($params as $key=>$val) {
			$queryString .= $queryString ? "&" : "";
			$queryString .= $key . "=" . $val;
		}

		$queryString = $queryString.Token_Key;
		$result = hash('sha256', $queryString);

		return $result;
	}
}
