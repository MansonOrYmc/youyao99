<?php
class Util_Common {

	private static $money_rate;
	private static $dest_id;
	private static $bll_area_info;

	public  static $is_https = FALSE;
	public  static $session_last_read; // 记录下用户的session记录 判断这次请求的session有没有改变

	public static function log_order_guid($order_id, $guid) {
		if (empty($guid) || empty($order_id)) {
			return FALSE;
		}
		$dao_order_log = new Dao_Order_OrderLog();
		return $dao_order_log->insert_order_guid($order_id, $guid);
	}

	//待处理订单保留时间
	//axing 2-14-09-11
	public static function zzk_exchange_time(){
		$hour_setting= '24小时';

		return $hour_setting;
	}

	/*
    func:短信关键字
    param:keyword
    return:keyword
    */
	public static function zzk_msg_keyword($keyword){
		$keyword = str_replace('Taipei','Tai pei',$keyword);
		$keyword = str_replace('taipei','tai pei',$keyword);
		$keyword = str_replace('观景','观 景',$keyword);
		$keyword = str_replace('黄色','黄 色',$keyword);
		$keyword = str_replace('色情','色 情',$keyword);
		$keyword = str_replace('航空','航 空',$keyword);
		$keyword = str_replace('观景','观 景',$keyword);
		$keyword = str_replace('政府','政 府',$keyword);
		$keyword = str_replace('中央','中 央',$keyword);
		$keyword = str_replace('李鹏','李 鹏',$keyword);
		$keyword = str_replace('万国','万 国',$keyword);
		$keyword = str_replace('主义','主 义',$keyword);
		$keyword = str_replace('测试','测 试',$keyword);
		$keyword = str_replace('女郎','女 郎',$keyword);
		$keyword = str_replace('taip','tai p',$keyword);
		$keyword = str_replace('民主','民 主',$keyword);
		$keyword = str_replace('琪琪','琪 琪',$keyword);
		$keyword = str_replace('加我Ｑ','加 我 Ｑ',$keyword);
		$keyword = str_replace('加我q','加 我 q',$keyword);
		$keyword = str_replace('加Ｑ','加 Ｑ',$keyword);
		$keyword = str_replace('加q','加 q',$keyword);
		$keyword = str_replace('精装','精 装',$keyword);
		$keyword = str_replace('咪咪','咪 咪',$keyword);

		return $keyword;
	}

	public static function category_map(){
		return array(
			'0' => '全部',
			'1' => '民宿',
			'2' => '旅館',
			'3' => '山莊',
			'4' => '飯店',
			'5' => '夜市',
			'6' => '車站',
			'7' => '商圈',
			'8' => '餐廳',
			'9' => '百貨',
			'10' => '酒店',
			'11' => '包車',
			'12' => '客棧',
			'13' => '旅店',
		);
	}

