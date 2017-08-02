<?php

class Taixinbank_PaymentAPI {
    private $txPararms;
    private $oid;
    private $paymentSource;

    /**
     * @param $order
     * @param $rmbPrice
     * @param $paymentSource
     * @param $isMobile
     * @param bool|false $debug
     * @return Taixinbank_PaymentAPI
     *
     * 准备支付API相关数据，生成API对象，必须调用该方法生成对象，并调用后续方法提交支付
     */
    public static function preparePaySubmit($order, $rmbPrice, $paymentSource, $isMobile, $debug = false)
    {
        require_once('TaixinConfig.php');

        if (is_numeric($order)) {
            if ($debug) {
                echo "loading order ".$order.PHP_EOL;
            }
            $bll = new Bll_Order_OrderInfo();
            $order = $bll->order_load($order);
            $rmbPrice = $order->total_price;
        }
        if (empty($paymentSource)) {
            $paymentSource = 'unset';
        }
        $paymentSource = substr(trim($paymentSource), 0, 10);

        $curTime = time();
        $orderId = 'tx_'.$order->id."_".$paymentSource."_".$curTime."_".rand(0,9);
//        $orderItem = '自在客_'.$order->uid.'_'.str_replace('-', '_', $order->uname).'_#'.$order->id;
        $orderItem = '自在客_'.$order->uid.'_'.$order->uname.'_#'.$order->id;
        $orderMemo = $orderItem."_人民币金额:".$rmbPrice."_台新支付_".$orderId;

        $params = array(
            'gw' => 'ALIPAY_I',
            'merchantid' => MerchantID,
            'mobile' => ($isMobile == 'Y') ? 'Y' : 'N',
            'orderamt' => self::convertBestNTPriceFromRMB($rmbPrice),
            'orderid' => $orderId,
            'orderitem' => $orderItem,
            'ordermemo' => $orderMemo,
            'ordertime' => date('YmdHis', $curTime),
            'returnerrurl' => '',
            'returnurl' => 'http://m.youyao.com/pay/taixin/notify/',
        );
        $sign = self::sign($params);
        $params['sign'] = $sign;

        $obj = new Taixinbank_PaymentAPI();
        $obj->txPararms = $params;
        $obj->oid = $order->id;
        $obj->paymentSource = $paymentSource;

        return $obj;
    }

    /**
     * @param $order
     * @param $rmbPrice
     * @param $paymentSource
     * @param $isMobile  'Y' or 'N'
     * @param bool|false $debug
     * @return string
     */
    public function generatePaySubmitHtml($debug = false)
    {
        if (empty($this->txPararms)) {
            return "error";
        }

        $url = Taixin_Domain."/TSCBgwAPI/gwmerchantapipayment.aspx";
        $query = "";
        foreach($this->txPararms as $k=>$v) {
            $query .= $query ? "&" : "";
            $query .= $k."=".urlencode($v);
        }
        $full_url = $url."?".$query;

        //
        // 台新只支持GET方式提交
        //
        $redirectHTML = <<< HTML
<html>
<head>
<meta http-equiv="refresh" content="0;url={$full_url}">
</head>
<body>
<p>请稍等...</p>
</body>
</html>
HTML;

        $formHtml = <<< HTML
<!doctype html>
<html lang="zh">
<head>
<meta charset="utf-8">
</head>
<body onload="javascript:document.payment_form.submit();">
<form id="payment_form" name="payment_form" method="GET" action="$url">
<input type="hidden" name="gw" value="{$this->txPararms['gw']}">
<input type="hidden" name="merchantid" value="{$this->txPararms['merchantid']}">
<input type="hidden" name="mobile" value="{$this->txPararms['mobile']}">
<input type="hidden" name="orderamt" value="{$this->txPararms['orderamt']}">
<input type="hidden" name="orderid" value="{$this->txPararms['orderid']}">
<input type="hidden" name="orderitem" value="{$this->txPararms['orderitem']}">
<input type="hidden" name="ordermemo" value="{$this->txPararms['ordermemo']}">
<input type="hidden" name="ordertime" value="{$this->txPararms['ordertime']}">
<input type="hidden" name="returnerrurl" value="{$this->txPararms['returnerrurl']}">
<input type="hidden" name="returnurl" value="{$this->txPararms['returnurl']}">
<input type="hidden" name="sign" value="{$this->txPararms['sign']}">
<input type="submit" value="submit" style="display:none;" />
</form>
</body>
</html>
HTML;
        if ($debug) {
            echo $formHtml.PHP_EOL;
            echo $full_url.PHP_EOL;
        }
        return $redirectHTML;

    }

    public function logPaySubmit() {
        if (empty($this->txPararms)) {
            return FALSE;
        }

        $daoOrderInfo = new Dao_Order_OrderInfo();
        $daoOrderInfo->save_order_payment_log(array(
            'oid' => $this->oid,
            'partner' => MerchantID,
            'out_trade_no' => $this->txPararms['orderid'],
            'payment_type' => 'txalipay',
            'payment_source' => $this->paymentSource,
            'currency' => 'TWD',
            'total_fee' => $this->txPararms['orderamt'],
        ));
    }

    public static function convertBestNTPriceFromRMB($rmbPrice, $debug = false) {
        $cny2usd = self::get_ali_cny2usd_rate();
        $usd2twd = self::get_tx_usd2twd_rate();
        if ($debug) {
            echo "cny2use: ".$cny2usd.PHP_EOL;
            echo "usd2twd: ".$usd2twd.PHP_EOL;
        }

        $tw_price = $rmbPrice / $cny2usd * $usd2twd;
        if ($debug) {
            $backRMBPrice = $tw_price / $usd2twd * $cny2usd;
            echo "back RMB: ".$backRMBPrice.PHP_EOL;
        }
//        $tw_price = intval($tw_price * 0.998);
        return round($tw_price);
    }

