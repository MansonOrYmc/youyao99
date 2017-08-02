<?php
class Soj_Collect
{
	private $pdo;

	private function prepare(){
		$temp_dir = APF::get_instance()->get_config('soj_temp_dir');
		$temp_file = $temp_dir.date('Y-m-d') . '.log';
		if(file_exists($temp_file)) {
			$result = unlink($temp_file);
			var_dump($result);
		}
	}
    public function run()
    {
	    $s_time = microtime(true);
    	echo "start\n";
	    $this->prepare();
	    $path = APF::get_instance()->get_config('soj_daily_file');
	    echo $path.PHP_EOL;
    	$file = fopen($path, "r") or exit("Unable to open file!\n");
		while(!feof($file)){
			$json = fgets($file);
			$json = json_decode($json,true);
			if($json['site']){
				$params = array(
				    'site'=>$json['site'],
					'guid'=>$json['guid'],
				    'pname'=>$json['p'],
					'url'=>$json['h'],
					'referer'=>$json['r'],
					'uid'=>$json['uid'],
					'dt'=>date("Y-m-d H:i:s",substr($json['t'],0,10)),
				    'day'=>date("Y-m-d",substr($json['t'],0,10)),
					'ip'=>$json['ip'],
				    'brower'=>$this->get_browser_info($json['agent']),
				    'spider'=>$this->check_spider_info($json['agent']),
				    'agent'=>$json['agent'],
				    'campaign_code'=>$json['campaign'],
					'keyword'=>$this->get_search_word_from_referrer($json['r']),
					'zzkcamp'=>$json['zzkcamp'],
					'zfansref'=>$json['zfansref']
				);
				$this->append_data($params);
			}
		}
		fclose($file);
	    $result = $this->import_data();
	    echo 'result:' . var_export($result, TRUE) . PHP_EOL;
	    echo microtime(true)-$s_time;
	    echo PHP_EOL;
    }

	private function import_data() {
		$temp_dir = APF::get_instance()->get_config('soj_temp_dir');
		$temp_path = $temp_dir . date('Y-m-d') . '.log';
		$table_name = 'log_soj_201601';
		$sql = <<<SQL

LOAD DATA LOCAL INFILE "$temp_path"
INTO TABLE $table_name
(`site`,`guid`,`pname`,`url`,`referer`,`uid`,`dt`,`day`,`ip`,`brower`,`spider`,`agent`,`campaign_code`,`keyword`,`zzkcamp`,`zfansref`);
SQL;
		$pdo = APF_DB_Factory::get_instance()->get_pdo("statsmaster");
		$stmt = $pdo->prepare($sql);
		return $stmt->execute();
	}

	private function append_data($params) {
		$temp_dir = APF::get_instance()->get_config('soj_temp_dir');
		$temp_file = date('Y-m-d') . '.log';
		$line = join("\t", $params) . PHP_EOL;
		file_put_contents($temp_dir . $temp_file, $line, FILE_APPEND);
	}

	private function get_search_word_from_referrer($referrer) {

		if(strpos($referrer,'baidu.com')!==false) {
			$encoding = $this->getParamValueByName($referrer,'ie');
			if(strtolower(substr($encoding,0,2))=='gb'){
				$encoding = 'gbk';
			}
			$word = $this->getParamValueByName($referrer, 'wd');
			if(empty($word)){
				$word = $this->getParamValueByName($referrer, 'word');
			}
			$word = $this->word_decode($word);
			return $word;
		}elseif(strpos($referrer,'sogou.com')!==false) {
			$word = $this->getParamValueByName($referrer, 'query');
			if(empty($word)){
				$word = $this->getParamValueByName($referrer, 'keyword');
			}
			$word = $this->word_decode($word);
			return $word;
		}
		elseif (strpos($referrer, 'whiich.com.tw') !== FALSE
			|| strpos($referrer, 'qunar.com') !== FALSE
			|| strpos($referrer, 'google.com') !== FALSE
			|| strpos($referrer, 'sm.cn') !== FALSE
			|| strpos($referrer, 'haosou.com') !== FALSE
			|| strpos($referrer, '.so.com') !== FALSE
			|| strpos($referrer, 'uodoo.com') !== FALSE
			|| strpos($referrer, 'bing.com') !== FALSE) {
			$word = $this->getParamValueByName($referrer, 'q');
			$word = $this->word_decode($word);
			return $word;
		}
		elseif (strpos($referrer, 'bing.com') !== FALSE) {
			$word = $this->getParamValueByName($referrer, 'q');
			$word = $this->word_decode($word);
			return $word;
		}
		elseif(strpos($referrer,'zizaike.com')===false && !empty($referrer)){
//			echo $referrer.PHP_EOL;
		}
		return false;
	}