	public static function zzk_property_loc_mapping() {
		$a = array(
			'1,8,553,2905' => '花莲',
			'1,8,553,60506' => '垦丁',
			'1,8,553,60516' => '恒春',
			'1,8,553,60511' => '九份',
			'1,8,553,60512' => '日月潭',
			'1,8,553,60513' => '清境',
			'1,8,553,2685' => '台北',
			'1,8,553,2986' => '高雄',
			'1,8,553,2684' => '台中',
			'1,8,553,2686' => '台南',
			'1,8,553,2952' => '金门',
			'1,8,553,27736' => '阿里山',
			'1,8,553,2674' => '南投',
			'1,8,553,2683' => '台东',
			'1,8,553,2736' => '宜兰',
			'1,8,553,2875' => '澎湖',
			'1,8,553,2713' => '基隆',
			'1,8,553,2793' => '新竹',
			'1,8,553,2745' => '屏东',
			'1,8,553,2708' => '嘉义',
			'1,8,553,2817' => '桃园',
			'1,8,553,27739' => '松山',
			'1,8,553,27737' => '彰化',
			'1,8,553,27740' => '苗栗',
			'1,8,553,23139' => '云林',
			'1,8,553,27738' => '秀林乡',
			'1,8,553,60507' => '马祖',
			'1,8,553,60509' => '新北',
			'1,8,553,60515' => '淡水',
			'1,8,553,60510' => '连江',
			'1,8,553,60517' => '绿岛',
			'1,8,553,60518' => '小琉球',
			'1,8,553,60519' => '兰屿',
			'11001' => '东京',
			'11002' => '山梨县',
			'11003' => '北海道',
			'11004' => '神奈川',
			'11005' => '静岡',
			'11006' => '京都',
			'11007' => '冲绳',
			'11008' => '长野',
			'11009' => '鹿児島',
			'11010' => '大阪',
			'11011' => '神戸',
			'11012' => '奈良',
			'11013' => '海京都',
			'11014' => '枥木',
			'11015' => '函馆',
			'11016' => '大沼',
			'11017' => '洞爷',
			'11018' => '登别',
			'11019' => '白老',
			'11020' => '二世古（新雪谷）',
			'11021' => '小樽',
			'11022' => '札幌',
			'11023' => '十勝',
			'11024' => '旭川',
			'11025' => '大雪',
			'11026' => '美瑛',
			'11027' => '富良野',
			'11028' => '阿寒',
			'11029' => '釧路',
			'11030' => '紋別',
			'11031' => '佐呂間',
			'11032' => '網走',
			'11033' => '知床',
			'11034' => '根室',
			'13001' => '硅谷',
			'13002' => '旧金山',
			'13003' => '洛杉矶',
			'13004' => '加州',
			'12001' => '杭州',
			'12002' => '三亚',
			'12003' => '上海',
			'12004' => '桐庐',
			'12005' => '西塘',
			'12006' => '扬州',
			'12007' => '舟山',
			'12008' => '黄山',
			'12009' => '宏村',
			'12010' => '婺源',
			'12011' => '深圳',
			'14001' => '香港'
		);
		return $a;
	}


	public static function zzk_property_set_mapping() {
		$a = array(
			1 => "电视机",
			2 => "电冰箱",
			3 => "空调",
			4 => "热水壶",
			5 => "吹风机",
			7 => "洗衣机",
			8 => "音响",
			9 => "独立卫浴",
			10 => "24小时热水",
			11 => "淋浴",
			12 => "热水浴缸",
			13 => "毛巾",
			14 => "拖鞋",
			15 => "一次性盥洗用品",
			16 => "无线网络",
			17 => "有线网络",
			//18=>"免费早餐",
			19 => "免费下午茶",
			20 => "免费接送",
			21 => "免费脚踏车",
			22 => "代订门票",
			23 => "代订包车",
			24 => "行李寄存",
			25 => "免费停车位",
			26 => "交通便利",
			27 => "游泳池",
			28 => "厨房",
			29 => "茶包",
			30 => "咖啡包",
			31 => "矿泉水",
			32 => "可以吸烟",
			33 => "可接待家庭/孩子",
			34 => "适合举办活动",
			35 => "可以携带宠物",
			36 => "没有窗户",
		);
		return $a;
	}


	public static function zzk_tw_price_convert($price_tw, $dest_id = 10) {
		if (empty(self::$money_rate) || $dest_id != self::$dest_id) {
			$bll_area_info = new Bll_Area_Area();
			$row = $bll_area_info->get_dest_config_by_destid($dest_id);
			self::$money_rate = $row['exchange_rate'];
			self::$dest_id = $dest_id;
		}
		$price_cn = 0;
		if ($price_tw > 0) {
			$price_cn = round($price_tw / self::$money_rate , 0);
		}
		return $price_cn;
	}
	public static function zzk_cn_price_convert($price_cn,$dest_id=12) {
		if(!$dest_id){
			$dest_id = 12;
		}
		$bll_area_info = new Bll_Area_Area();
		$row = $bll_area_info->get_dest_config_by_destid($dest_id);
		$money_rate = $row['exchange_rate'];
		$price_tw = 0;
		if($price_cn>0){
			$price_tw = round($price_cn*$money_rate,0);
			$price_tw = (int)$price_tw;
		}
		return $price_tw;
	}
	/*
     * 自在客价格转化
     * */
	public static function zzk_price_convert($price,$from_id,$to_id=12, $time=null){
		if(empty($to_id)){$to_id=12;}
		if(!self::$bll_area_info){
			self::$bll_area_info = new Bll_Area_Area();
		}

        // 有时间需要取出当时的汇率
        if($time > 0) {
            $bll_exchange_rate = new Bll_Price_ExchangeRate();
            $row_to['exchange_rate']   = $bll_exchange_rate->get_dest_exchange_rate_by_time($to_id, $time);
            $row_from['exchange_rate'] = $bll_exchange_rate->get_dest_exchange_rate_by_time($from_id, $time);
        }
        // 没时间取当前汇率
        else{
		    $row_to   = self::$bll_area_info->get_dest_config_by_destid($to_id); 
            $row_from = self::$bll_area_info->get_dest_config_by_destid($from_id);
        }

		$money_rate = $row_to['exchange_rate']/$row_from['exchange_rate'];
		if(is_numeric($price)) {
			$price = round($price*$money_rate,0);
		}
		$price = (int)($price);
		return $price;
	}

