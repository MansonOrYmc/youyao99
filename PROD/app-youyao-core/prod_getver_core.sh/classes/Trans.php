<?php
class Trans {
    private static $translate_array;
    private static $multi_trans_array;

	public static function t($str, $dest_id=null, $arg=array()) {
        if(Trans::$translate_array[$dest_id][md5($str.serialize($arg))])
        {//减少数据库查询
            return Trans::$translate_array[$dest_id][md5($str.serialize($arg))];
        }
		$translateDao = new Dao_Translate_Info();
		if(empty($dest_id)||$dest_id===null) $dest_id = Util_Language::get_locale_id();
		if(empty($dest_id)) $dest_id = 10;
		$r =  $translateDao->get_trans_by_key($str, $dest_id);
        if(!$r){
            $r= $str;
        }else{
            $match = array_keys($arg);
            $replace = array_values($arg);
            $r = str_replace($match, $replace, $r);
        }
        Trans::$translate_array[$dest_id][md5($str.serialize($arg))]=$r;
        return $r;
	}

	public static function t2($dest_id=null,$str,$arg=array()) {
		if(Trans::$translate_array[$dest_id][md5($str.serialize($arg))])
		{//减少数据库查询
			return Trans::$translate_array[$dest_id][md5($str.serialize($arg))];
		}
		$translateDao = new Dao_Translate_Info();
		if(empty($dest_id)||$dest_id===null) $dest_id = Util_Language::get_locale_id();
		if(empty($dest_id)) $dest_id = 10;

		$r =  $translateDao->get_trans_by_key($str, $dest_id);
		if(!$r){
			$r= $str;
		}else{
			$match = array_keys($arg);
			$replace = array_values($arg);
			$r = str_replace($match, $replace, $r);
		}
		Trans::$translate_array[$dest_id][md5($str.serialize($arg))]=$r;
		return $r;
	}

    public static function mulit_k($key_list, $dest_id=null) { // 多值翻译
        $translateDao = new Dao_Translate_Info();
        
		if(empty($dest_id)||$dest_id===null) $dest_id = Util_Language::get_locale_id();
		if(empty($dest_id)) $dest_id = 10;

		$trans = $translateDao->get_trans_by_multikey($key_list, $dest_id);
        foreach($trans as $row) {
            $result[$row['l_key']] = $row['l_desc'];
        }
        foreach($key_list as $key) {
            if(!$result[$key]) $result[$key] = $key;
        }
        return $result;
    }

	// 根据某一种语言翻译查询其他语言翻译， 翻译内容没有作为数据库索引  慎用
	public static function l($str, $to_dest_id = 10, $from_dest_id = null) {
        if(Trans::$translate_array[$dest_id][$str])
        {//减少数据库查询
            return Trans::$translate_array[$dest_id][$str];
        }

		$translateDao = new Dao_Translate_Info();

		$dest_id = $to_dest_id;
		if(empty($dest_id)||$dest_id==10) $dest_id = Util_Language::get_locale_id();
		if(empty($dest_id)) $dest_id = 10;

		$key = $translateDao->get_key_by_str($str, $from_dest_id);
		if($key) {
			$l = $translateDao->get_trans_by_key($key, $dest_id);
		}

		if(!$l) {
			$l = $str;
		}

		Trans::$multi_trans_array[$from_dest_id][$to_dest_id][$str] = $l;
		return $l;
	}

    public static function pinyin($str) {
        require_once(CORE_PATH . "classes/includes/src/Pinyin/Pinyin.php");
        $setting = array(
                'accent' => false,
            );
        return strtoupper(Pinyin::trans($str, $setting));
    }

}
