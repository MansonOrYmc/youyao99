<?php
class Util_Debug {

	public static function zzk_debug($key, $value) {
		$now = date("Y-m-d H:i:s");
		$myLogFile = "/tmp/v2_web.log";
		$fh = fopen($myLogFile, 'a') or die("can't open file:".$myLogFile);
		$file_path = str_replace("/home/tonycai/cms.youyao.com", ".", realpath(__FILE__));
		$stringData = $now . "\t" . $key . " : " . $value . "\t" . $file_path . PHP_EOL;
		fwrite($fh, $stringData);
		fclose($fh);
	}
    public static function zzk_log($msg,$debug=false)//即时输出调试使用
    {
        if($debug==false){return;}
        $vruan = get_cfg_var('vruan');
        if ($vruan!='handsome') return;
        if(!is_array($msg)) {print_r($msg);echo "\n";}
        else {print_r($msg);echo "\n";}
    }
}