    public static function get_price_text($price, $price_from=false) {
        $price_with_symbol = Trans::t('%p_price_symbol', Util_Currency::get_cy_id(), array('%p' => $price));
        $price_text = $price_with_symbol;
        if($price_from) {
            $price_text = Trans::t('price_from_%p', Util_Language::get_locale_id(), array('%p' => $price_text));
        }
        return $price_text;
    }

	public static function add_paypal_queue($p) {
		$dao_groceries = new Dao_Groceries_GroceriesInfo();
		$paypal_id = $dao_groceries->paypal_queue_by_oid($p['oid']);
		if (!$paypal_id) {
			$p['customer_level'] = $p['customer_level']?$p['customer_level']:0;
			$insert_info = array(
				$p['oid'],
				$p['sid'],
				$p['uid'],
				$p['uname'],
				$p['paypal_account'],
				$p['total_price_cn'],
				$p['total_price_tw'],
				$p['rebate_num'],
				$p['rev_percent'],
				$p['customer_level'],
				REQUEST_TIME,
				$p['dest_id']
			);
			$dao_groceries->insert_paypal_queue($insert_info);
		}else {
			$update_info = array(
				$p['paypal_account'],
				$p['total_price_cn'],
				$p['total_price_tw'],
				$p['rebate_num'],
				$p['rev_percent'],
				$p['customer_level'],
				$paypal_id
			);
			$dao_groceries->update_paypal_queue_by_id($update_info);
		}
	}

	//自在客价格规范
	public static function zzk_pay_price_format($price){
		$price = round($price,0);
		return $price;
	}

	public static function zzk_date_format($sel_date,$dest_id=10) {
		if(empty($sel_date)){
			return "";
		}
		if($dest_id == 10){
			$weekname=array('周日','周一','周二','周三','周四','周五','周六');
		}else{
			$bll_area_info = new Bll_Area_Area();
			$weekname  = array(
				$bll_area_info->get_dest_language($dest_id,"Sunday"),
				$bll_area_info->get_dest_language($dest_id,"Monday"),
				$bll_area_info->get_dest_language($dest_id,"Tuesday"),
				$bll_area_info->get_dest_language($dest_id,"Wednesday"),
				$bll_area_info->get_dest_language($dest_id,"Thursday"),
				$bll_area_info->get_dest_language($dest_id,"Friday"),
				$bll_area_info->get_dest_language($dest_id,"Saturday")
			);
		}
		$current_week = date('w', strtotime($sel_date));
		$current_weekname = $weekname[$current_week];
		return "$sel_date ($current_weekname)";
	}

	public static function zzk_translate($str, $lang) {
		$tanslate = "";
		$langue = 'ZH_TW';
		if($lang == 'zh-cn'){
			$langue = 'ZH_CN';
		}
		$header  = array('Content-Type'=>'application/json;charset=utf-8');
		$url     =  APF::get_instance()->get_config('translate_url');
		$result  = Util_Curl::post($url.$langue,$str,$header);
		$result  = json_decode($result['content'],true);
		if($result['code'] == 200){
			$tanslate = $result['info'];
		}

		return $tanslate;
		/*
		if (!defined('MEDIAWIKI_PATH')) {
			$path = APF::get_instance()->get_config('mediawiki_path');
			define('MEDIAWIKI_PATH', $path);
		}
		require_once dirname(__FILE__) . "/../includes/mediawiki-zhconverter.inc.php";
		$str = MediaWikiZhConverter::convert($str, $lang);
		return $str;
		*/
	}

