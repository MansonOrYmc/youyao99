<?php
if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED',0);
}

$starttime = round(microtime(true) * 1000);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING );

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

//$orderInfoDao = new Dao_Order_OrderInfo();
//$orderInfoDao->save_order_extra_info(array('oid' => 116, 'partner' => 'afaewfaewfewfew', 'currency' => 'aaa', 'total_fee' => 123.333));

$arg1 = isset($argv[1]) ? $argv[1] : 618790;
//$tx = new Taixinbank_PaymentAPI();
//$tx->queryTaixinExchangeRate();
//echo "converted price: ".$tx->convertBestNTPriceFromRMB($arg1, true).PHP_EOL;
//$tx->getTestPaymentUrl();
//$tx->generatePaymentFormHtml($arg1, 0, 'web', true);
//$partner = '000812461000099';
//$out_trade_no = 'tx_683046_msite_1447664116_7';
//echo Taixinbank_PaymentAPI::queryPayStatus($partner, $out_trade_no, true).PHP_EOL;

//$fi = new Bll_Field_Info();
//var_dump($fi->get_user_field_by_uids(66));
//$si = new Bll_Homestay_StayInfo();
//$si->write_homestay_record(296899, array('dest_id' => 11));
//$daoEm = new Dao_Im_Easemob();
//$ret = $daoEm->logEasemobClient(array(
//    'uid' => 66,
//    'email' => 'tonycai.zzk.group.001@youyao.com',
////    'devid' => 'abcdefg',
////    'bdcid' => 'afawfaew3afwfaew',
////    'jgpid' => 'aaacc',
//    'os' => 'web1',
//));
//var_dump($ret);

$daoPm = new Dao_Privatemsg_PrivateMsgInfo();
var_dump($daoPm->readMessages(59, 66));

//$emApi = Easemob_Api::create();
//var_dump($emApi->sendTxtMsg('59', '66', 'testaaa', array('provider' => 'drupal')));
//$ret = $emApi->getMessages(1449714019035);
//var_dump($ret);
//$emApi->refreshToken();
//var_dump($result = $emApi->registerUser(59));
//var_dump(Easemob_Api::fetchTokenFromEasemob());

//var_dump($emApi->getUser('591'));
//var_dump($emApi->sendTxtMsg('', 'andrew591a', 'hello from test111'));

//var_dump($emApi->getMessages(1445953970339));
//var_dump($emApi->getMessages(strtotime('2015-09-02')*1000));

//echo 'item:'.urldecode('%E8%87%AA%E5%9C%A8%E5%AE%A2_66_%E8%87%AA%E5%9C%A8%E5%AE%A2%E6%B0%91%E5%AE%BF_%E4%B8%8A%E6%B5%B7_%23618805').PHP_EOL;
//echo 'memo:'.urldecode('%E8%87%AA%E5%9C%A8%E5%AE%A2_66_%E8%87%AA%E5%9C%A8%E5%AE%A2%E6%B0%91%E5%AE%BF_%E4%B8%8A%E6%B5%B7_%23618805_%E4%BA%BA%E6%B0%91%E5%B8%81%E9%87%91%E9%A2%9D%3A495_%E5%8F%B0%E6%96%B0%E6%94%AF%E4%BB%98_%3Atx_618805_msite_1447306609_8').PHP_EOL;
//$orderInfoDao = new Dao_Order_OrderInfo();
//$payLogs = $orderInfoDao->fetch_pending_payment_log();
//var_dump($payLogs);

//$qStr = "gw=ALIPAY_I&merchantid=000812461000099&mobile=N&orderamt=100&orderid=txp_12345&orderitem=自在客测试民宿-房间1&ordermemo=自在客测试民宿-房间1-房款支付&ordertime=2 0 1 5 1 1 1 1 1 0 5 5 5 3&returnerrurl=&returnurl=http://m.youyao.com/82E7DFBF639C641CE51D5FED6FA551ACAF230404B11CFE9BB9BFDEDB2960B607";
//$result = hash('sha256', $qStr);
//var_dump($result);

//$zfansDao = new Dao_Activity_Zfans();
//$zfansDao->generate_discount_coupon(59, 'andrewxia@youyao.com');
//$coupon_code = 'rmb888';
//$zfc = Zfans_Coupon::getObject($coupon_code);
//if ($zfc) {
//    $couponsdao = new Dao_Coupons_CouponsInfo();
//    $coupon = $couponsdao->get_conpon_raw_data($coupon_code);
//    $coupon['status'] = 0;
//
//    $zfcStatus = $zfc->getCouponStatus();
//    if ($zfcStatus == 'invalid') {
//        return NULL;
//    }
//    if ($coupon['coupon_type'] == 2) {
//        $discount = $zfc->getCouponDiscount();
//    }
//    // if (!$zfc->isValid()) {
//    // 	$coupon['pvalue'] = 0;
//    // 	return $coupon;
//    // }
//    echo "discount is $discount \n";
//}
