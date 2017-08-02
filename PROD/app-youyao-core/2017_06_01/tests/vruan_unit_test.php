<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/7/28
 * Time: 上午9:39
 */
if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED',0);
}

$starttime = round(microtime(true) * 1000);

error_reporting(E_ALL);

$base_uri = DIRECTORY_SEPARATOR=='/' ? dirname($_SERVER["SCRIPT_NAME"]) : str_replace('\\', '/', dirname($_SERVER["SCRIPT_NAME"]));
define("BASE_URI", $base_uri =='/' ? '' : $base_uri);
unset($base_uri);
define('APP_NAME', 'zzk');
define('APP_PATH', realpath(dirname(dirname(__FILE__))).'/');
define('SYS_PATH', APP_PATH."../system/");

$G_LOAD_PATH = array(
    APP_PATH,
    SYS_PATH
);
$G_CONF_PATH = array(
    APP_PATH."config/",
    APP_PATH."../config/".APP_NAME."/",
    APP_PATH."../../config/".APP_NAME."/"
);

require_once(SYS_PATH."functions.php");
spl_autoload_register('apf_autoload');
apf_require_class("APF");
define('CORE_PATH', APP_PATH.'../app-youyao-core/');
//
//function str_str($v){
//    if($v == '左镇区'){
//        return '左镇';
//    }
//    if($v == '前镇区'){
//        return '前镇';
//    }
//    if(strlen($v)>6){
//        $v = str_replace('乡','',$v);
//        $v = str_replace('镇','',$v);
//        $v = str_replace('区','',$v);
//        $v = str_replace('市','',$v);
//        $v = str_replace('县','',$v);
//    }
//    return $v;
//}
//
//$data = file_get_contents('./data2.csv');
//$array = explode("\n",$data);
//$result=array();
//foreach($array as $v){
//
//    $a = explode(',',$v);
//    $result[$a[0]]=array($a[1],str_str($a[2]),str_str($a[3]));
//}
//$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
//foreach($result as $uid=>$value)
//{
//    $sql = "select name from one_db.drupal_users where uid = ?";
//    $stmt = $pdo->prepare($sql);
//    $stmt->execute(array($uid));
//    $name = $stmt->fetch();
//    if($value[2]==''&&$value[1]==''){
//        echo $uid.",".$name['name'].",".$value[0].",,,\n";
//       // print_r($uid."(".$name['name']."):".$value[0]."\n");
//    }
    //$where = '';
    //echo '民宿ID:【'.$uid."】\n";
//    if($value[2]=='')
//    {
//        //echo '第三级不存在'."\n";
//        if($value[1]==''){
//            //echo '第二级不存在'."\n";
//            continue;
//        }
//        else {
//            //echo '第二级是【'.$value[1]."】\n";
//            $where.=" LENGTH(type_code)= 7 and type_name ='".$value[1]."'";
//        }
//    }
//    else
//    {
//        //需要父亲id
//        //echo '第二级是【'.$value[1]."】\n";
//        $sql = "SELECT `id` FROM t_loc_type WHERE  dest_id = 10 and LENGTH(type_code)= 7 and type_name ='".$value[1]."'";
////        echo $sql."\n";
//        $stmt = $pdo->prepare($sql);
//        $stmt->execute();
//        $parent_id = $stmt->fetch();
//        $parent_id = $parent_id['id'];
//        //echo "父亲id".$parent_id."\n";
//        //echo '第三级是【'.$value[2]."】\n";
//        if($value[2]=='花莲'){
//            $value[2]='花莲乡';
//        }
//        $where.=" LENGTH(type_code)= 12 and type_name ='".$value[2]."' and parent_id = '$parent_id'";
//    }
//
//
//    $sql = "SELECT `type_code` FROM t_loc_type WHERE  dest_id = 10 and ".$where;
//    //echo "执行的SQL：".$sql."\n";
//    $stmt = $pdo->prepare($sql);
//    //$stmt->execute();
//    $value = $stmt->fetch();
////    echo "TYPE_CODE:".$value['type_code']."\n";
//    if($value['type_code']==''||$value['type_code']==null){
//        exit('错误');
//    }
////    echo "开始设置t_weibo_poi_tw表！\n";
//    $sql = "update `t_weibo_poi_tw` set local_code = '".$value['type_code']."' where uid =".$uid;
//    echo "执行的SQL：".$sql."\n";
//      $stmt = $pdo->prepare($sql);
//      //$stmt->execute();
//}

//print_r($location);

//$bll = new Bll_Area_Area();
//$city_list = $bll->get_area_by_destid(10);
//print_r($city_list);



//$dao = new Dao_Search_Dest();
//$old = $dao->get_t_loc_type(10);
//print_r($old);
//echo "\n";
//echo "\n";
//$hot_so = new So_Hot();
//$new =$hot_so->get_hot_list(10);
//$new = $dao->get_hot(10);
//print_r($new);

