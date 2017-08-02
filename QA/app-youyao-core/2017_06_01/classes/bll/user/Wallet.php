<?php
class Bll_User_Wallet {

    public function bank_name($bank_type) {
        $bank_list = array(
            'TW_BANK' => Trans::t('bank_%b', null, array('%b' => Trans::t('taiwan') ) ),
            'JAPAN_BANK' => Trans::t('bank_%b', null, array('%b' => Trans::t('japan') ) ),
            'CN_BANK' => Trans::t('bank_%b', null, array('%b' => Trans::t('zh') ) ),
            'ALIPAY' => Trans::t('Alipay'),
            'PAYPAL' => Trans::t('homestaypayaltitle'),
        );
		return $bank_list[$bank_type];
    }

    public function type_name($type) {
        $status_name = array(
            "FREEZE" => Trans::t('frozen'),
            "FREEZE_AVAILABLE" => Trans::t("to_be_available"),
            "AVAILABLE" => Trans::t("funds_available"),
            "WITHDRAWING" => Trans::t("withdrawaling"),
            "WITHDRAWED" => Trans::t("withdrawaled"),
            "REFUNDED" => Trans::t("refunded"),
            "PAYOUT" => Trans::t("compensate"),
        );
        return $status_name[$type];
    }

    // 金额总览 
    public function user_total_amount($uid) {
        if(!$uid) return array();
        $path = "get/moneyDetail";
        $params = array(
            'uid' => $uid,
        );

        return self::send_request($path, $params, "GET");
    }

    // business交易列表
    // @ status 
    //  冻结 FREEZE
    //  转为可用 FREEZE_AVAILABLE
    //  可用 AVAILABLE
    //  提现中 WITHDRAWING
    //  已提现 WITHDRAWED 
    //  退款完成 REFUNDED 
    //  赔付 PAYOUT
    public function trade_list($uid, 
        $pageNo, $pageSize, 
        $status, 
        $timeBegin, $timeEnd,
        $username,
        $query
    ) {

        $path = "businessQuery";
        $params = array(
            'userId' => $uid,
            'pageNo' => $pageNo ? $pageNo : 1,
            'pageSize' => $pageSize,
            'status' => $status,
            'timeBegin' => $timeBegin,
            'timeEnd' => $timeEnd,
            //'withdrawId' => $query,
            'username' => $username,
            'keyNo' => $query,
        );

        return self::send_request($path, $params, "GET");
    }

    // business提现记录
    public function withdraw_list($uid,
        $timeBegin, $timeEnd,
        $pageNo, $pageSize,
        $finish
    ) {
        $params = array(
            'userId' => $uid,
        );
        if($timeBegin) $params['timeBegin'] = $timeBegin;
        if($timeEnd)  $params['timeEnd'] = $timeEnd;
        if($pageNo) $params['pageNo'] = $pageNo;
        if($pageSize) $params['pageSize'] = $pageSize;
        if(isset($finish)) $params['finish'] = $finish;

        $path = "businessWithdrawQuery";

        return self::send_request($path, $params, "GET");
    }

    // 交易详情
    public function trade_detail($trade_id) {
        $params = array(
            'tradeId' => $trade_id,
        );

        $path = 'get/businessDetail';

        return self::send_request($path, $params, "GET");
    }

    // 通过日期聚合
    public function trade_group_list($uid,$status,
        $pageNo, $pageSize,
        $timeBegin, $timeEnd,
        $withdrawId, $tradeId
    ) {
        $params = array(
            'userId' => $uid,
        );
        if($pageNo) $params['pageNo'] = $pageNo;
        if($pageSize) $params['pageSize'] = $pageSize;
        if($timeBegin) $params['timeBegin'] = $timeBegin;
        if($timeEnd) $params['timeEnd'] = $timeEnd;
        if($withdrawId) $params['withdrawId'] = $withdrawId;
        if($tradeId) $params['tradeId'] = $tradeId;

        $path = "appData";

        return self::send_request($path, $params, "GET");

    }

