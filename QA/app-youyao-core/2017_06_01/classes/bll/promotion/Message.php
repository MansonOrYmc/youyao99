<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/12/7
 * Time: 上午10:01
 */
class  Bll_Promotion_Message{
    //获取每一天的促销信息
    public function get_one_room_pm($nid,$multilang=12){
        //目前的促销信息是民宿端打折，需要注意多语言
        $discs = Bll_Disc_Info::get_cal_discs_02($nid);
        if($discs){
            foreach ($discs as $date => $disc) {
                $message[$date] = new disc_pro_message($disc,$multilang);
            }
            return $message;
        }else{
            return false;
        }
    }


    function check_date_time($str, $format="Y-m-d"){
        $unixTime=strtotime($str);
        $checkDate= date($format, $unixTime);
        if($checkDate==$str)
            return 1;
        else
            return 0;
    }

}
class disc_pro_message{
    public $name;
    public $message;
    public $disc;
    function __construct($disc,$multilang=12){
        if($multilang<10 || $multilang>15){$multilang = 10;}
            if($disc['disc']<1&&$disc['disc']>0&&$disc['least_days']==1){
                //参与打折活动
//                $this->message = '订民宿,享'.($disc['disc']*10).'折优惠!';
                $this->message = sprintf(Trans::t("%d%% discount",$multilang),((100-$disc['disc']*100)));
            }elseif($disc['disc']<1&&$disc['disc']>0&&$disc['least_days']>1){
                //参与打折活动
//                $this->message = '订民宿,连住'.$disc['least_days'].'天,享'.($disc['disc']*10).'折优惠!';
                $this->message = sprintf(Trans::t("%d+ nights give %d%% discount",$multilang),$disc['least_days'],((100-$disc['disc']*100)));
            }else{
                //不参与打折活动
                $this->message = '';
            }
            $this->name = Trans::t('bnb_discount',$multilang);
            $this->disc = $disc['disc'];
            if($this->disc > 1){ $this->disc = 1;}

    }
}
