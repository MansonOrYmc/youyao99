<?php

class Report_Total {
    private $end_date;
    private $start_date;

    public function run() {
        $this->end_date = date('Y-m-d',time());
        $this->start_date = date('Y-m-d',time()-86400);

        $total_jy = $this->get_total_jy();
        $total_price = $this->get_total_price();
        $total_cjdd = $this->get_total_cjdd();
        $total_dd = $this->get_total_tdd();
        $total_chanel_price = $this->get_total_chanel_price();

         $total_ali_out = 0;
        foreach($total_chanel_price as $key=>$value){
              if(in_array($key,array('alipay_out','iPhone_alipay_global'))){
                  $total_ali_out+=$value['total_price'];
              }
        }

        $total_dd_daizhifu = $this->get_total_tdd_bystatus(4);
        $total_dd_close = $this->get_total_tdd_bystatus(3);
        $total_dd_daichuli = $this->get_total_tdd_bystatus(0);
        $total_message = $this->get_message_count();
        $total_homestay = $this->get_newhomestay_count();
        $total_newroom  = $this->get_newroom_count();
        $total_comment  = $this->get_comment_count();
        $total_guest    = $this->get_newcustomer_count();


        $message = "<html><head></head><body>";
        $message .= "<p>Jack,</p>";
        $message .= "<p></p>";
        $message .= "<p>".$this->start_date." 交易数据：</p>";
        $message .= "<table width='240'>";
        $message.="<tr><td width='120'>成交间夜: </td><td align='right'>".$total_jy['total']."</td></tr>";
        $message.="<tr><td>成交金额: </td><td align='right'>".number_format($total_price['total'])."</td></tr>";
        $message.="<tr><td>成交均价: </td><td align='right'>".intval($total_price['total']/$total_jy['total'])."</td></tr>";
        $message.="<tr><td>成交订单数: </td><td align='right'>".$total_cjdd['total']."</td></tr>";
        $message.="<tr><td>境外alipay: </td><td align='right'>".number_format(intval($total_ali_out))."</td></tr>";
        $message .= "</table>";

        $message.= "<p>".$this->start_date." 网站订单转化:</p>";
        $message .= "<table width='240'>";
        $message.="<tr><td width='120'>总订单数：</td><td align='right'>".$total_dd['total']."</td></tr>";
        $message.="<tr><td>待支付订单数：</td><td align='right'>".$total_dd_daizhifu['total']."</td></tr>";
        $message.="<tr><td>关闭订单数：</td><td align='right'>".$total_dd_close['total']."</td></tr>";
        $message.="<tr><td>待处理订单数：</td><td align='right'>".$total_dd_daichuli['total']."</td></tr>";
        $message.="<tr><td>成交客人数量：</td><td align='right'>".$total_cjdd['total_user']."</td></tr>";
        $message.="<tr><td>成交民宿数量：</td><td align='right'>".$total_cjdd['total_homestay']."</td></tr>";
        $message.="<tr><td>私信发送量：</td><td align='right'>".number_format($total_message['total'])."</td></tr>";
        $message.="<tr><td>新增客人总数：</td><td align='right'>".$total_guest['total']."</td></tr>";
        $message.="<tr><td>下订单的客人总数：</td><td align='right'>".$total_dd['total_user']."</td></tr>";
        $message .= "</table>";

        $message.= "<p>".$this->start_date." 网站新增数据:</p>";
        $message .= "<table width='240'>";
        $message.="<tr><td width='120'>新增房间: </td><td align='right'>".$total_newroom['total']."</td></tr>";
        $message.="<tr><td>新增民宿: </td><td align='right'>".$total_homestay['total']."</td></tr>";
        $message.="<tr><td>新增点评: </td><td align='right'>".$total_comment['total']."</td></tr>";
        $message .= "</table>";

        $message .= "</table><body></html>";

        //Util_SmtpMail::send('tonycai@zizaike.com',"网站交易报告 - ".$this->start_date,$message);
        Util_SmtpMail::send('dl-managers@zizaike.com',"网站交易报告 - ".$this->start_date,$message);


        ////////////////////



        $message = "Jack,<br />alipay_cn 境内alipy ; alipay_net_cn 境内alipy ; alipay_out 境外支付宝 ; iPhone_alipay 境外支付宝 ; iPhone_alipay_global境外支付宝 ; iPhone_wechatpay 微信支付 ; newwxpay 微信扫描支付 ; unionpay 银联支付";
        foreach($total_chanel_price as $key=>$value){
            $message.="<br /><br />收款渠道:".$value['payment_type'];
            $message.="<br />成交订单数:".$value['total_number'];
            $message.="<br />成交间夜:".$value['total_jy'];
            $message.="<br />成交金额:".$value['total_price'];
            $message.="<br />成交均价:".intval($value['total_price']/$value['total_jy']);
        }
        //Util_SmtpMail::send('tonycai@zizaike.com',"网站交易报告【收款渠道】 - ".$this->start_date,$message);
        Util_SmtpMail::send('dl-managers@zizaike.com',"网站交易报告【收款渠道】 - ".$this->start_date,$message);


        ////////////////////

        $bee_list = $this->get_total_beejy();
        $message = "Dear All,<br />==自营销渠道== <br />";
        foreach($bee_list as $key=>$value){
            $message.=$value['campaign_code']." ".$value['total']."间夜<br />";
        }
        //Util_SmtpMail::send('tonycai@zizaike.com',"自在客- 自营销渠道 - ".$this->start_date,$message);
        Util_SmtpMail::send('dl-sales@zizaike.com',"自在客- 自营销渠道 - ".$this->start_date,$message);



    }


