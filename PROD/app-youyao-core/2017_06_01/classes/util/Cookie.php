<?php
class Util_Cookie {

    /**
	 * Saves visitor information as a cookie so it can be reused.
	 *
	 * @param $values
	 *   An array of key/value pairs to be saved into a cookie.
	 */
    public static function user_cookie_save(array $values) {
    	foreach ($values as $field => $value) {
    		// Set cookie for 365 days.
    		setrawcookie('Drupal.visitor.' . $field, rawurlencode($value), REQUEST_TIME + 31536000, '/');
  		}
    }

    public static function zzk_dsetcookie($var, $value = '', $life = 0, $prefix = 1, $httponly = false) {

        $config = array(
                         'cookiepre'=>'bbs_',
                         'cookiedomain'=>'.youyao.com',
                         'cookiepath'=>'/',
                        );

        $var = ($prefix ? $config['cookiepre'] : '').$var;
        $_COOKIE[$var] = $value;

        if($value == '' || $life < 0) {
                $value = '';
                $life = -1;
        }

        $timestamp = REQUEST_TIME;
        $life = $life > 0 ? $timestamp + $life : ($life < 0 ? $timestamp - 31536000 : 0);
        $path = $config['cookiepath'];

        $secure = 0;
        setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure, $httponly);
}

    public static function zzk_get_zzkcamp($pagename,$no,$otherinfo){
        if(empty($pagename)||empty($no)){
            return '';
        }
        $zzkcamp = $pagename.'_'.$no.'_'.$otherinfo;
        return $zzkcamp;
    }
}
?>