    // 提现申请
    /*
     * @account = array(
     * bank_name, // 总行名
     * bank_daihao, // 总行代号
     * bank_name_sub, // 分行名
     * bank_daihao_sub, // 分行代号
     * bank_account,  // 银行账号
     * bank_username, // 开户名
     * )
     * */
    public function withdraw_apply($uid, $amount, $dest_id, $bank_type, $account, $info=null) {

        $path = "withdraw";
        $accountArr = array(
            'accountName' => $account['bank_account'],
            'userName'    => $account['bank_username'],
            'bankName'    => $account['bank_name'],
            'bankCode'    => $account['bank_daihao'],
            'branchBankName' => $account['bank_name_sub'],
            'branchBankCode' => $account['bank_daihao_sub'],
        );
        $params = array(
            'userId' => $uid,
            'originPrice' => $amount,
            'currency' => $dest_id,
            'account' => json_encode($accountArr),
            'accountType' => $bank_type,
        );
        if($info) $params['info'] = $info;

        return self::send_request($path, $params, "POST");
    }

    // 财务后台列表
    /* @WalletStatus
     * 'EXPORT', // 待导出
     * 'EXPORTED', // 已导出
     * 'WITHDRAWED', // 已提现
     * 'CHECK', // 待审核
     * 'CHECKED', // 已审核
     */
    public function finance_list(
        $search_type, $status, $bank_type,
        $timeBegin, $timeEnd,
        $pageNo, $pageSize,
        $query_id, $query_username, $uid
    ) {

        $path = "finance/query";

        $params = array(
            'searchType' => $search_type, // 0： 审核 ， 1：提现
        );
        if($uid)    $params['userId'] = $uid;
        if($pageNo) $params['pageNo'] = $pageNo;
        if($status) $params['status'] = $status;
        if($timeBegin) $params['timeBegin'] = $timeBegin;
        if($timeEnd) $params['timeEnd'] = $timeEnd;
        if($query_id) $params['withdrawId'] = $query_id;
        if($query_username) $params['username'] = $query_username;
        if($bank_type) $params['type'] = $bank_type;

        $params['pageSize'] = $pageSize ? $pageSize : 50;

        return self::send_request($path, $params, "GET");
    }

    // 导出
    public function finance_export($w_ids, $admin_uid=null) {
        if(!is_array($w_ids)) $w_ids = array($w_w_ids);
        if(empty($w_ids)) return array();

        if(!$admin_uid) {
            $user = Util_Signin::get_user();
            $admin_uid = $user->uid;
        }

        $path = "finance/exportAction";

        $params = array(
            'withdrawIds' => implode(",", $w_ids),
            'operatorId' => $admin_uid,
        );

        return self::send_request($path, $params, "POST");
    }

    // 导出回退
    public function finance_export_rollback($w_ids, $admin_uid=null) {
        if(!is_array($w_ids)) $w_ids = array($w_ids);
        if(empty($w_ids)) return array();

        if(!$admin_uid) {
            $user = Util_Signin::get_user();
            $admin_uid = $user->uid;
        }

        $path = "finance/backToExport";

        $params = array(
            'withdrawIds' => implode(",", $w_ids),
            'operatorId' => $admin_uid,
        );

        return self::send_request($path, $params, "POST");

    }

    // 提现完成
    public function withdraw_approve($w_ids, $admin_uid=null) {
        if(!is_array($w_ids)) $w_ids = array($w_ids);
        if(empty($w_ids)) return array();

        if(!$admin_uid) {
            $user = Util_Signin::get_user();
            $admin_uid = $user->uid;
        }

        $path = "withdrawed";
        $params = array(
            'withdrawIds' => implode(",", $w_ids),
            'operatorId' => $admin_uid,
        );

        return self::send_request($path, $params, "POST");
    }

    // 非常规支出审核通过
    public function audit_approve($w_ids, $admin_uid=null) {
        if(!is_array($w_ids)) $w_ids = array($w_ids);
        if(empty($w_ids)) return array();

        if(!$admin_uid) {
            $user = Util_Signin::get_user();
            $admin_uid = $user->uid;
        }

        $path = "payout";
        $params = array(
            'withdrawIds' => implode(",", $w_ids),
            'operatorId' => $admin_uid,
        );

        return self::send_request($path, $params, "POST");
    }

