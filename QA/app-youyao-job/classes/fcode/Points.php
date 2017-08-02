<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 16/2/18
 * Time: 上午11:35
 * 如果优惠券已经使用过，并且已经入住，则发放积分！并将优惠劵状态改为已经使用！
 * 如果优惠券已经使用过，并且未入住，则不发放积分！并将优惠劵状态改为已经使用！
 * 如果优惠券未使用过,但是未过期，则不做处理！
 * 如果优惠券未使用过,并且已过期，则将fcode状态设置为失效
 */
class Fcode_Points{
    public function run(){
        //①选择 fcode 有效记录,并且是积分还未发放的!
        $fcodes =  Fcode_TW::select_all();
        $message_log = '';
        foreach($fcodes as $fcode){
            $coupon_bll = new Bll_Coupons_CouponsInfo();
            $order_dao = new Dao_Order_OrderInfo();

            if(!$fcode['t_uid']) continue;
            $order_list = $order_dao->get_order_list_byuid($fcode['t_uid']);
            $is_checkin = false;
            foreach($order_list as $row) {
                if(strtotime($row['guest_date']) < time()) {
                    $is_checkin = true;
                    $order_id = $row['id'];
                    break;
                }
            }
            //$is_checkin = $coupon_bll->is_coupon_checkin($fcode['coupon']);//优惠券使用订单是否已经入住

            $is_used = $coupon_bll->is_coupon_used($fcode['coupon']);//优惠券是否已使用过
//            $order_id = $coupon_bll->get_order_id_by_code($fcode['coupon']);//优惠券相关订单号
            $is_expired = $coupon_bll->is_coupon_expired($fcode['coupon']);//优惠券是否已经过期
            $nick_name = $fcode['dst_guest_telnum'] == 0 ? $fcode['dst_mail'] : $fcode['dst_guest_telnum'];
            $nick_name = substr_replace($nick_name, '****', 3, 4);
            if($is_checkin){
                //如果/*优惠券已经使用过*/，并且已经入住，则发放积分！并将优惠劵状态改为已经使用！
                Fcode_TW::insert_points($fcode['id'],$fcode['s_uid'],$order_id);
                if(!$fcode['src_phone_num'] || $fcode['src_phone_num'] == 0) continue;
                //get points value
                $conf_dest_id = $fcode['dest_id'] == 10 ? 10 : 12;
                $point_config = APF::get_instance()->get_config('point','fcode');
                $point_config = $point_config[$conf_dest_id];
                $point = $point_config['value'];
                //send sms
                $message_log= '('.date('Y-m-d H:i:s',REQUEST_TIME).')fcode:'.$fcode['id'].'[优惠券已经使用过，并且已经入住，发放积分！优惠劵状态改为已经使用！]'."\n";
                $sms_content = Trans::t('fcode_integral_sms_%l_%n', $fcode['dest_id'], array(
                    "%l" => $nick_name,
                    "%n" => $point,
                ));
                $params = array(
                    'oid' => 0,
                    'sid' => 0,
                    'uid' => $fcode['t_uid'],
                    'mobile' => $fcode['src_phone_num'],
                    'content' => $sms_content,
                    'area' => $fcode['dest_id'] == 10 ? 2 : 1,
                );
                $sms = new Util_Notify();
                $sms->send_sms_notify($params);
            }
            if($is_used){
                //如果优惠券已经使用过，并且未入住，则不发放积分！并将优惠劵状态改为已经使用！
                Fcode_TW::update($fcode['id'],array('is_coupon_used'=>'1'));
                $message_log= '('.date('Y-m-d H:i:s',REQUEST_TIME).')fcode:'.$fcode['id'].'[优惠券已经使用过，并且未入住，不发放积分！并将优惠劵状态改为已经使用！]'."\n";
            }
            if(!$is_used and $is_expired){
                //如果优惠券未使用过,并且已过期，则将fcode状态设置为失效
                $message_log= '('.date('Y-m-d H:i:s',REQUEST_TIME).')fcode:'.$fcode['id'].'[优惠券未使用过,并且已过期，将fcode状态设置为失效]'."\n";
                Fcode_TW::update($fcode['id'],array('status'=>0));
            }
            echo $message_log;
        }
    }
}
