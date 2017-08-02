<?php

class Bll_Sms_SMSInfo
{
    private $smsInfoDao;

    public function __construct()
    {
        $this->smsInfoDao = new Dao_Sms_SMSInfo();
    }

    public function bll_send_sms_notify($info)
    {
        return $this->smsInfoDao->dao_send_sms_notify($info);
    }
}
