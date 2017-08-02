<?php

/**
 * Created by PhpStorm.
 * User: chanlevel
 * Date: 16/5/18
 * Time: 下午7:40
 */
class Util_Recommend
{

    public static function webview_format($title, $img_url, $url)
    {
        $type = 'webview';
        $androidtarget = "com.youyao.taiwanlodge.WebViewAcivity";
        if ($_GET['os'] == 'android' && $_GET['version'] > 70) {
            $type = 'newwebview';
            $androidtarget = 'com.youyao.taiwanlodge.webview.PromoWebView_Activity';
        }

        return array(
            'image' => $img_url,
            'type' => $type,
            'url' => $url,
            'title' => $title,
            'android' => array(
                'target' => $androidtarget,
                'bundle' => array(
                    'url' => $url,
                ),
            ),
            'ios' => array(
                'target' => 'WebViewController',
                'bundle' => array(
                    'url' => $url,
                ),
                'storyboard' => 1,

            ),
        );
    }


    /**
     * @param $homestay_uid
     * @param $title
     * @param $img
     * @return array
     */
    public static function homestay_format($homestay_uid, $title, $img)
    {

        return array(
            'image' => $img,
            'title' => $title,
            'type' => 'homestay',

            'android' => array(
                'target' => 'com.youyao.taiwanlodge.room.HomestayDetailNew_Activity',
                'bundle' => array(
                    'homestayUid' => strval($homestay_uid),
                    'homestayName' => $title,
                ),
            ),
            'ios' => array(
                'target' => 'RoomListViewController',
                'storyboard' => 0,
                'bundle' => array(
                    'homestayUid' => strval($homestay_uid),
                    'homeName' => $title,
                ),
            ),
        );
    }


}