	private function word_decode($word) {
		if (strpos($word, '%') !== FALSE) {
			$word = urldecode($word);
		}
		if (!empty($word)) {
			if (empty($encoding)) {
				$encoding = strtolower(mb_detect_encoding($word, 'gb2312,gbk,utf-8'));
			}
			switch ($encoding) {
				case 'gbk':
				case 'euc-cn':
					$word = mb_convert_encoding($word, 'utf-8', $encoding);
					break;
				default:
					break;
			}
		}
		return $word;
	}

	//解析url中参数的值
	private function getParamValueByName($url, $paramName) {
		if ($url == NULL) {
			return NULL;
		}

		// 找到参数位置
		$findString1 = "?" . $paramName . "=";
		$findString2 = "&" . $paramName . "=";

		$findString = strpos($url, $findString1) !==false ? $findString1 : $findString2;
		$findStringIndex = strpos($url, $findString1) !==false ? strpos($url, $findString1) : strpos($url, $findString2);
		if ($findStringIndex == false) {
			return NULL;
		}

		// 得到值的开始位置
		$startPos = $findStringIndex + strlen($findString);
		// 得到值的結束位置，也就是第2个&号的位置
		$endPos = strpos($url,'&', $startPos);

		// 返回的内容为&utm_term 或者 &kw 或者&tip的内容
		$length = ($endPos!==false ? $endPos : strlen($url))-$startPos;
		return substr($url, $startPos, $length);
	}

    public function insert_soj_log($params) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("statsmaster");
		$sql = "insert into log_soj_201601 (site, guid, pname, url,referer,uid,dt,day,ip,brower,spider,agent,campaign_code,keyword) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$stmt = $pdo->prepare($sql);
	    return $stmt->execute(array(
		    $params['site'],
		    $params['guid'],
		    $params['pname'],
		    $params['url'],
		    $params['referer'],
		    $params['uid'],
		    $params['dt'],
		    $params['day'],
		    $params['ip'],
		    $params['brower'],
		    $params['spider'],
		    $params['agent'],
		    $params['campaign_code'],
		    $params['keyword']
	    ));
    }
    
    public function get_browser_info($agent){
    	$browser = "";
    	if(strpos($agent,"MSIE 6.0")>0){
    		$browser =  'ie6';
    	}elseif(strpos($agent,"MSIE 7.0")>0){
    		$browser =  'ie7';
    	}elseif(strpos($agent,"MSIE 8.0")>0){
    		$browser =  'ie8';
    	}elseif(strpos($agent,"MSIE 9.0")>0){
    		$browser =  'ie9';
    	}elseif(strpos($agent,"MSIE 10.0")>0){
    		$browser =  'ie10';
    	}elseif(strpos($agent,"MSIE 11.0")>0){
    		$browser =  'ie11';
    	}
    	return $browser;
    }
    
    public function check_spider_info($agent){
    	$spider = 0;
    	if(strpos($agent,"spider")>0 || strpos($agent,"Spider")>0){
    		$spider = 1;
    	}
    	return $spider;
    }
    
}