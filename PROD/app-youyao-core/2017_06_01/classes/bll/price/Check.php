<?php

class Bll_Price_Check {
	public static function check($price, $uid) {
		$sp_uid = array(
		);
		if (in_array($uid, $sp_uid)) {
			$price = intval($price * 0.9);
		}
		return intval($price);
	}
}