	/**
	 * Registers an event for the current visitor to the flood control mechanism.
	 *
	 * @param $name
	 *   The name of an event.
	 * @param $window
	 *   Optional number of seconds before this event expires. Defaults to 3600 (1
	 *   hour). Typically uses the same value as the flood_is_allowed() $window
	 *   parameter. Expired events are purged on cron run to prevent the flood table
	 *   from growing indefinitely.
	 * @param $identifier
	 *   Optional identifier (defaults to the current user's IP address).
	 */
	public static function flood_register_event($name, $window = 3600, $identifier = NULL) {
		if (!isset($identifier)) {
			$identifier = Util_NetWorkAddress::get_client_ip();
		}

		$dao_groceries = new Dao_Groceries_GroceriesInfo();
		$flood_info = array(
			$name,
			$identifier,
			REQUEST_TIME,
			REQUEST_TIME + $window
		);
		$dao_groceries->send_flood_notify($flood_info);
	}

	/**
	 * Returns a persistent variable.
	 *
	 * Case-sensitivity of the variable_* functions depends on the database
	 * collation used. To avoid problems, always use lower case for persistent
	 * variable names.
	 *
	 * @param $name
	 *   The name of the variable to return.
	 * @param $default
	 *   The default value to use if this variable has never been set.
	 *
	 * @return
	 *   The value of the variable. Unserialization is taken care of as necessary.
	 *
	 * @see variable_del()
	 * @see variable_set()
	 */
	function variable_get($name, $default = NULL) {
		global $conf;

		return isset($conf[$name]) ? $conf[$name] : $default;
	}

