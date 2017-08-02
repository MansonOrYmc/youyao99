<?php

/**
 * Created by PhpStorm.
 * User: chanlevel
 * Date: 16/3/4
 * Time: 上午11:08
 */
class Push_Pusher
{


    public static function guest_order_push($order_id)
    {


        $jump = array(
            'type' => 'guest_order',
            'android' => array(
                'target' => 'com.youyao.taiwanlodge.order.OrderDetail_Activity',
                'bundle' => array(
                    'order_id' => $order_id
                )

            ),
            'ios' => array(
                'target' => 'OrderDetailViewController',
                'storyboard' => 1,
                'bundle' => array(

                    'oid' => $order_id
                )
            ),
        );
        return $jump;
    }


    public static function admin_order_push($order_id)
    {

        $jump = array(
            'type' => 'admin_order',
            'android' => array(
                'target' => 'com.youyao.taiwanlodge.host.ui.AdminOrderDetail_Activity',
                'bundle' => array(
                    'order_id' => $order_id
                )
            ),
            'ios' => array(
                'target' => 'AdminOrderDetailViewController',
                'storyboard' => 1,
                'bundle' => array(

                    'oid' => $order_id
                )

            ));
        return $jump;

    }

    public static function homestay_recomend_push($homestay_uid)
    {
        $homestay_uid=$homestay_uid.'';
        $jump = array(
            'type' => 'homestay',
            'android' => array(
                'target' => 'com.youyao.taiwanlodge.room.HomestayDetailNew_Activity',
                'bundle' => array(
                    'homestayUid' => $homestay_uid
                )

            ),
            'ios' => array(
                'target' => 'RoomListViewController',
                'storyboard' => 0,
                'bundle' => array(

                    'homestayUid' => $homestay_uid
                )
            )


        );
        return $jump;
    }

    public static function service_recommend_push($serviceid){
        $jump = array(
            'type' => 'homestay',
            'android' => array(
                'target' => 'com.youyao.taiwanlodge.zzkservice.ServiceItemDetailActivity',
                'bundle' => array(
                    'SERVICE_ID' => $serviceid
                )

            ),
            'ios' => array(
                'target' => 'ServiceDetailViewController',
                'storyboard' => 0,
                'bundle' => array(
                    'serviceId' => (string) $serviceid

                )
            )


        );
        return $jump;

    }




}