    // excel数据
    public function excel_data($w_ids, $admin_uid=null) {
        if(!is_array($w_ids)) $w_ids = array($w_ids);
        if(empty($w_ids)) return array();

        if(!$admin_uid) {
            $user = Util_Signin::get_user();
            $admin_uid = $user->uid;
        }

        $path = "finance/exportToExcel";
        $params = array(
            'withdrawIds' => implode(",", $w_ids),
            'operatorId' => $admin_uid,
        );

        return self::send_request($path, $params, "POST");
    }

    // 财务发起的提现申请
    public function finance_withdraw(
        $uid, $price, $currency,
        $accountType, $account,
        $admin_uid
    ) {
        if(!$admin_uid) {
            $user = Util_Signin::get_user();
            $admin_uid = $user->uid;
        }

        $path = "finance/withdrawRequest";
        $accountArr = array(
            'accountName' => $account['bank_account'],
            'userName'    => $account['bank_username'],
            'bankName'    => $account['bank_name'],
            'bankCode'    => $account['bank_daihao'],
            'branchBankName' => $account['bank_name_sub'],
            'branchBankCode' => $account['bank_daihao_sub'],
        );
        $params = array(
            'userId' => $uid,
            'price' => $price,
            'currency' => $currency,
            'accountInfo' => json_encode($accountArr),
            'accountType' => $accountType,
            'operatorId' => $admin_uid,
        );

        return self::send_request($path, $params, "POST");
    }

    // 获得订单的冻结金额
    public function frozen_amount($order_id) {
        if(!$order_id) return;
        $path = "get/orderFreezeMoney";
        $params = array(
            'orderId' => $order_id,
        );

        return self::send_request($path, $params, "GET");
    }

    // 订单的解冻金额
    public function order_amount($order_id) {
        if(!$order_id) return;
        $path = "get/orderMoney";
        $params = array(
            'orderId' => $order_id,
        );

        return self::send_request($path, $params, "GET");
    }

    // 批量解冻
    public function check_out() {
        $path = "checkout";
        return self::send_request($path, array(), "GET");
    }

    public function generate_bank_account($uid, $userInfo=array()) {
        if(empty($userInfo)) {
            $homestaybll = new Bll_Homestay_StayInfo();
            $userInfo = $homestaybll->get_whole_stay_info_by_id($uid);
        }
        if($userInfo['bank_type_use'] == 1) {
            if($userInfo['dest_id'] == 10) $bank_type = "TW_BANK";
            if($userInfo['dest_id'] == 11) $bank_type = "JAPAN_BANK";
            if($userInfo['dest_id'] == 12) $bank_type = "CN_BANK";
            $account = array(
                 'bank_account'  => $userInfo['blank_account'],
                 'bank_username' => $userInfo['blank_username'],
                 'bank_name'   => $userInfo['blank_name'],
                 'bank_daihao' => $userInfo['blank_daihao'],
                 'bank_name_sub'   => $userInfo['blank_name_sub'],
                 'bank_daihao_sub' => $userInfo['blank_daihao_sub'],
            );
        }
        elseif($userInfo['bank_type_use'] == 2) {
            $bank_type = "CN_BANK";
            $account = array(
                 'bank_account'  => $userInfo['cn_blank_account'],
                 'bank_username' => $userInfo['cn_blank_username'],
                 'bank_name'   => $userInfo['cn_blank_name'],
                 'bank_name_sub'   => $userInfo['cn_blank_name_sub'],
            );
        }
        elseif($userInfo['bank_type_use'] == 3) {
            $bank_type = "ALIPAY";
            $account = array(
                 'bank_account'  => $userInfo['alipay_account'],
            );
        }
        elseif($userInfo['bank_type_use'] == 4) {
            $bank_type = "PAYPAL";
            $account = array(
                 'bank_account'  => $userInfo['paypal_account'],
            );
        }

        return array(
            $bank_type,
            $account,
        );
    }

    public function send_request($path, $params, $type) {

        $java_host = APF::get_instance()->get_config("wallet_api");
        $url = $java_host . $path;

        return InternalRequest::send_request($url, $params, $type);
    }


}