//$touch = new Dao_Area_Area();
// print_r($touch->get_area_by_destid(10));

//$params['activity_name']='双11秒杀活动';
//$params['type']='1';
//$params['id']=34657;
//echo Util_Activity::get_token($params);



//
//if(false){//支付回调单元测试
//    $bll_order = new Bll_Order_OrderInfo();
//    $matches[1]=618773;
//    $order = $bll_order->order_load($matches[1]);
//    if(isset($matches[1]) && (int)$matches[1] > 0 && in_array($order->status, array(1,4))){
//        //update status
//        $bll_order_info = new Bll_Order_OrderInfo();
//        $result=$bll_order_info->zzk_save_order_trac_content($matches[1], $order->last_admin_uid, '境外alipay自动操作 ', '收款成功', 2, array('total_price'=>$order->total_price, 'total_price_tw'=>$order->total_price_tw, 'trade_no'=>$trade_no, 'out_trade_no'=>$out_trade_no, 'payment_type'=>'alipay_out','payment_source'=>'m.youyao.com'));
//        $orderInfoDao = new Dao_Order_OrderInfo();
//        $orderInfoDao->save_order_extra_info(array('oid' => $order->id, 'partner' => $alipay_config['partner'], 'currency' => $_REQUEST['currency'], 'total_fee' => $_REQUEST['total_fee']));
//    }
//}
//
//if(true){//自在客价格转化单元测试
//    $price=100;
//    $price = Util_Common::zzk_price_convert($price,12,10);//将100元人民币转成台币
//    echo '将100元人民币转成台币：',$price,"\n";
//    echo '将'.$price.'元台币币转成美元：';
//    $price = Util_Common::zzk_price_convert($price,10,13);//将100元人民币转成台币
//    echo $price."\n";
//}
//$bll_stats = new Bll_Room_Status();
//$orderInfoDao = new Dao_Order_OrderInfo();
//$order_id = 619338;
//$results = $orderInfoDao->get_homestay_booking_by_id($order_id);
//if (isset($results[0])) {
//    $order = (object) $results[0];
//    $bll_user_info = new Bll_User_UserInfo();
//    if (empty($order->guest_uid)) {
//        $guest_info = $bll_user_info->get_user_info_by_email($order->guest_mail);
//        $order->guest_uid = $guest_info['uid'];
//
//    }
//}
//$date_arr = range(strtotime($order->guest_date), strtotime($order->guest_checkout_date) - 24 * 60 * 60, 24 * 60 * 60);
//$date_arr = array_map(create_function('$date_v', 'return date("Y-m-d", $date_v);'), $date_arr);
//
//$token = md5(time() + $order_id);
//print_r($order_id."\n");
//print_r($date_arr);
//print_r($uid."\n");
//print_r($token."\n");<?php
//$host='192.168.8.8';
//$port=11211;
//$mem=new Memcache();
//$mem->connect($host,$port);
//$items=$mem->getExtendedStats ('items');
//$items=$items["$host:$port"]['items'];
//foreach($items as $key=>$values){
//    $number=$key;;
//    $str=$mem->getExtendedStats ("cachedump",$number,0);
//    $line=$str["$host:$port"];
//    if( is_array($line) && count($line)>0){
//        foreach($line as $key=>$value){
//            echo $key.'=>';
//            print_r($mem->get($key));
//            echo "\r\n";
//        }
//    }
//}

$dao = new Dao_User_UserInfo();
$third_id = 'ohU7Kt6hwb4lJP1tRPSVANVi8wLQ';
$r = $dao->is_third_login_register($third_id);
$bll_user = new Bll_User_UserInfo();
if($r['uid']>0){
    //表示已经关联过了，无需注册，返回用户信息即可
    //$this->userInfoDao->update_t_third_login(array('login_id'=>$third_id,'login_photo'=>$login_photo,'nickname'=>$nickname,'login_type'=>$login_type));
    //require_once CORE_PATH . 'classes/includes/password.inc';
    $user = $dao->get_user_by_uid($r['uid']);
//    print_r($user);exit;
    $r = $bll_user->get_data_by_user($user);
    if(empty($r['nickname'])){
        $r['nickname']=$nickname;
        $dao->insert_nickname($r['userid'], $nickname);
    }
    return $r;
}




//$bll_stats->set_multiple_days_logs($order->nid, $date_arr, $uid, Util_NetWorkAddress::get_client_ip(), 2, 1, $token,$order_id);

print Util_ZzkCommon::tradition2simple('三');



print_r(Bll_Room_Static::get_lowest_room_price('2016-03-25','2016-03-26',389,false));





//print_r(Bll_Room_Static::get_lowest_room_price('2016-03-25','2016-03-26',389,false));