    public static function get_ali_cny2usd_rate() {

        $ali = new Alipay_Global();
        $key = 'ali_cny2usdrate';
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();

        $value = $memcache->get($key);
        $resettime = $value[0].$value[1];
        if (!$value || time() >= strtotime($resettime)+60*60*24)
        {
            $time = 60*60*24;
            $value = $ali->cny2usd_rate();
            $memcache->set($key, $value, 0, $time);
            if (empty($value)) {
                return 6.373200;
            }
        }

        return $value[3];
    }

    public static function get_tx_usd2twd_rate() {

        $key = 'taixin_usd2twrate';
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();

        $value = $memcache->get($key);
        if(!$value) {
            $time = 1*60;
            $value = self::queryTaixinExchangeRate();
            $memcache->set($key, $value, 0, $time);
        }

        return $value;

    }

    public static function queryTaixinExchangeRate() {
        require_once('TaixinConfig.php');
        $url = Taixin_Domain."/TSCBgwAPI/gwMerchantApiQueryExRate.aspx";
        $params = array(
            'merchantid' => MerchantID,
            'querytime' => date('YmdHis',time()),
            'gw' => 'ALIPAY_I',
        );
        $sign = self::sign($params);
        $params['sign'] = $sign;
        $query = "";
        foreach($params as $k=>$v) {
            $query .= $query ? "&" : "";
            $query .= $k."=".$v;
        }
        $url = $url."?".$query;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
//		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);
//		curl_setopt($ch, CURLOPT_POSTFIELDS, $params); // 貌似只能通过get传输
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $err     = curl_errno( $ch );
        $errmsg  = curl_error($ch);
        $output = curl_exec($ch);
        curl_close($ch);

        $doc = new DOMDocument();
        $doc->loadXML($output);
        $returnCode = $doc->getElementsByTagName( "query_return_code" )->item(0)->nodeValue;
        $returnMsg = $doc->getElementsByTagName( "query_return_message" )->item(0)->nodeValue;
        if (trim($returnCode) == "000") {
            $rate = $doc->getElementsByTagName( "exchange_rate" )->item(0)->nodeValue;
            return $rate;
        } else {
            //TODO log return error msg;
        }

        // 返回默认汇率
        // TODO 记录历史汇率，返回最近的历史汇率
        return 31.6400;

    }

    public static function queryPayStatus($partner, $outTradeNo, $debug = false) {
        require_once('TaixinConfig.php');
        $url = Taixin_Domain."/TSCBgwAPI/gwMerchantApiQueryAlipay.aspx";
        $params = array(
            'gw' => 'ALIPAY_I',
            'merchantid' => $partner,
            'orderid' => $outTradeNo,
            'querytime' => date('YmdHis',time()),
        );
        $sign = self::sign($params);
        $params['sign'] = $sign;
        $query = "";
        foreach($params as $k=>$v) {
            $query .= $query ? "&" : "";
            $query .= $k."=".$v;
        }
        $url = $url."?".$query;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
//		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);
//		curl_setopt($ch, CURLOPT_POSTFIELDS, $params); // 貌似只能通过get传输
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $err     = curl_errno( $ch );
        $errmsg  = curl_error($ch);
        $output = curl_exec($ch);
        curl_close($ch);

        $doc = new DOMDocument();
        $doc->loadXML($output);
        $returnCode = $doc->getElementsByTagName( "return_code" )->item(0)->nodeValue;
        $returnMsg = $doc->getElementsByTagName( "return_message" )->item(0)->nodeValue;
        if ($debug) {
            var_dump($output);
            echo 'return_code: '.$returnCode.PHP_EOL;
            echo 'return_msg: '.$returnMsg.PHP_EOL;
        }
        if (trim($returnCode) == "000") {
            $alipayStatus = $doc->getElementsByTagName( "AlipayStatus" )->item(0)->nodeValue;
            if ($debug) {
                var_dump($alipayStatus);
            }
            return $alipayStatus;
        }

        return $returnCode;

    }

    public function getTestPaymentURL() {

        require_once('TaixinConfig.php');
        $url = Taixin_Domain."/TSCBgwAPI/gwmerchantapipayment.aspx";
        $params = array(
            'gw' => 'ALIPAY_I',
            'merchantid' => MerchantID,
            'mobile' => 'N',
            'orderamt' => 5.00,
            'orderid' => 'txp_123451232bd_'.time(),
            'orderitem' => '自在客 测试订单 - 测试123aaa',
            'ordermemo' => '自在客测试，测试支付，123aaa',
            'ordertime' => date('YmdHis',time()),
            'returnerrurl' => '',
            'returnurl' => 'http://m.qa.youyao.com/pay/taixin/notify/',
        );
//		print_r($params);
        $sign = self::sign($params);
        $params['sign'] = $sign;
        $query = "";
        foreach($params as $k=>$v) {
            $query .= $query ? "&" : "";
            $query .= $k."=".urlencode($v);
        }
        $url = $url."?".$query;
        echo $url."\n";
    }

    public static function sign($params) {
        require_once('TaixinConfig.php');

        ksort($params); // 文档要求是按照ASCII排序
        $queryString = "";
        foreach($params as $key=>$val) {
            $queryString .= $queryString ? "&" : "";
            $queryString .= $key . "=" . $val;
        }

        $queryString = $queryString.Token_Key;
//        var_dump($queryString);
        $result = hash('sha256', $queryString);

        return $result;
    }
}
