<?php
class Finance_AutoWithdraw {

    private $order_dao;

    public function run() {

        $this->order_dao = new Dao_Order_OrderInfo();

        // 每天执行
        $this->handle_half_week();
        $this->handle_week_before_checkin();

        // 每周五
        if(date("w") == 5) {
            $this->handle_week();
        }

        // 每月15日
        if( date("d") == 15 ) { 
            $this->handle_half_month();
        }

        // 每月月底
        if( date("t") == date("d")) {
            $this->handle_half_month();
            $this->handle_month();
        }
    }

    // 半周结
    public function handle_half_week() {
        // 2天前成交的订单
        $date = date("Y-m-d", strtotime("-2 days"));
        $result = $this->order_dao->settle_order_litst_by_confirm(1, $date);
        $this->total_withdraw($result);
        return true;
    }

    // 周结
    public function handle_week() {
        $from = date('Y-m-d', strtotime("-1 weeks"));
        $to = date('Y-m-d', strtotime("-1 days"));
        $result = $this->order_dao->settle_order_litst_by_confirm(2, $from, $to);
        $this->total_withdraw($result);
        return true;
    }

    // 半月结
    public function handle_half_month() {
        $from = date('Y-m-d', strtotime("-16 days"));
        $to = date('Y-m-d', strtotime("-1 days"));
        $result = $this->order_dao->settle_order_litst_by_confirm(3, $from, $to);
        $this->total_withdraw($result);
        return true;
    }

    // 月结
    public function handle_month() {
        $from = date('Y-m-d', strtotime("-16 days"));
        $to = date('Y-m-d', strtotime("-1 days"));
        $result = $this->order_dao->settle_order_litst_by_confirm(4, $from, $to);
        $this->total_withdraw($result);
        return true;
    }

    // 入住前一周
    public function handle_week_before_checkin() {
        // 入住日期正好是8天后
        $date = date("Y-m-d", strtotime("8 days"));
        $result = $this->order_dao->settle_order_list_by_checkin(6, $date);
        $this->part_withdraw($result);
        return true;
    }

    // 提现 全部结算
    public function total_withdraw($orders) {
        foreach($orders as $r) {
            Logger::info(__FILE__, __CLASS__, __LINE__, "auto total withdraw", var_export($r['hash_id'], true));
            //提取民宿全部金额
            $amountInfo = Bll_User_Wallet::user_total_amount($r['uid']);
            $amount = $amountInfo['total'];
            if(!$amount) {
                Logger::info(__FILE__, __CLASS__, __LINE__, "total amount is empty", var_export(array('order_id'=>$r['hash_id'],'amountInfo'=>$amountInfo), true));
                continue;
            }
            list($bank_type, $account) = Bll_User_Wallet::generate_bank_account($r['uid']);
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export(array($r['uid'], $amount, $r['dest_id'], $bank_type, $account), true));
            // 使用财务提现的接口
            $result = Bll_User_Wallet::finance_withdraw($r['uid'], $amount, $r['dest_id'], $bank_type, $account, 1);
        }
    }

    // 提现，按订单部分结算
    public function part_withdraw($orders){
        foreach($orders as $r) {
            Logger::info(__FILE__, __CLASS__, __LINE__, "auto part withdraw", var_export($r['hash_id'], true));
            //提取民宿全部金额
            $amountInfo = Bll_User_Wallet::user_total_amount($r['uid']);
            $total_amount = $amountInfo['total'];
            // 冻结金额
            $amount = Bll_User_Wallet::frozen_amount($r['hash_id']);
            // 已经结算金额
            if(!$amount) {
                $amount = Bll_User_Wallet::order_amount($r['hash_id']);
            }
            // 防止有赔付
            if($amount > $total_amount) {
                $amount = $total_amount;
            }
            if(!$amount) {
                Logger::info(__FILE__, __CLASS__, __LINE__, "total amount is empty", var_export(array('order_id'=>$r['hash_id'],'amountInfo'=>$amountInfo), true));
                continue;
            }
            list($bank_type, $account) = Bll_User_Wallet::generate_bank_account($r['uid']);
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export(array($r['uid'], $amount, $r['dest_id'], $bank_type, $account), true));
            // 使用财务提现的接口
            $result = Bll_User_Wallet::finance_withdraw($r['uid'], $amount, $r['dest_id'], $bank_type, $account, 1);
        }
    }

}
