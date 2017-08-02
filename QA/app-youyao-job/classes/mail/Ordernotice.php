<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/8/27
 * Time: 下午5:14
 */
class Mail_Ordernotice{
    //php /home/tonycai/one.zizaike.com.v2/app-zizaike-job/bin/launcher.php --class=Mail_Ordernotice
    public $last_id;
    //public $i;
    public function run(){
        echo "开始运行\n";
        $this->last_id = $this->getlastid();
        echo "最新id:".$this->last_id."\n";
        if($this->last_id == 1 )
        {
            exit('出错了'."\n");
        }
        while(true)
        {
            $i = $this->getlastid();
            if($i > $this->last_id){
                echo "最新id:$i\n";
                $r=$this->notice();
                if($r)
                {
                    $this->last_id = $i;
                }else
                {
                    exit('出错了'."\n");
                }
            }

            sleep(5);
        }

    }

    public function notice()
    {
        $weekarray=array("日","一","二","三","四","五","六");
        //$d=strtotime("10:38pm April 15 2015");
        //echo "创建日期是 " . date("Y-m-d h:i:sa", $d);
        //echo "星期".$weekarray[date("w")];
        //判断是否有新的通知
        $slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select `id`,`dest_id`,`uname`,`guest_name`,`guest_mail`,`guest_telnum`,`hash_id`,`room_name`,`nid`,`guest_date`,`guest_checkout_date`,`guest_days`,`guest_number`,`guest_child_number` from t_homestay_booking where id > '".$this->last_id."'";
        $stmt=  $slave_pdo->prepare($sql);
        if($stmt->execute())
        {
            $r = $stmt->fetchAll();
            echo "有".count($r)."条新订单\n";
            echo "开始发送!\n";
            foreach($r as $k =>$v){
                //http://optools.zizaike.com/order/1720767900/trac
                $body  = "订单号:"."<a href='http://optools.zizaike.com/order/".$v['hash_id']."/trac'>".$v['hash_id']."</a>"."<br/>";

                if($v['dest_id']!=12) {
                    $body .= "目的地:" . $v['dest_id'] . "<br/>";
                }else{
                    $body .= "目的地:" . "大陆". "<br/>";
                }

                $body .= "民宿name:".$v['uname']."<br/>";
                $body .= "预订房间:"."<a href = 'http://www.zizaike.com/r/".$v['nid']."'>".$v['room_name']."</a><br/>";
                $body .= "客人name:".$v['guest_name']."<br/>";
                $body .= "客人tel:".$v['guest_telnum']."<br/>";
                $body .= "客人mail:".$v['guest_mail']."<br/>";
                $body .= "入住日期:".$v['guest_date']."(周".$weekarray[date('w',strtotime($v['guest_date']))].")<br/>";
                $body .= "退房日期:".$v['guest_checkout_date']."(周".$weekarray[date('w',strtotime($v['guest_checkout_date']))].")<br/>";
                $body .= "入住天数:".$v['guest_days']."天<br/>";
                $body .= "入住人数:"."<br/>"."成人:".$v['guest_number']."人<br/>";
                $body .= "儿童:".$v['guest_child_number']."人<br/><br/>";
                $body .= "祝你生活愉快!"."<br/>";

                if($v['dest_id']==12)
                {
                    //"大陆民宿开发"<dl-mainland@zizaike.com>;
                    Util_SmtpMail::send_direct('vruan@zizaike.com', '大陆新订单通知', "Hello,Jiajia:"."<br/><br/>".$body);
                    echo "发送vruan成功!\n";
                    Util_SmtpMail::send_direct('dl-mainland@zizaike.com', '大陆新订单通知', "Hello,大陆开发团队:"."<br/><br/>".$body);
                    echo "发送jude成功!\n";
                }
            }
            echo "发送成功!\n";
            return true;
        }
        else {$r = 0;return $r;}
    }

    public function getlastid()
    {
        $slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select `id` from t_homestay_booking  order by id desc limit 1;";
        $stmt=  $slave_pdo->prepare($sql);
        if($stmt->execute())
        {$r = $stmt->fetch();
            return $r['id'];
        }
        else {$r = 1;return $r;}
    }


}





