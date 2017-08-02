<?php
class Finance_Unfreeze {

    public function run() {
        $result = Bll_User_Wallet::check_out();
        $order_dao = new Dao_Order_OrderInfo();
        Logger::info(__FILE__, __CLASS__, __LINE__, "unfreeze", var_export($result, true));
        foreach($result as $hash_id) {
            $orderInfo = $order_dao->get_homestay_booking_by_hash_id($hash_id);
            $orderInfo = reset($orderInfo);
            $this->withdraw($orderInfo);
        }
    }

    // 提现
    public function withdraw($order) {
        Logger::info(__FILE__, __CLASS__, __LINE__, "checkout withdraw", var_export($order['hash_id'], true));
        //，提取民宿全部可用金额，而不是根据订单提取，保证不会额外提取,订单与财务解耦。
        $amountInfo = Bll_User_Wallet::user_total_amount($order['uid']);
        $amount = $amountInfo['availableMoney'];
        if(!$amount) {
            Logger::info(__FILE__, __CLASS__, __LINE__, "total amount is empty", var_export(array('order_id'=>$order['hash_id'],'amountInfo'=>$amountInfo), true));
            return;
        }

        list($bank_type, $account) = Bll_User_Wallet::generate_bank_account($order['uid']);
        Logger::info(__FILE__, __CLASS__, __LINE__, var_export(array($order['uid'], $amount, $order['dest_id'], $bank_type, $account), true));
        // 使用用户提现的接口
        $result = Bll_User_Wallet::withdraw_apply($order['uid'], $amount, $order['dest_id'], $bank_type, $account);

        $send_mail_params = array(
            "action" => "order_unfreeze",
            "amount" => $amount,
            "uid" => $order['uid'],
            "multilang" => $order['dest_id'],
            "multiprice" => $order['dest_id'],
            "to" => $order['umail'],
            "send" => true,
        );
        Util_Common::async_curl_in_terminal(Util_Common::url("/m/send", "api"), $send_mail_params);

    }

}
