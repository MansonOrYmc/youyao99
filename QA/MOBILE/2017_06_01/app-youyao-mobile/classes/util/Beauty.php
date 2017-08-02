<?php

/**
 * Created by PhpStorm.
 * User: chanlevel
 * Date: 16/5/24
 * Time: 下午12:19
 */
class Util_Beauty
{
    public static function wanna($result)
    {

        if ($_REQUEST['beauty'] != 'true') return $result;
        //convert

        //如果设置了 code

        $data = $result['data'];
        if (empty($data)) $data = $result['body'];
        if (empty($data)) $data = $result['response'];

        if ($result['code'] !== NULL)
            if ($result['code'] == 1) {

                return array(
                    'status' => 200,
                    'msg' => $result['codeMsg'],
                    'userMsg' => $result['codeMsg'],
                    'data' => $data
                );

            } else {
                $response = array(
                    'status' => $result['code']==0?400:$result['code'],
                    'data' => null,
                    'userMsg' => $result['codeMsg'],
                    'msg' => $result['codeMsg'],
                );
                return $response;

            }
        // 设置了 status 并且不是数字
        if ($result['status']) {
            if ($result['status'] == 200 || strtolower($result['status']) == 'ok') {
                return array(
                    'status' => 200,
                    'msg' => $result['msg'],
                    'userMsg' => $result['userMsg'],
                    'data' => $data
                );
            } else {
                return array(
                    'status' => is_numeric($result['status']) ? $result['status'] : 400,
                    'data' => null,
                    'userMsg' => $result['userMsg'],
                    'msg' => $result['msg'],
                );

            }

        }


        return $result;

    }
}