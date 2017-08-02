<?php

class Util_Mobile {

	public static function check_phone_agent() {
		if (stristr($_SERVER['HTTP_VIA'], "wap")) {// 先检查是否为wap代理，准确度高
			return TRUE;
		}
		elseif (strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML") > 0) {// 检查浏览器是否接受 WML.
			return TRUE;
		}
		elseif (preg_match('/(blackberry|htc |htc_|htc-|motorola|nokia|opera mini|android|iphone|ipod|sonyericsson|symbian|up.browser|up.link|windows ce|windows mobile)/i', $_SERVER['HTTP_USER_AGENT'])) {//检查USER_AGENT
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
}