    public function get_total_jy(){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $sql = "select sum(a.room_num*a.guest_days) as total from LKYou.t_homestay_booking a
                where a.status in (2,6) and exists (select * from LKYou.log_homestay_booking_trac b
                where a.id = b.bid and b.status = 2 and b.create_date >= unix_timestamp('".$this->start_date."')
                and b.create_date < unix_timestamp('".$this->end_date."'))";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function get_total_price(){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $sql = "select sum(a.total_price) as total from LKYou.t_homestay_booking a
                where a.status in (2,6) and exists (select * from LKYou.log_homestay_booking_trac b
                where a.id = b.bid and b.status = 2 and b.create_date >= unix_timestamp('".$this->start_date."')
                and b.create_date < unix_timestamp('".$this->end_date."'))";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function get_total_chanel_price(){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $sql = "select count(a.id) as total_number,sum(a.room_num*a.guest_days) as total_jy, sum(a.total_price) as total_price,a.payment_type from LKYou.t_homestay_booking a
                where a.status in (2,6) and exists (select * from LKYou.log_homestay_booking_trac b
                where a.id = b.bid and b.status = 2 and b.create_date >= unix_timestamp('".$this->start_date."')
                and b.create_date < unix_timestamp('".$this->end_date."')) and a.payment_type in ('alipay_cn','alipay_net_cn','alipay_out','iPhone_alipay','iPhone_alipay_global','iPhone_wechatpay','newwxpay','unionpay') group by a.payment_type";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }


    public function get_total_cjdd(){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $sql = "select count(*) as total ,COUNT(DISTINCT a.uid) as total_homestay,COUNT(DISTINCT a.guest_mail) as total_user from LKYou.t_homestay_booking a
                where a.status in (2,6) and exists (select * from LKYou.log_homestay_booking_trac b
                where a.id = b.bid and b.status = 2 and b.create_date >= unix_timestamp('".$this->start_date."')
                and b.create_date < unix_timestamp('".$this->end_date."'))";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function get_total_tdd(){
    $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
    $sql = "select count(*) as total ,COUNT(DISTINCT uid) as total_homestay,COUNT(DISTINCT guest_mail) as total_user from LKYou.t_homestay_booking a
                where a.create_time >= unix_timestamp('".$this->start_date."')
                and a.create_time < unix_timestamp('".$this->end_date."')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetch();
}

    public function get_total_tdd_bystatus($status){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $sql = "select count(*) as total from LKYou.t_homestay_booking a
                where a.create_time >= unix_timestamp('".$this->start_date."')
                and a.create_time < unix_timestamp('".$this->end_date."') and a.status = $status";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function get_message_count(){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $sql = "select count(*) as total  from one_db.drupal_pm_message a
                where a.timestamp >unix_timestamp('".$this->start_date."')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function get_newhomestay_count(){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $sql = "select count(*) as total  from one_db.drupal_users a
                where a.created >unix_timestamp('".$this->start_date."') and a.uid in  ( select uid from one_db.drupal_users_roles where rid=5 )";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function get_newroom_count(){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $sql = "select count(*) as total  from one_db.drupal_node a
                where a.created >unix_timestamp('".$this->start_date."')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }


    public function get_comment_count(){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $sql = "select count(*) as total from LKYou.t_comment_info a
                where a.create_time >'".$this->start_date."'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }


    public function get_total_beejy(){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $sql = "select a.campaign_code,sum(a.room_num*a.guest_days) as total from LKYou.t_homestay_booking a
                where a.status in (2,6) and a.campaign_code like 'bee_%'  and exists (select * from LKYou.log_homestay_booking_trac b
                where a.id = b.bid and b.status = 2 and b.create_date >= unix_timestamp('".$this->start_date."')
                and b.create_date < unix_timestamp('".$this->end_date."')) group by a.campaign_code";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function get_newcustomer_count(){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $sql = "select count(*) as total from LKYou.t_customer a
                where a.create_time >unix_timestamp('".$this->start_date."')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }



}