	public static function zzk_make_links_blank($text=""){
		$text = preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '', $text);
		$patterns = array('<[\w.]+@[\w.]+>', );
		$matches = array('*', );
		$text = preg_replace($patterns, $matches, $text);
		return $text;
	}

	public static function shortUrl_new($long_url)
	{
		$key = Const_Host_Domain;
		$base32 = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

		$hex = hash('md5', $long_url.$key);
		$hexLen = strlen($hex);
		$subHexLen = $hexLen / 10;

		$output = array();
		for( $i = 0; $i < $subHexLen; $i++ )
		{
			$subHex = substr($hex, $i*10, 10);
			$idx = 0x3FFFFFFF & (1 * ('0x' . $subHex));

			$out = '';
			for( $j = 0; $j < 10; $j++ )
			{
				$val = 0x0000003D & $idx;
				$out .= $base32[$val];
				$idx = $idx >> 3;
			}
			$output[$i] = $out;
		}

		return $output;
	}

	//自在客活动活动
	public static function zzk_activity_add($id,$type=''){
		//三周年活动
		if(FALSE){
			$price = 0;
			$intro = '';
			if(substr($id,-4)==3333){
				$price = 100;
				$intro = '自在客三周年活动，订单号尾号为“3333”，成交立减100元';
			}
			elseif(substr($id,-3)==333){
				$price = 30;
				$intro = '自在客三周年活动，订单号尾号为“333”，成交立减30元';
			}
			elseif(substr($id,-2)==33){
				$price = 10;
				$intro = '自在客三周年活动，订单号尾号为“33”，成交立减10元';
			}
			elseif(substr($id,-1)==3){
				$price = 5;
				$intro = '自在客三周年活动，订单号尾号为“3”，成交立减5元';
			}
			if($price>0){
				//查询是否已经存在
				$row = zzkwww_select('t_activity','w')
					->fields('w',array('id'))
					->condition('oid',$id,'=')
					->execute()->fetchAll();
				if(!$row[0]->id){
					zzkwww_insert('t_activity')
						->fields(array(
							'oid' => $id,
							'price' => $price,
							'create_time' => REQUEST_TIME,
							'intro' => $intro,
						))->execute();
				}
			}
		}
	}


	public static function curl_get($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	public static function curl_post($url, $data) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	public static function curl_json_post($url, $data) {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type:application/json; charset=utf-8"));
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}

	public static function curl_xml_post($url, $data) {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type:text/xml; charset=utf-8"));
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}

	public static function filter_phone($phone){
		$preg = array(
			'/^\+86/',
			'/^86/',
			'/^0*86/',
		);
		$phone = trim($phone);
		$phone = preg_replace($preg, '', $phone);
		$phone = trim($phone);
		return $phone;
	}


	public static function get_ordersucc_title($order_num){
		return false;
		$title = "";
		if($order_num<11){
			$title =  "<span style='color:#f28602'>".$order_num."</span>笔成交";
		}elseif ($order_num<51){
			$title = "大于<span style='color:#f28602'>10+</span>成交";
		}elseif ($order_num<101){
			$title = "大于<span style='color:#f28602'>50+</span>成交";
		}elseif ($order_num<501){
			$title = "大于<span style='color:#f28602'>100+</span>成交";
		}elseif (500<$order_num){
			$title = "大于<span style='color:#f28602'>500+</span>成交";
		}
		return $title;
	}

	public static function interval_format($time) {
		$date1 = new DateTime("now");
		$date2 = new DateTime(date('Y-m-d H:i:s', $time));
		$interval = $date2->diff($date1);
		$y =  $interval->format('%y');
		$m =  $interval->format('%m');
		$d =  $interval->format('%d');
		$h =  $interval->format('%h');
		$i =  $interval->format('%i');
		$date_format = ($y ? $y."年" : "") . ($m ? $m."月" : "") . ($d ? $d."天" : "") . ($h ? $h."小时" : "") . ($i ? $i."分" : "") . "前";
		return $date_format;
	}

	public static function drupal_hmac_base64($data, $key) {

		$hmac = base64_encode(hash_hmac('sha256', $data, $key, TRUE));
		$result = strtr($hmac, array('+' => '-', '/' => '_', '=' => ''));

		return $result;

	}

	public static function user_pass_rehash($password, $timestamp, $login) {

		$drupal_hash_salt = '06QLfgvkItyOD3dBCPbTN60TZK3jeAL-ZYOfCpM3jBk'; // defined in drupal settings file when drupal install
		return self::drupal_hmac_base64($timestamp . $login, $drupal_hash_salt . $password);

	}

	public static function uid_hash($uid) { //uid nid hash加密
		if(!$uid || is_array($uid)) return;
		$verify = self::get_verify_code($uid);
		$baseuid = $uid.$verify;

		$uidcomp = str_pad($baseuid,8,0,STR_PAD_RIGHT);
		$z = base_convert($uidcomp, 10, 36);
		$base = base64_encode(str_rot13($z));
		$hash = strtr($base, array('+' => '-', '/' => '_', '=' => ''));

		return $hash;
	}

	public static function uid_rehash($hash) {
		if(!$hash || is_array($hash)) return;
		$base = str_rot13(base64_decode($hash));
		$z = base_convert($base, 36, 10);
		while($z%10==0 && strlen($z)>1) $z = substr($z, 0, -1);
		$uid = substr($z, 0, -1);
		if(substr($z, -1, 1) != self::get_verify_code($uid)) $uid = 0;

		return $uid;
	}

	public static function get_verify_code($uid) {
		$verify = 0;
		$uid .= "1101"; // 长泰办公室 加上参与验证
		for($i = 1; $i < strlen($uid)+1; $i++) {
			if($i%2==1){
				$verify = $uid%10 * $i + $verify; // 奇数位的数字×位数
			}else{
				$verify = pow($uid%10, $i) + $verify; // 偶数位的数字取位数次方
			}
			$uid = ($uid - $uid%10)/10;
		}

		if(strlen($verify)>1)
			do{
				for($idx=strlen($verify)-1; $idx>=0; $idx--) { //循环相加各个位数，直到结果只有一位
					$c = substr($verify, $idx, 1);
				}
				$verify = $c;
			}while(strlen($verify)>1);

		return $verify;
	}

	public static function form_token($form) { // 主要为了 1.安全 2.防止两个人同时编辑保存
		$json = json_encode($form);
		$prev = "daheigou :-P"; // 随便加一个前缀
		$md5 = md5($prev.$json);
		$hash = base64_encode($md5);
		$hash = strtr($hash, array('+' => '-', '/' => '_', '=' => ''));
		return $hash;
	}

	public static function hash_base64($data) {
		$hash = base64_encode(hash('sha256', $data, TRUE));
		return strtr($hash, array('+' => '-', '/' => '_', '=' => ''));
	}

	public static function page_not_found() {
		header("Location: ".Util_Common::url("/404"));
		exit();
	}

	public static function access_denied() {
		header("Location: ".Util_Common::url("?denied"));
		exit();
	}

	public static function format_text($text) {
		// 把开头和结尾的换行符替换掉 和迅雷的xx
		$match = array(
			'/^<p>/',
			'/<\/p>$/',
			'/\<.*thunder.*\>/',
		);
		$text = preg_replace($match, '', $text);
		$text = str_replace(array("<br>","<br/>", "<p>"), "\n", $text);
		$text = strip_tags($text);
		$text = str_replace("\n", "<br/>", $text);

		return $text;
	}

	//生成url
	public static function url($path, $sub_domain = null, $domain = null, $query_params = array()) {
		$apf = APF::get_instance();
		$req = $apf->get_request();
		if ( $sub_domain === null ){

			$sub_domain_config = $req->get_sub_domain();
			$city_set = $sub_domain_config ? $sub_domain_config : "taiwan";
			$sub_domain = $city_set;
		}

		if ( $domain === null ){
			$domain = $apf->get_config('base_domain');
		}

		$is_secure = 'http://';

		$get_query = http_build_query($query_params);
		$query_str = $get_query ? "?".$get_query : "";

		$url = $is_secure . $sub_domain . $domain . $path . $query_str;

		return $url;
	}

	// 将数组整理成 get 参数
	public static function _format_query_str($query) {
		if(empty($query)) return false;

		$question = array();
		foreach($query as $key=>$value) {
			$question[] = $key . "=" .urlencode($value);
		}

		$str = implode("&", $question);
		return $str;
	}

	// 生成占位符，主要在生成sql时使用
	public static function placeholders($text, $count=0, $separator=",") {
		$result = array();
		if($count > 0) {
			for($x=0; $x<$count; $x++) {
				$result[] = $text;
			}
		}

		return implode($separator, $result);
	}

	public static function message($key, $message, $type){
		$_SESSION['message'][$key] = array('type'=>$type, 'message'=>$message);
	}

	public static function get_message($key) {
		$message = $_SESSION['message'][$key];
		unset($_SESSION['message'][$key]); // 提示看一次就够了
		return $message;
	}

	/*
     * 将开始时间和结束时间变成所有日期的数组 ,
     * @params last=ture 则算上end_date
     * @weeklist 1-7 返回星期 array(1,2,3,4,5,6,7)
     */
	public static function date_range_array($start_date, $end_date, $last=false, $weeklist=array(1,2,3,4,5,6,7)) {

		$date_range = array();

		$begin = new DateTime( $start_date );
		$end = new DateTime( $end_date );

		$interval = DateInterval::createFromDateString('1 day');
		$period = new DatePeriod($begin, $interval, $end);

		foreach ( $period as $dt ) {
			$date_range[] =  $dt->format( "Y-m-d" );
		}

		if($last) {
			$date_range[] = $end_date;
		}

		if(count($weeklist) < 7) {
			$date_range_match = array();
			foreach($date_range as $row) {
				$week = self::_date_week_match($row, "str", "N");
				if(!in_array($week, $weeklist)) continue;
				$date_range_match[] = $row;
			}
			$date_range = $date_range_match;
		}

		return $date_range;
	}

	public static function _date_week_match($date, $type="str", $weektype="l") {
		if($type == "date") {
			return $date->format( $weektype );
		}else {
			return date($weektype, strtotime($date));
		}
	}

	//  使用命令行实现异步请求
	public static function async_curl_in_terminal($url, $data, $method="get", $header=null) {
		$queryStr = self::_format_query_str($data);
        if($header) {
            $header_str = " -H '$header' ";
        }
		if($method == "get") {
			$url .= "?".$queryStr;
			$shell = "curl $header_str '$url' ";
		} elseif($method == "post") {
			$shell = "curl $header_str -X POST -d '$queryStr' '$url'" ;
		}

		$shell .= " > /dev/null 2>&1 &";
//        $shell .= " > /tmp/v2_web.log 2>&1 &"; // 获得curl返回内容debug

//Util_Debug::zzk_debug("shell", $shell); // 命令行请求debug

		exec($shell, $output, $exit);

		return $exit==0;
	}

	public static function blocking_css_into_style($html) {
		$python_dir = "/usr/bin/python";
		$python_dir = APF::get_instance()->get_config("python_dir");
		//$conver_file = APF::get_instance()->get_config("conver_file_name");
		$conver_file = APP_PATH."classes/premail.py";

		$file_name = "/tmp/mailhtmltemp" . md5($html) . time();
		file_put_contents($file_name, $html);

		$shell =  "$python_dir  $conver_file  $file_name";

		exec($shell, $output, $exit);

		$result = file_get_contents($file_name.".converted");
		if($result == "") $result = $html;

		return $result;
	}

    public static function real_time_update_solr($uid, $type="user") {
/*
        $solr_server = APF::get_instance()->get_config("solr_job_server");
        $user_name   = APF::get_instance()->get_config("solr_job_server_username");
        $solr_dir    = APF::get_instance()->get_config("solr_job_dir");
        $dsh_path    = APF::get_instance()->get_config("dsh_path");
        if(!$solr_server || !$solr_dir) return;

        if($type == "node") {
            $script_file = "post_room_byuid.php";
        }else{
            $script_file = "post_user_byuid.php";
        }
        if($user_name) $user_name = $user_name . "@";
        if(!$dsh_path) $dsh_path = "dsh";

        $shell = "$dsh_path -c -m $user_name$solr_server \"cd $solr_dir; php solr_jobs/$script_file $uid \" > /dev/null 2>&1 &";
#Util_Debug::zzk_debug("shell", $shell); // 命令行请求debug

        exec($shell, $output, $exit);
*/
        $exchange = "commodity_exchange";
        $msg = new MsgQueue();
        $data = json_encode(array('uid' => $uid, 'type' => $type));
        $rk = "commodity.solr.add.success";
        $exchange = $exchange ? $exchange : self::$exchange;
        $msg->sender($data, $rk, $exchange);

        return true;
    }

    public static function check_date_time($str, $format="Y-m-d"){
        $unixTime=strtotime($str);
        $checkDate= date($format, $unixTime);
        if($checkDate==$str)
            return 1;
        else
            return 0;
    }

    public function img_captcha_verify($code) {
        // 输入的code 加密
        $md5 = md5(strtolower($code) . self::img_captcha_key());

        // cookie中的code解密
        $cookie = base64_decode($_COOKIE['captval']);
        $cookie_code = explode(",", $cookie);
        if($cookie_code[1] == $md5) {
            return true;
        }
        return false;
    }

    public function img_captcha_key() {
        return "zzkkaomodoumeile";
    }

    public function format_app_order_status($status, $refund_status) {
        if($refund_status === null) {
            return $status;
        }
        if($refund_status > -1 && is_numeric($refund_status) ) {
            $status = 7;
        }
        if($refund_status == 1) {
            $status = 8;
        }
        elseif($refund_status == 3 ) {
            $status = 2;
        }
        elseif(in_array($refund_status, array(2,10))) {
            $status = 9;
        }
        elseif($refund_status == 'REFUNDING') {
            $status = 7;
        }
        elseif($refund_status == 'REFUNDED') {
            $status = 9;
        }

        return $status;
    }

    public function trans_bed_type($zh_text){
        if(Util_Language::get_locale_id() == 12) {
            return $zh_text;
        }
        $type_list = APF::get_instance()->get_config("room_type", "roomtype");
        foreach($type_list as $r) {
            $zh_list[] = Trans::t($r, 12);
            $trans_list[] = Trans::t($r);
        }
        
        return str_replace($zh_list, $trans_list, $zh_text);
    }

    public function ie_browser() {
		if(preg_match('/(?i)msie [5-8]/',$_SERVER['HTTP_USER_AGENT'])) {
		    // if IE<=8
            return false;
		} else {
		    // if IE>8
            return true;
		}
    }

}
?>
