<?php

class Bll_Payment_UnionPay
{

    public function __construct()
    {
        define('UNIONPAY_ROOT', dirname(dirname(dirname(__FILE__))) . '/unionpay');
        include_once UNIONPAY_ROOT . '/SDK/common.php';
        include_once UNIONPAY_ROOT . '/SDK/SDKConfig.php';
        include_once UNIONPAY_ROOT . '/SDK/secureUtil.php';
        include_once UNIONPAY_ROOT . '/SDK/httpClient.php';
        include_once UNIONPAY_ROOT . '/SDK/log.class.php';

    }

    public function get_app_message($orderinfo)
    {
        if(substr($orderinfo['payment_order_id'], 0, 3) == 'PAY') {
            $order_id = $orderinfo['payment_order_id'];
        }else{
            $order_id = $orderinfo['payment_order_id'] . date('YmdHis');
        }

        $unionlog = new PhpLog(SDK_LOG_FILE_PATH, "PRC", SDK_LOG_LEVEL);
        $unionlog->LogInfo("============APP前台请求开始===============");
        $params = array(
            'version' => '5.0.0', //版本号
            'encoding' => 'utf-8', //编码方式
            'certId' => getSignCertId(), //证书ID
            'txnType' => '01', //交易类型
            'txnSubType' => '01', //交易子类
            'bizType' => '000201', //业务类型
            'frontUrl' => SDK_FRONT_NOTIFY_URL, //前台通知地址，控件接入的时候不会起作用
            'backUrl' => SDK_BACK_NOTIFY_URL, //后台通知地址
            'signMethod' => '01', //签名方法
            'channelType' => '08', //渠道类型，07-PC，08-手机
            'accessType' => '0', //接入类型
            'merId' => '898111448160942', //商户代码，请改自己的测试商户号
            'orderId' => $order_id, //商户订单号，8-40位数字字母
            'txnTime' => date('YmdHis'), //订单发送时间
            'txnAmt' => $orderinfo['WIDtotal_fee'] . '00', //交易金额，单位分
            'currencyCode' => '156', //交易币种
            'orderDesc' => $orderinfo['order_info'] ? $orderinfo['order_info'] : '自在客-#' . $orderinfo['payment_order_id'], //订单描述，可不上送，上送时控件中会显示该信息
            'reqReserved' => urlencode(json_encode(array('orderid' => $orderinfo['payment_order_id'], 'payment_source' => $orderinfo['payment_source']))), //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现
        );

        sign($params);

        $request = getRequestParamString($params);
        $unionlog->LogInfo("后台请求地址为>" . SDK_App_Request_Url);
        // 发送信息到后台
        $response = sendHttpRequest($params, SDK_App_Request_Url);
        $unionlog->LogInfo("后台返回结果为>" . $response);

        $result_arr = coverStringToArray($response);

        $result = array(
            'request' => $request,
            'response' => $response,
            'verify' => verify($result_arr) ? '验签成功' : '验签失败',
        );

        return $result;
    }

    public function unionpay_notify_receive($params)
    {
        if (isset($_POST['signature'])) {
            if (verify($_POST)) {
                $unionlog = new PhpLog(SDK_LOG_FILE_PATH, "PRC", SDK_LOG_LEVEL);
                $reserved = json_decode(urldecode($params['reqReserved']), true);
                $orderid = $reserved['orderid'];
                $orderbll = new Bll_Order_OrderInfo();
                $order_list = Util_Order::check_service_order($orderid, $params['traceNo'], 'unionpay', "unionpay");
                foreach($order_list as $order_id) {
                    $order = $orderbll->order_load($order_id);

                    if (!empty($order) && in_array($order->status, array(1, 4))) {

                        $unionlog->LogInfo("==============接收回参===========");
                        $unionlog->LogInfo("参数返回>" . print_r($params, true));
                        $payment_source = $reserved['payment_source'];

                        try {
                            $orderbll->zzk_save_order_trac_content($order->id, 1, '境内银联自动操作', '收款成功', 2, array(
                                'total_price' => $order->total_price,
                                'total_price_tw' => $order->total_price_tw,
                                'trade_no' => $params['traceNo'],
                                'out_trade_no' => $params['queryId'],
                                'payment_type' => 'unionpay',
                                'payment_source' => $payment_source,
                            ));
                            $unionlog->LogInfo("success");
                            $orderInfoDao = new Dao_Order_OrderInfo();
                            $orderInfoDao->save_order_extra_info(array(
                                'oid' => $order->id,
                                'partner' => $params['merId'],
                                'currency' => 'RMB',
                                'total_fee' => round($params['settleAmt'] / 100, 2),
                            ));
                        } catch (Exception $e) {
                            $unionlog->LogInfo("failed:" . $e->getMessage());
                        }
                        $unionlog->LogInfo("============结束=============");
                    }
                }
                return '验签成功';
            } else {
                return '验签失败';
            }
        } else {
            return '签名为空';
        }
    }
}
