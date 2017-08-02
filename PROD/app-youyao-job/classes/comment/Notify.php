<?php
class Comment_Notify {

    public function run() {
        $comment_bll = new Bll_Comment_CommentInfo();
        $order_bll = new Bll_Order_OrderInfo();
        $type = reset(getopt('', array("type:")));

        if($type == 'second') {
            $date = date('Y-m-d', strtotime("-1 week"));
        }else {
            $date = date('Y-m-d');
        }

        $checkout_list = $order_bll->get_checked_out_booking_by_date($date);
        foreach($checkout_list as $order) {
            $oids[] = $order['id'];
        }
        if(empty($oids)) return;

        $is_comment = $comment_bll->acquire_comment_by_orderids($oids);
        $is_comment_oid = array();
        foreach($is_comment as $comment) {
            $is_comment_oid[] = $comment['order_id'];
        }
        foreach($checkout_list as $row) {
            if(in_array($is_comment_oid, $row['id']) || $row['order_source'] == 'booking') continue;
            $send_mail_params = array(
                    'order_id' => $row['hash_id'],
                    'send'     => true,
                    'action'   => 'c_order_invite_comment',
                );
            if($type == 'second') {
                $send_mail_params['action'] = 'c_order_invite_comment_later';
            }
            Util_Curl::get(Util_Common::url("/m/send", "api"), $send_mail_params);
        }

        return;

    }

}
