<?php
/**
 * Created by PhpStorm.
 * User: LCY
 * Date: 15/8/27
 * Time: 上午10:33
 */
class Soj_MobileCollect{
    private $pdo;

    private function prepare(){
        $temp_dir = APF::get_instance()->get_config('soj_temp_dir');
        $temp_file = $temp_dir.date('Y-m-d') .'.mobile'. '.log';
        //判断该文件是否存在
        if(file_exists($temp_file)) {
            //如果存在这删除该文件
            $result = unlink($temp_file);
            //打印结果
            var_dump($result);
        }
    }

    public function run()
    {
        //获取当前时间戳和微秒数
        $s_time = microtime(true);
        //打印字符串"start"
        echo "start\n";
        $this->prepare();
        //取得文件路径和文件名
        $path = APF::get_instance()->get_config('soj_mobile_daily_file');
//        $path = '/vagrant/www/zizaike/app-zizaike-job/mobile.log.1';
        //打印文件路径和文件名
        echo $path.PHP_EOL;
        //打开文件
        $file = fopen($path, "r") or exit("Unable to open file!\n");
        while(!feof($file)){
            //读取文件
            $json = fgets($file);
            //转化json格式为php变量
            $json = json_decode($json,true);
            //先判断数据是否存在
            //var_dump($json);
            foreach ($json as $key=>&$val) {
                $val['ip']=$json['ip'];
                //unset($json['ip']);
//                print_r($val);
                if(in_array($val['site'],array('ios','android'))) {
                    $val['zparam'] = str_replace('\n','',$val['zparam']);
                    $val['zparam'] = str_replace('\\','',$val['zparam']);
                    $val['zparam'] = str_replace(PHP_EOL, '', $val['zparam']);
                    $val['zparam'] = mysql_escape_string($val['zparam']);
                    $params = array(
                        'uid' => $val['uid'],
                        'guid' => $val['guid'],
                        'site' => $val['site'],
                        'pname' => $val['p'],
                        'campaign_code' => $val['campaign'],
                        'log_bean_id' => $val['logBeanId'],
                        'version' => $val['version'],
                        'day' => date("Y-m-d", substr($val['t'], 0, 10)),
                        'dt' => date("Y-m-d H:i:s", substr($val['t'], 0, 10)),
                        'dtid' => $val['dtid'],
                        'ip' => $val['ip'],
                        'url' => $val['url'],
                        'referer' => $val['referer'],
                        'ext' => (string)$val['zparam'],
                        'event_type'=>$val['event_type']
                    );


//                var_dump($val);
//                    var_dump($params);
                    $this->append_data($params);
                }
            }
//            print_r($json);
//            print_r($val);
//            print_r($params);

        }
        //关闭文件
        fclose($file);
        $result = $this->import_data();
        echo 111;
        echo 'result:' . var_export($result, TRUE) . PHP_EOL;
        echo microtime(true)-$s_time;
        echo PHP_EOL;
    }


//    public function run()
//    {
//        //获取当前时间戳和微秒数
//        $s_time = microtime(true);
//        //打印字符串"start"
//        echo "start\n";
//        $this->prepare();
//        //取得文件路径和文件名
//        $path = APF::get_instance()->get_config('soj_mobile_daily_file');
//        $path = '/vagrant/www/zizaike/app-zizaike-job/mobile.log.2';
//        //打印文件路径和文件名
//        echo $path.PHP_EOL;
//        //打开文件
//        $file = fopen($path, "r") or exit("Unable to open file!\n");
//        while(!feof($file)){
//            $json = fgets($file);
//            $json = json_decode($json,true);
//            if($json['site']){
//                //var_dump($json);
//                $params = array(
//                    'site'=>$json['site'],
//                    'guid'=>$json['guid'],
//                    'pname'=>$json['p'],
//                    'url'=>$json['h'],
//                    'referer'=>$json['r'],
//                    'uid'=>$json['uid'],
//                    'dt'=>date("Y-m-d H:i:s",substr($json['t'],0,10)),
//                    'day'=>date("Y-m-d",substr($json['t'],0,10)),
//                    'ip'=>$json['ip'],
//                    'brower'=>'asd',
//                    'spider'=>'asd',
//                    'agent'=>$json['agent'],
//                    'campaign_code'=>$json['campaign'],
//                    'keyword'=>'asd',
//                    'zzkcamp'=>$json['zzkcamp'],
//                    'zfansref'=>$json['zfansref']
//                );
//                var_dump($params);
////                $this->append_data($params);
//            }
//        }
//        fclose($file);
//        $result = $this->import_data();
//        echo 'result:' . var_export($result, TRUE) . PHP_EOL;
//        echo microtime(true)-$s_time;
//        echo PHP_EOL;
//    }


    private function append_data($params) {
        $temp_dir = APF::get_instance()->get_config('soj_temp_dir');
//        $temp_dir = '/vagrant/www/zizaike/app-zizaike-job/';
        $temp_file = date('Y-m-d') .'.mobile'. '.log';
        $line = join("\t", $params) . PHP_EOL;
        file_put_contents($temp_dir . $temp_file, $line, FILE_APPEND);
    }


    private function import_data() {
        $temp_dir = APF::get_instance()->get_config('soj_temp_dir');
//        $temp_dir = '/vagrant/www/zizaike/app-zizaike-job/';
        $temp_path = $temp_dir . date('Y-m-d')  .'.mobile'. '.log';
        $table_name = 'mobile_log_soj_201602';
        echo $temp_path.$table_name;
        $sql = <<<SQL

LOAD DATA LOCAL INFILE "$temp_path"
INTO TABLE $table_name
(`uid`,`guid`,`site`,`pname`,`campaign_code`,`log_bean_id`,`version`,`day`,`dt`,`dtid`,`ip`,`url`,`referer`,`ext`,`event_type`);
SQL;
        $pdo = APF_DB_Factory::get_instance()->get_pdo("statsmaster");
        $stmt = $pdo->prepare($sql);
        var_dump($sql);
        return $stmt->execute();
    }



}