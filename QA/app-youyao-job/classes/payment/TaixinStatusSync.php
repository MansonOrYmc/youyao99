<?php

class Payment_TaixinStatusSync {

    public function run() {
        $orderInfoDao = new Dao_Order_OrderInfo();
        $payLogs = $orderInfoDao->fetch_pending_payment_log();

        $count = count($payLogs);
        $updated = 0;
        foreach($payLogs as $pl) {
            if (!empty($pl['oid']) && (int)$pl['oid'] > 0 && $pl['payment_type'] == 'txalipay') {
                $bll_order = new Bll_Order_OrderInfo();
                $order = $bll_order->order_load($pl['oid']);
                if (in_array($order->status, array(2,6))) { // 如果订单已经是成交状态，不查询台新，减少查询次数
                    $doUpdate = true;
                } else {
                    $alipayStatus = Taixinbank_PaymentAPI::queryPayStatus($pl['partner'], $pl['out_trade_no']);
                    $doUpdate = $alipayStatus == 'TRADE_FINISHED';
                }
                if ($doUpdate) { // 只有支付成功才更新
                    $bll_order->updateSuccessTaixinAlipayStatus($order, $pl['out_trade_no'],
                        $pl['payment_source'], $pl['partner'], $pl['currency'], $pl['total_fee']);
                    $updated += 1;
                }
            }
        }
        if ($count - $updated > 10) {
            // 大于10笔未成功支付，发邮件
            $this->mailAlert($count - $updated);
        }
    }

    private function mailAlert($count) {
        require_once SYS_PATH . 'lib/PHPMailer-5.2.8/PHPMailerAutoload.php';
        $mail = new PHPMailer();
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'html';
        $mail->Host = 'smtp.exmail.qq.com';
        $mail->Port = 25;
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth = true;
        $mail->Username = "noreply@zizaike.com";
        $mail->Password = "likeyong1205";

        $mail->setFrom('noreply@zizaike.com', 'zzk-batch');
        $mail->addAddress('dl-tech@zizaike.com');
        $mail->isHTML(true);

        $mail->Subject = "台新alipay累计{$count}笔支付未确认!";
        $mail->Body = "台新alipay累计{$count}笔支付未确认，请相关开发检查处理！";
        $mail->AltBody = 'This is a plain-text message body';

        if (!$mail->send()) {
            echo "Mailer Error: " . $mail->ErrorInfo . "\n";
        } else {
            echo "Message sent! \n";
        }
    }

}
