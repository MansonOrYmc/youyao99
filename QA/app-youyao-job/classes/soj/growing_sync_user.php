<?php
/**
 * Created by PhpStorm.
 * User: chanlevel
 * Date: 16/8/3
 * Time: 下午7:23
 */

require_once('../../app-zizaike-core/classes/util/Curl.php');
$pid='1d75c27d16f44a75876af0d66976e3a2';
$projid='9ab0ff5db9d74358989b725953712228';

//$keyarray='USER_ID:77567';


$fields=array(
    'cs1'=>'USER_ID:77567',
    'cs2'=>"USER_NAME:LEC"
);
$url="https://data.growingio.com/saas/9ab0ff5db9d74358989b725953712228/user?";

$header[] = 'Access-Token = 1151f081312b4ac09450670a5a0175b9';
$header[]='Content-Type = application/json';

$auth_token=authToken($projid,$pid,array_values($fields)[0]);
echo $auth_token;
$result=Util_Curl::post( $url.'auth='.$auth_token,json_encode($fields),$header);
var_dump( $result);

function post($url,$data){
   // $ch = curl_init('http://106.186.117.229/index.php');
    $ch=curl_init($url);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
    $result = curl_exec($ch);
    curl_close($ch);
    echo $result;
}

function authToken($projectKeyId, $secretKey, $keyArray)
{
    $message="ai=".$projectKeyId."&cs=".$keyArray;
    return hash_hmac('sha256',$message, $secretKey, false);
}



